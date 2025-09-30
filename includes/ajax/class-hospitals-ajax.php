<?php
/**
 * Hospitals AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Hospitals_AJAX extends MedX360_AJAX_Controller {
    
    public function register_actions() {
        // GET endpoints
        $this->register_ajax_action('get_hospitals', array($this, 'get_hospitals'));
        $this->register_ajax_action('get_hospital', array($this, 'get_hospital'));
        $this->register_ajax_action('get_hospital_by_slug', array($this, 'get_hospital_by_slug'));
        $this->register_ajax_action('get_hospitals_by_clinic', array($this, 'get_hospitals_by_clinic'));
        
        // POST endpoints
        $this->register_ajax_action('create_hospital', array($this, 'create_hospital'));
        
        // PUT endpoints
        $this->register_ajax_action('update_hospital', array($this, 'update_hospital'));
        
        // DELETE endpoints
        $this->register_ajax_action('delete_hospital', array($this, 'delete_hospital'));
    }
    
    /**
     * Get hospitals collection
     */
    public function get_hospitals() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $pagination = $this->get_pagination_params();
        $search = $this->get_search_params();
        $filters = $this->get_filter_params();
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('name', 'description', 'city', 'state'));
        
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
        
        // Get hospitals
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $hospitals = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_hospitals = array();
        foreach ($hospitals as $hospital) {
            $formatted_hospitals[] = $this->format_hospital_data($hospital);
        }
        
        $response = array(
            'data' => $formatted_hospitals,
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
     * Get single hospital
     */
    public function get_hospital() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $hospital_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$hospital_id) {
            $this->format_error_response(__('Hospital ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        if (!$hospital) {
            $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 404);
        }
        
        $this->format_response($this->format_hospital_data($hospital));
    }
    
    /**
     * Get hospital by slug
     */
    public function get_hospital_by_slug() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
        
        if (!$slug) {
            $this->format_error_response(__('Hospital slug is required', 'medx360'), 'validation_error', 400);
        }
        
        $hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE slug = %s",
            $slug
        ));
        
        if (!$hospital) {
            $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 404);
        }
        
        $this->format_response($this->format_hospital_data($hospital));
    }
    
    /**
     * Get hospitals by clinic
     */
    public function get_hospitals_by_clinic() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $clinic_id = isset($_POST['clinic_id']) ? intval($_POST['clinic_id']) : 0;
        
        if (!$clinic_id) {
            $this->format_error_response(__('Clinic ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $hospitals = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE clinic_id = %d AND status = 'active' ORDER BY name ASC",
            $clinic_id
        ));
        
        $formatted_hospitals = array();
        foreach ($hospitals as $hospital) {
            $formatted_hospitals[] = $this->format_hospital_data($hospital);
        }
        
        $this->format_response($formatted_hospitals);
    }
    
    /**
     * Create hospital
     */
    public function create_hospital() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $data = $this->get_post_data();
        
        // Normalize website URL (add protocol if missing)
        if (!empty($data['website']) && !preg_match('/^https?:\/\//', $data['website'])) {
            $data['website'] = 'https://' . $data['website'];
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_hospital_data($data);
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
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'clinic_id' => 'int',
            'name' => 'text',
            'slug' => 'text',
            'description' => 'textarea',
            'address' => 'textarea',
            'city' => 'text',
            'state' => 'text',
            'country' => 'text',
            'postal_code' => 'text',
            'phone' => 'text',
            'email' => 'email',
            'website' => 'url',
            'logo_url' => 'url',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Check if slug already exists
        $existing_slug = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE slug = %s",
            $sanitized_data['slug']
        ));
        
        if ($existing_slug) {
            $this->format_error_response(__('Slug already exists', 'medx360'), 'slug_exists', 400);
        }
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert hospital
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            $this->format_error_response(__('Failed to create hospital', 'medx360'), 'create_failed', 500);
        }
        
        $hospital_id = $wpdb->insert_id;
        
        // Get created hospital
        $hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        $this->format_response($this->format_hospital_data($hospital));
    }
    
    /**
     * Update hospital
     */
    public function update_hospital() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $hospital_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = $this->get_post_data();
        
        if (!$hospital_id) {
            $this->format_error_response(__('Hospital ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if hospital exists
        $existing_hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        if (!$existing_hospital) {
            $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 404);
        }
        
        // Normalize website URL (add protocol if missing)
        if (!empty($data['website']) && !preg_match('/^https?:\/\//', $data['website'])) {
            $data['website'] = 'https://' . $data['website'];
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_hospital_data($data);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if clinic exists (if clinic_id is being updated)
        if (isset($data['clinic_id']) && $data['clinic_id'] != $existing_hospital->clinic_id) {
            $clinics_table = $this->get_table_name('clinics');
            $clinic_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $clinics_table WHERE id = %d",
                $data['clinic_id']
            ));
            
            if (!$clinic_exists) {
                $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 400);
            }
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'clinic_id' => 'int',
            'name' => 'text',
            'slug' => 'text',
            'description' => 'textarea',
            'address' => 'textarea',
            'city' => 'text',
            'state' => 'text',
            'country' => 'text',
            'postal_code' => 'text',
            'phone' => 'text',
            'email' => 'email',
            'website' => 'url',
            'logo_url' => 'url',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Check if slug already exists (excluding current hospital)
        if (isset($sanitized_data['slug']) && $sanitized_data['slug'] !== $existing_hospital->slug) {
            $existing_slug = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE slug = %s AND id != %d",
                $sanitized_data['slug'],
                $hospital_id
            ));
            
            if ($existing_slug) {
                $this->format_error_response(__('Slug already exists', 'medx360'), 'slug_exists', 400);
            }
        }
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update hospital
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $hospital_id),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to update hospital', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated hospital
        $hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        $this->format_response($this->format_hospital_data($hospital));
    }
    
    /**
     * Delete hospital
     */
    public function delete_hospital() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $hospital_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$hospital_id) {
            $this->format_error_response(__('Hospital ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if hospital exists
        $existing_hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        if (!$existing_hospital) {
            $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 404);
        }
        
        // Delete hospital (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $hospital_id), array('%d'));
        
        if ($result === false) {
            $this->format_error_response(__('Failed to delete hospital', 'medx360'), 'delete_failed', 500);
        }
        
        $this->format_response(array('message' => __('Hospital deleted successfully', 'medx360')));
    }
    
    /**
     * Format hospital data for response
     */
    private function format_hospital_data($hospital) {
        $settings = !empty($hospital->settings) ? json_decode($hospital->settings, true) : array();
        
        return array(
            'id' => intval($hospital->id),
            'clinic_id' => intval($hospital->clinic_id),
            'name' => $hospital->name,
            'slug' => $hospital->slug,
            'description' => $hospital->description,
            'address' => $hospital->address,
            'city' => $hospital->city,
            'state' => $hospital->state,
            'country' => $hospital->country,
            'postal_code' => $hospital->postal_code,
            'phone' => $hospital->phone,
            'email' => $hospital->email,
            'website' => $hospital->website,
            'logo_url' => $hospital->logo_url,
            'status' => $hospital->status,
            'settings' => $settings,
            'created_at' => $hospital->created_at,
            'updated_at' => $hospital->updated_at
        );
    }
}
