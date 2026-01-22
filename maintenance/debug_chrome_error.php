<?php
/**
 * Debug Chrome JavaScript Error Line 594
 */

echo "=== Debug Chrome JavaScript Error Line 594 ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Get line 594
$lines = explode("\n", $content);
$line594 = isset($lines[593]) ? $lines[593] : '';

echo "Line 594 content: " . trim($line594) . "\n";

// Check for any JavaScript around line 594
echo "\nContext around line 594:\n";
for ($i = max(0, 593 - 5); $i < min(count($lines), 593 + 5); $i++) {
    $lineNum = $i + 1;
    $lineContent = $lines[$i];
    echo "Line $lineNum: " . trim($lineContent) . "\n";
}

// Check for any success identifier issues
echo "\nChecking for 'success' identifier issues:\n";

// Look for success without quotes
if (preg_match('/\bsuccess\b(?!.*\')/', $content)) {
    echo "❌ Found 'success' identifier without quotes\n";
    
    // Find lines with unquoted success
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (preg_match('/\bsuccess\b(?!.*\')/', $line)) {
            echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "✅ No unquoted 'success' identifiers found\n";
}

// Check for any syntax issues around line 594
echo "\nChecking for syntax issues around line 594:\n";
$context = implode("\n", array_slice($lines, 590, 10));
echo "Context:\n" . $context . "\n";

// Check for any malformed JavaScript
$jsIssues = [];

// Check for missing semicolons
if (preg_match('/\{[^}]*$/', $context)) {
    $jsIssues[] = "Possible missing closing brace";
}

// Check for extra commas
if (preg_match('/,\s*\}/', $context)) {
    $jsIssues[] = "Possible trailing comma before closing brace";
}

// Check for missing quotes
if (preg_match('/===\s*success(?!.*\')/', $context)) {
    $jsIssues[] = "Missing quotes around 'success' string";
}

if (!empty($jsIssues)) {
    echo "❌ JavaScript syntax issues found:\n";
    foreach ($jsIssues as $issue) {
        echo "  - $issue\n";
    }
} else {
    echo "✅ No obvious JavaScript syntax issues\n";
}

echo "\n=== Test Complete ===\n";
?>
