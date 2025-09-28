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
        add_action('rest_api_init', array($this, 'init_rest_api'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once MEDX360_PLUGIN_DIR . 'includes/class-database.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-migration.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-api-controller.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-auth.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-validator.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/class-onboarding.php';
        
        // API endpoints
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-clinics-api.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-hospitals-api.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-doctors-api.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-services-api.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-staff-api.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-bookings-api.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-payments-api.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-consultations-api.php';
        require_once MEDX360_PLUGIN_DIR . 'includes/api/class-onboarding-api.php';
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('medx360', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize database
        MedX360_Database::get_instance();
    }
    
    /**
     * Initialize REST API
     */
    public function init_rest_api() {
        // Initialize API controllers
        new MedX360_Clinics_API();
        new MedX360_Hospitals_API();
        new MedX360_Doctors_API();
        new MedX360_Services_API();
        new MedX360_Staff_API();
        new MedX360_Bookings_API();
        new MedX360_Payments_API();
        new MedX360_Consultations_API();
        new MedX360_Onboarding_API();
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
        
        add_submenu_page(
            'medx360',
            __('Settings', 'medx360'),
            __('Settings', 'medx360'),
            'manage_options',
            'medx360-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page() {
        echo '<div class="wrap medx360-admin">';
        echo '<h1>' . __('MedX360 Dashboard', 'medx360') . '</h1>';
        echo '<p>' . __('Welcome to MedX360 Medical Booking Management System.', 'medx360') . '</p>';
        
        // Setup Status
        echo '<div class="card">';
        echo '<h2>' . __('Setup Status', 'medx360') . '</h2>';
        echo '<p><strong>' . __('Status:', 'medx360') . '</strong> <span class="medx360-setup-status">' . __('Loading...', 'medx360') . '</span></p>';
        echo '<p><strong>' . __('Progress:', 'medx360') . '</strong> <div style="background: #f0f0f1; border-radius: 3px; height: 20px; margin: 5px 0;"><div class="medx360-progress-bar" style="background: #2271b1; height: 100%; border-radius: 3px; width: 0%; transition: width 0.3s ease;"></div></div></p>';
        echo '<p><strong>' . __('Next Step:', 'medx360') . '</strong> <span class="medx360-next-step">' . __('Loading...', 'medx360') . '</span></p>';
        echo '<p><button class="button-primary medx360-test-api">' . __('Test API', 'medx360') . '</button> <button class="button-primary medx360-start-setup">' . __('Start Setup', 'medx360') . '</button> <button class="button-primary medx360-complete-setup">' . __('Complete Setup', 'medx360') . '</button></p>';
        echo '</div>';
        
        // Statistics
        echo '<div class="card">';
        echo '<h2>' . __('System Statistics', 'medx360') . '</h2>';
        echo '<div class="stats-grid">';
        echo '<div class="stat-card medx360-stat-clinics"><h3>' . __('Clinics', 'medx360') . '</h3><p class="number">0</p><p class="label">' . __('Active Clinics', 'medx360') . '</p></div>';
        echo '<div class="stat-card medx360-stat-hospitals"><h3>' . __('Hospitals', 'medx360') . '</h3><p class="number">0</p><p class="label">' . __('Active Hospitals', 'medx360') . '</p></div>';
        echo '<div class="stat-card medx360-stat-doctors"><h3>' . __('Doctors', 'medx360') . '</h3><p class="number">0</p><p class="label">' . __('Active Doctors', 'medx360') . '</p></div>';
        echo '<div class="stat-card medx360-stat-services"><h3>' . __('Services', 'medx360') . '</h3><p class="number">0</p><p class="label">' . __('Available Services', 'medx360') . '</p></div>';
        echo '<div class="stat-card medx360-stat-staff"><h3>' . __('Staff', 'medx360') . '</h3><p class="number">0</p><p class="label">' . __('Active Staff', 'medx360') . '</p></div>';
        echo '<div class="stat-card medx360-stat-bookings"><h3>' . __('Bookings', 'medx360') . '</h3><p class="number">0</p><p class="label">' . __('Total Bookings', 'medx360') . '</p></div>';
        echo '</div>';
        echo '</div>';
        
        // API Information
        echo '<div class="card">';
        echo '<h2>' . __('API Information', 'medx360') . '</h2>';
        echo '<p><strong>' . __('API Base URL:', 'medx360') . '</strong> <code>' . rest_url('medx360/v1/') . '</code></p>';
        echo '<p><strong>' . __('Available Endpoints:', 'medx360') . '</strong></p>';
        echo '<ul>';
        echo '<li><code>GET /clinics</code> - ' . __('List clinics', 'medx360') . '</li>';
        echo '<li><code>GET /hospitals</code> - ' . __('List hospitals', 'medx360') . '</li>';
        echo '<li><code>GET /doctors</code> - ' . __('List doctors', 'medx360') . '</li>';
        echo '<li><code>GET /services</code> - ' . __('List services', 'medx360') . '</li>';
        echo '<li><code>GET /staff</code> - ' . __('List staff', 'medx360') . '</li>';
        echo '<li><code>GET /bookings</code> - ' . __('List bookings', 'medx360') . '</li>';
        echo '<li><code>GET /payments</code> - ' . __('List payments', 'medx360') . '</li>';
        echo '<li><code>GET /consultations</code> - ' . __('List consultations', 'medx360') . '</li>';
        echo '<li><code>GET /onboarding/status</code> - ' . __('Get setup status', 'medx360') . '</li>';
        echo '</ul>';
        echo '<p><strong>' . __('Authentication:', 'medx360') . '</strong> ' . __('Use WordPress nonce authentication. Include X-WP-Nonce header in requests.', 'medx360') . '</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Settings page callback
     */
    public function settings_page() {
        echo '<div class="wrap medx360-admin">';
        echo '<h1>' . __('MedX360 Settings', 'medx360') . '</h1>';
        
        // Get current settings
        $settings = get_option('medx360_settings', array());
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        
        // Booking Settings
        echo '<tr><th colspan="2"><h2>' . __('Booking Settings', 'medx360') . '</h2></th></tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="booking_advance_days">' . __('Booking Advance Days', 'medx360') . '</label></th>';
        echo '<td><input type="number" id="booking_advance_days" name="booking_advance_days" value="' . esc_attr($settings['booking_advance_days'] ?? 30) . '" min="1" max="365" />';
        echo '<p class="description">' . __('Maximum days in advance patients can book appointments', 'medx360') . '</p></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="booking_cancellation_hours">' . __('Cancellation Hours', 'medx360') . '</label></th>';
        echo '<td><input type="number" id="booking_cancellation_hours" name="booking_cancellation_hours" value="' . esc_attr($settings['booking_cancellation_hours'] ?? 24) . '" min="1" max="168" />';
        echo '<p class="description">' . __('Minimum hours before appointment for cancellation', 'medx360') . '</p></td>';
        echo '</tr>';
        
        // Notification Settings
        echo '<tr><th colspan="2"><h2>' . __('Notification Settings', 'medx360') . '</h2></th></tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="email_notifications">' . __('Email Notifications', 'medx360') . '</label></th>';
        echo '<td><input type="checkbox" id="email_notifications" name="email_notifications" value="1" ' . checked($settings['email_notifications'] ?? true, true, false) . ' />';
        echo '<p class="description">' . __('Send email notifications for bookings', 'medx360') . '</p></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="sms_notifications">' . __('SMS Notifications', 'medx360') . '</label></th>';
        echo '<td><input type="checkbox" id="sms_notifications" name="sms_notifications" value="1" ' . checked($settings['sms_notifications'] ?? false, true, false) . ' />';
        echo '<p class="description">' . __('Send SMS notifications for bookings', 'medx360') . '</p></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="reminder_notifications">' . __('Reminder Notifications', 'medx360') . '</label></th>';
        echo '<td><input type="checkbox" id="reminder_notifications" name="reminder_notifications" value="1" ' . checked($settings['reminder_notifications'] ?? true, true, false) . ' />';
        echo '<p class="description">' . __('Send reminder notifications before appointments', 'medx360') . '</p></td>';
        echo '</tr>';
        
        // System Settings
        echo '<tr><th colspan="2"><h2>' . __('System Settings', 'medx360') . '</h2></th></tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="timezone">' . __('Timezone', 'medx360') . '</label></th>';
        echo '<td><select id="timezone" name="timezone">';
        $timezones = timezone_identifiers_list();
        $current_timezone = $settings['timezone'] ?? wp_timezone_string();
        foreach ($timezones as $timezone) {
            echo '<option value="' . esc_attr($timezone) . '" ' . selected($current_timezone, $timezone, false) . '>' . esc_html($timezone) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('System timezone for appointments', 'medx360') . '</p></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="date_format">' . __('Date Format', 'medx360') . '</label></th>';
        echo '<td><input type="text" id="date_format" name="date_format" value="' . esc_attr($settings['date_format'] ?? 'Y-m-d') . '" />';
        echo '<p class="description">' . __('Date format (e.g., Y-m-d for 2024-01-15)', 'medx360') . '</p></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="time_format">' . __('Time Format', 'medx360') . '</label></th>';
        echo '<td><input type="text" id="time_format" name="time_format" value="' . esc_attr($settings['time_format'] ?? 'H:i') . '" />';
        echo '<p class="description">' . __('Time format (e.g., H:i for 24-hour, g:i A for 12-hour)', 'medx360') . '</p></td>';
        echo '</tr>';
        
        // Payment Settings
        echo '<tr><th colspan="2"><h2>' . __('Payment Settings', 'medx360') . '</h2></th></tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="currency">' . __('Currency', 'medx360') . '</label></th>';
        echo '<td><input type="text" id="currency" name="currency" value="' . esc_attr($settings['currency'] ?? 'USD') . '" maxlength="3" />';
        echo '<p class="description">' . __('Currency code (e.g., USD, EUR, GBP)', 'medx360') . '</p></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="currency_symbol">' . __('Currency Symbol', 'medx360') . '</label></th>';
        echo '<td><input type="text" id="currency_symbol" name="currency_symbol" value="' . esc_attr($settings['currency_symbol'] ?? '$') . '" maxlength="5" />';
        echo '<p class="description">' . __('Currency symbol (e.g., $, €, £)', 'medx360') . '</p></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="payment_gateway">' . __('Payment Gateway', 'medx360') . '</label></th>';
        echo '<td><select id="payment_gateway" name="payment_gateway">';
        $gateways = array('manual' => __('Manual', 'medx360'), 'stripe' => __('Stripe', 'medx360'), 'paypal' => __('PayPal', 'medx360'));
        $current_gateway = $settings['payment_gateway'] ?? 'manual';
        foreach ($gateways as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($current_gateway, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Default payment gateway', 'medx360') . '</p></td>';
        echo '</tr>';
        
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<button type="submit" class="button-primary medx360-save-settings">' . __('Save Settings', 'medx360') . '</button>';
        echo '</p>';
        
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'medx360') !== false) {
            wp_enqueue_script('medx360-admin', MEDX360_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MEDX360_VERSION, true);
            wp_enqueue_style('medx360-admin', MEDX360_PLUGIN_URL . 'assets/css/admin.css', array(), MEDX360_VERSION);
            
            wp_localize_script('medx360-admin', 'medx360', array(
                'api_url' => rest_url('medx360/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
                'strings' => array(
                    'loading' => __('Loading...', 'medx360'),
                    'error' => __('An error occurred', 'medx360'),
                    'success' => __('Success!', 'medx360'),
                )
            ));
        }
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
