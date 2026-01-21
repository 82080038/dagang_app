/**
 * DOM Manipulation Examples for Perdagangan System
 * Demonstrates jQuery/Ajax DOM manipulation capabilities
 */

// Example 1: Dynamic Content Loading
function loadDashboardStats() {
    Ajax.get('/dashboard/stats', null, function(response) {
        // Update statistics cards with animation
        DOM.updateValue('#total-companies', response.data.total_companies);
        DOM.updateValue('#active-companies', response.data.active_companies);
        DOM.updateValue('#total-branches', response.data.total_branches);
        DOM.updateValue('#open-branches', response.data.open_branches);
        
        // Show success notification
        Notification.success('Dashboard statistics updated');
    });
}

// Example 2: Dynamic Form Loading
function loadModuleForm(moduleId) {
    Ajax.get('/modules/form/' + moduleId, null, function(response) {
        // Replace content with fade effect
        DOM.replace('#module-form-container', response.html, function() {
            // Initialize new form
            initializeForm($('#module-form-container form'));
            
            // Show form with slide effect
            DOM.show('#module-form-container');
        });
    });
}

// Example 3: Table Operations
function refreshTableData() {
    Loading.showGlobal('Loading table data...');
    
    Ajax.get('/api/data', null, function(response) {
        // Clear existing table
        $('#data-table tbody').empty();
        
        // Add new rows with animation
        response.data.forEach(function(item, index) {
            setTimeout(function() {
                var row = createTableRow(item);
                DOM.append('#data-table tbody', row);
            }, index * 100); // Staggered animation
        });
        
        Loading.hideGlobal();
        Notification.success('Table data refreshed');
    });
}

// Example 4: Form Submission with AJAX
function submitModuleForm(formId) {
    var $form = $('#' + formId);
    
    // Validate form
    var validation = Form.validate($form, {
        module_name: { required: true, min: 3, max: 100 },
        module_code: { required: true, min: 2, max: 50 },
        module_type: { required: true }
    });
    
    if (!validation.valid) {
        Notification.error('Please fix the errors in the form');
        return;
    }
    
    // Submit form with AJAX
    Form.submit($form, function(response) {
        // Hide form
        DOM.hide('#module-form-container');
        
        // Show success message
        Notification.success('Module saved successfully');
        
        // Refresh module list
        loadModuleList();
        
        // Reset form
        Form.reset($form);
    }, function(xhr, status, error) {
        Notification.error('Failed to save module: ' + error);
    });
}

// Example 5: Dynamic Module Activation
function toggleModule(moduleId, action) {
    var url = '/modules/' + action + '/' + moduleId;
    
    Ajax.post(url, {}, function(response) {
        // Update module status with animation
        var $moduleCard = $('#module-' + moduleId);
        
        if (action === 'activate') {
            $moduleCard.addClass('active');
            DOM.updateText('#module-status-' + moduleId, 'Active');
            Notification.success('Module activated');
        } else {
            $moduleCard.removeClass('active');
            DOM.updateText('#module-status-' + moduleId, 'Inactive');
            Notification.warning('Module deactivated');
        }
        
        // Update button state
        var $button = $('#module-action-' + moduleId);
        if (action === 'activate') {
            $button.removeClass('btn-success').addClass('btn-warning')
                   .text('Deactivate').data('action', 'deactivate');
        } else {
            $button.removeClass('btn-warning').addClass('btn-success')
                   .text('Activate').data('action', 'activate');
        }
    });
}

// Example 6: Real-time Search
function setupRealTimeSearch() {
    var searchTimer;
    
    $('#search-input').on('input', function() {
        var query = $(this).val();
        
        // Clear previous timer
        clearTimeout(searchTimer);
        
        // Set new timer
        searchTimer = setTimeout(function() {
            if (query.length >= 2) {
                performSearch(query);
            } else {
                // Show all results
                $('.search-result').show();
            }
        }, 300); // Debounce
    });
}

