<?php
// Simple database migration script for system settings
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'perdagangan_system';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting system settings migration...\n";
    
    // 1. Create system_settings table
    echo "Creating system_settings table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS system_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(255) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
        description TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_by INT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_setting_key (setting_key),
        INDEX idx_setting_group (setting_group),
        INDEX idx_is_active (is_active)
    )";
    $pdo->exec($sql);
    echo "✓ system_settings table created\n";
    
    // 2. Create backup_history table
    echo "Creating backup_history table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS backup_history (
        id INT PRIMARY KEY AUTO_INCREMENT,
        backup_type ENUM('full', 'database', 'files', 'settings') NOT NULL DEFAULT 'full',
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size BIGINT DEFAULT 0,
        status ENUM('pending', 'in_progress', 'completed', 'failed') NOT NULL DEFAULT 'completed',
        error_message TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        INDEX idx_backup_type (backup_type),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    )";
    $pdo->exec($sql);
    echo "✓ backup_history table created\n";
    
    // 3. Create email_templates table
    echo "Creating email_templates table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS email_templates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        template_name VARCHAR(100) UNIQUE NOT NULL,
        subject VARCHAR(255) NOT NULL,
        html_content TEXT,
        text_content TEXT,
        variables JSON,
        is_active BOOLEAN DEFAULT TRUE,
        created_by INT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_template_name (template_name),
        INDEX idx_is_active (is_active)
    )";
    $pdo->exec($sql);
    echo "✓ email_templates table created\n";
    
    // 4. Create feature_toggles table
    echo "Creating feature_toggles table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS feature_toggles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        feature_name VARCHAR(100) UNIQUE NOT NULL,
        feature_group VARCHAR(50) NOT NULL DEFAULT 'general',
        is_enabled BOOLEAN DEFAULT FALSE,
        description TEXT,
        requires_restart BOOLEAN DEFAULT FALSE,
        created_by INT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_feature_name (feature_name),
        INDEX idx_feature_group (feature_group),
        INDEX idx_is_enabled (is_enabled)
    )";
    $pdo->exec($sql);
    echo "✓ feature_toggles table created\n";
    
    // 5. Insert default settings
    echo "Inserting default settings...\n";
    
    // Check if settings already exist
    $checkSql = "SELECT COUNT(*) as count FROM system_settings";
    $stmt = $pdo->query($checkSql);
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // General Settings
        $settings = [
            ['app_name', 'Aplikasi Perdagangan Multi-Cabang', 'general', 'Application name'],
            ['app_version', '2.0.0', 'general', 'Application version'],
            ['timezone', 'Asia/Jakarta', 'general', 'Default timezone'],
            ['date_format', 'd-m-Y', 'general', 'Date format'],
            ['time_format', 'H:i:s', 'general', 'Time format'],
            ['currency', 'IDR', 'general', 'Default currency'],
            ['decimal_places', '2', 'general', 'Number of decimal places for currency'],
            ['company_logo', '', 'general', 'Company logo file path'],
            ['company_address', '', 'general', 'Company address'],
            ['company_phone', '', 'general', 'Company phone'],
            ['company_email', 'info@perdagangan.com', 'general', 'Company email'],
            
            // Security Settings
            ['session_timeout', '7200', 'security', 'Session timeout in seconds'],
            ['max_login_attempts', '5', 'security', 'Maximum login attempts before lockout'],
            ['lockout_duration', '900', 'security', 'Account lockout duration in seconds'],
            ['password_min_length', '6', 'security', 'Minimum password length'],
            ['password_require_uppercase', '1', 'security', 'Require uppercase in password'],
            ['password_require_lowercase', '1', 'security', 'Require lowercase in password'],
            ['password_require_numbers', '1', 'security', 'Require numbers in password'],
            ['password_require_special', '1', 'security', 'Require special characters in password'],
            ['require_password_change', '0', 'security', 'Require password change on first login'],
            ['password_expiry_days', '90', 'security', 'Password expiry in days'],
            ['enable_2fa', '0', 'security', 'Enable two-factor authentication'],
            ['enable_ip_whitelist', '0', 'security', 'Enable IP whitelist'],
            ['ip_whitelist', '[]', 'security', 'Allowed IP addresses'],
            
            // Email Settings
            ['smtp_host', '', 'email', 'SMTP server hostname'],
            ['smtp_port', '587', 'email', 'SMTP server port'],
            ['smtp_username', '', 'email', 'SMTP username'],
            ['smtp_password', '', 'email', 'SMTP password'],
            ['smtp_encryption', 'tls', 'email', 'SMTP encryption type (tls, ssl, none)'],
            ['from_email', 'noreply@perdagangan.com', 'email', 'Default from email address'],
            ['from_name', 'Perdagangan System', 'email', 'Default from name'],
            ['email_queue_enabled', '0', 'email', 'Enable email queue system'],
            ['batch_email_limit', '100', 'email', 'Maximum emails per batch'],
            
            // Backup Settings
            ['auto_backup', '0', 'backup', 'Enable automatic backup'],
            ['backup_frequency', 'daily', 'backup', 'Backup frequency (daily, weekly, monthly)'],
            ['backup_retention', '30', 'backup', 'Number of days to keep backups'],
            ['backup_path', '/backups', 'backup', 'Backup storage path'],
            ['backup_compression', '1', 'backup', 'Compress backup files'],
            ['backup_include_files', '1', 'backup', 'Include files in backup'],
            ['backup_encryption', '0', 'backup', 'Encrypt backup files'],
            ['backup_encryption_key', '', 'backup', 'Backup encryption key'],
            
            // Feature Settings
            ['enable_reports', '1', 'features', 'Enable reporting system'],
            ['enable_notifications', '1', 'features', 'Enable system notifications'],
            ['enable_backup', '1', 'features', 'Enable backup system'],
            ['enable_audit_log', '1', 'features', 'Enable audit logging'],
            ['enable_api_access', '0', 'features', 'Enable API access'],
            ['enable_maintenance_mode', '0', 'features', 'Enable maintenance mode'],
            ['enable_registration', '0', 'features', 'Enable user registration'],
            ['enable_email_verification', '0', 'features', 'Enable email verification'],
            ['enable_password_reset', '1', 'features', 'Enable password reset'],
            ['enable_multi_company', '1', 'features', 'Enable multi-company support'],
            ['enable_multi_branch', '1', 'features', 'Enable multi-branch support'],
            
            // UI Settings
            ['theme', 'dark-blue', 'ui', 'Default theme'],
            ['language', 'id', 'ui', 'Default language'],
            ['items_per_page', '25', 'ui', 'Items per page in tables'],
            ['enable_animations', '1', 'ui', 'Enable UI animations'],
            ['enable_tooltips', '1', 'ui', 'Enable tooltips'],
            ['sidebar_collapsed', '0', 'ui', 'Sidebar collapsed by default']
        ];
        
        $insertSql = "INSERT INTO system_settings (setting_key, setting_value, setting_group, description) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertSql);
        
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
        
        echo "✓ Default settings inserted\n";
    } else {
        echo "⚠ Settings already exist, skipping insertion\n";
    }
    
    // 6. Insert default email templates
    echo "Inserting default email templates...\n";
    
    $checkTemplateSql = "SELECT COUNT(*) as count FROM email_templates";
    $stmt = $pdo->query($checkTemplateSql);
    $templateCount = $stmt->fetch()['count'];
    
    if ($templateCount == 0) {
        $templates = [
            ['welcome_email', 'Welcome to Perdagangan System', 
             '<h1>Welcome {{user_name}}!</h1><p>Your account has been created successfully.</p><p>Username: {{username}}</p><p>Password: {{password}}</p><p>Login URL: {{login_url}}</p>',
             'Welcome {{user_name}}! Your account has been created successfully.\n\nUsername: {{username}}\nPassword: {{password}}\nLogin URL: {{login_url}}',
             '["user_name", "username", "password", "login_url"]'],
            
            ['password_reset', 'Password Reset Request',
             '<h1>Password Reset</h1><p>Hi {{user_name}},</p><p>Click here to reset your password: {{reset_link}}</p><p>This link will expire in {{expiry_hours}} hours.</p>',
             'Hi {{user_name}},\n\nClick here to reset your password: {{reset_link}}\n\nThis link will expire in {{expiry_hours}} hours.',
             '["user_name", "reset_link", "expiry_hours"]'],
            
            ['backup_notification', 'Backup Completed',
             '<h1>System Backup Completed</h1><p>System backup has been completed successfully.</p><p>File: {{backup_file}}</p><p>Size: {{backup_size}}</p><p>Date: {{backup_date}}</p>',
             'System backup has been completed successfully.\n\nFile: {{backup_file}}\nSize: {{backup_size}}\nDate: {{backup_date}}',
             '["backup_file", "backup_size", "backup_date"]']
        ];
        
        $insertTemplateSql = "INSERT INTO email_templates (template_name, subject, html_content, text_content, variables) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertTemplateSql);
        
        foreach ($templates as $template) {
            $stmt->execute($template);
        }
        
        echo "✓ Default email templates inserted\n";
    } else {
        echo "⚠ Email templates already exist, skipping insertion\n";
    }
    
    // 7. Insert default feature toggles
    echo "Inserting default feature toggles...\n";
    
    $checkFeatureSql = "SELECT COUNT(*) as count FROM feature_toggles";
    $stmt = $pdo->query($checkFeatureSql);
    $featureCount = $stmt->fetch()['count'];
    
    if ($featureCount == 0) {
        $features = [
            ['reports', 'business', 1, 'Enable reporting system', 0],
            ['notifications', 'system', 1, 'Enable system notifications', 0],
            ['backup', 'system', 1, 'Enable backup system', 0],
            ['audit_log', 'system', 1, 'Enable audit logging', 0],
            ['api_access', 'system', 0, 'Enable API access', 1],
            ['maintenance_mode', 'system', 0, 'Enable maintenance mode', 1],
            ['registration', 'user', 0, 'Enable user registration', 0],
            ['email_verification', 'user', 0, 'Enable email verification', 0],
            ['password_reset', 'user', 1, 'Enable password reset', 0],
            ['multi_company', 'business', 1, 'Enable multi-company support', 0],
            ['multi_branch', 'business', 1, 'Enable multi-branch support', 0],
            ['advanced_reports', 'business', 1, 'Enable advanced reporting', 0],
            ['financial_reports', 'business', 1, 'Enable financial reports', 0],
            ['export_functionality', 'business', 1, 'Enable export functionality', 0],
            ['import_functionality', 'business', 1, 'Enable import functionality', 0],
            ['real_time_updates', 'system', 1, 'Enable real-time updates', 0],
            ['mobile_app', 'system', 0, 'Enable mobile app', 1],
            ['websocket', 'system', 0, 'Enable WebSocket', 1],
            ['file_management', 'system', 1, 'Enable file management', 0]
        ];
        
        $insertFeatureSql = "INSERT INTO feature_toggles (feature_name, feature_group, is_enabled, description, requires_restart) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertFeatureSql);
        
        foreach ($features as $feature) {
            $stmt->execute($feature);
        }
        
        echo "✓ Default feature toggles inserted\n";
    } else {
        echo "⚠ Feature toggles already exist, skipping insertion\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
    // Verify tables were created
    $tables = ['system_settings', 'backup_history', 'system_logs', 'email_templates', 'feature_toggles'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
