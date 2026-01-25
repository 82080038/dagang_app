<?php
/**
 * Debug Dashboard Scripts Loading
 * 
 * Script to test and debug JavaScript loading issues
 */

// Load configuration to get BASE_URL
require_once __DIR__ . '/../app/config/config.php';

echo "=== DASHBOARD SCRIPTS DEBUG ===\n";

// Test if we can access the dashboard page
echo "\n1. Testing dashboard page access:\n";
$dashboardUrl = BASE_URL . '/index.php?page=dashboard';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$fp = @fopen($dashboardUrl, 'r', false, $context);
if ($fp) {
    $content = stream_get_contents($fp);
    fclose($fp);
    
    // Check if Chart.js is included
    if (strpos($content, 'chart.js') !== false) {
        echo "✓ Chart.js script found in dashboard page\n";
    } else {
        echo "✗ Chart.js script NOT found in dashboard page\n";
    }
    
    // Check if jQuery is included
    if (strpos($content, 'jquery-3.6.0.min.js') !== false) {
        echo "✓ jQuery script found in dashboard page\n";
    } else {
        echo "✗ jQuery script NOT found in dashboard page\n";
    }
    
    // Check if BASE_URL is defined
    if (strpos($content, 'window.BASE_URL') !== false) {
        echo "✓ BASE_URL variable found in dashboard page\n";
    } else {
        echo "✗ BASE_URL variable NOT found in dashboard page\n";
    }
    
    // Check for JavaScript errors in the page
    if (strpos($content, '$ is not defined') !== false) {
        echo "⚠ Found '$ is not defined' reference in page\n";
    }
    
    if (strpos($content, 'Chart is not defined') !== false) {
        echo "⚠ Found 'Chart is not defined' reference in page\n";
    }
    
} else {
    echo "✗ Cannot access dashboard page: {$dashboardUrl}\n";
}

// Check current CSP configuration
echo "\n2. Current CSP configuration:\n";
if (defined('CONTENT_SECURITY_POLICY')) {
    if (CONTENT_SECURITY_POLICY === false) {
        echo "✓ CSP is DISABLED (good for development)\n";
    } else {
        echo "⚠ CSP is ENABLED: " . CONTENT_SECURITY_POLICY . "\n";
    }
} else {
    echo "✗ CSP not defined\n";
}

// Check security headers status
echo "\n3. Security headers status:\n";
if (defined('SECURITY_HEADERS')) {
    echo "SECURITY_HEADERS: " . (SECURITY_HEADERS ? 'ENABLED' : 'DISABLED') . "\n";
    if (SECURITY_HEADERS) {
        echo "⚠ Security headers are ENABLED (may block scripts)\n";
    } else {
        echo "✓ Security headers are DISABLED (good for development)\n";
    }
} else {
    echo "✗ SECURITY_HEADERS not defined\n";
}

// Check if we can load Chart.js directly
echo "\n4. Testing Chart.js direct access:\n";
$chartJsUrl = 'https://cdn.jsdelivr.net/npm/chart.js';
$fp = @fopen($chartJsUrl, 'r', false, $context);
if ($fp) {
    $chartJsContent = fread($fp, 1024);
    fclose($fp);
    
    if (strpos($chartJsContent, 'Chart') !== false) {
        echo "✓ Chart.js CDN is accessible and contains Chart object\n";
    } else {
        echo "✗ Chart.js CDN accessible but doesn't contain Chart object\n";
    }
} else {
    echo "✗ Chart.js CDN not accessible\n";
}

// Check jQuery direct access
echo "\n5. Testing jQuery direct access:\n";
$jqueryUrl = 'https://code.jquery.com/jquery-3.6.0.min.js';
$fp = @fopen($jqueryUrl, 'r', false, $context);
if ($fp) {
    $jqueryContent = fread($fp, 1024);
    fclose($fp);
    
    if (strpos($jqueryContent, 'jQuery') !== false) {
        echo "✓ jQuery CDN is accessible and contains jQuery object\n";
    } else {
        echo "✗ jQuery CDN accessible but doesn't contain jQuery object\n";
    }
} else {
    echo "✗ jQuery CDN not accessible\n";
}

// Check local files
echo "\n6. Checking local JavaScript files:\n";
$localJsFiles = [
    'public/assets/js/app_simple.js',
    'public/assets/js/app.js',
    'public/assets/js/jquery-ajax.js'
];

foreach ($localJsFiles as $jsFile) {
    $fullPath = __DIR__ . '/../' . $jsFile;
    if (file_exists($fullPath)) {
        echo "✓ Found: {$jsFile}\n";
        
        // Check file size
        $size = filesize($fullPath);
        echo "  Size: " . round($size / 1024, 2) . " KB\n";
    } else {
        echo "✗ Missing: {$jsFile}\n";
    }
}

// Recommendations
echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Clear browser cache (Ctrl+F5 or Ctrl+Shift+R)\n";
echo "2. Check browser console for specific JavaScript errors\n";
echo "3. Verify scripts are loading in correct order\n";
echo "4. Test with browser developer tools Network tab\n";
echo "5. Disable browser extensions temporarily\n";

echo "\n=== DEBUG COMPLETE ===\n";
?>
