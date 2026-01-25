
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Perusahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-actions { min-width: 100px; }
        .status-badge { font-size: 0.8rem; }
        .company-type { font-weight: 600; }
        .action-buttons .btn { margin: 0 2px; }
        .modal-body { max-height: 70vh; overflow-y: auto; }
        .form-floating label { color: #6c757d; }
        .address-display { font-size: 0.9rem; color: #6c757d; }
        .stats-card { transition: transform 0.2s; }
        .stats-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-building me-2"></i>Manajemen Perusahaan</h2>
                        <p class="text-muted mb-0">Kelola data perusahaan dan cabang</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#companyModal">
                        <i class="fas fa-plus me-2"></i>Tambah Perusahaan
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0" id="totalCompanies">0</h4>
                                <small>Total Perusahaan</small>
                            </div>
                            <i class="fas fa-building fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0" id="activeCompanies">0</h4>
                                <small>Aktif</small>
                            </div>
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0" id="totalBranches">0</h4>
                                <small>Total Cabang</small>
                            </div>
                            <i class="fas fa-store fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0" id="totalMembers">0</h4>
                                <small>Total Member</small>
                            </div>
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Cari perusahaan...">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="typeFilter">
                    <option value="">Semua Tipe</option>
                    <option value="individual">Individu</option>
                    <option value="pusat">Pusat</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Non-aktif</option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                    <i class="fas fa-redo me-2"></i>Reset
                </button>
                <button type="button" class="btn btn-success" id="exportData">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>

        <!-- Companies Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="companiesTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nama Perusahaan</th>
                                <th>Tipe</th>
                                <th>Level</th>
                                <th>Cabang</th>
                                <th>Member</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="companiesTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Modal -->
    <div class="modal fade" id="companyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Perusahaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="companyForm">
                        <input type="hidden" id="companyId" name="id_company">
                        <input type="hidden" id="addressId" name="address_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="companyName" name="company_name" required>
                                    <label for="companyName">Nama Perusahaan</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="companyCode" name="company_code" required>
                                    <label for="companyCode">Kode Perusahaan</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="companyType" name="company_type" required>
                                        <option value="">Pilih Tipe</option>
                                        <option value="individual">Individu</option>
                                        <option value="pusat">Pusat</option>
                                    </select>
                                    <label for="companyType">Tipe Perusahaan</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="scalabilityLevel" name="scalability_level" required>
                                        <option value="">Pilih Level</option>
                                        <option value="1">Level 1 - Individu</option>
                                        <option value="2">Level 2 - Usaha Kecil</option>
                                        <option value="3">Level 3 - Usaha Menengah</option>
                                        <option value="4">Level 4 - Usaha Besar</option>
                                        <option value="5">Level 5 - Enterprise</option>
                                    </select>
                                    <label for="scalabilityLevel">Level Skalabilitas</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="ownerName" name="owner_name" required>
                                    <label for="ownerName">Nama Pemilik</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                    <label for="phone">Telepon</label>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Alamat</h6>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="addressDetail" name="address_detail">
                                    <label for="addressDetail">Alamat Lengkap</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="provinceId" name="province_id">
                                        <option value="">Pilih Provinsi</option>
                                    </select>
                                    <label for="provinceId">Provinsi</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="regencyId" name="regency_id" disabled>
                                        <option value="">Pilih Kabupaten/Kota</option>
                                    </select>
                                    <label for="regencyId">Kabupaten/Kota</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="districtId" name="district_id" disabled>
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                    <label for="districtId">Kecamatan</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="villageId" name="village_id" disabled>
                                        <option value="">Pilih Kelurahan/Desa</option>
                                    </select>
                                    <label for="villageId">Kelurahan/Desa</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="postalCode" name="postal_code" readonly>
                                    <label for="postalCode">Kode Pos</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                                    <label class="form-check-label" for="isActive">
                                        Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveCompany">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Perusahaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detailContent">
                        <!-- Detail will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus perusahaan ini?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(document).ready(function() {
        // Load initial data
        loadCompanies();
        loadProvinces();
        
        // Event handlers
        $("#searchInput").on("input", function() {
            loadCompanies();
        });
        
        $("#typeFilter, #statusFilter").on("change", function() {
            loadCompanies();
        });
        
        $("#resetFilters").on("click", function() {
            $("#searchInput").val("");
            $("#typeFilter").val("");
            $("#statusFilter").val("");
            loadCompanies();
        });
        
        // Address cascade
        $("#provinceId").on("change", function() {
            var provinceId = $(this).val();
            if (provinceId) {
                loadRegencies(provinceId);
                $("#regencyId").prop("disabled", false);
                $("#districtId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kecamatan</option>");
                $("#villageId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kelurahan/Desa</option>");
                $("#postalCode").val("");
            } else {
                $("#regencyId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kabupaten/Kota</option>");
                $("#districtId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kecamatan</option>");
                $("#villageId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kelurahan/Desa</option>");
                $("#postalCode").val("");
            }
        });
        
        $("#regencyId").on("change", function() {
            var regencyId = $(this).val();
            if (regencyId) {
                loadDistricts(regencyId);
                $("#districtId").prop("disabled", false);
                $("#villageId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kelurahan/Desa</option>");
                $("#postalCode").val("");
            } else {
                $("#districtId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kecamatan</option>");
                $("#villageId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kelurahan/Desa</option>");
                $("#postalCode").val("");
            }
        });
        
        $("#districtId").on("change", function() {
            var districtId = $(this).val();
            if (districtId) {
                loadVillages(districtId);
                $("#villageId").prop("disabled", false);
                $("#postalCode").val("");
            } else {
                $("#villageId").prop("disabled", true).empty().append("<option value=\"\">Pilih Kelurahan/Desa</option>");
                $("#postalCode").val("");
            }
        });
        
        $("#villageId").on("change", function() {
            var selectedOption = $(this).find("option:selected");
            var postalCode = selectedOption.data("postal-code");
            var villageId = $(this).val();
            
            $("#postalCode").val("");
            
            if (villageId && postalCode) {
                $("#postalCode").val(postalCode);
            } else if (villageId) {
                $.ajax({
                    url: "index.php?page=address&action=get-postal-code&village_id=" + villageId,
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        if (response.status == "success" && response.data.postal_code) {
                            $("#postalCode").val(response.data.postal_code);
                        }
                    }
                });
            }
        });
        
        // Form submission
        $("#companyForm").on("submit", function(e) {
            e.preventDefault();
            submitCompanyForm();
        });
        
        // Save button
        $("#saveCompany").on("click", function() {
            submitCompanyForm();
        });
        
        // Delete confirmation
        $(document).on("click", "#confirmDeleteBtn", function() {
            var companyId = $(this).data("company-id");
            $.ajax({
                url: "index.php?page=companies&action=delete&id=" + companyId,
                type: "POST",
                dataType: "json",
                success: function(response) {
                    if (response.status == "success") {
                        showNotification(response.message, "success");
                        $("#deleteModal").modal("hide");
                        loadCompanies();
                    } else {
                        showNotification("Gagal menghapus perusahaan", "error");
                    }
                },
                error: function() {
                    showNotification("Terjadi kesalahan saat menghapus perusahaan", "error");
                }
            });
        });
    });
    
    function loadCompanies() {
        var search = $("#searchInput").val();
        var type = $("#typeFilter").val();
        var status = $("#statusFilter").val();
        
        $.ajax({
            url: "index.php?page=companies&action=get",
            type: "GET",
            data: { search: search, type: type, status: status },
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    renderCompaniesTable(response.data.companies);
                    updateStatistics(response.data.statistics);
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function() {
                showNotification("Terjadi kesalahan saat memuat data perusahaan", "error");
            }
        });
    }
    
    function renderCompaniesTable(companies) {
        var tbody = $("#companiesTableBody");
        tbody.empty();
        
        if (companies.length === 0) {
            tbody.append("<tr><td colspan=\"8\" class=\"text-center\">Tidak ada data perusahaan</td></tr>");
            return;
        }
        
        companies.forEach(function(company) {
            var statusBadge = company.is_active ? 
                "<span class=\"badge bg-success status-badge\">Aktif</span>" : 
                "<span class=\"badge bg-secondary status-badge\">Non-aktif</span>";
            
            var typeBadge = company.company_type === "individual" ? 
                "<span class=\"badge bg-info company-type\">Individu</span>" : 
                "<span class=\"badge bg-primary company-type\">Pusat</span>";
            
            var row = "<tr>" +
                "<td>" + company.id_company + "</td>" +
                "<td><strong>" + company.company_name + "</strong></td>" +
                "<td>" + typeBadge + "</td>" +
                "<td>Level " + company.scalability_level + "</td>" +
                "<td>" + company.branch_count + "</td>" +
                "<td>" + company.member_count + "</td>" +
                "<td>" + statusBadge + "</td>" +
                "<td class=\"text-center\">" +
                    "<div class=\"action-buttons\">" +
                        "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"viewCompany(" + company.id_company + ")\" title=\"Lihat Detail\">" +
                            "<i class=\"fas fa-eye\"></i>" +
                        "</button>" +
                        "<button type=\"button\" class=\"btn btn-sm btn-warning\" onclick=\"editCompany(" + company.id_company + ")\" title=\"Edit\">" +
                            "<i class=\"fas fa-edit\"></i>" +
                        "</button>" +
                        "<button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"deleteCompany(" + company.id_company + ")\" title=\"Hapus\">" +
                            "<i class=\"fas fa-trash\"></i>" +
                        "</button>" +
                    "</div>" +
                "</td>" +
                "</tr>";
            
            tbody.append(row);
        });
    }
    
    function updateStatistics(stats) {
        $("#totalCompanies").text(stats.total_companies);
        $("#activeCompanies").text(stats.active_companies);
        $("#totalBranches").text(stats.total_branches);
        $("#totalMembers").text(stats.total_members);
    }
    
    function loadProvinces() {
        $.ajax({
            url: "index.php?page=address&action=get-provinces",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var select = $("#provinceId");
                    select.empty().append("<option value=\"\">Pilih Provinsi</option>");
                    
                    response.data.forEach(function(province) {
                        select.append("<option value=\"" + province.id + "\">" + province.name + "</option>");
                    });
                }
            }
        });
    }
    
    function loadRegencies(provinceId) {
        $.ajax({
            url: "index.php?page=address&action=get-regencies&province_id=" + provinceId,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var select = $("#regencyId");
                    select.empty().append("<option value=\"\">Pilih Kabupaten/Kota</option>");
                    
                    response.data.forEach(function(regency) {
                        select.append("<option value=\"" + regency.id + "\">" + regency.name + "</option>");
                    });
                }
            }
        });
    }
    
    function loadDistricts(regencyId) {
        $.ajax({
            url: "index.php?page=address&action=get-districts&regency_id=" + regencyId,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var select = $("#districtId");
                    select.empty().append("<option value=\"\">Pilih Kecamatan</option>");
                    
                    response.data.forEach(function(district) {
                        select.append("<option value=\"" + district.id + "\">" + district.name + "</option>");
                    });
                }
            }
        });
    }
    
    function loadVillages(districtId) {
        $.ajax({
            url: "index.php?page=address&action=get-villages&district_id=" + districtId,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var select = $("#villageId");
                    select.empty().append("<option value=\"\">Pilih Kelurahan/Desa</option>");
                    
                    response.data.forEach(function(village) {
                        select.append("<option value=\"" + village.id + "\" data-postal-code=\"" + village.postal_code + "\">" + village.name + "</option>");
                    });
                }
            }
        });
    }
    
    function submitCompanyForm() {
        var formData = $("#companyForm").serialize();
        
        $.ajax({
            url: "index.php?page=companies&action=save",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    showNotification(response.message, "success");
                    $("#companyModal").modal("hide");
                    resetForm();
                    loadCompanies();
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function() {
                showNotification("Terjadi kesalahan saat menyimpan perusahaan", "error");
            }
        });
    }
    
    function viewCompany(id) {
        $.ajax({
            url: "index.php?page=companies&action=get&id=" + id,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    renderCompanyDetail(response.data.company);
                    $("#detailModal").modal("show");
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function() {
                showNotification("Terjadi kesalahan saat memuat detail perusahaan", "error");
            }
        });
    }
    
    function renderCompanyDetail(company) {
        var addressParts = [];
        if (company.address_detail) addressParts.push(company.address_detail);
        if (company.village_name) addressParts.push(company.village_name);
        if (company.district_name) addressParts.push(company.district_name);
        if (company.regency_name) addressParts.push(company.regency_name);
        if (company.province_name) addressParts.push(company.province_name);
        if (company.postal_code) addressParts.push(company.postal_code);
        
        var address = addressParts.join(", ") || "-";
        
        var content = "<div class=\"row\">" +
            "<div class=\"col-md-6\">" +
                "<p><strong>Nama Perusahaan:</strong><br>" + company.company_name + "</p>" +
                "<p><strong>Kode Perusahaan:</strong><br>" + company.company_code + "</p>" +
                "<p><strong>Tipe:</strong><br>" + (company.company_type === "individual" ? "Individu" : "Pusat") + "</p>" +
                "<p><strong>Level:</strong><br>Level " + company.scalability_level + "</p>" +
            "</div>" +
            "<div class=\"col-md-6\">" +
                "<p><strong>Pemilik:</strong><br>" + company.owner_name + "</p>" +
                "<p><strong>Email:</strong><br>" + company.email + "</p>" +
                "<p><strong>Telepon:</strong><br>" + company.phone + "</p>" +
                "<p><strong>Status:</strong><br>" + (company.is_active ? "Aktif" : "Non-aktif") + "</p>" +
            "</div>" +
            "</div>" +
            "<div class=\"row\">" +
                "<div class=\"col-md-12\">" +
                    "<p><strong>Alamat:</strong><br><span class=\"address-display\">" + address + "</span></p>" +
                "</div>" +
            "</div>";
        
        $("#detailContent").html(content);
    }
    
    function editCompany(id) {
        $.ajax({
            url: "index.php?page=companies&action=get&id=" + id,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var company = response.data.company;
                    
                    $("#modalTitle").text("Edit Perusahaan");
                    $("#companyId").val(company.id_company);
                    $("#addressId").val(company.address_id || "");
                    $("#companyName").val(company.company_name);
                    $("#companyCode").val(company.company_code);
                    $("#companyType").val(company.company_type);
                    $("#scalabilityLevel").val(company.scalability_level);
                    $("#ownerName").val(company.owner_name);
                    $("#email").val(company.email);
                    $("#phone").val(company.phone);
                    $("#addressDetail").val(company.address_detail || "");
                    $("#provinceId").val(company.province_id || "");
                    $("#regencyId").val(company.regency_id || "");
                    $("#districtId").val(company.district_id || "");
                    $("#villageId").val(company.village_id || "");
                    $("#postalCode").val(company.postal_code || "");
                    $("#isActive").prop("checked", company.is_active == 1);
                    
                    // Enable cascading selects if values exist
                    if (company.province_id) {
                        $("#regencyId").prop("disabled", false);
                        loadRegencies(company.province_id);
                    }
                    if (company.regency_id) {
                        $("#districtId").prop("disabled", false);
                        loadDistricts(company.regency_id);
                    }
                    if (company.district_id) {
                        $("#villageId").prop("disabled", false);
                        loadVillages(company.district_id);
                    }
                    
                    $("#companyModal").modal("show");
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function() {
                showNotification("Terjadi kesalahan saat memuat data perusahaan", "error");
            }
        });
    }
    
    function deleteCompany(id) {
        $("#confirmDeleteBtn").data("company-id", id);
        $("#deleteModal").modal("show");
    }
    
    function resetForm() {
        $("#companyForm")[0].reset();
        $("#companyId").val("");
        $("#addressId").val("");
        $("#modalTitle").text("Tambah Perusahaan");
        $("#regencyId, #districtId, #villageId").prop("disabled", true).empty().append("<option value=\"\">Pilih...</option>");
        $("#postalCode").val("");
    }
    
    function showNotification(message, type) {
        var alertClass = type === "success" ? "alert-success" : "alert-danger";
        var notification = "<div class=\"alert " + alertClass + " alert-dismissible fade show position-fixed\" style=\"top: 20px; right: 20px; z-index: 9999;\">" +
            message +
            "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>" +
            "</div>";
        
        $("body").append(notification);
        
        setTimeout(function() {
            $(".alert").fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    </script>
</body>
</html>