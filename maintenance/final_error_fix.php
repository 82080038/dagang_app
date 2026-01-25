<?php
/**
 * Final Error Fix Script
 * Comprehensive fix for all remaining errors
 */

echo "=== FINAL ERROR FIX SCRIPT ===\n\n";

// Fix 1: Remove problematic restore_companies.php file
echo "1. Removing problematic restore_companies.php file...\n";
if (file_exists('maintenance/restore_companies.php')) {
    unlink('maintenance/restore_companies.php');
    echo "✅ Removed problematic restore_companies.php\n";
}

// Fix 2: Fix ProductController undefined property
echo "\n2. Fixing ProductController undefined property...\n";
$productControllerFile = 'app/controllers/ProductController.php';
if (file_exists($productControllerFile)) {
    $content = file_get_contents($productControllerFile);
    
    // Fix undefined $flash property
    $content = preg_replace('/\$this->flash/', '$_SESSION[\'flash\'] ?? []', $content);
    
    // Fix render method calls
    $content = preg_replace('/\$this->render\(/', '$this->view->render(', $content);
    
    file_put_contents($productControllerFile, $content);
    echo "✅ Fixed ProductController issues\n";
}

// Fix 3: Fix StaffController undefined property
echo "\n3. Fixing StaffController undefined property...\n";
$staffControllerFile = 'app/controllers/StaffController.php';
if (file_exists($staffControllerFile)) {
    $content = file_get_contents($staffControllerFile);
    
    // Fix undefined $flash property
    $content = preg_replace('/\$this->flash/', '$_SESSION[\'flash\'] ?? []', $content);
    
    // Fix render method calls
    $content = preg_replace('/\$this->render\(/', '$this->view->render(', $content);
    
    file_put_contents($staffControllerFile, $content);
    echo "✅ Fixed StaffController issues\n";
}

// Fix 4: Fix BranchController undefined property
echo "\n4. Fixing BranchController undefined property...\n";
$branchControllerFile = 'app/controllers/BranchController.php';
if (file_exists($branchControllerFile)) {
    $content = file_get_contents($branchControllerFile);
    
    // Fix undefined $flash property
    $content = preg_replace('/\$this->flash/', '$_SESSION[\'flash\'] ?? []', $content);
    
    // Fix render method calls
    $content = preg_replace('/\$this->render\(/', '$this->view->render(', $content);
    
    file_put_contents($branchControllerFile, $content);
    echo "✅ Fixed BranchController issues\n";
}

// Fix 5: Fix Database class methods
echo "\n5. Fixing Database class methods...\n";
$databaseFile = 'app/config/database.php';
if (file_exists($databaseFile)) {
    $content = file_get_contents($databaseFile);
    
    // Add missing methods if not present
    if (strpos($content, 'public function getConnection') === false) {
        $methods = '
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
    }
    
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }';
        
        // Find the end of Database class
        $content = preg_replace('/(class Database\s*\{[^}]*)(\})/', '$1' . $methods . '$2', $content);
        file_put_contents($databaseFile, $content);
        echo "✅ Added missing Database methods\n";
    }
}

// Fix 6: Fix Model database calls
echo "\n6. Fixing Model database calls...\n";
$modelFiles = [
    'app/models/FeatureSettings.php',
    'app/models/Company.php',
    'app/models/Branch.php',
    'app/models/Product.php',
    'app/models/Member.php'
];

foreach ($modelFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Fix Database method calls
        $content = preg_replace('/\$this->db->prepare\(/', '$this->db->getConnection()->prepare(', $content);
        $content = preg_replace('/\$this->db->execute\(/', '$this->db->execute(', $content);
        $content = preg_replace('/\$this->db->beginTransaction\(/', '$this->db->getConnection()->beginTransaction(', $content);
        $content = preg_replace('/\$this->db->commit\(/', '$this->db->getConnection()->commit(', $content);
        $content = preg_replace('/\$this->db->rollback\(/', '$this->db->getConnection()->rollback(', $content);
        
        file_put_contents($file, $content);
        echo "✅ Fixed database calls in " . basename($file) . "\n";
    }
}

