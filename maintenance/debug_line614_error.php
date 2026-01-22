<?php
/**
 * Debug Line 614 JavaScript Error
 */

echo "=== Debug Line 614 JavaScript Error ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Get line 614
$lines = explode("\n", $content);
$line614 = isset($lines[613]) ? $lines[613] : '';

echo "Line 614 content: " . trim($line614) . "\n";
echo "Line 614 length: " . strlen($line614) . "\n";

// Check for any syntax issues in line 614
if (preg_match('/\)$/', $line614)) {
    echo "Line 614 ends with ')'\n";
    
    // Check if there are any unmatched parentheses
    $openCount = substr_count($line614, '(');
    $closeCount = substr_count($line614, ')');
    echo "Parentheses in line 614: $openCount open, $closeCount close\n";
    
    if ($closeCount > $openCount) {
        echo "❌ Too many close parentheses in line 614\n";
    }
}

// Check for any syntax issues around line 614
echo "\nContext around line 614:\n";
for ($i = max(0, 613 - 5); $i < min(count($lines), 613 + 5); $i++) {
    $lineNum = $i + 1;
    $lineContent = $lines[$i];
    
    // Check for syntax issues
    if (preg_match('/\)$/', $lineContent)) {
        echo "Line $lineNum: " . trim($lineContent) . " ⚠ ENDS WITH )\n";
        
        // Count parentheses in this line
        $openCount = substr_count($lineContent, '(');
        $closeCount = substr_count($lineContent, ')');
        echo "  Parentheses: $openCount open, $closeCount close\n";
        
        if ($closeCount > $openCount) {
            echo "  ❌ TOO MANY CLOSE PARENTHESES\n";
        } elseif ($openCount > $closeCount) {
            echo "  ❌ TOO MANY OPEN PARENTHESES\n";
        } else {
            echo "  ✅ BALANCED\n";
        }
    } else {
        echo "Line $lineNum: " . trim($lineContent) . "\n";
    }
}

// Check for any syntax errors in the entire file
echo "\nChecking for syntax errors in the entire file:\n";

// Look for any lines that might cause syntax errors
$suspiciousPatterns = [
    '/\)\s*\)\s*\)/',  // Triple closing parentheses
    '/\)\s*\)\s*}$/',  // Double closing parentheses before brace
    '/\)\s*\)\s*,/',  // Double closing parentheses before comma
    '/\)\s*\)\s*;/',  // Double closing parentheses before semicolon
];

foreach ($suspiciousPatterns as $pattern => $description) {
    if (preg_match_all($pattern, $content, $matches)) {
        echo "❌ Found suspicious pattern: $description\n";
        echo "  Count: " . count($matches[0]) . "\n";
        
        // Find lines with this pattern
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (preg_match($pattern, $line)) {
                echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}

// Check for any function calls with too many parentheses
echo "\nChecking for function calls with too many parentheses:\n";
if (preg_match_all('/\w+\([^)]*\)\s*\)/', $content, $matches)) {
    echo "Found " . count($matches[0]) . " function calls with extra parentheses\n";
    
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\w+\([^)]*\)\s*\)/', $line)) {
            echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

echo "\n=== Test Complete ===\n";
?>
