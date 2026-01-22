<?php
/**
 * Fix All Remaining Missing Commas in AJAX Objects
 */

echo "=== Fix All Remaining Missing Commas in AJAX Objects ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix missing commas in all AJAX objects with more specific patterns
$patterns = [
    '/}\s*error:\s*function/s' => '}, error: function',
    '/}\s*complete:\s*function/s' => '}, complete: function',
    '/}\s*beforeSend:\s*function/s' => '}, beforeSend: function',
    '/}\s*success:\s*function/s' => '}, success: function',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all remaining missing commas in AJAX objects\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