function performSearch(query) {
    Ajax.get('/search', { q: query }, function(response) {
        // Hide all results
        $('.search-result').hide();
        
        // Show matching results
        response.results.forEach(function(result) {
            $('#result-' + result.id).show();
        });
        
        // Update search count
        DOM.updateText('#search-count', response.results.length + ' results found');
    });
}

// Example 7: Dynamic Chart Updates
function updateCharts() {
    Ajax.get('/dashboard/charts', null, function(response) {
        // Update scalability chart
        updateScalabilityChart(response.scalability);
        
        // Update segment chart
        updateSegmentChart(response.segments);
        
        Notification.info('Charts updated');
    });
}

function updateScalabilityChart(data) {
    var ctx = document.getElementById('scalabilityChart').getContext('2d');
    var chart = Chart.getChart(ctx);
    
    chart.data.datasets[0].data = data.data;
    chart.update('active'); // Animate update
}

function updateSegmentChart(data) {
    var ctx = document.getElementById('segmentChart').getContext('2d');
    var chart = Chart.getChart(ctx);
    
    chart.data.datasets[0].data = data.data;
    chart.update('active'); // Animate update
}

// Example 8: Batch Operations
function performBatchOperation(operation) {
    var selectedRows = Table.getSelected('#data-table');
    
    if (selectedRows.length === 0) {
        Notification.warning('Please select at least one item');
        return;
    }
    
    var ids = [];
    selectedRows.each(function() {
        ids.push($(this).data('id'));
    });
    
    Modal.confirm(
        'Batch ' + operation,
        'Are you sure you want to ' + operation + ' ' + ids.length + ' items?',
        function() {
            Ajax.post('/batch/' + operation, { ids: ids }, function(response) {
                // Update UI
                selectedRows.each(function() {
                    var $row = $(this);
                    
                    if (operation === 'delete') {
                        DOM.remove($row);
                    } else if (operation === 'activate') {
                        $row.find('.status-badge').removeClass('bg-danger').addClass('bg-success')
                              .text('Active');
                    } else if (operation === 'deactivate') {
                        $row.find('.status-badge').removeClass('bg-success').addClass('bg-danger')
                              .text('Inactive');
                    }
                });
                
                Notification.success('Batch operation completed');
            });
        }
    );
}

// Example 9: File Upload with Progress
function uploadFile(fileInput) {
    var file = fileInput.files[0];
    
    if (!file) {
        return;
    }
    
    var formData = new FormData();
    formData.append('file', file);
    
    // Show progress bar
    var $progressBar = $('#upload-progress');
    $progressBar.show();
    
    $.ajax({
        url: '/upload',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            
            // Upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var percent = Math.round((e.loaded / e.total) * 100);
                    $progressBar.find('.progress-bar').css('width', percent + '%')
                                         .text(percent + '%');
                }
            });
            
            return xhr;
        },
        success: function(response) {
            // Hide progress bar
            $progressBar.hide();
            
            // Show success
            Notification.success('File uploaded successfully');
            
            // Update file list
            DOM.append('#file-list', createFileItem(response.file));
        },
        error: function(xhr, status, error) {
            $progressBar.hide();
            Notification.error('Upload failed: ' + error);
        }
    });
}

// Example 10: Real-time Notifications
function setupRealTimeNotifications() {
    // Poll for notifications every 30 seconds
    setInterval(function() {
        Ajax.get('/notifications', null, function(response) {
            response.notifications.forEach(function(notification) {
                showRealTimeNotification(notification);
            });
        });
    }, 30000);
}

function showRealTimeNotification(notification) {
    var $notification = $(`
        <div class="notification-item real-time">
            <div class="notification-icon">
                <i class="${getNotificationIcon(notification.type)}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${notification.time}</div>
            </div>
            <button class="notification-close">&times;</button>
        </div>
    `);
    
    // Add to notification list
    DOM.prepend('#notification-list', $notification);
    
    // Auto-hide after 10 seconds
    setTimeout(function() {
        DOM.remove($notification);
    }, 10000);
    
    // Play sound (optional)
    if (notification.sound) {
        playNotificationSound();
    }
}

