<?php
/**
 * Data validation class for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Validator {
    
    /**
     * Validate email
     */
    public static function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number
     */
    public static function validate_phone($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if phone number is between 7 and 15 digits
        return strlen($phone) >= 7 && strlen($phone) <= 15;
    }
    
    /**
     * Validate date
     */
    public static function validate_date($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate time
     */
    public static function validate_time($time, $format = 'H:i:s') {
        $d = DateTime::createFromFormat($format, $time);
        return $d && $d->format($format) === $time;
    }
    
    /**
     * Validate URL
     */
    public static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate slug
     */
    public static function validate_slug($slug) {
        return preg_match('/^[a-z0-9-]+$/', $slug) === 1;
    }
    
    /**
     * Validate price
     */
    public static function validate_price($price) {
        return is_numeric($price) && $price >= 0;
    }
    
    /**
     * Validate duration
     */
    public static function validate_duration($duration) {
        return is_numeric($duration) && $duration > 0 && $duration <= 1440; // Max 24 hours
    }
    
    /**
     * Validate status
     */
    public static function validate_status($status, $allowed_statuses) {
        return in_array($status, $allowed_statuses);
    }
    
    /**
     * Validate clinic data
     */
    public static function validate_clinic_data($data) {
        $errors = array();
        
        // Required fields
        $required_fields = array('name', 'slug');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required', 'medx360'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate slug
        if (!empty($data['slug']) && !self::validate_slug($data['slug'])) {
            $errors[] = __('Slug must contain only lowercase letters, numbers, and hyphens', 'medx360');
        }
        
        // Validate email
        if (!empty($data['email']) && !self::validate_email($data['email'])) {
            $errors[] = __('Invalid email address', 'medx360');
        }
        
        // Validate phone
        if (!empty($data['phone']) && !self::validate_phone($data['phone'])) {
            $errors[] = __('Invalid phone number', 'medx360');
        }
        
        // Validate website
        if (!empty($data['website']) && !self::validate_url($data['website'])) {
            $errors[] = __('Invalid website URL', 'medx360');
        }
        
        // Validate status
        if (!empty($data['status']) && !self::validate_status($data['status'], array('active', 'inactive', 'pending'))) {
            $errors[] = __('Invalid status', 'medx360');
        }
        
        return $errors;
    }
    
    /**
     * Validate hospital data
     */
    public static function validate_hospital_data($data) {
        $errors = array();
        
        // Required fields
        $required_fields = array('clinic_id', 'name', 'slug');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required', 'medx360'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate clinic_id
        if (!empty($data['clinic_id']) && !is_numeric($data['clinic_id'])) {
            $errors[] = __('Invalid clinic ID', 'medx360');
        }
        
        // Validate slug
        if (!empty($data['slug']) && !self::validate_slug($data['slug'])) {
            $errors[] = __('Slug must contain only lowercase letters, numbers, and hyphens', 'medx360');
        }
        
        // Validate email
        if (!empty($data['email']) && !self::validate_email($data['email'])) {
            $errors[] = __('Invalid email address', 'medx360');
        }
        
        // Validate phone
        if (!empty($data['phone']) && !self::validate_phone($data['phone'])) {
            $errors[] = __('Invalid phone number', 'medx360');
        }
        
        // Validate website
        if (!empty($data['website']) && !self::validate_url($data['website'])) {
            $errors[] = __('Invalid website URL', 'medx360');
        }
        
        // Validate status
        if (!empty($data['status']) && !self::validate_status($data['status'], array('active', 'inactive', 'pending'))) {
            $errors[] = __('Invalid status', 'medx360');
        }
        
        return $errors;
    }
    
    /**
     * Validate doctor data
     */
    public static function validate_doctor_data($data) {
        $errors = array();
        
        // Required fields
        $required_fields = array('clinic_id', 'first_name', 'last_name', 'email');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required', 'medx360'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate clinic_id
        if (!empty($data['clinic_id']) && !is_numeric($data['clinic_id'])) {
            $errors[] = __('Invalid clinic ID', 'medx360');
        }
        
        // Validate hospital_id
        if (!empty($data['hospital_id']) && !is_numeric($data['hospital_id'])) {
            $errors[] = __('Invalid hospital ID', 'medx360');
        }
        
        // Validate email
        if (!empty($data['email']) && !self::validate_email($data['email'])) {
            $errors[] = __('Invalid email address', 'medx360');
        }
        
        // Validate phone
        if (!empty($data['phone']) && !self::validate_phone($data['phone'])) {
            $errors[] = __('Invalid phone number', 'medx360');
        }
        
        // Validate consultation fee
        if (!empty($data['consultation_fee']) && !self::validate_price($data['consultation_fee'])) {
            $errors[] = __('Invalid consultation fee', 'medx360');
        }
        
        // Validate experience years
        if (!empty($data['experience_years']) && (!is_numeric($data['experience_years']) || $data['experience_years'] < 0)) {
            $errors[] = __('Invalid experience years', 'medx360');
        }
        
        // Validate status
        if (!empty($data['status']) && !self::validate_status($data['status'], array('active', 'inactive', 'pending'))) {
            $errors[] = __('Invalid status', 'medx360');
        }
        
        return $errors;
    }
    
    /**
     * Validate service data
     */
    public static function validate_service_data($data) {
        $errors = array();
        
        // Required fields
        $required_fields = array('clinic_id', 'name');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required', 'medx360'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate clinic_id
        if (!empty($data['clinic_id']) && !is_numeric($data['clinic_id'])) {
            $errors[] = __('Invalid clinic ID', 'medx360');
        }
        
        // Validate hospital_id
        if (!empty($data['hospital_id']) && !is_numeric($data['hospital_id'])) {
            $errors[] = __('Invalid hospital ID', 'medx360');
        }
        
        // Validate duration
        if (!empty($data['duration_minutes']) && !self::validate_duration($data['duration_minutes'])) {
            $errors[] = __('Invalid duration', 'medx360');
        }
        
        // Validate price
        if (!empty($data['price']) && !self::validate_price($data['price'])) {
            $errors[] = __('Invalid price', 'medx360');
        }
        
        // Validate status
        if (!empty($data['status']) && !self::validate_status($data['status'], array('active', 'inactive'))) {
            $errors[] = __('Invalid status', 'medx360');
        }
        
        return $errors;
    }
    
    /**
     * Validate booking data
     */
    public static function validate_booking_data($data) {
        $errors = array();
        
        // Required fields
        $required_fields = array('clinic_id', 'patient_name', 'patient_email', 'appointment_date', 'appointment_time');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required', 'medx360'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate clinic_id
        if (!empty($data['clinic_id']) && !is_numeric($data['clinic_id'])) {
            $errors[] = __('Invalid clinic ID', 'medx360');
        }
        
        // Validate hospital_id
        if (!empty($data['hospital_id']) && !is_numeric($data['hospital_id'])) {
            $errors[] = __('Invalid hospital ID', 'medx360');
        }
        
        // Validate doctor_id
        if (!empty($data['doctor_id']) && !is_numeric($data['doctor_id'])) {
            $errors[] = __('Invalid doctor ID', 'medx360');
        }
        
        // Validate service_id
        if (!empty($data['service_id']) && !is_numeric($data['service_id'])) {
            $errors[] = __('Invalid service ID', 'medx360');
        }
        
        // Validate patient email
        if (!empty($data['patient_email']) && !self::validate_email($data['patient_email'])) {
            $errors[] = __('Invalid patient email address', 'medx360');
        }
        
        // Validate patient phone
        if (!empty($data['patient_phone']) && !self::validate_phone($data['patient_phone'])) {
            $errors[] = __('Invalid patient phone number', 'medx360');
        }
        
        // Validate appointment date
        if (!empty($data['appointment_date']) && !self::validate_date($data['appointment_date'])) {
            $errors[] = __('Invalid appointment date', 'medx360');
        }
        
        // Validate appointment time
        if (!empty($data['appointment_time']) && !self::validate_time($data['appointment_time'])) {
            $errors[] = __('Invalid appointment time', 'medx360');
        }
        
        // Validate patient gender
        if (!empty($data['patient_gender']) && !self::validate_status($data['patient_gender'], array('male', 'female', 'other'))) {
            $errors[] = __('Invalid patient gender', 'medx360');
        }
        
        // Validate status
        if (!empty($data['status']) && !self::validate_status($data['status'], array('pending', 'confirmed', 'cancelled', 'completed', 'no_show'))) {
            $errors[] = __('Invalid booking status', 'medx360');
        }
        
        // Validate payment status
        if (!empty($data['payment_status']) && !self::validate_status($data['payment_status'], array('pending', 'paid', 'refunded', 'failed'))) {
            $errors[] = __('Invalid payment status', 'medx360');
        }
        
        // Validate total amount
        if (!empty($data['total_amount']) && !self::validate_price($data['total_amount'])) {
            $errors[] = __('Invalid total amount', 'medx360');
        }
        
        return $errors;
    }
    
    /**
     * Validate payment data
     */
    public static function validate_payment_data($data) {
        $errors = array();
        
        // Required fields
        $required_fields = array('booking_id', 'amount', 'payment_method');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required', 'medx360'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate booking_id
        if (!empty($data['booking_id']) && !is_numeric($data['booking_id'])) {
            $errors[] = __('Invalid booking ID', 'medx360');
        }
        
        // Validate amount
        if (!empty($data['amount']) && !self::validate_price($data['amount'])) {
            $errors[] = __('Invalid amount', 'medx360');
        }
        
        // Validate payment method
        if (!empty($data['payment_method']) && !self::validate_status($data['payment_method'], array('cash', 'card', 'bank_transfer', 'online', 'insurance'))) {
            $errors[] = __('Invalid payment method', 'medx360');
        }
        
        // Validate status
        if (!empty($data['status']) && !self::validate_status($data['status'], array('pending', 'completed', 'failed', 'refunded', 'cancelled'))) {
            $errors[] = __('Invalid payment status', 'medx360');
        }
        
        return $errors;
    }
    
    /**
     * Validate consultation data
     */
    public static function validate_consultation_data($data) {
        $errors = array();
        
        // Required fields
        $required_fields = array('booking_id', 'doctor_id');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required', 'medx360'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate booking_id
        if (!empty($data['booking_id']) && !is_numeric($data['booking_id'])) {
            $errors[] = __('Invalid booking ID', 'medx360');
        }
        
        // Validate doctor_id
        if (!empty($data['doctor_id']) && !is_numeric($data['doctor_id'])) {
            $errors[] = __('Invalid doctor ID', 'medx360');
        }
        
        // Validate consultation type
        if (!empty($data['consultation_type']) && !self::validate_status($data['consultation_type'], array('in_person', 'video', 'phone'))) {
            $errors[] = __('Invalid consultation type', 'medx360');
        }
        
        // Validate status
        if (!empty($data['status']) && !self::validate_status($data['status'], array('scheduled', 'in_progress', 'completed', 'cancelled'))) {
            $errors[] = __('Invalid consultation status', 'medx360');
        }
        
        // Validate follow up date
        if (!empty($data['follow_up_date']) && !self::validate_date($data['follow_up_date'])) {
            $errors[] = __('Invalid follow up date', 'medx360');
        }
        
        return $errors;
    }
}
