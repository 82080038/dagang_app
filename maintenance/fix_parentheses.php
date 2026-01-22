<?php
/**
 * Fix Unbalanced Parentheses
 */

echo "=== Fixing Unbalanced Parentheses ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Count parentheses
$openCount = substr_count($content, '(');
$closeCount = substr_count($content, ')');

echo "Open parentheses: $openCount\n";
echo "Close parentheses: $closeCount\n";
echo "Difference: " . ($openCount - $closeCount) . "\n";

if ($openCount > $closeCount) {
    echo "❌ Too many open parentheses\n";
} elseif ($closeCount > $openCount) {
    echo "❌ Too many close parentheses\n";
} else {
    echo "✅ Parentheses are balanced\n";
}

// Find lines with parentheses
$lines = explode("\n", $content);
echo "\nLines with parentheses around line 428:\n";
for ($i = max(0, 425); $i < min(count($lines), 435); $i++) {
    $line = $lines[$i];
    if (strpos($line, '(') !== false || strpos($line, ')') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
