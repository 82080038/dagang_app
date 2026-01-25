/**
 * System Monitoring Module
 * Handles all system monitoring operations with real-time metrics and backup management
 */

var MonitoringModule = {
    charts: {},
    refreshInterval: null,
    currentData: {},
    
    init: function() {
        this.bindEvents();
        this.loadInitialData();
        this.initializeCharts();
        this.startAutoRefresh();
    },
    
    bindEvents: function() {
        // Alert filter change
        $('#alert-filter').on('change', function() {
            MonitoringModule.loadAlerts();
        });
        
        // Refresh button
        $('.refresh-btn').on('click', function() {
            MonitoringModule.refreshMonitoring();
        });
        
        // Create backup button
        $('#create-backup-btn').on('click', function() {
            $('#createBackupModal').modal('show');
        });
        
        // Schedule backup button
        $('#schedule-backup-btn').on('click', function() {
            $('#scheduleModal').modal('show');
        });
        
        // Form submissions
        $('#schedule-form').on('submit', function(e) {
            e.preventDefault();
            MonitoringModule.saveSchedule();
        });
        
        $('#create-backup-form').on('submit', function(e) {
            e.preventDefault();
            MonitoringModule.executeCreateBackup();
        });
        
        // Modal close events
        $('#scheduleModal, #createBackupModal').on('hidden.bs.modal', function() {
            MonitoringModule.resetForms();
        });
    },
    
    loadInitialData: function() {
        this.loadSystemHealth();
        this.loadPerformanceMetrics();
        this.loadServicesStatus();
        this.loadBackupStatus();
        this.loadAlerts();
        this.loadBackupHistory();
    },
    
    loadSystemHealth: function() {
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=getSystemHealth',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    MonitoringModule.updateSystemHealth(response.data);
                }
            },
            error: function() {
                console.error('Failed to load system health');
            }
        });
    },
    
    loadPerformanceMetrics: function() {
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=getPerformanceMetrics&time_range=1h',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    MonitoringModule.updatePerformanceMetrics(response.data);
                }
            },
            error: function() {
                console.error('Failed to load performance metrics');
            }
        });
    },
    
    loadServicesStatus: function() {
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=getServiceStatus',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    MonitoringModule.updateServicesStatus(response.data);
                }
            },
            error: function() {
                console.error('Failed to load services status');
            }
        });
    },
    
    loadBackupStatus: function() {
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=getBackupStatus',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    MonitoringModule.updateBackupStatus(response.data);
                }
            },
            error: function() {
                console.error('Failed to load backup status');
            }
        });
    },
    
    loadAlerts: function() {
        var alertType = $('#alert-filter').val();
        
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=getAlerts&status=active&limit=10',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    MonitoringModule.updateAlerts(response.data);
                }
            },
            error: function() {
                console.error('Failed to load alerts');
            }
        });
    },
    
    loadBackupHistory: function() {
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=getBackupSchedules',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    MonitoringModule.updateBackupHistory(response.data);
                }
            },
            error: function() {
                console.error('Failed to load backup history');
            }
        });
    },
    
    updateSystemHealth: function(health) {
        var overallStatus = health.overall_status || 'unknown';
        var indicator = $('#system-health-indicator');
        var statusText = $('#system-health-status');
        
        // Update indicator and status
        indicator.removeClass('healthy warning critical error unknown');
        indicator.addClass(overallStatus);
        
        statusText.text(overallStatus.charAt(0).toUpperCase() + overallStatus.slice(1));
        
        // Update component health cards
        var components = health.components || {};
        var html = '';
        
        for (var component in components) {
            var data = components[component];
            var statusClass = data.status;
            var statusIcon = this.getStatusIcon(data.status);
            
            html += '<div class="service-status ' + statusClass + '">';
            html += '<i class="bi ' + statusIcon + ' me-2"></i>';
            html += '<div class="flex-grow-1">';
            html += '<strong>' + component.charAt(0).toUpperCase() + component.slice(1) + '</strong>';
            html += '<br><small>' + data.message + '</small>';
            html += '</div>';
            html += '<small class="text-muted">' + new Date(data.last_check).toLocaleString('id-ID') + '</small>';
            html += '</div>';
        }
        
        $('#services-status').html(html);
    },
    
    updatePerformanceMetrics: function(metrics) {
        var metricsData = metrics.metrics || {};
        
        // Update CPU metrics
        if (metricsData.cpu_usage) {
            this.updateMetricCard('cpu', metricsData.cpu_usage);
            this.updateChart('cpu-chart', metricsData.cpu_usage.data_points);
        }
        
        // Update Memory metrics
        if (metricsData.memory_usage) {
            this.updateMetricCard('memory', metricsData.memory_usage);
            this.updateChart('memory-chart', metricsData.memory_usage.data_points);
        }
        
        // Update Disk metrics
        if (metricsData.disk_io) {
            this.updateMetricCard('disk', metricsData.disk_io);
            this.updateChart('disk-chart', metricsData.disk_io.data_points);
        }
        
        // Update Response Time metrics
        if (metricsData.response_time) {
            this.updateMetricCard('response', metricsData.response_time);
            this.updateChart('response-chart', metricsData.response_time.data_points);
        }
    },
    
    updateMetricCard: function(metricType, data) {
        var value = data.current || 0;
        var unit = this.getMetricUnit(metricType);
        var change = this.calculateChange(data);
        
        $('#' + metricType + '-usage').text(value + unit);
        
        var changeElement = $('#' + metricType + '-change');
        changeElement.removeClass('positive negative');
        changeElement.addClass(change > 0 ? 'negative' : 'positive');
        changeElement.text((change > 0 ? '↑' : '↓') + ' ' + Math.abs(change) + '%');
    },
    
    updateChart: function(chartId, dataPoints) {
        var ctx = document.getElementById(chartId);
        if (!ctx) return;
        
        // Destroy existing chart if it exists
        if (this.charts[chartId]) {
            this.charts[chartId].destroy();
        }
        
        // Prepare data for Chart.js
        var labels = dataPoints.map(function(point) {
            return new Date(point.timestamp).toLocaleTimeString('id-ID');
        });
        
        var values = dataPoints.map(function(point) {
            return point.value;
        });
        
        // Create new chart
        this.charts[chartId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Usage',
                    data: values,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    },
    
    updateServicesStatus: function(services) {
        var servicesData = services.services || {};
        var html = '';
        
        for (var serviceName in servicesData) {
            var service = servicesData[serviceName];
            var statusClass = service.status;
            var statusIcon = this.getStatusIcon(service.status);
            
            html += '<div class="service-status ' + statusClass + '">';
            html += '<i class="bi ' + statusIcon + ' me-2"></i>';
            html += '<div class="flex-grow-1">';
            html += '<strong>' + service.name + '</strong>';
            html += '<br><small>Uptime: ' + service.uptime + '</small>';
            html += '<br><small>Memory: ' + service.memory_usage + ' | CPU: ' + service.cpu_usage + '</small>';
            html += '</div>';
            html += '</div>';
        }
        
        $('#services-status').html(html);
    },
    
    updateBackupStatus: function(backupStatus) {
        var html = '';
        
        // Last backup info
        var lastBackup = backupStatus.last_backup || {};
        html += '<div class="mb-3">';
        html += '<h6>Last Backup</h6>';
        html += '<div class="d-flex justify-content-between">';
        html += '<span>Type: ' + (lastBackup.type || 'None') + '</span>';
        html += '<span class="badge bg-' + this.getBackupStatusClass(lastBackup.status) + '">' + (lastBackup.status || 'Unknown') + '</span>';
        html += '</div>';
        html += '<small>Size: ' + (lastBackup.size || '0GB') + ' | Duration: ' + (lastBackup.duration || '0 minutes') + '</small>';
        html += '<small>Time: ' + (lastBackup.timestamp ? new Date(lastBackup.timestamp).toLocaleString('id-ID') : 'Never') + '</small>';
        html += '</div>';
        
        // Next scheduled backup
        var nextScheduled = backupStatus.next_scheduled || {};
        html += '<div class="mb-3">';
        html += '<h6>Next Scheduled</h6>';
        html += '<div class="d-flex justify-content-between">';
        html += '<span>Type: ' + (nextScheduled.type || 'None') + '</span>';
        html += '<span>Schedule: ' + (nextScheduled.schedule || 'None') + '</span>';
        html += '</div>';
        html += '<small>Time: ' + (nextScheduled.timestamp ? new Date(nextScheduled.timestamp).toLocaleString('id-ID') : 'Not scheduled') + '</small>';
        html += '</div>';
        
        // Backup history summary
        var history = backupStatus.backup_history || {};
        html += '<div class="mb-3">';
        html += '<h6>Backup History</h6>';
        html += '<div class="row text-center">';
        html += '<div class="col-4"><strong>' + (history.total_backups || 0) + '</strong><br><small>Total</small></div>';
        html += '<div class="col-4"><strong>' + (history.successful_backups || 0) + '</strong><br><small>Success</small></div>';
        html += '<div class="col-4"><strong>' + (history.failed_backups || 0) + '</strong><br><small>Failed</small></div>';
        html += '</div>';
        html += '<small>Total Size: ' + (history.total_size || '0GB') + '</small>';
        html += '</div>';
        
        // Storage status
        var storage = backupStatus.storage || {};
        html += '<div>';
        html += '<h6>Storage Status</h6>';
        html += '<div class="progress mb-2">';
        html += '<div class="progress-bar" role="progressbar" style="width: ' + (storage.usage_percentage || 0) + '%">';
        html += '<span class="progress-bar-text">' + (storage.usage_percentage || 0) + '%</span>';
        html += '</div>';
        html += '</div>';
        html += '<div class="d-flex justify-content-between">';
        html += '<small>Used: ' + (storage.used_space || '0GB') + '</small>';
        html += '<small>Available: ' + (storage.available_space || '0GB') + '</small>';
        html += '</div>';
        html += '</div>';
        
        $('#backup-status').html(html);
    },
    
    updateAlerts: function(alerts) {
        var html = '';
        
        if (!Array.isArray(alerts) || alerts.length === 0) {
            html = '<div class="text-center text-muted">';
            html += '<i class="bi bi-check-circle fs-1"></i>';
            html += '<p>No active alerts</p>';
            html += '</div>';
        } else {
            alerts.forEach(function(alert) {
                var alertClass = alert.severity;
                var alertIcon = this.getAlertIcon(alert.severity);
                
                html += '<div class="alert-item ' + alertClass + '">';
                html += '<div class="d-flex justify-content-between align-items-start">';
                html += '<div class="flex-grow-1">';
                html += '<div class="d-flex align-items-center">';
                html += '<i class="bi ' + alertIcon + ' me-2"></i>';
                html += '<strong>' + alert.alert_type.toUpperCase() + '</strong>';
                html += '</div>';
                html += '<p class="mb-1">' + alert.message + '</p>';
                html += '<small class="text-muted">' + (alert.component || 'System') + ' - ' + new Date(alert.created_at).toLocaleString('id-ID') + '</small>';
                html += '</div>';
                html += '<div class="d-flex gap-1">';
                html += '<button class="btn btn-sm btn-outline-primary" onclick="MonitoringModule.resolveAlert(' + alert.id + ')">Resolve</button>';
                html += '</div>';
                html += '</div>';
            });
        }
        
        $('#alerts-container').html(html);
    },
    
    updateBackupHistory: function(schedules) {
        var html = '';
        
        if (!Array.isArray(schedules) || schedules.length === 0) {
            html = '<tr>';
            html += '<td colspan="8" class="text-center text-muted">';
            html += '<i class="bi bi-info-circle me-2"></i>';
            html += 'No backup schedules configured';
            html += '</td>';
            html += '</tr>';
        } else {
            schedules.forEach(function(schedule) {
                var statusClass = schedule.status || 'unknown';
                var statusIcon = this.getBackupStatusIcon(schedule.status);
                
                html += '<tr>';
                html += '<td>' + schedule.schedule_name + '</td>';
                html += '<td><span class="badge bg-info">' + schedule.backup_type + '</span></td>';
                html += '<td><span class="badge bg-' + this.getBackupStatusClass(schedule.status) + '">' + schedule.status + '</span></td>';
                html += '<td>' + (schedule.file_size || '0') + '</td>';
                html += '<td>' + (schedule.duration || '0') + ' min</td>';
                html += '<td>' + new Date(schedule.created_at).toLocaleString('id-ID') + '</td>';
                html += '<td>';
                html += '<div class="d-flex gap-1">';
                html += '<button class="btn btn-sm btn-outline-primary" onclick="MonitoringModule.downloadBackup(\'' + schedule.backup_id + '\')"><i class="bi bi-download"></i></button>';
                html += '<button class="btn btn-sm btn-outline-danger" onclick="MonitoringModule.deleteBackup(\'' + schedule.backup_id + '\')"><i class="bi bi-trash"></i></button>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
            });
        }
        
        $('#backup-table-body').html(html);
    },
    
    createBackup: function() {
        $('#createBackupModal').modal('show');
    },
    
    showScheduleModal: function() {
        $('#scheduleModal').modal('show');
    },
    
    saveSchedule: function() {
        var formData = {
            schedule_name: $('#schedule-name').val(),
            backup_type: $('#backup-type').val(),
            frequency: $('#frequency').val(),
            backup_path: $('#backup-path').val(),
            compression: $('#compression').prop('checked'),
            encryption: $('#encryption').prop('checked')
        };
        
        // Validate form
        if (!formData.schedule_name) {
            this.showToast('Schedule name is required', 'error');
            return;
        }
        
        this.showLoading(true);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=scheduleBackup',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                MonitoringModule.showLoading(false);
                
                if (response.status === 'success') {
                    MonitoringModule.showToast('Backup schedule created successfully', 'success');
                    $('#scheduleModal').modal('hide');
                    MonitoringModule.loadBackupHistory();
                } else {
                    MonitoringModule.showToast('Failed to create backup schedule: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                MonitoringModule.showLoading(false);
                MonitoringModule.showToast('Failed to create backup schedule: ' + error, 'error');
            }
        });
    },
    
    executeCreateBackup: function() {
        var formData = {
            backup_type: $('#backup-type-create').val(),
            description: $('#backup-description').val(),
            compression: $('#compression-create').prop('checked')
        };
        
        this.showLoading(true);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=createSystemBackup',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                MonitoringModule.showLoading(false);
                
                if (response.status === 'success') {
                    MonitoringModule.showToast('Backup created successfully', 'success');
                    $('#createBackupModal').modal('hide');
                    MonitoringModule.loadBackupStatus();
                    MonitoringModule.loadBackupHistory();
                } else {
                    MonitoringModule.showToast('Failed to create backup: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                MonitoringModule.showLoading(false);
                MonitoringModule.showToast('Failed to create backup: ' + error, 'error');
            }
        });
    },
    
    downloadBackup: function(backupId) {
        window.open(BASE_URL + '/index.php?page=monitoring&action=downloadBackup&backup_id=' + backupId, '_blank');
    },
    
    deleteBackup: function(backupId) {
        if (!confirm('Are you sure you want to delete this backup?')) {
            return;
        }
        
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=deleteBackup',
            type: 'POST',
            data: { backup_id: backupId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    MonitoringModule.showToast('Backup deleted successfully', 'success');
                    MonitoringModule.loadBackupHistory();
                } else {
                    MonitoringModule.showToast('Failed to delete backup: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                MonitoringModule.showToast('Failed to delete backup: ' + error, 'error');
            }
        });
    },
    
    resolveAlert: function(alertId) {
        var resolution = prompt('Enter resolution details:');
        
        if (resolution === null) {
            return;
        }
        
        $.ajax({
            url: BASE_URL + '/index.php?page=monitoring&action=resolveAlert',
            type: 'POST',
            data: { alert_id: alertId, resolution: resolution },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    MonitoringModule.showToast('Alert resolved successfully', 'success');
                    MonitoringModule.loadAlerts();
                } else {
                    MonitoringModule.showToast('Failed to resolve alert: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                MonitoringModule.showToast('Failed to resolve alert: ' + error, 'error');
            }
        });
    },
    
    refreshMonitoring: function() {
        this.loadSystemHealth();
        this.loadPerformanceMetrics();
        this.loadServicesStatus();
        this.loadBackupStatus();
        this.loadAlerts();
        this.loadBackupHistory();
        
        this.showToast('Monitoring data refreshed', 'success');
    },
    
    startAutoRefresh: function() {
        // Auto-refresh every 30 seconds
        this.refreshInterval = setInterval(function() {
            MonitoringModule.refreshMonitoring();
        }, 30000);
    },
    
    stopAutoRefresh: function() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    },
    
    initializeCharts: function() {
        // Initialize empty charts
        this.updateChart('cpu-chart', []);
        this.updateChart('memory-chart', []);
        this.updateChart('disk-chart', []);
        this.updateChart('response-chart', []);
    },
    
    // Helper methods
    getMetricUnit: function(metricType) {
        var units = {
            'cpu': '%',
            'memory': '%',
            'disk': '%',
            'response': 's'
        };
        return units[metricType] || '';
    },
    
    calculateChange: function(data) {
        var current = data.current || 0;
        var average = data.average || 0;
        
        if (average === 0) {
            return 0;
        }
        
        return ((current - average) / average) * 100;
    },
    
    getStatusIcon: function(status) {
        var icons = {
            'healthy': 'bi-check-circle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'critical': 'bi-x-circle-fill',
            'error': 'bi-x-circle-fill',
            'running': 'bi-play-circle-fill',
            'stopped': 'bi-stop-circle-fill',
            'unknown': 'bi-question-circle-fill'
        };
        return icons[status] || 'bi-question-circle';
    },
    
    getAlertIcon: function(severity) {
        var icons = {
            'info': 'bi-info-circle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'critical': 'bi-x-circle-fill',
            'error': 'bi-x-circle-fill'
        };
        return icons[severity] || 'bi-info-circle-fill';
    },
    
    getBackupStatusClass: function(status) {
        var classes = {
            'completed': 'success',
            'in_progress': 'warning',
            'failed': 'danger',
            'cancelled': 'secondary',
            'pending': 'info'
        };
        return classes[status] || 'secondary';
    },
    
    getBackupStatusIcon: function(status) {
        var icons = {
            'completed': 'bi-check-circle',
            'in_progress': 'bi-arrow-clockwise',
            'failed': 'bi-x-circle',
            'cancelled': 'bi-x-circle',
            'pending': 'bi-clock'
        };
        return icons[status] || 'bi-clock';
    },
    
    showLoading: function(show) {
        $('#loading-spinner').toggle(show);
    },
    
    showToast: function(message, type) {
        // Create toast notification
        var toastHtml = '<div class="toast align-items-center text-white bg-' + type + ' border-0" role="alert">' +
            '<div class="d-flex">' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div>' +
            '</div>';
        
        var toastContainer = $('#toast-container');
        if (toastContainer.length === 0) {
            toastContainer = $('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            $('body').append(toastContainer);
        }
        
        var toastElement = $(toastHtml);
        toastContainer.append(toastElement);
        
        var toast = new bootstrap.Toast(toastElement[0]);
        toast.show();
        
        // Auto-remove toast after 5 seconds
        setTimeout(function() {
            toastElement.remove();
        }, 5000);
    },
    
    resetForms: function() {
        $('#schedule-form')[0].reset();
        $('#create-backup-form')[0].reset();
    }
};

// Auto-initialize when document is ready
$(document).ready(function() {
    MonitoringModule.init();
});
