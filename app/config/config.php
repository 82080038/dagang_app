<?php
/**
 * Application Configuration
 * 
 * Konstanta dan pengaturan utama aplikasi
 */

// Application Settings
define('APP_NAME', 'Aplikasi Perdagangan');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true);
define('APP_ENV', 'development');

// URL Settings
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($host, ':8000') !== false) {
    define('BASE_URL', "$protocol://$host");
} else {
    define('BASE_URL', 'http://localhost/dagang');
}
define('ASSETS_URL', BASE_URL . '/assets');

// Database Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'perdagangan_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Currency & Number Formatting
define('CURRENCY_SYMBOL', 'Rp');
define('DECIMAL_PLACES', 0);
define('DECIMAL_SEPARATOR', ',');
define('THOUSANDS_SEPARATOR', '.');

// Date & Time
define('DATE_FORMAT', 'd M Y');
define('DATETIME_FORMAT', 'd M Y H:i:s');
define('TIMEZONE', 'Asia/Jakarta');

// File Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Session
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'dagang_session');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('HASH_ALGORITHM', 'sha256');

// Logging
define('LOG_ENABLED', true);
define('LOG_PATH', __DIR__ . '/../logs');

// Email Settings (untuk notifikasi)
define('MAIL_ENABLED', false);
define('MAIL_FROM', 'noreply@dagang.com');
define('MAIL_FROM_NAME', APP_NAME);

// Cache
define('CACHE_ENABLED', false);
define('CACHE_DIR', __DIR__ . '/../cache');

// API Settings
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour

// Business Logic Constants
define('MIN_STOCK_LEVEL', 10);
define('LOW_STOCK_THRESHOLD', 5);
define('DEFAULT_TAX_RATE', 0.11); // 11%

// User Roles
define('ROLE_ADMIN', 1);
define('ROLE_MANAGER', 2);
define('ROLE_SUPERVISOR', 3);
define('ROLE_CASHIER', 4);
define('ROLE_STAFF', 5);

// Branch Types
define('BRANCH_TYPE_PUSAT', 'pusat');
define('BRANCH_TYPE_TOKO', 'toko');
define('BRANCH_TYPE_WARUNG', 'warung');
define('BRANCH_TYPE_MINIMARKET', 'minimarket');

// Transaction Types
define('TRANSACTION_SALE', 'SALE');
define('TRANSACTION_PURCHASE', 'PURCHASE');
define('TRANSACTION_TRANSFER', 'TRANSFER');
define('TRANSACTION_ADJUSTMENT', 'ADJUSTMENT');

// Payment Methods
define('PAYMENT_CASH', 'CASH');
define('PAYMENT_TRANSFER', 'TRANSFER');
define('PAYMENT_EDC', 'EDC');
define('PAYMENT_EWALLET', 'EWALLET');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Set session settings (hanya jika session belum aktif)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.name', SESSION_NAME);
}

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
}

// Cross-platform compatibility (hanya jika belum didefinisikan)
if (!defined('IS_WINDOWS')) {
    define('IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
}

// Directory separator (hanya jika belum didefinisikan)
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Root paths (hanya jika belum didefinisikan)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', __DIR__);
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', dirname(__DIR__) . '/public');
}
if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', ROOT_PATH . '/uploads');
}
if (!defined('LOGS_PATH')) {
    define('LOGS_PATH', ROOT_PATH . '/logs');
}
if (!defined('CACHE_PATH')) {
    define('CACHE_PATH', CACHE_DIR);
}

// Create necessary directories if they don't exist
$directories = [
    UPLOADS_PATH,
    LOGS_PATH,
    CACHE_PATH,
    UPLOADS_PATH . '/products',
    UPLOADS_PATH . '/documents',
    LOGS_PATH . '/app',
    LOGS_PATH . '/error',
    CACHE_PATH . '/views'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

?>
