-- =====================================================
-- USER MANAGEMENT ENHANCEMENTS - COMPREHENSIVE ROLE-BASED ACCESS
-- Enhanced user management with permissions, roles, and activity tracking
-- =====================================================

USE perdagangan_system;

-- 1. User Roles Table (Enhanced role management)
CREATE TABLE IF NOT EXISTS user_roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE,
    role_code VARCHAR(50) NOT NULL UNIQUE,
    role_description TEXT,
    role_level INT NOT NULL DEFAULT 0, -- Lower number = higher privilege
    is_system_role BOOLEAN DEFAULT FALSE, -- System roles cannot be deleted
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role_code (role_code),
    INDEX idx_role_level (role_level),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Permissions Table (Granular permissions)
CREATE TABLE IF NOT EXISTS permissions (
    id_permission INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE,
    permission_code VARCHAR(100) NOT NULL UNIQUE,
    permission_group VARCHAR(50) NOT NULL,
    description TEXT,
    is_system_permission BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_permission_code (permission_code),
    INDEX idx_permission_group (permission_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Role Permissions Mapping Table
CREATE TABLE IF NOT EXISTS role_permissions (
    id_role_permission INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted_by INT,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES user_roles(id_role) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id_permission) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES members(id_member),
    
    UNIQUE KEY uk_role_permission (role_id, permission_id),
    INDEX idx_role (role_id),
    INDEX idx_permission (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. User Role Assignments Table
CREATE TABLE IF NOT EXISTS user_role_assignments (
    id_assignment INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL, -- For temporary role assignments
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (user_id) REFERENCES members(id_member) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES user_roles(id_role) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES members(id_member),
    
    UNIQUE KEY uk_user_role_active (user_id, role_id, is_active),
    INDEX idx_user (user_id),
    INDEX idx_role (role_id),
    INDEX idx_active (is_active),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. User Activity Log Table (Enhanced audit trail)
CREATE TABLE IF NOT EXISTS user_activity_log (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'logout', 'create', 'update', 'delete', 'view', 'export', 'import', 'print', 'download', 'upload', 'approve', 'reject', 'other') NOT NULL,
    activity_description TEXT NOT NULL,
    module_name VARCHAR(100),
    action_details JSON, -- Store additional details like record IDs, old/new values
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES members(id_member) ON DELETE CASCADE,
    
    INDEX idx_user (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_module (module_name),
    INDEX idx_created_at (created_at),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. User Sessions Table (Enhanced session management)
CREATE TABLE IF NOT EXISTS user_sessions (
    id_session INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES members(id_member) ON DELETE CASCADE,
    
    INDEX idx_session_id (session_id),
    INDEX idx_user (user_id),
    INDEX idx_active (is_active),
    INDEX idx_expires (expires_at),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. User Preferences Table
CREATE TABLE IF NOT EXISTS user_preferences (
    id_preference INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preference_key VARCHAR(100) NOT NULL,
    preference_value TEXT,
    preference_type ENUM('string', 'boolean', 'integer', 'json') DEFAULT 'string',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES members(id_member) ON DELETE CASCADE,
    
    UNIQUE KEY uk_user_preference (user_id, preference_key),
    INDEX idx_user (user_id),
    INDEX idx_preference_key (preference_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. User Import/Export Templates Table
CREATE TABLE IF NOT EXISTS user_import_export_templates (
    id_template INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(200) NOT NULL,
    template_type ENUM('import', 'export') NOT NULL,
    template_fields JSON NOT NULL, -- Field definitions and mappings
    created_by INT,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES members(id_member),
    
    INDEX idx_template_type (template_type),
    INDEX idx_created_by (created_by),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system roles
INSERT INTO user_roles (role_name, role_code, role_description, role_level, is_system_role) VALUES
('Super Administrator', 'SUPER_ADMIN', 'Full system access with all privileges', 1, TRUE),
('Administrator', 'ADMIN', 'System administration with limited privileges', 2, TRUE),
('Company Owner', 'COMPANY_OWNER', 'Owns and manages entire company', 10, FALSE),
('Branch Owner', 'BRANCH_OWNER', 'Owns and manages specific branch', 11, FALSE),
('Director', 'DIRECTOR', 'Company director with multi-branch access', 12, FALSE),
('Manager', 'MANAGER', 'Branch manager with operational control', 13, FALSE),
('Supervisor', 'SUPERVISOR', 'Department supervisor with limited access', 14, FALSE),
('Cashier', 'CASHIER', 'Point of sale and transaction operations', 15, FALSE),
('Staff', 'STAFF', 'General staff with basic operations', 16, FALSE),
('Security', 'SECURITY', 'Security and monitoring only', 17, FALSE),
('Viewer', 'VIEWER', 'Read-only access to assigned areas', 18, FALSE),
('Customer', 'CUSTOMER', 'External customer access', 20, FALSE)
ON DUPLICATE KEY UPDATE role_name = VALUES(role_name), role_description = VALUES(role_description);

-- Insert default permissions
INSERT INTO permissions (permission_name, permission_code, permission_group, description, is_system_permission) VALUES
-- Company Management
('View Companies', 'companies.view', 'companies', 'View company information', TRUE),
('Create Companies', 'companies.create', 'companies', 'Create new companies', TRUE),
('Update Companies', 'companies.update', 'companies', 'Update company information', TRUE),
('Delete Companies', 'companies.delete', 'companies', 'Delete companies', TRUE),
('Manage All Companies', 'companies.view_all', 'companies', 'View all companies regardless of ownership', TRUE),

-- Branch Management
('View Branches', 'branches.view', 'branches', 'View branch information', TRUE),
('Create Branches', 'branches.create', 'branches', 'Create new branches', TRUE),
('Update Branches', 'branches.update', 'branches', 'Update branch information', TRUE),
('Delete Branches', 'branches.delete', 'branches', 'Delete branches', TRUE),
('Manage All Branches', 'branches.view_all', 'branches', 'View all branches regardless of ownership', TRUE),
('View Own Branch', 'branches.view_own', 'branches', 'View own branch only', TRUE),

-- User Management
('View Users', 'users.view', 'users', 'View user information', TRUE),
('Create Users', 'users.create', 'users', 'Create new users', TRUE),
('Update Users', 'users.update', 'users', 'Update user information', TRUE),
('Delete Users', 'users.delete', 'users', 'Delete users', TRUE),
('Manage Roles', 'users.manage_roles', 'users', 'Assign and manage user roles', TRUE),
('Manage Permissions', 'users.manage_permissions', 'users', 'Manage role permissions', TRUE),
('View All Users', 'users.view_all', 'users', 'View all users regardless of branch', TRUE),
('Import Users', 'users.import', 'users', 'Import users from file', TRUE),
('Export Users', 'users.export', 'users', 'Export users to file', TRUE),

-- Product Management
('View Products', 'products.view', 'products', 'View product information', TRUE),
('Create Products', 'products.create', 'products', 'Create new products', TRUE),
('Update Products', 'products.update', 'products', 'Update product information', TRUE),
('Delete Products', 'products.delete', 'products', 'Delete products', TRUE),
('Manage Inventory', 'products.manage_inventory', 'products', 'Manage product inventory', TRUE),

-- Transaction Management
('View Transactions', 'transactions.view', 'transactions', 'View transaction information', TRUE),
('Create Transactions', 'transactions.create', 'transactions', 'Create new transactions', TRUE),
('Update Transactions', 'transactions.update', 'transactions', 'Update transaction information', TRUE),
('Delete Transactions', 'transactions.delete', 'transactions', 'Delete transactions', TRUE),
('View All Transactions', 'transactions.view_all', 'transactions', 'View all transactions regardless of branch', TRUE),
('View Own Transactions', 'transactions.view_own', 'transactions', 'View own branch transactions only', TRUE),

-- Customer Management
('View Customers', 'customers.view', 'customers', 'View customer information', TRUE),
('Create Customers', 'customers.create', 'customers', 'Create new customers', TRUE),
('Update Customers', 'customers.update', 'customers', 'Update customer information', TRUE),
('Delete Customers', 'customers.delete', 'customers', 'Delete customers', TRUE),
('Manage Customer Credit', 'customers.manage_credit', 'customers', 'Manage customer credit limits', TRUE),

-- Supplier Management
('View Suppliers', 'suppliers.view', 'suppliers', 'View supplier information', TRUE),
('Create Suppliers', 'suppliers.create', 'suppliers', 'Create new suppliers', TRUE),
('Update Suppliers', 'suppliers.update', 'suppliers', 'Update supplier information', TRUE),
('Delete Suppliers', 'suppliers.delete', 'suppliers', 'Delete suppliers', TRUE),
('Manage Purchase Orders', 'suppliers.manage_po', 'suppliers', 'Create and manage purchase orders', TRUE),

-- Reports
('View Basic Reports', 'reports.view_basic', 'reports', 'View basic reports', TRUE),
('View Advanced Reports', 'reports.view_advanced', 'reports', 'View advanced reports', TRUE),
('View Financial Reports', 'reports.view_financial', 'reports', 'View financial reports', TRUE),
('Export Reports', 'reports.export', 'reports', 'Export reports to file', TRUE),

-- Settings
('View Settings', 'settings.view', 'settings', 'View system settings', TRUE),
('Update Basic Settings', 'settings.update_basic', 'settings', 'Update basic settings', TRUE),
('Update Advanced Settings', 'settings.update_advanced', 'settings', 'Update advanced settings', TRUE),
('System Configuration', 'settings.system_config', 'settings', 'System-level configuration', TRUE),

-- System
('View Audit Logs', 'system.view_audit', 'system', 'View audit logs', TRUE),
('Manage System', 'system.manage', 'system', 'System administration', TRUE),
('View System Info', 'system.view_info', 'system', 'View system information', TRUE)
ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name), description = VALUES(description);

-- Assign permissions to roles (Basic assignments - can be customized)
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id_role, p.id_permission 
FROM user_roles r, permissions p 
WHERE r.role_code IN ('SUPER_ADMIN', 'ADMIN') 
AND p.is_system_permission = TRUE
ON DUPLICATE KEY UPDATE granted_at = CURRENT_TIMESTAMP;

-- Assign basic permissions to staff roles
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id_role, p.id_permission 
FROM user_roles r, permissions p 
WHERE r.role_code IN ('MANAGER', 'SUPERVISOR', 'CASHIER', 'STAFF') 
AND p.permission_group IN ('transactions', 'products', 'customers', 'suppliers')
AND p.permission_code LIKE '%view%'
ON DUPLICATE KEY UPDATE granted_at = CURRENT_TIMESTAMP;

-- Create views for user management

-- View: User Roles with Permissions
CREATE OR REPLACE VIEW v_user_roles_with_permissions AS
SELECT 
    ur.id_role,
    ur.role_name,
    ur.role_code,
    ur.role_description,
    ur.role_level,
    ur.is_active,
    COUNT(rp.permission_id) as permission_count,
    GROUP_CONCAT(p.permission_code ORDER BY p.permission_code) as permissions,
    GROUP_CONCAT(p.permission_name ORDER BY p.permission_code) as permission_names
FROM user_roles ur
LEFT JOIN role_permissions rp ON ur.id_role = rp.id_role
LEFT JOIN permissions p ON rp.permission_id = p.id_permission
WHERE ur.is_active = 1
GROUP BY ur.id_role, ur.role_name, ur.role_code, ur.role_description, ur.role_level, ur.is_active;

-- View: User Assignments with Details
CREATE OR REPLACE VIEW v_user_assignments AS
SELECT 
    m.id_member as user_id,
    m.member_code,
    m.member_name as user_name,
    m.email,
    m.phone,
    m.position,
    m.is_active as user_active,
    ur.id_role,
    ur.role_name,
    ur.role_code,
    ur.role_level,
    b.branch_name,
    c.company_name,
    ura.assigned_at,
    ura.expires_at,
    ura.is_active as assignment_active,
    CASE 
        WHEN ura.expires_at IS NOT NULL AND ura.expires_at < CURRENT_TIMESTAMP THEN 'expired'
        WHEN ura.is_active = 0 THEN 'inactive'
        ELSE 'active'
    END as assignment_status
FROM members m
LEFT JOIN user_role_assignments ura ON m.id_member = ura.user_id AND ura.is_active = 1
LEFT JOIN user_roles ur ON ura.role_id = ur.id_role
LEFT JOIN branches b ON m.branch_id = b.id_branch
LEFT JOIN companies c ON b.company_id = c.id_company
WHERE m.is_active = 1;

-- View: User Activity Summary
CREATE OR REPLACE VIEW v_user_activity_summary AS
SELECT 
    ual.user_id,
    m.member_name,
    m.member_code,
    COUNT(*) as total_activities,
    COUNT(CASE WHEN ual.activity_type = 'login' THEN 1 END) as login_count,
    COUNT(CASE WHEN ual.activity_type = 'logout' THEN 1 END) as logout_count,
    COUNT(CASE WHEN ual.activity_type IN ('create', 'update', 'delete') THEN 1 END) as modification_count,
    MAX(ual.created_at) as last_activity,
    COUNT(CASE WHEN ual.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) THEN 1 END) as activities_last_7_days,
    COUNT(CASE WHEN ual.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) THEN 1 END) as activities_last_30_days
FROM user_activity_log ual
LEFT JOIN members m ON ual.user_id = m.id_member
GROUP BY ual.user_id, m.member_name, m.member_code;

-- Create triggers for automatic activity logging

DELIMITER //

-- Trigger for user activity logging on member table changes
CREATE TRIGGER log_member_activity_insert
AFTER INSERT ON members
FOR EACH ROW
BEGIN
    INSERT INTO user_activity_log (user_id, activity_type, activity_description, module_name, action_details, ip_address, user_agent, session_id)
    VALUES (
        NEW.id_member,
        'create',
        CONCAT('User account created: ', NEW.member_name, ' (', NEW.member_code, ')'),
        'user_management',
        JSON_OBJECT(
            'member_id', NEW.id_member,
            'member_code', NEW.member_code,
            'member_name', NEW.member_name,
            'email', NEW.email,
            'position', NEW.position,
            'branch_id', NEW.branch_id
        ),
        NULL, NULL, NULL, NULL
    );
END//

CREATE TRIGGER log_member_activity_update
AFTER UPDATE ON members
FOR EACH ROW
BEGIN
    IF NEW.member_name != OLD.member_name OR NEW.email != OLD.email OR NEW.position != OLD.position OR NEW.is_active != OLD.is_active THEN
        INSERT INTO user_activity_log (user_id, activity_type, activity_description, module_name, action_details, ip_address, user_agent, session_id)
        VALUES (
            NEW.id_member,
            'update',
            CONCAT('User account updated: ', NEW.member_name, ' (', NEW.member_code, ')'),
            'user_management',
            JSON_OBJECT(
                'member_id', NEW.id_member,
                'member_code', NEW.member_code,
                'old_values', JSON_OBJECT(
                    'member_name', OLD.member_name,
                    'email', OLD.email,
                    'position', OLD.position,
                    'is_active', OLD.is_active
                ),
                'new_values', JSON_OBJECT(
                    'member_name', NEW.member_name,
                    'email', NEW.email,
                    'position', NEW.position,
                    'is_active', NEW.is_active
                )
            ),
            NULL, NULL, NULL, NULL
        );
    END IF;
END//

DELIMITER ;

-- Create stored procedures for user management

DELIMITER //

-- Procedure: Get user permissions
CREATE PROCEDURE GetUserPermissions(IN userId INT)
BEGIN
    SELECT 
        p.permission_code,
        p.permission_name,
        p.permission_group,
        p.description
    FROM permissions p
    JOIN role_permissions rp ON p.id_permission = rp.permission_id
    JOIN user_role_assignments ura ON rp.role_id = ura.role_id
    WHERE ura.user_id = userId 
    AND ura.is_active = 1
    AND ura.expires_at IS NULL OR ura.expires_at > CURRENT_TIMESTAMP
    ORDER BY p.permission_group, p.permission_name;
END//

-- Procedure: Check user permission
CREATE PROCEDURE CheckUserPermission(IN userId INT, IN permissionCode VARCHAR(100))
BEGIN
    SELECT 
        COUNT(*) as has_permission
    FROM permissions p
    JOIN role_permissions rp ON p.id_permission = rp.permission_id
    JOIN user_role_assignments ura ON rp.role_id = ura.role_id
    WHERE ura.user_id = userId 
    AND p.permission_code = permissionCode
    AND ura.is_active = 1
    AND (ura.expires_at IS NULL OR ura.expires_at > CURRENT_TIMESTAMP);
END//

-- Procedure: Get user activity report
CREATE PROCEDURE GetUserActivityReport(IN userId INT, IN startDate DATE, IN endDate DATE)
BEGIN
    SELECT 
        DATE(created_at) as activity_date,
        activity_type,
        activity_description,
        module_name,
        ip_address,
        user_agent
    FROM user_activity_log
    WHERE user_id = userId
    AND created_at BETWEEN startDate AND endDate
    ORDER BY created_at DESC;
END//

DELIMITER ;

-- Add indexes for performance optimization
CREATE INDEX idx_user_activity_composite ON user_activity_log(user_id, activity_type, created_at);
CREATE INDEX idx_role_permissions_composite ON role_permissions(role_id, permission_id);
CREATE INDEX idx_user_assignments_composite ON user_role_assignments(user_id, role_id, is_active);

-- Add full-text search indexes
ALTER TABLE user_activity_log ADD FULLTEXT(activity_description, module_name);
ALTER TABLE members ADD FULLTEXT(member_name, member_code, email);

-- Update existing members table to add foreign key constraints if they don't exist
ALTER TABLE members 
ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER updated_at,
ADD COLUMN IF NOT EXISTS updated_by INT NULL AFTER created_by,
ADD INDEX idx_created_by (created_by),
ADD INDEX idx_updated_by (updated_by);

-- Create default user preferences
INSERT INTO user_preferences (user_id, preference_key, preference_value, preference_type)
SELECT 
    id_member, 
    'theme', 
    'default', 
    'string'
FROM members 
WHERE id_member NOT IN (
    SELECT DISTINCT user_id 
    FROM user_preferences 
    WHERE preference_key = 'theme'
);

INSERT INTO user_preferences (user_id, preference_key, preference_value, preference_type)
SELECT 
    id_member, 
    'language', 
    'id', 
    'string'
FROM members 
WHERE id_member NOT IN (
    SELECT DISTINCT user_id 
    FROM user_preferences 
    WHERE preference_key = 'language'
);

INSERT INTO user_preferences (user_id, preference_key, preference_value, preference_type)
SELECT 
    id_member, 
    'notifications_enabled', 
    '1', 
    'boolean'
FROM members 
WHERE id_member NOT IN (
    SELECT DISTINCT user_id 
    FROM user_preferences 
    WHERE preference_key = 'notifications_enabled'
);

-- Create default import/export templates
INSERT INTO user_import_export_templates (template_name, template_type, template_fields, is_default, created_by)
VALUES 
('User Import Template', 'import', 
'{"member_code": {"required": true, "label": "Member Code"}, "member_name": {"required": true, "label": "Full Name"}, "email": {"required": false, "label": "Email"}, "phone": {"required": false, "label": "Phone"}, "position": {"required": false, "label": "Position"}, "branch_id": {"required": true, "label": "Branch ID"}}', 
TRUE, 1),
('User Export Template', 'export',
'{"member_code": {"label": "Member Code"}, "member_name": {"label": "Full Name"}, "email": {"label": "Email"}, "phone": {"label": "Phone"}, "position": {"label": "Position"}, "branch_name": {"label": "Branch"}, "role_name": {"label": "Role"}, "is_active": {"label": "Active"}}',
TRUE, 1);

-- Summary
SELECT 
    'User Management Enhancements' as feature,
    COUNT(DISTINCT table_name) as tables_created
FROM information_schema.tables 
WHERE table_schema = 'perdagangan_system' 
AND table_name IN (
    'user_roles', 'permissions', 'role_permissions', 'user_role_assignments', 
    'user_activity_log', 'user_sessions', 'user_preferences', 'user_import_export_templates'
);
