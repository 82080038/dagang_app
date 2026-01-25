<?php
/**
 * Database Migration Runner
 * 
 * This script will execute all SQL migration files to ensure
 * all database tables and structures are properly created.
 */

// Include database configuration
require_once 'app/config/database.php';

// Migration files to execute (in order)
$migrationFiles = [
    'create_user_management_tables.sql',
    'create_user_management_enhancements.sql',
    'create_system_settings_tables.sql',
    'create_centralized_addresses.sql',
    'create_customer_management_tables.sql',
    'create_supplier_management_tables.sql',
    'create_product_tables.sql',
    'create_inventory_tables.sql',
    'create_transaction_tables.sql',
    'create_financial_management_tables.sql',
    'create_file_management_tables.sql',
    'create_notification_system_tables.sql',
    'create_websocket_integration_tables.sql',
    'create_search_system_tables.sql',
    'create_advanced_reports_tables.sql',
    'create_audit_logging_tables.sql',
    'create_monitoring_backup_tables.sql',
    'add_address_fields_to_main_tables.sql',
    'add_scalability_level.sql',
    'create_missing_tables.sql',
    'fix_address_field_names.sql',
    'create_reporting_tables.sql',
    'create_websocket_tables.sql'
];

echo "Starting database migration...\n";
echo "=====================================\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "Connected to database: " . DB_NAME . "\n\n";

    // Create migration tracking table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migration_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL,
            version VARCHAR(50) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_migration (migration_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $executedMigrations = [];
    $stmt = $pdo->query("SELECT migration_name FROM migration_history");
    while ($row = $stmt->fetch()) {
        $executedMigrations[] = $row['migration_name'];
    }

    $successCount = 0;
    $errorCount = 0;
    $skippedCount = 0;

    foreach ($migrationFiles as $file) {
        echo "Processing: {$file}... ";
        
        // Check if already executed
        if (in_array($file, $executedMigrations)) {
            echo "SKIPPED (already executed)\n";
            $skippedCount++;
            continue;
        }

        $filePath = __DIR__ . '/database_migrations/' . $file;
        
        if (!file_exists($filePath)) {
            echo "ERROR (file not found)\n";
            $errorCount++;
            continue;
        }

        try {
            // Read SQL file
            $sql = file_get_contents($filePath);
            
            // Remove comments and clean up SQL
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            $sql = trim($sql);
            
            if (empty($sql)) {
                echo "SKIPPED (empty file)\n";
                $skippedCount++;
                continue;
            }

            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            // Record migration
            $stmt = $pdo->prepare("INSERT INTO migration_history (migration_name, version) VALUES (?, ?)");
            $stmt->execute([$file, '1.0']);
            
            echo "SUCCESS\n";
            $successCount++;
            
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }

    echo "\n=====================================\n";
    echo "Migration Summary:\n";
    echo "Success: {$successCount}\n";
    echo "Skipped: {$skippedCount}\n";
    echo "Errors: {$errorCount}\n";
    echo "=====================================\n";

    if ($errorCount > 0) {
        echo "⚠️  Some migrations failed. Please check the errors above.\n";
    } else {
        echo "✅ All migrations completed successfully!\n";
    }

    // Show final table count
    $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
    $tableCount = $stmt->fetch()['table_count'];
    echo "\nDatabase now has {$tableCount} tables.\n";

} catch (PDOException $e) {
    echo "DATABASE ERROR: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "GENERAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
