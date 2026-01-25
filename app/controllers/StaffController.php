<?php
/**
 * Staff Controller
 * Handle CRUD operations for staff members with role management
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../config/constants.php';

class StaffController extends Controller {
    private $memberModel;
    private $branchModel;
    private $companyModel;
    
    public function __construct() {
        parent::__construct();
        $this->memberModel = new Member();
        $this->branchModel = new Branch();
        $this->companyModel = new Company();
    }
    
    /**
     * Display staff list
     */
    public function index() {
        $this->requirePermission(ROLE_MANAGER);
        
        $page = $_GET['page_num'] ?? 1;
        $search = $_GET['q'] ?? '';
        $branchId = $_GET['branch_id'] ?? null;
        
        // Filter staff based on user role
        $businessRole = $this->getUserRole();
        $userCompanyId = $this->getUserCompanyId();
        $userBranchId = $this->getUserBranchId();
        
        if ($businessRole <= ROLE_COMPANY_OWNER) {
            // Company owners can see all staff in their company
            $staff = $this->memberModel->getAll($limit = 10, $offset = ($page - 1) * 10, $search, $userCompanyId);
            $totalStaff = count($this->memberModel->getAll($search, $userCompanyId));
        } else if ($businessRole <= ROLE_MANAGER) {
            // Managers can see staff in their branch
            $staff = $this->memberModel->getAll($limit = 10, $offset = ($page - 1) * 10, $search, null, $userBranchId);
            $totalStaff = count($this->memberModel->getAll($search, null, $userBranchId));
        } else {
            // Other users can only see themselves
            $staff = $this->memberModel->getById($this->getUserId());
            $totalStaff = $staff ? 1 : 0;
        }
        
        // Handle AJAX request
        if ($this->isAjaxRequest()) {
            $filters = [];
            if ($branchId) $filters['branch_id'] = $branchId;
            
            $staff = $this->memberModel->getAll($limit = 10, $offset = ($page - 1) * 10, $filters);
            $totalStaff = count($this->memberModel->getAll($filters));
            
            $this->json([
                'status' => 'success',
                'data' => $staff,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalStaff / 10),
                    'total_records' => $totalStaff
                ]
            ]);
        } else {
            // Load branches for filter dropdown
            $branches = [];
            $userRole = $this->getUserRole();
            $userCompanyId = $this->getUserCompanyId();
            
            if ($userRole <= ROLE_COMPANY_OWNER) {
                // Company owners can see all branches in their company
                $branches = $this->branchModel->getByCompany($userCompanyId);
            } else if ($userRole <= ROLE_MANAGER) {
                // Managers can see their branch
                $branches = $this->branchModel->getById($userBranchId);
                $branches = $branches ? [$branches] : [];
            }
            
            $data = [
                'title' => 'Manajemen Staff',
                'staff' => $staff,
                'branches' => $branches,
                'userRole' => $userRole,
                'userCompanyId' => $userCompanyId,
                'userBranchId' => $userBranchId,
                'search' => $search,
                'current_page' => $page,
                'total_pages' => ceil($totalStaff / 10),
                'total_records' => $totalStaff
            ];
            
            $this->view->render('staff/index', $data);
        }
    }
    
    /**
     * Create new staff member
     */
    public function create() {
        $this->requirePermission(ROLE_MANAGER);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Validate data
            $errors = $this->memberModel->validateMember($data);
            
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
                // Add staff member
                $memberId = $this->memberModel->create($data);
                
                if ($memberId) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Staff member created successfully',
                        'data' => [
                            'id_member' => $memberId,
                            'member_name' => $data['member_name'],
                            'member_code' => $data['member_code']
                        ]
                    ];
                    $this->json($response);
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Failed to create staff member'
                    ];
                    $this->json($response);
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to create staff member: ' . $e->getMessage()
                ];
                $this->json($response);
            }
        } else {
            // Load branches for dropdown
            $branches = [];
            $userRole = $this->getUserRole();
            $userCompanyId = $this->getUserCompanyId();
            $userBranchId = $this->getUserBranchId();
            
            if ($userRole <= ROLE_COMPANY_OWNER) {
                // Company owners can create staff in any branch of their company
                $branches = $this->branchModel->getByCompany($userCompanyId);
            } else if ($userRole <= ROLE_MANAGER) {
                // Managers can create staff in their branch only
                $branches = $this->branchModel->getById($userBranchId);
                $branches = $branches ? [$branches] : [];
            }
            
            $data = [
                'title' => 'Tambah Staff Baru',
                'branches' => $branches,
                'userRole' => $userRole,
                'userCompanyId' => $userCompanyId,
                'userBranchId' => $userBranchId
            ];
            
            $this->view->render('staff/create', $data);
        }
    }
    
    /**
     * Edit staff member
     */
    public function edit($id) {
        $this->requirePermission(ROLE_MANAGER);
        
        $staff = $this->memberModel->getById($id);
        
        if (!$staff) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Staff member not found'
                ], 404);
            } else {
                $_SESSION['flash_error'] = 'Staff member not found';
                header('Location: index.php?page=staff');
                exit;
            }
        }
        
        // Check if user can edit this staff member
        if (!$this->canAccessStaff($staff)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Access denied. You cannot edit this staff member.'
                ], 403);
            } else {
                $_SESSION['flash_error'] = 'Access denied. You cannot edit this staff member.';
                header('Location: index.php?page=staff');
                exit;
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['id_member'] = $id;
            
            // Validate data
            $errors = $this->memberModel->validateMember($data);
            
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
                // Update staff member
                $success = $this->memberModel->update($id, $data);
                
                if ($success) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Staff member updated successfully'
                    ];
                    $this->json($response);
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Failed to update staff member'
                    ];
                    $this->json($response);
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to update staff member: ' . $e->getMessage()
                ];
                $this->json($response);
            }
        } else {
            // Load branches for dropdown
            $branches = [];
            $userRole = $this->getUserRole();
            $userCompanyId = $this->getUserCompanyId();
            $userBranchId = $this->getUserBranchId();
            
            if ($userRole <= ROLE_COMPANY_OWNER) {
                // Company owners can move staff to any branch of their company
                $branches = $this->branchModel->getByCompany($userCompanyId);
            } else if ($userRole <= ROLE_MANAGER) {
                // Managers can move staff within their branch only
                $branches = $this->branchModel->getById($userBranchId);
                $branches = $branches ? [$branches] : [];
            }
            
            $data = [
                'title' => 'Edit Staff',
                'staff' => $staff,
                'branches' => $branches,
                'userRole' => $userRole,
                'userCompanyId' => $userCompanyId,
                'userBranchId' => $userBranchId
            ];
            
            $this->view->render('staff/edit', $data);
        }
    }
    
    /**
     * Delete staff member
     */
    public function delete($id) {
        $this->requirePermission(ROLE_MANAGER);
        
        $staff = $this->memberModel->getById($id);
        
        if (!$staff) {
            $this->json([
                'status' => 'error',
                'message' => 'Staff member not found'
            ], 404);
            return;
        }
        
        // Check if user can delete this staff member
        if (!$this->canAccessStaff($staff)) {
            $this->json([
                'status' => 'error',
                'message' => 'Access denied. You cannot delete this staff member.'
            ], 403);
            return;
        }
        
        try {
            // Soft delete staff member
            $success = $this->memberModel->delete($id);
            
            if ($success) {
                $response = [
                    'status' => 'success',
                    'message' => 'Staff member deleted successfully'
                ];
                $this->json($response);
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to delete staff member'
                ];
                $this->json($response);
            }
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Failed to delete staff member: ' . $e->getMessage()
            ];
            $this->json($response);
        }
    }
    
    /**
     * Get staff member details (AJAX)
     */
    public function details($id) {
        $this->requirePermission(ROLE_MANAGER);
        
        $staff = $this->memberModel->getById($id);
        
        if (!$staff) {
            $this->json([
                'status' => 'error',
                'message' => 'Staff member not found'
            ], 404);
            return;
        }
        
        // Check if user can access this staff member
        if (!$this->canAccessStaff($staff)) {
            $this->json([
                'status' => 'error',
                'message' => 'Access denied. You cannot view this staff member.'
            ], 403);
            return;
        }
        
        // Get branch details
        $branch = $this->branchModel->getById($staff['branch_id']);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'staff' => $staff,
                'branch' => $branch
            ]
        ]);
    }
    
    /**
     * Search staff members (AJAX)
     */
    public function search() {
        $this->requirePermission(ROLE_MANAGER);
        
        $keyword = $_GET['q'] ?? '';
        
        if (empty($keyword)) {
            $this->json([
                'status' => 'error',
                'message' => 'Search keyword is required'
            ]);
        }
        
        try {
            $userRole = $this->getUserRole();
            $userCompanyId = $this->getUserCompanyId();
            $userBranchId = $this->getUserBranchId();
            
            $staff = [];
            
            if ($userRole <= ROLE_COMPANY_OWNER) {
                // Company owners can search all staff in their company
                $staff = $this->memberModel->search($keyword, $userCompanyId);
            } else if ($userRole <= ROLE_MANAGER) {
                // Managers can search staff in their branch
                $staff = $this->memberModel->search($keyword, null, $userBranchId);
            }
            
            $this->json([
                'status' => 'success',
                'data' => $staff
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Search failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check if user can access staff member
     */
    private function canAccessStaff($staff) {
        $appRole = $this->getAppRole();
        
        // Application owners can access all staff
        if ($appRole <= ROLE_APP_ADMIN) {
            return true;
        }
        
        $userRole = $this->getUserRole();
        $userCompanyId = $this->getUserCompanyId();
        $userBranchId = $this->getUserBranchId();
        
        // Company owners can access all staff in their company
        if ($userRole <= ROLE_COMPANY_OWNER && $staff['company_id'] == $userCompanyId) {
            return true;
        }
        
        // Managers can access staff in their branch
        if ($userRole <= ROLE_MANAGER && $staff['branch_id'] == $userBranchId) {
            return true;
        }
        
        // Staff can access their own record
        if ($staff['id_member'] == $this->getUserId()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Transfer staff between branches
     */
    public function transfer() {
        $this->requirePermission(ROLE_MANAGER);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $staffId = $_POST['staff_id'] ?? '';
            $fromBranchId = $_POST['from_branch_id'] ?? '';
            $toBranchId = $_POST['to_branch_id'] ?? '';
            $transferDate = $_POST['transfer_date'] ?? date('Y-m-d');
            $notes = $_POST['notes'] ?? '';
            
            if (empty($staffId) || empty($fromBranchId) || empty($toBranchId)) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Staff ID, from branch, and to branch are required'
                ]);
                return;
            }
            
            if ($fromBranchId === $toBranchId) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Cannot transfer to the same branch'
                ]);
                return;
            }
            
            // Check if user can access both branches
            if (!$this->canAccessBranch($fromBranchId) || !$this->canAccessBranch($toBranchId)) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Access denied. You cannot access one or both branches.'
                ], 403);
                return;
            }
            
            try {
                // Get staff member
                $staff = $this->memberModel->getById($staffId);
                
                if (!$staff) {
                    $this->json([
                        'status' => 'error',
                        'message' => 'Staff member not found'
                    ], 404);
                    return;
                }
                
                // Update staff branch
                $success = $this->memberModel->update($staffId, [
                    'branch_id' => $toBranchId,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                if ($success) {
                    // Log transfer (you might want to create a transfer log table)
                    $this->logTransfer($staffId, $fromBranchId, $toBranchId, $transferDate, $notes);
                    
                    $this->json([
                        'status' => 'success',
                        'message' => 'Staff berhasil dipindahkan ke cabang baru'
                    ]);
                } else {
                    $this->json([
                        'status' => 'error',
                        'message' => 'Gagal memindahkan staff'
                    ]);
                }
            } catch (Exception $e) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Gagal memindahkan staff: ' . $e->getMessage()
                ]);
            }
        } else {
            // Load branches for dropdown
            $branches = [];
            $userRole = $this->getUserRole();
            $userCompanyId = $this->getUserCompanyId();
            $userBranchId = $this->getUserBranchId();
            
            if ($userRole <= ROLE_COMPANY_OWNER) {
                // Company owners can transfer between any branches in their company
                $branches = $this->branchModel->getByCompany($userCompanyId);
            } else if ($userRole <= ROLE_MANAGER) {
                // Managers can only transfer within their branch (limited)
                $branches = $this->branchModel->getById($userBranchId);
                $branches = $branches ? [$branches] : [];
            }
            
            $data = [
                'title' => 'Pindahkan Staff',
                'branches' => $branches,
                'userRole' => $userRole,
                'userCompanyId' => $userCompanyId,
                'userBranchId' => $userBranchId
            ];
            
            $this->view->render('staff/transfer', $data);
        }
    }
    
    /**
     * Bulk operations on staff
     */
    public function bulkAction() {
        $this->requirePermission(ROLE_MANAGER);
        
        $action = $_POST['bulk_action'] ?? '';
        $staffIds = $_POST['staff_ids'] ?? [];
        
        if (empty($action) || empty($staffIds)) {
            $this->json([
                'status' => 'error',
                'message' => 'Pilih aksi dan staff yang akan diproses'
            ]);
            return;
        }
        
        $results = [
            'success' => 0,
            'error' => 0,
            'errors' => []
        ];
        
        foreach ($staffIds as $staffId) {
            try {
                // Check access for each staff member
                $staff = $this->memberModel->getById($staffId);
                if (!$this->canAccessStaff($staff)) {
                    $results['error']++;
                    $results['errors'][] = "Staff ID $staffId: Access denied";
                    continue;
                }
                
                switch ($action) {
                    case 'activate':
                        $success = $this->memberModel->update($staffId, ['is_active' => 1]);
                        if ($success) $results['success']++;
                        else $results['error']++;
                        break;
                        
                    case 'deactivate':
                        $success = $this->memberModel->update($staffId, ['is_active' => 0]);
                        if ($success) $results['success']++;
                        else $results['error']++;
                        break;
                        
                    case 'delete':
                        // Soft delete
                        $success = $this->memberModel->delete($staffId);
                        if ($success) $results['success']++;
                        else $results['error']++;
                        break;
                        
                    case 'promote_to_manager':
                        $success = $this->memberModel->update($staffId, ['position' => 'manager']);
                        if ($success) $results['success']++;
                        else $results['error']++;
                        break;
                        
                    case 'demote_to_staff':
                        $success = $this->memberModel->update($staffId, ['position' => 'staff']);
                        if ($success) $results['success']++;
                        else $results['error']++;
                        break;
                        
                    default:
                        $results['error']++;
                        $results['errors'][] = "Staff ID $staffId: Aksi tidak valid";
                }
            } catch (Exception $e) {
                $results['error']++;
                $results['errors'][] = "Staff ID $staffId: " . $e->getMessage();
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
     * Log staff transfer
     */
    private function logTransfer($staffId, $fromBranchId, $toBranchId, $transferDate, $notes) {
        try {
            // You might want to create a staff_transfers table
            // For now, we'll just log it to a file or session
            $logData = [
                'staff_id' => $staffId,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'transfer_date' => $transferDate,
                'notes' => $notes,
                'transferred_by' => $this->getUserId(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Log to session for now (you can improve this with database logging)
            if (!isset($_SESSION['transfer_logs'])) {
                $_SESSION['transfer_logs'] = [];
            }
            $_SESSION['transfer_logs'][] = $logData;
            
        } catch (Exception $e) {
            // Log error but don't fail the transfer
            error_log("Failed to log staff transfer: " . $e->getMessage());
        }
    }
}
?>
