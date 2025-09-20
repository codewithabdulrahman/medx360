<?php

namespace MedX360\Infrastructure\WP\React;

/**
 * React Manager - Handles React application initialization and asset management
 * 
 * @package MedX360\Infrastructure\WP\React
 */
class ReactManager
{
    private $adminAssetsBuilt = false;
    private $frontendAssetsBuilt = false;

    /**
     * Initialize React components
     */
    public function init()
    {
        // Check if assets are built
        $this->checkAssetsBuilt();
        
        // Add WordPress hooks
        add_action('wp_head', [$this, 'addReactConfig']);
        add_action('admin_head', [$this, 'addReactConfig']);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets()
    {
        if (!$this->adminAssetsBuilt) {
            return;
        }

        // Enqueue React admin bundle
        wp_enqueue_script(
            'medx360-admin',
            MEDX360_URL . 'dist/admin/index.js',
            [],
            MEDX360_VERSION,
            true
        );

        wp_enqueue_style(
            'medx360-admin',
            MEDX360_URL . 'dist/admin/index.css',
            [],
            MEDX360_VERSION
        );

        // Localize script with WordPress data
        wp_localize_script('medx360-admin', 'medx360Admin', [
            'apiUrl' => rest_url(MEDX360_API_NAMESPACE),
            'nonce' => wp_create_nonce('wp_rest'),
            'currentUser' => wp_get_current_user(),
            'settings' => get_option('medx360_settings', []),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => MEDX360_URL,
            'strings' => $this->getAdminStrings(),
        ]);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueueFrontendAssets()
    {
        if (!$this->frontendAssetsBuilt) {
            return;
        }

        // Enqueue React frontend bundle
        wp_enqueue_script(
            'medx360-frontend',
            MEDX360_URL . 'dist/frontend/frontend.js',
            [],
            MEDX360_VERSION,
            true
        );

        wp_enqueue_style(
            'medx360-frontend',
            MEDX360_URL . 'dist/frontend/index-aace95af.css',
            [],
            MEDX360_VERSION
        );

        // Localize script with WordPress data
        wp_localize_script('medx360-frontend', 'medx360Frontend', [
            'apiUrl' => rest_url(MEDX360_API_NAMESPACE),
            'nonce' => wp_create_nonce('wp_rest'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => MEDX360_URL,
            'strings' => $this->getFrontendStrings(),
        ]);
    }

    /**
     * Add React configuration to page head
     */
    public function addReactConfig()
    {
        $config = [
            'apiUrl' => rest_url(MEDX360_API_NAMESPACE),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => MEDX360_URL,
            'version' => MEDX360_VERSION,
        ];

        echo '<script type="text/javascript">';
        echo 'window.medx360Config = ' . json_encode($config) . ';';
        echo '</script>';
    }

    /**
     * Check if React assets are built
     */
    private function checkAssetsBuilt()
    {
        $adminJsPath = MEDX360_PATH . '/dist/admin/index.js';
        $frontendJsPath = MEDX360_PATH . '/dist/frontend/frontend.js';

        $this->adminAssetsBuilt = file_exists($adminJsPath);
        $this->frontendAssetsBuilt = file_exists($frontendJsPath);
    }

    /**
     * Get admin strings for localization
     */
    private function getAdminStrings()
    {
        return [
            'dashboard' => __('Dashboard', 'medx360'),
            'calendar' => __('Calendar', 'medx360'),
            'appointments' => __('Appointments', 'medx360'),
            'events' => __('Events', 'medx360'),
            'services' => __('Services', 'medx360'),
            'locations' => __('Locations', 'medx360'),
            'customers' => __('Customers', 'medx360'),
            'finance' => __('Finance', 'medx360'),
            'notifications' => __('Notifications', 'medx360'),
            'customize' => __('Customize', 'medx360'),
            'customFields' => __('Custom Fields', 'medx360'),
            'settings' => __('Settings', 'medx360'),
            'whatsNew' => __('What\'s New', 'medx360'),
            'liteVsPremium' => __('Lite vs Premium', 'medx360'),
            'loading' => __('Loading...', 'medx360'),
            'error' => __('Error', 'medx360'),
            'success' => __('Success', 'medx360'),
            'save' => __('Save', 'medx360'),
            'cancel' => __('Cancel', 'medx360'),
            'delete' => __('Delete', 'medx360'),
            'edit' => __('Edit', 'medx360'),
            'add' => __('Add', 'medx360'),
            'search' => __('Search', 'medx360'),
            'filter' => __('Filter', 'medx360'),
            'export' => __('Export', 'medx360'),
            'import' => __('Import', 'medx360'),
        ];
    }

    /**
     * Get frontend strings for localization
     */
    private function getFrontendStrings()
    {
        return [
            'bookAppointment' => __('Book Appointment', 'medx360'),
            'selectDate' => __('Select Date', 'medx360'),
            'selectTime' => __('Select Time', 'medx360'),
            'selectService' => __('Select Service', 'medx360'),
            'selectProvider' => __('Select Provider', 'medx360'),
            'patientInfo' => __('Patient Information', 'medx360'),
            'firstName' => __('First Name', 'medx360'),
            'lastName' => __('Last Name', 'medx360'),
            'email' => __('Email', 'medx360'),
            'phone' => __('Phone', 'medx360'),
            'dateOfBirth' => __('Date of Birth', 'medx360'),
            'gender' => __('Gender', 'medx360'),
            'male' => __('Male', 'medx360'),
            'female' => __('Female', 'medx360'),
            'other' => __('Other', 'medx360'),
            'notes' => __('Notes', 'medx360'),
            'confirmBooking' => __('Confirm Booking', 'medx360'),
            'bookingConfirmed' => __('Booking Confirmed', 'medx360'),
            'bookingError' => __('Booking Error', 'medx360'),
            'required' => __('Required', 'medx360'),
            'optional' => __('Optional', 'medx360'),
        ];
    }
}