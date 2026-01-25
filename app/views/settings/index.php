<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Aplikasi Perdagangan Multi-Cabang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/public/assets/css/style.css" rel="stylesheet">
    <style>
        .settings-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #007bff;
        }
        .settings-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .settings-card.general {
            border-left-color: #007bff;
        }
        .settings-card.security {
            border-left-color: #dc3545;
        }
        .settings-card.email {
            border-left-color: #28a745;
        }
        .settings-card.backup {
            border-left-color: #ffc107;
        }
        .settings-card.features {
            border-left-color: #17a2b8;
        }
        .settings-card.system {
            border-left-color: #6f42c1;
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-indicator.healthy {
            background-color: #28a745;
        }
        .status-indicator.warning {
            background-color: #ffc107;
        }
        .status-indicator.error {
            background-color: #dc3545;
        }
        .feature-toggle {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .feature-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .feature-toggle .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .feature-toggle .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        .feature-toggle input:checked + .slider {
            background-color: #28a745;
        }
        .feature-toggle input:checked + .slider:before {
            transform: translateX(26px);
        }
        .backup-item {
            border-left: 3px solid #007bff;
            padding-left: 12px;
            margin-bottom: 8px;
        }
        .log-item {
            border-left: 3px solid #6c757d;
            padding-left: 12px;
            margin-bottom: 4px;
            font-size: 0.875rem;
        }
        .log-item.error {
            border-left-color: #dc3545;
        }
        .log-item.warning {
            border-left-color: #ffc107;
        }
        .log-item.info {
            border-left-color: #17a2b8;
        }
        .system-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        .system-info-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .progress-ring {
            width: 60px;
            height: 60px;
        }
        .progress-ring circle {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        .progress-ring-text {
            font-size: 0.8rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/layouts/main.php'; ?>

    <main class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="bi bi-gear me-2"></i>
                    <?= $title ?>
                </h1>
                <p class="text-muted mb-0">Konfigurasi sistem dan pengaturan aplikasi</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" onclick="saveAllSettings()">
                    <i class="bi bi-save me-2"></i>
                    Simpan Semua
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="refreshSettings()">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Settings Tabs -->
        <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                    <i class="bi bi-gear me-2"></i>
                    Umum
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                    <i class="bi bi-shield-lock me-2"></i>
                    Keamanan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false">
                    <i class="bi bi-envelope me-2"></i>
                    Email
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="false">
                    <i class="bi bi-cloud-download me-2"></i>
                    Backup
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab" aria-controls="features" aria-selected="false">
                    <i class="bi bi-toggle-on me-2"></i>
                    Fitur
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab" aria-controls="system" aria-selected="false">
                    <i class="bi bi-pc-display me-2"></i>
                    Sistem
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="settingsTabContent">
            <!-- General Settings -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card settings-card general mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Pengaturan Umum
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="settings-general-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="app-name" class="form-label">Nama Aplikasi</label>
                                    <input type="text" class="form-control" id="settings-app-name-input" name="app_name" value="<?= $settings['general']['app_name']['value'] ?? 'Aplikasi Perdagangan Multi-Cabang' ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="app-version" class="form-label">Versi Aplikasi</label>
                                    <input type="text" class="form-control" id="settings-app-version-input" name="app_version" value="<?= $settings['general']['app_version']['value'] ?? '2.0.0' ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="timezone" class="form-label">Zona Waktu</label>
                                    <select class="form-select" id="settings-timezone-select" name="timezone">
                                        <option value="Asia/Jakarta" <?= ($settings['general']['timezone']['value'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' ?>>Asia/Jakarta</option>
                                        <option value="Asia/Bangkok" <?= ($settings['general']['timezone']['value'] ?? 'Asia/Jakarta') == 'Asia/Bangkok' ? 'selected' : '' ?>>Asia/Bangkok</option>
                                        <option value="Asia/Singapore" <?= ($settings['general']['timezone']['value'] ?? 'Asia/Jakarta') == 'Asia/Singapore' ? 'selected' : '' ?>>Asia/Singapore</option>
                                        <option value="UTC" <?= ($settings['general']['timezone']['value'] ?? 'Asia/Jakarta') == 'UTC' ? 'selected' : '' ?>>UTC</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="date-format" class="form-label">Format Tanggal</label>
                                    <select class="form-select" id="settings-date-format-select" name="date_format">
                                        <option value="d-m-Y" <?= ($settings['general']['date_format']['value'] ?? 'd-m-Y') == 'd-m-Y' ? 'selected' : '' ?>>DD-MM-YYYY</option>
                                        <option value="Y-m-d" <?= ($settings['general']['date_format']['value'] ?? 'd-m-Y') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                        <option value="m/d/Y" <?= ($settings['general']['date_format']['value'] ?? 'd-m-Y') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="currency" class="form-label">Mata Uang</label>
                                    <select class="form-select" id="settings-currency-select" name="currency">
                                        <option value="IDR" <?= ($settings['general']['currency']['value'] ?? 'IDR') == 'IDR' ? 'selected' : '' ?>>IDR</option>
                                        <option value="USD" <?= ($settings['general']['currency']['value'] ?? 'IDR') == 'USD' ? 'selected' : '' ?>>USD</option>
                                        <option value="EUR" <?= ($settings['general']['currency']['value'] ?? 'IDR') == 'EUR' ? 'selected' : '' ?>>EUR</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="decimal-places" class="form-label">Desimal</label>
                                    <input type="number" class="form-control" id="settings-decimal-places-input" name="decimal_places" value="<?= $settings['general']['decimal_places']['value'] ?? '2' ?>" min="0" max="4">
                                </div>
                                <div class="col-12">
                                    <label for="company-address" class="form-label">Alamat Perusahaan</label>
                                    <textarea class="form-control" id="settings-company-address-textarea" name="company_address" rows="3"><?= $settings['general']['company_address']['value'] ?? '' ?></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="tab-pane fade" id="security" role="tabpanel">
                <div class="card settings-card security mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            Pengaturan Keamanan
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="settings-security-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="session-timeout" class="form-label">Timeout Sesi (detik)</label>
                                    <input type="number" class="form-control" id="settings-session-timeout-input" name="session_timeout" value="<?= $settings['security']['session_timeout']['value'] ?? '7200' ?>" min="300" max="86400">
                                    <div class="form-text">Minimum 300 detik (5 menit)</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="max-login-attempts" class="form-label">Maksimal Login Gagal</label>
                                    <input type="number" class="form-control" id="settings-max-login-attempts-input" name="max_login_attempts" value="<?= $settings['security']['max_login_attempts']['value'] ?? '5' ?>" min="1" max="10">
                                </div>
                                <div class="col-md-6">
                                    <label for="password-min-length" class="form-label">Panjang Password Minimal</label>
                                    <input type="number" class="form-control" id="settings-password-min-length-input" name="password_min_length" value="<?= $settings['security']['password_min_length']['value'] ?? '6' ?>" min="4" max="20">
                                </div>
                                <div class="col-md-6">
                                    <label for="password-expiry-days" class="form-label">Kadaluwars Password (hari)</label>
                                    <input type="number" class="form-control" id="settings-password-expiry-days-input" name="password_expiry_days" value="<?= $settings['security']['password_expiry_days']['value'] ?? '90' ?>" min="0" max="365">
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="settings-require-password-change-checkbox" name="require_password_change" <?= ($settings['security']['require_password_change']['value'] ?? '0') == '1' ? 'checked' : '' ?>
                                        <label class="form-check-label" for="require-password-change">
                                            Wajib Ubah Password Saat Login Pertama
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="settings-enable-2fa-checkbox" name="enable_2fa" <?= ($settings['security']['enable_2fa']['value'] ?? '0') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable-2fa">
                                            Aktifkan 2FA (Two-Factor Authentication)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="tab-pane fade" id="email" role="tabpanel">
                <div class="card settings-card email mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-envelope me-2"></i>
                            Pengaturan Email
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="settings-email-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="smtp-host" class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" id="settings-smtp-host-input" name="smtp_host" value="<?= $settings['email']['smtp_host']['value'] ?? '' ?>" placeholder="smtp.gmail.com">
                                </div>
                                <div>
                                <div class="col-md-6">
                                    <label for="smtp-port" class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" id="settings-smtp-port-input" name="smtp_port" value="<?= $settings['email']['smtp_port']['value'] ?? '587' ?>" min="1" max="65535">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtp-username" class="form-label">SMTP Username</label>
                                    <input type="text" class="form-control" id="settings-smtp-username-input" name="smtp_username" value="<?= $settings['email']['smtp_username']['value'] ?? '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtp-password" class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" id="settings-smtp-password-input" name="smtp_password" value="<?= $settings['email']['smtp_password']['value'] ?? '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtp-encryption" class="form-label">Enkripsi SMTP</label>
                                    <select class="form-select" id="settings-smtp-encryption-select" name="smtp_encryption">
                                        <option value="tls" <?= ($settings['email']['smtp_encryption']['value'] ?? 'tls') == 'tls' ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= ($settings['email']['smtp_encryption']['value'] ?? 'tls') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        <option value="none" <?= ($settings['email']['smtp_encryption']['value'] ?? 'tls') == 'none' ? 'selected' : '' ?>>None</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="from-email" class="form-label">Email Pengirim</label>
                                    <input type="email" class="form-control" id="settings-from-email-input" name="from_email" value="<?= $settings['email']['from_email']['value'] ?? 'noreply@perdagangan.com' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="from-name" class="form-label">Nama Pengirim</label>
                                    <input type="text" class="form-control" id="settings-from-name-input" name="from_name" value="<?= $settings['email']['from_name']['value'] ?? 'Perdagangan System' ?>">
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-outline-primary" id="settings-test-email-btn" onclick="testEmailConnection()">
                                        <i class="bi bi-envelope-check me-2"></i>
                                        Test Koneksi Email
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Backup Settings -->
            <div class="tab-pane fade" id="backup" role="tabpanel">
                <div class="card settings-card backup mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cloud-download me-2"></i>
                            Pengaturan Backup
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="settings-backup-form">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="settings-auto-backup-checkbox" name="auto_backup" <?= ($settings['backup']['auto_backup']['value'] ?? '0') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="auto-backup">
                                            Aktifkan Backup Otomatis
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="backup-frequency" class="form-label">Frekuensi Backup</label>
                                    <select class="form-select" id="settings-backup-frequency-select" name="backup_frequency">
                                        <option value="daily" <?= ($settings['backup']['backup_frequency']['value'] ?? 'daily') == 'daily' ? 'selected' : '' ?>>Harian</option>
                                        <option value="weekly" <?= ($settings['backup']['backup_frequency']['value'] ?? 'daily') == 'weekly' ? 'selected' : '' ?>>Mingguan</option>
                                        <option value="monthly" <?= ($settings['backup']['backup_frequency']['value'] ?? 'daily') == 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="backup-retention" class="form-label">Retensi Backup (hari)</label>
                                    <input type="number" class="form-control" id="backup-retention" name="backup_retention" value="<?= $settings['backup']['backup_retention']['value'] ?? '30' ?>" min="1" max="365">
                                </div>
                                <div class="col-md-6">
                                    <label for="backup-path" class="form-label">Path Backup</label>
                                    <input type="text" class="form-control" id="backup-path" name="backup_path" value="<?= $settings['backup']['backup_path']['value'] ?? '/backups' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="backup-size" class="form-label">Ukuran Backup Terakhir</label>
                                    <input type="text" class="form-control" id="backup-size" value="<?= $settings['backup']['backup_size']['value'] ?? '0' ?>" readonly>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="backup-compression" name="backup_compression" <?= ($settings['backup']['backup_compression']['value'] ?? '1') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="backup-compression">
                                            Kompres File Backup
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Backup History -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Riwayat Backup
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="createBackup()">
                                <i class="bi bi-cloud-download me-2"></i>
                                Buat Backup
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="refreshBackupHistory()">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="backup-history">
                            <div class="text-center text-muted">
                                <i class="bi bi-clock-history fs-1"></i>
                                <p>Belum ada riwayat backup</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Settings -->
            <div class="tab-pane fade" id="features" role="tabpanel">
                <div class="card settings-card features mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-toggle-on me-2"></i>
                            Fitur Sistem
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="features-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable-reports" name="enable_reports" <?= ($settings['features']['enable_reports']['value'] ?? '1') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable-reports">
                                            Sistem Laporan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable-notifications" name="enable_notifications" <?= ($settings['features']['enable_notifications']['value'] ?? '1') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable-notifications">
                                            Notifikasi Sistem
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable-backup" name="enable_backup" <?= ($settings['features']['enable_backup']['value'] ?? '1') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable-backup">
                                            Sistem Backup
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable-audit-log" name="enable_audit_log" <?= ($settings['features']['enable_audit_log']['value'] ?? '1') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable-audit-log">
                                            Audit Logging
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable-api-access" name="enable_api_access" <?= ($settings['features']['enable_api_access']['value'] ?? '0') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable-api-access">
                                            Akses API
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable-maintenance-mode" name="enable_maintenance_mode" <?= ($settings['features']['enable_maintenance_mode']['value'] ?? '0') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable-maintenance-mode">
                                            Mode Maintenance
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="tab-pane fade" id="system" role="tabpanel">
                <div class="card settings-card system mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pc-display me-2"></i>
                            Informasi Sistem
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="system-info-grid">
                            <div class="system-info-item">
                                <h6>PHP Version</h6>
                                <p class="mb-0"><?= $system_info['php_version'] ?? 'Unknown' ?></p>
                            </div>
                            <div class="system-info-item">
                                <h6>MySQL Version</h6>
                                <p class="mb-0"><?= $system_info['mysql_version'] ?? 'Unknown' ?></p>
                            </div>
                            <div class="system-info-item">
                                <h6>Server OS</h6>
                                <p class="mb-0"><?= $system_info['server_os'] ?? 'Unknown' ?></p>
                            </div>
                            <div class="system-info-item">
                                <h6>Server Software</h6>
                                <p class="mb-0"><?= $system_info['server_software'] ?? 'Unknown' ?></p>
                            </div>
                            <div class="system-info-item">
                                <h6>Memory Limit</h6>
                                <p class="mb-0"><?= $system_info['memory_limit'] ?? 'Unknown' ?></p>
                            </div>
                            <div class="system-info-item">
                                <h6>Max Execution Time</h6>
                                <p class="mb-0"><?= $system_info['max_execution_time'] ?? 'Unknown' ?></p>
                            </div>
                            <div class="system-info-item">
                                <h6>Upload Max Filesize</h6>
                                <p class="mb-0"><?= $system_info['upload_max_filesize'] ?? 'Unknown' ?></p>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-activity me-2"></i>
                                    Status Sistem
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <span class="status-indicator healthy"></span>
                                            <div>
                                                <h6 class="mb-0">Database</h6>
                                                <small class="text-muted">Connection OK</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <span class="status-indicator warning"></span>
                                            <div>
                                                <h6 class="mb-0">Disk Space</h6>
                                                <small class="text-muted">Low</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <span class="status-indicator healthy"></span>
                                            <div>
                                                <h6 class="mb-0">Memory</h6>
                                                <small class="text-muted">Normal</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <span class="status-indicator healthy"></span>
                                            <div>
                                                <h6 class="mb-0">Services</h6>
                                                <small class="text-muted">Running</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Logs -->
                        <div class="card mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-file-text me-2"></i>
                                    Log Sistem
                                </h5>
                                <div>
                                    <div class="d-flex gap-2">
                                        <select class="form-select form-select-sm" id="log-type">
                                            <option value="all">Semua Log</option>
                                            <option value="error">Error Log</option>
                                            <option value="warning">Warning Log</option>
                                            <option value="info">Info Log</option>
                                            <option value="security">Security Log</option>
                                            <option value="system">System Log</option>
                                        </select>
                                        <button class="btn btn-outline-danger btn-sm" onclick="clearLogs()">
                                            <i class="bi bi-trash me-2"></i>
                                            Hapus Log
                                        </button>
                                    </div>
                            </div>
                            <div class="card-body">
                                <div id="system-logs">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-file-text fs-1"></i>
                                        <p>Tidak ada log yang tersedia</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="text-center py-5" id="loading-spinner" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Menyimpan pengaturan...</p>
        </div>

        <!-- Success Toast -->
        <div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container"></div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/jquery-3.6.0.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/settings.js"></script>
    <script>
        // Initialize Settings Module
        $(document).ready(function() {
            SettingsModule.init();
        });
    </script>
</body>
</html>
