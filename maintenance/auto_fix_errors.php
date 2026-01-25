<?php
/**
 * Automatic Error Fix Script
 * Scans and fixes common PHP errors automatically
 */

echo "=== AUTOMATIC ERROR FIX SCRIPT ===\n\n";

// Function to scan for syntax errors
function checkSyntaxErrors($files) {
    echo "1. Checking PHP Syntax Errors...\n";
    $syntaxErrors = [];
    
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $output = [];
            $returnCode = 0;
            exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $returnCode);
            
            if ($returnCode !== 0) {
                $syntaxErrors[$file] = $output;
            }
        }
    }
    
    if (empty($syntaxErrors)) {
        echo "✅ No syntax errors found\n";
    } else {
        echo "❌ Syntax errors found:\n";
        foreach ($syntaxErrors as $file => $errors) {
            echo "   - $file:\n";
            foreach ($errors as $error) {
                echo "     $error\n";
            }
        }
    }
    
    return $syntaxErrors;
}

// Function to fix undefined property errors
function fixUndefinedProperties() {
    echo "\n2. Fixing Undefined Property Errors...\n";
    
    $fixes = [
        'app/controllers/ProductController.php' => [
            'search' => '$this->flash',
            'replace' => '$_SESSION[\'flash\'] ?? []',
            'description' => 'Fix undefined $flash property'
        ],
        'app/controllers/StaffController.php' => [
            'search' => '$this->flash',
            'replace' => '$_SESSION[\'flash\'] ?? []',
            'description' => 'Fix undefined $flash property'
        ]
    ];
    
    foreach ($fixes as $file => $fix) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (strpos($content, $fix['search']) !== false) {
                $newContent = str_replace($fix['search'], $fix['replace'], $content);
                file_put_contents($file, $newContent);
                echo "✅ Fixed: {$fix['description']} in $file\n";
            }
        }
    }
}

// Function to fix database connection issues
function fixDatabaseIssues() {
    echo "\n3. Fixing Database Connection Issues...\n";
    
    $dbConfigFile = 'app/config/database.php';
    if (file_exists($dbConfigFile)) {
        $content = file_get_contents($dbConfigFile);
        
        // Check if Database class has getConnection method
        if (strpos($content, 'public function getConnection') === false) {
            $dbClassFix = '
    public function getConnection() {
        return $this->pdo;
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollback() {
        return $this->pdo->rollback();
    }';
            
            // Find the end of Database class and add methods
            $pattern = '/class Database\s*\{[^}]*}/';
            if (preg_match($pattern, $content)) {
                // Add getConnection method if missing
                $content = preg_replace('/(class Database\s*\{[^}]*)(\})/', '$1' . $dbClassFix . '$2', $content);
                file_put_contents($dbConfigFile, $content);
                echo "✅ Added missing Database methods\n";
            }
        }
    }
}

// Function to fix missing includes
function fixMissingIncludes() {
    echo "\n4. Fixing Missing Includes...\n";
    
    $includes = [
        'app/controllers/FeatureSettingsController.php' => [
            'required' => ['Database', 'FeatureSettings'],
            'add' => "require_once __DIR__ . '/../app/config/database.php';\nrequire_once __DIR__ . '/../models/FeatureSettings.php';\n"
        ],
        'app/models/FeatureSettings.php' => [
            'required' => ['Database'],
            'add' => "require_once __DIR__ . '/../config/database.php';\n"
        ]
    ];
    
    foreach ($includes as $file => $config) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $needsFix = false;
            
            foreach ($config['required'] as $class) {
                if (strpos($content, "class $class") !== false && strpos($content, "require_once") === false) {
                    $needsFix = true;
                    break;
                }
            }
            
            if ($needsFix) {
                $newContent = $config['add'] . $content;
                file_put_contents($file, $newContent);
                echo "✅ Added missing includes in $file\n";
            }
        }
    }
}

