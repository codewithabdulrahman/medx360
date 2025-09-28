<?php
/**
 * Hospitals API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Hospitals_API extends MedX360_API_Controller {
    
    protected $rest_base = 'hospitals';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_hospitals'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_hospital'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_hospital_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_hospital'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the hospital', 'medx360'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_hospital'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_hospital_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_hospital'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/slug/(?P<slug>[\w-]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_hospital_by_slug'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'slug' => array(
                    'description' => __('Hospital slug', 'medx360'),
                    'type' => 'string',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/clinic/(?P<clinic_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_hospitals_by_clinic'),
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
     * Get hospitals collection
     */
    public function get_hospitals($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $pagination = $this->get_pagination_params($request);
        $search = $this->get_search_params($request);
        $filters = $this->get_filter_params($request);
        
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
        
        return $this->format_response($response);
    }
    
    /**
     * Get single hospital
     */
    public function get_hospital($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $hospital_id = $request->get_param('id');
        
        $hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        if (!$hospital) {
            return $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 404);
        }
        
        return $this->format_response($this->format_hospital_data($hospital));
    }
    
    /**
     * Get hospital by slug
     */
    public function get_hospital_by_slug($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $slug = $request->get_param('slug');
        
        $hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE slug = %s",
            $slug
        ));
        
        if (!$hospital) {
            return $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 404);
        }
        
        return $this->format_response($this->format_hospital_data($hospital));
    }
    
    /**
     * Get hospitals by clinic
     */
    public function get_hospitals_by_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $clinic_id = $request->get_param('clinic_id');
        
        $hospitals = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE clinic_id = %d AND status = 'active' ORDER BY name ASC",
            $clinic_id
        ));
        
        $formatted_hospitals = array();
        foreach ($hospitals as $hospital) {
            $formatted_hospitals[] = $this->format_hospital_data($hospital);
        }
        
        return $this->format_response($formatted_hospitals);
    }
    
    /**
     * Create hospital
     */
    public function create_hospital($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $data = $request->get_json_params();
        
        // Validate data
        $errors = MedX360_Validator::validate_hospital_data($data);
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
            return $this->format_error_response(__('Slug already exists', 'medx360'), 'slug_exists', 400);
        }
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert hospital
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to create hospital', 'medx360'), 'create_failed', 500);
        }
        
        $hospital_id = $wpdb->insert_id;
        
        // Get created hospital
        $hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        return $this->format_response($this->format_hospital_data($hospital), 201);
    }
    
    /**
     * Update hospital
     */
    public function update_hospital($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $hospital_id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if hospital exists
        $existing_hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        if (!$existing_hospital) {
            return $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_hospital_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if clinic exists (if clinic_id is being updated)
        if (isset($data['clinic_id']) && $data['clinic_id'] != $existing_hospital->clinic_id) {
            $clinics_table = $this->get_table_name('clinics');
            $clinic_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $clinics_table WHERE id = %d",
                $data['clinic_id']
            ));
            
            if (!$clinic_exists) {
                return $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 400);
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
                return $this->format_error_response(__('Slug already exists', 'medx360'), 'slug_exists', 400);
            }
        }
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update hospital
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $hospital_id),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to update hospital', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated hospital
        $hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        return $this->format_response($this->format_hospital_data($hospital));
    }
    
    /**
     * Delete hospital
     */
    public function delete_hospital($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('hospitals');
        $hospital_id = $request->get_param('id');
        
        // Check if hospital exists
        $existing_hospital = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $hospital_id
        ));
        
        if (!$existing_hospital) {
            return $this->format_error_response(__('Hospital not found', 'medx360'), 'hospital_not_found', 404);
        }
        
        // Delete hospital (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $hospital_id), array('%d'));
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to delete hospital', 'medx360'), 'delete_failed', 500);
        }
        
        return $this->format_response(array('message' => __('Hospital deleted successfully', 'medx360')));
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
                'enum' => array('id', 'name', 'city', 'state', 'created_at', 'updated_at')
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
            )
        );
    }
    
    /**
     * Get hospital parameters
     */
    public function get_hospital_params() {
        return array(
            'clinic_id' => array(
                'description' => __('Clinic ID', 'medx360'),
                'type' => 'integer',
                'required' => true
            ),
            'name' => array(
                'description' => __('Hospital name', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'slug' => array(
                'description' => __('Hospital slug', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'description' => array(
                'description' => __('Hospital description', 'medx360'),
                'type' => 'string'
            ),
            'address' => array(
                'description' => __('Hospital address', 'medx360'),
                'type' => 'string'
            ),
            'city' => array(
                'description' => __('City', 'medx360'),
                'type' => 'string'
            ),
            'state' => array(
                'description' => __('State', 'medx360'),
                'type' => 'string'
            ),
            'country' => array(
                'description' => __('Country', 'medx360'),
                'type' => 'string'
            ),
            'postal_code' => array(
                'description' => __('Postal code', 'medx360'),
                'type' => 'string'
            ),
            'phone' => array(
                'description' => __('Phone number', 'medx360'),
                'type' => 'string'
            ),
            'email' => array(
                'description' => __('Email address', 'medx360'),
                'type' => 'string',
                'format' => 'email'
            ),
            'website' => array(
                'description' => __('Website URL', 'medx360'),
                'type' => 'string',
                'format' => 'uri'
            ),
            'logo_url' => array(
                'description' => __('Logo URL', 'medx360'),
                'type' => 'string',
                'format' => 'uri'
            ),
            'status' => array(
                'description' => __('Status', 'medx360'),
                'type' => 'string',
                'enum' => array('active', 'inactive', 'pending'),
                'default' => 'active'
            ),
            'settings' => array(
                'description' => __('Hospital settings', 'medx360'),
                'type' => 'object'
            )
        );
    }
}
