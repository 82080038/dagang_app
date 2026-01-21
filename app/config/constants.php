<?php
/**
 * Application Constants
 * Global Constants for Perdagangan System
 */

// User Roles
define('ROLE_SUPER_ADMIN', 1);
define('ROLE_ADMIN', 2);
define('ROLE_MANAGER', 3);
define('ROLE_CASHIER', 4);
define('ROLE_STAFF', 5);
define('ROLE_CUSTOMER', 6);

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
define('USER_BLOCKED', 3);

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
define('SCALABILITY_INDIVIDUAL', 1);
define('SCALABILITY_WARUNG', 2);
define('SCALABILITY_TOKO', 3);
define('SCALABILITY_MINIMARKET', 4);
define('SCALABILITY_DISTRIBUTOR', 5);
define('SCALABILITY_ENTERPRISE', 6);

// Business Categories
define('BUSINESS_RETAIL', 'retail');
define('BUSINESS_WHOLESALE', 'wholesale');
define('BUSINESS_MANUFACTURING', 'manufacturing');
define('BUSINESS_AGRICULTURE', 'agriculture');
define('BUSINESS_SERVICES', 'services');
define('BUSINESS_COOPERATIVE', 'cooperative');
define('BUSINESS_ONLINE', 'online');
define('BUSINESS_DISTRIBUTOR', 'distributor');
define('BUSINESS_PERSONAL', 'personal');

// API Response Codes
define('API_SUCCESS', 200);
define('API_CREATED', 201);
define('API_BAD_REQUEST', 400);
define('API_UNAUTHORIZED', 401);
define('API_FORBIDDEN', 403);
define('API_NOT_FOUND', 404);
define('API_SERVER_ERROR', 500);

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// Email Settings
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@example.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_ENCRYPTION', 'tls');

// Backup Settings
define('BACKUP_ENABLED', true);
define('BACKUP_PATH', ROOT_PATH . '/backups');
define('BACKUP_RETENTION_DAYS', 30);

// Logging Settings
define('LOG_ENABLED', true);
define('LOG_PATH', ROOT_PATH . '/logs');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
?>
