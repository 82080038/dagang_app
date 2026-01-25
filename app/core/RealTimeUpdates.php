<?php

/**
 * Real-time Updates Manager
 * 
 * Manages real-time notifications and updates through WebSocket
 * Integrates with existing system components to provide live updates
 */

class RealTimeUpdates {
    
    private $webSocketServer;
    private $config;
    
    public function __construct($config = []) {
        $this->config = array_merge([
            'enabled' => true,
            'websocket_host' => 'localhost',
            'websocket_port' => 8080,
            'notification_types' => [
                'file_upload',
                'file_download',
                'file_share',
                'user_login',
                'user_logout',
                'system_alert',
                'dashboard_update',
                'error_notification'
            ]
        ], $config);
    }
    
    /**
     * Notify file upload
     */
    public function notifyFileUpload($fileData, $userId = null) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $message = [
            'type' => 'file_upload',
            'data' => [
                'file_id' => $fileData['id_file'],
                'filename' => $fileData['original_name'],
                'file_size' => $fileData['file_size'],
                'file_type' => $fileData['mime_type'],
                'category' => $fileData['file_category'],
                'uploaded_by' => $fileData['uploaded_by'] ?? $userId,
                'upload_time' => date('Y-m-d H:i:s'),
                'company_id' => $fileData['company_id'],
                'branch_id' => $fileData['branch_id']
            ]
        ];
        
