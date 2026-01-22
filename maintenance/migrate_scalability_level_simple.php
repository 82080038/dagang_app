<?php
/**
 * Migration: Add missing columns to companies table
 */

require_once __DIR__ . '/app/config/database.php';

header('Content-Type: text/plain');

echo "=== Migration: Add Missing Columns ===\n\n";

try {
    $db = Database::getInstance();
    
    $columns = [
        'scalability_level' => "ENUM('1','2','3','4','5','6') DEFAULT '1' AFTER company_type",
        'business_category' => "ENUM('retail','wholesale','manufacturing','agriculture','services','cooperative','online','franchise','distributor','personal') DEFAULT 'retail' AFTER scalability_level"
    ];
    
    foreach ($columns as $columnName => $definition) {
        echo "1. Checking if $columnName column exists...\n";
        
        $check = $db->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                              WHERE TABLE_SCHEMA = DATABASE() 
                              AND TABLE_NAME = 'companies' 
                              AND COLUMN_NAME = '$columnName'")->fetchColumn();
        
        if ($check == 0) {
            echo "   Adding $columnName column...\n";
            $db->query("ALTER TABLE companies ADD COLUMN $columnName $definition");
            echo "   ✅ $columnName column added\n";
            
            echo "2. Creating index for $columnName...\n";
            $db->query("CREATE INDEX idx_$columnName ON companies ($columnName)");
            echo "   ✅ Index created\n";
            
            echo "3. Updating existing records for $columnName...\n";
            $db->query("UPDATE companies SET $columnName = NULL WHERE $columnName IS NULL");
            echo "   ✅ Existing records updated\n";
        } else {
            echo "   ℹ $columnName column already exists\n";
        }
        echo "\n";
    }
    
    echo "4. Verification...\n";
    
    $result = $db->query("DESCRIBE companies")->fetchAll();
    $foundColumns = [];
    foreach ($result as $column) {
        if (in_array($column['Field'], ['scalability_level', 'business_category'])) {
            $foundColumns[] = $column['Field'];
            echo "   ✅ {$column['Field']} column found: {$column['Type']}\n";
        }
    }
    
    $missingColumns = array_diff(['scalability_level', 'business_category'], $foundColumns);
    if (!empty($missingColumns)) {
        echo "   ❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
        exit(1);
    }
    
    echo "\n=== Migration Complete! ===\n";
    echo "✅ All missing columns successfully added to companies table\n";
    
} catch (Exception $e) {
    echo "\n=== Migration Failed! ===\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
