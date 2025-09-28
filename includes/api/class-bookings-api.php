<?php
/**
 * Bookings API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Bookings_API extends MedX360_API_Controller {
    
    protected $rest_base = 'bookings';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_bookings'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_booking'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_booking_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_booking'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the booking', 'medx360'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_booking'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_booking_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_booking'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/clinic/(?P<clinic_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_bookings_by_clinic'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'clinic_id' => array(
                    'description' => __('Clinic ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/doctor/(?P<doctor_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_bookings_by_doctor'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'doctor_id' => array(
                    'description' => __('Doctor ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/confirm', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'confirm_booking'),
            'permission_callback' => array($this, 'check_permission')
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/cancel', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'cancel_booking'),
            'permission_callback' => array($this, 'check_permission')
        ));
    }
    
    /**
     * Get bookings collection
     */
    public function get_bookings($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $pagination = $this->get_pagination_params($request);
        $search = $this->get_search_params($request);
        $filters = $this->get_filter_params($request);
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('patient_name', 'patient_email', 'patient_phone', 'notes'));
        
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
        
        return $this->format_response($response);
    }
    
    /**
     * Get single booking
     */
    public function get_booking($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = $request->get_param('id');
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$booking) {
            return $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        return $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Get bookings by clinic
     */
    public function get_bookings_by_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $clinic_id = $request->get_param('clinic_id');
        $date = $request->get_param('date');
        
        $where_conditions = array('clinic_id = %d');
        $where_values = array($clinic_id);
        
        if ($date) {
            $where_conditions[] = 'appointment_date = %s';
            $where_values[] = $date;
        }
        
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name $where_sql ORDER BY appointment_date, appointment_time",
            $where_values
        ));
        
        $formatted_bookings = array();
        foreach ($bookings as $booking) {
            $formatted_bookings[] = $this->format_booking_data($booking);
        }
        
        return $this->format_response($formatted_bookings);
    }
    
    /**
     * Get bookings by doctor
     */
    public function get_bookings_by_doctor($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $doctor_id = $request->get_param('doctor_id');
        $date = $request->get_param('date');
        
        $where_conditions = array('doctor_id = %d');
        $where_values = array($doctor_id);
        
        if ($date) {
            $where_conditions[] = 'appointment_date = %s';
            $where_values[] = $date;
        }
        
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name $where_sql ORDER BY appointment_date, appointment_time",
            $where_values
        ));
        
        $formatted_bookings = array();
        foreach ($bookings as $booking) {
            $formatted_bookings[] = $this->format_booking_data($booking);
        }
        
        return $this->format_response($formatted_bookings);
    }
    
    /**
     * Create booking
     */
    public function create_booking($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $data = $request->get_json_params();
        
        // Validate data
        $errors = MedX360_Validator::validate_booking_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if clinic exists
        $clinics_table = $this->get_table_name('clinics');
        $clinic_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $clinics_table WHERE id = %d",
            $data['clinic_id']
        ));
        
        if (!$clinic_exists) {
            return $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 400);
        }
        
        // Check if hospital exists (if provided)
        if (!empty($data['hospital_id'])) {
            $hospitals_table = $this->get_table_name('hospitals');
            $hospital_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $hospitals_table WHERE id = %d",
                $data['hospital_id']
            ));
            
            if (!$hospital_exists) {
                return $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 400);
            }
        }
        
        // Check if doctor exists (if provided)
        if (!empty($data['doctor_id'])) {
            $doctors_table = $this->get_table_name('doctors');
            $doctor_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $doctors_table WHERE id = %d",
                $data['doctor_id']
            ));
            
            if (!$doctor_exists) {
                return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 400);
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
                return $this->format_error_response(__('Service not found', 'medx360'), 'service_not_found', 400);
            }
        }
        
        // Check for conflicting appointments
        $conflict_sql = "SELECT COUNT(*) FROM $table_name WHERE 
            clinic_id = %d AND 
            appointment_date = %s AND 
            appointment_time = %s AND 
            status IN ('pending', 'confirmed')";
        
        $conflict_values = array($data['clinic_id'], $data['appointment_date'], $data['appointment_time']);
        
        if (!empty($data['doctor_id'])) {
            $conflict_sql .= " AND doctor_id = %d";
            $conflict_values[] = $data['doctor_id'];
        }
        
        $conflicts = $wpdb->get_var($wpdb->prepare($conflict_sql, $conflict_values));
        
        if ($conflicts > 0) {
            return $this->format_error_response(__('Time slot is already booked', 'medx360'), 'time_conflict', 400);
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
            return $this->format_error_response(__('Failed to create booking', 'medx360'), 'create_failed', 500);
        }
        
        $booking_id = $wpdb->insert_id;
        
        // Get created booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        return $this->format_response($this->format_booking_data($booking), 201);
    }
    
    /**
     * Update booking
     */
    public function update_booking($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if booking exists
        $existing_booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$existing_booking) {
            return $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_booking_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check for conflicting appointments (excluding current booking)
        if (isset($data['appointment_date']) || isset($data['appointment_time']) || isset($data['doctor_id'])) {
            $appointment_date = isset($data['appointment_date']) ? $data['appointment_date'] : $existing_booking->appointment_date;
            $appointment_time = isset($data['appointment_time']) ? $data['appointment_time'] : $existing_booking->appointment_time;
            $doctor_id = isset($data['doctor_id']) ? $data['doctor_id'] : $existing_booking->doctor_id;
            $clinic_id = isset($data['clinic_id']) ? $data['clinic_id'] : $existing_booking->clinic_id;
            
            $conflict_sql = "SELECT COUNT(*) FROM $table_name WHERE 
                clinic_id = %d AND 
                appointment_date = %s AND 
                appointment_time = %s AND 
                status IN ('pending', 'confirmed') AND
                id != %d";
            
            $conflict_values = array($clinic_id, $appointment_date, $appointment_time, $booking_id);
            
            if (!empty($doctor_id)) {
                $conflict_sql .= " AND doctor_id = %d";
                $conflict_values[] = $doctor_id;
            }
            
            $conflicts = $wpdb->get_var($wpdb->prepare($conflict_sql, $conflict_values));
            
            if ($conflicts > 0) {
                return $this->format_error_response(__('Time slot is already booked', 'medx360'), 'time_conflict', 400);
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
            return $this->format_error_response(__('Failed to update booking', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        return $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Confirm booking
     */
    public function confirm_booking($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = $request->get_param('id');
        
        // Check if booking exists
        $existing_booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$existing_booking) {
            return $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        // Update booking status
        $result = $wpdb->update(
            $table_name,
            array('status' => 'confirmed', 'updated_at' => current_time('mysql')),
            array('id' => $booking_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to confirm booking', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        return $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Cancel booking
     */
    public function cancel_booking($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = $request->get_param('id');
        
        // Check if booking exists
        $existing_booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$existing_booking) {
            return $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        // Update booking status
        $result = $wpdb->update(
            $table_name,
            array('status' => 'cancelled', 'updated_at' => current_time('mysql')),
            array('id' => $booking_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to cancel booking', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        return $this->format_response($this->format_booking_data($booking));
    }
    
    /**
     * Delete booking
     */
    public function delete_booking($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('bookings');
        $booking_id = $request->get_param('id');
        
        // Check if booking exists
        $existing_booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $booking_id
        ));
        
        if (!$existing_booking) {
            return $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 404);
        }
        
        // Delete booking (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $booking_id), array('%d'));
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to delete booking', 'medx360'), 'delete_failed', 500);
        }
        
        return $this->format_response(array('message' => __('Booking deleted successfully', 'medx360')));
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
    
    /**
     * Get collection parameters
     */
    public function get_collection_params() {
        return array(
            'page' => array(
                'description' => __('Current page of the collection', 'medx360'),
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1
            ),
            'per_page' => array(
                'description' => __('Maximum number of items to be returned', 'medx360'),
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100
            ),
            'search' => array(
                'description' => __('Limit results to those matching a string', 'medx360'),
                'type' => 'string'
            ),
            'orderby' => array(
                'description' => __('Sort collection by object attribute', 'medx360'),
                'type' => 'string',
                'default' => 'id',
                'enum' => array('id', 'patient_name', 'appointment_date', 'appointment_time', 'status', 'created_at', 'updated_at')
            ),
            'order' => array(
                'description' => __('Order sort attribute ascending or descending', 'medx360'),
                'type' => 'string',
                'default' => 'DESC',
                'enum' => array('ASC', 'DESC')
            ),
            'status' => array(
                'description' => __('Filter by status', 'medx360'),
                'type' => 'string',
                'enum' => array('pending', 'confirmed', 'cancelled', 'completed', 'no_show')
            ),
            'payment_status' => array(
                'description' => __('Filter by payment status', 'medx360'),
                'type' => 'string',
                'enum' => array('pending', 'paid', 'refunded', 'failed')
            ),
            'clinic_id' => array(
                'description' => __('Filter by clinic ID', 'medx360'),
                'type' => 'integer'
            ),
            'hospital_id' => array(
                'description' => __('Filter by hospital ID', 'medx360'),
                'type' => 'integer'
            ),
            'doctor_id' => array(
                'description' => __('Filter by doctor ID', 'medx360'),
                'type' => 'integer'
            ),
            'service_id' => array(
                'description' => __('Filter by service ID', 'medx360'),
                'type' => 'integer'
            ),
            'appointment_date' => array(
                'description' => __('Filter by appointment date', 'medx360'),
                'type' => 'string',
                'format' => 'date'
            )
        );
    }
    
    /**
     * Get booking parameters
     */
    public function get_booking_params() {
        return array(
            'clinic_id' => array(
                'description' => __('Clinic ID', 'medx360'),
                'type' => 'integer',
                'required' => true
            ),
            'hospital_id' => array(
                'description' => __('Hospital ID', 'medx360'),
                'type' => 'integer'
            ),
            'doctor_id' => array(
                'description' => __('Doctor ID', 'medx360'),
                'type' => 'integer'
            ),
            'service_id' => array(
                'description' => __('Service ID', 'medx360'),
                'type' => 'integer'
            ),
            'patient_name' => array(
                'description' => __('Patient name', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'patient_email' => array(
                'description' => __('Patient email', 'medx360'),
                'type' => 'string',
                'format' => 'email',
                'required' => true
            ),
            'patient_phone' => array(
                'description' => __('Patient phone', 'medx360'),
                'type' => 'string'
            ),
            'patient_dob' => array(
                'description' => __('Patient date of birth', 'medx360'),
                'type' => 'string',
                'format' => 'date'
            ),
            'patient_gender' => array(
                'description' => __('Patient gender', 'medx360'),
                'type' => 'string',
                'enum' => array('male', 'female', 'other')
            ),
            'appointment_date' => array(
                'description' => __('Appointment date', 'medx360'),
                'type' => 'string',
                'format' => 'date',
                'required' => true
            ),
            'appointment_time' => array(
                'description' => __('Appointment time', 'medx360'),
                'type' => 'string',
                'format' => 'time',
                'required' => true
            ),
            'duration_minutes' => array(
                'description' => __('Duration in minutes', 'medx360'),
                'type' => 'integer',
                'default' => 30,
                'minimum' => 1,
                'maximum' => 1440
            ),
            'status' => array(
                'description' => __('Booking status', 'medx360'),
                'type' => 'string',
                'enum' => array('pending', 'confirmed', 'cancelled', 'completed', 'no_show'),
                'default' => 'pending'
            ),
            'notes' => array(
                'description' => __('Booking notes', 'medx360'),
                'type' => 'string'
            ),
            'total_amount' => array(
                'description' => __('Total amount', 'medx360'),
                'type' => 'number',
                'minimum' => 0
            ),
            'payment_status' => array(
                'description' => __('Payment status', 'medx360'),
                'type' => 'string',
                'enum' => array('pending', 'paid', 'refunded', 'failed'),
                'default' => 'pending'
            ),
            'payment_method' => array(
                'description' => __('Payment method', 'medx360'),
                'type' => 'string'
            ),
            'payment_reference' => array(
                'description' => __('Payment reference', 'medx360'),
                'type' => 'string'
            )
        );
    }
}
