<?php
/**
 * Fix Double Quotes Issue
 */

echo "=== Fix Double Quotes Issue ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix double quotes that are doubled
$patterns = [
    '/""success""/' => '"success"',
    '/""error""/' => '"error"',
    '/""info""/' => '"info"',
    '/""warning""/' => '"warning"',
    '/bg-"success""/' => 'bg-success',
];

foreach ($patterns as $pattern => $replacement) {
    $content = str_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed double quotes issue\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
