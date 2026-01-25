-- WebSocket System Tables
CREATE TABLE IF NOT EXISTS `websocket_connections` (
    `id_connection` int(11) NOT NULL AUTO_INCREMENT,
    `client_id` varchar(255) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `username` varchar(100) DEFAULT NULL,
    `authenticated` tinyint(1) NOT NULL DEFAULT 0,
    `connected_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` enum('connected','disconnected','error') NOT NULL DEFAULT 'connected',
    PRIMARY KEY (`id_connection`),
    UNIQUE KEY `uk_websocket_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `websocket_messages` (
    `id_message` int(11) NOT NULL AUTO_INCREMENT,
    `channel` varchar(255) NOT NULL,
    `message_type` varchar(50) NOT NULL,
    `message_data` json NOT NULL,
    `sender_id` int(11) DEFAULT NULL,
    `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` enum('pending','sent','delivered','failed') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`id_message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `websocket_settings` (
    `id_setting` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text,
    `setting_type` enum('string','integer','boolean','json') NOT NULL DEFAULT 'string',
    PRIMARY KEY (`id_setting`),
    UNIQUE KEY `uk_websocket_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `websocket_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('enabled', 'true', 'boolean'),
('host', 'localhost', 'string'),
('port', '8080', 'integer');
