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
        
        // Get recent companies
        $recentCompanies = $this->companyModel->getAll(5);
        
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
        
        $this->render('dashboard/index', $data);
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
        
        $this->success('Dashboard statistics retrieved', $data);
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
        
        $companyStats = $this->companyModel->getStatistics();
        
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
                $companyStats['level_1_count'],
                $companyStats['level_2_count'],
                $companyStats['level_3_count'],
                $companyStats['level_4_count'],
                $companyStats['level_5_count'],
                $companyStats['level_6_count']
            ]
        ];
        
        $this->success('Scalability distribution retrieved', $data);
    }
    
    /**
     * API Business Segment Distribution
     */
    public function apiBusinessSegmentDistribution() {
        $this->requireAuth();
        
        $branchStats = $this->branchModel->getStatistics();
        
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
                $branchStats['ultra_mikro_count'],
                $branchStats['mikro_count'],
                $branchStats['kecil_menengah_count'],
                $branchStats['menengah_count'],
                $branchStats['besar_count'],
                $branchStats['enterprise_count']
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
        
        $this->render('dashboard/search', $data);
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
        
        $this->render('dashboard/company_details', $data);
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
        
        $this->render('dashboard/branch_details', $data);
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
