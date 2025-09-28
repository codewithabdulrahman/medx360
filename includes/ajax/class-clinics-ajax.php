<?php
/**
 * Clinics AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Clinics_AJAX extends MedX360_AJAX_Controller {
    
    public function register_actions() {
        // GET endpoints
        $this->register_ajax_action('get_clinics', array($this, 'get_clinics'));
        $this->register_ajax_action('get_clinic', array($this, 'get_clinic'));
        $this->register_ajax_action('get_clinic_by_slug', array($this, 'get_clinic_by_slug'));
        
        // POST endpoints
        $this->register_ajax_action('create_clinic', array($this, 'create_clinic'));
        
        // PUT endpoints
        $this->register_ajax_action('update_clinic', array($this, 'update_clinic'));
        
        // DELETE endpoints
        $this->register_ajax_action('delete_clinic', array($this, 'delete_clinic'));
    }
    
    /**
     * Get clinics collection
     */
    public function get_clinics() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
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
        
        // Get clinics
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $clinics = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_clinics = array();
        foreach ($clinics as $clinic) {
            $formatted_clinics[] = $this->format_clinic_data($clinic);
        }
        
        $response = array(
            'data' => $formatted_clinics,
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
     * Get single clinic
     */
    public function get_clinic() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $clinic_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$clinic_id) {
            $this->format_error_response(__('Clinic ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        if (!$clinic) {
            $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 404);
        }
        
        $this->format_response($this->format_clinic_data($clinic));
    }
    
    /**
     * Get clinic by slug
     */
    public function get_clinic_by_slug() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
        
        if (!$slug) {
            $this->format_error_response(__('Clinic slug is required', 'medx360'), 'validation_error', 400);
        }
        
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE slug = %s",
            $slug
        ));
        
        if (!$clinic) {
            $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 404);
        }
        
        $this->format_response($this->format_clinic_data($clinic));
    }
    
    /**
     * Create clinic
     */
    public function create_clinic() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $data = $this->get_post_data();
        
        // Validate data
        $errors = MedX360_Validator::validate_clinic_data($data);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
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
        
        $this->format_response($this->format_clinic_data($clinic));
    }
    
    /**
     * Update clinic
     */
    public function update_clinic() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $clinic_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = $this->get_post_data();
        
        if (!$clinic_id) {
            $this->format_error_response(__('Clinic ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if clinic exists
        $existing_clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        if (!$existing_clinic) {
            $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_clinic_data($data);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
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
        
        // Check if slug already exists (excluding current clinic)
        if (isset($sanitized_data['slug']) && $sanitized_data['slug'] !== $existing_clinic->slug) {
            $existing_slug = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE slug = %s AND id != %d",
                $sanitized_data['slug'],
                $clinic_id
            ));
            
            if ($existing_slug) {
                $this->format_error_response(__('Slug already exists', 'medx360'), 'slug_exists', 400);
            }
        }
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update clinic
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $clinic_id),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to update clinic', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated clinic
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        $this->format_response($this->format_clinic_data($clinic));
    }
    
    /**
     * Delete clinic
     */
    public function delete_clinic() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $clinic_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$clinic_id) {
            $this->format_error_response(__('Clinic ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if clinic exists
        $existing_clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        if (!$existing_clinic) {
            $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 404);
        }
        
        // Delete clinic (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $clinic_id), array('%d'));
        
        if ($result === false) {
            $this->format_error_response(__('Failed to delete clinic', 'medx360'), 'delete_failed', 500);
        }
        
        $this->format_response(array('message' => __('Clinic deleted successfully', 'medx360')));
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
}
