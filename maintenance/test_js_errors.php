<?php
/**
 * Test Companies Page for JavaScript Errors
 * Check if there are any JavaScript syntax errors
 */

echo "=== Test Companies Page for JavaScript Errors ===\n\n";

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

// Check for common JavaScript syntax errors
$errors = [
    'SyntaxError',
    'Unexpected token',
    'Uncaught',
    'ReferenceError',
    'TypeError'
];

$foundErrors = [];
foreach ($errors as $error) {
    if (strpos($response, $error) !== false) {
        $foundErrors[] = $error;
    }
}

if (!empty($foundErrors)) {
    echo "❌ JavaScript errors found:\n";
    foreach ($foundErrors as $error) {
        echo "   - $error\n";
    }
    
    // Show the problematic lines
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        foreach ($foundErrors as $error) {
            if (strpos($line, $error) !== false) {
                echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
} else {
    echo "✅ No JavaScript syntax errors found\n";
}

// Check for undefined variables
if (strpos($response, 'saveNewAddress') !== false) {
    echo "⚠ Found 'saveNewAddress' reference\n";
    
    // Show lines with saveNewAddress
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'saveNewAddress') !== false) {
            echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

echo "\n=== Test Selesai! ===\n";
?>
