<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manajemen Staff</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createStaffModal">
                <i class="fas fa-plus me-1"></i> Tambah Staff
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="staff-filter-form" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <div class="input-group">
                        <input type="text" id="staff-search-input" name="q" class="form-control" 
                               placeholder="Cari nama atau kode staff..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cabang</label>
                    <select id="staff-branch-filter" name="branch_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo $branch['id_branch']; ?>" 
                                    <?php echo (isset($_GET['branch_id']) && $_GET['branch_id'] == $branch['id_branch']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" id="staff-reset-filter" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="staff-table">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Staff</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Posisi</th>
                            <th>Cabang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="staff-table-body">
                        <?php if (empty($staff)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p>Belum ada data staff</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staff as $member): ?>
                                <tr id="staff-row-<?php echo $member['id_member']; ?>">
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($member['member_code']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($member['member_name']); ?></td>
                                    <td>
                                        <?php if ($member['email']): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" 
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($member['email']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($member['phone']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($member['phone']); ?>" 
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($member['phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($member['position']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($member['branch_name'] ?? '-'); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($member['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary staff-edit-btn" 
                                                    data-id="<?php echo $member['id_member']; ?>"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger staff-delete-btn" 
                                                    data-id="<?php echo $member['id_member']; ?>"
                                                    data-name="<?php echo htmlspecialchars($member['member_name']); ?>"
                                                    title="Hapus">
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
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Menampilkan <?php echo count($staff); ?> dari <?php echo $total_records; ?> staff
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=staff&page_num=<?php echo $current_page - 1; ?>&q=<?php echo urlencode($search); ?>&branch_id=<?php echo $_GET['branch_id'] ?? ''; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=staff&page_num=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&branch_id=<?php echo $_GET['branch_id'] ?? ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=staff&page_num=<?php echo $current_page + 1; ?>&q=<?php echo urlencode($search); ?>&branch_id=<?php echo $_GET['branch_id'] ?? ''; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Create Staff Modal -->
<div class="modal fade" id="createStaffModal" tabindex="-1" aria-labelledby="createStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createStaffModalLabel">Tambah Staff Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="staff-create-form">
                    <?php echo Csrf::input(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Cabang <span class="text-danger">*</span></label>
                        <select id="staff-branch-select" name="branch_id" class="form-select" required>
                            <option value="">Pilih Cabang</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo $branch['id_branch']; ?>">
                                    <?php echo htmlspecialchars($branch['branch_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kode Staff <span class="text-danger">*</span></label>
                        <input type="text" id="staff-code-input" name="member_code" class="form-control" required 
                               placeholder="Contoh: STF001">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="staff-name-input" name="member_name" class="form-control" required 
                               placeholder="Masukkan nama lengkap">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="staff-email-input" name="email" class="form-control" 
                               placeholder="email@example.com">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="tel" id="staff-phone-input" name="phone" class="form-control" 
                               placeholder="0812-3456-7890">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Posisi <span class="text-danger">*</span></label>
                        <select id="staff-position-select" name="position" class="form-select" required>
                            <option value="">Pilih Posisi</option>
                            <option value="owner">Owner/Pemilik</option>
                            <option value="manager">Manager</option>
                            <option value="cashier">Kasir</option>
                            <option value="staff">Staff/Karyawan</option>
                            <option value="security">Security</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" id="staff-password-input" name="password" class="form-control" required 
                               placeholder="Minimal 6 karakter">
                        <div class="form-text">Password minimal 6 karakter</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="staff-create-form" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStaffModalLabel">Edit Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="staff-edit-form">
                    <?php echo Csrf::input(); ?>
                    <input type="hidden" id="staff-id-input" name="id_member">
                    
                    <div class="mb-3">
                        <label class="form-label">Cabang <span class="text-danger">*</span></label>
                        <select id="edit-staff-branch-select" name="branch_id" class="form-select" required>
                            <option value="">Pilih Cabang</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kode Staff <span class="text-danger">*</span></label>
                        <input type="text" id="edit-staff-code-input" name="member_code" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="edit-staff-name-input" name="member_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="edit-staff-email-input" name="email" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="tel" id="edit-staff-phone-input" name="phone" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Posisi <span class="text-danger">*</span></label>
                        <select id="edit-staff-position-select" name="position" class="form-select" required>
                            <option value="">Pilih Posisi</option>
                            <option value="owner">Owner/Pemilik</option>
                            <option value="manager">Manager</option>
                            <option value="cashier">Kasir</option>
                            <option value="staff">Staff/Karyawan</option>
                            <option value="security">Security</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="edit-staff-status-select" name="is_active" class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="staff-edit-form" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteStaffModal" tabindex="-1" aria-labelledby="deleteStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteStaffModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus staff <strong id="delete-staff-name"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="confirm-delete-staff" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Staff Module
    var StaffModule = {
        init: function() {
            this.bindEvents();
            this.initializeSearch();
        },
        
        bindEvents: function() {
            // Filter form submission
            $('#staff-filter-form').on('submit', this.handleFilter);
            
            // Reset filter
            $('#staff-reset-filter').on('click', this.resetFilter);
            
            // Create staff
            $('#staff-create-form').on('submit', this.handleCreate);
            
            // Edit staff
            $(document).on('click', '.staff-edit-btn', this.handleEditClick);
            $('#staff-edit-form').on('submit', this.handleEdit);
            
            // Delete staff
            $(document).on('click', '.staff-delete-btn', this.handleDeleteClick);
            $('#confirm-delete-staff').on('click', this.handleDelete);
            
            // Branch filter change
            $('#staff-branch-filter').on('change', this.handleFilter);
        },
        
        initializeSearch: function() {
            var searchInput = $('#staff-search-input');
            var searchTimeout;
            
            searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                var self = this;
                
                searchTimeout = setTimeout(function() {
                    self.handleFilter();
                }, 500);
            });
        },
        
        handleFilter: function(e) {
            if (e) e.preventDefault();
            
            var formData = new FormData(document.getElementById('staff-filter-form'));
            
            // Show loading state
            StaffModule.showLoading();
            
            fetch('index.php?page=staff', {
                method: 'GET',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Update table content
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                var newTableBody = tempDiv.querySelector('#staff-table-body');
                var newPagination = tempDiv.querySelector('.d-flex.justify-content-between');
                
                if (newTableBody) {
                    document.getElementById('staff-table-body').innerHTML = newTableBody.innerHTML;
                }
                
                if (newPagination) {
                    document.querySelector('.d-flex.justify-content-between').outerHTML = newPagination.outerHTML;
                }
                
                StaffModule.hideLoading();
            })
            .catch(error => {
                console.error('Error:', error);
                StaffModule.hideLoading();
                StaffModule.showNotification('Gagal memuat data staff', 'error');
            });
        },
        
        resetFilter: function() {
            document.getElementById('staff-search-input').value = '';
            document.getElementById('staff-branch-filter').value = '';
            this.handleFilter();
        },
        
        handleCreate: function(e) {
            e.preventDefault();
            
            var formData = new FormData(e.target);
            
            // Show loading state
            var submitBtn = e.target.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
            
            fetch('index.php?page=staff&action=create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    StaffModule.showNotification('Staff berhasil ditambahkan!', 'success');
                    
                    // Close modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('createStaffModal'));
                    modal.hide();
                    
                    // Reset form
                    document.getElementById('staff-create-form').reset();
                    
                    // Reload table
                    StaffModule.handleFilter();
                } else {
                    StaffModule.showNotification('Error: ' + (data.message || 'Terjadi kesalahan'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                StaffModule.showNotification('Terjadi kesalahan saat menyimpan staff', 'error');
            })
            .finally(function() {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        },
        
        handleEditClick: function(e) {
            var staffId = $(this).data('id');
            
            // Show loading state
            var btn = $(this);
            var originalHtml = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            btn.prop('disabled', true);
            
            fetch('index.php?page=staff&action=details&id=' + staffId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    var staff = data.data.staff;
                    
                    // Populate edit form
                    $('#edit-staff-id-input').val(staff.id_member);
                    $('#edit-staff-branch-select').val(staff.branch_id);
                    $('#edit-staff-code-input').val(staff.member_code);
                    $('#edit-staff-name-input').val(staff.member_name);
                    $('#edit-staff-email-input').val(staff.email || '');
                    $('#edit-staff-phone-input').val(staff.phone || '');
                    $('#edit-staff-position-select').val(staff.position);
                    $('#edit-staff-status-select').val(staff.is_active ? '1' : '0');
                    
                    // Populate branch options
                    StaffModule.loadBranchOptions('edit-staff-branch-select');
                    
                    // Show modal
                    var modal = new bootstrap.Modal(document.getElementById('editStaffModal'));
                    modal.show();
                } else {
                    StaffModule.showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                StaffModule.showNotification('Gagal memuat data staff', 'error');
            })
            .finally(function() {
                // Reset button state
                btn.html(originalHtml);
                btn.prop('disabled', false);
            });
        },
        
        handleEdit: function(e) {
            e.preventDefault();
            
            var formData = new FormData(e.target);
            
            // Show loading state
            var submitBtn = e.target.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memperbarui...';
            
            fetch('index.php?page=staff&action=edit', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    StaffModule.showNotification('Staff berhasil diperbarui!', 'success');
                    
                    // Close modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('editStaffModal'));
                    modal.hide();
                    
                    // Reload table
                    StaffModule.handleFilter();
                } else {
                    StaffModule.showNotification('Error: ' + (data.message || 'Terjadi kesalahan'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                StaffModule.showNotification('Terjadi kesalahan saat memperbarui staff', 'error');
            })
            .finally(function() {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        },
        
        handleDeleteClick: function(e) {
            var staffId = $(this).data('id');
            var staffName = $(this).data('name');
            
            $('#delete-staff-name').text(staffName);
            $('#confirm-delete-staff').data('id', staffId);
            
            var modal = new bootstrap.Modal(document.getElementById('deleteStaffModal'));
            modal.show();
        },
        
        handleDelete: function() {
            var staffId = $(this).data('id');
            
            // Show loading state
            var btn = $(this);
            var originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Menghapus...');
            btn.prop('disabled', true);
            
            fetch('index.php?page=staff&action=delete&id=' + staffId, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    StaffModule.showNotification('Staff berhasil dihapus!', 'success');
                    
                    // Close modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('deleteStaffModal'));
                    modal.hide();
                    
                    // Remove row from table
                    $('#staff-row-' + staffId).fadeOut(500, function() {
                        $(this).remove();
                    });
                } else {
                    StaffModule.showNotification('Error: ' + (data.message || 'Terjadi kesalahan'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                StaffModule.showNotification('Terjadi kesalahan saat menghapus staff', 'error');
            })
            .finally(function() {
                // Reset button state
                btn.html(originalText);
                btn.prop('disabled', false);
            });
        },
        
        loadBranchOptions: function(selectId, selectedValue) {
            fetch('index.php?page=branches&action=getOptions')
                .then(response => response.json())
                .then(data => {
                    var select = document.getElementById(selectId);
                    select.innerHTML = '<option value="">Pilih Cabang</option>';
                    
                    data.branches.forEach(function(branch) {
                        var option = document.createElement('option');
                        option.value = branch.id_branch;
                        option.textContent = branch.branch_name;
                        if (selectedValue && branch.id_branch == selectedValue) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading branches:', error);
                });
        },
        
        showLoading: function() {
            // Add loading overlay to table
            var table = document.querySelector('.table-responsive');
            if (table) {
                table.style.opacity = '0.5';
                table.style.pointerEvents = 'none';
            }
        },
        
        hideLoading: function() {
            // Remove loading overlay from table
            var table = document.querySelector('.table-responsive');
            if (table) {
                table.style.opacity = '1';
                table.style.pointerEvents = 'auto';
            }
        },
        
        showNotification: function(message, type) {
            // Use toast notification if available, otherwise alert
            if (typeof showToast === 'function') {
                showToast(message, type);
            } else {
                alert(message);
            }
        }
    };
    
    // Initialize module when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        StaffModule.init();
    });
})();
</script>
