<?php
// File Management Interface
// Part of Phase 3: Advanced Features Development
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management - Perdagangan System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .file-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .file-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        
        .file-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .file-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .upload-area.dragover {
            border-color: #0d6efd;
            background-color: #f8f9ff;
        }
        
        .upload-progress {
            margin-top: 1rem;
        }
        
        .file-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .file-card:hover .file-actions {
            opacity: 1;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
        }
        
        .category-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box .bi-search {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .search-box input {
            padding-left: 2.5rem;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .file-thumbnail {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        
        .bulk-actions {
            position: sticky;
            bottom: 1rem;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../layouts/main.php'; ?>
    
    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="bi bi-files me-2"></i>File Management</h2>
                <p class="text-muted">Upload, organize, and share your files</p>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 mb-0"><?= $statistics['overview']['total_files'] ?? 0 ?></div>
                            <small>Files</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0"><?= $statistics['overview']['formatted_total_size'] ?? '0 B' ?></div>
                            <small>Total Size</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0"><?= $statistics['overview']['total_downloads'] ?? 0 ?></div>
                            <small>Downloads</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upload Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="upload-area" id="uploadArea">
                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #0d6efd;"></i>
                    <h5>Upload Files</h5>
                    <p class="text-muted">Drag and drop files here or click to browse</p>
                    <input type="file" id="fileInput" multiple style="display: none;">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-folder2-open me-1"></i>Choose Files
                    </button>
                    
                    <!-- Upload Form (Hidden by default) -->
                    <div id="uploadForm" style="display: none;" class="mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select class="form-select" id="uploadCategory">
                                    <option value="general">General</option>
                                    <option value="images">Images</option>
                                    <option value="documents">Documents</option>
                                    <option value="media">Media</option>
                                    <option value="archives">Archives</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Visibility</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="uploadPublic">
                                    <label class="form-check-label" for="uploadPublic">
                                        Make public
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="uploadDescription" rows="2" placeholder="Optional description..."></textarea>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <label class="form-label">Tags</label>
                                <input type="text" class="form-control" id="uploadTags" placeholder="Comma-separated tags...">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-success" id="uploadBtn">
                                    <i class="bi bi-upload me-1"></i>Upload Files
                                </button>
                                <button type="button" class="btn btn-secondary ms-2" id="cancelUploadBtn">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload Progress -->
                    <div id="uploadProgress" class="upload-progress" style="display: none;">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-1">Uploading...</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search files...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['file_category'] ?>"><?= ucfirst($category['file_category']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="image/">Images</option>
                        <option value="application/pdf">PDF</option>
                        <option value="application/msword">Word</option>
                        <option value="application/vnd.ms-excel">Excel</option>
                        <option value="text/">Text</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="visibilityFilter">
                        <option value="">All Files</option>
                        <option value="1">Public</option>
                        <option value="0">Private</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="button" class="btn btn-outline-primary" id="gridViewBtn">
                            <i class="bi bi-grid"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="listViewBtn">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bulk Actions (Hidden by default) -->
        <div class="bulk-actions" id="bulkActions" style="display: none;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted">
                        <span id="selectedCount">0</span> files selected
                    </span>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="bulkShareBtn">
                        <i class="bi bi-share me-1"></i>Share
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning" id="bulkDownloadBtn">
                        <i class="bi bi-download me-1"></i>Download
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="bulkDeleteBtn">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" id="clearSelectionBtn">
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Files Display -->
        <div class="row">
            <div class="col-12">
                <div id="filesContainer">
                    <!-- Grid View -->
                    <div class="file-grid" id="gridView">
                        <?php foreach ($result['files'] as $file): ?>
                            <div class="file-card" data-file-id="<?= $file['id_file'] ?>">
                                <div class="file-actions">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" onclick="FileModule.downloadFile(<?= $file['id_file'] ?>)">
                                            <i class="bi bi-download"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="FileModule.shareFile(<?= $file['id_file'] ?>)">
                                            <i class="bi bi-share"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="FileModule.editFile(<?= $file['id_file'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="FileModule.deleteFile(<?= $file['id_file'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
                                    <img src="<?= BASE_URL ?>/uploads/files/<?= $file['file_category'] ?>/<?= date('Y/m', strtotime($file['created_at'])) ?>/<?= $file['filename'] ?>" 
                                         class="file-thumbnail" alt="<?= htmlspecialchars($file['original_name']) ?>">
                                <?php else: ?>
                                    <div class="file-icon text-center">
                                        <i class="bi <?= $file['file_icon'] ?? 'bi-file' ?>"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="file-name" title="<?= htmlspecialchars($file['original_name']) ?>">
                                    <?= htmlspecialchars($file['original_name']) ?>
                                </div>
                                <div class="file-meta">
                                    <div><?= $file['formatted_size'] ?></div>
                                    <div><?= date('M d, Y', strtotime($file['created_at'])) ?></div>
                                    <div>
                                        <span class="category-badge bg-primary text-white">
                                            <?= ucfirst($file['file_category']) ?>
                                        </span>
                                        <?php if ($file['is_public']): ?>
                                            <span class="category-badge bg-success text-white">
                                                <i class="bi bi-globe"></i> Public
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-check mt-2">
                                    <input class="form-check-input file-checkbox" type="checkbox" value="<?= $file['id_file'] ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- List View (Hidden by default) -->
                    <div class="table-responsive" id="listView" style="display: none;">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllCheckbox">
                                    </th>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Category</th>
                                    <th>Uploaded</th>
                                    <th>Downloads</th>
                                    <th>Visibility</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['files'] as $file): ?>
                                    <tr>
                                        <td>
                                            <input class="form-check-input file-checkbox" type="checkbox" value="<?= $file['id_file'] ?>">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi <?= $file['file_icon'] ?? 'bi-file' ?> me-2"></i>
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars($file['original_name']) ?></div>
                                                    <small class="text-muted"><?= $file['uploader_name'] ?? 'Unknown' ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= $file['formatted_size'] ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?= ucfirst($file['file_category']) ?></span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($file['created_at'])) ?></td>
                                        <td><?= $file['download_count'] ?></td>
                                        <td>
                                            <?php if ($file['is_public']): ?>
                                                <span class="badge bg-success">Public</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Private</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="FileModule.downloadFile(<?= $file['id_file'] ?>)">
                                                    <i class="bi bi-download"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="FileModule.shareFile(<?= $file['id_file'] ?>)">
                                                    <i class="bi bi-share"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info" onclick="FileModule.editFile(<?= $file['id_file'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="FileModule.deleteFile(<?= $file['id_file'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($result['pagination']['total_pages'] > 1): ?>
                    <nav aria-label="File pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php
                            $currentPage = $result['pagination']['current_page'];
                            $totalPages = $result['pagination']['total_pages'];
                            
                            // Previous button
                            if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Page numbers -->
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Next button -->
                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Files -->
        <?php if (!empty($recentFiles)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Recent Files</h5>
                    <div class="row">
                        <?php foreach ($recentFiles as $file): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <i class="bi <?= $file['file_icon'] ?? 'bi-file' ?> me-2"></i>
                                            <div>
                                                <div class="fw-semibold small"><?= htmlspecialchars($file['original_name']) ?></div>
                                                <small class="text-muted"><?= date('M d, H:i', strtotime($file['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- File Edit Modal -->
    <div class="modal fade" id="fileEditModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="fileEditForm">
                        <input type="hidden" id="editFileId">
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input type="text" class="form-control" id="editTags">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="editCategory">
                                <option value="general">General</option>
                                <option value="images">Images</option>
                                <option value="documents">Documents</option>
                                <option value="media">Media</option>
                                <option value="archives">Archives</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editPublic">
                                <label class="form-check-label" for="editPublic">
                                    Make public
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="FileModule.saveFileEdit()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- File Share Modal -->
    <div class="modal fade" id="fileShareModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Share Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="shareLink" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="FileModule.copyShareLink()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Share Options</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="shareAllowDownload" checked>
                            <label class="form-check-label" for="shareAllowDownload">
                                Allow download
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="shareExpires">
                            <label class="form-check-label" for="shareExpires">
                                Link expires in
                            </label>
                        </div>
                        <select class="form-select mt-2" id="shareExpiry" style="display: none;">
                            <option value="1">1 hour</option>
                            <option value="24">1 day</option>
                            <option value="168">1 week</option>
                            <option value="720">1 month</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="FileModule.createShareLink()">Create Share Link</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Processing...</p>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/jquery-ajax.js"></script>
    
    <script>
        // File Management Module
        var FileModule = {
            selectedFiles: new Set(),
            currentView: 'grid',
            uploadedFiles: [],
            
            init: function() {
                this.bindEvents();
                this.initializeDragDrop();
                this.updateSelectedCount();
            },
            
            bindEvents: function() {
                // File input change
                $('#fileInput').on('change', this.handleFileSelect.bind(this));
                
                // Upload button
                $('#uploadBtn').on('click', this.uploadFiles.bind(this));
                
                // Cancel upload button
                $('#cancelUploadBtn').on('click', this.cancelUpload.bind(this));
                
                // Search
                $('#searchInput').on('input', this.debounce(this.searchFiles.bind(this), 500));
                
                // Filters
                $('#categoryFilter, #typeFilter, #visibilityFilter').on('change', this.applyFilters.bind(this));
                
                // View toggle
                $('#gridViewBtn').on('click', () => this.switchView('grid'));
                $('#listViewBtn').on('click', () => this.switchView('list'));
                
                // Select all checkbox
                $('#selectAllCheckbox').on('change', this.toggleSelectAll.bind(this));
                
                // File checkboxes
                $(document).on('change', '.file-checkbox', this.updateSelectedCount.bind(this));
                
                // Bulk actions
                $('#bulkShareBtn').on('click', this.bulkShare.bind(this));
                $('#bulkDownloadBtn').on('click', this.bulkDownload.bind(this));
                $('#bulkDeleteBtn').on('click', this.bulkDelete.bind(this));
                $('#clearSelectionBtn').on('click', this.clearSelection.bind(this));
                
                // Share expiry toggle
                $('#shareExpires').on('change', function() {
                    $('#shareExpiry').toggle(this.checked);
                });
            },
            
            initializeDragDrop: function() {
                const uploadArea = document.getElementById('uploadArea');
                
                uploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadArea.classList.add('dragover');
                });
                
                uploadArea.addEventListener('dragleave', () => {
                    uploadArea.classList.remove('dragover');
                });
                
                uploadArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    uploadArea.classList.remove('dragover');
                    this.handleFileSelect({ target: { files: e.dataTransfer.files } });
                });
            },
            
            handleFileSelect: function(e) {
                const files = Array.from(e.target.files);
                this.uploadedFiles = files;
                
                if (files.length > 0) {
                    $('#uploadForm').slideDown();
                    
                    // Update upload button text
                    $('#uploadBtn').html(`<i class="bi bi-upload me-1"></i>Upload ${files.length} File${files.length > 1 ? 's' : ''}`);
                } else {
                    $('#uploadForm').slideUp();
                }
            },
            
            uploadFiles: function() {
                if (this.uploadedFiles.length === 0) {
                    Toast.error('Please select files to upload');
                    return;
                }
                
                const formData = new FormData();
                const file = this.uploadedFiles[0]; // Upload one at a time for simplicity
                
                formData.append('file', file);
                formData.append('category', $('#uploadCategory').val());
                formData.append('description', $('#uploadDescription').val());
                formData.append('tags', $('#uploadTags').val());
                formData.append('is_public', $('#uploadPublic').is(':checked') ? '1' : '0');
                formData.append('csrf_token', this.getCsrfToken());
                
                // Show progress
                $('#uploadProgress').show();
                $('.progress-bar').css('width', '0%');
                
                $.ajax({
                    url: 'index.php?page=files&action=upload',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: () => {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) * 100);
                                $('.progress-bar').css('width', percent + '%');
                            }
                        });
                        return xhr;
                    },
                    success: (response) => {
                        if (response.status === 'success') {
                            Toast.success('File uploaded successfully');
                            this.cancelUpload();
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Toast.error(response.message || 'Upload failed');
                        }
                    },
                    error: () => {
                        Toast.error('Upload failed');
                    },
                    complete: () => {
                        $('#uploadProgress').hide();
                    }
                });
            },
            
            cancelUpload: function() {
                $('#fileInput').val('');
                $('#uploadForm').slideUp();
                $('#uploadDescription').val('');
                $('#uploadTags').val('');
                $('#uploadPublic').prop('checked', false);
                this.uploadedFiles = [];
            },
            
            downloadFile: function(fileId) {
                window.open(`index.php?page=files&action=download&id=${fileId}`, '_blank');
            },
            
            shareFile: function(fileId) {
                $('#fileShareModal').modal('show');
                // Generate share link logic here
            },
            
            editFile: function(fileId) {
                $.get(`index.php?page=files&action=getFile&id=${fileId}`)
                    .done((response) => {
                        if (response.status === 'success') {
                            const file = response.data;
                            $('#editFileId').val(file.id_file);
                            $('#editDescription').val(file.description || '');
                            $('#editTags').val(file.tags || '');
                            $('#editCategory').val(file.file_category);
                            $('#editPublic').prop('checked', file.is_public === 1);
                            $('#fileEditModal').modal('show');
                        }
                    });
            },
            
            saveFileEdit: function() {
                const fileId = $('#editFileId').val();
                const data = {
                    file_id: fileId,
                    description: $('#editDescription').val(),
                    tags: $('#editTags').val(),
                    category: $('#editCategory').val(),
                    is_public: $('#editPublic').is(':checked') ? '1' : '0',
                    csrf_token: this.getCsrfToken()
                };
                
                $.post('index.php?page=files&action=update', data)
                    .done((response) => {
                        if (response.status === 'success') {
                            Toast.success('File updated successfully');
                            $('#fileEditModal').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Toast.error(response.message || 'Update failed');
                        }
                    });
            },
            
            deleteFile: function(fileId) {
                if (confirm('Are you sure you want to delete this file?')) {
                    const data = {
                        file_id: fileId,
                        csrf_token: this.getCsrfToken()
                    };
                    
                    $.post('index.php?page=files&action=delete', data)
                        .done((response) => {
                            if (response.status === 'success') {
                                Toast.success('File deleted successfully');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                Toast.error(response.message || 'Delete failed');
                            }
                        });
                }
            },
            
            searchFiles: function() {
                const query = $('#searchInput').val();
                if (query.length >= 2 || query.length === 0) {
                    this.applyFilters();
                }
            },
            
            applyFilters: function() {
                const params = new URLSearchParams(window.location.search);
                
                // Update or remove search parameter
                const searchValue = $('#searchInput').val();
                if (searchValue) {
                    params.set('search', searchValue);
                } else {
                    params.delete('search');
                }
                
                // Update other filters
                const category = $('#categoryFilter').val();
                if (category) params.set('category', category);
                else params.delete('category');
                
                const type = $('#typeFilter').val();
                if (type) params.set('mime_type', type);
                else params.delete('mime_type');
                
                const visibility = $('#visibilityFilter').val();
                if (visibility !== '') params.set('is_public', visibility);
                else params.delete('is_public');
                
                // Reset to page 1
                params.set('page', '1');
                
                // Reload page with new filters
                window.location.href = 'index.php?page=files&' + params.toString();
            },
            
            switchView: function(view) {
                this.currentView = view;
                
                if (view === 'grid') {
                    $('#gridView').show();
                    $('#listView').hide();
                    $('#gridViewBtn').addClass('btn-primary').removeClass('btn-outline-primary');
                    $('#listViewBtn').addClass('btn-outline-secondary').removeClass('btn-primary');
                } else {
                    $('#gridView').hide();
                    $('#listView').show();
                    $('#listViewBtn').addClass('btn-primary').removeClass('btn-outline-primary');
                    $('#gridViewBtn').addClass('btn-outline-primary').removeClass('btn-primary');
                }
            },
            
            toggleSelectAll: function() {
                const isChecked = $('#selectAllCheckbox').is(':checked');
                $('.file-checkbox').prop('checked', isChecked);
                this.updateSelectedCount();
            },
            
            updateSelectedCount: function() {
                this.selectedFiles.clear();
                $('.file-checkbox:checked').each((index, checkbox) => {
                    this.selectedFiles.add($(checkbox).val());
                });
                
                const count = this.selectedFiles.size;
                $('#selectedCount').text(count);
                
                // Show/hide bulk actions
                if (count > 0) {
                    $('#bulkActions').slideDown();
                } else {
                    $('#bulkActions').slideUp();
                }
                
                // Update select all checkbox
                const totalCheckboxes = $('.file-checkbox').length;
                const checkedCheckboxes = $('.file-checkbox:checked').length;
                $('#selectAllCheckbox').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
                $('#selectAllCheckbox').prop('checked', checkedCheckboxes === totalCheckboxes);
            },
            
            clearSelection: function() {
                $('.file-checkbox').prop('checked', false);
                this.updateSelectedCount();
            },
            
            bulkShare: function() {
                // Implement bulk sharing
                Toast.info('Bulk sharing feature coming soon');
            },
            
            bulkDownload: function() {
                // Implement bulk download
                Toast.info('Bulk download feature coming soon');
            },
            
            bulkDelete: function() {
                if (confirm(`Are you sure you want to delete ${this.selectedFiles.size} files?`)) {
                    const data = {
                        operation: 'delete',
                        file_ids: Array.from(this.selectedFiles),
                        csrf_token: this.getCsrfToken()
                    };
                    
                    $.post('index.php?page=files&action=bulkOperations', data)
                        .done((response) => {
                            if (response.status === 'success') {
                                Toast.success('Files deleted successfully');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                Toast.error(response.message || 'Bulk delete failed');
                            }
                        });
                }
            },
            
            copyShareLink: function() {
                const shareLink = $('#shareLink').val();
                navigator.clipboard.writeText(shareLink).then(() => {
                    Toast.success('Share link copied to clipboard');
                });
            },
            
            createShareLink: function() {
                // Implement share link creation
                Toast.info('Share link creation feature coming soon');
            },
            
            getCsrfToken: function() {
                return $('meta[name="csrf-token"]').attr('content') || '';
            },
            
            debounce: function(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        };
        
        // Initialize module
        $(document).ready(function() {
            FileModule.init();
        });
    </script>
</body>
</html>
