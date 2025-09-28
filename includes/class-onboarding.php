<?php
/**
 * Onboarding class for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Onboarding {
    
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
     * Check if setup is completed
     */
    public static function is_setup_completed() {
        return get_option('medx360_setup_completed', false);
    }
    
    /**
     * Mark setup as completed
     */
    public static function mark_setup_completed() {
        update_option('medx360_setup_completed', true);
    }
    
    /**
     * Reset setup status
     */
    public static function reset_setup() {
        update_option('medx360_setup_completed', false);
    }
    
    /**
     * Get setup steps
     */
    public static function get_setup_steps() {
        return array(
            'welcome' => array(
                'title' => __('Welcome to MedX360', 'medx360'),
                'description' => __('Let\'s set up your medical booking system', 'medx360'),
                'completed' => true
            ),
            'clinic' => array(
                'title' => __('Clinic Information', 'medx360'),
                'description' => __('Add your clinic details', 'medx360'),
                'completed' => self::is_clinic_setup_completed()
            ),
            'hospital' => array(
                'title' => __('Hospital Information', 'medx360'),
                'description' => __('Add hospital details (optional)', 'medx360'),
                'completed' => self::is_hospital_setup_completed(),
                'optional' => true
            ),
            'doctors' => array(
                'title' => __('Add Doctors', 'medx360'),
                'description' => __('Add your medical staff', 'medx360'),
                'completed' => self::is_doctors_setup_completed()
            ),
            'services' => array(
                'title' => __('Medical Services', 'medx360'),
                'description' => __('Define your medical services', 'medx360'),
                'completed' => self::is_services_setup_completed()
            ),
            'settings' => array(
                'title' => __('System Settings', 'medx360'),
                'description' => __('Configure your booking system', 'medx360'),
                'completed' => self::is_settings_setup_completed()
            ),
            'complete' => array(
                'title' => __('Setup Complete', 'medx360'),
                'description' => __('Your system is ready to use', 'medx360'),
                'completed' => self::is_setup_completed()
            )
        );
    }
    
    /**
     * Check if clinic setup is completed
     */
    private static function is_clinic_setup_completed() {
        global $wpdb;
        $clinics_table = MedX360_Database::get_table_name('clinics');
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $clinics_table WHERE status = 'active'");
        return $count > 0;
    }
    
    /**
     * Check if hospital setup is completed
     */
    private static function is_hospital_setup_completed() {
        global $wpdb;
        $hospitals_table = MedX360_Database::get_table_name('hospitals');
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $hospitals_table WHERE status = 'active'");
        return $count > 0;
    }
    
    /**
     * Check if doctors setup is completed
     */
    private static function is_doctors_setup_completed() {
        global $wpdb;
        $doctors_table = MedX360_Database::get_table_name('doctors');
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $doctors_table WHERE status = 'active'");
        return $count > 0;
    }
    
    /**
     * Check if services setup is completed
     */
    private static function is_services_setup_completed() {
        global $wpdb;
        $services_table = MedX360_Database::get_table_name('services');
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $services_table WHERE status = 'active'");
        return $count > 0;
    }
    
    /**
     * Check if settings setup is completed
     */
    private static function is_settings_setup_completed() {
        $settings = get_option('medx360_settings', array());
        return !empty($settings);
    }
    
    /**
     * Get setup progress percentage
     */
    public static function get_setup_progress() {
        $steps = self::get_setup_steps();
        $total_steps = count($steps);
        $completed_steps = 0;
        
        foreach ($steps as $step) {
            if ($step['completed']) {
                $completed_steps++;
            }
        }
        
        return round(($completed_steps / $total_steps) * 100);
    }
    
    /**
     * Get next incomplete step
     */
    public static function get_next_step() {
        $steps = self::get_setup_steps();
        
        foreach ($steps as $key => $step) {
            if (!$step['completed']) {
                return $key;
            }
        }
        
        return 'complete';
    }
    
    /**
     * Create default clinic
     */
    public static function create_default_clinic($data) {
        global $wpdb;
        $clinics_table = MedX360_Database::get_table_name('clinics');
        
        $default_data = array(
            'name' => $data['name'] ?: 'My Clinic',
            'slug' => sanitize_title($data['name'] ?: 'my-clinic'),
            'description' => $data['description'] ?: '',
            'address' => $data['address'] ?: '',
            'city' => $data['city'] ?: '',
            'state' => $data['state'] ?: '',
            'country' => $data['country'] ?: '',
            'postal_code' => $data['postal_code'] ?: '',
            'phone' => $data['phone'] ?: '',
            'email' => $data['email'] ?: '',
            'website' => $data['website'] ?: '',
            'status' => 'active',
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($clinics_table, $default_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Create default services
     */
    public static function create_default_services($clinic_id) {
        global $wpdb;
        $services_table = MedX360_Database::get_table_name('services');
        
        $default_services = array(
            array(
                'clinic_id' => $clinic_id,
                'name' => __('General Consultation', 'medx360'),
                'description' => __('General medical consultation', 'medx360'),
                'duration_minutes' => 30,
                'price' => 50.00,
                'category' => 'consultation',
                'status' => 'active'
            ),
            array(
                'clinic_id' => $clinic_id,
                'name' => __('Follow-up Consultation', 'medx360'),
                'description' => __('Follow-up medical consultation', 'medx360'),
                'duration_minutes' => 15,
                'price' => 25.00,
                'category' => 'consultation',
                'status' => 'active'
            ),
            array(
                'clinic_id' => $clinic_id,
                'name' => __('Health Checkup', 'medx360'),
                'description' => __('Comprehensive health checkup', 'medx360'),
                'duration_minutes' => 60,
                'price' => 100.00,
                'category' => 'checkup',
                'status' => 'active'
            )
        );
        
        $created_services = array();
        
        foreach ($default_services as $service) {
            $service['created_at'] = current_time('mysql');
            $result = $wpdb->insert($services_table, $service);
            
            if ($result) {
                $created_services[] = $wpdb->insert_id;
            }
        }
        
        return $created_services;
    }
    
    /**
     * Get setup statistics
     */
    public static function get_setup_statistics() {
        global $wpdb;
        
        $stats = array();
        
        // Count clinics
        $clinics_table = MedX360_Database::get_table_name('clinics');
        $stats['clinics'] = $wpdb->get_var("SELECT COUNT(*) FROM $clinics_table WHERE status = 'active'");
        
        // Count hospitals
        $hospitals_table = MedX360_Database::get_table_name('hospitals');
        $stats['hospitals'] = $wpdb->get_var("SELECT COUNT(*) FROM $hospitals_table WHERE status = 'active'");
        
        // Count doctors
        $doctors_table = MedX360_Database::get_table_name('doctors');
        $stats['doctors'] = $wpdb->get_var("SELECT COUNT(*) FROM $doctors_table WHERE status = 'active'");
        
        // Count services
        $services_table = MedX360_Database::get_table_name('services');
        $stats['services'] = $wpdb->get_var("SELECT COUNT(*) FROM $services_table WHERE status = 'active'");
        
        // Count staff
        $staff_table = MedX360_Database::get_table_name('staff');
        $stats['staff'] = $wpdb->get_var("SELECT COUNT(*) FROM $staff_table WHERE status = 'active'");
        
        // Count bookings
        $bookings_table = MedX360_Database::get_table_name('bookings');
        $stats['bookings'] = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table");
        
        return $stats;
    }
    
    /**
     * Complete setup
     */
    public static function complete_setup() {
        self::mark_setup_completed();
        
        // Set default settings if not already set
        $settings = get_option('medx360_settings', array());
        
        if (empty($settings)) {
            $default_settings = array(
                'booking_advance_days' => 30,
                'booking_cancellation_hours' => 24,
                'email_notifications' => true,
                'sms_notifications' => false,
                'timezone' => wp_timezone_string(),
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'payment_gateway' => 'manual',
                'booking_confirmation' => true,
                'reminder_notifications' => true
            );
            
            update_option('medx360_settings', $default_settings);
        }
        
        return true;
    }
}
