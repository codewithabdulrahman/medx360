<?php
/**
 * Database Management Class
 * Handles table creation, updates, and migrations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_Database {
    
    private $version = '1.0.0';
    private $tables = array();
    
    public function __construct() {
        $this->define_tables();
        add_action('init', array($this, 'init'));
        register_activation_hook(MEDX360_PLUGIN_FILE, array($this, 'create_tables'));
        register_deactivation_hook(MEDX360_PLUGIN_FILE, array($this, 'cleanup'));
    }
    
    public function init() {
        $this->check_version();
    }
    
    /**
     * Define all database tables
     */
    private function define_tables() {
        global $wpdb;
        
        $this->tables = array(
            'patients' => $wpdb->prefix . 'medx360_patients',
            'appointments' => $wpdb->prefix . 'medx360_appointments',
            'staff' => $wpdb->prefix . 'medx360_staff',
            'payments' => $wpdb->prefix . 'medx360_payments',
            'notifications' => $wpdb->prefix . 'medx360_notifications',
            'roles' => $wpdb->prefix . 'medx360_roles',
            'permissions' => $wpdb->prefix . 'medx360_permissions',
            'settings' => $wpdb->prefix . 'medx360_settings',
            'locations' => $wpdb->prefix . 'medx360_locations', // Premium
            'resources' => $wpdb->prefix . 'medx360_resources', // Premium
            'integrations' => $wpdb->prefix . 'medx360_integrations', // Premium
            'reports' => $wpdb->prefix . 'medx360_reports', // Premium
        );
    }
    
    /**
     * Get table name
     */
    public function get_table($table_name) {
        return isset($this->tables[$table_name]) ? $this->tables[$table_name] : null;
    }
    
    /**
     * Create all tables
     */
    public function create_tables() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $this->create_patients_table();
        $this->create_appointments_table();
        $this->create_staff_table();
        $this->create_payments_table();
        $this->create_notifications_table();
        $this->create_roles_table();
        $this->create_permissions_table();
        $this->create_settings_table();
        
        // Premium tables (only create if premium is active)
        if ($this->is_premium_active()) {
            $this->create_locations_table();
            $this->create_resources_table();
            $this->create_integrations_table();
            $this->create_reports_table();
        }
        
        $this->insert_default_data();
        update_option('medx360_db_version', $this->version);
    }
    
    /**
     * Create patients table
     */
    private function create_patients_table() {
        global $wpdb;
        
        $table_name = $this->get_table('patients');
        
        $charset_collate = $wpdb->get_charset_collate();
        
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
        
        dbDelta($sql);
    }
    
    /**
     * Create appointments table
     */
    private function create_appointments_table() {
        global $wpdb;
        
        $table_name = $this->get_table('appointments');
        
        $charset_collate = $wpdb->get_charset_collate();
        
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
            KEY status (status),
            FOREIGN KEY (patient_id) REFERENCES {$this->get_table('patients')}(id) ON DELETE CASCADE,
            FOREIGN KEY (staff_id) REFERENCES {$this->get_table('staff')}(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create staff table
     */
    private function create_staff_table() {
        global $wpdb;
        
        $table_name = $this->get_table('staff');
        
        $charset_collate = $wpdb->get_charset_collate();
        
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
            KEY status (status),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE SET NULL
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create payments table
     */
    private function create_payments_table() {
        global $wpdb;
        
        $table_name = $this->get_table('payments');
        
        $charset_collate = $wpdb->get_charset_collate();
        
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
            KEY payment_date (payment_date),
            FOREIGN KEY (patient_id) REFERENCES {$this->get_table('patients')}(id) ON DELETE CASCADE,
            FOREIGN KEY (appointment_id) REFERENCES {$this->get_table('appointments')}(id) ON DELETE SET NULL
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create notifications table
     */
    private function create_notifications_table() {
        global $wpdb;
        
        $table_name = $this->get_table('notifications');
        
        $charset_collate = $wpdb->get_charset_collate();
        
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
            KEY created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create roles table
     */
    private function create_roles_table() {
        global $wpdb;
        
        $table_name = $this->get_table('roles');
        
        $charset_collate = $wpdb->get_charset_collate();
        
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
        
        dbDelta($sql);
    }
    
    /**
     * Create permissions table
     */
    private function create_permissions_table() {
        global $wpdb;
        
        $table_name = $this->get_table('permissions');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            role_id bigint(20) NOT NULL,
            granted_by bigint(20) DEFAULT NULL,
            granted_at datetime DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY role_id (role_id),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES {$this->get_table('roles')}(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create settings table
     */
    private function create_settings_table() {
        global $wpdb;
        
        $table_name = $this->get_table('settings');
        
        $charset_collate = $wpdb->get_charset_collate();
        
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
        
        dbDelta($sql);
    }
    
    /**
     * Create locations table (Premium)
     */
    private function create_locations_table() {
        global $wpdb;
        
        $table_name = $this->get_table('locations');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            address text NOT NULL,
            phone varchar(20) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            manager_id bigint(20) DEFAULT NULL,
            timezone varchar(50) DEFAULT 'UTC',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY manager_id (manager_id),
            KEY is_active (is_active),
            FOREIGN KEY (manager_id) REFERENCES {$this->get_table('staff')}(id) ON DELETE SET NULL
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create resources table (Premium)
     */
    private function create_resources_table() {
        global $wpdb;
        
        $table_name = $this->get_table('resources');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            type enum('room','equipment','vehicle','other') DEFAULT 'room',
            location_id bigint(20) DEFAULT NULL,
            capacity int(11) DEFAULT 1,
            hourly_rate decimal(10,2) DEFAULT 0.00,
            is_available tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY location_id (location_id),
            KEY type (type),
            KEY is_available (is_available),
            FOREIGN KEY (location_id) REFERENCES {$this->get_table('locations')}(id) ON DELETE SET NULL
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create integrations table (Premium)
     */
    private function create_integrations_table() {
        global $wpdb;
        
        $table_name = $this->get_table('integrations');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            type enum('ehr','payment','sms','email','calendar','other') DEFAULT 'other',
            api_key varchar(255) DEFAULT NULL,
            api_secret varchar(255) DEFAULT NULL,
            webhook_url varchar(255) DEFAULT NULL,
            settings text DEFAULT NULL,
            is_active tinyint(1) DEFAULT 0,
            last_sync datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name),
            KEY type (type),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create reports table (Premium)
     */
    private function create_reports_table() {
        global $wpdb;
        
        $table_name = $this->get_table('reports');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            type enum('appointment','financial','patient','staff','custom') DEFAULT 'custom',
            parameters text DEFAULT NULL,
            generated_by bigint(20) DEFAULT NULL,
            file_path varchar(500) DEFAULT NULL,
            status enum('pending','generating','completed','failed') DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY type (type),
            KEY generated_by (generated_by),
            KEY status (status),
            FOREIGN KEY (generated_by) REFERENCES {$wpdb->prefix}users(ID) ON DELETE SET NULL
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Insert default data
     */
    private function insert_default_data() {
        $this->insert_default_roles();
        $this->insert_default_settings();
    }
    
    /**
     * Insert default roles
     */
    private function insert_default_roles() {
        global $wpdb;
        
        $table_name = $this->get_table('roles');
        
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
        
        foreach ($default_roles as $role) {
            $wpdb->replace($table_name, $role);
        }
    }
    
    /**
     * Insert default settings
     */
    private function insert_default_settings() {
        global $wpdb;
        
        $table_name = $this->get_table('settings');
        
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
        
        foreach ($default_settings as $setting) {
            $wpdb->replace($table_name, $setting);
        }
    }
    
    /**
     * Check if premium features are active
     */
    private function is_premium_active() {
        return get_option('medx360_premium_active', false);
    }
    
    /**
     * Check database version and update if needed
     */
    private function check_version() {
        $installed_version = get_option('medx360_db_version', '0.0.0');
        
        if (version_compare($installed_version, $this->version, '<')) {
            $this->create_tables();
        }
    }
    
    /**
     * Cleanup on deactivation
     */
    public function cleanup() {
        // Optionally remove tables on deactivation
        // Uncomment the following lines if you want to remove tables on deactivation
        
        /*
        global $wpdb;
        
        foreach ($this->tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        delete_option('medx360_db_version');
        */
    }
    
    /**
     * Get all table names
     */
    public function get_all_tables() {
        return $this->tables;
    }
    
    /**
     * Check if table exists
     */
    public function table_exists($table_name) {
        global $wpdb;
        $table = $this->get_table($table_name);
        
        if (!$table) {
            return false;
        }
        
        return $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
    }
}
