<?php
/**
 * Base Migration Class
 * All migrations should extend this class
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class Medx360_Migration {
    
    protected $database;
    
    public function __construct() {
        $this->database = new Medx360_Database();
    }
    
    /**
     * Run the migration (upgrade)
     * Must be implemented by child classes
     */
    abstract public function up();
    
    /**
     * Rollback the migration (downgrade)
     * Optional - implement if rollback is possible
     */
    public function down() {
        // Default implementation - no rollback
        throw new Exception('Rollback not implemented for this migration');
    }
    
    /**
     * Helper method to execute SQL
     */
    protected function execute_sql($sql) {
        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Helper method to check if table exists
     */
    protected function table_exists($table_name) {
        global $wpdb;
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    }
    
    /**
     * Helper method to check if column exists
     */
    protected function column_exists($table_name, $column_name) {
        global $wpdb;
        
        $columns = $wpdb->get_col("SHOW COLUMNS FROM $table_name");
        return in_array($column_name, $columns);
    }
    
    /**
     * Helper method to add column if it doesn't exist
     */
    protected function add_column_if_not_exists($table_name, $column_name, $column_definition) {
        if (!$this->column_exists($table_name, $column_name)) {
            global $wpdb;
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN $column_name $column_definition");
        }
    }
    
    /**
     * Helper method to drop column if it exists
     */
    protected function drop_column_if_exists($table_name, $column_name) {
        if ($this->column_exists($table_name, $column_name)) {
            global $wpdb;
            $wpdb->query("ALTER TABLE $table_name DROP COLUMN $column_name");
        }
    }
    
    /**
     * Helper method to add index if it doesn't exist
     */
    protected function add_index_if_not_exists($table_name, $index_name, $columns) {
        global $wpdb;
        
        $indexes = $wpdb->get_results("SHOW INDEX FROM $table_name");
        $index_exists = false;
        
        foreach ($indexes as $index) {
            if ($index->Key_name === $index_name) {
                $index_exists = true;
                break;
            }
        }
        
        if (!$index_exists) {
            $wpdb->query("ALTER TABLE $table_name ADD INDEX $index_name ($columns)");
        }
    }
    
    /**
     * Helper method to drop index if it exists
     */
    protected function drop_index_if_exists($table_name, $index_name) {
        global $wpdb;
        
        $indexes = $wpdb->get_results("SHOW INDEX FROM $table_name");
        $index_exists = false;
        
        foreach ($indexes as $index) {
            if ($index->Key_name === $index_name) {
                $index_exists = true;
                break;
            }
        }
        
        if ($index_exists) {
            $wpdb->query("ALTER TABLE $table_name DROP INDEX $index_name");
        }
    }
    
    /**
     * Helper method to insert default data
     */
    protected function insert_default_data($table_name, $data) {
        global $wpdb;
        
        foreach ($data as $row) {
            $wpdb->replace($table_name, $row);
        }
    }
    
    /**
     * Helper method to get charset collate
     */
    protected function get_charset_collate() {
        global $wpdb;
        return $wpdb->get_charset_collate();
    }
}
