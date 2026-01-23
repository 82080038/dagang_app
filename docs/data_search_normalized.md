# Database Normalization - Product/Barang System
# Aplikasi Perdagangan Komprehensif Indonesia

## 🎯 Normalization Analysis

### **Current Issues Identified:**
1. **Denormalized products table** - Too many columns (50+ fields)
2. **JSON overuse** - Should be normalized to separate tables
3. **Mixed concerns** - Pricing, inventory, SEO, compliance in one table
4. **Redundant data** - Variant info in both products and variants tables
5. **Scalability issues** - Large single table approach

## 📊 Normalized Database Structure (3NF)

### **1. Core Product Table (products)**
```sql
CREATE TABLE products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_description TEXT,
    category_id INT,
    brand_id INT,
    unit_id INT,
    weight DECIMAL(10,3),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id_category),
    FOREIGN KEY (brand_id) REFERENCES brands(id_brand),
    FOREIGN KEY (unit_id) REFERENCES units(id_unit),
    INDEX idx_product_code (product_code),
    INDEX idx_product_name (product_name),
    INDEX idx_category (category_id),
    INDEX idx_brand (brand_id),
    INDEX idx_active (is_active)
);
```

### **2. Product Brands Table (brands)**
```sql
CREATE TABLE brands (
    id_brand INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) UNIQUE NOT NULL,
    brand_code VARCHAR(50) UNIQUE,
    brand_logo VARCHAR(255),
    brand_description TEXT,
    country_origin VARCHAR(50),
    website VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_brand_name (brand_name),
    INDEX idx_brand_code (brand_code)
);
```

### **3. Product Units Table (units)**
```sql
CREATE TABLE units (
    id_unit INT AUTO_INCREMENT PRIMARY KEY,
    unit_name VARCHAR(50) NOT NULL,
    unit_code VARCHAR(10) UNIQUE NOT NULL,
    unit_type ENUM('weight', 'volume', 'length', 'quantity', 'custom') NOT NULL,
    conversion_factor DECIMAL(10,6) DEFAULT 1.000000,
    base_unit_id INT,
    is_indonesian BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (base_unit_id) REFERENCES units(id_unit),
    INDEX idx_unit_code (unit_code),
    INDEX idx_unit_type (unit_type)
);
```

### **4. Product Attributes Table (product_attributes)**
```sql
CREATE TABLE product_attributes (
    id_attribute INT AUTO_INCREMENT PRIMARY KEY,
    attribute_name VARCHAR(100) NOT NULL,
    attribute_type ENUM('text', 'number', 'boolean', 'date', 'enum') NOT NULL,
    attribute_values JSON, -- For enum type: ["S", "M", "L", "XL"]
    is_required BOOLEAN DEFAULT FALSE,
    is_filterable BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attribute_name (attribute_name),
    INDEX idx_filterable (is_filterable)
);
```

### **5. Product Attribute Values Table (product_attribute_values)**
```sql
CREATE TABLE product_attribute_values (
    id_value INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_id INT NOT NULL,
    attribute_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES product_attributes(id_attribute) ON DELETE CASCADE,
    UNIQUE KEY uk_product_attribute (product_id, attribute_id),
    INDEX idx_product (product_id),
    INDEX idx_attribute (attribute_id)
);
```

### **6. Product Variants Table (product_variants)**
```sql
CREATE TABLE product_variants (
    id_variant INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_sku VARCHAR(50) UNIQUE NOT NULL,
    variant_name VARCHAR(200),
    barcode VARCHAR(50),
    weight DECIMAL(10,3),
    dimensions VARCHAR(50),
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_product_variant (product_id),
    INDEX idx_variant_sku (variant_sku),
    INDEX idx_barcode (barcode)
);
```

