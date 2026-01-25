-- WebSocket Integration Tables for Real-time Collaboration
-- Created: 2025-01-25
-- Purpose: Real-time updates, live notifications, and multi-user collaboration

-- WebSocket Connections Table
CREATE TABLE IF NOT EXISTS websocket_connections (
    id_connection INT AUTO_INCREMENT PRIMARY KEY,
    connection_id VARCHAR(255) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    session_id VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    connected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'disconnected') DEFAULT 'active',
    connection_type ENUM('web', 'mobile', 'api') DEFAULT 'web',
    company_id INT,
    branch_id INT,
    INDEX idx_connection_id (connection_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_last_activity (last_activity),
    INDEX idx_company_branch (company_id, branch_id),
    FOREIGN KEY (user_id) REFERENCES members(id_member) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id_company) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WebSocket Channels Table
CREATE TABLE IF NOT EXISTS websocket_channels (
    id_channel INT AUTO_INCREMENT PRIMARY KEY,
    channel_name VARCHAR(100) NOT NULL UNIQUE,
    channel_type ENUM('public', 'private', 'company', 'branch', 'user') NOT NULL,
    description TEXT,
    owner_id INT,
    company_id INT,
    branch_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_channel_name (channel_name),
    INDEX idx_channel_type (channel_type),
    INDEX idx_owner (owner_id),
    INDEX idx_company_branch (company_id, branch_id),
    FOREIGN KEY (owner_id) REFERENCES members(id_member) ON DELETE SET NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id_company) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WebSocket Channel Subscriptions Table
