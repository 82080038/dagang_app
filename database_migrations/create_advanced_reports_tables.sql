-- Create Advanced Reports System Tables
-- Advanced Reporting with AI-powered analytics and business intelligence

-- Main advanced reports table
CREATE TABLE IF NOT EXISTS `advanced_reports` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `report_id` varchar(50) NOT NULL,
    `report_type` enum('sales','inventory','customer','financial','performance','custom') NOT NULL,
    `report_title` varchar(255) NOT NULL,
    `report_description` text,
    `date_range` varchar(20) DEFAULT '7d',
    `filters` json DEFAULT NULL,
    `ai_model` varchar(50) DEFAULT 'standard',
    `status` enum('pending','processing','completed','failed','expired') DEFAULT 'pending',
    `file_path` varchar(500) DEFAULT NULL,
    `file_size` int(11) DEFAULT 0,
    `download_count` int(11) DEFAULT 0,
    `company_id` int(11) DEFAULT NULL,
    `branch_id` int(11) DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_report_id` (`report_id`),
    KEY `idx_report_type` (`report_type`),
    KEY `idx_status` (`status`),
    KEY `idx_ai_model` (`ai_model`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_company_id` (`company_id`),
    KEY `idx_branch_id` (`branch_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Models table for tracking ML models
CREATE TABLE IF NOT EXISTS `ai_models` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `model_type` varchar(50) NOT NULL,
    `model_name` varchar(100) NOT NULL,
    `description` text,
    `version` varchar(20) DEFAULT '1.0',
    `accuracy` decimal(5,4) DEFAULT 0.0000,
    `precision` decimal(5,4) DEFAULT 0.0000,
    `recall` decimal(5,4) DEFAULT 0.0000,
    `f1_score` decimal(5,4) DEFAULT 0.0000,
    `model_size` int(11) DEFAULT 0,
    `training_samples` int(11) DEFAULT 0,
    `last_trained` timestamp NULL DEFAULT NULL,
    `training_time` int(11) DEFAULT 0,
    `status` enum('training','trained','failed','deprecated') DEFAULT 'training',
    `model_path` varchar(500) DEFAULT NULL,
    `config` json DEFAULT NULL,
    `performance_metrics` json DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_model_type_version` (`model_type`, `version`),
    KEY `idx_model_type` (`model_type`),
    KEY `idx_status` (`status`),
    KEY `idx_last_trained` (`last_trained`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Training History table
CREATE TABLE IF NOT EXISTS `ai_training_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `model_type` varchar(50) NOT NULL,
    `training_id` varchar(50) NOT NULL,
    `training_samples` int(11) DEFAULT 0,
    `training_time` int(11) DEFAULT 0,
    `accuracy_before` decimal(5,4) DEFAULT 0.0000,
    `accuracy_after` decimal(5,4) DEFAULT 0.0000,
    `precision_before` decimal(5,4) DEFAULT 0.0000,
    `precision_after` decimal(5,4) DEFAULT 0.0000,
    `recall_before` decimal(5,4) DEFAULT 0.0000,
    `recall_after` decimal(5,4) DEFAULT 0.0000,
    `f1_score_before` decimal(5,4) DEFAULT 0.0000,
    `f1_score_after` decimal(5,4) DEFAULT 0.0000,
    `training_params` json DEFAULT NULL,
    `status` enum('started','completed','failed','cancelled') DEFAULT 'started',
    `error_message` text,
    `started_by` int(11) DEFAULT NULL,
    `started_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `completed_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_training_id` (`training_id`),
    KEY `idx_model_type` (`model_type`),
    KEY `idx_status` (`status`),
    KEY `idx_started_by` (`started_by`),
    KEY `idx_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Analytics table for storing analytics data
CREATE TABLE IF NOT EXISTS `report_analytics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `report_id` varchar(50) NOT NULL,
    `analytics_type` varchar(50) NOT NULL,
    `analytics_data` json NOT NULL,
    `insights` json DEFAULT NULL,
    `confidence_score` decimal(5,4) DEFAULT 0.0000,
    `data_points` int(11) DEFAULT 0,
    `anomalies_detected` int(11) DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_report_id` (`report_id`),
    KEY `idx_analytics_type` (`analytics_type`),
    KEY `idx_confidence_score` (`confidence_score`),
    FOREIGN KEY (`report_id`) REFERENCES `advanced_reports`(`report_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Predictions table for AI predictions
CREATE TABLE IF NOT EXISTS `report_predictions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `report_id` varchar(50) NOT NULL,
    `prediction_type` varchar(50) NOT NULL,
    `prediction_data` json NOT NULL,
    `confidence_score` decimal(5,4) DEFAULT 0.0000,
    `accuracy_score` decimal(5,4) DEFAULT 0.0000,
    `prediction_period` varchar(20) NOT NULL,
    `model_used` varchar(50) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `expires_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_report_id` (`report_id`),
    KEY `idx_prediction_type` (`prediction_type`),
    KEY `idx_confidence_score` (`confidence_score`),
    KEY `idx_expires_at` (`expires_at`),
    FOREIGN KEY (`report_id`) REFERENCES `advanced_reports`(`report_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Recommendations table
CREATE TABLE IF NOT EXISTS `report_recommendations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `report_id` varchar(50) NOT NULL,
    `recommendation_type` varchar(50) NOT NULL,
    `recommendation_text` text NOT NULL,
    `priority` enum('low','medium','high','critical') DEFAULT 'medium',
    `confidence_score` decimal(5,4) DEFAULT 0.0000,
    `actionable` tinyint(1) DEFAULT 1,
    `implemented` tinyint(1) DEFAULT 0,
    `implemented_by` int(11) DEFAULT NULL,
    `implemented_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_report_id` (`report_id`),
    KEY `idx_recommendation_type` (`recommendation_type`),
    KEY `idx_priority` (`priority`),
    KEY `idx_actionable` (`actionable`),
    KEY `idx_implemented` (`implemented`),
    FOREIGN KEY (`report_id`) REFERENCES `advanced_reports`(`report_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Templates table for predefined report templates
CREATE TABLE IF NOT EXISTS `report_templates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `template_id` varchar(50) NOT NULL,
    `template_name` varchar(100) NOT NULL,
    `template_description` text,
    `report_type` varchar(50) NOT NULL,
    `template_config` json NOT NULL,
    `is_default` tinyint(1) DEFAULT 0,
    `is_active` tinyint(1) DEFAULT 1,
    `usage_count` int(11) DEFAULT 0,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_template_id` (`template_id`),
    KEY `idx_report_type` (`report_type`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_usage_count` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default AI models
INSERT INTO `ai_models` (`model_type`, `model_name`, `description`, `version`, `accuracy`, `precision`, `recall`, `f1_score`, `training_samples`, `last_trained`, `status`) VALUES
('sales_forecasting', 'Sales Forecasting Model', 'Predict future sales based on historical data and trends', '1.0', 0.8500, 0.8200, 0.8800, 0.8480, 1000, '2024-01-15 10:30:00', 'trained'),
('inventory_optimization', 'Inventory Optimization Model', 'Optimize inventory levels based on demand patterns', '1.0', 0.7800, 0.7500, 0.8200, 0.7820, 800, '2024-01-15 09:15:00', 'trained'),
('customer_segmentation', 'Customer Segmentation Model', 'AI-powered customer behavior analysis and segmentation', '1.0', 0.8200, 0.8000, 0.8500, 0.8240, 600, '2024-01-15 11:30:00', 'trained'),
('price_optimization', 'Price Optimization Model', 'AI-powered pricing recommendations based on market data', '1.0', 0.7500, 0.7200, 0.7800, 0.7480, 400, '2024-01-15 12:30:00', 'trained'),
('anomaly_detection', 'Anomaly Detection Model', 'Detect unusual patterns and anomalies in business data', '1.0', 0.8800, 0.8500, 0.9100, 0.8790, 1200, '2024-01-15 13:30:00', 'trained');

-- Insert default report templates
INSERT INTO `report_templates` (`template_id`, `template_name`, `template_description`, `report_type`, `template_config`, `is_default`) VALUES
('sales_daily', 'Daily Sales Report', 'Comprehensive daily sales analysis with trends and insights', 'sales', '{"date_range": "1d", "include_forecasts": true, "include_charts": true, "ai_model": "sales_forecasting"}', 1),
('sales_weekly', 'Weekly Sales Report', 'Weekly sales performance with comparative analysis', 'sales', '{"date_range": "7d", "include_forecasts": true, "include_charts": true, "ai_model": "sales_forecasting"}', 1),
('sales_monthly', 'Monthly Sales Report', 'Monthly sales analysis with detailed breakdowns', 'sales', '{"date_range": "30d", "include_forecasts": true, "include_charts": true, "ai_model": "sales_forecasting"}', 1),
('inventory_status', 'Inventory Status Report', 'Current inventory levels and stock analysis', 'inventory', '{"date_range": "7d", "include_predictions": true, "include_alerts": true, "ai_model": "inventory_optimization"}', 1),
('customer_analysis', 'Customer Analysis Report', 'Customer behavior and segmentation analysis', 'customer', '{"date_range": "30d", "include_segments": true, "include_insights": true, "ai_model": "customer_segmentation"}', 1),
('financial_summary', 'Financial Summary Report', 'Financial performance and profitability analysis', 'financial', '{"date_range": "30d", "include_ratios": true, "include_trends": true, "ai_model": "financial_analysis"}', 1);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_advanced_reports_composite` ON `advanced_reports` (`report_type`, `status`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_ai_models_composite` ON `ai_models` (`model_type`, `status`, `last_trained`);
CREATE INDEX IF NOT EXISTS `idx_report_analytics_composite` ON `report_analytics` (`report_id`, `analytics_type`, `confidence_score`);
CREATE INDEX IF NOT EXISTS `idx_report_predictions_composite` ON `report_predictions` (`report_id`, `prediction_type`, `confidence_score`);
CREATE INDEX IF NOT EXISTS `idx_report_recommendations_composite` ON `report_recommendations` (`report_id`, `priority`, `actionable`);
