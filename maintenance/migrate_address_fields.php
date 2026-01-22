<?php
/**
 * Migration: Add Address Fields to Companies and Branches Tables
 * Date: 2026-01-22
 * Description: Add address_detail, province_id, regency_id, district_id, village_id fields
 */

require_once __DIR__ . '/app/config/database.php';

header('Content-Type: text/plain');

echo "=== Address Fields Migration ===\n\n";

try {
    $db = Database::getInstance();
    
    echo "1. Adding address fields to companies table...\n";
    
    // Check if address_detail exists in companies
    $checkAddressDetail = $db->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                                      WHERE TABLE_SCHEMA = DATABASE() 
                                      AND TABLE_NAME = 'companies' 
                                      AND COLUMN_NAME = 'address_detail'")->fetchColumn();
    
    if ($checkAddressDetail == 0) {
        echo "   Adding address_detail to companies...\n";
        $db->query("ALTER TABLE companies ADD COLUMN address_detail TEXT AFTER address");
        echo "   ✓ address_detail added to companies\n";
    } else {
        echo "   - address_detail already exists in companies\n";
    }
    
    // Check if province_id exists in companies
    $checkProvinceId = $db->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                                   WHERE TABLE_SCHEMA = DATABASE() 
                                   AND TABLE_NAME = 'companies' 
                                   AND COLUMN_NAME = 'province_id'")->fetchColumn();
    
    if ($checkProvinceId == 0) {
        echo "   Adding province_id to companies...\n";
        $db->query("ALTER TABLE companies ADD COLUMN province_id INT AFTER address_detail");
        echo "   ✓ province_id added to companies\n";
    } else {
        echo "   - province_id already exists in companies\n";
    }
    
    // Add other address fields to companies
    $addressFields = [
        'regency_id' => 'INT AFTER province_id',
        'district_id' => 'INT AFTER regency_id', 
        'village_id' => 'INT AFTER district_id'
    ];
    
    foreach ($addressFields as $field => $definition) {
        $checkField = $db->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                                 WHERE TABLE_SCHEMA = DATABASE() 
                                 AND TABLE_NAME = 'companies' 
                                 AND COLUMN_NAME = '$field'")->fetchColumn();
        
        if ($checkField == 0) {
            echo "   Adding $field to companies...\n";
            $db->query("ALTER TABLE companies ADD COLUMN $field $definition");
            echo "   ✓ $field added to companies\n";
        } else {
            echo "   - $field already exists in companies\n";
        }
    }
    
    echo "\n2. Adding address fields to branches table...\n";
    
    // Check and add address fields to branches
    $branchAddressFields = [
        'address_detail' => 'TEXT AFTER address',
        'province_id' => 'INT AFTER address_detail',
        'regency_id' => 'INT AFTER province_id',
        'district_id' => 'INT AFTER regency_id', 
        'village_id' => 'INT AFTER district_id'
    ];
    
    foreach ($branchAddressFields as $field => $definition) {
        $checkField = $db->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                                 WHERE TABLE_SCHEMA = DATABASE() 
                                 AND TABLE_NAME = 'branches' 
                                 AND COLUMN_NAME = '$field'")->fetchColumn();
        
        if ($checkField == 0) {
            echo "   Adding $field to branches...\n";
            $db->query("ALTER TABLE branches ADD COLUMN $field $definition");
            echo "   ✓ $field added to branches\n";
        } else {
            echo "   - $field already exists in branches\n";
        }
    }
    
    echo "\n3. Adding indexes for address fields...\n";
    
    // Add indexes for companies
    $companyIndexes = [
        'idx_company_province_id' => 'companies (province_id)',
        'idx_company_regency_id' => 'companies (regency_id)',
        'idx_company_district_id' => 'companies (district_id)',
        'idx_company_village_id' => 'companies (village_id)'
    ];
    
    foreach ($companyIndexes as $indexName => $indexDefinition) {
        try {
            $db->query("CREATE INDEX $indexName ON $indexDefinition");
            echo "   ✓ Added $indexName\n";
        } catch (Exception $e) {
            echo "   - Index $indexName already exists\n";
        }
    }
    
    // Add indexes for branches
    $branchIndexes = [
        'idx_branch_province_id' => 'branches (province_id)',
        'idx_branch_regency_id' => 'branches (regency_id)',
        'idx_branch_district_id' => 'branches (district_id)',
        'idx_branch_village_id' => 'branches (village_id)'
    ];
    
    foreach ($branchIndexes as $indexName => $indexDefinition) {
        try {
            $db->query("CREATE INDEX $indexName ON $indexDefinition");
            echo "   ✓ Added $indexName\n";
        } catch (Exception $e) {
            echo "   - Index $indexName already exists\n";
        }
    }
    
    echo "\n=== Migration completed successfully! ===\n";
    echo "Address fields have been added to companies and branches tables.\n";
    echo "You can now use the address dropdown system in forms.\n";
    
} catch (Exception $e) {
    echo "\n=== Migration failed! ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
