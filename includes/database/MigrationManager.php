<?php
/**
 * Database Migration Manager
 * Handles versioned database migrations for sustainable plugin development
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_MigrationManager {
    
    private $version = '1.0.0';
    private $migrations_table;
    private $database;
    
    public function __construct() {
        global $wpdb;
        $this->migrations_table = $wpdb->prefix . 'medx360_migrations';
        $this->database = new Medx360_Database();
        
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize migration system
     */
    public function init() {
        $this->check_migrations();
    }
    
    /**
     * Create migrations tracking table
     */
    public function create_migrations_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrations_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            migration_name varchar(255) NOT NULL,
            version varchar(20) NOT NULL,
            executed_at datetime DEFAULT CURRENT_TIMESTAMP,
            execution_time decimal(10,4) DEFAULT NULL,
            status enum('pending','running','completed','failed','rolled_back') DEFAULT 'pending',
            error_message text DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY migration_name (migration_name),
            KEY version (version),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Check and run pending migrations
     */
    public function check_migrations() {
        $current_version = get_option('medx360_db_version', '0.0.0');
        
        if (version_compare($current_version, $this->version, '<')) {
            $this->run_migrations($current_version, $this->version);
        }
    }
    
    /**
     * Run migrations from one version to another
     */
    public function run_migrations($from_version, $to_version) {
        $migrations = $this->get_migrations_to_run($from_version, $to_version);
        
        foreach ($migrations as $migration) {
            $this->run_migration($migration);
        }
        
        update_option('medx360_db_version', $to_version);
    }
    
    /**
     * Get migrations that need to be run
     */
    private function get_migrations_to_run($from_version, $to_version) {
        $all_migrations = $this->get_all_migrations();
        $executed_migrations = $this->get_executed_migrations();
        
        $pending_migrations = array();
        
        foreach ($all_migrations as $migration) {
            if (version_compare($migration['version'], $from_version, '>') && 
                version_compare($migration['version'], $to_version, '<=') &&
                !in_array($migration['name'], $executed_migrations)) {
                $pending_migrations[] = $migration;
            }
        }
        
        // Sort by version
        usort($pending_migrations, function($a, $b) {
            return version_compare($a['version'], $b['version']);
        });
        
        return $pending_migrations;
    }
    
    /**
     * Get all available migrations
     */
    private function get_all_migrations() {
        return array(
            // Version 1.0.0 - Initial tables
            array(
                'name' => 'create_core_tables_v1_0_0',
                'version' => '1.0.0',
                'class' => 'Medx360_Migration_1_0_0',
                'description' => 'Create core tables for patients, appointments, staff, clinics, services, payments, notifications, roles, permissions, and settings'
            ),
            
            // Version 1.1.0 - Add indexes and constraints
            array(
                'name' => 'add_indexes_and_constraints_v1_1_0',
                'version' => '1.1.0',
                'class' => 'Medx360_Migration_1_1_0',
                'description' => 'Add performance indexes and foreign key constraints'
            ),
            
            // Version 1.2.0 - Add audit logging
            array(
                'name' => 'add_audit_logging_v1_2_0',
                'version' => '1.2.0',
                'class' => 'Medx360_Migration_1_2_0',
                'description' => 'Add audit logging tables for tracking changes'
            ),
            
            // Version 1.3.0 - Add premium features
            array(
                'name' => 'add_premium_features_v1_3_0',
                'version' => '1.3.0',
                'class' => 'Medx360_Migration_1_3_0',
                'description' => 'Add premium feature tables (locations, resources, integrations, reports)'
            ),
            
            // Version 1.4.0 - Add advanced features
            array(
                'name' => 'add_advanced_features_v1_4_0',
                'version' => '1.4.0',
                'class' => 'Medx360_Migration_1_4_0',
                'description' => 'Add advanced features (templates, workflows, analytics)'
            )
        );
    }
    
    /**
     * Get executed migrations
     */
    private function get_executed_migrations() {
        global $wpdb;
        
        $results = $wpdb->get_col(
            "SELECT migration_name FROM {$this->migrations_table} WHERE status = 'completed'"
        );
        
        return $results;
    }
    
    /**
     * Run a single migration
     */
    private function run_migration($migration) {
        global $wpdb;
        
        $start_time = microtime(true);
        
        try {
            // Mark migration as running
            $wpdb->insert(
                $this->migrations_table,
                array(
                    'migration_name' => $migration['name'],
                    'version' => $migration['version'],
                    'status' => 'running'
                ),
                array('%s', '%s', '%s')
            );
            
            // Load and run migration class
            $migration_class = $migration['class'];
            if (class_exists($migration_class)) {
                $migration_instance = new $migration_class();
                $migration_instance->up();
            } else {
                throw new Exception("Migration class {$migration_class} not found");
            }
            
            $execution_time = microtime(true) - $start_time;
            
            // Mark migration as completed
            $wpdb->update(
                $this->migrations_table,
                array(
                    'status' => 'completed',
                    'execution_time' => $execution_time
                ),
                array('migration_name' => $migration['name']),
                array('%s', '%f'),
                array('%s')
            );
            
            error_log("Medx360 Migration {$migration['name']} completed in {$execution_time}s");
            
        } catch (Exception $e) {
            $execution_time = microtime(true) - $start_time;
            
            // Mark migration as failed
            $wpdb->update(
                $this->migrations_table,
                array(
                    'status' => 'failed',
                    'execution_time' => $execution_time,
                    'error_message' => $e->getMessage()
                ),
                array('migration_name' => $migration['name']),
                array('%s', '%f', '%s'),
                array('%s')
            );
            
            error_log("Medx360 Migration {$migration['name']} failed: " . $e->getMessage());
            
            // Optionally rollback
            $this->rollback_migration($migration);
        }
    }
    
    /**
     * Rollback a migration
     */
    private function rollback_migration($migration) {
        global $wpdb;
        
        try {
            $migration_class = $migration['class'];
            if (class_exists($migration_class)) {
                $migration_instance = new $migration_class();
                if (method_exists($migration_instance, 'down')) {
                    $migration_instance->down();
                }
            }
            
            $wpdb->update(
                $this->migrations_table,
                array('status' => 'rolled_back'),
                array('migration_name' => $migration['name']),
                array('%s'),
                array('%s')
            );
            
        } catch (Exception $e) {
            error_log("Medx360 Migration rollback failed for {$migration['name']}: " . $e->getMessage());
        }
    }
    
    /**
     * Get migration status
     */
    public function get_migration_status() {
        global $wpdb;
        
        $migrations = $wpdb->get_results(
            "SELECT * FROM {$this->migrations_table} ORDER BY version ASC, executed_at ASC"
        );
        
        return $migrations;
    }
    
    /**
     * Force run a specific migration (for development)
     */
    public function force_run_migration($migration_name) {
        $all_migrations = $this->get_all_migrations();
        
        foreach ($all_migrations as $migration) {
            if ($migration['name'] === $migration_name) {
                $this->run_migration($migration);
                break;
            }
        }
    }
    
    /**
     * Reset all migrations (for development)
     */
    public function reset_migrations() {
        global $wpdb;
        
        // Delete migration records
        $wpdb->query("DELETE FROM {$this->migrations_table}");
        
        // Reset version
        delete_option('medx360_db_version');
        
        // Run all migrations from scratch
        $this->run_migrations('0.0.0', $this->version);
    }
}
