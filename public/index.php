<?php
/**
 * Main Entry Point
 * Native PHP MVC Pattern
 */

// Load configuration
require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Controller.php';

// Load controllers
require_once __DIR__ . '/../app/controllers/DashboardController.php';

// Initialize router
$router = new Router();

// Define routes
$router->get('/', 'Dashboard', 'index');
$router->get('/dashboard', 'Dashboard', 'index');
$router->get('/dashboard/stats', 'Dashboard', 'apiStats');
$router->get('/dashboard/activity', 'Dashboard', 'apiRecentActivity');
$router->get('/dashboard/scalability', 'Dashboard', 'apiScalabilityDistribution');
$router->get('/dashboard/segments', 'Dashboard', 'apiBusinessSegmentDistribution');
$router->get('/dashboard/open-branches', 'Dashboard', 'apiOpenBranches');
$router->get('/dashboard/low-stock', 'Dashboard', 'apiLowStockAlert');
$router->get('/search', 'Dashboard', 'search');
$router->get('/company/{id}', 'Dashboard', 'companyDetails');
$router->get('/branch/{id}', 'Dashboard', 'branchDetails');
$router->get('/export', 'Dashboard', 'export');

// Dispatch request
try {
    $uri = $_SERVER['REQUEST_URI'];
    $router->dispatch($uri);
} catch (Exception $e) {
    // Handle errors
    if (APP_ENV === 'development') {
        echo '<h1>Error</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        // Log error
        error_log($e->getMessage());
        
        // Show friendly error page
        http_response_code(500);
        echo '<h1>Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
}
?>
