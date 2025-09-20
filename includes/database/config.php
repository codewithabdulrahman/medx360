<?php
/**
 * Database Configuration
 * Centralized configuration for database settings
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Configuration Class
 */
class Medx360_DatabaseConfig {
    
    /**
     * Database version
     */
    const VERSION = '1.2.0';
    
    /**
     * Table prefix
     */
    const TABLE_PREFIX = 'medx360_';
    
    /**
     * Migration settings
     */
    const MIGRATION_SETTINGS = array(
        'auto_run' => true,
        'log_errors' => true,
        'backup_before_migration' => false,
        'timeout' => 300, // 5 minutes
        'memory_limit' => '256M'
    );
    
    /**
     * Table configurations
     */
    const TABLE_CONFIGS = array(
        'patients' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'appointments' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'staff' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'clinics' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'services' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'payments' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'notifications' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'roles' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'permissions' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ),
        'settings' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        )
    );
    
    /**
     * Index configurations
     */
    const INDEX_CONFIGS = array(
        'patients' => array(
            'email' => array('type' => 'UNIQUE', 'columns' => 'email'),
            'status' => array('type' => 'INDEX', 'columns' => 'status'),
            'created_at' => array('type' => 'INDEX', 'columns' => 'created_at'),
            'name_status' => array('type' => 'INDEX', 'columns' => 'first_name, last_name, status')
        ),
        'appointments' => array(
            'patient_id' => array('type' => 'INDEX', 'columns' => 'patient_id'),
            'staff_id' => array('type' => 'INDEX', 'columns' => 'staff_id'),
            'appointment_date' => array('type' => 'INDEX', 'columns' => 'appointment_date'),
            'status' => array('type' => 'INDEX', 'columns' => 'status'),
            'date_status' => array('type' => 'INDEX', 'columns' => 'appointment_date, status'),
            'staff_date' => array('type' => 'INDEX', 'columns' => 'staff_id, appointment_date')
        ),
        'staff' => array(
            'email' => array('type' => 'UNIQUE', 'columns' => 'email'),
            'user_id' => array('type' => 'INDEX', 'columns' => 'user_id'),
            'status' => array('type' => 'INDEX', 'columns' => 'status'),
            'specialty_status' => array('type' => 'INDEX', 'columns' => 'specialty, status')
        ),
        'clinics' => array(
            'status' => array('type' => 'INDEX', 'columns' => 'status'),
            'type' => array('type' => 'INDEX', 'columns' => 'type'),
            'created_at' => array('type' => 'INDEX', 'columns' => 'created_at'),
            'type_status' => array('type' => 'INDEX', 'columns' => 'type, status')
        ),
        'services' => array(
            'status' => array('type' => 'INDEX', 'columns' => 'status'),
            'category' => array('type' => 'INDEX', 'columns' => 'category'),
            'created_at' => array('type' => 'INDEX', 'columns' => 'created_at'),
            'category_status' => array('type' => 'INDEX', 'columns' => 'category, status')
        ),
        'payments' => array(
            'patient_id' => array('type' => 'INDEX', 'columns' => 'patient_id'),
            'appointment_id' => array('type' => 'INDEX', 'columns' => 'appointment_id'),
            'payment_status' => array('type' => 'INDEX', 'columns' => 'payment_status'),
            'payment_date' => array('type' => 'INDEX', 'columns' => 'payment_date'),
            'date_status' => array('type' => 'INDEX', 'columns' => 'payment_date, payment_status')
        ),
        'notifications' => array(
            'user_id' => array('type' => 'INDEX', 'columns' => 'user_id'),
            'type' => array('type' => 'INDEX', 'columns' => 'type'),
            'is_read' => array('type' => 'INDEX', 'columns' => 'is_read'),
            'created_at' => array('type' => 'INDEX', 'columns' => 'created_at')
        ),
        'roles' => array(
            'name' => array('type' => 'UNIQUE', 'columns' => 'name')
        ),
        'permissions' => array(
            'user_id' => array('type' => 'INDEX', 'columns' => 'user_id'),
            'role_id' => array('type' => 'INDEX', 'columns' => 'role_id')
        ),
        'settings' => array(
            'setting_key' => array('type' => 'UNIQUE', 'columns' => 'setting_key')
        )
    );
    
    /**
     * Foreign key configurations
     */
    const FOREIGN_KEY_CONFIGS = array(
        'appointments' => array(
            'patient_id' => array(
                'reference_table' => 'patients',
                'reference_column' => 'id',
                'on_delete' => 'CASCADE',
                'on_update' => 'CASCADE'
            ),
            'staff_id' => array(
                'reference_table' => 'staff',
                'reference_column' => 'id',
                'on_delete' => 'CASCADE',
                'on_update' => 'CASCADE'
            )
        ),
        'payments' => array(
            'patient_id' => array(
                'reference_table' => 'patients',
                'reference_column' => 'id',
                'on_delete' => 'CASCADE',
                'on_update' => 'CASCADE'
            ),
            'appointment_id' => array(
                'reference_table' => 'appointments',
                'reference_column' => 'id',
                'on_delete' => 'SET NULL',
                'on_update' => 'CASCADE'
            )
        ),
        'notifications' => array(
            'user_id' => array(
                'reference_table' => 'users',
                'reference_column' => 'ID',
                'on_delete' => 'CASCADE',
                'on_update' => 'CASCADE'
            )
        ),
        'staff' => array(
            'user_id' => array(
                'reference_table' => 'users',
                'reference_column' => 'ID',
                'on_delete' => 'SET NULL',
                'on_update' => 'CASCADE'
            )
        ),
        'permissions' => array(
            'user_id' => array(
                'reference_table' => 'users',
                'reference_column' => 'ID',
                'on_delete' => 'CASCADE',
                'on_update' => 'CASCADE'
            ),
            'role_id' => array(
                'reference_table' => 'roles',
                'reference_column' => 'id',
                'on_delete' => 'CASCADE',
                'on_update' => 'CASCADE'
            )
        )
    );
    
    /**
     * Get database version
     */
    public static function get_version() {
        return self::VERSION;
    }
    
    /**
     * Get table prefix
     */
    public static function get_table_prefix() {
        return self::TABLE_PREFIX;
    }
    
    /**
     * Get migration settings
     */
    public static function get_migration_settings() {
        return self::MIGRATION_SETTINGS;
    }
    
    /**
     * Get table configuration
     */
    public static function get_table_config($table_name) {
        return isset(self::TABLE_CONFIGS[$table_name]) ? self::TABLE_CONFIGS[$table_name] : array();
    }
    
    /**
     * Get index configuration for table
     */
    public static function get_index_config($table_name) {
        return isset(self::INDEX_CONFIGS[$table_name]) ? self::INDEX_CONFIGS[$table_name] : array();
    }
    
    /**
     * Get foreign key configuration for table
     */
    public static function get_foreign_key_config($table_name) {
        return isset(self::FOREIGN_KEY_CONFIGS[$table_name]) ? self::FOREIGN_KEY_CONFIGS[$table_name] : array();
    }
    
    /**
     * Get all table names
     */
    public static function get_all_table_names() {
        return array_keys(self::TABLE_CONFIGS);
    }
    
    /**
     * Check if table exists in configuration
     */
    public static function table_exists($table_name) {
        return isset(self::TABLE_CONFIGS[$table_name]);
    }
    
    /**
     * Get charset collate for table
     */
    public static function get_charset_collate($table_name = null) {
        global $wpdb;
        
        if ($table_name && isset(self::TABLE_CONFIGS[$table_name])) {
            $config = self::TABLE_CONFIGS[$table_name];
            return $config['charset'] . '_' . $config['collate'];
        }
        
        return $wpdb->get_charset_collate();
    }
}
