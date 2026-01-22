<?php
/**
 * Test Specific JavaScript Error at Line 618
 * Test for the exact error mentioned by user
 */

echo "=== Test JavaScript Error at Line 618 ===\n\n";

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

// Check for the specific error pattern
$errorPatterns = [
    'index\.php\?page=companies:618',
    'Unexpected token \)',
    'SyntaxError',
    'Uncaught'
];

$foundErrors = [];
foreach ($errorPatterns as $pattern) {
    if (preg_match('/' . $pattern . '/', $response)) {
        $foundErrors[] = $pattern;
    }
}

if (!empty($foundErrors)) {
    echo "❌ Found error patterns:\n";
    foreach ($foundErrors as $error) {
        echo "   - $error\n";
    }
    
    // Extract lines around line 618 (accounting for 0-based indexing)
    $lines = explode("\n", $response);
    $startLine = max(0, 618 - 5);
    $endLine = min(count($lines) - 1, 618 + 5);
    
    echo "\nLines around line 618:\n";
    for ($i = $startLine; $i <= $endLine; $i++) {
        $lineNum = $i + 1;
        $lineContent = trim($lines[$i]);
        echo "Line $lineNum: " . $lineContent . "\n";
    }
} else {
    echo "✅ No error patterns found in page content\n";
}

// Check for JavaScript syntax issues around Object.assign
if (strpos($response, 'Object.assign') !== false) {
    echo "⚠ Found Object.assign usage\n";
    
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'Object.assign') !== false) {
            echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

// Check for any malformed JavaScript around AJAX calls
if (preg_match('/\$.ajax.*\{.*\}/\s*\);/', $response)) {
    echo "⚠ Found AJAX calls\n";
    
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\$.ajax.*\{.*\}/\s*\);/', $line)) {
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

// Check for any arrow functions
if (preg_match('/=>/', $response)) {
    echo "⚠ Found arrow functions\n";
    
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, '=>') !== false) {
            echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

// Check for any const declarations that might cause issues
if (preg_match('/const\s+\w+\s*=/', $response)) {
    echo "⚠ Found const declarations\n";
    
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/const\s+\w+\s*=/', $line)) {
            echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

echo "\n=== Test Selesai! ===\n";
echo "✅ JavaScript error analysis completed\n";
?>
