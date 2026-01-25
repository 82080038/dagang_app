<?php
/**
 * Comprehensive Error Check
 * 
 * Checks all files for syntax errors, missing dependencies, and common issues
 */

echo "🔍 COMPREHENSIVE ERROR CHECK\n";
echo "=====================================\n\n";

// Define BASE_URL for testing
define('BASE_URL', 'http://localhost/dagang');

// Check PHP syntax errors in all PHP files
echo "📝 PHP SYNTAX CHECK\n";
echo "=====================================\n";

$syntaxErrors = [];
$filesChecked = 0;

function checkPhpSyntax($directory) {
    global $syntaxErrors, $filesChecked;
    
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filesChecked++;
            $filePath = $file->getPathname();
            
            // Skip certain directories
            if (strpos($filePath, 'vendor') !== false || 
                strpos($filePath, '.git') !== false ||
                strpos($filePath, 'maintenance') !== false) {
                continue;
            }
            
            $output = [];
            $returnCode = 0;
            exec("E:\\xampp\\php\\php.exe -l \"$filePath\" 2>&1", $output, $returnCode);
            
            if ($returnCode !== 0) {
                $syntaxErrors[] = [
                    'file' => $filePath,
                    'error' => implode(' ', $output)
                ];
            }
        }
    }
}

// Check main application directories
checkPhpSyntax(__DIR__ . '/app');
checkPhpSyntax(__DIR__ . '/public');
checkPhpSyntax(__DIR__);

echo "Files checked: $filesChecked\n";
echo "Syntax errors found: " . count($syntaxErrors) . "\n\n";

if (!empty($syntaxErrors)) {
    echo "❌ SYNTAX ERRORS:\n";
    foreach ($syntaxErrors as $error) {
        echo "  - " . str_replace(__DIR__ . '/', '', $error['file']) . "\n";
        echo "    " . $error['error'] . "\n\n";
    }
} else {
    echo "✅ No syntax errors found\n\n";
}

// Check for common issues
echo "🔍 COMMON ISSUES CHECK\n";
echo "=====================================\n";

$issues = [];

// 1. Check for undefined constants usage
echo "Checking undefined constants...\n";
$phpFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/app'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

$constantUsageIssues = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    
    // Look for constants that might be undefined
    if (preg_match_all('/\b([A-Z_]{2,})\b/', $content, $matches)) {
        foreach ($matches[1] as $constant) {
            if (!defined($constant) && 
                !in_array($constant, ['PHP_EOL', 'DIRECTORY_SEPARATOR', 'PATH_SEPARATOR', 'TRUE', 'FALSE', 'NULL'])) {
                $constantUsageIssues[] = str_replace(__DIR__ . '/', '', $file) . ': ' . $constant;
            }
        }
    }
}

if (!empty($constantUsageIssues)) {
    echo "⚠️ Potential undefined constants:\n";
    foreach (array_slice($constantUsageIssues, 0, 10) as $issue) {
        echo "  - $issue\n";
    }
    if (count($constantUsageIssues) > 10) {
        echo "  ... and " . (count($constantUsageIssues) - 10) . " more\n";
    }
    echo "\n";
} else {
    echo "✅ No obvious undefined constants\n\n";
}

// 2. Check for missing require_once files
echo "Checking require_once paths...\n";
$pathIssues = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $dir = dirname($file);
    
    if (preg_match_all('/require_once\s+[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        foreach ($matches[1] as $path) {
            if (!preg_match('/^\.\./', $path)) {
                continue; // Skip absolute paths
            }
            
            $fullPath = realpath($dir . '/' . $path);
            if (!$fullPath || !file_exists($fullPath)) {
                $pathIssues[] = str_replace(__DIR__ . '/', '', $file) . ': ' . $path;
            }
        }
    }
}

if (!empty($pathIssues)) {
    echo "❌ Missing require_once files:\n";
    foreach ($pathIssues as $issue) {
        echo "  - $issue\n";
    }
    echo "\n";
} else {
    echo "✅ All require_once paths valid\n\n";
}

// 3. Check for class definition issues
echo "Checking class definitions...\n";
$classIssues = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    
    // Check for class extends issues
    if (preg_match_all('/class\s+(\w+)\s+extends\s+(\w+)/', $content, $matches)) {
        for ($i = 0; $i < count($matches[0]); $i++) {
            $className = $matches[1][$i];
            $parentClass = $matches[2][$i];
            
            // Check if parent class is available
            if (!preg_match('/(class|interface|trait)\s+' . $parentClass . '\s/', $content)) {
                // Check if it's included
                if (!preg_match('/require_once.*[\'"].*' . $parentClass . '/', $content)) {
                    $classIssues[] = str_replace(__DIR__ . '/', '', $file) . ": $className extends $parentClass (parent not found)";
                }
            }
        }
    }
}

