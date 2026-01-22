<?php
/**
 * Comprehensive Application Code Check
 * Check all files for JavaScript compatibility issues
 */

echo "=== Comprehensive Application Code Check ===\n\n";

// Get all PHP files with JavaScript
$directories = [
    'app/views' => 'Views',
    'app/views/layouts' => 'Layouts',
    'app/views/auth' => 'Auth',
    'app/views/companies' => 'Companies',
    'public/assets/js' => 'Public JS'
];

$totalIssues = 0;
$filesChecked = 0;

foreach ($directories as $dir => $name) {
    echo "=== Checking $name ===\n";
    
    if (!is_dir($dir)) {
        echo "Directory not found: $dir\n\n";
        continue;
    }
    
    $files = glob($dir . '/*.php');
    if (empty($files)) {
        $files = glob($dir . '/*.js');
    }
    
    foreach ($files as $file) {
        $filesChecked++;
        $content = file_get_contents($file);
        $fileName = basename($file);
        
        echo "\n--- File: $fileName ---\n";
        
        $issues = [];
        
        // Check for const declarations
        $constMatches = preg_match_all('/\bconst\s+\w+\s*=/', $content);
        if ($constMatches > 0) {
            $issues[] = "Found $constMatches const declarations";
            
            // Find lines with const
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (preg_match('/\bconst\s+\w+\s*=/', $line)) {
                    $issues[] = "  Line " . ($lineNum + 1) . ": " . trim(substr($line, 0, 80));
                }
            }
        }
        
        // Check for let declarations
        $letMatches = preg_match_all('/\blet\s+\w+\s*=/', $content);
        if ($letMatches > 0) {
            $issues[] = "Found $letMatches let declarations";
            
            // Find lines with let
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (preg_match('/\blet\s+\w+\s*=/', $line)) {
                    $issues[] = "  Line " . ($lineNum + 1) . ": " . trim(substr($line, 0, 80));
                }
            }
        }
        
        // Check for arrow functions
        $arrowMatches = preg_match_all('/=>/', $content);
        if ($arrowMatches > 0) {
            $issues[] = "Found $arrowMatches arrow functions";
            
            // Find lines with arrow functions
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (strpos($line, '=>') !== false) {
                    $issues[] = "  Line " . ($lineNum + 1) . ": " . trim(substr($line, 0, 80));
                }
            }
        }
        
        // Check for template literals
        $templateMatches = preg_match_all('/`.*\$\{.*\}`/', $content);
        if ($templateMatches > 0) {
            $issues[] = "Found $templateMatches template literals";
            
            // Find lines with template literals
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (preg_match('/`.*\$\{.*\}`/', $line)) {
                    $issues[] = "  Line " . ($lineNum + 1) . ": " . trim(substr($line, 0, 80));
                }
            }
        }
        
        // Check for default parameters
        $defaultParamMatches = preg_match_all('/function\s+\w+\s*\([^)]*\s*=/', $content);
        if ($defaultParamMatches > 0) {
            $issues[] = "Found $defaultParamMatches default parameters";
            
            // Find lines with default parameters
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (preg_match('/function\s+\w+\s*\([^)]*\s*=/', $line)) {
                    $issues[] = "  Line " . ($lineNum + 1) . ": " . trim(substr($line, 0, 80));
                }
            }
        }
        
        // Check for OR operators in function calls
        $orMatches = preg_match_all('/\w+\([^)]*\)\s*\|\|\s*[^)]+\)/', $content);
        if ($orMatches > 0) {
            $issues[] = "Found $orMatches OR operators in function calls";
            
            // Find lines with OR operators
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (preg_match('/\w+\([^)]*\)\s*\|\|\s*[^)]+\)/', $line)) {
                    $issues[] = "  Line " . ($lineNum + 1) . ": " . trim(substr($line, 0, 80));
                }
            }
        }
        
        // Check for bracket balance
        $lines = explode("\n", $content);
        $openBraces = 0;
        $closeBraces = 0;
        $openParens = 0;
        $closeParens = 0;
        
        foreach ($lines as $line) {
            $openBraces += substr_count($line, '{');
            $closeBraces += substr_count($line, '}');
            $openParens += substr_count($line, '(');
            $closeParens += substr_count($line, ')');
        }
        
        if ($openBraces !== $closeBraces) {
            $issues[] = "Unbalanced braces: $openBraces open, $closeBraces close";
        }
        
        if ($openParens !== $closeParens) {
            $issues[] = "Unbalanced parentheses: $openParens open, $closeParens close";
        }
        
        if (!empty($issues)) {
            echo "❌ Issues found:\n";
            foreach ($issues as $issue) {
                echo "  $issue\n";
            }
            $totalIssues += count($issues);
        } else {
            echo "✅ No issues found\n";
        }
    }
    
    echo "\n";
}

echo "=== Summary ===\n";
echo "Files checked: $filesChecked\n";
echo "Total issues found: $totalIssues\n";

if ($totalIssues > 0) {
    echo "\n❌ Please fix the above issues before continuing.\n";
} else {
    echo "\n✅ All files are compatible with older browsers!\n";
}

echo "\n=== Specific Line 623 Check ===\n";

// Check specific line 623 in companies/index.php
$companiesFile = 'app/views/companies/index.php';
if (file_exists($companiesFile)) {
    $content = file_get_contents($companiesFile);
    $lines = explode("\n", $content);
    
    if (isset($lines[622])) {
        echo "Line 623: " . trim($lines[622]) . "\n";
        
        // Check for syntax issues in this line
        $line = $lines[622];
        if (preg_match('/\)\s*[,;]\s*$/', $line)) {
            echo "❌ Found unexpected ')' at end of line\n";
        } else {
            echo "✅ No obvious syntax issues\n";
        }
        
        // Check context around line 623
        echo "\nContext around Line 623:\n";
        for ($i = max(0, 622 - 3); $i < min(count($lines), 622 + 3); $i++) {
            echo "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
        }
    } else {
        echo "Line 623 not found (file has " . count($lines) . " lines)\n";
    }
} else {
    echo "Companies file not found\n";
}

echo "\n=== Check Complete ===\n";
?>
