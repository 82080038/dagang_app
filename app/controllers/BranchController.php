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
    private $memberModel;
    
    public function __construct() {
        parent::__construct();
        $this->branchModel = new Branch();
        $this->companyModel = new Company();
        $this->memberModel = new Member();
    }
    
    /**
     * Display branches list
     */
    public function index() {
        $this->requirePermission(ROLE_COMPANY_OWNER);
        
        $page = $_GET['page_num'] ?? 1;
        $search = $_GET['q'] ?? '';
        $companyId = $_GET['company_id'] ?? null;
        
        // Filter branches based on user role
        $businessRole = $this->getUserRole();
        $userCompanyId = $this->getUserCompanyId();
        $userBranchId = $this->getUserBranchId();
        
        if ($businessRole <= ROLE_COMPANY_OWNER) {
            // Company owners can see all branches in their company
            $filters = ['company_id' => $userCompanyId];
            if ($search) $filters['search'] = $search;
            $branches = $this->branchModel->getAll($limit = 10, $offset = ($page - 1) * 10, $filters);
            $totalBranches = $this->branchModel->getTotalCount($filters);
        } else if ($businessRole <= ROLE_BRANCH_OWNER) {
            // Branch owners can see all branches (for now, TODO: limit to their company)
            $filters = ['company_id' => $userCompanyId];
            if ($search) $filters['search'] = $search;
            $branches = $this->branchModel->getAll($limit = 10, $offset = ($page - 1) * 10, $filters);
            $totalBranches = $this->branchModel->getTotalCount($filters);
        } else {
            // Other users can only see their own branch
            $branches = $this->branchModel->getById($userBranchId);
            $totalBranches = $branches ? 1 : 0;
        }
        
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
        
        $this->view->render('branches/index', $data);
    }
    
    /**
     * Create new branch
     */
    public function create() {
        $this->requirePermission(ROLE_COMPANY_OWNER);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Add branch type classification
            $branchType = $data['branch_operation_type'] ?? 'with_staff';
            $data['branch_operation_type'] = $branchType;
            
            // Validate data
            $errors = $this->branchModel->validateBranch($data);
            
            if (!empty($errors)) {
                $response = [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
                $this->json($response);
                return;
            }
            
            try {
                // Add branch
                $branchId = $this->branchModel->create($data);
                
                if ($branchId) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Branch created successfully',
                        'data' => [
                            'id_branch' => $branchId,
                            'branch_name' => $data['branch_name'],
                            'branch_code' => $data['branch_code'],
                            'operation_type' => $branchType
                        ]
                    ];
                    $this->json($response);
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Failed to create branch'
                    ];
                    $this->json($response);
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to create branch: ' . $e->getMessage()
                ];
                $this->json($response);
            }
        } else {
            // Load companies for dropdown
            $companies = [];
            $userRole = $this->getUserRole();
            $userCompanyId = $this->getUserCompanyId();
            $userBranchId = $this->getUserBranchId();
            
            if ($userRole <= ROLE_COMPANY_OWNER) {
                // Company owners can create branches in their company
                $companies = $this->companyModel->getAll();
            } else if ($userRole <= ROLE_APP_ADMIN) {
                // Application admins can create branches for any company
                $companies = $this->companyModel->getAll();
            }
            
            $data = [
                'title' => 'Tambah Cabang Baru',
                'companies' => $this->companyModel->getOptions(),
                'userRole' => $userRole,
                'userCompanyId' => $userCompanyId,
                'userBranchId' => $userBranchId
            ];
            
            $this->view->render('branches/create', $data);
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
            
            $this->view->render('branches/edit', $data);
        }
    }
    
    /**
     * Delete branch (soft delete)
     */
    public function delete($id) {
        $this->requirePermission(ROLE_COMPANY_OWNER);
        
        $branch = $this->branchModel->getById($id);
        
        if (!$branch) {
            $this->json([
                'status' => 'error',
                'message' => 'Cabang tidak ditemukan'
            ], 404);
            return;
        }
        
        // Check if user can delete this branch
        if (!$this->canAccessBranch($id)) {
            $this->json([
                'status' => 'error',
                'message' => 'Access denied. Anda tidak dapat menghapus cabang ini.'
            ], 403);
            return;
        }
        
        // Check if branch has active staff members
        $staffCount = $this->memberModel->getByBranch($id);
        $staffCount = count($staffCount);
        
        if ($staffCount > 0) {
            $this->json([
                'status' => 'error',
                'message' => "Tidak dapat menghapus cabang yang masih memiliki $staffCount staff aktif. Pindahkan atau non-aktifkan staff terlebih dahulu.",
                'staff_count' => $staffCount
            ], 400);
            return;
        }
        
        // Check if branch has active transactions
        $transactionCount = $this->getTransactionCount($id);
        
        if ($transactionCount > 0) {
            $this->json([
                'status' => 'warning',
                'message' => "Cabang memiliki $transactionCount transaksi. Apakah Anda yakin ingin menon-aktifkan cabang ini?",
                'transaction_count' => $transactionCount,
                'can_deactivate' => true
            ]);
            return;
        }
        
        try {
            // Soft delete (deactivate) branch
            $success = $this->branchModel->deactivate($id);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Cabang berhasil dinon-aktifkan'
                ]);
            } else {
                $this->json([
                    'status' => 'error',
                    'message' => 'Gagal menon-aktifkan cabang'
                ]);
            }
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal menon-aktifkan cabang: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Activate/Deactivate branch status
     */
    public function toggleStatus($id) {
        $this->requirePermission(ROLE_COMPANY_OWNER);
        
        $branch = $this->branchModel->getById($id);
        
        if (!$branch) {
            $this->json([
                'status' => 'error',
                'message' => 'Cabang tidak ditemukan'
            ], 404);
            return;
        }
        
        // Check if user can modify this branch
        if (!$this->canAccessBranch($id)) {
            $this->json([
                'status' => 'error',
                'message' => 'Access denied. Anda tidak dapat mengubah status cabang ini.'
            ], 403);
            return;
        }
        
        $newStatus = $branch['is_active'] ? 0 : 1;
        $statusText = $newStatus ? 'diaktifkan' : 'dinon-aktifkan';
        
        try {
            $success = $this->branchModel->update($id, ['is_active' => $newStatus]);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => "Cabang berhasil $statusText",
                    'new_status' => $newStatus
                ]);
            } else {
                $this->json([
                    'status' => 'error',
                    'message' => "Gagal $statusText cabang"
                ]);
            }
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => "Gagal $statusText cabang: " . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Bulk operations on branches
     */
    public function bulkAction() {
        $this->requirePermission(ROLE_COMPANY_OWNER);
        
        $action = $_POST['bulk_action'] ?? '';
        $branchIds = $_POST['branch_ids'] ?? [];
        
        if (empty($action) || empty($branchIds)) {
            $this->json([
                'status' => 'error',
                'message' => 'Pilih aksi dan cabang yang akan diproses'
            ]);
            return;
        }
        
        $results = [
            'success' => 0,
            'error' => 0,
            'errors' => []
        ];
        
        foreach ($branchIds as $branchId) {
            try {
                // Check access for each branch
                if (!$this->canAccessBranch($branchId)) {
                    $results['error']++;
                    $results['errors'][] = "Cabang ID $branchId: Access denied";
                    continue;
                }
                
                switch ($action) {
                    case 'activate':
                        $success = $this->branchModel->update($branchId, ['is_active' => 1]);
                        if ($success) $results['success']++;
                        else $results['error']++;
                        break;
                        
                    case 'deactivate':
                        // Check if branch has active staff
                        $staffCount = $this->memberModel->getByBranch($branchId);
                        $staffCount = count($staffCount);
                        if ($staffCount > 0) {
                            $results['error']++;
                            $results['errors'][] = "Cabang ID $branchId: Masih memiliki $staffCount staff aktif";
                        } else {
                            $success = $this->branchModel->update($branchId, ['is_active' => 0]);
                            if ($success) $results['success']++;
                            else $results['error']++;
                        }
                        break;
                        
                    case 'delete':
                        // Soft delete
                        $success = $this->branchModel->deactivate($branchId);
                        if ($success) $results['success']++;
                        else $results['error']++;
                        break;
                        
                    default:
                        $results['error']++;
                        $results['errors'][] = "Cabang ID $branchId: Aksi tidak valid";
                }
            } catch (Exception $e) {
                $results['error']++;
                $results['errors'][] = "Cabang ID $branchId: " . $e->getMessage();
            }
        }
        
        $message = "Bulk operation completed. Success: {$results['success']}, Error: {$results['error']}";
        
        $this->json([
            'status' => $results['error'] > 0 ? 'warning' : 'success',
            'message' => $message,
            'results' => $results
        ]);
    }
    
    /**
     * Get branches by operation type
     */
    public function getByOperationType($operationType = null) {
        $sql = "SELECT b.*, c.company_name 
                FROM branches b
                LEFT JOIN companies c ON b.company_id = c.id_company";
        
        $params = [];
        
        if ($operationType) {
            $sql .= " WHERE b.branch_operation_type = :operation_type";
            $params['operation_type'] = $operationType;
        }
        
        $sql .= " ORDER BY b.branch_name";
        
        return $this->branchModel->query($sql, $params);
    }
    
    /**
     * Get branch statistics by operation type
     */
    public function getOperationTypeStats($companyId = null) {
        $sql = "SELECT 
                    branch_operation_type,
                    COUNT(*) as count,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_count
                FROM branches";
        
        $params = [];
        
        if ($companyId) {
            $sql .= " WHERE company_id = :company_id";
            $params['company_id'] = $companyId;
        }
        
        $sql .= " GROUP BY branch_operation_type";
        
        return $this->branchModel->query($sql, $params);
    }
    
    /**
     * Check if branch can operate without staff
     */
    public function canOperateWithoutStaff($branchId) {
        $branch = $this->branchModel->getById($branchId);
        
        if (!$branch) {
            return false;
        }
        
        // Solo operation branches can operate without staff
        return in_array($branch['branch_operation_type'], ['solo', 'minimal']);
    }
    
    /**
     * Get transaction count for a branch
     */
    private function getTransactionCount($branchId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM transactions WHERE branch_id = :branch_id";
            $result = $this->branchModel->queryOne($sql, ['branch_id' => $branchId]);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
