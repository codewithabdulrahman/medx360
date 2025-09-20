<?php

namespace MedX360\Infrastructure\Database;

class DatabaseManager
{
    private $db;
    private $charset_collate;
    private $table_prefix;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->charset_collate = $this->db->get_charset_collate();
        $this->table_prefix = $this->db->prefix . 'medx360_';
    }

    /**
     * Create all custom tables
     */
    public function createTables()
    {
        $this->createPatientsTable();
        $this->createProvidersTable();
        $this->createServicesTable();
        $this->createLocationsTable();
        $this->createAppointmentsTable();
        $this->createClinicalNotesTable();
        $this->createPrescriptionsTable();
        $this->createRoomsTable();
        $this->createAppointmentServicesTable();
        $this->createProviderServicesTable();
        $this->createProviderLocationsTable();
    }

    /**
     * Drop all custom tables
     */
    public function dropTables()
    {
        $tables = [
            'appointment_services',
            'provider_services', 
            'provider_locations',
            'appointments',
            'clinical_notes',
            'prescriptions',
            'rooms',
            'patients',
            'providers',
            'services',
            'locations'
        ];

        foreach ($tables as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$this->table_prefix}{$table}");
        }
    }

    /**
     * Create patients table
     */
    private function createPatientsTable()
    {
        $table_name = $this->table_prefix . 'patients';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20) DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            date_of_birth date DEFAULT NULL,
            gender enum('male','female','other') DEFAULT NULL,
            address text DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(100) DEFAULT NULL,
            postal_code varchar(20) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            emergency_contact_name varchar(200) DEFAULT NULL,
            emergency_contact_phone varchar(20) DEFAULT NULL,
            insurance_provider varchar(200) DEFAULT NULL,
            insurance_number varchar(100) DEFAULT NULL,
            medical_history text DEFAULT NULL,
            allergies text DEFAULT NULL,
            medications text DEFAULT NULL,
            status enum('active','inactive','deleted') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY wp_user_id (wp_user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create providers table
     */
    private function createProvidersTable()
    {
        $table_name = $this->table_prefix . 'providers';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20) DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            title varchar(100) DEFAULT NULL,
            specialization varchar(200) DEFAULT NULL,
            license_number varchar(100) DEFAULT NULL,
            bio text DEFAULT NULL,
            profile_image varchar(500) DEFAULT NULL,
            working_hours text DEFAULT NULL,
            status enum('active','inactive','deleted') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY wp_user_id (wp_user_id),
            KEY status (status),
            KEY specialization (specialization)
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create services table
     */
    private function createServicesTable()
    {
        $table_name = $this->table_prefix . 'services';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            description text DEFAULT NULL,
            duration int(11) NOT NULL DEFAULT 30,
            price decimal(10,2) DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            color varchar(7) DEFAULT '#007cba',
            status enum('active','inactive','deleted') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY category (category),
            KEY duration (duration)
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create locations table
     */
    private function createLocationsTable()
    {
        $table_name = $this->table_prefix . 'locations';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            address text DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(100) DEFAULT NULL,
            postal_code varchar(20) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            latitude decimal(10,8) DEFAULT NULL,
            longitude decimal(11,8) DEFAULT NULL,
            status enum('active','inactive','deleted') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY city (city),
            KEY state (state)
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create appointments table
     */
    private function createAppointmentsTable()
    {
        $table_name = $this->table_prefix . 'appointments';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            patient_id bigint(20) NOT NULL,
            provider_id bigint(20) NOT NULL,
            location_id bigint(20) DEFAULT NULL,
            room_id bigint(20) DEFAULT NULL,
            appointment_date date NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            duration int(11) NOT NULL DEFAULT 30,
            status enum('scheduled','confirmed','in_progress','completed','cancelled','no_show') DEFAULT 'scheduled',
            notes text DEFAULT NULL,
            internal_notes text DEFAULT NULL,
            reminder_sent tinyint(1) DEFAULT 0,
            confirmation_sent tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY patient_id (patient_id),
            KEY provider_id (provider_id),
            KEY location_id (location_id),
            KEY room_id (room_id),
            KEY appointment_date (appointment_date),
            KEY status (status),
            KEY start_time (start_time),
            FOREIGN KEY (patient_id) REFERENCES {$this->table_prefix}patients(id) ON DELETE CASCADE,
            FOREIGN KEY (provider_id) REFERENCES {$this->table_prefix}providers(id) ON DELETE CASCADE,
            FOREIGN KEY (location_id) REFERENCES {$this->table_prefix}locations(id) ON DELETE SET NULL,
            FOREIGN KEY (room_id) REFERENCES {$this->table_prefix}rooms(id) ON DELETE SET NULL
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create clinical notes table
     */
    private function createClinicalNotesTable()
    {
        $table_name = $this->table_prefix . 'clinical_notes';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            appointment_id bigint(20) NOT NULL,
            patient_id bigint(20) NOT NULL,
            provider_id bigint(20) NOT NULL,
            note_type enum('consultation','follow_up','treatment','diagnosis','other') DEFAULT 'consultation',
            chief_complaint text DEFAULT NULL,
            history_of_present_illness text DEFAULT NULL,
            physical_examination text DEFAULT NULL,
            assessment text DEFAULT NULL,
            plan text DEFAULT NULL,
            vital_signs text DEFAULT NULL,
            medications_prescribed text DEFAULT NULL,
            follow_up_instructions text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY appointment_id (appointment_id),
            KEY patient_id (patient_id),
            KEY provider_id (provider_id),
            KEY note_type (note_type),
            KEY created_at (created_at),
            FOREIGN KEY (appointment_id) REFERENCES {$this->table_prefix}appointments(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES {$this->table_prefix}patients(id) ON DELETE CASCADE,
            FOREIGN KEY (provider_id) REFERENCES {$this->table_prefix}providers(id) ON DELETE CASCADE
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create prescriptions table
     */
    private function createPrescriptionsTable()
    {
        $table_name = $this->table_prefix . 'prescriptions';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            appointment_id bigint(20) NOT NULL,
            patient_id bigint(20) NOT NULL,
            provider_id bigint(20) NOT NULL,
            medication_name varchar(200) NOT NULL,
            dosage varchar(100) DEFAULT NULL,
            frequency varchar(100) DEFAULT NULL,
            duration varchar(100) DEFAULT NULL,
            instructions text DEFAULT NULL,
            quantity int(11) DEFAULT NULL,
            refills int(11) DEFAULT 0,
            status enum('active','completed','cancelled') DEFAULT 'active',
            prescribed_date date NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY appointment_id (appointment_id),
            KEY patient_id (patient_id),
            KEY provider_id (provider_id),
            KEY status (status),
            KEY prescribed_date (prescribed_date),
            FOREIGN KEY (appointment_id) REFERENCES {$this->table_prefix}appointments(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES {$this->table_prefix}patients(id) ON DELETE CASCADE,
            FOREIGN KEY (provider_id) REFERENCES {$this->table_prefix}providers(id) ON DELETE CASCADE
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create rooms table
     */
    private function createRoomsTable()
    {
        $table_name = $this->table_prefix . 'rooms';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            location_id bigint(20) NOT NULL,
            name varchar(200) NOT NULL,
            room_number varchar(50) DEFAULT NULL,
            capacity int(11) DEFAULT 1,
            equipment text DEFAULT NULL,
            status enum('active','inactive','maintenance','deleted') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY location_id (location_id),
            KEY status (status),
            KEY room_number (room_number),
            FOREIGN KEY (location_id) REFERENCES {$this->table_prefix}locations(id) ON DELETE CASCADE
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create appointment services junction table
     */
    private function createAppointmentServicesTable()
    {
        $table_name = $this->table_prefix . 'appointment_services';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            appointment_id bigint(20) NOT NULL,
            service_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY appointment_service (appointment_id, service_id),
            KEY appointment_id (appointment_id),
            KEY service_id (service_id),
            FOREIGN KEY (appointment_id) REFERENCES {$this->table_prefix}appointments(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES {$this->table_prefix}services(id) ON DELETE CASCADE
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create provider services junction table
     */
    private function createProviderServicesTable()
    {
        $table_name = $this->table_prefix . 'provider_services';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            provider_id bigint(20) NOT NULL,
            service_id bigint(20) NOT NULL,
            price decimal(10,2) DEFAULT NULL,
            duration int(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY provider_service (provider_id, service_id),
            KEY provider_id (provider_id),
            KEY service_id (service_id),
            FOREIGN KEY (provider_id) REFERENCES {$this->table_prefix}providers(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES {$this->table_prefix}services(id) ON DELETE CASCADE
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create provider locations junction table
     */
    private function createProviderLocationsTable()
    {
        $table_name = $this->table_prefix . 'provider_locations';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            provider_id bigint(20) NOT NULL,
            location_id bigint(20) NOT NULL,
            working_hours text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY provider_location (provider_id, location_id),
            KEY provider_id (provider_id),
            KEY location_id (location_id),
            FOREIGN KEY (provider_id) REFERENCES {$this->table_prefix}providers(id) ON DELETE CASCADE,
            FOREIGN KEY (location_id) REFERENCES {$this->table_prefix}locations(id) ON DELETE CASCADE
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get table name with prefix
     */
    public function getTableName($table)
    {
        return $this->table_prefix . $table;
    }

    /**
     * Check if tables exist
     */
    public function tablesExist()
    {
        $tables = [
            'patients',
            'providers', 
            'services',
            'locations',
            'appointments',
            'clinical_notes',
            'prescriptions',
            'rooms'
        ];

        foreach ($tables as $table) {
            $table_name = $this->getTableName($table);
            if ($this->db->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                return false;
            }
        }

        return true;
    }
}
