<?php
/**
 * Staff Model
 * Handles staff data operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_Staff extends Medx360_BaseModel {
    
    protected $table_name;
    protected $fillable = array(
        'user_id', 'first_name', 'last_name', 'email', 'phone', 'specialty',
        'license_number', 'hire_date', 'salary', 'status', 'working_hours'
    );
    
    protected $hidden = array('salary');
    protected $casts = array(
        'user_id' => 'integer',
        'salary' => 'float',
        'hire_date' => 'date'
    );
    
    public function __construct() {
        parent::__construct();
        $this->table_name = $this->database->get_table('staff');
    }
    
    /**
     * Get staff with appointments
     */
    public function get_with_appointments($id, $date_from = null, $date_to = null) {
        global $wpdb;
        
        $staff = $this->find($id);
        if (!$staff) {
            return null;
        }
        
        $where_conditions = array("a.staff_id = %d");
        $where_values = array($id);
        
        if ($date_from && $date_to) {
            $where_conditions[] = "a.appointment_date BETWEEN %s AND %s";
            $where_values[] = $date_from;
            $where_values[] = $date_to;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name
             FROM {$this->database->get_table('appointments')} a
             LEFT JOIN {$this->database->get_table('patients')} p ON a.patient_id = p.id
             WHERE $where_clause
             ORDER BY a.appointment_date DESC, a.appointment_time DESC",
            $where_values
        ));
        
        $staff->appointments = $appointments;
        return $staff;
    }
    
    /**
     * Get staff availability for a date
     */
    public function get_availability($id, $date) {
        global $wpdb;
        
        $staff = $this->find($id);
        if (!$staff) {
            return null;
        }
        
        // Get working hours
        $working_hours = json_decode($staff->working_hours, true);
        if (!$working_hours) {
            $working_hours = $this->get_default_working_hours();
        }
        
        $day = strtolower(date('l', strtotime($date)));
        $day_hours = $working_hours[$day] ?? '09:00-17:00';
        
        if ($day_hours === 'closed') {
            return array('available' => false, 'reason' => 'Day off');
        }
        
        // Get existing appointments
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT appointment_time, duration FROM {$this->table_name} a
             WHERE staff_id = %d AND appointment_date = %s AND status IN ('scheduled', 'confirmed')
             ORDER BY appointment_time",
            $id, $date
        ));
        
        // Generate available time slots
        $available_slots = $this->generate_time_slots($day_hours, $appointments);
        
        return array(
            'available' => true,
            'working_hours' => $day_hours,
            'appointments' => $appointments,
            'available_slots' => $available_slots
        );
    }
    
    /**
     * Get staff statistics
     */
    public function get_statistics($id, $date_from = null, $date_to = null) {
        global $wpdb;
        
        $where_conditions = array("staff_id = %d");
        $where_values = array($id);
        
        if ($date_from && $date_to) {
            $where_conditions[] = "appointment_date BETWEEN %s AND %s";
            $where_values[] = $date_from;
            $where_values[] = $date_to;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show_appointments,
                COALESCE(SUM(cost), 0) as total_revenue,
                AVG(duration) as avg_appointment_duration
             FROM {$this->database->get_table('appointments')}
             WHERE $where_clause",
            $where_values
        ));
        
        return $stats;
    }
    
    /**
     * Generate time slots
     */
    private function generate_time_slots($working_hours, $appointments) {
        list($start_time, $end_time) = explode('-', $working_hours);
        
        $slots = array();
        $current_time = strtotime($start_time);
        $end_timestamp = strtotime($end_time);
        
        while ($current_time < $end_timestamp) {
            $slot_start = date('H:i:s', $current_time);
            $slot_end = date('H:i:s', $current_time + 1800); // 30 minutes
            
            // Check if slot conflicts with existing appointments
            $conflict = false;
            foreach ($appointments as $appointment) {
                $app_start = strtotime($appointment->appointment_time);
                $app_end = strtotime($appointment->appointment_time . " +{$appointment->duration} minutes");
                
                if (($current_time >= $app_start && $current_time < $app_end) ||
                    ($current_time + 1800 > $app_start && $current_time + 1800 <= $app_end)) {
                    $conflict = true;
                    break;
                }
            }
            
            if (!$conflict) {
                $slots[] = array(
                    'start' => $slot_start,
                    'end' => $slot_end,
                    'formatted' => date('g:i A', $current_time) . ' - ' . date('g:i A', $current_time + 1800)
                );
            }
            
            $current_time += 1800; // Move to next 30-minute slot
        }
        
        return $slots;
    }
    
    /**
     * Get default working hours
     */
    private function get_default_working_hours() {
        return array(
            'monday' => '09:00-17:00',
            'tuesday' => '09:00-17:00',
            'wednesday' => '09:00-17:00',
            'thursday' => '09:00-17:00',
            'friday' => '09:00-17:00',
            'saturday' => '10:00-14:00',
            'sunday' => 'closed'
        );
    }
    
    /**
     * Search staff with advanced filters
     */
    public function search_advanced($filters = array()) {
        global $wpdb;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($filters['search'])) {
            $where_conditions[] = "(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR specialty LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($filters['specialty'])) {
            $where_conditions[] = "specialty = %s";
            $where_values[] = $filters['specialty'];
        }
        
        if (!empty($filters['status'])) {
            $where_conditions[] = "status = %s";
            $where_values[] = $filters['status'];
        }
        
        if (!empty($filters['hire_date_from'])) {
            $where_conditions[] = "hire_date >= %s";
            $where_values[] = $filters['hire_date_from'];
        }
        
        if (!empty($filters['hire_date_to'])) {
            $where_conditions[] = "hire_date <= %s";
            $where_values[] = $filters['hire_date_to'];
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
     * Get staff by specialty
     */
    public function get_by_specialty($specialty) {
        return $this->findAllBy('specialty', $specialty);
    }
    
    /**
     * Get available staff for appointment
     */
    public function get_available_staff($date, $time, $duration = 30) {
        global $wpdb;
        
        $day = strtolower(date('l', strtotime($date)));
        
        // Get all active staff
        $staff = $wpdb->get_results("SELECT * FROM {$this->table_name} WHERE status = 'active'");
        
        $available_staff = array();
        
        foreach ($staff as $staff_member) {
            $working_hours = json_decode($staff_member->working_hours, true);
            if (!$working_hours) {
                $working_hours = $this->get_default_working_hours();
            }
            
            $day_hours = $working_hours[$day] ?? '09:00-17:00';
            
            if ($day_hours === 'closed') {
                continue;
            }
            
            list($start_time, $end_time) = explode('-', $day_hours);
            
            // Check if requested time is within working hours
            if ($time >= $start_time && $time <= $end_time) {
                // Check for conflicts
                $conflict = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$this->database->get_table('appointments')}
                     WHERE staff_id = %d AND appointment_date = %s
                     AND status IN ('scheduled', 'confirmed')
                     AND ((appointment_time <= %s AND DATE_ADD(appointment_time, INTERVAL duration MINUTE) > %s) OR
                          (appointment_time < %s AND DATE_ADD(appointment_time, INTERVAL duration MINUTE) >= %s))",
                    $staff_member->id, $date, $time, $time, 
                    date('H:i:s', strtotime($time . " +{$duration} minutes")),
                    date('H:i:s', strtotime($time . " +{$duration} minutes"))
                ));
                
                if (!$conflict) {
                    $available_staff[] = $staff_member;
                }
            }
        }
        
        return $available_staff;
    }
    
    /**
     * Validate staff data
     */
    public function validate_staff_data($data) {
        $rules = array(
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'min:10|max:20',
            'specialty' => 'max:100',
            'license_number' => 'max:50',
            'hire_date' => 'date',
            'salary' => 'numeric|min:0',
            'status' => 'in:active,inactive,terminated'
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
     * Get staff workload
     */
    public function get_workload($id, $date_from, $date_to) {
        global $wpdb;
        
        $workload = $wpdb->get_results($wpdb->prepare(
            "SELECT appointment_date, COUNT(*) as appointment_count, SUM(duration) as total_duration
             FROM {$this->database->get_table('appointments')}
             WHERE staff_id = %d AND appointment_date BETWEEN %s AND %s
             AND status IN ('scheduled', 'confirmed', 'completed')
             GROUP BY appointment_date
             ORDER BY appointment_date",
            $id, $date_from, $date_to
        ));
        
        return $workload;
    }
    
    /**
     * Export staff data
     */
    public function export($filters = array()) {
        $staff = $this->search_advanced($filters);
        
        $export_data = array();
        foreach ($staff as $staff_member) {
            $export_data[] = array(
                'ID' => $staff_member->id,
                'User ID' => $staff_member->user_id,
                'First Name' => $staff_member->first_name,
                'Last Name' => $staff_member->last_name,
                'Email' => $staff_member->email,
                'Phone' => $staff_member->phone,
                'Specialty' => $staff_member->specialty,
                'License Number' => $staff_member->license_number,
                'Hire Date' => $staff_member->hire_date,
                'Salary' => $staff_member->salary,
                'Status' => $staff_member->status,
                'Working Hours' => $staff_member->working_hours,
                'Created At' => $staff_member->created_at
            );
        }
        
        return $export_data;
    }
}
