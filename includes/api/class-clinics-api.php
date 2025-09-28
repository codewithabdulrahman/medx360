<?php
/**
 * Clinics API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Clinics_API extends MedX360_API_Controller {
    
    protected $rest_base = 'clinics';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_clinics'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_clinic'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_clinic_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_clinic'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the clinic', 'medx360'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_clinic'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_clinic_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_clinic'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/slug/(?P<slug>[\w-]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_clinic_by_slug'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'slug' => array(
                    'description' => __('Clinic slug', 'medx360'),
                    'type' => 'string',
                    'required' => true
                )
            )
        ));
    }
    
    /**
     * Get clinics collection
     */
    public function get_clinics($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
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
        
        return $this->format_response($response);
    }
    
    /**
     * Get single clinic
     */
    public function get_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $clinic_id = $request->get_param('id');
        
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        if (!$clinic) {
            return $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 404);
        }
        
        return $this->format_response($this->format_clinic_data($clinic));
    }
    
    /**
     * Get clinic by slug
     */
    public function get_clinic_by_slug($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $slug = $request->get_param('slug');
        
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE slug = %s",
            $slug
        ));
        
        if (!$clinic) {
            return $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 404);
        }
        
        return $this->format_response($this->format_clinic_data($clinic));
    }
    
    /**
     * Create clinic
     */
    public function create_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $data = $request->get_json_params();
        
        // Validate data
        $errors = MedX360_Validator::validate_clinic_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
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
            return $this->format_error_response(__('Slug already exists', 'medx360'), 'slug_exists', 400);
        }
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert clinic
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to create clinic', 'medx360'), 'create_failed', 500);
        }
        
        $clinic_id = $wpdb->insert_id;
        
        // Get created clinic
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        return $this->format_response($this->format_clinic_data($clinic), 201);
    }
    
    /**
     * Update clinic
     */
    public function update_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $clinic_id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if clinic exists
        $existing_clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        if (!$existing_clinic) {
            return $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_clinic_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
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
                return $this->format_error_response(__('Slug already exists', 'medx360'), 'slug_exists', 400);
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
            return $this->format_error_response(__('Failed to update clinic', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated clinic
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        return $this->format_response($this->format_clinic_data($clinic));
    }
    
    /**
     * Delete clinic
     */
    public function delete_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('clinics');
        $clinic_id = $request->get_param('id');
        
        // Check if clinic exists
        $existing_clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $clinic_id
        ));
        
        if (!$existing_clinic) {
            return $this->format_error_response(__('Clinic not found', 'medx360'), 'clinic_not_found', 404);
        }
        
        // Delete clinic (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $clinic_id), array('%d'));
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to delete clinic', 'medx360'), 'delete_failed', 500);
        }
        
        return $this->format_response(array('message' => __('Clinic deleted successfully', 'medx360')));
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
            )
        );
    }
    
    /**
     * Get clinic parameters
     */
    public function get_clinic_params() {
        return array(
            'name' => array(
                'description' => __('Clinic name', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'slug' => array(
                'description' => __('Clinic slug', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'description' => array(
                'description' => __('Clinic description', 'medx360'),
                'type' => 'string'
            ),
            'address' => array(
                'description' => __('Clinic address', 'medx360'),
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
                'description' => __('Clinic settings', 'medx360'),
                'type' => 'object'
            )
        );
    }
}
