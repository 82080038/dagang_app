<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-inline-block">
                <select id="branchSelector" class="form-select">
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?= $branch['id_branch'] ?>" <?= $selected_branch == $branch['id_branch'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($branch['branch_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Stok Produk</h6>
            <div class="input-group" style="width: 300px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="inventoryTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-end">Stok Saat Ini</th>
                            <th>Satuan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                        <tr><td colspan="6" class="text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center" id="pagination">
                    <!-- Pagination will be generated via JS -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div class="modal fade" id="updateStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStockForm">
                    <input type="hidden" name="branch_id" id="modalBranchId">
                    <input type="hidden" name="product_id" id="modalProductId">
                    
                    <div class="mb-3">
                        <label class="form-label">Produk</label>
                        <input type="text" class="form-control" id="modalProductName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jenis Perubahan</label>
                        <select class="form-select" name="type" id="modalType" required>
                            <option value="in">Masuk (In)</option>
                            <option value="out">Keluar (Out)</option>
                            <option value="adjustment">Penyesuaian (Opname)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" class="form-control" name="quantity" id="modalQuantity" min="0.01" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveStockBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(run){
    if (window.jQuery) { run(); }
    else {
        var i = setInterval(function(){
            if (window.jQuery) { clearInterval(i); run(); }
        }, 30);
        setTimeout(function(){ clearInterval(i); }, 5000);
    }
})(function() {
$(function() {
    let currentBranchId = $('#branchSelector').val();
    let currentPage = 1;
    let searchQuery = '';

    function loadInventory(pageNum = 1) {
        if (!currentBranchId) return;
        const table = document.getElementById('inventoryTable');
        UI.loadingShow(table.parentElement);
        const params = new URLSearchParams({branch_id: currentBranchId, page_num: pageNum, q: searchQuery, ajax: 1});
        http.fetchJSON('index.php?page=inventory&action=index&'+params.toString(), {errorMessage:'Gagal memuat data'})
            .then(function(response){
                if (response.status === 'success') {
                    renderTable(response.data.inventory);
                    renderPagination(response.data.pagination);
                } else {
                    showToast('error','Gagal memuat data: ' + response.message);
                }
            })
            .catch(function(){ showToast('error','Terjadi kesalahan koneksi'); })
            .finally(function(){ UI.loadingHide(table.parentElement); });
    }

    function renderTable(items) {
        const tbody = $('#inventoryTable tbody');
        tbody.empty();
        
        if (items.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">Tidak ada data stok</td></tr>');
            return;
        }
        
        items.forEach(item => {
            const stockClass = parseFloat(item.quantity) <= 0 ? 'text-danger fw-bold' : '';
            
            const tr = `
                <tr>
                    <td>${item.product_code || '-'}</td>
                    <td>${item.product_name}</td>
                    <td>${item.category_name || '-'}</td>
                    <td class="text-end ${stockClass}">${parseFloat(item.quantity).toLocaleString()}</td>
                    <td>${item.unit || 'Pcs'}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-info btn-update-stock" 
                                data-id="${item.product_id}" 
                                data-name="${item.product_name}"
                                title="Update Stok">
                            <i class="bi bi-arrow-left-right"></i> Update
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });
    }

    function renderPagination(pagination) {
        const ul = $('#pagination');
        ul.empty();
        
        if (pagination.total_pages <= 1) return;
        
        // Previous
        ul.append(`
            <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
            </li>
        `);
        
        // Numbers
        for (let i = 1; i <= pagination.total_pages; i++) {
            ul.append(`
                <li class="page-item ${pagination.current_page === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }
        
        // Next
        ul.append(`
            <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
            </li>
        `);
    }

    // Events
    $('#branchSelector').change(function() {
        currentBranchId = $(this).val();
        currentPage = 1;
        loadInventory();
    });

    $('#searchBtn').click(function() {
        searchQuery = $('#searchInput').val();
        currentPage = 1;
        loadInventory();
    });

    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            searchQuery = $(this).val();
            currentPage = 1;
            loadInventory();
        }
    });

    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const pageNum = $(this).data('page');
        if (pageNum) {
            currentPage = pageNum;
            loadInventory(pageNum);
        }
    });

    // Update Stock Modal
        $(document).on('click', '.btn-update-stock', function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        
        $('#modalBranchId').val(currentBranchId);
        $('#modalProductId').val(productId);
        $('#modalProductName').val(productName);
        $('#modalQuantity').val('');
        $('#modalType').val('in');
        
        new bootstrap.Modal(document.getElementById('updateStockModal')).show();
    });

    $('#saveStockBtn').click(function() {
        const form = $('#updateStockForm');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        $.ajax({
            url: 'index.php?page=inventory&action=update',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('updateStockModal')).hide();
                    loadInventory(currentPage);
                    showToast('success','Stok berhasil diupdate');
                } else {
                    showToast('error','Gagal: ' + response.message);
                }
            },
            error: function() {
                showToast('error','Gagal menyimpan data');
            }
        });
    });

    // Initial Load
    loadInventory();
});
});
</script>
