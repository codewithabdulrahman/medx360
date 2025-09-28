<?php
/**
 * Doctors API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Doctors_API extends MedX360_API_Controller {
    
    protected $rest_base = 'doctors';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_doctors'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_doctor'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_doctor_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_doctor'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the doctor', 'medx360'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_doctor'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_doctor_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_doctor'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/clinic/(?P<clinic_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_doctors_by_clinic'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'clinic_id' => array(
                    'description' => __('Clinic ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/hospital/(?P<hospital_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_doctors_by_hospital'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'hospital_id' => array(
                    'description' => __('Hospital ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/schedule', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_doctor_schedule'),
                'permission_callback' => array($this, 'check_read_permission')
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_doctor_schedule'),
                'permission_callback' => array($this, 'check_permission')
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_doctor_schedule'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/availability', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_doctor_availability'),
                'permission_callback' => array($this, 'check_read_permission')
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_doctor_availability'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
    }
    
    /**
     * Get doctors collection
     */
    public function get_doctors($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('doctors');
        $pagination = $this->get_pagination_params($request);
        $search = $this->get_search_params($request);
        $filters = $this->get_filter_params($request);
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('first_name', 'last_name', 'specialization', 'email'));
        
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
        
        // Get doctors
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $doctors = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_doctors = array();
        foreach ($doctors as $doctor) {
            $formatted_doctors[] = $this->format_doctor_data($doctor);
        }
        
        $response = array(
            'data' => $formatted_doctors,
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
     * Get single doctor
     */
    public function get_doctor($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('doctors');
        $doctor_id = $request->get_param('id');
        
        $doctor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $doctor_id
        ));
        
        if (!$doctor) {
            return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 404);
        }
        
        return $this->format_response($this->format_doctor_data($doctor));
    }
    
    /**
     * Get doctors by clinic
     */
    public function get_doctors_by_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('doctors');
        $clinic_id = $request->get_param('clinic_id');
        
        $doctors = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE clinic_id = %d AND status = 'active' ORDER BY first_name, last_name ASC",
            $clinic_id
        ));
        
        $formatted_doctors = array();
        foreach ($doctors as $doctor) {
            $formatted_doctors[] = $this->format_doctor_data($doctor);
        }
        
        return $this->format_response($formatted_doctors);
    }
    
    /**
     * Get doctors by hospital
     */
    public function get_doctors_by_hospital($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('doctors');
        $hospital_id = $request->get_param('hospital_id');
        
        $doctors = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE hospital_id = %d AND status = 'active' ORDER BY first_name, last_name ASC",
            $hospital_id
        ));
        
        $formatted_doctors = array();
        foreach ($doctors as $doctor) {
            $formatted_doctors[] = $this->format_doctor_data($doctor);
        }
        
        return $this->format_response($formatted_doctors);
    }
    
    /**
     * Get doctor schedule
     */
    public function get_doctor_schedule($request) {
        global $wpdb;
        
        $doctor_id = $request->get_param('id');
        $schedules_table = $this->get_table_name('doctor_schedules');
        
        $schedules = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $schedules_table WHERE doctor_id = %d ORDER BY day_of_week, start_time",
            $doctor_id
        ));
        
        return $this->format_response($schedules);
    }
    
    /**
     * Create doctor schedule
     */
    public function create_doctor_schedule($request) {
        global $wpdb;
        
        $doctor_id = $request->get_param('id');
        $data = $request->get_json_params();
        $schedules_table = $this->get_table_name('doctor_schedules');
        
        // Validate doctor exists
        $doctors_table = $this->get_table_name('doctors');
        $doctor_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $doctors_table WHERE id = %d",
            $doctor_id
        ));
        
        if (!$doctor_exists) {
            return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 404);
        }
        
        // Validate required fields
        $required_fields = array('day_of_week', 'start_time', 'end_time');
        $errors = $this->validate_required_fields($data, $required_fields);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'day_of_week' => 'int',
            'start_time' => 'time',
            'end_time' => 'time',
            'is_available' => 'int'
        ));
        
        $sanitized_data['doctor_id'] = $doctor_id;
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert schedule
        $result = $wpdb->insert($schedules_table, $sanitized_data);
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to create schedule', 'medx360'), 'create_failed', 500);
        }
        
        $schedule_id = $wpdb->insert_id;
        
        // Get created schedule
        $schedule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $schedules_table WHERE id = %d",
            $schedule_id
        ));
        
        return $this->format_response($schedule, 201);
    }
    
    /**
     * Update doctor schedule
     */
    public function update_doctor_schedule($request) {
        global $wpdb;
        
        $doctor_id = $request->get_param('id');
        $data = $request->get_json_params();
        $schedules_table = $this->get_table_name('doctor_schedules');
        
        // Validate doctor exists
        $doctors_table = $this->get_table_name('doctors');
        $doctor_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $doctors_table WHERE id = %d",
            $doctor_id
        ));
        
        if (!$doctor_exists) {
            return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 404);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'day_of_week' => 'int',
            'start_time' => 'time',
            'end_time' => 'time',
            'is_available' => 'int'
        ));
        
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update all schedules for this doctor
        $result = $wpdb->update(
            $schedules_table,
            $sanitized_data,
            array('doctor_id' => $doctor_id),
            array('%d', '%s', '%s', '%d', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to update schedule', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated schedules
        $schedules = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $schedules_table WHERE doctor_id = %d ORDER BY day_of_week, start_time",
            $doctor_id
        ));
        
        return $this->format_response($schedules);
    }
    
    /**
     * Get doctor availability
     */
    public function get_doctor_availability($request) {
        global $wpdb;
        
        $doctor_id = $request->get_param('id');
        $date = $request->get_param('date');
        $availability_table = $this->get_table_name('doctor_availability');
        
        $where_conditions = array('doctor_id = %d');
        $where_values = array($doctor_id);
        
        if ($date) {
            $where_conditions[] = 'date = %s';
            $where_values[] = $date;
        }
        
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $availability = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $availability_table $where_sql ORDER BY date, start_time",
            $where_values
        ));
        
        return $this->format_response($availability);
    }
    
    /**
     * Create doctor availability
     */
    public function create_doctor_availability($request) {
        global $wpdb;
        
        $doctor_id = $request->get_param('id');
        $data = $request->get_json_params();
        $availability_table = $this->get_table_name('doctor_availability');
        
        // Validate doctor exists
        $doctors_table = $this->get_table_name('doctors');
        $doctor_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $doctors_table WHERE id = %d",
            $doctor_id
        ));
        
        if (!$doctor_exists) {
            return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 404);
        }
        
        // Validate required fields
        $required_fields = array('date');
        $errors = $this->validate_required_fields($data, $required_fields);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'date' => 'date',
            'start_time' => 'time',
            'end_time' => 'time',
            'is_available' => 'int',
            'reason' => 'text'
        ));
        
        $sanitized_data['doctor_id'] = $doctor_id;
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert availability
        $result = $wpdb->insert($availability_table, $sanitized_data);
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to create availability', 'medx360'), 'create_failed', 500);
        }
        
        $availability_id = $wpdb->insert_id;
        
        // Get created availability
        $availability = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $availability_table WHERE id = %d",
            $availability_id
        ));
        
        return $this->format_response($availability, 201);
    }
    
    /**
     * Create doctor
     */
    public function create_doctor($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('doctors');
        $data = $request->get_json_params();
        
        // Validate data
        $errors = MedX360_Validator::validate_doctor_data($data);
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
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'clinic_id' => 'int',
            'hospital_id' => 'int',
            'user_id' => 'int',
            'first_name' => 'text',
            'last_name' => 'text',
            'email' => 'email',
            'phone' => 'text',
            'specialization' => 'text',
            'license_number' => 'text',
            'experience_years' => 'int',
            'education' => 'textarea',
            'bio' => 'textarea',
            'profile_image' => 'url',
            'consultation_fee' => 'float',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert doctor
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to create doctor', 'medx360'), 'create_failed', 500);
        }
        
        $doctor_id = $wpdb->insert_id;
        
        // Get created doctor
        $doctor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $doctor_id
        ));
        
        return $this->format_response($this->format_doctor_data($doctor), 201);
    }
    
    /**
     * Update doctor
     */
    public function update_doctor($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('doctors');
        $doctor_id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if doctor exists
        $existing_doctor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $doctor_id
        ));
        
        if (!$existing_doctor) {
            return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_doctor_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if clinic exists (if clinic_id is being updated)
        if (isset($data['clinic_id']) && $data['clinic_id'] != $existing_doctor->clinic_id) {
            $clinics_table = $this->get_table_name('clinics');
            $clinic_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $clinics_table WHERE id = %d",
                $data['clinic_id']
            ));
            
            if (!$clinic_exists) {
                return $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 400);
            }
        }
        
        // Check if hospital exists (if hospital_id is being updated)
        if (isset($data['hospital_id']) && $data['hospital_id'] != $existing_doctor->hospital_id) {
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
            'specialization' => 'text',
            'license_number' => 'text',
            'experience_years' => 'int',
            'education' => 'textarea',
            'bio' => 'textarea',
            'profile_image' => 'url',
            'consultation_fee' => 'float',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update doctor
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $doctor_id),
            array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%f', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to update doctor', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated doctor
        $doctor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $doctor_id
        ));
        
        return $this->format_response($this->format_doctor_data($doctor));
    }
    
    /**
     * Delete doctor
     */
    public function delete_doctor($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('doctors');
        $doctor_id = $request->get_param('id');
        
        // Check if doctor exists
        $existing_doctor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $doctor_id
        ));
        
        if (!$existing_doctor) {
            return $this->format_error_response(__('Doctor not found', 'medx360'), 'doctor_not_found', 404);
        }
        
        // Delete doctor (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $doctor_id), array('%d'));
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to delete doctor', 'medx360'), 'delete_failed', 500);
        }
        
        return $this->format_response(array('message' => __('Doctor deleted successfully', 'medx360')));
    }
    
    /**
     * Format doctor data for response
     */
    private function format_doctor_data($doctor) {
        $settings = !empty($doctor->settings) ? json_decode($doctor->settings, true) : array();
        
        return array(
            'id' => intval($doctor->id),
            'clinic_id' => intval($doctor->clinic_id),
            'hospital_id' => intval($doctor->hospital_id),
            'user_id' => intval($doctor->user_id),
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'full_name' => $doctor->first_name . ' ' . $doctor->last_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'specialization' => $doctor->specialization,
            'license_number' => $doctor->license_number,
            'experience_years' => intval($doctor->experience_years),
            'education' => $doctor->education,
            'bio' => $doctor->bio,
            'profile_image' => $doctor->profile_image,
            'consultation_fee' => floatval($doctor->consultation_fee),
            'status' => $doctor->status,
            'settings' => $settings,
            'created_at' => $doctor->created_at,
            'updated_at' => $doctor->updated_at
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
                'enum' => array('id', 'first_name', 'last_name', 'specialization', 'created_at', 'updated_at')
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
                'enum' => array('active', 'inactive', 'pending')
            ),
            'clinic_id' => array(
                'description' => __('Filter by clinic ID', 'medx360'),
                'type' => 'integer'
            ),
            'hospital_id' => array(
                'description' => __('Filter by hospital ID', 'medx360'),
                'type' => 'integer'
            ),
            'specialization' => array(
                'description' => __('Filter by specialization', 'medx360'),
                'type' => 'string'
            )
        );
    }
    
    /**
     * Get doctor parameters
     */
    public function get_doctor_params() {
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
            'user_id' => array(
                'description' => __('WordPress User ID', 'medx360'),
                'type' => 'integer'
            ),
            'first_name' => array(
                'description' => __('First name', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'last_name' => array(
                'description' => __('Last name', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'email' => array(
                'description' => __('Email address', 'medx360'),
                'type' => 'string',
                'format' => 'email',
                'required' => true
            ),
            'phone' => array(
                'description' => __('Phone number', 'medx360'),
                'type' => 'string'
            ),
            'specialization' => array(
                'description' => __('Medical specialization', 'medx360'),
                'type' => 'string'
            ),
            'license_number' => array(
                'description' => __('Medical license number', 'medx360'),
                'type' => 'string'
            ),
            'experience_years' => array(
                'description' => __('Years of experience', 'medx360'),
                'type' => 'integer',
                'minimum' => 0
            ),
            'education' => array(
                'description' => __('Education background', 'medx360'),
                'type' => 'string'
            ),
            'bio' => array(
                'description' => __('Doctor biography', 'medx360'),
                'type' => 'string'
            ),
            'profile_image' => array(
                'description' => __('Profile image URL', 'medx360'),
                'type' => 'string',
                'format' => 'uri'
            ),
            'consultation_fee' => array(
                'description' => __('Consultation fee', 'medx360'),
                'type' => 'number',
                'minimum' => 0
            ),
            'status' => array(
                'description' => __('Status', 'medx360'),
                'type' => 'string',
                'enum' => array('active', 'inactive', 'pending'),
                'default' => 'active'
            ),
            'settings' => array(
                'description' => __('Doctor settings', 'medx360'),
                'type' => 'object'
            )
        );
    }
}
