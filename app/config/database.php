<?php
/**
 * Database Configuration - Cross-Platform Compatible
 * 
 * This configuration works on both Windows (development) and Linux (deployment)
 * while maintaining Linux compatibility standards.
 */

// Include cross-platform compatibility
require_once __DIR__ . '/cross_platform.php';

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perdagangan_system');
define('DB_CHARSET', 'utf8mb4');

// Cross-platform database connection
class Database {
    private static $instance = null;
    private $connection = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            
            // Log successful connection
            logMessage('INFO', 'Database connection established');
            
        } catch (PDOException $e) {
            logMessage('ERROR', 'Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            logMessage('ERROR', 'Query failed: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw new Exception('Query failed: ' . $e->getMessage());
        }
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function close() {
        $this->connection = null;
    }
}

// Helper functions for database operations
function dbQuery($sql, $params = []) {
    return Database::getInstance()->query($sql, $params);
}

function dbFetch($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->fetch();
}

function dbFetchAll($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->fetchAll();
}

function dbInsert($table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    
    try {
        $stmt = Database::getInstance()->query($sql, $data);
        return Database::getInstance()->lastInsertId();
    } catch (Exception $e) {
        logMessage('ERROR', 'Insert failed: ' . $e->getMessage() . ' Table: ' . $table);
        throw $e;
    }
}

function dbUpdate($table, $data, $where, $whereParams = []) {
    $setClause = [];
    foreach ($data as $key => $value) {
        $setClause[] = "$key = :$key";
    }
    $setClause = implode(', ', $setClause);
    
    $sql = "UPDATE $table SET $setClause WHERE $where";
    
    try {
        $params = array_merge($data, $whereParams);
        $stmt = Database::getInstance()->query($sql, $params);
        return $stmt->rowCount();
    } catch (Exception $e) {
        logMessage('ERROR', 'Update failed: ' . $e->getMessage() . ' Table: ' . $table);
        throw $e;
    }
}

function dbDelete($table, $where, $params = []) {
    $sql = "DELETE FROM $table WHERE $where";
    
    try {
        $stmt = Database::getInstance()->query($sql, $params);
        return $stmt->rowCount();
    } catch (Exception $e) {
        logMessage('ERROR', 'Delete failed: ' . $e->getMessage() . ' Table: ' . $table);
        throw $e;
    }
}

function dbCount($table, $where = '1=1', $params = []) {
    $sql = "SELECT COUNT(*) as count FROM $table WHERE $where";
    
    try {
        $result = dbFetch($sql, $params);
        return $result['count'];
    } catch (Exception $e) {
        logMessage('ERROR', 'Count failed: ' . $e->getMessage() . ' Table: ' . $table);
        return 0;
    }
}

function dbExists($table) {
    $sql = "SHOW TABLES LIKE '$table'";
    $result = dbFetchAll($sql);
    return count($result) > 0;
}

// Initialize database connection
try {
    Database::getInstance();
    logMessage('INFO', 'Database configuration loaded successfully');
} catch (Exception $e) {
    logMessage('ERROR', 'Database initialization failed: ' . $e->getMessage());
    // In production, you might want to handle this more gracefully
    if (!IS_WINDOWS) {
        // On Linux, we can be more strict about database errors
        throw $e;
    }
}

?>
