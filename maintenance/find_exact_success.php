<?php
/**
 * Find Exact Success Identifier Location
 */

echo "=== Find Exact Success Identifier Location ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Find all success identifiers and their locations
$lines = explode("\n", $content);
$successLines = [];

foreach ($lines as $lineNum => $line) {
    if (preg_match('/\bsuccess\b/', $line)) {
        $successLines[] = [
            'line' => $lineNum + 1,
            'content' => trim($line),
            'context' => substr($line, max(0, strpos($line, 'success') - 20), 40)
        ];
    }
}

echo "Found " . count($successLines) . " lines with 'success' identifier:\n\n";

foreach ($successLines as $item) {
    echo "Line {$item['line']}: {$item['content']}\n";
    echo "  Context: ...{$item['context']}...\n";
    
    // Check if it's problematic
    if (preg_match('/\bsuccess\b(?!.*\')/', $item['content']) && !preg_match('/success:/', $item['content'])) {
        echo "  ❌ PROBLEMATIC: Unquoted success identifier\n";
    } else {
        echo "  ✅ OK: Properly quoted or object key\n";
    }
    echo "\n";
}

// Check specifically around line 594
echo "Checking around line 594:\n";
for ($i = max(0, 593 - 5); $i < min(count($lines), 593 + 5); $i++) {
    $lineNum = $i + 1;
    $lineContent = $lines[$i];
    
    if (preg_match('/\bsuccess\b/', $lineContent)) {
        echo "Line $lineNum: " . trim($lineContent) . " ⚠ CONTAINS SUCCESS\n";
    } else {
        echo "Line $lineNum: " . trim($lineContent) . "\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
