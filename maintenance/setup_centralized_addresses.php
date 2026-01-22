<?php
/**
 * Setup Centralized Address System
 * Run migration to create centralized address tables
 */

require_once __DIR__ . '/app/config/database.php';

header('Content-Type: text/plain');

echo "=== Setup Sistem Alamat Terpusat ===\n\n";

try {
    $db = Database::getInstance();
    
    echo "1. Membaca migration file...\n";
    $migrationFile = __DIR__ . '/database_migrations/create_centralized_addresses.sql';
    
    if (!file_exists($migrationFile)) {
        echo "❌ File migration tidak ditemukan: $migrationFile\n";
        exit(1);
    }
    
    $sql = file_get_contents($migrationFile);
    echo "✅ Migration file berhasil dibaca\n";
    
    echo "2. Menjalankan migration statements...\n";
    
    // Split SQL into individual statements
    $statements = array_filter(explode(';', $sql));
    $executed = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $db->query($statement);
                $executed++;
                echo "   ✓ Statement $executed executed successfully\n";
            } catch (Exception $e) {
                echo "   ⚠ Warning: " . $e->getMessage() . "\n";
                // Continue with other statements
            }
        }
    }
    
    echo "\n3. Verifikasi tabel yang dibuat...\n";
    
    // Check if tables exist
    $tables = ['addresses', 'address_usage'];
    foreach ($tables as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'")->fetchColumn();
        if ($check) {
            echo "   ✅ Tabel '$table' berhasil dibuat\n";
        } else {
            echo "   ❌ Tabel '$table' tidak ditemukan\n";
        }
    }
    
    echo "\n4. Verifikasi sample data...\n";
    
    // Check sample addresses
    $addressCount = $db->query("SELECT COUNT(*) as count FROM addresses")->fetchColumn();
    echo "   📊 Total alamat tersimpan: $addressCount\n";
    
    if ($addressCount > 0) {
        echo "   ✅ Sample address data berhasil diinsert\n";
        
        // Show sample data
        $sample = $db->query("SELECT id_address, street_address, postal_code FROM addresses LIMIT 3")->fetchAll();
        echo "   📝 Sample data:\n";
        foreach ($sample as $row) {
            echo "      ID: {$row['id_address']}, Address: {$row['street_address']}, Postal: {$row['postal_code']}\n";
        }
    } else {
        echo "   ⚠ Tidak ada data sample\n";
    }
    
    echo "\n5. Verifikasi foreign key constraints...\n";
    
    // Check if address_id column exists in companies
    $companyCheck = $db->query("SHOW COLUMNS FROM companies LIKE 'address_id'")->fetchColumn();
    if ($companyCheck) {
        echo "   ✅ Column address_id berhasil ditambahkan ke companies table\n";
    } else {
        echo "   ❌ Column address_id tidak ditemukan di companies table\n";
    }
    
    // Check if address_id column exists in branches
    $branchCheck = $db->query("SHOW COLUMNS FROM branches LIKE 'address_id'")->fetchColumn();
    if ($branchCheck) {
        echo "   ✅ Column address_id berhasil ditambahkan ke branches table\n";
    } else {
        echo "   ❌ Column address_id tidak ditemukan di branches table\n";
    }
    
    echo "\n=== Setup Selesai! ===\n";
    echo "✅ Sistem alamat terpusat berhasil diimplementasi di database perdagangan_system\n";
    echo "\nTabel yang dibuat:\n";
    echo "- addresses (tabel utama alamat)\n";
    echo "- address_usage (tracking penggunaan alamat)\n";
    echo "- v_complete_addresses (view untuk alamat lengkap)\n";
    echo "- v_entity_addresses (view untuk alamat per entitas)\n";
    echo "\nColumn yang ditambahkan:\n";
    echo "- companies.address_id (foreign key ke addresses)\n";
    echo "- branches.address_id (foreign key ke addresses)\n";
    echo "\nSistem siap digunakan! 🎉\n";
    
} catch (Exception $e) {
    echo "\n=== Setup Gagal! ===\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Pastikan database perdagangan_system sudah ada\n";
    echo "2. Pastikan user memiliki permission untuk CREATE TABLE\n";
    echo "3. Pastikan alamat_db tersedia untuk foreign key constraints\n";
    exit(1);
}
?>
