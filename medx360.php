<?php
/**
 * Plugin Name: MedX360 - Medical Booking Management
 * Plugin URI: https://medx360.com
 * Description: A comprehensive medical booking management system with REST API support for clinics, hospitals, doctors, consultations, and appointments.
 * Version: 1.0.0
 * Author: MedX360 Team
 * Author URI: https://medx360.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: medx360
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MEDX360_VERSION', '1.0.0');
define('MEDX360_PLUGIN_FILE', __FILE__);
define('MEDX360_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MEDX360_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEDX360_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main MedX360 Plugin Class
 */
class MedX360 {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once MEDX360_PLUGIN_DIR . 'includes/class-database.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-ajax-controller.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-auth.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-validator.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-onboarding.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-logger.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-cache.php';
        
        // AJAX endpoints
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-clinics-ajax.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-hospitals-ajax.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-doctors-ajax.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-services-ajax.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-staff-ajax.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-bookings-ajax.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-payments-ajax.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-consultations-ajax.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/ajax/class-onboarding-ajax.php';
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('medx360', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize database
        MedX360_Database::get_instance();
        
        // Initialize AJAX actions
        $this->init_ajax();
        
        // Warm up cache
        MedX360_Cache::warm_up();
        
        // Log plugin initialization
        MedX360_Logger::info('MedX360 Plugin initialized', array(
            'version' => MEDX360_VERSION,
            'user_id' => get_current_user_id()
        ));
    }
    
    /**
     * Initialize AJAX
     */
    public function init_ajax() {
        // Prevent duplicate initialization
        static $initialized = false;
        if ($initialized) {
            return;
        }
        $initialized = true;
        
        // Initialize AJAX controllers
        $ajax_controllers = array(
            'MedX360_Clinics_AJAX',
            'MedX360_Hospitals_AJAX',
            'MedX360_Doctors_AJAX',
            'MedX360_Services_AJAX',
            'MedX360_Staff_AJAX',
            'MedX360_Bookings_AJAX',
            'MedX360_Payments_AJAX',
            'MedX360_Consultations_AJAX',
            'MedX360_Onboarding_AJAX'
        );
        
        foreach ($ajax_controllers as $controller_class) {
            if (class_exists($controller_class)) {
                $controller = new $controller_class();
                if (method_exists($controller, 'register_actions')) {
                    $controller->register_actions();
                }
            }
        }
        
        // Add test endpoint for debugging
        add_action('wp_ajax_medx360_test', array($this, 'ajax_test'));
        add_action('wp_ajax_nopriv_medx360_test', array($this, 'ajax_test'));
    }
    
    /**
     * AJAX test endpoint
     */
    public function ajax_test() {
        wp_send_json_success(array(
            'message' => __('MedX360 Plugin AJAX is working!', 'medx360'),
            'timestamp' => current_time('mysql'),
            'version' => MEDX360_VERSION
        ));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('MedX360', 'medx360'),
            __('MedX360', 'medx360'),
            'manage_options',
            'medx360',
            array($this, 'admin_page'),
            'dashicons-calendar-alt',
            30
        );
        
