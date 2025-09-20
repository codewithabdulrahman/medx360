<?php

namespace MedX360\Infrastructure\Routes;

use MedX360\Infrastructure\Common\Container;
use MedX360\Infrastructure\Routes\Dashboard\DashboardController;
use MedX360\Infrastructure\Routes\Services\ServicesController;
use MedX360\Infrastructure\Routes\Patients\PatientsController;
use MedX360\Infrastructure\Routes\Appointments\AppointmentsController;
use MedX360\Infrastructure\Routes\Locations\LocationsController;
use MedX360\Infrastructure\Routes\Providers\ProvidersController;

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

        // Patients routes
        register_rest_route($namespace, '/patients', [
            'methods' => 'GET',
            'callback' => [$this->getPatientsController(), 'getPatients'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/patients/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this->getPatientsController(), 'getPatient'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/patients', [
            'methods' => 'POST',
            'callback' => [$this->getPatientsController(), 'createPatient'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/patients/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this->getPatientsController(), 'updatePatient'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/patients/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this->getPatientsController(), 'deletePatient'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Appointments routes
        register_rest_route($namespace, '/appointments', [
            'methods' => 'GET',
            'callback' => [$this->getAppointmentsController(), 'getAppointments'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/appointments/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this->getAppointmentsController(), 'getAppointment'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/appointments', [
            'methods' => 'POST',
            'callback' => [$this->getAppointmentsController(), 'createAppointment'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/appointments/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this->getAppointmentsController(), 'updateAppointment'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/appointments/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this->getAppointmentsController(), 'deleteAppointment'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/appointments/availability', [
            'methods' => 'GET',
            'callback' => [$this->getAppointmentsController(), 'getAppointmentAvailability'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Locations routes
        register_rest_route($namespace, '/locations', [
            'methods' => 'GET',
            'callback' => [$this->getLocationsController(), 'getLocations'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/locations/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this->getLocationsController(), 'getLocation'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/locations', [
            'methods' => 'POST',
            'callback' => [$this->getLocationsController(), 'createLocation'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/locations/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this->getLocationsController(), 'updateLocation'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/locations/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this->getLocationsController(), 'deleteLocation'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/locations/city/(?P<city>[^/]+)', [
            'methods' => 'GET',
            'callback' => [$this->getLocationsController(), 'getLocationsByCity'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/locations/state/(?P<state>[^/]+)', [
            'methods' => 'GET',
            'callback' => [$this->getLocationsController(), 'getLocationsByState'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Providers routes
        register_rest_route($namespace, '/providers', [
            'methods' => 'GET',
            'callback' => [$this->getProvidersController(), 'getProviders'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/providers/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this->getProvidersController(), 'getProvider'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/providers', [
            'methods' => 'POST',
            'callback' => [$this->getProvidersController(), 'createProvider'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/providers/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this->getProvidersController(), 'updateProvider'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/providers/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this->getProvidersController(), 'deleteProvider'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/providers/specialization/(?P<specialization>[^/]+)', [
            'methods' => 'GET',
            'callback' => [$this->getProvidersController(), 'getProvidersBySpecialization'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/providers/(?P<id>\d+)/services', [
            'methods' => 'GET',
            'callback' => [$this->getProvidersController(), 'getProviderServices'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/providers/(?P<id>\d+)/services', [
            'methods' => 'POST',
            'callback' => [$this->getProvidersController(), 'addProviderService'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/providers/(?P<id>\d+)/services/(?P<service_id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this->getProvidersController(), 'removeProviderService'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Services routes (updated)
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

        register_rest_route($namespace, '/services/category/(?P<category>[^/]+)', [
            'methods' => 'GET',
            'callback' => [$this->getServicesController(), 'getServicesByCategory'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/services/categories', [
            'methods' => 'GET',
            'callback' => [$this->getServicesController(), 'getCategories'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route($namespace, '/services/stats', [
            'methods' => 'GET',
            'callback' => [$this->getServicesController(), 'getServiceStats'],
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
     * Get patients controller
     */
    private function getPatientsController()
    {
        return new PatientsController($this->container);
    }

    /**
     * Get appointments controller
     */
    private function getAppointmentsController()
    {
        return new AppointmentsController($this->container);
    }

    /**
     * Get locations controller
     */
    private function getLocationsController()
    {
        return new LocationsController($this->container);
    }

    private function getProvidersController()
    {
        return new ProvidersController($this->container);
    }

    /**
     * Check permissions for API access
     */
    public function checkPermissions()
    {
        return current_user_can('manage_options');
    }
}