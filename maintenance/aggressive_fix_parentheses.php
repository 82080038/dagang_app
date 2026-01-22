<?php
/**
 * Aggressive Fix for All Extra Parentheses
 */

echo "=== Aggressive Fix for All Extra Parentheses ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix all the specific problematic patterns found
$fixes = [
    // Fix beforeSend: function() {)
    '/beforeSend:\s*function\(\s*\)\s*\)\s*\{/' => 'beforeSend: function() {',
    
    // Fix ) {  },success: function(response) {
    '/\)\s*\{\s*\}\s*,\s*success:\s*function\([^)]*\)\s*\{/' => '}, success: function($1) {',
    
    // Fix ) {  },complete: function() {
    '/\)\s*\{\s*\}\s*,\s*complete:\s*function\([^)]*\)\s*\{/' => '}, complete: function($1) {',
    
    // Fix }, error: function(xhr, status, error) {xhr, status, error) {
    '/\},\s*error:\s*function\([^)]*\)\s*\{[^}]*xhr,\s*status,\s*error\)\s*\{/' => '}, error: function(xhr, status, error) {',
    
    // Fix setTimeout with extra parentheses
    '/setTimeout\(function\(\)\s*\{\s*[^}]*\}\s*,\s*\d+\)\s*\)/' => 'setTimeout(function() {$1}, $2)',
];

foreach ($fixes as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Fix all instances of double closing parentheses
$content = preg_replace('/\)\s*\)/', ')', $content);

// Fix all instances of extra closing parentheses at end of lines
$content = preg_replace('/\)\s*([,;])/', '$1', $content);

// Save the file
file_put_contents($file, $content);

echo "✅ Aggressively fixed all extra parentheses\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
