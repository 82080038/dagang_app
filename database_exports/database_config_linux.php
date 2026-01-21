<?php
/**
 * Database Configuration - Linux Deployment
 * Multi-Database Setup for Dagang Application
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'dagang_app');
define('DB_PASS', 'DagangApp2024!');
define('DB_CHARSET', 'utf8mb4');

// Multi-Database Configuration
define('DB_NAME_MAIN', 'perdagangan_system');
define('DB_NAME_ADDRESS', 'alamat_db');

// Database Access Rules
define('DB_MAIN_READ_WRITE', true);
define('DB_ADDRESS_READ_ONLY', true);

// Connection function for main database
function getMainDB() {
    static $main_db = null;
    if ($main_db === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_MAIN . ";charset=" . DB_CHARSET;
            $main_db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new Exception('Main database connection failed: ' . $e->getMessage());
        }
    }
    return $main_db;
}

// Connection function for address database
function getAddressDB() {
    static $address_db = null;
    if ($address_db === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_ADDRESS . ";charset=" . DB_CHARSET;
            $address_db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new Exception('Address database connection failed: ' . $e->getMessage());
        }
    }
    return $address_db;
}

// Helper functions for address data
function getProvinces() {
    $db = getAddressDB();
    $stmt = $db->query("SELECT id, name FROM provinces ORDER BY name");
    return $stmt->fetchAll();
}

function getRegencies($province_id) {
    $db = getAddressDB();
    $stmt = $db->prepare("SELECT id, name FROM regencies WHERE province_id = ? ORDER BY name");
    $stmt->execute([$province_id]);
    return $stmt->fetchAll();
}

function getDistricts($regency_id) {
    $db = getAddressDB();
    $stmt = $db->prepare("SELECT id, name FROM districts WHERE regency_id = ? ORDER BY name");
    $stmt->execute([$regency_id]);
    return $stmt->fetchAll();
}

function getVillages($district_id) {
    $db = getAddressDB();
    $stmt = $db->prepare("SELECT id, name FROM villages WHERE district_id = ? ORDER BY name");
    $stmt->execute([$district_id]);
    return $stmt->fetchAll();
}

function getAddressName($table, $id) {
    $db = getAddressDB();
    $stmt = $db->prepare("SELECT name FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ? $result['name'] : '';
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

// Test connections
try {
    $main_db = getMainDB();
    $address_db = getAddressDB();
    
    // Test main database
    $stmt = $main_db->query("SELECT COUNT(*) as count FROM companies");
    $companies = $stmt->fetch();
    
    // Test address database
    $stmt = $address_db->query("SELECT COUNT(*) as count FROM provinces");
    $provinces = $stmt->fetch();
    
    echo "Database connections successful!\n";
    echo "Companies: " . $companies['count'] . "\n";
    echo "Provinces: " . $provinces['count'] . "\n";
    
    // Test cross-database view
    $stmt = $main_db->query("SELECT COUNT(*) as count FROM v_branch_summary");
    $branches = $stmt->fetch();
    echo "Branch Summary View: " . $branches['count'] . " records\n";
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

?>
