<?php
/**
 * Migration 1.0.0 - Create Core Tables
 * Creates all the basic tables needed for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once MEDX360_PLUGIN_DIR . 'includes/database/Migration.php';

class Medx360_Migration_1_0_0 extends Medx360_Migration {
    
    public function up() {
        $this->create_patients_table();
        $this->create_appointments_table();
        $this->create_staff_table();
        $this->create_clinics_table();
        $this->create_services_table();
        $this->create_payments_table();
        $this->create_notifications_table();
        $this->create_roles_table();
        $this->create_permissions_table();
        $this->create_settings_table();
        
        $this->insert_initial_data();
    }
    
    public function down() {
        // Drop tables in reverse order to handle foreign keys
        $this->drop_table_if_exists($this->database->get_table('permissions'));
        $this->drop_table_if_exists($this->database->get_table('roles'));
        $this->drop_table_if_exists($this->database->get_table('notifications'));
        $this->drop_table_if_exists($this->database->get_table('payments'));
        $this->drop_table_if_exists($this->database->get_table('services'));
        $this->drop_table_if_exists($this->database->get_table('clinics'));
        $this->drop_table_if_exists($this->database->get_table('staff'));
        $this->drop_table_if_exists($this->database->get_table('appointments'));
        $this->drop_table_if_exists($this->database->get_table('patients'));
        $this->drop_table_if_exists($this->database->get_table('settings'));
    }
    
    private function create_patients_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('patients');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            date_of_birth date DEFAULT NULL,
            gender enum('male','female','other') DEFAULT NULL,
            address text DEFAULT NULL,
            emergency_contact_name varchar(100) DEFAULT NULL,
            emergency_contact_phone varchar(20) DEFAULT NULL,
            medical_history text DEFAULT NULL,
            allergies text DEFAULT NULL,
            insurance_provider varchar(100) DEFAULT NULL,
            insurance_number varchar(50) DEFAULT NULL,
            status enum('active','inactive','archived') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_appointments_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('appointments');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            patient_id bigint(20) NOT NULL,
            staff_id bigint(20) NOT NULL,
            appointment_date date NOT NULL,
            appointment_time time NOT NULL,
            duration int(11) DEFAULT 30,
            status enum('scheduled','confirmed','completed','cancelled','no_show') DEFAULT 'scheduled',
            appointment_type varchar(50) DEFAULT 'consultation',
            notes text DEFAULT NULL,
            cost decimal(10,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY patient_id (patient_id),
            KEY staff_id (staff_id),
            KEY appointment_date (appointment_date),
            KEY status (status)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_staff_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('staff');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            specialty varchar(100) DEFAULT NULL,
            license_number varchar(50) DEFAULT NULL,
            hire_date date DEFAULT NULL,
            salary decimal(10,2) DEFAULT NULL,
            status enum('active','inactive','terminated') DEFAULT 'active',
            working_hours text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_clinics_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('clinics');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            type varchar(100) NOT NULL,
            address text NOT NULL,
            phone varchar(20) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            website varchar(255) DEFAULT NULL,
            license_number varchar(50) DEFAULT NULL,
            status enum('active','inactive') DEFAULT 'active',
            established_date date DEFAULT NULL,
            services text DEFAULT NULL,
            operating_hours text DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY type (type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_services_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('services');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            category varchar(100) NOT NULL,
            duration int(11) DEFAULT 30,
            price decimal(10,2) DEFAULT 0.00,
            description text DEFAULT NULL,
            status enum('active','inactive') DEFAULT 'active',
            icon varchar(10) DEFAULT '🩺',
            staff_assigned text DEFAULT NULL,
            requirements text DEFAULT NULL,
            preparation_instructions text DEFAULT NULL,
            follow_up_instructions text DEFAULT NULL,
            notes text DEFAULT NULL,
            booking_count int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY category (category),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_payments_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('payments');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            patient_id bigint(20) NOT NULL,
            appointment_id bigint(20) DEFAULT NULL,
            amount decimal(10,2) NOT NULL,
            payment_method enum('cash','card','insurance','bank_transfer','other') DEFAULT 'cash',
            payment_status enum('pending','completed','failed','refunded') DEFAULT 'pending',
            transaction_id varchar(100) DEFAULT NULL,
            payment_date datetime DEFAULT CURRENT_TIMESTAMP,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY patient_id (patient_id),
            KEY appointment_id (appointment_id),
            KEY payment_status (payment_status),
            KEY payment_date (payment_date)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_notifications_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('notifications');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            type varchar(50) NOT NULL,
            title varchar(200) NOT NULL,
            message text NOT NULL,
            is_read tinyint(1) DEFAULT 0,
            priority enum('low','medium','high','urgent') DEFAULT 'medium',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY type (type),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_roles_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('roles');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            display_name varchar(100) NOT NULL,
            description text DEFAULT NULL,
            capabilities text DEFAULT NULL,
            is_premium tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_permissions_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('permissions');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            role_id bigint(20) NOT NULL,
            granted_by bigint(20) DEFAULT NULL,
            granted_at datetime DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY role_id (role_id)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function create_settings_table() {
        global $wpdb;
        
        $table_name = $this->database->get_table('settings');
        $charset_collate = $this->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value longtext DEFAULT NULL,
            setting_type enum('string','number','boolean','json','array') DEFAULT 'string',
            is_premium tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        $this->execute_sql($sql);
    }
    
    private function insert_initial_data() {
        $this->insert_default_roles();
        $this->insert_default_settings();
    }
    
    private function insert_default_roles() {
        global $wpdb;
        
        $table_name = $this->database->get_table('roles');
        
        $default_roles = array(
            array(
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full access to all features',
                'capabilities' => json_encode(array('manage_all', 'view_reports', 'manage_staff', 'manage_patients')),
                'is_premium' => 0
            ),
            array(
                'name' => 'doctor',
                'display_name' => 'Doctor',
                'description' => 'Medical staff with patient access',
                'capabilities' => json_encode(array('view_patients', 'manage_appointments', 'view_reports')),
                'is_premium' => 0
            ),
            array(
                'name' => 'receptionist',
                'display_name' => 'Receptionist',
                'description' => 'Front desk staff',
                'capabilities' => json_encode(array('view_patients', 'manage_appointments', 'manage_payments')),
                'is_premium' => 0
            ),
            array(
                'name' => 'nurse',
                'display_name' => 'Nurse',
                'description' => 'Nursing staff',
                'capabilities' => json_encode(array('view_patients', 'manage_appointments')),
                'is_premium' => 0
            )
        );
        
        parent::insert_default_data($table_name, $default_roles);
    }
    
    private function insert_default_settings() {
        global $wpdb;
        
        $table_name = $this->database->get_table('settings');
        
        $default_settings = array(
            array('setting_key' => 'clinic_name', 'setting_value' => 'Medx360 Clinic', 'setting_type' => 'string'),
            array('setting_key' => 'clinic_address', 'setting_value' => '', 'setting_type' => 'string'),
            array('setting_key' => 'clinic_phone', 'setting_value' => '', 'setting_type' => 'string'),
            array('setting_key' => 'clinic_email', 'setting_value' => '', 'setting_type' => 'string'),
            array('setting_key' => 'default_appointment_duration', 'setting_value' => '30', 'setting_type' => 'number'),
            array('setting_key' => 'working_hours', 'setting_value' => json_encode(array('monday' => '09:00-17:00', 'tuesday' => '09:00-17:00', 'wednesday' => '09:00-17:00', 'thursday' => '09:00-17:00', 'friday' => '09:00-17:00')), 'setting_type' => 'json'),
            array('setting_key' => 'currency', 'setting_value' => 'USD', 'setting_type' => 'string'),
            array('setting_key' => 'timezone', 'setting_value' => 'UTC', 'setting_type' => 'string'),
            array('setting_key' => 'email_notifications', 'setting_value' => '1', 'setting_type' => 'boolean'),
            array('setting_key' => 'sms_notifications', 'setting_value' => '0', 'setting_type' => 'boolean'),
            array('setting_key' => 'premium_active', 'setting_value' => '0', 'setting_type' => 'boolean', 'is_premium' => 1),
            array('setting_key' => 'multi_location_enabled', 'setting_value' => '0', 'setting_type' => 'boolean', 'is_premium' => 1),
            array('setting_key' => 'advanced_reporting_enabled', 'setting_value' => '0', 'setting_type' => 'boolean', 'is_premium' => 1)
        );
        
        parent::insert_default_data($table_name, $default_settings);
    }
    
    private function drop_table_if_exists($table_name) {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}
