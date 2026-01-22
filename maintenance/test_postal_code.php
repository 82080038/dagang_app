<?php
// Test script to verify postal code functionality
require_once 'app/config/config.php';
require_once 'app/models/Address.php';

echo "=== TESTING POSTAL CODE FUNCTIONALITY ===\n\n";

$addressModel = new Address();

// Test 1: Check if villages table has postal_code field
echo "1. Checking villages table structure...\n";
try {
    $sql = "DESCRIBE alamat_db.villages";
    $result = $addressModel->query($sql);
    echo "alamat_db.villages structure:\n";
    foreach ($result as $field) {
        if (strpos($field['Field'], 'postal') !== false || $field['Field'] == 'id' || $field['Field'] == 'name') {
            echo "- " . $field['Field'] . ": " . $field['Type'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error checking alamat_db.villages: " . $e->getMessage() . "\n";
    
    // Try local villages table
    try {
        $sql = "DESCRIBE villages";
        $result = $addressModel->query($sql);
        echo "local villages structure:\n";
        foreach ($result as $field) {
            if (strpos($field['Field'], 'postal') !== false || $field['Field'] == 'id_village' || $field['Field'] == 'name') {
                echo "- " . $field['Field'] . ": " . $field['Type'] . "\n";
            }
        }
    } catch (Exception $e2) {
        echo "Error checking local villages: " . $e2->getMessage() . "\n";
    }
}

echo "\n2. Testing getVillages with postal code...\n";
try {
    $villages = $addressModel->getVillages(1959); // Jakarta Pusat district
    echo "Found " . count($villages) . " villages in district 1959:\n";
    foreach (array_slice($villages, 0, 5) as $village) {
        echo "- ID: " . $village['id'] . " | Name: " . $village['name'] . " | Postal: " . ($village['postal_code'] ?? 'NULL') . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing getPostalCode function...\n";
try {
    $postalCode = $addressModel->getPostalCode(25526); // Example village ID
    echo "Postal code for village 25526: " . ($postalCode ?? 'NULL') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== COMPLETED ===\n";
?>
