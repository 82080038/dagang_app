<?php
// Database migration script for system settings
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'perdagangan_system';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read SQL file
    $sqlFile = 'database_migrations/create_system_settings_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments and clean SQL
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/^\s*$/m', '', $sql);
    
    // Split SQL into individual statements, but keep CREATE TABLE together with its indexes
    $statements = [];
    $currentStatement = '';
    $inCreateTable = false;
    
    foreach (explode("\n", $sql) as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Skip comment lines
        if (strpos($line, '--') === 0) continue;
        
        // Detect CREATE TABLE start
        if (stripos($line, 'CREATE TABLE') === 0) {
            $inCreateTable = true;
        }
        
        $currentStatement .= $line . ' ';
        
        // End of statement
        if (strpos($line, ';') !== false) {
            if ($inCreateTable && stripos($line, 'CREATE TABLE') === 0) {
                // This is a CREATE TABLE statement, keep it together
                $statements[] = $currentStatement;
                $inCreateTable = false;
            } elseif (!$inCreateTable) {
                // Regular statement
                $statements[] = $currentStatement;
            }
            $currentStatement = '';
        }
    }
    
    echo "Starting migration...\n";
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                // Skip "table already exists" errors
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate key name') !== false) {
                    echo "⚠ Skipped (already exists): " . substr($statement, 0, 50) . "...\n";
                } else {
                    echo "✗ Error: " . $e->getMessage() . "\n";
                    echo "Statement: " . substr($statement, 0, 100) . "...\n";
                }
            }
        }
    }
    
    echo "\nMigration completed successfully!\n";
    
    // Verify tables were created
    $tables = ['system_settings', 'backup_history', 'system_logs', 'email_templates', 'feature_toggles'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
