<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <h1 class="h2 h3-md mb-3 mb-md-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
            <div class="d-flex flex-column flex-sm-row gap-2">
                <button class="btn btn-outline-primary btn-sm flex-fill flex-sm-auto" id="refreshDashboardBtn" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise me-1"></i> <span class="d-none d-sm-inline">Refresh</span>
                </button>
                <div class="btn-group flex-fill flex-sm-auto">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" id="exportDropdownBtn">
                        <i class="bi bi-download me-1"></i> <span class="d-none d-sm-inline">Export</span>
                    </button>
                    <ul class="dropdown-menu" id="exportDropdownMenu">
                        <li><a class="dropdown-item" href="<?= $this->url('/export?format=json') ?>" id="exportJsonBtn">
                            <i class="bi bi-filetype-json me-2"></i>JSON
                        </a></li>
                        <li><a class="dropdown-item" href="<?= $this->url('/export?format=csv') ?>" id="exportCsvBtn">
                            <i class="bi bi-filetype-csv me-2"></i>CSV
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card bg-primary text-white dashboard-card primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-1" id="totalCompanies"><?= $companyStats['total_companies'] ?></h4>
                        <p class="card-text small mb-0">Total Perusahaan</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-building display-6 display-md-4 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card bg-success text-white dashboard-card success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-1" id="activeCompanies"><?= $companyStats['active_companies'] ?></h4>
                        <p class="card-text">Perusahaan Aktif</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle display-4 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card bg-info text-white dashboard-card info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-1" id="totalBranches"><?= $branchStats['total_branches'] ?></h4>
                        <p class="card-text small mb-0">Total Cabang</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-shop display-6 display-md-4 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card bg-warning text-white dashboard-card warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-1" id="openBranchesCount"><?= count($openBranches) ?></h4>
                        <p class="card-text small mb-0">Cabang Buka</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-door-open display-6 display-md-4 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Stats -->
<div class="row mb-4" id="realtimeStatsContainer">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="text-center">
                            <h5 class="h6 h5-md" id="todaySalesAmount">Rp 0</h5>
                            <small class="text-muted">Penjualan Hari Ini</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="text-center">
                            <h5 class="h6 h5-md" id="todayTransactionsCount">0</h5>
                            <small class="text-muted">Transaksi Hari Ini</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="text-center">
                            <h5 class="h6 h5-md" id="lowStockAlertsCount">0</h5>
                            <small class="text-muted">Alert Stok Rendah</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="text-center">
                            <small class="text-muted" id="lastUpdatedTime">Loading...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4" id="chartsContainer">
    <div class="col-lg-6 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0 h6 h5-md">
                    <i class="bi bi-bar-chart me-2"></i>Distribusi Skalabilitas
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="scalabilityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0 h6 h5-md">
                    <i class="bi bi-pie-chart me-2"></i>Segmen Bisnis
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="segmentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="row" id="tablesContainer">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0" id="branchesTableTitle">
                    <i class="bi bi-shop me-2"></i>Cabang dengan Inventory
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="branchesInventoryTable">
                        <thead>
                            <tr>
                                <th><i class="bi bi-building me-1"></i>Perusahaan</th>
                                <th><i class="bi bi-shop me-1"></i>Cabang</th>
                                <th><i class="bi bi-tag me-1"></i>Tipe</th>
                                <th><i class="bi bi-box me-1"></i>Produk</th>
                                <th><i class="bi bi-stack me-1"></i>Stok</th>
                                <th><i class="bi bi-exclamation-triangle me-1"></i>Stok Rendah</th>
                                <th><i class="bi bi-activity me-1"></i>Status</th>
                            </tr>
                        </thead>
                        <tbody id="branchesInventoryTableBody">
                            <?php foreach ($branchesWithInventory as $branch): ?>
                            <tr>
                                <td><?= $branch['company_name'] ?></td>
                                <td><?= $branch['branch_name'] ?></td>
                                <td><span class="badge bg-secondary"><?= $branch['branch_type'] ?></span></td>
                                <td><?= $branch['product_count'] ?></td>
                                <td><?= number_format($branch['total_stock']) ?></td>
                                <td>
                                    <?php if ($branch['low_stock_count'] > 0): ?>
                                        <span class="badge bg-danger"><?= $branch['low_stock_count'] ?></span>
                                    <?php else: ?>
                                        <span class="text-success"><i class="bi bi-check-circle"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-success">Aktif</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0" id="openBranchesTitle">
                    <i class="bi bi-door-open me-2"></i>Cabang Buka Sekarang
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush" id="openBranchesList">
                    <?php foreach ($openBranches as $branch): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><?= $branch['branch_name'] ?></h6>
                                <small class="text-muted"><?= $branch['company_name'] ?></small>
                            </div>
                            <span class="badge bg-success rounded-pill">
                                <i class="bi bi-door-open me-1"></i>Buka
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($openBranches)): ?>
                    <div class="text-center text-muted py-3" id="noOpenBranchesMsg">
                        <i class="bi bi-door-closed display-4 mb-2"></i>
                        <p>Tidak ada cabang yang buka saat ini</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Companies -->
<div class="row" id="recentCompaniesContainer">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0" id="recentCompaniesTitle">
                    <i class="bi bi-building me-2"></i>Perusahaan Terbaru
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="recentCompaniesTable">
                        <thead>
                            <tr>
                                <th><i class="bi bi-building me-1"></i>Nama Perusahaan</th>
                                <th><i class="bi bi-tag me-1"></i>Tipe</th>
                                <th><i class="bi bi-folder me-1"></i>Kategori</th>
                                <th><i class="bi bi-graph-up me-1"></i>Level</th>
                                <th><i class="bi bi-person me-1"></i>Pemilik</th>
                                <th><i class="bi bi-activity me-1"></i>Status</th>
                                <th><i class="bi bi-calendar me-1"></i>Dibuat</th>
                            </tr>
                        </thead>
                        <tbody id="recentCompaniesTableBody">
                            <?php foreach ($recentCompanies as $company): ?>
                            <tr>
                                <td><?= $company['company_name'] ?></td>
                                <td><span class="badge bg-primary"><?= $company['company_type'] ?></span></td>
                                <td><span class="badge bg-info"><?= $company['business_category'] ?></span></td>
                                <td><span class="badge bg-warning">Level <?= $company['scalability_level'] ?></span></td>
                                <td><?= $company['owner_name'] ?></td>
                                <td>
                                    <?php if ($company['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non-aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $this->formatDate($company['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard JavaScript with Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Load scalability chart
    loadScalabilityChart();
    
    // Load segment chart
    loadSegmentChart();
    
    // Auto refresh every 30 seconds
    setInterval(refreshDashboard, 30000);
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function loadScalabilityChart() {
    fetch('<?= BASE_URL ?>/index.php?page=dashboard&action=api-scalability')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('scalabilityChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.data.labels,
                    datasets: [{
                        label: 'Jumlah Perusahaan',
                        data: data.data.data,
                        backgroundColor: 'rgba(13, 110, 253, 0.8)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading scalability chart:', error);
        });
}

function loadSegmentChart() {
    fetch('<?= BASE_URL ?>/index.php?page=dashboard&action=api-segments')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('segmentChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.data.labels,
                    datasets: [{
                        data: data.data.data,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading segment chart:', error);
        });
}

function refreshDashboard() {
    location.reload();
}
</script>
