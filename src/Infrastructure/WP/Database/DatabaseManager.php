<?php

namespace MedX360\Infrastructure\WP\Database;

/**
 * Database Manager - Handles database operations and migrations
 * 
 * @package MedX360\Infrastructure\WP\Database
 */
class DatabaseManager
{
    private $db;
    private $charsetCollate;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->charsetCollate = $wpdb->get_charset_collate();
    }

    /**
     * Initialize database manager
     */
    public function init()
    {
        // Check for database updates
        $this->checkForUpdates();
    }

    /**
     * Create all database tables
     */
    public function createTables()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $this->createPatientsTable();
        $this->createProvidersTable();
        $this->createAppointmentsTable();
        $this->createClinicalNotesTable();
        $this->createPrescriptionsTable();
        $this->createServicesTable();
        $this->createLocationsTable();
        $this->createRoomsTable();
        $this->createNotificationsTable();
        $this->createAuditLogTable();
    }

    /**
     * Create patients table
     */
    private function createPatientsTable()
    {
        $tableName = $this->db->prefix . 'healthcare_patients';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            medical_record_number varchar(50) DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            date_of_birth date NOT NULL,
            gender varchar(20) DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            address text DEFAULT NULL,
            emergency_contact text DEFAULT NULL,
            insurance_info text DEFAULT NULL,
            medical_history text DEFAULT NULL,
            allergies text DEFAULT NULL,
            medications text DEFAULT NULL,
            notes text DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY medical_record_number (medical_record_number),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create providers table
     */
    private function createProvidersTable()
    {
        $tableName = $this->db->prefix . 'healthcare_providers';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            license_number varchar(100) DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            title varchar(50) DEFAULT NULL,
            specialties text DEFAULT NULL,
            qualifications text DEFAULT NULL,
            certifications text DEFAULT NULL,
            languages_spoken text DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            bio text DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY license_number (license_number),
            KEY status (status)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create appointments table
     */
    private function createAppointmentsTable()
    {
        $tableName = $this->db->prefix . 'healthcare_appointments';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            patient_id bigint(20) NOT NULL,
            provider_id bigint(20) NOT NULL,
            service_id bigint(20) DEFAULT NULL,
            location_id bigint(20) DEFAULT NULL,
            room_id bigint(20) DEFAULT NULL,
            appointment_type varchar(50) DEFAULT 'consultation',
            status varchar(20) DEFAULT 'scheduled',
            scheduled_at datetime NOT NULL,
            duration_minutes int(11) DEFAULT 30,
            notes text DEFAULT NULL,
            clinical_notes text DEFAULT NULL,
            prescription_data text DEFAULT NULL,
            lab_orders text DEFAULT NULL,
            referrals text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY patient_id (patient_id),
            KEY provider_id (provider_id),
            KEY service_id (service_id),
            KEY location_id (location_id),
            KEY room_id (room_id),
            KEY status (status),
            KEY scheduled_at (scheduled_at)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create clinical notes table
     */
    private function createClinicalNotesTable()
    {
        $tableName = $this->db->prefix . 'healthcare_clinical_notes';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            appointment_id bigint(20) NOT NULL,
            provider_id bigint(20) NOT NULL,
            note_type varchar(50) DEFAULT 'consultation',
            content text NOT NULL,
            attachments text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY appointment_id (appointment_id),
            KEY provider_id (provider_id),
            KEY note_type (note_type),
            KEY created_at (created_at)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create prescriptions table
     */
    private function createPrescriptionsTable()
    {
        $tableName = $this->db->prefix . 'healthcare_prescriptions';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            appointment_id bigint(20) NOT NULL,
            provider_id bigint(20) NOT NULL,
            patient_id bigint(20) NOT NULL,
            medication_name varchar(200) NOT NULL,
            dosage varchar(100) DEFAULT NULL,
            frequency varchar(100) DEFAULT NULL,
            duration varchar(100) DEFAULT NULL,
            instructions text DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY appointment_id (appointment_id),
            KEY provider_id (provider_id),
            KEY patient_id (patient_id),
            KEY status (status)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create services table
     */
    private function createServicesTable()
    {
        $tableName = $this->db->prefix . 'healthcare_services';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            description text DEFAULT NULL,
            duration_minutes int(11) DEFAULT 30,
            price decimal(10,2) DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY status (status)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create locations table
     */
    private function createLocationsTable()
    {
        $tableName = $this->db->prefix . 'healthcare_locations';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            address text DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create rooms table
     */
    private function createRoomsTable()
    {
        $tableName = $this->db->prefix . 'healthcare_rooms';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            location_id bigint(20) NOT NULL,
            name varchar(200) NOT NULL,
            room_number varchar(50) DEFAULT NULL,
            equipment text DEFAULT NULL,
            capacity int(11) DEFAULT 1,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY location_id (location_id),
            KEY status (status)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create notifications table
     */
    private function createNotificationsTable()
    {
        $tableName = $this->db->prefix . 'healthcare_notifications';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            appointment_id bigint(20) DEFAULT NULL,
            patient_id bigint(20) DEFAULT NULL,
            provider_id bigint(20) DEFAULT NULL,
            type varchar(50) NOT NULL,
            channel varchar(20) NOT NULL,
            subject varchar(200) DEFAULT NULL,
            content text NOT NULL,
            status varchar(20) DEFAULT 'pending',
            sent_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY appointment_id (appointment_id),
            KEY patient_id (patient_id),
            KEY provider_id (provider_id),
            KEY type (type),
            KEY status (status)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Create audit log table
     */
    private function createAuditLogTable()
    {
        $tableName = $this->db->prefix . 'healthcare_audit_log';

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            action varchar(100) NOT NULL,
            entity_type varchar(50) NOT NULL,
            entity_id bigint(20) DEFAULT NULL,
            old_values text DEFAULT NULL,
            new_values text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY entity_type (entity_type),
            KEY entity_id (entity_id),
            KEY created_at (created_at)
        ) $this->charsetCollate;";

        dbDelta($sql);
    }

    /**
     * Check for database updates
     */
    public function checkForUpdates()
    {
        $currentVersion = get_option('medx360_db_version', '0.0.0');
        $pluginVersion = MEDX360_VERSION;

        if (version_compare($currentVersion, $pluginVersion, '<')) {
            $this->createTables();
            update_option('medx360_db_version', $pluginVersion);
        }
    }

    /**
     * Drop all tables
     */
    public function dropTables()
    {
        $tables = [
            'healthcare_audit_log',
            'healthcare_notifications',
            'healthcare_rooms',
            'healthcare_locations',
            'healthcare_services',
            'healthcare_prescriptions',
            'healthcare_clinical_notes',
            'healthcare_appointments',
            'healthcare_providers',
            'healthcare_patients',
        ];

        foreach ($tables as $table) {
            $tableName = $this->db->prefix . $table;
            $this->db->query("DROP TABLE IF EXISTS $tableName");
        }

        delete_option('medx360_db_version');
    }

    /**
     * Get database connection
     */
    public function getConnection()
    {
        return $this->db;
    }
}
