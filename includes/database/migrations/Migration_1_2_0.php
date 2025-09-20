<?php
/**
 * Migration 1.2.0 - Add Audit Logging
 * Adds audit logging tables for tracking changes
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once MEDX360_PLUGIN_DIR . 'includes/database/Migration.php';

class Medx360_Migration_1_2_0 extends Medx360_Migration {
    
    public function up() {
        $this->create_audit_logs_table();
        $this->create_audit_log_details_table();
        $this->add_audit_triggers();
    }
    
    public function down() {
        $this->remove_audit_triggers();
        $this->drop_table_if_exists($this->database->get_table('audit_log_details'));
        $this->drop_table_if_exists($this->database->get_table('audit_logs'));
    }
    
    private function create_audit_logs_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'medx360_audit_logs';
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            table_name varchar(100) NOT NULL,
            record_id bigint(20) NOT NULL,
            action enum('INSERT','UPDATE','DELETE') NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY table_name (table_name),
            KEY record_id (record_id),
            KEY action (action),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_audit_log_details_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'medx360_audit_log_details';
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            audit_log_id bigint(20) NOT NULL,
            field_name varchar(100) NOT NULL,
            old_value text DEFAULT NULL,
            new_value text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY audit_log_id (audit_log_id),
            KEY field_name (field_name),
            FOREIGN KEY (audit_log_id) REFERENCES {$wpdb->prefix}medx360_audit_logs(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function add_audit_triggers() {
        // Add triggers for automatic audit logging
        $this->add_audit_trigger_for_table('patients');
        $this->add_audit_trigger_for_table('appointments');
        $this->add_audit_trigger_for_table('staff');
        $this->add_audit_trigger_for_table('clinics');
        $this->add_audit_trigger_for_table('services');
        $this->add_audit_trigger_for_table('payments');
    }
    
    private function add_audit_trigger_for_table($table_name) {
        global $wpdb;
        
        $full_table_name = $this->database->get_table($table_name);
        $audit_table = $wpdb->prefix . 'medx360_audit_logs';
        $audit_details_table = $wpdb->prefix . 'medx360_audit_log_details';
        
        // Insert trigger
        $insert_trigger = "
        CREATE TRIGGER {$table_name}_audit_insert
        AFTER INSERT ON $full_table_name
        FOR EACH ROW
        BEGIN
            INSERT INTO $audit_table (table_name, record_id, action, user_id, ip_address, user_agent)
            VALUES ('$table_name', NEW.id, 'INSERT', 
                    COALESCE(@medx360_user_id, 0), 
                    COALESCE(@medx360_ip_address, ''), 
                    COALESCE(@medx360_user_agent, ''));
        END";
        
        $this->execute_sql($insert_trigger);
        
        // Update trigger
        $update_trigger = "
        CREATE TRIGGER {$table_name}_audit_update
        AFTER UPDATE ON $full_table_name
        FOR EACH ROW
        BEGIN
            DECLARE audit_id BIGINT;
            
            INSERT INTO $audit_table (table_name, record_id, action, user_id, ip_address, user_agent)
            VALUES ('$table_name', NEW.id, 'UPDATE', 
                    COALESCE(@medx360_user_id, 0), 
                    COALESCE(@medx360_ip_address, ''), 
                    COALESCE(@medx360_user_agent, ''));
            
            SET audit_id = LAST_INSERT_ID();
            
            -- Log field changes
            IF OLD.first_name != NEW.first_name THEN
                INSERT INTO $audit_details_table (audit_log_id, field_name, old_value, new_value)
                VALUES (audit_id, 'first_name', OLD.first_name, NEW.first_name);
            END IF;
            
            IF OLD.last_name != NEW.last_name THEN
                INSERT INTO $audit_details_table (audit_log_id, field_name, old_value, new_value)
                VALUES (audit_id, 'last_name', OLD.last_name, NEW.last_name);
            END IF;
            
            IF OLD.email != NEW.email THEN
                INSERT INTO $audit_details_table (audit_log_id, field_name, old_value, new_value)
                VALUES (audit_id, 'email', OLD.email, NEW.email);
            END IF;
            
            IF OLD.status != NEW.status THEN
                INSERT INTO $audit_details_table (audit_log_id, field_name, old_value, new_value)
                VALUES (audit_id, 'status', OLD.status, NEW.status);
            END IF;
        END";
        
        $this->execute_sql($update_trigger);
        
        // Delete trigger
        $delete_trigger = "
        CREATE TRIGGER {$table_name}_audit_delete
        AFTER DELETE ON $full_table_name
        FOR EACH ROW
        BEGIN
            INSERT INTO $audit_table (table_name, record_id, action, user_id, ip_address, user_agent)
            VALUES ('$table_name', OLD.id, 'DELETE', 
                    COALESCE(@medx360_user_id, 0), 
                    COALESCE(@medx360_ip_address, ''), 
                    COALESCE(@medx360_user_agent, ''));
        END";
        
        $this->execute_sql($delete_trigger);
    }
    
    private function remove_audit_triggers() {
        $tables = array('patients', 'appointments', 'staff', 'clinics', 'services', 'payments');
        
        foreach ($tables as $table) {
            $this->execute_sql("DROP TRIGGER IF EXISTS {$table}_audit_insert");
            $this->execute_sql("DROP TRIGGER IF EXISTS {$table}_audit_update");
            $this->execute_sql("DROP TRIGGER IF EXISTS {$table}_audit_delete");
        }
    }
    
    private function drop_table_if_exists($table_name) {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}
