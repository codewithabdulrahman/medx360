<?php
/**
 * Onboarding API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Onboarding_API extends MedX360_API_Controller {
    
    protected $rest_base = 'onboarding';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/status', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_setup_status'),
            'permission_callback' => array($this, 'check_permission')
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/steps', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_setup_steps'),
            'permission_callback' => array($this, 'check_permission')
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/progress', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_setup_progress'),
            'permission_callback' => array($this, 'check_permission')
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/statistics', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_setup_statistics'),
            'permission_callback' => array($this, 'check_permission')
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/clinic', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'create_default_clinic'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => $this->get_clinic_params()
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/services', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'create_default_services'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'clinic_id' => array(
                    'description' => __('Clinic ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/complete', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'complete_setup'),
            'permission_callback' => array($this, 'check_permission')
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/reset', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'reset_setup'),
            'permission_callback' => array($this, 'check_permission')
        ));
        
        // Settings endpoint
        register_rest_route($this->namespace, '/settings', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_settings'),
                'permission_callback' => array($this, 'check_permission')
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'save_settings'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
    }
    
    /**
     * Get setup status
     */
    public function get_setup_status($request) {
        $is_completed = MedX360_Onboarding::is_setup_completed();
        $next_step = MedX360_Onboarding::get_next_step();
        $progress = MedX360_Onboarding::get_setup_progress();
        
        return $this->format_response(array(
            'is_completed' => $is_completed,
            'next_step' => $next_step,
            'progress' => $progress
        ));
    }
    
    /**
     * Get setup steps
     */
    public function get_setup_steps($request) {
        $steps = MedX360_Onboarding::get_setup_steps();
        
        return $this->format_response($steps);
    }
    
    /**
     * Get setup progress
     */
    public function get_setup_progress($request) {
        $progress = MedX360_Onboarding::get_setup_progress();
        $steps = MedX360_Onboarding::get_setup_steps();
        
        return $this->format_response(array(
            'progress_percentage' => $progress,
            'steps' => $steps
        ));
    }
    
    /**
     * Get setup statistics
     */
    public function get_setup_statistics($request) {
        $stats = MedX360_Onboarding::get_setup_statistics();
        
        return $this->format_response($stats);
    }
    
    /**
     * Create default clinic
     */
    public function create_default_clinic($request) {
        $data = $request->get_json_params();
        
        // Validate required fields
        $required_fields = array('name');
        $errors = $this->validate_required_fields($data, $required_fields);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Validate clinic data
        $clinic_errors = MedX360_Validator::validate_clinic_data($data);
        if (!empty($clinic_errors)) {
            return $this->format_error_response(implode(', ', $clinic_errors), 'validation_error', 400);
        }
        
        // Create default clinic
        $clinic_id = MedX360_Onboarding::create_default_clinic($data);
        
        if (!$clinic_id) {
            return $this->format_error_response(__('Failed to create default clinic', 'medx360'), 'create_failed', 500);
        }
        
        // Get created clinic
        global $wpdb;
        $clinics_table = MedX360_Database::get_table_name('clinics');
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $clinics_table WHERE id = %d",
            $clinic_id
        ));
        
        return $this->format_response(array(
            'clinic_id' => $clinic_id,
            'clinic' => $this->format_clinic_data($clinic),
            'message' => __('Default clinic created successfully', 'medx360')
        ), 201);
    }
    
    /**
     * Create default services
     */
    public function create_default_services($request) {
        $clinic_id = $request->get_param('clinic_id');
        
        // Validate clinic exists
        global $wpdb;
        $clinics_table = MedX360_Database::get_table_name('clinics');
        $clinic_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $clinics_table WHERE id = %d",
            $clinic_id
        ));
        
        if (!$clinic_exists) {
            return $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 400);
        }
        
        // Create default services
        $service_ids = MedX360_Onboarding::create_default_services($clinic_id);
        
        if (empty($service_ids)) {
            return $this->format_error_response(__('Failed to create default services', 'medx360'), 'create_failed', 500);
        }
        
        // Get created services
        $services_table = MedX360_Database::get_table_name('services');
        $placeholders = implode(',', array_fill(0, count($service_ids), '%d'));
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $services_table WHERE id IN ($placeholders)",
            $service_ids
        ));
        
        $formatted_services = array();
        foreach ($services as $service) {
            $formatted_services[] = $this->format_service_data($service);
        }
        
        return $this->format_response(array(
            'service_ids' => $service_ids,
            'services' => $formatted_services,
            'message' => __('Default services created successfully', 'medx360')
        ), 201);
    }
    
    /**
     * Complete setup
     */
    public function complete_setup($request) {
        $result = MedX360_Onboarding::complete_setup();
        
        if (!$result) {
            return $this->format_error_response(__('Failed to complete setup', 'medx360'), 'complete_failed', 500);
        }
        
        return $this->format_response(array(
            'message' => __('Setup completed successfully', 'medx360'),
            'is_completed' => true
        ));
    }
    
    /**
     * Reset setup
     */
    public function reset_setup($request) {
        MedX360_Onboarding::reset_setup();
        
        return $this->format_response(array(
            'message' => __('Setup reset successfully', 'medx360'),
            'is_completed' => false
        ));
    }
    
    /**
     * Get settings
     */
    public function get_settings($request) {
        $settings = get_option('medx360_settings', array());
        
        return $this->format_response($settings);
    }
    
    /**
     * Save settings
     */
    public function save_settings($request) {
        $data = $request->get_json_params();
        
        // Sanitize settings data
        $sanitized_settings = array();
        
        // Booking settings
        if (isset($data['booking_advance_days'])) {
            $sanitized_settings['booking_advance_days'] = intval($data['booking_advance_days']);
        }
        if (isset($data['booking_cancellation_hours'])) {
            $sanitized_settings['booking_cancellation_hours'] = intval($data['booking_cancellation_hours']);
        }
        
        // Notification settings
        if (isset($data['email_notifications'])) {
            $sanitized_settings['email_notifications'] = (bool) $data['email_notifications'];
        }
        if (isset($data['sms_notifications'])) {
            $sanitized_settings['sms_notifications'] = (bool) $data['sms_notifications'];
        }
        if (isset($data['reminder_notifications'])) {
            $sanitized_settings['reminder_notifications'] = (bool) $data['reminder_notifications'];
        }
        
        // System settings
        if (isset($data['timezone'])) {
            $sanitized_settings['timezone'] = sanitize_text_field($data['timezone']);
        }
        if (isset($data['date_format'])) {
            $sanitized_settings['date_format'] = sanitize_text_field($data['date_format']);
        }
        if (isset($data['time_format'])) {
            $sanitized_settings['time_format'] = sanitize_text_field($data['time_format']);
        }
        
        // Payment settings
        if (isset($data['currency'])) {
            $sanitized_settings['currency'] = sanitize_text_field($data['currency']);
        }
        if (isset($data['currency_symbol'])) {
            $sanitized_settings['currency_symbol'] = sanitize_text_field($data['currency_symbol']);
        }
        if (isset($data['payment_gateway'])) {
            $sanitized_settings['payment_gateway'] = sanitize_text_field($data['payment_gateway']);
        }
        
        // Merge with existing settings
        $existing_settings = get_option('medx360_settings', array());
        $merged_settings = array_merge($existing_settings, $sanitized_settings);
        
        // Save settings
        $result = update_option('medx360_settings', $merged_settings);
        
        if ($result) {
            return $this->format_response(array(
                'message' => __('Settings saved successfully', 'medx360'),
                'settings' => $merged_settings
            ));
        } else {
            return $this->format_error_response(__('Failed to save settings', 'medx360'), 'save_failed', 500);
        }
    }
    
    /**
     * Format clinic data for response
     */
    private function format_clinic_data($clinic) {
        $settings = !empty($clinic->settings) ? json_decode($clinic->settings, true) : array();
        
        return array(
            'id' => intval($clinic->id),
            'name' => $clinic->name,
            'slug' => $clinic->slug,
            'description' => $clinic->description,
            'address' => $clinic->address,
            'city' => $clinic->city,
            'state' => $clinic->state,
            'country' => $clinic->country,
            'postal_code' => $clinic->postal_code,
            'phone' => $clinic->phone,
            'email' => $clinic->email,
            'website' => $clinic->website,
            'logo_url' => $clinic->logo_url,
            'status' => $clinic->status,
            'settings' => $settings,
            'created_at' => $clinic->created_at,
            'updated_at' => $clinic->updated_at
        );
    }
    
    /**
     * Format service data for response
     */
    private function format_service_data($service) {
        $settings = !empty($service->settings) ? json_decode($service->settings, true) : array();
        
        return array(
            'id' => intval($service->id),
            'clinic_id' => intval($service->clinic_id),
            'hospital_id' => intval($service->hospital_id),
            'name' => $service->name,
            'description' => $service->description,
            'duration_minutes' => intval($service->duration_minutes),
            'price' => floatval($service->price),
            'category' => $service->category,
            'status' => $service->status,
            'settings' => $settings,
            'created_at' => $service->created_at,
            'updated_at' => $service->updated_at
        );
    }
    
    /**
     * Get clinic parameters
     */
    public function get_clinic_params() {
        return array(
            'name' => array(
                'description' => __('Clinic name', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'description' => array(
                'description' => __('Clinic description', 'medx360'),
                'type' => 'string'
            ),
            'address' => array(
                'description' => __('Clinic address', 'medx360'),
                'type' => 'string'
            ),
            'city' => array(
                'description' => __('City', 'medx360'),
                'type' => 'string'
            ),
            'state' => array(
                'description' => __('State', 'medx360'),
                'type' => 'string'
            ),
            'country' => array(
                'description' => __('Country', 'medx360'),
                'type' => 'string'
            ),
            'postal_code' => array(
                'description' => __('Postal code', 'medx360'),
                'type' => 'string'
            ),
            'phone' => array(
                'description' => __('Phone number', 'medx360'),
                'type' => 'string'
            ),
            'email' => array(
                'description' => __('Email address', 'medx360'),
                'type' => 'string',
                'format' => 'email'
            ),
            'website' => array(
                'description' => __('Website URL', 'medx360'),
                'type' => 'string',
                'format' => 'uri'
            ),
            'logo_url' => array(
                'description' => __('Logo URL', 'medx360'),
                'type' => 'string',
                'format' => 'uri'
            )
        );
    }
}
