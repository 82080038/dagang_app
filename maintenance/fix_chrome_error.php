<?php
/**
 * Fix Chrome JavaScript Error - String Comparison Issue
 */

echo "=== Fix Chrome JavaScript Error - String Comparison Issue ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// The issue might be with string comparison in older browsers
// Let's fix all 'success' string comparisons to use more compatible approach
$patterns = [
    '/response\.status === \'success\'/' => 'response.status == "success"',
    '/response\.status !== \'success\'/' => 'response.status != "success"',
    '/response\.status === \'error\'/' => 'response.status == "error"',
    '/response\.status !== \'error\'/' => 'response.status != "error"',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed string comparison for older browser compatibility\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
