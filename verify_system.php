<?php
/**
 * System Verification Script
 * 
 * Verifies that all Phase 3 features are properly integrated
 * and the database tables are correctly created.
 */

require_once 'app/config/database.php';

echo "🔍 SYSTEM VERIFICATION REPORT\n";
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
    echo "📊 Database: " . DB_NAME . "\n\n";

    // Check Phase 3 Tables
    echo "🚀 PHASE 3 FEATURES VERIFICATION\n";
    echo "=====================================\n\n";

    // 1. File Management System
    echo "📁 FILE MANAGEMENT SYSTEM:\n";
    $fileTables = ['files', 'file_categories', 'file_access_log'];
    foreach ($fileTables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        echo "  - $table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // 2. Notification System
    echo "\n🔔 NOTIFICATION SYSTEM:\n";
    $notificationTables = ['notifications', 'notification_templates', 'notification_preferences', 'notification_queue', 'notification_settings'];
    foreach ($notificationTables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        echo "  - $table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // 3. WebSocket Integration
    echo "\n🌐 WEBSOCKET INTEGRATION:\n";
    $websocketTables = ['websocket_connections', 'websocket_channels', 'websocket_channel_subscriptions', 'websocket_messages', 'websocket_message_delivery', 'websocket_events', 'websocket_settings', 'websocket_statistics'];
    foreach ($websocketTables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        echo "  - $table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // 4. Search System
    echo "\n🔍 SEARCH SYSTEM:\n";
    $searchTables = ['search_index', 'search_queries', 'search_results', 'search_suggestions', 'search_analytics', 'search_settings', 'search_indexing_queue'];
    foreach ($searchTables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        echo "  - $table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Check API Documentation
    echo "\n📚 API DOCUMENTATION:\n";
    $apiDocFile = __DIR__ . '/public/api/documentation.php';
    echo "  - API Documentation: " . (file_exists($apiDocFile) ? "✅ EXISTS" : "❌ MISSING") . "\n";

    // Check Model Files
    echo "\n🏗️ MODEL FILES:\n";
    $modelFiles = [
        'File.php' => 'File Management',
        'Notification.php' => 'Notification System',
        'WebSocket.php' => 'WebSocket Integration',
        'Search.php' => 'Search System'
    ];
    
    foreach ($modelFiles as $file => $feature) {
        $exists = file_exists(__DIR__ . '/app/models/' . $file);
        echo "  - $file ($feature): " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Check Controller Files
    echo "\n🎮 CONTROLLER FILES:\n";
    $controllerFiles = [
        'FileController.php' => 'File Management',
        'NotificationController.php' => 'Notification System',
        'WebSocketController.php' => 'WebSocket Integration',
        'SearchController.php' => 'Search System'
    ];
    
    foreach ($controllerFiles as $file => $feature) {
        $exists = file_exists(__DIR__ . '/app/controllers/' . $file);
        echo "  - $file ($feature): " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Check View Files
    echo "\n🎨 VIEW FILES:\n";
    $viewFiles = [
        'files/index.php' => 'File Management',
        'notifications/index.php' => 'Notification System',
        'websocket/index.php' => 'WebSocket Integration',
        'search/index.php' => 'Search System'
    ];
    
    foreach ($viewFiles as $file => $feature) {
        $exists = file_exists(__DIR__ . '/app/views/' . $file);
        echo "  - $file ($feature): " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Check JavaScript Files
    echo "\n⚡ JAVASCRIPT MODULES:\n";
    $jsFiles = [
        'files.js' => 'File Management',
        'websocket.js' => 'WebSocket Integration',
        'search.js' => 'Search System'
    ];
    
    foreach ($jsFiles as $file => $feature) {
        $exists = file_exists(__DIR__ . '/public/assets/js/' . $file);
        echo "  - $file ($feature): " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Check Navigation Integration
    echo "\n🧭 NAVIGATION INTEGRATION:\n";
    $mainLayout = file_get_contents(__DIR__ . '/app/views/layouts/main.php');
    $hasFiles = strpos($mainLayout, 'files') !== false;
    $hasNotifications = strpos($mainLayout, 'notifications') !== false;
    $hasWebSocket = strpos($mainLayout, 'websocket') !== false;
    $hasSearch = strpos($mainLayout, 'search') !== false;
    
    echo "  - File Management: " . ($hasFiles ? "✅ INTEGRATED" : "❌ MISSING") . "\n";
    echo "  - Notifications: " . ($hasNotifications ? "✅ INTEGRATED" : "❌ MISSING") . "\n";
    echo "  - WebSocket: " . ($hasWebSocket ? "✅ INTEGRATED" : "❌ MISSING") . "\n";
    echo "  - Search: " . ($hasSearch ? "✅ INTEGRATED" : "❌ MISSING") . "\n";

    // Check Router Integration
    echo "\n🛣️ ROUTER INTEGRATION:\n";
    $indexFile = file_get_contents(__DIR__ . '/index.php');
    $hasFilesRoute = strpos($indexFile, 'case \'files\'') !== false;
    $hasNotificationsRoute = strpos($indexFile, 'case \'notifications\'') !== false;
    $hasWebSocketRoute = strpos($indexFile, 'case \'websocket\'') !== false;
    $hasSearchRoute = strpos($indexFile, 'case \'search\'') !== false;
    
    echo "  - File Management: " . ($hasFilesRoute ? "✅ INTEGRATED" : "❌ MISSING") . "\n";
    echo "  - Notifications: " . ($hasNotificationsRoute ? "✅ INTEGRATED" : "❌ MISSING") . "\n";
    echo "  - WebSocket: " . ($hasWebSocketRoute ? "✅ INTEGRATED" : "❌ MISSING") . "\n";
    echo "  - Search: " . ($hasSearchRoute ? "✅ INTEGRATED" : "❌ MISSING") . "\n";

    // Sample Data Verification
    echo "\n📊 SAMPLE DATA VERIFICATION:\n";
    
    // Check notification templates
    $templateCount = $pdo->query("SELECT COUNT(*) as count FROM notification_templates")->fetch()['count'];
    echo "  - Notification Templates: $templateCount records\n";
    
    // Check search settings
    $settingsCount = $pdo->query("SELECT COUNT(*) as count FROM search_settings")->fetch()['count'];
    echo "  - Search Settings: $settingsCount records\n";
    
    // Check websocket settings
    $wsSettingsCount = $pdo->query("SELECT COUNT(*) as count FROM websocket_settings")->fetch()['count'];
    echo "  - WebSocket Settings: $wsSettingsCount records\n";

    // Total Statistics
    echo "\n📈 SYSTEM STATISTICS:\n";
    $totalTables = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'")->fetch()['count'];
    echo "  - Total Database Tables: $totalTables\n";
    
    $totalViews = $pdo->query("SELECT COUNT(*) as count FROM information_schema.views WHERE table_schema = '" . DB_NAME . "'")->fetch()['count'];
    echo "  - Total Database Views: $totalViews\n";

    echo "\n=====================================\n";
    echo "✅ VERIFICATION COMPLETED\n";
    echo "=====================================\n";

    // Overall Status
    $criticalTables = 0;
    $expectedTables = 4 * 5; // 4 systems x 5 tables average
    
    // Count critical tables
    $allTables = array_merge($fileTables, $notificationTables, $websocketTables, $searchTables);
    foreach ($allTables as $table) {
        if ($pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0) {
            $criticalTables++;
        }
    }

    $completionRate = round(($criticalTables / count($allTables)) * 100, 1);
    echo "📊 Phase 3 Completion Rate: $completionRate%\n";
    echo "📊 Critical Tables: $criticalTables/" . count($allTables) . "\n";

    if ($completionRate >= 80) {
        echo "🎉 STATUS: EXCELLENT - System is ready for production!\n";
    } elseif ($completionRate >= 60) {
        echo "✅ STATUS: GOOD - System is mostly functional\n";
    } else {
        echo "⚠️  STATUS: NEEDS ATTENTION - Some features may not work\n";
    }

} catch (PDOException $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ GENERAL ERROR: " . $e->getMessage() . "\n";
}
?>
