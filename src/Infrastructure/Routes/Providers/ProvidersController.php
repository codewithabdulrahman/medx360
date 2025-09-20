<?php

namespace MedX360\Infrastructure\Routes\Providers;

use MedX360\Infrastructure\Common\Container;
use MedX360\Infrastructure\Database\DatabaseManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Providers REST API Controller
 * 
 * @package MedX360\Infrastructure\Routes\Providers
 */
class ProvidersController
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
     * Get all providers
     */
    public function getProviders(WP_REST_Request $request)
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('providers');
        
        // Get query parameters
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search') ?: '';
        $status = $request->get_param('status') ?: 'active';
        $specialization = $request->get_param('specialization') ?: '';
        
        $offset = ($page - 1) * $per_page;
        
        // Build query
        $where_conditions = ["status = %s"];
        $where_values = [$status];
        
        if (!empty($search)) {
            $where_conditions[] = "(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($specialization)) {
            $where_conditions[] = "specialization = %s";
            $where_values[] = $specialization;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
        
        // Get providers
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY first_name ASC, last_name ASC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $providers = $wpdb->get_results($wpdb->prepare($query, $where_values), ARRAY_A);
        
        // Format response
        $response_data = [
            'data' => $providers,
            'total' => (int) $total_items,
            'page' => (int) $page,
            'per_page' => (int) $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ];
        
        return new WP_REST_Response($response_data, 200);
    }

    /**
     * Get single provider
     */
    public function getProvider(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('providers');
        
        $provider = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$provider) {
            return new WP_Error('provider_not_found', 'Provider not found', ['status' => 404]);
        }
        
        return new WP_REST_Response($provider, 200);
    }

    /**
     * Create new provider
     */
    public function createProvider(WP_REST_Request $request)
    {
        global $wpdb;
        
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('providers');
        
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', "Field '{$field}' is required", ['status' => 400]);
            }
        }
        
        // Check if email already exists
        $existing_provider = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table_name} WHERE email = %s AND status != 'deleted'", $data['email'])
        );
        
        if ($existing_provider) {
            return new WP_Error('email_exists', 'A provider with this email already exists', ['status' => 409]);
        }
        
        // Prepare data for insertion
        $insert_data = [
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'title' => sanitize_text_field($data['title'] ?? ''),
            'specialization' => sanitize_text_field($data['specialization'] ?? ''),
            'license_number' => sanitize_text_field($data['license_number'] ?? ''),
            'bio' => sanitize_textarea_field($data['bio'] ?? ''),
            'profile_image' => sanitize_url($data['profile_image'] ?? ''),
            'working_hours' => sanitize_textarea_field($data['working_hours'] ?? ''),
            'status' => 'active'
        ];
        
        $insert_format = ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'];
        
        $result = $wpdb->insert($table_name, $insert_data, $insert_format);
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to create provider', ['status' => 500]);
        }
        
        $provider_id = $wpdb->insert_id;
        $provider = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $provider_id),
            ARRAY_A
        );
        
        return new WP_REST_Response($provider, 201);
    }

    /**
     * Update provider
     */
    public function updateProvider(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('providers');
        
        // Check if provider exists
        $existing_provider = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$existing_provider) {
            return new WP_Error('provider_not_found', 'Provider not found', ['status' => 404]);
        }
        
        // Check if email already exists (excluding current provider)
        if (!empty($data['email'])) {
            $email_exists = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$table_name} WHERE email = %s AND id != %d AND status != 'deleted'", $data['email'], $id)
            );
            
            if ($email_exists) {
                return new WP_Error('email_exists', 'A provider with this email already exists', ['status' => 409]);
            }
        }
        
        // Prepare data for update
        $update_data = [];
        $update_format = [];
        
        $allowed_fields = [
            'first_name', 'last_name', 'email', 'phone', 'title', 'specialization',
            'license_number', 'bio', 'profile_image', 'working_hours', 'status'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = sanitize_text_field($data[$field]);
                $update_format[] = '%s';
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
            return new WP_Error('database_error', 'Failed to update provider', ['status' => 500]);
        }
        
        $provider = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return new WP_REST_Response($provider, 200);
    }

    /**
     * Delete provider
     */
    public function deleteProvider(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('providers');
        
        // Check if provider exists
        $existing_provider = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$existing_provider) {
            return new WP_Error('provider_not_found', 'Provider not found', ['status' => 404]);
        }
        
        // Check if provider has appointments
        $appointments_table = $this->dbManager->getTableName('appointments');
        $has_appointments = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$appointments_table} WHERE provider_id = %d", $id)
        );
        
        if ($has_appointments > 0) {
            return new WP_Error('has_appointments', 'Cannot delete provider with existing appointments', ['status' => 400]);
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
            return new WP_Error('database_error', 'Failed to delete provider', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Provider deleted successfully', 'id' => $id], 200);
    }

    /**
     * Get providers by specialization
     */
    public function getProvidersBySpecialization(WP_REST_Request $request)
    {
        global $wpdb;
        
        $specialization = $request->get_param('specialization');
        $table_name = $this->dbManager->getTableName('providers');
        
        if (empty($specialization)) {
            return new WP_Error('missing_param', 'Specialization parameter is required', ['status' => 400]);
        }
        
        $providers = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE specialization = %s AND status = 'active' ORDER BY first_name ASC, last_name ASC", $specialization),
            ARRAY_A
        );
        
        return new WP_REST_Response(['data' => $providers], 200);
    }

    /**
     * Get provider services
     */
    public function getProviderServices(WP_REST_Request $request)
    {
        global $wpdb;
        
        $provider_id = $request->get_param('id');
        $provider_services_table = $this->dbManager->getTableName('provider_services');
        $services_table = $this->dbManager->getTableName('services');
        
        if (empty($provider_id)) {
            return new WP_Error('missing_param', 'Provider ID is required', ['status' => 400]);
        }
        
        $query = "
            SELECT 
                ps.*,
                s.name as service_name,
                s.description as service_description,
                s.category as service_category
            FROM {$provider_services_table} ps
            LEFT JOIN {$services_table} s ON ps.service_id = s.id
            WHERE ps.provider_id = %d AND s.status = 'active'
            ORDER BY s.name ASC
        ";
        
        $services = $wpdb->get_results($wpdb->prepare($query, $provider_id), ARRAY_A);
        
        return new WP_REST_Response(['data' => $services], 200);
    }

    /**
     * Add service to provider
     */
    public function addProviderService(WP_REST_Request $request)
    {
        global $wpdb;
        
        $provider_id = $request->get_param('id');
        $data = $request->get_json_params();
        $provider_services_table = $this->dbManager->getTableName('provider_services');
        $services_table = $this->dbManager->getTableName('services');
        
        if (empty($provider_id) || empty($data['service_id'])) {
            return new WP_Error('missing_params', 'Provider ID and Service ID are required', ['status' => 400]);
        }
        
        // Check if service exists
        $service_exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$services_table} WHERE id = %d AND status = 'active'", $data['service_id'])
        );
        
        if (!$service_exists) {
            return new WP_Error('service_not_found', 'Service not found', ['status' => 404]);
        }
        
        // Check if provider-service combination already exists
        $existing_combination = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$provider_services_table} WHERE provider_id = %d AND service_id = %d", $provider_id, $data['service_id'])
        );
        
        if ($existing_combination) {
            return new WP_Error('combination_exists', 'Provider already offers this service', ['status' => 409]);
        }
        
        // Insert provider service
        $insert_data = [
            'provider_id' => intval($provider_id),
            'service_id' => intval($data['service_id']),
            'price' => !empty($data['price']) ? floatval($data['price']) : null,
            'duration' => !empty($data['duration']) ? intval($data['duration']) : null
        ];
        
        $result = $wpdb->insert($provider_services_table, $insert_data, ['%d', '%d', '%f', '%d']);
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to add service to provider', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Service added to provider successfully'], 201);
    }

    /**
     * Remove service from provider
     */
    public function removeProviderService(WP_REST_Request $request)
    {
        global $wpdb;
        
        $provider_id = $request->get_param('id');
        $service_id = $request->get_param('service_id');
        $provider_services_table = $this->dbManager->getTableName('provider_services');
        
        if (empty($provider_id) || empty($service_id)) {
            return new WP_Error('missing_params', 'Provider ID and Service ID are required', ['status' => 400]);
        }
        
        $result = $wpdb->delete(
            $provider_services_table,
            ['provider_id' => $provider_id, 'service_id' => $service_id],
            ['%d', '%d']
        );
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to remove service from provider', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Service removed from provider successfully'], 200);
    }
}
