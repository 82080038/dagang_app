<?php
/**
 * Product Transfer Controller
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/ProductTransfer.php';
require_once __DIR__ . '/../config/constants.php';

class ProductTransferController extends Controller {
    private $productModel;
    private $inventoryModel;
    private $transferModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->inventoryModel = new Inventory();
        $this->transferModel = new ProductTransfer();
    }
    
    /**
     * Display transfer dashboard
     */
    public function index() {
        $this->requirePermission(ROLE_MANAGER);
        $this->requireFeature('product_transfers');
        
        $page = $_GET['page_num'] ?? 1;
        $search = $_GET['q'] ?? '';
        $transferType = $_GET['transfer_type'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        $userRole = $this->getUserRole();
        $userCompanyId = $this->getUserCompanyId();
        $userBranchId = $this->getUserBranchId();
        
        // Get transfers based on user role
        if ($userRole <= ROLE_COMPANY_OWNER) {
            // Company owners can see all transfers in their company
            $transfers = $this->transferModel->getAll($page, $search, $transferType, $dateFrom, $dateTo, $userCompanyId);
            $totalTransfers = $this->transferModel->getTotalCount($search, $transferType, $dateFrom, $dateTo, $userCompanyId);
        } else if ($userRole <= ROLE_MANAGER) {
            // Managers can see transfers involving their branch
            $transfers = $this->transferModel->getByBranch($userBranchId, $page, $search, $transferType, $dateFrom, $dateTo);
            $totalTransfers = $this->transferModel->getByBranchCount($userBranchId, $search, $transferType, $dateFrom, $dateTo);
        } else {
            $transfers = [];
            $totalTransfers = 0;
        }
        
        $data = [
            'title' => 'Transfer Produk',
            'transfers' => $transfers,
            'totalTransfers' => $totalTransfers,
            'currentPage' => $page,
            'search' => $search,
            'transferType' => $transferType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'userRole' => $userRole,
            'userCompanyId' => $userCompanyId,
            'userBranchId' => $userBranchId
        ];
        
        $this->render('product-transfers/index', $data);
    }
    
    /**
     * Create new transfer
     */
    public function create() {
        $this->requirePermission(ROLE_MANAGER);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Validate transfer data
            $errors = $this->transferModel->validateTransfer($data);
            
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
                // Check if user can perform this transfer
                if (!$this->canPerformTransfer($data)) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Access denied. Anda tidak dapat melakukan transfer ini.'
                    ];
                    $this->json($response);
                    return;
                }
                
                // Check product availability
                $product = $this->productModel->getById($data['product_id']);
                if (!$product) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Produk tidak ditemukan'
                    ];
                    $this->json($response);
                    return;
                }
                
                // Check source inventory
                $sourceInventory = $this->inventoryModel->getByProductAndLocation(
                    $data['product_id'], 
                    $data['from_type'], 
                    $data['from_id']
                );
                
                if (!$sourceInventory || $sourceInventory['stock_quantity'] < $data['quantity']) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Stok tidak mencukupi. Stok tersedia: ' . ($sourceInventory['stock_quantity'] ?? 0)
                    ];
                    $this->json($response);
                    return;
                }
                
                // Create transfer
                $transferData = [
                    'product_id' => $data['product_id'],
                    'from_type' => $data['from_type'],
                    'from_id' => $data['from_id'],
                    'to_type' => $data['to_type'],
                    'to_id' => $data['to_id'],
                    'quantity' => $data['quantity'],
                    'transfer_date' => $data['transfer_date'] ?? date('Y-m-d'),
                    'notes' => $data['notes'] ?? '',
                    'status' => 'pending',
                    'created_by' => $this->getUserId(),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $transferId = $this->transferModel->create($transferData);
                
                if ($transferId) {
                    // Process the transfer
                    $this->processTransfer($transferId);
                    
                    $response = [
                        'status' => 'success',
                        'message' => 'Transfer berhasil dibuat',
                        'data' => [
                            'transfer_id' => $transferId,
                            'product_name' => $product['product_name'],
                            'quantity' => $data['quantity']
                        ]
                    ];
                    $this->json($response);
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Gagal membuat transfer'
                    ];
                    $this->json($response);
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => 'Gagal membuat transfer: ' . $e->getMessage()
                ];
                $this->json($response);
            }
        } else {
            // Load products and locations
            $userRole = $this->getUserRole();
            $userCompanyId = $this->getUserCompanyId();
            $userBranchId = $this->getUserBranchId();
            
            // Get products
            $products = $this->productModel->getActive();
            
            // Get available locations based on user role
            $locations = $this->getAvailableLocations($userRole, $userCompanyId, $userBranchId);
            
            $data = [
                'title' => 'Transfer Produk Baru',
                'products' => $products,
                'locations' => $locations,
                'userRole' => $userRole,
                'userCompanyId' => $userCompanyId,
                'userBranchId' => $userBranchId
            ];
            
            $this->render('product-transfers/create', $data);
        }
    }
    
    /**
     * Process transfer (update inventories)
     */
    public function processTransfer($transferId) {
        $this->requirePermission(ROLE_MANAGER);
        
        try {
            $transfer = $this->transferModel->getById($transferId);
            
            if (!$transfer) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Transfer tidak ditemukan'
                ], 404);
                return;
            }
            
            if ($transfer['status'] !== 'pending') {
                $this->json([
                    'status' => 'error',
                    'message' => 'Transfer sudah diproses'
                ]);
                return;
            }
            
            // Check if user can process this transfer
            if (!$this->canProcessTransfer($transfer)) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Access denied. Anda tidak dapat memproses transfer ini.'
                ], 403);
                return;
            }
            
            // Start transaction
            $this->inventoryModel->beginTransaction();
            
            try {
                // Deduct from source inventory
                $sourceInventory = $this->inventoryModel->getByProductAndLocation(
                    $transfer['product_id'],
                    $transfer['from_type'],
                    $transfer['from_id']
                );
                
                if ($sourceInventory && $sourceInventory['stock_quantity'] >= $transfer['quantity']) {
                    $this->inventoryModel->updateStock(
                        $sourceInventory['id_inventory'],
                        $sourceInventory['stock_quantity'] - $transfer['quantity']
                    );
                } else {
                    throw new Exception('Stok sumber tidak mencukupi');
                }
                
                // Add to destination inventory
                $destInventory = $this->inventoryModel->getByProductAndLocation(
                    $transfer['product_id'],
                    $transfer['to_type'],
                    $transfer['to_id']
                );
                
                if ($destInventory) {
                    // Update existing inventory
                    $this->inventoryModel->updateStock(
                        $destInventory['id_inventory'],
                        $destInventory['stock_quantity'] + $transfer['quantity']
                    );
                } else {
                    // Create new inventory record
                    $inventoryData = [
                        'product_id' => $transfer['product_id'],
                        'location_type' => $transfer['to_type'],
                        'location_id' => $transfer['to_id'],
                        'stock_quantity' => $transfer['quantity'],
                        'min_stock' => 0,
                        'max_stock' => 999999,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->inventoryModel->create($inventoryData);
                }
                
                // Update transfer status
                $this->transferModel->update($transferId, [
                    'status' => 'completed',
                    'processed_at' => date('Y-m-d H:i:s'),
                    'processed_by' => $this->getUserId()
                ]);
                
                // Commit transaction
                $this->inventoryModel->commit();
                
                $this->json([
                    'status' => 'success',
                    'message' => 'Transfer berhasil diproses'
                ]);
                
            } catch (Exception $e) {
                // Rollback transaction
                $this->inventoryModel->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memproses transfer: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Cancel transfer
     */
    public function cancel($transferId) {
        $this->requirePermission(ROLE_MANAGER);
        
        try {
            $transfer = $this->transferModel->getById($transferId);
            
            if (!$transfer) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Transfer tidak ditemukan'
                ], 404);
                return;
            }
            
            if ($transfer['status'] !== 'pending') {
                $this->json([
                    'status' => 'error',
                    'message' => 'Hanya transfer pending yang dapat dibatalkan'
                ]);
                return;
            }
            
            // Check if user can cancel this transfer
            if (!$this->canCancelTransfer($transfer)) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Access denied. Anda tidak dapat membatalkan transfer ini.'
                ], 403);
                return;
            }
            
            $success = $this->transferModel->update($transferId, [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancelled_by' => $this->getUserId()
            ]);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Transfer berhasil dibatalkan'
                ]);
            } else {
                $this->json([
                    'status' => 'error',
                    'message' => 'Gagal membatalkan transfer'
                ]);
            }
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan transfer: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get transfer details
     */
    public function details($transferId) {
        $this->requirePermission(ROLE_MANAGER);
        
        $transfer = $this->transferModel->getById($transferId);
        
        if (!$transfer) {
            $this->json([
                'status' => 'error',
                'message' => 'Transfer tidak ditemukan'
            ], 404);
            return;
        }
        
        // Check if user can view this transfer
        if (!$this->canViewTransfer($transfer)) {
            $this->json([
                'status' => 'error',
                'message' => 'Access denied. Anda tidak dapat melihat transfer ini.'
            ], 403);
            return;
        }
        
        // Get product details
        $product = $this->productModel->getById($transfer['product_id']);
        
        // Get location details
        $fromLocation = $this->getLocationDetails($transfer['from_type'], $transfer['from_id']);
        $toLocation = $this->getLocationDetails($transfer['to_type'], $transfer['to_id']);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'transfer' => $transfer,
                'product' => $product,
                'from_location' => $fromLocation,
                'to_location' => $toLocation
            ]
        ]);
    }
    
    /**
     * Bulk transfer operations
     */
    public function bulkTransfer() {
        $this->requirePermission(ROLE_MANAGER);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transfers = $_POST['transfers'] ?? [];
            $results = ['success' => 0, 'error' => 0, 'errors' => []];
            
            foreach ($transfers as $transferData) {
                try {
                    // Validate each transfer
                    $errors = $this->transferModel->validateTransfer($transferData);
                    if (!empty($errors)) {
                        $results['error']++;
                        $results['errors'][] = 'Transfer validation failed';
                        continue;
                    }
                    
                    // Check availability
                    $sourceInventory = $this->inventoryModel->getByProductAndLocation(
                        $transferData['product_id'],
                        $transferData['from_type'],
                        $transferData['from_id']
                    );
                    
                    if (!$sourceInventory || $sourceInventory['stock_quantity'] < $transferData['quantity']) {
                        $results['error']++;
                        $results['errors'][] = 'Insufficient stock for product ID ' . $transferData['product_id'];
                        continue;
                    }
                    
                    // Create transfer
                    $transferId = $this->transferModel->create([
                        'product_id' => $transferData['product_id'],
                        'from_type' => $transferData['from_type'],
                        'from_id' => $transferData['from_id'],
                        'to_type' => $transferData['to_type'],
                        'to_id' => $transferData['to_id'],
                        'quantity' => $transferData['quantity'],
                        'transfer_date' => $transferData['transfer_date'] ?? date('Y-m-d'),
                        'notes' => $transferData['notes'] ?? '',
                        'status' => 'pending',
                        'created_by' => $this->getUserId(),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    if ($transferId) {
                        $this->processTransfer($transferId);
                        $results['success']++;
                    } else {
                        $results['error']++;
                        $results['errors'][] = 'Failed to create transfer for product ID ' . $transferData['product_id'];
                    }
                    
                } catch (Exception $e) {
                    $results['error']++;
                    $results['errors'][] = 'Error transferring product ID ' . $transferData['product_id'] . ': ' . $e->getMessage();
                }
            }
            
            $message = "Bulk transfer completed. Success: {$results['success']}, Error: {$results['error']}";
            
            $this->json([
                'status' => $results['error'] > 0 ? 'warning' : 'success',
                'message' => $message,
                'results' => $results
            ]);
        }
    }
    
    /**
     * Get available locations for transfer
     */
    private function getAvailableLocations($userRole, $userCompanyId, $userBranchId) {
        $locations = [];
        
        if ($userRole <= ROLE_COMPANY_OWNER) {
            // Company owners can transfer between company and all branches
            $locations['company'] = [
                'type' => 'company',
                'id' => $userCompanyId,
                'name' => 'Perusahaan (Central Stock)'
            ];
            
            // Get all branches
            $branchModel = new \Branch();
            $branches = $branchModel->getByCompany($userCompanyId);
            foreach ($branches as $branch) {
                $locations['branches'][] = [
                    'type' => 'branch',
                    'id' => $branch['id_branch'],
                    'name' => $branch['branch_name']
                ];
            }
        } else if ($userRole <= ROLE_MANAGER) {
            // Managers can transfer between their branch and company
            $locations['company'] = [
                'type' => 'company',
                'id' => $userCompanyId,
                'name' => 'Perusahaan (Central Stock)'
            ];
            
            $locations['branch'] = [
                'type' => 'branch',
                'id' => $userBranchId,
                'name' => 'Cabang Saat Ini'
            ];
        }
        
        return $locations;
    }
    
    /**
     * Check if user can perform transfer
     */
    private function canPerformTransfer($data) {
        $userRole = $this->getUserRole();
        $userCompanyId = $this->getUserCompanyId();
        $userBranchId = $this->getUserBranchId();
        
        // Company owners can transfer between company and any branch
        if ($userRole <= ROLE_COMPANY_OWNER) {
            return true;
        }
        
        // Managers can transfer between their branch and company
        if ($userRole <= ROLE_MANAGER) {
            // Check if transfer involves their branch or company
            $involvesBranch = ($data['from_type'] === 'branch' && $data['from_id'] == $userBranchId) ||
                              ($data['to_type'] === 'branch' && $data['to_id'] == $userBranchId);
            $involvesCompany = ($data['from_type'] === 'company' && $data['from_id'] == $userCompanyId) ||
                              ($data['to_type'] === 'company' && $data['to_id'] == $userCompanyId);
            
            return $involvesBranch && $involvesCompany;
        }
        
        return false;
    }
    
    /**
     * Check if user can process transfer
     */
    private function canProcessTransfer($transfer) {
        return $this->canPerformTransfer($transfer);
    }
    
    /**
     * Check if user can cancel transfer
     */
    private function canCancelTransfer($transfer) {
        return $this->canPerformTransfer($transfer);
    }
    
    /**
     * Check if user can view transfer
     */
    private function canViewTransfer($transfer) {
        return $this->canPerformTransfer($transfer);
    }
    
    /**
     * Get location details
     */
    private function getLocationDetails($type, $id) {
        if ($type === 'company') {
            $companyModel = new \Company();
            return $companyModel->getById($id);
        } else if ($type === 'branch') {
            $branchModel = new \Branch();
            return $branchModel->getById($id);
        }
        
        return null;
    }
}
?>
