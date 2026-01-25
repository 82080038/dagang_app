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
        .monitoring-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #007bff;
        }
        .monitoring-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .monitoring-card.healthy {
            border-left-color: #28a745;
        }
        .monitoring-card.warning {
            border-left-color: #ffc107;
        }
        .monitoring-card.critical {
            border-left-color: #dc3545;
        }
        .monitoring-card.error {
            border-left-color: #dc3545;
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
        .status-indicator.critical {
            background-color: #dc3545;
        }
        .status-indicator.error {
            background-color: #dc3545;
        }
        .status-indicator.unknown {
            background-color: #6c757d;
        }
        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .metric-unit {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .metric-change {
            font-size: 0.75rem;
            font-weight: 500;
        }
        .metric-change.positive {
            color: #28a745;
        }
        .metric-change.negative {
            color: #dc3545;
        }
        .service-status {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            background: #f8f9fa;
        }
        .service-status.running {
            background: #d4edda;
            color: #155724;
        }
        .service-status.stopped {
            background: #f8d7da;
            color: #721c24;
        }
        .service-status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .alert-item {
            border-left: 3px solid #6c757d;
            padding-left: 12px;
            margin-bottom: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .alert-item:hover {
            background-color: #f8f9fa;
        }
        .alert-item.critical {
            border-left-color: #dc3545;
            background-color: #fff5f5;
        }
        .alert-item.warning {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }
        .alert-item.info {
            border-left-color: #17a2b8;
            background-color: #cff4fc;
        }
        .backup-item {
            border-left: 3px solid #007bff;
            padding-left: 12px;
            margin-bottom: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .backup-item:hover {
            background-color: #f8f9fa;
        }
        .backup-item.completed {
            border-left-color: #28a745;
        }
        .backup-item.failed {
            border-left-color: #dc3545;
        }
        .backup-item.in_progress {
            border-left-color: #ffc107;
        }
        .chart-container {
            height: 300px;
            position: relative;
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
        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #dee2e6;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #6c757d;
            border: 3px solid white;
        }
        .timeline-item.critical::after {
            background: #dc3545;
        }
        .timeline-item.warning::after {
            background: #ffc107;
        }
        .timeline-item.info::after {
            background: #17a2b8;
        }
        .timeline-item.success::after {
            background: #28a745;
        }
        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        .metric-chart {
            height: 200px;
            position: relative;
        }
        .metric-chart canvas {
            max-height: 100%;
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
                    <i class="bi bi-activity me-2"></i>
                    <?= $title ?>
                </h1>
                <p class="text-muted mb-0">System monitoring dan backup management</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" onclick="createBackup()">
                    <i class="bi bi-cloud-download me-2"></i>
                    Create Backup
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="refreshMonitoring()">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- System Health Overview -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">System Health</h6>
                            <div class="d-flex align-items-center">
                                <span class="status-indicator" id="system-health-indicator"></span>
                                <span class="metric-value" id="system-health-status">Healthy</span>
                            </div>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-heart-pulse fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">CPU Usage</h6>
                            <div class="d-flex align-items-center">
                                <span class="metric-value" id="cpu-usage">45%</span>
                                <span class="metric-change positive" id="cpu-change">↓ 2%</span>
                            </div>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-cpu fs-2"></i>
                        </div>
                    </div>
                    <div class="metric-chart mt-3">
                        <canvas id="cpu-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Memory Usage</h6>
                            <div class="d-flex align-items-center">
                                <span class="metric-value" id="memory-usage">65%</span>
                                <span class="metric-change positive" id="memory-change">↓ 5%</span>
                            </div>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-memory fs-2"></i>
                        </div>
                    </div>
                    <div class="metric-chart mt-3">
                        <canvas id="memory-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Disk Usage</h6>
                            <div class="d-flex align-items-center">
                                <span class="metric-value" id="disk-usage">85%</span>
                                <span class="metric-change negative" id="disk-change">↑ 1%</span>
                            </div>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-hdd fs-2"></i>
                        </div>
                    </div>
                    <div class="metric-chart mt-3">
                        <canvas id="disk-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Response Time</h6>
                            <div class="d-flex align-items-center">
                                <span class="metric-value" id="response-time">0.05s</span>
                                <span class="metric-change positive" id="response-change">↓ 0.01s</span>
                            </div>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-speedometer fs-2"></i>
                        </div>
                    </div>
                    <div class="metric-chart mt-3">
                        <canvas id="response-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Status -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card monitoring-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-server me-2"></i>
                            Service Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="services-status">
                            <!-- Services will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card monitoring-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cloud-download me-2"></i>
                            Backup Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="backup-status">
                            <!-- Backup status will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts and Timeline -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card monitoring-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Recent Alerts
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="alert-filter">
                                <option value="all">All Alerts</option>
                                <option value="critical">Critical</option>
                                <option value="warning">Warning</option>
                                <option value="info">Info</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="alerts-container">
                            <!-- Alerts will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card monitoring-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Activity Timeline
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="activity-timeline">
                            <!-- Timeline will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Management -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-check me-2"></i>
                    Backup Management
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="showScheduleModal()">
                        <i class="bi bi-calendar-plus me-2"></i>
                        Schedule Backup
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshBackupHistory()">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="backup-table">
                        <thead class="table-light">
                            <tr>
                                <th>Backup ID</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>File Size</th>
                                <th>Duration</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="backup-table-body">
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Loading backup history...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data monitoring...</p>
        </div>

        <!-- Schedule Backup Modal -->
        <div class="modal fade" id="scheduleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-calendar-plus me-2"></i>
                            Schedule Backup
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="schedule-form">
                            <div class="mb-3">
                                <label for="schedule-name" class="form-label">Schedule Name</label>
                                <input type="text" class="form-control" id="schedule-name" required>
                            </div>
                            <div class="mb-3">
                                <label for="backup-type" class="form-label">Backup Type</label>
                                <select class="form-select" id="backup-type" required>
                                    <option value="full">Full Backup</option>
                                    <option value="database">Database Only</option>
                                    <option value="files">Files Only</option>
                                    <option value="settings">Settings Only</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="frequency" class="form-label">Frequency</label>
                                <select class="form-select" id="frequency" required>
                                    <option value="hourly">Hourly</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="backup-path" class="form-label">Backup Path</label>
                                <input type="text" class="form-control" id="backup-path" value="/backups">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="compression" checked>
                                    <label class="form-check-label" for="compression">
                                        Compress backup files
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="encryption">
                                    <label class="form-check-label" for="encryption">
                                        Encrypt backup files
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="saveSchedule()">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Backup Modal -->
        <div class="modal fade" id="createBackupModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-cloud-download me-2"></i>
                            Create Backup
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-backup-form">
                            <div class="mb-3">
                                <label for="backup-type-create" class="form-label">Backup Type</label>
                                <select class="form-select" id="backup-type-create" required>
                                    <option value="full">Full Backup</option>
                                    <option value="database">Database Only</option>
                                    <option value="files">Files Only</option>
                                    <option value="settings">Settings Only</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="backup-description" class="form-label">Description</label>
                                <textarea class="form-control" id="backup-description" rows="3" placeholder="Optional backup description"></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="compression-create" checked>
                                    <label class="form-check-label" for="compression-create">
                                        Compress backup files
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="executeCreateBackup()">Create Backup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Container -->
        <div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container"></div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/monitoring.js"></script>
    <script>
        // Initialize Monitoring Module
        $(document).ready(function() {
            MonitoringModule.init();
        });
    </script>
</body>
</html>
