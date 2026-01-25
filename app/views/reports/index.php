<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Perdagangan System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        .report-filters {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #007bff;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        .stat-card.success {
            border-left-color: #28a745;
        }
        .stat-card.warning {
            border-left-color: #ffc107;
        }
        .stat-card.danger {
            border-left-color: #dc3545;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .btn-export {
            transition: all 0.3s ease;
        }
        .btn-export:hover {
            transform: translateY(-2px);
        }
        .loading-spinner {
            display: none;
        }
        .report-section {
            margin-bottom: 3rem;
        }
        .report-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h2 mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        <?= $title ?>
                    </h1>
                    <p class="text-muted mb-0">Advanced Business Intelligence & Analytics</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="refreshAllReports()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportAllReports()">
                            <i class="bi bi-download"></i> Export All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="report-filters">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startDate" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-3">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="endDate" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label for="companyFilter" class="form-label">Company</label>
                    <select class="form-select" id="companyFilter">
                        <option value="">All Companies</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company['id_company'] ?>"><?= htmlspecialchars($company['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="branchFilter" class="form-label">Branch</label>
                    <select class="form-select" id="branchFilter">
                        <option value="">All Branches</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?= $branch['id_branch'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-light w-100" onclick="applyFilters()">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Sales Report Section -->
        <div class="report-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="bi bi-cash-stack me-2"></i>Sales Report</h3>
                <button class="btn btn-outline-primary btn-sm btn-export" onclick="exportReport('sales')">
                    <i class="bi bi-file-earmark-csv"></i> Export CSV
                </button>
            </div>
            
            <!-- Sales Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card success">
                        <h6>Total Sales</h6>
                        <h3 id="totalSales">Rp 0</h3>
                        <small class="text-muted">Period total</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h6>Transactions</h6>
                        <h3 id="totalTransactions">0</h3>
                        <small class="text-muted">Total transactions</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <h6>Average Transaction</h6>
                        <h3 id="avgTransaction">Rp 0</h3>
                        <small class="text-muted">Per transaction</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card danger">
                        <h6>Unique Customers</h6>
                        <h3 id="uniqueCustomers">0</h3>
                        <small class="text-muted">Different customers</small>
                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="card report-card">
                <div class="card-body">
                    <h5 class="card-title">Sales Trend</h5>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Performance Section -->
        <div class="report-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="bi bi-box-seam me-2"></i>Product Performance</h3>
                <button class="btn btn-outline-primary btn-sm btn-export" onclick="exportReport('products')">
                    <i class="bi bi-file-earmark-csv"></i> Export CSV
                </button>
            </div>
            
            <div class="card report-card">
                <div class="card-body">
                    <h5 class="card-title">Top Products</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Code</th>
                                    <th>Quantity Sold</th>
                                    <th>Revenue</th>
                                    <th>Avg Price</th>
                                    <th>Transactions</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="loading-spinner">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        Loading product data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Report Section -->
        <div class="report-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="bi bi-warehouse me-2"></i>Inventory Report</h3>
                <button class="btn btn-outline-primary btn-sm btn-export" onclick="exportReport('inventory')">
                    <i class="bi bi-file-earmark-csv"></i> Export CSV
                </button>
            </div>
            
            <div class="card report-card">
                <div class="card-body">
                    <h5 class="card-title">Stock Status</h5>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="stat-card success">
                                <h6>Good Stock</h6>
                                <h3 id="goodStockCount">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card warning">
                                <h6>Medium Stock</h6>
                                <h3 id="mediumStockCount">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card danger">
                                <h6>Low Stock</h6>
                                <h3 id="lowStockCount">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h6>Total Products</h6>
                                <h3 id="totalProducts">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Total Stock</th>
                                    <th>Min Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="loading-spinner">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        Loading inventory data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Analysis Section -->
        <div class="report-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="bi bi-people me-2"></i>Customer Analysis</h3>
                <button class="btn btn-outline-primary btn-sm btn-export" onclick="exportReport('customers')">
                    <i class="bi bi-file-earmark-csv"></i> Export CSV
                </button>
            </div>
            
            <div class="card report-card">
                <div class="card-body">
                    <h5 class="card-title">Customer Segmentation</h5>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="stat-card success">
                                <h6>VIP Customers</h6>
                                <h3 id="vipCustomers">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h6>Regular Customers</h6>
                                <h3 id="regularCustomers">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card warning">
                                <h6>Occasional Customers</h6>
                                <h3 id="occasionalCustomers">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card danger">
                                <h6>Total Revenue</h6>
                                <h3 id="totalRevenue">Rp 0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Code</th>
                                    <th>Transactions</th>
                                    <th>Total Spent</th>
                                    <th>Avg Transaction</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody id="customerTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="loading-spinner">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        Loading customer data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let salesChart = null;
        
        // Initialize reports on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAllReports();
        });
        
        // Load all reports
        function loadAllReports() {
            loadSalesReport();
            loadProductPerformance();
            loadInventoryReport();
            loadCustomerAnalysis();
        }
        
        // Load sales report
        function loadSalesReport() {
            const params = getFilterParams();
            
            fetch(`index.php?page=reports&action=salesReport&${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateSalesStats(data.totals);
                        updateSalesChart(data.data);
                    } else {
                        console.error('Error loading sales report:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Update sales statistics
        function updateSalesStats(totals) {
            document.getElementById('totalSales').textContent = formatCurrency(totals.total_sales);
            document.getElementById('totalTransactions').textContent = totals.total_transactions;
            document.getElementById('avgTransaction').textContent = formatCurrency(totals.avg_transaction);
            document.getElementById('uniqueCustomers').textContent = totals.total_customers;
        }
        
        // Update sales chart
        function updateSalesChart(data) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            if (salesChart) {
                salesChart.destroy();
            }
            
            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.date),
                    datasets: [{
                        label: 'Sales',
                        data: data.map(item => item.total_sales),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }, {
                        label: 'Transactions',
                        data: data.map(item => item.transaction_count),
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Sales (Rp)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Transactions'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        }
        
        // Load product performance
        function loadProductPerformance() {
            const params = getFilterParams();
            
            fetch(`index.php?page=reports&action=productPerformanceReport&${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateProductTable(data.data);
                    } else {
                        console.error('Error loading product report:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Update product table
        function updateProductTable(data) {
            const tbody = document.getElementById('productTableBody');
            tbody.innerHTML = '';
            
            data.forEach(product => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${product.product_name}</td>
                    <td>${product.product_code}</td>
                    <td>${product.total_quantity}</td>
                    <td>${formatCurrency(product.total_revenue)}</td>
                    <td>${formatCurrency(product.avg_price)}</td>
                    <td>${product.transaction_count}</td>
                `;
            });
        }
        
        // Load inventory report
        function loadInventoryReport() {
            const params = getFilterParams();
            
            fetch(`index.php?page=reports&action=inventoryReport&${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateInventoryStats(data.summary);
                        updateInventoryTable(data.data);
                    } else {
                        console.error('Error loading inventory report:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Update inventory statistics
        function updateInventoryStats(summary) {
            document.getElementById('goodStockCount').textContent = summary.good_stock_count;
            document.getElementById('mediumStockCount').textContent = summary.medium_stock_count;
            document.getElementById('lowStockCount').textContent = summary.low_stock_count;
            document.getElementById('totalProducts').textContent = summary.total_products;
        }
        
        // Update inventory table
        function updateInventoryTable(data) {
            const tbody = document.getElementById('inventoryTableBody');
            tbody.innerHTML = '';
            
            data.forEach(item => {
                const row = tbody.insertRow();
                const statusClass = item.stock_status === 'Low Stock' ? 'danger' : 
                                  item.stock_status === 'Medium Stock' ? 'warning' : 'success';
                
                row.innerHTML = `
                    <td>${item.product_name}</td>
                    <td>${item.product_code}</td>
                    <td>${item.category_name || '-'}</td>
                    <td>${item.total_stock}</td>
                    <td>${item.total_min_stock}</td>
                    <td><span class="badge bg-${statusClass}">${item.stock_status}</span></td>
                `;
            });
        }
        
        // Load customer analysis
        function loadCustomerAnalysis() {
            const params = getFilterParams();
            
            fetch(`index.php?page=reports&action=customerAnalysisReport&${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateCustomerStats(data.segmentation);
                        updateCustomerTable(data.data);
                    } else {
                        console.error('Error loading customer report:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Update customer statistics
        function updateCustomerStats(segmentation) {
            document.getElementById('vipCustomers').textContent = segmentation.vip_customers;
            document.getElementById('regularCustomers').textContent = segmentation.regular_customers;
            document.getElementById('occasionalCustomers').textContent = segmentation.occasional_customers;
            document.getElementById('totalRevenue').textContent = formatCurrency(segmentation.total_revenue);
        }
        
        // Update customer table
        function updateCustomerTable(data) {
            const tbody = document.getElementById('customerTableBody');
            tbody.innerHTML = '';
            
            data.forEach(customer => {
                const row = tbody.insertRow();
                const typeClass = customer.customer_type === 'VIP' ? 'success' : 
                                 customer.customer_type === 'Regular' ? 'primary' : 'secondary';
                
                row.innerHTML = `
                    <td>${customer.customer_name}</td>
                    <td>${customer.customer_code || '-'}</td>
                    <td>${customer.transaction_count}</td>
                    <td>${formatCurrency(customer.total_spent)}</td>
                    <td>${formatCurrency(customer.avg_transaction)}</td>
                    <td><span class="badge bg-${typeClass}">${customer.customer_type}</span></td>
                `;
            });
        }
        
        // Get filter parameters
        function getFilterParams() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const companyId = document.getElementById('companyFilter').value;
            const branchId = document.getElementById('branchFilter').value;
            
            const params = new URLSearchParams({
                start_date: startDate,
                end_date: endDate
            });
            
            if (companyId) params.append('company_id', companyId);
            if (branchId) params.append('branch_id', branchId);
            
            return params.toString();
        }
        
        // Apply filters
        function applyFilters() {
            loadAllReports();
        }
        
        // Refresh all reports
        function refreshAllReports() {
            loadAllReports();
        }
        
        // Export report
        function exportReport(reportType) {
            const params = getFilterParams();
            window.open(`index.php?page=reports&action=exportReport&report_type=${reportType}&${params}`, '_blank');
        }
        
        // Export all reports
        function exportAllReports() {
            ['sales', 'products', 'inventory', 'customers'].forEach(type => {
                exportReport(type);
            });
        }
        
        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        }
    </script>
</body>
</html>
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-export {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        .chart-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 300px;
            background: #f8f9fa;
            border-radius: 10px;
            color: #6c757d;
        }
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                    <i class="bi bi-graph-up me-2"></i>
                    <?= $title ?>
                </h1>
                <p class="text-muted mb-0">Analisis dan laporan bisnis Anda</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-export" onclick="exportReport()">
                    <i class="bi bi-download me-2"></i>
                    Export
                </button>
                <button class="btn btn-outline-secondary" onclick="refreshReports()">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Report Filters -->
        <div class="report-filters">
            <h5 class="mb-3">
                <i class="bi bi-funnel me-2"></i>
                Filter Laporan
            </h5>
            <form id="report-filters-form" class="row g-3">
                <div class="col-md-3">
                    <label for="report-type" class="form-label">Tipe Laporan</label>
                    <select class="form-select" id="report-type" name="report_type">
                        <option value="">Pilih Tipe Laporan</option>
                        <optgroup label="Penjualan">
                            <option value="sales_summary">Ringkasan Penjualan</option>
                            <option value="sales_daily">Penjualan Harian</option>
                            <option value="sales_weekly">Penjualan Mingguan</option>
                            <option value="sales_monthly">Penjualan Bulanan</option>
                            <option value="sales_by_branch">Penjualan per Cabang</option>
                            <option value="sales_top_products">Produk Terlaris</option>
                        </optgroup>
                        <optgroup label="Inventaris">
                            <option value="inventory_stock_levels">Level Stok</option>
                            <option value="inventory_valuation">Valuasi Inventaris</option>
                            <option value="inventory_movements">Pergerakan Stok</option>
                        </optgroup>
                        <optgroup label="Keuangan">
                            <option value="financial_profit_loss">Laba Rugi</option>
                            <option value="financial_cash_flow">Arus Kas</option>
                        </optgroup>
                        <optgroup label="Performa Cabang">
                            <option value="branch_comparison">Perbanding Cabang</option>
                            <option value="branch_trends">Tren Cabang</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start-date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start-date" name="start_date" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-3">
                    <label for="end-date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end-date" name="end_date" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label for="company-filter" class="form-label">Perusahaan</label>
                    <select class="form-select" id="company-filter" name="company_id">
                        <option value="">Semua Perusahaan</option>
                        <?php if (!empty($companies)): ?>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id_company'] ?>"><?= htmlspecialchars($company['company_name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="branch-filter" class="form-label">Cabang</label>
                    <select class="form-select" id="branch-filter" name="branch_id">
                        <option value="">Semua Cabang</option>
                        <?php if (!empty($branches)): ?>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['id_branch'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-light">
                        <i class="bi bi-search me-2"></i>
                        Tampilkan Laporan
                    </button>
                    <button type="button" class="btn btn-outline-light ms-2" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4" id="stats-container">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Transaksi</h6>
                            <h3 class="mb-0" id="total-transactions">0</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-cart3 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Pendapatan</h6>
                            <h3 class="mb-0" id="total-revenue">Rp 0</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-cash-stack fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Rata-rata Transaksi</h6>
                            <h3 class="mb-0" id="avg-transaction">Rp 0</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-graph-up-arrow fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Stok Rendah</h6>
                            <h3 class="mb-0" id="low-stock-count">0</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section d-none" id="filter-section">
            <h6 class="mb-3">
                <i class="bi bi-funnel me-2"></i>
                Filter Aktif
            </h6>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-primary" id="filter-badge-report-type"></span>
                <span class="badge bg-secondary" id="filter-badge-date-range"></span>
                <span class="badge bg-info" id="filter-badge-company"></span>
                <span class="badge bg-success" id="filter-badge-branch"></span>
            </div>
            <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                <i class="bi bi-x-circle me-1"></i>
                Hapus Filter
            </button>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loading-spinner">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data...</p>
        </div>

        <!-- Report Content -->
        <div id="report-content">
            <!-- Charts Section -->
            <div class="row mb-4" id="charts-section">
                <div class="col-md-8">
                    <div class="card report-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Grafik Laporan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" id="main-chart">
                                <div class="chart-placeholder">
                                    <i class="bi bi-bar-chart-line fs-1 me-2"></i>
                                    <span>Pilih filter untuk menampilkan grafik</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card report-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Distribusi
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" id="pie-chart">
                                <div class="chart-placeholder">
                                    <i class="bi bi-pie-chart fs-1 me-2"></i>
                                    <span>Data distribusi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card report-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-table me-2"></i>
                        Data Laporan
                    </h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="table-entries">
                            <option value="10">10 entries</option>
                            <option value="25">25 entries</option>
                            <option value="50">50 entries</option>
                            <option value="100">100 entries</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="report-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="report-table-body">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Pilih filter untuk menampilkan data
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legacy Content (for compatibility) -->
        <div id="reportsLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 text-muted small">Memuat laporan...</div>
        </div>
        <div id="reportsError" class="alert alert-danger d-none" role="alert">
            Terjadi kesalahan saat memuat laporan. Silakan coba lagi.
        </div>
        <div id="reportsEmpty" class="alert alert-info d-none" role="alert">
            Belum ada yang bisa dilaporkan.
        </div>
        
        <!-- Konten Laporan Legacy -->
        <div id="reportsContent" class="d-none">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Penjualan Hari Ini</h5>
                            <div class="h4 text-primary" id="todayTotal">Rp 0</div>
                            <small class="text-muted"><span id="todayCount">0</span> transaksi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Low Stock</h5>
                            <div class="h4 text-danger" id="lowStockCount">0</div>
                            <small class="text-muted">Produk perlu restock</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ringkasan 7 Hari</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th class="text-end">Total Penjualan</th>
                                    <th class="text-end">Jumlah Transaksi</th>
                                </tr>
                            </thead>
                            <tbody id="last7TableBody">
                                <!-- populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Produk 30 Hari</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="topProductsTableBody">
                                <!-- populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-download me-2"></i>
                        Export Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="export-form">
                        <div class="mb-3">
                            <label for="export-format" class="form-label">Format Export</label>
                            <select class="form-select" id="export-format" name="format">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="export-report-type" class="form-label">Tipe Laporan</label>
                            <select class="form-select" id="export-report-type" name="report_type">
                                <option value="sales_summary">Ringkasan Penjualan</option>
                                <option value="inventory_stock_levels">Level Stok</option>
                                <option value="branch_comparison">Perbanding Cabang</option>
                            </select>
                        </div>
                        <input type="hidden" id="export-filters" name="filters">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-2"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/jquery-3.6.0.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/reports.js"></script>
    <script>
        // Initialize reports module
        $(document).ready(function() {
            ReportsModule.init();
        });
    </script>
</body>
</html>
