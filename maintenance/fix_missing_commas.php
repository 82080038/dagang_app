<?php
/**
 * Fix All Missing Commas in AJAX Objects
 */

echo "=== Fix All Missing Commas in AJAX Objects ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix missing commas in AJAX objects
$patterns = [
    '/beforeSend:\s*function\([^)]*\)\s*\{[^}]*\}\s*(?=success:|error:|complete:)/s' => 'beforeSend: function($0) { $1 },',
    '/success:\s*function\([^)]*\)\s*\{[^}]*\}\s*(?=error:|complete:)/s' => 'success: function($0) { $1 },',
    '/error:\s*function\([^)]*\)\s*\{[^}]*\}\s*(?=complete:)/s' => 'error: function($0) { $1 },',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all missing commas in AJAX objects\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
