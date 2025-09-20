<?php

namespace MedX360\Infrastructure\Routes\Patients;

use MedX360\Infrastructure\Common\Container;
use MedX360\Infrastructure\Database\DatabaseManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Patients REST API Controller
 * 
 * @package MedX360\Infrastructure\Routes\Patients
 */
class PatientsController
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
     * Get all patients
     */
    public function getPatients(WP_REST_Request $request)
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('patients');
        
        // Get query parameters
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search') ?: '';
        $status = $request->get_param('status') ?: 'active';
        
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
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
        
        // Get patients
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $patients = $wpdb->get_results($wpdb->prepare($query, $where_values), ARRAY_A);
        
        // Format response
        $response_data = [
            'data' => $patients,
            'total' => (int) $total_items,
            'page' => (int) $page,
            'per_page' => (int) $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ];
        
        return new WP_REST_Response($response_data, 200);
    }

    /**
     * Get single patient
     */
    public function getPatient(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('patients');
        
        $patient = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$patient) {
            return new WP_Error('patient_not_found', 'Patient not found', ['status' => 404]);
        }
        
        return new WP_REST_Response($patient, 200);
    }

    /**
     * Create new patient
     */
    public function createPatient(WP_REST_Request $request)
    {
        global $wpdb;
        
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('patients');
        
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', "Field '{$field}' is required", ['status' => 400]);
            }
        }
        
        // Check if email already exists
        $existing_patient = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table_name} WHERE email = %s AND status != 'deleted'", $data['email'])
        );
        
        if ($existing_patient) {
            return new WP_Error('email_exists', 'A patient with this email already exists', ['status' => 409]);
        }
        
        // Prepare data for insertion
        $insert_data = [
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'date_of_birth' => sanitize_text_field($data['date_of_birth'] ?? ''),
            'gender' => sanitize_text_field($data['gender'] ?? ''),
            'address' => sanitize_textarea_field($data['address'] ?? ''),
            'city' => sanitize_text_field($data['city'] ?? ''),
            'state' => sanitize_text_field($data['state'] ?? ''),
            'postal_code' => sanitize_text_field($data['postal_code'] ?? ''),
            'country' => sanitize_text_field($data['country'] ?? ''),
            'emergency_contact_name' => sanitize_text_field($data['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => sanitize_text_field($data['emergency_contact_phone'] ?? ''),
            'insurance_provider' => sanitize_text_field($data['insurance_provider'] ?? ''),
            'insurance_number' => sanitize_text_field($data['insurance_number'] ?? ''),
            'medical_history' => sanitize_textarea_field($data['medical_history'] ?? ''),
            'allergies' => sanitize_textarea_field($data['allergies'] ?? ''),
            'medications' => sanitize_textarea_field($data['medications'] ?? ''),
            'status' => 'active'
        ];
        
        $insert_format = ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'];
        
        $result = $wpdb->insert($table_name, $insert_data, $insert_format);
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to create patient', ['status' => 500]);
        }
        
        $patient_id = $wpdb->insert_id;
        $patient = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $patient_id),
            ARRAY_A
        );
        
        return new WP_REST_Response($patient, 201);
    }

    /**
     * Update patient
     */
    public function updatePatient(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('patients');
        
        // Check if patient exists
        $existing_patient = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$existing_patient) {
            return new WP_Error('patient_not_found', 'Patient not found', ['status' => 404]);
        }
        
        // Check if email already exists (excluding current patient)
        if (!empty($data['email'])) {
            $email_exists = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$table_name} WHERE email = %s AND id != %d AND status != 'deleted'", $data['email'], $id)
            );
            
            if ($email_exists) {
                return new WP_Error('email_exists', 'A patient with this email already exists', ['status' => 409]);
            }
        }
        
        // Prepare data for update
        $update_data = [];
        $update_format = [];
        
        $allowed_fields = [
            'first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender',
            'address', 'city', 'state', 'postal_code', 'country', 'emergency_contact_name',
            'emergency_contact_phone', 'insurance_provider', 'insurance_number',
            'medical_history', 'allergies', 'medications', 'status'
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
            return new WP_Error('database_error', 'Failed to update patient', ['status' => 500]);
        }
        
        $patient = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return new WP_REST_Response($patient, 200);
    }

    /**
     * Delete patient
     */
    public function deletePatient(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('patients');
        
        // Check if patient exists
        $existing_patient = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND status != 'deleted'", $id),
            ARRAY_A
        );
        
        if (!$existing_patient) {
            return new WP_Error('patient_not_found', 'Patient not found', ['status' => 404]);
        }
        
        // Check if patient has appointments
        $appointments_table = $this->dbManager->getTableName('appointments');
        $has_appointments = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$appointments_table} WHERE patient_id = %d", $id)
        );
        
        if ($has_appointments > 0) {
            return new WP_Error('has_appointments', 'Cannot delete patient with existing appointments', ['status' => 400]);
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
            return new WP_Error('database_error', 'Failed to delete patient', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Patient deleted successfully', 'id' => $id], 200);
    }
}