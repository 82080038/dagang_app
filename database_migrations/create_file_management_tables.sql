-- File Management System Tables
-- Created for Phase 3: Advanced Features Development

-- Create files table
CREATE TABLE IF NOT EXISTS `files` (
    `id_file` int(11) NOT NULL AUTO_INCREMENT,
    `filename` varchar(255) NOT NULL COMMENT 'Generated unique filename',
    `original_name` varchar(255) NOT NULL COMMENT 'Original uploaded filename',
    `file_path` varchar(500) NOT NULL COMMENT 'Full path to file',
    `file_size` bigint(20) NOT NULL DEFAULT 0 COMMENT 'File size in bytes',
    `mime_type` varchar(100) NOT NULL COMMENT 'MIME type of file',
    `file_extension` varchar(10) NOT NULL COMMENT 'File extension',
    `file_category` varchar(50) NOT NULL DEFAULT 'general' COMMENT 'File category',
    `description` text COMMENT 'File description',
    `tags` varchar(500) COMMENT 'Comma-separated tags',
    `uploaded_by` int(11) DEFAULT NULL COMMENT 'User who uploaded the file',
    `company_id` int(11) DEFAULT NULL COMMENT 'Company ID (if applicable)',
    `branch_id` int(11) DEFAULT NULL COMMENT 'Branch ID (if applicable)',
    `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether file is public',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether file is active',
    `download_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of downloads',
    `last_accessed` datetime DEFAULT NULL COMMENT 'Last access timestamp',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_file`),
    KEY `idx_files_uploaded_by` (`uploaded_by`),
    KEY `idx_files_company_id` (`company_id`),
    KEY `idx_files_branch_id` (`branch_id`),
    KEY `idx_files_category` (`file_category`),
    KEY `idx_files_mime_type` (`mime_type`),
    KEY `idx_files_is_active` (`is_active`),
    KEY `idx_files_created_at` (`created_at`),
    KEY `idx_files_filename` (`filename`),
    CONSTRAINT `fk_files_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
    CONSTRAINT `fk_files_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
    CONSTRAINT `fk_files_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File management table';

-- Create file_versions table for processed files (thumbnails, etc.)
CREATE TABLE IF NOT EXISTS `file_versions` (
    `id_version` int(11) NOT NULL AUTO_INCREMENT,
    `file_id` int(11) NOT NULL COMMENT 'Reference to original file',
    `file_type` varchar(50) NOT NULL COMMENT 'Type of version (thumbnail, medium, large, etc.)',
    `file_path` varchar(500) NOT NULL COMMENT 'Path to version file',
    `file_size` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Version file size in bytes',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_version`),
    KEY `idx_file_versions_file_id` (`file_id`),
    KEY `idx_file_versions_type` (`file_type`),
    CONSTRAINT `fk_file_versions_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id_file`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File versions for thumbnails and processed files';

-- Create file_shares table for file sharing
CREATE TABLE IF NOT EXISTS `file_shares` (
    `id_share` int(11) NOT NULL AUTO_INCREMENT,
    `file_id` int(11) NOT NULL COMMENT 'Shared file',
    `shared_by` int(11) NOT NULL COMMENT 'User who shared the file',
    `shared_with` int(11) DEFAULT NULL COMMENT 'User who received access (null for public)',
    `share_type` enum('link','user','company','branch','public') NOT NULL DEFAULT 'link' COMMENT 'Type of sharing',
    `share_token` varchar(100) DEFAULT NULL COMMENT 'Unique share token for link sharing',
    `expires_at` datetime DEFAULT NULL COMMENT 'Share expiration date',
    `can_download` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether download is allowed',
    `can_edit` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether editing is allowed',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether share is active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_share`),
    KEY `idx_file_shares_file_id` (`file_id`),
    KEY `idx_file_shares_shared_by` (`shared_by`),
    KEY `idx_file_shares_shared_with` (`shared_with`),
    KEY `idx_file_shares_token` (`share_token`),
    KEY `idx_file_shares_expires` (`expires_at`),
    CONSTRAINT `fk_file_shares_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id_file`) ON DELETE CASCADE,
    CONSTRAINT `fk_file_shares_shared_by` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
    CONSTRAINT `fk_file_shares_shared_with` FOREIGN KEY (`shared_with`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File sharing table';

-- Create file_access_log table for tracking file access
CREATE TABLE IF NOT EXISTS `file_access_log` (
    `id_log` int(11) NOT NULL AUTO_INCREMENT,
    `file_id` int(11) NOT NULL COMMENT 'Accessed file',
    `user_id` int(11) DEFAULT NULL COMMENT 'User who accessed (null for anonymous)',
    `access_type` enum('view','download','edit','share','delete') NOT NULL DEFAULT 'view' COMMENT 'Type of access',
    `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of access',
    `user_agent` text COMMENT 'User agent string',
    `share_token` varchar(100) DEFAULT NULL COMMENT 'Share token if accessed via share',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_log`),
    KEY `idx_file_access_log_file_id` (`file_id`),
    KEY `idx_file_access_log_user_id` (`user_id`),
    KEY `idx_file_access_log_access_type` (`access_type`),
    KEY `idx_file_access_log_created_at` (`created_at`),
    KEY `idx_file_access_log_share_token` (`share_token`),
    CONSTRAINT `fk_file_access_log_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id_file`) ON DELETE CASCADE,
    CONSTRAINT `fk_file_access_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File access log table';

-- Create file_categories table for predefined categories
CREATE TABLE IF NOT EXISTS `file_categories` (
    `id_category` int(11) NOT NULL AUTO_INCREMENT,
    `category_name` varchar(50) NOT NULL COMMENT 'Category name',
    `category_description` text COMMENT 'Category description',
    `allowed_extensions` varchar(255) DEFAULT NULL COMMENT 'Comma-separated allowed extensions',
    `max_file_size` bigint(20) DEFAULT NULL COMMENT 'Maximum file size in bytes (null for default)',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether category is active',
    `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Sort order',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_category`),
    UNIQUE KEY `uk_file_categories_name` (`category_name`),
    KEY `idx_file_categories_active` (`is_active`),
    KEY `idx_file_categories_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File categories configuration';

-- Insert default file categories
INSERT INTO `file_categories` (`category_name`, `category_description`, `allowed_extensions`, `max_file_size`, `sort_order`) VALUES
('general', 'General files and documents', 'pdf,doc,docx,txt,csv', 10485760, 1),
('images', 'Image files and graphics', 'jpg,jpeg,png,gif', 5242880, 2),
('documents', 'Business documents and reports', 'pdf,doc,doc,xls,xlsx,ppt,pptx', 10485760, 3),
('media', 'Audio and video files', 'mp3,mp4,avi,mov,wmv', 52428800, 4),
('archives', 'Compressed archives', 'zip,rar,7z,tar,gz', 52428800, 5),
('templates', 'Document templates', 'doc,docx,xls,xlsx,ppt,pptx,pdf', 10485760, 6),
('exports', 'System export files', 'csv,xlsx,json,xml', 20971520, 7),
('backups', 'System backup files', 'zip,sql,gz', 104857600, 8);

-- Create file_settings table for file management settings
CREATE TABLE IF NOT EXISTS `file_settings` (
    `id_setting` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL COMMENT 'Setting key',
    `setting_value` text COMMENT 'Setting value',
    `setting_description` text COMMENT 'Setting description',
    `setting_type` enum('string','integer','boolean','json') NOT NULL DEFAULT 'string' COMMENT 'Setting data type',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether setting is active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_setting`),
    UNIQUE KEY `uk_file_settings_key` (`setting_key`),
    KEY `idx_file_settings_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File management system settings';

-- Insert default file settings
INSERT INTO `file_settings` (`setting_key`, `setting_value`, `setting_description`, `setting_type`) VALUES
('max_file_size', '10485760', 'Maximum file size in bytes (10MB default)', 'integer'),
('max_image_size', '5242880', 'Maximum image file size in bytes (5MB default)', 'integer'),
('allowed_extensions', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,csv,zip,rar', 'Comma-separated list of allowed extensions', 'string'),
('enable_sharing', '1', 'Enable file sharing functionality', 'boolean'),
('enable_public_uploads', '0', 'Allow public file uploads', 'boolean'),
('auto_cleanup_days', '365', 'Automatically delete files older than X days', 'integer'),
('enable_version_control', '1', 'Enable file version control', 'boolean'),
('thumbnail_sizes', '{"thumbnail":{"width":150,"height":150},"medium":{"width":800,"height":600},"large":{"width":1200,"height":900}}', 'Thumbnail sizes configuration', 'json'),
('enable_access_logging', '1', 'Enable file access logging', 'boolean'),
('max_concurrent_uploads', '5', 'Maximum concurrent uploads per user', 'integer');

-- Create indexes for better performance
CREATE INDEX `idx_files_search` ON `files` (`original_name`, `description`, `tags`);
CREATE INDEX `idx_files_composite` ON `files` (`is_active`, `company_id`, `branch_id`);
CREATE INDEX `idx_file_access_log_composite` ON `file_access_log` (`file_id`, `created_at`);
CREATE INDEX `idx_file_shares_composite` ON `file_shares` (`file_id`, `is_active`, `expires_at`);

-- Create view for file statistics
CREATE OR REPLACE VIEW `file_statistics_view` AS
SELECT 
    f.file_category,
    COUNT(*) as total_files,
    SUM(f.file_size) as total_size,
    AVG(f.file_size) as avg_size,
    MIN(f.file_size) as min_size,
    MAX(f.file_size) as max_size,
    SUM(f.download_count) as total_downloads,
    COUNT(DISTINCT f.uploaded_by) as unique_uploaders,
    DATE(f.created_at) as upload_date
FROM files f 
WHERE f.is_active = 1 
GROUP BY f.file_category, DATE(f.created_at);

-- Create view for user file statistics
CREATE OR REPLACE VIEW `user_file_statistics_view` AS
SELECT 
    u.id_user,
    u.username,
    COUNT(f.id_file) as total_files,
    SUM(f.file_size) as total_size,
    SUM(f.download_count) as total_downloads,
    COUNT(DISTINCT f.file_category) as categories_used,
    MAX(f.created_at) as last_upload
FROM users u
LEFT JOIN files f ON u.id_user = f.uploaded_by AND f.is_active = 1
GROUP BY u.id_user, u.username;

-- Create trigger to log file access
DELIMITER //
CREATE TRIGGER `log_file_download` 
AFTER UPDATE ON `files`
FOR EACH ROW
BEGIN
    IF NEW.download_count > OLD.download_count THEN
        INSERT INTO `file_access_log` (`file_id`, `access_type`, `ip_address`, `user_agent`)
        VALUES (NEW.id_file, 'download', NULL, NULL);
    END IF;
END//
DELIMITER ;

-- Create stored procedure for file cleanup
DELIMITER //
CREATE PROCEDURE `cleanup_old_files`(IN days_old INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE file_id_var INT;
    DECLARE file_path_var VARCHAR(500);
    
    DECLARE file_cursor CURSOR FOR 
        SELECT id_file, file_path 
        FROM files 
        WHERE is_active = 1 
        AND created_at < DATE_SUB(NOW(), INTERVAL days_old DAY);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN file_cursor;
    
    read_loop: LOOP
        FETCH file_cursor INTO file_id_var, file_path_var;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Soft delete the file record
        UPDATE files SET is_active = 0 WHERE id_file = file_id_var;
        
        -- Log the cleanup
        INSERT INTO file_access_log (file_id, access_type, user_agent)
        VALUES (file_id_var, 'delete', 'SYSTEM_CLEANUP');
        
    END LOOP;
    
    CLOSE file_cursor;
END//
DELIMITER ;

-- Create function to format file size
DELIMITER //
CREATE FUNCTION `format_file_size`(bytes BIGINT) 
RETURNS VARCHAR(20) DETERMINISTIC
BEGIN
    DECLARE units VARCHAR(50) DEFAULT 'B,KB,MB,GB,TB';
    DECLARE unit_index INT DEFAULT 0;
    DECLARE formatted_size VARCHAR(20);
    
    WHILE bytes >= 1024 AND unit_index < 4 DO
        SET bytes = bytes / 1024;
        SET unit_index = unit_index + 1;
    END WHILE;
    
    SET formatted_size = CONCAT(ROUND(bytes, 2), ' ', SUBSTRING_INDEX(units, ',', unit_index + 1));
    
    RETURN formatted_size;
END//
DELIMITER ;

-- Add file management to user permissions (if permissions table exists)
ALTER TABLE `user_permissions` 
ADD COLUMN `can_upload_files` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Can upload files',
ADD COLUMN `can_delete_files` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Can delete files',
ADD COLUMN `can_share_files` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Can share files',
ADD COLUMN `can_manage_public_files` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Can manage public files';

-- Update existing users with default file permissions
UPDATE `user_permissions` SET 
    `can_upload_files` = 1,
    `can_delete_files` = 1,
    `can_share_files` = 1,
    `can_manage_public_files` = 0
WHERE 1=1;

-- Set admin permissions for file management
UPDATE `user_permissions` SET 
    `can_manage_public_files` = 1
WHERE `user_role` IN ('admin', 'super_admin');

-- Create directory structure for file uploads (this would be done in PHP)
-- The following directories should be created:
-- uploads/files/general/
-- uploads/files/images/
-- uploads/files/documents/
-- uploads/files/media/
-- uploads/files/archives/
-- uploads/files/templates/
-- uploads/files/exports/
-- uploads/files/backups/

-- Each category directory should have year/month subdirectories

-- Add file management to audit log categories
INSERT INTO `audit_log_categories` (`category_name`, `description`, `color`) VALUES
('FILE_UPLOAD', 'File upload operations', '#28a745'),
('FILE_DOWNLOAD', 'File download operations', '#17a2b8'),
('FILE_DELETE', 'File deletion operations', '#dc3545'),
('FILE_SHARE', 'File sharing operations', '#ffc107'),
('FILE_UPDATE', 'File update operations', '#6f42c1')
ON DUPLICATE KEY UPDATE 
    `description` = VALUES(`description`),
    `color` = VALUES(`color`);

-- Create file management audit log templates
INSERT INTO `audit_log_templates` (`category_id`, `action`, `template`, `description`) VALUES
((SELECT id_category FROM audit_log_categories WHERE category_name = 'FILE_UPLOAD'), 'UPLOAD', 'User {user_name} uploaded file "{file_name}" ({file_size})', 'File uploaded'),
((SELECT id_category FROM audit_log_categories WHERE category_name = 'FILE_DOWNLOAD'), 'DOWNLOAD', 'User {user_name} downloaded file "{file_name}"', 'File downloaded'),
((SELECT id_category FROM audit_log_categories WHERE category_name = 'FILE_DELETE'), 'DELETE', 'User {user_name} deleted file "{file_name}"', 'File deleted'),
((SELECT id_category FROM audit_log_categories WHERE category_name = 'FILE_SHARE'), 'SHARE', 'User {user_name} shared file "{file_name}" with {share_type}', 'File shared'),
((SELECT id_category FROM audit_log_categories WHERE category_name = 'FILE_UPDATE'), 'UPDATE', 'User {user_name} updated file "{file_name}"', 'File updated')
ON DUPLICATE KEY UPDATE 
    `template` = VALUES(`template`),
    `description` = VALUES(`description`);

-- Final verification query
SELECT 'File management tables created successfully' as status;
