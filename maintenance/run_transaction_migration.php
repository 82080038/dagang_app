<?php
require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/../database_migrations/create_transaction_tables.sql');
    
    // Split by semicolon to run multiple queries
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $db->query($query);
        }
    }
    
    echo "Transaction tables migration completed.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
