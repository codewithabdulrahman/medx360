<?php
/**
 * Migration 1.1.0 - Add Indexes and Constraints
 * Adds performance indexes and foreign key constraints
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once MEDX360_PLUGIN_DIR . 'includes/database/Migration.php';

class Medx360_Migration_1_1_0 extends Medx360_Migration {
    
    public function up() {
        $this->add_foreign_key_constraints();
        $this->add_performance_indexes();
    }
    
    public function down() {
        $this->remove_foreign_key_constraints();
        $this->remove_performance_indexes();
    }
    
    private function add_foreign_key_constraints() {
        global $wpdb;
        
        // Add foreign key constraints
        $constraints = array(
            // Appointments table
            array(
                'table' => $this->database->get_table('appointments'),
                'constraint' => 'fk_appointments_patient',
                'column' => 'patient_id',
                'reference_table' => $this->database->get_table('patients'),
                'reference_column' => 'id',
                'on_delete' => 'CASCADE'
            ),
            array(
                'table' => $this->database->get_table('appointments'),
                'constraint' => 'fk_appointments_staff',
                'column' => 'staff_id',
                'reference_table' => $this->database->get_table('staff'),
                'reference_column' => 'id',
                'on_delete' => 'CASCADE'
            ),
            
            // Payments table
            array(
                'table' => $this->database->get_table('payments'),
                'constraint' => 'fk_payments_patient',
                'column' => 'patient_id',
                'reference_table' => $this->database->get_table('patients'),
                'reference_column' => 'id',
                'on_delete' => 'CASCADE'
            ),
            array(
                'table' => $this->database->get_table('payments'),
                'constraint' => 'fk_payments_appointment',
                'column' => 'appointment_id',
                'reference_table' => $this->database->get_table('appointments'),
                'reference_column' => 'id',
                'on_delete' => 'SET NULL'
            ),
            
            // Notifications table
            array(
                'table' => $this->database->get_table('notifications'),
                'constraint' => 'fk_notifications_user',
                'column' => 'user_id',
                'reference_table' => $wpdb->prefix . 'users',
                'reference_column' => 'ID',
                'on_delete' => 'CASCADE'
            ),
            
            // Staff table
            array(
                'table' => $this->database->get_table('staff'),
                'constraint' => 'fk_staff_user',
                'column' => 'user_id',
                'reference_table' => $wpdb->prefix . 'users',
                'reference_column' => 'ID',
                'on_delete' => 'SET NULL'
            ),
            
            // Permissions table
            array(
                'table' => $this->database->get_table('permissions'),
                'constraint' => 'fk_permissions_user',
                'column' => 'user_id',
                'reference_table' => $wpdb->prefix . 'users',
                'reference_column' => 'ID',
                'on_delete' => 'CASCADE'
            ),
            array(
                'table' => $this->database->get_table('permissions'),
                'constraint' => 'fk_permissions_role',
                'column' => 'role_id',
                'reference_table' => $this->database->get_table('roles'),
                'reference_column' => 'id',
                'on_delete' => 'CASCADE'
            )
        );
        
        foreach ($constraints as $constraint) {
            $this->add_foreign_key_constraint($constraint);
        }
    }
    
    private function add_foreign_key_constraint($constraint) {
        global $wpdb;
        
        // Check if constraint already exists
        $existing_constraints = $wpdb->get_results(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = '{$constraint['table']}' 
             AND CONSTRAINT_NAME = '{$constraint['constraint']}'"
        );
        
        if (empty($existing_constraints)) {
            $sql = "ALTER TABLE {$constraint['table']} 
                    ADD CONSTRAINT {$constraint['constraint']} 
                    FOREIGN KEY ({$constraint['column']}) 
                    REFERENCES {$constraint['reference_table']}({$constraint['reference_column']}) 
                    ON DELETE {$constraint['on_delete']}";
            
            $wpdb->query($sql);
        }
    }
    
    private function add_performance_indexes() {
        // Add composite indexes for better query performance
        $indexes = array(
            // Appointments table
            array(
                'table' => $this->database->get_table('appointments'),
                'name' => 'idx_appointments_date_status',
                'columns' => 'appointment_date, status'
            ),
            array(
                'table' => $this->database->get_table('appointments'),
                'name' => 'idx_appointments_staff_date',
                'columns' => 'staff_id, appointment_date'
            ),
            
            // Patients table
            array(
                'table' => $this->database->get_table('patients'),
                'name' => 'idx_patients_name_status',
                'columns' => 'first_name, last_name, status'
            ),
            
            // Payments table
            array(
                'table' => $this->database->get_table('payments'),
                'name' => 'idx_payments_date_status',
                'columns' => 'payment_date, payment_status'
            ),
            
            // Services table
            array(
                'table' => $this->database->get_table('services'),
                'name' => 'idx_services_category_status',
                'columns' => 'category, status'
            ),
            
            // Clinics table
            array(
                'table' => $this->database->get_table('clinics'),
                'name' => 'idx_clinics_type_status',
                'columns' => 'type, status'
            ),
            
            // Staff table
            array(
                'table' => $this->database->get_table('staff'),
                'name' => 'idx_staff_specialty_status',
                'columns' => 'specialty, status'
            )
        );
        
        foreach ($indexes as $index) {
            $this->add_index_if_not_exists($index['table'], $index['name'], $index['columns']);
        }
    }
    
    private function remove_foreign_key_constraints() {
        global $wpdb;
        
        $constraints = array(
            'fk_appointments_patient',
            'fk_appointments_staff',
            'fk_payments_patient',
            'fk_payments_appointment',
            'fk_notifications_user',
            'fk_staff_user',
            'fk_permissions_user',
            'fk_permissions_role'
        );
        
        foreach ($constraints as $constraint) {
            $this->remove_foreign_key_constraint($constraint);
        }
    }
    
    private function remove_foreign_key_constraint($constraint_name) {
        global $wpdb;
        
        // Get all tables that might have this constraint
        $tables = array(
            $this->database->get_table('appointments'),
            $this->database->get_table('payments'),
            $this->database->get_table('notifications'),
            $this->database->get_table('staff'),
            $this->database->get_table('permissions')
        );
        
        foreach ($tables as $table) {
            $existing_constraints = $wpdb->get_results(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = '$table' 
                 AND CONSTRAINT_NAME = '$constraint_name'"
            );
            
            if (!empty($existing_constraints)) {
                $wpdb->query("ALTER TABLE $table DROP FOREIGN KEY $constraint_name");
                break;
            }
        }
    }
    
    private function remove_performance_indexes() {
        $indexes = array(
            'idx_appointments_date_status',
            'idx_appointments_staff_date',
            'idx_patients_name_status',
            'idx_payments_date_status',
            'idx_services_category_status',
            'idx_clinics_type_status',
            'idx_staff_specialty_status'
        );
        
        $tables = array(
            $this->database->get_table('appointments'),
            $this->database->get_table('patients'),
            $this->database->get_table('payments'),
            $this->database->get_table('services'),
            $this->database->get_table('clinics'),
            $this->database->get_table('staff')
        );
        
        foreach ($indexes as $index) {
            foreach ($tables as $table) {
                $this->drop_index_if_exists($table, $index);
            }
        }
    }
}
