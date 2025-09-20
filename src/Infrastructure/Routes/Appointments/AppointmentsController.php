<?php

namespace MedX360\Infrastructure\Routes\Appointments;

use MedX360\Infrastructure\Common\Container;
use MedX360\Infrastructure\Database\DatabaseManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Appointments REST API Controller
 * 
 * @package MedX360\Infrastructure\Routes\Appointments
 */
class AppointmentsController
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
     * Get all appointments
     */
    public function getAppointments(WP_REST_Request $request)
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('appointments');
        $patients_table = $this->dbManager->getTableName('patients');
        $providers_table = $this->dbManager->getTableName('providers');
        $locations_table = $this->dbManager->getTableName('locations');
        
        // Get query parameters
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $status = $request->get_param('status') ?: '';
        $patient_id = $request->get_param('patient_id') ?: '';
        $provider_id = $request->get_param('provider_id') ?: '';
        $date_from = $request->get_param('date_from') ?: '';
        $date_to = $request->get_param('date_to') ?: '';
        
        $offset = ($page - 1) * $per_page;
        
        // Build query with joins
        $join_clause = "
            LEFT JOIN {$patients_table} p ON a.patient_id = p.id
            LEFT JOIN {$providers_table} pr ON a.provider_id = pr.id
            LEFT JOIN {$locations_table} l ON a.location_id = l.id
        ";
        
        $where_conditions = [];
        $where_values = [];
        
        if (!empty($status)) {
            $where_conditions[] = "a.status = %s";
            $where_values[] = $status;
        }
        
        if (!empty($patient_id)) {
            $where_conditions[] = "a.patient_id = %d";
            $where_values[] = $patient_id;
        }
        
        if (!empty($provider_id)) {
            $where_conditions[] = "a.provider_id = %d";
            $where_values[] = $provider_id;
        }
        
        if (!empty($date_from)) {
            $where_conditions[] = "a.appointment_date >= %s";
            $where_values[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where_conditions[] = "a.appointment_date <= %s";
            $where_values[] = $date_to;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get total count
        $count_query = "
            SELECT COUNT(*) 
            FROM {$table_name} a 
            {$join_clause} 
            {$where_clause}
        ";
        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
        
        // Get appointments with related data
        $query = "
            SELECT 
                a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.email as patient_email,
                p.phone as patient_phone,
                CONCAT(pr.first_name, ' ', pr.last_name) as provider_name,
                pr.email as provider_email,
                pr.phone as provider_phone,
                l.name as location_name,
                l.address as location_address
            FROM {$table_name} a 
            {$join_clause} 
            {$where_clause}
            ORDER BY a.appointment_date DESC, a.start_time DESC 
            LIMIT %d OFFSET %d
        ";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $appointments = $wpdb->get_results($wpdb->prepare($query, $where_values), ARRAY_A);
        
        // Format response
        $response_data = [
            'data' => $appointments,
            'total' => (int) $total_items,
            'page' => (int) $page,
            'per_page' => (int) $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ];
        
        return new WP_REST_Response($response_data, 200);
    }

    /**
     * Get single appointment
     */
    public function getAppointment(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('appointments');
        $patients_table = $this->dbManager->getTableName('patients');
        $providers_table = $this->dbManager->getTableName('providers');
        $locations_table = $this->dbManager->getTableName('locations');
        
        $query = "
            SELECT 
                a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.email as patient_email,
                p.phone as patient_phone,
                CONCAT(pr.first_name, ' ', pr.last_name) as provider_name,
                pr.email as provider_email,
                pr.phone as provider_phone,
                l.name as location_name,
                l.address as location_address
            FROM {$table_name} a 
            LEFT JOIN {$patients_table} p ON a.patient_id = p.id
            LEFT JOIN {$providers_table} pr ON a.provider_id = pr.id
            LEFT JOIN {$locations_table} l ON a.location_id = l.id
            WHERE a.id = %d
        ";
        
        $appointment = $wpdb->get_row($wpdb->prepare($query, $id), ARRAY_A);
        
        if (!$appointment) {
            return new WP_Error('appointment_not_found', 'Appointment not found', ['status' => 404]);
        }
        
        return new WP_REST_Response($appointment, 200);
    }

    /**
     * Create new appointment
     */
    public function createAppointment(WP_REST_Request $request)
    {
        global $wpdb;
        
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('appointments');
        
        // Validate required fields
        $required_fields = ['patient_id', 'provider_id', 'appointment_date', 'start_time', 'end_time'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', "Field '{$field}' is required", ['status' => 400]);
            }
        }
        
        // Validate patient exists
        $patients_table = $this->dbManager->getTableName('patients');
        $patient_exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$patients_table} WHERE id = %d AND status = 'active'", $data['patient_id'])
        );
        
        if (!$patient_exists) {
            return new WP_Error('patient_not_found', 'Patient not found', ['status' => 404]);
        }
        
        // Validate provider exists
        $providers_table = $this->dbManager->getTableName('providers');
        $provider_exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$providers_table} WHERE id = %d AND status = 'active'", $data['provider_id'])
        );
        
        if (!$provider_exists) {
            return new WP_Error('provider_not_found', 'Provider not found', ['status' => 404]);
        }
        
        // Check for time conflicts
        $conflict_query = "
            SELECT COUNT(*) FROM {$table_name} 
            WHERE provider_id = %d 
            AND appointment_date = %s 
            AND status IN ('scheduled', 'confirmed')
            AND (
                (start_time <= %s AND end_time > %s) OR
                (start_time < %s AND end_time >= %s) OR
                (start_time >= %s AND end_time <= %s)
            )
        ";
        
        $conflicts = $wpdb->get_var($wpdb->prepare(
            $conflict_query,
            $data['provider_id'],
            $data['appointment_date'],
            $data['start_time'], $data['start_time'],
            $data['end_time'], $data['end_time'],
            $data['start_time'], $data['end_time']
        ));
        
        if ($conflicts > 0) {
            return new WP_Error('time_conflict', 'Provider has a conflicting appointment at this time', ['status' => 409]);
        }
        
        // Calculate duration
        $start_time = new \DateTime($data['appointment_date'] . ' ' . $data['start_time']);
        $end_time = new \DateTime($data['appointment_date'] . ' ' . $data['end_time']);
        $duration = $end_time->diff($start_time)->i + ($end_time->diff($start_time)->h * 60);
        
        // Prepare data for insertion
        $insert_data = [
            'patient_id' => intval($data['patient_id']),
            'provider_id' => intval($data['provider_id']),
            'location_id' => !empty($data['location_id']) ? intval($data['location_id']) : null,
            'room_id' => !empty($data['room_id']) ? intval($data['room_id']) : null,
            'appointment_date' => sanitize_text_field($data['appointment_date']),
            'start_time' => sanitize_text_field($data['start_time']),
            'end_time' => sanitize_text_field($data['end_time']),
            'duration' => $duration,
            'status' => sanitize_text_field($data['status'] ?? 'scheduled'),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'internal_notes' => sanitize_textarea_field($data['internal_notes'] ?? ''),
            'reminder_sent' => 0,
            'confirmation_sent' => 0
        ];
        
        $insert_format = ['%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d'];
        
        $result = $wpdb->insert($table_name, $insert_data, $insert_format);
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to create appointment', ['status' => 500]);
        }
        
        $appointment_id = $wpdb->insert_id;
        
        // Get the created appointment with related data
        $patients_table = $this->dbManager->getTableName('patients');
        $providers_table = $this->dbManager->getTableName('providers');
        $locations_table = $this->dbManager->getTableName('locations');
        
        $query = "
            SELECT 
                a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.email as patient_email,
                p.phone as patient_phone,
                CONCAT(pr.first_name, ' ', pr.last_name) as provider_name,
                pr.email as provider_email,
                pr.phone as provider_phone,
                l.name as location_name,
                l.address as location_address
            FROM {$table_name} a 
            LEFT JOIN {$patients_table} p ON a.patient_id = p.id
            LEFT JOIN {$providers_table} pr ON a.provider_id = pr.id
            LEFT JOIN {$locations_table} l ON a.location_id = l.id
            WHERE a.id = %d
        ";
        
        $appointment = $wpdb->get_row($wpdb->prepare($query, $appointment_id), ARRAY_A);
        
        return new WP_REST_Response($appointment, 201);
    }

    /**
     * Update appointment
     */
    public function updateAppointment(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        $table_name = $this->dbManager->getTableName('appointments');
        
        // Check if appointment exists
        $existing_appointment = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$existing_appointment) {
            return new WP_Error('appointment_not_found', 'Appointment not found', ['status' => 404]);
        }
        
        // Check for time conflicts if time is being changed
        if (isset($data['start_time']) || isset($data['end_time']) || isset($data['appointment_date']) || isset($data['provider_id'])) {
            $provider_id = $data['provider_id'] ?? $existing_appointment['provider_id'];
            $appointment_date = $data['appointment_date'] ?? $existing_appointment['appointment_date'];
            $start_time = $data['start_time'] ?? $existing_appointment['start_time'];
            $end_time = $data['end_time'] ?? $existing_appointment['end_time'];
            
            $conflict_query = "
                SELECT COUNT(*) FROM {$table_name} 
                WHERE provider_id = %d 
                AND appointment_date = %s 
                AND id != %d
                AND status IN ('scheduled', 'confirmed')
                AND (
                    (start_time <= %s AND end_time > %s) OR
                    (start_time < %s AND end_time >= %s) OR
                    (start_time >= %s AND end_time <= %s)
                )
            ";
            
            $conflicts = $wpdb->get_var($wpdb->prepare(
                $conflict_query,
                $provider_id,
                $appointment_date,
                $id,
                $start_time, $start_time,
                $end_time, $end_time,
                $start_time, $end_time
            ));
            
            if ($conflicts > 0) {
                return new WP_Error('time_conflict', 'Provider has a conflicting appointment at this time', ['status' => 409]);
            }
        }
        
        // Prepare data for update
        $update_data = [];
        $update_format = [];
        
        $allowed_fields = [
            'patient_id', 'provider_id', 'location_id', 'room_id', 'appointment_date',
            'start_time', 'end_time', 'duration', 'status', 'notes', 'internal_notes',
            'reminder_sent', 'confirmation_sent'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['patient_id', 'provider_id', 'location_id', 'room_id', 'duration', 'reminder_sent', 'confirmation_sent'])) {
                    $update_data[$field] = intval($data[$field]);
                    $update_format[] = '%d';
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
            return new WP_Error('database_error', 'Failed to update appointment', ['status' => 500]);
        }
        
        // Get updated appointment with related data
        $patients_table = $this->dbManager->getTableName('patients');
        $providers_table = $this->dbManager->getTableName('providers');
        $locations_table = $this->dbManager->getTableName('locations');
        
        $query = "
            SELECT 
                a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.email as patient_email,
                p.phone as patient_phone,
                CONCAT(pr.first_name, ' ', pr.last_name) as provider_name,
                pr.email as provider_email,
                pr.phone as provider_phone,
                l.name as location_name,
                l.address as location_address
            FROM {$table_name} a 
            LEFT JOIN {$patients_table} p ON a.patient_id = p.id
            LEFT JOIN {$providers_table} pr ON a.provider_id = pr.id
            LEFT JOIN {$locations_table} l ON a.location_id = l.id
            WHERE a.id = %d
        ";
        
        $appointment = $wpdb->get_row($wpdb->prepare($query, $id), ARRAY_A);
        
        return new WP_REST_Response($appointment, 200);
    }

    /**
     * Delete appointment
     */
    public function deleteAppointment(WP_REST_Request $request)
    {
        global $wpdb;
        
        $id = $request->get_param('id');
        $table_name = $this->dbManager->getTableName('appointments');
        
        // Check if appointment exists
        $existing_appointment = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$existing_appointment) {
            return new WP_Error('appointment_not_found', 'Appointment not found', ['status' => 404]);
        }
        
        // Check if appointment can be deleted (not completed)
        if ($existing_appointment['status'] === 'completed') {
            return new WP_Error('cannot_delete', 'Cannot delete completed appointments', ['status' => 400]);
        }
        
        // Delete appointment
        $result = $wpdb->delete($table_name, ['id' => $id], ['%d']);
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to delete appointment', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Appointment deleted successfully', 'id' => $id], 200);
    }

    /**
     * Get appointment availability
     */
    public function getAppointmentAvailability(WP_REST_Request $request)
    {
        global $wpdb;
        
        $provider_id = $request->get_param('provider_id');
        $date = $request->get_param('date');
        $duration = $request->get_param('duration') ?: 30;
        
        if (!$provider_id || !$date) {
            return new WP_Error('missing_params', 'Provider ID and date are required', ['status' => 400]);
        }
        
        $table_name = $this->dbManager->getTableName('appointments');
        
        // Get provider's appointments for the date
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT start_time, end_time FROM {$table_name} 
             WHERE provider_id = %d AND appointment_date = %s AND status IN ('scheduled', 'confirmed')
             ORDER BY start_time",
            $provider_id, $date
        ), ARRAY_A);
        
        // Get provider working hours (this would come from provider settings)
        $working_hours = [
            'start' => '09:00',
            'end' => '17:00'
        ];
        
        // Generate available time slots
        $available_slots = [];
        $current_time = new \DateTime($date . ' ' . $working_hours['start']);
        $end_time = new \DateTime($date . ' ' . $working_hours['end']);
        $slot_duration = new \DateInterval('PT' . $duration . 'M');
        
        while ($current_time->add($slot_duration) <= $end_time) {
            $slot_start = clone $current_time;
            $slot_start->sub($slot_duration);
            $slot_end = clone $current_time;
            
            $slot_start_str = $slot_start->format('H:i');
            $slot_end_str = $slot_end->format('H:i');
            
            // Check if this slot conflicts with existing appointments
            $conflicts = false;
            foreach ($appointments as $appointment) {
                $appt_start = new \DateTime($date . ' ' . $appointment['start_time']);
                $appt_end = new \DateTime($date . ' ' . $appointment['end_time']);
                
                if (($slot_start < $appt_end) && ($slot_end > $appt_start)) {
                    $conflicts = true;
                    break;
                }
            }
            
            if (!$conflicts) {
                $available_slots[] = [
                    'start_time' => $slot_start_str,
                    'end_time' => $slot_end_str,
                    'duration' => $duration
                ];
            }
        }
        
        return new WP_REST_Response([
            'date' => $date,
            'provider_id' => $provider_id,
            'available_slots' => $available_slots
        ], 200);
    }
}