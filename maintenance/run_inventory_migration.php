<?php
require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/../database_migrations/create_inventory_tables.sql');
    
    // Split by semicolon to run multiple queries, but handle basic SQL splitting
    // Since the file is simple, we can try running it directly if the driver supports multiple queries,
    // or split it. PDO usually allows multiple queries if configured, but let's split to be safe.
    
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $db->query($query);
            echo "Executed query successfully.\n";
        }
    }
    
    echo "Inventory tables migration completed.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
