<?php
// Debug script to check company_type field in database
require_once 'app/config/config.php';
require_once 'app/models/Company.php';

echo "=== CHECK COMPANY TYPE FIELD ===\n\n";

$model = new Company();

// Check database structure
echo "1. Checking database structure...\n";
try {
    $result = $model->query("DESCRIBE companies");
    echo "Companies table structure:\n";
    foreach ($result as $field) {
        if (strpos($field['Field'], 'type') !== false) {
            echo "- " . $field['Field'] . ": " . $field['Type'] . " (Null: " . ($field['Null'] == 'YES' ? 'YES' : 'NO') . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n2. Checking existing company data...\n";
try {
    $companies = $model->getAll(10);
    if (empty($companies)) {
        echo "No companies found in database.\n";
    } else {
        foreach ($companies as $company) {
            echo "ID: " . $company['id_company'] . 
                 " | Name: " . $company['company_name'] . 
                 " | Type: " . ($company['company_type'] ?? 'NULL') . 
                 " | Level: " . ($company['scalability_level'] ?? 'NULL') . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing CompanyController get() method...\n";
try {
    if (!empty($companies)) {
        $firstId = $companies[0]['id_company'];
        echo "Testing with company ID: " . $firstId . "\n";
        
        $company = $model->getById($firstId);
        if ($company) {
            echo "Company data:\n";
            echo "- company_type: " . ($company['company_type'] ?? 'NULL') . "\n";
            echo "- scalability_level: " . ($company['scalability_level'] ?? 'NULL') . "\n";
            echo "- All fields: " . json_encode($company, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Company not found\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== END CHECK ===\n";
?>
