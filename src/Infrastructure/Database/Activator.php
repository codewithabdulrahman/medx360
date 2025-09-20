<?php

namespace MedX360\Infrastructure\Database;

class Activator
{
    /**
     * Plugin activation hook
     */
    public static function activate()
    {
        // Create database tables
        $dbManager = new DatabaseManager();
        $dbManager->createTables();

        // Set default options
        self::setDefaultOptions();

        // Create default data
        self::createDefaultData();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook
     */
    public static function deactivate()
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall hook
     */
    public static function uninstall()
    {
        // Check if user wants to delete data
        $delete_data = get_option('medx360_delete_data_on_uninstall', false);
        
        if ($delete_data) {
            // Drop all custom tables
            $dbManager = new DatabaseManager();
            $dbManager->dropTables();

            // Delete all plugin options
            self::deletePluginOptions();
        }
    }

    /**
     * Set default plugin options
     */
    private static function setDefaultOptions()
    {
        $default_options = [
            'medx360_plugin_version' => MEDX360_VERSION,
            'medx360_database_version' => '1.0',
            'medx360_timezone' => get_option('timezone_string', 'UTC'),
            'medx360_date_format' => get_option('date_format', 'Y-m-d'),
            'medx360_time_format' => get_option('time_format', 'H:i'),
            'medx360_currency' => 'USD',
            'medx360_currency_symbol' => '$',
            'medx360_appointment_duration' => 30,
            'medx360_booking_advance_days' => 30,
            'medx360_booking_cancel_hours' => 24,
            'medx360_email_notifications' => true,
            'medx360_sms_notifications' => false,
            'medx360_require_confirmation' => true,
            'medx360_allow_online_booking' => true,
            'medx360_working_hours' => [
                'monday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                'tuesday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                'wednesday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                'thursday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                'friday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                'saturday' => ['start' => '09:00', 'end' => '13:00', 'enabled' => false],
                'sunday' => ['start' => '09:00', 'end' => '13:00', 'enabled' => false],
            ],
            'medx360_delete_data_on_uninstall' => false,
        ];

        foreach ($default_options as $option_name => $option_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $option_value);
            }
        }
    }

    /**
     * Create default data
     */
    private static function createDefaultData()
    {
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'medx360_';

        // Check if default location exists
        $location_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_prefix}locations WHERE name = %s",
                'Main Clinic'
            )
        );

        if (!$location_exists) {
            $wpdb->insert(
                $table_prefix . 'locations',
                [
                    'name' => 'Main Clinic',
                    'address' => '123 Main Street',
                    'city' => 'Your City',
                    'state' => 'Your State',
                    'postal_code' => '12345',
                    'country' => 'United States',
                    'phone' => '(555) 123-4567',
                    'email' => get_option('admin_email'),
                    'status' => 'active'
                ],
                ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
            );
        }

        // Check if default service exists
        $service_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_prefix}services WHERE name = %s",
                'General Consultation'
            )
        );

        if (!$service_exists) {
            $wpdb->insert(
                $table_prefix . 'services',
                [
                    'name' => 'General Consultation',
                    'description' => 'General medical consultation',
                    'duration' => 30,
                    'price' => 100.00,
                    'category' => 'Consultation',
                    'color' => '#007cba',
                    'status' => 'active'
                ],
                ['%s', '%s', '%d', '%f', '%s', '%s', '%s']
            );
        }
    }

    /**
     * Delete all plugin options
     */
    private static function deletePluginOptions()
    {
        $options_to_delete = [
            'medx360_plugin_version',
            'medx360_database_version',
            'medx360_timezone',
            'medx360_date_format',
            'medx360_time_format',
            'medx360_currency',
            'medx360_currency_symbol',
            'medx360_appointment_duration',
            'medx360_booking_advance_days',
            'medx360_booking_cancel_hours',
            'medx360_email_notifications',
            'medx360_sms_notifications',
            'medx360_require_confirmation',
            'medx360_allow_online_booking',
            'medx360_working_hours',
            'medx360_delete_data_on_uninstall',
        ];

        foreach ($options_to_delete as $option) {
            delete_option($option);
        }
    }
}
