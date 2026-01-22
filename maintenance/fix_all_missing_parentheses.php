<?php
/**
 * Fix All Missing Parentheses Aggressively
 */

echo "=== Fix All Missing Parentheses Aggressively ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Fix all function calls that are missing closing parentheses
$patterns = [
    // Fix function calls
    '/(\w+)\(([^)]*)\s*$/' => '$1($2)',
    '/(\w+)\(([^)]*)\s*([;,\)])/' => '$1($2)$3',
    '/(\w+)\(([^)]*)\s*([;,\)])/' => '$1($2)$3',
    
    // Fix method calls
    '/(\.\w+)\(([^)]*)\s*$/' => '$1($2)',
    '/(\.\w+)\(([^)]*)\s*([;,\)])/' => '$1($2)$3',
    
    // Fix jQuery selectors
    '/\$(\'[^\']*\'\)\.(\w+)\(([^)]*)\s*$/' => '$(\'$1\').$2($3)',
    '/\$(\'[^\']*\'\)\.(\w+)\(([^)]*)\s*([;,\)])/' => '$(\'$1\').$2($3)$4',
    
    // Fix showNotification calls
    '/showNotification\(([^,]*),\s*([^)]*)\s*$/' => 'showNotification($1, $2)',
    '/showNotification\(([^,]*),\s*([^)]*)\s*([;,\)])/' => 'showNotification($1, $2)$3',
    
    // Fix setTimeout calls
    '/setTimeout\(function\(\)\s*\{([^}]*)\}\s*,\s*(\d+)\s*$/' => 'setTimeout(function() {$1}, $2)',
    '/setTimeout\(function\(\)\s*\{([^}]*)\}\s*,\s*(\d+)\s*([;,\)])/' => 'setTimeout(function() {$1}, $2)$3',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Fix specific problematic lines
$content = preg_replace('/loadCompanies\(\s*$/', 'loadCompanies()', $content);
$content = preg_replace('/loadProvinces\(\s*$/', 'loadProvinces()', $content);
$content = preg_replace('/submitCompanyForm\(\s*$/', 'submitCompanyForm()', $content);
$content = preg_replace('/showNotification\(([^,]*),\s*([^)]*)\s*$/', 'showNotification($1, $2)', $content);

// Save the file
file_put_contents($file, $content);

echo "✅ Fixed all missing parentheses aggressively\n";

// Test syntax
$output = shell_exec('E:\xampp\php\php.exe -l "' . $file . '"');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ PHP syntax check passed\n";
} else {
    echo "❌ PHP syntax error found:\n$output\n";
}

echo "\n=== Test Complete ===\n";
?>