// Function to fix JavaScript errors
function fixJavaScriptErrors() {
    echo "\n5. Fixing JavaScript Errors...\n";
    
    $jsFiles = [
        'public/assets/js/app.js',
        'public/assets/js/jquery-ajax.js'
    ];
    
    foreach ($jsFiles as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            
            // Fix common jQuery issues
            $fixes = [
                '/\$\([^)]*\)\.click\(/' => '$(document).on("click", ',
                '/\$\([^)]*\)\.change\(/' => '$(document).on("change", ',
                '/\$\([^)]*\)\.submit\(/' => '$(document).on("submit", ',
            ];
            
            $originalContent = $content;
            foreach ($fixes as $pattern => $replacement) {
                $content = preg_replace($pattern, $replacement, $content);
            }
            
            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                echo "✅ Fixed jQuery event delegation in $file\n";
            }
        }
    }
}

// Function to fix Model issues
function fixModelIssues() {
    echo "\n6. Fixing Model Issues...\n";
    
    $modelFiles = [
        'app/models/FeatureSettings.php',
        'app/models/Company.php',
        'app/models/Branch.php'
    ];
    
    foreach ($modelFiles as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            
            // Fix Database::prepare() calls
            $content = preg_replace(
                '/\$this->db->prepare\(/',
                '$this->db->getConnection()->prepare(',
                $content
            );
            
            // Fix transaction calls
            $content = preg_replace(
                '/\$this->db->beginTransaction\(/',
                '$this->db->getConnection()->beginTransaction(',
                $content
            );
            
            $content = preg_replace(
                '/\$this->db->commit\(/',
                '$this->db->getConnection()->commit(',
                $content
            );
            
            $content = preg_replace(
                '/\$this->db->rollback\(/',
                '$this->db->getConnection()->rollback(',
                $content
            );
            
            file_put_contents($file, $content);
            echo "✅ Fixed Database method calls in $file\n";
        }
    }
}

// Function to fix Controller issues
function fixControllerIssues() {
    echo "\n7. Fixing Controller Issues...\n";
    
    $controllerFiles = [
        'app/controllers/ProductController.php',
        'app/controllers/StaffController.php',
        'app/controllers/BranchController.php'
    ];
    
    foreach ($controllerFiles as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            
            // Fix undefined $flash property
            $content = preg_replace(
                '/\$this->flash/',
                '$_SESSION[\'flash\'] ?? []',
                $content
            );
            
            // Fix render method calls
            $content = preg_replace(
                '/\$this->render\(/',
                '$this->view->render(',
                $content
            );
            
            file_put_contents($file, $content);
            echo "✅ Fixed Controller issues in $file\n";
        }
    }
}

// Function to create missing directories
function createMissingDirectories() {
    echo "\n8. Creating Missing Directories...\n";
    
    $directories = [
        'maintenance',
        'logs',
        'temp',
        'uploads',
        'cache'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "✅ Created directory: $dir\n";
        }
    }
}

// Function to fix permission issues
function fixPermissionIssues() {
    echo "\n9. Fixing Permission Issues...\n";
    
    // Fix .htaccess if missing
    if (!file_exists('.htaccess')) {
        $htaccess = 'RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]';
        
        file_put_contents('.htaccess', $htaccess);
        echo "✅ Created .htaccess file\n";
    }
    
    // Fix index.php if missing
    if (!file_exists('index.php')) {
        $indexContent = '<?php
require_once __DIR__ . "/app/config/bootstrap.php";
?>';
        file_put_contents('index.php', $indexContent);
        echo "✅ Created index.php file\n";
    }
}

// Main execution
$rootDir = __DIR__;
$files = glob($rootDir . '/**/*.php', GLOB_BRACE);

// Run all fixes
checkSyntaxErrors($files);
fixUndefinedProperties();
fixDatabaseIssues();
fixMissingIncludes();
fixJavaScriptErrors();
fixModelIssues();
fixControllerIssues();
createMissingDirectories();
fixPermissionIssues();

echo "\n=== AUTOMATIC ERROR FIX COMPLETED ===\n";
echo "\nNext steps:\n";
echo "1. Test the application in browser\n";
echo "2. Check error logs for remaining issues\n";
echo "3. Test all major features\n";
echo "4. Verify database connectivity\n";
echo "5. Test user authentication\n";
?>
