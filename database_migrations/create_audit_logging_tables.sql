-- Database Schema for Enhanced Audit Logging System
-- Part of Phase 2: System Administration Implementation

-- Enhanced Audit Logs Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    user_id INT NULL,
    company_id INT NULL,
    branch_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    old_values JSON,
    new_values JSON,
    session_id VARCHAR(255),
    request_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_type (activity_type),
    idx_entity_type (entity_type),
    idx_entity_id (entity_id),
    idx_user_id (user_id),
    idx_company_id (company_id),
    idx_branch_id (branch_id),
    idx_created_at (created_at),
    idx_session_id (session_id),
    INDEX idx_request_id (request_id),
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE SET NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id_company) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE SET NULL
);

-- Audit Log Categories Table
CREATE TABLE IF NOT EXISTS audit_log_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category_name (category_name),
    INDEX idx_is_active (is_active)
);

-- Audit Log Templates Table
CREATE TABLE IF NOT EXISTS audit_log_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) UNIQUE NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    description_template TEXT,
    category_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_template_name (template_name),
    INDEX idx_activity_type (activity_type),
    INDEX idx_category_id (category_id),
    FOREIGN KEY (category_id) REFERENCES audit_log_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Audit Log Retention Policies Table
CREATE TABLE IF NOT EXISTS audit_log_retention (
    id INT PRIMARY KEY AUTO_INCREMENT,
    policy_name VARCHAR(100) UNIQUE NOT NULL,
    category_id INT,
    retention_days INT NOT NULL DEFAULT 365,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_policy_name (policy_name),
    INDEX idx_category_id (category_id),
    INDEX idx_is_active (is_active),
    FOREIGN KEY (category_id) REFERENCES audit_log_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Audit Log Export History Table
CREATE TABLE IF NOT EXISTS audit_log_exports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    export_type ENUM('csv', 'excel', 'pdf') NOT NULL,
    filters JSON NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT DEFAULT 0,
    record_count INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    error_message TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_export_type (export_type),
    idx_status (status),
    idx_created_at (created_at),
    idx_created_by (created_by),
    FOREIGN KEY (created_by) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Insert default audit log categories
INSERT INTO audit_log_categories (category_name, description, is_active) VALUES
('authentication', 'Authentication and authorization events', TRUE),
('user_management', 'User account management activities', TRUE),
('data_access', 'Data access and modification', TRUE),
('system_configuration', 'System settings and configuration changes', TRUE),
('security', 'Security-related events and incidents', TRUE),
('backup', 'Backup and restore operations', TRUE),
('reporting', 'Report generation and access', TRUE),
('api_access', 'API usage and access', TRUE),
('file_operations', 'File upload, download, and management', TRUE),
('compliance', 'Compliance and regulatory activities', TRUE);

-- Insert default audit log templates
INSERT INTO audit_log_templates (template_name, activity_type, description_template, category_id, is_active) VALUES
('user_login', 'login_success', 'User {{user_name}} logged in successfully', 1, TRUE),
('user_logout', 'logout', 'User {{user_name}} logged out', 1, TRUE),
('login_failed', 'login_failed', 'Failed login attempt for {{username}} from {{ip_address}}', 1, TRUE),
('user_created', 'user_created', 'User {{user_name}} created by {{created_by}}', 2, TRUE),
('user_updated', 'user_updated', 'User {{user_name}} updated by {{updated_by}}', 2, TRUE),
('user_deleted', 'user_deleted', 'User {{user_name}} deleted by {{deleted_by}}', 2, TRUE),
('user_status_changed', 'user_status_toggled', 'User {{user_name}} status changed to {{status}} by {{changed_by}}', 2, TRUE),
('password_changed', 'password_change', 'Password changed for user {{user_name}}', 1, TRUE),
('password_reset', 'password_reset', 'Password reset requested for user {{user_name}}', 1, TRUE),
('permission_denied', 'permission_denied', 'Access denied for {{resource}} - {{action}} by user {{user_name}}', 1, TRUE),
('settings_updated', 'settings_updated', 'System settings updated by {{user_name}}', 4, TRUE),
('feature_toggled', 'feature_updated', 'Feature {{feature_name}} {{action}} by {{user_name}}', 4, TRUE),
('backup_created', 'backup_created', 'Backup created: {{backup_file}} by {{user_name}}', 5, TRUE),
('backup_downloaded', 'backup_downloaded', 'Backup {{backup_file}} downloaded by {{user_name}}', 5, TRUE),
('backup_deleted', 'backup_deleted', 'Backup {{backup_file}} deleted by {{user_name}}', 5, TRUE),
('report_generated', 'report_generated', 'Report {{report_name}} generated by {{user_name}}', 6, TRUE),
('report_exported', 'report_exported', 'Report {{report_name}} exported to {{format}} by {{user_name}}', 6, TRUE),
('data_exported', 'data_exported', 'Data exported to {{format}} by {{user_name}}', 7, TRUE),
('file_uploaded', 'file_uploaded', 'File {{file_name}} uploaded by {{user_name}}', 8, TRUE),
('file_downloaded', 'file_downloaded', 'File {{file_name}} downloaded by {{user_name}}', 8, TRUE),
('file_deleted', 'file_deleted', 'File {{file_name}} deleted by {{user_name}}', 8, TRUE),
('api_access', 'api_access', 'API access to {{endpoint}} by {{user_name}}', 9, TRUE),
('api_error', 'api_error', 'API error in {{endpoint}}: {{error_message}}', 9, TRUE),
('security_breach', 'security_breach', 'Security breach detected: {{description}}', 1, TRUE),
('suspicious_activity', 'suspicious_activity', 'Suspicious activity detected: {{description}}', 1, TRUE),
('compliance_check', 'compliance_check', 'Compliance check: {{check_name}}', 10, TRUE);

-- Insert default retention policies
INSERT INTO audit_log_retention (policy_name, category_id, retention_days, is_active) VALUES
('authentication_logs', 1, 365, TRUE),
('user_management_logs', 2, 1825, TRUE),  -- 5 years
('data_access_logs', 3, 2555, TRUE),  -- 7 years
('system_configuration_logs', 4, 1095, TRUE), -- 3 years
('security_logs', 5, 2555, TRUE),  -- 7 years
('backup_logs', 6, 365, TRUE),
('reporting_logs', 6, 1095, TRUE),  -- 3 years
('api_access_logs', 9, 365, TRUE),
('file_operations_logs', 8, 365, TRUE),
('compliance_logs', 10, 3650, TRUE); -- 10 years

-- Create indexes for better performance
CREATE INDEX idx_audit_logs_composite ON audit_logs(activity_type, created_at);
CREATE INDEX idx_audit_logs_user_date ON audit_logs(user_id, created_at);
CREATE INDEX idx_audit_logs_entity_date ON audit_logs(entity_type, entity_id, created_at);
CREATE INDEX idx_audit_logs_company_date ON audit_logs(company_id, created_at);
CREATE INDEX idx_audit_logs_branch_date ON audit_logs(branch_id, created_at);

-- Create views for common audit queries
CREATE VIEW audit_logs_summary AS
SELECT 
    activity_type,
    entity_type,
    COUNT(*) as count,
    MAX(created_at) as last_activity,
    MIN(created_at) as first_activity
FROM audit_logs
GROUP BY activity_type, entity_type;

CREATE VIEW audit_logs_by_user AS
SELECT 
    u.full_name as user_name,
    u.username,
    COUNT(*) as activity_count,
    MAX(al.created_at) as last_activity
FROM audit_logs al
LEFT JOIN users u ON al.user_id = u.id_user
GROUP BY al.user_id, u.full_name, u.username
ORDER BY activity_count DESC;

CREATE VIEW audit_logs_by_entity AS
SELECT 
    entity_type,
    entity_id,
    COUNT(*) as activity_count,
    MAX(al.created_at) as last_activity,
    MIN(al.created_at) as first_activity
FROM audit_logs al
WHERE al.entity_id IS NOT NULL
GROUP BY entity_type, entity_id
ORDER BY activity_count DESC;

-- Create stored procedure for automatic log cleanup
DELIMITER //
CREATE PROCEDURE CleanupOldAuditLogs()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE category_id INT;
    DECLARE retention_days INT;
    DECLARE cutoff_date DATE;
    
    DECLARE category_cursor CURSOR FOR 
        SELECT id, retention_days 
        FROM audit_log_retention 
        WHERE is_active = 1;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN category_cursor;
    
    read_loop: LOOP
        FETCH category_cursor INTO category_id, retention_days;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET cutoff_date = DATE_SUB(CURDATE(), INTERVAL retention_days DAY);
        
        DELETE FROM audit_logs 
        WHERE category_id = category_id 
          AND activity_type IN (
            SELECT activity_type 
            FROM audit_log_templates 
            WHERE category_id = category_id
          )
          AND created_at < cutoff_date;
    END LOOP;
    
    CLOSE category_cursor;
    
    -- Log the cleanup activity
    INSERT INTO audit_logs (activity_type, description, entity_type, user_id, created_at)
    VALUES ('system_maintenance', 'Old audit logs cleanup completed', 'system', NULL, NOW());
    
END //
DELIMITER ;

-- Create trigger for automatic audit logging
DELIMITER //
CREATE TRIGGER before_users_update 
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    DECLARE old_values JSON;
    DECLARE new_values JSON;
    
    SET old_values = JSON_OBJECT(
        'username', OLD.username,
        'email', OLD.email,
        'full_name', OLD.full_name,
        'role_id', OLD.role_id,
        'is_active', OLD.is_active
    );
    
    SET new_values = JSON_OBJECT(
        'username', NEW.username,
        'email', NEW.email,
        'full_name', NEW.full_name,
        'role_id', NEW.role_id,
        'is_active', NEW.is_active
    );
    
    INSERT INTO audit_logs (
        activity_type, 
        description, 
        entity_type, 
        entity_id, 
        user_id, 
        company_id, 
        branch_id, 
        old_values, 
        new_values,
        created_at
    ) VALUES (
        'user_updated',
        CONCAT('User ', NEW.full_name, ' updated'),
        'user',
        NEW.id_user,
        @current_user_id,
        @current_company_id,
        @current_branch_id,
        old_values,
        new_values,
        NOW()
    );
END //
DELIMITER ;

-- Create trigger for system settings changes
DELIMITER //
CREATE TRIGGER before_system_settings_update 
BEFORE UPDATE ON system_settings
FOR EACH ROW
BEGIN
    DECLARE old_values JSON;
    DECLARE new_values JSON;
    
    SET old_values = JSON_OBJECT(
        'setting_key', OLD.setting_key,
        'setting_value', OLD.setting_value
    );
    
    SET new_values = JSON_OBJECT(
        'setting_key', NEW.setting_key,
        'setting_value', NEW.setting_value
    );
    
    INSERT INTO audit_logs (
        activity_type, 
        description, 
        entity_type, 
        entity_id, 
        user_id, 
        company_id, 
        branch_id, 
        old_values, 
        new_values,
        created_at
    ) VALUES (
        'settings_updated',
        CONCAT('Setting ', NEW.setting_key, ' changed from ', OLD.setting_value, ' to ', NEW.setting_value),
        'settings',
        NEW.id,
        @current_user_id,
        @current_company_id,
        @current_branch_id,
        old_values,
        new_values,
        NOW()
    );
END //
DELIMITER ;

-- Create function to get audit log statistics
DELIMITER //
CREATE FUNCTION GetAuditLogStatistics(
    start_date DATE,
    end_date DATE,
    company_id_param INT,
    branch_id_param INT
)
RETURNS JSON
READS SQL DATA
BEGIN
    DECLARE result JSON;
    
    SELECT JSON_OBJECT(
        'total_logs', COUNT(*),
        'unique_users', (SELECT COUNT(DISTINCT user_id) FROM audit_logs WHERE created_at BETWEEN start_date AND end_date),
        'security_events', (SELECT COUNT(*) FROM audit_logs WHERE created_at BETWEEN start_date AND end_date AND activity_type IN ('login_failed', 'login_success', 'logout', 'password_change', 'permission_denied')),
        'system_changes', (SELECT COUNT(*) FROM audit_logs WHERE created_at BETWEEN start_date AND end_date AND activity_type IN ('settings_updated', 'feature_updated', 'system_config')),
        'data_access', (SELECT COUNT(*) FROM audit_logs WHERE created_at BETWEEN start_date AND end_date AND activity_type IN ('user_created', 'user_updated', 'user_deleted', 'data_exported'))
    )
    INTO result
    FROM audit_logs
    WHERE created_at BETWEEN start_date AND end_date
      AND (company_id_param IS NULL OR company_id = company_id_param)
      AND (branch_id_param IS NULL OR branch_id = branch_id_param);
    
    RETURN result;
END //
DELIMITER ;
