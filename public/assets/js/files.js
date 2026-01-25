/**
 * File Management JavaScript Module
 * 
 * Handles file operations including upload, download, management,
 * and organization with proper error handling and user feedback
 */

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
            
            const files = Array.from(e.dataTransfer.files);
            this.handleFileSelect({ target: { files: files } });
        });
    },
    
    handleFileSelect: function(e) {
        const files = Array.from(e.target.files);
        
        if (files.length === 0) {
            return;
        }
        
        this.uploadedFiles = files;
        this.showUploadForm();
        this.updateUploadPreview();
    },
    
    showUploadForm: function() {
        $('#uploadForm').slideDown();
        $('#uploadArea h5').text('Selected Files (' + this.uploadedFiles.length + ')');
    },
    
    updateUploadPreview: function() {
        let preview = '<div class="mt-3"><h6>Selected Files:</h6><ul class="list-unstyled">';
        
        this.uploadedFiles.forEach(file => {
            preview += '<li class="mb-2">';
            preview += '<i class="bi bi-file-earmark me-2"></i>';
            preview += '<strong>' + file.name + '</strong>';
            preview += '<span class="text-muted ms-2">(' + this.formatFileSize(file.size) + ')</span>';
            preview += '</li>';
        });
        
        preview += '</ul></div>';
        
        // Update or append preview
        if ($('#uploadPreview').length) {
            $('#uploadPreview').html(preview);
        } else {
            $('#uploadForm').append('<div id="uploadPreview">' + preview + '</div>');
        }
    },
    
    cancelUpload: function() {
        this.uploadedFiles = [];
        $('#fileInput').val('');
        $('#uploadForm').slideUp();
        $('#uploadArea h5').text('Upload Files');
        $('#uploadPreview').remove();
    },
    
    uploadFiles: function() {
        if (this.uploadedFiles.length === 0) {
            Toast.error('Please select files to upload');
            return;
        }
        
        const formData = new FormData();
        
        // Add files
        this.uploadedFiles.forEach((file, index) => {
            formData.append('files[' + index + ']', file);
        });
        
        // Add metadata
        formData.append('category', $('#uploadCategory').val());
        formData.append('description', $('#uploadDescription').val());
        formData.append('tags', $('#uploadTags').val());
        formData.append('is_public', $('#uploadPublic').is(':checked') ? '1' : '0');
        formData.append('csrf_token', window.CSRF_TOKEN);
        
        // Show progress
        this.showUploadProgress();
        
        // Upload files
        $.ajax({
            url: window.BASE_URL + '/index.php?page=files&action=upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: () => {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        this.updateUploadProgress(percentComplete);
                    }
                });
                return xhr;
            },
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('Files uploaded successfully');
                    this.cancelUpload();
                    this.refreshFileList();
                } else {
                    Toast.error(response.message || 'Upload failed');
                }
                this.hideUploadProgress();
            },
            error: (xhr, status, error) => {
                Toast.error('Upload failed: ' + error);
                this.hideUploadProgress();
            }
        });
    },
    
    showUploadProgress: function() {
        $('#uploadProgress').slideDown();
        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);
    },
    
    updateUploadProgress: function(percent) {
        $('.progress-bar')
            .css('width', percent + '%')
            .attr('aria-valuenow', percent)
            .text(Math.round(percent) + '%');
    },
    
    hideUploadProgress: function() {
        setTimeout(() => {
            $('#uploadProgress').slideUp();
        }, 1000);
    },
    
    downloadFile: function(fileId) {
        window.open(window.BASE_URL + '/index.php?page=files&action=download&id=' + fileId, '_blank');
    },
    
    shareFile: function(fileId) {
        // Get file details first
        $.ajax({
            url: window.BASE_URL + '/index.php?page=files&action=getFile&id=' + fileId,
            type: 'GET',
            success: (response) => {
                if (response.status === 'success') {
                    this.showShareModal(response.data);
                } else {
                    Toast.error(response.message || 'Failed to get file details');
                }
            },
            error: () => {
                Toast.error('Failed to get file details');
            }
        });
    },
    
    showShareModal: function(file) {
        $('#shareLink').val(window.BASE_URL + '/files/download/' + file.id_file);
        $('#shareModal').modal('show');
    },
    
    createShareLink: function() {
        const shareLink = $('#shareLink').val();
        
        // Copy to clipboard
        navigator.clipboard.writeText(shareLink).then(() => {
            Toast.success('Share link copied to clipboard');
        }).catch(() => {
            // Fallback for older browsers
            this.copyShareLink();
        });
    },
    
    copyShareLink: function() {
        const shareLink = $('#shareLink').val();
        const textArea = document.createElement('textarea');
        textArea.value = shareLink;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        Toast.success('Share link copied to clipboard');
    },
    
    editFile: function(fileId) {
        // Get file details
        $.ajax({
            url: window.BASE_URL + '/index.php?page=files&action=getFile&id=' + fileId,
            type: 'GET',
            success: (response) => {
                if (response.status === 'success') {
                    this.showEditModal(response.data);
                } else {
                    Toast.error(response.message || 'Failed to get file details');
                }
            },
            error: () => {
                Toast.error('Failed to get file details');
            }
        });
    },
    
    showEditModal: function(file) {
        $('#editFileId').val(file.id_file);
        $('#editDescription').val(file.description || '');
        $('#editTags').val(file.tags || '');
        $('#editCategory').val(file.file_category);
        $('#editPublic').prop('checked', file.is_public == 1);
        $('#fileEditModal').modal('show');
    },
    
    saveFileEdit: function() {
        const fileId = $('#editFileId').val();
        const data = {
            file_id: fileId,
            description: $('#editDescription').val(),
            tags: $('#editTags').val(),
            category: $('#editCategory').val(),
            is_public: $('#editPublic').is(':checked') ? '1' : '0',
            csrf_token: window.CSRF_TOKEN
        };
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=files&action=update',
            type: 'POST',
            data: data,
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('File updated successfully');
                    $('#fileEditModal').modal('hide');
                    this.refreshFileList();
                } else {
                    Toast.error(response.message || 'Update failed');
                }
            },
            error: () => {
                Toast.error('Update failed');
            }
        });
    },
    
    deleteFile: function(fileId) {
        if (!confirm('Are you sure you want to delete this file?')) {
            return;
        }
        
        const data = {
            file_id: fileId,
            csrf_token: window.CSRF_TOKEN
        };
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=files&action=delete',
            type: 'POST',
            data: data,
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('File deleted successfully');
                    this.refreshFileList();
                } else {
                    Toast.error(response.message || 'Delete failed');
                }
            },
            error: () => {
                Toast.error('Delete failed');
            }
        });
    },
    
    searchFiles: function() {
        const query = $('#searchInput').val().trim();
        
        if (query === '') {
            this.applyFilters();
            return;
        }
        
        const url = new URL(window.location);
        url.searchParams.set('q', query);
        url.searchParams.delete('page');
        
        window.location.href = url.toString();
    },
    
    applyFilters: function() {
        const category = $('#categoryFilter').val();
        const type = $('#typeFilter').val();
        const visibility = $('#visibilityFilter').val();
        
        const url = new URL(window.location);
        
        if (category) url.searchParams.set('category', category);
        else url.searchParams.delete('category');
        
        if (type) url.searchParams.set('mime_type', type);
        else url.searchParams.delete('mime_type');
        
        if (visibility) url.searchParams.set('is_public', visibility);
        else url.searchParams.delete('is_public');
        
        url.searchParams.delete('page');
        
        window.location.href = url.toString();
    },
    
    switchView: function(view) {
        this.currentView = view;
        
        if (view === 'grid') {
            $('#gridView').show();
            $('#listView').hide();
            $('#gridViewBtn').addClass('active');
            $('#listViewBtn').removeClass('active');
        } else {
            $('#gridView').hide();
            $('#listView').show();
            $('#gridViewBtn').removeClass('active');
            $('#listViewBtn').addClass('active');
        }
    },
    
    toggleSelectAll: function() {
        const isChecked = $('#selectAllCheckbox').is(':checked');
        $('.file-checkbox').prop('checked', isChecked);
        this.updateSelectedCount();
    },
    
    updateSelectedCount: function() {
        const selectedCount = $('.file-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
        
        // Show/hide bulk actions
        if (selectedCount > 0) {
            $('#bulkActions').slideDown();
        } else {
            $('#bulkActions').slideUp();
        }
        
        // Update select all checkbox state
        const totalCheckboxes = $('.file-checkbox').length;
        $('#selectAllCheckbox').prop('indeterminate', selectedCount > 0 && selectedCount < totalCheckboxes);
        $('#selectAllCheckbox').prop('checked', selectedCount === totalCheckboxes);
    },
    
    getSelectedFileIds: function() {
        const fileIds = [];
        $('.file-checkbox:checked').each(function() {
            fileIds.push($(this).val());
        });
        return fileIds;
    },
    
    clearSelection: function() {
        $('.file-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        this.updateSelectedCount();
    },
    
    bulkShare: function() {
        const fileIds = this.getSelectedFileIds();
        if (fileIds.length === 0) {
            Toast.warning('Please select files to share');
            return;
        }
        
        // For now, just show first file share modal
        this.shareFile(fileIds[0]);
    },
    
    bulkDownload: function() {
        const fileIds = this.getSelectedFileIds();
        if (fileIds.length === 0) {
            Toast.warning('Please select files to download');
            return;
        }
        
        // Download files one by one
        fileIds.forEach(fileId => {
            this.downloadFile(fileId);
        });
    },
    
    bulkDelete: function() {
        const fileIds = this.getSelectedFileIds();
        if (fileIds.length === 0) {
            Toast.warning('Please select files to delete');
            return;
        }
        
        if (!confirm('Are you sure you want to delete ' + fileIds.length + ' files?')) {
            return;
        }
        
        const data = {
            operation: 'delete',
            file_ids: fileIds,
            csrf_token: window.CSRF_TOKEN
        };
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=files&action=bulkOperations',
            type: 'POST',
            data: data,
            success: (response) => {
                if (response.status === 'success' || response.status === 'partial') {
                    Toast.success(response.message);
                    this.refreshFileList();
                    this.clearSelection();
                } else {
                    Toast.error(response.message || 'Bulk operation failed');
                }
            },
            error: () => {
                Toast.error('Bulk operation failed');
            }
        });
    },
    
    refreshFileList: function() {
        // Reload the page to refresh file list
        window.location.reload();
    },
    
    formatFileSize: function(bytes) {
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        bytes = Math.max(bytes, 0);
        const powVal = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
        const pow = Math.min(powVal, units.length - 1);
        
        bytes /= Math.pow(1024, pow);
        
        return bytes.toFixed(2) + ' ' + units[pow];
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

// Initialize on document ready
$(document).ready(function() {
    FileModule.init();
});
