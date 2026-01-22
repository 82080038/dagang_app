<?php
/**
 * Find and Remove Extra Parentheses
 */

echo "=== Finding Extra Parentheses ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Find the line with extra parentheses
for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    
    // Check for suspicious patterns
    if (strpos($line, '.html(') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
        
        // Count parentheses in this line
        $openInLine = substr_count($line, '(');
        $closeInLine = substr_count($line, ')');
        echo "  Open: $openInLine, Close: $closeInLine\n";
        
        if ($closeInLine > $openInLine) {
            echo "  ❌ This line has too many close parentheses\n";
            
            // Fix by removing one close parentheses
            $fixedLine = str_replace(');', ');', $line);
            $lines[$i] = $fixedLine;
            
            // Save the file
            $newContent = implode("\n", $lines);
            file_put_contents($file, $newContent);
            
            echo "  ✅ Fixed by removing extra parentheses\n";
            break;
        }
    }
}

// Check again
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

echo "\n=== Test Complete ===\n";
?>
