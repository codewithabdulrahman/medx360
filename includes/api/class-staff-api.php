<?php
/**
 * Staff API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Staff_API extends MedX360_API_Controller {
    
    protected $rest_base = 'staff';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_staff'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_staff'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_staff_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_staff_member'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the staff member', 'medx360'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_staff'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_staff_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_staff'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/clinic/(?P<clinic_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_staff_by_clinic'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'clinic_id' => array(
                    'description' => __('Clinic ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
    }
    
    /**
     * Get staff collection
     */
    public function get_staff($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $pagination = $this->get_pagination_params($request);
        $search = $this->get_search_params($request);
        $filters = $this->get_filter_params($request);
        
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
        
        return $this->format_response($response);
    }
    
    /**
     * Get single staff member
     */
    public function get_staff_member($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $staff_id = $request->get_param('id');
        
        $staff_member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        if (!$staff_member) {
            return $this->format_error_response(__('Staff member not found', 'medx360'), 'staff_not_found', 404);
        }
        
        return $this->format_response($this->format_staff_data($staff_member));
    }
    
    /**
     * Get staff by clinic
     */
    public function get_staff_by_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $clinic_id = $request->get_param('clinic_id');
        
        $staff = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE clinic_id = %d AND status = 'active' ORDER BY first_name, last_name ASC",
            $clinic_id
        ));
        
        $formatted_staff = array();
        foreach ($staff as $staff_member) {
            $formatted_staff[] = $this->format_staff_data($staff_member);
        }
        
        return $this->format_response($formatted_staff);
    }
    
    /**
     * Create staff member
     */
    public function create_staff($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $data = $request->get_json_params();
        
        // Validate required fields
        $required_fields = array('clinic_id', 'first_name', 'last_name', 'email', 'role');
        $errors = $this->validate_required_fields($data, $required_fields);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Validate email
        if (!MedX360_Validator::validate_email($data['email'])) {
            return $this->format_error_response(__('Invalid email address', 'medx360'), 'validation_error', 400);
        }
        
        // Validate phone
        if (!empty($data['phone']) && !MedX360_Validator::validate_phone($data['phone'])) {
            return $this->format_error_response(__('Invalid phone number', 'medx360'), 'validation_error', 400);
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
            return $this->format_error_response(__('Failed to create staff member', 'medx360'), 'create_failed', 500);
        }
        
        $staff_id = $wpdb->insert_id;
        
        // Get created staff member
        $staff_member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        return $this->format_response($this->format_staff_data($staff_member), 201);
    }
    
    /**
     * Update staff member
     */
    public function update_staff($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $staff_id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if staff member exists
        $existing_staff = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        if (!$existing_staff) {
            return $this->format_error_response(__('Staff member not found', 'medx360'), 'staff_not_found', 404);
        }
        
        // Validate email
        if (!empty($data['email']) && !MedX360_Validator::validate_email($data['email'])) {
            return $this->format_error_response(__('Invalid email address', 'medx360'), 'validation_error', 400);
        }
        
        // Validate phone
        if (!empty($data['phone']) && !MedX360_Validator::validate_phone($data['phone'])) {
            return $this->format_error_response(__('Invalid phone number', 'medx360'), 'validation_error', 400);
        }
        
        // Check if clinic exists (if clinic_id is being updated)
        if (isset($data['clinic_id']) && $data['clinic_id'] != $existing_staff->clinic_id) {
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
        if (isset($data['hospital_id']) && $data['hospital_id'] != $existing_staff->hospital_id) {
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
            return $this->format_error_response(__('Failed to update staff member', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated staff member
        $staff_member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        return $this->format_response($this->format_staff_data($staff_member));
    }
    
    /**
     * Delete staff member
     */
    public function delete_staff($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('staff');
        $staff_id = $request->get_param('id');
        
        // Check if staff member exists
        $existing_staff = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $staff_id
        ));
        
        if (!$existing_staff) {
            return $this->format_error_response(__('Staff member not found', 'medx360'), 'staff_not_found', 404);
        }
        
        // Delete staff member
        $result = $wpdb->delete($table_name, array('id' => $staff_id), array('%d'));
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to delete staff member', 'medx360'), 'delete_failed', 500);
        }
        
        return $this->format_response(array('message' => __('Staff member deleted successfully', 'medx360')));
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
                'enum' => array('id', 'first_name', 'last_name', 'role', 'department', 'created_at', 'updated_at')
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
            'role' => array(
                'description' => __('Filter by role', 'medx360'),
                'type' => 'string'
            ),
            'department' => array(
                'description' => __('Filter by department', 'medx360'),
                'type' => 'string'
            )
        );
    }
    
    /**
     * Get staff parameters
     */
    public function get_staff_params() {
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
            'role' => array(
                'description' => __('Staff role', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'department' => array(
                'description' => __('Department', 'medx360'),
                'type' => 'string'
            ),
            'status' => array(
                'description' => __('Status', 'medx360'),
                'type' => 'string',
                'enum' => array('active', 'inactive', 'pending'),
                'default' => 'active'
            ),
            'settings' => array(
                'description' => __('Staff settings', 'medx360'),
                'type' => 'object'
            )
        );
    }
}
