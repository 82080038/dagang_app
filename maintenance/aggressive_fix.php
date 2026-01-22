<?php
/**
 * Aggressive Fix for Parentheses Balance
 */

echo "=== Aggressive Fix for Parentheses Balance ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Count parentheses
$openCount = substr_count($content, '(');
$closeCount = substr_count($content, ')');

echo "Before fix:\n";
echo "Open parentheses: $openCount\n";
echo "Close parentheses: $closeCount\n";
echo "Difference: " . ($openCount - $closeCount) . "\n";

if ($closeCount > $openCount) {
    echo "❌ Too many close parentheses - removing excess\n";
    
    // Remove excess close parentheses
    $excess = $closeCount - $openCount;
    $content = substr($content, 0, -$excess);
    
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

echo "\n=== Test Complete ===\n";
?>
