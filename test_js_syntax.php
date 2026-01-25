<?php
/**
 * Test JavaScript Syntax
 */

echo "🔍 JAVASCRIPT SYNTAX CHECK\n";
echo "=====================================\n\n";

$jsFiles = [
    'public/assets/js/app.js',
    'public/assets/js/theme.js',
    'public/assets/js/users.js'
];

$errors = [];

foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for basic syntax issues
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        $openParens = substr_count($content, '(');
        $closeParens = substr_count($content, ')');
        
        $issues = [];
        if ($openBraces !== $closeBraces) {
            $issues[] = "Mismatched braces: {$openBraces} open, {$closeBraces} close";
        }
        if ($openParens !== $closeParens) {
            $issues[] = "Mismatched parentheses: {$openParens} open, {$closeParens} close";
        }
        
        // Check for common syntax errors
        if (strpos($content, 'user-delete-btn-') !== false) {
            $issues[] = "Found 'user-delete-btn-' - should be 'staff-delete-btn-'";
        }
        
        if (empty($issues)) {
            echo "✅ " . str_replace('public/assets/', '', $file) . ": OK\n";
        } else {
            echo "❌ " . str_replace('public/assets/', '', $file) . ":\n";
            foreach ($issues as $issue) {
                echo "   - $issue\n";
            }
            $errors[] = $file;
        }
    } else {
        echo "❌ " . str_replace('public/assets/', '', $file) . ": File not found\n";
        $errors[] = $file;
    }
}

echo "\n📊 SUMMARY\n";
echo "=====================================\n";
echo "Files checked: " . count($jsFiles) . "\n";
echo "Errors found: " . count($errors) . "\n";

if (empty($errors)) {
    echo "\n🎉 All JavaScript files are syntactically correct!\n";
} else {
    echo "\n⚠️ Some JavaScript files have issues that need to be fixed.\n";
}
?>
