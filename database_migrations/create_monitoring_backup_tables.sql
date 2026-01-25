-- Database Schema for System Monitoring and Backup Management
-- Part of Phase 2: System Administration Implementation

-- System Monitoring Table
CREATE TABLE IF NOT EXISTS system_monitoring (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(10,2) NOT NULL,
    metric_unit VARCHAR(20),
    component VARCHAR(50) NOT NULL,
    status ENUM('healthy', 'warning', 'critical', 'error') NOT NULL DEFAULT 'healthy',
    message TEXT,
    threshold_min DECIMAL(10,2),
    threshold_max DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_type (metric_type),
    idx_component (component),
    idx_status (status),
    idx_created_at (created_at),
    INDEX idx_metric_type_created (metric_type, created_at)
);

-- Performance Metrics Table
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(10,2) NOT NULL,
    metric_unit VARCHAR(20),
    component VARCHAR(50) NOT NULL,
    status ENUM('healthy', 'warning', 'critical', 'error') NOT NULL DEFAULT 'healthy',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_type (metric_type),
    idx_component (component),
    idx_created_at (created_at),
    INDEX idx_metric_type_created (metric_type, created_at)
);

-- System Alerts Table
CREATE TABLE IF NOT EXISTS system_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alert_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    severity ENUM('info', 'warning', 'critical', 'error') NOT NULL DEFAULT 'warning',
    component VARCHAR(50),
    status ENUM('active', 'resolved', 'acknowledged') NOT NULL DEFAULT 'active',
    resolution TEXT,
    created_by INT,
    resolved_by INT,
    resolved_at TIMESTAMP NULL,
    acknowledged_by INT,
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_alert_type (alert_type),
    idx_severity (severity),
    idx_status (status),
    idx_component (component),
    idx_created_at (created_at),
    FOREIGN KEY (created_by) REFERENCES users(id_user) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id_user) ON DELETE SET NULL,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Backup Schedules Table
CREATE TABLE IF NOT EXISTS backup_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_name VARCHAR(100) NOT NULL,
    backup_type ENUM('full', 'database', 'files', 'settings') NOT NULL DEFAULT 'full',
    frequency ENUM('hourly', 'daily', 'weekly', 'monthly') NOT NULL,
    next_run TIMESTAMP NOT NULL,
    last_run TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    retention_days INT DEFAULT 30,
    backup_path VARCHAR(500),
    compression BOOLEAN DEFAULT TRUE,
    encryption BOOLEAN DEFAULT FALSE,
    notification_enabled BOOLEAN DEFAULT TRUE,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_schedule_name (schedule_name),
    idx_backup_type (backup_type),
    idx_frequency (frequency),
    idx_next_run (next_run),
    idx_is_active (is_active),
    FOREIGN KEY (created_by) REFERENCES users(id_user) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Backup History Table
