<?php
/**
 * Test Companies Page Access
 * Test accessing the companies page directly
 */

echo "=== Test Companies Page Access ===\n\n";

// Test 1: Check if companies page file exists
$companiesPage = __DIR__ . '/app/views/companies/index.php';
if (file_exists($companiesPage)) {
    echo "✅ Companies page file exists\n";
    
    // Test 2: Check for syntax errors
    $output = shell_exec('E:\xampp\php\php.exe -l ' . $companiesPage . ' 2>&1');
    if (strpos($output, 'Parse error') === false && strpos($output, 'Fatal error') === false) {
        echo "✅ No syntax errors in companies page\n";
    } else {
        echo "❌ Syntax errors found in companies page:\n";
        echo $output . "\n";
    }
} else {
    echo "❌ Companies page file not found\n";
}

// Test 3: Check if required models exist
$requiredFiles = [
    __DIR__ . '/app/controllers/CompanyController.php',
    __DIR__ . '/app/models/Company.php',
    __DIR__ . '/app/models/Address.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ " . basename($file) . " exists\n";
    } else {
        echo "❌ " . basename($file) . " missing\n";
    }
}

echo "\n=== Test Selesai! ===\n";
echo "✅ Companies page access test completed\n";
?>
