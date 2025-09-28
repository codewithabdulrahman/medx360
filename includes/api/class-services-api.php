<?php
/**
 * Services API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Services_API extends MedX360_API_Controller {
    
    protected $rest_base = 'services';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_services'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_service'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_service_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_service'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the service', 'medx360'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_service'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_service_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_service'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/clinic/(?P<clinic_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_services_by_clinic'),
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
            'callback' => array($this, 'get_services_by_hospital'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'hospital_id' => array(
                    'description' => __('Hospital ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
    }
    
    /**
     * Get services collection
     */
    public function get_services($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $pagination = $this->get_pagination_params($request);
        $search = $this->get_search_params($request);
        $filters = $this->get_filter_params($request);
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('name', 'description', 'category'));
        
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
        
        // Get services
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $services = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_services = array();
        foreach ($services as $service) {
            $formatted_services[] = $this->format_service_data($service);
        }
        
        $response = array(
            'data' => $formatted_services,
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
     * Get single service
     */
    public function get_service($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $service_id = $request->get_param('id');
        
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        if (!$service) {
            return $this->format_error_response(__('Service not found', 'medx360'), 'service_not_found', 404);
        }
        
        return $this->format_response($this->format_service_data($service));
    }
    
    /**
     * Get services by clinic
     */
    public function get_services_by_clinic($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $clinic_id = $request->get_param('clinic_id');
        
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE clinic_id = %d AND status = 'active' ORDER BY name ASC",
            $clinic_id
        ));
        
        $formatted_services = array();
        foreach ($services as $service) {
            $formatted_services[] = $this->format_service_data($service);
        }
        
        return $this->format_response($formatted_services);
    }
    
    /**
     * Get services by hospital
     */
    public function get_services_by_hospital($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $hospital_id = $request->get_param('hospital_id');
        
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE hospital_id = %d AND status = 'active' ORDER BY name ASC",
            $hospital_id
        ));
        
        $formatted_services = array();
        foreach ($services as $service) {
            $formatted_services[] = $this->format_service_data($service);
        }
        
        return $this->format_response($formatted_services);
    }
    
    /**
     * Create service
     */
    public function create_service($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $data = $request->get_json_params();
        
        // Validate data
        $errors = MedX360_Validator::validate_service_data($data);
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
            'name' => 'text',
            'description' => 'textarea',
            'duration_minutes' => 'int',
            'price' => 'float',
            'category' => 'text',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert service
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to create service', 'medx360'), 'create_failed', 500);
        }
        
        $service_id = $wpdb->insert_id;
        
        // Get created service
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        return $this->format_response($this->format_service_data($service), 201);
    }
    
    /**
     * Update service
     */
    public function update_service($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $service_id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if service exists
        $existing_service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        if (!$existing_service) {
            return $this->format_error_response(__('Service not found', 'medx360'), 'service_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_service_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if clinic exists (if clinic_id is being updated)
        if (isset($data['clinic_id']) && $data['clinic_id'] != $existing_service->clinic_id) {
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
        if (isset($data['hospital_id']) && $data['hospital_id'] != $existing_service->hospital_id) {
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
            'name' => 'text',
            'description' => 'textarea',
            'duration_minutes' => 'int',
            'price' => 'float',
            'category' => 'text',
            'status' => 'text',
            'settings' => 'json'
        ));
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update service
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $service_id),
            array('%d', '%d', '%s', '%s', '%d', '%f', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to update service', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated service
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        return $this->format_response($this->format_service_data($service));
    }
    
    /**
     * Delete service
     */
    public function delete_service($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('services');
        $service_id = $request->get_param('id');
        
        // Check if service exists
        $existing_service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $service_id
        ));
        
        if (!$existing_service) {
            return $this->format_error_response(__('Service not found', 'medx360'), 'service_not_found', 404);
        }
        
        // Delete service (cascade will handle related records)
        $result = $wpdb->delete($table_name, array('id' => $service_id), array('%d'));
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to delete service', 'medx360'), 'delete_failed', 500);
        }
        
        return $this->format_response(array('message' => __('Service deleted successfully', 'medx360')));
    }
    
    /**
     * Format service data for response
     */
    private function format_service_data($service) {
        $settings = !empty($service->settings) ? json_decode($service->settings, true) : array();
        
        return array(
            'id' => intval($service->id),
            'clinic_id' => intval($service->clinic_id),
            'hospital_id' => intval($service->hospital_id),
            'name' => $service->name,
            'description' => $service->description,
            'duration_minutes' => intval($service->duration_minutes),
            'price' => floatval($service->price),
            'category' => $service->category,
            'status' => $service->status,
            'settings' => $settings,
            'created_at' => $service->created_at,
            'updated_at' => $service->updated_at
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
                'enum' => array('id', 'name', 'category', 'price', 'created_at', 'updated_at')
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
                'enum' => array('active', 'inactive')
            ),
            'clinic_id' => array(
                'description' => __('Filter by clinic ID', 'medx360'),
                'type' => 'integer'
            ),
            'hospital_id' => array(
                'description' => __('Filter by hospital ID', 'medx360'),
                'type' => 'integer'
            ),
            'category' => array(
                'description' => __('Filter by category', 'medx360'),
                'type' => 'string'
            )
        );
    }
    
    /**
     * Get service parameters
     */
    public function get_service_params() {
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
            'name' => array(
                'description' => __('Service name', 'medx360'),
                'type' => 'string',
                'required' => true
            ),
            'description' => array(
                'description' => __('Service description', 'medx360'),
                'type' => 'string'
            ),
            'duration_minutes' => array(
                'description' => __('Duration in minutes', 'medx360'),
                'type' => 'integer',
                'default' => 30,
                'minimum' => 1,
                'maximum' => 1440
            ),
            'price' => array(
                'description' => __('Service price', 'medx360'),
                'type' => 'number',
                'minimum' => 0
            ),
            'category' => array(
                'description' => __('Service category', 'medx360'),
                'type' => 'string'
            ),
            'status' => array(
                'description' => __('Status', 'medx360'),
                'type' => 'string',
                'enum' => array('active', 'inactive'),
                'default' => 'active'
            ),
            'settings' => array(
                'description' => __('Service settings', 'medx360'),
                'type' => 'object'
            )
        );
    }
}
