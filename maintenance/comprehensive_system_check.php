<?php
// Comprehensive system check for all potential issues
require_once 'app/config/config.php';

echo "=== COMPREHENSIVE SYSTEM CHECK ===\n\n";

// 1. Check JavaScript syntax errors in all view files
echo "1. Checking JavaScript syntax in view files...\n";
$viewFiles = [
    'app/views/companies/index.php',
    'app/views/dashboard/index.php',
    'app/views/layouts/main.php'
];

foreach ($viewFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for common JavaScript syntax errors
        $errors = [];
        
        // Check for unclosed functions
        if (substr_count($content, 'function') > substr_count($content, '}')) {
            $errors[] = "Possible unclosed function";
        }
        
        // Check for missing semicolons
        if (preg_match_all('/\$\([^)]*\)\.on\([^,]+,\s*function\s*\([^)]*\)\s*\{[^}]*\)(?!\s*;)/', $content, $matches)) {
            $errors[] = "Missing semicolon in jQuery event handler";
        }
        
        // Check for malformed AJAX calls
        if (preg_match_all('/\$\.\s*ajax\(\{[^}]*url[^}]*\}[^}]*\}(?!\s*;)/', $content, $matches)) {
            $errors[] = "Malformed AJAX call";
        }
        
        if (empty($errors)) {
            echo "✅ $file - No obvious syntax errors\n";
        } else {
            echo "❌ $file - Issues found: " . implode(', ', $errors) . "\n";
        }
    } else {
        echo "⚠️  $file - File not found\n";
    }
}

// 2. Check database constraints
echo "\n2. Checking database constraints...\n";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Check companies table constraints
    $result = $pdo->query("SHOW INDEX FROM companies");
    $indexes = [];
    while ($row = $result->fetch()) {
        $indexes[] = $row['Key_name'];
    }
    
    if (in_array('company_code', $indexes)) {
        echo "✅ company_code UNIQUE constraint exists\n";
    } else {
        echo "❌ company_code constraint missing\n";
    }
    
    // Check branch_code constraint if branches table exists
    try {
        $result = $pdo->query("SHOW TABLES LIKE 'branches'");
        if ($result->rowCount() > 0) {
            $result = $pdo->query("SHOW INDEX FROM branches");
            $branchIndexes = [];
            while ($row = $result->fetch()) {
                $branchIndexes[] = $row['Key_name'];
            }
            
            if (in_array('branch_code', $branchIndexes)) {
                echo "✅ branch_code UNIQUE constraint exists\n";
            } else {
                echo "❌ branch_code constraint missing\n";
            }
        }
    } catch (Exception $e) {
        echo "⚠️  Branches table check failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database check failed: " . $e->getMessage() . "\n";
}

// 3. Check controller methods
echo "\n3. Checking controller methods...\n";
$controllers = [
    'CompanyController' => ['create', 'update', 'edit', 'delete', 'get', 'details'],
    'AddressController' => ['getProvinces', 'getRegencies', 'getDistricts', 'getVillages', 'getPostalCode']
];

foreach ($controllers as $controller => $methods) {
    $file = "app/controllers/{$controller}.php";
    if (file_exists($file)) {
        include_once $file;
        
        if (class_exists($controller)) {
            foreach ($methods as $method) {
                if (method_exists($controller, $method)) {
                    echo "✅ $controller::$method() exists\n";
                } else {
                    echo "❌ $controller::$method() missing\n";
                }
            }
        } else {
            echo "❌ $controller class not found\n";
        }
    } else {
        echo "❌ $file not found\n";
    }
}

// 4. Check model methods
echo "\n4. Checking model methods...\n";
$models = [
    'Company' => ['getAll', 'getById', 'create', 'updateCompany', 'delete'],
    'Address' => ['getVillages', 'getPostalCode']
];

foreach ($models as $model => $methods) {
    $file = "app/models/{$model}.php";
    if (file_exists($file)) {
        include_once $file;
        
        if (class_exists($model)) {
            foreach ($methods as $method) {
                if (method_exists($model, $method)) {
                    echo "✅ $model::$method() exists\n";
                } else {
                    echo "❌ $model::$method() missing\n";
                }
            }
        } else {
            echo "❌ $model class not found\n";
        }
    } else {
        echo "❌ $file not found\n";
    }
}

// 5. Check routing configuration
echo "\n5. Checking routing configuration...\n";
$indexContent = file_exists('index.php') ? file_get_contents('index.php') : '';

if ($indexContent) {
    $routes = [
        'companies' => ['create', 'update', 'edit', 'delete', 'details', 'get'],
        'address' => ['get-provinces', 'get-regencies', 'get-districts', 'get-villages', 'get-postal-code']
    ];
    
    foreach ($routes as $page => $actions) {
        foreach ($actions as $action) {
            if (strpos($indexContent, "'$action'") !== false) {
                echo "✅ Route: $page?action=$action exists\n";
            } else {
                echo "❌ Route: $page?action=$action missing\n";
            }
        }
    }
} else {
    echo "❌ index.php not found\n";
}

// 6. Check for common JavaScript issues
echo "\n6. Checking for common JavaScript issues...\n";
$jsFiles = [
    'app/views/companies/index.php',
    'app/views/dashboard/index.php'
];

foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for undefined functions
        if (preg_match_all('/window\.(\w+)\s*=\s*(\w+)/', $content, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $funcName = $matches[2][$i];
                if (strpos($content, "function $funcName") === false) {
                    echo "⚠️  $file - Function $funcName exported but not defined\n";
                }
            }
        }
        
        // Check for jQuery ready issues
        if (strpos($content, '$(document).ready') === false && strpos($content, 'jQuery(document).ready') === false) {
            echo "⚠️  $file - No document ready found\n";
        }
        
        // Check for missing error handling
        if (substr_count($content, 'error:') < 3) {
            echo "⚠️  $file - Insufficient error handling\n";
        }
    }
}

// 7. Check database ENUM definitions
echo "\n7. Checking database ENUM definitions...\n";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Check company_type ENUM
    $result = $pdo->query("SHOW COLUMNS FROM companies LIKE 'company_type'");
    $column = $result->fetch();
    if ($column) {
        echo "✅ company_type ENUM: " . $column['Type'] . "\n";
        
        // Check if all form options are in ENUM
        $enumValues = [];
        preg_match("/'(.*?)'/", $column['Type'], $matches);
        $enumValues = array_slice($matches, 1);
        
        $formOptions = ['individual', 'warung', 'kios', 'toko_kelontong', 'minimarket', 'pengusaha_menengah', 'distributor', 'koperasi', 'perusahaan_besar', 'franchise'];
        $missing = array_diff($formOptions, $enumValues);
        
        if (empty($missing)) {
            echo "✅ All form options in ENUM\n";
        } else {
            echo "⚠️  Missing in ENUM: " . implode(', ', $missing) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ENUM check failed: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETED ===\n";
?>
