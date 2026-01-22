<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Cabang</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?page=branches&action=create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Cabang
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="index.php" method="GET" class="row g-3">
                <input type="hidden" name="page" value="branches">
                
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama cabang, kode..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Perusahaan Induk</label>
                    <select name="company_id" class="form-select">
                        <option value="">Semua Perusahaan</option>
                        <?php foreach ($companies as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo $company_id == $id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2">Filter</button>
                    <a href="index.php?page=branches" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Nama Cabang</th>
                            <th>Perusahaan Induk</th>
                            <th>Tipe</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($branches)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-store-slash fa-2x mb-3"></i>
                                    <p>Belum ada data cabang</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($branches as $branch): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($branch['branch_code']); ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($branch['branch_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($branch['owner_name']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($branch['company_name'] ?? '-'); ?></td>
                                    <td><?php echo ucfirst($branch['branch_type']); ?></td>
                                    <td>
                                        <div><i class="fas fa-phone fa-xs me-1"></i> <?php echo htmlspecialchars($branch['phone'] ?? '-'); ?></div>
                                        <?php if (!empty($branch['email'])): ?>
                                            <small class="text-muted"><i class="fas fa-envelope fa-xs me-1"></i> <?php echo htmlspecialchars($branch['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($branch['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="index.php?page=branches&action=edit&id=<?php echo $branch['id_branch']; ?>" 
                                               class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
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
                <nav>
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
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus cabang ini?')) {
        window.location.href = 'index.php?page=branches&action=delete&id=' + id;
    }
}
</script>
