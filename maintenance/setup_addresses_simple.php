<?php
/**
 * Setup Centralized Address System (Simplified)
 * Create tables without foreign key constraints first
 */

require_once __DIR__ . '/app/config/database.php';

header('Content-Type: text/plain');

echo "=== Setup Sistem Alamat Terpusat (Simplified) ===\n\n";

try {
    $db = Database::getInstance();
    
    echo "1. Membuat tabel addresses (tanpa foreign key constraints)...\n";
    
    // Create addresses table without FK constraints
    $sql1 = "
        CREATE TABLE IF NOT EXISTS addresses (
            id_address INT AUTO_INCREMENT PRIMARY KEY,
            province_id INT NOT NULL COMMENT 'Reference to alamat_db.provinces.id',
            regency_id INT NOT NULL COMMENT 'Reference to alamat_db.regencies.id', 
            district_id INT NOT NULL COMMENT 'Reference to alamat_db.districts.id',
            village_id INT NOT NULL COMMENT 'Reference to alamat_db.villages.id',
            address_detail TEXT NOT NULL COMMENT 'Alamat jalan lengkap (manual input)',
            postal_code VARCHAR(10) NULL COMMENT 'Kode pos (optional)',
            latitude DECIMAL(10,8) NULL COMMENT 'Koordinat latitude (optional)',
            longitude DECIMAL(11,8) NULL COMMENT 'Koordinat longitude (optional)',
            is_active BOOLEAN DEFAULT TRUE COMMENT 'Status aktif alamat',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            -- Indexes
            INDEX idx_province_id (province_id),
            INDEX idx_regency_id (regency_id),
            INDEX idx_district_id (district_id),
            INDEX idx_village_id (village_id),
            INDEX idx_active (is_active),
            INDEX idx_postal_code (postal_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='Tabel terpusat untuk data alamat dengan referensi ke alamat_db'
    ";
    
    $db->query($sql1);
    echo "   ✅ Tabel addresses berhasil dibuat\n";
    
    echo "2. Membuat tabel address_usage...\n";
    
    $sql2 = "
        CREATE TABLE IF NOT EXISTS address_usage (
            id_usage INT AUTO_INCREMENT PRIMARY KEY,
            address_id INT NOT NULL,
            entity_type ENUM('company', 'branch', 'member', 'supplier', 'customer') NOT NULL,
            entity_id INT NOT NULL,
            usage_type ENUM('primary', 'billing', 'shipping', 'contact') DEFAULT 'primary',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            -- Indexes
            INDEX idx_address_id (address_id),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_usage_type (usage_type),
            INDEX idx_active (is_active),
            
            -- Unique constraint: satu alamat tidak bisa digunakan oleh entitas yang sama dengan usage_type yang sama
            UNIQUE KEY uk_entity_address_usage (entity_type, entity_id, usage_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='Tracking penggunaan alamat oleh berbagai entitas'
    ";
    
    $db->query($sql2);
    echo "   ✅ Tabel address_usage berhasil dibuat\n";
    
    echo "3. Menambahkan address_id ke tabel companies...\n";
    
    // Add address_id to companies
    $sql3 = "ALTER TABLE companies ADD COLUMN address_id INT NULL AFTER email";
    try {
        $db->query($sql3);
        echo "   ✅ Column address_id berhasil ditambahkan ke companies\n";
    } catch (Exception $e) {
        echo "   ⚠ Column address_id sudah ada di companies: " . $e->getMessage() . "\n";
    }
    
    echo "4. Menambahkan address_id ke tabel branches...\n";
    
    // Add address_id to branches
    $sql4 = "ALTER TABLE branches ADD COLUMN address_id INT NULL AFTER email";
    try {
        $db->query($sql4);
        echo "   ✅ Column address_id berhasil ditambahkan ke branches\n";
    } catch (Exception $e) {
        echo "   ⚠ Column address_id sudah ada di branches: " . $e->getMessage() . "\n";
    }
    
    echo "5. Menambahkan indexes untuk address_id...\n";
    
    // Add indexes
    try {
        $db->query("CREATE INDEX idx_company_address_id ON companies (address_id)");
        echo "   ✅ Index idx_company_address_id berhasil dibuat\n";
    } catch (Exception $e) {
        echo "   ⚠ Index idx_company_address_id sudah ada\n";
    }
    
    try {
        $db->query("CREATE INDEX idx_branch_address_id ON branches (address_id)");
        echo "   ✅ Index idx_branch_address_id berhasil dibuat\n";
    } catch (Exception $e) {
        echo "   ⚠ Index idx_branch_address_id sudah ada\n";
    }
    
    echo "6. Insert sample address data...\n";
    
    // Check if data exists
    $check = $db->query("SELECT COUNT(*) as count FROM addresses")->fetchColumn();
    
    if ($check == 0) {
        $sampleAddresses = [
            [
                'address_detail' => 'Jl. Merdeka No. 123, RT 001/RW 002',
                'province_id' => 1,
                'regency_id' => 1,
                'district_id' => 1,
                'village_id' => 1,
                'postal_code' => '10310'
            ],
            [
                'address_detail' => 'Jl. Sudirman No. 456, RT 003/RW 004',
                'province_id' => 1,
                'regency_id' => 2,
                'district_id' => 2,
                'village_id' => 2,
                'postal_code' => '10250'
            ],
            [
                'address_detail' => 'Jl. Gatot Subroto No. 789, RT 005/RW 006',
                'province_id' => 1,
                'regency_id' => 3,
                'district_id' => 3,
                'village_id' => 3,
                'postal_code' => '10460'
            ]
        ];
        
        foreach ($sampleAddresses as $address) {
            $db->query("
                INSERT INTO addresses (address_detail, province_id, regency_id, district_id, village_id, postal_code)
                VALUES (:address_detail, :province_id, :regency_id, :district_id, :village_id, :postal_code)
            ", $address);
        }
        
        echo "   ✅ Sample address data berhasil diinsert\n";
    } else {
        echo "   ℹ Sample address data sudah ada\n";
    }
    
    echo "7. Verifikasi hasil...\n";
    
    // Check tables
    $tables = ['addresses', 'address_usage'];
    foreach ($tables as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'")->fetchColumn();
        if ($check) {
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetchColumn();
            echo "   ✅ Tabel '$table' ada ($count records)\n";
        } else {
            echo "   ❌ Tabel '$table' tidak ditemukan\n";
        }
    }
    
    // Check columns
    $columns = ['companies.address_id', 'branches.address_id'];
    foreach ($columns as $column) {
        list($table, $col) = explode('.', $column);
        $check = $db->query("SHOW COLUMNS FROM $table LIKE '$col'")->fetchColumn();
        if ($check) {
            echo "   ✅ Column $column ada\n";
        } else {
            echo "   ❌ Column $column tidak ditemukan\n";
        }
    }
    
    echo "\n=== Setup Berhasil! ===\n";
    echo "✅ Sistem alamat terpusat berhasil diimplementasi di database perdagangan_system\n";
    echo "\n📊 Statistik:\n";
    
    $addressCount = $db->query("SELECT COUNT(*) as count FROM addresses")->fetchColumn();
    $usageCount = $db->query("SELECT COUNT(*) as count FROM address_usage")->fetchColumn();
    
    echo "- Total alamat: $addressCount\n";
    echo "- Total penggunaan: $usageCount\n";
    
    echo "\n📋 Tabel yang dibuat:\n";
    echo "- addresses (tabel utama alamat)\n";
    echo "- address_usage (tracking penggunaan alamat)\n";
    echo "\n🔗 Kolom yang ditambahkan:\n";
    echo "- companies.address_id (foreign key ke addresses)\n";
    echo "- branches.address_id (foreign key ke addresses)\n";
    echo "\n🎯 Sistem siap digunakan!\n";
    echo "Catatan: Foreign key constraints ke alamat_db akan ditambahkan saat alamat_db tersedia\n";
    
} catch (Exception $e) {
    echo "\n=== Setup Gagal! ===\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