        return $this->broadcastToUser($userId, $message);
    }
    
    /**
     * Notify file download
     */
    public function notifyFileDownload($fileData, $userId = null) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $message = [
            'type' => 'file_download',
            'data' => [
                'file_id' => $fileData['id_file'],
                'filename' => $fileData['original_name'],
                'downloaded_by' => $userId,
                'download_time' => date('Y-m-d H:i:s'),
                'download_count' => $fileData['download_count'] + 1
            ]
        ];
        
        return $this->broadcastToUser($userId, $message);
    }
    
    /**
     * Notify file share
     */
    public function notifyFileShare($shareData, $userId = null) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $message = [
            'type' => 'file_share',
            'data' => [
                'file_id' => $shareData['file_id'],
                'share_token' => $shareData['share_token'],
                'shared_by' => $userId,
                'share_type' => $shareData['share_type'],
                'expires_at' => $shareData['expires_at'],
                'share_time' => date('Y-m-d H:i:s')
            ]
        ];
        
        return $this->broadcastToChannel('file_shares', $message);
    }
    
    /**
     * Notify user login
     */
    public function notifyUserLogin($userData) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $message = [
            'type' => 'user_login',
            'data' => [
                'user_id' => $userData['user_id'],
                'username' => $userData['username'],
                'login_time' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        ];
        
        return $this->broadcastToChannel('system', $message);
    }
    
    /**
     * Notify user logout
     */
    public function notifyUserLogout($userData) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $message = [
            'type' => 'user_logout',
            'data' => [
                'user_id' => $userData['user_id'],
                'username' => $userData['username'],
                'logout_time' => date('Y-m-d H:i:s'),
                'session_duration' => $this->calculateSessionDuration($userData)
            ]
        ];
        
        return $this->broadcastToChannel('system', $message);
    }
    
    /**
     * Send system alert
     */
    public function sendSystemAlert($message, $level = 'info', $target = 'all') {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $alertMessage = [
            'type' => 'system_alert',
            'data' => [
                'message' => $message,
                'level' => $level, // info, warning, error, success
                'timestamp' => date('Y-m-d H:i:s'),
                'target' => $target
            ]
        ];
        
        switch ($target) {
            case 'admin':
                return $this->broadcastToChannel('admin', $alertMessage);
            case 'company':
                return $this->broadcastToChannel('company_' . $_SESSION['company_id'], $alertMessage);
            case 'branch':
                return $this->broadcastToChannel('branch_' . $_SESSION['branch_id'], $alertMessage);
            default:
                return $this->broadcastToAll($alertMessage);
        }
    }
    
    /**
     * Update dashboard statistics
     */
    public function updateDashboardStats($stats) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $message = [
            'type' => 'dashboard_update',
            'data' => [
                'stats' => $stats,
                'timestamp' => date('Y-m-d H:i:s'),
                'update_type' => 'statistics'
            ]
        ];
        
        return $this->broadcastToChannel('dashboard', $message);
    }
    
    /**
     * Notify error
     */
    public function notifyError($error, $context = []) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $errorMessage = [
            'type' => 'error_notification',
            'data' => [
                'error' => $error,
                'context' => $context,
                'timestamp' => date('Y-m-d H:i:s'),
                'stack_trace' => $this->getStackTrace(),
                'user_id' => $_SESSION['user_id'] ?? null
            ]
        ];
        
        return $this->broadcastToChannel('errors', $errorMessage);
    }
    
    /**
     * Send custom notification
     */
    public function sendNotification($title, $message, $type = 'info', $target = null, $duration = 5000) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $notification = [
            'type' => 'user_notification',
            'data' => [
                'title' => $title,
                'message' => $message,
                'notification_type' => $type,
                'duration' => $duration,
                'timestamp' => date('Y-m-d H:i:s'),
                'icon' => $this->getNotificationIcon($type)
            ]
        ];
        
        if ($target) {
            return $this->broadcastToUser($target, $notification);
        } else {
            return $this->broadcastToAll($notification);
        }
    }
    
    /**
     * Broadcast to all connected clients
     */
    private function broadcastToAll($message) {
        return $this->sendToWebSocket([
            'action' => 'broadcast',
            'channel' => 'all',
            'message' => $message
        ]);
    }
    
    /**
     * Broadcast to specific channel
     */
    private function broadcastToChannel($channel, $message) {
        return $this->sendToWebSocket([
            'action' => 'broadcast',
            'channel' => $channel,
            'message' => $message
        ]);
    }
    
    /**
     * Send to specific user
     */
    private function broadcastToUser($userId, $message) {
        if (!$userId) {
            return false;
        }
        
        return $this->sendToWebSocket([
            'action' => 'broadcast',
            'channel' => 'user_' . $userId,
            'message' => $message
        ]);
    }
    
    /**
     * Send message to WebSocket server
     */
    private function sendToWebSocket($data) {
        try {
            $url = "http://{$this->config['websocket_host']}:{$this->config['websocket_port']}/broadcast";
            
            $options = [
                'http' => [
                    'header' => 'Content-Type: application/json',
                    'method' => 'POST',
                    'content' => json_encode($data),
                    'timeout' => 5
                ]
            ];
            
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            
            if ($result === false) {
                error_log("Failed to send WebSocket message: " . $url);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("WebSocket send error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate session duration
     */
    private function calculateSessionDuration($userData) {
        if (!isset($userData['login_time'])) {
            return 0;
        }
        
        $loginTime = is_numeric($userData['login_time']) ? $userData['login_time'] : strtotime($userData['login_time']);
        $currentTime = time();
        
        return $currentTime - $loginTime;
    }
    
    /**
     * Get stack trace for error reporting
     */
    private function getStackTrace() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $formattedTrace = [];
        
        foreach ($trace as $item) {
            $formattedTrace[] = [
                'file' => $item['file'] ?? 'unknown',
                'line' => $item['line'] ?? 0,
                'function' => $item['function'] ?? 'unknown',
                'class' => $item['class'] ?? 'unknown'
            ];
        }
        
        return $formattedTrace;
    }
    
    /**
     * Get notification icon based on type
     */
    private function getNotificationIcon($type) {
        $icons = [
            'success' => 'fas fa-check-circle',
            'error' => 'fas fa-exclamation-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'info' => 'fas fa-info-circle',
            'notification' => 'fas fa-bell'
        ];
        
        return $icons[$type] ?? $icons['info'];
    }
    
    /**
     * Check if WebSocket is enabled
     */
    public function isEnabled() {
        return $this->config['enabled'];
    }
    
    /**
     * Enable/disable real-time updates
     */
    public function setEnabled($enabled) {
        $this->config['enabled'] = (bool) $enabled;
    }
    
    /**
     * Get configuration
     */
    public function getConfig() {
        return $this->config;
    }
    
    /**
     * Update configuration
     */
    public function updateConfig($config) {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * Test WebSocket connection
     */
    public function testConnection() {
        if (!$this->config['enabled']) {
            return ['status' => 'disabled', 'message' => 'WebSocket is disabled'];
        }
        
        try {
            $url = "http://{$this->config['websocket_host']}:{$this->config['websocket_port']}/status";
            $result = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]));
            
            if ($result === false) {
                return ['status' => 'error', 'message' => 'Cannot connect to WebSocket server'];
            }
            
            $status = json_decode($result, true);
            return $status ?: ['status' => 'unknown', 'message' => 'Invalid response from WebSocket server'];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Connection test failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get WebSocket server statistics
     */
    public function getServerStats() {
        if (!$this->config['enabled']) {
            return ['status' => 'disabled'];
        }
        
        try {
            $url = "http://{$this->config['websocket_host']}:{$this->config['websocket_port']}/stats";
            $result = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]));
            
            if ($result === false) {
                return ['status' => 'error', 'message' => 'Cannot get WebSocket server stats'];
            }
            
            $stats = json_decode($result, true);
            return $stats ?: ['status' => 'error', 'message' => 'Invalid stats response'];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to get stats: ' . $e->getMessage()];
        }
    }
}
