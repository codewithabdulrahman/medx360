<?php
/**
 * Payments API Controller for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Payments_API extends MedX360_API_Controller {
    
    protected $rest_base = 'payments';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_payments'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_payment'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_payment_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_payment'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the payment', 'medx360'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_payment'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => $this->get_payment_params()
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/booking/(?P<booking_id>[\d]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_payments_by_booking'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'booking_id' => array(
                    'description' => __('Booking ID', 'medx360'),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/refund', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'refund_payment'),
            'permission_callback' => array($this, 'check_permission')
        ));
    }
    
    /**
     * Get payments collection
     */
    public function get_payments($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $pagination = $this->get_pagination_params($request);
        $search = $this->get_search_params($request);
        $filters = $this->get_filter_params($request);
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        $search_clause = $this->build_search_clause($search['search'], array('transaction_id', 'payment_method', 'payment_gateway'));
        
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
        
        return $this->format_response($response);
    }
    
    /**
     * Get single payment
     */
    public function get_payment($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $payment_id = $request->get_param('id');
        
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$payment) {
            return $this->format_error_response(__('Payment not found', 'medx360'), 'payment_not_found', 404);
        }
        
        return $this->format_response($this->format_payment_data($payment));
    }
    
    /**
     * Get payments by booking
     */
    public function get_payments_by_booking($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $booking_id = $request->get_param('booking_id');
        
        $payments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE booking_id = %d ORDER BY created_at DESC",
            $booking_id
        ));
        
        $formatted_payments = array();
        foreach ($payments as $payment) {
            $formatted_payments[] = $this->format_payment_data($payment);
        }
        
        return $this->format_response($formatted_payments);
    }
    
    /**
     * Create payment
     */
    public function create_payment($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $data = $request->get_json_params();
        
        // Validate data
        $errors = MedX360_Validator::validate_payment_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
        }
        
        // Check if booking exists
        $bookings_table = $this->get_table_name('bookings');
        $booking_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $bookings_table WHERE id = %d",
            $data['booking_id']
        ));
        
        if (!$booking_exists) {
            return $this->format_error_response(__('Booking not found', 'medx360'), 'booking_not_found', 400);
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
            return $this->format_error_response(__('Failed to create payment', 'medx360'), 'create_failed', 500);
        }
        
        $payment_id = $wpdb->insert_id;
        
        // Update booking payment status
        $wpdb->update(
            $bookings_table,
            array(
                'payment_status' => $sanitized_data['status'],
                'payment_method' => $sanitized_data['payment_method'],
                'payment_reference' => $sanitized_data['transaction_id'],
                'updated_at' => current_time('mysql')
            ),
            array('id' => $data['booking_id']),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        // Get created payment
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        return $this->format_response($this->format_payment_data($payment), 201);
    }
    
    /**
     * Update payment
     */
    public function update_payment($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $payment_id = $request->get_param('id');
        $data = $request->get_json_params();
        
        // Check if payment exists
        $existing_payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$existing_payment) {
            return $this->format_error_response(__('Payment not found', 'medx360'), 'payment_not_found', 404);
        }
        
        // Validate data
        $errors = MedX360_Validator::validate_payment_data($data);
        if (!empty($errors)) {
            return $this->format_error_response(implode(', ', $errors), 'validation_error', 400);
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
            return $this->format_error_response(__('Failed to update payment', 'medx360'), 'update_failed', 500);
        }
        
        // Update booking payment status if status changed
        if (isset($data['status']) && $data['status'] !== $existing_payment->status) {
            $bookings_table = $this->get_table_name('bookings');
            $wpdb->update(
                $bookings_table,
                array(
                    'payment_status' => $sanitized_data['status'],
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $existing_payment->booking_id),
                array('%s', '%s'),
                array('%d')
            );
        }
        
        // Get updated payment
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        return $this->format_response($this->format_payment_data($payment));
    }
    
    /**
     * Refund payment
     */
    public function refund_payment($request) {
        global $wpdb;
        
        $table_name = $this->get_table_name('payments');
        $payment_id = $request->get_param('id');
        
        // Check if payment exists
        $existing_payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$existing_payment) {
            return $this->format_error_response(__('Payment not found', 'medx360'), 'payment_not_found', 404);
        }
        
        // Check if payment can be refunded
        if ($existing_payment->status !== 'completed') {
            return $this->format_error_response(__('Only completed payments can be refunded', 'medx360'), 'invalid_status', 400);
        }
        
        // Update payment status to refunded
        $result = $wpdb->update(
            $table_name,
            array('status' => 'refunded', 'updated_at' => current_time('mysql')),
            array('id' => $payment_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return $this->format_error_response(__('Failed to refund payment', 'medx360'), 'update_failed', 500);
        }
        
        // Update booking payment status
        $bookings_table = $this->get_table_name('bookings');
        $wpdb->update(
            $bookings_table,
            array(
                'payment_status' => 'refunded',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $existing_payment->booking_id),
            array('%s', '%s'),
            array('%d')
        );
        
        // Get updated payment
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        return $this->format_response($this->format_payment_data($payment));
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
    
    /**
     * Get collection parameters
     */
    public function get_collection_params() {
        return array(
            'page' => array(
                'description' => __('Current page of the collection', 'medx360'),
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1
            ),
            'per_page' => array(
                'description' => __('Maximum number of items to be returned', 'medx360'),
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100
            ),
            'search' => array(
                'description' => __('Limit results to those matching a string', 'medx360'),
                'type' => 'string'
            ),
            'orderby' => array(
                'description' => __('Sort collection by object attribute', 'medx360'),
                'type' => 'string',
                'default' => 'id',
                'enum' => array('id', 'amount', 'status', 'created_at', 'updated_at')
            ),
            'order' => array(
                'description' => __('Order sort attribute ascending or descending', 'medx360'),
                'type' => 'string',
                'default' => 'DESC',
                'enum' => array('ASC', 'DESC')
            ),
            'status' => array(
                'description' => __('Filter by status', 'medx360'),
                'type' => 'string',
                'enum' => array('pending', 'completed', 'failed', 'refunded', 'cancelled')
            ),
            'payment_method' => array(
                'description' => __('Filter by payment method', 'medx360'),
                'type' => 'string'
            ),
            'payment_gateway' => array(
                'description' => __('Filter by payment gateway', 'medx360'),
                'type' => 'string'
            ),
            'booking_id' => array(
                'description' => __('Filter by booking ID', 'medx360'),
                'type' => 'integer'
            )
        );
    }
    
    /**
     * Get payment parameters
     */
    public function get_payment_params() {
        return array(
            'booking_id' => array(
                'description' => __('Booking ID', 'medx360'),
                'type' => 'integer',
                'required' => true
            ),
            'amount' => array(
                'description' => __('Payment amount', 'medx360'),
                'type' => 'number',
                'required' => true,
                'minimum' => 0
            ),
            'currency' => array(
                'description' => __('Currency code', 'medx360'),
                'type' => 'string',
                'default' => 'USD'
            ),
            'payment_method' => array(
                'description' => __('Payment method', 'medx360'),
                'type' => 'string',
                'required' => true,
                'enum' => array('cash', 'card', 'bank_transfer', 'online', 'insurance')
            ),
            'payment_gateway' => array(
                'description' => __('Payment gateway', 'medx360'),
                'type' => 'string'
            ),
            'transaction_id' => array(
                'description' => __('Transaction ID', 'medx360'),
                'type' => 'string'
            ),
            'status' => array(
                'description' => __('Payment status', 'medx360'),
                'type' => 'string',
                'enum' => array('pending', 'completed', 'failed', 'refunded', 'cancelled'),
                'default' => 'pending'
            ),
            'gateway_response' => array(
                'description' => __('Gateway response data', 'medx360'),
                'type' => 'object'
            )
        );
    }
}
