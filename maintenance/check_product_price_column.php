<?php
require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("DESCRIBE products");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('selling_price', $columns)) {
        echo "Column 'selling_price' exists.\n";
    } else {
        echo "Column 'selling_price' MISSING. Adding it...\n";
        $db->query("ALTER TABLE products ADD COLUMN selling_price DECIMAL(15, 2) NOT NULL DEFAULT 0 AFTER unit");
        echo "Column added.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
