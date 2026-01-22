<?php
/**
 * Fix Success String Literal Issues
 */

echo "=== Fix Success String Literal Issues ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix all showNotification calls with 'success' to use double quotes
$patterns = [
    "/showNotification\(response\.message, 'success'\)/" => 'showNotification(response.message, "success")',
    "/showNotification\(.*?, 'success'\)/" => 'showNotification($1, "success")',
    "/showNotification\(.*?, 'error'\)/" => 'showNotification($1, "error")',
    "/showNotification\(.*?, 'info'\)/" => 'showNotification($1, "info")',
    "/showNotification\(.*?, 'warning'\)/" => 'showNotification($1, "warning")',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed showNotification calls to use double quotes\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
