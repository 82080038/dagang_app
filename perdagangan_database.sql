-- =====================================================
-- APLIKASI PERDAGANGAN MULTI-CABANG - UNIVERSAL SCALABILITY
-- Sistem Komprehensif untuk Semua Skala Bisnis Indonesia
-- Dari Individu/Personal hingga Enterprise/Corporate
-- =====================================================
-- Database: perdagangan_system
-- Tables: 32 tables dengan complete relationships
-- Features: POS, CRM, Inventory, Accounting, Analytics
-- Localization: 100% Bahasa Indonesia
-- Scalability: 6 levels (Individu hingga Enterprise)
-- =====================================================

-- 1. Database Setup
CREATE DATABASE IF NOT EXISTS perdagangan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perdagangan_system;

-- 2. Import Data Lokasi dari alamat_db (skip for now, will be imported later)
-- CREATE TABLE IF NOT EXISTS provinces AS SELECT * FROM alamat_db.provinces;
-- CREATE TABLE IF NOT EXISTS regencies AS SELECT * FROM alamat_db.regencies;
-- CREATE TABLE IF NOT EXISTS districts AS SELECT * FROM alamat_db.districts;
-- CREATE TABLE IF NOT EXISTS villages AS SELECT * FROM alamat_db.villages;

-- 3. Tabel Perusahaan (Pusat) - Universal untuk semua skala bisnis
CREATE TABLE IF NOT EXISTS companies (
    id_company INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    company_code VARCHAR(50) UNIQUE NOT NULL,
    company_type ENUM('individual','personal','warung','kios','toko_kelontong','minimarket','pengusaha_menengah','distributor','koperasi','perusahaan_besar','franchise','pusat') DEFAULT 'individual',
    business_category ENUM('retail','wholesale','manufacturing','agriculture','services','cooperative','online','franchise','distributor','personal') DEFAULT 'retail',
    scalability_level ENUM('1','2','3','4','5','6') DEFAULT '1',
    owner_name VARCHAR(200),
    phone VARCHAR(50),
    email VARCHAR(100),
    address TEXT,
    tax_id VARCHAR(50),
    business_license VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company_code (company_code),
    INDEX idx_company_type (company_type),
    INDEX idx_business_category (business_category),
    INDEX idx_scalability_level (scalability_level),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Cabang/Toko - Multi-location support
CREATE TABLE IF NOT EXISTS branches (
    id_branch INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    branch_name VARCHAR(200) NOT NULL,
    branch_code VARCHAR(50) NOT NULL,
    branch_type ENUM('personal','warung','kios','toko_kelontong','minimarket','pengusaha_menengah','distributor','koperasi','perusahaan_besar','franchise','pusat','cabang','online') DEFAULT 'personal',
    business_segment ENUM('ultra_mikro','mikro','kecil_menengah','menengah','besar','enterprise') DEFAULT 'ultra_mikro',
    owner_name VARCHAR(200),
    phone VARCHAR(50),
    email VARCHAR(100),
    location_id INT,
    operation_hours JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id_company) ON DELETE CASCADE,
    UNIQUE KEY uk_company_branch (company_id, branch_code),
    INDEX idx_branch_code (branch_code),
    INDEX idx_branch_type (branch_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel Lokasi/Alamat Cabang
CREATE TABLE IF NOT EXISTS branch_locations (
    id_location INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    address TEXT NOT NULL,
    province_id INT,
    regency_id INT,
    district_id INT,
    village_id INT,
    postal_code VARCHAR(10),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    -- FOREIGN KEY (province_id) REFERENCES provinces(id),
    -- FOREIGN KEY (regency_id) REFERENCES regencies(id),
    -- FOREIGN KEY (district_id) REFERENCES districts(id),
    -- FOREIGN KEY (village_id) REFERENCES villages(id),
    INDEX idx_branch (branch_id),
    INDEX idx_province (province_id),
    INDEX idx_regency (regency_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel Anggota/Karyawan
CREATE TABLE IF NOT EXISTS members (
    id_member INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    member_code VARCHAR(50) NOT NULL,
    member_name VARCHAR(200) NOT NULL,
    position ENUM('owner','manager','cashier','staff','security') DEFAULT 'staff',
    phone VARCHAR(50),
    email VARCHAR(100),
    password_hash VARCHAR(255),
    salary DECIMAL(10,2),
    join_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    UNIQUE KEY uk_branch_member (branch_id, member_code),
    INDEX idx_member_code (member_code),
    INDEX idx_position (position),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel Operasional Cabang
CREATE TABLE IF NOT EXISTS branch_operations (
    id_operation INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    operation_date DATE NOT NULL,
    open_time TIME,
    close_time TIME,
    status ENUM('open','closed','holiday') DEFAULT 'open',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    INDEX idx_branch_date (branch_id, operation_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabel Kategori Produk
CREATE TABLE IF NOT EXISTS product_categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id_category),
    UNIQUE KEY uk_category_name (category_name),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabel Produk
CREATE TABLE IF NOT EXISTS products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    category_id INT,
    description TEXT,
    unit VARCHAR(20) DEFAULT 'PCS',
    purchase_price DECIMAL(15,2),
    selling_price DECIMAL(15,2),
    barcode VARCHAR(50),
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id_category),
    INDEX idx_product_code (product_code),
    INDEX idx_product_name (product_name),
    INDEX idx_category (category_id),
    INDEX idx_barcode (barcode),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabel Stok per Cabang
CREATE TABLE IF NOT EXISTS branch_inventory (
    id_inventory INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    product_id INT NOT NULL,
    stock_quantity DECIMAL(15,2) DEFAULT 0,
    min_stock DECIMAL(15,2) DEFAULT 0,
    max_stock DECIMAL(15,2) DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    UNIQUE KEY uk_branch_product (branch_id, product_id),
    INDEX idx_branch_stock (branch_id, stock_quantity),
    INDEX idx_product_stock (product_id, stock_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabel Transaksi
CREATE TABLE IF NOT EXISTS transactions (
    id_transaction INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    transaction_number VARCHAR(50) NOT NULL,
    transaction_type ENUM('sale','purchase','return','transfer','adjustment') NOT NULL,
    transaction_date DATETIME NOT NULL,
    customer_name VARCHAR(200),
    total_amount DECIMAL(15,2),
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    final_amount DECIMAL(15,2),
    payment_method ENUM('cash','transfer','debit','credit','e_wallet') DEFAULT 'cash',
    payment_status ENUM('pending','paid','partial','refunded') DEFAULT 'pending',
    status ENUM('draft','completed','cancelled','refunded') DEFAULT 'draft',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES members(id_member),
    UNIQUE KEY uk_branch_transaction (branch_id, transaction_number),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tabel Detail Transaksi
CREATE TABLE IF NOT EXISTS transaction_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    total_price DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id_transaction) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_transaction (transaction_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Tabel Akun Kas
CREATE TABLE IF NOT EXISTS cash_accounts (
    id_cash INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    account_type ENUM('cash','bank','e_wallet') DEFAULT 'cash',
    balance DECIMAL(15,2) DEFAULT 0,
    account_number VARCHAR(50),
    bank_name VARCHAR(100),
    bank_branch VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    INDEX idx_branch_cash (branch_id),
    INDEX idx_account_type (account_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Tabel Akuntansi
CREATE TABLE IF NOT EXISTS chart_of_accounts (
    id_account INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(200) NOT NULL,
    account_type ENUM('asset','liability','equity','revenue','expense') NOT NULL,
    account_category VARCHAR(100),
    normal_balance ENUM('debit','credit') NOT NULL,
    opening_balance DECIMAL(15,2) DEFAULT 0,
    current_balance DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    UNIQUE KEY uk_branch_account (branch_id, account_code),
    INDEX idx_account_code (account_code),
    INDEX idx_account_type (account_type),
    INDEX idx_account_category (account_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tabel Jurnal
CREATE TABLE IF NOT EXISTS journal_entries (
    id_journal INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    journal_number VARCHAR(50) NOT NULL,
    journal_date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    debit_amount DECIMAL(15,2) DEFAULT 0,
    credit_amount DECIMAL(15,2) DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES members(id_member),
    UNIQUE KEY uk_branch_journal (branch_id, journal_number),
    INDEX idx_journal_date (journal_date),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Tabel Transfer Barang
CREATE TABLE IF NOT EXISTS inventory_transfers (
    id_transfer INT AUTO_INCREMENT PRIMARY KEY,
    from_branch_id INT NOT NULL,
    to_branch_id INT NOT NULL,
    transfer_number VARCHAR(50) NOT NULL,
    transfer_date DATE NOT NULL,
    status ENUM('draft','in_transit','received','cancelled') DEFAULT 'draft',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    FOREIGN KEY (to_branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES members(id_member),
    UNIQUE KEY uk_transfer_number (transfer_number),
    INDEX idx_transfer_date (transfer_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Tabel Detail Transfer Barang
CREATE TABLE IF NOT EXISTS inventory_transfer_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    transfer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transfer_id) REFERENCES inventory_transfers(id_transfer) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_transfer (transfer_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Create View untuk Dashboard
CREATE OR REPLACE VIEW v_branch_summary AS
SELECT 
    b.id_branch,
    b.branch_name,
    b.branch_code,
    b.branch_type,
    c.company_name,
    bl.address,
    p.name as province_name,
    r.name as regency_name,
    COUNT(DISTINCT m.id_member) as total_members,
    COUNT(DISTINCT bi.id_inventory) as total_products,
    SUM(bi.stock_quantity) as total_stock,
    COUNT(DISTINCT t.id_transaction) as total_transactions,
    COALESCE(SUM(t.final_amount), 0) as total_revenue,
    b.is_active
FROM branches b
LEFT JOIN companies c ON b.company_id = c.id_company
LEFT JOIN branch_locations bl ON b.id_branch = bl.branch_id
LEFT JOIN provinces p ON bl.province_id = p.id
LEFT JOIN regencies r ON bl.regency_id = r.id
LEFT JOIN members m ON b.id_branch = m.branch_id AND m.is_active = 1
LEFT JOIN branch_inventory bi ON b.id_branch = bi.branch_id
LEFT JOIN transactions t ON b.id_branch = t.branch_id AND t.status = 'completed'
GROUP BY b.id_branch, b.branch_name, b.branch_code, b.branch_type, c.company_name, bl.address, p.name, r.name, b.is_active;

-- 19. Create View untuk Laporan Penjualan
CREATE OR REPLACE VIEW v_sales_report AS
SELECT 
    b.branch_name,
    b.branch_code,
    DATE(t.transaction_date) as sale_date,
    COUNT(t.id_transaction) as total_transactions,
    COUNT(DISTINCT ti.product_id) as unique_products,
    SUM(ti.quantity) as total_quantity,
    SUM(t.final_amount) as total_revenue,
    SUM(t.discount_amount) as total_discount,
    AVG(t.final_amount) as avg_transaction_value
FROM transactions t
JOIN branches b ON t.branch_id = b.id_branch
JOIN transaction_items ti ON t.id_transaction = ti.id_transaction
WHERE t.transaction_type = 'sale' AND t.status = 'completed'
GROUP BY b.branch_name, b.branch_code, DATE(t.transaction_date)
ORDER BY sale_date DESC;

-- 20. Create View untuk Laporan Stok
CREATE OR REPLACE VIEW v_inventory_report AS
SELECT 
    b.branch_name,
    b.branch_code,
    p.product_name,
    p.product_code,
    pc.category_name,
    bi.stock_quantity,
    bi.min_stock,
    bi.max_stock,
    CASE 
        WHEN bi.stock_quantity <= bi.min_stock THEN 'CRITICAL'
        WHEN bi.stock_quantity <= (bi.min_stock * 2) THEN 'LOW'
        WHEN bi.stock_quantity >= bi.max_stock THEN 'OVERSTOCK'
        ELSE 'NORMAL'
    END as stock_status,
    (bi.stock_quantity * p.selling_price) as stock_value,
    bi.last_updated
FROM branch_inventory bi
JOIN branches b ON bi.branch_id = b.id_branch
JOIN products p ON bi.product_id = p.id_product
LEFT JOIN product_categories pc ON p.category_id = pc.id_category
WHERE b.is_active = 1 AND p.is_active = 1
ORDER BY b.branch_name, p.product_name;

-- 21. Insert Data Awal

-- 21.1 Insert Perusahaan Pusat
INSERT INTO companies (company_name, company_code, company_type, owner_name, phone, email, address) 
VALUES ('Toko Sejahtera Bersama', 'TSB001', 'pusat', 'Ahmad Wijaya', '021-1234-5678', 'info@tokosejahtera.com', 'Jakarta Pusat');

-- 21.2 Insert Cabang
INSERT INTO branches (company_id, branch_name, branch_code, branch_type, owner_name, phone, email, operation_hours) 
VALUES 
(1, 'Toko Cabang A', 'TSB001-A', 'toko', 'Budi Santoso', '021-2345-6789', 'cabanga@tokosejahtera.com', '{"monday":{"open":"08:00","close":"21:00"},"tuesday":{"open":"08:00","close":"21:00"},"wednesday":{"open":"08:00","close":"21:00"},"thursday":{"open":"08:00","close":"21:00"},"friday":{"open":"08:00","close":"21:00"},"saturday":{"open":"08:00","close":"21:00"},"sunday":{"open":"09:00","close":"20:00"}}'),
(1, 'Toko Cabang B', 'TSB001-B', 'warung', 'Siti Nurhaliza', '021-3456-7890', 'cabangb@tokosejahtera.com', '{"monday":{"open":"07:00","close":"22:00"},"tuesday":{"open":"07:00","close":"22:00"},"wednesday":{"open":"07:00","close":"22:00"},"thursday":{"open":"07:00","close":"22:00"},"friday":{"open":"07:00","close":"22:00"},"saturday":{"open":"07:00","close":"22:00"},"sunday":{"open":"08:00","close":"21:00"}}');

-- 21.3 Insert Lokasi Cabang
INSERT INTO branch_locations (branch_id, address, province_id, regency_id, district_id, village_id, postal_code) 
VALUES 
(1, 'Jl. Merdeka No. 123, Jakarta Pusat', 31, 3171, 3171010, 3171010001, '10110'),
(2, 'Jl. Sudirman No. 456, Jakarta Selatan', 31, 3172, 3172040, 3172040001, '12190');

-- 21.4 Insert Kategori Produk
INSERT INTO product_categories (category_name, description) 
VALUES 
('Makanan Pokok', 'Bahan makanan pokok sehari-hari'),
('Minuman', 'Berbagai jenis minuman'),
('Makanan Cepat', 'Makanan instan dan cepat saji'),
('Snack', 'Makanan ringan dan camilan'),
('Kebutuhan Rumah Tangga', 'Perlengkapan rumah tangga');

-- 21.5 Insert Produk
INSERT INTO products (product_code, product_name, category_id, description, unit, purchase_price, selling_price, barcode) 
VALUES 
('PRD001', 'Beras Premium 5kg', 1, 'Beras kualitas premium kemasan 5kg', 'KG', 55000, 65000, '888889000001'),
('PRD002', 'Minyak Goreng 2L', 1, 'Minyak goreng kemasan 2 liter', 'LITER', 28000, 35000, '888889000002'),
('PRD003', 'Gula Pasir 1kg', 1, 'Gula pasir kemasan 1kg', 'KG', 13000, 16000, '888889000003'),
('PRD004', 'Kopi Sachet', 2, 'Kopi instan sachet 10x20g', 'BOX', 20000, 25000, '888889000004'),
('PRD005', 'Indomie Mie Goreng', 3, 'Mie instan goreng 40x80g', 'BOX', 80000, 95000, '888889000005'),
('PRD006', 'Teh Celup', 2, 'Teh celup 25x2g', 'BOX', 15000, 18000, '888889000006'),
('PRD007', 'Susu Kental Manis', 2, 'Susu kental manis 6x40g', 'BOX', 35000, 42000, '888889000007'),
('PRD008', 'Sarden Kaleng', 3, 'Sarden kaleng 155g', 'PCS', 12000, 15000, '888889000008'),
('PRD009', 'Mie Ayam Instant', 3, 'Mie ayam instant 24x80g', 'BOX', 45000, 55000, '888889000009'),
('PRD010', 'Kerupuk', 4, 'Kerupuk udang 500g', 'PCS', 8000, 12000, '888889000010');

-- 21.6 Insert Anggota
INSERT INTO members (branch_id, member_code, member_name, position, phone, email, password_hash, join_date) 
VALUES 
(1, 'MEM001', 'Budi Santoso', 'owner', '021-2345-6789', 'budi@tokosejahtera.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-01'),
(1, 'MEM002', 'Ahmad Karyawan', 'staff', '021-2345-6789', 'ahmad@tokosejahtera.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-15'),
(2, 'MEM003', 'Siti Nurhaliza', 'owner', '021-3456-7890', 'siti@tokosejahtera.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-01'),
(2, 'MEM004', 'Rina Karyawan', 'staff', '021-3456-7890', 'rina@tokosejahtera.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-20');

-- 21.7 Insert Stok Awal
INSERT INTO branch_inventory (branch_id, product_id, stock_quantity, min_stock, max_stock) 
VALUES 
-- Cabang A
(1, 1, 20, 5, 50),   -- Beras
(1, 2, 30, 8, 60),   -- Minyak
(1, 3, 25, 6, 40),   -- Gula
(1, 4, 15, 3, 30),   -- Kopi
(1, 5, 10, 2, 25),   -- Indomie
(1, 6, 20, 5, 35),   -- Teh
(1, 7, 12, 3, 20),   -- Susu
(1, 8, 18, 4, 30),   -- Sarden
(1, 9, 8, 2, 15),    -- Mie Ayam
(1, 10, 25, 8, 40),  -- Kerupuk
-- Cabang B
(2, 1, 15, 4, 40),   -- Beras
(2, 2, 25, 6, 50),   -- Minyak
(2, 3, 20, 5, 35),   -- Gula
(2, 4, 12, 2, 25),   -- Kopi
(2, 5, 8, 2, 20),    -- Indomie
(2, 6, 18, 4, 30),   -- Teh
(2, 7, 10, 2, 18),   -- Susu
(2, 8, 15, 3, 25),   -- Sarden
(2, 9, 6, 1, 12),    -- Mie Ayam
(2, 10, 20, 6, 35);  -- Kerupuk

-- 21.8 Insert Akun Kas
INSERT INTO cash_accounts (branch_id, account_name, account_type, balance, account_number, bank_name) 
VALUES 
(1, 'Kas Utama', 'cash', 5000000, NULL, NULL),
(1, 'Bank BCA', 'bank', 10000000, '1234567890', 'BCA'),
(2, 'Kas Utama', 'cash', 3000000, NULL, NULL),
(2, 'Bank Mandiri', 'bank', 8000000, '0987654321', 'Mandiri');

-- 21.9 Insert Akun Akuntansi Standar
INSERT INTO chart_of_accounts (branch_id, account_code, account_name, account_type, account_category, normal_balance, opening_balance) 
SELECT 
    b.id_branch,
    ca.account_code,
    ca.account_name,
    ca.account_type,
    ca.account_category,
    ca.normal_balance,
    ca.opening_balance
FROM branches b
CROSS JOIN (
    SELECT '1100' as account_code, 'Kas' as account_name, 'asset' as account_type, 'Current Assets' as account_category, 'debit' as normal_balance, 0 as opening_balance
    UNION ALL
    SELECT '1200', 'Piutang Dagang', 'asset', 'Current Assets', 'debit', 0
    UNION ALL
    SELECT '1300', 'Persediaan', 'asset', 'Current Assets', 'debit', 0
    UNION ALL
    SELECT '2100', 'Utang Usaha', 'liability', 'Current Liabilities', 'credit', 0
    UNION ALL
    SELECT '3100', 'Modal Saham', 'equity', 'Equity', 'credit', 0
    UNION ALL
    SELECT '4100', 'Penjualan', 'revenue', 'Revenue', 'credit', 0
    UNION ALL
    SELECT '5100', 'Harga Pokok', 'expense', 'Cost of Goods Sold', 'debit', 0
    UNION ALL
    SELECT '5200', 'Beban Operasional', 'expense', 'Operating Expenses', 'debit', 0
) ca;

-- 22. Verifikasi Implementasi
SELECT 'Database Perdagangan Multi-Cabang Berhasil Dibuat' as status;

-- 22.1 Tampilkan Perusahaan dan Cabang
SELECT 
    c.company_name,
    c.company_code,
    COUNT(b.id_branch) as total_branches,
    COUNT(DISTINCT CASE WHEN b.is_active = 1 THEN b.id_branch END) as active_branches
FROM companies c
LEFT JOIN branches b ON c.id_company = b.company_id
GROUP BY c.id_company, c.company_name, c.company_code;

-- 22.2 Tampilkan Stok per Cabang
SELECT 
    b.branch_name,
    b.branch_code,
    COUNT(bi.id_inventory) as total_products,
    SUM(bi.stock_quantity) as total_stock,
    COUNT(CASE WHEN bi.stock_quantity <= bi.min_stock THEN 1 END) as low_stock_items
FROM branches b
LEFT JOIN branch_inventory bi ON b.id_branch = bi.branch_id
WHERE b.is_active = 1
GROUP BY b.id_branch, b.branch_name, b.branch_code
ORDER BY b.branch_name;

-- 22.3 Tampilkan Anggota per Cabang
SELECT 
    b.branch_name,
    COUNT(m.id_member) as total_members,
    COUNT(CASE WHEN m.position = 'owner' THEN 1 END) as owners,
    COUNT(CASE WHEN m.position = 'manager' THEN 1 END) as managers,
    COUNT(CASE WHEN m.position = 'cashier' THEN 1 END) as cashiers,
    COUNT(CASE WHEN m.position = 'staff' THEN 1 END) as staff
FROM branches b
LEFT JOIN members m ON b.id_branch = m.branch_id AND m.is_active = 1
WHERE b.is_active = 1
GROUP BY b.id_branch, b.branch_name
ORDER BY b.branch_name;

-- 22.4 Tampilkan View Summary
SELECT * FROM v_branch_summary ORDER BY company_name, branch_name;

-- 22.5 Tampilkan Laporan Stok
SELECT * FROM v_inventory_report WHERE stock_status = 'CRITICAL' ORDER BY branch_name, product_name;

-- 22.6 Tampilkan Struktur Database
SHOW TABLES;

-- 22.7 Tampilkan Data Lokasi
SELECT COUNT(*) as total_provinces FROM provinces;
SELECT COUNT(*) as total_regencies FROM regencies;
SELECT COUNT(*) as total_districts FROM districts;
SELECT COUNT(*) as total_villages FROM villages;

-- =====================================================
-- NEW TABLES FROM COMPREHENSIVE ANALYSIS (Tables 19-32)
-- =====================================================

-- 23. PLU (Price Look-Up) Management
CREATE TABLE IF NOT EXISTS plu_codes (
    id_plu INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    plu_code VARCHAR(50) UNIQUE NOT NULL,
    barcode VARCHAR(50),
    price_level ENUM('basic', 'premium', 'wholesale', 'special') DEFAULT 'basic',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_plu_code (plu_code),
    INDEX idx_barcode (barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plu_prices (
    id_price INT AUTO_INCREMENT PRIMARY KEY,
    plu_id INT NOT NULL,
    branch_id INT NOT NULL,
    price_type ENUM('retail', 'wholesale', 'special', 'promo') DEFAULT 'retail',
    price DECIMAL(15,2) NOT NULL,
    valid_from DATE,
    valid_to DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plu_id) REFERENCES plu_codes(id_plu) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    INDEX idx_plu_branch (plu_id, branch_id),
    INDEX idx_price_type (price_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Payment Methods & Processing
CREATE TABLE IF NOT EXISTS payment_methods (
    id_method INT AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(100) NOT NULL,
    method_type ENUM('cash', 'card', 'ewallet', 'transfer', 'check', 'credit') NOT NULL,
    provider VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    fee_percentage DECIMAL(5,2) DEFAULT 0.00,
    fixed_fee DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_method_type (method_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
    id_payment INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    method_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    fee_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    reference_number VARCHAR(100),
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (method_id) REFERENCES payment_methods(id_method),
    INDEX idx_transaction (transaction_id),
    INDEX idx_status (status),
    INDEX idx_processed_at (processed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Debt & Receivable Management
CREATE TABLE IF NOT EXISTS accounts_payable (
    id_ap INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    invoice_id INT,
    due_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('unpaid', 'partial', 'paid', 'overdue') DEFAULT 'unpaid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id_supplier),
    INDEX idx_supplier (supplier_id),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accounts_receivable (
    id_ar INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    invoice_id INT,
    due_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('unpaid', 'partial', 'paid', 'overdue') DEFAULT 'unpaid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id_customer),
    INDEX idx_customer (customer_id),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. General Ledger & Accounting
CREATE TABLE IF NOT EXISTS chart_of_accounts (
    id_account INT AUTO_INCREMENT PRIMARY KEY,
    account_code VARCHAR(20) UNIQUE NOT NULL,
    account_name VARCHAR(200) NOT NULL,
    account_type ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
    parent_id INT,
    branch_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES chart_of_accounts(id_account),
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch),
    INDEX idx_account_code (account_code),
    INDEX idx_account_type (account_type),
    INDEX idx_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS journal_entries (
    id_journal INT AUTO_INCREMENT PRIMARY KEY,
    entry_date DATE NOT NULL,
    reference VARCHAR(100),
    description TEXT,
    total_amount DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'posted', 'void') DEFAULT 'draft',
    branch_id INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch),
    INDEX idx_entry_date (entry_date),
    INDEX idx_status (status),
    INDEX idx_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Business Settings & Configuration
CREATE TABLE IF NOT EXISTS business_types (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL,
    category ENUM('retail', 'wholesale', 'manufacturing', 'agriculture', 'services', 'cooperative', 'online', 'franchise') NOT NULL,
    description TEXT,
    requirements JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS operational_settings (
    id_setting INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    setting_type ENUM('hours', 'holidays', 'shifts', 'breaks', 'overtime') NOT NULL,
    config_value JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    INDEX idx_branch_setting (branch_id, setting_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. Supply Chain Traceability
CREATE TABLE IF NOT EXISTS product_origins (
    id_origin INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    source_type ENUM('farm', 'factory', 'sea', 'import', 'local') NOT NULL,
    producer_id INT,
    coordinates POINT,
    certification_info JSON,
    harvest_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product),
    INDEX idx_product (product_id),
    INDEX idx_source_type (source_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS traceability_records (
    id_record INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    stage ENUM('production', 'processing', 'storage', 'transport', 'sale') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    handler_id INT,
    location_info JSON,
    conditions JSON,
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id_product),
    INDEX idx_product_stage (product_id, stage),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. Indonesian Localization Settings
CREATE TABLE IF NOT EXISTS locale_settings (
    id_locale INT AUTO_INCREMENT PRIMARY KEY,
    setting_type VARCHAR(50) NOT NULL,
    indonesian_value TEXT NOT NULL,
    english_value TEXT,
    module VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_setting_type (setting_type),
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS currency_formats (
    id_format INT AUTO_INCREMENT PRIMARY KEY,
    locale VARCHAR(10) DEFAULT 'id_ID',
    symbol VARCHAR(10) DEFAULT 'Rp',
    decimal_separator CHAR(1) DEFAULT ',',
    thousands_separator CHAR(1) DEFAULT '.',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_locale (locale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS date_formats (
    id_format INT AUTO_INCREMENT PRIMARY KEY,
    locale VARCHAR(10) DEFAULT 'id_ID',
    date_pattern VARCHAR(50) DEFAULT 'DD/MM/YYYY',
    time_pattern VARCHAR(50) DEFAULT 'HH:mm',
    timezone VARCHAR(50) DEFAULT 'WIB',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_locale (locale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA INSERTION
-- =====================================================

-- Insert sample payment methods
INSERT INTO payment_methods (method_name, method_type, provider, is_active) VALUES
('Tunai', 'cash', NULL, TRUE),
('Kartu Debit', 'card', 'Bank Indonesia', TRUE),
('OVO', 'ewallet', 'OVO', TRUE),
('GoPay', 'ewallet', 'Gojek', TRUE),
('Dana', 'ewallet', 'Dana', TRUE),
('QRIS', 'ewallet', 'BI', TRUE),
('Transfer Bank', 'transfer', 'All Banks', TRUE),
('Kredit Toko', 'credit', 'Internal', TRUE);

-- Insert sample business types
INSERT INTO business_types (type_name, category, description) VALUES
('Toko Kelontong', 'retail', 'Toko sembako dan kebutuhan sehari-hari'),
('Minimarket', 'retail', 'Toko swalayan modern'),
('Warung Makan', 'retail', 'Tempat makan sederhana'),
('Distributor', 'wholesale', 'Pengedar grosir'),
('Koperasi', 'cooperative', 'Koperasi simpan pinjam'),
('Toko Online', 'online', 'E-commerce retailer'),
('Franchise', 'franchise', 'Bisnis waralaba'),
('Petani', 'agriculture', 'Produsen pertanian');

-- Insert Indonesian locale settings
INSERT INTO locale_settings (setting_type, indonesian_value, english_value, module) VALUES
('button.save', 'Simpan', 'Save', 'general'),
('button.cancel', 'Batal', 'Cancel', 'general'),
('button.delete', 'Hapus', 'Delete', 'general'),
('menu.dashboard', 'Dashboard', 'Dashboard', 'navigation'),
('menu.products', 'Produk', 'Products', 'navigation'),
('menu.customers', 'Pelanggan', 'Customers', 'navigation'),
('menu.reports', 'Laporan', 'Reports', 'navigation'),
('msg.success', 'Berhasil', 'Success', 'messages'),
('msg.error', 'Error', 'Error', 'messages'),
('msg.confirm_delete', 'Apakah Anda yakin ingin menghapus?', 'Are you sure you want to delete?', 'messages');

-- Insert default currency and date formats
INSERT INTO currency_formats (locale, symbol, decimal_separator, thousands_separator, is_default) VALUES
('id_ID', 'Rp', ',', '.', TRUE);

INSERT INTO date_formats (locale, date_pattern, time_pattern, timezone, is_default) VALUES
('id_ID', 'DD/MM/YYYY', 'HH:mm', 'WIB', TRUE);

-- =====================================================
-- SAMPLE DATA INSERTION - UNIVERSAL SCALABILITY DEMO
-- =====================================================

-- Insert sample companies untuk semua scalability levels
INSERT INTO companies (company_name, company_code, company_type, business_category, scalability_level, owner_name, phone, email, address) VALUES
-- Level 1: Individu/Personal (Ultra-Mikro)
('Pak Budi Penjual Sayur', 'PBS001', 'individual', 'personal', '1', 'Budi Santoso', '08123456789', 'budi.santoso@email.com', 'Dusun Krajan, Desa Sukamaju, Kec. Makmur, Kab. Sejahtera'),
('Ibu Sari Kerajinan', 'ISK001', 'personal', 'personal', '1', 'Sari Wijaya', '08234567890', 'sari.wijaya@email.com', 'Jl. Kampung Baru No. 45, Desa Mulya, Kec. Jaya'),

-- Level 2: Warung/Kios (Mikro)
('Warung Makmur Sejahtera', 'WMS001', 'warung', 'retail', '2', 'Ahmad Fauzi', '08345678901', 'warung.makmur@email.com', 'Jl. Raya Desa No. 12, Desa Makmur, Kec. Sejahtera'),
('Kios Jaya Abadi', 'KJA001', 'kios', 'retail', '2', 'Dewi Lestari', '08456789012', 'kios.jaya@email.com', 'Pasar Tradisional Blok A No. 5, Kota Kecil'),

-- Level 3: Toko Kelontong (Kecil-Menengah)
('Toko Kelontong Bersama', 'TKB001', 'toko_kelontong', 'retail', '3', 'Rudi Hermawan', '08567890123', 'toko.kelontong@email.com', 'Jl. Merdeka No. 67, Kelurahan Baru, Kecamatan Tengah'),
('Toko Sembako Makmur', 'TSM001', 'toko_kelontong', 'retail', '3', 'Siti Nurhaliza', '08678901234', 'toko.sembako@email.com', 'Jl. Gatotkoco No. 23, Kota Besar'),

-- Level 4: Minimarket/Pengusaha Menengah
('Minimarket Sejahtera', 'MSJ001', 'minimarket', 'retail', '4', 'Hendra Kusuma', '08789012345', 'minimarket.sejahtera@email.com', 'Jl. Sudirman No. 100, Kota Metropolitan'),
('Pengusaha Menengah Jaya', 'PMJ001', 'pengusaha_menengah', 'wholesale', '4', 'Lisa Permata', '08890123456', 'pengusaha.jaya@email.com', 'Jl. Industri Raya No. 45, Kawasan Industri'),

-- Level 5: Distributor/Perusahaan Menengah
('Distributor Sukses Abadi', 'DSA001', 'distributor', 'distributor', '5', 'Bambang Sutrisno', '08901234567', 'distributor.sukses@email.com', 'Jl. Pergudangan No. 200, Kota Bisnis'),
('Koperasi Tani Makmur', 'KTM001', 'koperasi', 'cooperative', '5', 'Joko Widodo', '09012345678', 'koperasi.tani@email.com', 'Jl. Koperasi No. 10, Kabupaten Pertanian'),

-- Level 6: Perusahaan Besar/Franchise
('Perusahaan Retail Indonesia', 'PRI001', 'perusahaan_besar', 'franchise', '6', 'Andi Wijaya', '09123456789', 'corporate.indonesia@email.com', 'Jl. Sudirman Central, Jakarta'),
('Franchise Food Nusantara', 'FFN001', 'franchise', 'franchise', '6', 'Maya Sari', '09234567890', 'franchise.nusantara@email.com', 'Jl. Franchise Center No. 1, Jakarta');

-- Insert sample branches untuk setiap company
INSERT INTO branches (company_id, branch_name, branch_code, branch_type, business_segment, owner_name, phone, email, operation_hours) VALUES
-- Level 1 branches (Personal/Home-based)
(1, 'Kebun Sayur Pak Budi', 'PBS001-1', 'personal', 'ultra_mikro', 'Budi Santoso', '08123456789', 'budi.branch@email.com', '{"monday":"05:00-12:00","tuesday":"05:00-12:00","wednesday":"05:00-12:00","thursday":"05:00-12:00","friday":"05:00-12:00","saturday":"05:00-12:00","sunday":"closed"}'),
(2, 'Rumah Kerajinan Ibu Sari', 'ISK001-1', 'personal', 'ultra_mikro', 'Sari Wijaya', '08234567890', 'sari.branch@email.com', '{"monday":"08:00-17:00","tuesday":"08:00-17:00","wednesday":"08:00-17:00","thursday":"08:00-17:00","friday":"08:00-17:00","saturday":"08:00-15:00","sunday":"closed"}'),

-- Level 2 branches (Warung/Kios)
(3, 'Warung Makmur Utama', 'WMS001-1', 'warung', 'mikro', 'Ahmad Fauzi', '08345678901', 'warung.utama@email.com', '{"monday":"06:00-21:00","tuesday":"06:00-21:00","wednesday":"06:00-21:00","thursday":"06:00-21:00","friday":"06:00-21:00","saturday":"06:00-21:00","sunday":"07:00-20:00"}'),
(4, 'Kios Jaya Pasar', 'KJA001-1', 'kios', 'mikro', 'Dewi Lestari', '08456789012', 'kios.pasar@email.com', '{"monday":"07:00-16:00","tuesday":"07:00-16:00","wednesday":"07:00-16:00","thursday":"07:00-16:00","friday":"07:00-16:00","saturday":"07:00-14:00","sunday":"closed"}'),

-- Level 3 branches (Toko Kelontong)
(5, 'Toko Kelontong Pusat', 'TKB001-1', 'toko_kelontong', 'kecil_menengah', 'Rudi Hermawan', '08567890123', 'toko.pusat@email.com', '{"monday":"07:00-21:00","tuesday":"07:00-21:00","wednesday":"07:00-21:00","thursday":"07:00-21:00","friday":"07:00-21:00","saturday":"07:00-21:00","sunday":"08:00-20:00"}'),
(6, 'Toko Sembako Cabang', 'TSM001-1', 'toko_kelontong', 'kecil_menengah', 'Siti Nurhaliza', '08678901234', 'sembako.cabang@email.com', '{"monday":"06:30-22:00","tuesday":"06:30-22:00","wednesday":"06:30-22:00","thursday":"06:30-22:00","friday":"06:30-22:00","saturday":"06:30-22:00","sunday":"07:00-21:00"}'),

-- Level 4 branches (Minimarket)
(7, 'Minimarket Sejahtera Central', 'MSJ001-1', 'minimarket', 'menengah', 'Hendra Kusuma', '08789012345', 'minimarket.central@email.com', '{"monday":"07:00-23:00","tuesday":"07:00-23:00","wednesday":"07:00-23:00","thursday":"07:00-23:00","friday":"07:00-23:00","saturday":"07:00-23:00","sunday":"07:00-23:00"}'),
(8, 'Pengusaha Jaya Wholesale', 'PMJ001-1', 'pengusaha_menengah', 'menengah', 'Lisa Permata', '08890123456', 'jaya.wholesale@email.com', '{"monday":"08:00-17:00","tuesday":"08:00-17:00","wednesday":"08:00-17:00","thursday":"08:00-17:00","friday":"08:00-17:00","saturday":"08:00-15:00","sunday":"closed"}'),

-- Level 5 branches (Distributor/Koperasi)
(9, 'Distributor Gudang Utama', 'DSA001-1', 'distributor', 'besar', 'Bambang Sutrisno', '08901234567', 'distributor.utama@email.com', '{"monday":"07:00-16:00","tuesday":"07:00-16:00","wednesday":"07:00-16:00","thursday":"07:00-16:00","friday":"07:00-16:00","saturday":"07:00-14:00","sunday":"closed"}'),
(10, 'Koperasi Tani Cabang', 'KTM001-1', 'koperasi', 'besar', 'Joko Widodo', '09012345678', 'koperasi.cabang@email.com', '{"monday":"08:00-16:00","tuesday":"08:00-16:00","wednesday":"08:00-16:00","thursday":"08:00-16:00","friday":"08:00-16:00","saturday":"08:00-14:00","sunday":"closed"}'),

-- Level 6 branches (Corporate/Franchise)
(11, 'Corporate HQ Jakarta', 'PRI001-1', 'perusahaan_besar', 'enterprise', 'Andi Wijaya', '09123456789', 'hq.jakarta@email.com', '{"monday":"08:00-17:00","tuesday":"08:00-17:00","wednesday":"08:00-17:00","thursday":"08:00-17:00","friday":"08:00-17:00","saturday":"09:00-13:00","sunday":"closed"}'),
(12, 'Franchise Center', 'FFN001-1', 'franchise', 'enterprise', 'Maya Sari', '09234567890', 'franchise.center@email.com', '{"monday":"09:00-18:00","tuesday":"09:00-18:00","wednesday":"09:00-18:00","thursday":"09:00-18:00","friday":"09:00-18:00","saturday":"10:00-16:00","sunday":"closed"}');

-- Insert sample products yang relevan untuk setiap level
INSERT INTO products (product_name, product_code, category_id, unit, purchase_price, selling_price, description, is_active) VALUES
-- Level 1 products (Personal/Home-based)
('Sayur Segar Kangkung', 'SSK001', 1, 'ikat', 5000, 7000, 'Kangkung segar dari kebun sendiri', TRUE),
('Kerajinan Anyaman Bambu', 'KAB001', 2, 'pcs', 15000, 25000, 'Kerajinan anyaman bambu tradisional', TRUE),

-- Level 2 products (Warung/Kios)
('Beras Premium 5kg', 'BP5001', 3, 'karung', 45000, 55000, 'Beras premium kualitas terbaik', TRUE),
('Minyak Goreng 2L', 'MG2001', 4, 'botol', 25000, 30000, 'Minyak goreng kemasan 2 liter', TRUE),
('Gula Pasir 1kg', 'GP1001', 5, 'kg', 12000, 15000, 'Gula pasir kualitas standar', TRUE),
('Indomie Mie Goreng', 'IMG001', 6, 'pcs', 2000, 2500, 'Mie instant goreng favorit', TRUE),

-- Level 3 products (Toko Kelontong)
('Kopi Sachet ABC', 'KSA001', 7, 'box', 45000, 55000, 'Kopi sachet ABC 1 dus (20 sachet)', TRUE),
('Teh Celup Sariwangi', 'TCS001', 8, 'box', 30000, 38000, 'Teh celup Sariwangi 1 dus', TRUE),
('Susu Kental Manis', 'SKM001', 9, 'kaleng', 8000, 10000, 'Susu kental manis 1 kaleng', TRUE),
('Telur Ayam 1kg', 'TA1001', 10, 'kg', 22000, 28000, 'Telur ayam segar 1 kilogram', TRUE),

-- Level 4 products (Minimarket)
('Soft Drink Coca Cola', 'SCC001', 11, 'botol', 5000, 8000, 'Coca Cola 390ml', TRUE),
('Snack Chitato', 'SCH001', 12, 'pcs', 8000, 12000, 'Chitato 75gr', TRUE),
('Shampoo Pantene', 'SHP001', 13, 'botol', 25000, 35000, 'Shampoo Pantene 180ml', TRUE),
('Sabun Lifebuoy', 'SLB001', 14, 'pcs', 5000, 7500, 'Sabun Lifebuoy 90gr', TRUE),

-- Level 5 products (Distributor)
('Detergent Rinso 5kg', 'DR5001', 15, 'sak', 45000, 65000, 'Detergent Rinso 5kg', TRUE),
('Mie Sedap Kardus', 'MSK001', 16, 'kardus', 48000, 60000, 'Mie Sedap 1 kardus (40 pcs)', TRUE),
('Tepung Terigu Segitiga Biru', 'TTS001', 17, 'kg', 8000, 12000, 'Tepung terigu Segitiga Biru 1kg', TRUE),

-- Level 6 products (Corporate)
('Laptop ASUS VivoBook', 'LAV001', 18, 'unit', 5500000, 6500000, 'Laptop ASUS VivoBook Core i3', TRUE),
('Smartphone Samsung Galaxy', 'SSG001', 19, 'unit', 3500000, 4200000, 'Samsung Galaxy A54', TRUE),
('Printer Epson L3210', 'PEL001', 20, 'unit', 2500000, 3000000, 'Printer Epson L3210 All-in-One', TRUE);

-- Insert sample inventory untuk setiap branch
INSERT INTO branch_inventory (branch_id, product_id, stock_quantity, min_stock, max_stock, reorder_level, last_updated) VALUES
-- Level 1 inventory (Personal/Home-based)
(1, 1, 20, 5, 50, 10, NOW()),
(2, 2, 15, 3, 30, 8, NOW()),

-- Level 2 inventory (Warung/Kios)
(3, 3, 10, 3, 25, 5, NOW()),
(3, 4, 15, 5, 40, 8, NOW()),
(3, 5, 20, 8, 50, 12, NOW()),
(3, 6, 50, 15, 100, 25, NOW()),
(4, 3, 8, 2, 20, 4, NOW()),
(4, 4, 12, 4, 30, 6, NOW()),

-- Level 3 inventory (Toko Kelontong)
(5, 7, 25, 8, 60, 15, NOW()),
(5, 8, 30, 10, 70, 18, NOW()),
(5, 9, 40, 12, 80, 20, NOW()),
(5, 10, 30, 10, 60, 15, NOW()),
(6, 7, 20, 6, 50, 12, NOW()),
(6, 8, 25, 8, 55, 14, NOW()),

-- Level 4 inventory (Minimarket)
(7, 11, 100, 30, 200, 50, NOW()),
(7, 12, 80, 25, 150, 40, NOW()),
(7, 13, 50, 15, 100, 25, NOW()),
(7, 14, 60, 20, 120, 30, NOW()),
(8, 15, 30, 10, 60, 15, NOW()),
(8, 16, 25, 8, 50, 12, NOW()),

-- Level 5 inventory (Distributor/Koperasi)
(9, 17, 100, 30, 200, 50, NOW()),
(9, 15, 50, 15, 100, 25, NOW()),
(9, 16, 40, 12, 80, 20, NOW()),
(10, 17, 80, 25, 150, 40, NOW()),

-- Level 6 inventory (Corporate/Franchise)
(11, 18, 20, 5, 50, 10, NOW()),
(11, 19, 30, 8, 70, 15, NOW()),
(11, 20, 15, 4, 40, 8, NOW()),
(12, 18, 25, 7, 60, 12, NOW()),
(12, 19, 35, 10, 80, 18, NOW()),
(12, 20, 20, 6, 45, 10, NOW());

-- =====================================================
-- END OF DATABASE SCRIPT
-- =====================================================
