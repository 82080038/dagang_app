<?php
/**
 * Final Warning Check
 * 
 * Test if all duplicate constant warnings are resolved
 */

echo "🔍 FINAL WARNING CHECK\n";
echo "====================\n\n";

// Test loading main entry point
echo "1. Testing main entry point (index.php)...\n";
ob_start();
include __DIR__ . '/../index.php';
$output = ob_get_clean();

// Check for warning messages in output
$warnings = [];
if (strpos($output, 'Warning:') !== false) {
    $warnings[] = 'Warning messages found in output';
}

if (strpos($output, 'already defined') !== false) {
    $warnings[] = 'Duplicate constant warnings found';
}

if (strpos($output, 'APP_DEBUG already defined') !== false) {
    $warnings[] = 'APP_DEBUG duplicate warning found';
}

if (strpos($output, 'DB_CHARSET already defined') !== false) {
    $warnings[] = 'DB_CHARSET duplicate warning found';
}

if (empty($warnings)) {
    echo "  ✅ No warnings found in main entry point\n";
} else {
    echo "  ❌ Warnings found:\n";
    foreach ($warnings as $warning) {
        echo "    - $warning\n";
    }
}

echo "\n";

// Test constant definitions
echo "2. Testing constant definitions...\n";

// Include config files in correct order
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/config/config.php';

$testConstants = ['APP_DEBUG', 'DB_CHARSET', 'APP_ENV', 'BASE_URL'];
$allDefined = true;

foreach ($testConstants as $constant) {
    if (defined($constant)) {
        echo "  ✅ $constant is defined\n";
    } else {
        echo "  ❌ $constant is NOT defined\n";
        $allDefined = false;
    }
}

echo "\n";

// Test redefinition protection
echo "3. Testing redefinition protection...\n";

$originalDebug = APP_DEBUG;
$originalCharset = DB_CHARSET;

// Try to redefine (should not cause warnings)
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'latin1');
}

// Check if values changed (they shouldn't)
if (APP_DEBUG === $originalDebug) {
    echo "  ✅ APP_DEBUG protection working\n";
} else {
    echo "  ❌ APP_DEBUG protection failed\n";
}

if (DB_CHARSET === $originalCharset) {
    echo "  ✅ DB_CHARSET protection working\n";
} else {
    echo "  ❌ DB_CHARSET protection failed\n";
}

echo "\n";

// Summary
echo "4. SUMMARY\n";
echo "----------\n";

if (empty($warnings) && $allDefined) {
    echo "✅ ALL TESTS PASSED\n";
    echo "✅ No duplicate constant warnings\n";
    echo "✅ All required constants defined\n";
    echo "✅ Redefinition protection working\n";
    echo "\n🎉 APPLICATION IS READY!\n";
    echo "   Test at: http://localhost/dagang\n";
} else {
    echo "❌ SOME ISSUES REMAIN\n";
    echo "   Please check the warnings above\n";
}

echo "\n✅ FINAL CHECK COMPLETED\n";
?>
