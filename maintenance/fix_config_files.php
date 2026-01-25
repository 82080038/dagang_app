<?php
/**
 * Fix Config Files - Apply Protection to Constants
 * 
 * This script automatically adds protection against duplicate constant definitions
 * to all configuration files in the application.
 */

echo "🔧 APPLYING FIXES TO CONFIG FILES\n";
echo "=================================\n\n";

$baseDir = __DIR__ . '/..';

// Files to fix
$configFiles = [
    'app/config/app.php',
    'app/config/database.php',
    'app/config/database_multi.php'
];

$fixesApplied = 0;

foreach ($configFiles as $file) {
    $filePath = $baseDir . '/' . $file;
    
    if (!file_exists($filePath)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    echo "📝 Processing: $file\n";
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Pattern to find unprotected define() statements
    $pattern = "/^(\s*)define\s*\(\s*['\"]([^'\"]+)['\"]\s*,/m";
    
    // Replace with protected version
    $content = preg_replace_callback($pattern, function($matches) {
        $indent = $matches[1];
        $constantName = $matches[2];
        
        // Skip if already protected
        if (strpos($matches[0], '!defined') !== false) {
            return $matches[0];
        }
        
        return $indent . "if (!defined('$constantName')) {\n" . 
               $indent . "    define('$constantName',";
    }, $content);
    
    // Need to close the if blocks - add closing braces
    $lines = explode("\n", $content);
    $newLines = [];
    $openIfs = 0;
    
    foreach ($lines as $line) {
        $newLines[] = $line;
        
        // Count opening if blocks
        if (preg_match("/if\s*\(\s*!defined\s*\(/", $line)) {
            $openIfs++;
        }
        
        // Count define statements inside if blocks
        if ($openIfs > 0 && preg_match("/define\s*\(/", $line) && !preg_match("/if\s*\(/", $line)) {
            // Find the end of this define statement (semicolon)
            if (strpos($line, ';') !== false) {
                $newLines[] = str_repeat(' ', 4) . "}";
                $openIfs--;
            }
        }
    }
    
    $content = implode("\n", $newLines);
    
    // Save changes if different
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "  ✅ Protection added\n";
        $fixesApplied++;
    } else {
        echo "  ℹ️  Already protected or no changes needed\n";
    }
}

echo "\n📊 SUMMARY:\n";
echo "Files processed: " . count($configFiles) . "\n";
echo "Fixes applied: $fixesApplied\n\n";

echo "🎯 NEXT STEPS:\n";
echo "1. Test application: php -f index.php\n";
echo "2. Check for remaining warnings\n";
echo "3. Run maintenance scripts to verify\n\n";

echo "✅ CONFIG FILE FIXES COMPLETED\n";
?>
