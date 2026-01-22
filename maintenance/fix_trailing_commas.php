<?php
/**
 * Fix All Trailing Commas for IE11 Compatibility
 */

echo "=== Fixing All Trailing Commas for IE11 ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix trailing commas in AJAX objects
$patterns = [
    '/},\s*$/m' => '}',  // Remove trailing comma before closing brace
    '/},\s*error:/m' => '}, error:',  // Fix comma before error function
    '/},\s*success:/m' => '}, success:',  // Fix comma before success function
    '/},\s*complete:/m' => '}, complete:',  // Fix comma before complete function
    '/},\s*beforeSend:/m' => '}, beforeSend:',  // Fix comma before beforeSend function
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ All trailing commas fixed\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

// Count trailing commas
$trailingCommas = preg_match_all('/},\s*$/', $content);
echo "Remaining trailing commas: $trailingCommas\n";

echo "\n=== Test Complete ===\n";
?>
