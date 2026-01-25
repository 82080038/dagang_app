<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/database.php';

class ReportsController extends Controller {
    private $pdo;
    
    public function __construct()
    {
        parent::__construct();
        $this->pdo = new PDO('mysql:host=localhost;dbname=perdagangan_system', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function index() {
        $this->requireAuth();
        $this->requirePermission(ROLE_MANAGER);
        $this->requireFeature('reports');
        
        $data = [
            'title' => 'Laporan',
            'companies' => $this->getAllCompanies(),
            'branches' => $this->getAllBranches(),
            'user_role' => $this->getUserRole()
        ];
        
        $this->render('reports/index', $data);
    }
    
    /**
     * Get all companies for report filters
     */
    private function getAllCompanies() {
        try {
            $stmt = $this->pdo->query("SELECT id_company, company_name FROM companies WHERE is_active = 1 ORDER BY company_name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get all branches for report filters
     */
    private function getAllBranches() {
        try {
            $stmt = $this->pdo->query("SELECT id_branch, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Generate sales report
     */
    public function salesReport() {
        $this->requireAuthJson();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $companyId = $_GET['company_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? null;
        
        try {
            $sql = "SELECT 
                        DATE(t.created_at) as date,
                        COUNT(*) as transaction_count,
                        COALESCE(SUM(t.total_amount), 0) as total_sales,
                        COALESCE(AVG(t.total_amount), 0) as avg_transaction,
                        COUNT(DISTINCT t.customer_id) as unique_customers
                    FROM transactions t
                    WHERE DATE(t.created_at) BETWEEN :start_date AND :end_date";
            
            $params = [
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ];
            
            if ($companyId) {
                $sql .= " AND t.company_id = :company_id";
                $params[':company_id'] = $companyId;
            }
            
            if ($branchId) {
                $sql .= " AND t.branch_id = :branch_id";
                $params[':branch_id'] = $branchId;
            }
            
            $sql .= " GROUP BY DATE(t.created_at) ORDER BY DATE(t.created_at)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate totals
            $totals = [
                'total_transactions' => array_sum(array_column($data, 'transaction_count')),
                'total_sales' => array_sum(array_column($data, 'total_sales')),
                'avg_transaction' => count($data) > 0 ? array_sum(array_column($data, 'avg_transaction')) / count($data) : 0,
                'total_customers' => array_sum(array_column($data, 'unique_customers'))
            ];
            
            $this->json([
                'status' => 'success',
                'data' => $data,
                'totals' => $totals,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate sales report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate product performance report
     */
    public function productPerformanceReport() {
        $this->requireAuthJson();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $companyId = $_GET['company_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? null;
        
        try {
            $sql = "SELECT 
                        p.product_name,
                        p.product_code,
                        COUNT(ti.id_item) as total_sold,
                        COALESCE(SUM(ti.quantity), 0) as total_quantity,
                        COALESCE(SUM(ti.subtotal), 0) as total_revenue,
                        COALESCE(AVG(ti.price), 0) as avg_price,
                        COUNT(DISTINCT ti.transaction_id) as transaction_count
                    FROM transaction_items ti
                    JOIN products p ON ti.product_id = p.id_product
                    JOIN transactions t ON ti.transaction_id = t.id_transaction
                    WHERE DATE(t.created_at) BETWEEN :start_date AND :end_date";
            
            $params = [
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ];
            
            if ($companyId) {
                $sql .= " AND t.company_id = :company_id";
                $params[':company_id'] = $companyId;
            }
            
            if ($branchId) {
                $sql .= " AND t.branch_id = :branch_id";
                $params[':branch_id'] = $branchId;
            }
            
            $sql .= " GROUP BY p.id_product ORDER BY total_revenue DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json([
                'status' => 'success',
                'data' => $data,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate product performance report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate inventory report
     */
    public function inventoryReport() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? null;
        $lowStockThreshold = $_GET['low_stock_threshold'] ?? 10;
        
        try {
            $sql = "SELECT 
                        p.product_name,
                        p.product_code,
                        c.category_name,
                        COALESCE(SUM(bi.stock_quantity), 0) as total_stock,
                        COALESCE(SUM(bi.min_stock), 0) as total_min_stock,
                        CASE 
                            WHEN COALESCE(SUM(bi.stock_quantity), 0) <= :low_stock THEN 'Low Stock'
                            WHEN COALESCE(SUM(bi.stock_quantity), 0) <= COALESCE(SUM(bi.min_stock), 0) * 2 THEN 'Medium Stock'
                            ELSE 'Good Stock'
                        END as stock_status
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id_category
                    LEFT JOIN branch_inventory bi ON p.id_product = bi.product_id
                    WHERE 1=1";
            
            $params = [':low_stock' => $lowStockThreshold];
            
            if ($companyId) {
                $sql .= " AND (bi.company_id = :company_id OR bi.company_id IS NULL)";
                $params[':company_id'] = $companyId;
            }
            
            if ($branchId) {
                $sql .= " AND (bi.branch_id = :branch_id OR bi.branch_id IS NULL)";
                $params[':branch_id'] = $branchId;
            }
            
            $sql .= " GROUP BY p.id_product ORDER BY total_stock ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Summary statistics
            $summary = [
                'total_products' => count($data),
                'low_stock_count' => count(array_filter($data, fn($item) => $item['stock_status'] === 'Low Stock')),
                'medium_stock_count' => count(array_filter($data, fn($item) => $item['stock_status'] === 'Medium Stock')),
                'good_stock_count' => count(array_filter($data, fn($item) => $item['stock_status'] === 'Good Stock')),
                'total_inventory_value' => array_sum(array_column($data, 'total_stock'))
            ];
            
            $this->json([
                'status' => 'success',
                'data' => $data,
                'summary' => $summary
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate inventory report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate customer analysis report
     */
    public function customerAnalysisReport() {
        $this->requireAuthJson();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $companyId = $_GET['company_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? null;
        
        try {
            $sql = "SELECT 
                        c.customer_name,
                        c.customer_code,
                        COUNT(t.id_transaction) as transaction_count,
                        COALESCE(SUM(t.total_amount), 0) as total_spent,
                        COALESCE(AVG(t.total_amount), 0) as avg_transaction,
                        MAX(t.created_at) as last_transaction,
                        CASE 
                            WHEN COUNT(t.id_transaction) >= 10 THEN 'VIP'
                            WHEN COUNT(t.id_transaction) >= 5 THEN 'Regular'
                            ELSE 'Occasional'
                        END as customer_type
                    FROM customers c
                    LEFT JOIN transactions t ON c.id_customer = t.customer_id
                        AND DATE(t.created_at) BETWEEN :start_date AND :end_date";
            
            $params = [
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ];
            
            if ($companyId) {
                $sql .= " AND (t.company_id = :company_id OR t.company_id IS NULL)";
                $params[':company_id'] = $companyId;
            }
            
            if ($branchId) {
                $sql .= " AND (t.branch_id = :branch_id OR t.branch_id IS NULL)";
                $params[':branch_id'] = $branchId;
            }
            
            $sql .= " GROUP BY c.id_customer ORDER BY total_spent DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Customer segmentation
            $segmentation = [
                'vip_customers' => count(array_filter($data, fn($item) => $item['customer_type'] === 'VIP')),
                'regular_customers' => count(array_filter($data, fn($item) => $item['customer_type'] === 'Regular')),
                'occasional_customers' => count(array_filter($data, fn($item) => $item['customer_type'] === 'Occasional')),
                'total_revenue' => array_sum(array_column($data, 'total_spent'))
            ];
            
            $this->json([
                'status' => 'success',
                'data' => $data,
                'segmentation' => $segmentation,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate customer analysis report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export report to CSV
     */
    public function exportReport() {
        $this->requireAuth();
        
        $reportType = $_GET['report_type'] ?? 'sales';
        $format = $_GET['format'] ?? 'csv';
        
        try {
            $filename = $reportType . '_report_' . date('Y-m-d_H-i-s') . '.' . $format;
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($output, "\xEF\xBB\xBF");
            
            switch ($reportType) {
                case 'sales':
                    $this->exportSalesReport($output);
                    break;
                case 'products':
                    $this->exportProductReport($output);
                    break;
                case 'inventory':
                    $this->exportInventoryReport($output);
                    break;
                case 'customers':
                    $this->exportCustomerReport($output);
                    break;
                default:
                    throw new Exception('Invalid report type');
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            echo 'Error exporting report: ' . $e->getMessage();
        }
    }
    
    private function exportSalesReport($output) {
        // CSV headers
        fputcsv($output, ['Date', 'Transaction Count', 'Total Sales', 'Average Transaction', 'Unique Customers']);
        
        // Get data
        $stmt = $this->pdo->query("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as transaction_count,
                COALESCE(SUM(total_amount), 0) as total_sales,
                COALESCE(AVG(total_amount), 0) as avg_transaction,
                COUNT(DISTINCT customer_id) as unique_customers
            FROM transactions 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at)
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['date'],
                $row['transaction_count'],
                number_format($row['total_sales'], 2),
                number_format($row['avg_transaction'], 2),
                $row['unique_customers']
            ]);
        }
    }
    
    private function exportProductReport($output) {
        fputcsv($output, ['Product Name', 'Product Code', 'Total Sold', 'Total Quantity', 'Total Revenue', 'Average Price']);
        
        $stmt = $this->pdo->query("
            SELECT 
                p.product_name,
                p.product_code,
                COUNT(ti.id_item) as total_sold,
                COALESCE(SUM(ti.quantity), 0) as total_quantity,
                COALESCE(SUM(ti.subtotal), 0) as total_revenue,
                COALESCE(AVG(ti.price), 0) as avg_price
            FROM transaction_items ti
            JOIN products p ON ti.product_id = p.id_product
            JOIN transactions t ON ti.transaction_id = t.id_transaction
            WHERE t.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY p.id_product
            ORDER BY total_revenue DESC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['product_name'],
                $row['product_code'],
                $row['total_sold'],
                $row['total_quantity'],
                number_format($row['total_revenue'], 2),
                number_format($row['avg_price'], 2)
            ]);
        }
    }
    
    private function exportInventoryReport($output) {
        fputcsv($output, ['Product Name', 'Product Code', 'Category', 'Total Stock', 'Min Stock', 'Stock Status']);
        
        $stmt = $this->pdo->query("
            SELECT 
                p.product_name,
                p.product_code,
                c.category_name,
                COALESCE(SUM(bi.stock_quantity), 0) as total_stock,
                COALESCE(SUM(bi.min_stock), 0) as total_min_stock,
                CASE 
                    WHEN COALESCE(SUM(bi.stock_quantity), 0) <= 10 THEN 'Low Stock'
                    WHEN COALESCE(SUM(bi.stock_quantity), 0) <= COALESCE(SUM(bi.min_stock), 0) * 2 THEN 'Medium Stock'
                    ELSE 'Good Stock'
                END as stock_status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id_category
            LEFT JOIN branch_inventory bi ON p.id_product = bi.product_id
            GROUP BY p.id_product
            ORDER BY total_stock ASC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['product_name'],
                $row['product_code'],
                $row['category_name'],
                $row['total_stock'],
                $row['total_min_stock'],
                $row['stock_status']
            ]);
        }
    }
    
    private function exportCustomerReport($output) {
        fputcsv($output, ['Customer Name', 'Customer Code', 'Transaction Count', 'Total Spent', 'Average Transaction', 'Customer Type']);
        
        $stmt = $this->pdo->query("
            SELECT 
                c.customer_name,
                c.customer_code,
                COUNT(t.id_transaction) as transaction_count,
                COALESCE(SUM(t.total_amount), 0) as total_spent,
                COALESCE(AVG(t.total_amount), 0) as avg_transaction,
                CASE 
                    WHEN COUNT(t.id_transaction) >= 10 THEN 'VIP'
                    WHEN COUNT(t.id_transaction) >= 5 THEN 'Regular'
                    ELSE 'Occasional'
                END as customer_type
            FROM customers c
            LEFT JOIN transactions t ON c.id_customer = t.customer_id
                AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY c.id_customer
            ORDER BY total_spent DESC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['customer_name'],
                $row['customer_code'],
                $row['transaction_count'],
                number_format($row['total_spent'], 2),
                number_format($row['avg_transaction'], 2),
                $row['customer_type']
            ]);
        }
    }
    
    /**
     * Generate AI-powered advanced report
     */
    public function generateAdvancedReport() {
        $this->requireAuthJson();
        
        $reportType = $_POST['report_type'] ?? 'sales';
        $dateRange = $_POST['date_range'] ?? '7d';
        $filters = json_decode($_POST['filters'] ?? '{}', true);
        $aiModel = $_POST['ai_model'] ?? 'standard';
        $includeForecasts = $_POST['include_forecasts'] ?? false;
        $includeCharts = $_POST['include_charts'] ?? false;
        
        try {
            $reportId = 'RPT_' . uniqid();
            
            // Insert report record
            $stmt = $this->pdo->prepare("
                INSERT INTO advanced_reports (report_id, report_type, report_title, date_range, filters, ai_model, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            
            $reportTitle = $this->getReportTitle($reportType, $dateRange);
            $stmt->execute([$reportId, $reportType, $reportTitle, $dateRange, json_encode($filters), $aiModel, $_SESSION['user_id']]);
            
            // Process report asynchronously
            $this->processAdvancedReport($reportId, $reportType, $dateRange, $filters, $aiModel, $includeForecasts, $includeCharts);
            
            $this->json([
                'status' => 'success',
                'message' => 'Report generation started',
                'report_id' => $reportId
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get report status and results
     */
    public function getReportStatus($reportId) {
        $this->requireAuthJson();
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT ar.*, ra.analytics_data, ra.insights, rp.prediction_data, rr.recommendation_text
                FROM advanced_reports ar
                LEFT JOIN report_analytics ra ON ar.report_id = ra.report_id
                LEFT JOIN report_predictions rp ON ar.report_id = rp.report_id
                LEFT JOIN report_recommendations rr ON ar.report_id = rr.report_id
                WHERE ar.report_id = ?
            ");
            $stmt->execute([$reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$report) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Report not found'
                ], 404);
                return;
            }
            
            $this->json([
                'status' => 'success',
                'data' => $report
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to get report status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available report templates
     */
    public function getReportTemplates() {
        $this->requireAuthJson();
        
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM report_templates WHERE is_active = 1 ORDER BY template_name
            ");
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json([
                'status' => 'success',
                'data' => $templates
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to get templates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get AI models status
     */
    public function getAIModelsStatus() {
        $this->requireAuthJson();
        
        try {
            $stmt = $this->pdo->query("
                SELECT model_type, model_name, version, accuracy, status, last_trained
                FROM ai_models
                ORDER BY model_type
            ");
            $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json([
                'status' => 'success',
                'data' => $models
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to get AI models: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process advanced report with AI
     */
    private function processAdvancedReport($reportId, $reportType, $dateRange, $filters, $aiModel, $includeForecasts, $includeCharts) {
        try {
            // Update status to processing
            $stmt = $this->pdo->prepare("UPDATE advanced_reports SET status = 'processing' WHERE report_id = ?");
            $stmt->execute([$reportId]);
            
            // Get base data
            $data = $this->getReportData($reportType, $dateRange, $filters);
            
            // Apply AI analysis if enabled
            if ($aiModel !== 'none') {
                $analytics = $this->applyAIAnalysis($data, $reportType, $aiModel);
                $this->saveAnalytics($reportId, $analytics);
                
                if ($includeForecasts) {
                    $predictions = $this->generatePredictions($data, $reportType, $aiModel);
                    $this->savePredictions($reportId, $predictions);
                }
                
                $recommendations = $this->generateRecommendations($data, $analytics, $reportType);
                $this->saveRecommendations($reportId, $recommendations);
            }
            
            // Generate report file
            $filePath = $this->generateReportFile($reportId, $data, $includeCharts);
            
            // Update status to completed
            $stmt = $this->pdo->prepare("
                UPDATE advanced_reports 
                SET status = 'completed', file_path = ?, file_size = ?, updated_at = CURRENT_TIMESTAMP
                WHERE report_id = ?
            ");
            $stmt->execute([$filePath, filesize($filePath), $reportId]);
            
        } catch (Exception $e) {
            // Update status to failed
            $stmt = $this->pdo->prepare("UPDATE advanced_reports SET status = 'failed' WHERE report_id = ?");
            $stmt->execute([$reportId]);
            
            error_log("Report processing failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get report data based on type and filters
     */
    private function getReportData($reportType, $dateRange, $filters) {
        $dateCondition = $this->getDateCondition($dateRange);
        
        switch ($reportType) {
            case 'sales':
                return $this->getSalesData($dateCondition, $filters);
            case 'inventory':
                return $this->getInventoryData($filters);
            case 'customer':
                return $this->getCustomerData($dateCondition, $filters);
            case 'financial':
                return $this->getFinancialData($dateCondition, $filters);
            default:
                return [];
        }
    }
    
    /**
     * Apply AI analysis to data
     */
    private function applyAIAnalysis($data, $reportType, $aiModel) {
        // Simulate AI analysis (in real implementation, this would call ML models)
        $analytics = [
            'trends' => $this->analyzeTrends($data),
            'patterns' => $this->detectPatterns($data),
            'anomalies' => $this->detectAnomalies($data),
            'insights' => $this->generateInsights($data, $reportType)
        ];
        
        return $analytics;
    }
    
    /**
     * Generate predictions
     */
    private function generatePredictions($data, $reportType, $aiModel) {
        // Simulate predictions (in real implementation, this would use ML models)
        $predictions = [
            'next_period' => $this->predictNextPeriod($data),
            'confidence' => 0.85,
            'model_used' => $aiModel
        ];
        
        return $predictions;
    }
    
    /**
     * Generate recommendations
     */
    private function generateRecommendations($data, $analytics, $reportType) {
        $recommendations = [];
        
        switch ($reportType) {
            case 'sales':
                if ($analytics['trends']['direction'] === 'declining') {
                    $recommendations[] = [
                        'type' => 'action',
                        'text' => 'Consider promotional activities to boost declining sales',
                        'priority' => 'high'
                    ];
                }
                break;
            case 'inventory':
                $lowStockItems = array_filter($data, fn($item) => $item['stock_status'] === 'Low Stock');
                if (count($lowStockItems) > 0) {
                    $recommendations[] = [
                        'type' => 'action',
                        'text' => 'Reorder ' . count($lowStockItems) . ' items with low stock',
                        'priority' => 'medium'
                    ];
                }
                break;
        }
        
        return $recommendations;
    }
    
    /**
     * Helper methods
     */
    private function getReportTitle($reportType, $dateRange) {
        $titles = [
            'sales' => 'Sales Analysis Report',
            'inventory' => 'Inventory Status Report',
            'customer' => 'Customer Analysis Report',
            'financial' => 'Financial Performance Report'
        ];
        
        return ($titles[$reportType] ?? 'Business Report') . ' - ' . $dateRange;
    }
    
    private function getDateCondition($dateRange) {
        $conditions = [
            '1d' => 'DATE(created_at) = CURDATE()',
            '7d' => 'created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            '30d' => 'created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)',
            '90d' => 'created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)',
            '1y' => 'created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)'
        ];
        
        return $conditions[$dateRange] ?? $conditions['7d'];
    }
    
    private function analyzeTrends($data) {
        if (empty($data)) return ['direction' => 'stable', 'change' => 0];
        
        // Simple trend analysis
        $values = array_column($data, 'total_sales' ?? 'total_revenue' ?? 'total_spent');
        if (count($values) < 2) return ['direction' => 'stable', 'change' => 0];
        
        $firstHalf = array_slice($values, 0, count($values) / 2);
        $secondHalf = array_slice($values, count($values) / 2);
        
        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);
        
        $change = (($secondAvg - $firstAvg) / $firstAvg) * 100;
        
        return [
            'direction' => $change > 5 ? 'increasing' : ($change < -5 ? 'declining' : 'stable'),
            'change' => round($change, 2)
        ];
    }
    
    private function detectPatterns($data) {
        // Simulate pattern detection
        return [
            'seasonal' => false,
            'cyclical' => false,
            'irregular' => true
        ];
    }
    
    private function detectAnomalies($data) {
        // Simple anomaly detection
        $anomalies = [];
        
        if (!empty($data)) {
            $values = array_column($data, 'total_sales' ?? 'total_revenue' ?? 'total_spent');
            $mean = array_sum($values) / count($values);
            $stddev = sqrt(array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / count($values));
            
            foreach ($data as $index => $item) {
                $value = $item['total_sales'] ?? $item['total_revenue'] ?? $item['total_spent'];
                if (abs($value - $mean) > 2 * $stddev) {
                    $anomalies[] = ['index' => $index, 'value' => $value, 'type' => $value > $mean ? 'spike' : 'dip'];
                }
            }
        }
        
        return $anomalies;
    }
    
    private function generateInsights($data, $reportType) {
        $insights = [];
        
        switch ($reportType) {
            case 'sales':
                if (!empty($data)) {
                    $totalSales = array_sum(array_column($data, 'total_sales'));
                    $avgSales = $totalSales / count($data);
                    $bestDay = max($data, fn($a, $b) => $a['total_sales'] <=> $b['total_sales']);
                    
                    $insights[] = "Total sales: Rp " . number_format($totalSales, 0);
                    $insights[] = "Average daily sales: Rp " . number_format($avgSales, 0);
                    $insights[] = "Best performing day: " . $bestDay['date'] . " (Rp " . number_format($bestDay['total_sales'], 0) . ")";
                }
                break;
        }
        
        return $insights;
    }
    
    private function predictNextPeriod($data) {
        // Simple linear regression for prediction
        if (empty($data)) return 0;
        
        $values = array_column($data, 'total_sales' ?? 'total_revenue' ?? 'total_spent');
        if (count($values) < 2) return $values[0] ?? 0;
        
        $n = count($values);
        $x = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($values);
        $sumXY = array_sum(array_map(fn($xi, $yi) => $xi * $yi, $x, $values));
        $sumX2 = array_sum(array_map(fn($xi) => $xi * $xi, $x));
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        return round($intercept + $slope * ($n + 1), 2);
    }
    
    private function saveAnalytics($reportId, $analytics) {
        $stmt = $this->pdo->prepare("
            INSERT INTO report_analytics (report_id, analytics_type, analytics_data, insights, confidence_score, data_points, anomalies_detected)
            VALUES (?, 'comprehensive', ?, ?, 0.85, ?, ?)
        ");
        $stmt->execute([
            $reportId,
            json_encode($analytics),
            json_encode($analytics['insights'] ?? []),
            count($analytics['trends'] ?? []),
            count($analytics['anomalies'] ?? [])
        ]);
    }
    
    private function savePredictions($reportId, $predictions) {
        $stmt = $this->pdo->prepare("
            INSERT INTO report_predictions (report_id, prediction_type, prediction_data, confidence_score, accuracy_score, prediction_period, model_used)
            VALUES (?, 'forecast', ?, ?, 0.00, 'next_period', ?)
        ");
        $stmt->execute([
            $reportId,
            json_encode($predictions),
            $predictions['confidence'] ?? 0.85,
            $predictions['model_used'] ?? 'standard'
        ]);
    }
    
    private function saveRecommendations($reportId, $recommendations) {
        foreach ($recommendations as $rec) {
            $stmt = $this->pdo->prepare("
                INSERT INTO report_recommendations (report_id, recommendation_type, recommendation_text, priority, confidence_score)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $reportId,
                $rec['type'] ?? 'general',
                $rec['text'],
                $rec['priority'] ?? 'medium',
                0.80
            ]);
        }
    }
    
    private function generateReportFile($reportId, $data, $includeCharts) {
        $filename = 'reports/' . $reportId . '.json';
        $reportData = [
            'report_id' => $reportId,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $data,
            'charts' => $includeCharts ? $this->generateChartData($data) : null
        ];
        
        file_put_contents($filename, json_encode($reportData, JSON_PRETTY_PRINT));
        return $filename;
    }
    
    private function generateChartData($data) {
        $charts = [];
        
        if (!empty($data)) {
            $charts['trend_chart'] = [
                'type' => 'line',
                'data' => array_map(fn($item) => [
                    'x' => $item['date'] ?? $item['product_name'] ?? $item['customer_name'],
                    'y' => $item['total_sales'] ?? $item['total_revenue'] ?? $item['total_spent']
                ], $data)
            ];
        }
        
        return $charts;
    }
    
    private function getSalesData($dateCondition, $filters) {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as transaction_count, COALESCE(SUM(total_amount), 0) as total_sales FROM transactions WHERE $dateCondition";
        
        if (!empty($filters['company_id'])) {
            $sql .= " AND company_id = " . (int)$filters['company_id'];
        }
        
        if (!empty($filters['branch_id'])) {
            $sql .= " AND branch_id = " . (int)$filters['branch_id'];
        }
        
        $sql .= " GROUP BY DATE(created_at) ORDER BY DATE(created_at)";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getInventoryData($filters) {
        $sql = "SELECT p.product_name, p.product_code, COALESCE(SUM(bi.stock_quantity), 0) as total_stock, bi.min_stock FROM products p LEFT JOIN branch_inventory bi ON p.id_product = bi.product_id WHERE 1=1";
        
        if (!empty($filters['company_id'])) {
            $sql .= " AND (bi.company_id = " . (int)$filters['company_id'] . " OR bi.company_id IS NULL)";
        }
        
        if (!empty($filters['branch_id'])) {
            $sql .= " AND (bi.branch_id = " . (int)$filters['branch_id'] . " OR bi.branch_id IS NULL)";
        }
        
        $sql .= " GROUP BY p.id_product ORDER BY total_stock ASC";
        
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add stock status
        foreach ($data as &$item) {
            $item['stock_status'] = $item['total_stock'] <= ($item['min_stock'] ?? 10) ? 'Low Stock' : 'Good Stock';
        }
        
        return $data;
    }
    
    private function getCustomerData($dateCondition, $filters) {
        $sql = "SELECT c.customer_name, c.customer_code, COUNT(t.id_transaction) as transaction_count, COALESCE(SUM(t.total_amount), 0) as total_spent FROM customers c LEFT JOIN transactions t ON c.id_customer = t.customer_id AND $dateCondition WHERE 1=1";
        
        if (!empty($filters['company_id'])) {
            $sql .= " AND (t.company_id = " . (int)$filters['company_id'] . " OR t.company_id IS NULL)";
        }
        
        if (!empty($filters['branch_id'])) {
            $sql .= " AND (t.branch_id = " . (int)$filters['branch_id'] . " OR t.branch_id IS NULL)";
        }
        
        $sql .= " GROUP BY c.id_customer ORDER BY total_spent DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getFinancialData($dateCondition, $filters) {
        // Placeholder for financial data
        return [
            ['period' => 'Current', 'revenue' => 1000000, 'expenses' => 600000, 'profit' => 400000],
            ['period' => 'Previous', 'revenue' => 900000, 'expenses' => 550000, 'profit' => 350000]
        ];
    }
    
    /**
     * Legacy API method - kept for compatibility
     */
    public function api() {
        $this->requireAuthJson();
        try {
            $today = $this->pdo->query("SELECT COALESCE(SUM(total_amount),0) as total, COUNT(*) as count FROM transactions WHERE DATE(created_at)=CURDATE()")->fetch(PDO::FETCH_ASSOC);
            $last7 = $this->pdo->query("SELECT DATE(created_at) as d, COALESCE(SUM(total_amount),0) as total, COUNT(*) as count FROM transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at)")->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'today' => $today,
                    'last7days' => $last7
                ]
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
