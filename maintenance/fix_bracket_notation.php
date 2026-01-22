<?php
/**
 * Fix Bracket Notation in showNotification
 */

echo "=== Fixing Bracket Notation ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Replace bracket notation with conditional checks
$patterns = [
    'Toast[type]' => 'type === "success" ? Toast.success : type === "error" ? Toast.error : type === "warning" ? Toast.warning : Toast.info',
    'Notification[type]' => 'type === "success" ? Notification.success : type === "error" ? Notification.error : type === "warning" ? Notification.warning : Notification.info'
];

foreach ($patterns as $pattern => $replacement) {
    $content = str_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Bracket notation fixed\n";

// Test syntax
$output = shell_exec('php -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
