<?php
/**
 * Payments AJAX Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Payments_AJAX extends MedX360_AJAX_Controller {
    
    public function register_actions() {
        // GET endpoints
        $this->register_ajax_action('get_payments', array($this, 'get_payments'));
        $this->register_ajax_action('get_payment', array($this, 'get_payment'));
        $this->register_ajax_action('get_payments_by_booking', array($this, 'get_payments_by_booking'));
        
        // POST endpoints
        $this->register_ajax_action('create_payment', array($this, 'create_payment'));
        
        // PUT endpoints
        $this->register_ajax_action('update_payment', array($this, 'update_payment'));
        $this->register_ajax_action('refund_payment', array($this, 'refund_payment'));
        
        // DELETE endpoints
        $this->register_ajax_action('delete_payment', array($this, 'delete_payment'));
    }
    
    /**
     * Get payments collection
     */
    public function get_payments() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $pagination = $this->get_pagination_params();
        $search = $this->get_search_params();
        $filters = $this->get_filter_params();
        
        // Add payment-specific filters
        if (isset($_POST['payment_method']) && !empty($_POST['payment_method'])) {
            $filters['payment_method'] = sanitize_text_field($_POST['payment_method']);
        }
        
        if (isset($_POST['payment_gateway']) && !empty($_POST['payment_gateway'])) {
            $filters['payment_gateway'] = sanitize_text_field($_POST['payment_gateway']);
        }
        
        if (isset($_POST['booking_id']) && !empty($_POST['booking_id'])) {
            $filters['booking_id'] = intval($_POST['booking_id']);
        }
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('transaction_id', 'payment_method'));
        
        $where_conditions = array_merge($where_clause['conditions'], $search_clause['conditions']);
        $where_values = array_merge($where_clause['values'], $search_clause['values']);
        
        $where_sql = '';
        if (!empty($where_conditions)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM $table_name $where_sql";
        if (!empty($where_values)) {
            $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $where_values));
        } else {
            $total_items = $wpdb->get_var($count_sql);
        }
        
        // Get payments
        $orderby = sanitize_sql_orderby($search['orderby'] . ' ' . $search['order']);
        $sql = "SELECT * FROM $table_name $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($pagination['per_page'], $pagination['offset']));
        $payments = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Format response
        $formatted_payments = array();
        foreach ($payments as $payment) {
            $formatted_payments[] = $this->format_payment_data($payment);
        }
        
        $response = array(
            'data' => $formatted_payments,
            'pagination' => array(
                'page' => $pagination['page'],
                'per_page' => $pagination['per_page'],
                'total_items' => intval($total_items),
                'total_pages' => ceil($total_items / $pagination['per_page'])
            )
        );
        
        $this->format_response($response);
    }
    
    /**
     * Get single payment
     */
    public function get_payment() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $payment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$payment_id) {
            $this->format_error_response(__('Payment ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$payment) {
            $this->format_error_response(__('Payment not found', 'medx360'), 'payment_not_found', 404);
        }
        
        $this->format_response($this->format_payment_data($payment));
    }
    
    /**
     * Get payments by booking
     */
    public function get_payments_by_booking() {
        // Check permissions
        if (!$this->check_read_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        
        if (!$booking_id) {
            $this->format_error_response(__('Booking ID is required', 'medx360'), 'validation_error', 400);
        }
        
        $payments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE booking_id = %d ORDER BY created_at DESC",
            $booking_id
        ));
        
        $formatted_payments = array();
        foreach ($payments as $payment) {
            $formatted_payments[] = $this->format_payment_data($payment);
        }
        
        $this->format_response($formatted_payments);
    }
    
    /**
     * Create payment
     */
    public function create_payment() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $data = $this->get_post_data();
        
        // Validate required fields
        $required_fields = array('booking_id', 'amount', 'payment_method');
        $errors = $this->validate_required_fields($data, $required_fields);
        if (!empty($errors)) {
            $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if booking exists
        $bookings_table = $this->get_table_name('bookings');
        $booking_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $bookings_table WHERE id = %d",
            $data['booking_id']
        ));
        
        if (!$booking_exists) {
            $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 400);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'booking_id' => 'int',
            'amount' => 'float',
            'currency' => 'text',
            'payment_method' => 'text',
            'payment_gateway' => 'text',
            'transaction_id' => 'text',
            'status' => 'text',
            'gateway_response' => 'json'
        ));
        
        // Add timestamps
        $sanitized_data['created_at'] = current_time('mysql');
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Insert payment
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            $this->format_error_response(__('Failed to create payment', 'medx360'), 'create_failed', 500);
        }
        
        $payment_id = $wpdb->insert_id;
        
        // Get created payment
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        $this->format_response($this->format_payment_data($payment));
    }
    
    /**
     * Update payment
     */
    public function update_payment() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $payment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = $this->get_post_data();
        
        if (!$payment_id) {
            $this->format_error_response(__('Payment ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if payment exists
        $existing_payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$existing_payment) {
            $this->format_error_response(__('Payment not found', 'medx360'), 'payment_not_found', 404);
        }
        
        // Sanitize data
        $sanitized_data = $this->sanitize_data($data, array(
            'booking_id' => 'int',
            'amount' => 'float',
            'currency' => 'text',
            'payment_method' => 'text',
            'payment_gateway' => 'text',
            'transaction_id' => 'text',
            'status' => 'text',
            'gateway_response' => 'json'
        ));
        
        // Add update timestamp
        $sanitized_data['updated_at'] = current_time('mysql');
        
        // Update payment
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => $payment_id),
            array('%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to update payment', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated payment
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        $this->format_response($this->format_payment_data($payment));
    }
    
    /**
     * Refund payment
     */
    public function refund_payment() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $payment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$payment_id) {
            $this->format_error_response(__('Payment ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if payment exists
        $existing_payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$existing_payment) {
            $this->format_error_response(__('Payment not found', 'medx360'), 'payment_not_found', 404);
        }
        
        // Check if payment can be refunded
        if ($existing_payment->status !== 'completed') {
            $this->format_error_response(__('Only completed payments can be refunded', 'medx360'), 'invalid_status', 400);
        }
        
        // Update payment status
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => 'refunded',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $payment_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            $this->format_error_response(__('Failed to refund payment', 'medx360'), 'update_failed', 500);
        }
        
        // Get updated payment
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        $this->format_response($this->format_payment_data($payment));
    }
    
    /**
     * Delete payment
     */
    public function delete_payment() {
        // Check permissions
        if (!$this->check_permission()) {
            $this->format_error_response(__('Permission denied', 'medx360'), 'permission_denied', 403);
        }
        
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $payment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$payment_id) {
            $this->format_error_response(__('Payment ID is required', 'medx360'), 'validation_error', 400);
        }
        
        // Check if payment exists
        $existing_payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$existing_payment) {
            $this->format_error_response(__('Payment not found', 'medx360'), 'payment_not_found', 404);
        }
        
        // Delete payment
        $result = $wpdb->delete($table_name, array('id' => $payment_id), array('%d'));
        
        if ($result === false) {
            $this->format_error_response(__('Failed to delete payment', 'medx360'), 'delete_failed', 500);
        }
        
        $this->format_response(array('message' => __('Payment deleted successfully', 'medx360')));
    }
    
    /**
     * Format payment data for response
     */
    private function format_payment_data($payment) {
        $gateway_response = !empty($payment->gateway_response) ? json_decode($payment->gateway_response, true) : array();
        
        return array(
            'id' => intval($payment->id),
            'booking_id' => intval($payment->booking_id),
            'amount' => floatval($payment->amount),
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'payment_gateway' => $payment->payment_gateway,
            'transaction_id' => $payment->transaction_id,
            'status' => $payment->status,
            'gateway_response' => $gateway_response,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at
        );
    }
}
