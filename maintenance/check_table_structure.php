<?php
// Check suppliers table structure
$pdo = new PDO("mysql:host=localhost;dbname=perdagangan_system", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("DESCRIBE suppliers");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Current columns in suppliers table:\n";
foreach ($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

// Check what's missing
$requiredColumns = [
    'supplier_code',
    'supplier_name', 
    'supplier_type',
    'business_category',
    'tax_id',
    'tax_name',
    'is_tax_registered',
    'contact_person',
    'phone',
    'mobile',
    'email',
    'website',
    'address_detail',
    'province_id',
    'regency_id',
    'district_id',
    'village_id',
    'postal_code',
    'business_license',
    'business_registration',
    'establishment_date',
    'capital_amount',
    'bank_name',
    'bank_account_number',
    'bank_account_name',
    'bank_branch',
    'supplier_category',
    'supplier_level',
    'total_orders',
    'total_amount',
    'average_delivery_time',
    'on_time_delivery_rate',
    'quality_score',
    'overall_score',
    'payment_terms',
    'credit_limit',
    'current_balance',
    'is_active',
    'is_blacklisted',
    'blacklist_reason',
    'notes',
    'created_by',
    'created_at',
    'updated_at'
];

echo "\nMissing columns:\n";
$existingColumns = array_column($columns, 'Field');
foreach ($requiredColumns as $required) {
    if (!in_array($required, $existingColumns)) {
        echo "- $required\n";
    }
}

?>
