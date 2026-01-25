<?php

/**
 * WebSocket Model
 * 
 * Handles WebSocket connections, channels, messages, and real-time collaboration features
 */

class WebSocket {
    private $db;
    private $connections_table = 'websocket_connections';
    private $channels_table = 'websocket_channels';
    private $subscriptions_table = 'websocket_channel_subscriptions';
    private $messages_table = 'websocket_messages';
    private $delivery_table = 'websocket_message_delivery';
    private $events_table = 'websocket_events';
    private $settings_table = 'websocket_settings';
    private $statistics_table = 'websocket_statistics';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Connection Management
     */

    /**
     * Register a new WebSocket connection
     */
    public function registerConnection($connectionId, $userId, $sessionId, $ipAddress, $userAgent, $connectionType = 'web') {
        $data = [
            'connection_id' => $connectionId,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'connection_type' => $connectionType,
            'company_id' => $_SESSION['company_id'] ?? null,
            'branch_id' => $_SESSION['branch_id'] ?? null
        ];

        return $this->db->insert($this->connections_table, $data);
    }

    /**
     * Update connection activity
     */
    public function updateConnectionActivity($connectionId) {
        $data = [
            'last_activity' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];

        $this->db->update($this->connections_table, $data, "connection_id = ?", [$connectionId]);
    }

    /**
     * Disconnect a WebSocket connection
     */
    public function disconnectConnection($connectionId) {
        $data = [
            'status' => 'disconnected'
        ];

        $this->db->update($this->connections_table, $data, "connection_id = ?", [$connectionId]);

        // Deactivate subscriptions
        $this->db->update($this->subscriptions_table, 
            ['is_active' => false], 
            "connection_id = ?", 
            [$connectionId]
        );
    }

    /**
     * Get active connections for user
     */
    public function getUserConnections($userId) {
        $sql = "SELECT * FROM {$this->connections_table} 
                WHERE user_id = ? AND status = 'active' 
                ORDER BY last_activity DESC";
        
        return $this->db->query($sql, [$userId]);
    }

