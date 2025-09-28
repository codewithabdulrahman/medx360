<?php
/**
 * Migration: Add Patient Table
 * Version: 1.1.0
 * Created: 2024-01-15 10:00:00
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Migration_AddPatientTable {
    
    /**
     * Run the migration
     */
    public function up() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create patients table
        $table_name = $wpdb->prefix . 'medx360_patients';
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20),
            date_of_birth date,
            gender enum('male','female','other'),
            address text,
            city varchar(100),
            state varchar(100),
            country varchar(100),
            postal_code varchar(20),
            emergency_contact_name varchar(255),
            emergency_contact_phone varchar(20),
            medical_history text,
            allergies text,
            medications text,
            insurance_provider varchar(255),
            insurance_number varchar(100),
            status enum('active','inactive','archived') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY city (city),
            KEY state (state)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add patient_id column to bookings table
        $bookings_table = $wpdb->prefix . 'medx360_bookings';
        $wpdb->query("ALTER TABLE $bookings_table ADD COLUMN patient_id int(11) AFTER service_id");
        $wpdb->query("ALTER TABLE $bookings_table ADD KEY patient_id (patient_id)");
        
        error_log('MedX360 Migration: Added patients table and patient_id to bookings');
    }
    
    /**
     * Rollback the migration
     */
    public function down() {
        global $wpdb;
        
        // Remove patient_id column from bookings table
        $bookings_table = $wpdb->prefix . 'medx360_bookings';
        $wpdb->query("ALTER TABLE $bookings_table DROP COLUMN patient_id");
        
        // Drop patients table
        $table_name = $wpdb->prefix . 'medx360_patients';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        error_log('MedX360 Migration: Rolled back patients table');
    }
}
