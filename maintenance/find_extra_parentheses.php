<?php
/**
 * Find Extra Parentheses in JavaScript
 */

echo "=== Find Extra Parentheses in JavaScript ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Extract JavaScript content
preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $matches);

$jsContent = '';
foreach ($matches[1] as $script) {
    $jsContent .= $script . "\n";
}

echo "JavaScript content length: " . strlen($jsContent) . " characters\n";

// Look for lines with extra closing parentheses
$lines = explode("\n", $jsContent);
$problematicLines = [];

foreach ($lines as $lineNum => $line) {
    // Count parentheses in this line
    $openCount = substr_count($line, '(');
    $closeCount = substr_count($line, ')');
    
    if ($closeCount > $openCount) {
        $problematicLines[] = [
            'line' => $lineNum + 1,
            'content' => trim($line),
            'open' => $openCount,
            'close' => $closeCount,
            'diff' => $closeCount - $openCount
        ];
    }
}

if (!empty($problematicLines)) {
    echo "❌ Found " . count($problematicLines) . " lines with extra closing parentheses:\n\n";
    
    foreach ($problematicLines as $item) {
        echo "Line {$item['line']}: {$item['content']}\n";
        echo "  Open: {$item['open']}, Close: {$item['close']}, Extra: {$item['diff']}\n";
        
        // Check if this is around line 614
        if (abs($item['line'] - 614) <= 5) {
            echo "  ⚠ CLOSE TO LINE 614!\n";
        }
        echo "\n";
    }
} else {
    echo "✅ No lines with extra closing parentheses found\n";
}

// Look for specific patterns that might cause issues
echo "\nChecking for specific problematic patterns:\n";

// Pattern 1: Double closing parentheses
if (preg_match_all('/\)\s*\)/', $jsContent, $matches)) {
    echo "Found " . count($matches[0]) . " instances of double closing parentheses\n";
    
    $jsLines = explode("\n", $jsContent);
    foreach ($jsLines as $lineNum => $line) {
        if (preg_match('/\)\s*\)/', $line)) {
            echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            
            // Check if this is around line 614
            if (abs($lineNum + 1 - 614) <= 5) {
                echo "    ⚠ CLOSE TO LINE 614!\n";
            }
        }
    }
}

// Pattern 2: Function call with extra parentheses
if (preg_match_all('/\w+\([^)]*\)\s*\)/', $jsContent, $matches)) {
    echo "Found " . count($matches[0]) . " function calls with extra parentheses\n";
    
    $jsLines = explode("\n", $jsContent);
    foreach ($jsLines as $lineNum => $line) {
        if (preg_match('/\w+\([^)]*\)\s*\)/', $line)) {
            echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            
            // Check if this is around line 614
            if (abs($lineNum + 1 - 614) <= 5) {
                echo "    ⚠ CLOSE TO LINE 614!\n";
            }
        }
    }
}

// Pattern 3: Extra closing parentheses at end of line
if (preg_match_all('/\)\s*[,;]$/', $jsContent, $matches)) {
    echo "Found " . count($matches[0]) . " lines ending with extra closing parentheses\n";
    
    $jsLines = explode("\n", $jsContent);
    foreach ($jsLines as $lineNum => $line) {
        if (preg_match('/\)\s*[,;]$/', $line)) {
            echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            
            // Check if this is around line 614
            if (abs($lineNum + 1 - 614) <= 5) {
                echo "    ⚠ CLOSE TO LINE 614!\n";
            }
        }
    }
}

echo "\n=== Test Complete ===\n";
?>
