<?php
/**
 * Onboarding AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Onboarding_AJAX extends MedX360_AJAX_Controller {
    
    public function register_actions() {
        // GET endpoints
        $this->register_ajax_action('get_onboarding_status', array($this, 'get_onboarding_status'));
        $this->register_ajax_action('get_onboarding_steps', array($this, 'get_onboarding_steps'));
        $this->register_ajax_action('get_onboarding_progress', array($this, 'get_onboarding_progress'));
        $this->register_ajax_action('get_onboarding_statistics', array($this, 'get_onboarding_statistics'));
        
        // POST endpoints
        $this->register_ajax_action('create_onboarding_clinic', array($this, 'create_onboarding_clinic'));
        $this->register_ajax_action('create_onboarding_services', array($this, 'create_onboarding_services'));
        
        // PUT endpoints
        $this->register_ajax_action('complete_onboarding', array($this, 'complete_onboarding'));
        $this->register_ajax_action('reset_onboarding', array($this, 'reset_onboarding'));
    }
    
    /**
     * Get onboarding status
     */
    public function get_onboarding_status() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        $is_completed = get_option('medx360_setup_completed', false);
        $progress = $this->calculate_onboarding_progress();
        
        $next_step = 'create_clinic';
        if ($progress > 0) {
            $next_step = 'add_services';
        }
        if ($progress > 50) {
            $next_step = 'add_doctors';
        }
        if ($progress >= 100) {
            $next_step = 'complete';
        }
        
        $response = array(
            'is_completed' => $is_completed,
            'next_step' => $next_step,
            'progress' => $progress
        );
        
        $this->format_response($response);
    }
    
    /**
     * Get onboarding steps
     */
    public function get_onboarding_steps() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        $steps = array(
            array(
                'id' => 'create_clinic',
                'title' => __('Create Clinic', 'medx360'),
                'description' => __('Set up your main clinic', 'medx360'),
                'completed' => $this->has_clinics(),
                'required' => true
            ),
            array(
                'id' => 'add_services',
                'title' => __('Add Services', 'medx360'),
                'description' => __('Define your medical services', 'medx360'),
                'completed' => $this->has_services(),
                'required' => true
            ),
            array(
                'id' => 'add_doctors',
                'title' => __('Add Doctors', 'medx360'),
                'description' => __('Add medical professionals', 'medx360'),
                'completed' => $this->has_doctors(),
                'required' => false
            ),
            array(
                'id' => 'add_staff',
                'title' => __('Add Staff', 'medx360'),
                'description' => __('Add support staff members', 'medx360'),
                'completed' => $this->has_staff(),
                'required' => false
            )
        );
        
        $this->format_response($steps);
    }
    
    /**
     * Get onboarding progress
     */
    public function get_onboarding_progress() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        $progress_percentage = $this->calculate_onboarding_progress();
        $steps = $this->get_onboarding_steps_data();
        
        $response = array(
            'progress_percentage' => $progress_percentage,
            'steps' => $steps
        );
        
        $this->format_response($response);
    }
    
    /**
     * Get onboarding statistics
     */
    public function get_onboarding_statistics() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $clinics_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_clinics");
        $hospitals_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_hospitals");
        $doctors_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_doctors");
        $services_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_services");
        $staff_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_staff");
        
        $response = array(
            'clinics_count' => intval($clinics_count),
            'hospitals_count' => intval($hospitals_count),
            'doctors_count' => intval($doctors_count),
            'services_count' => intval($services_count),
            'staff_count' => intval($staff_count)
        );
        
        $this->format_response($response);
    }
    
    /**
     * Create onboarding clinic
     */
    public function create_onboarding_clinic() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $data = $this->get_post_data();
        
        // Validate required fields
        $required_fields = array('name');
        $errors = $this->validate_required_fields($data, $required_fields);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        $table_name = $this->get_table_name('clinics');
        
        // Generate slug from name if not provided
        $slug = isset($data['slug']) ? sanitize_title($data['slug']) : sanitize_title($data['name']);
        
        // Ensure slug is unique
        $original_slug = $slug;
        $counter = 1;
        while ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE slug = %s", $slug))) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'name' => 'text',
            'description' => 'textarea',
            'address' => 'textarea',
            'city' => 'text',
            'state' => 'text',
            'country' => 'text',
            'postal_code' => 'text',
            'phone' => 'text',
            'email' => 'email',
            'website' => 'url',
            'logo_url' => 'url'
        ));
        
        $sanitized_data['slug'] = $slug;
        $sanitized_data['status'] = 'active';
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert clinic
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            $this->format_error_response(__('Failed to create clinic', 'medx360'), 'create_failed', 500);
        }
        
        $clinic_id = $wpdb->insert_id;
        
        // Get created clinic
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        $response = array(
            'clinic_id' => $clinic_id,
            'clinic' => $this->format_clinic_data($clinic),
            'message' => __('Default clinic created successfully', 'medx360')
        );
        
        $this->format_response($response);
    }
    
    /**
     * Create onboarding services
     */
    public function create_onboarding_services() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $clinic_id = isset($_POST['clinic_id']) ? intval($_POST['clinic_id']) : 0;
        
        if (!$clinic_id) {
            $this->format_error_response(__('Clinic ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if clinic exists
        $clinics_table = $this->get_table_name('clinics');
        $clinic_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $clinics_table WHERE id = %d",
            $clinic_id
        ));
        
        if (!$clinic_exists) {
            $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 400);
        }
        
        $table_name = $this->get_table_name('services');
        
        // Default services
        $default_services = array(
            array(
                'name' => __('General Consultation', 'medx360'),
                'description' => __('Standard medical consultation', 'medx360'),
                'duration_minutes' => 30,
                'price' => 100.00,
                'category' => __('Consultation', 'medx360')
            ),
            array(
                'name' => __('Follow-up Consultation', 'medx360'),
                'description' => __('Follow-up medical consultation', 'medx360'),
                'duration_minutes' => 15,
                'price' => 50.00,
                'category' => __('Consultation', 'medx360')
            ),
            array(
                'name' => __('Emergency Consultation', 'medx360'),
                'description' => __('Emergency medical consultation', 'medx360'),
                'duration_minutes' => 45,
                'price' => 200.00,
                'category' => __('Emergency', 'medx360')
            )
        );
        
        $service_ids = array();
        $created_services = array();
        
        foreach ($default_services as $service_data) {
            $service_data['clinic_id'] = $clinic_id;
            $service_data['status'] = 'active';
            $service_data['created_at'] = current_time('mysql');
            $service_data['updated_at'] = current_time('mysql');
            
            $result = $wpdb->insert($table_name, $service_data);
            
            if ($result !== false) {
                $service_id = $wpdb->insert_id;
                $service_ids[] = $service_id;
                
                $service = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $table_name WHERE id = %d",
                    $service_id
                ));
                
                $created_services[] = $this->format_service_data($service);
            }
        }
        
        $response = array(
            'service_ids' => $service_ids,
            'services' => $created_services,
            'message' => __('Default services created successfully', 'medx360')
        );
        
        $this->format_response($response);
    }
    
    /**
     * Complete onboarding
     */
    public function complete_onboarding() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        update_option('medx360_setup_completed', true);
        
        $response = array(
            'message' => __('Setup completed successfully', 'medx360'),
            'is_completed' => true
        );
        
        $this->format_response($response);
    }
    
    /**
     * Reset onboarding
     */
    public function reset_onboarding() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        update_option('medx360_setup_completed', false);
        
        $response = array(
            'message' => __('Setup reset successfully', 'medx360'),
            'is_completed' => false
        );
        
        $this->format_response($response);
    }
    
    /**
     * Calculate onboarding progress
     */
    private function calculate_onboarding_progress() {
        $steps_completed = 0;
        $total_steps = 4;
        
        if ($this->has_clinics()) $steps_completed++;
        if ($this->has_services()) $steps_completed++;
        if ($this->has_doctors()) $steps_completed++;
        if ($this->has_staff()) $steps_completed++;
        
        return round(($steps_completed / $total_steps) * 100);
    }
    
    /**
     * Check if clinics exist
     */
    private function has_clinics() {
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_clinics");
        return $count > 0;
    }
    
    /**
     * Check if services exist
     */
    private function has_services() {
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_services");
        return $count > 0;
    }
    
    /**
     * Check if doctors exist
     */
    private function has_doctors() {
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_doctors");
        return $count > 0;
    }
    
    /**
     * Check if staff exist
     */
    private function has_staff() {
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_staff");
        return $count > 0;
    }
    
    /**
     * Get onboarding steps data
     */
    private function get_onboarding_steps_data() {
        return array(
            array(
                'id' => 'create_clinic',
                'title' => __('Create Clinic', 'medx360'),
                'description' => __('Set up your main clinic', 'medx360'),
                'completed' => $this->has_clinics(),
                'required' => true
            ),
            array(
                'id' => 'add_services',
                'title' => __('Add Services', 'medx360'),
                'description' => __('Define your medical services', 'medx360'),
                'completed' => $this->has_services(),
                'required' => true
            ),
            array(
                'id' => 'add_doctors',
                'title' => __('Add Doctors', 'medx360'),
                'description' => __('Add medical professionals', 'medx360'),
                'completed' => $this->has_doctors(),
                'required' => false
            ),
            array(
                'id' => 'add_staff',
                'title' => __('Add Staff', 'medx360'),
                'description' => __('Add support staff members', 'medx360'),
                'completed' => $this->has_staff(),
                'required' => false
            )
        );
    }
    
    /**
     * Format clinic data
     */
    private function format_clinic_data($clinic) {
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
            'created_at' => $clinic->created_at,
            'updated_at' => $clinic->updated_at
        );
    }
    
    /**
     * Format service data
     */
    private function format_service_data($service) {
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
            'created_at' => $service->created_at,
            'updated_at' => $service->updated_at
        );
    }
}
