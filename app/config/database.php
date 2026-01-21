<?php
/**
 * Database Configuration
 * Native PHP Database Handler
 */

class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'perdagangan_system';
    private $charset = 'utf8mb4';
    
    public $conn;
    
    public function __construct() {
        $this->connect();
    }
    
    /**
     * Database Connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Prepare and Execute Query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get Last Insert ID
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Begin Transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit Transaction
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Rollback Transaction
     */
    public function rollback() {
        return $this->conn->rollback();
    }
    
    /**
     * Close Connection
     */
    public function close() {
        $this->conn = null;
    }
}
?>
