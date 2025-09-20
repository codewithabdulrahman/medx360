<?php

namespace MedX360\Infrastructure\Routes;

use MedX360\Infrastructure\Common\Container;
use MedX360\Infrastructure\Routes\Dashboard\DashboardController;
use MedX360\Infrastructure\Routes\Services\ServicesController;

/**
 * REST API Routes Manager
 * 
 * @package MedX360\Infrastructure\Routes
 */
class Routes
{
    private $container;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Initialize routes
     */
    public function init()
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register all REST API routes
     */
    public function registerRoutes()
    {
        $namespace = MEDX360_API_NAMESPACE;

        // Dashboard routes
        register_rest_route($namespace, '/dashboard/stats', [
            'methods' => 'GET',
            'callback' => [$this->getDashboardController(), 'getStats'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/dashboard/recent-appointments', [
            'methods' => 'GET',
            'callback' => [$this->getDashboardController(), 'getRecentAppointments'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/dashboard/upcoming-appointments', [
            'methods' => 'GET',
            'callback' => [$this->getDashboardController(), 'getUpcomingAppointments'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Services routes
        register_rest_route($namespace, '/services', [
            'methods' => 'GET',
            'callback' => [$this->getServicesController(), 'getServices'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/services/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this->getServicesController(), 'getService'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/services', [
            'methods' => 'POST',
            'callback' => [$this->getServicesController(), 'createService'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/services/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this->getServicesController(), 'updateService'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/services/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this->getServicesController(), 'deleteService'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);
    }

    /**
     * Get dashboard controller
     */
    private function getDashboardController()
    {
        return new DashboardController($this->container);
    }

    /**
     * Get services controller
     */
    private function getServicesController()
    {
        return new ServicesController($this->container);
    }

    /**
     * Check permissions for API access
     */
    public function checkPermissions()
    {
        return current_user_can('manage_options');
    }
}