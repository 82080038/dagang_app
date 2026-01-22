<?php
/**
 * Transaction Controller
 * Handle POS and Sales
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/Inventory.php';

class TransactionController extends Controller {
    private $transactionModel;
    private $branchModel;
    private $inventoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->branchModel = new Branch();
        $this->inventoryModel = new Inventory();
    }
    
    /**
     * POS Interface
     */
    public function index() {
        $this->requireAuth();
        
        // Default to first branch or user's assigned branch
        $branchId = $_GET['branch_id'] ?? null;
        $branches = $this->branchModel->getAll(100);
        
        if (!$branchId && !empty($branches)) {
            $branchId = $branches[0]['id_branch'];
        }

        // Get products with stock for this branch
        // We can use AJAX to search/load products to avoid heavy initial load
        // But for initial view, maybe load top 20?
        
        $data = [
            'title' => 'Point of Sale (POS)',
            'branches' => $branches,
            'selected_branch' => $branchId
        ];
        
        $this->render('transactions/index', $data);
    }
    
    /**
     * Process Checkout (AJAX)
     */
    public function create() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
            $data = $_POST; // Fallback to standard POST
        }
        
        if (empty($data['branch_id']) || empty($data['items'])) {
            $this->json(['status' => 'error', 'message' => 'Invalid data']);
            return;
        }
        
        try {
            $transactionData = [
                'branch_id' => $data['branch_id'],
                'user_id' => $_SESSION['user_id'] ?? 1,
                'total_amount' => $data['total_amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => $data['notes'] ?? ''
            ];
            
            $result = $this->transactionModel->createTransaction($transactionData, $data['items']);
            
            $this->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil',
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            $this->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Search Products for POS (AJAX)
     */
    public function searchProducts() {
        $this->requireAuth();
        
        $branchId = $_GET['branch_id'] ?? null;
        $query = $_GET['q'] ?? '';
        
        if (!$branchId) {
            $this->json(['status' => 'error', 'message' => 'Branch ID required']);
            return;
        }
        
        $products = $this->inventoryModel->getBranchInventory($branchId, 20, 0, $query);
        
        $this->json([
            'status' => 'success',
            'data' => $products
        ]);
    }
    
    /**
     * Transaction History
     */
    public function history() {
        $this->requireAuth();
        
        $page = $_GET['page_num'] ?? 1;
        $branchId = $_GET['branch_id'] ?? null;
        
        $filters = [];
        if ($branchId) $filters['branch_id'] = $branchId;
        
        $transactions = $this->transactionModel->getAll(20, ($page - 1) * 20, $filters);
        $total = $this->transactionModel->getTotalCount($filters);
        $branches = $this->branchModel->getAll(100);
        
        $data = [
            'title' => 'Riwayat Transaksi',
            'transactions' => $transactions,
            'branches' => $branches,
            'selected_branch' => $branchId,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total / 20)
            ]
        ];
        
        $this->render('transactions/history', $data);
    }
    
    /**
     * Transaction Receipt/Details
     */
    public function details($id) {
        $this->requireAuth();
        
        $transaction = $this->transactionModel->getDetails($id);
        
        if (!$transaction) {
            // 404
            echo "Transaction not found";
            return;
        }
        
        if ($this->isAjaxRequest()) {
            $this->json(['status' => 'success', 'data' => $transaction]);
            return;
        }
        
        // Render print view or modal content
        $data = [
            'transaction' => $transaction
        ];
        
        // Maybe render a partial if it's for a modal
        // For now, let's just return JSON or implement a simple view later
        // If this is called directly, maybe show a printable page
        $this->render('transactions/receipt', $data); // We need to create this view
    }
}
