<?php

/**
 * Notification Controller
 * 
 * Handles notification operations including creation, delivery,
 * management, and user preferences
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Notification.php';

class NotificationController extends Controller {
    
    private $notificationModel;
    
    public function __construct() {
        parent::__construct();
        $this->notificationModel = new Notification();
    }
    
    /**
     * Main notifications page
     */
    public function index() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->redirect('index.php?page=auth&action=login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get filters from request
        $filters = [
            'type' => $_GET['type'] ?? null,
            'status' => $_GET['status'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'unread_only' => isset($_GET['unread_only'])
        ];
        
        // Get pagination
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        
        // Get notifications
        $result = $this->notificationModel->getUserNotifications($userId, $filters, $page, $limit);
        
        // Get unread count
        $unreadCount = $this->notificationModel->getUnreadCount($userId);
        
        // Get user preferences
        $preferences = $this->notificationModel->getUserPreferences($userId);
        
        // Get notification statistics
        $statistics = $this->notificationModel->getStatistics(['user_id' => $userId]);
        
        // Load view
        include __DIR__ . '/../views/notifications/index.php';
    }
    
    /**
     * Get notifications (AJAX endpoint)
     */
    public function getNotifications() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get filters
        $filters = [
            'type' => $_GET['type'] ?? null,
            'status' => $_GET['status'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'unread_only' => isset($_GET['unread_only'])
        ];
        
        // Get pagination
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = intval($_GET['limit'] ?? 20);
        
        // Get notifications
        $result = $this->notificationModel->getUserNotifications($userId, $filters, $page, $limit);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $result
        ]);
    }
    
    /**
     * Get unread count (AJAX endpoint)
     */
    public function getUnreadCount() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $unreadCount = $this->notificationModel->getUnreadCount($userId);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'unread_count' => $unreadCount
            ]
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        // Check CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $notificationId = $_POST['notification_id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$notificationId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Notification ID required'], 400);
            return;
        }
        
        $result = $this->notificationModel->markAsRead($notificationId, $userId);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Notification marked as read',
                'data' => [
                    'unread_count' => $this->notificationModel->getUnreadCount($userId)
                ]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to mark notification as read'
            ], 400);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        // Check CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $result = $this->notificationModel->markAllAsRead($userId);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'All notifications marked as read',
                'data' => [
                    'unread_count' => 0
                ]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to mark notifications as read'
            ], 400);
        }
    }
    
    /**
     * Delete notification
     */
    public function delete() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        // Check CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $notificationId = $_POST['notification_id'] ?? null;
        $userId = $_SESSION['user_id'];
        
        if (!$notificationId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Notification ID required'], 400);
            return;
        }
        
        $result = $this->notificationModel->deleteNotification($notificationId, $userId);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Notification deleted',
                'data' => [
                    'unread_count' => $this->notificationModel->getUnreadCount($userId)
                ]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to delete notification'
            ], 400);
        }
    }
    
    /**
     * Send notification (admin only)
     */
    public function send() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        // Check if user can send notifications
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole > ROLE_ADMIN) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }
        
        // Check CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $userId = $_POST['user_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $type = $_POST['type'] ?? 'in_app';
        $data = $_POST['data'] ?? [];
        
        if (!$title || !$message) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Title and message required'], 400);
            return;
        }
        
        // Parse data if it's a string
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }
        
        if ($userId) {
            // Send to specific user
            $notificationId = $this->notificationModel->sendNotification($userId, $title, $message, $type, $data);
            
            if ($notificationId) {
                $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Notification sent successfully'
                ]);
            } else {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Failed to send notification'
                ], 400);
            }
        } else {
            // Broadcast to all users
            $result = $this->notificationModel->broadcast($title, $message, [], $type, $data);
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Notification broadcast completed',
                'data' => $result
            ]);
        }
    }
    
    /**
     * Get notification templates (admin only)
     */
    public function getTemplates() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole > ROLE_ADMIN) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }
        
        $type = $_GET['type'] ?? null;
        $templates = $this->notificationModel->getTemplates($type);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $templates
        ]);
    }
    
    /**
     * Create notification template (admin only)
     */
    public function createTemplate() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole > ROLE_ADMIN) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }
        
        // Check CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $data = [
            'template_name' => $_POST['template_name'] ?? '',
            'template_type' => $_POST['template_type'] ?? '',
            'subject' => $_POST['subject'] ?? null,
            'message_body' => $_POST['message_body'] ?? '',
            'html_body' => $_POST['html_body'] ?? null,
            'variables' => $_POST['variables'] ?? [],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validate required fields
        if (!$data['template_name'] || !$data['template_type'] || !$data['message_body']) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Template name, type, and message body required'], 400);
            return;
        }
        
        // Parse variables if it's a string
        if (is_string($data['variables'])) {
            $data['variables'] = json_decode($data['variables'], true) ?? [];
        }
        
        $templateId = $this->notificationModel->createTemplate($data);
        
        if ($templateId) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Template created successfully',
                'data' => [
                    'template_id' => $templateId
                ]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to create template'
            ], 400);
        }
    }
    
    /**
     * Update notification template (admin only)
     */
    public function updateTemplate() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole > ROLE_ADMIN) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }
        
        // Check CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $templateId = $_POST['template_id'] ?? null;
        if (!$templateId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Template ID required'], 400);
            return;
        }
        
        $data = [];
        
        $allowedFields = ['template_name', 'template_type', 'subject', 'message_body', 'html_body', 'variables', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
            }
        }
        
        // Parse variables if it's a string
        if (isset($data['variables']) && is_string($data['variables'])) {
            $data['variables'] = json_decode($data['variables'], true) ?? [];
        }
        
        $result = $this->notificationModel->updateTemplate($templateId, $data);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Template updated successfully'
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to update template'
            ], 400);
        }
    }
    
    /**
     * Get user preferences
     */
    public function getPreferences() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $preferences = $this->notificationModel->getUserPreferences($userId);
        
        // Convert to associative array
        $prefArray = [];
        foreach ($preferences as $pref) {
            $prefArray[$pref['notification_type']] = $pref;
        }
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $prefArray
        ]);
    }
    
    /**
     * Update user preferences
     */
    public function updatePreferences() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        // Check CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $preferences = $_POST['preferences'] ?? [];
        
        if (is_string($preferences)) {
            $preferences = json_decode($preferences, true) ?? [];
        }
        
        $result = $this->notificationModel->updatePreferences($userId, $preferences);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Preferences updated successfully'
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to update preferences'
            ], 400);
        }
    }
    
    /**
     * Get notification statistics
     */
    public function getStatistics() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? null;
        
        $filters = [];
        
        // Regular users can only see their own statistics
        if ($userRole > ROLE_ADMIN) {
            $filters['user_id'] = $userId;
        }
        
        // Apply date filters
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        
        $statistics = $this->notificationModel->getStatistics($filters);
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $statistics
        ]);
    }
    
    /**
     * Get notification settings (admin only)
     */
    public function getSettings() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole > ROLE_ADMIN) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }
        
        $settings = $this->notificationModel->getSettings();
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $settings
        ]);
    }
    
    /**
     * Update notification settings (admin only)
     */
    public function updateSettings() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole > ROLE_ADMIN) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }
        
        // Check CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $key = $_POST['setting_key'] ?? null;
        $value = $_POST['setting_value'] ?? null;
        
        if (!$key || $value === null) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Setting key and value required'], 400);
            return;
        }
        
        $result = $this->notificationModel->updateSetting($key, $value);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Setting updated successfully'
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to update setting'
            ], 400);
        }
    }
    
    /**
     * Helper method to validate CSRF token
     */
    private function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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
