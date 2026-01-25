<?php
/**
 * Test Feature Settings Integration
 * Verify database connection and feature toggle functionality
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/FeatureSettings.php';

echo "=== FEATURE SETTINGS INTEGRATION TEST ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    $db = Database::getInstance();
    echo "✅ Database connection successful\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: FeatureSettings Model
echo "2. Testing FeatureSettings Model...\n";
try {
    $featureSettings = new FeatureSettings();
    echo "✅ FeatureSettings model instantiated\n\n";
} catch (Exception $e) {
    echo "❌ FeatureSettings model failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Get All Features
echo "3. Testing getAllFeatures()...\n";
try {
    $allFeatures = $featureSettings->getAllFeatures();
    echo "✅ getAllFeatures() successful\n";
    echo "   - Categories: " . count($allFeatures) . "\n";
    
    foreach ($allFeatures as $categoryKey => $category) {
        echo "   - {$category['name']}: " . count($category['features']) . " features\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ getAllFeatures() failed: " . $e->getMessage() . "\n\n";
}

// Test 4: Test Company Features (Company ID 1)
echo "4. Testing Company Features (Company ID: 1)...\n";
try {
    $companyId = 1;
    $companyFeatures = $featureSettings->getCompanyFeatures($companyId);
    echo "✅ getCompanyFeatures() successful\n";
    echo "   - Company ID: {$companyId}\n";
    echo "   - Settings found: " . count($companyFeatures) . "\n\n";
} catch (Exception $e) {
    echo "❌ getCompanyFeatures() failed: " . $e->getMessage() . "\n\n";
}

// Test 5: Test Default Feature Status
echo "5. Testing Default Feature Status...\n";
try {
    $testFeatures = ['products', 'transactions', 'reports', 'chart_of_accounts'];
    
    foreach ($testFeatures as $featureKey) {
        $isEnabled = $featureSettings->isFeatureEnabled(1, $featureKey);
        $status = $isEnabled ? 'ENABLED' : 'DISABLED';
        echo "   - {$featureKey}: {$status}\n";
    }
    echo "✅ Default feature status check completed\n\n";
} catch (Exception $e) {
    echo "❌ Default feature status check failed: " . $e->getMessage() . "\n\n";
}

// Test 6: Test Feature Update
echo "6. Testing Feature Update...\n";
try {
    $testData = [
        'enabled' => true,
        'settings' => ['test' => 'value']
    ];
    
    $result = $featureSettings->updateCompanyFeature(1, 'feature_test_feature', $testData);
    echo "✅ Feature update successful\n";
    echo "   - Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";
} catch (Exception $e) {
    echo "❌ Feature update failed: " . $e->getMessage() . "\n\n";
}

// Test 7: Test Enabled Features Retrieval
echo "7. Testing Enabled Features Retrieval...\n";
try {
    $enabledFeatures = $featureSettings->getEnabledFeatures(1);
    echo "✅ getEnabledFeatures() successful\n";
    echo "   - Categories retrieved: " . count($enabledFeatures) . "\n";
    
    foreach ($enabledFeatures as $categoryKey => $category) {
        $enabledCount = 0;
        foreach ($category['features'] as $feature) {
            if ($feature['enabled']) $enabledCount++;
        }
        echo "   - {$category['name']}: {$enabledCount}/" . count($category['features']) . " enabled\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ getEnabledFeatures() failed: " . $e->getMessage() . "\n\n";
}

// Test 8: Test Batch Update
echo "8. Testing Batch Feature Update...\n";
try {
    $batchFeatures = [
        'products' => ['enabled' => true, 'settings' => []],
        'transactions' => ['enabled' => true, 'settings' => []],
        'reports' => ['enabled' => false, 'settings' => []],
        'chart_of_accounts' => ['enabled' => false, 'settings' => []]
    ];
    
    $result = $featureSettings->batchUpdateFeatures(1, $batchFeatures);
    echo "✅ Batch update successful\n";
    echo "   - Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";
} catch (Exception $e) {
    echo "❌ Batch update failed: " . $e->getMessage() . "\n\n";
}

// Test 9: Test Database Table
echo "9. Testing company_settings Table...\n";
try {
    $sql = "SELECT COUNT(*) as total FROM company_settings WHERE company_id = ?";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([1]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ company_settings table accessible\n";
    echo "   - Settings for Company 1: " . $result['total'] . "\n\n";
} catch (Exception $e) {
    echo "❌ company_settings table test failed: " . $e->getMessage() . "\n\n";
}

// Test 10: Test Feature Initialization
echo "10. Testing Feature Initialization...\n";
try {
    $result = $featureSettings->initializeCompanyFeatures(999); // Test with new company ID
    echo "✅ Feature initialization successful\n";
    echo "   - Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";
} catch (Exception $e) {
    echo "❌ Feature initialization failed: " . $e->getMessage() . "\n\n";
}

echo "=== FEATURE SETTINGS INTEGRATION TEST COMPLETED ===\n";
echo "All tests executed. Check the results above for any issues.\n";
echo "\nNext steps:\n";
echo "1. Test the feature settings interface in the browser\n";
echo "2. Verify navigation menu shows/hides based on feature settings\n";
echo "3. Test controller access restrictions\n";
echo "4. Verify AJAX endpoints work correctly\n";
?>
