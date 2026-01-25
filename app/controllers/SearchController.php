<?php

/**
 * Search Controller
 * 
 * Handles search requests, advanced search features,
 * analytics, and search management
 */

require_once __DIR__ . '/../core/Controller.php';

class SearchController extends Controller {
    
    private $search;
    
    public function __construct() {
        parent::__construct();
        $this->search = new Search();
    }
    
    /**
     * Main search page
     */
    public function index() {
        $this->requireAuth();
        
        $data = [
            'title' => 'Advanced Search',
            'popular_searches' => $this->search->getPopularSearches(10),
            'search_analytics' => $this->search->getAnalytics('7days'),
            'performance_metrics' => $this->search->getPerformanceMetrics('7days'),
            'entity_stats' => $this->search->getEntityTypeStats('7days'),
            'settings' => $this->search->getSettings()
        ];
        
        $this->view('search/index', $data);
    }
    
    /**
     * Perform search
     */
    public function performSearch() {
        $this->requireAuth();
        
        $query = $_GET['q'] ?? '';
        $filters = $_GET['filters'] ?? [];
        $options = [
            'limit' => (int)($_GET['limit'] ?? $this->search->getSetting('max_search_results', 100)),
            'offset' => (int)($_GET['offset'] ?? 0),
            'sort_by' => $_GET['sort_by'] ?? 'relevance',
            'entity_types' => $_GET['entity_types'] ?? null,
            'user_id' => $_SESSION['user_id'],
            'company_id' => $this->getUserCompanyId(),
            'branch_id' => $this->getUserBranchId()
        ];
        
        // Validate query length
        if (strlen($query) < $this->search->getSetting('fulltext_min_word_length', 3)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Search query must be at least ' . $this->search->getSetting('fulltext_min_word_length', 3) . ' characters long'
            ], 400);
            return;
        }
        
        // Validate limit
        $maxResults = $this->search->getSetting('max_search_results', 100);
        if ($options['limit'] > $maxResults) {
            $options['limit'] = $maxResults;
        }
        
        // Perform search
        $results = $this->search->search($query, $filters, $options);
        
        // Format results for display
        $formattedResults = $this->formatSearchResults($results['results']);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'results' => $formattedResults,
                'total' => $results['total'],
                'execution_time' => $results['execution_time'],
                'query' => $results['query'],
                'filters' => $results['filters'],
                'options' => $results['options'],
                'pagination' => [
                    'current_page' => floor($options['offset'] / $options['limit']) + 1,
                    'per_page' => $options['limit'],
                    'total_pages' => ceil($results['total'] / $options['limit']),
                    'has_next' => ($options['offset'] + $options['limit']) < $results['total'],
                    'has_prev' => $options['offset'] > 0
                ]
            ]
        ]);
    }
    
    /**
     * Get search suggestions
     */
    public function getSuggestions() {
        $this->requireAuth();
        
        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 10);
        $entityType = $_GET['entity_type'] ?? null;
        
        if (strlen($query) < 2) {
            $this->jsonResponse(['status' => 'success', 'data' => []]);
            return;
        }
        
        $suggestions = $this->search->getSuggestions($query, $limit, $entityType);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $suggestions
        ]);
    }
    
    /**
     * Get popular searches
     */
    public function getPopularSearches() {
        $this->requireAuth();
        
        $limit = (int)($_GET['limit'] ?? 10);
        $days = (int)($_GET['days'] ?? 30);
        
        $popularSearches = $this->search->getPopularSearches($limit, $days);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $popularSearches
        ]);
    }
    
    /**
     * Get search analytics
     */
    public function getAnalytics() {
        $this->requireAuth();
        
        // Check permissions
        if (!$this->hasPermission(ROLE_ADMIN)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }
        
        $dateRange = $_GET['date_range'] ?? '7days';
        $analytics = $this->search->getAnalytics($dateRange);
        $performanceMetrics = $this->search->getPerformanceMetrics($dateRange);
        $entityStats = $this->search->getEntityTypeStats($dateRange);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'analytics' => $analytics,
                'performance_metrics' => $performanceMetrics,
                'entity_stats' => $entityStats
            ]
        ]);
    }
    
    /**
     * Get search settings
     */
    public function getSettings() {
        $this->requireAuth();
        
        // Check permissions
        if (!$this->hasPermission(ROLE_ADMIN)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }
        
        $settings = $this->search->getSettings();
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $settings
        ]);
    }
    
    /**
     * Update search settings
     */
    public function updateSettings() {
        $this->requireAuth();
        $this->requirePermission(ROLE_ADMIN);
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $settings = $_POST['settings'] ?? [];
        
        if (empty($settings)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Settings data is required'], 400);
            return;
        }
        
        // Update each setting
        foreach ($settings as $key => $value) {
            $type = 'string';
            if (is_bool($value)) {
                $type = 'boolean';
            } elseif (is_numeric($value)) {
                $type = 'number';
            } elseif (is_array($value)) {
                $type = 'json';
            }
            
            $this->search->updateSetting($key, $value, $type);
        }
        
        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Search settings updated successfully',
            'data' => $this->search->getSettings()
        ]);
    }
    
    /**
     * Rebuild search index
     */
    public function rebuildIndex() {
        $this->requireAuth();
        $this->requirePermission(ROLE_ADMIN);
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $entityType = $_POST['entity_type'] ?? null;
        $batchSize = (int)($_POST['batch_size'] ?? 100);
        
        try {
            // Process indexing queue
            $processed = $this->search->processIndexingQueue($batchSize);
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => "Processed {$processed} items in indexing queue",
                'data' => ['processed' => $processed]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to rebuild index: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clean up old search data
     */
    public function cleanup() {
        $this->requireAuth();
        $this->requirePermission(ROLE_ADMIN);
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $days = (int)($_POST['days'] ?? 90);
        
        try {
            $this->search->cleanup($days);
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => "Cleaned up search data older than {$days} days"
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to cleanup search data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate analytics report
     */
    public function generateAnalytics() {
        $this->requireAuth();
        $this->requirePermission(ROLE_ADMIN);
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $date = $_POST['date'] ?? date('Y-m-d');
        
        try {
            $this->search->generateDailyAnalytics($date);
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => "Analytics generated for {$date}"
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to generate analytics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export search results
     */
    public function exportResults() {
        $this->requireAuth();
        
        $query = $_GET['q'] ?? '';
        $format = $_GET['format'] ?? 'csv';
        $filters = $_GET['filters'] ?? [];
        
        if (empty($query)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Search query is required'], 400);
            return;
        }
        
        // Get all results (increase limit for export)
        $options = [
            'limit' => $this->search->getSetting('export_max_results', 10000),
            'offset' => 0,
            'sort_by' => 'relevance',
            'entity_types' => $_GET['entity_types'] ?? null,
            'user_id' => $_SESSION['user_id'],
            'company_id' => $this->getUserCompanyId(),
            'branch_id' => $this->getUserBranchId()
        ];
        
        $results = $this->search->search($query, $filters, $options);
        $formattedResults = $this->formatSearchResults($results['results']);
        
        // Export based on format
        switch ($format) {
            case 'csv':
                $this->exportCSV($formattedResults, $query);
                break;
            case 'json':
                $this->exportJSON($formattedResults, $query);
                break;
            case 'xml':
                $this->exportXML($formattedResults, $query);
                break;
            default:
                $this->jsonResponse(['status' => 'error', 'message' => 'Invalid export format'], 400);
        }
    }
    
    /**
     * Format search results for display
     */
    private function formatSearchResults($results) {
        $formatted = [];
        
        foreach ($results as $result) {
            $formatted[] = [
                'id' => $result['id_search_index'],
                'entity_type' => $result['entity_type'],
                'entity_id' => $result['entity_id'],
                'title' => $result['title'],
                'content' => $result['content'],
                'summary' => $result['summary'],
                'keywords' => $result['keywords'],
                'tags' => json_decode($result['tags'], true) ?: [],
                'metadata' => json_decode($result['metadata'], true) ?: [],
                'relevance_score' => $result['relevance_score'],
                'created_at' => $result['created_at'],
                'indexed_at' => $result['indexed_at'],
                'url' => $this->getEntityUrl($result['entity_type'], $result['entity_id']),
                'icon' => $this->getEntityIcon($result['entity_type']),
                'preview' => $this->generatePreview($result['content'], $result['title'])
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get entity URL
     */
    private function getEntityUrl($entityType, $entityId) {
        $baseUrl = BASE_URL . '/index.php?page=';
        
        switch ($entityType) {
            case 'product':
                return $baseUrl . 'products&action=view&id=' . $entityId;
            case 'customer':
                return $baseUrl . 'customers&action=view&id=' . $entityId;
            case 'supplier':
                return $baseUrl . 'suppliers&action=view&id=' . $entityId;
            case 'transaction':
                return $baseUrl . 'transactions&action=view&id=' . $entityId;
            case 'file':
                return $baseUrl . 'files&action=view&id=' . $entityId;
            case 'member':
                return $baseUrl . 'users&action=view&id=' . $entityId;
            case 'company':
                return $baseUrl . 'companies&action=view&id=' . $entityId;
            case 'branch':
                return $baseUrl . 'branches&action=view&id=' . $entityId;
            case 'notification':
                return $baseUrl . 'notifications&action=view&id=' . $entityId;
            default:
                return '#';
        }
    }
    
    /**
     * Get entity icon
     */
    private function getEntityIcon($entityType) {
        $icons = [
            'product' => 'fas fa-box',
            'customer' => 'fas fa-users',
            'supplier' => 'fas fa-truck',
            'transaction' => 'fas fa-receipt',
            'file' => 'fas fa-file',
            'member' => 'fas fa-user',
            'company' => 'fas fa-building',
            'branch' => 'fas fa-store',
            'notification' => 'fas fa-bell',
            'audit_log' => 'fas fa-history'
        ];
        
        return $icons[$entityType] ?? 'fas fa-file';
    }
    
    /**
     * Generate content preview
     */
    private function generatePreview($content, $title, $length = 200) {
        $preview = $content;
        
        // If content is empty, use title
        if (empty($preview)) {
            $preview = $title;
        }
        
        // Strip HTML tags
        $preview = strip_tags($preview);
        
        // Truncate to specified length
        if (strlen($preview) > $length) {
            $preview = substr($preview, 0, $length) . '...';
        }
        
        return $preview;
    }
    
    /**
     * Export results as CSV
     */
    private function exportCSV($results, $query) {
        $filename = 'search_results_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV header
        fputcsv($output, ['Title', 'Entity Type', 'Entity ID', 'Content Preview', 'Relevance Score', 'Created At']);
        
        // CSV data
        foreach ($results as $result) {
            fputcsv($output, [
                $result['title'],
                $result['entity_type'],
                $result['entity_id'],
                $result['preview'],
                $result['relevance_score'],
                $result['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export results as JSON
     */
    private function exportJSON($results, $query) {
        $filename = 'search_results_' . date('Y-m-d_H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $exportData = [
            'query' => $query,
            'exported_at' => date('Y-m-d H:i:s'),
            'total_results' => count($results),
            'results' => $results
        ];
        
        echo json_encode($exportData, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Export results as XML
     */
    private function exportXML($results, $query) {
        $filename = 'search_results_' . date('Y-m-d_H-i-s') . '.xml';
        
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $xml = new SimpleXMLElement('<search_results/>');
        $xml->addAttribute('query', $query);
        $xml->addAttribute('exported_at', date('Y-m-d H:i:s'));
        $xml->addAttribute('total_results', count($results));
        
        foreach ($results as $result) {
            $resultNode = $xml->addChild('result');
            $resultNode->addChild('title', htmlspecialchars($result['title']));
            $resultNode->addChild('entity_type', $result['entity_type']);
            $resultNode->addChild('entity_id', $result['entity_id']);
            $resultNode->addChild('content_preview', htmlspecialchars($result['preview']));
            $resultNode->addChild('relevance_score', $result['relevance_score']);
            $resultNode->addChild('created_at', $result['created_at']);
        }
        
        echo $xml->asXML();
        exit;
    }
    
    /**
     * Helper method to send JSON response
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
