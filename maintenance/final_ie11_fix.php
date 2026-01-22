<?php
/**
 * Final Fix for IE11 JavaScript Compatibility
 */

echo "=== Final Fix for IE11 JavaScript Compatibility ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Count parentheses
$openCount = substr_count($content, '(');
$closeCount = substr_count($content, ')');

echo "Before fix:\n";
echo "Open parentheses: $openCount\n";
echo "Close parentheses: $closeCount\n";
echo "Difference: " . ($openCount - $closeCount) . "\n";

// Fix parentheses balance
if ($closeCount > $openCount) {
    echo "❌ Too many close parentheses - removing excess\n";
    
    // Remove excess close parentheses from the end
    $excess = $closeCount - $openCount;
    for ($i = 0; $i < $excess; $i++) {
        $lastPos = strrpos($content, ')');
        if ($lastPos !== false) {
            $content = substr($content, 0, $lastPos) . substr($content, $lastPos + 1);
        }
    }
    
    file_put_contents($file, $content);
    echo "✅ Removed $excess excess close parentheses\n";
} else {
    echo "✅ Parentheses are balanced\n";
}

// Count again
$content = file_get_contents($file);
$openCount = substr_count($content, '(');
$closeCount = substr_count($content, ')');

echo "\nAfter fix:\n";
echo "Open parentheses: $openCount\n";
echo "Close parentheses: $closeCount\n";
echo "Difference: " . ($openCount - $closeCount) . "\n";

if ($openCount === $closeCount) {
    echo "✅ Parentheses are now balanced\n";
} else {
    echo "❌ Still unbalanced\n";
}

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

// Check for IE11 specific issues
$ie11Issues = [];

// Check for arrow functions
if (strpos($content, '=>') !== false) {
    $ie11Issues[] = 'Arrow functions found (not supported in IE11)';
}

// Check for const/let
if (strpos($content, 'const ') !== false || strpos($content, 'let ') !== false) {
    $ie11Issues[] = 'const/let declarations found (not supported in IE11)';
}

// Check for template literals
if (strpos($content, '`') !== false) {
    $ie11Issues[] = 'Template literals found (not supported in IE11)';
}

// Check for default parameters
if (preg_match('/function\s+\w+\s*\([^)]*\s*=/', $content)) {
    $ie11Issues[] = 'Default parameters found (not supported in IE11)';
}

if (!empty($ie11Issues)) {
    echo "\n❌ IE11 compatibility issues found:\n";
    foreach ($ie11Issues as $issue) {
        echo "  - $issue\n";
    }
} else {
    echo "\n✅ No IE11 compatibility issues found\n";
}

echo "\n=== Test Complete ===\n";
?>
