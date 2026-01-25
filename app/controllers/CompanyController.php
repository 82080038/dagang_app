<?php
/**
 * Company Controller
 * Handle CRUD operations for companies
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Company.php';

class CompanyController extends Controller {
    private $companyModel;
    
    public function __construct() {
        parent::__construct();
        $this->companyModel = new Company();
    }
    
    /**
     * Display companies list
     */
    public function index() {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        $page = $_GET['page_num'] ?? 1;
        $search = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? '';
        
        // Handle AJAX request
        if ($this->isAjaxRequest()) {
            $companies = $this->companyModel->getAll($limit = 10, $offset = ($page - 1) * 10);
            $totalCompanies = $this->companyModel->getTotalCount();
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'companies' => $companies,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => ceil($totalCompanies / 10),
                        'total_items' => $totalCompanies
                    ]
                ]
            ]);
            return;
        }
        
        // Handle regular HTML request
        $companies = $this->companyModel->getAll($limit = 10, $offset = ($page - 1) * 10);
        $totalCompanies = $this->companyModel->getTotalCount();
        
        $data = [
            'title' => 'Daftar Perusahaan',
            'companies' => $companies,
            'pagination' => [
                'current' => $page,
                'total' => ceil($totalCompanies / 10),
                'total_items' => $totalCompanies
            ],
            'search' => $search,
            'type' => $type
        ];
        
        $this->view->render('companies/index', $data);
    }
    
    /**
     * Create new company
     */
    public function create() {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Validate data
            $errors = $this->companyModel->validateCompany($data);
            
            if (!empty($errors)) {
                $response = [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
            } else {
                try {
                    $companyId = $this->companyModel->createCompany($data);
                    
                    $response = [
                        'status' => 'success',
                        'message' => 'Company created successfully',
                        'data' => [
                            'company_id' => $companyId,
                            'redirect_url' => BASE_URL . '/index.php?page=companies'
                        ]
                    ];
                } catch (Exception $e) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Failed to create company: ' . $e->getMessage()
                    ];
                }
            }
            
            if ($this->isAjaxRequest()) {
                $this->json($response);
            } else {
                if ($response['status'] === 'success') {
                    $_SESSION['flash']['success'] = $response['message'];
                    header('Location: index.php?page=companies');
                } else {
                    $_SESSION['flash']['error'] = $response['message'];
                    $_SESSION['errors'] = $errors ?? [];
                    $_SESSION['old_input'] = $data;
                    header('Location: index.php?page=companies&action=create');
                }
                exit;
            }
        } else {
            $data = [
                'title' => 'Tambah Perusahaan Baru',
                'company_types' => $this->companyModel->getTypeOptions(),
                'business_categories' => $this->companyModel->getBusinessCategoryOptions(),
                'scalability_levels' => $this->companyModel->getScalabilityLevelOptions()
            ];
            
            $this->view->render('companies/create', $data);
        }
    }
    
    /**
     * Edit company
     */
    public function edit($id) {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        $company = $this->companyModel->getById($id);
        
        if (!$company) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Company not found'
                ], 404);
            } else {
                $_SESSION['flash']['error'] = 'Company not found';
                header('Location: index.php?page=companies');
                exit;
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Validate data
            $errors = $this->companyModel->validateCompany($data);
            
            if (!empty($errors)) {
                $response = [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
            } else {
                try {
                    $this->companyModel->updateCompany($id, $data);
                    
                    $response = [
                        'status' => 'success',
                        'message' => 'Company updated successfully',
                        'data' => [
                            'company_id' => $id,
                            'redirect_url' => BASE_URL . '/index.php?page=companies'
                        ]
                    ];
                } catch (Exception $e) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Failed to update company: ' . $e->getMessage()
                    ];
                }
            }
            
            if ($this->isAjaxRequest()) {
                $this->json($response);
            } else {
                if ($response['status'] === 'success') {
                    $_SESSION['flash']['success'] = $response['message'];
                    header('Location: index.php?page=companies');
                } else {
                    $_SESSION['flash']['error'] = $response['message'];
                    $_SESSION['errors'] = $errors ?? [];
                    $_SESSION['old_input'] = $data;
                    header("Location: index.php?page=companies&action=edit&id=$id");
                }
                exit;
            }
        } else {
            $data = [
                'title' => 'Edit Perusahaan',
                'company' => $company,
                'company_types' => $this->companyModel->getTypeOptions(),
                'business_categories' => $this->companyModel->getBusinessCategoryOptions(),
                'scalability_levels' => $this->companyModel->getScalabilityLevelOptions()
            ];
            
            $this->view->render('companies/edit', $data);
        }
    }
    
    /**
     * Update company (AJAX)
     */
    public function update() {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'status' => 'error',
                'message' => 'Method not allowed'
            ], 405);
            return;
        }
        
        $companyId = $_POST['company_id'] ?? null;
        
        if (!$companyId) {
            $this->json([
                'status' => 'error',
                'message' => 'Company ID is required'
            ]);
            return;
        }
        
        // Validate required fields
        $requiredFields = ['company_name', 'company_type', 'scalability_level', 'owner_name',
            'address_detail', 'province_id', 'regency_id', 'district_id', 'village_id'];
        $data = [];
        $errors = [];
        
        foreach ($requiredFields as $field) {
            $value = $_POST[$field] ?? '';
            if (empty(trim($value))) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            } else {
                $data[$field] = trim($value);
            }
        }
        
        // Optional fields
        $optionalFields = ['company_code', 'business_category', 'email', 'phone', 'address_id', 'postal_code'];
        foreach ($optionalFields as $field) {
            $data[$field] = $_POST[$field] ?? null;
        }
        
        if (!empty($errors)) {
            $this->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $errors
            ]);
            return;
        }
        
        try {
            $this->companyModel->updateCompany($companyId, $data);
            
            $this->json([
                'status' => 'success',
                'message' => 'Perusahaan berhasil diperbarui',
                'data' => [
                    'company_id' => $companyId
                ]
            ]);
        } catch (Exception $e) {
            // Check for duplicate entry error
            if (strpos($e->getMessage(), '1062') !== false && strpos($e->getMessage(), 'company_code') !== false) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Kode perusahaan sudah digunakan. Silakan gunakan kode lain.',
                    'errors' => ['company_code' => 'Kode perusahaan sudah digunakan']
                ]);
            } else {
                $this->json([
                    'status' => 'error',
                    'message' => 'Gagal memperbarui perusahaan: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Delete company
     */
    public function delete($id) {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        $company = $this->companyModel->getById($id);
        
        if (!$company) {
            $response = [
                'status' => 'error',
                'message' => 'Company not found'
            ];
        } else {
            try {
                $this->companyModel->deactivate($id);
                
                $response = [
                    'status' => 'success',
                    'message' => 'Company deleted successfully'
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to delete company: ' . $e->getMessage()
                ];
            }
        }
        
        if ($this->isAjaxRequest()) {
            $this->json($response);
        } else {
            $_SESSION['flash'][$response['status']] = $response['message'];
            header('Location: index.php?page=companies');
            exit;
        }
    }
    
    /**
     * Search companies
     */
    public function search() {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        $keyword = $_GET['q'] ?? '';
        
        if (empty($keyword)) {
            $this->json([
                'status' => 'error',
                'message' => 'Search keyword is required'
            ]);
        }
        
        $companies = $this->companyModel->search($keyword);
        
        $this->json([
            'status' => 'success',
            'data' => $companies
        ]);
    }
    
    /**
     * Get company details (AJAX)
     */
    public function details($id) {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        // Disable error reporting to prevent HTML output in JSON
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Use getCompanyWithAddress to get complete address data
        $company = $this->companyModel->getCompanyWithAddress($id);
        
        if (!$company) {
            $this->json([
                'status' => 'error',
                'message' => 'Company not found'
            ], 404);
        }
        
        try {
            // Get additional details
            $companyWithBranches = $this->companyModel->getWithBranches($id);
            $statistics = $this->companyModel->getStatistics($id);
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'company' => $company,
                    'branches' => $companyWithBranches,
                    'statistics' => $statistics
                ]
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to load company details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get company for edit form (AJAX)
     */
    public function get($id) {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        // Disable error reporting to prevent HTML output in JSON
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Use getCompanyWithAddress to get complete address data for edit form
        $company = $this->companyModel->getCompanyWithAddress($id);
        
        if (!$company) {
            $this->json([
                'status' => 'error',
                'message' => 'Company not found'
            ], 404);
        }
        
        $this->json([
            'status' => 'success',
            'data' => [
                'company' => $company
            ]
        ]);
    }
    
    /**
     * Toggle company status (activate/deactivate)
     */
    public function toggleStatus($id) {
        $this->requireAppPermission(ROLE_APP_ADMIN);
        
        $company = $this->companyModel->getById($id);
        
        if (!$company) {
            $this->json([
                'status' => 'error',
                'message' => 'Company not found'
            ], 404);
        }
        
        try {
            if ($company['is_active']) {
                $this->companyModel->deactivate($id);
                $message = 'Company deactivated successfully';
                $newStatus = 0;
            } else {
                $this->companyModel->activate($id);
                $message = 'Company activated successfully';
                $newStatus = 1;
            }
            
            $this->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'company_id' => $id,
                    'new_status' => $newStatus
                ]
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to toggle company status: ' . $e->getMessage()
            ]);
        }
    }
}
?>
