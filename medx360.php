<?php
/**
 * Plugin Name: Medx360
 * Description: React-based WordPress plugin for Medx360 booking system.
 * Version: 1.0.0
 * Author: Medx360 Team
 * Text Domain: medx360
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
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

// Include required files
require_once MEDX360_PLUGIN_DIR . 'includes/database/Database.php';
require_once MEDX360_PLUGIN_DIR . 'includes/models/BaseModel.php';
require_once MEDX360_PLUGIN_DIR . 'includes/models/Patient.php';
require_once MEDX360_PLUGIN_DIR . 'includes/models/Appointment.php';
require_once MEDX360_PLUGIN_DIR . 'includes/models/Staff.php';
require_once MEDX360_PLUGIN_DIR . 'includes/models/Payment.php';
require_once MEDX360_PLUGIN_DIR . 'includes/api/RestAPI.php';
require_once MEDX360_PLUGIN_DIR . 'includes/utils/Helper.php';
require_once MEDX360_PLUGIN_DIR . 'includes/admin/Admin.php';

/**
 * Main Plugin Class
 */
class Medx360_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->init_classes();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Add custom capabilities
        add_action('init', array($this, 'add_custom_capabilities'));
    }
    
    /**
     * Initialize classes
     */
    private function init_classes() {
        new Medx360_Database();
        new Medx360_RestAPI();
        new Medx360_Admin();
    }
    
    /**
     * Plugin initialization
     */
    public function init() {
        load_plugin_textdomain('medx360', false, dirname(plugin_basename(__FILE__)) . '/languages');
        $this->check_premium_status();
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our plugin page
        if ($hook !== 'toplevel_page_medx360') {
            return;
        }
        
        wp_enqueue_script(
            'medx360-react',
            MEDX360_PLUGIN_URL . 'build/index.js',
            array('wp-element', 'wp-api-fetch'),
            filemtime(MEDX360_PLUGIN_DIR . 'build/index.js'),
            true
        );
        
        wp_enqueue_style(
            'medx360-style',
            MEDX360_PLUGIN_URL . 'build/index.css',
            array(),
            filemtime(MEDX360_PLUGIN_DIR . 'build/index.css')
        );
        
        // Localize script with WordPress data
        wp_localize_script('medx360-react', 'medx360Data', array(
            'apiUrl' => rest_url('medx360/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'user' => array(
                'id' => get_current_user_id(),
                'name' => wp_get_current_user()->display_name,
                'email' => wp_get_current_user()->user_email,
                'roles' => wp_get_current_user()->roles
            ),
            'settings' => $this->get_plugin_settings(),
            'premium' => array(
                'active' => get_option('medx360_premium_active', false),
                'features' => $this->get_premium_features()
            )
        ));
    }
    
    /**
     * Add single admin menu page
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Medx360', 'medx360'),
            __('Medx360', 'medx360'),
            'medx360_access',
            'medx360',
            array($this, 'render_admin_page'),
            'dashicons-calendar-alt',
            30
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <div id="medx360-react-app"></div>
        </div>
        <?php
    }
    
    /**
     * Add custom capabilities
     */
    public function add_custom_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('medx360_access');
            $role->add_cap('medx360_premium_access');
            $role->add_cap('medx360_manage_patients');
            $role->add_cap('medx360_manage_appointments');
            $role->add_cap('medx360_manage_staff');
            $role->add_cap('medx360_manage_payments');
            $role->add_cap('medx360_view_reports');
            $role->add_cap('medx360_manage_settings');
        }
        
        $editor_role = get_role('editor');
        if ($editor_role) {
            $editor_role->add_cap('medx360_access');
            $editor_role->add_cap('medx360_manage_patients');
            $editor_role->add_cap('medx360_manage_appointments');
            $editor_role->add_cap('medx360_view_reports');
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $database = new Medx360_Database();
        $database->create_tables();
        
        add_option('medx360_version', MEDX360_VERSION);
        add_option('medx360_premium_active', false);
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Check premium status
     */
    private function check_premium_status() {
        $premium_active = get_option('medx360_premium_active', false);
        
        if (!$premium_active) {
            $license_key = get_option('medx360_license_key', '');
            if (!empty($license_key)) {
                $is_valid = $this->validate_license($license_key);
                update_option('medx360_premium_active', $is_valid);
            }
        }
    }
    
    /**
     * Validate license key
     */
    private function validate_license($license_key) {
        // Implement license validation logic
        return false; // Placeholder
    }
    
    /**
     * Get plugin settings
     */
    private function get_plugin_settings() {
        global $wpdb;
        
        $settings_table = $wpdb->prefix . 'medx360_settings';
        $settings = $wpdb->get_results("SELECT setting_key, setting_value, setting_type FROM $settings_table");
        
        $formatted_settings = array();
        foreach ($settings as $setting) {
            $value = $setting->setting_value;
            
            switch ($setting->setting_type) {
                case 'boolean':
                    $value = (bool) $value;
                    break;
                case 'number':
                    $value = is_numeric($value) ? (float) $value : $value;
                    break;
                case 'json':
                case 'array':
                    $value = json_decode($value, true);
                    break;
            }
            
            $formatted_settings[$setting->setting_key] = $value;
        }
        
        return $formatted_settings;
    }
    
    /**
     * Get premium features
     */
    private function get_premium_features() {
        return array(
            'multi_location' => array(
                'name' => __('Multi-Location Management', 'medx360'),
                'description' => __('Manage multiple clinic locations', 'medx360'),
                'active' => get_option('medx360_multi_location_enabled', false)
            ),
            'advanced_staff' => array(
                'name' => __('Advanced Staff Management', 'medx360'),
                'description' => __('Advanced staff scheduling and resource management', 'medx360'),
                'active' => get_option('medx360_advanced_staff_enabled', false)
            ),
            'advanced_notifications' => array(
                'name' => __('Advanced Notifications', 'medx360'),
                'description' => __('Custom notification templates and multi-channel notifications', 'medx360'),
                'active' => get_option('medx360_advanced_notifications_enabled', false)
            ),
            'integrations' => array(
                'name' => __('Integrations', 'medx360'),
                'description' => __('Third-party integrations and API connections', 'medx360'),
                'active' => get_option('medx360_integrations_enabled', false)
            ),
            'advanced_payments' => array(
                'name' => __('Advanced Payments', 'medx360'),
                'description' => __('Advanced payment processing and insurance billing', 'medx360'),
                'active' => get_option('medx360_advanced_payments_enabled', false)
            ),
            'advanced_reports' => array(
                'name' => __('Advanced Reports', 'medx360'),
                'description' => __('Advanced analytics and custom reports', 'medx360'),
                'active' => get_option('medx360_advanced_reports_enabled', false)
            )
        );
    }
}

// Initialize the plugin
Medx360_Plugin::get_instance();
