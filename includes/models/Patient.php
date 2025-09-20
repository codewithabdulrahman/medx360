<?php
/**
 * Patient Model
 * Handles patient data operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_Patient extends Medx360_BaseModel {
    
    protected $table_name;
    protected $fillable = array(
        'first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender',
        'address', 'emergency_contact_name', 'emergency_contact_phone',
        'medical_history', 'allergies', 'insurance_provider', 'insurance_number', 'status'
    );
    
    protected $hidden = array();
    protected $casts = array(
        'date_of_birth' => 'date',
        'status' => 'string'
    );
    
    public function __construct() {
        parent::__construct();
        $this->table_name = $this->database->get_table('patients');
    }
    
    /**
     * Get patient with appointments
     */
    public function get_with_appointments($id) {
        global $wpdb;
        
        $patient = $this->find($id);
        if (!$patient) {
            return null;
        }
        
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, s.first_name as staff_first_name, s.last_name as staff_last_name
             FROM {$this->database->get_table('appointments')} a
             LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
             WHERE a.patient_id = %d
             ORDER BY a.appointment_date DESC, a.appointment_time DESC",
            $id
        ));
        
        $patient->appointments = $appointments;
        return $patient;
    }
    
    /**
     * Get patient with payments
     */
    public function get_with_payments($id) {
        global $wpdb;
        
        $patient = $this->find($id);
        if (!$patient) {
            return null;
        }
        
        $payments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('payments')}
             WHERE patient_id = %d
             ORDER BY payment_date DESC",
            $id
        ));
        
        $patient->payments = $payments;
        return $patient;
    }
    
    /**
     * Get patient statistics
     */
    public function get_statistics($id) {
        global $wpdb;
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(a.id) as total_appointments,
                SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                COALESCE(SUM(p.amount), 0) as total_paid,
                COUNT(p.id) as total_payments
             FROM {$this->database->get_table('patients')} pt
             LEFT JOIN {$this->database->get_table('appointments')} a ON pt.id = a.patient_id
             LEFT JOIN {$this->database->get_table('payments')} p ON pt.id = p.patient_id AND p.payment_status = 'completed'
             WHERE pt.id = %d",
            $id
        ));
        
        return $stats;
    }
    
    /**
     * Search patients with advanced filters
     */
    public function search_advanced($filters = array()) {
        global $wpdb;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($filters['search'])) {
            $where_conditions[] = "(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($filters['status'])) {
            $where_conditions[] = "status = %s";
            $where_values[] = $filters['status'];
        }
        
        if (!empty($filters['gender'])) {
            $where_conditions[] = "gender = %s";
            $where_values[] = $filters['gender'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "created_at >= %s";
            $where_values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "created_at <= %s";
            $where_values[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT * FROM {$this->table_name} WHERE $where_clause ORDER BY created_at DESC";
        
        if (!empty($filters['limit'])) {
            $query .= " LIMIT %d";
            $where_values[] = $filters['limit'];
            
            if (!empty($filters['offset'])) {
                $query .= " OFFSET %d";
                $where_values[] = $filters['offset'];
            }
        }
        
        return $wpdb->get_results($wpdb->prepare($query, $where_values));
    }
    
    /**
     * Get patients by appointment date range
     */
    public function get_by_appointment_date_range($date_from, $date_to) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT p.* FROM {$this->table_name} p
             INNER JOIN {$this->database->get_table('appointments')} a ON p.id = a.patient_id
             WHERE a.appointment_date BETWEEN %s AND %s
             ORDER BY p.last_name, p.first_name",
            $date_from, $date_to
        ));
    }
    
    /**
     * Validate patient data
     */
    public function validate_patient_data($data) {
        $rules = array(
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|min:10|max:20',
            'date_of_birth' => 'date',
            'gender' => 'in:male,female,other',
            'status' => 'in:active,inactive,archived'
        );
        
        return $this->validate($data, $rules);
    }
    
    /**
     * Check if email exists
     */
    public function email_exists($email, $exclude_id = null) {
        global $wpdb;
        
        if ($exclude_id) {
            return $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE email = %s AND id != %d",
                $email, $exclude_id
            ));
        }
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE email = %s",
            $email
        ));
    }
    
    /**
     * Get patients with upcoming appointments
     */
    public function get_with_upcoming_appointments($days_ahead = 7) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT p.*, 
                    MIN(a.appointment_date) as next_appointment_date,
                    MIN(a.appointment_time) as next_appointment_time
             FROM {$this->table_name} p
             INNER JOIN {$this->database->get_table('appointments')} a ON p.id = a.patient_id
             WHERE a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL %d DAY)
             AND a.status IN ('scheduled', 'confirmed')
             GROUP BY p.id
             ORDER BY next_appointment_date, next_appointment_time",
            $days_ahead
        ));
    }
    
    /**
     * Get patients with overdue payments
     */
    public function get_with_overdue_payments() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT DISTINCT p.*, 
                    SUM(pay.amount) as total_overdue,
                    COUNT(pay.id) as overdue_count
             FROM {$this->table_name} p
             INNER JOIN {$this->database->get_table('payments')} pay ON p.id = pay.patient_id
             WHERE pay.payment_status = 'pending'
             AND pay.payment_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY p.id
             ORDER BY total_overdue DESC"
        );
    }
    
    /**
     * Archive patient
     */
    public function archive($id) {
        return $this->update($id, array('status' => 'archived'));
    }
    
    /**
     * Restore patient
     */
    public function restore($id) {
        return $this->update($id, array('status' => 'active'));
    }
    
    /**
     * Get patient age
     */
    public function get_age($date_of_birth) {
        if (!$date_of_birth) {
            return null;
        }
        
        $birth_date = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($birth_date);
        
        return $age->y;
    }
    
    /**
     * Export patients data
     */
    public function export($filters = array()) {
        $patients = $this->search_advanced($filters);
        
        $export_data = array();
        foreach ($patients as $patient) {
            $export_data[] = array(
                'ID' => $patient->id,
                'First Name' => $patient->first_name,
                'Last Name' => $patient->last_name,
                'Email' => $patient->email,
                'Phone' => $patient->phone,
                'Date of Birth' => $patient->date_of_birth,
                'Gender' => $patient->gender,
                'Address' => $patient->address,
                'Emergency Contact' => $patient->emergency_contact_name,
                'Emergency Phone' => $patient->emergency_contact_phone,
                'Insurance Provider' => $patient->insurance_provider,
                'Insurance Number' => $patient->insurance_number,
                'Status' => $patient->status,
                'Created At' => $patient->created_at
            );
        }
        
        return $export_data;
    }
}
