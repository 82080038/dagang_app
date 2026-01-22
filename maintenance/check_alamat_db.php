<?php
// Script to check alamat_db structure
require_once 'app/config/database_multi.php';

try {
    $db = getAddressDB();
    echo "Connected to alamat_db\n";
    
    $tables = ['provinces', 'regencies', 'districts', 'villages'];
    
    foreach ($tables as $table) {
        echo "\nChecking table: $table\n";
        $stmt = $db->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  {$col['Field']} - {$col['Type']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
