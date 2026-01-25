<?php
/**
 * Enhanced Companies Management View
 * Advanced jQuery/Ajax and DOM manipulation
 */

// Get company data with enhanced features
$companies = $this->companyModel->getAll(50, 0, ['search' => $_GET['q'] ?? '']);
$statistics = [
    'total_companies' => $this->companyModel->getTotalCount(),
    'active_companies' => $this->companyModel->getTotalCount(['is_active' => 1]),
    'total_branches' => $this->branchModel->getTotalCount(),
    'total_members' => $this->memberModel->getTotalCount()
];
?>

<div class="container-fluid py-4">
    <!-- Enhanced Header with Live Stats -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2><i class="fas fa-building me-2"></i>Manajemen Perusahaan</h2>
                    <p class="text-muted mb-0">Kelola data perusahaan dan cabang dengan fitur real-time</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="exportData()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                    <button type="button" class="btn btn-primary" data-toggle="dynamic-modal" data-url="index.php?page=companies&action=create" data-size="lg">
                        <i class="fas fa-plus me-2"></i>Tambah Perusahaan
                    </button>
                </div>
            </div>
            
            <!-- Live Statistics Cards -->
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card stats-card bg-primary text-white live-update" data-update-url="index.php?page=companies&action=stats" data-interval="5000">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0" id="totalCompanies"><?= $statistics['total_companies'] ?></h4>
                                    <small class="opacity-75">Total Perusahaan</small>
                                </div>
                                <i class="fas fa-building fa-2x opacity-75"></i>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-white" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-success text-white live-update" data-update-url="index.php?page=companies&action=stats" data-interval="5000">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0" id="activeCompanies"><?= $statistics['active_companies'] ?></h4>
                                    <small class="opacity-75">Aktif</small>
                                </div>
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-white" style="width: <?= ($statistics['active_companies'] / $statistics['total_companies']) * 100 ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-info text-white live-update" data-update-url="index.php?page=companies&action=stats" data-interval="5000">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0" id="totalBranches"><?= $statistics['total_branches'] ?></h4>
                                    <small class="opacity-75">Total Cabang</small>
                                </div>
                                <i class="fas fa-store fa-2x opacity-75"></i>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-white" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-warning text-white live-update" data-update-url="index.php?page=companies&action=stats" data-interval="5000">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0" id="totalMembers"><?= $statistics['total_members'] ?></h4>
                                    <small class="opacity-75">Total Member</small>
                                </div>
                                <i class="fas fa-users fa-2x opacity-75"></i>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-white" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters with Real-time Search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control search-input" id="searchInput" 
                               placeholder="Cari perusahaan..." 
                               data-target="companiesTable" 
                               data-url="index.php?page=companies&action=search">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select filter-select" id="typeFilter" data-target="companiesTable">
                        <option value="">Semua Tipe</option>
                        <option value="individual">Individu</option>
                        <option value="pusat">Pusat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select filter-select" id="statusFilter" data-target="companiesTable">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Non-aktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select filter-select" id="levelFilter" data-target="companiesTable">
                        <option value="">Semua Level</option>
                        <option value="1">Level 1</option>
                        <option value="2">Level 2</option>
                        <option value="3">Level 3</option>
                        <option value="4">Level 4</option>
                        <option value="5">Level 5</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button class="btn btn-outline-secondary" onclick="resetFilters()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="toggleColumn('id')">ID</a></li>
                            <li><a class="dropdown-item" href="#" onclick="toggleColumn('type')">Tipe</a></li>
                            <li><a class="dropdown-item" href="#" onclick="toggleColumn('level')">Level</a></li>
                            <li><a class="dropdown-item" href="#" onclick="toggleColumn('branches')">Cabang</a></li>
                            <li><a class="dropdown-item" href="#" onclick="toggleColumn('members')">Member</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Companies Table -->
    <div class="card table-container">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Perusahaan</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-info" id="recordCount"><?= count($companies) ?> records</span>
                <div class="bulk-actions" style="display: none;">
                    <button class="btn btn-sm btn-outline-danger" onclick="bulkDelete()">
                        <i class="fas fa-trash"></i> Hapus (<span class="selected-count">0</span>)
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="bulkActivate()">
                        <i class="fas fa-check"></i> Aktifkan
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="bulkDeactivate()">
                        <i class="fas fa-pause"></i> Non-aktifkan
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover advanced-table" id="companiesTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input select-all-checkbox">
                            </th>
                            <th width="60" class="sortable" data-column="id">ID <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="company_name">Nama Perusahaan <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="company_type">Tipe <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="scalability_level">Level <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="owner_name">Pemilik <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="email">Email <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="phone">Telepon <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="branch_count">Cabang <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="member_count">Member <i class="fas fa-sort ms-1"></i></th>
                            <th class="sortable" data-column="is_active">Status <i class="fas fa-sort ms-1"></i></th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="companiesTableBody">
                        <?php foreach ($companies as $company): ?>
                        <tr data-id="<?= $company['id_company'] ?>" class="table-row">
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="<?= $company['id_company'] ?>">
                            </td>
                            <td><?= $company['id_company'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="company-avatar me-2">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($company['company_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($company['company_code']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $company['company_type'] === 'individual' ? 'info' : 'primary' ?> company-type">
                                    <?= $company['company_type'] === 'individual' ? 'Individu' : 'Pusat' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">Level <?= $company['scalability_level'] ?></span>
                            </td>
                            <td><?= htmlspecialchars($company['owner_name']) ?></td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($company['email']) ?>" class="text-decoration-none">
                                    <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($company['email']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="tel:<?= htmlspecialchars($company['phone']) ?>" class="text-decoration-none">
                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($company['phone']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $company['branch_count'] ?? 0 ?></span>
                            </td>
                            <td>
                                <span class="badge bg-success"><?= $company['member_count'] ?? 0 ?></span>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" type="checkbox" 
                                           data-id="<?= $company['id_company'] ?>"
                                           <?= $company['is_active'] ? 'checked' : '' ?>
                                           onchange="toggleStatus(<?= $company['id_company'] ?>, this.checked)">
                                    <label class="form-check-label"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="viewCompany(<?= $company['id_company'] ?>)"
                                            data-toggle="smart-tooltip" data-content="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            onclick="editCompany(<?= $company['id_company'] ?>)"
                                            data-toggle="smart-tooltip" data-content="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteCompany(<?= $company['id_company'] ?>)"
                                            data-toggle="smart-tooltip" data-content="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Modal Container -->
<div id="dynamicModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript -->
<script>
$(document).ready(function() {
    // Initialize enhanced features
    initEnhancedCompaniesTable();
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        if (!$('.modal.show').length) {
            refreshData();
        }
    }, 30000);
});

function initEnhancedCompaniesTable() {
    // Initialize real-time search
    $('#searchInput').on('input', debounce(function() {
        performSearch();
    }, 300));
    
    // Initialize filters
    $('.filter-select').on('change', function() {
        performSearch();
    });
    
    // Initialize row selection
    initRowSelection();
    
    // Initialize sortable columns
    initSortableColumns();
    
    // Initialize status toggles
    $('.status-toggle').on('change', function() {
        var id = $(this).data('id');
        var status = $(this).prop('checked');
        updateStatus(id, status);
    });
}

function performSearch() {
    var search = $('#searchInput').val();
    var type = $('#typeFilter').val();
    var status = $('#statusFilter').val();
    var level = $('#levelFilter').val();
    
    $.ajax({
        url: 'index.php?page=companies&action=search',
        type: 'GET',
        data: {
            search: search,
            type: type,
            status: status,
            level: level
        },
        dataType: 'json',
        beforeSend: function() {
            $('#companiesTableBody').addClass('loading');
            showLoading();
        },
        success: function(response) {
            if (response.status === 'success') {
                renderCompaniesTable(response.data.companies);
                updateRecordCount(response.data.companies.length);
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Terjadi kesalahan saat mencari data', 'error');
        },
        complete: function() {
            $('#companiesTableBody').removeClass('loading');
            hideLoading();
        }
    });
}

function renderCompaniesTable(companies) {
    var $tbody = $('#companiesTableBody');
    $tbody.empty();
    
    if (companies.length === 0) {
        $tbody.html('<tr><td colspan="13" class="text-center text-muted">Tidak ada data perusahaan</td></tr>');
        return;
    }
    
    companies.forEach(function(company, index) {
        var $row = createCompanyRow(company);
        $row.hide();
        $tbody.append($row);
        
        // Stagger animation
        setTimeout(function() {
            $row.fadeIn(200);
        }, index * 50);
    });
}

function createCompanyRow(company) {
    var statusBadge = company.is_active ? 
        '<div class="form-check form-switch"><input class="form-check-input status-toggle" type="checkbox" data-id="' + company.id_company + '" checked onchange="toggleStatus(' + company.id_company + ', this.checked)"><label class="form-check-label"></label></div>' : 
        '<div class="form-check form-switch"><input class="form-check-input status-toggle" type="checkbox" data-id="' + company.id_company + '" onchange="toggleStatus(' + company.id_company + ', this.checked)"><label class="form-check-label"></label></div>';
    
    var typeBadge = company.company_type === 'individual' ? 
        '<span class="badge bg-info company-type">Individu</span>' : 
        '<span class="badge bg-primary company-type">Pusat</span>';
    
    var $row = $('<tr data-id="' + company.id_company + '" class="table-row">' +
        '<td><input type="checkbox" class="form-check-input row-checkbox" value="' + company.id_company + '"></td>' +
        '<td>' + company.id_company + '</td>' +
        '<td>' +
            '<div class="d-flex align-items-center">' +
                '<div class="company-avatar me-2"><i class="fas fa-building"></i></div>' +
                '<div>' +
                    '<div class="fw-bold">' + company.company_name + '</div>' +
                    '<small class="text-muted">' + company.company_code + '</small>' +
                '</div>' +
            '</div>' +
        '</td>' +
        '<td>' + typeBadge + '</td>' +
        '<td><span class="badge bg-secondary">Level ' + company.scalability_level + '</span></td>' +
        '<td>' + company.owner_name + '</td>' +
        '<td><a href="mailto:' + company.email + '" class="text-decoration-none"><i class="fas fa-envelope me-1"></i>' + company.email + '</a></td>' +
        '<td><a href="tel:' + company.phone + '" class="text-decoration-none"><i class="fas fa-phone me-1"></i>' + company.phone + '</a></td>' +
        '<td><span class="badge bg-info">' + (company.branch_count || 0) + '</span></td>' +
        '<td><span class="badge bg-success">' + (company.member_count || 0) + '</span></td>' +
        '<td>' + statusBadge + '</td>' +
        '<td class="text-center">' +
            '<div class="btn-group" role="group">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="viewCompany(' + company.id_company + ')" data-toggle="smart-tooltip" data-content="Lihat Detail"><i class="fas fa-eye"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-warning" onclick="editCompany(' + company.id_company + ')" data-toggle="smart-tooltip" data-content="Edit"><i class="fas fa-edit"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCompany(' + company.id_company + ')" data-toggle="smart-tooltip" data-content="Hapus"><i class="fas fa-trash"></i></button>' +
            '</div>' +
        '</td>' +
        '</tr>');
    
    return $row;
}

function updateRecordCount(count) {
    $('#recordCount').text(count + ' records');
}

function toggleStatus(id, status) {
    $.ajax({
        url: 'index.php?page=companies&action=update-status',
        type: 'POST',
        data: {
            id: id,
            status: status ? 1 : 0
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showNotification('Status berhasil diperbarui', 'success');
            } else {
                showNotification('Gagal memperbarui status', 'error');
                // Revert checkbox
                $('.status-toggle[data-id="' + id + '"]').prop('checked', !status);
            }
        },
        error: function() {
            showNotification('Terjadi kesalahan saat memperbarui status', 'error');
            $('.status-toggle[data-id="' + id + '"]').prop('checked', !status);
        }
    });
}

function viewCompany(id) {
    loadDynamicModal('index.php?page=companies&action=view&id=' + id, 'lg');
}

function editCompany(id) {
    loadDynamicModal('index.php?page=companies&action=edit&id=' + id, 'lg');
}

function deleteCompany(id) {
    confirmDialog('Apakah Anda yakin ingin menghapus perusahaan ini?', function() {
        $.ajax({
            url: 'index.php?page=companies&action=delete',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showNotification(response.message, 'success');
                    $('tr[data-id="' + id + '"]').fadeOut(300, function() {
                        $(this).remove();
                        updateRecordCount($('#companiesTableBody tr').length);
                    });
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Terjadi kesalahan saat menghapus perusahaan', 'error');
            }
        });
    });
}

function refreshData() {
    performSearch();
    showNotification('Data berhasil diperbarui', 'success', 2000);
}

function clearSearch() {
    $('#searchInput').val('');
    performSearch();
}

function resetFilters() {
    $('#searchInput').val('');
    $('#typeFilter').val('');
    $('#statusFilter').val('');
    $('#levelFilter').val('');
    performSearch();
}

function exportData() {
    var search = $('#searchInput').val();
    var type = $('#typeFilter').val();
    var status = $('#statusFilter').val();
    var level = $('#levelFilter').val();
    
    var params = new URLSearchParams({
        search: search,
        type: type,
        status: status,
        level: level,
        export: 1
    });
    
    window.open('index.php?page=companies&action=export&' + params.toString(), '_blank');
}

function toggleColumn(column) {
    var columnIndex = getColumnIndex(column);
    var $table = $('#companiesTable');
    var $th = $table.find('th').eq(columnIndex);
    var $tds = $table.find('td:nth-child(' + (columnIndex + 1) + ')');
    
    if ($th.is(':visible')) {
        $th.hide();
        $tds.hide();
    } else {
        $th.show();
        $tds.show();
    }
}

function getColumnIndex(column) {
    var columns = {
        'id': 1,
        'type': 3,
        'level': 4,
        'branches': 9,
        'members': 10
    };
    return columns[column] || 0;
}

function initRowSelection() {
    var $selectAll = $('.select-all-checkbox');
    var $checkboxes = $('.row-checkbox');
    
    $selectAll.on('change', function() {
        var isChecked = $(this).prop('checked');
        $checkboxes.prop('checked', isChecked);
        updateBulkActions();
    });
    
    $(document).on('change', '.row-checkbox', function() {
        updateBulkActions();
    });
}

function updateBulkActions() {
    var $checkedBoxes = $('.row-checkbox:checked');
    var $bulkActions = $('.bulk-actions');
    
    if ($checkedBoxes.length > 0) {
        $bulkActions.show();
        $bulkActions.find('.selected-count').text($checkedBoxes.length);
    } else {
        $bulkActions.hide();
    }
}

function bulkDelete() {
    var ids = $('.row-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (ids.length === 0) return;
    
    confirmDialog('Apakah Anda yakin ingin menghapus ' + ids.length + ' perusahaan?', function() {
        $.ajax({
            url: 'index.php?page=companies&action=bulk-delete',
            type: 'POST',
            data: { ids: ids },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showNotification(response.message, 'success');
                    ids.forEach(function(id) {
                        $('tr[data-id="' + id + '"]').fadeOut(300, function() {
                            $(this).remove();
                        });
                    });
                    updateBulkActions();
                    updateRecordCount($('#companiesTableBody tr').length);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Terjadi kesalahan saat menghapus perusahaan', 'error');
            }
        });
    });
}

function bulkActivate() {
    var ids = $('.row-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (ids.length === 0) return;
    
    $.ajax({
        url: 'index.php?page=companies&action=bulk-status',
        type: 'POST',
        data: { ids: ids, status: 1 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showNotification(response.message, 'success');
                ids.forEach(function(id) {
                    $('.status-toggle[data-id="' + id + '"]').prop('checked', true);
                });
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Terjadi kesalahan saat mengaktifkan perusahaan', 'error');
        }
    });
}

function bulkDeactivate() {
    var ids = $('.row-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (ids.length === 0) return;
    
    $.ajax({
        url: 'index.php?page=companies&action=bulk-status',
        type: 'POST',
        data: { ids: ids, status: 0 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showNotification(response.message, 'success');
                ids.forEach(function(id) {
                    $('.status-toggle[data-id="' + id + '"]').prop('checked', false);
                });
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Terjadi kesalahan saat menon-aktifkan perusahaan', 'error');
        }
    });
}

function initSortableColumns() {
    $('.sortable').on('click', function() {
        var $th = $(this);
        var column = $th.data('column');
        var currentOrder = $th.data('order') || 'asc';
        var newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
        
        // Update sort indicators
        $('.sortable').removeClass('asc desc').data('order', null);
        $th.addClass(newOrder).data('order', newOrder);
        
        // Add sorting icon
        $th.find('i').removeClass('fa-sort fa-sort-up fa-sort-down').addClass('fa-sort-' + newOrder);
        
        // Perform sorting
        sortTable(column, newOrder);
    });
}

function sortTable(column, order) {
    var $tbody = $('#companiesTableBody');
    var rows = $tbody.find('tr').get();
    
    rows.sort(function(a, b) {
        var aValue = getColumnValue($(a), column);
        var bValue = getColumnValue($(b), column);
        
        if (order === 'asc') {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });
    
    $tbody.empty();
    rows.forEach(function(row) {
        $tbody.append(row);
    });
}

function getColumnValue($row, column) {
    var columnIndex = {
        'id': 1,
        'company_name': 2,
        'company_type': 3,
        'scalability_level': 4,
        'owner_name': 5,
        'email': 6,
        'phone': 7,
        'branch_count': 8,
        'member_count': 9,
        'is_active': 10
    };
    
    var index = columnIndex[column] || 0;
    return $row.find('td').eq(index).text().trim();
}
</script>

<style>
.stats-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.company-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.table-row {
    transition: all 0.2s ease;
}

.table-row:hover {
    background-color: #f8f9fa;
}

.loading {
    opacity: 0.6;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.live-update.updated {
    animation: highlight 1s ease;
}

@keyframes highlight {
    0% { background-color: #fff3cd; }
    100% { background-color: transparent; }
}

.bulk-actions {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.smart-tooltip {
    background: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 9999;
}

.form-check-input.status-toggle {
    cursor: pointer;
}

.form-check-input.status-toggle:checked {
    background-color: #198754;
    border-color: #198754;
}

.form-check-input.status-toggle:checked:focus {
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}
</style>
</body>
</html>
