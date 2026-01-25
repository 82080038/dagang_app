<?php

/**
 * File Model
 * 
 * Handles file operations including upload, download, organization,
 * version control, and access management
 */

class File extends Model {
    
    protected $table = 'files';
    protected $primaryKey = 'id_file';
    
    // Allowed file types and their MIME types
    private $allowedMimeTypes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'text/plain' => ['txt'],
        'text/csv' => ['csv'],
        'application/zip' => ['zip'],
        'application/x-rar-compressed' => ['rar']
    ];
    
    // Maximum file sizes (in bytes)
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    private $maxImageSize = 5 * 1024 * 1024; // 5MB for images
    
    protected $fillable = [
        'filename',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'file_extension',
        'file_category',
        'description',
        'tags',
        'uploaded_by',
        'company_id',
        'branch_id',
        'is_public',
        'is_active',
        'download_count',
        'last_accessed',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Upload file with validation and organization
     */
    public function uploadFile($fileData, $options = []) {
        // Validate file data
        if (!$this->validateFile($fileData)) {
            return false;
        }
        
        // Generate unique filename
        $filename = $this->generateUniqueFilename($fileData['name']);
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        
        // Determine upload directory
        $uploadDir = $this->getUploadDirectory($options['category'] ?? 'general');
        $filePath = $uploadDir . '/' . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            $this->setError('Failed to move uploaded file');
            return false;
        }
        
        // Process file (resize images, create thumbnails, etc.)
        $processedFiles = $this->processFile($filePath, $extension);
        
        // Save file record to database
        $fileRecord = [
            'filename' => $filename,
            'original_name' => $fileData['name'],
            'file_path' => $filePath,
            'file_size' => $fileData['size'],
            'mime_type' => $fileData['type'],
            'file_extension' => $extension,
            'file_category' => $options['category'] ?? 'general',
            'description' => $options['description'] ?? null,
            'tags' => $options['tags'] ?? null,
            'uploaded_by' => $_SESSION['user_id'] ?? null,
            'company_id' => $options['company_id'] ?? $_SESSION['company_id'] ?? null,
            'branch_id' => $options['branch_id'] ?? $_SESSION['branch_id'] ?? null,
            'is_public' => $options['is_public'] ?? false,
            'is_active' => true,
            'download_count' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $fileId = $this->create($fileRecord);
        
        if (!$fileId) {
            // Clean up uploaded file if database insert failed
            unlink($filePath);
            $this->setError('Failed to save file record');
            return false;
        }
        
        // Save processed files if any
        if (!empty($processedFiles)) {
            $this->saveProcessedFiles($fileId, $processedFiles);
        }
        
        // Log file upload
        $this->logFileActivity('UPLOAD', $fileId, $fileRecord);
        
        return $fileId;
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($fileData) {
        // Check if file was uploaded
        if (!isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
            $this->setError('Invalid file upload');
            return false;
        }
        
        // Check file size
        $maxSize = in_array($fileData['type'], array_keys($this->allowedMimeTypes)) && 
                   strpos($fileData['type'], 'image/') === 0 ? 
                   $this->maxImageSize : $this->maxFileSize;
                   
        if ($fileData['size'] > $maxSize) {
            $this->setError('File size exceeds maximum limit');
            return false;
        }
        
        // Check MIME type
        if (!array_key_exists($fileData['type'], $this->allowedMimeTypes)) {
            $this->setError('File type not allowed');
            return false;
        }
        
        // Verify file extension matches MIME type
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedMimeTypes[$fileData['type']])) {
            $this->setError('File extension does not match file type');
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        
        return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Get upload directory based on category and date
     */
    private function getUploadDirectory($category) {
        $year = date('Y');
        $month = date('m');
        
        $baseDir = UPLOADS_PATH . '/files';
        $categoryDir = $baseDir . '/' . $category;
        $yearDir = $categoryDir . '/' . $year;
        $monthDir = $yearDir . '/' . $month;
        
        return $monthDir;
    }
    
    /**
     * Process file (resize images, create thumbnails, etc.)
     */
    private function processFile($filePath, $extension) {
        $processedFiles = [];
        
        // Process images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $processedFiles = $this->processImage($filePath);
        }
        
        // Process PDFs (create preview if possible)
        elseif ($extension === 'pdf') {
            $processedFiles = $this->processPDF($filePath);
        }
        
        return $processedFiles;
    }
    
    /**
     * Process image files
     */
    private function processImage($filePath) {
        $processedFiles = [];
        
        try {
            // Get image info
            $imageInfo = getimagesize($filePath);
            if (!$imageInfo) {
                return $processedFiles;
            }
            
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mime = $imageInfo['mime'];
            
            // Create different sizes
            $sizes = [
                'thumbnail' => [150, 150],
                'medium' => [800, 600],
                'large' => [1200, 900]
            ];
            
            foreach ($sizes as $sizeName => $dimensions) {
                $newWidth = $dimensions[0];
                $newHeight = $dimensions[1];
                
                // Calculate aspect ratio
                if ($width > $height) {
                    $newHeight = ($height / $width) * $newWidth;
                } else {
                    $newWidth = ($width / $height) * $newHeight;
                }
                
                // Create new image
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Load original image
                if ($mime === 'image/jpeg') {
                    $source = imagecreatefromjpeg($filePath);
                } elseif ($mime === 'image/png') {
                    $source = imagecreatefrompng($filePath);
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                } elseif ($mime === 'image/gif') {
                    $source = imagecreatefromgif($filePath);
                } else {
                    continue;
                }
                
                // Resize and save
                imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                $pathInfo = pathinfo($filePath);
                $newFilePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
                
                if ($mime === 'image/jpeg') {
                    imagejpeg($newImage, $newFilePath, 85);
                } elseif ($mime === 'image/png') {
                    imagepng($newImage, $newFilePath, 8);
                } elseif ($mime === 'image/gif') {
                    imagegif($newImage, $newFilePath);
                }
                
                $processedFiles[$sizeName] = $newFilePath;
                
                // Clean up
                imagedestroy($newImage);
                imagedestroy($source);
            }
            
        } catch (Exception $e) {
            error_log("Image processing error: " . $e->getMessage());
        }
        
        return $processedFiles;
    }
    
    /**
     * Process PDF files
     */
    private function processPDF($filePath) {
        $processedFiles = [];
        
        try {
            // This would require additional libraries like Imagick or Ghostscript
            // For now, we'll just return empty array
            // In a production environment, you could implement PDF thumbnail generation
            
        } catch (Exception $e) {
            error_log("PDF processing error: " . $e->getMessage());
        }
        
        return $processedFiles;
    }
    
    /**
     * Save processed files to database
     */
    private function saveProcessedFiles($fileId, $processedFiles) {
        foreach ($processedFiles as $type => $filePath) {
            $data = [
                'file_id' => $fileId,
                'file_type' => $type,
                'file_path' => $filePath,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('file_versions', $data);
        }
    }
    
    /**
     * Get file by ID with access control
     */
    public function getFile($fileId, $userId = null) {
        $userId = $userId ?? $_SESSION['user_id'] ?? null;
        
        $sql = "SELECT f.*, u.username as uploader_name,
                       c.company_name, b.branch_name
                FROM files f
                LEFT JOIN users u ON f.uploaded_by = u.id_user
                LEFT JOIN companies c ON f.company_id = c.id_company
                LEFT JOIN branches b ON f.branch_id = b.id_branch
                WHERE f.id_file = ? AND f.is_active = 1";
        
        $file = $this->db->query($sql, [$fileId])->fetch();
        
        if (!$file) {
            return null;
        }
        
        // Check access permissions
        if (!$this->canAccessFile($file, $userId)) {
            $this->setError('Access denied');
            return null;
        }
        
        // Update last accessed
        $this->updateLastAccessed($fileId);
        
        return $file;
    }
    
    /**
     * Check if user can access file
     */
    private function canAccessFile($file, $userId) {
        // Public files can be accessed by anyone
        if ($file['is_public']) {
            return true;
        }
        
        // User must be logged in
        if (!$userId) {
            return false;
        }
        
        // User uploaded the file
        if ($file['uploaded_by'] == $userId) {
            return true;
        }
        
        // Check company/branch access
        $userCompany = $_SESSION['company_id'] ?? null;
        $userBranch = $_SESSION['branch_id'] ?? null;
        
        if ($file['company_id'] && $file['company_id'] == $userCompany) {
            return true;
        }
        
        if ($file['branch_id'] && $file['branch_id'] == $userBranch) {
            return true;
        }
        
        // Check role-based access
        $userRole = $_SESSION['user_role'] ?? null;
        if (in_array($userRole, ['admin', 'super_admin'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Update last accessed timestamp
     */
    private function updateLastAccessed($fileId) {
        $sql = "UPDATE files SET last_accessed = ? WHERE id_file = ?";
        $this->db->query($sql, [date('Y-m-d H:i:s'), $fileId]);
    }
    
    /**
     * Download file
     */
    public function downloadFile($fileId) {
        $file = $this->getFile($fileId);
        
        if (!$file) {
            return false;
        }
        
        // Check if file exists
        if (!file_exists($file['file_path'])) {
            $this->setError('File not found on server');
            return false;
        }
        
        // Increment download count
        $this->incrementDownloadCount($fileId);
        
        // Log download
        $this->logFileActivity('DOWNLOAD', $fileId, $file);
        
        // Set headers for download
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . filesize($file['file_path']));
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output file
        readfile($file['file_path']);
        exit;
    }
    
    /**
     * Increment download count
     */
    private function incrementDownloadCount($fileId) {
        $sql = "UPDATE files SET download_count = download_count + 1 WHERE id_file = ?";
        $this->db->query($sql, [$fileId]);
    }
    
    /**
     * Get files with filtering and pagination
     */
    public function getFiles($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = ['f.is_active = 1'];
        $params = [];
        
        // Build WHERE clause
        if (!empty($filters['category'])) {
            $where[] = "f.file_category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['company_id'])) {
            $where[] = "f.company_id = ?";
            $params[] = $filters['company_id'];
        }
        
        if (!empty($filters['branch_id'])) {
            $where[] = "f.branch_id = ?";
            $params[] = $filters['branch_id'];
        }
        
        if (!empty($filters['uploaded_by'])) {
            $where[] = "f.uploaded_by = ?";
            $params[] = $filters['uploaded_by'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(f.original_name LIKE ? OR f.description LIKE ? OR f.tags LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['mime_type'])) {
            $where[] = "f.mime_type LIKE ?";
            $params[] = $filters['mime_type'] . '%';
        }
        
        if (isset($filters['is_public'])) {
            $where[] = "f.is_public = ?";
            $params[] = $filters['is_public'];
        }
        
        // Apply access control
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? null;
        $userCompany = $_SESSION['company_id'] ?? null;
        $userBranch = $_SESSION['branch_id'] ?? null;
        
        if ($userRole !== 'admin' && $userRole !== 'super_admin') {
            $accessWhere = [];
            if ($userId) {
                $accessWhere[] = "f.uploaded_by = ?";
                $params[] = $userId;
            }
            if ($userCompany) {
                $accessWhere[] = "f.company_id = ?";
                $params[] = $userCompany;
            }
            if ($userBranch) {
                $accessWhere[] = "f.branch_id = ?";
                $params[] = $userBranch;
            }
            $accessWhere[] = "f.is_public = 1";
            $params[] = true;
            
            $where[] = "(" . implode(" OR ", $accessWhere) . ")";
        }
        
        $whereClause = implode(" AND ", $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM files f WHERE {$whereClause}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'];
        
        // Get files
        $sql = "SELECT f.*, u.username as uploader_name,
                       c.company_name, b.branch_name
                FROM files f
                LEFT JOIN users u ON f.uploaded_by = u.id_user
                LEFT JOIN companies c ON f.company_id = c.id_company
                LEFT JOIN branches b ON f.branch_id = b.id_branch
                WHERE {$whereClause}
                ORDER BY f.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $files = $this->db->query($sql, $params)->fetchAll();
        
        return [
            'files' => $files,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'items_per_page' => $limit
            ]
        ];
    }
    
    /**
     * Update file information
     */
    public function updateFile($fileId, $data) {
        $file = $this->getFile($fileId);
        
        if (!$file) {
            return false;
        }
        
        // Check if user can update this file
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? null;
        
        if ($file['uploaded_by'] != $userId && !in_array($userRole, ['admin', 'super_admin'])) {
            $this->setError('Access denied');
            return false;
        }
        
        // Update allowed fields
        $allowedFields = ['description', 'tags', 'is_public', 'file_category'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return true; // Nothing to update
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        $result = $this->update($fileId, $updateData);
        
        if ($result) {
            // Log update
            $this->logFileActivity('UPDATE', $fileId, array_merge($file, $updateData));
        }
        
        return $result;
    }
    
    /**
     * Delete file (soft delete)
     */
    public function deleteFile($fileId) {
        $file = $this->getFile($fileId);
        
        if (!$file) {
            return false;
        }
        
        // Check if user can delete this file
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? null;
        
        if ($file['uploaded_by'] != $userId && !in_array($userRole, ['admin', 'super_admin'])) {
            $this->setError('Access denied');
            return false;
        }
        
        // Soft delete
        $result = $this->update($fileId, [
            'is_active' => false,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            // Log deletion
            $this->logFileActivity('DELETE', $fileId, $file);
        }
        
        return $result;
    }
    
    /**
     * Get file statistics
     */
    public function getFileStatistics($filters = []) {
        $where = ['is_active = 1'];
        $params = [];
        
        // Apply filters
        if (!empty($filters['company_id'])) {
            $where[] = "company_id = ?";
            $params[] = $filters['company_id'];
        }
        
        if (!empty($filters['branch_id'])) {
            $where[] = "branch_id = ?";
            $params[] = $filters['branch_id'];
        }
        
        $whereClause = implode(" AND ", $where);
        
        // Get statistics
        $sql = "SELECT 
                    COUNT(*) as total_files,
                    SUM(file_size) as total_size,
                    COUNT(DISTINCT file_category) as categories_count,
                    COUNT(DISTINCT uploaded_by) as uploaders_count,
                    SUM(download_count) as total_downloads
                FROM files 
                WHERE {$whereClause}";
        
        $stats = $this->db->query($sql, $params)->fetch();
        
        // Get files by category
        $categorySql = "SELECT file_category, COUNT(*) as count 
                        FROM files 
                        WHERE {$whereClause}
                        GROUP BY file_category 
                        ORDER BY count DESC";
        
        $categories = $this->db->query($categorySql, $params)->fetchAll();
        
        // Get files by type
        $typeSql = "SELECT SUBSTRING_INDEX(mime_type, '/', 1) as type, COUNT(*) as count 
                   FROM files 
                   WHERE {$whereClause}
                   GROUP BY type 
                   ORDER BY count DESC";
        
        $types = $this->db->query($typeSql, $params)->fetchAll();
        
        return [
            'overview' => $stats,
            'by_category' => $categories,
            'by_type' => $types
        ];
    }
    
    /**
     * Log file activity
     */
    private function logFileActivity($action, $fileId, $fileData) {
        $logData = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'entity_type' => 'file',
            'entity_id' => $fileId,
            'old_values' => $action === 'UPDATE' ? $fileData : null,
            'new_values' => $action !== 'DELETE' ? $fileData : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('audit_logs', $logData);
    }
    
    /**
     * Get file categories
     */
    public function getFileCategories() {
        $sql = "SELECT DISTINCT file_category FROM files WHERE is_active = 1 ORDER BY file_category";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Get recent files
     */
    public function getRecentFiles($limit = 10, $filters = []) {
        $where = ['f.is_active = 1'];
        $params = [];
        
        // Apply access control
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? null;
        
        if ($userRole !== 'admin' && $userRole !== 'super_admin') {
            $accessWhere = ["f.is_public = 1"];
            if ($userId) {
                $accessWhere[] = "f.uploaded_by = ?";
                $params[] = $userId;
            }
            $where[] = "(" . implode(" OR ", $accessWhere) . ")";
        }
        
        if (!empty($filters['company_id'])) {
            $where[] = "f.company_id = ?";
            $params[] = $filters['company_id'];
        }
        
        $whereClause = implode(" AND ", $where);
        
        $sql = "SELECT f.*, u.username as uploader_name
                FROM files f
                LEFT JOIN users u ON f.uploaded_by = u.id_user
                WHERE {$whereClause}
                ORDER BY f.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Format file size for display
     */
    public function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Get file icon based on MIME type
     */
    public function getFileIcon($mimeType) {
        $iconMap = [
            'image/jpeg' => 'bi-file-image',
            'image/png' => 'bi-file-image',
            'image/gif' => 'bi-file-image',
            'application/pdf' => 'bi-file-pdf',
            'application/msword' => 'bi-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'bi-file-word',
            'application/vnd.ms-excel' => 'bi-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'bi-file-excel',
            'text/plain' => 'bi-file-text',
            'text/csv' => 'bi-file-csv',
            'application/zip' => 'bi-file-zip',
            'application/x-rar-compressed' => 'bi-file-zip'
        ];
        
        return $iconMap[$mimeType] ?? 'bi-file';
    }
}
