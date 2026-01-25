<?php
/**
 * Database Integration Check
 * 
 * Comprehensive check of application-database integration
 */

require_once 'app/config/database.php';

echo "🔍 DATABASE INTEGRATION CHECK\n";
echo "=====================================\n\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "✅ Database Connection: SUCCESS\n";
    echo "📊 Database: " . DB_NAME . "\n";
    echo "👤 Host: " . DB_HOST . "\n\n";

    // Check core tables
    echo "🏗️ CORE TABLES VERIFICATION\n";
    echo "=====================================\n";
    
    $coreTables = [
        'companies', 'branches', 'members', 'products', 'transactions', 'inventory', 'categories'
    ];
    
    foreach ($coreTables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        $count = $exists ? $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'] : 0;
        echo "  - $table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . " ($count records)\n";
    }

    // Check Phase 3 tables
    echo "\n🚀 PHASE 3 TABLES VERIFICATION\n";
    echo "=====================================\n";
    
    $phase3Tables = [
        'files', 'file_categories', 'file_access_log',
        'notifications', 'notification_templates', 'notification_preferences', 'notification_queue', 'notification_settings',
        'websocket_connections', 'websocket_channels', 'websocket_channel_subscriptions', 'websocket_messages', 'websocket_message_delivery', 'websocket_events', 'websocket_settings', 'websocket_statistics',
        'search_index', 'search_queries', 'search_results', 'search_suggestions', 'search_analytics', 'search_settings', 'search_indexing_queue'
    ];
    
    foreach ($phase3Tables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        $count = $exists ? $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'] : 0;
        echo "  - $table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . " ($count records)\n";
    }

    // Check additional tables
    echo "\n🏗️ ADDITIONAL TABLES VERIFICATION\n";
    echo "=====================================\n";
    
    $additionalTables = [
        'customers', 'suppliers', 'journal_entries', 'journal_entry_lines', 'financial_reports', 'audit_logs'
    ];
    
    foreach ($additionalTables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        $count = $exists ? $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'] : 0;
        echo "  - $table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . " ($count records)\n";
    }

    // Check views
    echo "\n👁 DATABASE VIEWS VERIFICATION\n";
    echo "=====================================\n";
    
    try {
        $views = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($views as $view) {
            echo "  - " . $view['Tables_in_' . DB_NAME] . "\n";
        }
    } catch (Exception $e) {
        echo "  - Views check: ⚠️ ERROR - " . $e->getMessage() . "\n";
    }

    // Check foreign key relationships
    echo "\n🔗 FOREIGN KEY VERIFICATION\n";
    echo "=====================================\n";
    
    $relationships = [
        'companies -> branches (company_id)',
        'branches -> members (branch_id)',
        'products -> inventory (product_id)',
        'members -> transactions (created_by)',
        'files -> file_categories (category_id)',
        'notifications -> members (user_id)',
        'search_index -> members (created_by)',
        'websocket_connections -> members (user_id)'
    ];
    
    foreach ($relationships as $relationship) {
        try {
            list($table, $column) = explode(' -> ', $relationship);
            $sql = "SELECT COUNT(*) as count FROM information_schema.key_column_usage 
                    WHERE table_schema = '" . DB_NAME . "' 
                    AND table_name = '$table' 
                    AND column_name = '$column' 
                    AND referenced_table_name IS NOT NULL";
            $count = $pdo->query($sql)->fetch()['count'];
            echo "  - $relationship: " . ($count > 0 ? "✅ EXISTS" : "❌ MISSING") . "\n";
        } catch (Exception $e) {
            echo "  - $relationship: ❌ ERROR - " . $e->getMessage() . "\n";
        }
    }

    // Check indexes
    echo "\n📊 INDEX VERIFICATION\n";
    echo "=====================================\n";
    
    $indexes = $pdo->query("SHOW INDEX FROM information_schema.statistics WHERE table_schema = '" . DB_NAME . "'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($indexes as $index) {
        echo "  - " . $index['Table_name'] . "." . $index['Index_name'] . "\n";
    }

    // Check data integrity
    echo "\n🔍 DATA INTEGRITY CHECK\n";
    echo "====================================\n";
    
    // Check for orphaned records
    $orphanedChecks = [
        "SELECT COUNT(*) as count FROM members WHERE branch_id NOT IN (SELECT id_branch FROM branches)",
        "SELECT COUNT(*) as count FROM products WHERE category_id NOT IN (SELECT id_category FROM categories)",
        "SELECT COUNT(*) as count FROM transactions WHERE created_by NOT IN (SELECT id_member FROM members)"
    ];
    
    foreach ($orphanedChecks as $sql) {
        try {
            $count = $pdo->query($sql)->fetch()['count'];
            echo "  - Orphaned records check: " . ($count > 0 ? "⚠️ FOUND " . $count . " records" : "✅ OK") . "\n";
        } catch (Exception $e) {
            echo "  - Orphaned records check: ❌ ERROR - " . $e->getMessage() . "\n";
        }
    }

    // Check application files
    echo "\n📁 APPLICATION FILES VERIFICATION\n";
    echo "=====================================\n";
    
    $appFiles = [
        'app/config/database.php',
        'app/core/Controller.php',
        'app/models/File.php',
        'app/models/Notification.php',
        'app/models/WebSocket.php',
        'app/models/Search.php',
        'app/controllers/FileController.php',
        'app/controllers/NotificationController.php',
        'app/controllers/WebSocketController.php',
        'app/controllers/SearchController.php'
    ];
    
    foreach ($appFiles as $file) {
        $exists = file_exists($file);
        echo "  - $file: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Check view files
    echo "\n🎨 VIEW FILES VERIFICATION\n";
    echo "====================================\n";
    
    $viewFiles = [
        'app/views/layouts/main.php',
        'app/views/files/index.php',
        'app/views/notifications/index.php',
        'app/views/websocket/index.php',
        'app/views/search/index.php'
    ];
    
    foreach ($viewFiles as $file) {
        $exists = file_exists($file);
        echo "  - $file: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Check JavaScript files
    echo "\n⚡ JAVASCRIPT FILES VERIFICATION\n";
    echo "====================================\n";
    
    $jsFiles = [
        'public/assets/js/app.js',
        'public/assets/js/files.js',
        'public/assets/js/websocket.js',
        'public/assets/js/search.js'
    ];
    
    foreach ($jsFiles as $file) {
        $exists = file_exists($file);
        echo "  - $file: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Test database connectivity for models
    echo "\n🧪 MODEL CONNECTIVITY TEST\n";
    echo "=====================================\n";
    
    $modelTests = [
        'File' => 'app/models/File.php',
        'Notification' => 'app/models/Notification.php',
        'WebSocket' => 'app/models/WebSocket.php',
        'Search' => 'app/models/Search.php'
    ];
    
    foreach ($modelTests as $modelName => $modelFile) {
        try {
            require_once $modelFile;
            if (class_exists($modelName)) {
                echo "  - $modelName Model: ✅ LOADED\n";
            } else {
                echo "  - $modelName Model: ❌ CLASS NOT FOUND\n";
            }
        } catch (Exception $e) {
            echo "  - $modelName Model: ❌ ERROR - " . $e->getMessage() . "\n";
        }
    }

    // Test controller connectivity
    echo "\n🎮 CONTROLLER CONNECTIVITY TEST\n";
    echo "====================================\n";
    
    $controllerTests = [
        'FileController' => 'app/controllers/FileController.php',
        'NotificationController' => 'app/controllers/NotificationController.php',
        'WebSocketController' => 'app/controllers/WebSocketController.php',
        'SearchController' => 'app/controllers/SearchController.php'
    ];
    
    foreach ($controllerTests as $controllerName => $controllerFile) {
        try {
            require_once $controllerFile;
            if (class_exists($controllerName)) {
                echo "  - $controllerName: ✅ LOADED\n";
            } else {
                echo "  - $controllerName: ❌ CLASS NOT FOUND\n";
            }
        } catch (Exception $e) {
            echo "  - $controllerName: ❌ ERROR - " . $e->getMessage() . "\n";
        }
    }

    // Test routing
    echo "\n🛣️ ROUTING INTEGRATION TEST\n";
    echo "====================================\n";
    
    $routingFile = file_get_contents('index.php');
    $routes = [
        'files' => strpos($routingFile, 'case \'files\'') !== false,
        'notifications' => strpos($routingFile, 'case \'notifications\'') !== false,
        'websocket' => strpos($routingFile, 'case \'websocket\'') !== false,
        'search' => strpos($routingFile, 'case \'search\'') !== false
    ];
    
    foreach ($routes as $route => $exists) {
        echo "  - $route: " . ($exists ? "✅ INTEGRATED" : "❌ MISSING") . "\n";
    }

    // Final summary
    echo "\n📊 FINAL SUMMARY\n";
    echo "=====================================\n";
    
    $totalTables = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'")->fetch()['count'];
    $totalViews = $pdo->query("SELECT COUNT(*) as count FROM information_schema.views WHERE table_schema = '" . DB_NAME . "'")->fetch()['count'];
    
    echo "📊 Total Database Tables: $totalTables\n";
    echo "📊 Total Database Views: $totalViews\n";
    echo "📊 Database Size: " . $this->getDatabaseSize($pdo) . "\n";
    echo "🎯 Status: " . ($totalTables >= 100 ? "EXCELLENT" : "NEEDS ATTENTION") . "\n";

} catch (PDOException $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ GENERAL ERROR: " . $e->getMessage() . "\n";
}

function getDatabaseSize($pdo) {
    try {
        $result = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
        return $result['size_mb'] . ' MB';
    } catch (Exception $e) {
        return 'Unknown';
    }
}
?>
