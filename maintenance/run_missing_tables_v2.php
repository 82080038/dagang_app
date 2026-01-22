<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting migration...\n";

// Direct DB Connection to avoid include issues
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME_MAIN', 'perdagangan_system');

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_MAIN, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to " . DB_NAME_MAIN . "\n";
    
    $sqlFile = __DIR__ . '/../database_migrations/create_missing_tables.sql';
    echo "Reading SQL file: $sqlFile\n";
    
    if (!file_exists($sqlFile)) {
        die("SQL file not found!\n");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "SQL Length: " . strlen($sql) . "\n";
    
    // Split by semicolon
    $statements = explode(';', $sql);
    echo "Found " . count($statements) . " statements.\n";
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) continue;
        
        try {
            // Echo first 50 chars
            echo "Executing: " . substr($stmt, 0, 50) . "...\n";
            $db->exec($stmt);
            echo "Success.\n";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
    
    // Check products category_id
    echo "\nChecking products table...\n";
    try {
        $stmt = $db->query("DESCRIBE products");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('category_id', $cols)) {
            echo "Adding category_id to products...\n";
            $db->exec("ALTER TABLE products ADD COLUMN category_id INT NULL AFTER company_id");
        } else {
            echo "Column category_id already exists.\n";
        }
        
        // Add FK
        echo "Adding FK products_category...\n";
        $db->exec("ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id_category) ON DELETE SET NULL");
        echo "Success.\n";
    } catch (PDOException $e) {
        echo "Note: " . $e->getMessage() . "\n";
    }

    echo "\nMigration completed.\n";

} catch (Exception $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
}