if (!empty($classIssues)) {
    echo "⚠️ Potential class inheritance issues:\n";
    foreach ($classIssues as $issue) {
        echo "  - $issue\n";
    }
    echo "\n";
} else {
    echo "✅ No obvious class issues\n\n";
}

// 4. Check for function/method compatibility
echo "Checking method compatibility...\n";
$methodIssues = [];

// Check Controller classes for method compatibility
foreach ($phpFiles as $file) {
    if (strpos($file, 'Controller.php') !== false) {
        $content = file_get_contents($file);
        
        // Look for method declarations that might conflict with parent
        if (preg_match_all('/public\s+function\s+(\w+)\s*\([^)]*\)/', $content, $matches)) {
            // This is a simplified check - in reality, we'd need to parse the full class hierarchy
            foreach ($matches[1] as $method) {
                if (in_array($method, ['hasPermission', 'logActivity', 'requireAuth', 'json', 'view', 'redirect'])) {
                    // These methods might conflict with parent Controller methods
                    $methodIssues[] = str_replace(__DIR__ . '/', '', $file) . ": method $method() might conflict with parent";
                }
            }
        }
    }
}

if (!empty($methodIssues)) {
    echo "⚠️ Potential method compatibility issues:\n";
    foreach ($methodIssues as $issue) {
        echo "  - $issue\n";
    }
    echo "\n";
} else {
    echo "✅ No obvious method compatibility issues\n\n";
}

// 5. Check database configuration
echo "Checking database configuration...\n";
$dbConfigFile = __DIR__ . '/app/config/database.php';
if (file_exists($dbConfigFile)) {
    require_once $dbConfigFile;
    
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            echo "✅ Database connection successful\n";
            
            // Check if main tables exist
            $tables = ['companies', 'branches', 'members', 'products', 'transactions'];
            $missingTables = [];
            foreach ($tables as $table) {
                $result = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($result->rowCount() == 0) {
                    $missingTables[] = $table;
                }
            }
            
            if (!empty($missingTables)) {
                echo "⚠️ Missing core tables: " . implode(', ', $missingTables) . "\n";
            } else {
                echo "✅ Core tables exist\n";
            }
            
        } catch (PDOException $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Database constants not defined\n";
    }
} else {
    echo "❌ Database config file not found\n";
}
echo "\n";

// 6. Check for JavaScript syntax errors
echo "Checking JavaScript files...\n";
$jsErrors = [];
$jsIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/public/assets/js'));

foreach ($jsIterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'js') {
        $content = file_get_contents($file->getPathname());
        
        // Basic syntax checks
        if (substr_count($content, '{') !== substr_count($content, '}')) {
            $jsErrors[] = str_replace(__DIR__ . '/', '', $file->getPathname()) . ": Mismatched braces";
        }
        if (substr_count($content, '(') !== substr_count($content, ')')) {
            $jsErrors[] = str_replace(__DIR__ . '/', '', $file->getPathname()) . ": Mismatched parentheses";
        }
    }
}

if (!empty($jsErrors)) {
    echo "⚠️ JavaScript syntax issues:\n";
    foreach ($jsErrors as $error) {
        echo "  - $error\n";
    }
    echo "\n";
} else {
    echo "✅ JavaScript files appear syntactically correct\n\n";
}

// Summary
echo "📊 ERROR SUMMARY\n";
echo "=====================================\n";
echo "PHP Syntax Errors: " . count($syntaxErrors) . "\n";
echo "Path Issues: " . count($pathIssues) . "\n";
echo "Class Issues: " . count($classIssues) . "\n";
echo "Method Issues: " . count($methodIssues) . "\n";
echo "JavaScript Issues: " . count($jsErrors) . "\n";

$totalIssues = count($syntaxErrors) + count($pathIssues) + count($classIssues) + count($methodIssues) + count($jsErrors);

if ($totalIssues === 0) {
    echo "\n🎉 NO CRITICAL ERRORS FOUND!\n";
    echo "✅ Application appears to be in good condition\n";
} else {
    echo "\n⚠️ TOTAL ISSUES FOUND: $totalIssues\n";
    echo "🔧 Please review and fix the issues listed above\n";
}

echo "\n🎯 RECOMMENDATIONS\n";
echo "=====================================\n";
echo "1. Fix any syntax errors immediately\n";
echo "2. Resolve missing file dependencies\n";
echo "3. Check class inheritance issues\n";
echo "4. Test application functionality\n";
echo "5. Run database migrations if needed\n";
echo "6. Test all major features\n";
?>
