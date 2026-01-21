<?php
/**
 * Application Configuration
 * Native PHP App Settings
 */

// Application Settings
define('APP_NAME', 'Aplikasi Perdagangan Multi-Cabang');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, staging, production

// Database Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'perdagangan_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Paths
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// URL Settings
define('BASE_URL', 'http://localhost/dagang');
define('ASSETS_URL', BASE_URL . '/public/assets');
define('UPLOADS_URL', BASE_URL . '/public/uploads');

// Session Settings
define('SESSION_NAME', 'perdagangan_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// Security Settings
define('ENCRYPTION_KEY', 'your-secret-key-here');
define('HASH_ALGO', 'sha256');
define('PASSWORD_ALGO', PASSWORD_BCRYPT);

// Pagination Settings
define('ITEMS_PER_PAGE', 20);
define('MAX_ITEMS_PER_PAGE', 100);

// Currency Settings
define('CURRENCY_CODE', 'IDR');
define('CURRENCY_SYMBOL', 'Rp');
define('DECIMAL_PLACES', 2);
define('THOUSANDS_SEPARATOR', '.');
define('DECIMAL_SEPARATOR', ',');

// Date/Time Settings
define('DATE_FORMAT', 'd/m/Y');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'd/m/Y H:i');
define('TIMEZONE', 'Asia/Jakarta');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start Session
session_name(SESSION_NAME);
session_start();
?>
