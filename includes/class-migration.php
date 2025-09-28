<?php
/**
 * Database Migration System for MedX360
 * Handles schema changes and updates safely
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Migration {
    
    private static $instance = null;
    private $migrations_dir;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->migrations_dir = MEDX360_PLUGIN_DIR . 'includes/migrations/';
    }
    
    /**
     * Run all pending migrations
     */
    public function run_migrations() {
        $current_version = $this->get_current_migration_version();
        $target_version = $this->get_target_migration_version();
        
        if ($current_version >= $target_version) {
            return; // No migrations needed
        }
        
        $migrations = $this->get_pending_migrations($current_version, $target_version);
        
        foreach ($migrations as $migration) {
            $this->run_single_migration($migration);
        }
        
        // Update migration version
        update_option('medx360_migration_version', $target_version);
    }
    
    /**
     * Get current migration version
     */
    private function get_current_migration_version() {
        return get_option('medx360_migration_version', '1.0.0');
    }
    
    /**
     * Get target migration version (plugin version)
     */
    private function get_target_migration_version() {
        return MEDX360_VERSION;
    }
    
    /**
     * Get pending migrations
     */
    private function get_pending_migrations($current_version, $target_version) {
        $migrations = array();
        
        // Define migration files in order
        $migration_files = array(
            '1.0.0' => '001_initial_schema.php',
            '1.1.0' => '002_add_patient_table.php',
            '1.2.0' => '003_add_notifications_table.php',
            '1.3.0' => '004_add_appointment_reminders.php',
            '1.4.0' => '005_add_multi_language_support.php',
        );
        
        foreach ($migration_files as $version => $file) {
            if (version_compare($version, $current_version, '>') && 
                version_compare($version, $target_version, '<=')) {
                $migrations[] = array(
                    'version' => $version,
                    'file' => $file
                );
            }
        }
        
        return $migrations;
    }
    
    /**
     * Run a single migration
     */
    private function run_single_migration($migration) {
        $file_path = $this->migrations_dir . $migration['file'];
        
        if (!file_exists($file_path)) {
            error_log("MedX360 Migration: File not found - {$file_path}");
            return false;
        }
        
        // Include migration file
        require_once $file_path;
        
        $class_name = $this->get_migration_class_name($migration['file']);
        
        if (!class_exists($class_name)) {
            error_log("MedX360 Migration: Class not found - {$class_name}");
            return false;
        }
        
        $migration_instance = new $class_name();
        
        try {
            // Run migration
            $migration_instance->up();
            
            // Log successful migration
            error_log("MedX360 Migration: Successfully ran {$migration['file']}");
            
            return true;
            
        } catch (Exception $e) {
            error_log("MedX360 Migration: Failed to run {$migration['file']} - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get migration class name from file name
     */
    private function get_migration_class_name($file) {
        $name = str_replace('.php', '', $file);
        $parts = explode('_', $name);
        $class_name = 'MedX360_Migration_' . implode('_', array_map('ucfirst', array_slice($parts, 1)));
        return $class_name;
    }
    
    /**
     * Rollback a migration (for development/testing)
     */
    public function rollback_migration($version) {
        $file_path = $this->migrations_dir . "rollback_{$version}.php";
        
        if (!file_exists($file_path)) {
            error_log("MedX360 Migration: Rollback file not found - {$file_path}");
            return false;
        }
        
        require_once $file_path;
        
        $class_name = "MedX360_Migration_Rollback_{$version}";
        
        if (!class_exists($class_name)) {
            error_log("MedX360 Migration: Rollback class not found - {$class_name}");
            return false;
        }
        
        $rollback_instance = new $class_name();
        
        try {
            $rollback_instance->down();
            error_log("MedX360 Migration: Successfully rolled back {$version}");
            return true;
        } catch (Exception $e) {
            error_log("MedX360 Migration: Failed to rollback {$version} - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new migration file template
     */
    public function create_migration($name, $version) {
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $filepath = $this->migrations_dir . $filename;
        
        $class_name = 'MedX360_Migration_' . str_replace('_', '', ucwords($name, '_'));
        
        $template = "<?php
/**
 * Migration: {$name}
 * Version: {$version}
 * Created: " . date('Y-m-d H:i:s') . "
 */

if (!defined('ABSPATH')) {
    exit;
}

class {$class_name} {
    
    /**
     * Run the migration
     */
    public function up() {
        global \$wpdb;
        
        // Add your migration code here
        // Example:
        // \$table_name = \$wpdb->prefix . 'medx360_new_table';
        // \$sql = \"CREATE TABLE \$table_name (
        //     id int(11) NOT NULL AUTO_INCREMENT,
        //     name varchar(255) NOT NULL,
        //     created_at datetime DEFAULT CURRENT_TIMESTAMP,
        //     PRIMARY KEY (id)
        // ) \" . \$wpdb->get_charset_collate() . \";\";
        // 
        // require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        // dbDelta(\$sql);
        
        error_log('MedX360 Migration: Running {$name}');
    }
    
    /**
     * Rollback the migration
     */
    public function down() {
        global \$wpdb;
        
        // Add your rollback code here
        // Example:
        // \$table_name = \$wpdb->prefix . 'medx360_new_table';
        // \$wpdb->query(\"DROP TABLE IF EXISTS \$table_name\");
        
        error_log('MedX360 Migration: Rolling back {$name}');
    }
}";
        
        if (!is_dir($this->migrations_dir)) {
            wp_mkdir_p($this->migrations_dir);
        }
        
        file_put_contents($filepath, $template);
        
        return $filepath;
    }
    
    /**
     * Get migration status
     */
    public function get_migration_status() {
        $current_version = $this->get_current_migration_version();
        $target_version = $this->get_target_migration_version();
        
        return array(
            'current_version' => $current_version,
            'target_version' => $target_version,
            'needs_update' => version_compare($current_version, $target_version, '<'),
            'pending_migrations' => $this->get_pending_migrations($current_version, $target_version)
        );
    }
}
