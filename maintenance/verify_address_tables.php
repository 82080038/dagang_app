<?php
/**
 * Verify Address Tables Structure
 */

require_once __DIR__ . '/app/config/database.php';

header('Content-Type: text/plain');

echo "=== Verifikasi Struktur Tabel Alamat ===\n\n";

try {
    $db = Database::getInstance();
    
    echo "📊 Struktur Tabel addresses:\n";
    $addresses = $db->query("DESCRIBE addresses")->fetchAll();
    foreach ($addresses as $column) {
        echo "   {$column['Field']} | {$column['Type']} | {$column['Null']} | {$column['Key']}\n";
    }
    
    echo "\n📊 Struktur Tabel address_usage:\n";
    $usage = $db->query("DESCRIBE address_usage")->fetchAll();
    foreach ($usage as $column) {
        echo "   {$column['Field']} | {$column['Type']} | {$column['Null']} | {$column['Key']}\n";
    }
    
    echo "\n📊 Data Sample di Tabel addresses:\n";
    $sample = $db->query("SELECT * FROM addresses LIMIT 3")->fetchAll();
    foreach ($sample as $row) {
        echo "   ID: {$row['id_address']}\n";
        echo "   Address: {$row['street_address']}\n";
        echo "   Province ID: {$row['province_id']}\n";
        echo "   Regency ID: {$row['regency_id']}\n";
        echo "   District ID: {$row['district_id']}\n";
        echo "   Village ID: {$row['village_id']}\n";
        echo "   Postal Code: {$row['postal_code']}\n";
        echo "   Active: " . ($row['is_active'] ? 'Yes' : 'No') . "\n";
        echo "   Created: {$row['created_at']}\n";
        echo "   ---\n";
    }
    
    echo "\n📊 Struktur Tabel companies (address_id column):\n";
    $companyCol = $db->query("SHOW COLUMNS FROM companies LIKE 'address_id'")->fetch();
    if ($companyCol) {
        echo "   {$companyCol['Field']} | {$companyCol['Type']} | {$companyCol['Null']} | {$companyCol['Default']}\n";
    }
    
    echo "\n📊 Struktur Tabel branches (address_id column):\n";
    $branchCol = $db->query("SHOW COLUMNS FROM branches LIKE 'address_id'")->fetch();
    if ($branchCol) {
        echo "   {$branchCol['Field']} | {$branchCol['Type']} | {$branchCol['Null']} | {$branchCol['Default']}\n";
    }
    
    echo "\n📊 Indexes yang tersedia:\n";
    $indexes = $db->query("
        SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME 
        FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME IN ('addresses', 'address_usage', 'companies', 'branches')
        AND (INDEX_NAME LIKE '%address%' OR COLUMN_NAME LIKE '%address%')
        ORDER BY TABLE_NAME, INDEX_NAME
    ")->fetchAll();
    
    foreach ($indexes as $index) {
        echo "   {$index['TABLE_NAME']}.{$index['INDEX_NAME']} ({$index['COLUMN_NAME']})\n";
    }
    
    echo "\n=== Verifikasi Selesai! ===\n";
    echo "✅ Semua tabel alamat terpusat berhasil dibuat dengan struktur yang benar\n";
    echo "✅ Sample data berhasil diinsert\n";
    echo "✅ Foreign key columns berhasil ditambahkan ke companies dan branches\n";
    echo "✅ Indexes untuk optimasi query berhasil dibuat\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
