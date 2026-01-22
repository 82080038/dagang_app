<?php
/**
 * Fix Missing Quote in Badge Class
 */

echo "=== Fix Missing Quote in Badge Class ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix missing quote in badge class
$patterns = [
    '/bg-success>/' => 'bg-success">',
    '/bg-error>/' => 'bg-error">',
    '/bg-info>/' => 'bg-info">',
    '/bg-warning>/' => 'bg-warning">',
    '/bg-primary>/' => 'bg-primary">',
    '/bg-secondary>/' => 'bg-secondary">',
    '/bg-danger>/' => 'bg-danger">',
    '/bg-light>/' => 'bg-light">',
    '/bg-dark>/' => 'bg-dark">',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed missing quote in badge class\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
