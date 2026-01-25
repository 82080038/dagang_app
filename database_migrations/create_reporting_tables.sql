-- Database Schema for Advanced Reporting System
-- Part of Phase 1: Critical Business Features Implementation

-- Report Templates Table
CREATE TABLE IF NOT EXISTS report_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    query_template TEXT NOT NULL,
    parameters JSON,
    created_by INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_created_by (created_by)
);

-- Report Permissions Table
CREATE TABLE IF NOT EXISTS report_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permission_name VARCHAR(100) UNIQUE NOT NULL,
    permission_group VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_permission_group (permission_group),
    INDEX idx_is_active (is_active)
);

-- Report Template Permissions Mapping
CREATE TABLE IF NOT EXISTS report_template_permissions (
    template_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (template_id, permission_id),
    FOREIGN KEY (template_id) REFERENCES report_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES report_permissions(id) ON DELETE CASCADE
);

-- Report Schedules Table
CREATE TABLE IF NOT EXISTS report_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_template_id INT NOT NULL,
    schedule_type ENUM('daily','weekly','monthly','yearly') NOT NULL,
    recipients JSON NOT NULL,
    next_run DATETIME NOT NULL,
    last_run DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (report_template_id) REFERENCES report_templates(id) ON DELETE CASCADE,
    INDEX idx_next_run (next_run),
    INDEX idx_is_active (is_active),
    INDEX idx_schedule_type (schedule_type)
);

-- Report History Table
CREATE TABLE IF NOT EXISTS report_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_template_id INT NOT NULL,
    schedule_id INT NULL,
    generated_by INT,
    file_path VARCHAR(500),
    file_format ENUM('pdf','excel','csv') NOT NULL,
    parameters JSON,
    file_size INT,
    generated_at DATETIME NOT NULL,
    expires_at DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_template_id) REFERENCES report_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES report_schedules(id) ON DELETE SET NULL,
    INDEX idx_generated_at (generated_at),
    INDEX idx_template_id (report_template_id),
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_expires_at (expires_at)
);

-- Insert basic report permissions
INSERT INTO report_permissions (permission_name, permission_group, description) VALUES
('view_basic', 'reports', 'View basic reports'),
('view_advanced', 'reports', 'View advanced reports'),
('view_financial', 'reports', 'View financial reports'),
('export', 'reports', 'Export reports'),
('create', 'reports', 'Create custom reports'),
('manage_templates', 'reports', 'Manage report templates'),
('schedule', 'reports', 'Schedule automated reports'),
('delete', 'reports', 'Delete reports');

-- Insert sample report templates
INSERT INTO report_templates (name, description, query_template, parameters, created_by) VALUES 
('Sales Summary Report', 'Daily/Weekly/Monthly sales summary with revenue and transaction counts', 
'SELECT COUNT(*) as total_transactions, COALESCE(SUM(total_amount), 0) as total_revenue, COALESCE(AVG(total_amount), 0) as avg_transaction_value FROM transactions WHERE created_at BETWEEN :start_date AND :end_date', 
'{"start_date": {"type": "date", "required": true}, "end_date": {"type": "date", "required": true}}', 1),

('Inventory Stock Levels', 'Current stock levels across all branches with low stock alerts', 
'SELECT p.product_name, p.product_code, b.branch_name, bi.quantity as current_stock, COALESCE(bi.min_stock, 0) as min_stock FROM branch_inventory bi JOIN products p ON bi.product_id = p.id_product JOIN branches b ON bi.branch_id = b.id_branch', 
'{"branch_id": {"type": "integer", "required": false}, "category_id": {"type": "integer", "required": false}}', 1),

('Branch Performance Comparison', 'Compare performance across all branches', 
'SELECT b.branch_name, COUNT(*) as transactions, COALESCE(SUM(t.total_amount), 0) as revenue FROM transactions t JOIN branches b ON t.branch_id = b.id_branch WHERE t.created_at BETWEEN :start_date AND :end_date GROUP BY b.id_branch, b.branch_name ORDER BY revenue DESC', 
'{"start_date": {"type": "date", "required": true}, "end_date": {"type": "date", "required": true}, "company_id": {"type": "integer", "required": false}}', 1),

('Financial Profit & Loss', 'Basic profit and loss statement', 
'SELECT ''Revenue'' as category, ''Sales Revenue'' as subcategory, COALESCE(SUM(total_amount), 0) as amount FROM transactions WHERE created_at BETWEEN :start_date AND :end_date UNION ALL SELECT ''Expenses'' as category, ''Operating Costs'' as subcategory, 0 as amount', 
'{"start_date": {"type": "date", "required": true}, "end_date": {"type": "date", "required": true}, "company_id": {"type": "integer", "required": false}}', 1);

-- Assign permissions to templates
INSERT INTO report_template_permissions (template_id, permission_id) VALUES
(1, 1), (1, 4), -- Sales Summary - view_basic, export
(2, 1), (2, 4), -- Inventory - view_basic, export
(3, 1), (3, 2), (3, 4), -- Branch Performance - view_basic, view_advanced, export
(4, 1), (4, 3), (4, 4); -- Financial - view_basic, view_financial, export
