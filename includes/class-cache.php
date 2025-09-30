<?php
/**
 * Caching class for MedX360
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Cache {
    
    private static $instance = null;
    private $cache_group = 'medx360';
    private $default_expiration = 3600; // 1 hour
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor
    }
    
    /**
     * Get cached data
     */
    public static function get($key, $group = null) {
        $group = $group ?: self::get_instance()->cache_group;
        return wp_cache_get($key, $group);
    }
    
    /**
     * Set cached data
     */
    public static function set($key, $data, $expiration = null, $group = null) {
        $group = $group ?: self::get_instance()->cache_group;
        $expiration = $expiration ?: self::get_instance()->default_expiration;
        
        return wp_cache_set($key, $data, $group, $expiration);
    }
    
    /**
     * Delete cached data
     */
    public static function delete($key, $group = null) {
        $group = $group ?: self::get_instance()->cache_group;
        return wp_cache_delete($key, $group);
    }
    
    /**
     * Clear all cache for a group
     */
    public static function flush_group($group = null) {
        $group = $group ?: self::get_instance()->cache_group;
        return wp_cache_flush_group($group);
    }
    
    /**
     * Get or set cached data
     */
    public static function remember($key, $callback, $expiration = null, $group = null) {
        $cached = self::get($key, $group);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $data = $callback();
        self::set($key, $data, $expiration, $group);
        
        return $data;
    }
    
    /**
     * Cache database query results
     */
    public static function cache_query($query, $callback, $expiration = null) {
        $cache_key = 'query_' . md5($query);
        return self::remember($cache_key, $callback, $expiration, 'medx360_queries');
    }
    
    /**
     * Cache API responses
     */
    public static function cache_api_response($endpoint, $params, $callback, $expiration = null) {
        $cache_key = 'api_' . md5($endpoint . serialize($params));
        return self::remember($cache_key, $callback, $expiration, 'medx360_api');
    }
    
    /**
     * Cache user-specific data
     */
    public static function cache_user_data($key, $callback, $expiration = null) {
        $user_id = get_current_user_id();
        $cache_key = "user_{$user_id}_{$key}";
        return self::remember($cache_key, $callback, $expiration, 'medx360_user');
    }
    
    /**
     * Invalidate cache for specific entity
     */
    public static function invalidate_entity($entity_type, $entity_id) {
        $keys = array(
            "entity_{$entity_type}_{$entity_id}",
            "list_{$entity_type}",
            "count_{$entity_type}"
        );
        
        foreach ($keys as $key) {
            self::delete($key);
        }
        
        // Clear related caches
        self::flush_group('medx360_queries');
    }
    
    /**
     * Cache statistics
     */
    public static function cache_stats($callback, $expiration = 1800) { // 30 minutes
        return self::remember('stats', $callback, $expiration, 'medx360_stats');
    }
    
    /**
     * Cache clinic data
     */
    public static function cache_clinic($clinic_id, $callback, $expiration = null) {
        $cache_key = "clinic_{$clinic_id}";
        return self::remember($cache_key, $callback, $expiration, 'medx360_clinics');
    }
    
    /**
     * Cache doctor data
     */
    public static function cache_doctor($doctor_id, $callback, $expiration = null) {
        $cache_key = "doctor_{$doctor_id}";
        return self::remember($cache_key, $callback, $expiration, 'medx360_doctors');
    }
    
    /**
     * Cache hospital data
     */
    public static function cache_hospital($hospital_id, $callback, $expiration = null) {
        $cache_key = "hospital_{$hospital_id}";
        return self::remember($cache_key, $callback, $expiration, 'medx360_hospitals');
    }
    
    /**
     * Cache booking data
     */
    public static function cache_booking($booking_id, $callback, $expiration = null) {
        $cache_key = "booking_{$booking_id}";
        return self::remember($cache_key, $callback, $expiration, 'medx360_bookings');
    }
    
    /**
     * Warm up cache
     */
    public static function warm_up() {
        // Cache frequently accessed data
        self::cache_stats(function() {
            global $wpdb;
            
            $stats = array();
            $tables = array('clinics', 'hospitals', 'doctors', 'services', 'staff', 'bookings', 'consultations', 'payments');
            
            foreach ($tables as $table) {
                $table_name = $wpdb->prefix . 'medx360_' . $table;
                $stats[$table . '_count'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            }
            
            return $stats;
        });
    }
    
    /**
     * Get cache statistics
     */
    public static function get_cache_stats() {
        return array(
            'enabled' => wp_using_ext_object_cache(),
            'groups' => array(
                'medx360',
                'medx360_queries',
                'medx360_api',
                'medx360_user',
                'medx360_stats',
                'medx360_clinics',
                'medx360_doctors',
                'medx360_hospitals',
                'medx360_bookings'
            )
        );
    }
}
