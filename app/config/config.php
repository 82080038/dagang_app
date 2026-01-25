<?php
/**
 * Application Configuration
 * 
 * Konstanta dan pengaturan utama aplikasi
 */

// Email Settings (untuk notifikasi)
if (!defined('MAIL_ENABLED')) {
    define('MAIL_ENABLED', false);
}
if (!defined('MAIL_FROM')) {
    define('MAIL_FROM', 'noreply@dagang.com');
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', 'Aplikasi Perdagangan');
}

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

// Currency & Number Formatting
define('DECIMAL_SEPARATOR', ',');
define('THOUSANDS_SEPARATOR', '.');

// Date & Time
define('TIMEZONE', 'Asia/Jakarta');

// File Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads');

// Pagination
if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 20);
}

// Session
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 7200); // 2 hours
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'dagang_session');
}
if (!defined('SESSION_SECURE')) {
    define('SESSION_SECURE', false); // Set to false for development, true for production
}
if (!defined('SESSION_HTTP_ONLY')) {
    define('SESSION_HTTP_ONLY', true); // Prevent JavaScript access
}
if (!defined('SESSION_SAMESITE')) {
    define('SESSION_SAMESITE', 'Strict'); // CSRF protection
}
if (!defined('SESSION_STRICT_MODE')) {
    define('SESSION_STRICT_MODE', true); // Prevent session fixation
}

// Security
if (!defined('CSRF_TOKEN_NAME')) {
    define('CSRF_TOKEN_NAME', 'csrf_token');
}
if (!defined('HASH_ALGORITHM')) {
    define('HASH_ALGORITHM', 'sha256');
}

// Security Headers
if (!defined('SECURITY_HEADERS')) {
    define('SECURITY_HEADERS', false); // Set to false for development, true for production
}
if (!defined('X_FRAME_OPTIONS')) {
    define('X_FRAME_OPTIONS', 'DENY');
}
if (!defined('X_CONTENT_TYPE_OPTIONS')) {
    define('X_CONTENT_TYPE_OPTIONS', 'nosniff');
}
if (!defined('X_XSS_PROTECTION')) {
    define('X_XSS_PROTECTION', '1; mode=block');
}
if (!defined('STRICT_TRANSPORT_SECURITY')) {
    define('STRICT_TRANSPORT_SECURITY', 'max-age=31536000; includeSubDomains');
}
if (!defined('CONTENT_SECURITY_POLICY')) {
    define('CONTENT_SECURITY_POLICY', false); // DISABLED for development
}

// Cache
define('CACHE_DIR', __DIR__ . '/../cache');

// Environment Settings (only if not already defined)
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true); // Set to false in production for security
}
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development'); // development, staging, production
}

// Business Logic Constants
define('MIN_STOCK_LEVEL', 10);
define('LOW_STOCK_THRESHOLD', 5);
define('DEFAULT_TAX_RATE', 0.11); // 11%

// Branch Types
define('BRANCH_TYPE_PUSAT', 'pusat');
define('BRANCH_TYPE_TOKO', 'toko');
define('BRANCH_TYPE_WARUNG', 'warung');
define('BRANCH_TYPE_MINIMARKET', 'minimarket');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Set session settings (hanya jika session belum aktif dan headers belum sent)
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.name', SESSION_NAME);
    ini_set('session.cookie_httponly', SESSION_HTTP_ONLY);
    ini_set('session.cookie_secure', SESSION_SECURE);
    ini_set('session.cookie_samesite', SESSION_SAMESITE);
    ini_set('session.use_strict_mode', SESSION_STRICT_MODE);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', '');
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
