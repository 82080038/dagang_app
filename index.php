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
require_once APP_PATH . '/config/constants.php';
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
require_once APP_PATH . '/core/SecurityHeaders.php';

// Send security headers
SecurityHeaders::sendHeaders();
SecurityHeaders::sanitizeErrorReporting();

// Load models
require_once APP_PATH . '/models/Company.php';
require_once APP_PATH . '/models/Branch.php';
require_once APP_PATH . '/models/Member.php';
require_once APP_PATH . '/models/Address.php';
require_once APP_PATH . '/models/File.php';
require_once APP_PATH . '/models/Notification.php';

// Load controllers
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/DashboardController.php';
require_once APP_PATH . '/controllers/CompanyController.php';
require_once APP_PATH . '/controllers/BranchController.php';
require_once APP_PATH . '/controllers/ProductController.php';
require_once APP_PATH . '/controllers/InventoryController.php';
require_once APP_PATH . '/controllers/TransactionController.php';
require_once APP_PATH . '/controllers/SettingsController.php';
require_once APP_PATH . '/controllers/FileController.php';
require_once APP_PATH . '/controllers/NotificationController.php';
require_once APP_PATH . '/controllers/WebSocketController.php';
require_once APP_PATH . '/controllers/SearchController.php';
require_once APP_PATH . '/controllers/ReportsController.php';
require_once APP_PATH . '/controllers/AdvancedReportsController.php';
require_once APP_PATH . '/controllers/UserController.php';
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
$advancedReportsController = new AdvancedReportsController();

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
        } elseif ($action === 'salesReport') {
            $reportsController->salesReport();
        } elseif ($action === 'inventoryReport') {
            $reportsController->inventoryReport();
        } elseif ($action === 'financialReport') {
            $reportsController->financialReport();
        } elseif ($action === 'customerReport') {
            $reportsController->customerReport();
        } elseif ($action === 'branchPerformanceReport') {
            $reportsController->branchPerformanceReport();
        } elseif ($action === 'exportReport') {
            $reportsController->exportReport();
        } elseif ($action === 'saveTemplate') {
            $reportsController->saveTemplate();
        } elseif ($action === 'getTemplates') {
            $reportsController->getTemplates();
        } elseif ($action === 'scheduleReport') {
            $reportsController->scheduleReport();
        } elseif ($action === 'getScheduledReports') {
            $reportsController->getScheduledReports();
        } elseif ($action === 'deleteScheduledReport' && $id) {
            $reportsController->deleteScheduledReport($id);
        } else {
            $reportsController->index();
        }
        break;
        
    case 'advanced_reports':
        if ($action === 'generateAIReport') {
            $advancedReportsController->generateAIReport();
        } elseif ($action === 'getAIAnalytics') {
            $advancedReportsController->getAIAnalytics();
        } elseif ($action === 'getAIPredictions') {
            $advancedReportsController->getAIPredictions();
        } elseif ($action === 'getAIInsights') {
            $advancedReportsController->getAIInsights();
        } elseif ($action === 'getAIRecommendations') {
            $advancedReportsController->getAIRecommendations();
        } elseif ($action === 'getAIModelPerformance') {
            $advancedReportsController->getAIModelPerformance();
        } elseif ($action === 'trainAIModel') {
            $advancedReportsController->trainAIModel();
        } elseif ($action === 'exportAIReport') {
            $advancedReportsController->exportAIReport();
        } elseif ($action === 'statistics') {
            $advancedReportsController->getStatistics();
        } elseif ($action === 'ai_models') {
            $advancedReportsController->getAvailableAIModels();
        } else {
            $advancedReportsController->index();
        }
        break;
        
    case 'settings':
        $settingsController = new SettingsController();
        if ($action === 'getSettings') {
            $settingsController->getSettings();
        } elseif ($action === 'updateSettings') {
            $settingsController->updateSettings();
        } elseif ($action === 'updateCompanySettings') {
            $settingsController->updateCompanySettings();
        } elseif ($action === 'updateEmailSettings') {
            $settingsController->updateEmailSettings();
        } elseif ($action === 'updateBackupSettings') {
            $settingsController->updateBackupSettings();
        } elseif ($action === 'createBackup') {
            $settingsController->createBackup();
        } elseif ($action === 'getBackupHistory') {
            $settingsController->getBackupHistory();
        } elseif ($action === 'downloadBackup') {
            $settingsController->downloadBackup();
        } elseif ($action === 'deleteBackup') {
            $settingsController->deleteBackup();
        } elseif ($action === 'getSystemLogs') {
            $settingsController->getSystemLogs();
        } elseif ($action === 'clearSystemLogs') {
            $settingsController->clearSystemLogs();
        } elseif ($action === 'getSystemStatus') {
            $settingsController->getSystemStatus();
        } elseif ($action === 'updateFeatureToggles') {
            $settingsController->updateFeatureToggles();
        } else {
            $settingsController->index();
        }
        break;
        
    case 'monitoring':
        $monitoringController = new MonitoringController();
        if ($action === 'getSystemHealth') {
            $monitoringController->getSystemHealth();
        } elseif ($action === 'getPerformanceMetrics') {
            $monitoringController->getPerformanceMetrics();
        } elseif ($action === 'getBackupStatus') {
            $monitoringController->getBackupStatus();
        } elseif ($action === 'getServiceStatus') {
            $monitoringController->getServiceStatus();
        } elseif ($action === 'getAlertHistory') {
            $monitoringController->getAlertHistory();
        } elseif ($action === 'createSystemBackup') {
            $monitoringController->createSystemBackup();
        } elseif ($action === 'scheduleBackup') {
            $monitoringController->scheduleBackup();
        } elseif ($action === 'getBackupSchedules') {
            $monitoringController->getBackupSchedules();
        } elseif ($action === 'deleteBackupSchedule') {
            $monitoringController->deleteBackupSchedule();
        } elseif ($action === 'createAlert') {
            $monitoringController->createAlert();
        } elseif ($action === 'getAlerts') {
            $monitoringController->getAlerts();
        } elseif ($action === 'resolveAlert') {
            $monitoringController->resolveAlert();
        } elseif ($action === 'getMonitoringDashboard') {
            $monitoringController->getMonitoringDashboard();
        } elseif ($action === 'testMonitoring') {
            $monitoringController->testMonitoring();
        } else {
            $monitoringController->index();
        }
        break;
        
    case 'audit':
        $auditController = new AuditController();
        if ($action === 'getAuditLogs') {
            $auditController->getAuditLogs();
        } elseif ($action === 'getAuditLogDetails') {
            $auditController->getAuditLogDetails();
        } elseif ($action === 'exportAuditLogs') {
            $auditController->exportAuditLogs();
        } elseif ($action === 'clearAuditLogs') {
            $auditController->clearAuditLogs();
        } elseif ($action === 'getAuditStatistics') {
            $auditController->getAuditStatistics();
        } elseif ($action === 'getComplianceReport') {
            $auditController->getComplianceReport();
        } elseif ($action === 'searchAuditLogs') {
            $auditController->searchAuditLogs();
        } else {
            $auditController->index();
        }
        break;
        
    case 'users':
        $userController = new UserController();
        if ($action === 'getUsers') {
            $userController->getUsers();
        } elseif ($action === 'create') {
            $userController->create();
        } elseif ($action === 'update') {
            $userController->update();
        } elseif ($action === 'delete') {
            $userController->delete();
        } elseif ($action === 'toggleStatus') {
            $userController->toggleStatus();
        } elseif ($action === 'getUser') {
            $userController->getUser();
        } else {
            $userController->index();
        }
        break;
        
    case 'api':
        // API documentation endpoint
        if ($page === 'documentation') {
            require_once __DIR__ . '/public/api/documentation.php';
        }
        break;
        
    case 'files':
        $fileController = new FileController();
        if ($action === 'upload') {
            $fileController->upload();
        } elseif ($action === 'download') {
            $fileController->download();
        } elseif ($action === 'getFile') {
            $fileController->getFile();
        } elseif ($action === 'update') {
            $fileController->update();
        } elseif ($action === 'delete') {
            $fileController->delete();
        } elseif ($action === 'getStatistics') {
            $fileController->getStatistics();
        } elseif ($action === 'bulkOperations') {
            $fileController->bulkOperations();
        } elseif ($action === 'search') {
            $fileController->search();
        } elseif ($action === 'getUploadProgress') {
            $fileController->getUploadProgress();
        } else {
            $fileController->index();
        }
        break;
        
    case 'notifications':
        $notificationController = new NotificationController();
        if ($action === 'getNotifications') {
            $notificationController->getNotifications();
        } elseif ($action === 'getUnreadCount') {
            $notificationController->getUnreadCount();
        } elseif ($action === 'markAsRead') {
            $notificationController->markAsRead();
        } elseif ($action === 'markAllAsRead') {
            $notificationController->markAllAsRead();
        } elseif ($action === 'delete') {
            $notificationController->delete();
        } elseif ($action === 'send') {
            $notificationController->send();
        } elseif ($action === 'getTemplates') {
            $notificationController->getTemplates();
        } elseif ($action === 'createTemplate') {
            $notificationController->createTemplate();
        } elseif ($action === 'updateTemplate') {
            $notificationController->updateTemplate();
        } elseif ($action === 'getPreferences') {
            $notificationController->getPreferences();
        } elseif ($action === 'updatePreferences') {
            $notificationController->updatePreferences();
        } elseif ($action === 'getStatistics') {
            $notificationController->getStatistics();
        } elseif ($action === 'getSettings') {
            $notificationController->getSettings();
        } elseif ($action === 'updateSettings') {
            $notificationController->updateSettings();
        } else {
            $notificationController->index();
        }
        break;
        
    case 'websocket':
        $webSocketController = new WebSocketController();
        if ($action === 'getStatus') {
            $webSocketController->getStatus();
        } elseif ($action === 'sendTestNotification') {
            $webSocketController->sendTestNotification();
        } elseif ($action === 'broadcast') {
            $webSocketController->broadcast();
        } elseif ($action === 'sendSystemAlert') {
            $webSocketController->sendSystemAlert();
        } elseif ($action === 'sendPrivateMessage') {
            $webSocketController->sendPrivateMessage();
        } elseif ($action === 'getClients') {
            $webSocketController->getClients();
        } elseif ($action === 'getChannels') {
            $webSocketController->getChannels();
        } elseif ($action === 'getChannelMessages') {
            $webSocketController->getChannelMessages();
        } elseif ($action === 'getOnlineUsers') {
            $webSocketController->getOnlineUsers();
        } elseif ($action === 'updatePresence') {
            $webSocketController->updatePresence();
        } elseif ($action === 'toggleWebSocket') {
            $webSocketController->toggleWebSocket();
        } elseif ($action === 'updateConfig') {
            $webSocketController->updateConfig();
        } elseif ($action === 'getStatistics') {
            $webSocketController->getStatistics();
        } elseif ($action === 'cleanup') {
            $webSocketController->cleanup();
        } elseif ($action === 'notifyFileUpload') {
            $webSocketController->notifyFileUpload();
        } elseif ($action === 'notifyFileDownload') {
            $webSocketController->notifyFileDownload();
        } elseif ($action === 'updateDashboardStats') {
            $webSocketController->updateDashboardStats();
        } else {
            $webSocketController->index();
        }
        break;
        
    case 'reports':
        $reportsController = new ReportsController();
        if ($action === 'salesReport') {
            $reportsController->salesReport();
        } elseif ($action === 'productPerformanceReport') {
            $reportsController->productPerformanceReport();
        } elseif ($action === 'inventoryReport') {
            $reportsController->inventoryReport();
        } elseif ($action === 'customerAnalysisReport') {
            $reportsController->customerAnalysisReport();
        } elseif ($action === 'exportReport') {
            $reportsController->exportReport();
        } elseif ($action === 'api') {
            $reportsController->api();
        } else {
            $reportsController->index();
        }
        break;
        
    case 'search':
        $searchController = new SearchController();
        if ($action === 'performSearch') {
            $searchController->performSearch();
        } elseif ($action === 'getSuggestions') {
            $searchController->getSuggestions();
        } elseif ($action === 'getPopularSearches') {
            $searchController->getPopularSearches();
        } elseif ($action === 'getAnalytics') {
            $searchController->getAnalytics();
        } elseif ($action === 'getSettings') {
            $searchController->getSettings();
        } elseif ($action === 'updateSettings') {
            $searchController->updateSettings();
        } elseif ($action === 'rebuildIndex') {
            $searchController->rebuildIndex();
        } elseif ($action === 'cleanup') {
            $searchController->cleanup();
        } elseif ($action === 'generateAnalytics') {
            $searchController->generateAnalytics();
        } elseif ($action === 'exportResults') {
            $searchController->exportResults();
        } else {
            $searchController->index();
        }
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
