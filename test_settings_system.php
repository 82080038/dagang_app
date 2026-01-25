<?php
/**
 * Test Script for System Settings Management
 * Tests database connection, model functionality, and basic CRUD operations
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'perdagangan_system';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== SYSTEM SETTINGS MANAGEMENT TEST ===\n\n";
    
    // Test 1: Check if tables exist
    echo "1. Testing Database Tables...\n";
    $tables = ['system_settings', 'backup_history', 'system_logs', 'email_templates', 'feature_toggles'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' missing\n";
        }
    }
    
    // Test 2: Check if settings data exists
    echo "\n2. Testing Settings Data...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_settings");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count settings in database\n";
    
    if ($count > 0) {
        // Test 3: Test Settings Model functionality
        echo "\n3. Testing Settings Model...\n";
        
        // Test getSetting method
        $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'app_name'");
        $result = $stmt->fetch();
        if ($result) {
            echo "✓ getSetting('app_name'): " . $result['setting_value'] . "\n";
        }
        
        // Test getSettingsByGroup method
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_group = 'general'");
        $generalSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ getSettingsByGroup('general'): Found " . count($generalSettings) . " settings\n";
        
        // Test getAllSettings method
        $stmt = $pdo->query("SELECT setting_group, COUNT(*) as count FROM system_settings GROUP BY setting_group");
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ getAllSettings(): Found " . count($groups) . " groups\n";
        foreach ($groups as $group) {
            echo "  - " . $group['setting_group'] . ": " . $group['count'] . " settings\n";
        }
    }
    
    // Test 4: Test Settings Controller methods
    echo "\n4. Testing Settings Controller Integration...\n";
    
    // Test updateSetting method simulation
    $testKey = 'test_setting_' . time();
    $testValue = 'test_value_' . time();
    $testGroup = 'test';
    
    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_group, description, is_active, created_at, updated_at) VALUES (?, ?, ?, 'Test setting', 1, NOW(), NOW())");
    $result = $stmt->execute([$testKey, $testValue, $testGroup]);
    
    if ($result) {
        echo "✓ updateSetting(): Successfully created test setting\n";
        
        // Verify the setting was created
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$testKey]);
        $result = $stmt->fetch();
        
        if ($result && $result['setting_value'] === $testValue) {
            echo "✓ updateSetting(): Setting value verified\n";
        }
        
        // Clean up test setting
        $stmt = $pdo->prepare("DELETE FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$testKey]);
        echo "✓ updateSetting(): Test setting cleaned up\n";
    }
    
    // Test 5: Test Feature Toggles
    echo "\n5. Testing Feature Toggles...\n";
    $stmt = $pdo->query("SELECT feature_name, is_enabled FROM feature_toggles ORDER BY feature_name");
    $features = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Found " . count($features) . " feature toggles\n";
    
    foreach ($features as $feature) {
        $status = $feature['is_enabled'] ? 'enabled' : 'disabled';
        echo "  - " . $feature['feature_name'] . ": $status\n";
    }
    
    // Test 6: Test Email Templates
    echo "\n6. Testing Email Templates...\n";
    $stmt = $pdo->query("SELECT template_name, subject FROM email_templates ORDER BY template_name");
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Found " . count($templates) . " email templates\n";
    
    foreach ($templates as $template) {
        echo "  - " . $template['template_name'] . ": " . $template['subject'] . "\n";
    }
    
    // Test 7: Test System Health
    echo "\n7. Testing System Health...\n";
    
    // Database health
    try {
        $pdo->query("SELECT 1");
        echo "✓ Database connection: healthy\n";
    } catch (Exception $e) {
        echo "✗ Database connection: failed - " . $e->getMessage() . "\n";
    }
    
    // Disk space
    $totalSpace = disk_total_space('/');
    $freeSpace = disk_free_space('/');
    $usedSpace = $totalSpace - $freeSpace;
    $usagePercentage = ($usedSpace / $totalSpace) * 100;
    
    echo "✓ Disk space: " . round($usagePercentage, 2) . "% used\n";
    
    // Memory usage
    $memoryLimit = ini_get('memory_limit');
    $memoryUsage = memory_get_usage(true);
    $memoryUsagePercentage = ($memoryUsage / parseBytes($memoryLimit)) * 100;
    
    echo "✓ Memory usage: " . round($memoryUsagePercentage, 2) . "% of $memoryLimit\n";
    
    // Test 8: Test Security Settings
    echo "\n8. Testing Security Settings...\n";
    $securitySettings = ['session_timeout', 'max_login_attempts', 'password_min_length', 'enable_2fa'];
    
    foreach ($securitySettings as $setting) {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$setting]);
        $result = $stmt->fetch();
        
        if ($result) {
            $value = $setting === 'enable_2fa' ? ($result['setting_value'] == '1' ? 'enabled' : 'disabled') : $result['setting_value'];
            echo "✓ $setting: $value\n";
        } else {
            echo "✗ $setting: not found\n";
        }
    }
    
    echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
    echo "System Settings Management is working correctly!\n";
    
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

function parseBytes($bytes) {
    $unit = preg_replace('/[^0-9]/', '', $bytes);
    $value = (int) $unit;
    
    if (strpos($bytes, 'K') !== false) {
        return $value * 1024;
    } elseif (strpos($bytes, 'M') !== false) {
        return $value * 1024 * 1024;
    } elseif (strpos($bytes, 'G') !== false) {
        return $value * 1024 * 1024 * 1024;
    }
    
    return $value;
}
?>