    /**
     * Get all active connections
     */
    public function getActiveConnections($filters = []) {
        $where = ["c.status = 'active'"];
        $params = [];

        if (!empty($filters['company_id'])) {
            $where[] = "c.company_id = ?";
            $params[] = $filters['company_id'];
        }

        if (!empty($filters['branch_id'])) {
            $where[] = "c.branch_id = ?";
            $params[] = $filters['branch_id'];
        }

        $sql = "SELECT c.*, m.member_name, m.email, co.company_name, b.branch_name
                FROM {$this->connections_table} c
                LEFT JOIN members m ON c.user_id = m.id_member
                LEFT JOIN companies co ON c.company_id = co.id_company
                LEFT JOIN branches b ON c.branch_id = b.id_branch
                WHERE " . implode(' AND ', $where) . "
                ORDER BY c.last_activity DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Channel Management
     */

    /**
     * Create a new WebSocket channel
     */
    public function createChannel($channelName, $channelType, $description = '', $ownerId = null, $companyId = null, $branchId = null) {
        $data = [
            'channel_name' => $channelName,
            'channel_type' => $channelType,
            'description' => $description,
            'owner_id' => $ownerId,
            'company_id' => $companyId,
            'branch_id' => $branchId
        ];

        return $this->db->insert($this->channels_table, $data);
    }

    /**
     * Get channel by name
     */
    public function getChannelByName($channelName) {
        $sql = "SELECT * FROM {$this->channels_table} WHERE channel_name = ? AND is_active = TRUE";
        return $this->db->query($sql, [$channelName])[0] ?? null;
    }

    /**
     * Get channels for user
     */
    public function getUserChannels($userId, $channelType = null) {
        $where = ["ch.is_active = TRUE"];
        $params = [];

        if ($channelType) {
            $where[] = "ch.channel_type = ?";
            $params[] = $channelType;
        }

        $sql = "SELECT DISTINCT ch.*, 
                       COUNT(cs.id_subscription) as subscriber_count
                FROM {$this->channels_table} ch
                LEFT JOIN {$this->subscriptions_table} cs ON ch.id_channel = cs.id_channel AND cs.is_active = TRUE
                WHERE (" . implode(' OR ', $where) . ")
                AND (
                    ch.channel_type = 'public' OR
                    ch.owner_id = ? OR
                    cs.user_id = ?
                )
                GROUP BY ch.id_channel
                ORDER BY ch.channel_name";

        array_push($params, $userId, $userId);

        return $this->db->query($sql, $params);
    }

    /**
     * Subscribe user to channel
     */
    public function subscribeToChannel($connectionId, $channelId, $userId) {
        // Check if already subscribed
        $existing = $this->db->query(
            "SELECT id_subscription FROM {$this->subscriptions_table} 
             WHERE connection_id = ? AND channel_id = ? AND user_id = ?",
            [$connectionId, $channelId, $userId]
        );

        if ($existing) {
            // Reactivate existing subscription
            $this->db->update($this->subscriptions_table,
                ['is_active' => true, 'subscribed_at' => date('Y-m-d H:i:s')],
                "connection_id = ? AND channel_id = ? AND user_id = ?",
                [$connectionId, $channelId, $userId]
            );
        } else {
            // Create new subscription
            $data = [
                'connection_id' => $connectionId,
                'channel_id' => $channelId,
                'user_id' => $userId
            ];

            $this->db->insert($this->subscriptions_table, $data);
        }
    }

    /**
     * Unsubscribe from channel
     */
    public function unsubscribeFromChannel($connectionId, $channelId) {
        $this->db->update($this->subscriptions_table,
            ['is_active' => false],
            "connection_id = ? AND channel_id = ?",
            [$connectionId, $channelId]
        );
    }

    /**
     * Message Management
     */

    /**
     * Send message to channel
     */
    public function sendMessageToChannel($channelId, $senderId, $messageType, $messageContent, $messageData = null, $priority = 'normal') {
        $data = [
            'channel_id' => $channelId,
            'sender_id' => $senderId,
            'message_type' => $messageType,
            'message_content' => $messageContent,
            'message_data' => $messageData ? json_encode($messageData) : null,
            'priority' => $priority
        ];

        $messageId = $this->db->insert($this->messages_table, $data);

        if ($messageId) {
            // Create delivery records for all active subscribers
            $this->createDeliveryRecords($messageId, $channelId);
        }

        return $messageId;
    }

    /**
     * Send private message
     */
    public function sendPrivateMessage($senderId, $recipientId, $messageContent, $messageData = null) {
        // Create or get private channel
        $channelName = 'private_' . min($senderId, $recipientId) . '_' . max($senderId, $recipientId);
        
        $channel = $this->getChannelByName($channelName);
        if (!$channel) {
            $channelId = $this->createChannel($channelName, 'private', 'Private message channel', $senderId);
        } else {
            $channelId = $channel['id_channel'];
        }

        $data = [
            'channel_id' => $channelId,
            'sender_id' => $senderId,
            'message_type' => 'text',
            'message_content' => $messageContent,
            'message_data' => $messageData ? json_encode($messageData) : null,
            'recipient_id' => $recipientId
        ];

        $messageId = $this->db->insert($this->messages_table, $data);

        if ($messageId) {
            // Create delivery records for recipient's active connections
            $this->createPrivateDeliveryRecords($messageId, $recipientId);
        }

        return $messageId;
    }

    /**
     * Create delivery records for channel message
     */
    private function createDeliveryRecords($messageId, $channelId) {
        $sql = "INSERT INTO {$this->delivery_table} (message_id, connection_id, user_id)
                SELECT ?, cs.connection_id, cs.user_id
                FROM {$this->subscriptions_table} cs
                INNER JOIN {$this->connections_table} c ON cs.connection_id = c.connection_id
                WHERE cs.channel_id = ? AND cs.is_active = TRUE AND c.status = 'active'";

        $this->db->query($sql, [$messageId, $channelId]);
    }

    /**
     * Create delivery records for private message
     */
    private function createPrivateDeliveryRecords($messageId, $recipientId) {
        $sql = "INSERT INTO {$this->delivery_table} (message_id, connection_id, user_id)
                SELECT ?, connection_id, user_id
                FROM {$this->connections_table}
                WHERE user_id = ? AND status = 'active'";

        $this->db->query($sql, [$messageId, $recipientId]);
    }

    /**
     * Get messages for channel
     */
    public function getChannelMessages($channelId, $limit = 50, $offset = 0, $beforeMessageId = null) {
        $where = ["channel_id = ?"];
        $params = [$channelId];

        if ($beforeMessageId) {
            $where[] = "id_message < ?";
            $params[] = $beforeMessageId;
        }

        $sql = "SELECT wm.*, m.member_name as sender_name, m.email as sender_email
                FROM {$this->messages_table} wm
                LEFT JOIN members m ON wm.sender_id = m.id_member
                WHERE " . implode(' AND ', $where) . "
                ORDER BY wm.created_at DESC
                LIMIT ? OFFSET ?";

        array_push($params, $limit, $offset);

        return $this->db->query($sql, $params);
    }

    /**
     * Get undelivered messages for connection
     */
    public function getUndeliveredMessages($connectionId) {
        $sql = "SELECT wmd.*, wm.message_content, wm.message_type, wm.message_data, 
                       wm.priority, wm.created_at, m.member_name as sender_name
                FROM {$this->delivery_table} wmd
                INNER JOIN {$this->messages_table} wm ON wmd.message_id = wm.id_message
                LEFT JOIN members m ON wm.sender_id = m.id_member
                WHERE wmd.connection_id = ? AND wmd.delivery_status = 'pending'
                ORDER BY wm.priority DESC, wm.created_at ASC";

        return $this->db->query($sql, [$connectionId]);
    }

    /**
     * Mark message as delivered
     */
    public function markMessageDelivered($messageId, $connectionId) {
        $data = [
            'delivery_status' => 'delivered',
            'delivered_at' => date('Y-m-d H:i:s')
        ];

        $this->db->update($this->delivery_table, $data, 
            "message_id = ? AND connection_id = ?", 
            [$messageId, $connectionId]
        );

        // Update message delivery count
        $this->db->query(
            "UPDATE {$this->messages_table} 
             SET delivery_count = delivery_count + 1, 
                 is_delivered = CASE 
                     WHEN delivery_count + 1 >= (SELECT COUNT(*) FROM {$this->delivery_table} WHERE message_id = ?) 
                     THEN TRUE 
                     ELSE is_delivered 
                 END
             WHERE id_message = ?",
            [$messageId, $messageId]
        );
    }

    /**
     * Event Management
     */

    /**
     * Log WebSocket event
     */
    public function logEvent($eventType, $eventName, $eventData, $sourceUserId = null, $targetUserId = null) {
        $data = [
            'event_type' => $eventType,
            'event_name' => $eventName,
            'event_data' => json_encode($eventData),
            'source_user_id' => $sourceUserId,
            'target_user_id' => $targetUserId,
            'company_id' => $_SESSION['company_id'] ?? null,
            'branch_id' => $_SESSION['branch_id'] ?? null
        ];

        return $this->db->insert($this->events_table, $data);
    }

    /**
     * Get recent events
     */
    public function getRecentEvents($limit = 100, $filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['event_type'])) {
            $where[] = "event_type = ?";
            $params[] = $filters['event_type'];
        }

