/**
 * Users Management Module
 * Handles all user management operations with role-based access control
 */

var UsersModule = {
    currentPage: 1,
    currentFilters: {},
    selectedUsers: [],
    
    init: function() {
        this.bindEvents();
        this.loadInitialData();
        this.initializeFilters();
    },
    
    bindEvents: function() {
        // Filter form submission
        $('#user-filters-form').on('submit', function(e) {
            e.preventDefault();
            UsersModule.applyFilters();
        });
        
        // Create user modal
        $('#create-user-form').on('submit', function(e) {
            e.preventDefault();
            UsersModule.createUser();
        });
        
        // Edit user modal
        $('#edit-user-form').on('submit', function(e) {
            e.preventDefault();
            UsersModule.updateUser();
        });
        
        // Search input with debouncing
        $('#search-input').on('input', function(e) {
            clearTimeout(UsersModule.searchTimeout);
            UsersModule.searchTimeout = setTimeout(function() {
                UsersModule.applyFilters();
            }, 500);
        });
        
        // Filter changes
        $('#role-filter, #branch-filter, #status-filter').on('change', function() {
            UsersModule.applyFilters();
        });
        
        // Table entries
        $('#table-entries').on('change', function() {
            UsersModule.applyFilters();
        });
        
        // Select all checkbox
        $('#select-all-users').on('change', function() {
            UsersModule.toggleSelectAll();
        });
        
        // Individual user checkboxes
        $(document).on('change', '[id^="user-checkbox-"]', function() {
            UsersModule.updateSelectAllCheckbox();
        });
        
        // Refresh button
        $('#refresh-users-btn').on('click', function() {
            UsersModule.loadUsers();
        });
        
        // Export button
        $('#export-users-btn').on('click', function() {
            UsersModule.exportUsers();
        });
        
        // Bulk operations
        $('#bulk-operations-btn').on('click', function() {
            UsersModule.showBulkOperationsModal();
        });
        
        // User detail modal
        $(document).on('click', '[id^="user-detail-btn-"]', function() {
            var userId = $(this).attr('id').split('-')[3];
            UsersModule.showUserDetail(userId);
        });
        
        // Edit button in detail modal
        $('#edit-from-detail-btn').on('click', function() {
            var userId = $('#edit-user-id').val();
            $('#userDetailModal').modal('hide');
            $('#editUserModal').modal('show');
            UsersModule.populateEditForm(userId);
        });
        
        // Status toggle buttons
        $(document).on('click', '[id^="user-status-btn-"]', function() {
            var userId = $(this).attr('id').split('-')[3];
            UsersModule.toggleUserStatus(userId);
        });
        
        // Edit button
        $(document).on('click', '[id^="user-edit-btn-"]', function() {
            var userId = $(this).attr('id').id.split('-')[3];
            UsersModule.showEditUserModal(userId);
        });
        
        // Delete button
        $(document).on('click', '[id^="staff-delete-btn-"]', function() {
            var userId = $(this).attr('id').id.split('-')[3];
            UsersModule.confirmDeleteUser(userId);
        });
        
        // Bulk operation buttons
        $('.bulk-activate-btn, .bulk-deactivate-btn, .bulk-delete-btn').on('click', function() {
            var operation = $(this).data('operation');
            UsersModule.setBulkOperation(operation);
        });
        
        // Execute bulk operation
        $('#execute-bulk-operation').on('click', function() {
            UsersModule.executeBulkOperation();
        });
        
        // Modal close events
        $('#createUserModal, #editUserModal, #userDetailModal, #bulkOperationsModal').on('hidden.bs.modal', function() {
            UsersModule.resetForms();
        });
    },
    
    loadInitialData: function() {
        this.loadUsers();
        this.loadUserStatistics();
    },
    
    initializeFilters: function() {
        // Set initial filter values
        $('#search-input').val(this.currentFilters.search || '');
        $('#role-filter').val(this.currentFilters.role_filter || '');
        $('#branch-filter').val(this.currentFilters.branch_filter || '');
        $('#status-filter').val(this.currentFilters.status_filter || '');
        $('#table-entries').val(this.currentPage);
    },
    
    loadUsers: function() {
        this.showLoading(true);
        
        var filters = {
            page: this.currentPage,
            limit: parseInt($('#table-entries').val()),
            search: $('#search-input').val(),
            role_filter: $('#role-filter').val(),
            branch_filter: $('#branch-filter').val(),
            status_filter: $('#status_filter').val()
        };
        
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=getUsers',
            type: 'GET',
            data: filters,
            dataType: 'json',
            success: function(response) {
                UsersModule.handleUsersResponse(response);
            },
            error: function(xhr, status, error) {
                UsersModule.showLoading(false);
                UsersModule.showToast('Gagal memuat data pengguna: ' + error, 'error');
            }
        });
    },
    
    handleUsersResponse: function(response) {
        this.showLoading(false);
        
        if (response.status !== 'success') {
            UsersModule.showToast(response.message || 'Gagal memuat data pengguna', 'error');
            return;
        }
        
        var data = response.data || [];
        var pagination = response.pagination || {};
        
        // Update statistics
        this.updateStatistics(response.statistics || {});
        
        // Update table
        this.updateUsersTable(data);
        
        // Update pagination
        this.updatePagination(pagination);
        
        // Update filter badges
        this.updateFilterDisplay();
        
        // Clear selection
        this.selectedUsers = [];
        this.updateSelectAllCheckbox();
        
        UsersModule.showToast('Data pengguna berhasil dimuat', 'success');
    },
    
    updateUsersTable: function(users) {
        var tbody = $('#users-table-body');
        tbody.empty();
        
        if (!Array.isArray(users) || users.length === 0) {
            tbody.append('<tr><td colspan="11" class="text-center text-muted">Tidak ada data pengguna</td></tr>');
            return;
        }
        
        users.forEach(function(user) {
            var row = UsersModule.buildUserRow(user);
            tbody.append(row);
        });
    },
    
    buildUserRow: function(user) {
        var row = $('<tr>');
        
        // Checkbox
        row.append('<td><input type="checkbox" class="form-check-input" id="user-checkbox-' + user.id_user + '" value="' + user.id_user + '"></td>');
        
        // ID
        row.append('<td>' + user.id_user + '</td>');
        
        // Avatar
        var avatarClass = UsersModule.getRoleClass(user.role_level);
        var avatarText = user.full_name.charAt(0).toUpperCase();
        row.append('<td><div class="user-avatar ' + avatarClass + '">' + avatarText + '</div></td>');
        
        // Username
        row.append('<td><span class="fw-medium">' + user.username + '</span></td>');
        
        // Full Name
        row.append('<td>' + user.full_name + '</td>');
        
        // Email
        row.append('<td><a href="mailto:' + user.email + '">' + user.email + '</a></td>');
        
        // Role
        var roleBadgeClass = UsersModule.getRoleBadgeClass(user.role_level);
        row.append('<td><span class="badge ' + roleBadgeClass + '">' + user.role_name + '</span></span></td>');
        
        // Branch
        if (user.branch_name) {
            row.append('<td><span class="badge bg-info">' + user.branch_name + '</span></span></td>');
        } else {
            row.append('<td>-</td>');
        }
        
        // Status
        var statusClass = user.is_active ? 'success' : 'secondary';
        var statusText = user.is_active ? 'Aktif' : 'Tidak Aktif';
        row.append('<td><span class="badge ' + statusClass + '">' + statusText + '</span></span></td>');
        
        // Actions
        var actions = '<div class="action-buttons">';
        actions += '<button class="btn btn-sm btn-outline-primary btn-edit" id="user-edit-btn-' + user.id_user + '" onclick="UsersModule.showEditUserModal(' + user.id_user + ')"><i class="bi bi-pencil"></i></button>';
        actions += '<button class="btn btn-outline-info" id="user-detail-btn-' + user.id_user + '" onclick="UsersModule.showUserDetail(' + user.id_user + ')"><i class="bi bi-eye"></i></button>';
        actions += '<button class="btn btn-outline-warning" id="user-status-btn-' + user.id_user + '" onclick="UsersModule.toggleUserStatus(' + user.id_user + ')"><i class="bi bi-toggle-on"></i></button>';
        actions += '<button class="btn btn-outline-danger" id="staff-delete-btn-' + user.id_user + '" onclick="UsersModule.confirmDeleteUser(' + user.id_user + ')"><i class="bi-trash"></i></button>';
        actions += '</div>';
        row.append('<td>' + actions + '</td>');
        
        return row;
    },
    
    getRoleClass: function(roleLevel) {
        switch(roleLevel) {
            case 1: return 'owner'; // Super Admin
            case 2: return 'manager'; // Owner
            case 3: return 'director'; // Director
            case 4: return 'manager'; // Manager
            case 5: return 'supervisor'; // Supervisor
            case 6: return 'cashier'; // Cashier
            case 7: return 'staff'; // Staff
            case 8: return 'security'; // Security
            default: return 'staff';
        }
    },
    
    getRoleBadgeClass: function(roleLevel) {
        switch (roleLevel) {
            case 1: return 'bg-dark'; // Super Admin
            case 2: return 'bg-success'; // Owner
            case 3: return 'bg-primary'; // Director
            case 4: return 'bg-info'; // Manager
            case 5: return 'bg-warning'; // Supervisor
            case 6: return 'bg-warning'; // Cashier
            case 7: return 'bg-secondary'; // Staff
            case 8: return 'bg-danger'; // Security
            default: return 'bg-secondary';
        }
    },
    
    updateStatistics: function(statistics) {
        $('#total-users').text(statistics.total_users || 0);
        $('#active-users').text(statistics.active_users || 0);
        $('#inactive-users').text(statistics.inactive_users || 0);
        $('#user-roles').text(statistics.user_roles || 0);
    },
    
    updatePagination: function(pagination) {
        var currentPage = pagination.current_page || 1;
        var totalPages = pagination.pages || 1;
        var startRecord = (currentPage - 1) * (pagination.per_page || 10) + 1;
        var endRecord = Math.min(startRecord + (pagination.per_page || 10) - 1, pagination.total || 0);
        
        $('#pagination-info').text('Menampilkan ' + startRecord + '-' + endRecord + ' dari ' + pagination.total + ' data');
        
        var paginationHtml = '';
        
        // Previous button
        if (currentPage > 1) {
            paginationHtml += '<li class="page-item"><a class="page-link" href="#" onclick="UsersModule.goToPage(' + (currentPage - 1) + ')" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
        }
        
        // Page numbers
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, currentPage + 2);
        
        for (var i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHtml += '<li class="page-item active"><a class="page-link" href="#" onclick="UsersModule.goToPage(' + i + ')" aria-current="true">' + i + '</a></li>';
            } else {
                paginationHtml += '<li class="page-item"><a class="page-link" href="#" onclick="UsersModule.goToPage(' + i + ')" aria-label="Go to page ' + i + '">' + i + '</a></li>';
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHtml += '<li class="page-item"><a class="page-link" href="#" onclick="UsersModule.goToPage(' + (currentPage + 1) + ')" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        }
        
        $('#pagination').html(paginationHtml);
    },
    
    goToPage: function(page) {
        this.currentPage = page;
        this.loadUsers();
    },
    
    showCreateUserModal: function() {
        $('#createUserModal').modal('show');
        this.resetCreateForm();
    },
    
    showEditUserModal: function(userId) {
        this.loadUserData(userId);
        $('#editUserModal').modal('show');
    },
    
    loadUserData: function(userId) {
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=getUser&id=' + userId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status !== 'success') {
                    UsersModule.showToast('Gagal memuat data pengguna: ' + (response.message || 'Unknown error'), 'error');
                    return;
                }
                
                var user = response.data;
                $('#edit-user-id').val(user.id_user);
                $('#edit-username').val(user.username);
                $('#edit-email').val(user.email);
                $('#edit-full-name').val(user.full_name);
                $('#edit-phone').val(user.phone || '');
                $('#edit-address').val(user.address || '');
                $('#edit-role-id').val(user.role_id);
                $('#edit-branch-id').val(user.branch_id || '');
                $('#edit-is-active').val(user.is_active ? '1' : '0');
                
                // Reset password field
                $('#edit-password').val('');
                $('#edit-confirm-password').val('');
            },
            error: function() {
                UsersModule.showToast('Gagal memuat data pengguna: ' + error, 'error');
            }
        });
    },
    
    populateEditForm: function(userId) {
        // Form will be populated by loadUserData
    },
    
    createUser: function() {
        var form = $('#create-user-form')[0];
        
        // Validate form
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        var formData = new FormData(form);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=create',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#createUserModal').modal('hide');
                UsersModule.loadUsers();
                UsersModule.showToast('Pengguna berhasil dibuat', 'success');
                form.reset();
                form.classList.remove('was-validated');
            },
            error: function(xhr, status, error) {
                UsersModule.showToast('Gagal membuat pengguna: ' + error, 'error');
            }
        });
    },
    
    updateUser: function() {
        var form = $('#edit-user-form')[0];
        
        // Validate form
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        var formData = new FormData(form);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=update',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#editUserModal').modal('hide');
                UsersModule.loadUsers();
                UsersModule.showToast('Pengguna berhasil diupdate', 'success');
                form.reset();
                form.classList.remove('was-validated');
            },
            error: function(xhr, status, error) {
                UsersModule.showToast('Gagal update pengguna: ' + error, 'error');
            }
        });
    },
    
    toggleUserStatus: function(userId) {
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=toggleStatus',
            type: 'POST',
            data: {id: userId},
            dataType: 'json',
            success: function(response) {
                UsersModule.loadUsers();
                var statusText = response.is_active ? 'diaktifkan' : 'dinonaktifkan';
                UsersModule.showToast('Status pengguna berhasil diubah: ' + statusText, 'success');
            },
            error: function(xhr, status, error) {
                UsersModule.showToast('Gagal mengubah status pengguna: ' + error, 'error');
            }
        });
    },
    
    confirmDeleteUser: function(userId) {
        var user = this.getUserRow(userId);
        if (!user) {
            UsersModule.showToast('Pengguna tidak ditemukan', 'warning');
            return;
        }
        
        var confirmMessage = 'Apakah Anda yakin ingin menghapus pengguna "' + user.full_name + '"?';
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=delete',
            type: 'POST',
            data: {id: userId},
            dataType: 'json',
            success: function(response) {
                UsersModule.loadUsers();
                UsersModule.showToast('Pengguna berhasil dihapus', 'success');
            },
            error: function(xhr, status, error) {
                UsersModule.showToast('Gagal menghapus pengguna: ' + error, 'error');
            }
        });
    },
    
    getUserRow: function(userId) {
        // Find user row by ID
        var row = $('#user-row-' + userId);
        if (row.length === 0) {
            return null;
        }
        
        return {
            id_user: row.find('td:nth-child(1)').text(),
            full_name: row.find('td:nth-child(4)')text(),
            username: row.find('td:nth-child(3)'.text()),
            role_name: row.find('td:nth-child(7)'.text()
        };
    },
    
    showUserDetail: function(userId) {
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=getUser&id=' + userId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status !== 'success') {
                    UsersModule.showToast('Gagal memuat detail pengguna', 'error');
                    return;
                }
                
                var user = response.data;
                
                // Update detail modal content
                $('#detail-avatar').html(user.full_name.charAt(0).toUpperCase());
                $('#detail-full-name').text(user.full_name);
                $('#detail-username').text('@' + user.username);
                $('#detail-email').text(user.email);
                $('#detail-phone').text(user.phone || '-');
                $('#detail-address').text(user.address || '-');
                $('#detail-role').text(user.role_name);
                $('#detail-branch').text(user.branch_name || '-');
                $('#detail-status').text(user.is_active ? 'Aktif' : 'Tidak Aktif');
                $('#detail-last-login').text(user.last_login || 'Belum pernah login');
                
                // Load activity log
                UsersModule.loadUserActivityLog(userId);
                
                $('#userDetailModal').modal('show');
            },
            error: function() {
                UsersModule.showToast('Gagal memuat detail pengguna', 'error');
            }
        });
    },
    
    loadUserActivityLog: function(userId) {
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=getUserActivityLog&id=' + userId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var logHtml = '';
                
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(log) {
                        logHtml += '<div class="d-flex justify-content-between align-items-center border-bottom pb-2">';
                        logHtml += '<small class="text-muted">' + log.created_at + '</small>';
                        logHtml += '<span class="badge bg-info">' + log.activity_display + '</span>';
                        logHtml += '</div>';
                    });
                } else {
                    logHtml = '<p class="text-muted">Tidak ada aktivitas yang tercatat</p>';
                }
                
                $('#detail-activity-log').html(logHtml);
            },
            error: function() {
                $('#detail-activity-log').html('<p class="text-muted">Gagal memuat log aktivitas</p>');
            }
        });
    },
    
    showBulkOperationsModal: function() {
        if (this.selectedUsers.length === 0) {
            UsersModule.showToast('Pilih pengguna terlebih dahulu', 'warning');
            return;
        }
        
        $('#bulk-operations-modal').modal('show');
    },
    
    setBulkOperation: function(operation) {
        $('.bulk-activate-btn, .bulk-deactivate-btn, .bulk-delete-btn').removeClass('btn-outline-success, btn-outline-warning, btn-outline-danger');
        
        switch (operation) {
            case 'activate':
                $('.bulk-activate-btn').addClass('btn-outline-success');
                break;
            case 'deactivate':
                $('.bulk-deactivate-btn').addClass('btn-outline-warning');
                break;
            case 'delete':
                $('.bulk-delete-btn').addClass('btn-outline-danger');
                break;
        }
        
        $('#bulk-operation').data('operation', operation);
    },
    
    executeBulkOperation: function() {
        var operation = $('#bulk-operation').data('operation');
        var userIds = this.selectedUsers;
        
        if (users.length === 0) {
            UsersModule.showToast('Tidak ada pengguna yang dipilih', 'warning');
            return;
        }
        
        $.ajax({
            url: BASE_URL + '/index.php?page=users&action=bulkOperations',
            type: 'POST',
            data: {
                operation: operation,
                user_ids: users
            },
            dataType: 'json',
            success: function(response) {
                $('#bulk-operations-modal').modal('hide');
                UsersModule.loadUsers();
                UsersModule.showToast('Operasi ' + operation + ' berhasil dilakukan', 'success');
                
                // Clear selection
                UsersModule.selectedUsers = [];
                $('#select-all-users').prop('checked', false);
                UsersModule.updateSelectAllCheckbox();
                
                // Reset bulk operation
                $('.bulk-activate-btn, .bulk-deactivate-btn, .bulk-delete-btn').removeClass('btn-outline-success, btn-outline-warning, btn-outline-danger');
            },
            error: function(xhr, status, error) {
                UsersModule.showToast('Gagal melakukan operasi massal: ' + error, 'error');
            }
        });
    },
    
    exportUsers: function() {
        // TODO: Implement CSV export functionality
        UsersModule.showToast('Export fitur akan diimplementasi di Phase 3', 'info');
    },
    
    refreshUsers: function() {
        this.loadUsers();
    },
    
    showLoading: function(show) {
        $('#loading-spinner').toggle(show);
        $('#users-table').toggle(!show);
    },
    
    showToast: function(message, type) {
        // Create toast notification
        var toastHtml = '<div class="toast align-items-center text-white bg-' + type + ' border-0" role="alert">' +
            '<div class="d-flex">' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div>' +
            '</div>';
        
        var toastContainer = $('#globalToastContainer');
        if (toastContainer.length === 0) {
            toastContainer = $('<div id="globalToastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            $('body').append(toastContainer);
        }
        
        var toastElement = $(toastHtml);
        toastContainer.append(toastElement);
        
        var toast = new bootstrap.Toast(toastElement[0]);
        toast.show();
        
        // Auto-remove toast after 5 seconds
        setTimeout(function() {
            toastElement.remove();
        }, 5000);
    },
    
    resetCreateForm: function() {
        var form = $('#create-user-form')[0];
        form[0].reset();
        form.removeClass('was-validated');
        
        // Clear validation messages
        $('.invalid-feedback').empty();
        
        // Set default values
        $('#create-role-id').val('');
        $('#create-branch-id').val('');
        $('#create-is-active').prop('checked', true);
    },
    
    resetEditForm: function() {
        var form = $('#edit-user-form')[0];
        form[0].reset();
        form.removeClass('was-validated');
        
        // Clear validation messages
        $('.invalid-feedback').empty();
        
        // Clear password fields
        $('#edit-password').val('');
        $('#edit-confirm-password').val('');
    }
};

// Auto-initialize when document is ready
$(document).ready(function() {
    UsersModule.init();
});
