<?php
/**
 * Base API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_API_Controller {
    
    protected $namespace = 'medx360/v1';
    protected $rest_base = '';
    
    public function __construct() {
        $this->register_routes();
    }
    
    /**
     * Register routes
     */
    public function register_routes() {
        // Override in child classes
    }
    
    /**
     * Check if user has permission
     */
    public function check_permission($request) {
        return current_user_can('manage_options');
    }
    
    /**
     * Check if user has read permission
     */
    public function check_read_permission($request) {
        return current_user_can('read');
    }
    
    /**
     * Get pagination parameters
     */
    protected function get_pagination_params($request) {
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $per_page = min($per_page, 100); // Limit to 100 items per page
        
        return array(
            'page' => $page,
            'per_page' => $per_page,
            'offset' => ($page - 1) * $per_page
        );
    }
    
    /**
     * Get search parameters
     */
    protected function get_search_params($request) {
        return array(
            'search' => $request->get_param('search') ?: '',
            'orderby' => $request->get_param('orderby') ?: 'id',
            'order' => $request->get_param('order') ?: 'DESC'
        );
    }
    
    /**
     * Get filter parameters
     */
    protected function get_filter_params($request) {
        $filters = array();
        
        // Common filters
        if ($request->get_param('status')) {
            $filters['status'] = $request->get_param('status');
        }
        
        if ($request->get_param('clinic_id')) {
            $filters['clinic_id'] = $request->get_param('clinic_id');
        }
        
        if ($request->get_param('hospital_id')) {
            $filters['hospital_id'] = $request->get_param('hospital_id');
        }
        
        return $filters;
    }
    
    /**
     * Format response data
     */
    protected function format_response($data, $status = 200) {
        return new WP_REST_Response($data, $status);
    }
    
    /**
     * Format error response
     */
    protected function format_error_response($message, $code = 'error', $status = 400) {
        return new WP_Error($code, $message, array('status' => $status));
    }
    
    /**
     * Validate required fields
     */
    protected function validate_required_fields($data, $required_fields) {
        $errors = array();
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required', 'medx360'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitize data
     */
    protected function sanitize_data($data, $fields) {
        $sanitized = array();
        
        foreach ($fields as $field => $type) {
            if (isset($data[$field])) {
                switch ($type) {
                    case 'text':
                        $sanitized[$field] = sanitize_text_field($data[$field]);
                        break;
                    case 'textarea':
                        $sanitized[$field] = sanitize_textarea_field($data[$field]);
                        break;
                    case 'email':
                        $sanitized[$field] = sanitize_email($data[$field]);
                        break;
                    case 'url':
                        $sanitized[$field] = esc_url_raw($data[$field]);
                        break;
                    case 'int':
                        $sanitized[$field] = intval($data[$field]);
                        break;
                    case 'float':
                        $sanitized[$field] = floatval($data[$field]);
                        break;
                    case 'date':
                        $sanitized[$field] = sanitize_text_field($data[$field]);
                        break;
                    case 'time':
                        $sanitized[$field] = sanitize_text_field($data[$field]);
                        break;
                    case 'json':
                        $sanitized[$field] = $data[$field]; // JSON data should be validated separately
                        break;
                    default:
                        $sanitized[$field] = sanitize_text_field($data[$field]);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get table name
     */
    protected function get_table_name($table) {
        return MedX360_Database::get_table_name($table);
    }
    
    /**
     * Build WHERE clause for queries
     */
    protected function build_where_clause($filters) {
        global $wpdb;
        
        $where_conditions = array();
        $where_values = array();
        
        foreach ($filters as $field => $value) {
            if ($value !== '' && $value !== null) {
                $where_conditions[] = "$field = %s";
                $where_values[] = $value;
            }
        }
        
        return array(
            'conditions' => $where_conditions,
            'values' => $where_values
        );
    }
    
    /**
     * Build search clause
     */
    protected function build_search_clause($search, $searchable_fields) {
        global $wpdb;
        
        if (empty($search) || empty($searchable_fields)) {
            return array('conditions' => array(), 'values' => array());
        }
        
        $search_conditions = array();
        $search_values = array();
        
        foreach ($searchable_fields as $field) {
            $search_conditions[] = "$field LIKE %s";
            $search_values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        return array(
            'conditions' => $search_conditions,
            'values' => $search_values
        );
    }
}
