<?php
/**
 * Fix All Extra Closing Parentheses
 */

echo "=== Fix All Extra Closing Parentheses ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix extra closing parentheses in specific patterns
$patterns = [
    '/\}\s*\)\s*\}/' => '}}',  // Fix })})
    '/\)\s*\)\s*}/' => ')}',   // Fix ))}  
    '/\)\s*\)\s*,/' => '),',   // Fix )),
    '/\)\s*\)\s*;/' => ');',   // Fix ));
    '/beforeSend:\s*function\(\)\s*\)\s*\{/' => 'beforeSend: function() {',  // Fix beforeSend: function() {)
    '/success:\s*function\([^)]*\)\s*\)\s*\{/' => 'success: function($1) {',  // Fix success: function() {)
    '/error:\s*function\([^)]*\)\s*\)\s*\{/' => 'error: function($1) {',  // Fix error: function() {)
    '/complete:\s*function\([^)]*\)\s*\)\s*\{/' => 'complete: function($1) {',  // Fix complete: function() {)
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all extra closing parentheses\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
