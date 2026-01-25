<?php
/**
 * Supplier Management Database Migration Script
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
$sqlFile = __DIR__ . '/../database_migrations/create_supplier_management_tables.sql';
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
        'suppliers',
        'supplier_contacts', 
        'supplier_products',
        'purchase_orders',
        'purchase_order_items',
        'supplier_performance',
        'supplier_categories',
        'supplier_category_assignments'
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
        echo "\n🚀 All supplier management tables created successfully!\n";
        
        // Insert default supplier categories
        echo "\n=== Inserting Default Data ===\n";
        
        try {
            $categories = [
                ['category_name' => 'Raw Materials', 'category_description' => 'Suppliers of raw materials for production'],
                ['category_name' => 'Finished Goods', 'category_description' => 'Suppliers of finished products for resale'],
                ['category_name' => 'Packaging Materials', 'category_description' => 'Suppliers of packaging and shipping materials'],
                ['category_name' => 'Equipment & Tools', 'category_description' => 'Suppliers of business equipment and tools'],
                ['category_name' => 'Services', 'category_description' => 'Service providers and consultants'],
                ['category_name' => 'Utilities', 'category_description' => 'Utility companies and service providers'],
                ['category_name' => 'Technology', 'category_description' => 'IT and technology suppliers'],
                ['category_name' => 'Maintenance', 'category_description' => 'Maintenance and repair services'],
                ['category_name' => 'Transportation', 'category_description' => 'Logistics and transportation providers'],
                ['category_name' => 'Office Supplies', 'category_description' => 'Office and administrative supplies']
            ];
            
            foreach ($categories as $category) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO supplier_categories (category_name, category_description) VALUES (:name, :description)");
                $stmt->execute($category);
            }
            
            echo "✅ Default supplier categories inserted\n";
            
        } catch (PDOException $e) {
            echo "⚠️  Failed to insert default categories: " . $e->getMessage() . "\n";
        }
        
        echo "\n=== System Ready ===\n";
        echo "🎯 Supplier Management System is now ready for use!\n";
        echo "\n=== Next Steps ===\n";
        echo "1. Test the system through the web interface\n";
        echo "2. Navigate to /suppliers in your browser\n";
        echo "3. Create your first supplier\n";
        echo "4. Test purchase order creation\n";
        echo "5. Verify Indonesian business context features\n";
        
    } else {
        echo "\n❌ Some tables were not created properly\n";
    }
    
} else {
    echo "\n❌ Migration completed with errors\n";
    echo "Please check the error messages above and fix any issues\n";
}

?>