CREATE TABLE IF NOT EXISTS backup_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    backup_id VARCHAR(50) UNIQUE NOT NULL,
    backup_type ENUM('full', 'database', 'files', 'settings') NOT NULL DEFAULT 'full',
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT DEFAULT 0,
    duration DECIMAL(8,2) DEFAULT 0,
    status ENUM('pending', 'in_progress', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    error_message TEXT,
    schedule_id INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_backup_id (backup_id),
    idx_backup_type (backup_type),
    idx_status (status),
    idx_created_at (created_at),
    idx_schedule_id (schedule_id),
    FOREIGN KEY (schedule_id) REFERENCES backup_schedules(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Service Status Table
CREATE TABLE IF NOT EXISTS service_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    status ENUM('running', 'stopped', 'error', 'unknown') NOT NULL DEFAULT 'unknown',
    uptime_seconds BIGINT DEFAULT 0,
    memory_usage BIGINT DEFAULT 0,
    cpu_usage DECIMAL(5,2) DEFAULT 0,
    last_check TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service_name (service_name),
    idx_status (status),
    idx_last_check (last_check)
);

-- Monitoring Thresholds Table
CREATE TABLE IF NOT EXISTS monitoring_thresholds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_type VARCHAR(50) NOT NULL,
    component VARCHAR(50) NOT NULL,
    warning_threshold DECIMAL(10,2),
    critical_threshold DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_metric_type (metric_type),
    idx_component (component),
    idx_is_active (is_active),
    FOREIGN KEY (created_by) REFERENCES users(id_user) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Insert default monitoring thresholds
INSERT INTO monitoring_thresholds (metric_type, component, warning_threshold, critical_threshold, is_active) VALUES
('cpu_usage', 'system', 80, 90, TRUE),
('memory_usage', 'system', 80, 90, TRUE),
('disk_usage', 'system', 80, 90, TRUE),
('response_time', 'web_server', 1.0, 2.0, TRUE),
('response_time', 'database', 0.5, 1.0, TRUE),
('queue_size', 'queue', 100, 500, TRUE),
('error_rate', 'system', 5, 10, TRUE),
('backup_age', 'backup', 86400, 172800, TRUE); -- 24h warning, 48h critical

-- Insert default service status entries
INSERT INTO service_status (service_name, display_name, status, created_at, updated_at) VALUES
('web_server', 'Web Server', 'running', NOW(), NOW()),
('database', 'Database Server', 'running', NOW(), NOW()),
('cache', 'Cache Server', 'running', NOW(), NOW()),
('queue', 'Queue Worker', 'running', NOW(), NOW()),
('scheduler', 'Task Scheduler', 'running', NOW(), NOW());

-- Insert default backup schedules
INSERT INTO backup_schedules (schedule_name, backup_type, frequency, next_run, is_active, created_by, created_at) VALUES
('Daily Full Backup', 'full', 'daily', DATE_ADD(CURDATE(), INTERVAL 1 DAY), TRUE, 1, NOW()),
('Weekly Database Backup', 'database', 'weekly', DATE_ADD(CURDATE(), INTERVAL 7 DAY), TRUE, 1, NOW()),
('Monthly Settings Backup', 'settings', 'monthly', DATE_ADD(CURDATE(), INTERVAL 1 MONTH), TRUE, 1, NOW());

-- Create indexes for better performance
CREATE INDEX idx_system_monitoring_composite ON system_monitoring(metric_type, component, created_at);
CREATE INDEX idx_performance_metrics_composite ON performance_metrics(metric_type, component, created_at);
CREATE INDEX idx_system_alerts_severity_status ON system_alerts(severity, status, created_at);
CREATE INDEX idx_backup_history_status_created ON backup_history(status, created_at);
CREATE INDEX idx_backup_schedules_next_run ON backup_schedules(next_run, is_active);

-- Create views for common monitoring queries
CREATE VIEW system_health_summary AS
SELECT 
    component,
    status,
    COUNT(*) as count,
    MAX(created_at) as last_check,
    CASE 
        WHEN status IN ('error', 'critical') THEN 'critical'
        WHEN status = 'warning' THEN 'warning'
        ELSE 'healthy'
    END as health_level
FROM system_monitoring
GROUP BY component, status;

CREATE VIEW performance_summary AS
SELECT 
    metric_type,
    component,
    AVG(metric_value) as average_value,
    MAX(metric_value) as max_value,
    MIN(metric_value) as min_value,
    COUNT(*) as data_points,
    MAX(created_at) as last_measurement
FROM performance_metrics
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY metric_type, component;

CREATE VIEW alert_summary AS
SELECT 
    severity,
    status,
    COUNT(*) as count,
    MAX(created_at) as last_alert
FROM system_alerts
GROUP BY severity, status;

-- Create stored procedure for automatic monitoring
DELIMITER //
CREATE PROCEDURE UpdateSystemMetrics()
BEGIN
    DECLARE cpu_usage DECIMAL(5,2);
    DECLARE memory_usage DECIMAL(5,2);
    DECLARE disk_usage DECIMAL(5,2);
    DECLARE response_time DECIMAL(5,2);
    
    -- Get CPU usage (mock implementation)
    SET cpu_usage = ROUND(RAND() * 100, 2);
    
    -- Get memory usage
    SET memory_usage = ROUND((memory_get_usage(true) / (1024 * 1024 * 1024)) * 100, 2);
    
    -- Get disk usage
    SET disk_usage = ROUND(((disk_total_space('/') - disk_free_space('/')) / disk_total_space('/')) * 100, 2);
    
    -- Get response time (mock implementation)
    SET response_time = ROUND(RAND() * 2, 2);
    
    -- Insert CPU usage
    INSERT INTO performance_metrics (metric_type, metric_value, metric_unit, component, status, created_at)
    VALUES ('cpu_usage', cpu_usage, '%', 'system', 
            CASE WHEN cpu_usage > 90 THEN 'critical' WHEN cpu_usage > 80 THEN 'warning' ELSE 'healthy' END, NOW());
    
    -- Insert memory usage
    INSERT INTO performance_metrics (metric_type, metric_value, metric_unit, component, status, created_at)
    VALUES ('memory_usage', memory_usage, '%', 'system', 
            CASE WHEN memory_usage > 90 THEN 'critical' WHEN memory_usage > 80 THEN 'warning' ELSE 'healthy' END, NOW());
    
    -- Insert disk usage
    INSERT INTO performance_metrics (metric_type, metric_value, metric_unit, component, status, created_at)
    VALUES ('disk_usage', disk_usage, '%', 'system', 
            CASE WHEN disk_usage > 90 THEN 'critical' WHEN disk_usage > 80 THEN 'warning' ELSE 'healthy' END, NOW());
    
    -- Insert response time
    INSERT INTO performance_metrics (metric_type, metric_value, metric_unit, component, status, created_at)
    VALUES ('response_time', response_time, 'seconds', 'web_server', 
            CASE WHEN response_time > 2.0 THEN 'critical' WHEN response_time > 1.0 THEN 'warning' ELSE 'healthy' END, NOW());
    
    -- Update service status
    UPDATE service_status 
    SET status = 'running', last_check = NOW()
    WHERE service_name IN ('web_server', 'database', 'cache', 'queue', 'scheduler');
    
END //
DELIMITER ;

-- Create trigger for automatic alert creation
DELIMITER //
CREATE TRIGGER check_thresholds_after_metric_insert
AFTER INSERT ON performance_metrics
FOR EACH ROW
BEGIN
    DECLARE warning_threshold DECIMAL(10,2);
    DECLARE critical_threshold DECIMAL(10,2);
    
    -- Get thresholds for this metric
    SELECT warning_threshold, critical_threshold 
    INTO warning_threshold, critical_threshold
    FROM monitoring_thresholds 
    WHERE metric_type = NEW.metric_type 
      AND component = NEW.component 
      AND is_active = TRUE
    LIMIT 1;
    
    -- Create alert if threshold exceeded
    IF NEW.metric_value >= critical_threshold THEN
        INSERT INTO system_alerts (alert_type, message, severity, component, status, created_at)
        VALUES (NEW.metric_type, 
                CONCAT(NEW.component, ' ', NEW.metric_type, ' critical: ', NEW.metric_value, NEW.metric_unit),
                'critical', NEW.component, 'active', NOW());
    ELSEIF NEW.metric_value >= warning_threshold THEN
        INSERT INTO system_alerts (alert_type, message, severity, component, status, created_at)
        VALUES (NEW.metric_type, 
                CONCAT(NEW.component, ' ', NEW.metric_type, ' warning: ', NEW.metric_value, NEW.metric_unit),
                'warning', NEW.component, 'active', NOW());
    END IF;
END //
DELIMITER ;

-- Create function for system health calculation
DELIMITER //
CREATE FUNCTION CalculateSystemHealth()
RETURNS VARCHAR(20)
READS SQL DATA
BEGIN
    DECLARE critical_count INT;
    DECLARE warning_count INT;
    DECLARE total_count INT;
    
    SELECT COUNT(*) INTO critical_count
    FROM system_monitoring 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
      AND status IN ('critical', 'error');
    
    SELECT COUNT(*) INTO warning_count
    FROM system_monitoring 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
      AND status = 'warning';
    
    SELECT COUNT(*) INTO total_count
    FROM system_monitoring 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE);
    
    IF critical_count > 0 THEN
        RETURN 'critical';
    ELSEIF warning_count > 0 THEN
        RETURN 'warning';
    ELSEIF total_count = 0 THEN
        RETURN 'unknown';
    ELSE
        RETURN 'healthy';
    END IF;
