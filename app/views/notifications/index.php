<?php
// Notification Management Interface
// Part of Phase 3: Advanced Features Development
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Perdagangan System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        .notification-item {
            border-left: 4px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .notification-item.unread {
            border-left-color: #0d6efd;
            background-color: #f8f9ff;
        }
        
        .notification-item.high-priority {
            border-left-color: #dc3545;
        }
        
        .notification-item.urgent-priority {
            border-left-color: #ff6b6b;
        }
        
        .notification-item:hover {
            transform: translateX(2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .notification-icon.email {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .notification-icon.in_app {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .notification-icon.sms {
            background-color: #e8f5e8;
            color: #388e3c;
        }
        
        .notification-icon.push {
            background-color: #fff3e0;
            color: #f57c00;
        }
        
        .notification-time {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .priority-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .notification-actions {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .notification-item:hover .notification-actions {
            opacity: 1;
        }
        
        .preferences-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
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
                <h2><i class="bi bi-bell me-2"></i>Notifications</h2>
                <p class="text-muted">Manage your notifications and preferences</p>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 mb-0"><?= $unreadCount ?></div>
                            <small>Unread</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0"><?= $statistics['overview']['total_notifications'] ?? 0 ?></div>
                            <small>Total</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0"><?= $statistics['overview']['read_count'] ?? 0 ?></div>
                            <small>Read</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" onclick="NotificationModule.markAllAsRead()">
                        <i class="bi bi-check-all me-1"></i>Mark All as Read
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="NotificationModule.refreshNotifications()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="NotificationModule.showPreferences()">
                        <i class="bi bi-gear me-1"></i>Preferences
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="push">Push</option>
                        <option value="in_app">In-App</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="sent">Sent</option>
                        <option value="delivered">Delivered</option>
                        <option value="read">Read</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="unreadOnly">
                        <label class="form-check-label" for="unreadOnly">
                            Unread only
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notifications List -->
        <div class="row">
            <div class="col-12">
                <div id="notificationsContainer">
                    <?php if (empty($result['notifications'])): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash" style="font-size: 3rem; color: #dee2e6;"></i>
                            <h5 class="mt-3">No notifications</h5>
                            <p class="text-muted">You don't have any notifications yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($result['notifications'] as $notification): ?>
                            <div class="notification-item <?= $notification['read_at'] ? '' : 'unread' ?> <?= $notification['priority'] ?>-priority mb-3 p-3 bg-white rounded shadow-sm" 
                                 data-notification-id="<?= $notification['id_notification'] ?>">
                                <div class="d-flex align-items-start">
                                    <div class="notification-icon <?= $notification['notification_type'] ?> me-3">
                                        <i class="bi <?= $this->getNotificationIcon($notification['notification_type']) ?>"></i>
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                                <p class="mb-2"><?= htmlspecialchars($notification['message']) ?></p>
                                                <div class="d-flex align-items-center">
                                                    <span class="notification-time me-3">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?= $this->formatNotificationTime($notification['created_at']) ?>
                                                    </span>
                                                    <span class="priority-badge bg-<?= $this->getPriorityColor($notification['priority']) ?> text-white">
                                                        <?= ucfirst($notification['priority']) ?>
                                                    </span>
                                                    <span class="badge bg-secondary ms-2">
                                                        <?= ucfirst($notification['notification_type']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="notification-actions">
                                                <div class="btn-group btn-group-sm">
                                                    <?php if (!$notification['read_at']): ?>
                                                        <button type="button" class="btn btn-outline-primary" 
                                                                onclick="NotificationModule.markAsRead(<?= $notification['id_notification'] ?>)"
                                                                title="Mark as read">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="NotificationModule.deleteNotification(<?= $notification['id_notification'] ?>)"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($result['pagination']['total_pages'] > 1): ?>
                    <nav aria-label="Notification pagination" class="mt-4">
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
    </div>
    
    <!-- Preferences Modal -->
    <div class="modal fade" id="preferencesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notification Preferences</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="preferencesForm">
                        <div class="preferences-section">
                            <h6>Email Notifications</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="emailEnabled" name="preferences[email][is_enabled]" value="1">
                                        <label class="form-check-label" for="emailEnabled">
                                            Enable email notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" name="preferences[email][frequency]">
                                        <option value="immediate">Immediate</option>
                                        <option value="hourly">Hourly</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="preferences-section">
                            <h6>In-App Notifications</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="inAppEnabled" name="preferences[in_app][is_enabled]" value="1">
                                        <label class="form-check-label" for="inAppEnabled">
                                            Enable in-app notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" name="preferences[in_app][frequency]">
                                        <option value="immediate">Immediate</option>
                                        <option value="hourly">Hourly</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="preferences-section">
                            <h6>SMS Notifications</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="smsEnabled" name="preferences[sms][is_enabled]" value="1">
                                        <label class="form-check-label" for="smsEnabled">
                                            Enable SMS notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" name="preferences[sms][frequency]">
                                        <option value="immediate">Immediate</option>
                                        <option value="hourly">Hourly</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="preferences-section">
                            <h6>Quiet Hours</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" class="form-control" name="preferences[email][quiet_hours_start]">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Time</label>
                                    <input type="time" class="form-control" name="preferences[email][quiet_hours_end]">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="NotificationModule.savePreferences()">Save Preferences</button>
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
        // Notification Management Module
        var NotificationModule = {
            currentPreferences: {},
            
            init: function() {
                this.bindEvents();
                this.loadPreferences();
                this.startAutoRefresh();
            },
            
            bindEvents: function() {
                // Filters
                $('#typeFilter, #statusFilter, #priorityFilter').on('change', this.applyFilters.bind(this));
                $('#unreadOnly').on('change', this.applyFilters.bind(this));
            },
            
            loadPreferences: function() {
                $.ajax({
                    url: window.BASE_URL + '/index.php?page=notifications&action=getPreferences',
                    type: 'GET',
                    success: (response) => {
                        if (response.status === 'success') {
                            this.currentPreferences = response.data;
                            this.populatePreferencesForm();
                        }
                    }
                });
            },
            
            populatePreferencesForm: function() {
                // Email preferences
                if (this.currentPreferences.email) {
                    $('#emailEnabled').prop('checked', this.currentPreferences.email.is_enabled == 1);
                    $('select[name="preferences[email][frequency]"]').val(this.currentPreferences.email.frequency);
                    $('input[name="preferences[email][quiet_hours_start]"]').val(this.currentPreferences.email.quiet_hours_start);
                    $('input[name="preferences[email][quiet_hours_end]"]').val(this.currentPreferences.email.quiet_hours_end);
                }
                
                // In-app preferences
                if (this.currentPreferences.in_app) {
                    $('#inAppEnabled').prop('checked', this.currentPreferences.in_app.is_enabled == 1);
                    $('select[name="preferences[in_app][frequency]"]').val(this.currentPreferences.in_app.frequency);
                }
                
                // SMS preferences
                if (this.currentPreferences.sms) {
                    $('#smsEnabled').prop('checked', this.currentPreferences.sms.is_enabled == 1);
                    $('select[name="preferences[sms][frequency]"]').val(this.currentPreferences.sms.frequency);
                }
            },
            
            showPreferences: function() {
                $('#preferencesModal').modal('show');
            },
            
            savePreferences: function() {
                const formData = $('#preferencesForm').serialize();
                
                $.ajax({
                    url: window.BASE_URL + '/index.php?page=notifications&action=updatePreferences',
                    type: 'POST',
                    data: formData + '&csrf_token=' + window.CSRF_TOKEN,
                    success: (response) => {
                        if (response.status === 'success') {
                            Toast.success('Preferences saved successfully');
                            $('#preferencesModal').modal('hide');
                            this.loadPreferences();
                        } else {
                            Toast.error(response.message || 'Failed to save preferences');
                        }
                    },
                    error: () => {
                        Toast.error('Failed to save preferences');
                    }
                });
            },
            
            markAsRead: function(notificationId) {
                $.ajax({
                    url: window.BASE_URL + '/index.php?page=notifications&action=markAsRead',
                    type: 'POST',
                    data: {
                        notification_id: notificationId,
                        csrf_token: window.CSRF_TOKEN
                    },
                    success: (response) => {
                        if (response.status === 'success') {
                            // Update UI
                            const notificationElement = $('[data-notification-id="' + notificationId + '"]');
                            notificationElement.removeClass('unread');
                            notificationElement.find('.notification-actions .btn-outline-primary').remove();
                            
                            // Update unread count
                            this.updateUnreadCount(response.data.unread_count);
                            
                            Toast.success('Notification marked as read');
                        } else {
                            Toast.error(response.message || 'Failed to mark as read');
                        }
                    },
                    error: () => {
                        Toast.error('Failed to mark as read');
                    }
                });
            },
            
            markAllAsRead: function() {
                if (!confirm('Mark all notifications as read?')) {
                    return;
                }
                
                $.ajax({
                    url: window.BASE_URL + '/index.php?page=notifications&action=markAllAsRead',
                    type: 'POST',
                    data: {
                        csrf_token: window.CSRF_TOKEN
                    },
                    success: (response) => {
                        if (response.status === 'success') {
                            // Update UI
                            $('.notification-item').removeClass('unread');
                            $('.notification-actions .btn-outline-primary').remove();
                            
                            // Update unread count
                            this.updateUnreadCount(0);
                            
                            Toast.success('All notifications marked as read');
                        } else {
                            Toast.error(response.message || 'Failed to mark all as read');
                        }
                    },
                    error: () => {
                        Toast.error('Failed to mark all as read');
                    }
                });
            },
            
            deleteNotification: function(notificationId) {
                if (!confirm('Delete this notification?')) {
                    return;
                }
                
                $.ajax({
                    url: window.BASE_URL + '/index.php?page=notifications&action=delete',
                    type: 'POST',
                    data: {
                        notification_id: notificationId,
                        csrf_token: window.CSRF_TOKEN
                    },
                    success: (response) => {
                        if (response.status === 'success') {
                            // Remove from UI
                            $('[data-notification-id="' + notificationId + '"]').fadeOut(300, function() {
                                $(this).remove();
                            });
                            
                            // Update unread count
                            this.updateUnreadCount(response.data.unread_count);
                            
                            Toast.success('Notification deleted');
                        } else {
                            Toast.error(response.message || 'Failed to delete notification');
                        }
                    },
                    error: () => {
                        Toast.error('Failed to delete notification');
                    }
                });
            },
            
            refreshNotifications: function() {
                this.showLoading(true);
                
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            },
            
            applyFilters: function() {
                const type = $('#typeFilter').val();
                const status = $('#statusFilter').val();
                const priority = $('#priorityFilter').val();
                const unreadOnly = $('#unreadOnly').is(':checked');
                
                const url = new URL(window.location);
                
                if (type) url.searchParams.set('type', type);
                else url.searchParams.delete('type');
                
                if (status) url.searchParams.set('status', status);
                else url.searchParams.delete('status');
                
                if (priority) url.searchParams.set('priority', priority);
                else url.searchParams.delete('priority');
                
                if (unreadOnly) url.searchParams.set('unread_only', '1');
                else url.searchParams.delete('unread_only');
                
                url.searchParams.delete('page');
                
                window.location.href = url.toString();
            },
            
            updateUnreadCount: function(count) {
                // Update stats card
                $('.stats-card .h4').first().text(count);
                
                // Update navigation badge if exists
                const badge = $('.nav-link[href*="notifications"] .badge');
                if (badge.length) {
                    if (count > 0) {
                        badge.text(count).show();
                    } else {
                        badge.hide();
                    }
                }
            },
            
            showLoading: function(show) {
                if (show) {
                    $('#loadingOverlay').show();
                } else {
                    $('#loadingOverlay').hide();
                }
            },
            
            startAutoRefresh: function() {
                // Refresh unread count every 30 seconds
                setInterval(() => {
                    $.ajax({
                        url: window.BASE_URL + '/index.php?page=notifications&action=getUnreadCount',
                        type: 'GET',
                        success: (response) => {
                            if (response.status === 'success') {
                                this.updateUnreadCount(response.data.unread_count);
                            }
                        }
                    });
                }, 30000);
            }
        };
        
        // Initialize on document ready
        $(document).ready(function() {
            NotificationModule.init();
        });
    </script>
</body>
</html>
