-- =====================================================
-- SEARCH SYSTEM DATABASE MIGRATION
-- =====================================================
-- Advanced search with indexing, full-text search, and analytics
-- Created: 2026-01-25
-- Version: 1.0

-- =====================================================
-- 1. SEARCH INDEX TABLE
-- =====================================================

-- Main search index table for all searchable content
CREATE TABLE IF NOT EXISTS `search_index` (
    `id_search_index` INT AUTO_INCREMENT PRIMARY KEY,
    `entity_type` ENUM('product', 'customer', 'supplier', 'transaction', 'file', 'member', 'company', 'branch', 'notification', 'audit_log') NOT NULL,
    `entity_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT,
    `summary` TEXT,
    `keywords` TEXT,
    `tags` JSON,
    `metadata` JSON,
    `company_id` INT NULL,
    `branch_id` INT NULL,
    `created_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `indexed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_active` BOOLEAN DEFAULT TRUE,
    `search_weight` DECIMAL(3,2) DEFAULT 1.00,
    `access_level` ENUM('public', 'company', 'branch', 'private') DEFAULT 'company',
    
    -- Full-text search indexes
    FULLTEXT KEY `ft_search_content` (`title`, `content`, `summary`, `keywords`),
    
    -- Regular indexes for performance
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_company_branch` (`company_id`, `branch_id`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_active_weight` (`is_active`, `search_weight`),
    INDEX `idx_access_level` (`access_level`),
    INDEX `idx_indexed_at` (`indexed_at`),
    
    -- Unique constraint to prevent duplicates
    UNIQUE KEY `uk_entity` (`entity_type`, `entity_id`),
    
    -- Foreign key constraints
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE SET NULL,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id_branch`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `members`(`id_member`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. SEARCH QUERIES TABLE
-- =====================================================

-- Track all search queries for analytics and optimization
CREATE TABLE IF NOT EXISTS `search_queries` (
    `id_search_query` INT AUTO_INCREMENT PRIMARY KEY,
    `query_text` VARCHAR(500) NOT NULL,
    `query_hash` VARCHAR(64) NOT NULL,
    `user_id` INT NULL,
    `company_id` INT NULL,
    `branch_id` INT NULL,
    `entity_types` JSON,
    `filters` JSON,
    `sort_by` ENUM('relevance', 'date_desc', 'date_asc', 'title_asc', 'title_desc') DEFAULT 'relevance',
    `results_count` INT DEFAULT 0,
    `execution_time_ms` DECIMAL(10,3) DEFAULT 0,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `session_id` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX `idx_query_hash` (`query_hash`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_company_branch` (`company_id`, `branch_id`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_results_count` (`results_count`),
    
    -- Foreign key constraints
    FOREIGN KEY (`user_id`) REFERENCES `members`(`id_member`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE SET NULL,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id_branch`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. SEARCH RESULTS TABLE
-- =====================================================

-- Track which results were clicked for analytics
CREATE TABLE IF NOT EXISTS `search_results` (
    `id_search_result` INT AUTO_INCREMENT PRIMARY KEY,
    `search_query_id` INT NOT NULL,
    `search_index_id` INT NOT NULL,
    `rank_position` INT NOT NULL,
    `relevance_score` DECIMAL(5,3) DEFAULT 0,
    `clicked` BOOLEAN DEFAULT FALSE,
    `clicked_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX `idx_search_query` (`search_query_id`),
    INDEX `idx_search_index` (`search_index_id`),
    INDEX `idx_rank_position` (`rank_position`),
    INDEX `idx_clicked` (`clicked`),
    INDEX `idx_clicked_at` (`clicked_at`),
    
    -- Foreign key constraints
    FOREIGN KEY (`search_query_id`) REFERENCES `search_queries`(`id_search_query`) ON DELETE CASCADE,
    FOREIGN KEY (`search_index_id`) REFERENCES `search_index`(`id_search_index`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. SEARCH SUGGESTIONS TABLE
-- =====================================================

-- Auto-complete suggestions based on popular searches
CREATE TABLE IF NOT EXISTS `search_suggestions` (
    `id_search_suggestion` INT AUTO_INCREMENT PRIMARY KEY,
    `suggestion_text` VARCHAR(255) NOT NULL,
    `suggestion_type` ENUM('query', 'entity', 'tag', 'keyword') DEFAULT 'query',
    `entity_type` VARCHAR(50) NULL,
    `frequency` INT DEFAULT 1,
    `last_used` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `company_id` INT NULL,
    `branch_id` INT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX `idx_suggestion_text` (`suggestion_text`),
    INDEX `idx_suggestion_type` (`suggestion_type`),
    INDEX `idx_entity_type` (`entity_type`),
    INDEX `idx_frequency` (`frequency`),
    INDEX `idx_last_used` (`last_used`),
    INDEX `idx_company_branch` (`company_id`, `branch_id`),
    INDEX `idx_active` (`is_active`),
    
    -- Foreign key constraints
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE SET NULL,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id_branch`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. SEARCH ANALYTICS TABLE
-- =====================================================

-- Daily/weekly/monthly search analytics
CREATE TABLE IF NOT EXISTS `search_analytics` (
    `id_search_analytics` INT AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL,
    `company_id` INT NULL,
    `branch_id` INT NULL,
    `total_searches` INT DEFAULT 0,
    `unique_users` INT DEFAULT 0,
    `avg_results_per_search` DECIMAL(8,2) DEFAULT 0,
    `avg_execution_time_ms` DECIMAL(10,3) DEFAULT 0,
    `click_through_rate` DECIMAL(5,3) DEFAULT 0,
    `top_queries` JSON,
    `no_result_queries` INT DEFAULT 0,
    `entity_type_breakdown` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX `idx_date` (`date`),
    INDEX `idx_company_branch` (`company_id`, `branch_id`),
    INDEX `idx_total_searches` (`total_searches`),
    INDEX `idx_unique_users` (`unique_users`),
    
    -- Unique constraint to prevent duplicates
    UNIQUE KEY `uk_date_company_branch` (`date`, `company_id`, `branch_id`),
    
    -- Foreign key constraints
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE SET NULL,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id_branch`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. SEARCH CONFIGURATION TABLE
-- =====================================================

-- Search system configuration and settings
CREATE TABLE IF NOT EXISTS `search_settings` (
    `id_search_setting` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    `description` TEXT,
    `company_id` INT NULL,
    `branch_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX `idx_setting_key` (`setting_key`),
    INDEX `idx_company_branch` (`company_id`, `branch_id`),
    
    -- Unique constraint to prevent duplicates
    UNIQUE KEY `uk_setting_key_company_branch` (`setting_key`, `company_id`, `branch_id`),
    
    -- Foreign key constraints
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE CASCADE,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id_branch`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. SEARCH INDEXING QUEUE TABLE
-- =====================================================

-- Queue for background indexing operations
CREATE TABLE IF NOT EXISTS `search_indexing_queue` (
    `id_indexing_queue` INT AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT NOT NULL,
    `operation` ENUM('index', 'update', 'delete') NOT NULL,
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `data` JSON,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `error_message` TEXT,
    `attempts` INT DEFAULT 0,
    `max_attempts` INT DEFAULT 3,
    `scheduled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `processed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX `idx_status_priority` (`status`, `priority`),
    INDEX `idx_scheduled_at` (`scheduled_at`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_operation` (`operation`),
    INDEX `idx_attempts` (`attempts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. INSERT DEFAULT SEARCH SETTINGS
-- =====================================================

INSERT INTO `search_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('search_enabled', 'true', 'boolean', 'Enable search functionality'),
('auto_indexing', 'true', 'boolean', 'Enable automatic content indexing'),
('fulltext_min_word_length', '3', 'number', 'Minimum word length for full-text search'),
('max_search_results', '100', 'number', 'Maximum number of search results to return'),
('search_timeout_seconds', '30', 'number', 'Maximum search execution time in seconds'),
('enable_search_analytics', 'true', 'boolean', 'Enable search analytics tracking'),
('enable_search_suggestions', 'true', 'boolean', 'Enable auto-complete suggestions'),
('suggestion_min_frequency', '3', 'number', 'Minimum frequency for search suggestions'),
('index_batch_size', '100', 'number', 'Number of items to index in one batch'),
('cleanup_old_queries_days', '90', 'number', 'Days to keep old search queries'),
('enable_spell_check', 'false', 'boolean', 'Enable spell checking in search'),
('enable_fuzzy_search', 'true', 'boolean', 'Enable fuzzy matching in search'),
('fuzzy_distance', '2', 'number', 'Maximum edit distance for fuzzy search'),
('boost_recent_content', 'true', 'boolean', 'Boost recent content in search results'),
('recent_content_days', '30', 'number', 'Days to consider content as recent'),
('enable_entity_boosting', 'true', 'boolean', 'Enable entity type boosting'),
('entity_boost_weights', '{"product": 1.2, "transaction": 1.1, "customer": 1.0, "file": 0.9}', 'json', 'Boost weights for different entity types'),
('enable_search_logging', 'true', 'boolean', 'Enable detailed search logging'),
('log_slow_queries', 'true', 'boolean', 'Log slow search queries'),
('slow_query_threshold_ms', '1000', 'number', 'Threshold for slow queries in milliseconds'),
('enable_search_caching', 'true', 'boolean', 'Enable search result caching'),
('cache_ttl_seconds', '300', 'number', 'Cache time-to-live in seconds'),
('max_cache_size', '1000', 'number', 'Maximum number of cached results'),
('enable_search_api', 'true', 'boolean', 'Enable search API endpoints'),
('api_rate_limit_per_minute', '60', 'number', 'API rate limit per minute per user'),
('enable_advanced_filters', 'true', 'boolean', 'Enable advanced search filters'),
('enable_date_range_search', 'true', 'boolean', 'Enable date range search'),
('enable_tag_search', 'true', 'boolean', 'Enable tag-based search'),
('enable_content_preview', 'true', 'boolean', 'Enable content preview in results'),
('preview_length', '200', 'number', 'Length of content preview in characters'),
('enable_search_export', 'true', 'boolean', 'Enable search results export'),
('export_max_results', '10000', 'number', 'Maximum results for export'),
('enable_search_history', 'true', 'boolean', 'Enable user search history'),
('history_max_entries', '50', 'number', 'Maximum search history entries per user');

-- =====================================================
-- 9. CREATE VIEWS FOR SEARCH ANALYTICS
-- =====================================================

-- Popular searches view
CREATE OR REPLACE VIEW `v_popular_searches` AS
SELECT 
    query_text,
    COUNT(*) as search_count,
    COUNT(DISTINCT user_id) as unique_users,
    AVG(results_count) as avg_results,
    AVG(execution_time_ms) as avg_execution_time,
    MAX(created_at) as last_searched
FROM search_queries
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY query_text
HAVING search_count >= 3
ORDER BY search_count DESC, last_searched DESC;

-- Search performance view
CREATE OR REPLACE VIEW `v_search_performance` AS
SELECT 
    DATE(created_at) as search_date,
    COUNT(*) as total_searches,
    COUNT(DISTINCT user_id) as unique_users,
    AVG(results_count) as avg_results,
    AVG(execution_time_ms) as avg_execution_time,
    SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) as no_result_searches,
    COUNT(CASE WHEN execution_time_ms > 1000 THEN 1 END) as slow_searches
FROM search_queries
GROUP BY DATE(created_at)
ORDER BY search_date DESC;

-- Entity search statistics view
CREATE OR REPLACE VIEW `v_entity_search_stats` AS
SELECT 
    entity_type,
    COUNT(*) as total_searches,
    COUNT(DISTINCT user_id) as unique_users,
    AVG(results_count) as avg_results,
    AVG(execution_time_ms) as avg_execution_time
FROM search_queries sq
JOIN search_results sr ON sq.id_search_query = sr.id_search_query
JOIN search_index si ON sr.search_index_id = si.id_search_index
GROUP BY entity_type
ORDER BY total_searches DESC;

-- =====================================================
-- 10. STORED PROCEDURES FOR SEARCH OPERATIONS
-- =====================================================

DELIMITER //

-- Procedure to add/update search index
CREATE PROCEDURE `sp_update_search_index`(
    IN p_entity_type VARCHAR(50),
    IN p_entity_id INT,
    IN p_title VARCHAR(255),
    IN p_content TEXT,
    IN p_summary TEXT,
    IN p_keywords TEXT,
    IN p_tags JSON,
    IN p_metadata JSON,
    IN p_company_id INT,
    IN p_branch_id INT,
    IN p_created_by INT,
    IN p_search_weight DECIMAL(3,2),
    IN p_access_level VARCHAR(20)
)
BEGIN
    DECLARE v_exists INT DEFAULT 0;
    
    -- Check if record exists
    SELECT COUNT(*) INTO v_exists 
    FROM search_index 
    WHERE entity_type = p_entity_type AND entity_id = p_entity_id;
    
    IF v_exists > 0 THEN
        -- Update existing record
        UPDATE search_index SET
            title = p_title,
            content = p_content,
            summary = p_summary,
            keywords = p_keywords,
            tags = p_tags,
            metadata = p_metadata,
            company_id = p_company_id,
            branch_id = p_branch_id,
            created_by = p_created_by,
            search_weight = p_search_weight,
            access_level = p_access_level,
            indexed_at = CURRENT_TIMESTAMP,
            is_active = TRUE
        WHERE entity_type = p_entity_type AND entity_id = p_entity_id;
    ELSE
        -- Insert new record
        INSERT INTO search_index (
            entity_type, entity_id, title, content, summary, keywords, 
            tags, metadata, company_id, branch_id, created_by, 
            search_weight, access_level
        ) VALUES (
            p_entity_type, p_entity_id, p_title, p_content, p_summary, p_keywords,
            p_tags, p_metadata, p_company_id, p_branch_id, p_created_by,
            p_search_weight, p_access_level
        );
    END IF;
END //

-- Procedure to remove from search index
CREATE PROCEDURE `sp_remove_search_index`(
    IN p_entity_type VARCHAR(50),
    IN p_entity_id INT
)
BEGIN
    UPDATE search_index 
    SET is_active = FALSE 
    WHERE entity_type = p_entity_type AND entity_id = p_entity_id;
END //

-- Procedure to log search query
CREATE PROCEDURE `sp_log_search_query`(
    IN p_query_text VARCHAR(500),
    IN p_query_hash VARCHAR(64),
    IN p_user_id INT,
    IN p_company_id INT,
    IN p_branch_id INT,
    IN p_entity_types JSON,
    IN p_filters JSON,
    IN p_sort_by VARCHAR(20),
    IN p_results_count INT,
    IN p_execution_time_ms DECIMAL(10,3),
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT,
    IN p_session_id VARCHAR(255)
)
BEGIN
    INSERT INTO search_queries (
        query_text, query_hash, user_id, company_id, branch_id,
        entity_types, filters, sort_by, results_count, execution_time_ms,
        ip_address, user_agent, session_id
    ) VALUES (
        p_query_text, p_query_hash, p_user_id, p_company_id, p_branch_id,
        p_entity_types, p_filters, p_sort_by, p_results_count, p_execution_time_ms,
        p_ip_address, p_user_agent, p_session_id
    );
    
    -- Update search suggestions
    INSERT INTO search_suggestions (suggestion_text, frequency, company_id, branch_id)
    VALUES (p_query_text, 1, p_company_id, p_branch_id)
    ON DUPLICATE KEY UPDATE 
        frequency = frequency + 1,
        last_used = CURRENT_TIMESTAMP;
END //

-- Procedure to generate daily analytics
CREATE PROCEDURE `sp_generate_search_analytics`(
    IN p_analytics_date DATE,
    IN p_company_id INT,
    IN p_branch_id INT
)
BEGIN
    DECLARE v_total_searches INT DEFAULT 0;
    DECLARE v_unique_users INT DEFAULT 0;
    DECLARE v_avg_results DECIMAL(8,2) DEFAULT 0;
    DECLARE v_avg_execution_time DECIMAL(10,3) DEFAULT 0;
    DECLARE v_click_through_rate DECIMAL(5,3) DEFAULT 0;
    DECLARE v_no_result_queries INT DEFAULT 0;
    DECLARE v_top_queries JSON;
    DECLARE v_entity_type_breakdown JSON;
    
    -- Calculate statistics
    SELECT 
        COUNT(*) INTO v_total_searches,
        COUNT(DISTINCT user_id) INTO v_unique_users,
        AVG(results_count) INTO v_avg_results,
        AVG(execution_time_ms) INTO v_avg_execution_time,
        SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) INTO v_no_result_queries
    FROM search_queries 
    WHERE DATE(created_at) = p_analytics_date
        AND (p_company_id IS NULL OR company_id = p_company_id)
        AND (p_branch_id IS NULL OR branch_id = p_branch_id);
    
    -- Get top queries
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'query', query_text,
            'count', search_count
        )
    ) INTO v_top_queries
    FROM (
        SELECT query_text, COUNT(*) as search_count
        FROM search_queries 
        WHERE DATE(created_at) = p_analytics_date
            AND (p_company_id IS NULL OR company_id = p_company_id)
            AND (p_branch_id IS NULL OR branch_id = p_branch_id)
        GROUP BY query_text
        ORDER BY search_count DESC
        LIMIT 10
    ) top_queries;
    
    -- Get entity type breakdown
    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'entity_type', entity_type,
            'count', entity_count
        )
    ) INTO v_entity_type_breakdown
    FROM (
        SELECT si.entity_type, COUNT(*) as entity_count
        FROM search_queries sq
        JOIN search_results sr ON sq.id_search_query = sr.id_search_query
        JOIN search_index si ON sr.search_index_id = si.id_search_index
        WHERE DATE(sq.created_at) = p_analytics_date
            AND (p_company_id IS NULL OR sq.company_id = p_company_id)
            AND (p_branch_id IS NULL OR sq.branch_id = p_branch_id)
        GROUP BY si.entity_type
    ) entity_stats;
    
    -- Insert or update analytics
    INSERT INTO search_analytics (
        date, company_id, branch_id, total_searches, unique_users,
        avg_results_per_search, avg_execution_time_ms, click_through_rate,
        top_queries, no_result_queries, entity_type_breakdown
    ) VALUES (
        p_analytics_date, p_company_id, p_branch_id, v_total_searches, v_unique_users,
        v_avg_results, v_avg_execution_time, v_click_through_rate,
        v_top_queries, v_no_result_queries, v_entity_type_breakdown
    )
    ON DUPLICATE KEY UPDATE
        total_searches = v_total_searches,
        unique_users = v_unique_users,
        avg_results_per_search = v_avg_results,
        avg_execution_time_ms = v_avg_execution_time,
        click_through_rate = v_click_through_rate,
        top_queries = v_top_queries,
        no_result_queries = v_no_result_queries,
        entity_type_breakdown = v_entity_type_breakdown,
        updated_at = CURRENT_TIMESTAMP;
