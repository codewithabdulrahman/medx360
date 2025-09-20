<?php
/**
 * REST API Endpoints
 * Handles all API requests for the Medx360 plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_RestAPI {
    
    private $namespace = 'medx360/v1';
    private $database;
    
    public function __construct() {
        $this->database = new Medx360_Database();
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register all REST API routes
     */
    public function register_routes() {
        // Dashboard endpoints
        $this->register_dashboard_routes();
        
        // Patient endpoints
        $this->register_patient_routes();
        
        // Appointment endpoints
        $this->register_appointment_routes();
        
        // Staff endpoints
        $this->register_staff_routes();
        
        // Clinic endpoints
        $this->register_clinic_routes();
        
        // Service endpoints
        $this->register_service_routes();
        
        // Payment endpoints
        $this->register_payment_routes();
        
        // Notification endpoints
        $this->register_notification_routes();
        
        // Role and permission endpoints
        $this->register_role_routes();
        
        // Settings endpoints
        $this->register_settings_routes();
        
        // Premium endpoints
        if ($this->is_premium_active()) {
            $this->register_premium_routes();
        }
    }
    
    /**
     * Register dashboard routes
     */
    private function register_dashboard_routes() {
        register_rest_route($this->namespace, '/dashboard/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_dashboard_stats'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'date_from' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days'))
                ),
                'date_to' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d')
                )
            )
        ));
        
        register_rest_route($this->namespace, '/dashboard/recent-activities', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_recent_activities'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10
                )
            )
        ));
    }
    
    /**
     * Register patient routes
     */
    private function register_patient_routes() {
        register_rest_route($this->namespace, '/patients', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_patients'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'page' => array('required' => false, 'type' => 'integer', 'default' => 1),
                'per_page' => array('required' => false, 'type' => 'integer', 'default' => 20),
                'search' => array('required' => false, 'type' => 'string'),
                'status' => array('required' => false, 'type' => 'string')
            )
        ));
        
        register_rest_route($this->namespace, '/patients', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_patient'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => $this->get_patient_args()
        ));
        
        register_rest_route($this->namespace, '/patients/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_patient'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route($this->namespace, '/patients/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_patient'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array_merge(
                array('id' => array('required' => true, 'type' => 'integer')),
                $this->get_patient_args()
            )
        ));
        
        register_rest_route($this->namespace, '/patients/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_patient'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
    }
    
    /**
     * Register appointment routes
     */
    private function register_appointment_routes() {
        register_rest_route($this->namespace, '/appointments', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_appointments'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'page' => array('required' => false, 'type' => 'integer', 'default' => 1),
                'per_page' => array('required' => false, 'type' => 'integer', 'default' => 20),
                'date_from' => array('required' => false, 'type' => 'string', 'format' => 'date'),
                'date_to' => array('required' => false, 'type' => 'string', 'format' => 'date'),
                'patient_id' => array('required' => false, 'type' => 'integer'),
                'staff_id' => array('required' => false, 'type' => 'integer'),
                'status' => array('required' => false, 'type' => 'string')
            )
        ));
        
        register_rest_route($this->namespace, '/appointments', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_appointment'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => $this->get_appointment_args()
        ));
        
        register_rest_route($this->namespace, '/appointments/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_appointment'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route($this->namespace, '/appointments/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_appointment'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array_merge(
                array('id' => array('required' => true, 'type' => 'integer')),
                $this->get_appointment_args()
            )
        ));
        
        register_rest_route($this->namespace, '/appointments/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_appointment'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route($this->namespace, '/appointments/calendar', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_calendar_appointments'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'month' => array('required' => false, 'type' => 'integer', 'default' => date('n')),
                'year' => array('required' => false, 'type' => 'integer', 'default' => date('Y')),
                'staff_id' => array('required' => false, 'type' => 'integer')
            )
        ));
    }
    
    /**
     * Register staff routes
     */
    private function register_staff_routes() {
        register_rest_route($this->namespace, '/staff', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_staff'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'page' => array('required' => false, 'type' => 'integer', 'default' => 1),
                'per_page' => array('required' => false, 'type' => 'integer', 'default' => 20),
                'search' => array('required' => false, 'type' => 'string'),
                'status' => array('required' => false, 'type' => 'string')
            )
        ));
        
        register_rest_route($this->namespace, '/staff', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_staff'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => $this->get_staff_args()
        ));
        
        register_rest_route($this->namespace, '/staff/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_staff_member'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route($this->namespace, '/staff/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_staff'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array_merge(
                array('id' => array('required' => true, 'type' => 'integer')),
                $this->get_staff_args()
            )
        ));
        
        register_rest_route($this->namespace, '/staff/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_staff'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
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
     * Register payment routes
     */
    private function register_payment_routes() {
        register_rest_route($this->namespace, '/payments', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_payments'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'page' => array('required' => false, 'type' => 'integer', 'default' => 1),
                'per_page' => array('required' => false, 'type' => 'integer', 'default' => 20),
                'date_from' => array('required' => false, 'type' => 'string', 'format' => 'date'),
                'date_to' => array('required' => false, 'type' => 'string', 'format' => 'date'),
                'patient_id' => array('required' => false, 'type' => 'integer'),
                'status' => array('required' => false, 'type' => 'string')
            )
        ));
        
        register_rest_route($this->namespace, '/payments', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_payment'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => $this->get_payment_args()
        ));
        
        register_rest_route($this->namespace, '/payments/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_payment'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route($this->namespace, '/payments/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_payment'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array_merge(
                array('id' => array('required' => true, 'type' => 'integer')),
                $this->get_payment_args()
            )
        ));
    }
    
    /**
     * Register notification routes
     */
    private function register_notification_routes() {
        register_rest_route($this->namespace, '/notifications', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notifications'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'page' => array('required' => false, 'type' => 'integer', 'default' => 1),
                'per_page' => array('required' => false, 'type' => 'integer', 'default' => 20),
                'is_read' => array('required' => false, 'type' => 'boolean'),
                'type' => array('required' => false, 'type' => 'string')
            )
        ));
        
        register_rest_route($this->namespace, '/notifications/(?P<id>\d+)/read', array(
            'methods' => 'PUT',
            'callback' => array($this, 'mark_notification_read'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route($this->namespace, '/notifications/mark-all-read', array(
            'methods' => 'PUT',
            'callback' => array($this, 'mark_all_notifications_read'),
            'permission_callback' => array($this, 'check_permission')
        ));
    }
    
    /**
     * Register role and permission routes
     */
    private function register_role_routes() {
        register_rest_route($this->namespace, '/roles', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_roles'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route($this->namespace, '/roles', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_role'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => $this->get_role_args()
        ));
        
        register_rest_route($this->namespace, '/roles/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_role'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array_merge(
                array('id' => array('required' => true, 'type' => 'integer')),
                $this->get_role_args()
            )
        ));
        
        register_rest_route($this->namespace, '/permissions', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_permissions'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'user_id' => array('required' => false, 'type' => 'integer')
            )
        ));
    }
    
    /**
     * Register settings routes
     */
    private function register_settings_routes() {
        register_rest_route($this->namespace, '/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_settings'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route($this->namespace, '/settings', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'settings' => array(
                    'required' => true,
                    'type' => 'object',
                    'description' => 'Settings object with key-value pairs'
                )
            )
        ));
        
        register_rest_route($this->namespace, '/settings/(?P<key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_setting'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'key' => array('required' => true, 'type' => 'string')
            )
        ));
    }
    
    /**
     * Register premium routes
     */
    private function register_premium_routes() {
        // Multi-location routes
        register_rest_route($this->namespace, '/locations', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_locations'),
            'permission_callback' => array($this, 'check_premium_permission')
        ));
        
        register_rest_route($this->namespace, '/locations', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_location'),
            'permission_callback' => array($this, 'check_premium_permission'),
            'args' => $this->get_location_args()
        ));
        
        // Advanced reporting routes
        register_rest_route($this->namespace, '/reports/generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_report'),
            'permission_callback' => array($this, 'check_premium_permission'),
            'args' => array(
                'type' => array('required' => true, 'type' => 'string'),
                'parameters' => array('required' => false, 'type' => 'object')
            )
        ));
        
        // Integration routes
        register_rest_route($this->namespace, '/integrations', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_integrations'),
            'permission_callback' => array($this, 'check_premium_permission')
        ));
        
        register_rest_route($this->namespace, '/integrations/(?P<id>\d+)/test', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_integration'),
            'permission_callback' => array($this, 'check_premium_permission'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer')
            )
        ));
    }
    
    /**
     * Dashboard Stats endpoint
     */
    public function get_dashboard_stats($request) {
        global $wpdb;
        
        $date_from = $request->get_param('date_from');
        $date_to = $request->get_param('date_to');
        
        $stats = array(
            'today_appointments' => $this->get_today_appointments_count(),
            'total_patients' => $this->get_total_patients_count(),
            'pending_payments' => $this->get_pending_payments_count(),
            'total_staff' => $this->get_total_staff_count(),
            'monthly_revenue' => $this->get_monthly_revenue($date_from, $date_to),
            'appointment_stats' => $this->get_appointment_stats($date_from, $date_to)
        );
        
        return rest_ensure_response($stats);
    }
    
    /**
     * Recent Activities endpoint
     */
    public function get_recent_activities($request) {
        global $wpdb;
        
        $limit = $request->get_param('limit');
        
        $activities = array();
        
        // Get recent appointments
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, p.first_name, p.last_name, s.first_name as staff_first_name, s.last_name as staff_last_name
             FROM {$this->database->get_table('appointments')} a
             LEFT JOIN {$this->database->get_table('patients')} p ON a.patient_id = p.id
             LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
             ORDER BY a.created_at DESC
             LIMIT %d",
            $limit
        ));
        
        foreach ($appointments as $appointment) {
            $activities[] = array(
                'type' => 'appointment',
                'message' => sprintf('New appointment booked for %s %s', $appointment->first_name, $appointment->last_name),
                'time' => $this->time_ago($appointment->created_at),
                'icon' => '📅'
            );
        }
        
        return rest_ensure_response($activities);
    }
    
    /**
     * Patient endpoints
     */
    public function get_patients($request) {
        global $wpdb;
        
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $search = $request->get_param('search');
        $status = $request->get_param('status');
        
        $offset = ($page - 1) * $per_page;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if ($search) {
            $where_conditions[] = "(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)";
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
        $total_query = "SELECT COUNT(*) FROM {$this->database->get_table('patients')} WHERE $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($total_query, $where_values));
        
        // Get patients
        $patients_query = "SELECT * FROM {$this->database->get_table('patients')} WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $patients = $wpdb->get_results($wpdb->prepare($patients_query, array_merge($where_values, array($per_page, $offset))));
        
        return rest_ensure_response(array(
            'data' => $patients,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }
    
    public function create_patient($request) {
        global $wpdb;
        
        $data = $request->get_json_params();
        
        // Validate required fields
        $required_fields = array('first_name', 'last_name', 'email', 'phone');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', "Field '$field' is required", array('status' => 400));
            }
        }
        
        // Check if email already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->database->get_table('patients')} WHERE email = %s",
            $data['email']
        ));
        
        if ($existing) {
            return new WP_Error('email_exists', 'Email already exists', array('status' => 400));
        }
        
        $result = $wpdb->insert(
            $this->database->get_table('patients'),
            array(
                'first_name' => sanitize_text_field($data['first_name']),
                'last_name' => sanitize_text_field($data['last_name']),
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone']),
                'date_of_birth' => !empty($data['date_of_birth']) ? sanitize_text_field($data['date_of_birth']) : null,
                'gender' => !empty($data['gender']) ? sanitize_text_field($data['gender']) : null,
                'address' => !empty($data['address']) ? sanitize_textarea_field($data['address']) : null,
                'emergency_contact_name' => !empty($data['emergency_contact_name']) ? sanitize_text_field($data['emergency_contact_name']) : null,
                'emergency_contact_phone' => !empty($data['emergency_contact_phone']) ? sanitize_text_field($data['emergency_contact_phone']) : null,
                'medical_history' => !empty($data['medical_history']) ? sanitize_textarea_field($data['medical_history']) : null,
                'allergies' => !empty($data['allergies']) ? sanitize_textarea_field($data['allergies']) : null,
                'insurance_provider' => !empty($data['insurance_provider']) ? sanitize_text_field($data['insurance_provider']) : null,
                'insurance_number' => !empty($data['insurance_number']) ? sanitize_text_field($data['insurance_number']) : null,
                'status' => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active'
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create patient', array('status' => 500));
        }
        
        $patient_id = $wpdb->insert_id;
        $patient = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('patients')} WHERE id = %d",
            $patient_id
        ));
        
        return rest_ensure_response($patient, 201);
    }
    
    public function get_patient($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        
        $patient = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('patients')} WHERE id = %d",
            $id
        ));
        
        if (!$patient) {
            return new WP_Error('not_found', 'Patient not found', array('status' => 404));
        }
        
        return rest_ensure_response($patient);
    }
    
    public function update_patient($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if patient exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('patients')} WHERE id = %d",
            $id
        ));
        
        if (!$existing) {
            return new WP_Error('not_found', 'Patient not found', array('status' => 404));
        }
        
        // Check if email is being changed and if it already exists
        if (!empty($data['email']) && $data['email'] !== $existing->email) {
            $email_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->database->get_table('patients')} WHERE email = %s AND id != %d",
                $data['email'], $id
            ));
            
            if ($email_exists) {
                return new WP_Error('email_exists', 'Email already exists', array('status' => 400));
            }
        }
        
        $update_data = array();
        $update_format = array();
        
        $allowed_fields = array(
            'first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender',
            'address', 'emergency_contact_name', 'emergency_contact_phone',
            'medical_history', 'allergies', 'insurance_provider', 'insurance_number', 'status'
        );
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = sanitize_text_field($data[$field]);
                $update_format[] = '%s';
            }
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data to update', array('status' => 400));
        }
        
        $result = $wpdb->update(
            $this->database->get_table('patients'),
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update patient', array('status' => 500));
        }
        
        $patient = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('patients')} WHERE id = %d",
            $id
        ));
        
        return rest_ensure_response($patient);
    }
    
    public function delete_patient($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        
        // Check if patient exists
        $patient = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('patients')} WHERE id = %d",
            $id
        ));
        
        if (!$patient) {
            return new WP_Error('not_found', 'Patient not found', array('status' => 404));
        }
        
        // Check if patient has appointments
        $appointments = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->database->get_table('appointments')} WHERE patient_id = %d",
            $id
        ));
        
        if ($appointments > 0) {
            return new WP_Error('has_appointments', 'Cannot delete patient with existing appointments', array('status' => 400));
        }
        
        $result = $wpdb->delete(
            $this->database->get_table('patients'),
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete patient', array('status' => 500));
        }
        
        return rest_ensure_response(array('message' => 'Patient deleted successfully'));
    }
    
    /**
     * Permission check methods
     */
    public function check_permission($request) {
        return current_user_can('manage_options') || current_user_can('medx360_access');
    }
    
    public function check_admin_permission($request) {
        return current_user_can('manage_options');
    }
    
    public function check_premium_permission($request) {
        return $this->check_permission($request) && $this->is_premium_active();
    }
    
    /**
     * Helper methods
     */
    private function is_premium_active() {
        return get_option('medx360_premium_active', false);
    }
    
    private function time_ago($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        
        return date('M j, Y', strtotime($datetime));
    }
    
    private function get_today_appointments_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->database->get_table('appointments')} WHERE appointment_date = CURDATE()");
    }
    
    private function get_total_patients_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->database->get_table('patients')} WHERE status = 'active'");
    }
    
    private function get_pending_payments_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->database->get_table('payments')} WHERE payment_status = 'pending'");
    }
    
    private function get_total_staff_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->database->get_table('staff')} WHERE status = 'active'");
    }
    
    private function get_monthly_revenue($date_from, $date_to) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->database->get_table('payments')} 
             WHERE payment_status = 'completed' AND payment_date BETWEEN %s AND %s",
            $date_from, $date_to
        ));
    }
    
    private function get_appointment_stats($date_from, $date_to) {
        global $wpdb;
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show
             FROM {$this->database->get_table('appointments')} 
             WHERE appointment_date BETWEEN %s AND %s",
            $date_from, $date_to
        ));
        
        return $stats;
    }
    
    /**
     * Get validation args for different endpoints
     */
    private function get_patient_args() {
        return array(
            'first_name' => array('required' => true, 'type' => 'string'),
            'last_name' => array('required' => true, 'type' => 'string'),
            'email' => array('required' => true, 'type' => 'string', 'format' => 'email'),
            'phone' => array('required' => true, 'type' => 'string'),
            'date_of_birth' => array('required' => false, 'type' => 'string', 'format' => 'date'),
            'gender' => array('required' => false, 'type' => 'string', 'enum' => array('male', 'female', 'other')),
            'address' => array('required' => false, 'type' => 'string'),
            'emergency_contact_name' => array('required' => false, 'type' => 'string'),
            'emergency_contact_phone' => array('required' => false, 'type' => 'string'),
            'medical_history' => array('required' => false, 'type' => 'string'),
            'allergies' => array('required' => false, 'type' => 'string'),
            'insurance_provider' => array('required' => false, 'type' => 'string'),
            'insurance_number' => array('required' => false, 'type' => 'string'),
            'status' => array('required' => false, 'type' => 'string', 'enum' => array('active', 'inactive', 'archived'))
        );
    }
    
    private function get_appointment_args() {
        return array(
            'patient_id' => array('required' => true, 'type' => 'integer'),
            'staff_id' => array('required' => true, 'type' => 'integer'),
            'appointment_date' => array('required' => true, 'type' => 'string', 'format' => 'date'),
            'appointment_time' => array('required' => true, 'type' => 'string', 'format' => 'time'),
            'duration' => array('required' => false, 'type' => 'integer', 'default' => 30),
            'status' => array('required' => false, 'type' => 'string', 'enum' => array('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show')),
            'appointment_type' => array('required' => false, 'type' => 'string', 'default' => 'consultation'),
            'notes' => array('required' => false, 'type' => 'string'),
            'cost' => array('required' => false, 'type' => 'number', 'default' => 0.00)
        );
    }
    
    private function get_staff_args() {
        return array(
            'user_id' => array('required' => false, 'type' => 'integer'),
            'first_name' => array('required' => true, 'type' => 'string'),
            'last_name' => array('required' => true, 'type' => 'string'),
            'email' => array('required' => true, 'type' => 'string', 'format' => 'email'),
            'phone' => array('required' => false, 'type' => 'string'),
            'specialty' => array('required' => false, 'type' => 'string'),
            'license_number' => array('required' => false, 'type' => 'string'),
            'hire_date' => array('required' => false, 'type' => 'string', 'format' => 'date'),
            'salary' => array('required' => false, 'type' => 'number'),
            'status' => array('required' => false, 'type' => 'string', 'enum' => array('active', 'inactive', 'terminated')),
            'working_hours' => array('required' => false, 'type' => 'string')
        );
    }
    
    private function get_payment_args() {
        return array(
            'patient_id' => array('required' => true, 'type' => 'integer'),
            'appointment_id' => array('required' => false, 'type' => 'integer'),
            'amount' => array('required' => true, 'type' => 'number'),
            'payment_method' => array('required' => false, 'type' => 'string', 'enum' => array('cash', 'card', 'insurance', 'bank_transfer', 'other')),
            'payment_status' => array('required' => false, 'type' => 'string', 'enum' => array('pending', 'completed', 'failed', 'refunded')),
            'transaction_id' => array('required' => false, 'type' => 'string'),
            'notes' => array('required' => false, 'type' => 'string')
        );
    }
    
    private function get_role_args() {
        return array(
            'name' => array('required' => true, 'type' => 'string'),
            'display_name' => array('required' => true, 'type' => 'string'),
            'description' => array('required' => false, 'type' => 'string'),
            'capabilities' => array('required' => false, 'type' => 'array'),
            'is_premium' => array('required' => false, 'type' => 'boolean', 'default' => false)
        );
    }
    
    private function get_location_args() {
        return array(
            'name' => array('required' => true, 'type' => 'string'),
            'address' => array('required' => true, 'type' => 'string'),
            'phone' => array('required' => false, 'type' => 'string'),
            'email' => array('required' => false, 'type' => 'string', 'format' => 'email'),
            'manager_id' => array('required' => false, 'type' => 'integer'),
            'timezone' => array('required' => false, 'type' => 'string', 'default' => 'UTC'),
            'is_active' => array('required' => false, 'type' => 'boolean', 'default' => true)
        );
    }
    
    private function get_clinic_args() {
        return array(
            'name' => array('required' => true, 'type' => 'string'),
            'address' => array('required' => true, 'type' => 'string'),
            'phone' => array('required' => false, 'type' => 'string'),
            'email' => array('required' => false, 'type' => 'string', 'format' => 'email'),
            'website' => array('required' => false, 'type' => 'string'),
            'description' => array('required' => false, 'type' => 'string'),
            'status' => array('required' => false, 'type' => 'string', 'enum' => array('active', 'inactive'))
        );
    }
    
    private function get_service_args() {
        return array(
            'name' => array('required' => true, 'type' => 'string'),
            'description' => array('required' => false, 'type' => 'string'),
            'price' => array('required' => true, 'type' => 'number'),
            'duration' => array('required' => false, 'type' => 'integer', 'default' => 30),
            'category' => array('required' => false, 'type' => 'string'),
            'status' => array('required' => false, 'type' => 'string', 'enum' => array('active', 'inactive'))
        );
    }
    
    // Additional endpoint implementations would go here...
    // For brevity, I'm including the structure and key methods
    // The remaining endpoints follow similar patterns
}
