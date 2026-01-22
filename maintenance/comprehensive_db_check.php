<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config/database_multi.php';

try {
    // Access variables from included file
    if (!isset($main_db)) {
        die("Error: \$main_db not defined in database_multi.php\n");
    }
    
    $db = $main_db;
    echo "Connected to " . DB_NAME_MAIN . "\n";
    
    // List of expected tables
    $expectedTables = [
        'companies', 'branches', 'products', 'categories', 
        'suppliers', 'customers', 'transactions', 'transaction_details', 
        'users', 'modules', 'company_settings', 'addresses', 'address_usage'
    ];
    
    // Get existing tables
    $stmt = $db->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n=== Table Existence Check ===\n";
    $missingTables = [];
    foreach ($expectedTables as $table) {
        if (in_array($table, $existingTables)) {
            echo "[OK] Table '$table' exists.\n";
        } else {
            echo "[MISSING] Table '$table' DOES NOT exist.\n";
            $missingTables[] = $table;
        }
    }
    
    echo "\n=== Foreign Key Check (Sampling) ===\n";
    // Helper to check FK
    function checkFK($db, $table, $constraintName) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND CONSTRAINT_NAME = :constraint AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
        $stmt->execute([':schema' => DB_NAME_MAIN, ':table' => $table, ':constraint' => $constraintName]);
        return $stmt->fetchColumn() > 0;
    }
    
    $fksToCheck = [
        'companies' => ['fk_companies_address'],
        'branches' => ['branches_ibfk_1', 'fk_branches_address'],
    ];

    foreach ($fksToCheck as $table => $fks) {
        if (in_array($table, $existingTables)) {
            echo "Checking FKs for '$table':\n";
            foreach ($fks as $fk) {
                if (checkFK($db, $table, $fk)) {
                    echo "  [OK] Constraint '$fk' exists.\n";
                } else {
                    echo "  [WARNING] Constraint '$fk' NOT FOUND.\n";
                    
                    // List actual FKs
                    $stmt = $db->prepare("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
                    $stmt->execute([':schema' => DB_NAME_MAIN, ':table' => $table]);
                    $actualFKs = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo "    Actual FKs: " . implode(', ', $actualFKs) . "\n";
                }
            }
        }
    }
    
    echo "\n=== Column Structure Check (Sampling) ===\n";
    // Check if critical columns exist
    $criticalColumns = [
        'companies' => ['address_id', 'email', 'phone'],
        'branches' => ['address_id', 'company_id'],
        'users' => ['company_id', 'username', 'role']
    ];
    
    foreach ($criticalColumns as $table => $cols) {
        if (in_array($table, $existingTables)) {
            echo "Checking columns for '$table':\n";
            $stmt = $db->query("DESCRIBE $table");
            $dbCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($cols as $col) {
                if (in_array($col, $dbCols)) {
                    echo "  [OK] Column '$col' exists.\n";
                } else {
                    echo "  [MISSING] Column '$col' MISSING.\n";
                }
            }
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
