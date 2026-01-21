<?php
/**
 * Base Controller Class
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/View.php';

class Controller {
    protected $view;
    protected $data = [];
    
    public function __construct() {
        $this->view = new View();
    }
    
    /**
     * Load View
     */
    protected function view($view, $data = []) {
        $this->data = array_merge($this->data, $data);
        return $this->view->render($view, $this->data);
    }
    
    /**
     * Load View with Layout
     */
    protected function render($view, $data = []) {
        $this->data = array_merge($this->data, $data);
        $this->data['content'] = $this->view->render($view, $this->data, true);
        return $this->view->render('layouts/main', $this->data);
    }
    
    /**
     * Set Data for View
     */
    protected function set($key, $value) {
        $this->data[$key] = $value;
    }
    
    /**
     * Get Data
     */
    protected function getData($key, $default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    /**
     * JSON Response
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    /**
     * Success JSON Response
     */
    protected function success($message = 'Success', $data = null) {
        $response = [
            'status' => 'success',
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->json($response);
    }
    
    /**
     * Error JSON Response
     */
    protected function error($message = 'Error', $statusCode = 400, $data = null) {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['errors'] = $data;
        }
        
        $this->json($response, $statusCode);
    }
    
    /**
     * Redirect
     */
    protected function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    /**
     * Back Redirect
     */
    protected function back() {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL;
        $this->redirect($referer);
    }
    
    /**
     * Validate CSRF Token
     */
    protected function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF Token
     */
    protected function generateCSRF() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Check if User is Logged In
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get Current User ID
     */
    protected function getUserId() {
        return $this->isLoggedIn() ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Get Current User Role
     */
    protected function getUserRole() {
        return $this->isLoggedIn() ? $_SESSION['user_role'] : null;
    }
    
    /**
     * Check User Permission
     */
    protected function hasPermission($requiredRole) {
        $userRole = $this->getUserRole();
        return $userRole && $userRole <= $requiredRole; // Lower number = higher permission
    }
    
    /**
     * Require Authentication
     */
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect(BASE_URL . '/login');
        }
    }
    
    /**
     * Require Permission
     */
    protected function requirePermission($requiredRole) {
        $this->requireAuth();
        
        if (!$this->hasPermission($requiredRole)) {
            $this->error('Access denied', 403);
        }
    }
    
    /**
     * Get POST Data
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    
    /**
     * Get GET Data
     */
    protected function getQuery($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    /**
     * Get Input Data (POST or GET)
     */
    protected function input($key = null, $default = null) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'POST') {
            return $this->post($key, $default);
        } else {
            return $this->getQuery($key, $default);
        }
    }
    
    /**
     * Validate Required Fields
     */
    protected function validateRequired($fields, $data = null) {
        if ($data === null) {
            $data = $this->post();
        }
        
        $errors = [];
        
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Field {$field} is required";
            }
        }
        
        return $errors;
    }
    
    /**
     * Upload File
     */
    protected function uploadFile($fieldName, $uploadPath, $allowedTypes = [], $maxSize = 5242880) {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'No file uploaded or upload error'];
        }
        
        $file = $_FILES[$fieldName];
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['error' => 'File size too large'];
        }
        
        // Check file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowedTypes) && !in_array($fileExtension, $allowedTypes)) {
            return ['error' => 'File type not allowed'];
        }
        
        // Generate unique filename
        $filename = uniqid() . '.' . $fileExtension;
        $filepath = $uploadPath . '/' . $filename;
        
        // Create upload directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
        } else {
            return ['error' => 'Failed to move uploaded file'];
        }
    }
    
    /**
     * Format Currency
     */
    protected function formatCurrency($amount) {
        return CURRENCY_SYMBOL . ' ' . number_format($amount, DECIMAL_PLACES, DECIMAL_SEPARATOR, THOUSANDS_SEPARATOR);
    }
    
    /**
     * Format Date
     */
    protected function formatDate($date, $format = null) {
        if ($format === null) {
            $format = DATE_FORMAT;
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Sanitize Input
     */
    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Log Activity
     */
    protected function logActivity($action, $description = '') {
        $userId = $this->getUserId();
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        $logEntry = [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Save to logs file or database
        if (LOG_ENABLED) {
            $logFile = LOG_PATH . '/activity_' . date('Y-m-d') . '.log';
            $logMessage = json_encode($logEntry) . PHP_EOL;
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }
}
?>
