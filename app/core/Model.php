<?php
/**
 * Base Model Class
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../config/database.php';

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get All Records
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params = ['limit' => $limit, 'offset' => $offset];
        } else {
            $params = [];
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get Record by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Create New Record
     */
    public function create($data) {
        // Filter fillable fields
        $data = $this->filterFillable($data);
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, $data);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update Record
     */
    public function update($id, $data) {
        // Filter fillable fields
        $data = $this->filterFillable($data);
        
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "{$key} = :{$key}";
        }
        
        $setClause = implode(', ', $setClause);
        $data[$this->primaryKey] = $id;
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}";
        $this->db->query($sql, $data);
        
        return true;
    }
    
    /**
     * Delete Record
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $this->db->query($sql, ['id' => $id]);
        
        return true;
    }
    
    /**
     * Find Record by Column
     */
    public function findBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        $stmt = $this->db->query($sql, ['value' => $value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Find One Record by Column
     */
    public function findOneBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        $stmt = $this->db->query($sql, ['value' => $value]);
        return $stmt->fetch();
    }
    
    /**
     * Count Records
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Custom Query
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Custom Query Single
     */
    public function queryOne($sql, $params = []) {
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Begin Transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit Transaction
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback Transaction
     */
    public function rollback() {
        return $this->db->rollback();
    }
    
    /**
     * Filter Fillable Fields
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Hide Sensitive Fields
     */
    protected function hideFields($data) {
        if (empty($this->hidden)) {
            return $data;
        }
        
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }
    
    /**
     * Get POST Data
     */
    public function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    
    /**
     * Validate Fields
     */
    public function validate($data, $rules = []) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (strpos($rule, 'required') !== false && empty($data[$field])) {
                $errors[$field] = "Field {$field} is required";
            }
            
            if (strpos($rule, 'email') !== false && !empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Field {$field} must be a valid email";
            }
            
            if (strpos($rule, 'numeric') !== false && !empty($data[$field]) && !is_numeric($data[$field])) {
                $errors[$field] = "Field {$field} must be numeric";
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches) && !empty($data[$field]) && strlen($data[$field]) < $matches[1]) {
                $errors[$field] = "Field {$field} must be at least {$matches[1]} characters";
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches) && !empty($data[$field]) && strlen($data[$field]) > $matches[1]) {
                $errors[$field] = "Field {$field} must not exceed {$matches[1]} characters";
            }
        }
        
        return $errors;
    }
}
?>
