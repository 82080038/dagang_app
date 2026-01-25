<?php

/**
 * WebSocket Controller
 * 
 * Handles WebSocket-related API endpoints and server management
 * Provides real-time collaboration features and message broadcasting
 */

require_once __DIR__ . '/../core/Controller.php';

class WebSocketController extends Controller {
    
    private $webSocket;
    private $config;
    
    public function __construct() {
        parent::__construct();
        $this->webSocket = new WebSocket();
        $this->config = [
            'enabled' => $this->webSocket->getSetting('websocket_enabled', true),
            'websocket_host' => $this->webSocket->getSetting('websocket_host', 'localhost'),
            'websocket_port' => $this->webSocket->getSetting('websocket_port', 8080)
        ];
    }
    
    /**
     * Main WebSocket management page
     */
    public function index() {
        $this->requireAuth();
        
        // Check if user has permission to access WebSocket management
        if (!$this->hasPermission(ROLE_ADMIN)) {
            $_SESSION['flash_error'] = 'Access denied. You need admin privileges to access WebSocket management.';
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        $data = [
            'title' => 'WebSocket Management',
            'config' => $this->config,
            'statistics' => $this->webSocket->getRealTimeStatistics(),
            'active_connections' => $this->webSocket->getActiveConnections(),
            'channels' => $this->webSocket->getUserChannels($_SESSION['user_id']),
            'recent_events' => $this->webSocket->getRecentEvents(50),
            'settings' => $this->getWebSocketSettings()
        ];
        
        $this->view('websocket/index', $data);
    }
    
    /**
     * Get WebSocket server status
     */
    public function getStatus() {
        $this->requireAuth();
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'enabled' => $this->config['enabled'],
                'config' => $this->config,
                'statistics' => $this->webSocket->getRealTimeStatistics(),
                'active_connections' => count($this->webSocket->getActiveConnections()),
                'online_users' => $this->webSocket->getOnlineUsers()
            ]
        ]);
    }
    
    /**
     * Get connected clients
     */
    public function getClients() {
        $this->requireAuth();
        
        $filters = [];
        if (!$this->hasPermission(ROLE_APP_ADMIN)) {
            $filters['company_id'] = $this->getUserCompanyId();
            $filters['branch_id'] = $this->getUserBranchId();
        }
        
        $connections = $this->webSocket->getActiveConnections($filters);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $connections
        ]);
    }
    
    /**
     * Get active channels
     */
    public function getChannels() {
        $this->requireAuth();
        
        $channels = $this->webSocket->getUserChannels($_SESSION['user_id']);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $channels
        ]);
    }
    
    /**
     * Send test notification
     */
    public function sendTestNotification() {
        $this->requireAuth();
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $title = $_POST['title'] ?? 'Test Notification';
        $message = $_POST['message'] ?? 'This is a test notification from the WebSocket system';
        $type = $_POST['type'] ?? 'info';
        $channel = $_POST['channel'] ?? 'notifications';
        
        // Get channel
        $channelData = $this->webSocket->getChannelByName($channel);
        if (!$channelData) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Channel not found: ' . $channel
            ], 404);
            return;
        }
        
        $result = $this->webSocket->sendMessageToChannel(
            $channelData['id_channel'],
            $_SESSION['user_id'],
            'json',
            json_encode([
                'type' => 'test_notification',
                'title' => $title,
                'message' => $message,
                'notification_type' => $type,
                'timestamp' => time()
            ]),
            'normal'
        );
        
        if ($result) {
            // Log the event
            $this->webSocket->logEvent('notification', 'test_notification_sent', [
                'title' => $title,
                'message' => $message,
                'channel' => $channel
            ], $_SESSION['user_id']);
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Test notification sent successfully',
                'data' => ['message_id' => $result]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to send test notification'
            ], 500);
        }
    }
    
    /**
     * Broadcast message to channel
     */
    public function broadcast() {
        $this->requireAuth();
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $channelName = $_POST['channel'] ?? null;
        $message = $_POST['message'] ?? null;
        $priority = $_POST['priority'] ?? 'normal';
        
        if (!$channelName || !$message) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Channel and message are required'
            ], 400);
            return;
        }
        
        // Get channel
        $channelData = $this->webSocket->getChannelByName($channelName);
        if (!$channelData) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Channel not found: ' . $channelName
            ], 404);
            return;
        }
        
        $broadcastMessage = [
            'type' => 'broadcast',
            'data' => [
                'message' => $message,
                'broadcast_type' => 'user_message',
                'timestamp' => date('Y-m-d H:i:s'),
                'broadcast_by' => $_SESSION['user_id'],
                'broadcast_by_name' => $_SESSION['user_name'] ?? 'Unknown User'
            ]
        ];
        
        $result = $this->webSocket->sendMessageToChannel(
            $channelData['id_channel'],
            $_SESSION['user_id'],
            'json',
            json_encode($broadcastMessage),
            $priority
        );
        
        if ($result) {
            // Log the broadcast event
            $this->webSocket->logEvent('broadcast', 'message_broadcast', [
                'channel' => $channelName,
                'message' => $message,
                'priority' => $priority
            ], $_SESSION['user_id']);
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Message broadcasted to channel: ' . $channelName,
                'data' => ['message_id' => $result]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to broadcast message'
            ], 500);
        }
    }
    
    /**
     * Send system alert
     */
    public function sendSystemAlert() {
        $this->requireAuth();
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $title = $_POST['title'] ?? 'System Alert';
        $message = $_POST['message'] ?? null;
        $level = $_POST['level'] ?? 'info';
        $targetUsers = $_POST['target_users'] ?? null;
        
        if (!$message) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Message is required'
            ], 400);
            return;
        }
        
        $result = $this->webSocket->sendSystemAlert($title, $message, $level, $targetUsers);
        
        if ($result) {
            // Log the system alert
            $this->webSocket->logEvent('system', 'system_alert_sent', [
                'title' => $title,
                'message' => $message,
                'level' => $level,
                'target_users' => $targetUsers
            ], $_SESSION['user_id']);
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'System alert sent successfully',
                'data' => ['message_id' => $result]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to send system alert'
            ], 500);
        }
    }
    
    /**
     * Send private message
     */
    public function sendPrivateMessage() {
        $this->requireAuth();
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $recipientId = $_POST['recipient_id'] ?? null;
        $message = $_POST['message'] ?? null;
        
        if (!$recipientId || !$message) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Recipient ID and message are required'
            ], 400);
            return;
        }
        
        $result = $this->webSocket->sendPrivateMessage(
            $_SESSION['user_id'],
            $recipientId,
            $message
        );
        
        if ($result) {
            // Log the private message
            $this->webSocket->logEvent('message', 'private_message_sent', [
                'recipient_id' => $recipientId,
                'message' => $message
            ], $_SESSION['user_id'], $recipientId);
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Private message sent successfully',
                'data' => ['message_id' => $result]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to send private message'
            ], 500);
        }
    }
    
    /**
     * Get channel messages
     */
    public function getChannelMessages() {
        $this->requireAuth();
        
        $channelId = $_GET['channel_id'] ?? null;
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        $beforeMessageId = $_GET['before_message_id'] ?? null;
        
        if (!$channelId) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Channel ID is required'
            ], 400);
            return;
        }
        
        $messages = $this->webSocket->getChannelMessages($channelId, $limit, $offset, $beforeMessageId);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $messages
        ]);
    }
    
    /**
     * Get online users
     */
    public function getOnlineUsers() {
        $this->requireAuth();
        
        $filters = [];
        if (!$this->hasPermission(ROLE_APP_ADMIN)) {
            $filters['company_id'] = $this->getUserCompanyId();
            $filters['branch_id'] = $this->getUserBranchId();
        }
        
        $onlineUsers = $this->webSocket->getOnlineUsers($filters);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $onlineUsers
        ]);
    }
    
    /**
     * Update user presence
     */
    public function updatePresence() {
        $this->requireAuth();
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $presence = $_POST['presence'] ?? 'online';
        $status = $_POST['status'] ?? '';
        
        $result = $this->webSocket->updateUserPresence($_SESSION['user_id'], $presence, $status);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Presence updated successfully'
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to update presence'
            ], 500);
        }
    }
    
    /**
     * Enable/disable WebSocket
     */
    public function toggleWebSocket() {
        $this->requireAuth();
        $this->requirePermission(ROLE_APP_ADMIN);
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $enabled = $_POST['enabled'] ?? null;
        
        if ($enabled === null) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Enabled status is required'
            ], 400);
            return;
        }
        
        $this->webSocket->updateSetting('websocket_enabled', $enabled, 'boolean');
        
        $this->jsonResponse([
            'status' => 'success',
            'message' => 'WebSocket ' . ($enabled ? 'enabled' : 'disabled') . ' successfully',
            'data' => [
                'enabled' => $this->webSocket->getSetting('websocket_enabled')
            ]
        ]);
    }
    
    /**
     * Update WebSocket configuration
     */
    public function updateConfig() {
        $this->requireAuth();
        $this->requirePermission(ROLE_APP_ADMIN);
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $config = $_POST['config'] ?? [];
        
        if (empty($config)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Configuration data is required'
            ], 400);
            return;
        }
        
        // Update each setting
        foreach ($config as $key => $value) {
            $type = 'string';
            if (is_bool($value)) {
                $type = 'boolean';
            } elseif (is_numeric($value)) {
                $type = 'number';
            } elseif (is_array($value)) {
                $type = 'json';
            }
            
            $this->webSocket->updateSetting($key, $value, $type);
        }
        
        $this->jsonResponse([
            'status' => 'success',
            'message' => 'WebSocket configuration updated successfully',
            'data' => [
                'config' => $this->getWebSocketSettings()
            ]
        ]);
    }
    
    /**
     * Send file upload notification
     */
    public function notifyFileUpload() {
        $this->requireAuth();
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $fileId = $_POST['file_id'] ?? null;
        $fileName = $_POST['file_name'] ?? null;
        $fileSize = $_POST['file_size'] ?? null;
        
        if (!$fileId || !$fileName) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'File ID and name are required'
            ], 400);
            return;
        }
        
        $result = $this->webSocket->sendFileNotification($fileId, $fileName, $fileSize, $_SESSION['user_id']);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'File upload notification sent'
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to send file upload notification'
            ], 500);
        }
    }
    
    /**
     * Send file download notification
     */
    public function notifyFileDownload() {
        $this->requireAuth();
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $fileId = $_POST['file_id'] ?? null;
        $fileName = $_POST['file_name'] ?? null;
        $fileSize = $_POST['file_size'] ?? null;
        
        if (!$fileId || !$fileName) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'File ID and name are required'
            ], 400);
            return;
        }
        
        $result = $this->webSocket->sendFileNotification($fileId, $fileName, $fileSize, $_SESSION['user_id'], 'file-downloads');
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'File download notification sent'
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to send file download notification'
            ], 500);
        }
    }
    
    /**
     * Get WebSocket statistics
     */
    public function getStatistics() {
        $this->requireAuth();
        
        $dateRange = $_GET['date_range'] ?? '7days';
        $statistics = $this->webSocket->getStatistics($dateRange);
        $realTimeStats = $this->webSocket->getRealTimeStatistics();
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'historical' => $statistics,
                'realtime' => $realTimeStats
            ]
        ]);
    }
    
    /**
     * Cleanup operations
     */
    public function cleanup() {
        $this->requireAuth();
        $this->requirePermission(ROLE_APP_ADMIN);
        $this->validateCSRF();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $cleanupType = $_POST['cleanup_type'] ?? 'all';
        $results = [];
        
        if ($cleanupType === 'all' || $cleanupType === 'connections') {
            $results['connections_cleaned'] = $this->webSocket->cleanupInactiveConnections();
        }
        
        if ($cleanupType === 'all' || $cleanupType === 'messages') {
            $days = $_POST['message_retention_days'] ?? 30;
            $results['messages_cleaned'] = $this->webSocket->cleanupOldMessages($days);
        }
        
        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Cleanup completed successfully',
            'data' => $results
        ]);
    }
    
    /**
     * Get WebSocket settings
     */
    private function getWebSocketSettings() {
        return [
            'websocket_enabled' => $this->webSocket->getSetting('websocket_enabled', true),
            'websocket_host' => $this->webSocket->getSetting('websocket_host', 'localhost'),
            'websocket_port' => $this->webSocket->getSetting('websocket_port', 8080),
            'max_connections_per_user' => $this->webSocket->getSetting('max_connections_per_user', 5),
            'connection_timeout' => $this->webSocket->getSetting('connection_timeout', 300),
            'message_retention_days' => $this->webSocket->getSetting('message_retention_days', 30),
            'enable_file_sharing' => $this->webSocket->getSetting('enable_file_sharing', true),
            'max_file_size_mb' => $this->webSocket->getSetting('max_file_size_mb', 10),
            'enable_private_messages' => $this->webSocket->getSetting('enable_private_messages', true),
            'enable_channel_creation' => $this->webSocket->getSetting('enable_channel_creation', false),
            'auto_cleanup_connections' => $this->webSocket->getSetting('auto_cleanup_connections', true),
            'cleanup_interval_minutes' => $this->webSocket->getSetting('cleanup_interval_minutes', 5),
            'enable_message_encryption' => $this->webSocket->getSetting('enable_message_encryption', false),
            'enable_rate_limiting' => $this->webSocket->getSetting('enable_rate_limiting', true),
            'max_messages_per_minute' => $this->webSocket->getSetting('max_messages_per_minute', 30),
            'enable_presence_tracking' => $this->webSocket->getSetting('enable_presence_tracking', true),
            'enable_typing_indicators' => $this->webSocket->getSetting('enable_typing_indicators', true),
            'enable_read_receipts' => $this->webSocket->getSetting('enable_read_receipts', true),
            'enable_message_history' => $this->webSocket->getSetting('enable_message_history', true)
        ];
    }
    
    /**
     * Helper method to send JSON response
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
