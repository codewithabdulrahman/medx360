<?php
/**
 * Clinic and Service API Endpoints
 * Handles CRUD operations for clinics and services
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_ClinicServiceAPI {
    
    private $namespace = 'medx360/v1';
    private $database;
    
    public function __construct() {
        $this->database = new Medx360_Database();
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register clinic and service routes
     */
    public function register_routes() {
        $this->register_clinic_routes();
        $this->register_service_routes();
    }
    
    /**
     * Register clinic routes
     */
    private function register_clinic_routes() {
        register_rest_route($this->namespace, '/clinics', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_clinics'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'page' => array('required' => false, 'type' => 'integer', 'default' => 1),
                'per_page' => array('required' => false, 'type' => 'integer', 'default' => 20),
                'search' => array('required' => false, 'type' => 'string'),
                'status' => array('required' => false, 'type' => 'string')
            )
        ));
        
        register_rest_route($this->namespace, '/clinics', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_clinic'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => $this->get_clinic_args()
        ));
        
        register_rest_route($this->namespace, '/clinics/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_clinic'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route($this->namespace, '/clinics/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_clinic'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array_merge(
                array('id' => array('required' => true, 'type' => 'integer')),
                $this->get_clinic_args()
            )
        ));
        
        register_rest_route($this->namespace, '/clinics/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_clinic'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
    }
    
    /**
     * Register service routes
     */
    private function register_service_routes() {
        register_rest_route($this->namespace, '/services', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_services'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'page' => array('required' => false, 'type' => 'integer', 'default' => 1),
                'per_page' => array('required' => false, 'type' => 'integer', 'default' => 20),
                'search' => array('required' => false, 'type' => 'string'),
                'category' => array('required' => false, 'type' => 'string'),
                'status' => array('required' => false, 'type' => 'string')
            )
        ));
        
        register_rest_route($this->namespace, '/services', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_service'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => $this->get_service_args()
        ));
        
        register_rest_route($this->namespace, '/services/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_service'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route($this->namespace, '/services/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_service'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array_merge(
                array('id' => array('required' => true, 'type' => 'integer')),
                $this->get_service_args()
            )
        ));
        
        register_rest_route($this->namespace, '/services/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_service'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
    }
    
    /**
     * Clinic endpoint implementations
     */
    public function get_clinics($request) {
        global $wpdb;
        
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $search = $request->get_param('search');
        $status = $request->get_param('status');
        
        $offset = ($page - 1) * $per_page;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if ($search) {
            $where_conditions[] = "(name LIKE %s OR address LIKE %s OR type LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if ($status) {
            $where_conditions[] = "status = %s";
            $where_values[] = $status;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $total_query = "SELECT COUNT(*) FROM {$this->database->get_table('clinics')} WHERE $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($total_query, $where_values));
        
        // Get clinics
        $clinics_query = "SELECT * FROM {$this->database->get_table('clinics')} WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $clinics = $wpdb->get_results($wpdb->prepare($clinics_query, array_merge($where_values, array($per_page, $offset))));
        
        // Decode JSON fields
        foreach ($clinics as $clinic) {
            if ($clinic->services) {
                $clinic->services = json_decode($clinic->services, true);
            }
            if ($clinic->operating_hours) {
                $clinic->operating_hours = json_decode($clinic->operating_hours, true);
            }
        }
        
        return rest_ensure_response(array(
            'data' => $clinics,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }
    
    public function create_clinic($request) {
        global $wpdb;
        
        $data = $request->get_json_params();
        
        // Validate required fields
        $required_fields = array('name', 'type', 'address');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', "Field '$field' is required", array('status' => 400));
            }
        }
        
        $result = $wpdb->insert(
            $this->database->get_table('clinics'),
            array(
                'name' => sanitize_text_field($data['name']),
                'type' => sanitize_text_field($data['type']),
                'address' => sanitize_textarea_field($data['address']),
                'phone' => !empty($data['phone']) ? sanitize_text_field($data['phone']) : null,
                'email' => !empty($data['email']) ? sanitize_email($data['email']) : null,
                'website' => !empty($data['website']) ? esc_url_raw($data['website']) : null,
                'license_number' => !empty($data['license_number']) ? sanitize_text_field($data['license_number']) : null,
                'status' => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active',
                'established_date' => !empty($data['established_date']) ? sanitize_text_field($data['established_date']) : null,
                'services' => !empty($data['services']) ? json_encode($data['services']) : null,
                'operating_hours' => !empty($data['operating_hours']) ? json_encode($data['operating_hours']) : null,
                'notes' => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create clinic', array('status' => 500));
        }
        
        $clinic_id = $wpdb->insert_id;
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('clinics')} WHERE id = %d",
            $clinic_id
        ));
        
        // Decode JSON fields
        if ($clinic->services) {
            $clinic->services = json_decode($clinic->services, true);
        }
        if ($clinic->operating_hours) {
            $clinic->operating_hours = json_decode($clinic->operating_hours, true);
        }
        
        return rest_ensure_response($clinic, 201);
    }
    
    public function get_clinic($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('clinics')} WHERE id = %d",
            $id
        ));
        
        if (!$clinic) {
            return new WP_Error('not_found', 'Clinic not found', array('status' => 404));
        }
        
        // Decode JSON fields
        if ($clinic->services) {
            $clinic->services = json_decode($clinic->services, true);
        }
        if ($clinic->operating_hours) {
            $clinic->operating_hours = json_decode($clinic->operating_hours, true);
        }
        
        return rest_ensure_response($clinic);
    }
    
    public function update_clinic($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if clinic exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('clinics')} WHERE id = %d",
            $id
        ));
        
        if (!$existing) {
            return new WP_Error('not_found', 'Clinic not found', array('status' => 404));
        }
        
        $update_data = array();
        $update_format = array();
        
        $allowed_fields = array(
            'name', 'type', 'address', 'phone', 'email', 'website', 'license_number',
            'status', 'established_date', 'services', 'operating_hours', 'notes'
        );
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('services', 'operating_hours'))) {
                    $update_data[$field] = json_encode($data[$field]);
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
                $update_format[] = '%s';
            }
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data to update', array('status' => 400));
        }
        
        $result = $wpdb->update(
            $this->database->get_table('clinics'),
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update clinic', array('status' => 500));
        }
        
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('clinics')} WHERE id = %d",
            $id
        ));
        
        // Decode JSON fields
        if ($clinic->services) {
            $clinic->services = json_decode($clinic->services, true);
        }
        if ($clinic->operating_hours) {
            $clinic->operating_hours = json_decode($clinic->operating_hours, true);
        }
        
        return rest_ensure_response($clinic);
    }
    
    public function delete_clinic($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        
        // Check if clinic exists
        $clinic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('clinics')} WHERE id = %d",
            $id
        ));
        
        if (!$clinic) {
            return new WP_Error('not_found', 'Clinic not found', array('status' => 404));
        }
        
        $result = $wpdb->delete(
            $this->database->get_table('clinics'),
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete clinic', array('status' => 500));
        }
        
        return rest_ensure_response(array('message' => 'Clinic deleted successfully'));
    }
    
    /**
     * Service endpoint implementations
     */
    public function get_services($request) {
        global $wpdb;
        
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $search = $request->get_param('search');
        $category = $request->get_param('category');
        $status = $request->get_param('status');
        
        $offset = ($page - 1) * $per_page;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if ($search) {
            $where_conditions[] = "(name LIKE %s OR description LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if ($category) {
            $where_conditions[] = "category = %s";
            $where_values[] = $category;
        }
        
        if ($status) {
            $where_conditions[] = "status = %s";
            $where_values[] = $status;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $total_query = "SELECT COUNT(*) FROM {$this->database->get_table('services')} WHERE $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($total_query, $where_values));
        
        // Get services
        $services_query = "SELECT * FROM {$this->database->get_table('services')} WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $services = $wpdb->get_results($wpdb->prepare($services_query, array_merge($where_values, array($per_page, $offset))));
        
        // Decode JSON fields
        foreach ($services as $service) {
            if ($service->staff_assigned) {
                $service->staff_assigned = json_decode($service->staff_assigned, true);
            }
        }
        
        return rest_ensure_response(array(
            'data' => $services,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }
    
    public function create_service($request) {
        global $wpdb;
        
        $data = $request->get_json_params();
        
        // Validate required fields
        $required_fields = array('name', 'category', 'description');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', "Field '$field' is required", array('status' => 400));
            }
        }
        
        $result = $wpdb->insert(
            $this->database->get_table('services'),
            array(
                'name' => sanitize_text_field($data['name']),
                'category' => sanitize_text_field($data['category']),
                'duration' => !empty($data['duration']) ? intval($data['duration']) : 30,
                'price' => !empty($data['price']) ? floatval($data['price']) : 0.00,
                'description' => sanitize_textarea_field($data['description']),
                'status' => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active',
                'icon' => !empty($data['icon']) ? sanitize_text_field($data['icon']) : '🩺',
                'staff_assigned' => !empty($data['staff_assigned']) ? json_encode($data['staff_assigned']) : null,
                'requirements' => !empty($data['requirements']) ? sanitize_textarea_field($data['requirements']) : null,
                'preparation_instructions' => !empty($data['preparation_instructions']) ? sanitize_textarea_field($data['preparation_instructions']) : null,
                'follow_up_instructions' => !empty($data['follow_up_instructions']) ? sanitize_textarea_field($data['follow_up_instructions']) : null,
                'notes' => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null
            ),
            array('%s', '%s', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create service', array('status' => 500));
        }
        
        $service_id = $wpdb->insert_id;
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('services')} WHERE id = %d",
            $service_id
        ));
        
        // Decode JSON fields
        if ($service->staff_assigned) {
            $service->staff_assigned = json_decode($service->staff_assigned, true);
        }
        
        return rest_ensure_response($service, 201);
    }
    
    public function get_service($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('services')} WHERE id = %d",
            $id
        ));
        
        if (!$service) {
            return new WP_Error('not_found', 'Service not found', array('status' => 404));
        }
        
        // Decode JSON fields
        if ($service->staff_assigned) {
            $service->staff_assigned = json_decode($service->staff_assigned, true);
        }
        
        return rest_ensure_response($service);
    }
    
    public function update_service($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if service exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('services')} WHERE id = %d",
            $id
        ));
        
        if (!$existing) {
            return new WP_Error('not_found', 'Service not found', array('status' => 404));
        }
        
        $update_data = array();
        $update_format = array();
        
        $allowed_fields = array(
            'name', 'category', 'duration', 'price', 'description', 'status', 'icon',
            'staff_assigned', 'requirements', 'preparation_instructions', 'follow_up_instructions', 'notes'
        );
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'staff_assigned') {
                    $update_data[$field] = json_encode($data[$field]);
                } elseif (in_array($field, array('duration'))) {
                    $update_data[$field] = intval($data[$field]);
                    $update_format[] = '%d';
                } elseif (in_array($field, array('price'))) {
                    $update_data[$field] = floatval($data[$field]);
                    $update_format[] = '%f';
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                    $update_format[] = '%s';
                }
            }
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data to update', array('status' => 400));
        }
        
        $result = $wpdb->update(
            $this->database->get_table('services'),
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update service', array('status' => 500));
        }
        
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('services')} WHERE id = %d",
            $id
        ));
        
        // Decode JSON fields
        if ($service->staff_assigned) {
            $service->staff_assigned = json_decode($service->staff_assigned, true);
        }
        
        return rest_ensure_response($service);
    }
    
    public function delete_service($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        
        // Check if service exists
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('services')} WHERE id = %d",
            $id
        ));
        
        if (!$service) {
            return new WP_Error('not_found', 'Service not found', array('status' => 404));
        }
        
        $result = $wpdb->delete(
            $this->database->get_table('services'),
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete service', array('status' => 500));
        }
        
        return rest_ensure_response(array('message' => 'Service deleted successfully'));
    }
    
    /**
     * Permission check
     */
    public function check_permission($request) {
        return current_user_can('manage_options') || current_user_can('medx360_access');
    }
    
    /**
     * Get validation args for clinic and service endpoints
     */
    private function get_clinic_args() {
        return array(
            'name' => array('required' => true, 'type' => 'string'),
            'type' => array('required' => true, 'type' => 'string'),
            'address' => array('required' => true, 'type' => 'string'),
            'phone' => array('required' => false, 'type' => 'string'),
            'email' => array('required' => false, 'type' => 'string', 'format' => 'email'),
            'website' => array('required' => false, 'type' => 'string', 'format' => 'uri'),
            'license_number' => array('required' => false, 'type' => 'string'),
            'status' => array('required' => false, 'type' => 'string', 'enum' => array('active', 'inactive')),
            'established_date' => array('required' => false, 'type' => 'string', 'format' => 'date'),
            'services' => array('required' => false, 'type' => 'array'),
            'operating_hours' => array('required' => false, 'type' => 'object'),
            'notes' => array('required' => false, 'type' => 'string')
        );
    }
    
    private function get_service_args() {
        return array(
            'name' => array('required' => true, 'type' => 'string'),
            'category' => array('required' => true, 'type' => 'string'),
            'duration' => array('required' => false, 'type' => 'integer', 'default' => 30),
            'price' => array('required' => false, 'type' => 'number', 'default' => 0.00),
            'description' => array('required' => true, 'type' => 'string'),
            'status' => array('required' => false, 'type' => 'string', 'enum' => array('active', 'inactive')),
            'icon' => array('required' => false, 'type' => 'string'),
            'staff_assigned' => array('required' => false, 'type' => 'array'),
            'requirements' => array('required' => false, 'type' => 'string'),
            'preparation_instructions' => array('required' => false, 'type' => 'string'),
            'follow_up_instructions' => array('required' => false, 'type' => 'string'),
            'notes' => array('required' => false, 'type' => 'string')
        );
    }
}

// Initialize the API
new Medx360_ClinicServiceAPI();
