<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manajemen Produk</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?page=products&action=create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="index.php" method="GET" class="row g-3">
                <input type="hidden" name="page" value="products">
                
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama produk, kode, barcode..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
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
                    <button type="submit" class="btn btn-secondary me-2">Filter</button>
                    <a href="index.php?page=products" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga Beli</th>
                            <th class="text-end">Harga Jual</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($product['product_code']); ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                        <?php if (!empty($product['barcode'])): ?>
                                            <small class="text-muted"><i class="fas fa-barcode me-1"></i><?php echo htmlspecialchars($product['barcode']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($product['unit']); ?></td>
                                    <td class="text-end">Rp <?php echo number_format($product['purchase_price'], 0, ',', '.'); ?></td>
                                    <td class="text-end fw-bold text-primary">Rp <?php echo number_format($product['selling_price'], 0, ',', '.'); ?></td>
                                    <td class="text-center">-</td> <!-- Stock to be implemented with Inventory module -->
                                    <td class="text-center">
                                        <?php if ($product['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="index.php?page=products&action=edit&id=<?php echo $product['id_product']; ?>" 
                                               class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
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
                    <nav>
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
function confirmDelete(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) return;
    http.ajaxJSON({
        url: 'index.php?page=products&action=delete',
        method: 'POST',
        data: {id:id},
        errorMessage: 'Gagal menghapus produk'
    }).then(function(data){
        if (data.status === 'success') { showToast('success', data.message || 'Berhasil'); location.reload(); }
        else { showToast('error', data.message || 'Gagal menghapus produk'); }
    });
}
</script>
