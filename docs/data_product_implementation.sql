-- =====================================================
-- PRODUCT DATABASE IMPLEMENTATION - INTEGRATED SYSTEM
-- Koordinasi Data Search & Normalized Structure
-- =====================================================
-- Database: product_system
-- Tables: 20 tables dengan complete relationships
-- Features: 3NF Normalized, Indonesian Market Ready
-- =====================================================

-- 1. Database Setup
CREATE DATABASE IF NOT EXISTS product_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE product_system;

-- =====================================================
-- PHASE 1: CORE TABLES
-- =====================================================

-- 2. Product Categories Table
CREATE TABLE product_categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_slug VARCHAR(100) UNIQUE,
    description TEXT,
    parent_id INT NULL,
    category_image VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id_category) ON DELETE SET NULL,
    INDEX idx_category_slug (category_slug),
    INDEX idx_parent (parent_id),
    INDEX idx_display_order (display_order),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Brands Table
CREATE TABLE brands (
    id_brand INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) UNIQUE NOT NULL,
    brand_slug VARCHAR(100) UNIQUE,
    brand_logo VARCHAR(255),
    brand_description TEXT,
    country_origin VARCHAR(50),
    website VARCHAR(255),
    is_indonesian BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_brand_name (brand_name),
    INDEX idx_brand_slug (brand_slug),
    INDEX idx_indonesian (is_indonesian)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Units Table
