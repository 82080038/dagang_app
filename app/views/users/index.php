<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Aplikasi Perdagangan Multi-Cabang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/public/assets/css/style.css" rel="stylesheet">
    <style>
        .user-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #007bff;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .user-card.owner {
            border-left-color: #28a745;
        }
        .user-card.manager {
            border-left-color: #17a2b8;
        }
        .user-card.supervisor {
            border-left-color: #ffc107;
        }
        .user-card.cashier {
            border-left-color: #fd7e14;
        }
        .user-card.staff {
            border-left-color: #6c757d;
        }
        .user-card.security {
            border-left-color: #dc3545;
        }
        .user-card.inactive {
            opacity: 0.7;
            border-left-color: #6c757d;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .action-buttons {
            display: flex;
            gap: 0.25rem;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-sm {
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-weight: 500;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        .user-detail-modal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
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
                    <i class="bi bi-people me-2"></i>
                    <?= $title ?>
                </h1>
                <p class="text-muted mb-0">Manajemen pengguna dan akses kontrol</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" onclick="showCreateUserModal()">
                    <i class="bi bi-plus-circle me-2"></i>
                    Tambah User
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="exportUsers()">
                    <i class="bi bi-download me-2"></i>
                    Export
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="refreshUsers()">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3">
                <i class="bi bi-funnel me-2"></i>
                Filter Pengguna
            </h5>
            <form id="user-filters-form" class="row g-3">
                <div class="col-md-3">
                    <label for="search-input" class="form-label">Pencarian</label>
                    <input type="text" class="form-control" id="search-input" name="search" placeholder="Cari berdasarkan nama, email, atau username">
                </div>
                <div class="col-md-2">
                    <label for="role-filter" class="form-label">Peran</label>
                    <select class="form-select" id="role-filter" name="role_filter">
                        <option value="">Semua Peran</option>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="branch-filter" class="form-label">Cabang</label>
                    <select class="form-select" id="branch-filter" name="branch_filter">
                        <option value="">Semua Cabang</option>
                        <?php if (!empty($branches)): ?>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['id_branch'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status-filter" class="form-label">Status</label>
                    <select class="form-select" id="status-filter" name="status_filter">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-light">
                        <i class="bi bi-search me-2"></i>
                        Tampilkan
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
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Users</h6>
                            <h3 class="mb-0" id="total-users">0</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-people fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active Users</h6>
                            <h3 class="mb-0" id="active-users">0</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-person-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Inactive Users</h6>
                            <h3 class="mb-0" id="inactive-users">0</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-person-x fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">User Roles</h6>
                            <h3 class="mb-0" id="user-roles">0</h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-shield-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loading-spinner">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data...</p>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table me-2"></i>
                    Daftar Pengguna
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
                    <table class="table table-hover" id="users-table">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="select-all-users">
                                </th>
                                <th>ID</th>
                                <th>Avatar</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Peran</th>
                                <th>Cabang</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <tr>
                                <td colspan="11" class="text-center text-muted">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Pilih filter untuk menampilkan data
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3" id="pagination-container">
            <div class="text-muted" id="pagination-info">
                Menampilkan 0 dari 0 data
            </div>
            <nav aria-label="Users pagination">
                <ul class="pagination mb-0" id="pagination">
                    <!-- Pagination will be populated dynamically -->
                </ul>
            </nav>
        </div>
    </main>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        Tambah Pengguna Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="create-user-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create-username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="create-username" name="username" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="create-email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-full-name" class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="create-full-name" name="full_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-phone" class="form-label">Telepon</label>
                                <input type="tel" class="form-control" id="create-phone" name="phone">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-role-id" class="form-label">Peran *</label>
                                <select class="form-select" id="create-role-id" name="role_id" required>
                                    <option value="">Pilih Peran</option>
                                    <?php if (!empty($roles)): ?>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-branch-id" class="form-label">Cabang</label>
                                <select class="form-select" id="create-branch-id" name="branch_id">
                                    <option value="">Pilih Cabang</option>
                                    <?php if (!empty($branches)): ?>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?= $branch['id_branch'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-12">
                                <label for="create-address" class="form-label">Alamat</label>
                                <textarea class="form-control" id="create-address" name="address" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="create-password" name="password" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-confirm-password" class="form-label">Konfirmasi Password *</label>
                                <input type="password" class="form-control" id="create-confirm-password" name="confirm_password" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="create-is-active" name="is_active" checked>
                                    <label class="form-check-label" for="create-is-active">
                                        Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-gear me-2"></i>
                        Edit Pengguna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-user-form">
                        <input type="hidden" id="edit-user-id" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit-username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="edit-username" name="username" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="edit-email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-full-name" class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="edit-full-name" name="full_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-phone" class="form-label">Telepon</label>
                                <input type="tel" class="form-control" id="edit-phone" name="phone">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-role-id" class="form-label">Peran *</label>
                                <select class="form-select" id="edit-role-id" name="role_id" required>
                                    <option value="">Pilih Peran</option>
                                    <?php if (!empty($roles)): ?>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-branch-id" class="form-label">Cabang</label>
                                <select class="form-select" id="edit-branch-id" name="branch_id">
                                    <option value="">Pilih Cabang</option>
                                    <?php if (!empty($branches)): ?>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?= $branch['id_branch'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label for="edit-address" class="form-label">Alamat</label>
                                <textarea class="form-control" id="edit-address" name="address" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-password" class="form-label">Password (kosongkan jika tidak diubah)</label>
                                <input type="password" class="form-control" id="edit-password" name="password">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-is-active" class="form-label">Status</label>
                                <select class="form-select" id="edit-is-active" name="is_active">
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Detail Modal -->
    <div class="modal fade" id="userDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-badge me-2"></i>
                        Detail Pengguna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body user-detail-modal">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="user-avatar" id="detail-avatar">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h5 id="detail-full-name"></h5>
                            <p class="text-muted">@<span id="detail-username"></span></p>
                            <p class="text-muted" id="detail-email"></p>
                            <p class="text-muted" id="detail-phone"></p>
                            <p class="text-muted" id="detail-address"></p>
                            <div class="mt-3">
                                <span class="badge bg-primary role-badge" id="detail-role"></span>
                                <span class="badge bg-info" id="detail-branch"></span>
                                <span class="badge bg-success status-badge" id="detail-status"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Informasi Login Terakhir</h6>
                            <p class="text-muted">
                                <i class="bi bi-clock"></i> 
                                <span id="detail-last-login"></span>
                            </p>
                        </div>
                        <div class="col-12">
                            <h6>Log Aktivitas</h6>
                            <div id="detail-activity-log">
                                <!-- Activity log will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="editUserFromDetail()">
                        <i class="bi bi-pencil me-2"></i>
                        Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Operations Modal -->
    <div class="modal fade" id="bulkOperationsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-check2-square me-2"></i>
                        Operasi Massal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Pilih operasi yang ingin dilakukan pada pengguna yang dipilih:</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-success" onclick="bulkOperation('activate')">
                            <i class="bi bi-check-circle me-2"></i>
                            Aktifkan
                        </button>
                        <button type="button" class="btn-outline-warning" onclick="bulkOperation('deactivate')">
                            <i class="bi bi-pause-circle me-2"></i>
                            Nonaktifkan
                        </button>
                        <button type="button" class="btn-outline-danger" onclick="bulkOperation('delete')">
                            <i class="bi bi-trash me-2"></i>
                            Hapus
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="executeBulkOperation()">
                        <i class="bi bi-check-circle me-2"></i>
                        Eksekusi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/jquery-3.6.0.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/users.js"></script>
    <script>
        // Initialize Users Module
        $(document).ready(function() {
            UsersModule.init();
        });
    </script>
</body>
</html>
