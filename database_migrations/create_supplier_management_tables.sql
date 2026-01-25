-- =====================================================
-- SUPPLIER MANAGEMENT SYSTEM - COMPLETE PROCUREMENT SOLUTION
-- Supports Indonesian business context with NPWP validation
-- =====================================================

USE perdagangan_system;

-- 1. Suppliers Table - Core Supplier Data
CREATE TABLE IF NOT EXISTS suppliers (
    id_supplier INT AUTO_INCREMENT PRIMARY KEY,
    supplier_code VARCHAR(50) UNIQUE NOT NULL,
    supplier_name VARCHAR(200) NOT NULL,
    supplier_type ENUM('individual', 'company', 'distributor', 'manufacturer', 'importer', 'local_producer') DEFAULT 'company',
    business_category ENUM('retail', 'wholesale', 'manufacturing', 'agriculture', 'services', 'distribution', 'import_export') DEFAULT 'wholesale',
    
    -- Tax Information (Indonesian Context)
    tax_id VARCHAR(50), -- NPWP (Nomor Pokok Wajib Pajak)
    tax_name VARCHAR(200), -- Tax registration name
    is_tax_registered BOOLEAN DEFAULT FALSE,
    
    -- Contact Information
    contact_person VARCHAR(200),
    phone VARCHAR(50),
    mobile VARCHAR(50),
    email VARCHAR(100),
    website VARCHAR(255),
    
    -- Address Information
    address_id INT,
    address_detail TEXT,
    province_id INT,
    regency_id INT,
    district_id INT,
    village_id INT,
    postal_code VARCHAR(10),
    
    -- Business Information
    business_license VARCHAR(100), -- SIUP (Surat Izin Usaha Perdagangan)
    business_registration VARCHAR(100), -- TDP (Tanda Daftar Perusahaan)
    establishment_date DATE,
    capital_amount DECIMAL(15,2),
    
    -- Banking Information
    bank_name VARCHAR(100),
    bank_account_number VARCHAR(50),
    bank_account_name VARCHAR(200),
    bank_branch VARCHAR(100),
    
    -- Supplier Classification
    supplier_category ENUM('regular', 'preferred', 'strategic', 'backup', 'blacklisted') DEFAULT 'regular',
    supplier_level ENUM('basic', 'silver', 'gold', 'platinum') DEFAULT 'basic',
    
    -- Performance Metrics
    total_orders INT DEFAULT 0,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    average_delivery_time INT DEFAULT 0, -- in days
    on_time_delivery_rate DECIMAL(5,2) DEFAULT 0.00, -- percentage
    quality_score DECIMAL(5,2) DEFAULT 0.00, -- 1-100 scale
    overall_score DECIMAL(5,2) DEFAULT 0.00, -- 1-100 scale
    
    -- Payment Terms
    payment_terms ENUM('cod', '7_days', '14_days', '30_days', '45_days', '60_days', '90_days') DEFAULT '30_days',
    credit_limit DECIMAL(15,2) DEFAULT 0.00,
    current_balance DECIMAL(15,2) DEFAULT 0.00,
    
    -- Status and Metadata
    is_active BOOLEAN DEFAULT TRUE,
    is_blacklisted BOOLEAN DEFAULT FALSE,
    blacklist_reason TEXT,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (address_id) REFERENCES addresses(id_address),
    FOREIGN KEY (created_by) REFERENCES members(id_member),
    -- FOREIGN KEY (province_id) REFERENCES provinces(id),
    -- FOREIGN KEY (regency_id) REFERENCES regencies(id),
    -- FOREIGN KEY (district_id) REFERENCES districts(id),
    -- FOREIGN KEY (village_id) REFERENCES villages(id),
    
    -- Indexes
    INDEX idx_supplier_code (supplier_code),
    INDEX idx_supplier_name (supplier_name),
    INDEX idx_supplier_type (supplier_type),
    INDEX idx_supplier_category (supplier_category),
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    INDEX idx_tax_id (tax_id),
    INDEX idx_payment_terms (payment_terms),
    INDEX idx_overall_score (overall_score),
    INDEX idx_active (is_active),
    INDEX idx_blacklisted (is_blacklisted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Supplier Contacts Table (Multiple contact persons)
CREATE TABLE IF NOT EXISTS supplier_contacts (
    id_contact INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    contact_name VARCHAR(200) NOT NULL,
    contact_position VARCHAR(100),
    contact_department VARCHAR(100),
    phone VARCHAR(50),
    mobile VARCHAR(50),
    email VARCHAR(100),
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id_supplier) ON DELETE CASCADE,
    
    INDEX idx_supplier (supplier_id),
    INDEX idx_primary (is_primary),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Supplier Products Table (Products supplied by each supplier)
CREATE TABLE IF NOT EXISTS supplier_products (
    id_supplier_product INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    product_id INT,
    supplier_product_code VARCHAR(100), -- Supplier's internal product code
    supplier_product_name VARCHAR(200), -- Supplier's product name
    unit_price DECIMAL(15,2),
    min_order_quantity DECIMAL(10,2) DEFAULT 1,
    lead_time_days INT DEFAULT 0,
    availability ENUM('always', 'seasonal', 'on_demand', 'discontinued') DEFAULT 'always',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id_supplier) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE SET NULL,
    
    INDEX idx_supplier (supplier_id),
    INDEX idx_product (product_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Purchase Orders Table
CREATE TABLE IF NOT EXISTS purchase_orders (
    id_po INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_id INT NOT NULL,
    branch_id INT NOT NULL,
    
    -- Order Information
    order_date DATE NOT NULL,
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    status ENUM('draft', 'sent', 'confirmed', 'partial_received', 'received', 'cancelled', 'closed') DEFAULT 'draft',
    
    -- Financial Information
    subtotal DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    discount_amount DECIMAL(15,2) DEFAULT 0.00,
    shipping_cost DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL,
    
    -- Payment Information
    payment_terms ENUM('cod', '7_days', '14_days', '30_days', '45_days', '60_days', '90_days') DEFAULT '30_days',
    payment_status ENUM('unpaid', 'partial', 'paid', 'overdue') DEFAULT 'unpaid',
    due_date DATE,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    
    -- Delivery Information
    delivery_address TEXT,
    delivery_contact_person VARCHAR(200),
    delivery_phone VARCHAR(50),
    delivery_instructions TEXT,
    
    -- Approval Workflow
    requested_by INT,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    approval_notes TEXT,
    
    -- Metadata
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id_supplier),
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch),
    FOREIGN KEY (requested_by) REFERENCES members(id_member),
    FOREIGN KEY (approved_by) REFERENCES members(id_member),
    
    -- Indexes
    INDEX idx_po_number (po_number),
    INDEX idx_supplier (supplier_id),
    INDEX idx_branch (branch_id),
    INDEX idx_order_date (order_date),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Purchase Order Items Table
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id_po_item INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    product_id INT,
    supplier_product_id INT,
    product_code VARCHAR(100),
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    
    -- Order Details
    quantity_ordered DECIMAL(10,2) NOT NULL,
    quantity_received DECIMAL(10,2) DEFAULT 0.00,
    unit_price DECIMAL(15,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Calculated Fields
    subtotal DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL,
    
    -- Status
    status ENUM('ordered', 'partial_received', 'received', 'cancelled') DEFAULT 'ordered',
    
    -- Notes
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id_po) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id_product),
    FOREIGN KEY (supplier_product_id) REFERENCES supplier_products(id_supplier_product),
    
    -- Indexes
    INDEX idx_po (po_id),
    INDEX idx_product (product_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Supplier Performance Tracking
CREATE TABLE IF NOT EXISTS supplier_performance (
    id_performance INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    evaluation_period_start DATE NOT NULL,
    evaluation_period_end DATE NOT NULL,
    
    -- Performance Metrics (1-100 scale)
    delivery_score DECIMAL(5,2), -- On-time delivery performance
    quality_score DECIMAL(5,2), -- Product quality rating
    price_score DECIMAL(5,2), -- Price competitiveness
    service_score DECIMAL(5,2), -- Customer service quality
    compliance_score DECIMAL(5,2), -- Documentation and compliance
    
    -- Calculated Metrics
    overall_score DECIMAL(5,2),
    
    -- Statistics
    total_orders INT DEFAULT 0,
    on_time_deliveries INT DEFAULT 0,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    average_delivery_time DECIMAL(5,2) DEFAULT 0.00, -- in days
    
    -- Evaluation Details
    evaluator_id INT,
    evaluation_notes TEXT,
    recommendations TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id_supplier),
    FOREIGN KEY (evaluator_id) REFERENCES members(id_member),
    
    INDEX idx_supplier (supplier_id),
    INDEX idx_evaluation_period (evaluation_period_start, evaluation_period_end),
    INDEX idx_overall_score (overall_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Supplier Categories Table (for classification)
CREATE TABLE IF NOT EXISTS supplier_categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_description TEXT,
    parent_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES supplier_categories(id_category),
    
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Supplier Category Assignments
CREATE TABLE IF NOT EXISTS supplier_category_assignments (
    id_assignment INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    category_id INT NOT NULL,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id_supplier) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES supplier_categories(id_category) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES members(id_member),
    
    UNIQUE KEY uk_supplier_category (supplier_id, category_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Views for Supplier Analytics

-- View: Supplier Summary with Performance
CREATE OR REPLACE VIEW v_supplier_summary AS
SELECT 
    s.id_supplier,
    s.supplier_code,
    s.supplier_name,
    s.supplier_type,
    s.business_category,
    s.contact_person,
    s.phone,
    s.email,
    s.supplier_category,
    s.supplier_level,
    s.payment_terms,
    s.total_orders,
    s.total_amount,
    s.average_delivery_time,
    s.on_time_delivery_rate,
    s.quality_score,
    s.overall_score,
    s.current_balance,
    s.is_active,
    s.is_blacklisted,
    p.name as province_name,
    r.name as regency_name,
    d.name as district_name,
    v.name as village_name,
    CASE 
        WHEN s.overall_score >= 90 THEN 'Excellent'
        WHEN s.overall_score >= 80 THEN 'Very Good'
        WHEN s.overall_score >= 70 THEN 'Good'
        WHEN s.overall_score >= 60 THEN 'Fair'
        WHEN s.overall_score >= 50 THEN 'Poor'
        ELSE 'Very Poor'
    END as performance_rating,
    COUNT(DISTINCT sp.id_supplier_product) as product_count,
    COUNT(DISTINCT sc.id_contact) as contact_count
FROM suppliers s
LEFT JOIN alamat_db.provinces p ON s.province_id = p.id_province
LEFT JOIN alamat_db.regencies r ON s.regency_id = r.id_regency  
LEFT JOIN alamat_db.districts d ON s.district_id = d.id_district
LEFT JOIN alamat_db.villages v ON s.village_id = v.id_village
LEFT JOIN supplier_products sp ON s.id_supplier = sp.supplier_id AND sp.is_active = 1
LEFT JOIN supplier_contacts sc ON s.id_supplier = sc.supplier_id AND sc.is_active = 1
GROUP BY s.id_supplier, s.supplier_code, s.supplier_name, s.supplier_type, s.business_category,
         s.contact_person, s.phone, s.email, s.supplier_category, s.supplier_level, s.payment_terms,
         s.total_orders, s.total_amount, s.average_delivery_time, s.on_time_delivery_rate,
         s.quality_score, s.overall_score, s.current_balance, s.is_active, s.is_blacklisted,
         p.name, r.name, d.name, v.name;

-- View: Purchase Order Analytics
CREATE OR REPLACE VIEW v_purchase_order_analytics AS
SELECT 
    po.id_po,
    po.po_number,
    po.supplier_id,
    s.supplier_name,
    po.branch_id,
    b.branch_name,
    po.order_date,
    po.expected_delivery_date,
    po.actual_delivery_date,
    po.status,
    po.total_amount,
    po.payment_status,
    po.payment_terms,
    DATEDIFF(po.actual_delivery_date, po.order_date) as actual_delivery_days,
    DATEDIFF(po.expected_delivery_date, po.order_date) as expected_delivery_days,
    CASE 
        WHEN po.actual_delivery_date <= po.expected_delivery_date THEN 'On Time'
        WHEN po.actual_delivery_date IS NULL THEN 'Pending'
        ELSE 'Late'
    END as delivery_performance,
    COUNT(DISTINCT poi.id_po_item) as item_count,
    SUM(poi.quantity_ordered) as total_quantity_ordered,
    SUM(poi.quantity_received) as total_quantity_received
FROM purchase_orders po
LEFT JOIN suppliers s ON po.supplier_id = s.id_supplier
LEFT JOIN branches b ON po.branch_id = b.id_branch
LEFT JOIN purchase_order_items poi ON po.id_po = poi.id_po
GROUP BY po.id_po, po.po_number, po.supplier_id, s.supplier_name, po.branch_id, b.branch_name,
         po.order_date, po.expected_delivery_date, po.actual_delivery_date, po.status,
         po.total_amount, po.payment_status, po.payment_terms;

-- Insert Default Supplier Categories
INSERT INTO supplier_categories (category_name, category_description) VALUES
('Raw Materials', 'Suppliers of raw materials for production'),
('Finished Goods', 'Suppliers of finished products for resale'),
('Packaging Materials', 'Suppliers of packaging and shipping materials'),
('Equipment & Tools', 'Suppliers of business equipment and tools'),
('Services', 'Service providers and consultants'),
('Utilities', 'Utility companies and service providers'),
('Technology', 'IT and technology suppliers'),
('Maintenance', 'Maintenance and repair services'),
('Transportation', 'Logistics and transportation providers'),
('Office Supplies', 'Office and administrative supplies');

-- Create Triggers for Automatic Metrics Update

DELIMITER //

-- Trigger to update supplier metrics when purchase order is completed
CREATE TRIGGER update_supplier_metrics_on_po_complete
AFTER UPDATE ON purchase_orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'received' AND OLD.status != 'received' THEN
        -- Update supplier totals
        UPDATE suppliers SET
            total_orders = total_orders + 1,
            total_amount = total_amount + NEW.total_amount,
            updated_at = CURRENT_TIMESTAMP
        WHERE id_supplier = NEW.supplier_id;
        
        -- Update delivery performance
        IF NEW.actual_delivery_date IS NOT NULL AND NEW.expected_delivery_date IS NOT NULL THEN
            SET @delivery_days = DATEDIFF(NEW.actual_delivery_date, NEW.expected_delivery_date);
            SET @is_on_time = CASE WHEN NEW.actual_delivery_date <= NEW.expected_delivery_date THEN 1 ELSE 0 END;
            
            UPDATE suppliers SET
                average_delivery_time = (
                    (average_delivery_time * (total_orders - 1) + @delivery_days) / total_orders
                ),
                on_time_delivery_rate = (
                    (on_time_delivery_rate * (total_orders - 1) + (@is_on_time * 100)) / total_orders
                ),
                updated_at = CURRENT_TIMESTAMP
            WHERE id_supplier = NEW.supplier_id;
        END IF;
    END IF;
END//

-- Trigger to generate PO numbers
CREATE TRIGGER generate_po_number
BEFORE INSERT ON purchase_orders
FOR EACH ROW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(po_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM purchase_orders
    WHERE po_number LIKE 'PO-%'
    AND DATE_FORMAT(order_date, '%Y') = YEAR(NEW.order_date);
    
    SET NEW.po_number = CONCAT('PO-', YEAR(NEW.order_date), '-', LPAD(next_number, 5, '0'));
END//

DELIMITER ;

-- Add indexes for performance optimization
CREATE INDEX idx_supplier_composite ON suppliers(supplier_category, supplier_level, is_active);
CREATE INDEX idx_po_composite ON purchase_orders(supplier_id, status, order_date);
CREATE INDEX idx_performance_composite ON supplier_performance(supplier_id, overall_score, evaluation_period_end);

-- Add full-text search indexes
ALTER TABLE suppliers ADD FULLTEXT(supplier_name, contact_person, notes);
ALTER TABLE purchase_orders ADD FULLTEXT(po_number, notes);

-- Update existing transactions table to reference suppliers
ALTER TABLE transactions 
ADD COLUMN supplier_id INT NULL,
ADD COLUMN reference_type ENUM('po', 'direct', 'other') DEFAULT 'direct',
ADD COLUMN reference_id INT NULL,
ADD INDEX idx_supplier (supplier_id),
ADD INDEX idx_reference (reference_type, reference_id);

-- Create stored procedures for supplier analytics

DELIMITER //

CREATE PROCEDURE GetSupplierPerformanceReport(IN supplier_id INT)
BEGIN
    SELECT 
        s.*,
        sp.overall_score,
        sp.delivery_score,
        sp.quality_score,
        sp.price_score,
        sp.service_score,
        sp.total_orders as period_orders,
        sp.total_amount as period_amount,
        sp.evaluation_period_start,
        sp.evaluation_period_end
    FROM suppliers s
    LEFT JOIN supplier_performance sp ON s.id_supplier = sp.supplier_id
    WHERE s.id_supplier = supplier_id
    ORDER BY sp.evaluation_period_end DESC
    LIMIT 1;
END//

CREATE PROCEDURE GetTopSuppliers(IN limit_count INT, IN period_months INT)
BEGIN
    SELECT 
        s.id_supplier,
        s.supplier_code,
        s.supplier_name,
        s.supplier_category,
        s.overall_score,
        COUNT(po.id_po) as order_count,
        SUM(po.total_amount) as total_value,
        AVG(DATEDIFF(po.actual_delivery_date, po.expected_delivery_date)) as avg_delivery_delay
    FROM suppliers s
    LEFT JOIN purchase_orders po ON s.id_supplier = po.supplier_id
        AND po.order_date >= DATE_SUB(CURRENT_DATE, INTERVAL period_months MONTH)
        AND po.status = 'received'
    WHERE s.is_active = 1 AND s.is_blacklisted = 0
    GROUP BY s.id_supplier, s.supplier_code, s.supplier_name, s.supplier_category, s.overall_score
    HAVING order_count > 0
    ORDER BY total_value DESC, overall_score DESC
    LIMIT limit_count;
END//

DELIMITER ;
