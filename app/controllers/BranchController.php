<?php
/**
 * Branch Controller
 * Handle CRUD operations for branches
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/Company.php';

class BranchController extends Controller {
    private $branchModel;
    private $companyModel;
    
    public function __construct() {
        parent::__construct();
        $this->branchModel = new Branch();
        $this->companyModel = new Company();
    }
    
    /**
     * Display branches list
     */
    public function index() {
        $this->requireAuth();
        
        $page = $_GET['page_num'] ?? 1;
        $search = $_GET['q'] ?? '';
        $companyId = $_GET['company_id'] ?? null;
        
        // Handle AJAX request
        if ($this->isAjaxRequest()) {
            $filters = [];
            if ($companyId) $filters['company_id'] = $companyId;
            
            $branches = $this->branchModel->getAll($limit = 10, $offset = ($page - 1) * 10, $filters);
            $totalBranches = $this->branchModel->getTotalCount($filters);
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'branches' => $branches,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => ceil($totalBranches / 10),
                        'total_items' => $totalBranches
                    ]
                ]
            ]);
            return;
        }
        
        // Handle regular HTML request
        $filters = [];
        if ($companyId) $filters['company_id'] = $companyId;
        
        $branches = $this->branchModel->getAll($limit = 10, $offset = ($page - 1) * 10, $filters);
        $totalBranches = $this->branchModel->getTotalCount($filters);
        $companies = $this->companyModel->getOptions();
        
        $data = [
            'title' => 'Daftar Cabang',
            'branches' => $branches,
            'companies' => $companies,
            'pagination' => [
                'current' => $page,
                'total_pages' => ceil($totalBranches / 10),
                'total_items' => $totalBranches
            ],
            'search' => $search,
            'company_id' => $companyId
        ];
        
        $this->render('branches/index', $data);
    }
    
    /**
     * Create new branch
     */
    public function create() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Validate data
            $errors = $this->branchModel->validateBranch($data);
            
            if (!empty($errors)) {
                $response = [
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $errors
                ];
            } else {
                try {
                    $branchId = $this->branchModel->create($data);
                    
                    $response = [
                        'status' => 'success',
                        'message' => 'Cabang berhasil dibuat',
                        'data' => [
                            'branch_id' => $branchId,
                            'redirect_url' => BASE_URL . '/index.php?page=branches'
                        ]
                    ];
                } catch (Exception $e) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Gagal membuat cabang: ' . $e->getMessage()
                    ];
                }
            }
            
            if ($this->isAjaxRequest()) {
                $this->json($response);
            } else {
                if ($response['status'] === 'success') {
                    $_SESSION['flash']['success'] = $response['message'];
                    header('Location: index.php?page=branches');
                } else {
                    $_SESSION['flash']['error'] = $response['message'];
                    $_SESSION['errors'] = $errors ?? [];
                    $_SESSION['old_input'] = $data;
                    header('Location: index.php?page=branches&action=create');
                }
                exit;
            }
        } else {
            $data = [
                'title' => 'Tambah Cabang Baru',
                'companies' => $this->companyModel->getOptions()
            ];
            
            $this->render('branches/create', $data);
        }
    }
    
    /**
     * Edit branch
     */
    public function edit($id) {
        $this->requireAuth();
        
        $branch = $this->branchModel->getById($id);
        
        if (!$branch) {
            $_SESSION['flash']['error'] = 'Cabang tidak ditemukan';
            header('Location: index.php?page=branches');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Validate data
            $errors = $this->branchModel->validateBranch($data);
            
            if (!empty($errors)) {
                $response = [
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $errors
                ];
            } else {
                try {
                    $this->branchModel->update($id, $data);
                    
                    $response = [
                        'status' => 'success',
                        'message' => 'Cabang berhasil diperbarui',
                        'data' => [
                            'branch_id' => $id,
                            'redirect_url' => BASE_URL . '/index.php?page=branches'
                        ]
                    ];
                } catch (Exception $e) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Gagal memperbarui cabang: ' . $e->getMessage()
                    ];
                }
            }
            
            if ($this->isAjaxRequest()) {
                $this->json($response);
            } else {
                if ($response['status'] === 'success') {
                    $_SESSION['flash']['success'] = $response['message'];
                    header('Location: index.php?page=branches');
                } else {
                    $_SESSION['flash']['error'] = $response['message'];
                    $_SESSION['errors'] = $errors ?? [];
                    $_SESSION['old_input'] = $data;
                    header("Location: index.php?page=branches&action=edit&id=$id");
                }
                exit;
            }
        } else {
            $data = [
                'title' => 'Edit Cabang',
                'branch' => $branch,
                'companies' => $this->companyModel->getOptions()
            ];
            
            $this->render('branches/edit', $data);
        }
    }
    
    /**
     * Delete branch
     */
    public function delete($id) {
        $this->requireAuth();
        
        try {
            $this->branchModel->deactivate($id);
            $response = [
                'status' => 'success',
                'message' => 'Cabang berhasil dihapus'
            ];
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Gagal menghapus cabang: ' . $e->getMessage()
            ];
        }
        
        if ($this->isAjaxRequest()) {
            $this->json($response);
        } else {
            $_SESSION['flash'][$response['status']] = $response['message'];
            header('Location: index.php?page=branches');
            exit;
        }
    }
}
