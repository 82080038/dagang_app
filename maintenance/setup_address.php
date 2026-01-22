<?php
/**
 * Setup Address Tables Migration
 * Create provinces, regencies, districts, and villages tables with sample data
 */

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/models/Address.php';

header('Content-Type: text/plain');

echo "=== Address Tables Setup ===\n\n";

try {
    $db = Database::getInstance();
    $address = new Address();
    
    echo "1. Creating address tables...\n";
    $address->createLocalAddressTables();
    echo "   ✓ Address tables created successfully.\n";
    
    echo "2. Inserting sample data...\n";
    $address->insertSampleData();
    echo "   ✓ Sample data inserted successfully.\n";
    
    echo "\n=== Setup completed successfully! ===\n";
    echo "You can now use the address dropdowns in the company form.\n";
    
} catch (Exception $e) {
    echo "\n=== Setup failed! ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
