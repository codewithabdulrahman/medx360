<?php
/**
 * Bookings AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Bookings_AJAX extends MedX360_AJAX_Controller {
    
    public function register_actions() {
        // GET endpoints
        $this->register_ajax_action('get_bookings', array($this, 'get_bookings'));
        $this->register_ajax_action('get_booking', array($this, 'get_booking'));
        $this->register_ajax_action('get_bookings_by_clinic', array($this, 'get_bookings_by_clinic'));
        $this->register_ajax_action('get_bookings_by_doctor', array($this, 'get_bookings_by_doctor'));
        
        // POST endpoints
        $this->register_ajax_action('create_booking', array($this, 'create_booking'));
        
        // PUT endpoints
        $this->register_ajax_action('update_booking', array($this, 'update_booking'));
        $this->register_ajax_action('confirm_booking', array($this, 'confirm_booking'));
        $this->register_ajax_action('cancel_booking', array($this, 'cancel_booking'));
        
        // DELETE endpoints
        $this->register_ajax_action('delete_booking', array($this, 'delete_booking'));
    }
    
    /**
     * Get bookings collection
     */
    public function get_bookings() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $pagination = $this->get_pagination_params();
        $search = $this->get_search_params();
        $filters = $this->get_filter_params();
        
        // Add booking-specific filters
        if (isset($_POST['payment_status']) && !empty($_POST['payment_status'])) {
            $filters['payment_status'] = sanitize_text_field($_POST['payment_status']);
        }
        
        if (isset($_POST['doctor_id']) && !empty($_POST['doctor_id'])) {
            $filters['doctor_id'] = intval($_POST['doctor_id']);
        }
        
        if (isset($_POST['service_id']) && !empty($_POST['service_id'])) {
            $filters['service_id'] = intval($_POST['service_id']);
        }
        
        if (isset($_POST['appointment_date']) && !empty($_POST['appointment_date'])) {
            $filters['appointment_date'] = sanitize_text_field($_POST['appointment_date']);
        }
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('patient_name', 'patient_email', 'patient_phone'));
        
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
        
        // Get bookings
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $bookings = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_bookings = array();
        foreach ($bookings as $booking) {
            $formatted_bookings[] = $this->format_booking_data($booking);
        }
        
        $response = array(
            'data' => $formatted_bookings,
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
     * Get single booking
     */
    public function get_booking() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$booking_id) {
            $this->format_error_response(__('Booking ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$booking) {
            $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Get bookings by clinic
     */
    public function get_bookings_by_clinic() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $clinic_id = isset($_POST['clinic_id']) ? intval($_POST['clinic_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        
        if (!$clinic_id) {
            $this->format_error_response(__('Clinic ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $where_conditions = array('clinic_id = %d');
        $where_values = array($clinic_id);
        
        if ($date) {
            $where_conditions[] = 'appointment_date = %s';
            $where_values[] = $date;
        }
        
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name $where_sql ORDER BY appointment_date, appointment_time ASC",
            $where_values
        ));
        
        $formatted_bookings = array();
        foreach ($bookings as $booking) {
            $formatted_bookings[] = $this->format_booking_data($booking);
        }
        
        $this->format_response($formatted_bookings);
    }
    
    /**
     * Get bookings by doctor
     */
    public function get_bookings_by_doctor() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        
        if (!$doctor_id) {
            $this->format_error_response(__('Doctor ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $where_conditions = array('doctor_id = %d');
        $where_values = array($doctor_id);
        
        if ($date) {
            $where_conditions[] = 'appointment_date = %s';
            $where_values[] = $date;
        }
        
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name $where_sql ORDER BY appointment_date, appointment_time ASC",
            $where_values
        ));
        
        $formatted_bookings = array();
        foreach ($bookings as $booking) {
            $formatted_bookings[] = $this->format_booking_data($booking);
        }
        
        $this->format_response($formatted_bookings);
    }
    
    /**
     * Create booking
     */
    public function create_booking() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $data = $this->get_post_data();
        
        // Validate data
        $errors = MedX360_Validator::validate_booking_data($data);
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
        
        // Check if doctor exists (if provided)
        if (!empty($data['doctor_id'])) {
            $doctors_table = $this->get_table_name('doctors');
            $doctor_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $doctors_table WHERE id = %d",
                $data['doctor_id']
            ));
            
            if (!$doctor_exists) {
                $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 400);
            }
        }
        
        // Check if service exists (if provided)
        if (!empty($data['service_id'])) {
            $services_table = $this->get_table_name('services');
            $service_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $services_table WHERE id = %d",
                $data['service_id']
            ));
            
            if (!$service_exists) {
                $this->format_error_response(__('Service not found', 'medx360'), 'service_not_found', 400);
            }
        }
        
        // Check for time conflicts
        $conflict_check = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE doctor_id = %d AND appointment_date = %s AND appointment_time = %s AND status NOT IN ('cancelled', 'no_show')",
            $data['doctor_id'],
            $data['appointment_date'],
            $data['appointment_time']
        ));
        
        if ($conflict_check > 0) {
            $this->format_error_response(__('Time slot already booked', 'medx360'), 'time_conflict', 400);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'clinic_id' => 'int',
            'hospital_id' => 'int',
            'doctor_id' => 'int',
            'service_id' => 'int',
            'patient_name' => 'text',
            'patient_email' => 'email',
            'patient_phone' => 'text',
            'patient_dob' => 'date',
            'patient_gender' => 'text',
            'appointment_date' => 'date',
            'appointment_time' => 'time',
            'duration_minutes' => 'int',
            'status' => 'text',
            'notes' => 'textarea',
            'total_amount' => 'float',
            'payment_status' => 'text',
            'payment_method' => 'text',
            'payment_reference' => 'text'
        ));
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert booking
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            $this->format_error_response(__('Failed to create booking', 'medx360'), 'create_failed', 500);
        }
        
        $booking_id = $wpdb->insert_id;
        
        // Get created booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Update booking
     */
    public function update_booking() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = $this->get_post_data();
        
        if (!$booking_id) {
            $this->format_error_response(__('Booking ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if booking exists
        $existing_booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$existing_booking) {
            $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_booking_data($data);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check for time conflicts (excluding current booking)
        if (isset($data['doctor_id']) && isset($data['appointment_date']) && isset($data['appointment_time'])) {
            $conflict_check = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE doctor_id = %d AND appointment_date = %s AND appointment_time = %s AND id != %d AND status NOT IN ('cancelled', 'no_show')",
                $data['doctor_id'],
                $data['appointment_date'],
                $data['appointment_time'],
                $booking_id
            ));
            
            if ($conflict_check > 0) {
                $this->format_error_response(__('Time slot already booked', 'medx360'), 'time_conflict', 400);
            }
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'clinic_id' => 'int',
            'hospital_id' => 'int',
            'doctor_id' => 'int',
            'service_id' => 'int',
            'patient_name' => 'text',
            'patient_email' => 'email',
            'patient_phone' => 'text',
            'patient_dob' => 'date',
            'patient_gender' => 'text',
            'appointment_date' => 'date',
            'appointment_time' => 'time',
            'duration_minutes' => 'int',
            'status' => 'text',
            'notes' => 'textarea',
            'total_amount' => 'float',
            'payment_status' => 'text',
            'payment_method' => 'text',
            'payment_reference' => 'text'
        ));
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update booking
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $booking_id),
            array('%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to update booking', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Confirm booking
     */
    public function confirm_booking() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$booking_id) {
            $this->format_error_response(__('Booking ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if booking exists
        $existing_booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$existing_booking) {
            $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        // Update booking status
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => 'confirmed',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $booking_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to confirm booking', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Cancel booking
     */
    public function cancel_booking() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$booking_id) {
            $this->format_error_response(__('Booking ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if booking exists
        $existing_booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$existing_booking) {
            $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        // Update booking status
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => 'cancelled',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $booking_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to cancel booking', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Delete booking
     */
    public function delete_booking() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$booking_id) {
            $this->format_error_response(__('Booking ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if booking exists
        $existing_booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$existing_booking) {
            $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        // Delete booking (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $booking_id), array('%d'));
        
        if ($result === false) {
            $this->format_error_response(__('Failed to delete booking', 'medx360'), 'delete_failed', 500);
        }
        
        $this->format_response(array('message' => __('Booking deleted successfully', 'medx360')));
    }
    
    /**
     * Format booking data for response
     */
    private function format_booking_data($booking) {
        return array(
            'id' => intval($booking->id),
            'clinic_id' => intval($booking->clinic_id),
            'hospital_id' => intval($booking->hospital_id),
            'doctor_id' => intval($booking->doctor_id),
            'service_id' => intval($booking->service_id),
            'patient_name' => $booking->patient_name,
            'patient_email' => $booking->patient_email,
            'patient_phone' => $booking->patient_phone,
            'patient_dob' => $booking->patient_dob,
            'patient_gender' => $booking->patient_gender,
            'appointment_date' => $booking->appointment_date,
            'appointment_time' => $booking->appointment_time,
            'duration_minutes' => intval($booking->duration_minutes),
            'status' => $booking->status,
            'notes' => $booking->notes,
            'total_amount' => floatval($booking->total_amount),
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'payment_reference' => $booking->payment_reference,
            'created_at' => $booking->created_at,
            'updated_at' => $booking->updated_at
        );
    }
}
