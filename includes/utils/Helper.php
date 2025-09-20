<?php
/**
 * Helper Utility Class
 * Provides common utility functions for the Medx360 plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Medx360_Helper {
    
    /**
     * Format date for display
     */
    public static function format_date($date, $format = 'M j, Y') {
        if (empty($date)) {
            return '';
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Format time for display
     */
    public static function format_time($time, $format = 'g:i A') {
        if (empty($time)) {
            return '';
        }
        
        return date($format, strtotime($time));
    }
    
    /**
     * Format datetime for display
     */
    public static function format_datetime($datetime, $format = 'M j, Y g:i A') {
        if (empty($datetime)) {
            return '';
        }
        
        return date($format, strtotime($datetime));
    }
    
    /**
     * Format currency
     */
    public static function format_currency($amount, $currency = 'USD') {
        if (!is_numeric($amount)) {
            return '$0.00';
        }
        
        return '$' . number_format($amount, 2);
    }
    
    /**
     * Sanitize text input
     */
    public static function sanitize_text($text) {
        return sanitize_text_field($text);
    }
    
    /**
     * Sanitize email
     */
    public static function sanitize_email($email) {
        return sanitize_email($email);
    }
    
    /**
     * Sanitize phone number
     */
    public static function sanitize_phone($phone) {
        // Remove all non-numeric characters except + at the beginning
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return substr($phone, 0, 20); // Limit to 20 characters
    }
    
    /**
     * Validate email
     */
    public static function is_valid_email($email) {
        return is_email($email);
    }
    
    /**
     * Validate phone number (basic validation)
     */
    public static function is_valid_phone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }
    
    /**
     * Generate random string
     */
    public static function generate_random_string($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $string;
    }
    
    /**
     * Generate unique ID
     */
    public static function generate_unique_id($prefix = '') {
        return $prefix . uniqid() . self::generate_random_string(4);
    }
    
    /**
     * Convert array to CSV
     */
    public static function array_to_csv($data, $filename = 'export.csv') {
        if (empty($data)) {
            return false;
        }
        
        $output = fopen('php://temp', 'r+');
        
        // Write headers
        if (!empty($data[0])) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    /**
     * Convert CSV to array
     */
    public static function csv_to_array($csv_string) {
        $lines = str_getcsv($csv_string, "\n");
        $data = array();
        
        foreach ($lines as $line) {
            $data[] = str_getcsv($line);
        }
        
        return $data;
    }
    
    /**
     * Send email notification
     */
    public static function send_email($to, $subject, $message, $headers = array()) {
        $default_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'
        );
        
        $headers = array_merge($default_headers, $headers);
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send SMS notification (placeholder - would need SMS service integration)
     */
    public static function send_sms($phone, $message) {
        // This would integrate with an SMS service like Twilio
        // For now, just log the SMS
        error_log("SMS to {$phone}: {$message}");
        return true;
    }
    
    /**
     * Log activity
     */
    public static function log_activity($user_id, $action, $details = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'medx360_activity_log';
        
        // Create activity log table if it doesn't exist
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                action varchar(100) NOT NULL,
                details text DEFAULT NULL,
                ip_address varchar(45) DEFAULT NULL,
                user_agent text DEFAULT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY action (action),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        
        return $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'action' => $action,
                'details' => json_encode($details),
                'ip_address' => self::get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get client IP address
     */
    public static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Check if user has permission
     */
    public static function user_can($capability) {
        return current_user_can($capability);
    }
    
    /**
     * Get user role
     */
    public static function get_user_role($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_userdata($user_id);
        return $user ? $user->roles[0] : null;
    }
    
    /**
     * Check if premium feature is active
     */
    public static function is_premium_active() {
        return get_option('medx360_premium_active', false);
    }
    
    /**
     * Check if specific premium feature is enabled
     */
    public static function is_premium_feature_enabled($feature) {
        if (!self::is_premium_active()) {
            return false;
        }
        
        return get_option("medx360_{$feature}_enabled", false);
    }
    
    /**
     * Get timezone offset
     */
    public static function get_timezone_offset() {
        $timezone = get_option('timezone_string');
        if ($timezone) {
            $tz = new DateTimeZone($timezone);
            $datetime = new DateTime('now', $tz);
            return $tz->getOffset($datetime);
        }
        
        return get_option('gmt_offset') * 3600;
    }
    
    /**
     * Convert timezone
     */
    public static function convert_timezone($datetime, $from_tz = 'UTC', $to_tz = null) {
        if (!$to_tz) {
            $to_tz = get_option('timezone_string') ?: 'UTC';
        }
        
        $from = new DateTimeZone($from_tz);
        $to = new DateTimeZone($to_tz);
        $date = new DateTime($datetime, $from);
        $date->setTimezone($to);
        
        return $date->format('Y-m-d H:i:s');
    }
    
    /**
     * Get business hours
     */
    public static function get_business_hours() {
        $default_hours = array(
            'monday' => '09:00-17:00',
            'tuesday' => '09:00-17:00',
            'wednesday' => '09:00-17:00',
            'thursday' => '09:00-17:00',
            'friday' => '09:00-17:00',
            'saturday' => '10:00-14:00',
            'sunday' => 'closed'
        );
        
        $settings = get_option('medx360_working_hours', $default_hours);
        
        if (is_string($settings)) {
            $settings = json_decode($settings, true);
        }
        
        return $settings ?: $default_hours;
    }
    
    /**
     * Check if time is within business hours
     */
    public static function is_business_hours($datetime) {
        $business_hours = self::get_business_hours();
        $day = strtolower(date('l', strtotime($datetime)));
        $time = date('H:i', strtotime($datetime));
        
        if (!isset($business_hours[$day]) || $business_hours[$day] === 'closed') {
            return false;
        }
        
        $hours = explode('-', $business_hours[$day]);
        if (count($hours) !== 2) {
            return false;
        }
        
        $open_time = $hours[0];
        $close_time = $hours[1];
        
        return $time >= $open_time && $time <= $close_time;
    }
    
    /**
     * Get next business day
     */
    public static function get_next_business_day($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $business_hours = self::get_business_hours();
        $next_day = $date;
        
        do {
            $next_day = date('Y-m-d', strtotime($next_day . ' +1 day'));
            $day = strtolower(date('l', strtotime($next_day)));
        } while (!isset($business_hours[$day]) || $business_hours[$day] === 'closed');
        
        return $next_day;
    }
    
    /**
     * Format file size
     */
    public static function format_file_size($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Generate QR code data (placeholder)
     */
    public static function generate_qr_code($data) {
        // This would integrate with a QR code library
        // For now, return a placeholder URL
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($data);
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encrypt($data) {
        $key = wp_salt('AUTH_KEY');
        $iv = wp_salt('SECURE_AUTH_KEY');
        
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, substr($iv, 0, 16)));
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decrypt($encrypted_data) {
        $key = wp_salt('AUTH_KEY');
        $iv = wp_salt('SECURE_AUTH_KEY');
        
        return openssl_decrypt(base64_decode($encrypted_data), 'AES-256-CBC', $key, 0, substr($iv, 0, 16));
    }
    
    /**
     * Clean up old data
     */
    public static function cleanup_old_data($days = 365) {
        global $wpdb;
        
        $tables = array(
            'medx360_activity_log' => 'created_at',
            'medx360_notifications' => 'created_at'
        );
        
        foreach ($tables as $table => $date_field) {
            $table_name = $wpdb->prefix . $table;
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name WHERE $date_field < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            ));
        }
    }
    
    /**
     * Get plugin statistics
     */
    public static function get_plugin_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Count patients
        $stats['patients'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_patients WHERE status = 'active'");
        
        // Count appointments
        $stats['appointments'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_appointments");
        
        // Count staff
        $stats['staff'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_staff WHERE status = 'active'");
        
        // Count payments
        $stats['payments'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medx360_payments");
        
        // Total revenue
        $stats['revenue'] = $wpdb->get_var("SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}medx360_payments WHERE payment_status = 'completed'");
        
        return $stats;
    }
}
