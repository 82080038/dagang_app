<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config/database_multi.php';

try {
    if (!isset($main_db)) die("Error: \$main_db not defined.\n");
    $db = $main_db;
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to " . DB_NAME_MAIN . "\n";
    
    $sqlFile = 'database_migrations/create_missing_tables.sql';
    if (!file_exists($sqlFile)) die("SQL file not found.\n");
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon (ignoring comments best effort)
    // Simple split works for this file structure
    $statements = explode(';', $sql);
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) continue;
        
        // Skip comments only lines
        if (strpos($stmt, '--') === 0 && strpos($stmt, "\n") === false) continue;
        
        try {
            echo "Executing SQL statement...\n";
            $db->exec($stmt);
            echo "Success.\n";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            // Continue with next statement
        }
    }
    
    // Special handling for products category_id FK
    echo "\nChecking products table for category_id...\n";
    $stmt = $db->query("DESCRIBE products");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('category_id', $cols)) {
        echo "Adding category_id to products...\n";
        $db->exec("ALTER TABLE products ADD COLUMN category_id INT NULL AFTER company_id");
    }
    
    // Add FK if not exists
    echo "Adding FK products_category...\n";
    try {
        $db->exec("ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id_category) ON DELETE SET NULL");
        echo "Success.\n";
    } catch (PDOException $e) {
        echo "FK might already exist: " . $e->getMessage() . "\n";
    }

    echo "\nMigration completed.\n";

} catch (Exception $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
}
