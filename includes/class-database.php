<?php
/**
 * Database management class for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Database {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor
    }
    
    /**
     * Create all database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Clinics table
        $clinics_table = $wpdb->prefix . 'medx360_clinics';
        $clinics_sql = "CREATE TABLE $clinics_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            address text,
            city varchar(100),
            state varchar(100),
            country varchar(100),
            postal_code varchar(20),
            phone varchar(20),
            email varchar(100),
            website varchar(255),
            logo_url varchar(500),
            status enum('active','inactive','pending') DEFAULT 'active',
            settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY status (status),
            KEY city (city),
            KEY state (state)
        ) $charset_collate;";
        
        // Hospitals table
        $hospitals_table = $wpdb->prefix . 'medx360_hospitals';
        $hospitals_sql = "CREATE TABLE $hospitals_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            clinic_id int(11) NOT NULL,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            address text,
            city varchar(100),
            state varchar(100),
            country varchar(100),
            postal_code varchar(20),
            phone varchar(20),
            email varchar(100),
            website varchar(255),
            logo_url varchar(500),
            status enum('active','inactive','pending') DEFAULT 'active',
            settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY clinic_id (clinic_id),
            KEY status (status),
            KEY city (city),
            KEY state (state)
        ) $charset_collate;";
        
        // Doctors table
        $doctors_table = $wpdb->prefix . 'medx360_doctors';
        $doctors_sql = "CREATE TABLE $doctors_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            clinic_id int(11) NOT NULL,
            hospital_id int(11),
            user_id bigint(20),
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20),
            specialization varchar(255),
            license_number varchar(100),
            experience_years int(3),
            education text,
            bio text,
            profile_image varchar(500),
            consultation_fee decimal(10,2),
            status enum('active','inactive','pending') DEFAULT 'active',
            settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY clinic_id (clinic_id),
            KEY hospital_id (hospital_id),
            KEY user_id (user_id),
            KEY status (status),
            KEY specialization (specialization)
        ) $charset_collate;";
        
        // Services table
        $services_table = $wpdb->prefix . 'medx360_services';
        $services_sql = "CREATE TABLE $services_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            clinic_id int(11) NOT NULL,
            hospital_id int(11),
            name varchar(255) NOT NULL,
            description text,
            duration_minutes int(4) DEFAULT 30,
            price decimal(10,2),
            category varchar(100),
            status enum('active','inactive') DEFAULT 'active',
            settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY clinic_id (clinic_id),
            KEY hospital_id (hospital_id),
            KEY status (status),
            KEY category (category)
        ) $charset_collate;";
        
        // Staff table
        $staff_table = $wpdb->prefix . 'medx360_staff';
        $staff_sql = "CREATE TABLE $staff_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            clinic_id int(11) NOT NULL,
            hospital_id int(11),
            user_id bigint(20),
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20),
            role varchar(100) NOT NULL,
            department varchar(100),
            status enum('active','inactive','pending') DEFAULT 'active',
            settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY clinic_id (clinic_id),
            KEY hospital_id (hospital_id),
            KEY user_id (user_id),
            KEY status (status),
            KEY role (role)
        ) $charset_collate;";
        
        // Bookings table
        $bookings_table = $wpdb->prefix . 'medx360_bookings';
        $bookings_sql = "CREATE TABLE $bookings_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            clinic_id int(11) NOT NULL,
            hospital_id int(11),
            doctor_id int(11),
            service_id int(11),
            patient_name varchar(255) NOT NULL,
            patient_email varchar(100) NOT NULL,
            patient_phone varchar(20),
            patient_dob date,
            patient_gender enum('male','female','other'),
            appointment_date date NOT NULL,
            appointment_time time NOT NULL,
            duration_minutes int(4) DEFAULT 30,
            status enum('pending','confirmed','cancelled','completed','no_show') DEFAULT 'pending',
            notes text,
            total_amount decimal(10,2),
            payment_status enum('pending','paid','refunded','failed') DEFAULT 'pending',
            payment_method varchar(50),
            payment_reference varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY clinic_id (clinic_id),
            KEY hospital_id (hospital_id),
            KEY doctor_id (doctor_id),
            KEY service_id (service_id),
            KEY appointment_date (appointment_date),
            KEY status (status),
            KEY payment_status (payment_status)
        ) $charset_collate;";
        
        // Consultations table
        $consultations_table = $wpdb->prefix . 'medx360_consultations';
        $consultations_sql = "CREATE TABLE $consultations_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_id int(11) NOT NULL,
            doctor_id int(11) NOT NULL,
            patient_id int(11),
            consultation_type enum('in_person','video','phone') DEFAULT 'in_person',
            diagnosis text,
            prescription text,
            notes text,
            follow_up_date date,
            status enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY doctor_id (doctor_id),
            KEY patient_id (patient_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Payments table
        $payments_table = $wpdb->prefix . 'medx360_payments';
        $payments_sql = "CREATE TABLE $payments_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_id int(11) NOT NULL,
            amount decimal(10,2) NOT NULL,
            currency varchar(3) DEFAULT 'USD',
            payment_method varchar(50) NOT NULL,
            payment_gateway varchar(50),
            transaction_id varchar(255),
            status enum('pending','completed','failed','refunded','cancelled') DEFAULT 'pending',
            gateway_response longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY status (status),
            KEY transaction_id (transaction_id)
        ) $charset_collate;";
        
        // Doctor schedules table
        $schedules_table = $wpdb->prefix . 'medx360_doctor_schedules';
        $schedules_sql = "CREATE TABLE $schedules_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            doctor_id int(11) NOT NULL,
            day_of_week tinyint(1) NOT NULL COMMENT '1=Monday, 7=Sunday',
            start_time time NOT NULL,
            end_time time NOT NULL,
            is_available tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY doctor_id (doctor_id),
            KEY day_of_week (day_of_week)
        ) $charset_collate;";
        
        // Doctor availability exceptions table
        $availability_table = $wpdb->prefix . 'medx360_doctor_availability';
        $availability_sql = "CREATE TABLE $availability_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            doctor_id int(11) NOT NULL,
            date date NOT NULL,
            start_time time,
            end_time time,
            is_available tinyint(1) DEFAULT 1,
            reason varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY doctor_id (doctor_id),
            KEY date (date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Use dbDelta which handles table creation/updates intelligently
        dbDelta($clinics_sql);
        dbDelta($hospitals_sql);
        dbDelta($doctors_sql);
        dbDelta($services_sql);
        dbDelta($staff_sql);
        dbDelta($bookings_sql);
        dbDelta($consultations_sql);
        dbDelta($payments_sql);
        dbDelta($schedules_sql);
        dbDelta($availability_sql);
        
        // Insert default data only if tables are empty
        self::insert_default_data();
        
        // Update database version
        update_option('medx360_db_version', MEDX360_VERSION);
    }
    
    /**
     * Insert default data
     */
    private static function insert_default_data() {
        global $wpdb;
        
        $clinics_table = $wpdb->prefix . 'medx360_clinics';
        
        // Check if default clinic exists
        $existing_clinic = $wpdb->get_var("SELECT id FROM $clinics_table LIMIT 1");
        
        if (!$existing_clinic) {
            $wpdb->insert(
                $clinics_table,
                array(
                    'name' => 'Default Clinic',
                    'slug' => 'default-clinic',
                    'description' => 'Default clinic for MedX360 system',
                    'status' => 'active',
                    'created_at' => current_time('mysql')
                )
            );
        }
    }
    
    /**
     * Get table name with prefix
     */
    public static function get_table_name($table) {
        global $wpdb;
        return $wpdb->prefix . 'medx360_' . $table;
    }
    
    /**
     * Drop all tables
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            'medx360_doctor_availability',
            'medx360_doctor_schedules',
            'medx360_payments',
            'medx360_consultations',
            'medx360_bookings',
            'medx360_staff',
            'medx360_services',
            'medx360_doctors',
            'medx360_hospitals',
            'medx360_clinics'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table");
        }
        
        // Remove options
        delete_option('medx360_version');
        delete_option('medx360_db_version');
        delete_option('medx360_setup_completed');
        delete_option('medx360_settings');
    }
    
    /**
     * Check if database needs update
     */
    public static function needs_update() {
        $current_version = get_option('medx360_db_version', '0.0.0');
        return version_compare($current_version, MEDX360_VERSION, '<');
    }
    
    /**
     * Update database if needed
     */
    public static function maybe_update() {
        if (self::needs_update()) {
            self::create_tables();
        }
    }
}
