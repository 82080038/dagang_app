<?php
/**
 * Inventory Controller
 * Handle stock management
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/Product.php';

class InventoryController extends Controller {
    private $inventoryModel;
    private $branchModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->inventoryModel = new Inventory();
        $this->branchModel = new Branch();
        $this->productModel = new Product();
    }
    
    /**
     * Display inventory list
     */
    public function index() {
        $this->requireAuth();
        
        $branchId = $_GET['branch_id'] ?? null;
        $page = $_GET['page_num'] ?? 1;
        $search = $_GET['q'] ?? '';
        
        // If no branch selected, get the first available one or show selection
        // For simplicity, let's get all branches to populate dropdown
        $branches = $this->branchModel->getAll(100); // Limit 100 for now
        
        if (!$branchId && !empty($branches)) {
            $branchId = $branches[0]['id_branch'];
        }
        
        if ($this->isAjaxRequest()) {
            if (!$branchId) {
                $this->json(['status' => 'error', 'message' => 'Branch ID required']);
                return;
            }
            
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $inventory = $this->inventoryModel->getBranchInventory($branchId, $limit, $offset, $search);
            $totalItems = $this->inventoryModel->getTotalBranchInventory($branchId, $search);
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'inventory' => $inventory,
                    'pagination' => [
                        'current_page' => (int)$page,
                        'total_pages' => ceil($totalItems / $limit),
                        'total_items' => $totalItems
                    ]
                ]
            ]);
            return;
        }
        
        $data = [
            'title' => 'Manajemen Inventaris',
            'branches' => $branches,
            'selected_branch' => $branchId,
            'search' => $search
        ];
        
        $this->render('inventory/index', $data);
    }
    
    /**
     * Update stock (AJAX)
     */
    public function update() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF
        // $this->verifyCsrf(); // Uncomment if CSRF is implemented in frontend
        
        $branchId = $_POST['branch_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 0;
        $type = $_POST['type'] ?? 'in'; // in, out, adjustment
        $notes = $_POST['notes'] ?? '';
        
        if (!$branchId || !$productId || !$quantity) {
            $this->json(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'] ?? 1; // Fallback to 1 if session not set (dev)
            
            $this->inventoryModel->updateStock(
                $branchId, 
                $productId, 
                $quantity, 
                $type, 
                $notes, 
                $userId,
                'manual_update',
                null
            );
            
            $this->json(['status' => 'success', 'message' => 'Stok berhasil diperbarui']);
            
        } catch (Exception $e) {
            $this->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
