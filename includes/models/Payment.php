<?php
/**
 * Payment Model
 * Handles payment data operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_Payment extends Medx360_BaseModel {
    
    protected $table_name;
    protected $fillable = array(
        'patient_id', 'appointment_id', 'amount', 'payment_method', 'payment_status',
        'transaction_id', 'payment_date', 'notes'
    );
    
    protected $hidden = array();
    protected $casts = array(
        'patient_id' => 'integer',
        'appointment_id' => 'integer',
        'amount' => 'float',
        'payment_date' => 'datetime'
    );
    
    public function __construct() {
        parent::__construct();
        $this->table_name = $this->database->get_table('payments');
    }
    
    /**
     * Get payment with patient and appointment details
     */
    public function get_with_details($id) {
        global $wpdb;
        
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT p.*, 
                    pt.first_name as patient_first_name, pt.last_name as patient_last_name,
                    pt.email as patient_email, pt.phone as patient_phone,
                    a.appointment_date, a.appointment_time, a.appointment_type,
                    s.first_name as staff_first_name, s.last_name as staff_last_name
             FROM {$this->table_name} p
             LEFT JOIN {$this->database->get_table('patients')} pt ON p.patient_id = pt.id
             LEFT JOIN {$this->database->get_table('appointments')} a ON p.appointment_id = a.id
             LEFT JOIN {$this->database->get_table('staff')} s ON a.staff_id = s.id
             WHERE p.id = %d",
            $id
        ));
        
        return $payment;
    }
    
    /**
     * Get payments by date range
     */
    public function get_by_date_range($date_from, $date_to, $status = null) {
        global $wpdb;
        
        $where_conditions = array("payment_date BETWEEN %s AND %s");
        $where_values = array($date_from, $date_to);
        
        if ($status) {
            $where_conditions[] = "payment_status = %s";
            $where_values[] = $status;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, 
                    pt.first_name as patient_first_name, pt.last_name as patient_last_name,
                    a.appointment_date, a.appointment_time
             FROM {$this->table_name} p
             LEFT JOIN {$this->database->get_table('patients')} pt ON p.patient_id = pt.id
             LEFT JOIN {$this->database->get_table('appointments')} a ON p.appointment_id = a.id
             WHERE $where_clause
             ORDER BY p.payment_date DESC",
            $where_values
        ));
    }
    
    /**
     * Get payments by patient
     */
    public function get_by_patient($patient_id, $limit = null, $offset = null) {
        global $wpdb;
        
        $query = "SELECT p.*, a.appointment_date, a.appointment_time
                 FROM {$this->table_name} p
                 LEFT JOIN {$this->database->get_table('appointments')} a ON p.appointment_id = a.id
                 WHERE p.patient_id = %d
                 ORDER BY p.payment_date DESC";
        
        $values = array($patient_id);
        
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
     * Get payment statistics
     */
    public function get_statistics($date_from = null, $date_to = null) {
        global $wpdb;
        
        $where_clause = '';
        $where_values = array();
        
        if ($date_from && $date_to) {
            $where_clause = "WHERE payment_date BETWEEN %s AND %s";
            $where_values = array($date_from, $date_to);
        }
        
        $query = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
                    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                    SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed_payments,
                    SUM(CASE WHEN payment_status = 'refunded' THEN 1 ELSE 0 END) as refunded_payments,
                    COALESCE(SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END), 0) as total_revenue,
                    COALESCE(SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount,
                    COALESCE(SUM(CASE WHEN payment_status = 'refunded' THEN amount ELSE 0 END), 0) as refunded_amount,
                    AVG(CASE WHEN payment_status = 'completed' THEN amount ELSE NULL END) as avg_payment_amount
                  FROM {$this->table_name} $where_clause";
        
        if (!empty($where_values)) {
            return $wpdb->get_row($wpdb->prepare($query, $where_values));
        }
        
        return $wpdb->get_row($query);
    }
    
    /**
     * Get payments by method
     */
    public function get_by_payment_method($method, $date_from = null, $date_to = null) {
        global $wpdb;
        
        $where_conditions = array("payment_method = %s");
        $where_values = array($method);
        
        if ($date_from && $date_to) {
            $where_conditions[] = "payment_date BETWEEN %s AND %s";
            $where_values[] = $date_from;
            $where_values[] = $date_to;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE $where_clause ORDER BY payment_date DESC",
            $where_values
        ));
    }
    
    /**
     * Get overdue payments
     */
    public function get_overdue_payments($days_overdue = 30) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, 
                    pt.first_name as patient_first_name, pt.last_name as patient_last_name,
                    pt.email as patient_email, pt.phone as patient_phone,
                    DATEDIFF(CURDATE(), p.payment_date) as days_overdue
             FROM {$this->table_name} p
             LEFT JOIN {$this->database->get_table('patients')} pt ON p.patient_id = pt.id
             WHERE p.payment_status = 'pending'
             AND p.payment_date < DATE_SUB(CURDATE(), INTERVAL %d DAY)
             ORDER BY p.payment_date ASC",
            $days_overdue
        ));
    }
    
    /**
     * Process payment
     */
    public function process_payment($id, $transaction_id = null) {
        $data = array('payment_status' => 'completed');
        
        if ($transaction_id) {
            $data['transaction_id'] = $transaction_id;
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Mark payment as failed
     */
    public function mark_failed($id, $reason = null) {
        $data = array('payment_status' => 'failed');
        
        if ($reason) {
            $data['notes'] = $reason;
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Refund payment
     */
    public function refund_payment($id, $amount = null, $reason = null) {
        $payment = $this->find($id);
        if (!$payment) {
            return false;
        }
        
        $refund_amount = $amount ?: $payment->amount;
        
        $data = array(
            'payment_status' => 'refunded',
            'amount' => $refund_amount
        );
        
        if ($reason) {
            $data['notes'] = $reason;
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Get monthly revenue
     */
    public function get_monthly_revenue($year = null, $month = null) {
        global $wpdb;
        
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('n');
        }
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table_name}
             WHERE payment_status = 'completed'
             AND YEAR(payment_date) = %d AND MONTH(payment_date) = %d",
            $year, $month
        ));
    }
    
    /**
     * Get revenue by payment method
     */
    public function get_revenue_by_method($date_from = null, $date_to = null) {
        global $wpdb;
        
        $where_clause = "WHERE payment_status = 'completed'";
        $where_values = array();
        
        if ($date_from && $date_to) {
            $where_clause .= " AND payment_date BETWEEN %s AND %s";
            $where_values[] = $date_from;
            $where_values[] = $date_to;
        }
        
        $query = "SELECT payment_method, COUNT(*) as count, SUM(amount) as total
                  FROM {$this->table_name} $where_clause
                  GROUP BY payment_method
                  ORDER BY total DESC";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($query, $where_values));
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Generate payment report
     */
    public function generate_report($date_from, $date_to, $filters = array()) {
        global $wpdb;
        
        $where_conditions = array("payment_date BETWEEN %s AND %s");
        $where_values = array($date_from, $date_to);
        
        if (!empty($filters['status'])) {
            $where_conditions[] = "payment_status = %s";
            $where_values[] = $filters['status'];
        }
        
        if (!empty($filters['payment_method'])) {
            $where_conditions[] = "payment_method = %s";
            $where_values[] = $filters['payment_method'];
        }
        
        if (!empty($filters['patient_id'])) {
            $where_conditions[] = "patient_id = %s";
            $where_values[] = $filters['patient_id'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, 
                    pt.first_name as patient_first_name, pt.last_name as patient_last_name,
                    a.appointment_date, a.appointment_time, a.appointment_type
             FROM {$this->table_name} p
             LEFT JOIN {$this->database->get_table('patients')} pt ON p.patient_id = pt.id
             LEFT JOIN {$this->database->get_table('appointments')} a ON p.appointment_id = a.id
             WHERE $where_clause
             ORDER BY p.payment_date DESC",
            $where_values
        ));
    }
    
    /**
     * Validate payment data
     */
    public function validate_payment_data($data) {
        $rules = array(
            'patient_id' => 'required|numeric',
            'appointment_id' => 'numeric',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'in:cash,card,insurance,bank_transfer,other',
            'payment_status' => 'in:pending,completed,failed,refunded',
            'transaction_id' => 'max:100'
        );
        
        return $this->validate($data, $rules);
    }
    
    /**
     * Create payment for appointment
     */
    public function create_for_appointment($appointment_id, $amount, $payment_method = 'cash') {
        global $wpdb;
        
        // Get appointment details
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->database->get_table('appointments')} WHERE id = %d",
            $appointment_id
        ));
        
        if (!$appointment) {
            return false;
        }
        
        $payment_data = array(
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment_id,
            'amount' => $amount,
            'payment_method' => $payment_method,
            'payment_status' => 'pending',
            'payment_date' => current_time('mysql')
        );
        
        return $this->create($payment_data);
    }
    
    /**
     * Get patient payment history
     */
    public function get_patient_history($patient_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, a.appointment_date, a.appointment_time, a.appointment_type
             FROM {$this->table_name} p
             LEFT JOIN {$this->database->get_table('appointments')} a ON p.appointment_id = a.id
             WHERE p.patient_id = %d
             ORDER BY p.payment_date DESC",
            $patient_id
        ));
    }
    
    /**
     * Get outstanding balance for patient
     */
    public function get_outstanding_balance($patient_id) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table_name}
             WHERE patient_id = %d AND payment_status = 'pending'",
            $patient_id
        ));
    }
    
    /**
     * Export payments data
     */
    public function export($filters = array()) {
        $payments = $this->get_by_date_range(
            $filters['date_from'] ?? date('Y-m-01'),
            $filters['date_to'] ?? date('Y-m-t')
        );
        
        $export_data = array();
        foreach ($payments as $payment) {
            $export_data[] = array(
                'ID' => $payment->id,
                'Patient Name' => $payment->patient_first_name . ' ' . $payment->patient_last_name,
                'Appointment Date' => $payment->appointment_date,
                'Appointment Time' => $payment->appointment_time,
                'Amount' => $payment->amount,
                'Payment Method' => $payment->payment_method,
                'Status' => $payment->payment_status,
                'Transaction ID' => $payment->transaction_id,
                'Payment Date' => $payment->payment_date,
                'Notes' => $payment->notes,
                'Created At' => $payment->created_at
            );
        }
        
        return $export_data;
    }
}
