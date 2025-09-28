<?php
/**
 * Services AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Services_AJAX extends MedX360_AJAX_Controller {
    
    public function register_actions() {
        // GET endpoints
        $this->register_ajax_action('get_services', array($this, 'get_services'));
        $this->register_ajax_action('get_service', array($this, 'get_service'));
        $this->register_ajax_action('get_services_by_clinic', array($this, 'get_services_by_clinic'));
        $this->register_ajax_action('get_services_by_hospital', array($this, 'get_services_by_hospital'));
        
        // POST endpoints
        $this->register_ajax_action('create_service', array($this, 'create_service'));
        
        // PUT endpoints
        $this->register_ajax_action('update_service', array($this, 'update_service'));
        
        // DELETE endpoints
        $this->register_ajax_action('delete_service', array($this, 'delete_service'));
    }
    
    /**
     * Get services collection
     */
    public function get_services() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $pagination = $this->get_pagination_params();
        $search = $this->get_search_params();
        $filters = $this->get_filter_params();
        
        // Add service-specific filters
        if (isset($_POST['category']) && !empty($_POST['category'])) {
            $filters['category'] = sanitize_text_field($_POST['category']);
        }
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('name', 'description', 'category'));
        
        $where_conditions = array_merge($where_clause['conditions'], $search_clause['conditions']);
        $where_values = array_merge($where_clause['values'], $search_clause['values']);
        
        $where_sql = '';
        if (!empty($where_conditions)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM $table_name $where_sql";
        if (!empty($where_values)) {
            $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $where_values));
        } else {
            $total_items = $wpdb->get_var($count_sql);
        }
        
        // Get services
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $services = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_services = array();
        foreach ($services as $service) {
            $formatted_services[] = $this->format_service_data($service);
        }
        
        $response = array(
            'data' => $formatted_services,
            'pagination' => array(
                'page' => $pagination['page'],
                'per_page' => $pagination['per_page'],
                'total_items' => intval($total_items),
                'total_pages' => ceil($total_items / $pagination['per_page'])
            )
        );
        
        $this->format_response($response);
    }
    
    /**
     * Get single service
     */
    public function get_service() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $service_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$service_id) {
            $this->format_error_response(__('Service ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        if (!$service) {
            $this->format_error_response(__('Service not found', 'medx360'), 'service_not_found', 404);
        }
        
        $this->format_response($this->format_service_data($service));
    }
    
    /**
     * Get services by clinic
     */
    public function get_services_by_clinic() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $clinic_id = isset($_POST['clinic_id']) ? intval($_POST['clinic_id']) : 0;
        
        if (!$clinic_id) {
            $this->format_error_response(__('Clinic ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE clinic_id = %d AND status = 'active' ORDER BY name ASC",
            $clinic_id
        ));
        
        $formatted_services = array();
        foreach ($services as $service) {
            $formatted_services[] = $this->format_service_data($service);
        }
        
        $this->format_response($formatted_services);
    }
    
    /**
     * Get services by hospital
     */
    public function get_services_by_hospital() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $hospital_id = isset($_POST['hospital_id']) ? intval($_POST['hospital_id']) : 0;
        
        if (!$hospital_id) {
            $this->format_error_response(__('Hospital ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE hospital_id = %d AND status = 'active' ORDER BY name ASC",
            $hospital_id
        ));
        
        $formatted_services = array();
        foreach ($services as $service) {
            $formatted_services[] = $this->format_service_data($service);
        }
        
        $this->format_response($formatted_services);
    }
    
    /**
     * Create service
     */
    public function create_service() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $data = $this->get_post_data();
        
        // Validate data
        $errors = MedX360_Validator::validate_service_data($data);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if clinic exists
        $clinics_table = $this->get_table_name('clinics');
        $clinic_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $clinics_table WHERE id = %d",
            $data['clinic_id']
        ));
        
        if (!$clinic_exists) {
            $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 400);
        }
        
        // Check if hospital exists (if provided)
        if (!empty($data['hospital_id'])) {
            $hospitals_table = $this->get_table_name('hospitals');
            $hospital_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $hospitals_table WHERE id = %d",
                $data['hospital_id']
            ));
            
            if (!$hospital_exists) {
                $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 400);
            }
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'clinic_id' => 'int',
            'hospital_id' => 'int',
            'name' => 'text',
            'description' => 'textarea',
            'duration_minutes' => 'int',
            'price' => 'float',
            'category' => 'text',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert service
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            $this->format_error_response(__('Failed to create service', 'medx360'), 'create_failed', 500);
        }
        
        $service_id = $wpdb->insert_id;
        
        // Get created service
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        $this->format_response($this->format_service_data($service));
    }
    
    /**
     * Update service
     */
    public function update_service() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $service_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = $this->get_post_data();
        
        if (!$service_id) {
            $this->format_error_response(__('Service ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if service exists
        $existing_service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        if (!$existing_service) {
            $this->format_error_response(__('Service not found', 'medx360'), 'service_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_service_data($data);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if clinic exists (if clinic_id is being updated)
        if (isset($data['clinic_id']) && $data['clinic_id'] != $existing_service->clinic_id) {
            $clinics_table = $this->get_table_name('clinics');
            $clinic_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $clinics_table WHERE id = %d",
                $data['clinic_id']
            ));
            
            if (!$clinic_exists) {
                $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 400);
            }
        }
        
        // Check if hospital exists (if hospital_id is being updated)
        if (isset($data['hospital_id']) && $data['hospital_id'] != $existing_service->hospital_id) {
            if (!empty($data['hospital_id'])) {
                $hospitals_table = $this->get_table_name('hospitals');
                $hospital_exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $hospitals_table WHERE id = %d",
                    $data['hospital_id']
                ));
                
                if (!$hospital_exists) {
                    $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 400);
                }
            }
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'clinic_id' => 'int',
            'hospital_id' => 'int',
            'name' => 'text',
            'description' => 'textarea',
            'duration_minutes' => 'int',
            'price' => 'float',
            'category' => 'text',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update service
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $service_id),
            array('%d', '%d', '%s', '%s', '%d', '%f', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to update service', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated service
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        $this->format_response($this->format_service_data($service));
    }
    
    /**
     * Delete service
     */
    public function delete_service() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $service_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$service_id) {
            $this->format_error_response(__('Service ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if service exists
        $existing_service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        if (!$existing_service) {
            $this->format_error_response(__('Service not found', 'medx360'), 'service_not_found', 404);
        }
        
        // Delete service (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $service_id), array('%d'));
        
        if ($result === false) {
            $this->format_error_response(__('Failed to delete service', 'medx360'), 'delete_failed', 500);
        }
        
        $this->format_response(array('message' => __('Service deleted successfully', 'medx360')));
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
}