END //
DELIMITER ;

-- Create event for automatic backup scheduling
DELIMITER //
CREATE EVENT IF NOT EXISTS auto_backup_event
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    -- Check for due backup schedules
    CALL ProcessDueBackups();
END //
DELIMITER ;

-- Create procedure for processing due backups
DELIMITER //
CREATE PROCEDURE ProcessDueBackups()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE schedule_id INT;
    DECLARE backup_type VARCHAR(20);
    DECLARE backup_path VARCHAR(500);
    DECLARE compression BOOLEAN;
    DECLARE encryption BOOLEAN;
    DECLARE notification_enabled BOOLEAN;
    
    DECLARE backup_cursor CURSOR FOR 
        SELECT id, backup_type, backup_path, compression, encryption, notification_enabled
        FROM backup_schedules 
        WHERE is_active = TRUE 
          AND next_run <= NOW()
        FOR UPDATE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN backup_cursor;
    
    backup_loop: LOOP
        FETCH backup_cursor INTO schedule_id, backup_type, backup_path, compression, encryption, notification_enabled;
        IF done THEN
            LEAVE backup_loop;
        END IF;
        
        -- Create backup record
        INSERT INTO backup_history (backup_id, backup_type, file_name, file_path, status, schedule_id, created_at)
        VALUES (CONCAT('backup_', UNIX_TIMESTAMP()), backup_type, 
                CONCAT('backup_', DATE_FORMAT(NOW(), '%Y%m%d_%H%i%s'), '.sql'), 
                backup_path, 'in_progress', schedule_id, NOW());
        
        -- Update next run time
        UPDATE backup_schedules 
        SET next_run = CASE 
            WHEN frequency = 'hourly' THEN DATE_ADD(NOW(), INTERVAL 1 HOUR)
            WHEN frequency = 'daily' THEN DATE_ADD(NOW(), INTERVAL 1 DAY)
            WHEN frequency = 'weekly' THEN DATE_ADD(NOW(), INTERVAL 1 WEEK)
            WHEN frequency = 'monthly' THEN DATE_ADD(NOW(), INTERVAL 1 MONTH)
            ELSE DATE_ADD(NOW(), INTERVAL 1 DAY)
        END,
        last_run = NOW()
        WHERE id = schedule_id;
        
        -- TODO: Implement actual backup creation logic here
        -- This would call the backup creation function
        
    END LOOP;
    
    CLOSE backup_cursor;
    
END //
DELIMITER ;

-- Create view for backup monitoring
CREATE VIEW backup_monitoring AS
SELECT 
    bs.schedule_name,
    bs.backup_type,
    bs.frequency,
    bs.next_run,
    bs.last_run,
    bs.is_active,
    bh.status as last_backup_status,
    bh.created_at as last_backup_time,
    bh.file_size as last_backup_size,
    CASE 
        WHEN bh.status = 'completed' AND bh.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'healthy'
        WHEN bh.status = 'failed' OR bh.created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR) THEN 'critical'
        ELSE 'warning'
    END as backup_health
FROM backup_schedules bs
LEFT JOIN backup_history bh ON bs.id = bh.schedule_id
WHERE bs.is_active = TRUE
ORDER BY bs.next_run;
