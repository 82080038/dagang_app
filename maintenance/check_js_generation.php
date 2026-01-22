<?php
/**
 * Check for JavaScript Generation Issues
 */

echo "=== Check for JavaScript Generation Issues ===\n";

// Get the actual page content as it would be served
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

// Check for any JavaScript that might be causing issues
echo "\nChecking for JavaScript issues in generated content:\n";

// Look for success identifier issues in the generated content
if (preg_match_all('/\bsuccess\b/', $response, $matches)) {
    echo "Found 'success' identifier in generated content: " . count($matches[0]) . " times\n";
    
    // Find lines with success
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\bsuccess\b/', $line)) {
            echo "Line " . ($lineNum + 1) . ": " . trim(substr($line, 0, 100)) . "\n";
            
            // Check for syntax issues
            if (preg_match('/\bsuccess\b(?!.*\')/', $line) && !preg_match('/success:/', $line)) {
                echo "  ❌ POSSIBLE ISSUE: Unquoted 'success' identifier\n";
            }
        }
    }
} else {
    echo "No 'success' identifier found in generated content\n";
}

// Check for any showNotification calls that might be causing issues
echo "\nChecking for showNotification calls in generated content:\n";
if (preg_match_all('/showNotification\([^)]*\)/', $response, $matches)) {
    echo "Found showNotification calls: " . count($matches[0]) . " times\n";
    
    foreach ($matches[0] as $match) {
        echo "  showNotification call: " . $match . "\n";
        
        // Check for syntax issues
        if (preg_match('/showNotification\([^,]*,\s*([^\')\)]+)/', $match, $submatch)) {
            echo "    ❌ ISSUE: Unquoted type parameter: " . $submatch[1] . "\n";
        }
    }
} else {
    echo "No showNotification calls found in generated content\n";
}

// Check for any Object.keys that might be causing issues
echo "\nChecking for Object.keys in generated content:\n";
if (preg_match_all('/Object\.keys/', $response, $matches)) {
    echo "Found Object.keys: " . count($matches[0]) . " times\n";
    
    foreach ($matches[0] as $match) {
        echo "  Object.keys usage: " . $match . "\n";
    }
} else {
    echo "No Object.keys found in generated content\n";
}

// Check for any template literals that might be causing issues
echo "\nChecking for template literals in generated content:\n";
if (preg_match_all('/`[^`]*`/', $response, $matches)) {
    echo "Found template literals: " . count($matches[0]) . " times\n";
    
    foreach ($matches[0] as $match) {
        echo "  Template literal: " . $match . "\n";
    }
} else {
    echo "No template literals found in generated content\n";
}

// Check for any arrow functions that might be causing issues
echo "\nChecking for arrow functions in generated content:\n";
if (preg_match_all('/=>/', $response, $matches)) {
    echo "Found arrow functions: " . count($matches[0]) . " times\n";
    
    foreach ($matches[0] as $match) {
        echo "  Arrow function: " . $match . "\n";
    }
} else {
    echo "No arrow functions found in generated content\n";
}

// Check for any const/let that might be causing issues
echo "\nChecking for const/let in generated content:\n";
if (preg_match_all('/\b(const|let)\s+\w+\s*=/', $response, $matches)) {
    echo "Found const/let declarations: " . count($matches[0]) . " times\n";
    
    foreach ($matches[0] as $match) {
        echo "  Declaration: " . $match . "\n";
    }
} else {
    echo "No const/let found in generated content\n";
}

echo "\n=== Test Complete ===\n";
?>
