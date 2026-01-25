<?php include_once '../views/layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Customer Management</h1>
            <p class="text-muted mb-0">Manage your customer database and relationships</p>
        </div>
        <div>
            <?php if ($this->hasPermission(ROLE_MANAGER)): ?>
            <a href="index.php?page=customers&action=create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Customer
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= number_format($statistics['total_customers'] ?? 0) ?></h4>
                            <p class="mb-0">Total Customers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
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
                            <h4 class="mb-0"><?= number_format($statistics['active_customers'] ?? 0) ?></h4>
                            <p class="mb-0">Active Customers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x opacity-75"></i>
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
                            <h4 class="mb-0"><?= number_format($statistics['active_30_days'] ?? 0) ?></h4>
                            <p class="mb-0">Active (30 days)</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
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
                            <h4 class="mb-0"><?= number_format($statistics['total_debt'] ?? 0, 0, ',', '.') ?></h4>
                            <p class="mb-0">Total Debt</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer Segments</h5>
                </div>
                <div class="card-body">
                    <canvas id="customer-segment-chart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Loyalty Tiers</h5>
                </div>
                <div class="card-body">
                    <canvas id="loyalty-tier-chart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form id="customer-filter-form" method="GET" action="index.php">
                <input type="hidden" name="page" value="customers">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="customer-search-input" class="form-label">Search</label>
                        <input type="text" class="form-control" id="customer-search-input" name="search" 
                               value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by name, code, phone, email...">
                    </div>
                    <div class="col-md-2">
                        <label for="customer-segment-filter" class="form-label">Segment</label>
                        <select class="form-select" id="customer-segment-filter" name="segment">
                            <option value="">All Segments</option>
                            <?php foreach (['regular', 'vip', 'premium', 'wholesale', 'corporate'] as $seg): ?>
                            <option value="<?= $seg ?>" <?= ($segment ?? '') === $seg ? 'selected' : '' ?>>
                                <?= ucfirst($seg) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="customer-tier-filter" class="form-label">Loyalty Tier</label>
                        <select class="form-select" id="customer-tier-filter" name="tier">
                            <option value="">All Tiers</option>
                            <?php foreach (['bronze', 'silver', 'gold', 'platinum', 'diamond'] as $tier): ?>
                            <option value="<?= $tier ?>" <?= ($tier ?? '') === $tier ? 'selected' : '' ?>>
                                <?= ucfirst($tier) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="customer-status-filter" class="form-label">Status</label>
                        <select class="form-select" id="customer-status-filter" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="blacklisted" <?= ($status ?? '') === 'blacklisted' ? 'selected' : '' ?>>Blacklisted</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                            <a href="index.php?page=customers" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Customers</h5>
            <div class="d-flex gap-2">
                <?php if ($this->hasPermission(ROLE_MANAGER)): ?>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulk-action-modal">
                    <i class="fas fa-tasks me-1"></i>Bulk Actions
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="CustomerModule.exportData()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Bulk Actions (hidden by default) -->
            <div id="bulk-actions" class="d-none mb-3">
                <div class="alert alert-info">
                    <span id="selected-count">0</span> customers selected
                    <div class="float-end">
                        <select class="form-select form-select-sm d-inline-block w-auto" id="bulk-action-select">
                            <option value="">Choose action...</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <?php if ($this->hasPermission(ROLE_ADMIN)): ?>
                            <option value="blacklist">Blacklist</option>
                            <?php endif; ?>
                        </select>
                        <button type="button" class="btn btn-sm btn-primary ms-2" onclick="CustomerModule.executeBulkAction()">
                            Execute
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary ms-1" onclick="CustomerModule.clearSelection()">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="customer-table">
                    <thead class="table-dark">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="select-all-customers">
                            </th>
                            <th>Customer Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Segment</th>
                            <th>Loyalty Tier</th>
                            <th>Phone</th>
                            <th>Total Purchases</th>
                            <th>Last Purchase</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customer-table-body">
                        <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No customers found</p>
                                <a href="index.php?page=customers&action=create" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-2"></i>Add First Customer
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                            <tr id="customer-row-<?= $customer['id_customer'] ?>">
                                <td>
                                    <input type="checkbox" class="form-check-input customer-checkbox" 
                                           value="<?= $customer['id_customer'] ?>">
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($customer['customer_code']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($customer['customer_name']) ?></div>
                                            <?php if (!empty($customer['email'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= ucfirst($customer['customer_type']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= ucfirst($customer['customer_segment']) ?></span>
                                </td>
                                <td>
                                    <span class="loyalty-badge loyalty-<?= $customer['loyalty_tier'] ?>">
                                        <?= ucfirst($customer['loyalty_tier']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($customer['phone'] ?? '-') ?></td>
                                <td class="text-end">
                                    <?= number_format($customer['total_purchases'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td>
                                    <?php if ($customer['last_purchase_date']): ?>
                                    <small><?= date('M d, Y', strtotime($customer['last_purchase_date'])) ?></small>
                                    <?php else: ?>
                                    <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($customer['is_blacklisted']): ?>
                                    <span class="badge bg-danger">Blacklisted</span>
                                    <?php elseif (!$customer['is_active']): ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                    <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="CustomerModule.viewCustomer(<?= $customer['id_customer'] ?>)"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($this->hasPermission(ROLE_MANAGER)): ?>
                                        <button type="button" class="btn btn-outline-warning btn-sm" 
                                                onclick="CustomerModule.editCustomer(<?= $customer['id_customer'] ?>)"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                    data-bs-toggle="dropdown" title="More">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="CustomerModule.viewTransactions(<?= $customer['id_customer'] ?>)">
                                                        <i class="fas fa-receipt me-2"></i>View Transactions
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="CustomerModule.manageLoyalty(<?= $customer['id_customer'] ?>)">
                                                        <i class="fas fa-star me-2"></i>Loyalty Points
                                                    </a>
                                                </li>
                                                <?php if ($this->hasPermission(ROLE_MANAGER)): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <?php if ($customer['is_active']): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="CustomerModule.deactivateCustomer(<?= $customer['id_customer'] ?>)">
                                                        <i class="fas fa-ban me-2"></i>Deactivate
                                                    </a>
                                                </li>
                                                <?php else: ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="CustomerModule.activateCustomer(<?= $customer['id_customer'] ?>)">
                                                        <i class="fas fa-check me-2"></i>Activate
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <?php if ($this->hasPermission(ROLE_ADMIN)): ?>
                                                <?php if (!$customer['is_blacklisted']): ?>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="CustomerModule.blacklistCustomer(<?= $customer['id_customer'] ?>)">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>Blacklist
                                                    </a>
                                                </li>
                                                <?php else: ?>
                                                <li>
                                                    <a class="dropdown-item text-success" href="#" onclick="CustomerModule.unblacklistCustomer(<?= $customer['id_customer'] ?>)">
                                                        <i class="fas fa-shield-alt me-2"></i>Remove from Blacklist
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Customer pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=customers&search=<?= urlencode($search ?? '') ?>&segment=<?= urlencode($segment ?? '') ?>&tier=<?= urlencode($tier ?? '') ?>&status=<?= urlencode($status ?? '') ?>&p=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=customers&search=<?= urlencode($search ?? '') ?>&segment=<?= urlencode($segment ?? '') ?>&tier=<?= urlencode($tier ?? '') ?>&status=<?= urlencode($status ?? '') ?>&p=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=customers&search=<?= urlencode($search ?? '') ?>&segment=<?= urlencode($segment ?? '') ?>&tier=<?= urlencode($tier ?? '') ?>&status=<?= urlencode($status ?? '') ?>&p=<?= $page + 1 ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div class="modal fade" id="customer-details-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customer-details-content">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Loyalty Points Modal -->
<div class="modal fade" id="loyalty-points-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Loyalty Points</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="loyalty-points-form">
                <div class="modal-body">
                    <input type="hidden" id="loyalty-customer-id" name="customer_id">
                    
                    <div class="mb-3">
                        <label for="loyalty-points-input" class="form-label">Points</label>
                        <input type="number" class="form-control" id="loyalty-points-input" name="points" 
                               placeholder="Use positive for adding, negative for deducting" required>
                        <small class="form-text text-muted">Enter positive number to add points, negative to deduct</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="loyalty-reference-type" class="form-label">Reference Type</label>
                        <select class="form-select" id="loyalty-reference-type" name="reference_type">
                            <option value="manual_adjustment">Manual Adjustment</option>
                            <option value="bonus">Bonus</option>
                            <option value="redemption">Redemption</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="loyalty-description" class="form-label">Description</label>
                        <textarea class="form-control" id="loyalty-description" name="description" rows="3" 
                                  placeholder="Reason for points adjustment"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Points
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Blacklist Modal -->
<div class="modal fade" id="blacklist-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Blacklist Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="blacklist-form">
                <div class="modal-body">
                    <input type="hidden" id="blacklist-customer-id" name="customer_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Blacklisting a customer will prevent them from making purchases and accessing services.
                    </div>
                    
                    <div class="mb-3">
                        <label for="blacklist-reason" class="form-label">Reason for Blacklisting</label>
                        <textarea class="form-control" id="blacklist-reason" name="reason" rows="3" 
                                  placeholder="Please provide a reason for blacklisting this customer" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-2"></i>Blacklist Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../views/layouts/footer.php'; ?>

<!-- Custom CSS for Loyalty Badges -->
<style>
.loyalty-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}
.loyalty-bronze { background-color: #CD7F32; color: white; }
.loyalty-silver { background-color: #C0C0C0; color: black; }
.loyalty-gold { background-color: #FFD700; color: black; }
.loyalty-platinum { background-color: #E5E4E2; color: black; }
.loyalty-diamond { background-color: #B9F2FF; color: black; }
</style>

<!-- JavaScript Module -->
<script>
// Customer Module
var CustomerModule = {
    init: function() {
        this.bindEvents();
        this.initializeCharts();
        this.initializeSearch();
    },
    
    bindEvents: function() {
        // Filter form
        $('#customer-filter-form').on('submit', this.handleFilter);
        
        // Search input (live search)
        $('#customer-search-input').on('input', this.debounce(this.handleSearch, 500));
        
        // Select all checkbox
        $('#select-all-customers').on('change', this.handleSelectAll);
        
        // Individual checkboxes
        $(document).on('change', '.customer-checkbox', this.handleCheckboxChange);
        
        // Loyalty points form
        $('#loyalty-points-form').on('submit', this.handleLoyaltyPoints);
        
        // Blacklist form
        $('#blacklist-form').on('submit', this.handleBlacklist);
    },
    
    initializeCharts: function() {
        // Customer Segment Chart
        var segmentCtx = document.getElementById('customer-segment-chart').getContext('2d');
        var segmentData = <?= json_encode($segmentDistribution ?? []) ?>;
        
        new Chart(segmentCtx, {
            type: 'doughnut',
            data: {
                labels: segmentData.map(item => item.customer_segment),
                datasets: [{
                    data: segmentData.map(item => item.count),
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#fd7e14', '#6f42c1'
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
        
        // Loyalty Tier Chart
        var tierCtx = document.getElementById('loyalty-tier-chart').getContext('2d');
        var tierData = <?= json_encode($tierDistribution ?? []) ?>;
        
        new Chart(tierCtx, {
            type: 'bar',
            data: {
                labels: tierData.map(item => item.loyalty_tier),
                datasets: [{
                    label: 'Customers',
                    data: tierData.map(item => item.count),
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    },
    
    initializeSearch: function() {
        // Initialize any search functionality
    },
    
    handleFilter: function(e) {
        // Let the form submit normally
        return true;
    },
    
    handleSearch: function(e) {
        var searchValue = e.target.value;
        // Implement live search if needed
        console.log('Searching for:', searchValue);
    },
    
    handleSelectAll: function(e) {
        var isChecked = e.target.checked;
        $('.customer-checkbox').prop('checked', isChecked);
        CustomerModule.updateBulkActions();
    },
    
    handleCheckboxChange: function() {
        CustomerModule.updateBulkActions();
    },
    
    updateBulkActions: function() {
        var checkedCount = $('.customer-checkbox:checked').length;
        $('#selected-count').text(checkedCount);
        
        if (checkedCount > 0) {
            $('#bulk-actions').removeClass('d-none');
        } else {
            $('#bulk-actions').addClass('d-none');
        }
        
        // Update select all checkbox
        var totalCount = $('.customer-checkbox').length;
        $('#select-all-customers').prop('checked', checkedCount === totalCount && checkedCount > 0);
    },
    
    clearSelection: function() {
        $('.customer-checkbox').prop('checked', false);
        this.updateBulkActions();
    },
    
    executeBulkAction: function() {
        var action = $('#bulk-action-select').val();
        if (!action) {
            Toast.warning('Please select an action');
            return;
        }
        
        var customerIds = $('.customer-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (customerIds.length === 0) {
            Toast.warning('Please select customers');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${customerIds.length} customers?`)) {
            return;
        }
        
        fetch('index.php?page=customers&action=bulkAction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                bulk_action: action,
                customer_ids: customerIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Toast.success(data.message);
                location.reload();
            } else {
                Toast.error(data.message);
            }
        })
        .catch(error => {
            Toast.error('An error occurred');
        });
    },
    
    viewCustomer: function(customerId) {
        fetch(`index.php?page=customers&action=apiGetCustomer&id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Load customer details into modal
                var customer = data.customer;
                var content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm">
                                <tr><td>Customer Code:</td><td>${customer.customer_code}</td></tr>
                                <tr><td>Name:</td><td>${customer.customer_name}</td></tr>
                                <tr><td>Type:</td><td>${customer.customer_type}</td></tr>
                                <tr><td>Segment:</td><td>${customer.customer_segment}</td></tr>
                                <tr><td>Loyalty Tier:</td><td>${customer.loyalty_tier}</td></tr>
                                <tr><td>Phone:</td><td>${customer.phone || '-'}</td></tr>
                                <tr><td>Email:</td><td>${customer.email || '-'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Business Metrics</h6>
                            <table class="table table-sm">
                                <tr><td>Total Purchases:</td><td>${customer.total_purchases}</td></tr>
                                <tr><td>Total Transactions:</td><td>${customer.total_transactions}</td></tr>
                                <tr><td>Avg Transaction:</td><td>${customer.average_transaction_value}</td></tr>
                                <tr><td>Loyalty Points:</td><td>${customer.loyalty_points}</td></tr>
                                <tr><td>Credit Limit:</td><td>${customer.credit_limit}</td></tr>
                                <tr><td>Current Debt:</td><td>${customer.current_debt}</td></tr>
                                <tr><td>Credit Status:</td><td>${customer.credit_status}</td></tr>
                            </table>
                        </div>
                    </div>
                    ${customer.address_detail ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Address</h6>
                            <p>${customer.address_detail}</p>
                            <p>${customer.village_name}, ${customer.district_name}, ${customer.regency_name}, ${customer.province_name}</p>
                        </div>
                    </div>
                    ` : ''}
                `;
                
                $('#customer-details-content').html(content);
                $('#customer-details-modal').modal('show');
            } else {
                Toast.error(data.message);
            }
        })
        .catch(error => {
            Toast.error('Failed to load customer details');
        });
    },
    
    editCustomer: function(customerId) {
        window.location.href = `index.php?page=customers&action=edit&id=${customerId}`;
    },
    
    activateCustomer: function(customerId) {
        if (!confirm('Are you sure you want to activate this customer?')) return;
        
        fetch(`index.php?page=customers&action=activate&id=${customerId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Toast.success(data.message);
                location.reload();
            } else {
                Toast.error(data.message);
            }
        })
        .catch(error => {
            Toast.error('An error occurred');
        });
    },
    
    deactivateCustomer: function(customerId) {
        if (!confirm('Are you sure you want to deactivate this customer?')) return;
        
        fetch(`index.php?page=customers&action=delete&id=${customerId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Toast.success(data.message);
                location.reload();
            } else {
                Toast.error(data.message);
            }
        })
        .catch(error => {
            Toast.error('An error occurred');
        });
    },
    
    blacklistCustomer: function(customerId) {
        $('#blacklist-customer-id').val(customerId);
        $('#blacklist-modal').modal('show');
    },
    
    unblacklistCustomer: function(customerId) {
        if (!confirm('Are you sure you want to remove this customer from the blacklist?')) return;
        
        fetch(`index.php?page=customers&action=unblacklist&id=${customerId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Toast.success(data.message);
                location.reload();
            } else {
                Toast.error(data.message);
            }
        })
        .catch(error => {
            Toast.error('An error occurred');
        });
    },
    
    manageLoyalty: function(customerId) {
        $('#loyalty-customer-id').val(customerId);
        $('#loyalty-points-modal').modal('show');
    },
    
    viewTransactions: function(customerId) {
        // Placeholder for transaction viewing
        Toast.info('Transaction history feature coming soon');
    },
    
    handleLoyaltyPoints: function(e) {
        e.preventDefault();
        
        var formData = new FormData(e.target);
        var customerId = formData.get('customer_id');
        
        fetch(`index.php?page=customers&action=addLoyaltyPoints&id=${customerId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Toast.success(data.message);
                $('#loyalty-points-modal').modal('hide');
                location.reload();
            } else {
                Toast.error(data.message);
            }
        })
        .catch(error => {
            Toast.error('An error occurred');
        });
    },
    
    handleBlacklist: function(e) {
        e.preventDefault();
        
        var formData = new FormData(e.target);
        var customerId = formData.get('customer_id');
        
        fetch(`index.php?page=customers&action=blacklist&id=${customerId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Toast.success(data.message);
                $('#blacklist-modal').modal('hide');
                location.reload();
            } else {
                Toast.error(data.message);
            }
        })
        .catch(error => {
            Toast.error('An error occurred');
        });
    },
    
    exportData: function() {
        // Placeholder for export functionality
        Toast.info('Export feature coming soon');
    },
    
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Initialize module when DOM is ready
$(document).ready(function() {
    CustomerModule.init();
});
</script>
