<?php
/**
 * Fix CSS Loading Issues
 * 
 * Script to diagnose and fix CSS/JS loading problems
 */

echo "=== CSS/JS LOADING DIAGNOSTIC ===\n";

// Check if CSS files exist
$cssFiles = [
    'public/assets/css/style.css',
    'public/assets/css/sidebar.css',
    'public/assets/css/app.css'
];

echo "\n1. Checking CSS files:\n";
foreach ($cssFiles as $cssFile) {
    $fullPath = __DIR__ . '/../' . $cssFile;
    if (file_exists($fullPath)) {
        echo "✓ Found: {$cssFile}\n";
    } else {
        echo "✗ Missing: {$cssFile}\n";
    }
}

// Check if JS files exist
$jsFiles = [
    'public/assets/js/app.js',
    'public/assets/js/jquery-ajax.js',
    'public/assets/js/app_simple.js'
];

echo "\n2. Checking JS files:\n";
foreach ($jsFiles as $jsFile) {
    $fullPath = __DIR__ . '/../' . $jsFile;
    if (file_exists($fullPath)) {
        echo "✓ Found: {$jsFile}\n";
    } else {
        echo "✗ Missing: {$jsFile}\n";
    }
}

// Check BASE_URL configuration
echo "\n3. Checking BASE_URL configuration:\n";
require_once __DIR__ . '/../app/config/config.php';
echo "BASE_URL: " . BASE_URL . "\n";
echo "ASSETS_URL: " . ASSETS_URL . "\n";

// Test CSS file accessibility
echo "\n4. Testing CSS file accessibility:\n";
$testUrl = BASE_URL . '/public/assets/css/style.css';
$context = stream_context_create([
    'http' => [
        'method' => 'HEAD',
        'timeout' => 5
    ]
]);

$fp = @fopen($testUrl, 'r', false, $context);
if ($fp) {
    $headers = stream_get_meta_data($fp)['wrapper_data'];
    fclose($fp);
    
    $contentType = 'unknown';
    foreach ($headers as $header) {
        if (strpos($header, 'Content-Type:') === 0) {
            $contentType = $header;
            break;
        }
    }
    echo "✓ CSS accessible: {$testUrl}\n";
    echo "  Content-Type: {$contentType}\n";
} else {
    echo "✗ CSS not accessible: {$testUrl}\n";
}

// Check .htaccess files
echo "\n5. Checking .htaccess files:\n";
$htaccessFiles = [
    '.htaccess',
    'public/.htaccess',
    'public/assets/.htaccess'
];

foreach ($htaccessFiles as $htaccess) {
    $fullPath = __DIR__ . '/../' . $htaccess;
    if (file_exists($fullPath)) {
        echo "✓ Found: {$htaccess}\n";
    } else {
        echo "✗ Missing: {$htaccess}\n";
    }
}

// Recommendations
echo "\n6. Recommendations:\n";
echo "If CSS files are returning HTML instead of CSS:\n";
echo "1. Check .htaccess configuration\n";
echo "2. Ensure Apache mod_rewrite is enabled\n";
echo "3. Verify file permissions\n";
echo "4. Check MIME type configuration\n";

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
?>
