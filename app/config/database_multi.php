<?php
/**
 * Database Configuration - Multi-Database Setup
 * 
 * Database Structure:
 * 1. perdagangan_system - Main application database (read/write)
 * 2. alamat_db - Address reference database (read-only)
 * 
 * Relationship:
 * - perdagangan_system stores only foreign keys (id) from alamat_db
 * - alamat_db is reference-only, cannot be modified
 * - All address data should be fetched from alamat_db
 */

// Database Configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost')
if (!defined('DB_USER')) {
    define('DB_USER', 'root')
if (!defined('DB_PASS')) {
    define('DB_PASS', '')
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4')

// Main Application Database
if (!defined('DB_NAME_MAIN')) {
    define('DB_NAME_MAIN', 'perdagangan_system')
if (!defined('DB_NAME_ADDRESS')) {
    define('DB_NAME_ADDRESS', 'alamat_db')

// Database Connections
$main_db = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_MAIN . ";charset=" . DB_CHARSET,
    }
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);

$address_db = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_ADDRESS . ";charset=" . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);

// Database Access Rules
if (!defined('DB_MAIN_READ_WRITE')) {
    define('DB_MAIN_READ_WRITE', true)
if (!defined('DB_ADDRESS_READ_ONLY')) {
    define('DB_ADDRESS_READ_ONLY', true)

// Address Database Tables (Reference Only)
$address_tables = [
    'provinces' => 'id_province, name',
    'regencies' => 'id_regency, id_province, name',
    'districts' => 'id_district, id_regency, name',
    'villages' => 'id_village, id_district, name'
];
    }

// Main Database Tables (Application Data)
$main_tables = [
    'companies' => 'id_company, company_name, company_code, company_type, business_category, scalability_level, owner_name, phone, email, address, tax_id, business_license, is_active, created_at, updated_at',
    'branches' => 'id_branch, company_id, branch_name, branch_code, branch_type, business_segment, address, phone, email, operation_hours, is_active, created_at, updated_at',
    'products' => 'id_product, company_id, branch_id, category_id, code, name, description, unit, purchase_price, selling_price, stock, min_stock, max_stock, is_active, created_at, updated_at',
    'categories' => 'id_category, company_id, name, description, parent_id, is_active, created_at, updated_at',
    'suppliers' => 'id_supplier, company_id, name, contact_person, phone, email, address, tax_id, is_active, created_at, updated_at',
    'customers' => 'id_customer, company_id, name, phone, email, address, tax_id, is_active, created_at, updated_at',
    'transactions' => 'id_transaction, company_id, branch_id, transaction_type, transaction_number, customer_id, total_amount, payment_method, status, created_at, updated_at',
    'transaction_details' => 'id_detail, transaction_id, product_id, quantity, unit_price, total_price, created_at',
    'users' => 'id_user, company_id, username, email, password_hash, role, is_active, created_at, updated_at',
    'modules' => 'id_module, name, code, version, description, type, dependencies, settings, is_active, created_at, updated_at',
    'company_settings' => 'id_setting, company_id, module_id, setting_key, setting_value, setting_type, created_at, updated_at'
];

// Address Foreign Key Relationships
$address_relationships = [
    'companies' => [
        'province_id' => 'provinces.id_province',
        'regency_id' => 'regencies.id_regency',
        'district_id' => 'districts.id_district',
        'village_id' => 'villages.id_village'
    ],
    'branches' => [
        'province_id' => 'provinces.id_province',
        'regency_id' => 'regencies.id_regency',
        'district_id' => 'districts.id_district',
        'village_id' => 'villages.id_village'
    ],
    'suppliers' => [
        'province_id' => 'provinces.id_province',
        'regency_id' => 'regencies.id_regency',
        'district_id' => 'districts.id_district',
        'village_id' => 'villages.id_village'
    ],
    'customers' => [
        'province_id' => 'provinces.id_province',
        'regency_id' => 'regencies.id_regency',
        'district_id' => 'districts.id_district',
        'village_id' => 'villages.id_village'
    ]
];

// Database Helper Functions
function getMainDB() {
    global $main_db;
    return $main_db;
}

function getAddressDB() {
    global $address_db;
    return $address_db;
}

function getAddressData($table, $id = null, $fields = '*') {
    global $address_db;
    
    if (!in_array($table, array_keys($GLOBALS['address_tables']))) {
        throw new Exception("Table '$table' not found in address database");
    }
    
    $sql = "SELECT $fields FROM $table";
    $params = [];
    
    if ($id) {
        $sql .= " WHERE id_" . substr($table, 0, -1) . " = ?";
        $params[] = $id;
    }
    
    $stmt = $address_db->prepare($sql);
    $stmt->execute($params);
    
    return $id ? $stmt->fetch() : $stmt->fetchAll();
}

function getProvinces() {
    return getAddressData('provinces');
}

function getRegencies($province_id = null) {
    return getAddressData('regencies', $province_id);
}

function getDistricts($regency_id = null) {
    return getAddressData('districts', $regency_id);
}

function getVillages($district_id = null) {
    return getAddressData('villages', $district_id);
}

function getAddressName($table, $id) {
    $data = getAddressData($table, $id, 'name');
    return $data ? $data['name'] : '';
}

function getFullAddress($province_id, $regency_id, $district_id, $village_id) {
    $address = [];
    
    if ($village_id) {
        $address[] = getAddressName('villages', $village_id);
    }
    if ($district_id) {
        $address[] = getAddressName('districts', $district_id);
    }
    if ($regency_id) {
        $address[] = getAddressName('regencies', $regency_id);
    }
    if ($province_id) {
        $address[] = getAddressName('provinces', $province_id);
    }
    
    return implode(', ', $address);
}

// Validation Functions
function validateAddressId($table, $id) {
    global $address_db;
    
    if (!in_array($table, ['provinces', 'regencies', 'districts', 'villages'])) {
        return false;
    }
    
    $sql = "SELECT COUNT(*) as count FROM $table WHERE id_" . substr($table, 0, -1) . " = ?";
    $stmt = $address_db->prepare($sql);
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    return $result['count'] > 0;
}

function validateAddressRelationships($data) {
    $errors = [];
    
    // Validate province
    if (!empty($data['province_id']) && !validateAddressId('provinces', $data['province_id'])) {
        $errors[] = 'Invalid province ID';
    }
    
    // Validate regency
    if (!empty($data['regency_id'])) {
        if (!validateAddressId('regencies', $data['regency_id'])) {
            $errors[] = 'Invalid regency ID';
        } elseif (!empty($data['province_id'])) {
            // Check if regency belongs to province
            $regency = getAddressData('regencies', $data['regency_id'], 'id_province');
            if ($regency && $regency['id_province'] != $data['province_id']) {
                $errors[] = 'Regency does not belong to selected province';
            }
        }
    }
    
    // Validate district
    if (!empty($data['district_id'])) {
        if (!validateAddressId('districts', $data['district_id'])) {
            $errors[] = 'Invalid district ID';
        } elseif (!empty($data['regency_id'])) {
            // Check if district belongs to regency
            $district = getAddressData('districts', $data['district_id'], 'id_regency');
            if ($district && $district['id_regency'] != $data['regency_id']) {
                $errors[] = 'District does not belong to selected regency';
            }
        }
    }
    
    // Validate village
    if (!empty($data['village_id'])) {
        if (!validateAddressId('villages', $data['village_id'])) {
            $errors[] = 'Invalid village ID';
        } elseif (!empty($data['district_id'])) {
            // Check if village belongs to district
            $village = getAddressData('villages', $data['village_id'], 'id_district');
            if ($village && $village['id_district'] != $data['district_id']) {
                $errors[] = 'Village does not belong to selected district';
            }
        }
    }
    
    return $errors;
}

// Security: Prevent modification of address database
function isAddressDatabaseOperation($sql) {
    $forbidden_operations = ['INSERT', 'UPDATE', 'DELETE', 'CREATE', 'ALTER', 'DROP', 'TRUNCATE'];
    $sql_upper = strtoupper($sql);
    
    foreach ($forbidden_operations as $operation) {
        if (strpos($sql_upper, $operation) !== false) {
            return true;
        }
    }
    
    return false;
}

// Log database configuration
error_log("Database Configuration Loaded:");
error_log("Main Database: " . DB_NAME_MAIN . " (Read/Write)");
error_log("Address Database: " . DB_NAME_ADDRESS . " (Read-Only)");
?>
