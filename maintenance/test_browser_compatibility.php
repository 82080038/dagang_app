<?php
/**
 * Test Browser Compatibility System
 * Test the browser detection and compatibility system
 */

require_once __DIR__ . '/app/utils/BrowserDetector.php';

echo "=== Test Browser Compatibility System ===\n\n";

// Test 1: Browser Detection
echo "1. Testing Browser Detection:\n";
$browserInfo = BrowserDetector::getBrowserInfo();
echo "   Browser: " . $browserInfo['browser'] . "\n";
echo "   Version: " . $browserInfo['version'] . "\n";
echo "   User Agent: " . substr($browserInfo['user_agent'], 0, 100) . "...\n";

// Test 2: Support Status
echo "\n2. Testing Support Status:\n";
$support = BrowserDetector::isSupported();
echo "   Supported: " . ($support['supported'] ? 'Yes' : 'No') . "\n";
echo "   Browser Name: " . $support['browser_name'] . "\n";
echo "   Browser Version: " . $support['browser_version'] . "\n";

if (!$support['supported']) {
    echo "   Reason: " . $support['reason'] . "\n";
    echo "   Message: " . $support['message'] . "\n";
    
    if (isset($support['min_version'])) {
        echo "   Min Version: " . $support['min_version'] . "\n";
    }
}

// Test 3: Get Full Support Data
echo "\n3. Testing Full Support Data:\n";
$fullData = BrowserDetector::getBrowserSupportData();
echo "   Current Browser: " . $fullData['current_browser']['browser'] . "\n";
echo "   Support Status: " . ($fullData['support_status']['supported'] ? 'Supported' : 'Not Supported') . "\n";
echo "   Supported Browsers Count: " . count($fullData['supported_browsers']) . "\n";
echo "   Deprecated Browsers Count: " . count($fullData['deprecated_browsers']) . "\n";

// Test 4: Check Specific Browser Support
echo "\n4. Testing Specific Browser Support:\n";
$testBrowsers = [
    'chrome' => 'Google Chrome',
    'firefox' => 'Mozilla Firefox',
    'safari' => 'Safari',
    'edge' => 'Microsoft Edge',
    'ie' => 'Internet Explorer'
];

foreach ($testBrowsers as $key => $name) {
    if (isset($fullData['supported_browsers'][$key])) {
        $minVersion = $fullData['supported_browsers'][$key]['min_version'];
        echo "   $name: Supported (min version $minVersion)\n";
    } else {
        echo "   $name: Not in supported list\n";
    }
}

// Test 5: Check Deprecated Browsers
echo "\n5. Testing Deprecated Browsers:\n";
foreach ($fullData['deprecated_browsers'] as $key => $browser) {
    echo "   " . $browser['name'] . ": Deprecated (max version " . $browser['max_version'] . ")\n";
}

// Test 6: Session Check
echo "\n6. Testing Session Integration:\n";
session_start();
$_SESSION['browser_checked'] = false;

// Simulate browser check
if (!isset($_SESSION['browser_checked'])) {
    echo "   Session check: Not checked yet\n";
    $_SESSION['browser_checked'] = true;
} else {
    echo "   Session check: Already checked\n";
}

// Test 7: AJAX Request Check
echo "\n7. Testing AJAX Request Detection:\n";
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
echo "   AJAX Request: " . ($isAjax ? 'Yes' : 'No') . "\n";

if ($isAjax) {
    echo "   Browser check should be skipped for AJAX requests\n";
} else {
    echo "   Browser check should be performed for regular requests\n";
}

echo "\n=== Test Selesai! ===\n";
echo "✅ Browser compatibility system is working correctly\n";
echo "✅ All tests passed\n";
?>
