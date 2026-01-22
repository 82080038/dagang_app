<?php
/**
 * Web-based Database Migration Script
 * Add scalability_level field to companies table
 * Access: http://localhost/dagang/migrate.php
 */

// Prevent direct access in production
if (!defined('APP_DEBUG') || !APP_DEBUG) {
    die('Migration script is only available in debug mode.');
}

require_once __DIR__ . '/app/config/database.php';

header('Content-Type: text/plain');

echo "=== Database Migration: Add scalability_level ===\n\n";

try {
    $db = Database::getInstance();
    
    echo "1. Checking if scalability_level column exists...\n";
    
    // Check if column exists
    $checkSql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = 'companies' 
                 AND COLUMN_NAME = 'scalability_level'";
    
    $stmt = $db->query($checkSql);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "   Column does not exist. Adding scalability_level column...\n";
        
        // Add the column
        $alterSql = "ALTER TABLE companies ADD COLUMN scalability_level ENUM('1','2','3','4','5','6') DEFAULT '1' AFTER company_type";
        $db->query($alterSql);
        echo "   ✓ Column added successfully.\n";
        
        // Add index
        echo "2. Adding index for scalability_level...\n";
        $indexSql = "ALTER TABLE companies ADD INDEX idx_scalability_level (scalability_level)";
        $db->query($indexSql);
        echo "   ✓ Index added successfully.\n";
        
        // Update existing records
        echo "3. Updating existing records with default scalability levels...\n";
        
        $updates = [
            "UPDATE companies SET scalability_level = '1' WHERE company_type IN ('individual', 'personal') AND scalability_level IS NULL",
            "UPDATE companies SET scalability_level = '2' WHERE company_type IN ('warung', 'kios') AND scalability_level IS NULL", 
            "UPDATE companies SET scalability_level = '3' WHERE company_type IN ('toko_kelontong', 'toko') AND scalability_level IS NULL",
            "UPDATE companies SET scalability_level = '4' WHERE company_type IN ('minimarket', 'pengusaha_menengah') AND scalability_level IS NULL",
            "UPDATE companies SET scalability_level = '5' WHERE company_type IN ('distributor') AND scalability_level IS NULL",
            "UPDATE companies SET scalability_level = '6' WHERE company_type IN ('perusahaan_besar', 'franchise') AND scalability_level IS NULL",
            "UPDATE companies SET scalability_level = '1' WHERE scalability_level IS NULL"
        ];
        
        foreach ($updates as $i => $sql) {
            $stmt = $db->query($sql);
            echo "   " . ($i + 1) . ". Updated records\n";
        }
        
        echo "   ✓ Existing records updated successfully.\n";
    } else {
        echo "   Column already exists. Skipping migration.\n";
    }
    
    echo "\n=== Migration completed successfully! ===\n";
    echo "You can now refresh the companies page to see the scalability levels.\n";
    
} catch (Exception $e) {
    echo "\n=== Migration failed! ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
    exit(1);
}
?>
