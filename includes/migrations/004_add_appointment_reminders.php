<?php
/**
 * Migration: Add Appointment Reminders
 * Version: 1.3.0
 * Created: 2024-01-15 10:00:00
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Migration_AddAppointmentReminders {
    
    /**
     * Run the migration
     */
    public function up() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create appointment reminders table
        $table_name = $wpdb->prefix . 'medx360_appointment_reminders';
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_id int(11) NOT NULL,
            reminder_type enum('email','sms','push') NOT NULL,
            reminder_time datetime NOT NULL,
            message text,
            status enum('pending','sent','failed','cancelled') DEFAULT 'pending',
            sent_at datetime,
            error_message text,
            retry_count int(3) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY reminder_time (reminder_time),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add reminder settings to bookings table
        $bookings_table = $wpdb->prefix . 'medx360_bookings';
        $wpdb->query("ALTER TABLE $bookings_table ADD COLUMN reminder_sent tinyint(1) DEFAULT 0 AFTER payment_reference");
        $wpdb->query("ALTER TABLE $bookings_table ADD COLUMN reminder_settings longtext AFTER reminder_sent");
        
        error_log('MedX360 Migration: Added appointment reminders table and settings');
    }
    
    /**
     * Rollback the migration
     */
    public function down() {
        global $wpdb;
        
        // Remove reminder settings from bookings table
        $bookings_table = $wpdb->prefix . 'medx360_bookings';
        $wpdb->query("ALTER TABLE $bookings_table DROP COLUMN reminder_sent");
        $wpdb->query("ALTER TABLE $bookings_table DROP COLUMN reminder_settings");
        
        // Drop appointment reminders table
        $table_name = $wpdb->prefix . 'medx360_appointment_reminders';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        error_log('MedX360 Migration: Rolled back appointment reminders');
    }
}
