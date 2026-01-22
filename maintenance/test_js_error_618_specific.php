<?php
/**
 * Test JavaScript Error at Line 618 - Specific Check
 * Test for the exact error mentioned by user
 */

echo "=== Test JavaScript Error at Line 618 - Specific Check ===\n\n";

// Get the page content
$url = 'http://localhost/dagang/index.php?page=companies';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Failed to fetch page content\n";
    exit(1);
}

echo "✅ Page content fetched successfully\n";

// Extract lines around line 618
$lines = explode("\n", $response);
$line618 = isset($lines[617]) ? $lines[617] : ''; // 0-based index

echo "\nLine 618 content:\n";
echo "Line 618: " . trim($line618) . "\n";

// Check for specific issues in line 618
$issues = [];

// Check for unexpected token ')'
if (preg_match('/\)\s*[,;]\s*$/', $line618)) {
    $issues[] = "Found unexpected ')' at end of line";
}

// Check for showNotification call issues
if (strpos($line618, 'showNotification') !== false) {
    echo "⚠ Found showNotification call in line 618\n";
    
    // Check the function call syntax
    if (preg_match('/showNotification\([^)]*\)/', $line618)) {
        echo "✅ showNotification syntax looks correct\n";
    } else {
        $issues[] = "showNotification syntax might be incorrect";
    }
}

// Check for any malformed JavaScript around this line
$contextLines = [];
for ($i = max(0, 617 - 3); $i < min(count($lines), 617 + 3); $i++) {
    $contextLines[] = ($i + 1) . ": " . trim($lines[$i]);
}

echo "\nContext around line 618:\n";
foreach ($contextLines as $contextLine) {
    echo $contextLine . "\n";
}

// Check for any unclosed brackets or parentheses in the context
$context = implode("\n", array_slice($lines, 614, 10));
$openBraces = substr_count($context, '{');
$closeBraces = substr_count($context, '}');
$openParens = substr_count($context, '(');
$closeParens = substr_count($context, ')');

echo "\nBracket/parenthesis balance check:\n";
echo "Open braces: $openBraces, Close braces: $closeBraces\n";
echo "Open parentheses: $openParens, Close parentheses: $closeParens\n";

if ($openBraces !== $closeBraces) {
    $issues[] = "Unbalanced braces in context";
}

if ($openParens !== $closeParens) {
    $issues[] = "Unbalanced parentheses in context";
}

// Check for any const or let declarations that might cause issues
if (preg_match('/\b(const|let)\s+\w+\s*=/', $context)) {
    $issues[] = "Found const/let declarations that might not work in older browsers";
}

// Check for any template literals
if (preg_match('/`.*\$\{.*\}`/', $context)) {
    $issues[] = "Found template literals that might not work in older browsers";
}

// Check for any arrow functions
if (preg_match('/=>/', $context)) {
    $issues[] = "Found arrow functions that might not work in older browsers";
}

// Check for any default parameters
if (preg_match('/function\s+\w+\s*\([^)]*\s*=/', $context)) {
    $issues[] = "Found default parameters that might not work in older browsers";
}

if (!empty($issues)) {
    echo "\n❌ Issues found:\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
} else {
    echo "\n✅ No obvious issues found in line 618 context\n";
}

// Check the entire file for potential issues
echo "\n=== Full File Analysis ===\n";

// Count const/let declarations
$constCount = preg_match_all('/\bconst\s+\w+\s*=/', $response);
$letCount = preg_match_all('/\blet\s+\w+\s*=/', $response);
echo "Const declarations: $constCount\n";
echo "Let declarations: $letCount\n";

// Count template literals
$templateLiteralCount = preg_match_all('/`.*\$\{.*\}`/', $response);
echo "Template literals: $templateLiteralCount\n";

// Count arrow functions
$arrowFunctionCount = preg_match_all('/=>/', $response);
echo "Arrow functions: $arrowFunctionCount\n";

// Count default parameters
$defaultParamCount = preg_match_all('/function\s+\w+\s*\([^)]*\s*=/', $response);
echo "Default parameters: $defaultParamCount\n";

echo "\n=== Test Selesai! ===\n";
echo "✅ JavaScript error analysis completed\n";
?>
