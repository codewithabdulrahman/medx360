<?php
/**
 * Appointment Model
 * Handles appointment data operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_Appointment extends Medx360_BaseModel {
    
    protected $table_name;
    protected $fillable = array(
        'patient_id', 'staff_id', 'appointment_date', 'appointment_time', 'duration',
        'status', 'appointment_type', 'notes', 'cost'
    );
    
    protected $hidden = array();
    protected $casts = array(
        'patient_id' => 'integer',
        'staff_id' => 'integer',
        'duration' => 'integer',
        'cost' => 'float',
        'appointment_date' => 'date',
        'appointment_time' => 'time'
    );
    
    public function __construct() {
        parent::__construct();
        $this->table_name = $this->database->get_table('appointments');
    }
    
    /**
     * Get appointment with patient and staff details
     */
    public function get_with_details($id) {
        global $wpdb;
        
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    p.email as patient_email, p.phone as patient_phone,
                    s.first_name as staff_first_name, s.last_name as staff_last_name,
                    s.specialty as staff_specialty
             FROM {$this->table_name} a
             LEFT JOIN {$this->database->get_table('patients')} p ON a.patient_id = p.id
             LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
             WHERE a.id = %d",
            $id
        ));
        
        return $appointment;
    }
    
    /**
     * Get appointments for calendar view
     */
    public function get_calendar_appointments($month, $year, $staff_id = null) {
        global $wpdb;
        
        $where_conditions = array(
            "MONTH(appointment_date) = %d",
            "YEAR(appointment_date) = %d"
        );
        $where_values = array($month, $year);
        
        if ($staff_id) {
            $where_conditions[] = "staff_id = %d";
            $where_values[] = $staff_id;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    s.first_name as staff_first_name, s.last_name as staff_last_name
             FROM {$this->table_name} a
             LEFT JOIN {$this->database->get_table('patients')} p ON a.patient_id = p.id
             LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
             WHERE $where_clause
             ORDER BY appointment_date, appointment_time",
            $where_values
        ));
    }
    
    /**
     * Get appointments by date range
     */
    public function get_by_date_range($date_from, $date_to, $staff_id = null) {
        global $wpdb;
        
        $where_conditions = array(
            "appointment_date BETWEEN %s AND %s"
        );
        $where_values = array($date_from, $date_to);
        
        if ($staff_id) {
            $where_conditions[] = "staff_id = %d";
            $where_values[] = $staff_id;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    s.first_name as staff_first_name, s.last_name as staff_last_name
             FROM {$this->table_name} a
             LEFT JOIN {$this->database->get_table('patients')} p ON a.patient_id = p.id
             LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
             WHERE $where_clause
             ORDER BY appointment_date, appointment_time",
            $where_values
        ));
    }
    
    /**
     * Get today's appointments
     */
    public function get_today_appointments($staff_id = null) {
        global $wpdb;
        
        $where_conditions = array("appointment_date = CURDATE()");
        $where_values = array();
        
        if ($staff_id) {
            $where_conditions[] = "staff_id = %d";
            $where_values[] = $staff_id;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT a.*, 
                        p.first_name as patient_first_name, p.last_name as patient_last_name,
                        s.first_name as staff_first_name, s.last_name as staff_last_name
                 FROM {$this->table_name} a
                 LEFT JOIN {$this->database->get_table('patients')} p ON a.patient_id = p.id
                 LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
                 WHERE $where_clause
                 ORDER BY appointment_time";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($query, $where_values));
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get upcoming appointments
     */
    public function get_upcoming_appointments($days_ahead = 7, $staff_id = null) {
        global $wpdb;
        
        $where_conditions = array(
            "appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL %d DAY)",
            "status IN ('scheduled', 'confirmed')"
        );
        $where_values = array($days_ahead);
        
        if ($staff_id) {
            $where_conditions[] = "staff_id = %d";
            $where_values[] = $staff_id;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, 
                    p.first_name as patient_first_name, p.last_name as patient_last_name,
                    s.first_name as staff_first_name, s.last_name as staff_last_name
             FROM {$this->table_name} a
             LEFT JOIN {$this->database->get_table('patients')} p ON a.patient_id = p.id
             LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
             WHERE $where_clause
             ORDER BY appointment_date, appointment_time",
            $where_values
        ));
    }
    
    /**
     * Check for appointment conflicts
     */
    public function check_conflict($staff_id, $appointment_date, $appointment_time, $duration, $exclude_id = null) {
        global $wpdb;
        
        $start_time = $appointment_time;
        $end_time = date('H:i:s', strtotime($appointment_time . " +{$duration} minutes"));
        
        $where_conditions = array(
            "staff_id = %d",
            "appointment_date = %s",
            "status IN ('scheduled', 'confirmed')",
            "((appointment_time <= %s AND DATE_ADD(appointment_time, INTERVAL duration MINUTE) > %s) OR
              (appointment_time < %s AND DATE_ADD(appointment_time, INTERVAL duration MINUTE) >= %s))"
        );
        $where_values = array($staff_id, $appointment_date, $start_time, $start_time, $end_time, $end_time);
        
        if ($exclude_id) {
            $where_conditions[] = "id != %d";
            $where_values[] = $exclude_id;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE $where_clause",
            $where_values
        ));
    }
    
    /**
     * Get appointment statistics
     */
    public function get_statistics($date_from = null, $date_to = null) {
        global $wpdb;
        
        $where_clause = '';
        $where_values = array();
        
        if ($date_from && $date_to) {
            $where_clause = "WHERE appointment_date BETWEEN %s AND %s";
            $where_values = array($date_from, $date_to);
        }
        
        $query = "SELECT 
                    COUNT(*) as total_appointments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    COALESCE(SUM(cost), 0) as total_revenue
                  FROM {$this->table_name} $where_clause";
        
        if (!empty($where_values)) {
            return $wpdb->get_row($wpdb->prepare($query, $where_values));
        }
        
        return $wpdb->get_row($query);
    }
    
    /**
     * Get appointments by status
     */
    public function get_by_status($status, $limit = null, $offset = null) {
        global $wpdb;
        
        $query = "SELECT a.*, 
                        p.first_name as patient_first_name, p.last_name as patient_last_name,
                        s.first_name as staff_first_name, s.last_name as staff_last_name
                 FROM {$this->table_name} a
                 LEFT JOIN {$this->database->get_table('patients')} p ON a.patient_id = p.id
                 LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
                 WHERE a.status = %s
                 ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        $values = array($status);
        
        if ($limit) {
            $query .= " LIMIT %d";
            $values[] = $limit;
            
            if ($offset) {
                $query .= " OFFSET %d";
                $values[] = $offset;
            }
        }
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }
    
    /**
     * Confirm appointment
     */
    public function confirm($id) {
        return $this->update($id, array('status' => 'confirmed'));
    }
    
    /**
     * Complete appointment
     */
    public function complete($id) {
        return $this->update($id, array('status' => 'completed'));
    }
    
    /**
     * Cancel appointment
     */
    public function cancel($id, $reason = null) {
        $data = array('status' => 'cancelled');
        if ($reason) {
            $data['notes'] = $reason;
        }
        return $this->update($id, $data);
    }
    
    /**
     * Mark as no-show
     */
    public function mark_no_show($id) {
        return $this->update($id, array('status' => 'no_show'));
    }
    
    /**
     * Reschedule appointment
     */
    public function reschedule($id, $new_date, $new_time) {
        // Check for conflicts
        $appointment = $this->find($id);
        if (!$appointment) {
            return false;
        }
        
        $conflict = $this->check_conflict($appointment->staff_id, $new_date, $new_time, $appointment->duration, $id);
        if ($conflict) {
            return new WP_Error('conflict', 'Appointment time conflicts with existing appointment');
        }
        
        return $this->update($id, array(
            'appointment_date' => $new_date,
            'appointment_time' => $new_time,
            'status' => 'scheduled'
        ));
    }
    
    /**
     * Validate appointment data
     */
    public function validate_appointment_data($data) {
        $rules = array(
            'patient_id' => 'required|numeric',
            'staff_id' => 'required|numeric',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'duration' => 'numeric|min:15|max:480',
            'status' => 'in:scheduled,confirmed,completed,cancelled,no_show',
            'appointment_type' => 'max:50',
            'cost' => 'numeric|min:0'
        );
        
        return $this->validate($data, $rules);
    }
    
    /**
     * Get appointment duration in minutes
     */
    public function get_duration_minutes($appointment_time, $duration) {
        $start = strtotime($appointment_time);
        $end = strtotime($appointment_time . " +{$duration} minutes");
        
        return ($end - $start) / 60;
    }
    
    /**
     * Get appointment end time
     */
    public function get_end_time($appointment_time, $duration) {
        return date('H:i:s', strtotime($appointment_time . " +{$duration} minutes"));
    }
    
    /**
     * Export appointments data
     */
    public function export($filters = array()) {
        $appointments = $this->get_by_date_range(
            $filters['date_from'] ?? date('Y-m-01'),
            $filters['date_to'] ?? date('Y-m-t')
        );
        
        $export_data = array();
        foreach ($appointments as $appointment) {
            $export_data[] = array(
                'ID' => $appointment->id,
                'Patient Name' => $appointment->patient_first_name . ' ' . $appointment->patient_last_name,
                'Staff Name' => $appointment->staff_first_name . ' ' . $appointment->staff_last_name,
                'Date' => $appointment->appointment_date,
                'Time' => $appointment->appointment_time,
                'Duration' => $appointment->duration,
                'Type' => $appointment->appointment_type,
                'Status' => $appointment->status,
                'Cost' => $appointment->cost,
                'Notes' => $appointment->notes,
                'Created At' => $appointment->created_at
            );
        }
        
        return $export_data;
    }
}
