<?php
/**
 * Fix Template Literal and Parentheses
 */

echo "=== Fixing Template Literal and Parentheses ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Replace template literal with regular string
$content = str_replace('.html(\'<i class="bi bi-save me-1"></i> Simpan\');', '.html(\'<i class="bi bi-save me-1"></i> Simpan\');', $content);

// Save the file
file_put_contents($file, $content);

// Count parentheses again
$openCount = substr_count($content, '(');
$closeCount = substr_count($content, ')');

echo "After fix:\n";
echo "Open parentheses: $openCount\n";
echo "Close parentheses: $closeCount\n";
echo "Difference: " . ($openCount - $closeCount) . "\n";

if ($openCount === $closeCount) {
    echo "✅ Parentheses are now balanced\n";
} else {
    echo "❌ Still unbalanced\n";
}

echo "\n=== Test Complete ===\n";
?>
