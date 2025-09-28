<?php
/**
 * Migration: Add Multi-Language Support
 * Version: 1.4.0
 * Created: 2024-01-15 10:00:00
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Migration_AddMultiLanguageSupport {
    
    /**
     * Run the migration
     */
    public function up() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create translations table
        $table_name = $wpdb->prefix . 'medx360_translations';
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            entity_type varchar(50) NOT NULL,
            entity_id int(11) NOT NULL,
            field_name varchar(100) NOT NULL,
            language_code varchar(10) NOT NULL,
            translation_value text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY entity_type (entity_type),
            KEY entity_id (entity_id),
            KEY field_name (field_name),
            KEY language_code (language_code),
            UNIQUE KEY unique_translation (entity_type, entity_id, field_name, language_code)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add language support to clinics table
        $clinics_table = $wpdb->prefix . 'medx360_clinics';
        $wpdb->query("ALTER TABLE $clinics_table ADD COLUMN default_language varchar(10) DEFAULT 'en' AFTER status");
        $wpdb->query("ALTER TABLE $clinics_table ADD COLUMN supported_languages text AFTER default_language");
        
        // Add language support to services table
        $services_table = $wpdb->prefix . 'medx360_services';
        $wpdb->query("ALTER TABLE $services_table ADD COLUMN language varchar(10) DEFAULT 'en' AFTER status");
        
        error_log('MedX360 Migration: Added multi-language support');
    }
    
    /**
     * Rollback the migration
     */
    public function down() {
        global $wpdb;
        
        // Remove language support from services table
        $services_table = $wpdb->prefix . 'medx360_services';
        $wpdb->query("ALTER TABLE $services_table DROP COLUMN language");
        
        // Remove language support from clinics table
        $clinics_table = $wpdb->prefix . 'medx360_clinics';
        $wpdb->query("ALTER TABLE $clinics_table DROP COLUMN supported_languages");
        $wpdb->query("ALTER TABLE $clinics_table DROP COLUMN default_language");
        
        // Drop translations table
        $table_name = $wpdb->prefix . 'medx360_translations';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        error_log('MedX360 Migration: Rolled back multi-language support');
    }
}
