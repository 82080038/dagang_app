<?php
/**
 * Debug Chrome JavaScript Error Line 594 - Success Identifier
 */

echo "=== Debug Chrome JavaScript Error Line 594 - Success Identifier ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Get line 594
$lines = explode("\n", $content);
$line594 = isset($lines[593]) ? $lines[593] : '';

echo "Line 594 content: " . trim($line594) . "\n";

// Check for any success identifier issues
echo "\nChecking for 'success' identifier issues:\n";

// Look for success that might be causing issues
$lines = explode("\n", $content);
foreach ($lines as $lineNum => $line) {
    // Check for success in contexts that might cause issues
    if (preg_match('/\bsuccess\b/', $line)) {
        echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        
        // Check if it's properly quoted
        if (preg_match('/\bsuccess\b(?!.*\')/', $line) && !preg_match('/success:/', $line)) {
            echo "  ❌ POSSIBLE ISSUE: Unquoted 'success' identifier\n";
        }
    }
}

// Check for any malformed JavaScript around line 594
echo "\nChecking for malformed JavaScript around line 594:\n";
$contextLines = array_slice($lines, 590, 20);
foreach ($contextLines as $i => $line) {
    $lineNum = 590 + $i + 1;
    echo "Line $lineNum: " . trim($line) . "\n";
    
    // Check for syntax issues
    if (preg_match('/\b(success|error)\b/', $line)) {
        echo "  Contains: success/error\n";
    }
    
    if (preg_match('/\{[^}]*$/', $line)) {
        echo "  ❌ POSSIBLE ISSUE: Unclosed brace\n";
    }
    
    if (preg_match('/\([^)]*$/', $line)) {
        echo "  ❌ POSSIBLE ISSUE: Unclosed parenthesis\n";
    }
}

// Check for any PHP variables that might be causing issues
echo "\nChecking for PHP variables that might cause JavaScript issues:\n";
if (preg_match_all('/\$\w+/', $content, $matches)) {
    echo "Found PHP variables: " . implode(', ', array_unique($matches[0])) . "\n";
}

// Check for any HTML that might be causing issues
echo "\nChecking for HTML that might cause JavaScript issues:\n";
if (preg_match_all('/<[^>]*>/', $content, $matches)) {
    echo "Found HTML tags: " . count($matches[0]) . " tags\n";
}

// Check for any special characters that might cause issues
echo "\nChecking for special characters that might cause JavaScript issues:\n";
$specialChars = ['"', "'", '`', '\\', '/', '<', '>', '&'];
foreach ($specialChars as $char) {
    $count = substr_count($content, $char);
    if ($count > 0) {
        echo "Found '$char': $count times\n";
    }
}

// Check for any unquoted success in showNotification calls
echo "\nChecking for unquoted success in showNotification calls:\n";
if (preg_match_all('/showNotification\([^,]*,\s*\'([^\']*)\'\)/', $content, $matches)) {
    echo "Found showNotification calls with quotes:\n";
    foreach ($matches[1] as $match) {
        echo "  Type: '$match'\n";
    }
}

// Check for any success without quotes
echo "\nChecking for success without quotes:\n";
if (preg_match_all('/showNotification\([^,]*,\s*([^\')\)]+)/', $content, $matches)) {
    echo "Found showNotification calls without quotes:\n";
    foreach ($matches[1] as $match) {
        echo "  Type: $match (UNQUOTED - ISSUE!)\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
