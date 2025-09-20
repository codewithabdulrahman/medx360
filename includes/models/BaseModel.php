<?php
/**
 * Base Model Class
 * Provides common functionality for all data models
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class Medx360_BaseModel {
    
    protected $table_name;
    protected $primary_key = 'id';
    protected $fillable = array();
    protected $hidden = array();
    protected $casts = array();
    protected $timestamps = true;
    
    protected $database;
    
    public function __construct() {
        $this->database = new Medx360_Database();
    }
    
    /**
     * Get all records
     */
    public function all($limit = null, $offset = null) {
        global $wpdb;
        
        $query = "SELECT * FROM {$this->table_name}";
        
        if ($this->timestamps) {
            $query .= " ORDER BY created_at DESC";
        }
        
        if ($limit) {
            $query .= " LIMIT %d";
            if ($offset) {
                $query .= " OFFSET %d";
                return $wpdb->get_results($wpdb->prepare($query, $limit, $offset));
            }
            return $wpdb->get_results($wpdb->prepare($query, $limit));
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        global $wpdb;
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE {$this->primary_key} = %d",
            $id
        ));
        
        return $result ? $this->cast_attributes($result) : null;
    }
    
    /**
     * Find record by field
     */
    public function findBy($field, $value) {
        global $wpdb;
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE {$field} = %s",
            $value
        ));
        
        return $result ? $this->cast_attributes($result) : null;
    }
    
    /**
     * Find multiple records by field
     */
    public function findAllBy($field, $value, $limit = null, $offset = null) {
        global $wpdb;
        
        $query = "SELECT * FROM {$this->table_name} WHERE {$field} = %s";
        
        if ($this->timestamps) {
            $query .= " ORDER BY created_at DESC";
        }
        
        if ($limit) {
            $query .= " LIMIT %d";
            if ($offset) {
                $query .= " OFFSET %d";
                return $wpdb->get_results($wpdb->prepare($query, $value, $limit, $offset));
            }
            return $wpdb->get_results($wpdb->prepare($query, $value, $limit));
        }
        
        return $wpdb->get_results($wpdb->prepare($query, $value));
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        global $wpdb;
        
        $data = $this->filter_fillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = current_time('mysql');
            $data['updated_at'] = current_time('mysql');
        }
        
        $result = $wpdb->insert($this->table_name, $data);
        
        if ($result === false) {
            return false;
        }
        
        $id = $wpdb->insert_id;
        return $this->find($id);
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        global $wpdb;
        
        $data = $this->filter_fillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = current_time('mysql');
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array($this->primary_key => $id)
        );
        
        if ($result === false) {
            return false;
        }
        
        return $this->find($id);
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array($this->primary_key => $id)
        );
    }
    
    /**
     * Count records
     */
    public function count($where = array()) {
        global $wpdb;
        
        $query = "SELECT COUNT(*) FROM {$this->table_name}";
        
        if (!empty($where)) {
            $conditions = array();
            $values = array();
            
            foreach ($where as $field => $value) {
                $conditions[] = "{$field} = %s";
                $values[] = $value;
            }
            
            $query .= " WHERE " . implode(' AND ', $conditions);
            return $wpdb->get_var($wpdb->prepare($query, $values));
        }
        
        return $wpdb->get_var($query);
    }
    
    /**
     * Search records
     */
    public function search($search_term, $fields = array(), $limit = null, $offset = null) {
        global $wpdb;
        
        if (empty($fields)) {
            $fields = $this->fillable;
        }
        
        $conditions = array();
        $values = array();
        
        foreach ($fields as $field) {
            $conditions[] = "{$field} LIKE %s";
            $values[] = '%' . $wpdb->esc_like($search_term) . '%';
        }
        
        $query = "SELECT * FROM {$this->table_name} WHERE " . implode(' OR ', $conditions);
        
        if ($this->timestamps) {
            $query .= " ORDER BY created_at DESC";
        }
        
        if ($limit) {
            $query .= " LIMIT %d";
            if ($offset) {
                $query .= " OFFSET %d";
                $values[] = $limit;
                $values[] = $offset;
            } else {
                $values[] = $limit;
            }
        }
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }
    
    /**
     * Paginate records
     */
    public function paginate($page = 1, $per_page = 20, $where = array()) {
        global $wpdb;
        
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT * FROM {$this->table_name}";
        $count_query = "SELECT COUNT(*) FROM {$this->table_name}";
        
        $where_clause = '';
        $values = array();
        
        if (!empty($where)) {
            $conditions = array();
            foreach ($where as $field => $value) {
                $conditions[] = "{$field} = %s";
                $values[] = $value;
            }
            $where_clause = " WHERE " . implode(' AND ', $conditions);
        }
        
        $query .= $where_clause;
        $count_query .= $where_clause;
        
        if ($this->timestamps) {
            $query .= " ORDER BY created_at DESC";
        }
        
        $query .= " LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset;
        
        $data = $wpdb->get_results($wpdb->prepare($query, $values));
        $total = $wpdb->get_var($wpdb->prepare($count_query, array_slice($values, 0, -2)));
        
        return array(
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        );
    }
    
    /**
     * Filter fillable fields
     */
    protected function filter_fillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Cast attributes
     */
    protected function cast_attributes($data) {
        if (empty($this->casts)) {
            return $data;
        }
        
        foreach ($this->casts as $field => $type) {
            if (isset($data->$field)) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $data->$field = (int) $data->$field;
                        break;
                    case 'float':
                    case 'double':
                        $data->$field = (float) $data->$field;
                        break;
                    case 'bool':
                    case 'boolean':
                        $data->$field = (bool) $data->$field;
                        break;
                    case 'array':
                    case 'json':
                        $data->$field = json_decode($data->$field, true);
                        break;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Hide sensitive fields
     */
    protected function hide_fields($data) {
        if (empty($this->hidden)) {
            return $data;
        }
        
        foreach ($this->hidden as $field) {
            unset($data->$field);
        }
        
        return $data;
    }
    
    /**
     * Validate data
     */
    protected function validate($data, $rules = array()) {
        $errors = array();
        
        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "Field '{$field}' is required";
                continue;
            }
            
            if (strpos($rule, 'email') !== false && !empty($value) && !is_email($value)) {
                $errors[$field] = "Field '{$field}' must be a valid email";
            }
            
            if (strpos($rule, 'numeric') !== false && !empty($value) && !is_numeric($value)) {
                $errors[$field] = "Field '{$field}' must be numeric";
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches) && !empty($value) && strlen($value) < $matches[1]) {
                $errors[$field] = "Field '{$field}' must be at least {$matches[1]} characters";
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches) && !empty($value) && strlen($value) > $matches[1]) {
                $errors[$field] = "Field '{$field}' must not exceed {$matches[1]} characters";
            }
        }
        
        return $errors;
    }
    
    /**
     * Get table name
     */
    public function get_table_name() {
        return $this->table_name;
    }
    
    /**
     * Get primary key
     */
    public function get_primary_key() {
        return $this->primary_key;
    }
    
    /**
     * Get fillable fields
     */
    public function get_fillable() {
        return $this->fillable;
    }
    
    /**
     * Get hidden fields
     */
    public function get_hidden() {
        return $this->hidden;
    }
    
    /**
     * Get casts
     */
    public function get_casts() {
        return $this->casts;
    }
}
