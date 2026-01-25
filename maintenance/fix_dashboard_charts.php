<?php
/**
 * Fix Dashboard Chart Loading Issues
 * 
 * Script to diagnose and fix Chart.js and jQuery loading problems
 */

echo "=== DASHBOARD CHARTS DIAGNOSTIC ===\n";

// Check if Chart.js is accessible
echo "\n1. Checking Chart.js CDN:\n";
$chartJsUrl = 'https://cdn.jsdelivr.net/npm/chart.js';
$context = stream_context_create([
    'http' => [
        'method' => 'HEAD',
        'timeout' => 5
    ]
]);

$fp = @fopen($chartJsUrl, 'r', false, $context);
if ($fp) {
    echo "✓ Chart.js CDN accessible: {$chartJsUrl}\n";
    fclose($fp);
} else {
    echo "✗ Chart.js CDN not accessible: {$chartJsUrl}\n";
}

// Check jQuery CDN
echo "\n2. Checking jQuery CDN:\n";
$jqueryUrl = 'https://code.jquery.com/jquery-3.6.0.min.js';
$fp = @fopen($jqueryUrl, 'r', false, $context);
if ($fp) {
    echo "✓ jQuery CDN accessible: {$jqueryUrl}\n";
    fclose($fp);
} else {
    echo "✗ jQuery CDN not accessible: {$jqueryUrl}\n";
}

// Check Bootstrap CDN
echo "\n3. Checking Bootstrap CDN:\n";
$bootstrapUrl = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
$fp = @fopen($bootstrapUrl, 'r', false, $context);
if ($fp) {
    echo "✓ Bootstrap CDN accessible: {$bootstrapUrl}\n";
    fclose($fp);
} else {
    echo "✗ Bootstrap CDN not accessible: {$bootstrapUrl}\n";
}

// Check local JS files
echo "\n4. Checking local JS files:\n";
$localJsFiles = [
    'public/assets/js/app_simple.js',
    'public/assets/js/app.js',
    'public/assets/js/jquery-ajax.js'
];

foreach ($localJsFiles as $jsFile) {
    $fullPath = __DIR__ . '/../' . $jsFile;
    if (file_exists($fullPath)) {
        echo "✓ Found: {$jsFile}\n";
    } else {
        echo "✗ Missing: {$jsFile}\n";
    }
}

// Check BASE_URL configuration
echo "\n5. Checking BASE_URL configuration:\n";
require_once __DIR__ . '/../app/config/config.php';
echo "BASE_URL: " . BASE_URL . "\n";

// Test dashboard API endpoints
echo "\n6. Testing dashboard API endpoints:\n";
$apiEndpoints = [
    'dashboard/api-stats',
    'dashboard/api-scalability',
    'dashboard/api-segments'
];

foreach ($apiEndpoints as $endpoint) {
    $testUrl = BASE_URL . '/index.php?page=' . $endpoint;
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
        echo "✓ {$endpoint}: {$contentType}\n";
    } else {
        echo "✗ {$endpoint}: Not accessible\n";
    }
}

// Check CSP configuration
echo "\n7. Checking CSP configuration:\n";
if (defined('CONTENT_SECURITY_POLICY')) {
    echo "CSP: " . CONTENT_SECURITY_POLICY . "\n";
    
    if (strpos(CONTENT_SECURITY_POLICY, 'chart.js') !== false) {
        echo "✓ Chart.js allowed in CSP\n";
    } else {
        echo "✗ Chart.js not allowed in CSP\n";
    }
    
    if (strpos(CONTENT_SECURITY_POLICY, 'jquery.com') !== false) {
        echo "✓ jQuery CDN allowed in CSP\n";
    } else {
        echo "✗ jQuery CDN not allowed in CSP\n";
    }
} else {
    echo "CSP: Not defined\n";
}

// Check security headers status
echo "\n8. Checking security headers:\n";
if (defined('SECURITY_HEADERS')) {
    echo "SECURITY_HEADERS: " . (SECURITY_HEADERS ? 'ENABLED' : 'DISABLED') . "\n";
    if (!SECURITY_HEADERS) {
        echo "✓ Security headers disabled (good for development)\n";
    } else {
        echo "⚠ Security headers enabled (may block resources)\n";
    }
} else {
    echo "SECURITY_HEADERS: Not defined\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "\nRECOMMENDATIONS:\n";
echo "1. Ensure Chart.js CDN is accessible\n";
echo "2. Check CSP allows Chart.js and jQuery CDNs\n";
echo "3. Verify BASE_URL is correct\n";
echo "4. Test dashboard API endpoints return JSON\n";
echo "5. Check browser console for specific errors\n";
?>
