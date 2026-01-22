<?php
/**
 * Test View Include Error
 */

// Define APP_PATH constant
define('APP_PATH', __DIR__ . '/app');

echo "=== Test View Include Error ===\n";

// Test if companies view file can be included without errors
$viewFile = APP_PATH . '/views/companies/index.php';

echo "Testing view file: $viewFile\n";

if (file_exists($viewFile)) {
    echo "✅ View file exists\n";
    
    // Test syntax check
    $output = shell_exec('E:\xampp\php\php.exe -l "' . $viewFile . '"');
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✅ View file syntax check passed\n";
    } else {
        echo "❌ View file syntax error:\n$output\n";
    }
    
    // Test include with data
    echo "\nTesting include with data:\n";
    $testData = [
        'title' => 'Test Companies',
        'companies' => [],
        'pagination' => []
    ];
    
    try {
        // Extract data to variables
        extract($testData);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        include $viewFile;
        
        // Get buffer contents
        $content = ob_get_clean();
        
        echo "✅ View file included successfully\n";
        echo "Content length: " . strlen($content) . " characters\n";
        
        // Check for JavaScript in content
        if (strpos($content, '<script>') !== false) {
            echo "✅ Contains JavaScript\n";
            
            // Check for success identifier in JavaScript
            if (preg_match('/\bsuccess\b/', $content)) {
                echo "❌ Found 'success' identifier in JavaScript\n";
            } else {
                echo "✅ No 'success' identifier found in JavaScript\n";
            }
        } else {
            echo "❌ No JavaScript found in content\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error including view file: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ View file not found: $viewFile\n";
}

echo "\n=== Test Complete ===\n";
?>
