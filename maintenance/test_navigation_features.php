<?php
/**
 * Test Navigation Feature Toggle
 * Verify that navigation menu shows/hides based on feature settings
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/FeatureSettings.php';

echo "=== NAVIGATION FEATURE TOGGLE TEST ===\n\n";

// Initialize feature settings
$featureSettings = new FeatureSettings();

// Test 1: Check Current Feature Status
echo "1. Current Feature Status for Company 1:\n";
$enabledFeatures = $featureSettings->getEnabledFeatures(1);

foreach ($enabledFeatures as $categoryKey => $category) {
    echo "\n{$category['name']}:\n";
    foreach ($category['features'] as $featureKey => $feature) {
        $status = $feature['enabled'] ? 'ENABLED' : 'DISABLED';
        echo "   - {$feature['name']}: {$status}\n";
    }
}

// Test 2: Simulate Navigation Menu Items
echo "\n\n2. Navigation Menu Simulation:\n";
$navigationItems = [
    'products' => ['name' => 'Produk', 'icon' => 'fa-box'],
    'transactions' => ['name' => 'Transaksi', 'icon' => 'fa-exchange-alt'],
    'suppliers' => ['name' => 'Supplier', 'icon' => 'fa-truck'],
    'customers' => ['name' => 'Pelanggan', 'icon' => 'fa-users'],
    'reports' => ['name' => 'Laporan', 'icon' => 'fa-chart-bar'],
    'product_transfers' => ['name' => 'Transfer Produk', 'icon' => 'fa-exchange-alt'],
    'chart_of_accounts' => ['name' => 'Chart of Accounts', 'icon' => 'fa-calculator'],
    'journal_entries' => ['name' => 'Jurnal Umum', 'icon' => 'fa-book'],
    'financial_reports' => ['name' => 'Laporan Keuangan', 'icon' => 'fa-chart-line'],
    'cash_management' => ['name' => 'Manajemen Kas', 'icon' => 'fa-money-bill']
];

echo "Menu items that should be visible:\n";
foreach ($navigationItems as $featureKey => $item) {
    $isEnabled = $featureSettings->isFeatureEnabled(1, $featureKey);
    if ($isEnabled) {
        echo "   ✅ {$item['name']} ({$item['icon']})\n";
    }
}

echo "\nMenu items that should be hidden:\n";
foreach ($navigationItems as $featureKey => $item) {
    $isEnabled = $featureSettings->isFeatureEnabled(1, $featureKey);
    if (!$isEnabled) {
        echo "   ❌ {$item['name']} ({$item['icon']})\n";
    }
}

// Test 3: Test Feature Toggle
echo "\n\n3. Testing Feature Toggle:\n";

// Disable products feature
echo "   - Disabling 'products' feature...\n";
$featureSettings->updateCompanyFeature(1, 'feature_products', ['enabled' => false, 'settings' => []]);

// Check if products is now disabled
$productsEnabled = $featureSettings->isFeatureEnabled(1, 'products');
echo "   - Products feature status: " . ($productsEnabled ? 'ENABLED' : 'DISABLED') . "\n";

// Re-enable products feature
echo "   - Re-enabling 'products' feature...\n";
$featureSettings->updateCompanyFeature(1, 'feature_products', ['enabled' => true, 'settings' => []]);

// Check if products is now enabled
$productsEnabled = $featureSettings->isFeatureEnabled(1, 'products');
echo "   - Products feature status: " . ($productsEnabled ? 'ENABLED' : 'DISABLED') . "\n";

// Test 4: Test Accounting Menu Group
echo "\n\n4. Accounting Menu Group Test:\n";
$accountingFeatures = ['chart_of_accounts', 'journal_entries', 'financial_reports', 'cash_management'];
$accountingEnabled = [];

foreach ($accountingFeatures as $featureKey) {
    $isEnabled = $featureSettings->isFeatureEnabled(1, $featureKey);
    $accountingEnabled[$featureKey] = $isEnabled;
}

$anyAccountingEnabled = array_sum($accountingEnabled) > 0;
echo "   - Accounting menu should be shown: " . ($anyAccountingEnabled ? 'YES' : 'NO') . "\n";

if ($anyAccountingEnabled) {
    echo "   - Enabled accounting features:\n";
    foreach ($accountingEnabled as $featureKey => $enabled) {
        if ($enabled) {
            echo "     * {$featureKey}\n";
        }
    }
}

// Test 5: Test Feature Settings for Different Companies
echo "\n\n5. Testing Different Companies:\n";
$companies = [1, 2, 3];

foreach ($companies as $companyId) {
    $enabledCount = 0;
    $totalFeatures = 0;
    
    $companyFeatures = $featureSettings->getEnabledFeatures($companyId);
    foreach ($companyFeatures as $category) {
        foreach ($category['features'] as $feature) {
            $totalFeatures++;
            if ($feature['enabled']) {
                $enabledCount++;
            }
        }
    }
    
    echo "   - Company {$companyId}: {$enabledCount}/{$totalFeatures} features enabled\n";
}

echo "\n=== NAVIGATION FEATURE TOGGLE TEST COMPLETED ===\n";
echo "\nExpected behavior in browser:\n";
echo "1. Menu items should appear/disappear based on feature settings\n";
echo "2. Accounting dropdown should only show if any accounting feature is enabled\n";
echo "3. Feature changes should be reflected immediately in navigation\n";
echo "4. Different companies should have different menu configurations\n";
?>
