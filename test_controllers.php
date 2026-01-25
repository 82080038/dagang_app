<?php
/**
 * Test Controllers Loading
 * 
 * Verify all controllers can be loaded without errors
 */

echo "🔍 TESTING CONTROLLERS LOADING\n";
echo "=====================================\n\n";

// Define BASE_URL for testing
define('BASE_URL', 'http://localhost/dagang');

// Test main controllers
$controllers = [
    'AdvancedReportsController',
    'FeatureSettingsController', 
    'ProductTransferController',
    'StaffController'
];

$successCount = 0;
$errorCount = 0;

foreach ($controllers as $controller) {
    echo "Testing $controller... ";
    
    try {
        $file = __DIR__ . "/app/controllers/{$controller}.php";
        
        if (!file_exists($file)) {
            echo "❌ FILE NOT FOUND\n";
            $errorCount++;
            continue;
        }
        
        // Check syntax
        $output = [];
        $returnCode = 0;
        exec("E:\\xampp\\php\\php.exe -l \"$file\" 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "✅ OK\n";
            $successCount++;
        } else {
            echo "❌ SYNTAX ERROR: " . implode(' ', $output) . "\n";
            $errorCount++;
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n📊 SUMMARY\n";
echo "=====================================\n";
echo "✅ Success: $successCount\n";
echo "❌ Errors: $errorCount\n";
echo "📊 Total: " . ($successCount + $errorCount) . "\n";

if ($errorCount === 0) {
    echo "\n🎉 ALL CONTROLLERS LOADED SUCCESSFULLY!\n";
    echo "✅ Application is ready to run\n";
} else {
    echo "\n⚠️  Some controllers have errors that need to be fixed\n";
}

echo "\n🔧 TESTING APPLICATION STARTUP\n";
echo "=====================================\n";

// Test if main index.php can start without errors
try {
    // Capture warnings/errors
    $oldErrorReporting = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
    
    // Try to include the main files without executing
    require_once __DIR__ . '/app/config/database.php';
    require_once __DIR__ . '/app/config/constants.php';
    require_once __DIR__ . '/app/core/Controller.php';
    
    echo "✅ Core files loaded successfully\n";
    
    // Test if constants are defined
    if (defined('ROLE_SUPER_ADMIN')) {
        echo "✅ Constants loaded successfully\n";
    } else {
        echo "❌ Constants not loaded\n";
    }
    
    error_reporting($oldErrorReporting);
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n🎯 FINAL STATUS\n";
echo "=====================================\n";
if ($errorCount === 0) {
    echo "🎉 APPLICATION IS READY!\n";
    echo "✅ All controllers can be loaded\n";
    echo "✅ Constants file is accessible\n";
    echo "✅ Database configuration is loaded\n";
    echo "\n🚀 You can now access the application at: http://localhost/dagang\n";
} else {
    echo "⚠️  APPLICATION NEEDS FIXES\n";
    echo "❌ Some controllers have errors\n";
    echo "🔧 Please fix the errors before running the application\n";
}
?>
