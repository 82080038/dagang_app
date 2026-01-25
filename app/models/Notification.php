<?php

/**
 * Notification Model
 * 
 * Handles notification operations including creation, delivery,
 * scheduling, and user preferences
 */

class Notification extends Model {
    
    protected $table = 'notifications';
    protected $primaryKey = 'id_notification';
    
    protected $fillable = [
        'user_id',
        'template_id',
        'notification_type',
        'title',
        'message',
        'data',
        'priority',
        'status',
        'scheduled_at',
        'sent_at',
        'read_at',
        'expires_at',
        'created_by',
        'created_at',
        'updated_at'
    ];
    
    // Notification types
    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';
    const TYPE_PUSH = 'push';
    const TYPE_IN_APP = 'in_app';
    
    // Notification priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    
    // Notification statuses
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_READ = 'read';
    
    /**
     * Create notification from template
     */
    public function createFromTemplate($templateName, $userId, $data = [], $options = []) {
        $template = $this->getTemplate($templateName);
        
        if (!$template) {
            $this->setError('Template not found: ' . $templateName);
            return false;
        }
        
        // Process template variables
        $title = $this->processTemplate($template['subject'] ?? 'Notification', $data);
        $message = $this->processTemplate($template['message_body'], $data);
        $htmlBody = $template['html_body'] ? $this->processTemplate($template['html_body'], $data) : null;
        
        // Create notification
        $notificationData = [
            'user_id' => $userId,
            'template_id' => $template['id_template'],
            'notification_type' => $template['template_type'],
            'title' => $title,
            'message' => $message,
            'data' => json_encode($data),
            'priority' => $options['priority'] ?? self::PRIORITY_MEDIUM,
            'status' => self::STATUS_PENDING,
            'scheduled_at' => $options['scheduled_at'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $notificationId = $this->create($notificationData);
        
        if (!$notificationId) {
            $this->setError('Failed to create notification');
            return false;
        }
        
        // Queue for delivery if not in-app
        if ($template['template_type'] !== self::TYPE_IN_APP) {
            $this->queueNotification($notificationId, $template['template_type'], $userId, $data);
        }
        
        return $notificationId;
    }
    
    /**
     * Process template with variables
     */
    private function processTemplate($template, $data) {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
    
    /**
     * Get notification template
     */
    private function getTemplate($templateName) {
        $sql = "SELECT * FROM notification_templates 
                WHERE template_name = ? AND is_active = 1";
        
        return $this->db->query($sql, [$templateName])->fetch();
    }
    
    /**
     * Queue notification for delivery
     */
    private function queueNotification($notificationId, $type, $userId, $data) {
        $user = $this->getUserById($userId);
        
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
        
        $notification = $this->getById($notificationId);
        
        $queueData = [
            'notification_id' => $notificationId,
            'queue_type' => $type,
            'recipient' => $this->getRecipient($type, $user),
            'subject' => $notification['title'],
            'message' => $notification['message'],
            'data' => $notification['data'],
            'priority' => $notification['priority'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('notification_queue', $queueData);
    }
    
    /**
     * Get recipient based on notification type
     */
    private function getRecipient($type, $user) {
        switch ($type) {
            case self::TYPE_EMAIL:
                return $user['email'];
            case self::TYPE_SMS:
                return $user['phone'];
            case self::TYPE_PUSH:
                return $user['device_token'] ?? '';
            default:
                return '';
        }
    }
    
    /**
     * Get user by ID
     */
    private function getUserById($userId) {
        $sql = "SELECT * FROM users WHERE id_user = ?";
        return $this->db->query($sql, [$userId])->fetch();
    }
    
    /**
     * Send notification immediately
     */
    public function sendNotification($userId, $title, $message, $type = self::TYPE_IN_APP, $data = []) {
        $notificationData = [
            'user_id' => $userId,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => json_encode($data),
            'priority' => self::PRIORITY_MEDIUM,
            'status' => self::STATUS_PENDING,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $notificationId = $this->create($notificationData);
        
        if (!$notificationId) {
            return false;
        }
        
        // Queue for delivery if not in-app
        if ($type !== self::TYPE_IN_APP) {
            $this->queueNotification($notificationId, $type, $userId, $data);
        }
        
        return $notificationId;
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = ['user_id = ?'];
        $params = [$userId];
        
        // Build WHERE clause
        if (!empty($filters['type'])) {
            $where[] = "notification_type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $where[] = "priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['unread_only'])) {
            $where[] = "read_at IS NULL";
        }
        
        $whereClause = implode(" AND ", $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'];
        
        // Get notifications
        $sql = "SELECT * FROM {$this->table}
                WHERE {$whereClause}
                ORDER BY created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $notifications = $this->db->query($sql, $params)->fetchAll();
        
        return [
            'notifications' => $notifications,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'items_per_page' => $limit
            ]
        ];
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null) {
        $updateData = [
            'status' => self::STATUS_READ,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $where = ['id_notification = ?'];
        $params = [$notificationId];
        
        if ($userId) {
            $where[] = "user_id = ?";
            $params[] = $userId;
        }
        
        return $this->db->update($this->table, $updateData, $where, $params);
    }
    
    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId) {
        $updateData = [
            'status' => self::STATUS_READ,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $where = ['user_id = ?', 'read_at IS NULL'];
        $params = [$userId];
        
        return $this->db->update($this->table, $updateData, $where, $params);
    }
    
    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE user_id = ? AND read_at IS NULL";
        
        $result = $this->db->query($sql, [$userId])->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Create notification for multiple users (broadcast)
     */
    public function broadcast($title, $message, $userIds = [], $type = self::TYPE_IN_APP, $data = []) {
        if (empty($userIds)) {
            // Broadcast to all users
            $sql = "SELECT id_user FROM users WHERE is_active = 1";
            $users = $this->db->query($sql)->fetchAll();
            $userIds = array_column($users, 'id_user');
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($userIds as $userId) {
            $notificationId = $this->sendNotification($userId, $title, $message, $type, $data);
            if ($notificationId) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_count' => count($userIds)
        ];
    }
    
    /**
     * Get notification statistics
     */
    public function getStatistics($filters = []) {
        $where = ['1=1'];
        $params = [];
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = implode(" AND ", $where);
        
        // Get overview statistics
        $sql = "SELECT 
                    COUNT(*) as total_notifications,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
                    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                    SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread_count
                FROM {$this->table}
                WHERE {$whereClause}";
        
        $stats = $this->db->query($sql, $params)->fetch();
        
        // Get notifications by type
        $typeSql = "SELECT notification_type, COUNT(*) as count
                   FROM {$this->table}
                   WHERE {$whereClause}
                   GROUP BY notification_type
                   ORDER BY count DESC";
        
        $byType = $this->db->query($typeSql, $params)->fetchAll();
        
        // Get notifications by priority
        $prioritySql = "SELECT priority, COUNT(*) as count
                       FROM {$this->table}
                       WHERE {$whereClause}
                       GROUP BY priority
                       ORDER BY FIELD(priority, 'urgent', 'high', 'medium', 'low')";
        
        $byPriority = $this->db->query($prioritySql, $params)->fetchAll();
        
        return [
            'overview' => $stats,
            'by_type' => $byType,
            'by_priority' => $byPriority
        ];
    }
    
    /**
     * Delete notification (soft delete)
     */
    public function deleteNotification($notificationId, $userId = null) {
        $where = ['id_notification = ?'];
        $params = [$notificationId];
        
        if ($userId) {
            $where[] = "user_id = ?";
            $params[] = $userId;
        }
        
        return $this->db->delete($this->table, $where, $params);
    }
    
    /**
     * Get notification templates
     */
    public function getTemplates($type = null) {
        $sql = "SELECT * FROM notification_templates WHERE is_active = 1";
        $params = [];
        
        if ($type) {
            $sql .= " AND template_type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY template_name";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Create notification template
     */
    public function createTemplate($data) {
        $templateData = [
            'template_name' => $data['template_name'],
            'template_type' => $data['template_type'],
            'subject' => $data['subject'] ?? null,
            'message_body' => $data['message_body'],
            'html_body' => $data['html_body'] ?? null,
            'variables' => json_encode($data['variables'] ?? []),
            'is_active' => $data['is_active'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('notification_templates', $templateData);
    }
    
    /**
     * Update notification template
     */
    public function updateTemplate($templateId, $data) {
        $updateData = [];
        
        $allowedFields = ['template_name', 'template_type', 'subject', 'message_body', 'html_body', 'variables', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (isset($updateData['variables'])) {
            $updateData['variables'] = json_encode($updateData['variables']);
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        $where = ['id_template = ?'];
        $params = [$templateId];
        
        return $this->db->update('notification_templates', $updateData, $where, $params);
    }
    
    /**
     * Get notification preferences for user
     */
    public function getUserPreferences($userId) {
        $sql = "SELECT * FROM notification_preferences WHERE user_id = ?";
        return $this->db->query($sql, [$userId])->fetchAll();
    }
    
    /**
     * Update user notification preferences
     */
    public function updatePreferences($userId, $preferences) {
        $success = true;
        
        foreach ($preferences as $type => $data) {
            $prefData = [
                'user_id' => $userId,
                'notification_type' => $type,
                'is_enabled' => $data['is_enabled'] ?? 1,
                'frequency' => $data['frequency'] ?? 'immediate',
                'categories' => $data['categories'] ?? null,
                'quiet_hours_start' => $data['quiet_hours_start'] ?? null,
                'quiet_hours_end' => $data['quiet_hours_end'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Insert or update
            $existing = $this->db->query(
                "SELECT id_preference FROM notification_preferences WHERE user_id = ? AND notification_type = ?",
                [$userId, $type]
            )->fetch();
            
            if ($existing) {
                $where = ['user_id = ?', 'notification_type = ?'];
                $params = [$userId, $type];
                $success = $this->db->update('notification_preferences', $prefData, $where, $params) && $success;
            } else {
                $prefData['created_at'] = date('Y-m-d H:i:s');
                $success = $this->db->insert('notification_preferences', $prefData) && $success;
            }
        }
        
        return $success;
    }
    
    /**
     * Check if user should receive notification based on preferences
     */
    public function shouldSendNotification($userId, $type, $category = null) {
        $preferences = $this->getUserPreferences($userId);
        
        foreach ($preferences as $pref) {
            if ($pref['notification_type'] === $type) {
                if (!$pref['is_enabled']) {
                    return false;
                }
                
                if ($pref['categories'] && $category) {
                    $allowedCategories = explode(',', $pref['categories']);
                    if (!in_array($category, $allowedCategories)) {
                        return false;
                    }
                }
                
                // Check quiet hours
                if ($pref['quiet_hours_start'] && $pref['quiet_hours_end']) {
                    $currentTime = date('H:i:s');
                    if ($currentTime >= $pref['quiet_hours_start'] && $currentTime <= $pref['quiet_hours_end']) {
                        return false;
                    }
                }
                
                return true;
            }
        }
        
        // Default to true if no preferences found
        return true;
    }
    
    /**
     * Get notification queue items
     */
    public function getQueueItems($filters = [], $limit = 100) {
        $where = ['status = ?'];
        $params = ['pending'];
        
        // Add priority filter
        if (!empty($filters['priority'])) {
            $where[] = "priority = ?";
            $params[] = $filters['priority'];
        }
        
        // Add type filter
        if (!empty($filters['type'])) {
            $where[] = "queue_type = ?";
            $params[] = $filters['type'];
        }
        
        // Add scheduled filter
        $where[] = "(scheduled_at IS NULL OR scheduled_at <= NOW())";
        
        $whereClause = implode(" AND ", $where);
        
        $sql = "SELECT * FROM notification_queue
                WHERE {$whereClause}
                ORDER BY FIELD(priority, 'urgent', 'high', 'medium', 'low'), created_at
                LIMIT {$limit}";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Update queue item status
     */
    public function updateQueueStatus($queueId, $status, $errorMessage = null) {
        $updateData = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($status === 'processing') {
            $updateData['processed_at'] = date('Y-m-d H:i:s');
            $updateData['attempts'] = 'attempts + 1';
        } elseif ($status === 'sent') {
            $updateData['sent_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'failed') {
            $updateData['error_message'] = $errorMessage;
        }
        
        $where = ['id_queue = ?'];
        $params = [$queueId];
        
        return $this->db->update('notification_queue', $updateData, $where, $params);
    }
    
    /**
     * Get notification settings
     */
    public function getSettings() {
        $sql = "SELECT * FROM notification_settings WHERE is_active = 1";
        $settings = $this->db->query($sql)->fetchAll();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->parseSettingValue($setting['setting_value'], $setting['setting_type']);
        }
        
        return $result;
    }
    
    /**
     * Parse setting value based on type
     */
    private function parseSettingValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    /**
     * Update notification setting
     */
    public function updateSetting($key, $value) {
        $settingData = [
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $where = ['setting_key = ?'];
        $params = [$key];
        
        return $this->db->update('notification_settings', $settingData, $where, $params);
    }
}
