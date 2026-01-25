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
        .audit-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #007bff;
        }
        .audit-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .audit-card.security {
            border-left-color: #dc3545;
        }
        .audit-card.user {
            border-left-color: #28a745;
        }
        .audit-card.system {
            border-left-color: #17a2b8;
        }
        .audit-card.backup {
            border-left-color: #ffc107;
        }
        .audit-card.compliance {
            border-left-color: #6f42c1;
        }
        .log-item {
            border-left: 3px solid #6c757d;
            padding-left: 12px;
            margin-bottom: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .log-item:hover {
            background-color: #f8f9fa;
        }
        .log-item.error {
            border-left-color: #dc3545;
            background-color: #fff5f5;
        }
        .log-item.warning {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }
        .log-item.info {
            border-left-color: #17a2b8;
            background-color: #cff4fc;
        }
        .log-item.security {
            border-left-color: #dc3545;
            background-color: #fff5f5;
        }
        .log-item.success {
            border-left-color: #28a745;
            background-color: #d4edda;
        }
        .activity-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .activity-type-badge.error {
            background-color: #dc3545;
        }
        .activity-type-badge.warning {
            background-color: #ffc107;
        }
        .activity-type-badge.info {
            background-color: #17a2b8;
        }
        .activity-type-badge.success {
            background-color: #28a745;
        }
        .activity-type-badge.security {
            background-color: #dc3545;
        }
        .activity-type-badge.system {
            background-color: #6f42c1;
        }
        .activity-type-badge.backup {
            background-color: #ffc107;
        }
        .activity-type-badge.compliance {
            background-color: #6f42c1;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-sm {
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-weight: 500;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        .compliance-section {
            background: linear-gradient(135deg, #6f42c1 0%, #5a2d8c 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .chart-container {
            height: 300px;
            position: relative;
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
        .timeline-item.error::after {
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
        .timeline-item.security::after {
            background: #dc3545;
        }
        .export-options {
            display: flex;
            gap: 0.5rem;
        }
        .search-highlight {
            background-color: #fff3cd;
            padding: 0.1rem 0.25rem;
            border-radius: 3px;
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
                    <i class="bi bi-shield-check me-2"></i>
                    <?= $title ?>
                </h1>
                <p class="text-muted mb-0">Audit logging dan compliance monitoring</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" onclick="exportAuditLogs()">
                    <i class="bi bi-download me-2"></i>
                    Export
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="refreshAuditLogs()">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4" id="stats-container">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Logs</h6>
                            <h3 class="mb-0" id="total-logs">0</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-list-ul fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Today's Logs</h6>
                            <h3 class="mb-0" id="today-logs">0</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-calendar-day fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Security Events</h6>
                            <h3 class="mb-0" id="security-events">0</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-shield-exclamation fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">System Changes</h6>
                            <h3 class="mb-0" id="system-changes">0</h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-gear fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3">
                <i class="bi bi-funnel me-2"></i>
                Filter Audit Logs
            </h5>
            <form id="audit-filters-form" class="row g-3">
                <div class="col-md-2">
                    <label for="log-type" class="form-label">Log Type</label>
                    <select class="form-select" id="log-type" name="log_type">
                        <option value="">Semua Tipe</option>
                        <?php if (!empty($log_types)): ?>
                            <?php foreach ($log_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="entity-type" class="form-label">Entity Type</label>
                    <select class="form-select" id="entity-type" name="entity_type">
                        <option value="">Semua Entity</option>
                        <?php if (!empty($entities)): ?>
                            <?php foreach ($entities as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="user-filter" class="form-label">User</label>
                    <select class="form-select" id="user-filter" name="user_id">
                        <option value="">Semua User</option>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id_user'] ?>"><?= $user['full_name'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date-from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date-from" name="date_from">
                </div>
                <div class="col-md-2">
                    <label for="date-to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date-to" name="date_to">
                </div>
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-light">
                            <i class="bi bi-search me-2"></i>
                            Tampilkan
                        </button>
                        <button type="button" class="btn btn-outline-light" onclick="resetFilters()">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Reset
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="clearLogs()">
                            <i class="bi bi-trash me-2"></i>
                            Clear Logs
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Compliance Section -->
        <div class="compliance-section">
            <h5 class="mb-3">
                <i class="bi bi-shield-check me-2"></i>
                Compliance Monitoring
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="compliance-report-type" class="form-label text-white">Report Type</label>
                    <select class="form-select" id="compliance-report-type">
                        <option value="summary">Summary Report</option>
                        <option value="detailed">Detailed Report</option>
                        <option value="security">Security Report</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="compliance-date-from" class="form-label text-white">From Date</label>
                    <input type="date" class="form-control" id="compliance-date-from">
                </div>
                <div class="col-md-4">
                    <label for="compliance-date-to" class="form-label text-white">To Date</label>
                    <input type="date" class="form-control" id="compliance-date-to">
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-light" onclick="generateComplianceReport()">
                        <i class="bi bi-file-text me-2"></i>
                        Generate Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loading-spinner">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data audit logs...</p>
        </div>

        <!-- Audit Logs Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Audit Logs
                </h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="table-entries">
                        <option value="25">25 entries</option>
                        <option value="50">50 entries</option>
                        <option value="100">100 entries</option>
                        <option value="200">200 entries</option>
                    </select>
                    <div class="export-options">
                        <button class="btn btn-outline-success btn-sm" onclick="exportToCSV()">
                            <i class="bi bi-file-earmark me-2"></i>
                            CSV
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="exportToExcel()">
                            <i class="bi bi-file-earmark-excel me-2"></i>
                            Excel
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="exportToPDF()">
                            <i class="bi bi-file-earmark-pdf me-2"></i>
                            PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="audit-table">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="select-all-logs">
                                </th>
                                <th>ID</th>
                                <th>Timestamp</th>
                                <th>Activity Type</th>
                                <th>Description</th>
                                <th>User</th>
                                <th>Entity</th>
                                <th>IP Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="audit-table-body">
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Pilih filter untuk menampilkan data audit logs
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3" id="pagination-container">
            <div class="text-muted" id="pagination-info">
                Menampilkan 0 dari 0 data
            </div>
            <nav aria-label="Audit logs pagination">
                <ul class="pagination mb-0" id="pagination">
                    <!-- Pagination will be populated dynamically -->
                </ul>
            </nav>
        </div>

        <!-- Export Modal -->
        <div class="modal fade" id="exportModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-download me-2"></i>
                            Export Audit Logs
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="export-form">
                            <div class="mb-3">
                                <label for="export-format" class="form-label">Export Format</label>
                                <select class="form-select" id="export-format">
                                    <option value="csv">CSV</option>
                                    <option value="excel">Excel</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="export-date-from" class="form-label">Date Range</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="date" class="form-control" id="export-date-from" placeholder="From Date">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" class="form-control" id="export-date-to" placeholder="To Date">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="export-filters" class="form-label">Filters</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="include-filters" checked>
                                    <label class="form-check-label" for="include-filters">
                                        Include current filters
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="executeExport()">
                            <i class="bi bi-download me-2"></i>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Details Modal -->
        <div class="modal fade" id="logDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Log Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="log-details-content">
                            <!-- Log details will be populated here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
    <script src="<?= BASE_URL ?>/public/assets/js/audit.js"></script>
    <script>
        // Initialize Audit Module
        $(document).ready(function() {
            AuditModule.init();
        });
    </script>
</body>
</html>
