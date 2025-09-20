<?php

namespace MedX360\Infrastructure\Common;

use Pimple\Container as PimpleContainer;

/**
 * Dependency Injection Container
 * 
 * @package MedX360\Infrastructure\Common
 */
class Container extends PimpleContainer
{
    /**
     * Container constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->registerServices();
    }

    /**
     * Register all services
     */
    private function registerServices()
    {
        // Database services - using WordPress global $wpdb for now
        $this['database.connection'] = function ($c) {
            global $wpdb;
            return $wpdb;
        };

        // Repository services - placeholder implementations
        $this['repository.patient'] = function ($c) {
            return new \stdClass(); // Placeholder
        };

        $this['repository.provider'] = function ($c) {
            return new \stdClass(); // Placeholder
        };

        $this['repository.appointment'] = function ($c) {
            return new \stdClass(); // Placeholder
        };

        $this['repository.clinical_note'] = function ($c) {
            return new \stdClass(); // Placeholder
        };

        // Service services - placeholder implementations
        $this['service.patient'] = function ($c) {
            return new \stdClass(); // Placeholder
        };

        $this['service.provider'] = function ($c) {
            return new \stdClass(); // Placeholder
        };

        $this['service.appointment'] = function ($c) {
            return new \stdClass(); // Placeholder
        };

        $this['service.notification'] = function ($c) {
            return new \stdClass(); // Placeholder
        };

        $this['service.security'] = function ($c) {
            return new \stdClass(); // Placeholder
        };
    }

    /**
     * Get a service from the container
     * 
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        return $this[$id];
    }

    /**
     * Check if a service exists
     * 
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this[$id]);
    }
}