CREATE TABLE units (
    id_unit INT AUTO_INCREMENT PRIMARY KEY,
    unit_name VARCHAR(50) NOT NULL,
    unit_code VARCHAR(10) UNIQUE NOT NULL,
    unit_type ENUM('weight', 'volume', 'length', 'quantity', 'custom') NOT NULL,
    conversion_factor DECIMAL(10,6) DEFAULT 1.000000,
    base_unit_id INT NULL,
    is_indonesian BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (base_unit_id) REFERENCES units(id_unit) ON DELETE SET NULL,
    INDEX idx_unit_code (unit_code),
    INDEX idx_unit_type (unit_type),
    INDEX idx_indonesian (is_indonesian)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Core Products Table
CREATE TABLE products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    product_sku VARCHAR(50) UNIQUE,
    product_name VARCHAR(200) NOT NULL,
    product_slug VARCHAR(200) UNIQUE,
    product_description TEXT,
    short_description VARCHAR(500),
    category_id INT NOT NULL,
    brand_id INT,
    unit_id INT NOT NULL,
    weight DECIMAL(10,3),
    dimensions VARCHAR(50),
    is_digital_product BOOLEAN DEFAULT FALSE,
    is_perishable BOOLEAN DEFAULT FALSE,
    shelf_life_days INT,
    manufacture_date DATE,
    expiry_date DATE,
    indonesian_origin BOOLEAN DEFAULT FALSE,
    halal_certified BOOLEAN DEFAULT FALSE,
    bpom_number VARCHAR(50),
    sni_number VARCHAR(50),
    ppn_rate DECIMAL(5,2) DEFAULT 11.00,
    pph_category ENUM('final', 'non_final', 'exempt') DEFAULT 'non_final',
    min_order_quantity INT DEFAULT 1,
    max_order_quantity INT,
    reorder_point DECIMAL(15,2),
    lead_time_days INT,
    view_count INT DEFAULT 0,
    purchase_count INT DEFAULT 0,
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id_category),
    FOREIGN KEY (brand_id) REFERENCES brands(id_brand) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES units(id_unit),
    INDEX idx_product_code (product_code),
    INDEX idx_product_sku (product_sku),
    INDEX idx_product_name (product_name),
    INDEX idx_product_slug (product_slug),
    INDEX idx_category (category_id),
    INDEX idx_brand (brand_id),
    INDEX idx_indonesian_origin (indonesian_origin),
    INDEX idx_halal_certified (halal_certified),
    INDEX idx_active (is_active),
    FULLTEXT idx_search (product_name, product_description, short_description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PHASE 2: DYNAMIC ATTRIBUTES & VARIANTS
-- =====================================================

-- 6. Product Attributes Table
CREATE TABLE product_attributes (
    id_attribute INT AUTO_INCREMENT PRIMARY KEY,
    attribute_name VARCHAR(100) NOT NULL,
    attribute_code VARCHAR(50) UNIQUE,
    attribute_type ENUM('text', 'number', 'boolean', 'date', 'enum', 'color', 'size') NOT NULL,
    attribute_values JSON,
    is_required BOOLEAN DEFAULT FALSE,
    is_filterable BOOLEAN DEFAULT TRUE,
    is_searchable BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attribute_code (attribute_code),
    INDEX idx_filterable (is_filterable),
    INDEX idx_searchable (is_searchable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Product Attribute Values Table
CREATE TABLE product_attribute_values (
    id_value INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_id INT NOT NULL,
    attribute_value TEXT NOT NULL,
    attribute_value_numeric DECIMAL(15,6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES product_attributes(id_attribute) ON DELETE CASCADE,
    UNIQUE KEY uk_product_attribute (product_id, attribute_id),
    INDEX idx_product (product_id),
    INDEX idx_attribute (attribute_id),
    INDEX idx_value_search (attribute_value(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Product Variants Table
CREATE TABLE product_variants (
    id_variant INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_sku VARCHAR(50) UNIQUE NOT NULL,
    variant_name VARCHAR(200),
    variant_barcode VARCHAR(50),
    weight DECIMAL(10,3),
    dimensions VARCHAR(50),
    image_url VARCHAR(255),
    price_modifier DECIMAL(15,2) DEFAULT 0,
    price_modifier_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_product_variant (product_id),
    INDEX idx_variant_sku (variant_sku),
    INDEX idx_variant_barcode (variant_barcode),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Variant Attribute Values Table
CREATE TABLE variant_attribute_values (
    id_value INT AUTO_INCREMENT PRIMARY KEY,
    variant_id INT NOT NULL,
    attribute_id INT NOT NULL,
    attribute_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES product_attributes(id_attribute) ON DELETE CASCADE,
    UNIQUE KEY uk_variant_attribute (variant_id, attribute_id),
    INDEX idx_variant (variant_id),
    INDEX idx_attribute (attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PHASE 3: PRICING & INVENTORY
-- =====================================================

-- 10. Product Pricing Table
CREATE TABLE product_pricing (
    id_pricing INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL,
    variant_id INT NULL,
    location_id INT NULL,
    price_type ENUM('purchase', 'selling', 'wholesale', 'promo', 'special') NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    valid_from DATE,
    valid_to DATE,
    min_quantity INT DEFAULT 1,
    max_quantity INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    INDEX idx_product_price (product_id, price_type),
    INDEX idx_variant_price (variant_id, price_type),
    INDEX idx_location_price (location_id, price_type),
    INDEX idx_validity (valid_from, valid_to),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Regional Pricing Table
CREATE TABLE product_regional_pricing (
    id_pricing INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL,
    variant_id INT NULL,
    region_type ENUM('province', 'city', 'island', 'zone') NOT NULL,
    region_code VARCHAR(10) NOT NULL,
    region_name VARCHAR(100) NOT NULL,
    price_multiplier DECIMAL(5,3) DEFAULT 1.000,
    additional_cost DECIMAL(15,2) DEFAULT 0,
    min_order_quantity INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    UNIQUE KEY uk_product_region (COALESCE(product_id, 0), COALESCE(variant_id, 0), region_type, region_code),
    INDEX idx_region_code (region_code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Product Inventory Table
CREATE TABLE product_inventory (
    id_inventory INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL,
    variant_id INT NULL,
    location_id INT NULL,
    quantity DECIMAL(15,2) DEFAULT 0,
    reserved_quantity DECIMAL(15,2) DEFAULT 0,
    available_quantity DECIMAL(15,2) GENERATED ALWAYS AS (quantity - reserved_quantity) STORED,
    min_stock_level DECIMAL(15,2) DEFAULT 0,
    max_stock_level DECIMAL(15,2),
    reorder_point DECIMAL(15,2),
    reorder_quantity DECIMAL(15,2),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    UNIQUE KEY uk_location_product_variant (location_id, COALESCE(product_id, 0), COALESCE(variant_id, 0)),
    INDEX idx_location_stock (location_id, available_quantity),
    INDEX idx_reorder (reorder_point),
    INDEX idx_low_stock (available_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PHASE 4: MEDIA & SEO
-- =====================================================

-- 13. Product Media Table
CREATE TABLE product_media (
    id_media INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL,
    variant_id INT NULL,
    media_type ENUM('image', 'video', 'document') NOT NULL,
    media_url VARCHAR(500) NOT NULL,
    media_title VARCHAR(200),
    media_description TEXT,
    file_size INT,
    mime_type VARCHAR(100),
    width INT,
    height INT,
    display_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    INDEX idx_product_media (product_id, media_type),
    INDEX idx_variant_media (variant_id, media_type),
    INDEX idx_primary (is_primary),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Product SEO Table
CREATE TABLE product_seo (
    id_seo INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    url_slug VARCHAR(255) UNIQUE,
    meta_title VARCHAR(200),
    meta_description TEXT,
    meta_keywords TEXT,
    og_title VARCHAR(200),
    og_description TEXT,
    og_image VARCHAR(500),
    structured_data JSON,
    canonical_url VARCHAR(500),
    search_keywords TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_url_slug (url_slug),
    FULLTEXT idx_seo_search (meta_title, meta_description, meta_keywords, search_keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tags Table
CREATE TABLE tags (
    id_tag INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(100) UNIQUE NOT NULL,
    tag_slug VARCHAR(100) UNIQUE,
    tag_description TEXT,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tag_name (tag_name),
    INDEX idx_tag_slug (tag_slug),
    INDEX idx_usage_count (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_tags (
    product_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, tag_id),
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id_tag) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PHASE 5: INDONESIAN MARKET SPECIFIC
-- =====================================================

-- 16. Compliance Types Table
CREATE TABLE compliance_types (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL,
    type_code VARCHAR(20) UNIQUE,
    description TEXT,
    issuing_authority VARCHAR(200),
    is_required BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_code (type_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Product Compliance Table
CREATE TABLE product_compliance (
    id_compliance INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    compliance_type_id INT NOT NULL,
    certificate_number VARCHAR(100),
    issue_date DATE,
    expiry_date DATE,
    status ENUM('active', 'expired', 'suspended', 'pending') DEFAULT 'pending',
    document_url VARCHAR(500),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (compliance_type_id) REFERENCES compliance_types(id_type),
    INDEX idx_product_compliance (product_id, compliance_type_id),
    INDEX idx_certificate (certificate_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Local Measurement Units Table
CREATE TABLE local_measurement_units (
    id_unit INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    unit_name VARCHAR(50) NOT NULL,
    conversion_to_base DECIMAL(10,6),
    base_unit_id INT NOT NULL,
    is_commonly_used BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (base_unit_id) REFERENCES units(id_unit),
    INDEX idx_product (product_id),
    INDEX idx_base_unit (base_unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Seasonal Demand Patterns Table
CREATE TABLE seasonal_demand_patterns (
    id_pattern INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    season_type ENUM('ramadan', 'lebaran', 'christmas', 'new_year', 'school_year', 'harvest', 'normal') NOT NULL,
    demand_multiplier DECIMAL(5,3) DEFAULT 1.000,
    start_date DATE,
    end_date DATE,
    is_recurring BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_product_season (product_id, season_type),
    INDEX idx_date_range (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PHASE 6: ANALYTICS & REVIEWS
-- =====================================================

-- 20. Product Analytics Table
CREATE TABLE product_analytics (
    id_analytics INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL,
    variant_id INT NULL,
    location_id INT NULL,
    date DATE NOT NULL,
    views INT DEFAULT 0,
    unique_views INT DEFAULT 0,
    cart_additions INT DEFAULT 0,
    purchases INT DEFAULT 0,
    revenue DECIMAL(15,2) DEFAULT 0,
    conversion_rate DECIMAL(5,4) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    UNIQUE KEY uk_product_date_location (COALESCE(product_id, 0), COALESCE(variant_id, 0), date, COALESCE(location_id, 0)),
    INDEX idx_date_location (date, location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Product Reviews Table
CREATE TABLE product_reviews (
    id_review INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_id INT NULL,
    customer_id INT,
    customer_name VARCHAR(200),
    customer_email VARCHAR(200),
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(200),
    review_content TEXT,
    pros TEXT,
    cons TEXT,
    would_recommend BOOLEAN,
    images JSON,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    INDEX idx_product_rating (product_id, rating),
    INDEX idx_approved (is_approved),
    INDEX idx_customer (customer_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Specification Types Table
CREATE TABLE specification_types (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL,
    category ENUM('technical', 'nutritional', 'material', 'dimensional', 'environmental', 'safety') NOT NULL,
    unit_id INT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id_unit),
    INDEX idx_type_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Product Specifications Table
CREATE TABLE product_specifications (
    id_spec INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    specification_type_id INT NOT NULL,
    spec_value TEXT NOT NULL,
    spec_value_numeric DECIMAL(15,6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (specification_type_id) REFERENCES specification_types(id_type),
    UNIQUE KEY uk_product_spec (product_id, specification_type_id),
    INDEX idx_spec_type (specification_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INITIAL DATA SEEDING
-- =====================================================

-- Insert Product Categories (Indonesian Market)
INSERT INTO product_categories (category_name, category_slug, description, display_order) VALUES
('Makanan & Minuman', 'makanan-dan-minuman', 'Semua jenis makanan dan minuman', 1),
('Kebutuhan Rumah Tangga', 'kebutuhan-rumah-tangga', 'Perlengkapan dan kebutuhan rumah tangga', 2),
('Fashion & Aksesoris', 'fashion-aksesoris', 'Pakaian, sepatu, dan aksesoris', 3),
('Elektronik & Gadget', 'elektronik-gadget', 'Peralatan elektronik dan gadget', 4),
('Kesehatan & Kecantikan', 'kesehatan-kecantikan', 'Produk kesehatan dan perawatan kecantikan', 5),
('Olahraga & Outdoor', 'olahraga-outdoor', 'Peralatan olahraga dan aktivitas outdoor', 6),
('Otomotif', 'otomotif', 'Aksesoris dan perlengkapan otomotif', 7),
('Pertanian & Perkebunan', 'pertanian-perkebunan', 'Produk pertanian dan perkebunan', 8),
('Office & Stationery', 'office-stationery', 'Alat tulis dan kantor', 9),
('Mainan & Hobi', 'mainan-hobi', 'Mainan anak dan hobi', 10);

-- Insert Sub-categories (Makanan & Minuman)
INSERT INTO product_categories (category_name, category_slug, description, parent_id, display_order) VALUES
('Makanan Pokok', 'makanan-pokok', 'Beras, gula, minyak, tepung', 1, 1),
('Makanan Instan', 'makanan-instan', 'Mie instant, makanan kaleng', 1, 2),
('Daging & Seafood', 'daging-seafood', 'Daging segar, ikan, seafood', 1, 3),
('Sayur & Buah', 'sayur-buah', 'Sayuran segar dan buah-buahan', 1, 4),
('Minuman', 'minuman', 'Kopi, teh, jus, minuman kemasan', 1, 5),
('Makanan Ringan', 'makanan-ringan', 'Kerupuk, kue, camilan', 1, 6),
('Bumbu Dapur', 'bumbu-dapur', 'Rempah-rempah dan bumbu masak', 1, 7),
('Produk Susu', 'produk-susu', 'Susu, yogurt, keju', 1, 8);

-- Insert Units
INSERT INTO units (unit_name, unit_code, unit_type, is_indonesian) VALUES
('Kilogram', 'kg', 'weight', FALSE),
('Gram', 'g', 'weight', FALSE),
('Liter', 'l', 'volume', FALSE),
('Mililiter', 'ml', 'volume', FALSE),
('Meter', 'm', 'length', FALSE),
('Sentimeter', 'cm', 'length', FALSE),
('Pieces', 'pcs', 'quantity', FALSE),
('Ikat', 'ikat', 'custom', TRUE),
('Bungkus', 'bungkus', 'custom', TRUE),
('Karung', 'karung', 'custom', TRUE),
('Botol', 'botol', 'custom', TRUE),
('Dus', 'dus', 'custom', TRUE);

-- Insert Brands (Indonesian & International)
INSERT INTO brands (brand_name, brand_slug, country_origin, is_indonesian) VALUES
('Indofood', 'indofood', 'Indonesia', TRUE),
('Garudafood', 'garudafood', 'Indonesia', TRUE),
('Mayora', 'mayora', 'Indonesia', TRUE),
('Ultra Jaya', 'ultra-jaya', 'Indonesia', TRUE),
('Frisian Flag', 'frisian-flag', 'Indonesia', TRUE),
('Nestlé', 'nestle', 'Switzerland', FALSE),
('Unilever', 'unilever', 'UK', FALSE),
('Procter & Gamble', 'procter-gamble', 'USA', FALSE);

-- Insert Product Attributes
INSERT INTO product_attributes (attribute_name, attribute_code, attribute_type, attribute_values, is_filterable) VALUES
('Warna', 'warna', 'color', '["Merah", "Biru", "Hijau", "Kuning", "Hitam", "Putih"]', TRUE),
('Ukuran', 'ukuran', 'size', '["XS", "S", "M", "L", "XL", "XXL"]', TRUE),
('Rasa', 'rasa', 'enum', '["Original", "Manis", "Asin", "Pedas", "Keju"]', TRUE),
('Kemasan', 'kemasan', 'enum', '["Pouch", "Botol", "Kaleng", "Dus", "Plastik"]', TRUE),
('Berat', 'berat', 'number', NULL, FALSE),
('Tanggal Kadaluarsa', 'tanggal_kadaluarsa', 'date', NULL, FALSE);

-- Insert Compliance Types
INSERT INTO compliance_types (type_name, type_code, description, issuing_authority, is_required) VALUES
('BPOM Registration', 'bpom', 'Badan Pengawas Obat dan Makanan', 'Badan POM', TRUE),
('Halal Certification', 'halal', 'Majelis Ulama Indonesia', 'MUI', FALSE),
('SNI Certification', 'sni', 'Standar Nasional Indonesia', 'BSN', FALSE),
('Import License', 'import', 'Izin Impor Produk', 'Kementerian Perdagangan', FALSE);

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- Product Catalog View
CREATE OR REPLACE VIEW v_product_catalog AS
SELECT 
    p.id_product,
    p.product_code,
    p.product_sku,
    p.product_name,
    p.product_slug,
    p.short_description,
    p.product_description,
    p.weight,
    p.dimensions,
    p.indonesian_origin,
    p.halal_certified,
    p.view_count,
    p.purchase_count,
    p.rating_average,
    p.review_count,
    p.is_active,
    p.created_at,
    c.category_name,
    c.category_slug as category_slug,
    b.brand_name,
    b.brand_slug,
    u.unit_name,
    u.unit_code,
    pm.media_url as primary_image,
    pp.price as selling_price
FROM products p
LEFT JOIN product_categories c ON p.category_id = c.id_category
LEFT JOIN brands b ON p.brand_id = b.id_brand
LEFT JOIN units u ON p.unit_id = u.id_unit
LEFT JOIN product_media pm ON p.id_product = pm.product_id AND pm.media_type = 'image' AND pm.is_primary = TRUE
LEFT JOIN product_pricing pp ON p.id_product = pp.product_id AND pp.price_type = 'selling' AND pp.is_active = TRUE
WHERE p.is_active = TRUE;

-- Product Inventory View
CREATE OR REPLACE VIEW v_product_inventory AS
SELECT 
    p.id_product,
    p.product_code,
    p.product_name,
    c.category_name,
    COALESCE(pi.quantity, 0) as total_quantity,
    COALESCE(pi.reserved_quantity, 0) as reserved_quantity,
    COALESCE(pi.available_quantity, 0) as available_quantity,
    pi.min_stock_level,
    pi.reorder_point,
    CASE 
        WHEN COALESCE(pi.available_quantity, 0) <= pi.reorder_point THEN 'CRITICAL'
        WHEN COALESCE(pi.available_quantity, 0) <= pi.min_stock_level THEN 'LOW'
        ELSE 'NORMAL'
    END as stock_status,
    pi.last_updated
FROM products p
LEFT JOIN product_categories c ON p.category_id = c.id_category
LEFT JOIN product_inventory pi ON p.id_product = pi.product_id
WHERE p.is_active = TRUE;

-- Product Analytics Summary View
CREATE OR REPLACE VIEW v_product_analytics_summary AS
SELECT 
    p.id_product,
    p.product_name,
    c.category_name,
    COALESCE(SUM(pa.views), 0) as total_views,
    COALESCE(SUM(pa.purchases), 0) as total_purchases,
    COALESCE(SUM(pa.revenue), 0) as total_revenue,
    COALESCE(AVG(pa.conversion_rate), 0) as avg_conversion_rate,
    COUNT(DISTINCT pa.date) as days_tracked
FROM products p
LEFT JOIN product_categories c ON p.category_id = c.id_category
LEFT JOIN product_analytics pa ON p.id_product = pa.product_id
WHERE p.is_active = TRUE
GROUP BY p.id_product, p.product_name, c.category_name;

-- =====================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- =====================================================

DELIMITER //

-- Update Product Rating
CREATE PROCEDURE UpdateProductRating(IN product_id INT)
BEGIN
    DECLARE avg_rating DECIMAL(3,2) DEFAULT 0;
    DECLARE review_count INT DEFAULT 0;
    
    SELECT 
        COALESCE(AVG(rating), 0), 
        COUNT(*)
    INTO avg_rating, review_count
    FROM product_reviews 
    WHERE product_id = product_id AND is_approved = TRUE;
    
    UPDATE products 
    SET rating_average = avg_rating, 
        review_count = review_count,
        updated_at = CURRENT_TIMESTAMP
    WHERE id_product = product_id;
END //

-- Update Product View Count
CREATE PROCEDURE UpdateProductView(IN product_id INT)
BEGIN
    UPDATE products 
    SET view_count = view_count + 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE id_product = product_id;
END //

-- Check Low Stock
CREATE PROCEDURE CheckLowStock()
BEGIN
    SELECT 
        p.id_product,
        p.product_code,
        p.product_name,
        c.category_name,
        pi.available_quantity,
        pi.reorder_point,
        CASE 
            WHEN pi.available_quantity <= 0 THEN 'OUT_OF_STOCK'
            WHEN pi.available_quantity <= pi.reorder_point THEN 'CRITICAL'
            WHEN pi.available_quantity <= pi.min_stock_level THEN 'LOW'
            ELSE 'NORMAL'
        END as stock_status
    FROM products p
    JOIN product_categories c ON p.category_id = c.id_category
    JOIN product_inventory pi ON p.id_product = pi.product_id
    WHERE p.is_active = TRUE 
    AND pi.available_quantity <= pi.reorder_point
    ORDER BY pi.available_quantity ASC;
END //

DELIMITER ;

-- =====================================================
-- TRIGGERS FOR DATA INTEGRITY
-- =====================================================

DELIMITER //

-- Update tag usage count
CREATE TRIGGER update_tag_usage_count
AFTER INSERT ON product_tags
FOR EACH ROW
BEGIN
    UPDATE tags 
    SET usage_count = usage_count + 1 
    WHERE id_tag = NEW.tag_id;
END //

-- Update tag usage count on delete
CREATE TRIGGER update_tag_usage_count_delete
AFTER DELETE ON product_tags
FOR EACH ROW
BEGIN
    UPDATE tags 
    SET usage_count = GREATEST(usage_count - 1, 0) 
    WHERE id_tag = OLD.tag_id;
END //

DELIMITER ;

-- =====================================================
-- VERIFICATION
-- =====================================================

SELECT 'Product Database Implementation Completed Successfully!' as status;
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'product_system';
SHOW TABLES;

-- Sample Query Test
SELECT * FROM v_product_catalog LIMIT 5;
SELECT * FROM v_product_inventory WHERE stock_status = 'CRITICAL' LIMIT 5;

-- =====================================================
-- END OF IMPLEMENTATION
-- =====================================================
