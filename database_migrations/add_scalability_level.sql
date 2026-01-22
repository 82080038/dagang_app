-- Migration: Add scalability_level field to companies table
-- Date: 2026-01-22
-- Description: Add missing scalability_level column to companies table

-- Check if column exists, if not add it
SET @dbname = DATABASE();
SET @tablename = 'companies';
SET @columnname = 'scalability_level';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_schema = @dbname)
      AND (table_name = @tablename)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM(\'1\',\'2\',\'3\',\'4\',\'5\',\'6\') DEFAULT \'1\' AFTER company_type;')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for scalability_level
SET @indexname = 'idx_scalability_level';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_schema = @dbname)
      AND (table_name = @tablename)
      AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (', @columnname, ');')
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

-- Update existing records with default scalability levels based on company_type
UPDATE companies SET scalability_level = '1' WHERE company_type IN ('individual', 'personal') AND scalability_level IS NULL;
UPDATE companies SET scalability_level = '2' WHERE company_type IN ('warung', 'kios') AND scalability_level IS NULL;
UPDATE companies SET scalability_level = '3' WHERE company_type IN ('toko_kelontong', 'toko') AND scalability_level IS NULL;
UPDATE companies SET scalability_level = '4' WHERE company_type IN ('minimarket', 'pengusaha_menengah') AND scalability_level IS NULL;
UPDATE companies SET scalability_level = '5' WHERE company_type IN ('distributor') AND scalability_level IS NULL;
UPDATE companies SET scalability_level = '6' WHERE company_type IN ('perusahaan_besar', 'franchise') AND scalability_level IS NULL;

-- Set default level 1 for any remaining NULL values
UPDATE companies SET scalability_level = '1' WHERE scalability_level IS NULL;
