<?php
/**
 * Apply Protection to All Constants
 * 
 * This script adds if (!defined()) protection to all constants
 * in configuration files to prevent duplicate definition warnings.
 */

echo "🛡️ APPLYING PROTECTION TO ALL CONSTANTS\n";
echo "======================================\n\n";

$baseDir = __DIR__ . '/..';

// Files that need protection
$filesToProtect = [
    'app/config/app.php',
    'app/config/database_multi.php'
];

$totalFixes = 0;

foreach ($filesToProtect as $file) {
    $filePath = $baseDir . '/' . $file;
    
    if (!file_exists($filePath)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    echo "📝 Processing: $file\n";
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Add protection to all define statements
    $lines = explode("\n", $content);
    $newLines = [];
    $inDefine = false;
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        
        // Skip comments and empty lines
        if (empty($trimmedLine) || strpos($trimmedLine, '//') === 0 || strpos($trimmedLine, '/*') === 0 || strpos($trimmedLine, '*') === 0) {
            $newLines[] = $line;
            continue;
        }
        
        // Check if this line has a define statement
        if (preg_match("/^(\s*)define\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*(.+);/", $line, $matches)) {
            $indent = $matches[1];
            $constantName = $matches[2];
            $constantValue = $matches[3];
            
            // Check if already protected
            if (strpos($line, '!defined') !== false) {
                $newLines[] = $line;
                continue;
            }
            
            // Add protection
            $newLines[] = $indent . "if (!defined('$constantName')) {";
            $newLines[] = $indent . "    define('$constantName', $constantValue";
            $inDefine = true;
            continue;
        }
        
        // Handle multi-line define statements
        if ($inDefine) {
            $newLines[] = $line;
            
            // Check if this line ends the define statement
            if (strpos($line, ';') !== false) {
                $newLines[] = str_repeat(' ', 4) . "}";
                $inDefine = false;
            }
            continue;
        }
        
        $newLines[] = $line;
    }
    
    $content = implode("\n", $newLines);
    
    // Save changes
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "  ✅ Protection added successfully\n";
        $totalFixes++;
    } else {
        echo "  ℹ️  No changes needed\n";
    }
}

echo "\n📊 SUMMARY:\n";
echo "Files processed: " . count($filesToProtect) . "\n";
echo "Total fixes applied: $totalFixes\n\n";

echo "🎯 TESTING APPLICATION:\n";
echo "Please test the application now:\n";
echo "1. Open: http://localhost/dagang\n";
echo "2. Check for any remaining warnings\n";
echo "3. Test all major features\n\n";

echo "✅ PROTECTION APPLIED SUCCESSFULLY\n";
?>
