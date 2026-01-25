<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manajemen Produk</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?page=products&action=create" class="btn btn-sm btn-primary" id="product-create-btn">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="product-filter-form" action="index.php" method="GET" class="row g-3">
                <input type="hidden" name="page" value="products">
                
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" id="product-search-input" name="q" class="form-control" placeholder="Nama produk, kode, barcode..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <select id="product-category-filter" name="category_id" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php if (isset($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id_category']; ?>" <?php echo (isset($category_id) && $category_id == $cat['id_category']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2" id="product-filter-btn">Filter</button>
                    <a href="index.php?page=products" class="btn btn-outline-secondary" id="product-reset-btn">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="product-table" class="table table-hover mb-0">
                    <thead id="product-table-head" class="bg-light">
                        <tr>
                            <th id="product-col-code">Kode</th>
                            <th id="product-col-name">Nama Produk</th>
                            <th id="product-col-category">Kategori</th>
                            <th id="product-col-unit">Satuan</th>
                            <th id="product-col-purchase-price" class="text-end">Harga Beli</th>
                            <th id="product-col-selling-price" class="text-end">Harga Jual</th>
                            <th id="product-col-stock" class="text-center">Stok</th>
                            <th id="product-col-status" class="text-center">Status</th>
                            <th id="product-col-actions" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        <!-- Data loaded via Controller/AJAX or PHP render -->
                        <?php if (empty($products ?? [])): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-box-open fa-2x mb-3"></i>
                                    <p>Belum ada data produk</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr id="product-row-<?php echo $product['id_product']; ?>">
                                    <td id="product-code-<?php echo $product['id_product']; ?>"><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($product['product_code']); ?></span></td>
                                    <td id="product-name-<?php echo $product['id_product']; ?>">
                                        <div class="fw-bold"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                        <?php if (!empty($product['barcode'])): ?>
                                            <small class="text-muted"><i class="fas fa-barcode me-1"></i><?php echo htmlspecialchars($product['barcode']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td id="product-category-<?php echo $product['id_product']; ?>"><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                                    <td id="product-unit-<?php echo $product['id_product']; ?>"><?php echo htmlspecialchars($product['unit']); ?></td>
                                    <td id="product-purchase-price-<?php echo $product['id_product']; ?>" class="text-end">Rp <?php echo number_format($product['purchase_price'], 0, ',', '.'); ?></td>
                                    <td id="product-selling-price-<?php echo $product['id_product']; ?>" class="text-end fw-bold text-primary">Rp <?php echo number_format($product['selling_price'], 0, ',', '.'); ?></td>
                                    <td id="product-stock-<?php echo $product['id_product']; ?>" class="text-center">-</td> <!-- Stock to be implemented with Inventory module -->
                                    <td id="product-status-<?php echo $product['id_product']; ?>" class="text-center">
                                        <?php if ($product['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td id="product-actions-<?php echo $product['id_product']; ?>" class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="index.php?page=products&action=edit&id=<?php echo $product['id_product']; ?>" 
                                               class="btn btn-outline-secondary" id="product-edit-btn-<?php echo $product['id_product']; ?>" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" id="product-delete-btn-<?php echo $product['id_product']; ?>" 
                                                    onclick="confirmDelete(<?php echo $product['id_product']; ?>)" title="Hapus">
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
            
            <!-- Pagination -->
            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <small class="text-muted">
                        Menampilkan <?php echo count($products); ?> dari <?php echo $pagination['total_items']; ?> data
                    </small>
                    <nav id="product-pagination">
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($pagination['current_page'] == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=products&page_num=<?php echo $i; ?>&q=<?php echo htmlspecialchars($search ?? ''); ?>&category_id=<?php echo htmlspecialchars($category_id ?? ''); ?>">
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
</div>

<script>
// Product Management Module
var ProductModule = {
    init: function() {
        this.bindEvents();
        this.initializeSearch();
    },
    
    bindEvents: function() {
        // Form submission
        $('#product-filter-form').on('submit', this.handleFilter);
        
        // Search input
        $('#product-search-input').on('input', this.handleSearch);
        
        // Category filter
        $('#product-category-filter').on('change', this.handleFilter);
        
        // Edit buttons (delegated for dynamic content)
        $(document).on('click', '[id^="product-edit-btn-"]', this.handleEdit);
        
        // Delete buttons (delegated for dynamic content)
        $(document).on('click', '[id^="product-delete-btn-"]', this.handleDelete);
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
        var productId = $(this).attr('id').split('-')[3];
        
        // Navigate to edit page
        window.location.href = 'index.php?page=products&action=edit&id=' + productId;
    },
    
    handleDelete: function(e) {
        e.preventDefault();
        var productId = $(this).attr('id').split('-')[3];
        
        if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
            // AJAX delete request
            $.ajax({
                url: 'index.php?page=products&action=delete',
                type: 'POST',
                data: {
                    id: productId,
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Remove row from table
                        $('#product-row-' + productId).fadeOut(500, function() {
                            $(this).remove();
                        });
                        
                        // Show success message
                        ProductModule.showNotification('Produk berhasil dihapus', 'success');
                    } else {
                        // Show error message
                        ProductModule.showNotification('Error: ' + (response.message || 'Terjadi kesalahan'), 'error');
                    }
                },
                error: function() {
                    // Fallback to legacy function
                    ProductModule.legacyDelete(productId);
                }
            });
        }
    },
    
    legacyDelete: function(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) return;
        http.ajaxJSON({
            url: 'index.php?page=products&action=delete',
            method: 'POST',
            data: {id:id},
            errorMessage: 'Gagal menghapus produk'
        }).then(function(data){
            if (data.status === 'success') { 
                ProductModule.showNotification('Produk berhasil dihapus', 'success'); 
                location.reload(); 
            }
            else { 
                ProductModule.showNotification('Error: ' + (data.message || 'Gagal menghapus produk'), 'error'); 
            }
        });
    },
    
    initializeSearch: function() {
        // Add clear button to search input
        var searchInput = $('#product-search-input');
        if (searchInput.length && !searchInput.next('.clear-search').length) {
            searchInput.wrap('<div class="position-relative"></div>');
            searchInput.after('<button type="button" class="btn btn-outline-secondary btn-sm clear-search" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%);"><i class="fas fa-times"></i></button>');
        }
        
        // Clear search functionality
        $('.clear-search').on('click', function() {
            $('#product-search-input').val('');
            window.location.href = 'index.php?page=products';
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
    ProductModule.init();
});

// Legacy function for backward compatibility
function confirmDelete(id) {
    ProductModule.handleDelete.call({id: 'product-delete-btn-' + id});
}
</script>
