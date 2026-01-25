<?php
/**
 * User Management Database Migration Script
 * Runs the SQL migration directly through PHP
 */

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=perdagangan_system", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Read the SQL migration file
$sqlFile = __DIR__ . '/../database_migrations/create_user_management_enhancements.sql';
if (!file_exists($sqlFile)) {
    echo "❌ Migration file not found: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if (!$sql) {
    echo "❌ Failed to read migration file\n";
    exit(1);
}

echo "📄 Migration file loaded successfully\n";

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "🔧 Found " . count($statements) . " SQL statements to execute\n\n";

$successCount = 0;
$errorCount = 0;

foreach ($statements as $i => $statement) {
    if (empty($statement)) continue;
    
    try {
        $pdo->exec($statement);
        echo "✅ Statement " . ($i + 1) . " executed successfully\n";
        $successCount++;
    } catch (PDOException $e) {
        // Check if it's a "table already exists" error
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⚠️  Statement " . ($i + 1) . " skipped (table already exists)\n";
        } else {
            echo "❌ Statement " . ($i + 1) . " failed: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
}

echo "\n=== Migration Results ===\n";
echo "✅ Successful statements: $successCount\n";
echo "❌ Failed statements: $errorCount\n";

if ($errorCount === 0) {
    echo "\n🎉 Migration completed successfully!\n";
    
    // Verify tables were created
    echo "\n=== Verifying Tables ===\n";
    
    $tables = [
        'user_roles',
        'permissions', 
        'role_permissions',
        'user_role_assignments',
        'user_activity_log',
        'user_sessions',
        'user_preferences',
        'user_import_export_templates'
    ];
    
    $allTablesExist = true;
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            echo "✅ Table '$table' exists\n";
        } catch (PDOException $e) {
            echo "❌ Table '$table' missing: " . $e->getMessage() . "\n";
            $allTablesExist = false;
        }
    }
    
    if ($allTablesExist) {
        echo "\n🚀 All user management tables created successfully!\n";
        
        // Verify default data
        echo "\n=== Verifying Default Data ===\n";
        
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_roles");
            $roleCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ User roles: " . $roleCount['count'] . " records\n";
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM permissions");
            $permissionCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Permissions: " . $permissionCount['count'] . " records\n";
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM role_permissions");
            $rolePermissionCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Role permissions: " . $rolePermissionCount['count'] . " assignments\n";
            
        } catch (PDOException $e) {
            echo "⚠️  Failed to verify default data: " . $e->getMessage() . "\n";
        }
        
        echo "\n=== System Ready ===\n";
        echo "🎯 User Management System is now ready for use!\n";
        echo "\n=== Next Steps ===\n";
        echo "1. Test the system through the web interface\n";
        echo "2. Navigate to /users in your browser\n";
        echo "3. Create your first enhanced user\n";
        echo "4. Test role assignments and permissions\n";
        echo "5. Verify activity logging\n";
        echo "6. Test bulk operations (import/export)\n";
        
    } else {
        echo "\n❌ Some tables were not created properly\n";
    }
    
} else {
    echo "\n❌ Migration completed with errors\n";
    echo "Please check the error messages above and fix any issues\n";
}

?>
