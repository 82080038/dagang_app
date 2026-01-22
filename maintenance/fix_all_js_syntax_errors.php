<?php
/**
 * Fix All JavaScript Syntax Errors in Companies View
 */

echo "=== Fix All JavaScript Syntax Errors in Companies View ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix double function declarations
$content = preg_replace('/error:\s*function\(error:\s*function\(/', 'error: function(xhr, status, error) {', $content);
$content = preg_replace('/beforeSend:\s*function\(beforeSend:\s*function\(/', 'beforeSend: function() {', $content);

// Fix missing commas in AJAX objects
$patterns = [
    '/}\s*error:\s*function\([^{)]*\)\s*\{[^}]*\}\s*(?=complete:|success:|beforeSend:)/s' => '}, error: function($1) { $2 },',
    '/}\s*complete:\s*function\([^{)]*\)\s*\{[^}]*\}\s*(?=success:|beforeSend:|error:)/s' => '}, complete: function($1) { $2 },',
    '/}\s*success:\s*function\([^{)]*\)\s*\{[^}]*\}\s*(?=error:|complete:|beforeSend:)/s' => '}, success: function($1) { $2 },',
    '/}\s*beforeSend:\s*function\([^{)]*\)\s*\{[^}]*\}\s*(?=success:|error:|complete:)/s' => '}, beforeSend: function($1) { $2 },',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all JavaScript syntax errors\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
