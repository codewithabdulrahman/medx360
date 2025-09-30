<?php
/**
 * Logging class for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Logger {
    
    const LOG_LEVEL_ERROR = 'error';
    const LOG_LEVEL_WARNING = 'warning';
    const LOG_LEVEL_INFO = 'info';
    const LOG_LEVEL_DEBUG = 'debug';
    
    private static $instance = null;
    private $log_file = null;
    private $enabled = true;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->enabled = defined('WP_DEBUG') && WP_DEBUG;
        $this->log_file = WP_CONTENT_DIR . '/medx360.log';
    }
    
    /**
     * Log an error message
     */
    public static function error($message, $context = array()) {
        self::get_instance()->log(self::LOG_LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Log a warning message
     */
    public static function warning($message, $context = array()) {
        self::get_instance()->log(self::LOG_LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Log an info message
     */
    public static function info($message, $context = array()) {
        self::get_instance()->log(self::LOG_LEVEL_INFO, $message, $context);
    }
    
    /**
     * Log a debug message
     */
    public static function debug($message, $context = array()) {
        self::get_instance()->log(self::LOG_LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Log a message
     */
    private function log($level, $message, $context = array()) {
        if (!$this->enabled) {
            return;
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $context_str = !empty($context) ? ' ' . json_encode($context) : '';
        $log_entry = "[{$timestamp}] [{$level}] {$message}{$context_str}" . PHP_EOL;
        
        // Write to custom log file
        error_log($log_entry, 3, $this->log_file);
        
        // Also write to WordPress debug log if enabled
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log("MedX360: {$log_entry}");
        }
    }
    
    /**
     * Log AJAX request
     */
    public static function log_ajax_request($action, $data = array(), $response = null) {
        $context = array(
            'action' => $action,
            'user_id' => get_current_user_id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        );
        
        if (!empty($data)) {
            $context['data'] = $data;
        }
        
        if ($response !== null) {
            $context['response'] = $response;
        }
        
        self::info("AJAX Request: {$action}", $context);
    }
    
    /**
     * Log database query
     */
    public static function log_query($query, $execution_time = null) {
        $context = array(
            'query' => $query,
            'execution_time' => $execution_time
        );
        
        self::debug('Database Query', $context);
    }
    
    /**
     * Log performance metrics
     */
    public static function log_performance($operation, $start_time, $end_time = null) {
        if ($end_time === null) {
            $end_time = microtime(true);
        }
        
        $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
        
        $context = array(
            'operation' => $operation,
            'execution_time_ms' => round($execution_time, 2)
        );
        
        if ($execution_time > 1000) { // Log as warning if > 1 second
            self::warning("Slow operation: {$operation}", $context);
        } else {
            self::debug("Performance: {$operation}", $context);
        }
    }
    
    /**
     * Log security events
     */
    public static function log_security($event, $details = array()) {
        $context = array_merge($details, array(
            'user_id' => get_current_user_id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => current_time('mysql')
        ));
        
        self::warning("Security Event: {$event}", $context);
    }
    
    /**
     * Clear log file
     */
    public static function clear_log() {
        $instance = self::get_instance();
        if (file_exists($instance->log_file)) {
            file_put_contents($instance->log_file, '');
        }
    }
    
    /**
     * Get log file path
     */
    public static function get_log_file() {
        return self::get_instance()->log_file;
    }
    
    /**
     * Check if logging is enabled
     */
    public static function is_enabled() {
        return self::get_instance()->enabled;
    }
}
