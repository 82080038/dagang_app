-- Notification System Tables
-- Created for Phase 3: Advanced Features Development

-- Create notification_templates table
CREATE TABLE IF NOT EXISTS `notification_templates` (
    `id_template` int(11) NOT NULL AUTO_INCREMENT,
    `template_name` varchar(100) NOT NULL COMMENT 'Template name',
    `template_type` enum('email','sms','push','in_app') NOT NULL COMMENT 'Notification type',
    `subject` varchar(255) DEFAULT NULL COMMENT 'Email subject',
    `message_body` text NOT NULL COMMENT 'Message template',
    `html_body` text DEFAULT NULL COMMENT 'HTML email template',
    `variables` text DEFAULT NULL COMMENT 'Available variables in JSON',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether template is active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_template`),
    UNIQUE KEY `uk_notification_templates_name` (`template_name`, `template_type`),
    KEY `idx_notification_templates_type` (`template_type`),
    KEY `idx_notification_templates_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notification templates for different types';

-- Insert default notification templates
INSERT INTO `notification_templates` (`template_name`, `template_type`, `subject`, `message_body`, `variables`, `is_active`) VALUES
('user_welcome', 'email', 'Selamat Datang di Perdagangan System', 'Hai {user_name},\n\nSelamat datang di Perdagangan System! Akun Anda telah berhasil dibuat.\n\nEmail: {user_email}\nPeran: {user_role}\nPerusahaan: {company_name}\n\nSilakan login untuk mulai menggunakan sistem.', '{"user_name","user_email","user_role","company_name"}', 1),
('file_uploaded', 'in_app', 'File Berhasil Diunggah', '{user_name} telah mengunggah file "{file_name}" ({file_size})', '{"user_name","file_name","file_size"}', 1),
('file_downloaded', 'in_app', 'File Diunduh', '{user_name} telah mengunduh file "{file_name}"', '{"user_name","file_name"}', 1),
('transaction_created', 'in_app', 'Transaksi Baru', 'Transaksi baru telah dibuat dengan total Rp {transaction_total}', '{"transaction_total"}', 1),
('low_stock_alert', 'email', 'Peringatan Stok Menipis', 'Produk "{product_name}" stok menipis! Sisa stok: {current_stock} {unit}', '{"product_name","current_stock","unit"}', 1),
('system_maintenance', 'email', 'Pemeliharaan Sistem', 'Sistem akan dalam pemeliharaan pada {maintenance_time} hingga {end_time}.', '{"maintenance_time","end_time"}', 1);

-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
    `id_notification` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL COMMENT 'Target user (null for broadcast)',
    `template_id` int(11) DEFAULT NULL COMMENT 'Template reference',
    `notification_type` enum('email','sms','push','in_app') NOT NULL COMMENT 'Notification type',
    `title` varchar(255) NOT NULL COMMENT 'Notification title',
    `message` text NOT NULL COMMENT 'Notification message',
    `data` json DEFAULT NULL COMMENT 'Additional data in JSON',
    `priority` enum('low','medium','high','urgent') DEFAULT 'medium' COMMENT 'Notification priority',
    `status` enum('pending','sent','delivered','failed','read') DEFAULT 'pending' COMMENT 'Delivery status',
    `scheduled_at` datetime DEFAULT NULL COMMENT 'Scheduled delivery time',
    `sent_at` datetime DEFAULT NULL COMMENT 'Actual send time',
    `read_at` datetime DEFAULT NULL COMMENT 'Read timestamp',
    `expires_at` datetime DEFAULT NULL COMMENT 'Expiration time',
    `created_by` int(11) DEFAULT NULL COMMENT 'User who created notification',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_notification`),
    KEY `idx_notifications_user_id` (`user_id`),
    KEY `idx_notifications_template_id` (`template_id`),
    KEY `idx_notifications_type` (`notification_type`),
    KEY `idx_notifications_status` (`status`),
    KEY `idx_notifications_priority` (`priority`),
    `idx_notifications_created_at` (`created_at`),
    KEY `idx_notifications_scheduled_at` (`scheduled_at`),
    KEY `idx_notifications_expires_at` (`expires_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
    CONSTRAINT `fk_notifications_template` FOREIGN KEY (`template_id`) REFERENCES `notification_templates` (`id_template`) ON DELETE SET NULL,
    CONSTRAINT `fk_notifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notifications storage and tracking';

-- Create notification_preferences table
CREATE TABLE IF NOT EXISTS `notification_preferences` (
    `id_preference` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'User preference owner',
    `notification_type` enum('email','sms','push','in_app') NOT NULL COMMENT 'Notification type',
    `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether this type is enabled',
    `frequency` enum('immediate','hourly','daily','weekly','never') DEFAULT 'immediate' COMMENT 'Delivery frequency',
    `categories` varchar(255) DEFAULT NULL COMMENT 'Comma-separated categories',
    `quiet_hours_start` time DEFAULT NULL COMMENT 'Quiet hours start time',
    `quiet_hours_end` time DEFAULT NULL COMMENT 'Quiet hours end time',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_preference`),
    UNIQUE KEY `uk_notification_preferences_user_type` (`user_id`, `notification_type`),
    KEY `idx_notification_preferences_user` (`user_id`),
    KEY `idx_notification_preferences_type` (`notification_type`),
    CONSTRAINT `fk_notification_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User notification preferences';

-- Create notification_queue table for batch processing
CREATE TABLE IF NOT EXISTS `notification_queue` (
    `id_queue` int(11) NOT NULL AUTO_INCREMENT,
    `notification_id` int(11) NOT NULL COMMENT 'Reference to notification',
    `queue_type` enum('email','sms','push') NOT NULL COMMENT 'Queue type',
    `recipient` varchar(255) NOT NULL COMMENT 'Recipient (email, phone number, device token)',
    `subject` varchar(255) DEFAULT NULL COMMENT 'Email subject',
    `message` text NOT NULL COMMENT 'Message content',
    `data` json DEFAULT NULL COMMENT 'Additional data',
    `priority` enum('low','medium','high','urgent') DEFAULT 'medium' COMMENT 'Queue priority',
    `attempts` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of delivery attempts',
    `max_attempts` int(11) NOT NULL DEFAULT 3 COMMENT 'Maximum retry attempts',
    `status` enum('pending','processing','sent','failed','cancelled') DEFAULT 'pending' COMMENT 'Queue status',
    `error_message` text DEFAULT NULL COMMENT 'Last error message',
    `scheduled_at` datetime DEFAULT NULL COMMENT 'Scheduled delivery time',
    `processed_at` datetime DEFAULT NULL COMMENT 'Processing timestamp',
    `sent_at` datetime DEFAULT NULL COMMENT 'Successful send timestamp',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_queue`),
    KEY `idx_notification_queue_notification_id` (`notification_id`),
    KEY `idx_notification_queue_type` (`queue_type`),
    KEY `idx_notification_queue_status` (`status`),
    KEY `idx_notification_queue_priority` (`priority`),
    KEY `idx_notification_queue_scheduled_at` (`scheduled_at`),
    KEY `idx_notification_queue_recipient` (`recipient`),
    CONSTRAINT `fk_notification_queue_notification` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id_notification`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notification queue for batch processing';

-- Create notification_settings table
CREATE TABLE IF NOT EXISTS `notification_settings` (
    `id_setting` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL COMMENT 'Setting key',
    `setting_value` text COMMENT 'Setting value',
    `setting_description` text COMMENT 'Setting description',
    `setting_type` enum('string','integer','boolean','json') NOT NULL DEFAULT 'string' COMMENT 'Setting data type',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether setting is active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_setting`),
    UNIQUE KEY `uk_notification_settings_key` (`setting_key`),
    KEY `idx_notification_settings_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notification system settings';

-- Insert default notification settings
INSERT INTO `notification_settings` (`setting_key`, `setting_value`, `setting_description`, `setting_type`) VALUES
('email_enabled', '1', 'Enable email notifications', 'boolean'),
('email_from_address', 'noreply@perdagangan.com', 'From email address', 'string'),
('email_from_name', 'Perdagangan System', 'From email name', 'string'),
('smtp_host', 'localhost', 'SMTP server host', 'string'),
('smtp_port', '587', 'SMTP server port', 'integer'),
('smtp_username', '', 'SMTP username', 'string'),
('smtp_password', '', 'SMTP password', 'string'),
('smtp_encryption', 'tls', 'SMTP encryption', 'string'),
('sms_enabled', '0', 'Enable SMS notifications', 'boolean'),
('sms_provider', 'twilio', 'SMS provider', 'string'),
('sms_api_key', '', 'SMS API key', 'string'),
('sms_api_secret', '', 'SMS API secret', 'string'),
('sms_from_number', '', 'SMS from number', 'string'),
('push_enabled', '1', 'Enable push notifications', 'boolean'),
('push_vapid_public_key', '', 'VAPID public key', 'string'),
('push_vapid_private_key', '', 'VAPID private key', 'string'),
('push_vapid_subject', 'mailto:admin@perdagangan.com', 'VAPID subject', 'string'),
('batch_size', '100', 'Batch processing size', 'integer'),
('batch_interval', '60', 'Batch processing interval (seconds)', 'integer'),
('max_retry_attempts', '3', 'Maximum retry attempts', 'integer'),
('cleanup_days', '30', 'Cleanup old notifications after X days', 'integer'),
('quiet_hours_enabled', '1', 'Enable quiet hours', 'boolean'),
('quiet_hours_start', '22:00', 'Quiet hours start time', 'string'),
('quiet_hours_end', '08:00', 'Quiet hours end time', 'string');

-- Create indexes for better performance
CREATE INDEX `idx_notifications_composite` ON `notifications` (`user_id`, `status`, `created_at`);
CREATE INDEX `idx_notification_queue_composite` ON `notification_queue` (`status`, `priority`, `scheduled_at`);
CREATE INDEX `idx_notifications_search` ON `notifications` (`title`, `message`, `data`);

-- Create stored procedure for notification cleanup
DELIMITER //
CREATE PROCEDURE `cleanup_old_notifications`(IN days_old INT)
BEGIN
    DELETE FROM notifications 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL days_old DAY)
    AND status IN ('delivered', 'read', 'failed');
    
    SELECT ROW_COUNT() as cleaned_notifications;
END//
DELIMITER ;

-- Create function to format notification time
DELIMITER //
CREATE FUNCTION `format_notification_time`(notification_time DATETIME) 
RETURNS VARCHAR(50) DETERMINISTIC
BEGIN
    IF notification_time IS NULL THEN
        RETURN 'Never';
    END IF;
    
    IF notification_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN
        RETURN TIME_FORMAT(notification_time, '%H:%i:%s');
    ELSEIF notification_time >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN
        RETURN DATE_FORMAT(notification_time, '%H:%i');
    ELSEIF notification_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)) THEN
        RETURN DATE_FORMAT(notification_time, '%b %d');
    ELSE
        RETURN DATE_FORMAT(notification_time, '%Y-%m-%d');
    END IF;
END//
DELIMITER ;

-- Add notification preferences to existing users
INSERT INTO `notification_preferences` (`user_id`, `notification_type`, `is_enabled`, `frequency`)
SELECT id_user, 'email', 1, 'immediate' FROM users
ON DUPLICATE KEY UPDATE 
    `is_enabled` = VALUES(`is_enabled`),
    `frequency` = VALUES(`frequency`);

INSERT INTO `notification_preferences` (`user_id`, `notification_type`, `is_enabled`, `frequency`)
SELECT id_user, 'in_app', 1, 'immediate' FROM users
ON DUPLICATE KEY UPDATE 
    `is_enabled` = VALUES(`is_enabled`),
    `frequency` = VALUES(`frequency`);

-- Final verification query
SELECT 'Notification system tables created successfully' as status;
