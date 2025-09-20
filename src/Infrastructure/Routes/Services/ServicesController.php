<?php

namespace MedX360\Infrastructure\Routes\Services;

use MedX360\Infrastructure\Common\Container;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Services Controller - Handles CRUD operations for healthcare services
 * 
 * @package MedX360\Infrastructure\Routes\Services
 */
class ServicesController
{
    private $container;
    private $tableName;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'healthcare_services';
    }

    /**
     * Get all services
     */
    public function getServices(WP_REST_Request $request)
    {
        global $wpdb;

        $params = $request->get_params();
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $per_page = isset($params['per_page']) ? min(100, max(1, intval($params['per_page']))) : 20;
        $offset = ($page - 1) * $per_page;

        $where_clause = '';
        $where_values = [];

        // Filter by status
        if (isset($params['status']) && !empty($params['status'])) {
            $where_clause .= ' AND status = %s';
            $where_values[] = sanitize_text_field($params['status']);
        }

        // Filter by category
        if (isset($params['category']) && !empty($params['category'])) {
            $where_clause .= ' AND category = %s';
            $where_values[] = sanitize_text_field($params['category']);
        }

        // Search by name
        if (isset($params['search']) && !empty($params['search'])) {
            $where_clause .= ' AND name LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like(sanitize_text_field($params['search'])) . '%';
        }

        $sql = "SELECT * FROM {$this->tableName} WHERE 1=1 {$where_clause} ORDER BY created_at DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }

        $total_sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE 1=1 {$where_clause}";
        if (!empty($where_values)) {
            $total_sql = $wpdb->prepare($total_sql, $where_values);
        }

        $total = $wpdb->get_var($total_sql);
        $services = $wpdb->get_results($sql . " LIMIT {$per_page} OFFSET {$offset}");

        return new WP_REST_Response([
            'data' => $services,
            'total' => intval($total),
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page),
        ], 200);
    }

    /**
     * Get a single service
     */
    public function getService(WP_REST_Request $request)
    {
        global $wpdb;

        $id = intval($request->get_param('id'));
        
        if ($id <= 0) {
            return new WP_Error('invalid_id', 'Invalid service ID', ['status' => 400]);
        }

        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->tableName} WHERE id = %d",
            $id
        ));

        if (!$service) {
            return new WP_Error('service_not_found', 'Service not found', ['status' => 404]);
        }

        return new WP_REST_Response($service, 200);
    }

    /**
     * Create a new service
     */
    public function createService(WP_REST_Request $request)
    {
        global $wpdb;

        $data = $request->get_json_params();
        
        // Validate required fields
        if (empty($data['name'])) {
            return new WP_Error('missing_name', 'Service name is required', ['status' => 400]);
        }

        $service_data = [
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'duration_minutes' => intval($data['duration_minutes'] ?? 30),
            'price' => floatval($data['price'] ?? 0),
            'category' => sanitize_text_field($data['category'] ?? ''),
            'status' => sanitize_text_field($data['status'] ?? 'active'),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];

        $result = $wpdb->insert($this->tableName, $service_data);

        if ($result === false) {
            return new WP_Error('create_failed', 'Failed to create service', ['status' => 500]);
        }

        $service_id = $wpdb->insert_id;
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->tableName} WHERE id = %d",
            $service_id
        ));

        return new WP_REST_Response($service, 201);
    }

    /**
     * Update a service
     */
    public function updateService(WP_REST_Request $request)
    {
        global $wpdb;

        $id = intval($request->get_param('id'));
        
        if ($id <= 0) {
            return new WP_Error('invalid_id', 'Invalid service ID', ['status' => 400]);
        }

        $data = $request->get_json_params();
        
        // Check if service exists
        $existing_service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->tableName} WHERE id = %d",
            $id
        ));

        if (!$existing_service) {
            return new WP_Error('service_not_found', 'Service not found', ['status' => 404]);
        }

        $update_data = [];
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
        }
        if (isset($data['duration_minutes'])) {
            $update_data['duration_minutes'] = intval($data['duration_minutes']);
        }
        if (isset($data['price'])) {
            $update_data['price'] = floatval($data['price']);
        }
        if (isset($data['category'])) {
            $update_data['category'] = sanitize_text_field($data['category']);
        }
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
        }

        $update_data['updated_at'] = current_time('mysql');

        $result = $wpdb->update(
            $this->tableName,
            $update_data,
            ['id' => $id],
            ['%s', '%s', '%d', '%f', '%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update service', ['status' => 500]);
        }

        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->tableName} WHERE id = %d",
            $id
        ));

        return new WP_REST_Response($service, 200);
    }

    /**
     * Delete a service
     */
    public function deleteService(WP_REST_Request $request)
    {
        global $wpdb;

        $id = intval($request->get_param('id'));
        
        if ($id <= 0) {
            return new WP_Error('invalid_id', 'Invalid service ID', ['status' => 400]);
        }

        // Check if service exists
        $existing_service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->tableName} WHERE id = %d",
            $id
        ));

        if (!$existing_service) {
            return new WP_Error('service_not_found', 'Service not found', ['status' => 404]);
        }

        $result = $wpdb->delete($this->tableName, ['id' => $id], ['%d']);

        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete service', ['status' => 500]);
        }

        return new WP_REST_Response(['message' => 'Service deleted successfully'], 200);
    }
}
