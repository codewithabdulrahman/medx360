<?php

namespace MedX360\Infrastructure\Routes\Locations;

use MedX360\Infrastructure\Common\Container;
use MedX360\Infrastructure\Database\DatabaseManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Locations REST API Controller
 * 
 * @package MedX360\Infrastructure\Routes\Locations
 */
class LocationsController
{
    private $container;
    private $dbManager;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->dbManager = new DatabaseManager();
    }

    /**
     * Get all locations
     */
    public function getLocations(WP_REST_Request $request)
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('locations');
        
        // Get query parameters
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search') ?: '';
        $status = $request->get_param('status') ?: 'active';
        $city = $request->get_param('city') ?: '';
        $state = $request->get_param('state') ?: '';
        
        $offset = ($page - 1) * $per_page;
        
        // Build query
        $where_conditions = ["status = %s"];
        $where_values = [$status];
        
        if (!empty($search)) {
            $where_conditions[] = "(name LIKE %s OR address LIKE %s OR city LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($city)) {
            $where_conditions[] = "city = %s";
            $where_values[] = $city;
        }
        
        if (!empty($state)) {
            $where_conditions[] = "state = %s";
            $where_values[] = $state;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
        
        // Get locations
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY name ASC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $locations = $wpdb->get_results($wpdb->prepare($query, $where_values), ARRAY_A);
        
        // Format response
        $response_data = [
            'data' => $locations,
            'total' => (int) $total_items,
            'page' => (int) $page,
            'per_page' => (int) $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ];
        
        return new WP_REST_Response($response_data, 200);
    }

    /**
     * Get single location
     */
    public function getLocation(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('locations');
        
        $location = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$location) {
            return new WP_Error('location_not_found', 'Location not found', ['status' => 404]);
        }
        
        return new WP_REST_Response($location, 200);
    }

    /**
     * Create new location
     */
    public function createLocation(WP_REST_Request $request)
    {
        global $wpdb;
        
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('locations');
        
        // Validate required fields
        if (empty($data['name'])) {
            return new WP_Error('missing_field', "Field 'name' is required", ['status' => 400]);
        }
        
        // Check if location name already exists
        $existing_location = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table_name} WHERE name = %s AND status != 'deleted'", $data['name'])
        );
        
        if ($existing_location) {
            return new WP_Error('name_exists', 'A location with this name already exists', ['status' => 409]);
        }
        
        // Prepare data for insertion
        $insert_data = [
            'name' => sanitize_text_field($data['name']),
            'address' => sanitize_textarea_field($data['address'] ?? ''),
            'city' => sanitize_text_field($data['city'] ?? ''),
            'state' => sanitize_text_field($data['state'] ?? ''),
            'postal_code' => sanitize_text_field($data['postal_code'] ?? ''),
            'country' => sanitize_text_field($data['country'] ?? ''),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'email' => sanitize_email($data['email'] ?? ''),
            'latitude' => !empty($data['latitude']) ? floatval($data['latitude']) : null,
            'longitude' => !empty($data['longitude']) ? floatval($data['longitude']) : null,
            'status' => 'active'
        ];
        
        $insert_format = ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s'];
        
        $result = $wpdb->insert($table_name, $insert_data, $insert_format);
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to create location', ['status' => 500]);
        }
        
        $location_id = $wpdb->insert_id;
        $location = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $location_id),
            ARRAY_A
        );
        
        return new WP_REST_Response($location, 201);
    }

    /**
     * Update location
     */
    public function updateLocation(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('locations');
        
        // Check if location exists
        $existing_location = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$existing_location) {
            return new WP_Error('location_not_found', 'Location not found', ['status' => 404]);
        }
        
        // Check if location name already exists (excluding current location)
        if (!empty($data['name'])) {
            $name_exists = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$table_name} WHERE name = %s AND id != %d AND status != 'deleted'", $data['name'], $id)
            );
            
            if ($name_exists) {
                return new WP_Error('name_exists', 'A location with this name already exists', ['status' => 409]);
            }
        }
        
        // Prepare data for update
        $update_data = [];
        $update_format = [];
        
        $allowed_fields = [
            'name', 'address', 'city', 'state', 'postal_code', 'country',
            'phone', 'email', 'latitude', 'longitude', 'status'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['latitude', 'longitude'])) {
                    $update_data[$field] = floatval($data[$field]);
                    $update_format[] = '%f';
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                    $update_format[] = '%s';
                }
            }
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data provided for update', ['status' => 400]);
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            ['id' => $id],
            $update_format,
            ['%d']
        );
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to update location', ['status' => 500]);
        }
        
        $location = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return new WP_REST_Response($location, 200);
    }

    /**
     * Delete location
     */
    public function deleteLocation(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('locations');
        
        // Check if location exists
        $existing_location = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$existing_location) {
            return new WP_Error('location_not_found', 'Location not found', ['status' => 404]);
        }
        
        // Check if location has appointments
        $appointments_table = $this->dbManager->getTableName('appointments');
        $has_appointments = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$appointments_table} WHERE location_id = %d", $id)
        );
        
        if ($has_appointments > 0) {
            return new WP_Error('has_appointments', 'Cannot delete location with existing appointments', ['status' => 400]);
        }
        
        // Check if location has rooms
        $rooms_table = $this->dbManager->getTableName('rooms');
        $has_rooms = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$rooms_table} WHERE location_id = %d", $id)
        );
        
        if ($has_rooms > 0) {
            return new WP_Error('has_rooms', 'Cannot delete location with existing rooms', ['status' => 400]);
        }
        
        // Soft delete - set status to 'deleted'
        $result = $wpdb->update(
            $table_name,
            ['status' => 'deleted'],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to delete location', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Location deleted successfully', 'id' => $id], 200);
    }

    /**
     * Get locations by city
     */
    public function getLocationsByCity(WP_REST_Request $request)
    {
        global $wpdb;
        
        $city = $request->get_param('city');
        $table_name = $this->dbManager->getTableName('locations');
        
        if (empty($city)) {
            return new WP_Error('missing_param', 'City parameter is required', ['status' => 400]);
        }
        
        $locations = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE city = %s AND status = 'active' ORDER BY name ASC", $city),
            ARRAY_A
        );
        
        return new WP_REST_Response(['data' => $locations], 200);
    }

    /**
     * Get locations by state
     */
    public function getLocationsByState(WP_REST_Request $request)
    {
        global $wpdb;
        
        $state = $request->get_param('state');
        $table_name = $this->dbManager->getTableName('locations');
        
        if (empty($state)) {
            return new WP_Error('missing_param', 'State parameter is required', ['status' => 400]);
        }
        
        $locations = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE state = %s AND status = 'active' ORDER BY name ASC", $state),
            ARRAY_A
        );
        
        return new WP_REST_Response(['data' => $locations], 200);
    }
}