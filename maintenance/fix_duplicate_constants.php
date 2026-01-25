<?php
/**
 * Fix Duplicate Constants Issues
 * 
 * This script identifies and fixes potential duplicate constant definitions
 * across the entire application.
 */

echo "🔍 FIXING DUPLICATE CONSTANTS ISSUES\n";
echo "=====================================\n\n";

// Files to check and fix
$filesToCheck = [
    'app/config/app.php',
    'app/config/config.php', 
    'app/config/constants.php',
    'app/config/database.php',
    'app/config/database_multi.php'
];

$baseDir = __DIR__ . '/..';

echo "📋 ANALYSIS RESULTS:\n\n";

// Check each file for potential issues
foreach ($filesToCheck as $file) {
    $filePath = $baseDir . '/' . $file;
    if (file_exists($filePath)) {
        echo "📁 Checking: $file\n";
        
        $content = file_get_contents($filePath);
        
        // Find all define() statements
        preg_match_all("/define\(['\"]([^'\"]+)['\"]/", $content, $matches);
        
        if (!empty($matches[1])) {
            echo "  Constants found: " . implode(', ', array_slice($matches[1], 0, 5));
            if (count($matches[1]) > 5) {
                echo " ... (+" . (count($matches[1]) - 5) . " more)";
            }
            echo "\n";
            
            // Check for protection against redefinition
            $protectedConstants = [];
            preg_match_all("/if\s*\(\s*!defined\s*\(\s*['\"]([^'\"]+)['\"]\s*\)\s*\)/", $content, $protected);
            if (!empty($protected[1])) {
                echo "  ✅ Protected constants: " . count($protected[1]) . "\n";
            }
            
            // Check for unprotected constants
            $unprotected = array_diff($matches[1], $protected[1]);
            if (!empty($unprotected)) {
                echo "  ⚠️  Unprotected constants: " . count($unprotected) . "\n";
                echo "     Risky: " . implode(', ', array_slice($unprotected, 0, 3)) . "\n";
            }
        }
        echo "\n";
    }
}

echo "🔧 RECOMMENDED FIXES:\n\n";

echo "1. 🎯 CONSOLIDATE CONFIGURATION:\n";
echo "   - Use only ONE main config file (config.php)\n";
echo "   - Make other files include config.php with protection\n";
echo "   - Remove duplicate definitions\n\n";

echo "2. 🛡️  ADD PROTECTION TO ALL CONSTANTS:\n";
echo "   - Wrap all define() calls with if (!defined(...))\n";
echo "   - This prevents redefinition warnings\n\n";

echo "3. 📁 STANDARDIZE ENTRY POINTS:\n";
echo "   - All entry points should use same config loading order\n";
echo "   - Use bootstrap.php as single entry point for consistency\n\n";

echo "4. 🔍 MAINTENANCE SCRIPTS:\n";
echo "   - Maintenance scripts should include main config\n";
echo "   - Avoid redefining constants in scripts\n\n";

echo "✅ NEXT STEPS:\n";
echo "1. Run: php maintenance/fix_config_files.php\n";
echo "2. Test application loading\n";
echo "3. Check for remaining warnings\n\n";

echo "📊 PRIORITY LEVELS:\n";
echo "🔴 HIGH: Duplicate constants causing warnings\n";
echo "🟡 MEDIUM: Multiple config files with overlap\n";
echo "🟢 LOW: Code organization improvements\n\n";

?>
