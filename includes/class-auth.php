<?php
/**
 * Authentication and Authorization class for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Auth {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', array($this, 'init_auth'));
    }
    
    /**
     * Initialize authentication
     */
    public function init_auth() {
        // Add custom authentication methods if needed
    }
    
    /**
     * Check if user can manage clinics
     */
    public static function can_manage_clinics($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'manage_options') || user_can($user_id, 'edit_posts');
    }
    
    /**
     * Check if user can manage hospitals
     */
    public static function can_manage_hospitals($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'manage_options') || user_can($user_id, 'edit_posts');
    }
    
    /**
     * Check if user can manage doctors
     */
    public static function can_manage_doctors($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'manage_options') || user_can($user_id, 'edit_posts');
    }
    
    /**
     * Check if user can manage bookings
     */
    public static function can_manage_bookings($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'manage_options') || user_can($user_id, 'edit_posts');
    }
    
    /**
     * Check if user can view bookings
     */
    public static function can_view_bookings($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'read') || user_can($user_id, 'edit_posts');
    }
    
    /**
     * Check if user can manage payments
     */
    public static function can_manage_payments($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'manage_options');
    }
    
    /**
     * Check if user can access clinic data
     */
    public static function can_access_clinic($clinic_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Super admin can access all clinics
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        // Check if user is assigned to this clinic
        global $wpdb;
        $staff_table = MedX360_Database::get_table_name('staff');
        $doctors_table = MedX360_Database::get_table_name('doctors');
        
        $is_staff = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $staff_table WHERE clinic_id = %d AND user_id = %d AND status = 'active'",
            $clinic_id,
            $user_id
        ));
        
        $is_doctor = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $doctors_table WHERE clinic_id = %d AND user_id = %d AND status = 'active'",
            $clinic_id,
            $user_id
        ));
        
        return $is_staff || $is_doctor;
    }
    
    /**
     * Check if user can access hospital data
     */
    public static function can_access_hospital($hospital_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Super admin can access all hospitals
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        // Get clinic_id from hospital
        global $wpdb;
        $hospitals_table = MedX360_Database::get_table_name('hospitals');
        $clinic_id = $wpdb->get_var($wpdb->prepare(
            "SELECT clinic_id FROM $hospitals_table WHERE id = %d",
            $hospital_id
        ));
        
        if (!$clinic_id) {
            return false;
        }
        
        return self::can_access_clinic($clinic_id, $user_id);
    }
    
    /**
     * Check if user can access doctor data
     */
    public static function can_access_doctor($doctor_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Super admin can access all doctors
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        // Check if user is the doctor
        global $wpdb;
        $doctors_table = MedX360_Database::get_table_name('doctors');
        $doctor = $wpdb->get_row($wpdb->prepare(
            "SELECT clinic_id, user_id FROM $doctors_table WHERE id = %d",
            $doctor_id
        ));
        
        if (!$doctor) {
            return false;
        }
        
        // If user is the doctor
        if ($doctor->user_id == $user_id) {
            return true;
        }
        
        // Check clinic access
        return self::can_access_clinic($doctor->clinic_id, $user_id);
    }
    
    /**
     * Check if user can access booking data
     */
    public static function can_access_booking($booking_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Super admin can access all bookings
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        // Get booking details
        global $wpdb;
        $bookings_table = MedX360_Database::get_table_name('bookings');
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT clinic_id, hospital_id, doctor_id FROM $bookings_table WHERE id = %d",
            $booking_id
        ));
        
        if (!$booking) {
            return false;
        }
        
        // Check clinic access
        if (self::can_access_clinic($booking->clinic_id, $user_id)) {
            return true;
        }
        
        // Check hospital access
        if ($booking->hospital_id && self::can_access_hospital($booking->hospital_id, $user_id)) {
            return true;
        }
        
        // Check doctor access
        if ($booking->doctor_id && self::can_access_doctor($booking->doctor_id, $user_id)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get user's accessible clinic IDs
     */
    public static function get_user_clinic_ids($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Super admin can access all clinics
        if (user_can($user_id, 'manage_options')) {
            global $wpdb;
            $clinics_table = MedX360_Database::get_table_name('clinics');
            $results = $wpdb->get_col("SELECT id FROM $clinics_table WHERE status = 'active'");
            return $results;
        }
        
        global $wpdb;
        $staff_table = MedX360_Database::get_table_name('staff');
        $doctors_table = MedX360_Database::get_table_name('doctors');
        
        $clinic_ids = array();
        
        // Get clinics from staff role
        $staff_clinics = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT clinic_id FROM $staff_table WHERE user_id = %d AND status = 'active'",
            $user_id
        ));
        
        // Get clinics from doctor role
        $doctor_clinics = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT clinic_id FROM $doctors_table WHERE user_id = %d AND status = 'active'",
            $user_id
        ));
        
        $clinic_ids = array_unique(array_merge($staff_clinics, $doctor_clinics));
        
        return $clinic_ids;
    }
    
    /**
     * Get user's accessible hospital IDs
     */
    public static function get_user_hospital_ids($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $clinic_ids = self::get_user_clinic_ids($user_id);
        
        if (empty($clinic_ids)) {
            return array();
        }
        
        global $wpdb;
        $hospitals_table = MedX360_Database::get_table_name('hospitals');
        
        $placeholders = implode(',', array_fill(0, count($clinic_ids), '%d'));
        $hospital_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM $hospitals_table WHERE clinic_id IN ($placeholders) AND status = 'active'",
            $clinic_ids
        ));
        
        return $hospital_ids;
    }
    
    /**
     * Get user's accessible doctor IDs
     */
    public static function get_user_doctor_ids($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $clinic_ids = self::get_user_clinic_ids($user_id);
        
        if (empty($clinic_ids)) {
            return array();
        }
        
        global $wpdb;
        $doctors_table = MedX360_Database::get_table_name('doctors');
        
        $placeholders = implode(',', array_fill(0, count($clinic_ids), '%d'));
        $doctor_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM $doctors_table WHERE clinic_id IN ($placeholders) AND status = 'active'",
            $clinic_ids
        ));
        
        return $doctor_ids;
    }
}
