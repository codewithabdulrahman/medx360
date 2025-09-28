<?php
/**
 * Consultations API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Consultations_API extends MedX360_API_Controller {
    
    protected $rest_base = 'consultations';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_consultations'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_consultation'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_consultation_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_consultation'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the consultation', 'medx360'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_consultation'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_consultation_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_consultation'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/booking/(?P<booking_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_consultations_by_booking'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'booking_id' => array(
                    'description' => __('Booking ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/doctor/(?P<doctor_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_consultations_by_doctor'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'doctor_id' => array(
                    'description' => __('Doctor ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/complete', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'complete_consultation'),
            'permission_callback' => array($this, 'check_permission')
        ));
    }
    
    /**
     * Get consultations collection
     */
    public function get_consultations($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $pagination = $this->get_pagination_params($request);
        $search = $this->get_search_params($request);
        $filters = $this->get_filter_params($request);
        
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
        
        return $this->format_response($response);
    }
    
    /**
     * Get single consultation
     */
    public function get_consultation($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $consultation_id = $request->get_param('id');
        
        $consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        if (!$consultation) {
            return $this->format_error_response(__('Consultation not found', 'medx360'), 'consultation_not_found', 404);
        }
        
        return $this->format_response($this->format_consultation_data($consultation));
    }
    
    /**
     * Get consultations by booking
     */
    public function get_consultations_by_booking($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $booking_id = $request->get_param('booking_id');
        
        $consultations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE booking_id = %d ORDER BY created_at DESC",
            $booking_id
        ));
        
        $formatted_consultations = array();
        foreach ($consultations as $consultation) {
            $formatted_consultations[] = $this->format_consultation_data($consultation);
        }
        
        return $this->format_response($formatted_consultations);
    }
    
    /**
     * Get consultations by doctor
     */
    public function get_consultations_by_doctor($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $doctor_id = $request->get_param('doctor_id');
        $date = $request->get_param('date');
        
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
        
        return $this->format_response($formatted_consultations);
    }
    
    /**
     * Create consultation
     */
    public function create_consultation($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $data = $request->get_json_params();
        
        // Validate data
        $errors = MedX360_Validator::validate_consultation_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if booking exists
        $bookings_table = $this->get_table_name('bookings');
        $booking_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $bookings_table WHERE id = %d",
            $data['booking_id']
        ));
        
        if (!$booking_exists) {
            return $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 400);
        }
        
        // Check if doctor exists
        $doctors_table = $this->get_table_name('doctors');
        $doctor_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $doctors_table WHERE id = %d",
            $data['doctor_id']
        ));
        
        if (!$doctor_exists) {
            return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 400);
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
            return $this->format_error_response(__('Failed to create consultation', 'medx360'), 'create_failed', 500);
        }
        
        $consultation_id = $wpdb->insert_id;
        
        // Get created consultation
        $consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        return $this->format_response($this->format_consultation_data($consultation), 201);
    }
    
    /**
     * Update consultation
     */
    public function update_consultation($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $consultation_id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if consultation exists
        $existing_consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        if (!$existing_consultation) {
            return $this->format_error_response(__('Consultation not found', 'medx360'), 'consultation_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_consultation_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if booking exists (if booking_id is being updated)
        if (isset($data['booking_id']) && $data['booking_id'] != $existing_consultation->booking_id) {
            $bookings_table = $this->get_table_name('bookings');
            $booking_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $bookings_table WHERE id = %d",
                $data['booking_id']
            ));
            
            if (!$booking_exists) {
                return $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 400);
            }
        }
        
        // Check if doctor exists (if doctor_id is being updated)
        if (isset($data['doctor_id']) && $data['doctor_id'] != $existing_consultation->doctor_id) {
            $doctors_table = $this->get_table_name('doctors');
            $doctor_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $doctors_table WHERE id = %d",
                $data['doctor_id']
            ));
            
            if (!$doctor_exists) {
                return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 400);
            }
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
            return $this->format_error_response(__('Failed to update consultation', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated consultation
        $consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        return $this->format_response($this->format_consultation_data($consultation));
    }
    
    /**
     * Complete consultation
     */
    public function complete_consultation($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $consultation_id = $request->get_param('id');
        
        // Check if consultation exists
        $existing_consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        if (!$existing_consultation) {
            return $this->format_error_response(__('Consultation not found', 'medx360'), 'consultation_not_found', 404);
        }
        
        // Update consultation status
        $result = $wpdb->update(
            $table_name,
            array('status' => 'completed', 'updated_at' => current_time('mysql')),
            array('id' => $consultation_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to complete consultation', 'medx360'), 'update_failed', 500);
        }
        
        // Update booking status to completed
        $bookings_table = $this->get_table_name('bookings');
        $wpdb->update(
            $bookings_table,
            array(
                'status' => 'completed',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $existing_consultation->booking_id),
            array('%s', '%s'),
            array('%d')
        );
        
        // Get updated consultation
        $consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        return $this->format_response($this->format_consultation_data($consultation));
    }
    
    /**
     * Delete consultation
     */
    public function delete_consultation($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('consultations');
        $consultation_id = $request->get_param('id');
        
        // Check if consultation exists
        $existing_consultation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $consultation_id
        ));
        
        if (!$existing_consultation) {
            return $this->format_error_response(__('Consultation not found', 'medx360'), 'consultation_not_found', 404);
        }
        
        // Delete consultation
        $result = $wpdb->delete($table_name, array('id' => $consultation_id), array('%d'));
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to delete consultation', 'medx360'), 'delete_failed', 500);
        }
        
        return $this->format_response(array('message' => __('Consultation deleted successfully', 'medx360')));
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
                'enum' => array('id', 'status', 'consultation_type', 'created_at', 'updated_at')
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
                'enum' => array('scheduled', 'in_progress', 'completed', 'cancelled')
            ),
            'consultation_type' => array(
                'description' => __('Filter by consultation type', 'medx360'),
                'type' => 'string',
                'enum' => array('in_person', 'video', 'phone')
            ),
            'booking_id' => array(
                'description' => __('Filter by booking ID', 'medx360'),
                'type' => 'integer'
            ),
            'doctor_id' => array(
                'description' => __('Filter by doctor ID', 'medx360'),
                'type' => 'integer'
            ),
            'patient_id' => array(
                'description' => __('Filter by patient ID', 'medx360'),
                'type' => 'integer'
            )
        );
    }
    
    /**
     * Get consultation parameters
     */
    public function get_consultation_params() {
        return array(
            'booking_id' => array(
                'description' => __('Booking ID', 'medx360'),
                'type' => 'integer',
                'required' => true
            ),
            'doctor_id' => array(
                'description' => __('Doctor ID', 'medx360'),
                'type' => 'integer',
                'required' => true
            ),
            'patient_id' => array(
                'description' => __('Patient ID', 'medx360'),
                'type' => 'integer'
            ),
            'consultation_type' => array(
                'description' => __('Consultation type', 'medx360'),
                'type' => 'string',
                'enum' => array('in_person', 'video', 'phone'),
                'default' => 'in_person'
            ),
            'diagnosis' => array(
                'description' => __('Diagnosis', 'medx360'),
                'type' => 'string'
            ),
            'prescription' => array(
                'description' => __('Prescription', 'medx360'),
                'type' => 'string'
            ),
            'notes' => array(
                'description' => __('Consultation notes', 'medx360'),
                'type' => 'string'
            ),
            'follow_up_date' => array(
                'description' => __('Follow-up date', 'medx360'),
                'type' => 'string',
                'format' => 'date'
            ),
            'status' => array(
                'description' => __('Consultation status', 'medx360'),
                'type' => 'string',
                'enum' => array('scheduled', 'in_progress', 'completed', 'cancelled'),
                'default' => 'scheduled'
            )
        );
    }
}
