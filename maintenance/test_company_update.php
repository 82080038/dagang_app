<?php
// Test company update functionality
require_once 'app/config/config.php';
require_once 'app/models/Company.php';

echo "=== TESTING COMPANY UPDATE FUNCTIONALITY ===\n\n";

$companyModel = new Company();

// Test 1: Check if update method exists in model
echo "1. Checking updateCompany method...\n";
if (method_exists($companyModel, 'updateCompany')) {
    echo "✅ updateCompany method exists in Company model\n";
} else {
    echo "❌ updateCompany method missing in Company model\n";
}

// Test 2: Check existing companies
echo "\n2. Checking existing companies...\n";
try {
    $companies = $companyModel->getAll(5);
    if (!empty($companies)) {
        echo "Found " . count($companies) . " companies:\n";
        foreach ($companies as $company) {
            echo "- ID: " . $company['id_company'] . 
                 " | Name: " . $company['company_name'] . 
                 " | Code: " . $company['company_code'] . "\n";
        }
    } else {
        echo "No companies found in database.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 3: Check database structure for company_code uniqueness
echo "\n3. Checking company_code constraint...\n";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    $result = $pdo->query("SHOW INDEX FROM companies WHERE Key_name = 'company_code'");
    if ($result->rowCount() > 0) {
        echo "✅ company_code index exists (UNIQUE constraint)\n";
    } else {
        echo "⚠️  company_code index not found\n";
    }
} catch (Exception $e) {
    echo "Error checking index: " . $e->getMessage() . "\n";
}

echo "\n=== COMPLETED ===\n";
?>
