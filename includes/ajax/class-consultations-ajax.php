<?php
/**
 * Consultations AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Consultations_AJAX extends MedX360_AJAX_Controller {
    
    public function register_actions() {
        // GET endpoints
        $this->register_ajax_action('get_consultations', array($this, 'get_consultations'));
        $this->register_ajax_action('get_consultation', array($this, 'get_consultation'));
        $this->register_ajax_action('get_consultations_by_booking', array($this, 'get_consultations_by_booking'));
        $this->register_ajax_action('get_consultations_by_doctor', array($this, 'get_consultations_by_doctor'));
        
        // POST endpoints
        $this->register_ajax_action('create_consultation', array($this, 'create_consultation'));
        
        // PUT endpoints
        $this->register_ajax_action('update_consultation', array($this, 'update_consultation'));
        $this->register_ajax_action('complete_consultation', array($this, 'complete_consultation'));
        
        // DELETE endpoints
        $this->register_ajax_action('delete_consultation', array($this, 'delete_consultation'));
    }
    
    /**
     * Get consultations collection
     */
    public function get_consultations() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $pagination = $this->get_pagination_params();
        $search = $this->get_search_params();
        $filters = $this->get_filter_params();
        
        // Add consultation-specific filters
        if (isset($_POST['consultation_type']) && !empty($_POST['consultation_type'])) {
            $filters['consultation_type'] = sanitize_text_field($_POST['consultation_type']);
        }
        
        if (isset($_POST['booking_id']) && !empty($_POST['booking_id'])) {
            $filters['booking_id'] = intval($_POST['booking_id']);
        }
        
        if (isset($_POST['doctor_id']) && !empty($_POST['doctor_id'])) {
            $filters['doctor_id'] = intval($_POST['doctor_id']);
        }
        
        if (isset($_POST['patient_id']) && !empty($_POST['patient_id'])) {
            $filters['patient_id'] = intval($_POST['patient_id']);
        }
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('diagnosis', 'prescription', 'notes'));
        
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
        
        // Get consultations
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $consultations = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_consultations = array();
        foreach ($consultations as $consultation) {
            $formatted_consultations[] = $this->format_consultation_data($consultation);
        }
        
        $response = array(
            'data' => $formatted_consultations,
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
     * Get single consultation
     */
    public function get_consultation() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $consultation_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$consultation_id) {
            $this->format_error_response(__('Consultation ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        if (!$consultation) {
            $this->format_error_response(__('Consultation not found', 'medx360'), 'consultation_not_found', 404);
        }
        
        $this->format_response($this->format_consultation_data($consultation));
    }
    
    /**
     * Get consultations by booking
     */
    public function get_consultations_by_booking() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        
        if (!$booking_id) {
            $this->format_error_response(__('Booking ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $consultations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE booking_id = %d ORDER BY created_at DESC",
            $booking_id
        ));
        
        $formatted_consultations = array();
        foreach ($consultations as $consultation) {
            $formatted_consultations[] = $this->format_consultation_data($consultation);
        }
        
        $this->format_response($formatted_consultations);
    }
    
    /**
     * Get consultations by doctor
     */
    public function get_consultations_by_doctor() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        
        if (!$doctor_id) {
            $this->format_error_response(__('Doctor ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $where_conditions = array('doctor_id = %d');
        $where_values = array($doctor_id);
        
        if ($date) {
            $where_conditions[] = 'DATE(created_at) = %s';
            $where_values[] = $date;
        }
        
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $consultations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name $where_sql ORDER BY created_at DESC",
            $where_values
        ));
        
        $formatted_consultations = array();
        foreach ($consultations as $consultation) {
            $formatted_consultations[] = $this->format_consultation_data($consultation);
        }
        
        $this->format_response($formatted_consultations);
    }
    
    /**
     * Create consultation
     */
    public function create_consultation() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $data = $this->get_post_data();
        
        // Validate required fields
        $required_fields = array('booking_id', 'doctor_id');
        $errors = $this->validate_required_fields($data, $required_fields);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if booking exists
        $bookings_table = $this->get_table_name('bookings');
        $booking_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $bookings_table WHERE id = %d",
            $data['booking_id']
        ));
        
        if (!$booking_exists) {
            $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 400);
        }
        
        // Check if doctor exists
        $doctors_table = $this->get_table_name('doctors');
        $doctor_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $doctors_table WHERE id = %d",
            $data['doctor_id']
        ));
        
        if (!$doctor_exists) {
            $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 400);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'booking_id' => 'int',
            'doctor_id' => 'int',
            'patient_id' => 'int',
            'consultation_type' => 'text',
            'diagnosis' => 'textarea',
            'prescription' => 'textarea',
            'notes' => 'textarea',
            'follow_up_date' => 'date',
            'status' => 'text'
        ));
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert consultation
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            $this->format_error_response(__('Failed to create consultation', 'medx360'), 'create_failed', 500);
        }
        
        $consultation_id = $wpdb->insert_id;
        
        // Get created consultation
        $consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        $this->format_response($this->format_consultation_data($consultation));
    }
    
    /**
     * Update consultation
     */
    public function update_consultation() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $consultation_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = $this->get_post_data();
        
        if (!$consultation_id) {
            $this->format_error_response(__('Consultation ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if consultation exists
        $existing_consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        if (!$existing_consultation) {
            $this->format_error_response(__('Consultation not found', 'medx360'), 'consultation_not_found', 404);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'booking_id' => 'int',
            'doctor_id' => 'int',
            'patient_id' => 'int',
            'consultation_type' => 'text',
            'diagnosis' => 'textarea',
            'prescription' => 'textarea',
            'notes' => 'textarea',
            'follow_up_date' => 'date',
            'status' => 'text'
        ));
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update consultation
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $consultation_id),
            array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to update consultation', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated consultation
        $consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        $this->format_response($this->format_consultation_data($consultation));
    }
    
    /**
     * Complete consultation
     */
    public function complete_consultation() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $consultation_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$consultation_id) {
            $this->format_error_response(__('Consultation ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if consultation exists
        $existing_consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        if (!$existing_consultation) {
            $this->format_error_response(__('Consultation not found', 'medx360'), 'consultation_not_found', 404);
        }
        
        // Update consultation status
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => 'completed',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $consultation_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to complete consultation', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated consultation
        $consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        $this->format_response($this->format_consultation_data($consultation));
    }
    
    /**
     * Delete consultation
     */
    public function delete_consultation() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $consultation_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$consultation_id) {
            $this->format_error_response(__('Consultation ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if consultation exists
        $existing_consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        if (!$existing_consultation) {
            $this->format_error_response(__('Consultation not found', 'medx360'), 'consultation_not_found', 404);
        }
        
        // Delete consultation
        $result = $wpdb->delete($table_name, array('id' => $consultation_id), array('%d'));
        
        if ($result === false) {
            $this->format_error_response(__('Failed to delete consultation', 'medx360'), 'delete_failed', 500);
        }
        
        $this->format_response(array('message' => __('Consultation deleted successfully', 'medx360')));
    }
    
    /**
     * Format consultation data for response
     */
    private function format_consultation_data($consultation) {
        return array(
            'id' => intval($consultation->id),
            'booking_id' => intval($consultation->booking_id),
            'doctor_id' => intval($consultation->doctor_id),
            'patient_id' => intval($consultation->patient_id),
            'consultation_type' => $consultation->consultation_type,
            'diagnosis' => $consultation->diagnosis,
            'prescription' => $consultation->prescription,
            'notes' => $consultation->notes,
            'follow_up_date' => $consultation->follow_up_date,
            'status' => $consultation->status,
            'created_at' => $consultation->created_at,
            'updated_at' => $consultation->updated_at
        );
    }
}
