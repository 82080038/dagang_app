<?php
/**
 * Application Constants
 * Global Constants for Perdagangan System
 */

// Prevent duplicate constant definitions
if (!defined('ROLE_APP_OWNER')) {
    // User Roles
    // Application Roles (System Administration)
    define('ROLE_APP_OWNER', 1);        // Pemilik aplikasi/developer
    define('ROLE_APP_ADMIN', 2);        // System administrator

    // Business Roles (Business Operations)
    define('ROLE_COMPANY_OWNER', 10);   // Pemilik perusahaan
    define('ROLE_BRANCH_OWNER', 11);    // Pemilik cabang
    define('ROLE_MANAGER', 12);         // Manager bisnis
    define('ROLE_CASHIER', 13);         // Kasir
    define('ROLE_STAFF', 14);           // Karyawan
    define('ROLE_CUSTOMER', 15);         // Pelanggan

    // Transaction Types
    define('TRANSACTION_SALE', 'sale');
    define('TRANSACTION_PURCHASE', 'purchase');
    define('TRANSACTION_RETURN', 'return');
    define('TRANSACTION_TRANSFER', 'transfer');
    define('TRANSACTION_ADJUSTMENT', 'adjustment');

    // Payment Methods
    define('PAYMENT_CASH', 'cash');
    define('PAYMENT_CARD', 'card');
    define('PAYMENT_EWALLET', 'ewallet');
    define('PAYMENT_TRANSFER', 'transfer');
    define('PAYMENT_CHECK', 'check');
    define('PAYMENT_CREDIT', 'credit');

    // Payment Status
    define('PAYMENT_PENDING', 'pending');
    define('PAYMENT_PAID', 'paid');
    define('PAYMENT_PARTIAL', 'partial');
    define('PAYMENT_FAILED', 'failed');
    define('PAYMENT_REFUNDED', 'refunded');

    // Stock Movement Types
    define('STOCK_IN', 'in');
    define('STOCK_OUT', 'out');
    define('STOCK_TRANSFER', 'transfer');
    define('STOCK_ADJUSTMENT', 'adjustment');
    define('STOCK_RETURN', 'return');

    // Product Status
    define('PRODUCT_ACTIVE', 1);
    define('PRODUCT_INACTIVE', 0);
    define('PRODUCT_DISCONTINUED', 2);

    // Branch Status
    define('BRANCH_ACTIVE', 1);
    define('BRANCH_INACTIVE', 0);
    define('BRANCH_SUSPENDED', 2);

    // User Status
    define('USER_ACTIVE', 1);
    define('USER_INACTIVE', 0);
    define('USER_SUSPENDED', 2);

    // Company Types
    define('COMPANY_INDIVIDUAL', 'individual');
    define('COMPANY_PERSONAL', 'personal');
    define('COMPANY_WARUNG', 'warung');
    define('COMPANY_KIOS', 'kios');
    define('COMPANY_TOKO_KELONTONG', 'toko_kelontong');
    define('COMPANY_MINIMARKET', 'minimarket');
    define('COMPANY_PENGUSAHA_MENENGAH', 'pengusaha_menengah');
    define('COMPANY_DISTRIBUTOR', 'distributor');
    define('COMPANY_KOPERASI', 'koperasi');
    define('COMPANY_PERUSAHAAN_BESAR', 'perusahaan_besar');
    define('COMPANY_FRANCHISE', 'franchise');
    define('COMPANY_PUSAT', 'pusat');

    // Scalability Levels
    define('SCALABILITY_ULTRA_MIKRO', '1');
    define('SCALABILITY_MIKRO', '2');
    define('SCALABILITY_KECIL', '3');
    define('SCALABILITY_MENENGAH', '4');
    define('SCALABILITY_BESAR', '5');
    define('SCALABILITY_ENTERPRISE', '6');

    // File Upload Settings
    define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
    define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);

    // Cache Settings
    define('CACHE_ENABLED', true);
    define('CACHE_DURATION', 3600); // 1 hour

    // Logging Settings
    define('LOG_ENABLED', true);
    define('LOG_PATH', __DIR__ . '/../logs/');
    define('LOG_LEVEL', 'INFO');

    // API Settings
    define('API_VERSION', 'v1');
    define('API_RATE_LIMIT', 100); // requests per hour

    // Security Settings
    define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour
    define('SESSION_TIMEOUT', 7200); // 2 hours

    // Pagination Settings
    define('DEFAULT_PAGE_SIZE', 20);
    define('MAX_PAGE_SIZE', 100);

    // Date/Time Settings
    define('DATE_FORMAT', 'Y-m-d');
    define('TIME_FORMAT', 'H:i:s');
    define('DATETIME_FORMAT', 'Y-m-d H:i:s');

    // Currency Settings
    define('CURRENCY_CODE', 'IDR');
    define('CURRENCY_SYMBOL', 'Rp');
    define('DECIMAL_PLACES', 2);

    // Application Settings
    define('APP_NAME', 'Perdagangan System');
    define('APP_VERSION', '3.0.0');

    // Email Settings
    define('EMAIL_FROM', 'noreply@perdagangan.com');
    define('EMAIL_FROM_NAME', 'Perdagangan System');

    // Notification Settings
    define('NOTIFICATION_TYPES', ['email', 'sms', 'push', 'in_app']);
    define('NOTIFICATION_PRIORITIES', ['low', 'medium', 'high', 'urgent']);
}

// Individual constants that need separate protection
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

if (!defined('DB_COLLATION')) {
    define('DB_COLLATION', 'utf8mb4_unicode_ci');
}
?>
