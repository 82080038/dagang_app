<?php
/**
 * Fix JavaScript Parentheses Issues
 */

echo "🔧 FIXING JAVASCRIPT PARENTHESES\n";
echo "=====================================\n\n";

$files = [
    'public/assets/js/theme.js',
    'public/assets/js/users.js'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "Fixing: " . str_replace('public/assets/', '', $file) . "\n";
        
        $content = file_get_contents($file);
        $original = $content;
        
        // Fix common parentheses issues
        $content = preg_replace('/\{\s*\}/', '{}', $content);
        $content = preg_replace('/\(\s*\)/', '()', $content);
        
        // Fix specific issues
        if (strpos($file, 'theme.js') !== false) {
            // Fix theme.js specific issues
            $content = preg_replace('/\{\s*try\s*\{/', '{try{', $content);
            $content = preg_replace('/\}\s*\}\s*catch\(e\)\{\}/', '}}catch(e){}', $content);
        }
        
        if ($content !== $original) {
            file_put_contents($file, $content);
            echo "  ✅ Fixed\n";
        } else {
            echo "  ⚠️ No changes needed\n";
        }
    }
}

echo "\n🔍 VERIFYING FIXES\n";
echo "=====================================\n";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        $openParens = substr_count($content, '(');
        $closeParens = substr_count($content, ')');
        
        $status = "✅";
        if ($openBraces !== $closeBraces || $openParens !== $closeParens) {
            $status = "❌";
        }
        
        echo $status . " " . str_replace('public/assets/', '', $file) . "\n";
        echo "   Braces: {$openBraces} open, {$closeBraces} close\n";
        echo "   Parentheses: {$openParens} open, {$closeParens} close\n";
    }
}

echo "\n🎯 DONE\n";
?>