### **7. Variant Attribute Values Table (variant_attribute_values)**
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
);
```

### **8. Product Pricing Table (product_pricing)**
```sql
CREATE TABLE product_pricing (
    id_pricing INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL, -- For base product pricing
    variant_id INT NULL, -- For variant pricing
    branch_id INT,
    price_type ENUM('purchase', 'selling', 'wholesale', 'promo', 'special') NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    valid_from DATE,
    valid_to DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    INDEX idx_product_price (product_id, price_type),
    INDEX idx_variant_price (variant_id, price_type),
    INDEX idx_branch_price (branch_id, price_type),
    INDEX idx_validity (valid_from, valid_to)
);
```

### **9. Product Inventory Table (product_inventory)**
```sql
CREATE TABLE product_inventory (
    id_inventory INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL,
    variant_id INT NULL,
    branch_id INT NOT NULL,
    quantity DECIMAL(15,2) DEFAULT 0,
    reserved_quantity DECIMAL(15,2) DEFAULT 0,
    available_quantity DECIMAL(15,2) GENERATED ALWAYS AS (quantity - reserved_quantity) STORED,
    min_stock_level DECIMAL(15,2) DEFAULT 0,
    max_stock_level DECIMAL(15,2),
    reorder_point DECIMAL(15,2),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    UNIQUE KEY uk_branch_product_variant (branch_id, COALESCE(product_id, 0), COALESCE(variant_id, 0)),
    INDEX idx_branch_stock (branch_id, available_quantity),
    INDEX idx_reorder (reorder_point)
);
```

### **10. Product Media Table (product_media)**
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
    display_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    INDEX idx_product_media (product_id, media_type),
    INDEX idx_variant_media (variant_id, media_type),
    INDEX idx_primary (is_primary)
);
```

### **11. Product SEO Table (product_seo)**
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_url_slug (url_slug)
);
```

### **12. Product Tags Table (product_tags)**
```sql
CREATE TABLE tags (
    id_tag INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(100) UNIQUE NOT NULL,
    tag_slug VARCHAR(100) UNIQUE,
    tag_description TEXT,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tag_name (tag_name),
    INDEX idx_tag_slug (tag_slug)
);

CREATE TABLE product_tags (
    product_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, tag_id),
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id_tag) ON DELETE CASCADE
);
```

### **13. Product Compliance Table (product_compliance)**
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
);

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
);
```

### **14. Product Analytics Table (product_analytics)**
```sql
CREATE TABLE product_analytics (
    id_analytics INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NULL,
    variant_id INT NULL,
    branch_id INT,
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
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    UNIQUE KEY uk_product_date_branch (COALESCE(product_id, 0), COALESCE(variant_id, 0), date, COALESCE(branch_id, 0)),
    INDEX idx_date_branch (date, branch_id)
);
```

### **15. Product Reviews Table (product_reviews)**
```sql
CREATE TABLE product_reviews (
    id_review INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_id INT NULL,
    customer_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(200),
    review_content TEXT,
    pros TEXT,
    cons TEXT,
    would_recommend BOOLEAN,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id_variant) ON DELETE CASCADE,
    INDEX idx_product_rating (product_id, rating),
    INDEX idx_approved (is_approved),
    INDEX idx_customer (customer_id)
);
```

### **16. Product Specifications Table (product_specifications)**
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
);

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
);
```

## 🔄 Migration Strategy

### **Phase 1: Create Normalized Structure**
1. Create new normalized tables
2. Set up proper foreign key relationships
3. Create indexes for performance

### **Phase 2: Data Migration**
1. Migrate core product data
2. Extract attributes to attribute tables
3. Separate pricing data
4. Move media files to media table
5. Migrate compliance data

### **Phase 3: Validation & Testing**
1. Data integrity checks
2. Performance testing
3. Application updates
4. Rollback planning

## 📈 Benefits of Normalization

### **Performance Improvements:**
- Smaller table sizes
- Better query performance
- Efficient indexing
- Reduced redundancy

### **Data Integrity:**
- Proper foreign key constraints
- No data duplication
- Consistent data types
- Better validation

### **Scalability:**
- Easier to add new attributes
- Flexible pricing models
- Better support for variants
- Improved maintenance

### **Maintenance:**
- Clear table responsibilities
- Easier debugging
- Better documentation
- Simplified backups

## 🎯 Normalization Rules Applied

### **1NF (First Normal Form):**
- All columns atomic
- No repeating groups
- Primary key defined

### **2NF (Second Normal Form):**
- No partial dependencies
- All non-key attributes depend on entire primary key
- Proper table decomposition

### **3NF (Third Normal Form):**
- No transitive dependencies
- All attributes depend only on primary key
- Eliminated redundancy

---

*Database ini sekarang fully normalized dengan 16 tabel utama yang mendukung skalabilitas, performa, dan integritas data untuk aplikasi perdagangan Indonesia.*