CREATE TABLE IF NOT EXISTS websocket_channel_subscriptions (
    id_subscription INT AUTO_INCREMENT PRIMARY KEY,
    connection_id VARCHAR(255) NOT NULL,
    channel_id INT NOT NULL,
    user_id INT NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_message_id INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_connection_channel (connection_id, channel_id),
    INDEX idx_user_channel (user_id, channel_id),
    INDEX idx_subscribed_at (subscribed_at),
    FOREIGN KEY (channel_id) REFERENCES websocket_channels(id_channel) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES members(id_member) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WebSocket Messages Table
CREATE TABLE IF NOT EXISTS websocket_messages (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    channel_id INT NOT NULL,
    sender_id INT NOT NULL,
    message_type ENUM('text', 'json', 'notification', 'system', 'file', 'image') DEFAULT 'text',
    message_content LONGTEXT,
    message_data JSON,
    recipient_id INT NULL, -- For private messages
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_delivered BOOLEAN DEFAULT FALSE,
    delivery_count INT DEFAULT 0,
    INDEX idx_channel_id (channel_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_created_at (created_at),
    INDEX idx_priority (priority),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (channel_id) REFERENCES websocket_channels(id_channel) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES members(id_member) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES members(id_member) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WebSocket Message Delivery Table
CREATE TABLE IF NOT EXISTS websocket_message_delivery (
    id_delivery INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    connection_id VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    delivered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_status ENUM('pending', 'delivered', 'failed', 'expired') DEFAULT 'pending',
    error_message TEXT,
    retry_count INT DEFAULT 0,
    INDEX idx_message_connection (message_id, connection_id),
    INDEX idx_user_delivery (user_id, delivery_status),
    INDEX idx_delivered_at (delivered_at),
    FOREIGN KEY (message_id) REFERENCES websocket_messages(id_message) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WebSocket Events Table
CREATE TABLE IF NOT EXISTS websocket_events (
    id_event INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    event_name VARCHAR(100) NOT NULL,
    event_data JSON,
    source_user_id INT,
    target_user_id INT NULL,
    company_id INT,
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    status ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
    INDEX idx_event_type (event_type),
    INDEX idx_event_name (event_name),
    INDEX idx_source_user (source_user_id),
    INDEX idx_target_user (target_user_id),
    INDEX idx_company_branch (company_id, branch_id),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status),
    FOREIGN KEY (source_user_id) REFERENCES members(id_member) ON DELETE SET NULL,
    FOREIGN KEY (target_user_id) REFERENCES members(id_member) ON DELETE SET NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id_company) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WebSocket Settings Table
CREATE TABLE IF NOT EXISTS websocket_settings (
    id_setting INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE,
    company_id INT NULL,
    branch_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key),
    INDEX idx_company_branch (company_id, branch_id),
    FOREIGN KEY (company_id) REFERENCES companies(id_company) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WebSocket Statistics Table
CREATE TABLE IF NOT EXISTS websocket_statistics (
    id_stat INT AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE NOT NULL,
    company_id INT NULL,
    branch_id INT NULL,
    total_connections INT DEFAULT 0,
    active_connections INT DEFAULT 0,
    total_messages INT DEFAULT 0,
    delivered_messages INT DEFAULT 0,
    failed_messages INT DEFAULT 0,
    average_response_time DECIMAL(10,3) DEFAULT 0,
    peak_connections INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date_company_branch (stat_date, company_id, branch_id),
    INDEX idx_stat_date (stat_date),
    INDEX idx_company_branch (company_id, branch_id),
    FOREIGN KEY (company_id) REFERENCES companies(id_company) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default WebSocket settings
INSERT INTO websocket_settings (setting_key, setting_value, setting_type, description, is_system) VALUES
('websocket_enabled', 'true', 'boolean', 'Enable WebSocket functionality', TRUE),
('websocket_port', '8080', 'number', 'WebSocket server port', TRUE),
('websocket_host', 'localhost', 'string', 'WebSocket server host', TRUE),
('max_connections_per_user', '5', 'number', 'Maximum connections per user', TRUE),
('connection_timeout', '300', 'number', 'Connection timeout in seconds', TRUE),
('message_retention_days', '30', 'number', 'Message retention period in days', TRUE),
('enable_file_sharing', 'true', 'boolean', 'Enable file sharing through WebSocket', TRUE),
('max_file_size_mb', '10', 'number', 'Maximum file size for sharing in MB', TRUE),
('enable_private_messages', 'true', 'boolean', 'Enable private messaging', TRUE),
('enable_channel_creation', 'false', 'boolean', 'Allow users to create channels', TRUE),
('auto_cleanup_connections', 'true', 'boolean', 'Automatically cleanup inactive connections', TRUE),
('cleanup_interval_minutes', '5', 'number', 'Cleanup interval for inactive connections', TRUE),
('enable_message_encryption', 'false', 'boolean', 'Enable message encryption', TRUE),
('enable_rate_limiting', 'true', 'boolean', 'Enable rate limiting for messages', TRUE),
('max_messages_per_minute', '30', 'number', 'Maximum messages per minute per user', TRUE),
('enable_presence_tracking', 'true', 'boolean', 'Enable user presence tracking', TRUE),
('enable_typing_indicators', 'true', 'boolean', 'Enable typing indicators', TRUE),
('enable_read_receipts', 'true', 'boolean', 'Enable read receipts', TRUE),
('enable_message_history', 'true', 'boolean', 'Enable message history storage', TRUE);

-- Insert default WebSocket channels
INSERT INTO websocket_channels (channel_name, channel_type, description, is_active) VALUES
('global', 'public', 'Global system announcements', TRUE),
('notifications', 'public', 'System notifications channel', TRUE),
('file-uploads', 'public', 'File upload notifications', TRUE),
('system-alerts', 'public', 'System alerts and warnings', TRUE),
('user-presence', 'public', 'User presence updates', TRUE);

-- Create views for WebSocket monitoring
CREATE OR REPLACE VIEW websocket_active_connections AS
SELECT 
    c.connection_id,
    c.user_id,
    m.member_name,
    m.email,
    c.company_id,
    co.company_name,
    c.branch_id,
    b.branch_name,
    c.ip_address,
    c.connection_type,
    c.connected_at,
    c.last_activity,
    TIMESTAMPDIFF(SECOND, c.connected_at, NOW()) as duration_seconds,
    COUNT(cs.id_subscription) as channel_count
FROM websocket_connections c
LEFT JOIN members m ON c.user_id = m.id_member
LEFT JOIN companies co ON c.company_id = co.id_company
LEFT JOIN branches b ON c.branch_id = b.id_branch
LEFT JOIN websocket_channel_subscriptions cs ON c.connection_id = cs.connection_id AND cs.is_active = TRUE
WHERE c.status = 'active'
GROUP BY c.connection_id;

CREATE OR REPLACE VIEW websocket_channel_activity AS
SELECT 
    ch.id_channel,
    ch.channel_name,
    ch.channel_type,
    ch.description,
    COUNT(cs.id_subscription) as active_subscribers,
    COUNT(wm.id_message) as total_messages,
    MAX(wm.created_at) as last_message_at,
    COUNT(CASE WHEN wm.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as messages_last_hour
FROM websocket_channels ch
LEFT JOIN websocket_channel_subscriptions cs ON ch.id_channel = cs.id_channel AND cs.is_active = TRUE
LEFT JOIN websocket_messages wm ON ch.id_channel = wm.channel_id
WHERE ch.is_active = TRUE
GROUP BY ch.id_channel;

-- Create stored procedures for WebSocket management
DELIMITER //

-- Procedure to cleanup inactive connections
CREATE PROCEDURE CleanupInactiveConnections()
BEGIN
    DECLARE timeout_seconds INT DEFAULT 300;
    
    -- Get timeout setting
    SELECT CAST(setting_value AS UNSIGNED) INTO timeout_seconds
    FROM websocket_settings 
    WHERE setting_key = 'connection_timeout' AND is_system = TRUE;
    
    -- Mark inactive connections
    UPDATE websocket_connections 
    SET status = 'disconnected'
    WHERE status = 'active' 
    AND last_activity < DATE_SUB(NOW(), INTERVAL timeout_seconds SECOND);
    
    -- Cleanup old subscriptions
    UPDATE websocket_channel_subscriptions cs
    INNER JOIN websocket_connections c ON cs.connection_id = c.connection_id
    SET cs.is_active = FALSE
    WHERE c.status = 'disconnected';
    
    -- Update statistics
    CALL UpdateWebSocketStatistics();
END //

-- Procedure to update WebSocket statistics
CREATE PROCEDURE UpdateWebSocketStatistics()
BEGIN
    DECLARE today_date DATE DEFAULT CURDATE();
    
    INSERT INTO websocket_statistics (
        stat_date, 
        total_connections, 
        active_connections, 
        total_messages,
        delivered_messages,
        failed_messages,
        peak_connections
    )
    SELECT 
        today_date,
        COUNT(*),
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END),
        (SELECT COUNT(*) FROM websocket_messages WHERE DATE(created_at) = today_date),
        (SELECT COUNT(*) FROM websocket_message_delivery WHERE DATE(delivered_at) = today_date AND delivery_status = 'delivered'),
        (SELECT COUNT(*) FROM websocket_message_delivery WHERE DATE(delivered_at) = today_date AND delivery_status = 'failed'),
        (SELECT COUNT(*) FROM websocket_connections WHERE DATE(connected_at) = today_date)
    FROM websocket_connections
    ON DUPLICATE KEY UPDATE
        total_connections = VALUES(total_connections),
        active_connections = VALUES(active_connections),
        total_messages = VALUES(total_messages),
        delivered_messages = VALUES(delivered_messages),
        failed_messages = VALUES(failed_messages),
        peak_connections = GREATEST(peak_connections, VALUES(peak_connections)),
        updated_at = CURRENT_TIMESTAMP;
END //

-- Procedure to broadcast message to channel
CREATE PROCEDURE BroadcastToChannel(
    IN p_channel_id INT,
    IN p_sender_id INT,
    IN p_message_type VARCHAR(20),
    IN p_message_content LONGTEXT,
    IN p_message_data JSON,
    IN p_priority VARCHAR(10)
)
BEGIN
    DECLARE v_message_id INT;
    
    -- Insert message
    INSERT INTO websocket_messages (
        channel_id, 
        sender_id, 
        message_type, 
        message_content, 
        message_data, 
        priority
    ) VALUES (
        p_channel_id, 
        p_sender_id, 
        p_message_type, 
        p_message_content, 
        p_message_data, 
        p_priority
    );
    
    SET v_message_id = LAST_INSERT_ID();
    
    -- Create delivery records for all active connections in channel
    INSERT INTO websocket_message_delivery (
        message_id, 
        connection_id, 
        user_id
    )
    SELECT 
        v_message_id,
        cs.connection_id,
        cs.user_id
    FROM websocket_channel_subscriptions cs
    INNER JOIN websocket_connections c ON cs.connection_id = c.connection_id
    WHERE cs.channel_id = p_channel_id 
    AND cs.is_active = TRUE 
    AND c.status = 'active';
    
    SELECT v_message_id as message_id;
END //

-- Procedure to send private message
CREATE PROCEDURE SendPrivateMessage(
    IN p_sender_id INT,
    IN p_recipient_id INT,
    IN p_message_content LONGTEXT,
    IN p_message_data JSON
)
BEGIN
    DECLARE v_channel_id INT;
    DECLARE v_message_id INT;
    
    -- Get or create private channel
    SELECT id_channel INTO v_channel_id
    FROM websocket_channels 
    WHERE channel_type = 'private' 
    AND owner_id = p_sender_id 
    AND channel_name = CONCAT('private_', LEAST(p_sender_id, p_recipient_id), '_', GREATEST(p_sender_id, p_recipient_id));
    
    IF v_channel_id IS NULL THEN
        INSERT INTO websocket_channels (
            channel_name, 
            channel_type, 
            owner_id,
            description
        ) VALUES (
            CONCAT('private_', LEAST(p_sender_id, p_recipient_id), '_', GREATEST(p_sender_id, p_recipient_id)),
            'private',
            p_sender_id,
            'Private message channel'
        );
        
        SET v_channel_id = LAST_INSERT_ID();
    END IF;
    
    -- Insert message
    INSERT INTO websocket_messages (
        channel_id, 
        sender_id, 
        message_type, 
        message_content, 
        message_data, 
        recipient_id
    ) VALUES (
        v_channel_id, 
        p_sender_id, 
        'text', 
        p_message_content, 
        p_message_data, 
        p_recipient_id
    );
    
    SET v_message_id = LAST_INSERT_ID();
    
    -- Create delivery records for recipient's active connections
    INSERT INTO websocket_message_delivery (
        message_id, 
        connection_id, 
        user_id
    )
    SELECT 
        v_message_id,
        c.connection_id,
        c.user_id
    FROM websocket_connections c
    WHERE c.user_id = p_recipient_id 
    AND c.status = 'active';
    
    SELECT v_message_id as message_id;
END //

DELIMITER ;

-- Create triggers for WebSocket events
DELIMITER //

-- Trigger to log WebSocket connection events
CREATE TRIGGER websocket_connection_insert
AFTER INSERT ON websocket_connections
FOR EACH ROW
BEGIN
    INSERT INTO websocket_events (
        event_type,
        event_name,
        event_data,
        source_user_id,
        company_id,
        branch_id,
        status
    ) VALUES (
        'connection',
        'user_connected',
        JSON_OBJECT(
            'connection_id', NEW.connection_id,
            'ip_address', NEW.ip_address,
            'user_agent', NEW.user_agent,
            'connection_type', NEW.connection_type
        ),
        NEW.user_id,
        NEW.company_id,
        NEW.branch_id,
        'processed'
    );
END //

-- Trigger to log WebSocket message events
CREATE TRIGGER websocket_message_insert
AFTER INSERT ON websocket_messages
FOR EACH ROW
BEGIN
    INSERT INTO websocket_events (
        event_type,
        event_name,
        event_data,
        source_user_id,
        target_user_id,
        company_id,
        branch_id,
        status
    ) VALUES (
        'message',
        'message_sent',
        JSON_OBJECT(
            'message_id', NEW.id_message,
            'channel_id', NEW.channel_id,
            'message_type', NEW.message_type,
            'priority', NEW.priority
        ),
        NEW.sender_id,
        NEW.recipient_id,
        (SELECT company_id FROM members WHERE id_member = NEW.sender_id),
        (SELECT branch_id FROM members WHERE id_member = NEW.sender_id),
        'pending'
    );
END //

DELIMITER ;

-- Create indexes for performance optimization
CREATE INDEX idx_websocket_messages_composite ON websocket_messages(channel_id, created_at, priority);
CREATE INDEX idx_websocket_delivery_composite ON websocket_message_delivery(message_id, delivery_status, delivered_at);
CREATE INDEX idx_websocket_events_composite ON websocket_events(event_type, created_at, status);
CREATE INDEX idx_websocket_subscriptions_composite ON websocket_channel_subscriptions(channel_id, is_active, subscribed_at);

-- Add comments for documentation
ALTER TABLE websocket_connections COMMENT = 'Active WebSocket connections and user sessions';
ALTER TABLE websocket_channels COMMENT = 'WebSocket communication channels';
ALTER TABLE websocket_channel_subscriptions COMMENT = 'User subscriptions to WebSocket channels';
ALTER TABLE websocket_messages COMMENT = 'Messages sent through WebSocket channels';
ALTER TABLE websocket_message_delivery COMMENT = 'Message delivery tracking';
ALTER TABLE websocket_events COMMENT = 'WebSocket system events for logging and monitoring';
ALTER TABLE websocket_settings COMMENT = 'WebSocket system configuration settings';
ALTER TABLE websocket_statistics COMMENT = 'WebSocket usage statistics and metrics';