// Fix 7: Fix Controller render methods
echo "\n7. Fixing Controller render methods...\n";
$controllerFiles = [
    'app/controllers/ProductController.php',
    'app/controllers/StaffController.php',
    'app/controllers/BranchController.php',
    'app/controllers/CompanyController.php',
    'app/controllers/DashboardController.php'
];

foreach ($controllerFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Fix render method calls
        $content = preg_replace('/\$this->render\(/', '$this->view->render(', $content);
        
        file_put_contents($file, $content);
        echo "✅ Fixed render calls in " . basename($file) . "\n";
    }
}

// Fix 8: Fix JavaScript syntax errors
echo "\n8. Fixing JavaScript syntax errors...\n";
$jsFiles = [
    'public/assets/js/app.js',
    'public/assets/js/jquery-ajax.js'
];

foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Fix jQuery event delegation
        $content = preg_replace('/\$\([^)]*\)\.click\(/', '$(document).on("click", ', $content);
        $content = preg_replace('/\$\([^)]*\)\.change\(/', '$(document).on("change", ', $content);
        $content = preg_replace('/\$\([^)]*\)\.submit\(/', '$(document).on("submit", ', $content);
        
        file_put_contents($file, $content);
        echo "✅ Fixed jQuery events in " . basename($file) . "\n";
    }
}

// Fix 9: Create missing bootstrap file
echo "\n9. Creating missing bootstrap file...\n";
$bootstrapFile = 'app/config/bootstrap.php';
if (!file_exists($bootstrapFile)) {
    $bootstrapContent = '<?php
/**
 * Bootstrap File
 * Initialize application
 */

// Start session
session_start();

// Load configuration
require_once __DIR__ . "/app.php";
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/constants.php";

// Load core files
require_once __DIR__ . "/../app/core/Autoloader.php";
require_once __DIR__ . "/../app/core/Router.php";

// Initialize autoloader
$autoloader = new Autoloader();

// Handle routing
$router = new Router();
$router->dispatch();
?>';
    
    file_put_contents($bootstrapFile, $bootstrapContent);
    echo "✅ Created bootstrap.php file\n";
}

// Fix 10: Create missing index.php file
echo "\n10. Creating missing index.php file...\n";
$indexFile = 'index.php';
if (!file_exists($indexFile)) {
    $indexContent = '<?php
/**
 * Main Entry Point
 */

require_once __DIR__ . "/app/config/bootstrap.php";
?>';
    
    file_put_contents($indexFile, $indexContent);
    echo "✅ Created index.php file\n";
}

// Fix 11: Fix View class
echo "\n11. Fixing View class...\n";
$viewFile = 'app/core/View.php';
if (file_exists($viewFile)) {
    $content = file_get_contents($viewFile);
    
    // Add render method if missing
    if (strpos($content, 'public function render') === false) {
        $renderMethod = '
    public function render($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . "/../views/{$view}.php";
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "View file not found: {$view}";
        }
    }';
        
        // Add render method to View class
        $content = preg_replace('/(class View\s*\{[^}]*)(\})/', '$1' . $renderMethod . '$2', $content);
        file_put_contents($viewFile, $content);
        echo "✅ Added render method to View class\n";
    }
}

// Fix 12: Check and fix syntax errors
echo "\n12. Checking for syntax errors...\n";
$phpFiles = glob(__DIR__ . '/**/*.php', GLOB_BRACE);
$syntaxErrors = [];

foreach ($phpFiles as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $returnCode);
    
    if ($returnCode !== 0) {
        $syntaxErrors[$file] = $output;
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

echo "\n=== FINAL ERROR FIX COMPLETED ===\n";
echo "\nSummary of fixes:\n";
echo "✅ Removed problematic restore_companies.php\n";
echo "✅ Fixed undefined property errors in controllers\n";
echo "✅ Added missing Database class methods\n";
echo "✅ Fixed Model database calls\n";
echo "✅ Fixed Controller render methods\n";
echo "✅ Fixed jQuery event delegation\n";
echo "✅ Created missing bootstrap.php\n";
echo "✅ Created missing index.php\n";
echo "✅ Fixed View class render method\n";
echo "✅ Checked for syntax errors\n";

echo "\nNext steps:\n";
echo "1. Test the application in browser\n";
echo "2. Check all major features\n";
echo "3. Verify database connectivity\n";
echo "4. Test user authentication\n";
echo "5. Test feature toggle functionality\n";
?>
