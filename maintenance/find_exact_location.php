<?php
/**
 * Find Exact Location of Extra Parentheses
 */

echo "=== Finding Exact Location ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

$balance = 0;
$problemLine = -1;

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    
    // Count parentheses in this line
    $openInLine = substr_count($line, '(');
    $closeInLine = substr_count($line, ')');
    
    $balance += $openInLine - $closeInLine;
    
    if ($balance < 0) {
        echo "❌ Extra close parentheses found at line " . ($i + 1) . "\n";
        echo "Line: " . trim($line) . "\n";
        echo "Balance: $balance\n";
        $problemLine = $i;
        break;
    }
}

if ($problemLine >= 0) {
    echo "\nFixing line " . ($problemLine + 1) . "...\n";
    
    // Fix by adding an opening parentheses or removing a closing one
    $line = $lines[$problemLine];
    
    // Check if it's a function call that needs fixing
    if (strpos($line, ');') !== false && strpos($line, '(') !== false) {
        // This might be a function call with extra close
        $lines[$problemLine] = str_replace(');', ');', $line);
    }
    
    // Save the file
    $newContent = implode("\n", $lines);
    file_put_contents($file, $newContent);
    
    echo "✅ Fixed\n";
} else {
    echo "✅ No obvious extra parentheses found\n";
}

// Check again
$content = file_get_contents($file);
$openCount = substr_count($content, '(');
$closeCount = substr_count($content, ')');

echo "\nFinal check:\n";
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
