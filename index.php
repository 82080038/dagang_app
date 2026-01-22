<?php
/**
 * Main Entry Point
 * 
 * Router sederhana untuk aplikasi perdagangan multi-cabang
 * Menggunakan PHP Native tanpa framework
 */

// Start session
session_start();

// Error reporting untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');
define('VIEWS_PATH', __DIR__ . '/app/views');

// Load configuration
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/config/database.php';

// Load browser detector
require_once APP_PATH . '/utils/BrowserDetector.php';

// Check browser compatibility (only for non-AJAX requests)
if (!isset($_GET['page']) || $_GET['page'] !== 'address') {
    $browserSupport = BrowserDetector::isSupported();
    
    if (!$browserSupport['supported'] && !isset($_SESSION['browser_checked'])) {
        $_SESSION['browser_checked'] = true;
        header('Location: browser_check.php');
        exit;
    }
    $_SESSION['browser_checked'] = true;
}

// Load core classes
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Csrf.php';

// Load models
require_once APP_PATH . '/models/Company.php';
require_once APP_PATH . '/models/Branch.php';
require_once APP_PATH . '/models/Member.php';
require_once APP_PATH . '/models/Address.php';

// Load controllers
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/DashboardController.php';
require_once APP_PATH . '/controllers/CompanyController.php';
require_once APP_PATH . '/controllers/BranchController.php';
require_once APP_PATH . '/controllers/ProductController.php';
require_once APP_PATH . '/controllers/InventoryController.php';
require_once APP_PATH . '/controllers/TransactionController.php';
require_once APP_PATH . '/controllers/SettingsController.php';
require_once APP_PATH . '/controllers/ReportsController.php';
require_once APP_PATH . '/controllers/AddressController.php';
require_once APP_PATH . '/utils/HealthCheck.php';
HealthCheck::run();

// Simple router
$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Initialize controllers
$authController = new AuthController();
$dashboardController = new DashboardController();
$companyController = new CompanyController();
$branchController = new BranchController();
$productController = new ProductController();
$inventoryController = new InventoryController();
$transactionController = new TransactionController();
$addressController = new AddressController();

// Route handling
switch ($page) {
    case 'login':
        $authController->login();
        break;
        
    case 'register':
        $authController->register();
        break;
        
    case 'logout':
        $authController->logout();
        break;
        
    case 'dashboard':
        if ($action === 'api-stats') {
            $dashboardController->apiStats();
        } elseif ($action === 'api-realtime') {
            $dashboardController->apiRealtimeStats();
        } elseif ($action === 'api-activity') {
            $dashboardController->apiRecentActivity();
        } elseif ($action === 'api-scalability') {
            $dashboardController->apiScalabilityDistribution();
        } elseif ($action === 'api-segments') {
            $dashboardController->apiBusinessSegmentDistribution();
        } elseif ($action === 'api-open-branches') {
            $dashboardController->apiOpenBranches();
        } elseif ($action === 'api-low-stock') {
            $dashboardController->apiLowStockAlert();
        } else {
            $dashboardController->index();
        }
        break;
        
    case 'companies':
        if ($action === 'create') {
            $companyController->create();
        } elseif ($action === 'update') {
            $companyController->update();
        } elseif ($action === 'edit' && $id) {
            $companyController->edit($id);
        } elseif ($action === 'delete' && $id) {
            $companyController->delete($id);
        } elseif ($action === 'search') {
            $companyController->search();
        } elseif ($action === 'details' && $id) {
            $companyController->details($id);
        } elseif ($action === 'get' && $id) {
            $companyController->get($id);
        } elseif ($action === 'toggle-status' && $id) {
            $companyController->toggleStatus($id);
        } else {
            $companyController->index();
        }
        break;
        
    case 'address':
        if ($action === 'get-provinces') {
            $addressController->getProvinces();
        } elseif ($action === 'get-regencies') {
            $addressController->getRegencies();
        } elseif ($action === 'get-districts') {
            $addressController->getDistricts();
        } elseif ($action === 'get-villages') {
            $addressController->getVillages();
        } elseif ($action === 'get-postal-code') {
            $addressController->getPostalCode();
        } elseif ($action === 'create') {
            $addressController->create();
        } elseif ($action === 'update') {
            $addressController->update();
        } elseif ($action === 'get') {
            $addressController->get();
        } elseif ($action === 'search') {
            $addressController->search();
        } elseif ($action === 'get-entity-addresses') {
            $addressController->getEntityAddresses();
        } elseif ($action === 'setup') {
            $addressController->setup();
        } elseif ($action === 'setup-centralized') {
            $addressController->setupCentralized();
        } else {
            // Address endpoints are AJAX only, return 404 for direct access
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
            exit;
        }
        break;
        
    case 'branches':
        if ($action === 'create') {
            $branchController->create();
        } elseif ($action === 'edit' && $id) {
            $branchController->edit($id);
        } elseif ($action === 'delete') {
            $branchController->delete(); // AJAX
        } else {
            $branchController->index();
        }
        break;
        
    case 'products':
        if ($action === 'create') {
            $productController->create();
        } elseif ($action === 'edit' && $id) {
            $productController->edit(); // edit() gets ID from $_GET internally in ProductController
        } elseif ($action === 'delete') {
            $productController->delete();
        } else {
            $productController->index();
        }
        break;
        
    case 'transactions':
        if ($action === 'create') {
            $transactionController->create();
        } elseif ($action === 'history') {
            $transactionController->history();
        } elseif ($action === 'details' && $id) {
            $transactionController->details($id);
        } elseif ($action === 'searchProducts') {
            $transactionController->searchProducts();
        } else {
            $transactionController->index();
        }
        break;
        
    case 'inventory':
        if ($action === 'update') {
            $inventoryController->update();
        } else {
            $inventoryController->index();
        }
        break;
        
    case 'reports':
        $reportsController = new ReportsController();
        if ($action === 'api') {
            $reportsController->api();
        } else {
            $reportsController->index();
        }
        break;
        
    case 'settings':
        $settingsController = new SettingsController();
        $settingsController->index();
        break;
        
    default:
        // 404 - Page not found
        http_response_code(404);
        echo '<div class="container mt-5">';
        echo '<div class="alert alert-warning">';
        echo '<h4>404 - Page Not Found</h4>';
        echo '<p>Halaman yang Anda cari tidak ditemukan.</p>';
        echo '<a href="index.php?page=dashboard" class="btn btn-primary">Kembali ke Dashboard</a>';
        echo '</div>';
        echo '</div>';
        break;
}

?>
