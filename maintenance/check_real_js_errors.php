<?php
/**
 * Check for Real JavaScript Syntax Errors
 */

echo "=== Check for Real JavaScript Syntax Errors ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Extract JavaScript content
preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $matches);

$jsContent = '';
foreach ($matches[1] as $script) {
    $jsContent .= $script . "\n";
}

echo "JavaScript content length: " . strlen($jsContent) . " characters\n";

// Check for actual syntax errors that would cause "Unexpected identifier 'success'"
$problematicPatterns = [
    '/\bsuccess\b(?!\s*[:=])/' => 'Unquoted success identifier',
    '/\berror\b(?!\s*[:=])/' => 'Unquoted error identifier',
    '/\binfo\b(?!\s*[:=])/' => 'Unquoted info identifier',
    '/\bwarning\b(?!\s*[:=])/' => 'Unquoted warning identifier',
];

$foundIssues = false;
foreach ($problematicPatterns as $pattern => $description) {
    if (preg_match_all($pattern, $jsContent, $matches)) {
        echo "❌ Found $description: " . count($matches[0]) . " times\n";
        $foundIssues = true;
        
        // Show specific lines
        $lines = explode("\n", $jsContent);
        foreach ($lines as $lineNum => $line) {
            if (preg_match($pattern, $line)) {
                echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}

if (!$foundIssues) {
    echo "✅ No problematic identifiers found\n";
}

// Check for specific syntax that would cause "Unexpected identifier 'success'"
echo "\nChecking for specific syntax that would cause 'Unexpected identifier success':\n";

// Look for success without quotes in contexts where it would be problematic
if (preg_match_all('/\{[^}]*\bsuccess\b[^}]*\}/', $jsContent, $matches)) {
    echo "Found success in object contexts: " . count($matches[0]) . " times\n";
    foreach ($matches[0] as $match) {
        echo "  " . trim($match) . "\n";
    }
}

// Check for function calls with unquoted success
if (preg_match_all('/\w+\([^)]*\bsuccess\b[^)]*\)/', $jsContent, $matches)) {
    echo "Found success in function calls: " . count($matches[0]) . " times\n";
    foreach ($matches[0] as $match) {
        echo "  " . trim($match) . "\n";
    }
}

// Check for variable assignments with unquoted success
if (preg_match_all('/\w+\s*=\s*\bsuccess\b/', $jsContent, $matches)) {
    echo "Found success in assignments: " . count($matches[0]) . " times\n";
    foreach ($matches[0] as $match) {
        echo "  " . trim($match) . "\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
