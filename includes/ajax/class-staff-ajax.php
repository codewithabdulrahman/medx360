<?php
/**
 * Staff AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Staff_AJAX extends MedX360_AJAX_Controller {
    
    public function register_actions() {
        // GET endpoints
        $this->register_ajax_action('get_staff', array($this, 'get_staff'));
        $this->register_ajax_action('get_staff_member', array($this, 'get_staff_member'));
        $this->register_ajax_action('get_staff_by_clinic', array($this, 'get_staff_by_clinic'));
        
        // POST endpoints
        $this->register_ajax_action('create_staff', array($this, 'create_staff'));
        
        // PUT endpoints
        $this->register_ajax_action('update_staff', array($this, 'update_staff'));
        
        // DELETE endpoints
        $this->register_ajax_action('delete_staff', array($this, 'delete_staff'));
    }
    
    /**
     * Get staff collection
     */
    public function get_staff() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $pagination = $this->get_pagination_params();
        $search = $this->get_search_params();
        $filters = $this->get_filter_params();
        
        // Add staff-specific filters
        if (isset($_POST['role']) && !empty($_POST['role'])) {
            $filters['role'] = sanitize_text_field($_POST['role']);
        }
        
        if (isset($_POST['department']) && !empty($_POST['department'])) {
            $filters['department'] = sanitize_text_field($_POST['department']);
        }
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('first_name', 'last_name', 'email', 'role', 'department'));
        
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
        
        // Get staff
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $staff = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_staff = array();
        foreach ($staff as $staff_member) {
            $formatted_staff[] = $this->format_staff_data($staff_member);
        }
        
        $response = array(
            'data' => $formatted_staff,
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
     * Get single staff member
     */
    public function get_staff_member() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $staff_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$staff_id) {
            $this->format_error_response(__('Staff ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $staff_member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        if (!$staff_member) {
            $this->format_error_response(__('Staff member not found', 'medx360'), 'staff_not_found', 404);
        }
        
        $this->format_response($this->format_staff_data($staff_member));
    }
    
    /**
     * Get staff by clinic
     */
    public function get_staff_by_clinic() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $clinic_id = isset($_POST['clinic_id']) ? intval($_POST['clinic_id']) : 0;
        
        if (!$clinic_id) {
            $this->format_error_response(__('Clinic ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $staff = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE clinic_id = %d AND status = 'active' ORDER BY first_name, last_name ASC",
            $clinic_id
        ));
        
        $formatted_staff = array();
        foreach ($staff as $staff_member) {
            $formatted_staff[] = $this->format_staff_data($staff_member);
        }
        
        $this->format_response($formatted_staff);
    }
    
    /**
     * Create staff member
     */
    public function create_staff() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $data = $this->get_post_data();
        
        // Validate data
        $errors = MedX360_Validator::validate_staff_data($data);
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
            'user_id' => 'int',
            'first_name' => 'text',
            'last_name' => 'text',
            'email' => 'email',
            'phone' => 'text',
            'role' => 'text',
            'department' => 'text',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert staff member
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            $this->format_error_response(__('Failed to create staff member', 'medx360'), 'create_failed', 500);
        }
        
        $staff_id = $wpdb->insert_id;
        
        // Get created staff member
        $staff_member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        $this->format_response($this->format_staff_data($staff_member));
    }
    
    /**
     * Update staff member
     */
    public function update_staff() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $staff_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = $this->get_post_data();
        
        if (!$staff_id) {
            $this->format_error_response(__('Staff ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if staff member exists
        $existing_staff = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        if (!$existing_staff) {
            $this->format_error_response(__('Staff member not found', 'medx360'), 'staff_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_staff_data($data);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if clinic exists (if clinic_id is being updated)
        if (isset($data['clinic_id']) && $data['clinic_id'] != $existing_staff->clinic_id) {
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
        if (isset($data['hospital_id']) && $data['hospital_id'] != $existing_staff->hospital_id) {
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
            'user_id' => 'int',
            'first_name' => 'text',
            'last_name' => 'text',
            'email' => 'email',
            'phone' => 'text',
            'role' => 'text',
            'department' => 'text',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update staff member
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $staff_id),
            array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to update staff member', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated staff member
        $staff_member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        $this->format_response($this->format_staff_data($staff_member));
    }
    
    /**
     * Delete staff member
     */
    public function delete_staff() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $staff_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$staff_id) {
            $this->format_error_response(__('Staff ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if staff member exists
        $existing_staff = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        if (!$existing_staff) {
            $this->format_error_response(__('Staff member not found', 'medx360'), 'staff_not_found', 404);
        }
        
        // Delete staff member (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $staff_id), array('%d'));
        
        if ($result === false) {
            $this->format_error_response(__('Failed to delete staff member', 'medx360'), 'delete_failed', 500);
        }
        
        $this->format_response(array('message' => __('Staff member deleted successfully', 'medx360')));
    }
    
    /**
     * Format staff data for response
     */
    private function format_staff_data($staff) {
        $settings = !empty($staff->settings) ? json_decode($staff->settings, true) : array();
        
        return array(
            'id' => intval($staff->id),
            'clinic_id' => intval($staff->clinic_id),
            'hospital_id' => intval($staff->hospital_id),
            'user_id' => intval($staff->user_id),
            'first_name' => $staff->first_name,
            'last_name' => $staff->last_name,
            'full_name' => $staff->first_name . ' ' . $staff->last_name,
            'email' => $staff->email,
            'phone' => $staff->phone,
            'role' => $staff->role,
            'department' => $staff->department,
            'status' => $staff->status,
            'settings' => $settings,
            'created_at' => $staff->created_at,
            'updated_at' => $staff->updated_at
        );
    }
}
