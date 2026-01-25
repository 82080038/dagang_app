<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-network-wired me-2"></i>
                    WebSocket Management
                </h1>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" id="websocket-refresh-btn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-outline-success" id="websocket-status-btn">
                        <i class="fas fa-circle"></i> Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- WebSocket Status Card -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="websocket-active-connections">0</h4>
                            <p class="card-text">Active Connections</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="websocket-online-users">0</h4>
                            <p class="card-text">Online Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="websocket-messages-today">0</h4>
                            <p class="card-text">Messages Today</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="websocket-active-channels">0</h4>
                            <p class="card-text">Active Channels</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-broadcast-tower fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WebSocket Configuration and Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>
                        WebSocket Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <form id="websocket-config-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="websocket-enabled" class="form-label">Enable WebSocket</label>
                                <select class="form-select" id="websocket-enabled" name="websocket_enabled">
                                    <option value="true" <?php echo $settings['websocket_enabled'] ? 'selected' : ''; ?>>Enabled</option>
                                    <option value="false" <?php echo !$settings['websocket_enabled'] ? 'selected' : ''; ?>>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="websocket-port" class="form-label">Port</label>
                                <input type="number" class="form-control" id="websocket-port" name="websocket_port" 
                                       value="<?php echo $settings['websocket_port']; ?>" min="1" max="65535">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max-connections" class="form-label">Max Connections/User</label>
                                <input type="number" class="form-control" id="max-connections" name="max_connections_per_user" 
                                       value="<?php echo $settings['max_connections_per_user']; ?>" min="1" max="50">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="connection-timeout" class="form-label">Connection Timeout (seconds)</label>
                                <input type="number" class="form-control" id="connection-timeout" name="connection_timeout" 
                                       value="<?php echo $settings['connection_timeout']; ?>" min="60" max="3600">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="message-retention" class="form-label">Message Retention (days)</label>
                                <input type="number" class="form-control" id="message-retention" name="message_retention_days" 
                                       value="<?php echo $settings['message_retention_days']; ?>" min="1" max="365">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max-messages-minute" class="form-label">Max Messages/Minute</label>
                                <input type="number" class="form-control" id="max-messages-minute" name="max_messages_per_minute" 
                                       value="<?php echo $settings['max_messages_per_minute']; ?>" min="1" max="100">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-file-sharing" name="enable_file_sharing" 
                                           <?php echo $settings['enable_file_sharing'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-file-sharing">
                                        Enable File Sharing
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-private-messages" name="enable_private_messages" 
                                           <?php echo $settings['enable_private_messages'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-private-messages">
                                        Enable Private Messages
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-presence-tracking" name="enable_presence_tracking" 
                                           <?php echo $settings['enable_presence_tracking'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-presence-tracking">
                                        Enable Presence Tracking
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="auto-cleanup-connections" name="auto_cleanup_connections" 
                                           <?php echo $settings['auto_cleanup_connections'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto-cleanup-connections">
                                        Auto Cleanup Connections
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-paper-plane me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#testNotificationModal">
                                <i class="fas fa-bell me-2"></i>Send Test Notification
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                                <i class="fas fa-broadcast-tower me-2"></i>Broadcast Message
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="button" class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#systemAlertModal">
                                <i class="fas fa-exclamation-triangle me-2"></i>Send System Alert
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="button" class="btn btn-outline-info w-100" id="cleanup-btn">
                                <i class="fas fa-broom me-2"></i>Cleanup Old Data
                            </button>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>WebSocket Status</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Server Status:</span>
                        <span class="badge bg-<?php echo $config['enabled'] ? 'success' : 'danger'; ?>" id="websocket-server-status">
                            <?php echo $config['enabled'] ? 'Online' : 'Offline'; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Host:</span>
                        <span><?php echo $config['websocket_host']; ?>:<?php echo $config['websocket_port']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Last Update:</span>
                        <span id="websocket-last-update">Never</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Connections -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Active Connections
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="websocket-connections-table">
                            <thead>
                                <tr>
                                    <th>Connection ID</th>
                                    <th>User</th>
                                    <th>Company</th>
                                    <th>Branch</th>
                                    <th>IP Address</th>
                                    <th>Type</th>
                                    <th>Connected</th>
                                    <th>Last Activity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($active_connections)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No active connections</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($active_connections as $connection): ?>
                                    <tr>
                                        <td><code><?php echo substr($connection['connection_id'], 0, 8); ?>...</code></td>
                                        <td>
                                            <?php echo htmlspecialchars($connection['member_name'] ?? 'Unknown'); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($connection['email'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($connection['company_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($connection['branch_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($connection['ip_address']); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($connection['connection_type']); ?></span></td>
                                        <td><?php echo date('M d, H:i', strtotime($connection['connected_at'])); ?></td>
                                        <td><?php echo date('M d, H:i', strtotime($connection['last_activity'])); ?></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Channels and Recent Events -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-broadcast-tower me-2"></i>
                        Active Channels
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Channel Name</th>
                                    <th>Type</th>
                                    <th>Subscribers</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($channels)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No active channels</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($channels as $channel): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($channel['channel_name']); ?></code></td>
                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($channel['channel_type']); ?></span></td>
                                        <td><?php echo $channel['subscriber_count'] ?? 0; ?></td>
                                        <td><?php echo htmlspecialchars($channel['description'] ?? ''); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Events
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_events)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent events</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($recent_events, 0, 10) as $event): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($event['event_type']); ?></span></td>
                                        <td><?php echo htmlspecialchars($event['source_user_name'] ?? 'System'); ?></td>
                                        <td><?php echo date('H:i:s', strtotime($event['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Notification Modal -->
<div class="modal fade" id="testNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="test-notification-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="test-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="test-title" name="title" value="Test Notification" required>
                    </div>
                    <div class="mb-3">
                        <label for="test-message" class="form-label">Message</label>
                        <textarea class="form-control" id="test-message" name="message" rows="3" required>This is a test notification from the WebSocket system</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="test-type" class="form-label">Type</label>
                        <select class="form-select" id="test-type" name="type">
                            <option value="info">Info</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="test-channel" class="form-label">Channel</label>
                        <select class="form-select" id="test-channel" name="channel">
                            <option value="notifications">notifications</option>
                            <option value="global">global</option>
                            <option value="system-alerts">system-alerts</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Broadcast Message Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Broadcast Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="broadcast-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="broadcast-channel" class="form-label">Channel</label>
                        <select class="form-select" id="broadcast-channel" name="channel" required>
                            <option value="">Select Channel</option>
                            <option value="global">global</option>
                            <option value="notifications">notifications</option>
                            <option value="system-alerts">system-alerts</option>
                            <option value="file-uploads">file-uploads</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="broadcast-message" class="form-label">Message</label>
                        <textarea class="form-control" id="broadcast-message" name="message" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="broadcast-priority" class="form-label">Priority</label>
                        <select class="form-select" id="broadcast-priority" name="priority">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Broadcast Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- System Alert Modal -->
<div class="modal fade" id="systemAlertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send System Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="system-alert-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="alert-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="alert-title" name="title" value="System Alert" required>
                    </div>
                    <div class="mb-3">
                        <label for="alert-message" class="form-label">Message</label>
                        <textarea class="form-control" id="alert-message" name="message" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="alert-level" class="form-label">Alert Level</label>
                        <select class="form-select" id="alert-level" name="level">
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Send Alert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cleanup Old Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cleanup-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cleanup-type" class="form-label">Cleanup Type</label>
                        <select class="form-select" id="cleanup-type" name="cleanup_type">
                            <option value="all">All</option>
                            <option value="connections">Inactive Connections</option>
                            <option value="messages">Old Messages</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message-retention-days" class="form-label">Message Retention (days)</label>
                        <input type="number" class="form-control" id="message-retention-days" name="message_retention_days" value="30" min="1" max="365">
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action will permanently delete old data. Please confirm before proceeding.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Cleanup Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var WebSocketModule = {
    init: function() {
        this.bindEvents();
        this.loadStatistics();
        this.startAutoRefresh();
    },
    
    bindEvents: function() {
        // Configuration form
        $('#websocket-config-form').on('submit', this.handleConfigSave);
        
        // Test notification form
        $('#test-notification-form').on('submit', this.handleTestNotification);
        
        // Broadcast form
        $('#broadcast-form').on('submit', this.handleBroadcast);
        
        // System alert form
        $('#system-alert-form').on('submit', this.handleSystemAlert);
        
        // Cleanup form
        $('#cleanup-form').on('submit', this.handleCleanup);
        
        // Buttons
        $('#websocket-refresh-btn').on('click', this.loadStatistics);
        $('#websocket-status-btn').on('click', this.showStatus);
        $('#cleanup-btn').on('click', () => {
            $('#cleanupModal').modal('show');
        });
    },
    
    loadStatistics: function() {
        $.ajax({
            url: window.BASE_URL + '/index.php?page=websocket&action=getStatus',
            type: 'GET',
            success: (response) => {
                if (response.status === 'success') {
                    this.updateStatistics(response.data);
                    this.updateLastUpdate();
                }
            },
            error: () => {
                Toast.error('Failed to load WebSocket statistics');
            }
        });
    },
    
    updateStatistics: function(data) {
        $('#websocket-active-connections').text(data.active_connections || 0);
        $('#websocket-online-users').text(data.online_users ? data.online_users.length : 0);
        $('#websocket-messages-today').text(data.statistics.messages_today || 0);
        $('#websocket-active-channels').text(data.statistics.active_channels || 0);
        
        // Update server status
        const statusBadge = $('#websocket-server-status');
        if (data.enabled) {
            statusBadge.removeClass('bg-danger').addClass('bg-success').text('Online');
        } else {
            statusBadge.removeClass('bg-success').addClass('bg-danger').text('Offline');
        }
    },
    
    updateLastUpdate: function() {
        $('#websocket-last-update').text(new Date().toLocaleTimeString());
    },
    
    handleConfigSave: function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const config = {};
        
        // Convert form data to config object
        for (let [key, value] of formData.entries()) {
            if (key.includes('enable_')) {
                config[key] = formData.has(key);
            } else {
                config[key] = value;
            }
        }
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=websocket&action=updateConfig',
            type: 'POST',
            data: {
                config: config,
                csrf_token: window.CSRF_TOKEN
            },
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('WebSocket configuration updated successfully');
                    this.loadStatistics();
                } else {
                    Toast.error(response.message || 'Failed to update configuration');
                }
            }.bind(this),
            error: () => {
                Toast.error('Failed to update configuration');
            }
        });
    },
    
    handleTestNotification: function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=websocket&action=sendTestNotification',
            type: 'POST',
            data: {
                title: formData.get('title'),
                message: formData.get('message'),
                type: formData.get('type'),
                channel: formData.get('channel'),
                csrf_token: window.CSRF_TOKEN
            },
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('Test notification sent successfully');
                    $('#testNotificationModal').modal('hide');
                    e.target.reset();
                } else {
                    Toast.error(response.message || 'Failed to send test notification');
                }
            },
            error: () => {
                Toast.error('Failed to send test notification');
            }
        });
    },
    
    handleBroadcast: function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=websocket&action=broadcast',
            type: 'POST',
            data: {
                channel: formData.get('channel'),
                message: formData.get('message'),
                priority: formData.get('priority'),
                csrf_token: window.CSRF_TOKEN
            },
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('Message broadcasted successfully');
                    $('#broadcastModal').modal('hide');
                    e.target.reset();
                } else {
                    Toast.error(response.message || 'Failed to broadcast message');
                }
            },
            error: () => {
                Toast.error('Failed to broadcast message');
            }
        });
    },
    
    handleSystemAlert: function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=websocket&action=sendSystemAlert',
            type: 'POST',
            data: {
                title: formData.get('title'),
                message: formData.get('message'),
                level: formData.get('level'),
                csrf_token: window.CSRF_TOKEN
            },
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('System alert sent successfully');
                    $('#systemAlertModal').modal('hide');
                    e.target.reset();
                } else {
                    Toast.error(response.message || 'Failed to send system alert');
                }
            },
            error: () => {
                Toast.error('Failed to send system alert');
            }
        });
    },
    
    handleCleanup: function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        if (!confirm('Are you sure you want to cleanup old data? This action cannot be undone.')) {
            return;
        }
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=websocket&action=cleanup',
            type: 'POST',
            data: {
                cleanup_type: formData.get('cleanup_type'),
                message_retention_days: formData.get('message_retention_days'),
                csrf_token: window.CSRF_TOKEN
            },
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('Cleanup completed successfully');
                    $('#cleanupModal').modal('hide');
                    this.loadStatistics();
                    
                    // Show cleanup results
                    const results = response.data;
                    let message = 'Cleanup completed:\n';
                    for (const [key, value] of Object.entries(results)) {
                        message += `${key}: ${value}\n`;
                    }
                    alert(message);
                } else {
                    Toast.error(response.message || 'Failed to cleanup data');
                }
            }.bind(this),
            error: () => {
                Toast.error('Failed to cleanup data');
            }
        });
    },
    
    showStatus: function() {
        $.ajax({
            url: window.BASE_URL + '/index.php?page=websocket&action=getStatus',
            type: 'GET',
            success: (response) => {
                if (response.status === 'success') {
                    let statusHtml = '<h6>WebSocket Status</h6>';
                    statusHtml += '<p><strong>Enabled:</strong> ' + (response.data.enabled ? 'Yes' : 'No') + '</p>';
                    statusHtml += '<p><strong>Host:</strong> ' + response.data.config.websocket_host + ':' + response.data.config.websocket_port + '</p>';
                    statusHtml += '<p><strong>Active Connections:</strong> ' + response.data.active_connections + '</p>';
                    statusHtml += '<p><strong>Online Users:</strong> ' + (response.data.online_users ? response.data.online_users.length : 0) + '</p>';
                    statusHtml += '<p><strong>Messages Today:</strong> ' + response.data.statistics.messages_today + '</p>';
                    
                    alert(statusHtml);
                }
            },
            error: () => {
                Toast.error('Failed to get WebSocket status');
            }
        });
    },
    
    startAutoRefresh: function() {
        // Refresh statistics every 30 seconds
        setInterval(() => {
            this.loadStatistics();
        }, 30000);
    }
};

// Initialize on document ready
$(document).ready(function() {
    WebSocketModule.init();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
