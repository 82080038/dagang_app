-- =====================================================
-- ADD ADDRESS FIELDS TO MAIN TABLES
-- Add address_detail, province_id, regency_id, district_id, village_id to companies and branches
-- =====================================================

-- 1. Add address fields to companies table
ALTER TABLE companies 
ADD COLUMN IF NOT EXISTS address_detail TEXT NULL COMMENT 'Alamat jalan lengkap (manual input)',
ADD COLUMN IF NOT EXISTS province_id INT NULL COMMENT 'Reference to alamat_db.provinces.id',
ADD COLUMN IF NOT EXISTS regency_id INT NULL COMMENT 'Reference to alamat_db.regencies.id',
ADD COLUMN IF NOT EXISTS district_id INT NULL COMMENT 'Reference to alamat_db.districts.id',
ADD COLUMN IF NOT EXISTS village_id INT NULL COMMENT 'Reference to alamat_db.villages.id',
ADD COLUMN IF NOT EXISTS postal_code VARCHAR(10) NULL COMMENT 'Kode pos',
ADD COLUMN IF NOT EXISTS address_id INT NULL COMMENT 'Reference to centralized addresses table';

-- 2. Add address fields to branches table
ALTER TABLE branches 
ADD COLUMN IF NOT EXISTS address_detail TEXT NULL COMMENT 'Alamat jalan lengkap (manual input)',
ADD COLUMN IF NOT EXISTS province_id INT NULL COMMENT 'Reference to alamat_db.provinces.id',
ADD COLUMN IF NOT EXISTS regency_id INT NULL COMMENT 'Reference to alamat_db.regencies.id',
ADD COLUMN IF NOT EXISTS district_id INT NULL COMMENT 'Reference to alamat_db.districts.id',
ADD COLUMN IF NOT EXISTS village_id INT NULL COMMENT 'Reference to alamat_db.villages.id',
ADD COLUMN IF NOT EXISTS postal_code VARCHAR(10) NULL COMMENT 'Kode pos',
ADD COLUMN IF NOT EXISTS address_id INT NULL COMMENT 'Reference to centralized addresses table';

-- 3. Add indexes for performance
ALTER TABLE companies 
ADD INDEX IF EXISTS idx_province_id (province_id),
ADD INDEX IF EXISTS idx_regency_id (regency_id),
ADD INDEX IF EXISTS idx_district_id (district_id),
ADD INDEX IF EXISTS idx_village_id (village_id),
ADD INDEX IF EXISTS idx_address_id (address_id);

ALTER TABLE branches 
ADD INDEX IF EXISTS idx_province_id (province_id),
ADD INDEX IF EXISTS idx_regency_id (regency_id),
ADD INDEX IF EXISTS idx_district_id (district_id),
ADD INDEX IF EXISTS idx_village_id (village_id),
ADD INDEX IF EXISTS idx_address_id (address_id);

-- 4. Add foreign key constraints (optional - can be added later)
-- ALTER TABLE companies 
-- ADD CONSTRAINT fk_companies_address_id FOREIGN KEY (address_id) REFERENCES addresses(id_address) ON DELETE SET NULL;

-- ALTER TABLE branches 
-- ADD CONSTRAINT fk_branches_address_id FOREIGN KEY (address_id) REFERENCES addresses(id_address) ON DELETE SET NULL;

-- 5. Update existing data (migrate from old address field if exists)
UPDATE companies SET 
    address_detail = address 
WHERE address_detail IS NULL AND address IS NOT NULL AND address != '';

UPDATE branches SET 
    address_detail = address 
WHERE address_detail IS NULL AND address IS NOT NULL AND address != '';

-- 6. Verify the changes
SELECT 'Address fields added to main tables successfully' as status;
DESCRIBE companies;
DESCRIBE branches;