        if (!empty($filters['company_id'])) {
            $where[] = "company_id = ?";
            $params[] = $filters['company_id'];
        }

        $sql = "SELECT we.*, m1.member_name as source_user_name, m2.member_name as target_user_name
                FROM {$this->events_table} we
                LEFT JOIN members m1 ON we.source_user_id = m1.id_member
                LEFT JOIN members m2 ON we.target_user_id = m2.id_member
                " . (!empty($where) ? "WHERE " . implode(' AND ', $where) : "") . "
                ORDER BY we.created_at DESC
                LIMIT ?";

        $params[] = $limit;

        return $this->db->query($sql, $params);
    }

    /**
     * Settings Management
     */

    /**
     * Get WebSocket setting
     */
    public function getSetting($key, $default = null) {
        $sql = "SELECT setting_value FROM {$this->settings_table} WHERE setting_key = ?";
        $result = $this->db->query($sql, [$key]);
        
        if ($result) {
            $value = $result[0]['setting_value'];
            $type = $result[0]['setting_type'] ?? 'string';
            
            switch ($type) {
                case 'boolean':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                case 'number':
                    return is_numeric($value) ? (int)$value : $default;
                case 'json':
                    return json_decode($value, true);
                default:
                    return $value;
            }
        }
        
        return $default;
    }

    /**
     * Update WebSocket setting
     */
    public function updateSetting($key, $value, $type = 'string') {
        $data = [
            'setting_value' => is_array($value) ? json_encode($value) : $value,
            'setting_type' => $type,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $existing = $this->db->query("SELECT id_setting FROM {$this->settings_table} WHERE setting_key = ?", [$key]);
        
        if ($existing) {
            $this->db->update($this->settings_table, $data, "setting_key = ?", [$key]);
        } else {
            $data['setting_key'] = $key;
            $this->db->insert($this->settings_table, $data);
        }
    }

    /**
     * Statistics Management
     */

    /**
     * Get WebSocket statistics
     */
    public function getStatistics($dateRange = '7days') {
        $where = [];
        $params = [];

        switch ($dateRange) {
            case 'today':
                $where[] = "stat_date = CURDATE()";
                break;
            case '7days':
                $where[] = "stat_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $where[] = "stat_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
        }

        $sql = "SELECT * FROM {$this->statistics_table}
                " . (!empty($where) ? "WHERE " . implode(' AND ', $where) : "") . "
                ORDER BY stat_date DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Get real-time statistics
     */
    public function getRealTimeStatistics() {
        $stats = [];

        // Active connections
        $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->connections_table} WHERE status = 'active'");
        $stats['active_connections'] = $result[0]['count'];

        // Total messages today
        $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->messages_table} WHERE DATE(created_at) = CURDATE()");
        $stats['messages_today'] = $result[0]['count'];

        // Delivered messages today
        $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->delivery_table} WHERE DATE(delivered_at) = CURDATE() AND delivery_status = 'delivered'");
        $stats['delivered_today'] = $result[0]['count'];

        // Active channels
        $result = $this->db->query("SELECT COUNT(DISTINCT channel_id) as count FROM {$this->subscriptions_table} WHERE is_active = TRUE");
        $stats['active_channels'] = $result[0]['count'];

        // Online users
        $result = $this->db->query("SELECT COUNT(DISTINCT user_id) as count FROM {$this->connections_table} WHERE status = 'active'");
        $stats['online_users'] = $result[0]['count'];

        return $stats;
    }

    /**
     * Cleanup Operations
     */

    /**
     * Cleanup old messages
     */
    public function cleanupOldMessages($days = 30) {
        $sql = "DELETE FROM {$this->messages_table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                AND id_message NOT IN (
                    SELECT DISTINCT message_id FROM {$this->delivery_table} 
                    WHERE delivery_status = 'pending'
                )";
        
        return $this->db->query($sql, [$days]);
    }

    /**
     * Cleanup inactive connections
     */
    public function cleanupInactiveConnections() {
        $timeout = $this->getSetting('connection_timeout', 300);
        
        $sql = "UPDATE {$this->connections_table} 
                SET status = 'disconnected'
                WHERE status = 'active' 
                AND last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $result = $this->db->query($sql, [$timeout]);

        // Deactivate subscriptions for disconnected connections
        if ($result) {
            $this->db->query(
                "UPDATE {$this->subscriptions_table} cs
                 INNER JOIN {$this->connections_table} c ON cs.connection_id = c.connection_id
                 SET cs.is_active = FALSE
                 WHERE c.status = 'disconnected'"
            );
        }

        return $result;
    }

    /**
     * Presence Management
     */

    /**
     * Update user presence
     */
    public function updateUserPresence($userId, $presence = 'online', $status = '') {
        $this->logEvent('presence', 'user_presence_update', [
            'presence' => $presence,
            'status' => $status
        ], $userId);

        // Broadcast presence update to presence channel
        $this->sendMessageToChannel(
            $this->getChannelByName('user-presence')['id_channel'],
            $userId,
            'json',
            json_encode([
                'type' => 'presence_update',
                'user_id' => $userId,
                'presence' => $presence,
                'status' => $status,
                'timestamp' => time()
            ])
        );
    }

    /**
     * Get online users
     */
    public function getOnlineUsers($filters = []) {
        $where = ["c.status = 'active'"];
        $params = [];

        if (!empty($filters['company_id'])) {
            $where[] = "c.company_id = ?";
            $params[] = $filters['company_id'];
        }

        if (!empty($filters['branch_id'])) {
            $where[] = "c.branch_id = ?";
            $params[] = $filters['branch_id'];
        }

        $sql = "SELECT DISTINCT c.user_id, m.member_name, m.email, m.position,
                       c.company_id, co.company_name,
                       c.branch_id, b.branch_name,
                       c.last_activity
                FROM {$this->connections_table} c
                INNER JOIN members m ON c.user_id = m.id_member
                LEFT JOIN companies co ON c.company_id = co.id_company
                LEFT JOIN branches b ON c.branch_id = b.id_branch
                WHERE " . implode(' AND ', $where) . "
                ORDER BY m.member_name";

        return $this->db->query($sql, $params);
    }

    /**
     * File Sharing
     */

    /**
     * Send file notification
     */
    public function sendFileNotification($fileId, $fileName, $fileSize, $userId, $channelType = 'file-uploads') {
        $channel = $this->getChannelByName($channelType);
        if (!$channel) {
            return false;
        }

        $messageData = [
            'type' => 'file_upload',
            'file_id' => $fileId,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'uploaded_by' => $userId,
            'timestamp' => time()
        ];

        return $this->sendMessageToChannel(
            $channel['id_channel'],
            $userId,
            'json',
            json_encode($messageData),
            'normal'
        );
    }

    /**
     * System Alerts
     */

    /**
     * Send system alert
     */
    public function sendSystemAlert($title, $message, $level = 'info', $targetUsers = null) {
        $channel = $this->getChannelByName('system-alerts');
        if (!$channel) {
            return false;
        }

        $messageData = [
            'type' => 'system_alert',
            'title' => $title,
            'message' => $message,
            'level' => $level,
            'target_users' => $targetUsers,
            'timestamp' => time()
        ];

        return $this->sendMessageToChannel(
            $channel['id_channel'],
            $_SESSION['user_id'] ?? 1,
            'json',
            json_encode($messageData),
            $level === 'critical' ? 'urgent' : 'high'
        );
    }
}
