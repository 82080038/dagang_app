<?php
/**
 * Application Configuration
 * Native PHP App Settings
 */

// Application Settings
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Aplikasi Perdagangan Multi-Cabang')
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0')
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development')

// Database Settings
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost')
if (!defined('DB_NAME')) {
    define('DB_NAME', 'perdagangan_system')
if (!defined('DB_USER')) {
    define('DB_USER', 'root')
if (!defined('DB_PASS')) {
    define('DB_PASS', '')
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4')

// Application Paths
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2))
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app')
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public')
if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', PUBLIC_PATH . '/assets')
if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', PUBLIC_PATH . '/uploads')

// URL Settings
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/dagang')
if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_URL . '/public/assets')
if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', BASE_URL . '/public/uploads')

// Session Settings
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'perdagangan_session')
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 7200)

// Security Settings
if (!defined('ENCRYPTION_KEY')) {
    define('ENCRYPTION_KEY', 'your-secret-key-here')
if (!defined('HASH_ALGO')) {
    define('HASH_ALGO', 'sha256')
if (!defined('PASSWORD_ALGO')) {
    define('PASSWORD_ALGO', PASSWORD_BCRYPT)

// Pagination Settings
if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 20)
if (!defined('MAX_ITEMS_PER_PAGE')) {
    define('MAX_ITEMS_PER_PAGE', 100)

// Currency Settings
if (!defined('CURRENCY_CODE')) {
    define('CURRENCY_CODE', 'IDR')
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'Rp')
if (!defined('DECIMAL_PLACES')) {
    define('DECIMAL_PLACES', 2)
if (!defined('THOUSANDS_SEPARATOR')) {
    define('THOUSANDS_SEPARATOR', '.')
if (!defined('DECIMAL_SEPARATOR')) {
    define('DECIMAL_SEPARATOR', ',')

// Date/Time Settings
if (!defined('DATE_FORMAT')) {
    define('DATE_FORMAT', 'd/m/Y')
if (!defined('TIME_FORMAT')) {
    define('TIME_FORMAT', 'H:i')
if (!defined('DATETIME_FORMAT')) {
    define('DATETIME_FORMAT', 'd/m/Y H:i')
if (!defined('TIMEZONE')) {
    define('TIMEZONE', 'Asia/Jakarta')

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    }
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start Session
session_name(SESSION_NAME);
session_start();
?>
