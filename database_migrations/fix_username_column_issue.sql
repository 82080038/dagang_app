-- =====================================================
-- FIX USERNAME COLUMN ISSUE - MEMBERS TABLE
-- =====================================================
-- Problem: Query mencari 'username' tapi tabel menggunakan 'member_code'
-- Solution: Tambahkan kolom username atau update query
-- =====================================================

USE perdagangan_system;

-- Opsi 1: Tambahkan kolom username (recommended)
ALTER TABLE members 
ADD COLUMN username VARCHAR(50) GENERATED ALWAYS AS (member_code) VIRTUAL;

-- Opsi 2: Jika ingin username terpisah, uncomment ini:
-- ALTER TABLE members 
-- ADD COLUMN username VARCHAR(50) AFTER member_code,
-- ADD UNIQUE KEY uk_username (username);

-- Update existing data jika menggunakan opsi 2:
-- UPDATE members SET username = member_code WHERE username IS NULL;

-- Verifikasi struktur
DESCRIBE members;
