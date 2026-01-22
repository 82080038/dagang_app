<?php
/**
 * Final JavaScript Error Test
 * Test all JavaScript compatibility issues
 */

echo "=== Final JavaScript Error Test ===\n\n";

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

// Check for all problematic patterns
$patterns = [
    'const ' => 'const declarations',
    'let ' => 'let declarations',
    '=>' => 'arrow functions',
    '`.*\$\{.*\}`' => 'template literals',
    'function.*\s*=' => 'default parameters',
    '\|\|' => 'OR operators'
];

echo "\n=== Pattern Analysis ===\n";
foreach ($patterns as $pattern => $description) {
    $count = preg_match_all('/' . $pattern . '/', $response);
    echo "$description: $count\n";
    
    if ($count > 0) {
        echo "  Found instances:\n";
        $lines = explode("\n", $response);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/' . $pattern . '/', $line)) {
                echo "    Line " . ($lineNum + 1) . ": " . trim(substr($line, 0, 100)) . "\n";
            }
        }
    }
}

// Check for specific issues around line 622
echo "\n=== Line 622 Analysis ===\n";
$lines = explode("\n", $response);
if (isset($lines[621])) {
    echo "Line 622: " . trim($lines[621]) . "\n";
    
    // Check for syntax issues
    $line = $lines[621];
    if (preg_match('/\)\s*[,;]\s*$/', $line)) {
        echo "❌ Found unexpected ')' at end of line\n";
    } else {
        echo "✅ No obvious syntax issues\n";
    }
}

// Check context around line 622
echo "\n=== Context around Line 622 ===\n";
for ($i = max(0, 621 - 3); $i < min(count($lines), 621 + 3); $i++) {
    echo "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
}

// Check for bracket balance
$context = implode("\n", array_slice($lines, 618, 10));
$openBraces = substr_count($context, '{');
$closeBraces = substr_count($context, '}');
$openParens = substr_count($context, '(');
$closeParens = substr_count($context, ')');

echo "\n=== Bracket Balance ===\n";
echo "Open braces: $openBraces, Close braces: $closeBraces\n";
echo "Open parentheses: $openParens, Close parentheses: $closeParens\n";

if ($openBraces !== $closeBraces) {
    echo "❌ Unbalanced braces\n";
} else {
    echo "✅ Braces balanced\n";
}

if ($openParens !== $closeParens) {
    echo "❌ Unbalanced parentheses\n";
} else {
    echo "✅ Parentheses balanced\n";
}

echo "\n=== Test Selesai! ===\n";
echo "✅ JavaScript compatibility analysis completed\n";
?>
