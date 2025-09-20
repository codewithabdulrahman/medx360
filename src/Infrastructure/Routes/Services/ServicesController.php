<?php

namespace MedX360\Infrastructure\Routes\Services;

use MedX360\Infrastructure\Common\Container;
use MedX360\Infrastructure\Database\DatabaseManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Services REST API Controller
 * 
 * @package MedX360\Infrastructure\Routes\Services
 */
class ServicesController
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
     * Get all services
     */
    public function getServices(WP_REST_Request $request)
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('services');
        
        // Get query parameters
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search') ?: '';
        $status = $request->get_param('status') ?: 'active';
        $category = $request->get_param('category') ?: '';
        
        $offset = ($page - 1) * $per_page;
        
        // Build query
        $where_conditions = ["status = %s"];
        $where_values = [$status];
        
        if (!empty($search)) {
            $where_conditions[] = "(name LIKE %s OR description LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($category)) {
            $where_conditions[] = "category = %s";
            $where_values[] = $category;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
        
        // Get services
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY name ASC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $services = $wpdb->get_results($wpdb->prepare($query, $where_values), ARRAY_A);
        
        // Format response
        $response_data = [
            'data' => $services,
            'total' => (int) $total_items,
            'page' => (int) $page,
            'per_page' => (int) $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ];
        
        return new WP_REST_Response($response_data, 200);
    }

    /**
     * Get single service
     */
    public function getService(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('services');
        
        $service = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$service) {
            return new WP_Error('service_not_found', 'Service not found', ['status' => 404]);
        }
        
        return new WP_REST_Response($service, 200);
    }

    /**
     * Create new service
     */
    public function createService(WP_REST_Request $request)
    {
        global $wpdb;
        
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('services');
        
        // Validate required fields
        if (empty($data['name'])) {
            return new WP_Error('missing_field', "Field 'name' is required", ['status' => 400]);
        }
        
        // Check if service name already exists
        $existing_service = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table_name} WHERE name = %s AND status != 'deleted'", $data['name'])
        );
        
        if ($existing_service) {
            return new WP_Error('name_exists', 'A service with this name already exists', ['status' => 409]);
        }
        
        // Prepare data for insertion
        $insert_data = [
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'duration' => intval($data['duration'] ?? 30),
            'price' => !empty($data['price']) ? floatval($data['price']) : null,
            'category' => sanitize_text_field($data['category'] ?? ''),
            'color' => sanitize_text_field($data['color'] ?? '#007cba'),
            'status' => 'active'
        ];
        
        $insert_format = ['%s', '%s', '%d', '%f', '%s', '%s', '%s'];
        
        $result = $wpdb->insert($table_name, $insert_data, $insert_format);
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to create service', ['status' => 500]);
        }
        
        $service_id = $wpdb->insert_id;
        $service = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $service_id),
            ARRAY_A
        );
        
        return new WP_REST_Response($service, 201);
    }

    /**
     * Update service
     */
    public function updateService(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('services');
        
        // Check if service exists
        $existing_service = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$existing_service) {
            return new WP_Error('service_not_found', 'Service not found', ['status' => 404]);
        }
        
        // Check if service name already exists (excluding current service)
        if (!empty($data['name'])) {
            $name_exists = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$table_name} WHERE name = %s AND id != %d AND status != 'deleted'", $data['name'], $id)
            );
            
            if ($name_exists) {
                return new WP_Error('name_exists', 'A service with this name already exists', ['status' => 409]);
            }
        }
        
        // Prepare data for update
        $update_data = [];
        $update_format = [];
        
        $allowed_fields = ['name', 'description', 'duration', 'price', 'category', 'color', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['duration'])) {
                    $update_data[$field] = intval($data[$field]);
                    $update_format[] = '%d';
                } elseif (in_array($field, ['price'])) {
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
            return new WP_Error('database_error', 'Failed to update service', ['status' => 500]);
        }
        
        $service = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return new WP_REST_Response($service, 200);
    }

    /**
     * Delete service
     */
    public function deleteService(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('services');
        
        // Check if service exists
        $existing_service = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$existing_service) {
            return new WP_Error('service_not_found', 'Service not found', ['status' => 404]);
        }
        
        // Check if service has appointments
        $appointment_services_table = $this->dbManager->getTableName('appointment_services');
        $has_appointments = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$appointment_services_table} WHERE service_id = %d", $id)
        );
        
        if ($has_appointments > 0) {
            return new WP_Error('has_appointments', 'Cannot delete service with existing appointments', ['status' => 400]);
        }
        
        // Check if service is offered by providers
        $provider_services_table = $this->dbManager->getTableName('provider_services');
        $has_providers = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$provider_services_table} WHERE service_id = %d", $id)
        );
        
        if ($has_providers > 0) {
            return new WP_Error('has_providers', 'Cannot delete service offered by providers', ['status' => 400]);
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
            return new WP_Error('database_error', 'Failed to delete service', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Service deleted successfully', 'id' => $id], 200);
    }

    /**
     * Get services by category
     */
    public function getServicesByCategory(WP_REST_Request $request)
    {
        global $wpdb;
        
        $category = $request->get_param('category');
        $table_name = $this->dbManager->getTableName('services');
        
        if (empty($category)) {
            return new WP_Error('missing_param', 'Category parameter is required', ['status' => 400]);
        }
        
        $services = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE category = %s AND status = 'active' ORDER BY name ASC", $category),
            ARRAY_A
        );
        
        return new WP_REST_Response(['data' => $services], 200);
    }

    /**
     * Get all categories
     */
    public function getCategories(WP_REST_Request $request)
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('services');
        
        $categories = $wpdb->get_results(
            "SELECT DISTINCT category FROM {$table_name} WHERE category IS NOT NULL AND category != '' AND status = 'active' ORDER BY category ASC",
            ARRAY_A
        );
        
        $category_list = array_column($categories, 'category');
        
        return new WP_REST_Response(['data' => $category_list], 200);
    }

    /**
     * Get service statistics
     */
    public function getServiceStats(WP_REST_Request $request)
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('services');
        $appointment_services_table = $this->dbManager->getTableName('appointment_services');
        $appointments_table = $this->dbManager->getTableName('appointments');
        
        // Get total services
        $total_services = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'active'");
        
        // Get most popular services
        $popular_services = $wpdb->get_results("
            SELECT 
                s.id,
                s.name,
                s.category,
                COUNT(as_rel.appointment_id) as appointment_count
            FROM {$table_name} s
            LEFT JOIN {$appointment_services_table} as_rel ON s.id = as_rel.service_id
            LEFT JOIN {$appointments_table} a ON as_rel.appointment_id = a.id
            WHERE s.status = 'active' AND a.status IN ('completed', 'scheduled', 'confirmed')
            GROUP BY s.id, s.name, s.category
            ORDER BY appointment_count DESC
            LIMIT 10
        ", ARRAY_A);
        
        // Get services by category
        $services_by_category = $wpdb->get_results("
            SELECT 
                category,
                COUNT(*) as service_count
            FROM {$table_name}
            WHERE status = 'active' AND category IS NOT NULL AND category != ''
            GROUP BY category
            ORDER BY service_count DESC
        ", ARRAY_A);
        
        return new WP_REST_Response([
            'total_services' => (int) $total_services,
            'popular_services' => $popular_services,
            'services_by_category' => $services_by_category
        ], 200);
    }
}