<?php

/**
 * Search Model
 * 
 * Handles advanced search functionality with full-text search,
 * indexing, analytics, and intelligent suggestions
 */

class Search {
    private $db;
    private $search_index_table = 'search_index';
    private $search_queries_table = 'search_queries';
    private $search_results_table = 'search_results';
    private $search_suggestions_table = 'search_suggestions';
    private $search_analytics_table = 'search_analytics';
    private $search_settings_table = 'search_settings';
    private $indexing_queue_table = 'search_indexing_queue';

    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }

    private $settings = [];

    /**
     * Load search settings
     */
    private function loadSettings() {
        $results = $this->db->query("SELECT setting_key, setting_value, setting_type FROM {$this->search_settings_table}");
        
        foreach ($results as $setting) {
            $value = $setting['setting_value'];
            
            switch ($setting['setting_type']) {
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'number':
                    $value = is_numeric($value) ? (float)$value : 0;
                    break;
                case 'json':
                    $value = json_decode($value, true) ?: [];
                    break;
            }
            
            $this->settings[$setting['setting_key']] = $value;
        }
    }

    /**
     * Get setting value
     */
    public function getSetting($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Update search index
     */
    public function updateIndex($entityType, $entityId, $title, $content = '', $summary = '', $keywords = '', $tags = [], $metadata = [], $companyId = null, $branchId = null, $createdBy = null, $searchWeight = 1.0, $accessLevel = 'company') {
        $data = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'title' => $title,
            'content' => $content,
            'summary' => $summary,
            'keywords' => $keywords,
            'tags' => json_encode($tags),
            'metadata' => json_encode($metadata),
            'company_id' => $companyId ?? $_SESSION['company_id'] ?? null,
            'branch_id' => $branchId ?? $_SESSION['branch_id'] ?? null,
            'created_by' => $createdBy ?? $_SESSION['user_id'] ?? null,
            'search_weight' => $searchWeight,
            'access_level' => $accessLevel,
            'indexed_at' => date('Y-m-d H:i:s'),
            'is_active' => true
        ];

        // Use stored procedure for atomic operation
        $this->db->query("CALL sp_update_search_index(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $entityType, $entityId, $title, $content, $summary, $keywords,
            json_encode($tags), json_encode($metadata), $companyId, $branchId, 
            $createdBy, $searchWeight, $accessLevel
        ]);

        return true;
    }

    /**
     * Remove from search index
     */
    public function removeFromIndex($entityType, $entityId) {
        $this->db->query("CALL sp_remove_search_index(?, ?)", [$entityType, $entityId]);
        return true;
    }

    /**
     * Perform search
     */
    public function search($query, $filters = [], $options = []) {
        $startTime = microtime(true);
        
        // Default options
        $defaultOptions = [
            'limit' => $this->getSetting('max_search_results', 100),
            'offset' => 0,
            'sort_by' => 'relevance',
            'entity_types' => null,
            'company_id' => $_SESSION['company_id'] ?? null,
            'branch_id' => $_SESSION['branch_id'] ?? null,
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // Validate search is enabled
        if (!$this->getSetting('search_enabled', true)) {
            return [
                'results' => [],
                'total' => 0,
                'execution_time' => 0,
                'query' => $query
            ];
        }

        // Build search query
        $sql = $this->buildSearchQuery($query, $filters, $options);
        $params = $this->buildSearchParams($query, $filters, $options);
        
        // Execute search
        $results = $this->db->query($sql, $params);
        $total = $this->getSearchCount($query, $filters, $options);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        // Log search query
        $this->logSearchQuery($query, $results, $executionTime, $options);
        
        return [
            'results' => $results,
            'total' => $total,
            'execution_time' => round($executionTime, 3),
            'query' => $query,
            'filters' => $filters,
            'options' => $options
        ];
    }

    /**
     * Build search SQL query
     */
    private function buildSearchQuery($query, $filters, $options) {
        $sql = "SELECT si.*, ";
        
        // Add relevance score
        if ($options['sort_by'] === 'relevance') {
            $sql .= "MATCH(si.title, si.content, si.summary, si.keywords) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance_score, ";
            $sql .= "(si.search_weight * ";
            if ($this->getSetting('boost_recent_content', true)) {
                $recentDays = $this->getSetting('recent_content_days', 30);
                $sql .= "CASE WHEN si.indexed_at >= DATE_SUB(NOW(), INTERVAL {$recentDays} DAY) THEN 1.2 ELSE 1.0 END * ";
            }
            $sql .= "1.0) as final_score ";
        } else {
            $sql .= "1 as relevance_score ";
        }
        
        $sql .= "FROM {$this->search_index_table} si ";
        $sql .= "WHERE si.is_active = TRUE ";
        
        // Add full-text search condition
        if (!empty($query)) {
            $sql .= "AND MATCH(si.title, si.content, si.summary, si.keywords) AGAINST(? IN NATURAL LANGUAGE MODE) ";
        }
        
        // Add entity type filter
        if (!empty($options['entity_types'])) {
            $placeholders = str_repeat('?,', count($options['entity_types']) - 1) . '?';
            $sql .= "AND si.entity_type IN ({$placeholders}) ";
        }
        
        // Add company/branch filters
        if ($options['company_id']) {
            $sql .= "AND (si.company_id = ? OR si.access_level = 'public') ";
        }
        
        if ($options['branch_id']) {
            $sql .= "AND (si.branch_id = ? OR si.access_level IN ('public', 'company')) ";
        }
        
        // Add custom filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                switch ($key) {
                    case 'date_from':
                        $sql .= "AND si.created_at >= ? ";
                        break;
                    case 'date_to':
                        $sql .= "AND si.created_at <= ? ";
                        break;
                    case 'created_by':
                        $sql .= "AND si.created_by = ? ";
                        break;
                    case 'access_level':
                        $sql .= "AND si.access_level = ? ";
                        break;
                }
            }
        }
        
        // Add sorting
        switch ($options['sort_by']) {
            case 'relevance':
                $sql .= "ORDER BY final_score DESC, si.indexed_at DESC ";
                break;
            case 'date_desc':
                $sql .= "ORDER BY si.created_at DESC ";
                break;
            case 'date_asc':
                $sql .= "ORDER BY si.created_at ASC ";
                break;
            case 'title_asc':
                $sql .= "ORDER BY si.title ASC ";
                break;
            case 'title_desc':
                $sql .= "ORDER BY si.title DESC ";
                break;
            default:
                $sql .= "ORDER BY si.indexed_at DESC ";
        }
        
        // Add pagination
        $sql .= "LIMIT ? OFFSET ?";
        
        return $sql;
    }

    /**
     * Build search parameters
     */
    private function buildSearchParams($query, $filters, $options) {
        $params = [];
        
        // Add query for full-text search
        if (!empty($query)) {
            $params[] = $query;
        }
        
        // Add query for relevance calculation
        if ($options['sort_by'] === 'relevance' && !empty($query)) {
            $params[] = $query;
        }
        
        // Add entity types
        if (!empty($options['entity_types'])) {
            $params = array_merge($params, $options['entity_types']);
        }
        
        // Add company/branch filters
        if ($options['company_id']) {
            $params[] = $options['company_id'];
        }
        
        if ($options['branch_id']) {
            $params[] = $options['branch_id'];
        }
        
        // Add custom filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                switch ($key) {
                    case 'date_from':
                    case 'date_to':
                    case 'created_by':
                    case 'access_level':
                        $params[] = $value;
                        break;
                }
            }
        }
        
        // Add pagination
        $params[] = (int)$options['limit'];
        $params[] = (int)$options['offset'];
        
        return $params;
    }

    /**
     * Get total search count
     */
    private function getSearchCount($query, $filters, $options) {
        $sql = "SELECT COUNT(*) as total FROM {$this->search_index_table} si WHERE si.is_active = TRUE ";
        $params = [];
        
        // Add full-text search condition
        if (!empty($query)) {
            $sql .= "AND MATCH(si.title, si.content, si.summary, si.keywords) AGAINST(? IN NATURAL LANGUAGE MODE) ";
            $params[] = $query;
        }
        
        // Add entity type filter
        if (!empty($options['entity_types'])) {
            $placeholders = str_repeat('?,', count($options['entity_types']) - 1) . '?';
            $sql .= "AND si.entity_type IN ({$placeholders}) ";
            $params = array_merge($params, $options['entity_types']);
        }
        
        // Add company/branch filters
        if ($options['company_id']) {
            $sql .= "AND (si.company_id = ? OR si.access_level = 'public') ";
            $params[] = $options['company_id'];
        }
        
        if ($options['branch_id']) {
            $sql .= "AND (si.branch_id = ? OR si.access_level IN ('public', 'company')) ";
            $params[] = $options['branch_id'];
        }
        
        // Add custom filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                switch ($key) {
                    case 'date_from':
                        $sql .= "AND si.created_at >= ? ";
                        $params[] = $value;
                        break;
                    case 'date_to':
                        $sql .= "AND si.created_at <= ? ";
                        $params[] = $value;
                        break;
                    case 'created_by':
                        $sql .= "AND si.created_by = ? ";
                        $params[] = $value;
                        break;
                    case 'access_level':
                        $sql .= "AND si.access_level = ? ";
                        $params[] = $value;
                        break;
                }
            }
        }
        
        $result = $this->db->query($sql, $params);
        return $result[0]['total'] ?? 0;
    }

    /**
     * Log search query
     */
    private function logSearchQuery($query, $results, $executionTime, $options) {
        if (!$this->getSetting('enable_search_logging', true)) {
            return;
        }
        
        $queryHash = md5($query . json_encode($options['entity_types']) . json_encode($options['filters']));
        
        $this->db->query("CALL sp_log_search_query(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $query,
            $queryHash,
            $options['user_id'],
            $options['company_id'],
            $options['branch_id'],
            json_encode($options['entity_types']),
            json_encode($options['filters']),
            $options['sort_by'],
            count($results),
            $executionTime,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            session_id()
        ]);
    }

    /**
     * Get search suggestions
     */
    public function getSuggestions($query, $limit = 10, $entityType = null) {
        if (!$this->getSetting('enable_search_suggestions', true)) {
            return [];
        }
        
        $sql = "SELECT suggestion_text, suggestion_type, entity_type, frequency 
                FROM {$this->search_suggestions_table} 
                WHERE is_active = TRUE 
                AND suggestion_text LIKE ? 
                AND frequency >= ?";
        
        $params = ["%{$query}%", $this->getSetting('suggestion_min_frequency', 3)];
        
        if ($entityType) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }
        
        if ($_SESSION['company_id']) {
            $sql .= " AND (company_id = ? OR company_id IS NULL)";
            $params[] = $_SESSION['company_id'];
        }
        
        $sql .= " ORDER BY frequency DESC, last_used DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params);
    }

    /**
     * Get popular searches
     */
    public function getPopularSearches($limit = 10, $days = 30) {
        $sql = "SELECT query_text, COUNT(*) as search_count, 
                       COUNT(DISTINCT user_id) as unique_users,
                       AVG(results_count) as avg_results
                FROM {$this->search_queries_table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $params = [$days];
        
        if ($_SESSION['company_id']) {
            $sql .= " AND company_id = ?";
            $params[] = $_SESSION['company_id'];
        }
        
        $sql .= " GROUP BY query_text 
                  HAVING search_count >= 3 
                  ORDER BY search_count DESC, MAX(created_at) DESC 
                  LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params);
    }

    /**
     * Get search analytics
     */
    public function getAnalytics($dateRange = '7days') {
        $where = "";
        $params = [];
        
        switch ($dateRange) {
            case 'today':
                $where = "DATE(created_at) = CURDATE()";
                break;
            case '7days':
                $where = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $where = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case '90days':
                $where = "created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
                break;
        }
        
        if ($_SESSION['company_id']) {
            $where .= ($where ? " AND " : "") . "company_id = ?";
            $params[] = $_SESSION['company_id'];
        }
        
        $sql = "SELECT * FROM {$this->search_analytics_table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY date DESC";
        
        return $this->db->query($sql, $params);
    }

    /**
     * Get search performance metrics
     */
    public function getPerformanceMetrics($dateRange = '7days') {
        $where = "";
        $params = [];
        
        switch ($dateRange) {
            case 'today':
                $where = "DATE(created_at) = CURDATE()";
                break;
            case '7days':
                $where = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $where = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
        
        if ($_SESSION['company_id']) {
            $where .= ($where ? " AND " : "") . "company_id = ?";
            $params[] = $_SESSION['company_id'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_searches,
                    COUNT(DISTINCT user_id) as unique_users,
                    AVG(results_count) as avg_results,
                    AVG(execution_time_ms) as avg_execution_time,
                    MAX(execution_time_ms) as max_execution_time,
                    SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) as no_result_searches,
                    COUNT(CASE WHEN execution_time_ms > 1000 THEN 1 END) as slow_searches
                FROM {$this->search_queries_table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->db->query($sql, $params);
        return $result[0] ?? [];
    }

    /**
     * Get entity type statistics
     */
    public function getEntityTypeStats($dateRange = '7days') {
        $where = "";
        $params = [];
        
        switch ($dateRange) {
            case 'today':
                $where = "DATE(sq.created_at) = CURDATE()";
                break;
            case '7days':
                $where = "sq.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $where = "sq.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
        
        if ($_SESSION['company_id']) {
            $where .= ($where ? " AND " : "") . "sq.company_id = ?";
            $params[] = $_SESSION['company_id'];
        }
        
        $sql = "SELECT 
                    si.entity_type,
                    COUNT(*) as total_searches,
                    COUNT(DISTINCT sq.user_id) as unique_users,
                    AVG(sq.results_count) as avg_results,
                    AVG(sq.execution_time_ms) as avg_execution_time
                FROM {$this->search_queries_table} sq
                JOIN {$this->search_results_table} sr ON sq.id_search_query = sr.id_search_query
                JOIN {$this->search_index_table} si ON sr.search_index_id = si.id_search_index";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $sql .= " GROUP BY si.entity_type ORDER BY total_searches DESC";
        
        return $this->db->query($sql, $params);
    }

    /**
     * Process indexing queue
     */
    public function processIndexingQueue($batchSize = 100) {
        $sql = "SELECT * FROM {$this->indexing_queue_table} 
                WHERE status = 'pending' 
                ORDER BY priority DESC, scheduled_at ASC 
                LIMIT ?";
        
        $queueItems = $this->db->query($sql, [$batchSize]);
        
        foreach ($queueItems as $item) {
            $this->processQueueItem($item);
        }
        
        return count($queueItems);
    }

    /**
     * Process individual queue item
     */
    private function processQueueItem($item) {
        try {
            // Mark as processing
            $this->db->update($this->indexing_queue_table, 
                ['status' => 'processing', 'attempts' => $item['attempts'] + 1],
                "id_indexing_queue = ?", 
                [$item['id_indexing_queue']]
            );
            
            // Process based on operation
            switch ($item['operation']) {
                case 'index':
                case 'update':
                    $this->processIndexUpdate($item);
                    break;
                case 'delete':
                    $this->processIndexDelete($item);
                    break;
            }
            
            // Mark as completed
            $this->db->update($this->indexing_queue_table,
                ['status' => 'completed', 'processed_at' => date('Y-m-d H:i:s')],
                "id_indexing_queue = ?",
                [$item['id_indexing_queue']]
            );
            
        } catch (Exception $e) {
            // Mark as failed if max attempts reached
            if ($item['attempts'] + 1 >= $item['max_attempts']) {
                $this->db->update($this->indexing_queue_table,
                    ['status' => 'failed', 'error_message' => $e->getMessage()],
                    "id_indexing_queue = ?",
                    [$item['id_indexing_queue']]
                );
            } else {
                // Reset to pending for retry
                $this->db->update($this->indexing_queue_table,
                    ['status' => 'pending', 'error_message' => $e->getMessage()],
                    "id_indexing_queue = ?",
                    [$item['id_indexing_queue']]
                );
            }
        }
    }

    /**
     * Process index update
     */
    private function processIndexUpdate($item) {
        $data = json_decode($item['data'], true);
        
        // Get entity-specific data based on type
        switch ($item['entity_type']) {
            case 'product':
                $this->indexProduct($item['entity_id'], $data);
                break;
            case 'customer':
                $this->indexCustomer($item['entity_id'], $data);
                break;
            case 'transaction':
                $this->indexTransaction($item['entity_id'], $data);
                break;
            case 'file':
                $this->indexFile($item['entity_id'], $data);
                break;
            // Add more entity types as needed
        }
    }

    /**
     * Process index delete
     */
    private function processIndexDelete($item) {
        $this->removeFromIndex($item['entity_type'], $item['entity_id']);
    }

    /**
     * Index product
     */
    private function indexProduct($productId, $data) {
        $this->updateIndex(
            'product',
            $productId,
            $data['name'] ?? '',
            $data['description'] ?? '',
            '',
            $data['sku'] ?? '',
            ['category' => $data['category'] ?? ''],
            $data,
            null,
            null,
            null,
            1.2, // Higher weight for products
            'company'
        );
    }

    /**
     * Index customer
     */
    private function indexCustomer($customerId, $data) {
        $this->updateIndex(
            'customer',
            $customerId,
            $data['name'] ?? '',
            $data['address'] ?? '',
            '',
            $data['phone'] ?? '',
            ['email' => $data['email'] ?? ''],
            $data,
            null,
            null,
            null,
            1.0,
            'company'
        );
    }

    /**
     * Index transaction
     */
    private function indexTransaction($transactionId, $data) {
        $this->updateIndex(
            'transaction',
            $transactionId,
            $data['reference'] ?? '',
            $data['notes'] ?? '',
            '',
            $data['type'] ?? '',
            ['amount' => $data['amount'] ?? 0],
            $data,
            null,
            null,
            null,
            1.1,
            'company'
        );
    }

    /**
     * Index file
     */
    private function indexFile($fileId, $data) {
        $this->updateIndex(
            'file',
            $fileId,
            $data['filename'] ?? '',
            $data['content'] ?? '',
            $data['description'] ?? '',
            implode(' ', $data['tags'] ?? []),
            $data['tags'] ?? [],
            $data,
            null,
            null,
            null,
            0.9,
            'company'
        );
    }

    /**
     * Generate daily analytics
     */
    public function generateDailyAnalytics($date = null) {
        $date = $date ?? date('Y-m-d');
        
        $this->db->query("CALL sp_generate_search_analytics(?, ?, ?)", [
            $date,
            $_SESSION['company_id'] ?? null,
            $_SESSION['branch_id'] ?? null
        ]);
        
        return true;
    }

    /**
     * Clean up old search data
     */
    public function cleanup($days = 90) {
        $cleanupDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Clean up old search queries
        $this->db->query(
            "DELETE FROM {$this->search_queries_table} WHERE created_at < ?",
            [$cleanupDate]
        );
        
        // Clean up old search results
        $this->db->query(
            "DELETE sr FROM {$this->search_results_table} sr
             INNER JOIN {$this->search_queries_table} sq ON sr.search_query_id = sq.id_search_query
             WHERE sq.created_at < ?",
            [$cleanupDate]
        );
        
        // Clean up old suggestions with low frequency
        $this->db->query(
            "DELETE FROM {$this->search_suggestions_table} 
             WHERE frequency < 3 AND last_used < ?",
            [$cleanupDate]
        );
        
        return true;
    }

    /**
     * Get search settings
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * Update search setting
     */
    public function updateSetting($key, $value, $type = 'string') {
        $data = [
            'setting_value' => is_array($value) ? json_encode($value) : $value,
            'setting_type' => $type,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $existing = $this->db->query(
            "SELECT id_search_setting FROM {$this->search_settings_table} WHERE setting_key = ?",
            [$key]
        );
        
        if ($existing) {
            $this->db->update($this->search_settings_table, $data, "setting_key = ?", [$key]);
        } else {
            $data['setting_key'] = $key;
            $this->db->insert($this->search_settings_table, $data);
        }
        
        // Reload settings
        $this->loadSettings();
        
        return true;
    }
}
