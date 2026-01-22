<?php
/**
 * Test JavaScript Runtime Errors
 * Test for potential runtime JavaScript errors
 */

echo "=== Test JavaScript Runtime Errors ===\n\n";

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

// Check for potential runtime issues
$issues = [];

// Check for undefined variables that might cause runtime errors
$undefinedVars = ['saveNewAddress', 'BASE_URL', 'redirect_url'];
foreach ($undefinedVars as $var) {
    if (strpos($response, $var) !== false) {
        $issues[] = "Found potential undefined variable: $var";
    }
}

// Check for function calls that might fail
$riskyFunctions = ['window.open', 'setTimeout', 'setInterval'];
foreach ($riskyFunctions as $func) {
    if (strpos($response, $func) !== false) {
        $issues[] = "Found risky function: $func";
    }
}

// Check for URL concatenation that might cause issues
if (strpos($response, '+ companyId') !== false) {
    $issues[] = "Found URL concatenation with companyId";
}

if (strpos($response, '+ companyId') !== false) {
    $issues[] = "Found URL concatenation with companyId";
}

if (!empty($issues)) {
    echo "⚠ Potential runtime issues found:\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
    
    // Show specific lines
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        foreach ($undefinedVars as $var) {
            if (strpos($line, $var) !== false) {
                echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
} else {
    echo "✅ No obvious runtime issues found\n";
}

// Check for any malformed JavaScript
if (strpos($response, '&& companyId') !== false) {
    echo "⚠ Found potential malformed JavaScript\n";
}

echo "\n=== Test Selesai! ===\n";
?>
