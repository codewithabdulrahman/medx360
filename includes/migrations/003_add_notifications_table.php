<?php
/**
 * Migration: Add Notifications Table
 * Version: 1.2.0
 * Created: 2024-01-15 10:00:00
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Migration_AddNotificationsTable {
    
    /**
     * Run the migration
     */
    public function up() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create notifications table
        $table_name = $wpdb->prefix . 'medx360_notifications';
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            type enum('email','sms','push','in_app') NOT NULL,
            recipient_type enum('patient','doctor','staff','admin') NOT NULL,
            recipient_id int(11),
            recipient_email varchar(100),
            recipient_phone varchar(20),
            subject varchar(255),
            message text NOT NULL,
            template_id varchar(100),
            booking_id int(11),
            status enum('pending','sent','failed','cancelled') DEFAULT 'pending',
            scheduled_at datetime,
            sent_at datetime,
            error_message text,
            retry_count int(3) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY recipient_type (recipient_type),
            KEY recipient_id (recipient_id),
            KEY booking_id (booking_id),
            KEY status (status),
            KEY scheduled_at (scheduled_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add notification settings to clinics table
        $clinics_table = $wpdb->prefix . 'medx360_clinics';
        $wpdb->query("ALTER TABLE $clinics_table ADD COLUMN notification_settings longtext AFTER settings");
        
        error_log('MedX360 Migration: Added notifications table and notification settings');
    }
    
    /**
     * Rollback the migration
     */
    public function down() {
        global $wpdb;
        
        // Remove notification settings from clinics table
        $clinics_table = $wpdb->prefix . 'medx360_clinics';
        $wpdb->query("ALTER TABLE $clinics_table DROP COLUMN notification_settings");
        
        // Drop notifications table
        $table_name = $wpdb->prefix . 'medx360_notifications';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        error_log('MedX360 Migration: Rolled back notifications table');
    }
}
