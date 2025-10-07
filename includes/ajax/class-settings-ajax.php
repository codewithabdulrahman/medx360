<?php
/**
 * Settings AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Settings_AJAX extends MedX360_AJAX_Controller {
    public function register_actions() {
        // Read settings
        $this->register_ajax_action('get_settings', array($this, 'get_settings'));

        // Save settings (POST)
        $this->register_ajax_action('save_settings', array($this, 'save_settings'));
    }

    /**
     * Get current settings
     */
    public function get_settings() {
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }

        $settings = get_option('medx360_settings', array());

        $this->format_response($settings);
    }

    /**
     * Save settings
     */
    public function save_settings() {
        // Must be an admin
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }

        // Verify nonce for security
        if (!$this->verify_nonce()) {
            $this->format_error_response(__('Invalid nonce', 'medx360'), 'invalid_nonce', 403);
        }

        $data = $this->get_post_data();

        // Define allowed fields and types
        $fields = array(
            'booking_advance_days' => 'int',
            'booking_cancellation_hours' => 'int',
            'email_notifications' => 'int',
            'sms_notifications' => 'int',
            'reminder_notifications' => 'int',
            'timezone' => 'text',
            'date_format' => 'text',
            'time_format' => 'text',
            'currency' => 'text',
            'currency_symbol' => 'text',
            'payment_gateway' => 'text',
            'booking_confirmation' => 'int'
        );

        $sanitized = $this->sanitize_data($data, $fields);

        // Convert booleans (JS may send true/false) to ints where needed
        foreach (array('email_notifications', 'sms_notifications', 'reminder_notifications', 'booking_confirmation') as $flag) {
            if (isset($data[$flag])) {
                // Accept bool or string/int
                if (is_bool($data[$flag])) {
                    $sanitized[$flag] = $data[$flag] ? 1 : 0;
                } else {
                    $sanitized[$flag] = intval($data[$flag]);
                }
            }
        }

        // Basic validation
        $errors = array();
        if (isset($sanitized['booking_advance_days']) && $sanitized['booking_advance_days'] < 0) {
            $errors[] = __('booking_advance_days must be >= 0', 'medx360');
        }
        if (isset($sanitized['booking_cancellation_hours']) && $sanitized['booking_cancellation_hours'] < 0) {
            $errors[] = __('booking_cancellation_hours must be >= 0', 'medx360');
        }

        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }

        // Merge with existing settings
        $existing = get_option('medx360_settings', array());
        $new = array_merge($existing, $sanitized);

        // Persist settings
        $updated = update_option('medx360_settings', $new);

        if ($updated === false) {
            $this->format_error_response(__('Failed to save settings', 'medx360'), 'update_failed', 500);
        }

        $this->format_response(array(
            'message' => __('Settings saved successfully', 'medx360'),
            'settings' => $new
        ));
    }
}
