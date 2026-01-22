<?php
/**
 * Fix All Success Identifiers in Companies View
 */

echo "=== Fix All Success Identifiers in Companies View ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix all success identifiers that might be causing issues
$patterns = [
    '/\bsuccess\b(?!\s*:)/' => '"success"',  // Replace any unquoted success with quoted
    '/response\.status == success/' => 'response.status == "success"',  // Fix string comparison
    '/response\.status !== success/' => 'response.status != "success"',  // Fix negative comparison
    '/showNotification\(.*?,\s*\'success\'\s*\)/' => 'showNotification($1, "success")',  // Fix showNotification calls
    '/showNotification\(.*?,\s*\'error\'\s*\)/' => 'showNotification($1, "error")',  // Fix showNotification calls
    '/showNotification\(.*?,\s*\'info\'\s*\)/' => 'showNotification($1, "info")',  // Fix showNotification calls
    '/showNotification\(.*?,\s*\'warning\'\s*\)/' => 'showNotification($1, "warning")',  // Fix showNotification calls
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all success identifiers in companies view\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
