<?php
/**
 * Update Suppliers Table Structure
 * Adds missing columns to the existing suppliers table
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

// Columns to add
$columnsToAdd = [
    "supplier_code VARCHAR(50) UNIQUE NOT NULL AFTER id_supplier",
    "supplier_name VARCHAR(200) NOT NULL AFTER supplier_code",
    "supplier_type ENUM('individual', 'company', 'distributor', 'manufacturer', 'importer', 'local_producer') DEFAULT 'company' AFTER supplier_name",
    "business_category ENUM('retail', 'wholesale', 'manufacturing', 'agriculture', 'services', 'distribution', 'import_export') DEFAULT 'wholesale' AFTER business_category",
    "tax_name VARCHAR(200) AFTER tax_id",
    "is_tax_registered BOOLEAN DEFAULT FALSE AFTER tax_name",
    "mobile VARCHAR(50) AFTER phone",
    "website VARCHAR(255) AFTER email",
    "address_detail TEXT AFTER address",
    "province_id INT AFTER address_detail",
    "regency_id INT AFTER province_id",
    "district_id INT AFTER regency_id",
    "village_id INT AFTER district_id",
    "postal_code VARCHAR(10) AFTER village_id",
    "business_license VARCHAR(100) AFTER postal_code",
    "business_registration VARCHAR(100) AFTER business_license",
    "establishment_date DATE AFTER business_registration",
    "capital_amount DECIMAL(15,2) AFTER establishment_date",
    "bank_name VARCHAR(100) AFTER capital_amount",
    "bank_account_number VARCHAR(50) AFTER bank_name",
    "bank_account_name VARCHAR(200) AFTER bank_account_number",
    "bank_branch VARCHAR(100) AFTER bank_account_name",
    "supplier_category ENUM('regular', 'preferred', 'strategic', 'backup', 'blacklisted') DEFAULT 'regular' AFTER bank_branch",
    "supplier_level ENUM('basic', 'silver', 'gold', 'platinum') DEFAULT 'basic' AFTER supplier_category",
    "total_orders INT DEFAULT 0 AFTER supplier_level",
    "total_amount DECIMAL(15,2) DEFAULT 0.00 AFTER total_orders",
    "average_delivery_time INT DEFAULT 0 AFTER total_amount",
    "on_time_delivery_rate DECIMAL(5,2) DEFAULT 0.00 AFTER average_delivery_time",
    "quality_score DECIMAL(5,2) DEFAULT 0.00 AFTER on_time_delivery_rate",
    "overall_score DECIMAL(5,2) DEFAULT 0.00 AFTER quality_score",
    "payment_terms ENUM('cod', '7_days', '14_days', '30_days', '45_days', '60_days', '90_days') DEFAULT '30_days' AFTER overall_score",
    "credit_limit DECIMAL(15,2) DEFAULT 0.00 AFTER payment_terms",
    "current_balance DECIMAL(15,2) DEFAULT 0.00 AFTER credit_limit",
    "is_blacklisted BOOLEAN DEFAULT FALSE AFTER current_balance",
    "blacklist_reason TEXT AFTER is_blacklisted",
    "notes TEXT AFTER blacklist_reason",
    "created_by INT AFTER notes"
];

echo "🔧 Adding missing columns to suppliers table...\n";

$successCount = 0;
$errorCount = 0;

foreach ($columnsToAdd as $column) {
    try {
        $pdo->exec("ALTER TABLE suppliers ADD COLUMN $column");
        echo "✅ Column added: $column\n";
        $successCount++;
    } catch (PDOException $e) {
        // Check if column already exists
        if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "⚠️  Column already exists: $column\n";
        } else {
            echo "❌ Failed to add column: $column - " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
}

// Update existing data
echo "\n🔄 Updating existing data...\n";

try {
    // Generate supplier codes for existing records
    $stmt = $pdo->query("SELECT id_supplier, name FROM suppliers WHERE supplier_code IS NULL");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($suppliers as $supplier) {
        $code = 'SUP' . str_pad($supplier['id_supplier'], 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("UPDATE suppliers SET supplier_code = ?, supplier_name = ? WHERE id_supplier = ?");
        $stmt->execute([$code, $supplier['name'], $supplier['id_supplier']]);
    }
    
    echo "✅ Supplier codes generated for existing records\n";
    
    // Set default values
    $pdo->exec("UPDATE suppliers SET supplier_type = 'company' WHERE supplier_type IS NULL");
    $pdo->exec("UPDATE suppliers SET business_category = 'wholesale' WHERE business_category IS NULL");
    $pdo->exec("UPDATE suppliers SET supplier_category = 'regular' WHERE supplier_category IS NULL");
    $pdo->exec("UPDATE suppliers SET supplier_level = 'basic' WHERE supplier_level IS NULL");
    $pdo->exec("UPDATE suppliers SET payment_terms = '30_days' WHERE payment_terms IS NULL");
    
    echo "✅ Default values set\n";
    
} catch (PDOException $e) {
    echo "❌ Failed to update existing data: " . $e->getMessage() . "\n";
}

// Add indexes
echo "\n🔧 Adding indexes...\n";

$indexesToAdd = [
    "ADD INDEX idx_supplier_code (supplier_code)",
    "ADD INDEX idx_supplier_name (supplier_name)",
    "ADD INDEX idx_supplier_type (supplier_type)",
    "ADD INDEX idx_supplier_category (supplier_category)",
    "ADD INDEX idx_phone (phone)",
    "ADD INDEX idx_email (email)",
    "ADD INDEX idx_payment_terms (payment_terms)",
    "ADD INDEX idx_overall_score (overall_score)",
    "ADD INDEX idx_active (is_active)",
    "ADD INDEX idx_blacklisted (is_blacklisted)"
];

foreach ($indexesToAdd as $index) {
    try {
        $pdo->exec("ALTER TABLE suppliers $index");
        echo "✅ Index added: $index\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "⚠️  Index already exists: $index\n";
        } else {
            echo "❌ Failed to add index: $index - " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== Update Results ===\n";
echo "✅ Columns added: $successCount\n";
echo "❌ Errors: $errorCount\n";

if ($errorCount === 0) {
    echo "\n🎉 Suppliers table updated successfully!\n";
    
    // Verify the table structure
    echo "\n=== Verifying Updated Table ===\n";
    
    $stmt = $pdo->query("DESCRIBE suppliers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Suppliers table now has " . count($columns) . " columns\n";
    
    // Test basic operations
    echo "\n=== Testing Basic Operations ===\n";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM suppliers");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Total suppliers: " . $count['count'] . "\n";
        
        if ($count['count'] > 0) {
            $stmt = $pdo->query("SELECT id_supplier, supplier_code, supplier_name, supplier_category FROM suppliers LIMIT 5");
            $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "✅ Sample suppliers:\n";
            foreach ($suppliers as $supplier) {
                echo "   - {$supplier['supplier_code']}: {$supplier['supplier_name']} ({$supplier['supplier_category']})\n";
            }
        }
        
    } catch (PDOException $e) {
        echo "❌ Test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🚀 Suppliers table is now ready for the Supplier Management System!\n";
    
} else {
    echo "\n❌ Update completed with errors\n";
}

?>
