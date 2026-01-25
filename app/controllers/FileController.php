<?php

/**
 * File Controller
 * 
 * Handles file operations including upload, download, management,
 * and organization with proper access control
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/File.php';
require_once __DIR__ . '/../core/RealTimeUpdates.php';

class FileController extends Controller {
    
    private $fileModel;
    private $realTimeUpdates;
    
    public function __construct() {
        parent::__construct();
        $this->fileModel = new File();
        $this->realTimeUpdates = new RealTimeUpdates();
    }
    
    /**
     * Main file management page
     */
    public function index() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->redirect('index.php?page=auth&action=login');
            return;
        }
        
        // Get filters from request
        $filters = [
            'category' => $_GET['category'] ?? null,
            'search' => $_GET['search'] ?? null,
            'mime_type' => $_GET['mime_type'] ?? null,
            'is_public' => isset($_GET['is_public']) ? ($_GET['is_public'] === '1') : null
        ];
        
        // Apply company/branch filters if not admin
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole !== 'admin' && $userRole !== 'super_admin') {
            $filters['company_id'] = $_SESSION['company_id'] ?? null;
            $filters['branch_id'] = $_SESSION['branch_id'] ?? null;
        }
        
        // Get pagination
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        
        // Get files
        $result = $this->fileModel->getFiles($filters, $page, $limit);
        
        // Get statistics
        $statistics = $this->fileModel->getFileStatistics($filters);
        
        // Get categories for filter dropdown
        $categories = $this->fileModel->getFileCategories();
        
        // Get recent files
        $recentFiles = $this->fileModel->getRecentFiles(5, $filters);
        
        // Load view
        include __DIR__ . '/../views/files/index.php';
    }
    
    /**
     * Upload file handler
     */
    public function upload() {
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
        
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['status' => 'error', 'message' => 'No file uploaded or upload error'], 400);
            return;
        }
        
        // Prepare file data
        $fileData = [
            'name' => $_FILES['file']['name'],
            'type' => $_FILES['file']['type'],
            'size' => $_FILES['file']['size'],
            'tmp_name' => $_FILES['file']['tmp_name']
        ];
        
        // Prepare options
        $options = [
            'category' => $_POST['category'] ?? 'general',
            'description' => $_POST['description'] ?? null,
            'tags' => $_POST['tags'] ?? null,
            'is_public' => isset($_POST['is_public']) && $_POST['is_public'] === '1'
        ];
        
        // Add company/branch info if not admin
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole !== 'admin' && $userRole !== 'super_admin') {
            $options['company_id'] = $_SESSION['company_id'] ?? null;
            $options['branch_id'] = $_SESSION['branch_id'] ?? null;
        }
        
        // Upload file
        $fileId = $this->fileModel->uploadFile($fileData, $options);
        
        if ($fileId) {
            // Send WebSocket notification
            $fileData = $this->fileModel->getFile($fileId);
            if ($fileData) {
                $this->realTimeUpdates->notifyFileUpload($fileData, $_SESSION['user_id']);
            }
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'data' => [
                    'file_id' => $fileId,
                    'filename' => $_FILES['file']['name']
                ]
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $this->fileModel->getFirstError() ?? 'Upload failed'
            ], 400);
        }
    }
    
    /**
     * Download file
     */
    public function download() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $fileId = $_GET['id'] ?? null;
        if (!$fileId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'File ID required'], 400);
            return;
        }
        
        // Download file
        $result = $this->fileModel->downloadFile($fileId);
        
        if (!$result) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $this->fileModel->getFirstError() ?? 'Download failed'
            ], 404);
        } else {
            // Send WebSocket notification for successful download
            $fileData = $this->fileModel->getFile($fileId);
            if ($fileData) {
                $this->realTimeUpdates->notifyFileDownload($fileData, $_SESSION['user_id']);
            }
        }
    }
    
    /**
     * Get file details
     */
    public function getFile() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $fileId = $_GET['id'] ?? null;
        if (!$fileId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'File ID required'], 400);
            return;
        }
        
        // Get file
        $file = $this->fileModel->getFile($fileId);
        
        if ($file) {
            // Format file size
            $file['formatted_size'] = $this->fileModel->formatFileSize($file['file_size']);
            $file['file_icon'] = $this->fileModel->getFileIcon($file['mime_type']);
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $file
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $this->fileModel->getFirstError() ?? 'File not found'
            ], 404);
        }
    }
    
    /**
     * Update file information
     */
    public function update() {
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
        
        $fileId = $_POST['file_id'] ?? null;
        if (!$fileId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'File ID required'], 400);
            return;
        }
        
        // Prepare update data
        $updateData = [];
        
        if (isset($_POST['description'])) {
            $updateData['description'] = $_POST['description'];
        }
        
        if (isset($_POST['tags'])) {
            $updateData['tags'] = $_POST['tags'];
        }
        
        if (isset($_POST['category'])) {
            $updateData['file_category'] = $_POST['category'];
        }
        
        if (isset($_POST['is_public'])) {
            $updateData['is_public'] = $_POST['is_public'] === '1';
        }
        
        // Update file
        $result = $this->fileModel->updateFile($fileId, $updateData);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'File updated successfully'
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $this->fileModel->getFirstError() ?? 'Update failed'
            ], 400);
        }
    }
    
    /**
     * Delete file
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
        
        $fileId = $_POST['file_id'] ?? null;
        if (!$fileId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'File ID required'], 400);
            return;
        }
        
        // Delete file
        $result = $this->fileModel->deleteFile($fileId);
        
        if ($result) {
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'File deleted successfully'
            ]);
        } else {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $this->fileModel->getFirstError() ?? 'Delete failed'
            ], 400);
        }
    }
    
    /**
     * Get file statistics
     */
    public function getStatistics() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        // Prepare filters
        $filters = [];
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole !== 'admin' && $userRole !== 'super_admin') {
            $filters['company_id'] = $_SESSION['company_id'] ?? null;
            $filters['branch_id'] = $_SESSION['branch_id'] ?? null;
        }
        
        // Get statistics
        $statistics = $this->fileModel->getFileStatistics($filters);
        
        // Format total size
        if ($statistics['overview']['total_size']) {
            $statistics['overview']['formatted_total_size'] = $this->fileModel->formatFileSize($statistics['overview']['total_size']);
        }
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $statistics
        ]);
    }
    
    /**
     * Bulk operations
     */
    public function bulkOperations() {
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
        
        $operation = $_POST['operation'] ?? null;
        $fileIds = $_POST['file_ids'] ?? [];
        
        if (!$operation || empty($fileIds)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Operation and file IDs required'], 400);
            return;
        }
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($fileIds as $fileId) {
            try {
                switch ($operation) {
                    case 'delete':
                        if ($this->fileModel->deleteFile($fileId)) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Failed to delete file {$fileId}";
                        }
                        break;
                        
                    case 'make_public':
                        if ($this->fileModel->updateFile($fileId, ['is_public' => true])) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Failed to make file {$fileId} public";
                        }
                        break;
                        
                    case 'make_private':
                        if ($this->fileModel->updateFile($fileId, ['is_public' => false])) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Failed to make file {$fileId} private";
                        }
                        break;
                        
                    default:
                        $errorCount++;
                        $errors[] = "Unknown operation: {$operation}";
                        break;
                }
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Error processing file {$fileId}: " . $e->getMessage();
            }
        }
        
        $this->jsonResponse([
            'status' => $errorCount === 0 ? 'success' : 'partial',
            'message' => "Processed {$successCount} files successfully. {$errorCount} errors.",
            'data' => [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]
        ]);
    }
    
    /**
     * Search files
     */
    public function search() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $query = $_GET['q'] ?? null;
        if (!$query) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Search query required'], 400);
            return;
        }
        
        // Prepare filters
        $filters = [
            'search' => $query,
            'category' => $_GET['category'] ?? null,
            'mime_type' => $_GET['mime_type'] ?? null
        ];
        
        // Apply company/branch filters if not admin
        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole !== 'admin' && $userRole !== 'super_admin') {
            $filters['company_id'] = $_SESSION['company_id'] ?? null;
            $filters['branch_id'] = $_SESSION['branch_id'] ?? null;
        }
        
        // Get pagination
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        
        // Search files
        $result = $this->fileModel->getFiles($filters, $page, $limit);
        
        // Format results
        foreach ($result['files'] as &$file) {
            $file['formatted_size'] = $this->fileModel->formatFileSize($file['file_size']);
            $file['file_icon'] = $this->fileModel->getFileIcon($file['mime_type']);
        }
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $result
        ]);
    }
    
    /**
     * Get upload progress (for large files)
     */
    public function getUploadProgress() {
        // Check permissions
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $uploadId = $_GET['upload_id'] ?? null;
        if (!$uploadId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Upload ID required'], 400);
            return;
        }
        
        // This would typically use APCu or Redis to track upload progress
        // For now, we'll return a placeholder response
        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'upload_id' => $uploadId,
                'bytes_uploaded' => 0,
                'bytes_total' => 0,
                'percentage' => 0,
                'status' => 'pending'
            ]
        ]);
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
