<?php
// Script to check database structure
require_once 'app/config/database.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    echo "Connected to " . DB_NAME . "\n";
    
    $tables = ['addresses', 'companies', 'branches'];
    
    foreach ($tables as $table) {
        echo "\nChecking table: $table\n";
        try {
            $stmt = $db->query("SHOW CREATE TABLE $table");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo $row['Create Table'] . "\n";
        } catch (Exception $e) {
            echo "Error checking table $table: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
