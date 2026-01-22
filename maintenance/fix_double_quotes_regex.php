<?php
/**
 * Fix All Double Quotes with Regex
 */

echo "=== Fix All Double Quotes with Regex ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix all instances of double quotes around success/error/info/warning
$content = preg_replace('/""(success|error|info|warning)""/', '"$1"', $content);

// Fix the specific badge class issue
$content = preg_replace('/bg-"(success|error|info|warning|primary|secondary|danger|warning|info|light|dark)""/', 'bg-$1', $content);

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all double quotes with regex\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
