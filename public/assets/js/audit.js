/**
 * Audit Logging Module
 * Handles all audit logging operations with comprehensive compliance monitoring
 */

var AuditModule = {
    currentPage: 1,
    currentFilters: {},
    selectedLogs: [],
    
    init: function() {
        this.bindEvents();
        this.loadInitialData();
        this.initializeFilters();
    },
    
    bindEvents: function() {
        // Filter form submission
        $('#audit-filters-form').on('submit', function(e) {
            e.preventDefault();
            AuditModule.applyFilters();
        });
        
        // Log type change
        $('#log-type').on('change', function() {
            AuditModule.applyFilters();
        });
        
        // Entity type change
        $('#entity-type').on('change', function() {
            AuditModule.applyFilters();
        });
        
        // User filter change
        $('#user-filter').on('change', function() {
            AuditModule.applyFilters();
        });
        
        // Date range change
        $('#date-from, #date-to').on('change', function() {
            AuditModule.applyFilters();
        });
        
        // Table entries
        $('#table-entries').on('change', function() {
            AuditModule.applyFilters();
        });
        
        // Select all checkbox
        $('#select-all-logs').on('change', function() {
            AuditModule.toggleSelectAll();
        });
        
        // Individual log checkboxes
        $(document).on('change', '[id^="log-checkbox-"]', function() {
            AuditModule.updateSelectAllCheckbox();
        });
        
        // Refresh button
        $('#refresh-logs-btn').on('click', function() {
            AuditModule.refreshAuditLogs();
        });
        
        // Export buttons
        $('#export-logs-btn').on('click', function() {
            $('#exportModal').modal('show');
        });
        
        $('#export-form').on('submit', function(e) {
            e.preventDefault();
            AuditModule.executeExport();
        });
        
        // Export format buttons
        $('#export-csv, #export-excel, #export-pdf').on('click', function() {
            var format = $(this).data('format');
            $('#export-format').val(format);
            $('#exportModal').modal('show');
        });
        
        // Clear logs button
        $('#clear-logs-btn').on('click', function() {
            AuditModule.clearAuditLogs();
        });
        
        // Compliance report
        $('#generate-compliance-report').on('click', function() {
            AuditModule.generateComplianceReport();
        });
        
        // Log details modal
        $(document).on('click', '[id^="log-details-btn-"]', function() {
            var logId = $(this).attr('id').split('-')[3];
            AuditModule.showLogDetails(logId);
        });
        
        // Modal close events
        $('#exportModal, #logDetailsModal').on('hidden.bs.modal', function() {
            AuditModule.resetForms();
        });
    },
    
    loadInitialData: function() {
        this.loadAuditLogs();
        this.loadAuditStatistics();
    },
    
    initializeFilters: function() {
        // Set default date range (last 30 days)
        var today = new Date();
        var thirtyDaysAgo = new Date(today);
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        $('#date-from').val(thirtyDaysAgo.toISOString().split('T')[0]);
        $('#date-to').val(today.toISOString().split('T')[0]);
        
        // Set default compliance date range (last month)
        var oneMonthAgo = new Date(today);
        oneMonthAgo.setMonth(today.getMonth() - 1);
        
        $('#compliance-date-from').val(oneMonthAgo.toISOString().split('T')[0]);
        $('#compliance-date-to').val(today.toISOString().split('T')[0]);
    },
    
    loadAuditLogs: function() {
        this.showLoading(true);
        
        var filters = {
            page: this.currentPage,
            limit: parseInt($('#table-entries').val()),
            log_type: $('#log-type').val(),
            entity_type: $('#entity-type').val(),
            user_id: $('#user-filter').val(),
            date_from: $('#date-from').val(),
            date_to: $('#date-to').val(),
            search: ''
        };
        
        $.ajax({
            url: BASE_URL + '/index.php?page=audit&action=getAuditLogs',
            type: 'GET',
            data: filters,
            dataType: 'json',
            success: function(response) {
                AuditModule.handleAuditLogsResponse(response);
            },
            error: function(xhr, status, error) {
                AuditModule.showLoading(false);
                AuditModule.showToast('Gagal memuat audit logs: ' + error, 'error');
            }
        });
    },
    
    handleAuditLogsResponse: function(response) {
        this.showLoading(false);
        
        if (response.status !== 'success') {
            AuditModule.showToast(response.message || 'Gagal memuat audit logs', 'error');
            return;
        }
        
        var data = response.data || [];
        var pagination = response.pagination || {};
        
        // Update statistics
        this.updateStatistics(response.statistics || {});
        
        // Update table
        this.updateAuditTable(data);
        
        // Update pagination
        this.updatePagination(pagination);
        
        // Clear selection
        this.selectedLogs = [];
        this.updateSelectAllCheckbox();
        
        AuditModule.showToast('Audit logs berhasil dimuat', 'success');
    },
    
    updateAuditTable: function(logs) {
        var tbody = $('#audit-table-body');
        tbody.empty();
        
        if (!Array.isArray(logs) || logs.length === 0) {
            tbody.append('<tr><td colspan="9" class="text-center text-muted"><i class="bi bi-info-circle me-2"></i>Pilih filter untuk menampilkan data audit logs</td></tr>');
            return;
        }
        
        logs.forEach(function(log) {
            var row = AuditModule.buildLogRow(log);
            tbody.append(row);
        });
    },
    
    buildLogRow: function(log) {
        var row = $('<tr>');
        
        // Checkbox
        row.append('<td><input type="checkbox" class="form-check-input" id="log-checkbox-' + log.id + '" value="' + log.id + '"></td>');
        
        // ID
        row.append('<td>' + log.id + '</td>');
        
        // Timestamp
        row.append('<td><small>' + new Date(log.created_at).toLocaleString('id-ID') + '</small></td>');
        
        // Activity Type
        var activityTypeClass = this.getActivityTypeClass(log.activity_type);
        row.append('<td><span class="badge activity-type-badge ' + activityTypeClass + '">' + this.getActivityTypeLabel(log.activity_type) + '</span></td>');
        
        // Description
        var description = log.description;
        if (log.old_values || log.new_values) {
            description += this.formatChangeDetails(log.old_values, log.new_values);
        }
        row.append('<td>' + this.highlightSearch(description) + '</td>');
        
        // User
        var userName = log.user_name || 'System';
        row.append('<td>' + userName + '</td>');
        
        // Entity
        var entityInfo = this.formatEntityInfo(log.entity_type, log.entity_id);
        row.append('<td>' + entityInfo + '</td>');
        
        // IP Address
        row.append('<td><small>' + (log.ip_address || 'Unknown') + '</small></td>');
        
        // Actions
        var actions = '<div class="d-flex gap-1">';
        actions += '<button class="btn btn-sm btn-outline-primary btn-icon" id="log-details-btn-' + log.id + '" onclick="AuditModule.showLogDetails(' + log.id + ')"><i class="bi bi-eye"></i></button>';
        actions += '</div>';
        row.append('<td>' + actions + '</td>');
        
        return row;
    },
    
    getActivityTypeClass: function(activityType) {
        switch(activityType) {
            case 'error': return 'error';
            case 'warning': return 'warning';
            case 'info': return 'info';
            case 'success': return 'success';
            case 'security': return 'security';
            case 'system': return 'system';
            case 'backup': return 'backup';
            case 'compliance': return 'compliance';
            default: return 'info';
        }
    },
    
    getActivityTypeLabel: function(activityType) {
        var labels = {
            'login_success': 'Login',
            'logout': 'Logout',
            'login_failed': 'Login Failed',
            'password_change': 'Password Change',
            'user_created': 'User Created',
            'user_updated': 'User Updated',
            'user_deleted': 'User Deleted',
            'user_status_toggled': 'Status Changed',
            'settings_updated': 'Settings Updated',
            'feature_updated': 'Feature Updated',
            'backup_created': 'Backup Created',
            'backup_downloaded': 'Backup Downloaded',
            'backup_deleted': 'Backup Deleted',
            'report_generated': 'Report Generated',
            'report_exported': 'Report Exported',
            'data_exported': 'Data Exported',
            'file_uploaded': 'File Uploaded',
            'file_downloaded': 'File Downloaded',
            'file_deleted': 'File Deleted',
            'api_access': 'API Access',
            'api_error': 'API Error',
            'security_breach': 'Security Breach',
            'suspicious_activity': 'Suspicious Activity',
            'compliance_check': 'Compliance Check'
        };
        
        return labels[activityType] || activityType;
    },
    
    formatEntityInfo: function(entityType, entityId) {
        if (!entityType) {
            return '-';
        }
        
        var entityLabels = {
            'user': 'User',
            'company': 'Company',
            'branch': 'Branch',
            'product': 'Product',
            'transaction': 'Transaction',
            'inventory': 'Inventory',
            'report': 'Report',
            'settings': 'Settings',
            'backup': 'Backup',
            'system': 'System'
        };
        
        var label = entityLabels[entityType] || entityType;
        
        if (entityId) {
            label += ' #' + entityId;
        }
        
        return label;
    },
    
    formatChangeDetails: function(oldValues, newValues) {
        if (!oldValues && !newValues) {
            return '';
        }
        
        try {
            var old = JSON.parse(oldValues || '{}');
            var new = JSON.parse(newValues || '{}');
            
            var changes = [];
            
            for (var key in old) {
                if (old[key] !== new[key]) {
                    changes.push(key + ': "' + old[key] + '" → "' + new[key] + '"');
                }
            }
            
            if (changes.length > 0) {
                return '<br><small class="text-muted">Changes: ' + changes.join(', ') + '</small>';
            }
        } catch (e) {
            return '';
        }
        
        return '';
    },
    
    highlightSearch: function(text) {
        var searchTerm = $('#search-input').val();
        if (!searchTerm) {
            return text;
        }
        
        var regex = new RegExp('(' + searchTerm + ')', 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    },
    
    updateStatistics: function(statistics) {
        $('#total-logs').text(statistics.total_logs || 0);
        $('#today-logs').text(statistics.logs_today || 0);
        $('#security-events').text(statistics.security_events || 0);
        $('#system-changes').text(statistics.system_changes || 0);
    },
    
    updatePagination: function(pagination) {
        var currentPage = pagination.current_page || 1;
        var totalPages = pagination.pages || 1;
        var startRecord = (currentPage - 1) * (pagination.per_page || 50) + 1;
        var endRecord = Math.min(startRecord + (pagination.per_page || 50) - 1, pagination.total || 0);
        
        $('#pagination-info').text('Menampilkan ' + startRecord + '-' + endRecord + ' dari ' + pagination.total + ' data');
        
        var paginationHtml = '';
        
        // Previous button
        if (currentPage > 1) {
            paginationHtml += '<li class="page-item"><a class="page-link" href="#" onclick="AuditModule.goToPage(' + (currentPage - 1) + ')" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
        }
        
        // Page numbers
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, currentPage + 2);
        
        for (var i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHtml += '<li class="page-item active"><a class="page-link" href="#" onclick="AuditModule.goToPage(' + i + ')" aria-current="true">' + i + '</a></li>';
            } else {
                paginationHtml += '<li class="page-item"><a class="page-link" href="#" onclick="AuditModule.goToPage(' + i + ')" aria-label="Go to page ' + i + '">' + i + '</a></li>';
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHtml += '<li class="page-item"><a class="page-link" href="#" onclick="AuditModule.goToPage(' + (currentPage + 1) + ')" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        }
        
        $('#pagination').html(paginationHtml);
    },
    
    goToPage: function(page) {
        this.currentPage = page;
        this.loadAuditLogs();
    },
    
    showLogDetails: function(logId) {
        $.ajax({
            url: BASE_URL + '/index.php?page=audit&action=getAuditLogDetails&id=' + logId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    AuditModule.displayLogDetails(response.data);
                    $('#logDetailsModal').modal('show');
                } else {
                    AuditModule.showToast('Gagal memuat detail log: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function() {
                AuditModule.showToast('Gagal memuat detail log', 'error');
            }
        });
    },
    
    displayLogDetails: function(log) {
        var content = '<div class="row">';
        
        // Basic Information
        content += '<div class="col-md-6">';
        content += '<h6>Basic Information</h6>';
        content += '<table class="table table-sm">';
        content += '<tr><th>Log ID:</th><td>' + log.id + '</td></tr>';
        content += '<tr><th>Timestamp:</th><td>' + new Date(log.created_at).toLocaleString('id-ID') + '</td></tr>';
        content += '<tr><th>Activity Type:</th><td><span class="badge activity-type-badge ' + this.getActivityTypeClass(log.activity_type) + '">' + this.getActivityTypeLabel(log.activity_type) + '</span></td></tr>';
        content += '<tr><th>User Agent:</th><td><small>' + (log.user_agent || 'Unknown') + '</small></td></tr>';
        content += '<tr><th>IP Address:</th><td>' + (log.ip_address || 'Unknown') + '</td></tr>';
        content += '<tr><th>Session ID:</th><td><small>' + (log.session_id || 'Unknown') + '</small></td></tr>';
        content += '</table>';
        content += '</div>';
        
        // Entity Information
        content += '<div class="col-md-6">';
        content += '<h6>Entity Information</h6>';
        content += '<table class="table table-sm">';
        content += '<tr><th>Entity Type:</th><td>' + (log.entity_type || 'N/A') + '</td></tr>';
        content += '<tr><th>Entity ID:</th><td>' + (log.entity_id || 'N/A') + '</td></tr>';
        content += '<tr><th>User:</th><td>' + (log.user_name || 'System') + '</td></tr>';
        content += '<tr><th>Company:</th><td>' + (log.company_name || 'N/A') + '</td></tr>';
        content += '<tr><th>Branch:</th><td>' + (log.branch_name || 'N/A') + '</td></tr>';
        content += '</table>';
        content += '</div>';
        
        content += '</div>';
        
        // Description
        content += '<div class="col-12">';
        content += '<h6>Description</h6>';
        content += '<p>' + log.description + '</p>';
        content += '</div>';
        
        // Change Details
        if (log.old_values || log.new_values) {
            content += '<div class="col-12">';
            content += '<h6>Change Details</h6>';
            content += '<div class="row">';
            
            if (log.old_values) {
                content += '<div class="col-md-6">';
                content += '<h6>Old Values:</h6>';
                content += '<pre class="bg-light p-2"><code>' + JSON.stringify(JSON.parse(log.old_values), null, 2) + '</code></pre>';
                content += '</div>';
            }
            
            if (log.new_values) {
                content += '<div class="col-md-6">';
                content += '<h6>New Values:</h6>';
                content += '<pre class="bg-light p-2"><code>' + JSON.stringify(JSON.parse(log.new_values), null, 2) + '</code></pre>';
                content += '</div>';
            }
            
            content += '</div>';
            content += '</div>';
        }
        
        content += '</div>';
        
        $('#log-details-content').html(content);
    },
    
    applyFilters: function() {
        this.currentPage = 1;
        this.currentFilters = {
            log_type: $('#log-type').val(),
            entity_type: $('#entity-type').val(),
            user_id: $('#user-filter').val(),
            date_from: $('#date-from').val(),
            date_to: $('#date-to').val(),
            search: ''
        };
        
        this.loadAuditLogs();
    },
    
    resetFilters: function() {
        $('#audit-filters-form')[0].reset();
        this.initializeFilters();
        this.applyFilters();
    },
    
    clearAuditLogs: function() {
        var filters = {
            date_from: $('#date-from').val(),
            date_to: $('#date-to').val(),
            log_type: $('#log-type').val(),
            entity_type: $('#entity-type').val()
        };
        
        if (!confirm('Apakah Anda yakin ingin menghapus audit logs untuk periode ' + (filters.date_from || 'all') + ' - ' + (filters.date_to || 'all') + '?')) {
            return;
        }
        
        $.ajax({
            url: BASE_URL + '/index.php?page=audit&action=clearAuditLogs',
            type: 'POST',
            data: JSON.stringify(filters),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    AuditModule.showToast('Audit logs berhasil dihapus: ' + response.cleared_count + ' records', 'success');
                    AuditModule.loadAuditLogs();
                } else {
                    AuditModule.showToast('Gagal menghapus audit logs: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                AuditModule.showToast('Gagal menghapus audit logs: ' + error, 'error');
            }
        });
    },
    
    exportAuditLogs: function() {
        $('#exportModal').modal('show');
    },
    
    executeExport: function() {
        var format = $('#export-format').val();
        var includeFilters = $('#include-filters').prop('checked');
        var dateFrom = $('#export-date-from').val();
        var dateTo = $('#export-date-to').val();
        
        var filters = {};
        
        if (includeFilters) {
            filters = this.currentFilters;
        }
        
        if (dateFrom) {
            filters.date_from = dateFrom;
        }
        
        if (dateTo) {
            filters.date_to = dateTo;
        }
        
        this.showLoading(true);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=audit&action=exportAuditLogs&format=' + format,
            type: 'POST',
            data: JSON.stringify(filters),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                AuditModule.showLoading(false);
                
                if (response.status === 'success') {
                    AuditModule.showToast('Export ' + format.toUpperCase() + ' berhasil dibuat', 'success');
                    $('#exportModal').modal('hide');
                    
                    // Download the file
                    if (response.file_url) {
                        window.open(response.file_url, '_blank');
                    }
                } else {
                    AuditModule.showToast('Gagal export: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                AuditModule.showLoading(false);
                AuditModule.showToast('Gagal export: ' + error, 'error');
            }
        });
    },
    
    exportToCSV: function() {
        $('#export-format').val('csv');
        this.executeExport();
    },
    
    exportToExcel: function() {
        $('#export-format').val('excel');
        this.executeExport();
    },
    
    exportToPDF: function() {
        $('#export-format').val('pdf');
        this.executeExport();
    },
    
    generateComplianceReport: function() {
        var reportType = $('#compliance-report-type').val();
        var dateFrom = $('#compliance-date-from').val();
        var dateTo = $('#compliance-date-to').val();
        
        if (!dateFrom || !dateTo) {
            this.showToast('Silakan pilih tanggal untuk compliance report', 'warning');
            return;
        }
        
        this.showLoading(true);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=audit&action=getComplianceReport&report_type=' + reportType + '&date_from=' + dateFrom + '&date_to=' + dateTo,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                AuditModule.showLoading(false);
                
                if (response.status === 'success) {
                    AuditModule.displayComplianceReport(response.data);
                    AuditModule.showToast('Compliance report berhasil dibuat', 'success');
                } else {
                    AuditModule.showToast('Gagal membuat compliance report: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                AuditModule.showLoading(false);
                AuditModule.showToast('Gagal membuat compliance report: ' + error, 'error');
            }
        });
    },
    
    displayComplianceReport: function(report) {
        var content = '<div class="row">';
        
        // Report Summary
        if (report.report_type === 'summary') {
            content += '<div class="col-12">';
            content += '<h5>Compliance Summary Report</h5>';
            content += '<div class="row">';
            content += '<div class="col-md-3"><h6>Total Activities:</h6><p class="text-primary">' + (report.summary.total_activities || 0) + '</p></div>';
            content += '<div class="col-md-3"><h6>Unique Users:</h6><p class="text-info">' + (report.summary.unique_users || 0) + '</p></div>';
            content += '<div class="col-md-3"><h6>Security Events:</h6><p class="text-danger">' + (report.summary.security_events || 0) + '</p></div>';
            content += '<div class="col-md-3"><h6>System Changes:</h6><p class="text-info">' + (report.summary.system_changes || 0) + '</p></div>';
            content += '</div>';
            content += '</div>';
        }
        
        // Detailed Report
        if (report.report_type === 'detailed') {
            content += '<div class="col-12">';
            content += '<h5>Detailed Compliance Report</h5>';
            content += '<div class="table-responsive">';
            content += '<table class="table table-sm">';
            content += '<thead><tr><th>Date</th><th>Activity</th><th>User</th><th>Description</th><th>Entity</th></tr></thead>';
            
            if (report.details && Array.isArray(report.details)) {
                report.details.forEach(function(item) {
                    content += '<tr>';
                    content += '<td>' + new Date(item.created_at).toLocaleString('id-ID') + '</td>';
                    content += '<td><span class="badge activity-type-badge ' + AuditModule.getActivityTypeClass(item.activity_type) + '">' + AuditModule.getActivityTypeLabel(item.activity_type) + '</span></td>';
                    content += '<td>' + (item.user_name || 'System') + '</td>';
                    content += '<td>' + item.description + '</td>';
                    content += '<td>' + AuditModule.formatEntityInfo(item.entity_type, item.entity_id) + '</td>';
                    content += '</tr>';
                });
            }
            
            content += '</table>';
            content += '</div>';
            content += '</div>';
        }
        
        // Security Report
        if (report.report_type === 'security') {
            content += '<div class="col-12">';
            content += '<h5>Security Compliance Report</h5>';
            content += '<div class="row">';
            content += '<div class="col-md-3"><h6>Total Security Events:</h6><p class="text-danger">' + (report.summary.security_events || 0) + '</p></div>';
            content += '<div class="col-md-3"><h6>Failed Logins:</h6><p class="text-warning">' + (report.summary.failed_logins || 0) + '</p></div>';
            content += '<div class="col-md-3"><h6>Successful Logins:</h6><p class="text-success">' + (report.summary.successful_logins || 0) + '</p></div>';
            content += '<div class="col-md-3"><h6>Permission Denied:</h6><p class="text-warning">' + (report.summary.permission_denied || 0) + '</p></div>';
            content += '</div>';
            content += '</div>';
            
            // Security Events Timeline
            if (report.details && Array.isArray(report.details)) {
                content += '<div class="col-12">';
                content += '<h6>Security Events Timeline</h6>';
                content += '<div class="timeline">';
                
                report.details.forEach(function(item) {
                    content += '<div class="timeline-item ' + item.activity_type + '">';
                    content += '<div class="timeline-content">';
                    content += '<small class="text-muted">' + new Date(item.created_at).toLocaleString('id-ID') + '</small>';
                    content += '<strong>' + item.description + '</strong>';
                    content += '<br><small>by ' + (item.user_name || 'System') + '</small>';
                    content += '</div>';
                    content += '</div>';
                });
                
                content += '</div>';
                content += '</div>';
            }
            content += '</div>';
        }
        
        content += '</div>';
        
        $('#compliance-report-content').html(content);
        
        // Show compliance report modal
        $('#complianceReportModal').modal('show');
    },
    
    refreshAuditLogs: function() {
        this.loadAuditLogs();
    },
    
    toggleSelectAll: function() {
        var selectAll = $('#select-all-logs');
        var checkboxes = $('[id^="log-checkbox-"]');
        var isChecked = selectAll.prop('checked');
        
        checkboxes.prop('checked', !isChecked);
        
        this.updateSelectAllCheckbox();
    },
    
    updateSelectAllCheckbox: function() {
        var checkboxes = $('[id^="log-checkbox-"]');
        var allChecked = checkboxes.length > 0 && checkboxes.length === checkboxes.filter(':checked').length;
        
        $('#select-all-logs').prop('checked', allChecked);
        
        // Update selected logs array
        this.selectedLogs = [];
        checkboxes.filter(':checked').each(function() {
            var logId = $(this).val();
            AuditModule.selectedLogs.push(logId);
        });
    },
    
    showLoading: function(show) {
        $('#loading-spinner').toggle(show);
        $('#audit-table').toggle(!show);
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
    }
};

// Auto-initialize when document is ready
$(document).ready(function() {
    AuditModule.init();
});
