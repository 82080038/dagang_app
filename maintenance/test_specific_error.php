<?php
/**
 * Test Specific JavaScript Error
 * Test for the specific error mentioned by user
 */

echo "=== Test Specific JavaScript Error ===\n\n";

// Get the page content
$url = 'http://localhost/dagang/index.php?page=companies';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0\r\n"
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Failed to fetch page content\n";
    exit(1);
}

echo "✅ Page content fetched successfully\n";

// Check for the specific error pattern
$errorPattern = 'index\.php\?page=companies:616';
if (strpos($response, $errorPattern) !== false) {
    echo "❌ Found error pattern: $errorPattern\n";
    
    // Show the problematic lines
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, $errorPattern) !== false) {
            echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "✅ No error pattern found in page content\n";
}

// Check for any URL that might cause the error
$urls = [];
preg_match_all('/index\.php\?page=companies[^"\'\s]*/', $response, $urls);

if (!empty($urls[0])) {
    echo "Found URLs that might cause issues:\n";
    foreach ($urls[0] as $url) {
        echo "   - $url\n";
    }
} else {
    echo "✅ No problematic URLs found\n";
}

// Check for any JavaScript that might append to URLs
$jsPatterns = [
    'window\.open',
    'location\.href',
    'location\.assign',
    'location\.replace',
    'history\.pushState'
];

foreach ($jsPatterns as $pattern) {
    if (preg_match('/' . $pattern . '/i', $response)) {
        echo "⚠ Found $pattern usage\n";
        
        // Show lines with this pattern
        $lines = explode("\n", $response);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/' . $pattern . '/i', $line)) {
                echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}

echo "\n=== Test Selesai! ===\n";
?>
