<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::getInstance();
    
    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'branch_inventory'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'branch_inventory' exists.\n";
        $stmt = $db->query("DESCRIBE branch_inventory");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "Table 'branch_inventory' does NOT exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
