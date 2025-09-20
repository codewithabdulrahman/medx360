<?php
/*
Plugin Name: MedX360
Plugin URI: https://medx360.com/
Description: A comprehensive healthcare appointment booking system built with React, designed specifically for medical practices, hospitals, and healthcare organizations with HIPAA compliance.
Version: 1.0.0
Author: MedX360 Solutions
Author URI: https://medx360.com/
Text Domain: medx360
Domain Path: /languages
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

namespace MedX360;

use MedX360\Infrastructure\Common\Container;
use MedX360\Infrastructure\Routes\Routes;
use MedX360\Infrastructure\WP\Admin\AdminMenu;
use MedX360\Infrastructure\WP\Security\SecurityManager;
use MedX360\Infrastructure\Database\DatabaseManager;
use MedX360\Infrastructure\Database\Activator;
use MedX360\Infrastructure\WP\React\ReactManager;

// Prevent direct access
defined('ABSPATH') or die('No direct access allowed');

// Plugin constants
if (!defined('MEDX360_PATH')) {
    define('MEDX360_PATH', __DIR__);
}

if (!defined('MEDX360_URL')) {
    define('MEDX360_URL', plugin_dir_url(__FILE__));
}

if (!defined('MEDX360_VERSION')) {
    define('MEDX360_VERSION', '1.0.0');
}

if (!defined('MEDX360_API_NAMESPACE')) {
    define('MEDX360_API_NAMESPACE', 'medx360/v1');
}

// Autoloader
require_once MEDX360_PATH . '/vendor/autoload.php';

/**
 * Main Plugin Class
 * 
 * @package MedX360
 */
class MedX360Plugin
{
    private $container;
    private $reactManager;
    private $databaseManager;
    private $securityManager;
    private $adminMenu;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize the plugin
     */
    private function init()
    {
        // Initialize core components
        $this->container = new \MedX360\Infrastructure\Common\Container();
        $this->reactManager = new \MedX360\Infrastructure\WP\React\ReactManager();
        $this->databaseManager = new \MedX360\Infrastructure\Database\DatabaseManager();
        $this->adminMenu = new \MedX360\Infrastructure\WP\Admin\AdminMenu();
        // Temporarily comment out SecurityManager to debug
        // $this->securityManager = new \MedX360\Infrastructure\WP\Security\SecurityManager();

        // Initialize admin menu immediately to avoid permission issues
        $this->adminMenu->init();

        // Hook into WordPress
        add_action('init', [$this, 'initPlugin']);
        add_action('admin_init', [$this, 'adminInit']);
        add_action('rest_api_init', [$this, 'initRestApi']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        // Activation and deactivation hooks
        register_activation_hook(__FILE__, [Activator::class, 'activate']);
        register_deactivation_hook(__FILE__, [Activator::class, 'deactivate']);
        register_uninstall_hook(__FILE__, [Activator::class, 'uninstall']);
    }

    /**
     * Plugin initialization
     */
    public function initPlugin()
    {
        // Initialize React components
        $this->reactManager->init();

        // Database initialization is handled by activation hooks
        // No need to call init() on DatabaseManager

        // Initialize security - temporarily commented out
        // $this->securityManager->init();
    }

    /**
     * Admin initialization
     */
    public function adminInit()
    {
        // Admin-specific initialization can go here
    }

    /**
     * Initialize REST API
     */
    public function initRestApi()
    {
        $routes = new Routes($this->container);
        $routes->init();
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueueFrontendAssets()
    {
        // Only enqueue on pages that use the booking form
        if (is_singular() && (has_shortcode(get_post()->post_content, 'medx360_booking') || has_block('medx360/booking-form'))) {
            $this->reactManager->enqueueFrontendAssets();
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets($hook)
    {
        // Only enqueue on MedX360 admin pages
        if (strpos($hook, 'medx360') !== false) {
            $this->reactManager->enqueueAdminAssets();
        }
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // This method is now handled by Activator class
        Activator::activate();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // This method is now handled by Activator class
        Activator::deactivate();
    }

    /**
     * Plugin uninstall
     */
    public static function uninstall()
    {
        // This method is now handled by Activator class
        Activator::uninstall();
    }
}

// Initialize the plugin
new MedX360Plugin();

// Shortcode for booking form
add_shortcode('medx360_booking', function($atts) {
    $atts = shortcode_atts([
        'location' => '',
        'service' => '',
        'provider' => '',
    ], $atts);

    return '<div id="medx360-booking-app" data-location="' . esc_attr($atts['location']) . '" data-service="' . esc_attr($atts['service']) . '" data-provider="' . esc_attr($atts['provider']) . '"></div>';
});

// Gutenberg block registration
add_action('init', function() {
    if (function_exists('register_block_type')) {
        register_block_type('medx360/booking-form', [
            'editor_script' => 'medx360-frontend',
            'editor_style' => 'medx360-frontend',
            'style' => 'medx360-frontend',
            'render_callback' => function($attributes) {
                $location = $attributes['location'] ?? '';
                $service = $attributes['service'] ?? '';
                $provider = $attributes['provider'] ?? '';
                
                return '<div id="medx360-booking-app" data-location="' . esc_attr($location) . '" data-service="' . esc_attr($service) . '" data-provider="' . esc_attr($provider) . '"></div>';
            },
        ]);
    }
});

// Add admin notice for successful activation
add_action('admin_notices', function() {
    if (get_option('medx360_activated')) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>MedX360</strong> has been activated successfully! You can now access it from the admin menu.</p>';
        echo '</div>';
        delete_option('medx360_activated');
    }
});