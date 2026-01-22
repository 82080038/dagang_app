<?php
/**
 * Fix All showNotification Calls
 */

echo "=== Fix All showNotification Calls ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix all showNotification calls with missing first parameter
$patterns = [
    '/showNotification\(\s*,\s*"error"\s*\)/' => 'showNotification("Terjadi kesalahan", "error")',
    '/showNotification\(\s*,\s*"success"\s*\)/' => 'showNotification("Berhasil", "success")',
    '/showNotification\(\s*,\s*"info"\s*\)/' => 'showNotification("Info", "info")',
    '/showNotification\(\s*,\s*"warning"\s*\)/' => 'showNotification("Peringatan", "warning")',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all showNotification calls with missing parameters\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
