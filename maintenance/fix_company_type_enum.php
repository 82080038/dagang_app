<?php
// Fix company_type ENUM to match form options
require_once 'app/config/config.php';

echo "=== FIXING COMPANY_TYPE ENUM ===\n\n";

try {
    // Get database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "1. Current ENUM structure...\n";
    $result = $pdo->query("SHOW COLUMNS FROM companies LIKE 'company_type'");
    $column = $result->fetch();
    echo "Current: " . $column['Type'] . "\n\n";

    echo "2. Updating ENUM to match form options...\n";
    
    $newEnum = "ENUM('individual','warung','kios','toko_kelontong','minimarket','pengusaha_menengah','distributor','koperasi','perusahaan_besar','franchise','pusat','cabang')";
    
    $sql = "ALTER TABLE companies MODIFY COLUMN company_type " . $newEnum . " DEFAULT 'individual'";
    
    echo "SQL: " . $sql . "\n";
    $pdo->exec($sql);
    echo "✅ ENUM updated successfully!\n\n";

    echo "3. Verifying new structure...\n";
    $result = $pdo->query("SHOW COLUMNS FROM companies LIKE 'company_type'");
    $column = $result->fetch();
    echo "New: " . $column['Type'] . "\n\n";

    echo "4. Current data after update...\n";
    $result = $pdo->query("SELECT id_company, company_name, company_type FROM companies ORDER BY id_company");
    while ($row = $result->fetch()) {
        echo "ID: " . $row['id_company'] . " | " . $row['company_name'] . " | Type: " . $row['company_type'] . "\n";
    }

    echo "\n=== COMPLETED ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
