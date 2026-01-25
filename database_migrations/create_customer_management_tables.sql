-- =====================================================
-- CUSTOMER MANAGEMENT SYSTEM - PHASE 1 IMPLEMENTATION
-- Complete CRM and Customer Relationship Management
-- Supports all business scales from individual to enterprise
-- =====================================================

USE perdagangan_system;

-- 1. Customers Table - Core Customer Data
CREATE TABLE IF NOT EXISTS customers (
    id_customer INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(200) NOT NULL,
    customer_type ENUM('individual', 'business', 'corporate', 'government') DEFAULT 'individual',
    business_name VARCHAR(200),
    tax_id VARCHAR(50),
    phone VARCHAR(50),
    email VARCHAR(100),
    whatsapp VARCHAR(50),
    
    -- Address Information
    address_id INT,
    address_detail TEXT,
    province_id INT,
    regency_id INT,
    district_id INT,
    village_id INT,
    postal_code VARCHAR(10),
    
    -- Customer Classification
    customer_segment ENUM('regular', 'vip', 'premium', 'wholesale', 'corporate') DEFAULT 'regular',
    customer_category ENUM('walk_in', 'frequent', 'loyal', 'high_value', 'at_risk') DEFAULT 'walk_in',
    
    -- Business Metrics
    total_purchases DECIMAL(15,2) DEFAULT 0.00,
    total_transactions INT DEFAULT 0,
    average_transaction_value DECIMAL(15,2) DEFAULT 0.00,
    last_purchase_date DATE,
    first_purchase_date DATE,
    
    -- Credit Management
    credit_limit DECIMAL(15,2) DEFAULT 0.00,
    current_debt DECIMAL(15,2) DEFAULT 0.00,
    credit_status ENUM('no_credit', 'good', 'at_risk', 'overdue', 'blocked') DEFAULT 'no_credit',
    payment_terms ENUM('cash', '7_days', '14_days', '30_days', '60_days') DEFAULT 'cash',
    
    -- Loyalty Program
    loyalty_points INT DEFAULT 0,
    loyalty_tier ENUM('bronze', 'silver', 'gold', 'platinum', 'diamond') DEFAULT 'bronze',
    membership_date DATE,
    
    -- Communication Preferences
    preferred_contact ENUM('phone', 'email', 'whatsapp', 'sms') DEFAULT 'phone',
    marketing_consent BOOLEAN DEFAULT FALSE,
    notification_consent BOOLEAN DEFAULT TRUE,
    
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
    INDEX idx_customer_code (customer_code),
    INDEX idx_customer_name (customer_name),
    INDEX idx_customer_type (customer_type),
    INDEX idx_customer_segment (customer_segment),
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    INDEX idx_loyalty_tier (loyalty_tier),
    INDEX idx_credit_status (credit_status),
    INDEX idx_last_purchase (last_purchase_date),
    INDEX idx_total_purchases (total_purchases),
    INDEX idx_active (is_active),
    INDEX idx_blacklisted (is_blacklisted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Customer Addresses Table (for multiple addresses)
CREATE TABLE IF NOT EXISTS customer_addresses (
    id_address INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    address_type ENUM('billing', 'shipping', 'both') DEFAULT 'both',
    address_detail TEXT NOT NULL,
    province_id INT,
    regency_id INT,
    district_id INT,
    village_id INT,
    postal_code VARCHAR(10),
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id_customer) ON DELETE CASCADE,
    -- FOREIGN KEY (province_id) REFERENCES provinces(id),
    -- FOREIGN KEY (regency_id) REFERENCES regencies(id),
    -- FOREIGN KEY (district_id) REFERENCES districts(id),
    -- FOREIGN KEY (village_id) REFERENCES villages(id),
    
    INDEX idx_customer (customer_id),
    INDEX idx_address_type (address_type),
    INDEX idx_primary (is_primary),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Customer Contacts Table (multiple contact persons)
CREATE TABLE IF NOT EXISTS customer_contacts (
    id_contact INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    contact_name VARCHAR(200) NOT NULL,
    contact_position VARCHAR(100),
    phone VARCHAR(50),
    email VARCHAR(100),
    whatsapp VARCHAR(50),
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id_customer) ON DELETE CASCADE,
    
    INDEX idx_customer (customer_id),
    INDEX idx_primary (is_primary),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Customer Groups Table (for segmentation)
CREATE TABLE IF NOT EXISTS customer_groups (
    id_group INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(100) NOT NULL,
    group_description TEXT,
    group_type ENUM('demographic', 'behavioral', 'geographic', 'psychographic', 'custom') DEFAULT 'custom',
    color_code VARCHAR(7) DEFAULT '#007bff',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES members(id_member),
    
    INDEX idx_group_name (group_name),
    INDEX idx_group_type (group_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Customer Group Memberships
CREATE TABLE IF NOT EXISTS customer_group_memberships (
    id_membership INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    group_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id_customer) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES customer_groups(id_group) ON DELETE CASCADE,
    
    UNIQUE KEY uk_customer_group (customer_id, group_id),
    INDEX idx_customer (customer_id),
    INDEX idx_group (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Customer Tags Table (for flexible tagging)
CREATE TABLE IF NOT EXISTS customer_tags (
    id_tag INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(50) NOT NULL,
    tag_color VARCHAR(7) DEFAULT '#6c757d',
    tag_description TEXT,
    usage_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tag_name (tag_name),
    INDEX idx_usage_count (usage_count),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Customer Tag Assignments
CREATE TABLE IF NOT EXISTS customer_tag_assignments (
    id_assignment INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    tag_id INT NOT NULL,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id_customer) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES customer_tags(id_tag) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES members(id_member),
    
    UNIQUE KEY uk_customer_tag (customer_id, tag_id),
    INDEX idx_customer (customer_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Customer Interaction History
CREATE TABLE IF NOT EXISTS customer_interactions (
    id_interaction INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    interaction_type ENUM('phone_call', 'email', 'whatsapp', 'sms', 'visit', 'complaint', 'inquiry', 'support') NOT NULL,
    interaction_date DATETIME NOT NULL,
    subject VARCHAR(200),
    description TEXT,
    outcome ENUM('successful', 'pending', 'failed', 'requires_follow_up') DEFAULT 'pending',
    follow_up_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id_customer) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES members(id_member),
    
    INDEX idx_customer (customer_id),
    INDEX idx_interaction_type (interaction_type),
    INDEX idx_interaction_date (interaction_date),
    INDEX idx_outcome (outcome),
    INDEX idx_follow_up (follow_up_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Customer Loyalty Transactions
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id_transaction INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    transaction_type ENUM('earned', 'redeemed', 'expired', 'adjusted') NOT NULL,
    points INT NOT NULL,
    reference_type ENUM('purchase', 'redemption', 'manual_adjustment', 'bonus') NOT NULL,
    reference_id INT,
    description TEXT,
    transaction_date DATETIME NOT NULL,
    expires_at DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id_customer) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES members(id_member),
    
    INDEX idx_customer (customer_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_expires (expires_at),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Customer Feedback and Reviews
CREATE TABLE IF NOT EXISTS customer_feedback (
    id_feedback INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    feedback_type ENUM('review', 'complaint', 'suggestion', 'compliment') NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    response TEXT,
    status ENUM('pending', 'responded', 'resolved', 'closed') DEFAULT 'pending',
    feedback_date DATETIME NOT NULL,
    responded_by INT,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id_customer) ON DELETE CASCADE,
    FOREIGN KEY (responded_by) REFERENCES members(id_member),
    
    INDEX idx_customer (customer_id),
    INDEX idx_feedback_type (feedback_type),
    INDEX idx_rating (rating),
    INDEX idx_status (status),
    INDEX idx_feedback_date (feedback_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Views for Customer Analytics

-- View: Customer Summary with Metrics
CREATE OR REPLACE VIEW v_customer_summary AS
SELECT 
    c.id_customer,
    c.customer_code,
    c.customer_name,
    c.customer_type,
    c.business_name,
    c.phone,
    c.email,
    c.customer_segment,
    c.customer_category,
    c.loyalty_tier,
    c.total_purchases,
    c.total_transactions,
    c.average_transaction_value,
    c.last_purchase_date,
    c.first_purchase_date,
    c.credit_limit,
    c.current_debt,
    c.credit_status,
    c.loyalty_points,
    c.is_active,
    c.is_blacklisted,
    DATEDIFF(CURRENT_DATE, c.last_purchase_date) as days_since_last_purchase,
    CASE 
        WHEN c.last_purchase_date IS NULL THEN 'Never Purchased'
        WHEN DATEDIFF(CURRENT_DATE, c.last_purchase_date) <= 30 THEN 'Active'
        WHEN DATEDIFF(CURRENT_DATE, c.last_purchase_date) <= 90 THEN 'Recent'
        WHEN DATEDIFF(CURRENT_DATE, c.last_purchase_date) <= 180 THEN 'At Risk'
        ELSE 'Inactive'
    END as activity_status,
    COUNT(DISTINCT cg.id_group) as group_count,
    COUNT(DISTINCT cta.id_tag) as tag_count,
    COUNT(DISTINCT ci.id_interaction) as interaction_count
FROM customers c
LEFT JOIN customer_group_memberships cgm ON c.id_customer = cgm.customer_id
LEFT JOIN customer_groups cg ON cgm.group_id = cg.id_group AND cg.is_active = 1
LEFT JOIN customer_tag_assignments cta ON c.id_customer = cta.customer_id
LEFT JOIN customer_tags ct ON cta.tag_id = ct.id_tag AND ct.is_active = 1
LEFT JOIN customer_interactions ci ON c.id_customer = ci.customer_id
WHERE c.is_active = 1
GROUP BY c.id_customer, c.customer_code, c.customer_name, c.customer_type, c.business_name, 
         c.phone, c.email, c.customer_segment, c.customer_category, c.loyalty_tier,
         c.total_purchases, c.total_transactions, c.average_transaction_value, 
         c.last_purchase_date, c.first_purchase_date, c.credit_limit, c.current_debt,
         c.credit_status, c.loyalty_points, c.is_active, c.is_blacklisted;

-- View: Customer Analytics Dashboard
CREATE OR REPLACE VIEW v_customer_analytics AS
SELECT 
    customer_segment,
    loyalty_tier,
    customer_type,
    COUNT(*) as customer_count,
    SUM(total_purchases) as total_revenue,
    AVG(total_purchases) as avg_customer_value,
    SUM(total_transactions) as total_transactions,
    AVG(total_transactions) as avg_transactions_per_customer,
    SUM(loyalty_points) as total_loyalty_points,
    SUM(CASE WHEN credit_status != 'no_credit' THEN 1 ELSE 0 END) as credit_customers,
    SUM(CASE WHEN is_blacklisted = 1 THEN 1 ELSE 0 END) as blacklisted_customers,
    SUM(CASE WHEN last_purchase_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_customers_30d,
    SUM(CASE WHEN last_purchase_date >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY) THEN 1 ELSE 0 END) as active_customers_90d
FROM customers 
WHERE is_active = 1
GROUP BY customer_segment, loyalty_tier, customer_type;

-- Insert Default Customer Groups
INSERT INTO customer_groups (group_name, group_description, group_type, color_code) VALUES
('High Value Customers', 'Customers with high purchase volume', 'behavioral', '#28a745'),
('At Risk Customers', 'Customers who haven\'t purchased recently', 'behavioral', '#ffc107'),
('New Customers', 'Customers who joined in the last 30 days', 'behavioral', '#17a2b8'),
('VIP Customers', 'Very important premium customers', 'custom', '#6f42c1'),
('Wholesale Buyers', 'Business customers buying in bulk', 'custom', '#fd7e14'),
('Local Residents', 'Customers from the same area', 'geographic', '#20c997');

-- Insert Default Customer Tags
INSERT INTO customer_tags (tag_name, tag_color, tag_description) VALUES
('Frequent Buyer', '#28a745', 'Buys regularly'),
('High Spender', '#dc3545', 'Above average spending'),
('Late Payer', '#ffc107', 'Often pays late'),
('Good Payer', '#17a2b8', 'Always pays on time'),
('New Customer', '#6f42c1', 'Recently joined'),
('VIP', '#fd7e14', 'Very important customer'),
('Problematic', '#dc3545', 'Requires attention'),
('Loyal', '#20c997', 'Long-term customer');

-- Update existing accounts_receivable to reference customers table
ALTER TABLE accounts_receivable 
ADD CONSTRAINT fk_ar_customer 
FOREIGN KEY (customer_id) REFERENCES customers(id_customer) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Add customer_id to transactions table if not exists
ALTER TABLE transactions 
ADD COLUMN customer_id INT NULL,
ADD CONSTRAINT fk_transaction_customer 
FOREIGN KEY (customer_id) REFERENCES customers(id_customer) 
ON DELETE SET NULL ON UPDATE CASCADE,
ADD INDEX idx_customer (customer_id);

-- Create indexes for performance optimization
CREATE INDEX idx_customer_composite ON customers(customer_segment, loyalty_tier, is_active);
CREATE INDEX idx_customer_purchases ON customers(total_purchases DESC, last_purchase_date DESC);
CREATE INDEX idx_customer_credit ON customers(credit_status, current_debt);

-- Add triggers for automatic customer metrics updates
DELIMITER //

-- Trigger to update customer metrics after transaction
CREATE TRIGGER update_customer_metrics_after_transaction
AFTER INSERT ON transaction_items
FOR EACH ROW
BEGIN
    DECLARE transaction_customer_id INT;
    DECLARE transaction_total DECIMAL(15,2);
    
    -- Get customer and total from transaction
    SELECT customer_id, final_amount INTO transaction_customer_id, transaction_total
    FROM transactions 
    WHERE id_transaction = NEW.transaction_id;
    
    -- Update customer metrics if customer exists
    IF transaction_customer_id IS NOT NULL THEN
        UPDATE customers SET
            total_purchases = total_purchases + transaction_total,
            total_transactions = total_transactions + 1,
            average_transaction_value = total_purchases / total_transactions,
            last_purchase_date = CURRENT_DATE,
            first_purchase_date = COALESCE(first_purchase_date, CURRENT_DATE)
        WHERE id_customer = transaction_customer_id;
    END IF;
END//

-- Trigger to update tag usage count
CREATE TRIGGER update_tag_usage_count
AFTER INSERT ON customer_tag_assignments
FOR EACH ROW
BEGIN
    UPDATE customer_tags 
    SET usage_count = usage_count + 1 
    WHERE id_tag = NEW.tag_id;
END//

DELIMITER ;
