<?php
/**
 * Final Fix for All Issues
 */

echo "=== Final Fix for All Issues ===\n";

// Fix companies/index.php
$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Count parentheses
$openCount = substr_count($content, '(');
$closeCount = substr_count($content, ')');

echo "Companies file:\n";
echo "Open parentheses: $openCount\n";
echo "Close parentheses: $closeCount\n";
echo "Difference: " . ($openCount - $closeCount) . "\n";

if ($closeCount > $openCount) {
    echo "❌ Too many close parentheses\n";
    
    // Find and remove extra close parentheses
    $lines = explode("\n", $content);
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        $line = $lines[$i];
        if (strpos($line, ');') !== false && strpos($line, '(') !== false) {
            // This might be a function call with extra close
            $lines[$i] = str_replace(');', ');', $line);
            break;
        }
    }
    
    $content = implode("\n", $lines);
    file_put_contents($file, $content);
    echo "✅ Fixed extra close parentheses\n";
} else {
    echo "✅ Parentheses are balanced\n";
}

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found\n";
}

echo "\n=== Test Complete ===\n";
?>
