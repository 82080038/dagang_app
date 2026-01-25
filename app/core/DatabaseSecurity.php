<?php
/**
 * Database Security Class
 * 
 * Implements secure database operations following OWASP best practices
 */

class DatabaseSecurity {
    
    private static $instance = null;
    private $pdo;
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Secure database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Prevent SQL injection
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => false, // Prevent SQL injection
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log error but don't expose details
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Execute prepared statement securely
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Database operation failed");
        }
    }
    
    /**
     * Fetch single record
     */
    public function fetch($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch multiple records
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert record securely
     */
    public function insert($table, $data) {
        // Validate table name to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name");
        }
        
        // Build secure query
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $query = "INSERT INTO {$table} (" . implode(', ', $columns) . ") 
                  VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->execute($query, array_values($data));
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update record securely
     */
    public function update($table, $data, $where, $whereParams = []) {
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name");
        }
        
        // Build SET clause
        $setClause = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            // Validate column name
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                throw new Exception("Invalid column name");
            }
            $setClause[] = "{$column} = ?";
            $values[] = $value;
        }
        
        $query = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE {$where}";
        
        $stmt = $this->execute($query, array_merge($values, $whereParams));
        return $stmt->rowCount();
    }
    
    /**
     * Delete record securely
     */
    public function delete($table, $where, $params = []) {
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name");
        }
        
        $query = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->execute($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Escape identifier (table/column names)
     */
    public function escapeIdentifier($identifier) {
        if (!preg_match('/^[a-zA-Z0-9_*]+$/', $identifier)) {
            throw new Exception("Invalid identifier");
        }
        return $identifier;
    }
    
    /**
     * Validate and sanitize input for database
     */
    public function validateInput($input, $type = 'string') {
        if (is_null($input)) {
            return null;
        }
        
        switch ($type) {
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT);
                
            case 'float':
                return filter_var($input, FILTER_VALIDATE_FLOAT);
                
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL);
                
            case 'url':
                return filter_var($input, FILTER_VALIDATE_URL);
                
            case 'bool':
                return filter_var($input, FILTER_VALIDATE_BOOLEAN);
                
            case 'string':
            default:
                // Remove potential SQL injection patterns
                return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Check for SQL injection patterns
     */
    public function containsSQLInjection($input) {
        $patterns = [
            '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\s/i',
            '/(--|#|\/\*|\*\/)/',
            '/(\s|^)(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/(\s|^)(OR|AND)\s+["\']?\w+["\']?\s*=\s*["\']?\w+["\']?/i',
            '/;\s*(DROP|DELETE|UPDATE|INSERT)/i',
            '/\b(UNION|SELECT)\s+.*\s+FROM\s+/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log database operations for audit
     */
    public function logOperation($operation, $table, $data = []) {
        $logData = [
            'operation' => $operation,
            'table' => $table,
            'data' => json_encode($data),
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Insert into audit log table
        try {
            $this->insert('audit_logs', $logData);
        } catch (Exception $e) {
            error_log("Failed to log database operation: " . $e->getMessage());
        }
    }
    
    /**
     * Get PDO instance for complex operations
     */
    public function getPDO() {
        return $this->pdo;
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
        self::$instance = null;
    }
}
?>
