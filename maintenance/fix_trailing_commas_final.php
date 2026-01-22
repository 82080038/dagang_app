<?php
/**
 * Fix All Trailing Commas in AJAX
 */

echo "=== Fix All Trailing Commas in AJAX ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix trailing commas in AJAX objects
$content = preg_replace('/},\s*error:/m', '}, error:', $content);
$content = preg_replace('/},\s*complete:/m', '}, complete:', $content);
$content = preg_replace('/},\s*beforeSend:/m', '}, beforeSend:', $content);
$content = preg_replace('/},\s*success:/m', '}, success:', $content);

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all trailing commas in AJAX objects\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
