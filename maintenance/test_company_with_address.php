<?php
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Company.php';

$companyModel = new Company();

echo "Testing company with address_id = 10\n";
$company = $companyModel->getCompanyWithAddress(8);

if ($company) {
    echo "✅ Company found: " . $company['company_name'] . "\n";
    echo "📍 Address Detail: " . ($company['address_detail'] ?? 'NULL') . "\n";
    echo "📍 Province: " . ($company['province_name'] ?? 'NULL') . "\n";
    echo "📍 Regency: " . ($company['regency_name'] ?? 'NULL') . "\n";
    echo "📍 District: " . ($company['district_name'] ?? 'NULL') . "\n";
    echo "📍 Village: " . ($company['village_name'] ?? 'NULL') . "\n";
    echo "📍 Postal Code: " . ($company['postal_code'] ?? 'NULL') . "\n";
} else {
    echo "❌ Company not found\n";
}

echo "\nJSON Response:\n";
echo json_encode(['status' => 'success', 'data' => ['company' => $company]], JSON_UNESCAPED_UNICODE);
?>
