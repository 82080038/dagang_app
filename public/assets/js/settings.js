/**
 * Settings Management Module
 * Handles all system settings operations with comprehensive configuration management
 */

var SettingsModule = {
    currentSettings: {},
    originalSettings: {},
    
    init: function() {
        this.bindEvents();
        this.loadSettings();
        this.initializeTabs();
    },
    
    bindEvents: function() {
        // Settings forms
        $('#settings-general-form').on('submit', function(e) {
            e.preventDefault();
            SettingsModule.saveGeneralSettings();
        });
        
        $('#settings-security-form').on('submit', function(e) {
            e.preventDefault();
            SettingsModule.saveSecuritySettings();
        });
        
        $('#settings-email-form').on('submit', function(e) {
            e.preventDefault();
            SettingsModule.saveEmailSettings();
        });
        
        $('#settings-backup-form').on('submit', function(e) {
            e.preventDefault();
            SettingsModule.saveBackupSettings();
        });
        
        $('#features-form').on('submit', function(e) {
            e.preventDefault();
            SettingsModule.saveFeatureSettings();
        });
        
        // Log type change
        $('#settings-log-type-select').on('change', function() {
            SettingsModule.loadSystemLogs();
        });
        
        // Tab change events
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr('data-bs-target');
            SettingsModule.onTabChange(target);
        });
        
        // Feature toggles
        $('.feature-toggle input[id^="settings-"]').on('change', function() {
            var feature = $(this).attr('id').replace('settings-', '').replace('-checkbox', '');
            var enabled = $(this).prop('checked');
            SettingsModule.onFeatureToggle(feature, enabled);
        });
    },
    
    initializeTabs: function() {
        // Initialize Bootstrap tabs
        var triggerTabList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tab"]'));
        var tabList = triggerTabList.map(function(tabTriggerEl) {
            return new bootstrap.Tab(tabTriggerEl);
        });
    },
    
    loadSettings: function() {
        this.showLoading(true);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=getSettings',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                SettingsModule.handleSettingsResponse(response);
            },
            error: function(xhr, status, error) {
                SettingsModule.showLoading(false);
                SettingsModule.showToast('Gagal memuat pengaturan: ' + error, 'error');
            }
        });
    },
    
    handleSettingsResponse: function(response) {
        this.showLoading(false);
        
        if (response.status !== 'success') {
            SettingsModule.showToast(response.message || 'Gagal memuat pengaturan', 'error');
            return;
        }
        
        this.currentSettings = response.data || {};
        this.originalSettings = JSON.parse(JSON.stringify(this.currentSettings));
        
        // Populate forms with current settings
        this.populateGeneralSettings();
        this.populateSecuritySettings();
        this.populateEmailSettings();
        this.populateBackupSettings();
        this.populateFeatureSettings();
        
        // Load system information
        this.loadSystemInfo();
        
        // Load backup history
        this.loadBackupHistory();
        
        // Load system logs
        this.loadSystemLogs();
        
        SettingsModule.showToast('Pengaturan berhasil dimuat', 'success');
    },
    
    populateGeneralSettings: function() {
        var general = this.currentSettings.general || {};
        
        $('#settings-app-name-input').val(general.app_name?.value || '');
        $('#settings-app-version-input').val(general.app_version?.value || '');
        $('#settings-timezone-select').val(general.timezone?.value || 'Asia/Jakarta');
        $('#settings-date-format-select').val(general.date_format?.value || 'd-m-Y');
        $('#settings-currency-select').val(general.currency?.value || 'IDR');
        $('#settings-decimal-places-input').val(general.decimal_places?.value || '2');
        $('#settings-company-address-textarea').val(general.company_address?.value || '');
    },
    
    populateSecuritySettings: function() {
        var security = this.currentSettings.security || {};
        
        $('#settings-session-timeout-input').val(security.session_timeout?.value || '7200');
        $('#settings-max-login-attempts-input').val(security.max_login_attempts?.value || '5');
        $('#settings-password-min-length-input').val(security.password_min_length?.value || '6');
        $('#settings-password-expiry-days-input').val(security.password_expiry_days?.value || '90');
        $('#settings-require-password-change-checkbox').prop('checked', security.require_password_change?.value == '1');
        $('#settings-enable-2fa-checkbox').prop('checked', security.enable_2fa?.value == '1');
    },
    
    populateEmailSettings: function() {
        var email = this.currentSettings.email || {};
        
        $('#settings-smtp-host-input').val(email.smtp_host?.value || '');
        $('#settings-smtp-port-input').val(email.smtp_port?.value || '587');
        $('#settings-smtp-username-input').val(email.smtp_username?.value || '');
        $('#settings-smtp-password-input').val(email.smtp_password?.value || '');
        $('#settings-smtp-encryption-select').val(email.smtp_encryption?.value || 'tls');
        $('#settings-from-email-input').val(email.from_email?.value || 'noreply@perdagangan.com');
        $('#settings-from-name-input').val(email.from_name?.value || 'Perdagangan System');
    },
    
    populateBackupSettings: function() {
        var backup = this.currentSettings.backup || {};
        
        $('#auto-backup').prop('checked', backup.auto_backup?.value == '1');
        $('#backup-frequency').val(backup.backup_frequency?.value || 'daily');
        $('#backup-retention').val(backup.backup_retention?.value || '30');
        $('#backup-path').val(backup.backup_path?.value || '/backups');
        $('#backup-size').val(this.formatBytes(backup.backup_size?.value || 0));
        $('#backup-compression').prop('checked', backup.backup_compression?.value == '1');
    },
    
    populateFeatureSettings: function() {
        var features = this.currentSettings.features || {};
        
        $('#enable-reports').prop('checked', features.enable_reports?.value == '1');
        $('#enable-notifications').prop('checked', features.enable_notifications?.value == '1');
        $('#enable-backup').prop('checked', features.enable_backup?.value == '1');
        $('#enable-audit-log').prop('checked', features.enable_audit_log?.value == '1');
        $('#enable-api-access').prop('checked', features.enable_api_access?.value == '1');
        $('#enable-maintenance-mode').prop('checked', features.enable_maintenance_mode?.value == '1');
    },
    
    saveGeneralSettings: function() {
        var formData = this.getFormData('#general-settings-form');
        
        this.saveSettings('general', formData);
    },
    
    saveSecuritySettings: function() {
        var formData = this.getFormData('#security-settings-form');
        
        this.saveSettings('security', formData);
    },
    
    saveEmailSettings: function() {
        var formData = this.getFormData('#email-settings-form');
        
        this.saveSettings('email', formData);
    },
    
    saveBackupSettings: function() {
        var formData = this.getFormData('#backup-settings-form');
        
        this.saveSettings('backup', formData);
    },
    
    saveFeatureSettings: function() {
        var formData = this.getFormData('#features-form');
        
        this.saveSettings('features', formData);
    },
    
    saveAllSettings: function() {
        var allSettings = {
            general: this.getFormData('#general-settings-form'),
            security: this.getFormData('#security-settings-form'),
            email: this.getFormData('#email-settings-form'),
            backup: this.getFormData('#backup-settings-form'),
            features: this.getFormData('#features-form')
        };
        
        this.showLoading(true);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=updateSettings',
            type: 'POST',
            data: JSON.stringify(allSettings),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                SettingsModule.showLoading(false);
                
                if (response.status === 'success') {
                    SettingsModule.showToast('Semua pengaturan berhasil disimpan', 'success');
                    SettingsModule.loadSettings(); // Reload to get updated values
                } else {
                    SettingsModule.showToast('Gagal menyimpan pengaturan: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                SettingsModule.showLoading(false);
                SettingsModule.showToast('Gagal menyimpan pengaturan: ' + error, 'error');
            }
        });
    },
    
    saveSettings: function(group, data) {
        this.showLoading(true);
        
        var settingsData = {};
        settingsData[group] = data;
        
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=updateSettings',
            type: 'POST',
            data: JSON.stringify(settingsData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                SettingsModule.showLoading(false);
                
                if (response.status === 'success') {
                    SettingsModule.showToast('Pengaturan ' + group + ' berhasil disimpan', 'success');
                    SettingsModule.loadSettings(); // Reload to get updated values
                } else {
                    SettingsModule.showToast('Gagal menyimpan pengaturan: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                SettingsModule.showLoading(false);
                SettingsModule.showToast('Gagal menyimpan pengaturan: ' + error, 'error');
            }
        });
    },
    
    getFormData: function(formSelector) {
        var formData = {};
        var form = $(formSelector);
        
        // Get all form inputs
        form.find('input, select, textarea').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            
            if ($(this).is(':checkbox')) {
                value = $(this).prop('checked') ? '1' : '0';
            }
            
            if (name) {
                formData[name] = value;
            }
        });
        
        return formData;
    },
    
    testEmailConnection: function() {
        var emailSettings = this.getFormData('#email-settings-form');
        emailSettings.test_connection = true;
        
        this.showLoading(true);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=updateEmailSettings',
            type: 'POST',
            data: JSON.stringify(emailSettings),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                SettingsModule.showLoading(false);
                
                if (response.status === 'success') {
                    var testResult = response.test_result;
                    if (testResult && testResult.success) {
                        SettingsModule.showToast('Koneksi email berhasil: ' + testResult.message, 'success');
                    } else {
                        SettingsModule.showToast('Koneksi email gagal: ' + (testResult?.message || 'Unknown error'), 'error');
                    }
                } else {
                    SettingsModule.showToast('Gagal test koneksi email: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                SettingsModule.showLoading(false);
                SettingsModule.showToast('Gagal test koneksi email: ' + error, 'error');
            }
        });
    },
    
    createBackup: function() {
        if (!confirm('Apakah Anda yakin ingin membuat backup sekarang?')) {
            return;
        }
        
        this.showLoading(true);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=createBackup',
            type: 'POST',
            data: {backup_type: 'full'},
            dataType: 'json',
            success: function(response) {
                SettingsModule.showLoading(false);
                
                if (response.status === 'success') {
                    SettingsModule.showToast('Backup berhasil dibuat: ' + response.backup_file.file, 'success');
                    SettingsModule.loadBackupHistory();
                } else {
                    SettingsModule.showToast('Gagal membuat backup: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                SettingsModule.showLoading(false);
                SettingsModule.showToast('Gagal membuat backup: ' + error, 'error');
            }
        });
    },
    
    loadBackupHistory: function() {
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=getBackupHistory',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    SettingsModule.displayBackupHistory(response.data);
                } else {
                    console.error('Failed to load backup history:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load backup history:', error);
            }
        });
    },
    
    displayBackupHistory: function(backups) {
        var container = $('#backup-history');
        container.empty();
        
        if (!Array.isArray(backups) || backups.length === 0) {
            container.html(`
                <div class="text-center text-muted">
                    <i class="bi bi-clock-history fs-1"></i>
                    <p>Belum ada riwayat backup</p>
                </div>
            `);
            return;
        }
        
        var html = '';
        backups.forEach(function(backup) {
            var statusClass = backup.status === 'completed' ? 'success' : 
                            backup.status === 'failed' ? 'danger' : 'warning';
            var statusText = backup.status === 'completed' ? 'Selesai' : 
                            backup.status === 'failed' ? 'Gagal' : 'Proses';
            
            html += `
                <div class="backup-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${backup.file_name}</h6>
                            <small class="text-muted">
                                ${backup.backup_type} • ${SettingsModule.formatBytes(backup.file_size)} • 
                                ${new Date(backup.created_at).toLocaleString('id-ID')}
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-${statusClass}">${statusText}</span>
                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="SettingsModule.downloadBackup('${backup.file_name}')">
                                <i class="bi bi-download"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger ms-1" onclick="SettingsModule.deleteBackup('${backup.id}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    },
    
    downloadBackup: function(fileName) {
        window.open(BASE_URL + '/index.php?page=settings&action=downloadBackup&file=' + encodeURIComponent(fileName), '_blank');
    },
    
    deleteBackup: function(backupId) {
        if (!confirm('Apakah Anda yakin ingin menghapus backup ini?')) {
            return;
        }
        
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=deleteBackup',
            type: 'POST',
            data: {file: backupId},
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    SettingsModule.showToast('Backup berhasil dihapus', 'success');
                    SettingsModule.loadBackupHistory();
                } else {
                    SettingsModule.showToast('Gagal menghapus backup: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                SettingsModule.showToast('Gagal menghapus backup: ' + error, 'error');
            }
        });
    },
    
    loadSystemLogs: function() {
        var logType = $('#log-type').val();
        
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=getSystemLogs',
            type: 'GET',
            data: {log_type: logType, limit: 50},
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    SettingsModule.displaySystemLogs(response.data);
                } else {
                    console.error('Failed to load system logs:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load system logs:', error);
            }
        });
    },
    
    displaySystemLogs: function(logs) {
        var container = $('#system-logs');
        container.empty();
        
        if (!Array.isArray(logs) || logs.length === 0) {
            container.html(`
                <div class="text-center text-muted">
                    <i class="bi bi-file-text fs-1"></i>
                    <p>Tidak ada log yang tersedia</p>
                </div>
            `);
            return;
        }
        
        var html = '';
        logs.forEach(function(log) {
            var logClass = log.log_type || 'info';
            var icon = SettingsModule.getLogIcon(log.log_type);
            
            html += `
                <div class="log-item ${logClass}">
                    <div class="d-flex justify-content-between">
                        <div>
                            <i class="bi ${icon} me-2"></i>
                            <strong>${log.log_type.toUpperCase()}</strong>
                            ${log.message}
                        </div>
                        <small class="text-muted">
                            ${new Date(log.created_at).toLocaleString('id-ID')}
                        </small>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    },
    
    getLogIcon: function(logType) {
        var icons = {
            'error': 'bi-exclamation-circle',
            'warning': 'bi-exclamation-triangle',
            'info': 'bi-info-circle',
            'debug': 'bi-bug',
            'security': 'bi-shield-exclamation',
            'system': 'bi-gear',
            'user': 'bi-person',
            'backup': 'bi-cloud-download',
            'settings': 'bi-gear'
        };
        
        return icons[logType] || 'bi-info-circle';
    },
    
    clearLogs: function() {
        var logType = $('#log-type').val();
        
        if (!confirm('Apakah Anda yakin ingin menghapus log ' + logType + '?')) {
            return;
        }
        
        $.ajax({
            url: BASE_URL + '/index.php?page=settings&action=clearSystemLogs',
            type: 'POST',
            data: {log_type: logType},
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    SettingsModule.showToast('Log berhasil dihapus', 'success');
                    SettingsModule.loadSystemLogs();
                } else {
                    SettingsModule.showToast('Gagal menghapus log: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                SettingsModule.showToast('Gagal menghapus log: ' + error, 'error');
            }
        });
    },
    
    loadSystemInfo: function() {
        // System info is already loaded from the server
        // This could be enhanced with real-time system status
    },
    
    onTabChange: function(target) {
        // Handle tab-specific actions
        switch(target) {
            case '#backup':
                this.loadBackupHistory();
                break;
            case '#system':
                this.loadSystemLogs();
                break;
        }
    },
    
    onFeatureToggle: function(feature, enabled) {
        // Handle feature toggle effects
        if (feature === 'enable-maintenance-mode' && enabled) {
            if (!confirm('Mode maintenance akan menonaktifkan akses pengguna. Lanjutkan?')) {
                $('#' + feature).prop('checked', false);
                return;
            }
        }
        
        if (feature === 'enable-api-access' && enabled) {
            this.showToast('API access akan memerlukan konfigurasi tambahan', 'info');
        }
    },
    
    refreshSettings: function() {
        this.loadSettings();
    },
    
    refreshBackupHistory: function() {
        this.loadBackupHistory();
    },
    
    showLoading: function(show) {
        $('#loading-spinner').toggle(show);
        $('.tab-pane').toggle(!show);
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
    
    formatBytes: function(bytes, precision = 2) {
        if (bytes === 0) return '0 B';
        
        var units = ['B', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        
        return parseFloat((bytes / Math.pow(1024, i)).toFixed(precision)) + ' ' + units[i];
    }
};

// Auto-initialize when document is ready
$(document).ready(function() {
    SettingsModule.init();
});