END //

DELIMITER ;

-- =====================================================
-- 11. TRIGGERS FOR AUTOMATIC INDEXING
-- =====================================================

-- Trigger for products
DELIMITER //
CREATE TRIGGER `tr_product_search_index` 
AFTER INSERT ON `products` 
FOR EACH ROW
BEGIN
    INSERT INTO search_indexing_queue (
        entity_type, entity_id, operation, priority, data
    ) VALUES (
        'product', NEW.id_product, 'index', 'normal',
        JSON_OBJECT(
            'name', NEW.product_name,
            'description', NEW.description,
            'sku', NEW.sku,
            'category', NEW.category,
            'price', NEW.price
        )
    );
END //

CREATE TRIGGER `tr_product_search_index_update` 
AFTER UPDATE ON `products` 
FOR EACH ROW
BEGIN
    INSERT INTO search_indexing_queue (
        entity_type, entity_id, operation, priority, data
    ) VALUES (
        'product', NEW.id_product, 'update', 'normal',
        JSON_OBJECT(
            'name', NEW.product_name,
            'description', NEW.description,
            'sku', NEW.sku,
            'category', NEW.category,
            'price', NEW.price
        )
    );
END //

CREATE TRIGGER `tr_product_search_index_delete` 
AFTER DELETE ON `products` 
FOR EACH ROW
BEGIN
    INSERT INTO search_indexing_queue (
        entity_type, entity_id, operation, priority
    ) VALUES (
        'product', OLD.id_product, 'delete', 'high'
    );
END //
DELIMITER ;

-- =====================================================
-- 12. INDEX OPTIMIZATION
-- =====================================================

-- Create composite indexes for common search patterns
ALTER TABLE search_index 
ADD INDEX `idx_entity_active_weight` (`entity_type`, `is_active`, `search_weight`),
ADD INDEX `idx_company_active_date` (`company_id`, `is_active`, `indexed_at`),
ADD INDEX `idx_branch_active_date` (`branch_id`, `is_active`, `indexed_at`);

ALTER TABLE search_queries 
ADD INDEX `idx_user_date` (`user_id`, `created_at`),
ADD INDEX `idx_company_date` (`company_id`, `created_at`),
ADD INDEX `idx_results_time` (`results_count`, `execution_time_ms`);

-- =====================================================
-- MIGRATION COMPLETION
-- =====================================================

-- Log migration completion
INSERT INTO system_logs (log_type, log_message, created_at) 
VALUES ('migration', 'Search system tables created successfully', NOW());

-- Update migration tracking
INSERT INTO migration_history (migration_name, version, executed_at) 
VALUES ('create_search_system_tables', '1.0', NOW())
ON DUPLICATE KEY UPDATE executed_at = NOW();