        add_submenu_page(
            'medx360',
            __('Dashboard', 'medx360'),
            __('Dashboard', 'medx360'),
            'manage_options',
            'medx360',
            array($this, 'admin_page')
        );
        
    }
    
    /**
     * Admin page callback - React Frontend
     */
    public function admin_page() {
        // Check if React frontend exists
        $react_frontend_path = MEDX360_PLUGIN_DIR . 'frontend/dist/index.html';
        
        if (!file_exists($react_frontend_path)) {
            // Fallback to basic admin interface if React frontend doesn't exist
            $this->admin_page_fallback();
            return;
        }
        
        // Serve React frontend
        echo '<div class="medx360-react-container">';
        echo '<div id="root"></div>';
        echo '</div>';
        
        // Load React frontend assets
        $this->load_react_assets();
    }
    
    /**
     * Load React frontend assets
     */
    private function load_react_assets() {
        // Get the built assets from dist folder
        $dist_path = MEDX360_PLUGIN_DIR . 'frontend/dist/';
        $dist_url = MEDX360_PLUGIN_URL . 'frontend/dist/';
        
        // Find the built CSS and JS files
        $css_files = glob($dist_path . '*.css');
        $js_files = glob($dist_path . '*.js');
        
        // Load CSS files
        foreach ($css_files as $css_file) {
            $css_filename = basename($css_file);
            echo '<link rel="stylesheet" href="' . esc_url($dist_url . $css_filename) . '">';
        }
        
        // Add custom CSS for WordPress admin integration
        $this->output_admin_styles();
        
        // Load JS files
        foreach ($js_files as $js_file) {
            $js_filename = basename($js_file);
            echo '<script src="' . esc_url($dist_url . $js_filename) . '"></script>';
        }
        
        // Pass WordPress data to React
        $this->output_wp_data_script();
    }
    
    /**
     * Output admin styles for React integration
     */
    private function output_admin_styles() {
        echo '<style>
            .medx360-react-container {
                margin: -20px -20px 0 -20px;
                min-height: calc(100vh - 32px);
                background: white;
                padding-top: 32px;
                position: relative;
                z-index: 1;
            }
            #root {
                min-height: calc(100vh - 32px);
            }
            .medx360-react-container * {
                box-sizing: border-box;
            }
            .medx360-react-container .fixed {
                position: relative !important;
            }
            .medx360-react-container .overflow-hidden {
                overflow: visible !important;
            }
            @media screen and (max-width: 782px) {
                .medx360-react-container {
                    padding-top: 46px;
                }
            }
            @media screen and (min-width: 783px) {
                .medx360-react-container {
                    padding-top: 32px;
                }
            }
        </style>';
    }
    
    /**
     * Output WordPress data script for React
     */
    private function output_wp_data_script() {
        $wp_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medx360_ajax'),
            'user' => wp_get_current_user(),
            'strings' => array(
                'loading' => __('Loading...', 'medx360'),
                'error' => __('An error occurred', 'medx360'),
                'success' => __('Success!', 'medx360')
            )
        );
        
        echo '<script>
            window.medx360 = ' . wp_json_encode($wp_data) . ';
            
            function adjustForAdminBar() {
                const adminBar = document.getElementById("wpadminbar");
                const container = document.querySelector(".medx360-react-container");
                
                if (adminBar && container) {
                    container.style.paddingTop = adminBar.offsetHeight + "px";
                }
            }
            
            document.addEventListener("DOMContentLoaded", adjustForAdminBar);
            window.addEventListener("resize", adjustForAdminBar);
            
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", adjustForAdminBar);
            } else {
                adjustForAdminBar();
            }
        </script>';
    }
    
    /**
     * Fallback admin page for when React frontend is not available
     */
    private function admin_page_fallback() {
        echo '<div class="wrap medx360-admin">';
        echo '<h1>' . __('MedX360 Dashboard', 'medx360') . '</h1>';
        echo '<div class="notice notice-warning">';
        echo '<p><strong>' . __('React Frontend Not Available', 'medx360') . '</strong></p>';
        echo '<p>' . __('The React frontend is not available. Please run "npm run build" in the frontend directory.', 'medx360') . '</p>';
        echo '</div>';
        
        // Basic AJAX information
        echo '<div class="card">';
        echo '<h2>' . __('AJAX Information', 'medx360') . '</h2>';
        echo '<p><strong>' . __('AJAX URL:', 'medx360') . '</strong> <code>' . admin_url('admin-ajax.php') . '</code></p>';
        echo '<p><strong>' . __('Available Actions:', 'medx360') . '</strong></p>';
        echo '<ul>';
        echo '<li><strong>Clinics:</strong> <code>medx360_get_clinics</code>, <code>medx360_create_clinic</code>, <code>medx360_update_clinic</code>, <code>medx360_delete_clinic</code></li>';
        echo '<li><strong>Hospitals:</strong> <code>medx360_get_hospitals</code>, <code>medx360_create_hospital</code>, <code>medx360_update_hospital</code>, <code>medx360_delete_hospital</code></li>';
        echo '<li><strong>Doctors:</strong> <code>medx360_get_doctors</code>, <code>medx360_create_doctor</code>, <code>medx360_update_doctor</code>, <code>medx360_delete_doctor</code></li>';
        echo '<li><strong>Services:</strong> <code>medx360_get_services</code>, <code>medx360_create_service</code>, <code>medx360_update_service</code>, <code>medx360_delete_service</code></li>';
        echo '<li><strong>Staff:</strong> <code>medx360_get_staff</code>, <code>medx360_create_staff</code>, <code>medx360_update_staff</code>, <code>medx360_delete_staff</code></li>';
        echo '<li><strong>Bookings:</strong> <code>medx360_get_bookings</code>, <code>medx360_create_booking</code>, <code>medx360_update_booking</code>, <code>medx360_delete_booking</code></li>';
        echo '<li><strong>Payments:</strong> <code>medx360_get_payments</code>, <code>medx360_create_payment</code>, <code>medx360_update_payment</code>, <code>medx360_refund_payment</code></li>';
        echo '<li><strong>Consultations:</strong> <code>medx360_get_consultations</code>, <code>medx360_create_consultation</code>, <code>medx360_update_consultation</code>, <code>medx360_complete_consultation</code></li>';
        echo '<li><strong>Onboarding:</strong> <code>medx360_get_onboarding_status</code>, <code>medx360_create_onboarding_clinic</code>, <code>medx360_complete_onboarding</code></li>';
        echo '<li><strong>Test:</strong> <code>medx360_test</code> - ' . __('Test AJAX connection', 'medx360') . '</li>';
        echo '</ul>';
        echo '<p><strong>' . __('Authentication:', 'medx360') . '</strong> ' . __('Use WordPress nonce authentication. Include nonce parameter in POST data.', 'medx360') . '</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    
    /**
     * Enqueue admin scripts - React Frontend
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'medx360') !== false) {
            $react_frontend_path = MEDX360_PLUGIN_DIR . 'frontend/dist/index.html';
            
            if (!file_exists($react_frontend_path)) {
                // Fallback to basic admin interface
                $this->enqueue_admin_fallback();
            }
        }
    }
    
    /**
     * Enqueue fallback admin assets
     */
    private function enqueue_admin_fallback() {
        wp_enqueue_script('medx360-admin', MEDX360_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MEDX360_VERSION, true);
        wp_enqueue_style('medx360-admin', MEDX360_PLUGIN_URL . 'assets/css/admin.css', array(), MEDX360_VERSION);
        
        wp_localize_script('medx360-admin', 'medx360', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medx360_ajax'),
            'strings' => array(
                'loading' => __('Loading...', 'medx360'),
                'error' => __('An error occurred', 'medx360'),
                'success' => __('Success!', 'medx360'),
            )
        ));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create/update database tables (only if needed)
        MedX360_Database::maybe_update();
        
        // Set default options
        add_option('medx360_version', MEDX360_VERSION);
        add_option('medx360_setup_completed', false);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
MedX360::get_instance();
