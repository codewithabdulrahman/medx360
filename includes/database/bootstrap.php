<?php
/**
 * Database Bootstrap File
 * Centralizes all database-related includes and initialization
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include database configuration
require_once MEDX360_PLUGIN_DIR . 'includes/database/config.php';

// Define database version from config
define('MEDX360_DB_VERSION', Medx360_DatabaseConfig::get_version());

// Include core database classes
require_once MEDX360_PLUGIN_DIR . 'includes/database/Migration.php';
require_once MEDX360_PLUGIN_DIR . 'includes/database/MigrationManager.php';
require_once MEDX360_PLUGIN_DIR . 'includes/database/Database.php';

// Include all migration files
require_once MEDX360_PLUGIN_DIR . 'includes/database/migrations/Migration_1_0_0.php';
require_once MEDX360_PLUGIN_DIR . 'includes/database/migrations/Migration_1_1_0.php';
require_once MEDX360_PLUGIN_DIR . 'includes/database/migrations/Migration_1_2_0.php';

// Future migrations will be added here
// require_once MEDX360_PLUGIN_DIR . 'includes/database/migrations/Migration_1_3_0.php';
// require_once MEDX360_PLUGIN_DIR . 'includes/database/migrations/Migration_1_4_0.php';

/**
 * Database Initialization Class
 * Handles all database-related initialization
 */
class Medx360_DatabaseBootstrap {
    
    private static $instance = null;
    private $database;
    private $migration_manager;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize database components
     */
    private function init() {
        // Initialize core database components
        $this->database = new Medx360_Database();
        $this->migration_manager = new Medx360_MigrationManager();
        
        // Hook into WordPress
        add_action('init', array($this, 'check_database_version'));
        add_action('admin_init', array($this, 'admin_init'));
        
        // Plugin activation/deactivation hooks
        register_activation_hook(MEDX360_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(MEDX360_PLUGIN_FILE, array($this, 'deactivate'));
    }
    
    /**
     * Check database version and run migrations if needed
     */
    public function check_database_version() {
        $current_version = get_option('medx360_db_version', '0.0.0');
        
        if (version_compare($current_version, MEDX360_DB_VERSION, '<')) {
            $this->run_migrations($current_version, MEDX360_DB_VERSION);
        }
    }
    
    /**
     * Run migrations between versions
     */
    private function run_migrations($from_version, $to_version) {
        try {
            $this->migration_manager->run_migrations($from_version, $to_version);
            update_option('medx360_db_version', $to_version);
            
            // Log successful migration
            error_log("Medx360: Database migrated from $from_version to $to_version");
            
        } catch (Exception $e) {
            error_log("Medx360: Migration failed - " . $e->getMessage());
            
            // Send admin notification if migration fails
            if (is_admin()) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error"><p>';
                    echo '<strong>Medx360 Database Error:</strong> ' . esc_html($e->getMessage());
                    echo ' Please check your database configuration.';
                    echo '</p></div>';
                });
            }
        }
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // Admin initialization - simplified without database health checks
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create migrations table
        $this->migration_manager->create_migrations_table();
        
        // Run all migrations
        $this->run_migrations('0.0.0', MEDX360_DB_VERSION);
        
        // Set plugin version
        update_option('medx360_version', MEDX360_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear transients
        delete_transient('medx360_db_health');
    }
    
    /**
     * Get database instance
     */
    public function get_database() {
        return $this->database;
    }
    
    /**
     * Get migration manager instance
     */
    public function get_migration_manager() {
        return $this->migration_manager;
    }
    
    
    /**
     * Force run migrations (for development)
     */
    public function force_migrations() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->migration_manager->reset_migrations();
        }
    }
    
    /**
     * Get migration status
     */
    public function get_migration_status() {
        return $this->migration_manager->get_migration_status();
    }
}

/**
 * Initialize database bootstrap
 */
Medx360_DatabaseBootstrap::get_instance();

/**
 * Helper functions for database access
 */

/**
 * Get database instance
 */
function medx360_get_database() {
    return Medx360_DatabaseBootstrap::get_instance()->get_database();
}

/**
 * Get migration manager instance
 */
function medx360_get_migration_manager() {
    return Medx360_DatabaseBootstrap::get_instance()->get_migration_manager();
}


/**
 * Get table name with prefix
 */
function medx360_get_table($table_name) {
    return medx360_get_database()->get_table($table_name);
}

/**
 * Get database version
 */
function medx360_get_db_version() {
    return get_option('medx360_db_version', '0.0.0');
}

/**
 * Check if migration is needed
 */
function medx360_needs_migration() {
    $current_version = medx360_get_db_version();
    return version_compare($current_version, MEDX360_DB_VERSION, '<');
}
