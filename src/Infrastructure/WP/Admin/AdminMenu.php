<?php

namespace MedX360\Infrastructure\WP\Admin;

/**
 * Admin Menu Manager
 * 
 * @package MedX360\Infrastructure\WP\Admin
 */
class AdminMenu
{
    /**
     * Initialize admin menu
     */
    public function init()
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
    }

    /**
     * Add admin menu pages
     */
    public function addAdminMenu()
    {
        // Main menu page (Dashboard)
        add_menu_page(
            __('MedX360', 'medx360'),
            __('MedX360', 'medx360'),
            'manage_options',
            'medx360',
            [$this, 'renderDashboard'],
            'dashicons-calendar-alt',
            30
        );

        // Submenu pages
        add_submenu_page(
            'medx360',
            __('Dashboard', 'medx360'),
            __('Dashboard', 'medx360'),
            'manage_options',
            'medx360',
            [$this, 'renderDashboard']
        );

        add_submenu_page(
            'medx360',
            __('Calendar', 'medx360'),
            __('Calendar', 'medx360'),
            'manage_options',
            'medx360-calendar',
            [$this, 'renderCalendar']
        );

        add_submenu_page(
            'medx360',
            __('Appointments', 'medx360'),
            __('Appointments', 'medx360'),
            'manage_options',
            'medx360-appointments',
            [$this, 'renderAppointments']
        );

        add_submenu_page(
            'medx360',
            __('Events', 'medx360'),
            __('Events', 'medx360'),
            'manage_options',
            'medx360-events',
            [$this, 'renderEvents']
        );

        add_submenu_page(
            'medx360',
            __('Services', 'medx360'),
            __('Services', 'medx360'),
            'manage_options',
            'medx360-services',
            [$this, 'renderServices']
        );

        add_submenu_page(
            'medx360',
            __('Locations', 'medx360'),
            __('Locations', 'medx360'),
            'manage_options',
            'medx360-locations',
            [$this, 'renderLocations']
        );

        add_submenu_page(
            'medx360',
            __('Customers', 'medx360'),
            __('Customers', 'medx360'),
            'manage_options',
            'medx360-customers',
            [$this, 'renderCustomers']
        );

        add_submenu_page(
            'medx360',
            __('Finance', 'medx360'),
            __('Finance', 'medx360'),
            'manage_options',
            'medx360-finance',
            [$this, 'renderFinance']
        );

        add_submenu_page(
            'medx360',
            __('Notifications', 'medx360'),
            __('Notifications', 'medx360'),
            'manage_options',
            'medx360-notifications',
            [$this, 'renderNotifications']
        );

        add_submenu_page(
            'medx360',
            __('Customize', 'medx360'),
            __('Customize', 'medx360'),
            'manage_options',
            'medx360-customize',
            [$this, 'renderCustomize']
        );

        add_submenu_page(
            'medx360',
            __('Custom Fields', 'medx360'),
            __('Custom Fields', 'medx360'),
            'manage_options',
            'medx360-custom-fields',
            [$this, 'renderCustomFields']
        );

        add_submenu_page(
            'medx360',
            __('Settings', 'medx360'),
            __('Settings', 'medx360'),
            'manage_options',
            'medx360-settings',
            [$this, 'renderSettings']
        );

        add_submenu_page(
            'medx360',
            __('What\'s New', 'medx360'),
            __('What\'s New', 'medx360'),
            'manage_options',
            'medx360-whats-new',
            [$this, 'renderWhatsNew']
        );

        add_submenu_page(
            'medx360',
            __('Lite vs Premium', 'medx360'),
            __('Lite vs Premium', 'medx360'),
            'manage_options',
            'medx360-lite-vs-premium',
            [$this, 'renderLiteVsPremium']
        );
    }

    /**
     * Render Dashboard page
     */
    public function renderDashboard()
    {
        $this->renderPage('dashboard');
    }

    /**
     * Render Calendar page
     */
    public function renderCalendar()
    {
        $this->renderPage('calendar');
    }

    /**
     * Render Appointments page
     */
    public function renderAppointments()
    {
        $this->renderPage('appointments');
    }

    /**
     * Render Events page
     */
    public function renderEvents()
    {
        $this->renderPage('events');
    }

    /**
     * Render Services page
     */
    public function renderServices()
    {
        $this->renderPage('services');
    }

    /**
     * Render Locations page
     */
    public function renderLocations()
    {
        $this->renderPage('locations');
    }

    /**
     * Render Customers page
     */
    public function renderCustomers()
    {
        $this->renderPage('customers');
    }

    /**
     * Render Finance page
     */
    public function renderFinance()
    {
        $this->renderPage('finance');
    }

    /**
     * Render Notifications page
     */
    public function renderNotifications()
    {
        $this->renderPage('notifications');
    }

    /**
     * Render Customize page
     */
    public function renderCustomize()
    {
        $this->renderPage('customize');
    }

    /**
     * Render Custom Fields page
     */
    public function renderCustomFields()
    {
        $this->renderPage('custom-fields');
    }

    /**
     * Render Settings page
     */
    public function renderSettings()
    {
        $this->renderPage('settings');
    }

    /**
     * Render What's New page
     */
    public function renderWhatsNew()
    {
        $this->renderPage('whats-new');
    }

    /**
     * Render Lite vs Premium page
     */
    public function renderLiteVsPremium()
    {
        $this->renderPage('lite-vs-premium');
    }

    /**
     * Generic page renderer
     */
    private function renderPage($pageSlug)
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <div id="medx360-admin" data-page="<?php echo esc_attr($pageSlug); ?>"></div>
        </div>
        <?php
    }
}