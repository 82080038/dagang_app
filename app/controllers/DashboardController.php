<?php
/**
 * Dashboard Controller
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/Branch.php';

class DashboardController extends Controller {
    private $companyModel;
    private $branchModel;
    
    public function __construct() {
        parent::__construct();
        $this->companyModel = new Company();
        $this->branchModel = new Branch();
    }
    
    /**
     * Dashboard Index
     */
    public function index() {
        $this->requireAuth();
        
        // Get statistics
        $companyStats = $this->companyModel->getStatistics();
        $branchStats = $this->branchModel->getStatistics();
        
        // Get recent companies with available fields only
        $recentCompanies = $this->companyModel->query("
            SELECT 
                id_company,
                company_name,
                company_code,
                company_type,
                owner_name,
                phone,
                email,
                is_active,
                created_at,
                'retail' as business_category,
                CASE 
                    WHEN company_type IN ('individual', 'warung', 'kios') THEN 1
                    WHEN company_type IN ('toko_kelontong', 'minimarket') THEN 2
                    WHEN company_type IN ('pengusaha_menengah', 'distributor') THEN 3
                    WHEN company_type IN ('perusahaan_besar', 'franchise', 'koperasi') THEN 4
                    ELSE 1
                END as scalability_level
            FROM companies 
            WHERE is_active = 1 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        
        // Get active branches with inventory
        $branchesWithInventory = $this->branchModel->getWithInventorySummary();
        
        // Get open branches
        $openBranches = $this->branchModel->getOpenBranches();
        
        $data = [
            'title' => 'Dashboard',
            'companyStats' => $companyStats,
            'branchStats' => $branchStats,
            'recentCompanies' => $recentCompanies,
            'branchesWithInventory' => $branchesWithInventory,
            'openBranches' => $openBranches
        ];
        
        $this->view->render('dashboard/index', $data);
    }
    
    /**
     * Get today's sales statistics
     */
    private function getTodaySales() {
        $sql = "SELECT 
                    COUNT(*) as total_transactions,
                    COALESCE(SUM(total_amount), 0) as total_sales,
                    COALESCE(AVG(total_amount), 0) as avg_transaction
                FROM transactions 
                WHERE DATE(created_at) = CURDATE() 
                AND transaction_type = 'SALE'";
        
        return $this->companyModel->queryOne($sql);
    }
    
    /**
     * Get low stock alerts
     */
    private function getLowStockAlerts() {
        $sql = "SELECT 
                    p.product_id,
                    p.product_name,
                    p.min_stock_level,
                    COALESCE(SUM(bi.quantity), 0) as total_stock
                FROM products p
                LEFT JOIN branch_inventory bi ON p.product_id = bi.product_id
                WHERE p.is_active = 1
                GROUP BY p.product_id
                HAVING total_stock <= p.min_stock_level OR total_stock = 0
                ORDER BY total_stock ASC
                LIMIT 10";
        
        return $this->companyModel->query($sql);
    }
    
    /**
     * API Dashboard Statistics
     */
    public function apiStats() {
        $this->requireAuth();
        
        $companyStats = $this->companyModel->getStatistics();
        $branchStats = $this->branchModel->getStatistics();
        
        $data = [
            'companies' => $companyStats,
            'branches' => $branchStats,
            'total_active_entities' => $companyStats['active_companies'] + $branchStats['active_branches']
        ];
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Dashboard statistics retrieved',
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * API Real-time Statistics
     */
    public function apiRealtimeStats() {
        $this->requireAuth();
        
        // Get today's statistics
        $todaySales = $this->getTodaySales();
        $openBranches = $this->branchModel->getOpenBranches();
        $lowStockAlerts = $this->getLowStockAlerts();
        
        $data = [
            'today_sales' => $todaySales,
            'open_branches_count' => count($openBranches),
            'low_stock_alerts' => count($lowStockAlerts),
            'last_updated' => date('Y-m-d H:i:s')
        ];
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Real-time statistics retrieved',
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * API Recent Activity
     */
    public function apiRecentActivity() {
        $this->requireAuth();
        
        // Get recent companies
        $recentCompanies = $this->companyModel->getAll(5);
        
        // Format activity data
        $activities = [];
        
        foreach ($recentCompanies as $company) {
            $activities[] = [
                'type' => 'company_created',
                'title' => 'Perusahaan Baru Ditambahkan',
                'description' => $company['company_name'] . ' (' . $company['company_type'] . ')',
                'time' => $this->formatDate($company['created_at']),
                'icon' => 'building',
                'color' => 'success'
            ];
        }
        
        $this->success('Recent activity retrieved', $activities);
    }
    
    /**
     * API Scalability Distribution
     */
    public function apiScalabilityDistribution() {
        $this->requireAuth();
        
        // Disable error reporting to prevent HTML output in JSON
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $companyStats = $this->companyModel->getStatistics();
        
        // Ensure all required fields exist with default values
        $data = [
            'labels' => [
                'Level 1 - Individu',
                'Level 2 - Warung/Kios',
                'Level 3 - Toko Kelontong',
                'Level 4 - Minimarket',
                'Level 5 - Distributor',
                'Level 6 - Enterprise'
            ],
            'data' => [
                isset($companyStats['level_1_count']) ? $companyStats['level_1_count'] : 0,
                isset($companyStats['level_2_count']) ? $companyStats['level_2_count'] : 0,
                isset($companyStats['level_3_count']) ? $companyStats['level_3_count'] : 0,
                isset($companyStats['level_4_count']) ? $companyStats['level_4_count'] : 0,
                isset($companyStats['level_5_count']) ? $companyStats['level_5_count'] : 0,
                isset($companyStats['level_6_count']) ? $companyStats['level_6_count'] : 0
            ]
        ];
        
        $this->success('Scalability distribution retrieved', $data);
    }
    
    /**
     * API Business Segment Distribution
     */
    public function apiBusinessSegmentDistribution() {
        $this->requireAuth();
        
        // Disable error reporting to prevent HTML output in JSON
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $branchStats = $this->branchModel->getStatistics();
        
        // Ensure all required fields exist with default values
        $data = [
            'labels' => [
                'Ultra Mikro',
                'Mikro',
                'Kecil-Menengah',
                'Menengah',
                'Besar',
                'Enterprise'
            ],
            'data' => [
                isset($branchStats['ultra_mikro_count']) ? $branchStats['ultra_mikro_count'] : 0,
                isset($branchStats['mikro_count']) ? $branchStats['mikro_count'] : 0,
                isset($branchStats['kecil_menengah_count']) ? $branchStats['kecil_menengah_count'] : 0,
                isset($branchStats['menengah_count']) ? $branchStats['menengah_count'] : 0,
                isset($branchStats['besar_count']) ? $branchStats['besar_count'] : 0,
                isset($branchStats['enterprise_count']) ? $branchStats['enterprise_count'] : 0
            ]
        ];
        
        $this->success('Business segment distribution retrieved', $data);
    }
    
    /**
     * API Open Branches
     */
    public function apiOpenBranches() {
        $this->requireAuth();
        
        $openBranches = $this->branchModel->getOpenBranches();
        
        $data = [
            'total_open' => count($openBranches),
            'branches' => $openBranches
        ];
        
        $this->success('Open branches retrieved', $data);
    }
    
    /**
     * API Low Stock Alert
     */
    public function apiLowStockAlert() {
        $this->requireAuth();
        
        $branchesWithInventory = $this->branchModel->getWithInventorySummary();
        
        $lowStockBranches = array_filter($branchesWithInventory, function($branch) {
            return $branch['low_stock_count'] > 0;
        });
        
        $data = [
            'total_alerts' => count($lowStockBranches),
            'branches' => array_values($lowStockBranches)
        ];
        
        $this->success('Low stock alerts retrieved', $data);
    }
    
    /**
     * Search Companies and Branches
     */
    public function search() {
        $this->requireAuth();
        
        $keyword = $this->input('keyword');
        
        if (empty($keyword)) {
            $this->error('Keyword is required');
        }
        
        // Search companies
        $companies = $this->companyModel->search($keyword);
        
        // Search branches
        $branches = $this->branchModel->search($keyword);
        
        $data = [
            'companies' => $companies,
            'branches' => $branches
        ];
        
        $this->view->render('dashboard/search', $data);
    }
    
    /**
     * Company Details
     */
    public function companyDetails($id) {
        $this->requireAuth();
        
        $company = $this->companyModel->getById($id);
        
        if (!$company) {
            $this->error('Company not found', 404);
        }
        
        // Get company branches
        $branches = $this->branchModel->getByCompany($id);
        
        $data = [
            'company' => $company,
            'branches' => $branches,
            'title' => 'Detail Perusahaan'
        ];
        
        $this->view->render('dashboard/company_details', $data);
    }
    
    /**
     * Branch Details
     */
    public function branchDetails($id) {
        $this->requireAuth();
        
        $branch = $this->branchModel->getById($id);
        
        if (!$branch) {
            $this->error('Branch not found', 404);
        }
        
        // Get company info
        $company = $this->companyModel->getById($branch['company_id']);
        
        // Check if branch is open
        $isOpen = $this->branchModel->isOpen($id);
        
        $data = [
            'branch' => $branch,
            'company' => $company,
            'isOpen' => $isOpen,
            'operationHours' => $this->branchModel->getOperationHours($id),
            'title' => 'Detail Cabang'
        ];
        
        $this->view->render('dashboard/branch_details', $data);
    }
    
    /**
     * Export Dashboard Data
     */
    public function export() {
        $this->requireAuth();
        
        $format = $this->input('format', 'json');
        
        $companyStats = $this->companyModel->getStatistics();
        $branchStats = $this->branchModel->getStatistics();
        
        $data = [
            'export_date' => date('Y-m-d H:i:s'),
            'companies' => $companyStats,
            'branches' => $branchStats,
            'total_entities' => $companyStats['total_companies'] + $branchStats['total_branches']
        ];
        
        switch ($format) {
            case 'csv':
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="dashboard_export.csv"');
                
                $output = fopen('php://output', 'w');
                
                // Header
                fputcsv($output, ['Metric', 'Value']);
                
                // Data
                fputcsv($output, ['Total Companies', $companyStats['total_companies']]);
                fputcsv($output, ['Active Companies', $companyStats['active_companies']]);
                fputcsv($output, ['Total Branches', $branchStats['total_branches']]);
                fputcsv($output, ['Active Branches', $branchStats['active_branches']]);
                
                fclose($output);
                break;
                
            case 'json':
            default:
                $this->json($data);
                break;
        }
    }
}
?>
