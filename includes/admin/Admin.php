<?php
/**
 * Admin Class
 * Handles WordPress admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_Admin {
    
    public function __construct() {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_filter('plugin_action_links_' . MEDX360_PLUGIN_BASENAME, array($this, 'add_action_links'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // Register settings
        $this->register_settings();
        
        // Handle form submissions
        $this->handle_form_submissions();
    }
    
    /**
     * Register plugin settings
     */
    private function register_settings() {
        // General settings
        register_setting('medx360_settings', 'medx360_clinic_name');
        register_setting('medx360_settings', 'medx360_clinic_address');
        register_setting('medx360_settings', 'medx360_clinic_phone');
        register_setting('medx360_settings', 'medx360_clinic_email');
        register_setting('medx360_settings', 'medx360_default_appointment_duration');
        register_setting('medx360_settings', 'medx360_working_hours');
        register_setting('medx360_settings', 'medx360_currency');
        register_setting('medx360_settings', 'medx360_timezone');
        
        // Notification settings
        register_setting('medx360_settings', 'medx360_email_notifications');
        register_setting('medx360_settings', 'medx360_sms_notifications');
        register_setting('medx360_settings', 'medx360_notification_templates');
        
        // Premium settings
        register_setting('medx360_settings', 'medx360_premium_active');
        register_setting('medx360_settings', 'medx360_license_key');
        register_setting('medx360_settings', 'medx360_multi_location_enabled');
        register_setting('medx360_settings', 'medx360_advanced_staff_enabled');
        register_setting('medx360_settings', 'medx360_advanced_notifications_enabled');
        register_setting('medx360_settings', 'medx360_integrations_enabled');
        register_setting('medx360_settings', 'medx360_advanced_payments_enabled');
        register_setting('medx360_settings', 'medx360_advanced_reports_enabled');
    }
    
    /**
     * Handle form submissions
     */
    private function handle_form_submissions() {
        if (!isset($_POST['medx360_action'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['medx360_nonce'], 'medx360_admin_action')) {
            wp_die('Security check failed');
        }
        
        $action = sanitize_text_field($_POST['medx360_action']);
        
        switch ($action) {
            case 'save_settings':
                $this->save_settings();
                break;
            case 'activate_premium':
                $this->activate_premium();
                break;
            case 'deactivate_premium':
                $this->deactivate_premium();
                break;
            case 'export_data':
                $this->export_data();
                break;
            case 'import_data':
                $this->import_data();
                break;
            case 'reset_data':
                $this->reset_data();
                break;
        }
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        $settings = array(
            'medx360_clinic_name' => sanitize_text_field($_POST['clinic_name'] ?? ''),
            'medx360_clinic_address' => sanitize_textarea_field($_POST['clinic_address'] ?? ''),
            'medx360_clinic_phone' => sanitize_text_field($_POST['clinic_phone'] ?? ''),
            'medx360_clinic_email' => sanitize_email($_POST['clinic_email'] ?? ''),
            'medx360_default_appointment_duration' => intval($_POST['default_appointment_duration'] ?? 30),
            'medx360_working_hours' => json_encode($_POST['working_hours'] ?? array()),
            'medx360_currency' => sanitize_text_field($_POST['currency'] ?? 'USD'),
            'medx360_timezone' => sanitize_text_field($_POST['timezone'] ?? 'UTC'),
            'medx360_email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
            'medx360_sms_notifications' => isset($_POST['sms_notifications']) ? 1 : 0,
            'medx360_notification_templates' => json_encode($_POST['notification_templates'] ?? array())
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        // Update database settings table
        $this->update_database_settings($settings);
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        });
    }
    
    /**
     * Update database settings table
     */
    private function update_database_settings($settings) {
        global $wpdb;
        
        $settings_table = $wpdb->prefix . 'medx360_settings';
        
        foreach ($settings as $key => $value) {
            $setting_key = str_replace('medx360_', '', $key);
            $setting_type = $this->get_setting_type($value);
            
            $wpdb->replace(
                $settings_table,
                array(
                    'setting_key' => $setting_key,
                    'setting_value' => $value,
                    'setting_type' => $setting_type
                ),
                array('%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Get setting type based on value
     */
    private function get_setting_type($value) {
        if (is_bool($value) || $value === '1' || $value === '0') {
            return 'boolean';
        } elseif (is_numeric($value)) {
            return 'number';
        } elseif (is_array($value) || (is_string($value) && (strpos($value, '{') === 0 || strpos($value, '[') === 0))) {
            return 'json';
        } else {
            return 'string';
        }
    }
    
    /**
     * Activate premium features
     */
    private function activate_premium() {
        $license_key = sanitize_text_field($_POST['license_key'] ?? '');
        
        if (empty($license_key)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>License key is required!</p></div>';
            });
            return;
        }
        
        // Validate license key (implement your validation logic)
        $is_valid = $this->validate_license_key($license_key);
        
        if ($is_valid) {
            update_option('medx360_license_key', $license_key);
            update_option('medx360_premium_active', true);
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Premium features activated successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Invalid license key!</p></div>';
            });
        }
    }
    
    /**
     * Deactivate premium features
     */
    private function deactivate_premium() {
        update_option('medx360_premium_active', false);
        update_option('medx360_license_key', '');
        
        // Disable all premium features
        $premium_features = array(
            'medx360_multi_location_enabled',
            'medx360_advanced_staff_enabled',
            'medx360_advanced_notifications_enabled',
            'medx360_integrations_enabled',
            'medx360_advanced_payments_enabled',
            'medx360_advanced_reports_enabled'
        );
        
        foreach ($premium_features as $feature) {
            update_option($feature, false);
        }
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>Premium features deactivated successfully!</p></div>';
        });
    }
    
    /**
     * Validate license key
     */
    private function validate_license_key($license_key) {
        // Implement your license validation logic here
        // This would typically make an API call to your license server
        
        // For demo purposes, accept any non-empty license key
        return !empty($license_key);
    }
    
    /**
     * Export data
     */
    private function export_data() {
        $export_type = sanitize_text_field($_POST['export_type'] ?? 'all');
        
        $data = array();
        
        switch ($export_type) {
            case 'patients':
                $data = $this->export_patients();
                break;
            case 'appointments':
                $data = $this->export_appointments();
                break;
            case 'staff':
                $data = $this->export_staff();
                break;
            case 'payments':
                $data = $this->export_payments();
                break;
            case 'all':
                $data = $this->export_all_data();
                break;
        }
        
        if (!empty($data)) {
            $filename = 'medx360_export_' . $export_type . '_' . date('Y-m-d_H-i-s') . '.json';
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            echo json_encode($data, JSON_PRETTY_PRINT);
            exit;
        }
    }
    
    /**
     * Export patients data
     */
    private function export_patients() {
        global $wpdb;
        
        $patients_table = $wpdb->prefix . 'medx360_patients';
        return $wpdb->get_results("SELECT * FROM $patients_table", ARRAY_A);
    }
    
    /**
     * Export appointments data
     */
    private function export_appointments() {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'medx360_appointments';
        return $wpdb->get_results("SELECT * FROM $appointments_table", ARRAY_A);
    }
    
    /**
     * Export staff data
     */
    private function export_staff() {
        global $wpdb;
        
        $staff_table = $wpdb->prefix . 'medx360_staff';
        return $wpdb->get_results("SELECT * FROM $staff_table", ARRAY_A);
    }
    
    /**
     * Export payments data
     */
    private function export_payments() {
        global $wpdb;
        
        $payments_table = $wpdb->prefix . 'medx360_payments';
        return $wpdb->get_results("SELECT * FROM $payments_table", ARRAY_A);
    }
    
    /**
     * Export all data
     */
    private function export_all_data() {
        return array(
            'patients' => $this->export_patients(),
            'appointments' => $this->export_appointments(),
            'staff' => $this->export_staff(),
            'payments' => $this->export_payments(),
            'export_date' => current_time('mysql'),
            'plugin_version' => MEDX360_VERSION
        );
    }
    
    /**
     * Import data
     */
    private function import_data() {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Please select a valid file to import!</p></div>';
            });
            return;
        }
        
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        $data = json_decode($file_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Invalid JSON file!</p></div>';
            });
            return;
        }
        
        $imported = 0;
        
        if (isset($data['patients'])) {
            $imported += $this->import_patients($data['patients']);
        }
        
        if (isset($data['appointments'])) {
            $imported += $this->import_appointments($data['appointments']);
        }
        
        if (isset($data['staff'])) {
            $imported += $this->import_staff($data['staff']);
        }
        
        if (isset($data['payments'])) {
            $imported += $this->import_payments($data['payments']);
        }
        
        add_action('admin_notices', function() use ($imported) {
            echo '<div class="notice notice-success"><p>' . $imported . ' records imported successfully!</p></div>';
        });
    }
    
    /**
     * Import patients
     */
    private function import_patients($patients) {
        global $wpdb;
        
        $patients_table = $wpdb->prefix . 'medx360_patients';
        $imported = 0;
        
        foreach ($patients as $patient) {
            // Check if patient already exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $patients_table WHERE email = %s",
                $patient['email']
            ));
            
            if (!$existing) {
                $wpdb->insert($patients_table, $patient);
                $imported++;
            }
        }
        
        return $imported;
    }
    
    /**
     * Import appointments
     */
    private function import_appointments($appointments) {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'medx360_appointments';
        $imported = 0;
        
        foreach ($appointments as $appointment) {
            $wpdb->insert($appointments_table, $appointment);
            $imported++;
        }
        
        return $imported;
    }
    
    /**
     * Import staff
     */
    private function import_staff($staff) {
        global $wpdb;
        
        $staff_table = $wpdb->prefix . 'medx360_staff';
        $imported = 0;
        
        foreach ($staff as $staff_member) {
            // Check if staff already exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $staff_table WHERE email = %s",
                $staff_member['email']
            ));
            
            if (!$existing) {
                $wpdb->insert($staff_table, $staff_member);
                $imported++;
            }
        }
        
        return $imported;
    }
    
    /**
     * Import payments
     */
    private function import_payments($payments) {
        global $wpdb;
        
        $payments_table = $wpdb->prefix . 'medx360_payments';
        $imported = 0;
        
        foreach ($payments as $payment) {
            $wpdb->insert($payments_table, $payment);
            $imported++;
        }
        
        return $imported;
    }
    
    /**
     * Reset data
     */
    private function reset_data() {
        global $wpdb;
        
        $tables = array(
            'medx360_patients',
            'medx360_appointments',
            'medx360_staff',
            'medx360_payments',
            'medx360_notifications',
            'medx360_roles',
            'medx360_permissions'
        );
        
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $wpdb->query("TRUNCATE TABLE $table_name");
        }
        
        // Re-insert default data
        $database = new Medx360_Database();
        $database->insert_default_data();
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>All data has been reset to defaults!</p></div>';
        });
    }
    
    /**
     * Add action links to plugin page
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=medx360-settings') . '">' . __('Settings', 'medx360') . '</a>';
        array_unshift($links, $settings_link);
        
        return $links;
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        // Check if database tables exist
        if (!$this->check_database_tables()) {
            echo '<div class="notice notice-error"><p>';
            echo __('Medx360: Database tables are missing. Please deactivate and reactivate the plugin.', 'medx360');
            echo '</p></div>';
        }
        
        // Check if premium features are available
        if (!$this->is_premium_active()) {
            echo '<div class="notice notice-info"><p>';
            echo __('Medx360: Upgrade to premium to unlock advanced features! ', 'medx360');
            echo '<a href="' . admin_url('admin.php?page=medx360-settings') . '">' . __('Learn More', 'medx360') . '</a>';
            echo '</p></div>';
        }
    }
    
    /**
     * Check if database tables exist
     */
    private function check_database_tables() {
        global $wpdb;
        
        $required_tables = array(
            'medx360_patients',
            'medx360_appointments',
            'medx360_staff',
            'medx360_payments',
            'medx360_notifications',
            'medx360_roles',
            'medx360_permissions',
            'medx360_settings'
        );
        
        foreach ($required_tables as $table) {
            $table_name = $wpdb->prefix . $table;
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if premium is active
     */
    private function is_premium_active() {
        return get_option('medx360_premium_active', false);
    }
    
    /**
     * Admin enqueue scripts
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'medx360') === false) {
            return;
        }
        
        // Add admin-specific styles
        wp_add_inline_style('medx360-style', '
            .medx360-admin-notice {
                margin: 20px 0;
                padding: 15px;
                border-left: 4px solid #007cba;
                background: #f7f7f7;
            }
            .medx360-admin-notice h3 {
                margin-top: 0;
            }
            .medx360-admin-notice p {
                margin-bottom: 0;
            }
        ');
    }
}
