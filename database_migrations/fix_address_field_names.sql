-- =====================================================
-- FIX ADDRESS FIELD NAMES INCONSISTENCY
-- Standardize all address fields to use 'address_detail'
-- =====================================================

-- 1. Update addresses table to use address_detail instead of street_address
ALTER TABLE addresses 
CHANGE COLUMN street_address address_detail TEXT NOT NULL COMMENT 'Alamat jalan lengkap (manual input)';

-- 2. Update views that reference street_address
DROP VIEW IF EXISTS v_complete_addresses;

CREATE OR REPLACE VIEW v_complete_addresses AS
SELECT 
    a.id_address,
    a.address_detail,
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
    v.postal_code as village_postal_code,
    CONCAT(
        a.address_detail, ', ',
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
LEFT JOIN alamat_db.villages v ON a.village_id = v.id
WHERE a.is_active = 1;

CREATE OR REPLACE VIEW v_entity_addresses AS
SELECT 
    e.id_entity_address,
    e.entity_type,
    e.entity_id,
    e.usage_type,
    e.is_active,
    a.id_address,
    a.address_detail,
    a.postal_code,
    p.name as province_name,
    r.name as regency_name,
    d.name as district_name,
    v.name as village_name,
    v.postal_code as village_postal_code,
    CONCAT(
        a.address_detail, ', ',
        v.name, ', ',
        d.name, ', ',
        r.name, ', ',
        p.name,
        IF(a.postal_code IS NOT NULL, CONCAT(' ', a.postal_code), '')
    ) as full_address
FROM entity_addresses e
LEFT JOIN addresses a ON e.address_id = a.id_address
LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
LEFT JOIN alamat_db.districts d ON a.district_id = d.id
LEFT JOIN alamat_db.villages v ON a.village_id = v.id
WHERE e.is_active = 1;

-- 3. Update existing data (if any)
UPDATE addresses SET address_detail = street_address WHERE address_detail IS NULL OR address_detail = '';

-- 4. Verify the changes
SELECT 'Address table updated successfully' as status;
SELECT COUNT(*) as total_addresses FROM addresses WHERE address_detail IS NOT NULL;
