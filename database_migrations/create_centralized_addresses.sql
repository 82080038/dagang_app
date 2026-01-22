-- Migration: Create Centralized Addresses Table
-- Date: 2026-01-22
-- Description: Create centralized addresses table with references to alamat_db

USE perdagangan_system;

-- Create centralized addresses table
CREATE TABLE IF NOT EXISTS addresses (
    id_address INT AUTO_INCREMENT PRIMARY KEY,
    street_address TEXT NOT NULL COMMENT 'Alamat jalan lengkap (manual input)',
    province_id INT UNSIGNED NOT NULL COMMENT 'Reference to alamat_db.provinces.id',
    regency_id INT UNSIGNED NOT NULL COMMENT 'Reference to alamat_db.regencies.id', 
    district_id INT UNSIGNED NOT NULL COMMENT 'Reference to alamat_db.districts.id',
    village_id INT UNSIGNED NOT NULL COMMENT 'Reference to alamat_db.villages.id',
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
    INDEX idx_postal_code (postal_code),
    
    -- Foreign key constraints (if alamat_db is accessible)
    CONSTRAINT fk_address_province FOREIGN KEY (province_id) REFERENCES alamat_db.provinces(id),
    CONSTRAINT fk_address_regency FOREIGN KEY (regency_id) REFERENCES alamat_db.regencies(id),
    CONSTRAINT fk_address_district FOREIGN KEY (district_id) REFERENCES alamat_db.districts(id),
    CONSTRAINT fk_address_village FOREIGN KEY (village_id) REFERENCES alamat_db.villages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel terpusat untuk data alamat dengan referensi ke alamat_db';

-- Create address usage table (for tracking which entities use which addresses)
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
    
    -- Foreign key
    CONSTRAINT fk_usage_address FOREIGN KEY (address_id) REFERENCES addresses(id_address) ON DELETE CASCADE,
    
    -- Unique constraint: satu alamat tidak bisa digunakan oleh entitas yang sama dengan usage_type yang sama
    UNIQUE KEY uk_entity_address_usage (entity_type, entity_id, usage_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracking penggunaan alamat oleh berbagai entitas';

-- Insert sample addresses for testing
INSERT INTO addresses (street_address, province_id, regency_id, district_id, village_id, postal_code) VALUES
('Jl. Merdeka No. 123, RT 001/RW 002', 1, 1, 1, 1, '10310'),
('Jl. Sudirman No. 456, RT 003/RW 004', 1, 2, 2, 2, '10250'),
('Jl. Gatot Subroto No. 789, RT 005/RW 006', 1, 3, 3, 3, '10460'),
('Jl. Thamrin No. 321, RT 007/RW 008', 1, 4, 4, 4, '10110'),
('Jl. Hayam Wuruk No. 654, RT 009/RW 010', 1, 5, 5, 5, '10120');

-- Update companies table to use address_id instead of individual address fields
ALTER TABLE companies 
ADD COLUMN address_id INT NULL AFTER email,
ADD FOREIGN KEY (address_id) REFERENCES addresses(id_address) ON DELETE SET NULL,
ADD INDEX idx_address_id (address_id);

-- Update branches table to use address_id instead of individual address fields  
ALTER TABLE branches
ADD COLUMN address_id INT NULL AFTER email,
ADD FOREIGN KEY (address_id) REFERENCES addresses(id_address) ON DELETE SET NULL,
ADD INDEX idx_address_id (address_id);

-- Create view for complete address information (with alamat_db join)
CREATE OR REPLACE VIEW v_complete_addresses AS
SELECT 
    a.id_address,
    a.street_address,
    a.postal_code,
    a.latitude,
    a.longitude,
    a.is_active,
    a.created_at,
    a.updated_at,
    p.name as province_name,
    r.name as regency_name,
    d.name as district_name,
    v.name as village_name,
    CONCAT(
        a.street_address, ', ',
        v.name, ', ',
        d.name, ', ',
        r.name, ', ',
        p.name,
        IF(a.postal_code IS NOT NULL, CONCAT(' ', a.postal_code), '')
    ) as full_address
FROM addresses a
LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id  
LEFT JOIN alamat_db.districts d ON a.district_id = d.id
LEFT JOIN alamat_db.villages v ON a.village_id = v.id;

-- Create view for entities with their addresses
CREATE OR REPLACE VIEW v_entity_addresses AS
SELECT 
    e.entity_type,
    e.entity_id,
    e.usage_type,
    e.is_active,
    a.id_address,
    a.street_address,
    a.postal_code,
    p.name as province_name,
    r.name as regency_name,
    d.name as district_name,
    v.name as village_name,
    CONCAT(
        a.street_address, ', ',
        v.name, ', ',
        d.name, ', ',
        r.name, ', ',
        p.name,
        IF(a.postal_code IS NOT NULL, CONCAT(' ', a.postal_code), '')
    ) as full_address
FROM address_usage e
JOIN addresses a ON e.address_id = a.id_address
LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
LEFT JOIN alamat_db.districts d ON a.district_id = d.id
LEFT JOIN alamat_db.villages v ON a.village_id = v.id
WHERE e.is_active = 1 AND a.is_active = 1;
