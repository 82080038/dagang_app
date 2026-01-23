# Product Database Implementation - Integrated System
# Koordinasi Data Search & Normalized Structure

## 🎯 Integration Overview

### **Source Documents:**
1. **data_search.md** - Comprehensive requirements & Indonesian market needs
2. **data_search_normalized.md** - Optimized 3NF database structure

### **Integration Strategy:**
- **Core Structure:** Use normalized 3NF design
- **Feature Completeness:** Incorporate all Indonesian market requirements
- **Implementation Ready:** Production-ready SQL scripts

## 📊 Final Integrated Database Structure

### **Phase 1: Core Tables (Essential)**

#### **1. Product Categories Table**
```sql
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
```

#### **2. Brands Table**
```sql
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
```

#### **3. Units Table**
```sql
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
```

#### **4. Core Products Table**
```sql
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
    dimensions VARCHAR(50), -- Format: "LxWxH"
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
```

### **Phase 2: Dynamic Attributes & Variants**

#### **5. Product Attributes Table**
```sql
CREATE TABLE product_attributes (
    id_attribute INT AUTO_INCREMENT PRIMARY KEY,
    attribute_name VARCHAR(100) NOT NULL,
    attribute_code VARCHAR(50) UNIQUE,
    attribute_type ENUM('text', 'number', 'boolean', 'date', 'enum', 'color', 'size') NOT NULL,
    attribute_values JSON, -- For enum type: ["S", "M", "L", "XL"]
    is_required BOOLEAN DEFAULT FALSE,
    is_filterable BOOLEAN DEFAULT TRUE,
    is_searchable BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attribute_code (attribute_code),
    INDEX idx_filterable (is_filterable),
    INDEX idx_searchable (is_searchable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### **6. Product Attribute Values Table**
```sql
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
```

#### **7. Product Variants Table**
```sql
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
```

#### **8. Variant Attribute Values Table**
```sql
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
```

### **Phase 3: Pricing & Inventory**

#### **9. Product Pricing Table**
```sql
CREATE TABLE product_pricing (
    id_pricing INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL,
    variant_id INT NULL,
    location_id INT NULL, -- Store/branch location
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
```

#### **10. Regional Pricing Table**
```sql
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
```

#### **11. Product Inventory Table**
```sql
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
```

### **Phase 4: Media & SEO**

#### **12. Product Media Table**
```sql
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
```

#### **13. Product SEO Table**
```sql
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
```

#### **14. Tags Table**
```sql
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
```

### **Phase 5: Indonesian Market Specific**

#### **15. Product Compliance Table**
```sql
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
```

#### **16. Local Measurement Units Table**
```sql
CREATE TABLE local_measurement_units (
    id_unit INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    unit_name VARCHAR(50) NOT NULL, -- "ikat", "bungkus", "karung"
    conversion_to_base DECIMAL(10,6), -- 1 ikat = 0.5 kg
    base_unit_id INT NOT NULL,
    is_commonly_used BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (base_unit_id) REFERENCES units(id_unit),
    INDEX idx_product (product_id),
    INDEX idx_base_unit (base_unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### **17. Seasonal Demand Patterns Table**
```sql
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
```

### **Phase 6: Analytics & Reviews**

#### **18. Product Analytics Table**
```sql
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
```

#### **19. Product Reviews Table**
```sql
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
```

#### **20. Product Specifications Table**
```sql
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
```

## 🔄 Implementation Phases

### **Phase 1: Foundation (Week 1-2)**
1. Create core tables (products, categories, brands, units)
2. Set up basic relationships and indexes
3. Insert initial categories and units data

### **Phase 2: Attributes & Variants (Week 3-4)**
1. Implement dynamic attributes system
2. Create variant management
3. Set up attribute-value relationships

### **Phase 3: Pricing & Inventory (Week 5-6)**
1. Multi-tier pricing system
2. Regional pricing
3. Inventory management

### **Phase 4: Media & SEO (Week 7-8)**
1. Product media management
2. SEO optimization
3. Tag system

### **Phase 5: Indonesian Features (Week 9-10)**
1. Compliance tracking
2. Local measurement units
3. Seasonal demand patterns

### **Phase 6: Analytics & Reviews (Week 11-12)**
1. Analytics tracking
2. Review system
3. Specifications management

## 📊 Performance Optimizations

### **Indexing Strategy:**
- Primary keys on all tables
- Foreign key indexes
- Composite indexes for frequent queries
- Full-text search indexes
- JSON value indexes where applicable

### **Query Optimization:**
- Use generated columns for computed values
- Implement proper JOIN strategies
- Use appropriate data types
- Set up partitioning for large tables

---

*Integrated product database system combining comprehensive requirements with optimized normalized structure.*
