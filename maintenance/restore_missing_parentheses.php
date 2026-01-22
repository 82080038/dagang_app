<?php
/**
 * Restore Missing Parentheses and Brackets
 */

echo "=== Restore Missing Parentheses and Brackets ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Count current parentheses and brackets
$openParen = substr_count($content, '(');
$closeParen = substr_count($content, ')');
$openBrace = substr_count($content, '{');
$closeBrace = substr_count($content, '}');
$openBracket = substr_count($content, '[');
$closeBracket = substr_count($content, ']');

echo "Current state:\n";
echo "Open parentheses: $openParen\n";
echo "Close parentheses: $closeParen\n";
echo "Open braces: $openBrace\n";
echo "Close braces: $closeBrace\n";
echo "Open brackets: $openBracket\n";
echo "Close brackets: $closeBracket\n";

// Calculate missing
$missingParen = $openParen - $closeParen;
$missingBrace = $openBrace - $closeBrace;
$missingBracket = $openBracket - $closeBracket;

echo "\nMissing:\n";
echo "Missing parentheses: $missingParen\n";
echo "Missing braces: $missingBrace\n";
echo "Missing brackets: $missingBracket\n";

// Add missing closing parentheses at the end of the file
if ($missingParen > 0) {
    for ($i = 0; $i < $missingParen; $i++) {
        $content .= ')';
    }
    echo "✅ Added $missingParen closing parentheses\n";
}

// Add missing closing braces at the end of the file
if ($missingBrace > 0) {
    for ($i = 0; $i < $missingBrace; $i++) {
        $content .= '}';
    }
    echo "✅ Added $missingBrace closing braces\n";
}

// Add missing closing brackets at the end of the file
if ($missingBracket > 0) {
    for ($i = 0; $i < $missingBracket; $i++) {
        $content .= ']';
    }
    echo "✅ Added $missingBracket closing brackets\n";
}

// Save the file
file_put_contents($file, $content);

echo "\n=== Test Complete ===\n";
?>
