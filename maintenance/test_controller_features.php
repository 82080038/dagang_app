<?php
/**
 * Test Controller Feature Requirements
 * Verify that controllers properly check feature requirements
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/FeatureSettings.php';

echo "=== CONTROLLER FEATURE REQUIREMENTS TEST ===\n\n";

// Initialize feature settings
$featureSettings = new FeatureSettings();

// Test 1: Check Controller Feature Requirements
echo "1. Controller Feature Requirements Check:\n";

$controllerFeatures = [
    'ProductController' => 'products',
    'TransactionController' => 'transactions', 
    'ProductTransferController' => 'product_transfers',
    'ReportsController' => 'reports'
];

foreach ($controllerFeatures as $controller => $feature) {
    $isEnabled = $featureSettings->isFeatureEnabled(1, $feature);
    $status = $isEnabled ? 'ALLOWED' : 'BLOCKED';
    echo "   - {$controller}: {$status} (requires '{$feature}')\n";
}

// Test 2: Simulate Feature Blocking
echo "\n\n2. Simulating Feature Blocking:\n";

// Disable products feature
echo "   - Disabling 'products' feature...\n";
$featureSettings->updateCompanyFeature(1, 'feature_products', ['enabled' => false, 'settings' => []]);

// Check ProductController access
$productsEnabled = $featureSettings->isFeatureEnabled(1, 'products');
echo "   - ProductController access: " . ($productsEnabled ? 'ALLOWED' : 'BLOCKED') . "\n";

// Re-enable products feature
echo "   - Re-enabling 'products' feature...\n";
$featureSettings->updateCompanyFeature(1, 'feature_products', ['enabled' => true, 'settings' => []]);

// Check ProductController access again
$productsEnabled = $featureSettings->isFeatureEnabled(1, 'products');
echo "   - ProductController access: " . ($productsEnabled ? 'ALLOWED' : 'BLOCKED') . "\n";

// Test 3: Test Multiple Feature Scenarios
echo "\n\n3. Multiple Feature Scenarios:\n";

$scenarios = [
    'Basic Operations' => ['products', 'transactions'],
    'Full Operations' => ['products', 'transactions', 'suppliers', 'customers'],
    'With Reports' => ['products', 'transactions', 'reports'],
    'With Transfers' => ['products', 'transactions', 'product_transfers'],
    'Accounting Mode' => ['chart_of_accounts', 'journal_entries', 'financial_reports'],
    'Full System' => ['products', 'transactions', 'suppliers', 'customers', 'reports', 'product_transfers', 'chart_of_accounts', 'journal_entries']
];

foreach ($scenarios as $scenarioName => $features) {
    echo "\n   Scenario: {$scenarioName}\n";
    
    // Disable all features first
    $allFeatures = ['products', 'transactions', 'suppliers', 'customers', 'reports', 'product_transfers', 'chart_of_accounts', 'journal_entries'];
    foreach ($allFeatures as $feature) {
        $featureSettings->updateCompanyFeature(1, 'feature_' . $feature, ['enabled' => false, 'settings' => []]);
    }
    
    // Enable scenario features
    foreach ($features as $feature) {
        $featureSettings->updateCompanyFeature(1, 'feature_' . $feature, ['enabled' => true, 'settings' => []]);
    }
    
    // Check controller access
    foreach ($controllerFeatures as $controller => $feature) {
        $isEnabled = $featureSettings->isFeatureEnabled(1, $feature);
        $status = $isEnabled ? '✅' : '❌';
        echo "     {$status} {$controller}\n";
    }
}

// Test 4: Reset to Default State
echo "\n\n4. Resetting to Default State:\n";

$defaultFeatures = [
    'products' => true,
    'transactions' => true,
    'suppliers' => true,
    'customers' => true,
    'reports' => false,
    'product_transfers' => false,
    'chart_of_accounts' => false,
    'journal_entries' => false
];

foreach ($defaultFeatures as $feature => $enabled) {
    $featureSettings->updateCompanyFeature(1, 'feature_' . $feature, ['enabled' => $enabled, 'settings' => []]);
}

echo "   - Features reset to default values\n";

// Verify final state
echo "\n   Final Controller Access Status:\n";
foreach ($controllerFeatures as $controller => $feature) {
    $isEnabled = $featureSettings->isFeatureEnabled(1, $feature);
    $status = $isEnabled ? 'ALLOWED' : 'BLOCKED';
    echo "   - {$controller}: {$status}\n";
}

// Test 5: Test Error Handling
echo "\n\n5. Error Handling Test:\n";

// Test with invalid company ID
try {
    $invalidResult = $featureSettings->isFeatureEnabled(999, 'products');
    echo "   - Invalid company ID: " . ($invalidResult ? 'ENABLED' : 'DISABLED') . " (should be DISABLED)\n";
} catch (Exception $e) {
    echo "   - Invalid company ID: Exception caught - " . $e->getMessage() . "\n";
}

// Test with invalid feature key
try {
    $invalidResult = $featureSettings->isFeatureEnabled(1, 'invalid_feature');
    echo "   - Invalid feature key: " . ($invalidResult ? 'ENABLED' : 'DISABLED') . " (should be DISABLED)\n";
} catch (Exception $e) {
    echo "   - Invalid feature key: Exception caught - " . $e->getMessage() . "\n";
}

echo "\n=== CONTROLLER FEATURE REQUIREMENTS TEST COMPLETED ===\n";
echo "\nExpected behavior in application:\n";
echo "1. Controllers should block access when features are disabled\n";
echo "2. Users should see friendly error messages when accessing disabled features\n";
echo "3. Navigation should hide menu items for disabled features\n";
echo "4. AJAX endpoints should return proper error responses for disabled features\n";
echo "5. System should gracefully handle invalid company/feature combinations\n";
?>
