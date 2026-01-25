-- Database Schema for Enhanced User Management System
-- Part of Phase 2: System Administration Implementation

-- Users Table (Enhanced)
CREATE TABLE IF NOT EXISTS users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    branch_id INT NULL,
    company_id INT NULL,
    phone VARCHAR(50),
    address TEXT,
    profile_picture VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role_id (role_id),
    INDEX idx_branch_id (branch_id),
    INDEX idx_company_id (company_id),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
);

-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    level INT NOT NULL DEFAULT 999,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_is_active (is_active)
);

-- Permissions Table
CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permission_name VARCHAR(100) UNIQUE NOT NULL,
    permission_group VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_permission_group (permission_group),
    INDEX idx_is_active (is_active)
);

-- User Permissions Mapping
CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) => permissions(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_permission_id (permission_id)
);

-- Role Permissions Mapping
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) => roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) => permissions(id) ON DELETE CASCADE,
    INDEX idx_role_id (role_id),
    INDEX idx_permission_id (permission_id)
);

-- User Sessions Table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_time DATETIME NOT NULL,
    last_activity DATETIME,
    expires_at DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_active (is_active)
);

-- Audit Logs Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    old_values JSON,
    new_values JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE SET NULL,
    INDEX idx_activity_type (activity_type),
    idx_entity_type (entity_type),
    idx_entity_id (entity_id),
    idx_user_id (user_id),
    idx_created_at (created_at)
);

-- Insert default roles
INSERT INTO roles (name, description, level) VALUES 
('Super Admin', 'System administrator with full access', 1),
('Owner', 'Business owner with company-wide access', 2),
('Director', 'Company director with multi-branch access', 3),
('Manager', 'Branch manager with single branch access', 4),
('Supervisor', 'Department supervisor with limited access', 5),
('Cashier', 'Point of sale operator', 6),
('Staff', 'General staff with basic access', 7),
('Security', 'Security personnel with monitoring access', 8);

-- Insert default permissions
INSERT INTO permissions (permission_name, permission_group, description) VALUES
-- User Management
('users.create', 'users', 'Create new users'),
('users.read', 'users', 'View user list and details'),
('users.update', 'users', 'Update user information'),
('users.delete', 'users', 'Delete users'),
('users.export', 'users', 'Export user data'),
('users.manage_roles', 'users', 'Manage user roles and permissions'),
('users.bulk_operations', 'users', 'Perform bulk user operations'),

-- Companies Management
('companies.create', 'companies', 'Create new companies'),
('companies.read', 'companies', 'View company list and details'),
('companies.update', 'companies', 'Update company information'),
('companies.delete', 'companies', 'Delete companies'),
('companies.export', 'companies', 'Export company data'),

-- Branches Management
('branches.create', 'branches', 'Create new branches'),
('branches.read', 'branches', 'View branch list and details'),
('branches.update', 'branches', 'Update branch information'),
('branches.delete', 'branches', 'Delete branches'),
('branches.export', 'branches', 'Export branch data'),

-- Products Management
('products.create', 'products', 'Create new products'),
('products.read', 'products', 'View product list and details'),
('products.update', 'products', 'Update product information'),
('products.delete', 'products', 'Delete products'),
('products.export', 'products', 'Export product data'),

-- Transactions Management
('transactions.create', 'transactions', 'Create new transactions'),
('transactions.read', 'transactions', 'View transaction list and details'),
('transactions.update', 'transactions', 'Update transaction information'),
('transactions.delete', 'transactions', 'Delete transactions'),
('transactions.export', 'transactions', 'Export transaction data'),

-- Reports Management
('reports.view_basic', 'reports', 'View basic reports'),
('reports.view_advanced', 'reports', 'View advanced reports'),
('reports.view_financial', 'reports', 'View financial reports'),
('reports.export', 'reports', 'Export reports'),
('reports.create', 'reports', 'Create custom reports'),
('reports.manage_templates', 'reports', 'Manage report templates'),
('reports.schedule', 'reports', 'Schedule automated reports'),

-- Settings Management
('settings.view', 'settings', 'View system settings'),
('settings.update_basic', 'settings', 'Update basic settings'),
('settings.update_advanced', 'settings', 'Update advanced settings'),
('settings.system_config', 'settings', 'System configuration'),

-- Dashboard Access
('dashboard.view', 'dashboard', 'View dashboard'),
('dashboard.api_stats', 'dashboard', 'Access dashboard statistics'),

-- System Administration
('system.audit_logs', 'system', 'View audit logs'),
('system.backup', 'system', 'Perform system backup'),
('system.monitoring', 'system', 'System monitoring'),
('system.maintenance', 'system', 'System maintenance');

-- Assign permissions to roles
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id as role_id, p.id as permission_id
FROM roles r
CROSS JOIN permissions p ON 1=1
WHERE r.level <= 8; -- All roles get basic permissions

-- Super Admin gets all permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1 as role_id, id as permission_id FROM permissions;

-- Owner gets most permissions (except system admin)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2 as role_id, id as permission_id FROM permissions 
WHERE permission_group NOT IN ('system.audit_logs', 'system.backup', 'system.monitoring', 'system.maintenance');

-- Manager gets business permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4 as role_id, id as permission_id FROM permissions 
WHERE permission_group IN ('users', 'companies', 'branches', 'products', 'transactions', 'reports', 'dashboard');

-- Supervisor gets limited permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 5 as role_id, id as permission_id FROM permissions 
WHERE permission_group IN ('products', 'transactions', 'reports.view_basic', 'dashboard.view');

-- Cashier gets operational permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 6 as role_id, id as permission_id FROM permissions 
WHERE permission_group IN ('products', 'transactions', 'dashboard.view');

-- Staff gets basic permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 7 as role_id, id as permission_id FROM permissions 
WHERE permission_group IN ('dashboard.view');

-- Security gets monitoring permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 8 as role_id, id as permission_id FROM permissions 
WHERE permission_group IN ('dashboard.view', 'system.monitoring');

-- Create default super admin user if not exists
INSERT INTO users (
    username, email, full_name, password, role_id, is_active, created_at, created_by
) VALUES (
    'admin', 'admin@perdagangan.com', 'System Administrator', 
    '$2y$10$92IXUNpkjO0O0OaHxp0wJ.WRQWwM4g3Q6gQz6gQz6gQz6gQz6gQz6gQz6gQz6gQz6g', 
    1, 1, NOW(), 1
) ON DUPLICATE KEY UPDATE SET 
    updated_at = NOW(), updated_by = 1;
