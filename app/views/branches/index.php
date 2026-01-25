<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Cabang</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?page=branches&action=create" class="btn btn-sm btn-primary" id="branch-create-btn">
                <i class="fas fa-plus me-1"></i> Tambah Cabang
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="branch-filter-form" action="index.php" method="GET" class="row g-3">
                <input type="hidden" name="page" value="branches">
                
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" id="branch-search-input" name="q" class="form-control" placeholder="Nama cabang, kode..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Perusahaan Induk</label>
                    <select id="branch-company-filter" name="company_id" class="form-select">
                        <option value="">Semua Perusahaan</option>
                        <?php foreach ($companies as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo $company_id == $id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2" id="branch-filter-btn">Filter</button>
                    <a href="index.php?page=branches" class="btn btn-outline-secondary" id="branch-reset-btn">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="branch-table" class="table table-hover mb-0">
                    <thead id="branch-table-head" class="bg-light">
                        <tr>
                            <th id="branch-col-code">Kode</th>
                            <th id="branch-col-name">Nama Cabang</th>
                            <th id="branch-col-company">Perusahaan Induk</th>
                            <th id="branch-col-type">Tipe</th>
                            <th id="branch-col-contact">Kontak</th>
                            <th id="branch-col-status">Status</th>
                            <th id="branch-col-actions" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="branch-table-body">
                        <?php if (empty($branches)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-store-slash fa-2x mb-3"></i>
                                    <p>Belum ada data cabang</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($branches as $branch): ?>
                                <tr id="branch-row-<?php echo $branch['id_branch']; ?>">
                                    <td id="branch-code-<?php echo $branch['id_branch']; ?>"><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($branch['branch_code']); ?></span></td>
                                    <td id="branch-name-<?php echo $branch['id_branch']; ?>">
                                        <div class="fw-bold"><?php echo htmlspecialchars($branch['branch_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($branch['owner_name']); ?></small>
                                    </td>
                                    <td id="branch-company-<?php echo $branch['id_branch']; ?>"><?php echo htmlspecialchars($branch['company_name'] ?? '-'); ?></td>
                                    <td id="branch-type-<?php echo $branch['id_branch']; ?>"><?php echo ucfirst($branch['branch_type']); ?></td>
                                    <td id="branch-contact-<?php echo $branch['id_branch']; ?>">
                                        <div><i class="fas fa-phone fa-xs me-1"></i> <?php echo htmlspecialchars($branch['phone'] ?? '-'); ?></div>
                                        <?php if (!empty($branch['email'])): ?>
                                            <small class="text-muted"><i class="fas fa-envelope fa-xs me-1"></i> <?php echo htmlspecialchars($branch['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td id="branch-status-<?php echo $branch['id_branch']; ?>">
                                        <?php if ($branch['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td id="branch-actions-<?php echo $branch['id_branch']; ?>" class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="index.php?page=branches&action=edit&id=<?php echo $branch['id_branch']; ?>" 
                                               class="btn btn-outline-secondary" id="branch-edit-btn-<?php echo $branch['id_branch']; ?>" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" id="branch-delete-btn-<?php echo $branch['id_branch']; ?>"
                                                    onclick="confirmDelete(<?php echo $branch['id_branch']; ?>)" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="card-footer bg-white d-flex justify-content-end">
                <nav id="branch-pagination">
                    <ul class="pagination mb-0">
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i == $pagination['current'] ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=branches&page_num=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&company_id=<?php echo $company_id; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Branch Management Module
var BranchModule = {
    init: function() {
        this.bindEvents();
        this.initializeSearch();
    },
    
    bindEvents: function() {
        // Form submission
        $('#branch-filter-form').on('submit', this.handleFilter);
        
        // Search input
        $('#branch-search-input').on('input', this.handleSearch);
        
        // Company filter
        $('#branch-company-filter').on('change', this.handleFilter);
        
        // Edit buttons (delegated for dynamic content)
        $(document).on('click', '[id^="branch-edit-btn-"]', this.handleEdit);
        
        // Delete buttons (delegated for dynamic content)
        $(document).on('click', '[id^="branch-delete-btn-"]', this.handleDelete);
    },
    
    handleFilter: function(e) {
        e.preventDefault();
        var formData = $(e.target).serialize();
        
        // Redirect with filter parameters
        window.location.href = 'index.php?' + formData;
    },
    
    handleSearch: function(e) {
        var searchTerm = $(e.target).val();
        
        // Debounce search
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(function() {
            // Reload page with search term
            var currentUrl = new URL(window.location);
            currentUrl.searchParams.set('q', searchTerm);
            window.location.href = currentUrl.toString();
        }, 500);
    },
    
    handleEdit: function(e) {
        e.preventDefault();
        var branchId = $(this).attr('id').split('-')[3];
        
        // Navigate to edit page
        window.location.href = 'index.php?page=branches&action=edit&id=' + branchId;
    },
    
    handleDelete: function(e) {
        e.preventDefault();
        var branchId = $(this).attr('id').split('-')[3];
        
        if (confirm('Apakah Anda yakin ingin menghapus cabang ini?')) {
            // AJAX delete request
            $.ajax({
                url: 'index.php?page=branches&action=delete&id=' + branchId,
                type: 'POST',
                data: {
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Remove row from table
                        $('#branch-row-' + branchId).fadeOut(500, function() {
                            $(this).remove();
                        });
                        
                        // Show success message
                        BranchModule.showNotification('Cabang berhasil dihapus', 'success');
                    } else {
                        // Show error message
                        BranchModule.showNotification('Error: ' + (response.message || 'Terjadi kesalahan'), 'error');
                    }
                },
                error: function() {
                    // Fallback to page reload
                    window.location.href = 'index.php?page=branches&action=delete&id=' + branchId;
                }
            });
        }
    },
    
    initializeSearch: function() {
        // Add clear button to search input
        var searchInput = $('#branch-search-input');
        if (searchInput.length && !searchInput.next('.clear-search').length) {
            searchInput.wrap('<div class="position-relative"></div>');
            searchInput.after('<button type="button" class="btn btn-outline-secondary btn-sm clear-search" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%);"><i class="fas fa-times"></i></button>');
        }
        
        // Clear search functionality
        $('.clear-search').on('click', function() {
            $('#branch-search-input').val('');
            window.location.href = 'index.php?page=branches';
        });
    },
    
    showNotification: function(message, type) {
        // Create toast notification
        var toastHtml = '<div class="toast align-items-center text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="d-flex">' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '</div>';
        
        // Add to container
        var toastContainer = $('#globalToastContainer');
        if (toastContainer.length === 0) {
            toastContainer = $('<div id="globalToastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            $('body').append(toastContainer);
        }
        
        var toastElement = $(toastHtml);
        toastContainer.append(toastElement);
        
        // Initialize and show toast
        var toast = new bootstrap.Toast(toastElement[0]);
        toast.show();
    }
};

// Initialize when DOM is ready
$(document).ready(function() {
    BranchModule.init();
});

// Legacy function for backward compatibility
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus cabang ini?')) {
        window.location.href = 'index.php?page=branches&action=delete&id=' + id;
    }
}
</script>
