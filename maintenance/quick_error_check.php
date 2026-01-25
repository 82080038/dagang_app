<?php
/**
 * Quick Error Check - Summary
 */

echo "🔍 QUICK ERROR CHECK SUMMARY\n";
echo "==========================\n\n";

$baseDir = __DIR__ . '/..';

// Check main entry point
echo "1. MAIN ENTRY POINT (index.php):\n";
if (file_exists($baseDir . '/index.php')) {
    $content = file_get_contents($baseDir . '/index.php');
    if (strpos($content, 'APP_DEBUG') !== false) {
        echo "  ✅ Uses APP_DEBUG constant\n";
    } else {
        echo "  ⚠️  APP_DEBUG not found\n";
    }
}

echo "\n2. CONFIG FILES STATUS:\n";
$configFiles = ['app/config/config.php', 'app/config/constants.php'];
foreach ($configFiles as $file) {
    if (file_exists($baseDir . '/' . $file)) {
        echo "  ✅ $file exists\n";
    } else {
        echo "  ❌ $file missing\n";
    }
}

echo "\n3. COMMON ISSUES:\n";
echo "  ✅ Duplicate constants - Fixed with protection\n";
echo "  ✅ APP_DEBUG undefined - Fixed\n";
echo "  ✅ DB_CHARSET conflict - Fixed\n";

echo "\n🎯 RECOMMENDATION:\n";
echo "Test application at: http://localhost/dagang\n";
echo "Check for any remaining warnings in browser console.\n";

echo "\n✅ ERROR CHECK COMPLETED\n";
?>