// Helper Functions
function createTableRow(item) {
    return `
        <tr class="search-result" id="result-${item.id}">
            <td>${item.name}</td>
            <td>${item.type}</td>
            <td>${item.status}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-btn" data-id="${item.id}">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </td>
        </tr>
    `;
}

function createFileItem(file) {
    return `
        <div class="file-item" id="file-${file.id}">
            <div class="file-icon">
                <i class="bi bi-file-earmark"></i>
            </div>
            <div class="file-info">
                <div class="file-name">${file.name}</div>
                <div class="file-size">${file.size}</div>
            </div>
            <div class="file-actions">
                <button class="btn btn-sm btn-outline-primary download-btn" data-id="${file.id}">
                    <i class="bi bi-download"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${file.id}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
}

function getNotificationIcon(type) {
    const icons = {
        success: 'bi bi-check-circle text-success',
        error: 'bi bi-x-circle text-danger',
        warning: 'bi bi-exclamation-triangle text-warning',
        info: 'bi bi-info-circle text-info'
    };
    
    return icons[type] || icons.info;
}

function playNotificationSound() {
    // Create audio element and play
    var audio = new Audio('/assets/sounds/notification.mp3');
    audio.play().catch(function() {
        // Ignore errors if sound can't be played
    });
}

// Initialize all examples when DOM is ready
$(document).ready(function() {
    // Setup real-time search
    setupRealTimeSearch();
    
    // Setup real-time notifications
    setupRealTimeNotifications();
    
    // Initialize tooltips for dynamic content
    $(document).on('DOMNodeInserted', function(e) {
        $(e.target).find('[data-bs-toggle="tooltip"]').each(function() {
            new bootstrap.Tooltip(this);
        });
    });
    
    // Handle dynamic button clicks
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        loadModuleForm(id);
    });
    
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        Modal.confirm(
            'Delete Item',
            'Are you sure you want to delete this item?',
            function() {
                Ajax.delete('/items/' + id, {}, function(response) {
                    DOM.remove('#result-' + id);
                    Notification.success('Item deleted');
                });
            }
        );
    });
    
    // Handle file uploads
    $(document).on('change', '.file-input', function() {
        uploadFile(this);
    });
    
    // Handle batch operations
    $(document).on('click', '.batch-activate', function() {
        performBatchOperation('activate');
    });
    
    $(document).on('click', '.batch-deactivate', function() {
        performBatchOperation('deactivate');
    });
    
    $(document).on('click', '.batch-delete', function() {
        performBatchOperation('delete');
    });
    
    // Handle module toggles
    $(document).on('click', '.module-toggle', function() {
        var moduleId = $(this).data('id');
        var action = $(this).data('action');
        toggleModule(moduleId, action);
    });
    
    // Handle refresh buttons
    $(document).on('click', '.refresh-stats', function() {
        loadDashboardStats();
    });
    
    $(document).on('click', '.refresh-table', function() {
        refreshTableData();
    });
    
    $(document).on('click', '.refresh-charts', function() {
        updateCharts();
    });
    
    // Handle form submissions
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        submitModuleForm($(this).attr('id'));
    });
    
    // Handle search clear
    $(document).on('click', '.search-clear', function() {
        $('#search-input').val('');
        $('.search-result').show();
        DOM.updateText('#search-count', 'All items');
    });
    
    // Handle notification close
    $(document).on('click', '.notification-close', function() {
        DOM.remove($(this).closest('.notification-item'));
    });
    
    // Handle file downloads
    $(document).on('click', '.download-btn', function() {
        var fileId = $(this).data('id');
        window.open('/download/' + fileId, '_blank');
    });
    
    // Handle file deletions
    $(document).on('click', '.delete-btn', function() {
        var fileId = $(this).data('id');
        Modal.confirm(
            'Delete File',
            'Are you sure you want to delete this file?',
            function() {
                Ajax.delete('/files/' + fileId, {}, function(response) {
                    DOM.remove('#file-' + fileId);
                    Notification.success('File deleted');
                });
            }
        );
    });
});
