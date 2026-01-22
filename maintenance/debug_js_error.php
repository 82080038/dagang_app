<?php
/**
 * Debug JavaScript Error
 * Try to reproduce the exact error
 */

echo "=== Debug JavaScript Error ===\n\n";

// Get the page content
$url = 'http://localhost/dagang/index.php?page=companies';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Failed to fetch page content\n";
    exit(1);
}

echo "✅ Page content fetched successfully\n";

// Check for any JavaScript that might cause the specific error
$patterns = [
    'index\.php\?page=companies:',
    'index\.php\?page=companies.*:',
    'page=companies.*:',
    'companies:',
    ':616'
];

foreach ($patterns as $pattern) {
    if (preg_match('/' . $pattern . '/', $response)) {
        echo "❌ Found pattern: $pattern\n";
        
        // Show lines with this pattern
        $lines = explode("\n", $response);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/' . $pattern . '/', $line)) {
                echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}

// Check for any JavaScript that might append :616 to URLs
$jsFunctions = [
    'window\.open',
    'location\.href',
    'location\.assign',
    'location\.replace',
    'history\.pushState',
    'history\.replaceState'
];

foreach ($jsFunctions as $func) {
    if (preg_match('/' . $func . '/i', $response)) {
        echo "⚠ Found $func usage\n";
        
        // Show lines with this function
        $lines = explode("\n", $response);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/' . $func . '/i', $line)) {
                echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}

// Check for any string concatenation that might cause issues
if (preg_match('/\+.*companyId/', $response)) {
    echo "⚠ Found companyId concatenation\n";
    
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\+.*companyId/', $line)) {
            echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

// Check for any template literals that might cause issues
if (preg_match('/`.*\$\{.*\}`/', $response)) {
    echo "⚠ Found template literals\n";
    
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/`.*\$\{.*\}`/', $line)) {
            echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

echo "\n=== Test Selesai! ===\n";
?>
