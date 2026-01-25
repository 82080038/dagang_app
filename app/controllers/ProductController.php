<?php
/**
 * Product Controller
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController extends Controller {
    private $productModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * Product List (Index)
     */
    public function index() {
        $this->requireAuth();
        $this->requireFeature('products');
        
        $page = $_GET['page_num'] ?? 1;
        $search = $_GET['q'] ?? '';
        $categoryId = $_GET['category_id'] ?? null;
        
        // Handle AJAX request for datatables/search
        if ($this->isAjaxRequest()) {
            $filters = [];
            if ($search) $filters['search'] = $search;
            if ($categoryId) $filters['category_id'] = $categoryId;
            
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $products = $this->productModel->getAll($limit, $offset, $filters);
            $totalProducts = $this->productModel->getTotalCount($filters);
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'products' => $products,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => ceil($totalProducts / $limit),
                        'total_items' => $totalProducts
                    ]
                ]
            ]);
            return;
        }
        
        $categories = $this->categoryModel->getAll(100, 0, ['is_active' => 1]);
        
        $data = [
            'title' => 'Manajemen Produk',
            'categories' => $categories
        ];
        
        $this->view->render('products/index', $data);
    }
    
    /**
     * Create Product
     */
    public function create() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash']['error'] = 'Invalid security token';
                $this->redirect('index.php?page=products&action=create');
                return;
            }
            
            // Validate Input
            $required = ['product_name', 'category_id', 'selling_price'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $_SESSION['flash']['error'] = 'Mohon lengkapi semua field wajib';
                    $this->redirect('index.php?page=products&action=create');
                    return;
                }
            }
            
            // Prepare Data
            $data = [
                'product_code' => $_POST['product_code'] ?? $this->productModel->generateCode(),
                'product_name' => $_POST['product_name'],
                'category_id' => $_POST['category_id'],
                'description' => $_POST['description'] ?? '',
                'unit' => $_POST['unit'] ?? 'pcs',
                'purchase_price' => str_replace(['.', ','], '', $_POST['purchase_price'] ?? 0),
                'selling_price' => str_replace(['.', ','], '', $_POST['selling_price'] ?? 0),
                'barcode' => $_POST['barcode'] ?? '',
                'low_stock_threshold' => $_POST['low_stock_threshold'] ?? 10,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if ($this->productModel->create($data)) {
                $_SESSION['flash']['success'] = 'Produk berhasil ditambahkan';
                $this->redirect('index.php?page=products');
            } else {
                $_SESSION['flash']['error'] = 'Gagal menambahkan produk';
                $this->redirect('index.php?page=products&action=create');
            }
            return;
        }
        
        $categories = $this->categoryModel->getAll(100, 0, ['is_active' => 1]);
        
        $data = [
            'title' => 'Tambah Produk',
            'categories' => $categories,
            'generated_code' => $this->productModel->generateCode()
        ];
        
        $this->view->render('products/create', $data);
    }

    /**
     * Edit Product
     */
    public function edit() {
        $this->requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('index.php?page=products');
            return;
        }
        
        $product = $this->productModel->getById($id);
        if (!$product) {
            $_SESSION['flash'] ?? []->set('error', 'Produk tidak ditemukan');
            $this->redirect('index.php?page=products');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['flash']['error'] = 'Invalid security token';
                $this->redirect("index.php?page=products&action=edit&id=$id");
                return;
            }
            
            $data = [
                'product_name' => $_POST['product_name'],
                'category_id' => $_POST['category_id'],
                'description' => $_POST['description'] ?? '',
                'unit' => $_POST['unit'] ?? 'pcs',
                'purchase_price' => str_replace(['.', ','], '', $_POST['purchase_price'] ?? 0),
                'selling_price' => str_replace(['.', ','], '', $_POST['selling_price'] ?? 0),
                'barcode' => $_POST['barcode'] ?? '',
                'low_stock_threshold' => $_POST['low_stock_threshold'] ?? 10,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($this->productModel->update($id, $data)) {
                $_SESSION['flash']['success'] = 'Produk berhasil diperbarui';
                $this->redirect('index.php?page=products');
            } else {
                $_SESSION['flash']['error'] = 'Gagal memperbarui produk';
                $this->redirect("index.php?page=products&action=edit&id=$id");
            }
            return;
        }
        
        $categories = $this->categoryModel->getAll(100, 0, ['is_active' => 1]);
        
        $data = [
            'title' => 'Edit Produk',
            'product' => $product,
            'categories' => $categories
        ];
        
        $this->view->render('products/edit', $data);
    }
    
    /**
     * Delete Product
     */
    public function delete() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if ($this->productModel->delete($id)) {
                $this->json(['status' => 'success', 'message' => 'Produk berhasil dihapus']);
            } else {
                $this->json(['status' => 'error', 'message' => 'Gagal menghapus produk']);
            }
        }
    }
}
