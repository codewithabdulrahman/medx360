<?php
/**
 * Base AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_AJAX_Controller {
    
    protected $action_prefix = 'medx360_';
    
    public function __construct() {
        // Don't register actions in constructor
        // Actions will be registered when init_ajax() is called
    }
    
    /**
     * Register AJAX actions
     */
    public function register_actions() {
        // Override in child classes
    }
    
    /**
     * Register AJAX action for both logged-in and non-logged-in users
     */
    protected function register_ajax_action($action, $callback, $require_auth = true) {
        $full_action = $this->action_prefix . $action;
        
        if ($require_auth) {
            add_action('wp_ajax_' . $full_action, $callback);
        } else {
            add_action('wp_ajax_nopriv_' . $full_action, $callback);
        }
        
        // Also register for logged-in users if not requiring auth
        if (!$require_auth) {
            add_action('wp_ajax_' . $full_action, $callback);
        }
    }
    
    /**
     * Check if user has permission
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Check if user has read permission
     */
    public function check_read_permission() {
        return current_user_can('read');
    }
    
    /**
     * Get pagination parameters from request
     */
    protected function get_pagination_params() {
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        $per_page = min($per_page, 100); // Limit to 100 items per page
        
        return array(
            'page' => $page,
            'per_page' => $per_page,
            'offset' => ($page - 1) * $per_page
        );
    }
    
    /**
     * Get search parameters from request
     */
    protected function get_search_params() {
        return array(
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
            'orderby' => isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'id',
            'order' => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC'
        );
    }
    
    /**
     * Get filter parameters from request
     */
    protected function get_filter_params() {
        $filters = array();
        
        // Common filters
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            $filters['status'] = sanitize_text_field($_POST['status']);
        }
        
        if (isset($_POST['clinic_id']) && !empty($_POST['clinic_id'])) {
            $filters['clinic_id'] = intval($_POST['clinic_id']);
        }
        
        if (isset($_POST['hospital_id']) && !empty($_POST['hospital_id'])) {
            $filters['hospital_id'] = intval($_POST['hospital_id']);
        }
        
        return $filters;
    }
    
    /**
     * Format response data
     */
    protected function format_response($data, $status = 'success') {
        wp_send_json_success($data);
    }
    
    /**
     * Format error response
     */
    protected function format_error_response($message, $code = 'error', $status_code = 400) {
        wp_send_json_error(array(
            'code' => $code,
            'message' => $message,
            'status' => $status_code
        ));
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
    
    /**
     * Get POST data
     */
    protected function get_post_data() {
        return $_POST;
    }
    
    /**
     * Get JSON POST data
     */
    protected function get_json_post_data() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
    
    /**
     * Verify nonce
     */
    protected function verify_nonce($nonce = null) {
        if ($nonce === null) {
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        }
        
        return wp_verify_nonce($nonce, 'medx360_ajax');
    }
}
