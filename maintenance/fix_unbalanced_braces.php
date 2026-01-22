<?php
/**
 * Fix Unbalanced Braces and Parentheses
 */

echo "=== Fix Unbalanced Braces and Parentheses ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Count braces and parentheses
$braceOpen = substr_count($content, '{');
$braceClose = substr_count($content, '}');
$parenOpen = substr_count($content, '(');
$parenClose = substr_count($content, ')');

echo "Before fix:\n";
echo "Open braces: $braceOpen\n";
echo "Close braces: $braceClose\n";
echo "Open parentheses: $parenOpen\n";
echo "Close parentheses: $parenClose\n";
echo "Brace difference: " . ($braceOpen - $braceClose) . "\n";
echo "Parentheses difference: " . ($parenOpen - $parenClose) . "\n";

// Fix unbalanced braces
if ($braceClose > $braceOpen) {
    echo "❌ Too many close braces - removing excess\n";
    
    $excess = $braceClose - $braceOpen;
    for ($i = 0; $i < $excess; $i++) {
        $lastPos = strrpos($content, '}');
        if ($lastPos !== false) {
            $content = substr($content, 0, $lastPos) . substr($content, $lastPos + 1);
        }
    }
    
    echo "✅ Removed $excess excess close braces\n";
} elseif ($braceOpen > $braceClose) {
    echo "❌ Too many open braces - adding missing\n";
    
    $missing = $braceOpen - $braceClose;
    for ($i = 0; $i < $missing; $i++) {
        $content .= '}';
    }
    
    echo "✅ Added $missing missing close braces\n";
} else {
    echo "✅ Braces are balanced\n";
}

// Fix unbalanced parentheses
if ($parenClose > $parenOpen) {
    echo "❌ Too many close parentheses - removing excess\n";
    
    $excess = $parenClose - $parenOpen;
    for ($i = 0; $i < $excess; $i++) {
        $lastPos = strrpos($content, ')');
        if ($lastPos !== false) {
            $content = substr($content, 0, $lastPos) . substr($content, $lastPos + 1);
        }
    }
    
    echo "✅ Removed $excess excess close parentheses\n";
} elseif ($parenOpen > $parenClose) {
    echo "❌ Too many open parentheses - adding missing\n";
    
    $missing = $parenOpen - $parenClose;
    for ($i = 0; $i < $missing; $i++) {
        $content .= ')';
    }
    
    echo "✅ Added $missing missing close parentheses\n";
} else {
    echo "✅ Parentheses are balanced\n";
}

// Save the file
file_put_contents($file, $content);

// Count again
$content = file_get_contents($file);
$braceOpen = substr_count($content, '{');
$braceClose = substr_count($content, '}');
$parenOpen = substr_count($content, '(');
$parenClose = substr_count($content, ')');

echo "\nAfter fix:\n";
echo "Open braces: $braceOpen\n";
echo "Close braces: $braceClose\n";
echo "Open parentheses: $parenOpen\n";
echo "Close parentheses: $parenClose\n";
echo "Brace difference: " . ($braceOpen - $braceClose) . "\n";
echo "Parentheses difference: " . ($parenOpen - $parenClose) . "\n";

if ($braceOpen === $braceClose && $parenOpen === $parenClose) {
    echo "✅ All braces and parentheses are now balanced\n";
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
