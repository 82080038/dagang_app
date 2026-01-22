<?php
// Restore complete companies index.php with clean HTML + JavaScript
$complete_file = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Perusahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-building me-2"></i>Manajemen Perusahaan</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#companyModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Perusahaan
            </button>
        </div>
        
        <!-- Search and Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchCompaniesInput" placeholder="Cari perusahaan...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterCompanyType">
                    <option value="">Semua Tipe</option>
                    <option value="individual">Individu</option>
                    <option value="warung">Warung</option>
                    <option value="kios">Kios</option>
                    <option value="toko_kelontong">Toko Kelontong</option>
                    <option value="minimarket">Minimarket</option>
                    <option value="pengusaha_menengah">Pengusaha Menengah</option>
                    <option value="distributor">Distributor</option>
                    <option value="koperasi">Koperasi</option>
                    <option value="perusahaan_besar">Perusahaan Besar</option>
                    <option value="franchise">Franchise</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-secondary" onclick="refreshCompanies()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Companies Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th><i class="bi bi-building me-1"></i>Perusahaan</th>
                        <th><i class="bi bi-hash me-1"></i>Kode</th>
                        <th><i class="bi bi-tag me-1"></i>Tipe</th>
                        <th><i class="bi bi-person me-1"></i>Pemilik</th>
                        <th><i class="bi bi-telephone me-1"></i>Telepon</th>
                        <th><i class="bi bi-graph-up me-1"></i>Level</th>
                        <th><i class="bi bi-activity me-1"></i>Status</th>
                        <th><i class="bi bi-gear me-1"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody id="companiesTableBody">
                    <!-- Companies will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div id="companiesPagination"></div>
    </div>

    <!-- Company Modal -->
    <div class="modal fade" id="companyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="companyModalTitle">Tambah Perusahaan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="companyForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Nama Perusahaan *</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_code" class="form-label">Kode Perusahaan</label>
                                    <input type="text" class="form-control" id="company_code" name="company_code">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_type" class="form-label">Tipe Perusahaan *</label>
                                    <select class="form-select" id="company_type" name="company_type" required>
                                        <option value="">Pilih Tipe</option>
                                        <option value="individual">Individu/Personal</option>
                                        <option value="warung">Warung</option>
                                        <option value="kios">Kios</option>
                                        <option value="toko_kelontong">Toko Kelontong</option>
                                        <option value="minimarket">Minimarket</option>
                                        <option value="pengusaha_menengah">Pengusaha Menengah</option>
                                        <option value="distributor">Distributor</option>
                                        <option value="koperasi">Koperasi</option>
                                        <option value="perusahaan_besar">Perusahaan Besar</option>
                                        <option value="franchise">Franchise</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scalability_level" class="form-label">Level Skalabilitas *</label>
                                    <select class="form-select" id="scalability_level" name="scalability_level" required>
                                        <option value="">Pilih Level</option>
                                        <option value="1">Level 1 - Individu/Personal</option>
                                        <option value="2">Level 2 - Warung/Kios</option>
                                        <option value="3">Level 3 - Toko Kelontong</option>
                                        <option value="4">Level 4 - Minimarket/Pengusaha Menengah</option>
                                        <option value="5">Level 5 - Distributor/Perusahaan Menengah</option>
                                        <option value="6">Level 6 - Perusahaan Besar/Franchise</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="owner_name" class="form-label">Nama Pemilik *</label>
                                    <input type="text" class="form-control" id="owner_name" name="owner_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="business_category" class="form-label">Kategori Bisnis</label>
                                    <select class="form-select" id="business_category" name="business_category">
                                        <option value="">Pilih Kategori</option>
                                        <option value="retail">Retail</option>
                                        <option value="wholesale">Wholesale</option>
                                        <option value="manufacturing">Manufacturing</option>
                                        <option value="agriculture">Agriculture</option>
                                        <option value="services">Services</option>
                                        <option value="cooperative">Cooperative</option>
                                        <option value="online">Online</option>
                                        <option value="franchise">Franchise</option>
                                        <option value="distributor">Distributor</option>
                                        <option value="personal">Personal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telepon</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" id="company_id" name="company_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="saveCompanyBtn">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus perusahaan "<span id="deleteCompanyName"></span>"?</p>
                    <p class="text-danger">Perhatian: Tindakan ini akan menonaktifkan perusahaan dan semua cabang terkait.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Details Modal -->
    <div class="modal fade" id="companyDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-building me-2"></i>Detail Perusahaan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Loading State -->
                    <div id="companyDetailLoading" class="text-center py-4">
                        <i class="bi bi-spinner fa-spin me-2"></i> Memuat data...
                    </div>
                    
                    <!-- Error State -->
                    <div id="companyDetailError" class="alert alert-danger" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="companyDetailErrorText">Gagal memuat data perusahaan</span>
                    </div>
                    
                    <!-- Company Details Content -->
                    <div id="companyDetailContent" style="display: none;">
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <h4 class="mb-3" id="detailCompanyName"></h4>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p class="mb-2">
                                            <strong>Kode:</strong> 
                                            <code id="detailCompanyCode"></code>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Tipe:</strong> 
                                            <span id="detailCompanyType" class="badge bg-primary"></span>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Level:</strong> 
                                            <span id="detailScalabilityLevel" class="badge bg-info"></span>
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-2">
                                            <strong>Pemilik:</strong> 
                                            <span id="detailOwnerName"></span>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Kategori:</strong> 
                                            <span id="detailBusinessCategory"></span>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Status:</strong> 
                                            <span id="detailStatus" class="badge"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Statistik</h6>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="fw-bold text-primary" id="detailBranchCount">0</div>
                                                <small class="text-muted">Cabang</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-success" id="detailMemberCount">0</div>
                                                <small class="text-muted">Member</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-warning" id="detailProductCount">0</div>
                                                <small class="text-muted">Produk</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6><i class="bi bi-telephone me-2"></i>Kontak</h6>
                                <p class="mb-2">
                                    <strong>Email:</strong> 
                                    <a href="#" id="detailEmail"></a>
                                </p>
                                <p class="mb-2">
                                    <strong>Telepon:</strong> 
                                    <span id="detailPhone"></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-file-text me-2"></i>Dokumen</h6>
                                <p class="mb-2">
                                    <strong>NPWP:</strong> 
                                    <span id="detailTaxId"></span>
                                </p>
                                <p class="mb-2">
                                    <strong>SIUP:</strong> 
                                    <span id="detailBusinessLicense"></span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6><i class="bi bi-geo-alt me-2"></i>Alamat</h6>
                                <div id="detailAddress" class="bg-light p-3 rounded"></div>
                            </div>
                        </div>
                        
                        <!-- Branches List -->
                        <div class="row">
                            <div class="col-12">
                                <h6><i class="bi bi-diagram-2 me-2"></i>Daftar Cabang</h6>
                                <div id="detailBranchesList">
                                    <div class="text-muted">Belum ada cabang</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="editFromDetailBtn">
                        <i class="bi bi-pencil me-1"></i> Edit Perusahaan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Wait for jQuery to be loaded
    (function() {
        function initCompaniesPage() {
            loadCompanies();
            
            // Search functionality
            $("#searchCompaniesInput").on("input", function() {
                clearTimeout(window.searchTimeout);
                window.searchTimeout = setTimeout(function() {
                    loadCompanies();
                }, 300);
            });
            
            // Filter functionality
            $("#filterCompanyType").on("change", function() {
                loadCompanies();
            });
            
            // Company form submission
            $("#companyForm").on("submit", function(e) {
                e.preventDefault();
                submitCompanyForm();
            });
            
            // Delete confirmation
            $("#confirmDeleteBtn").on("click", function() {
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
                        showNotification("Terjadi kesalahan", "error");
                    }
                });
            });
            
            // Reset form when modal is hidden
            $("#companyModal").on("hidden.bs.modal", function() {
                $("#companyForm")[0].reset();
                $("#company_id").val("");
                $("#companyModalTitle").text("Tambah Perusahaan Baru");
                $("#companyForm .is-invalid").removeClass("is-invalid");
                $("#companyForm .invalid-feedback").remove();
            });
        }
        
        function loadCompanies(page) {
            page = page || 1;
            var search = $("#searchCompaniesInput").val();
            var filterType = $("#filterCompanyType").val();
            
            var params = new URLSearchParams({
                page_num: page,
                q: search,
                type: filterType
            });
            
            $.ajax({
                url: "index.php?page=companies&" + params.toString(),
                type: "GET",
                dataType: "json",
                beforeSend: function() {
                    $("#companiesTableBody").html("<tr><td colspan=\"8\" class=\"text-center\"><i class=\"bi bi-spinner fa-spin\"></i> Loading...</td></tr>");
                },
                success: function(response) {
                    if (response.status == "success") {
                        renderCompaniesTable(response.data.companies);
                        renderPagination(response.data.pagination);
                    } else {
                        $("#companiesTableBody").html("<tr><td colspan=\"8\" class=\"text-center text-danger\">Gagal memuat data</td></tr>");
                    }
                },
                error: function() {
                    $("#companiesTableBody").html("<tr><td colspan=\"8\" class=\"text-center text-danger\">Terjadi kesalahan</td></tr>");
                }
            });
        }
        
        function renderCompaniesTable(companies) {
            var html = "";
            
            if (companies.length === 0) {
                html = "<tr><td colspan=\"8\" class=\"text-center text-muted\">Tidak ada data perusahaan</td></tr>";
            } else {
                companies.forEach(function(company) {
                    var statusBadge = company.is_active ? 
                        "<span class=\"badge bg-success\">Aktif</span>" : 
                        "<span class=\"badge bg-danger\">Non-aktif</span>";
                    
                    var levelBadge = "";
                    var level = company.scalability_level;
                    if (level !== null && level !== undefined && level !== "") {
                        var levelNum = parseInt(level);
                        switch(levelNum) {
                            case 1: levelBadge = "<span class=\"badge bg-primary\">Level 1</span>"; break;
                            case 2: levelBadge = "<span class=\"badge bg-info\">Level 2</span>"; break;
                            case 3: levelBadge = "<span class=\"badge bg-warning\">Level 3</span>"; break;
                            case 4: levelBadge = "<span class=\"badge bg-secondary\">Level 4</span>"; break;
                            case 5: levelBadge = "<span class=\"badge bg-dark\">Level 5</span>"; break;
                            case 6: levelBadge = "<span class=\"badge bg-danger\">Level 6</span>"; break;
                            default: levelBadge = "<span class=\"badge bg-light text-dark\">Level " + level + "</span>";
                        }
                    } else {
                        levelBadge = "<span class=\"badge bg-light text-dark\">-</span>";
                    }
                    
                    html += "<tr>" +
                        "<td>" +
                            "<div class=\"d-flex align-items-center\">" +
                                "<div class=\"me-2\">" +
                                    "<i class=\"bi bi-building text-primary\"></i>" +
                                "</div>" +
                                "<div>" +
                                    "<div class=\"fw-bold\">" + company.company_name + "</div>" +
                                    "<small class=\"text-muted\">" + (company.business_category ? company.business_category : "-") + "</small>" +
                                "</div>" +
                            "</div>" +
                        "</td>" +
                        "<td><code>" + company.company_code + "</code></td>" +
                        "<td>" + company.company_type + "</td>" +
                        "<td>" + company.owner_name + "</td>" +
                        "<td>" + (company.phone ? company.phone : "-") + "</td>" +
                        "<td>" + levelBadge + "</td>" +
                        "<td>" + statusBadge + "</td>" +
                        "<td>" +
                            "<div class=\"btn-group btn-group-sm\" role=\"group\">" +
                                "<button class=\"btn btn-outline-primary btn-sm\" onclick=\"editCompany(" + company.id_company + ")\" title=\"Edit\">" +
                                    "<i class=\"bi bi-pencil\"></i>" +
                                "</button>" +
                                "<button class=\"btn btn-outline-info btn-sm\" onclick=\"viewCompanyDetails(" + company.id_company + ")\" title=\"Detail\">" +
                                    "<i class=\"bi bi-eye\"></i>" +
                                "</button>" +
                                "<button class=\"btn btn-outline-warning btn-sm\" onclick=\"toggleCompanyStatus(" + company.id_company + ")\" title=\"Toggle Status\">" +
                                    "<i class=\"bi bi-toggle-on\"></i>" +
                                "</button>" +
                                "<button class=\"btn btn-outline-danger btn-sm\" onclick=\"deleteCompany(" + company.id_company + ", \'" + company.company_name + "\')\" title=\"Hapus\">" +
                                    "<i class=\"bi bi-trash\"></i>" +
                                "</button>" +
                            "</div>" +
                        "</td>" +
                    "</tr>";
                });
            }
            
            $("#companiesTableBody").html(html);
        }
        
        function renderPagination(pagination) {
            if (pagination.total_pages <= 1) return;
            
            var html = "<nav><ul class=\"pagination justify-content-center\">";
            
            if (pagination.current_page > 1) {
                html += "<li class=\"page-item\"><a class=\"page-link\" href=\"#\" onclick=\"loadCompanies(" + (pagination.current_page - 1) + ")\">Previous</a></li>";
            } else {
                html += "<li class=\"page-item disabled\"><span class=\"page-link\">Previous</span></li>";
            }
            
            var start = Math.max(1, pagination.current_page - 2);
            var end = Math.min(pagination.total_pages, pagination.current_page + 2);
            
            for (var i = start; i <= end; i++) {
                var active = i === pagination.current_page ? "active" : "";
                html += "<li class=\"page-item " + active + "\"><a class=\"page-link\" href=\"#\" onclick=\"loadCompanies(" + i + ")\">" + i + "</a></li>";
            }
            
            if (pagination.current_page < pagination.total_pages) {
                html += "<li class=\"page-item\"><a class=\"page-link\" href=\"#\" onclick=\"loadCompanies(" + (pagination.current_page + 1) + ")\">Next</a></li>";
            } else {
                html += "<li class=\"page-item disabled\"><span class=\"page-link\">Next</span></li>";
            }
            
            html += "</ul></nav>";
            $("#companiesPagination").html(html);
        }
        
        function submitCompanyForm() {
            var formData = $("#companyForm").serialize();
            
            $.ajax({
                url: "index.php?page=companies&action=create",
                type: "POST",
                data: formData,
                dataType: "json",
                beforeSend: function() {
                    $("#saveCompanyBtn").prop("disabled", true).html("<i class=\"bi bi-spinner fa-spin me-1\"></i> Menyimpan...");
                },
                success: function(response) {
                    if (response.status == "success") {
                        showNotification(response.message, "success");
                        $("#companyModal").modal("hide");
                        $("#companyForm")[0].reset();
                        loadCompanies();
                    } else {
                        var message = response.message ? response.message : "Terjadi kesalahan";
                        showNotification(message, "error");
                    }
                },
                error: function() {
                    showNotification("Terjadi kesalahan", "error");
                },
                complete: function() {
                    $("#saveCompanyBtn").prop("disabled", false).html("<i class=\"bi bi-save me-1\"></i> Simpan");
                }
            });
        }
        
        function editCompany(companyId) {
            $.ajax({
                url: "index.php?page=companies&action=get&id=" + companyId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status == "success") {
                        var company = response.data.company;
                        
                        $("#companyModalTitle").text("Edit Perusahaan");
                        $("#company_id").val(company.id_company);
                        $("#company_name").val(company.company_name);
                        $("#company_code").val(company.company_code);
                        $("#company_type").val(company.company_type);
                        $("#scalability_level").val(company.scalability_level);
                        $("#owner_name").val(company.owner_name);
                        $("#business_category").val(company.business_category);
                        $("#email").val(company.email);
                        $("#phone").val(company.phone);
                        
                        $("#companyModal").modal("show");
                    } else {
                        showNotification("Terjadi kesalahan", "error");
                    }
                },
                error: function() {
                    showNotification("Terjadi kesalahan", "error");
                }
            });
        }
        
        function deleteCompany(companyId, companyName) {
            $("#deleteCompanyName").text(companyName);
            $("#confirmDeleteBtn").data("company-id", companyId);
            $("#deleteModal").modal("show");
        }
        
        function refreshCompanies() {
            loadCompanies();
            showNotification("Data berhasil dimuat ulang", "info");
        }
        
        function toggleCompanyStatus(companyId) {
            $.ajax({
                url: "index.php?page=companies&action=toggle-status&id=" + companyId,
                type: "POST",
                dataType: "json",
                success: function(response) {
                    if (response.status == "success") {
                        showNotification(response.message, "success");
                        loadCompanies();
                    } else {
                        var message = response.message ? response.message : "Gagal mengubah status";
                        showNotification("Terjadi kesalahan", "error");
                    }
                },
                error: function() {
                    showNotification("Terjadi kesalahan", "error");
                }
            });
        }
        
        function viewCompanyDetails(companyId) {
            $("#companyDetailLoading").show();
            $("#companyDetailError").hide();
            $("#companyDetailContent").hide();
            $("#companyDetailModal").modal("show");
            
            $.ajax({
                url: "index.php?page=companies&action=details&id=" + companyId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status == "success") {
                        populateCompanyDetails(response.data);
                        $("#companyDetailLoading").hide();
                        $("#companyDetailContent").show();
                    } else {
                        $("#companyDetailLoading").hide();
                        $("#companyDetailError").show();
                        $("#companyDetailErrorText").text(response.message || "Gagal memuat data perusahaan");
                    }
                },
                error: function(xhr, status, error) {
                    $("#companyDetailLoading").hide();
                    $("#companyDetailError").show();
                    $("#companyDetailErrorText").text("Terjadi kesalahan: " + error);
                }
            });
        }
        
        function populateCompanyDetails(data) {
            var company = data.company;
            var statistics = data.statistics || {};
            var branches = data.branches || {};
            
            $("#detailCompanyName").text(company.company_name || "-");
            $("#detailCompanyCode").text(company.company_code || "-");
            $("#detailCompanyType").text(company.company_type || "-").removeClass().addClass("badge bg-primary");
            $("#detailOwnerName").text(company.owner_name || "-");
            $("#detailBusinessCategory").text(company.business_category || "-");
            
            var levelBadge = "";
            var level = company.scalability_level;
            if (level !== null && level !== undefined && level !== "") {
                var levelNum = parseInt(level);
                switch(levelNum) {
                    case 1: levelBadge = "bg-primary"; break;
                    case 2: levelBadge = "bg-info"; break;
                    case 3: levelBadge = "bg-warning"; break;
                    case 4: levelBadge = "bg-secondary"; break;
                    case 5: levelBadge = "bg-dark"; break;
                    case 6: levelBadge = "bg-danger"; break;
                    default: levelBadge = "bg-light text-dark";
                }
            } else {
                levelBadge = "bg-light text-dark";
            }
            $("#detailScalabilityLevel").text("Level " + (level || "-")).removeClass().addClass("badge " + levelBadge);
            
            var statusBadge = company.is_active ? "bg-success" : "bg-danger";
            var statusText = company.is_active ? "Aktif" : "Non-aktif";
            $("#detailStatus").text(statusText).removeClass().addClass("badge " + statusBadge);
            
            $("#detailBranchCount").text(statistics.total_branches || branches.branches_count || 0);
            $("#detailMemberCount").text(statistics.total_members || 0);
            $("#detailProductCount").text(statistics.total_products || 0);
            
            var email = company.email || "-";
            if (email !== "-") {
                $("#detailEmail").attr("href", "mailto:" + email).text(email);
            } else {
                $("#detailEmail").attr("href", "#").text(email);
            }
            $("#detailPhone").text(company.phone || "-");
            
            $("#detailTaxId").text(company.tax_id || "-");
            $("#detailBusinessLicense").text(company.business_license || "-");
            
            var addressHtml = "";
            if (company.full_address) {
                addressHtml = company.full_address;
            } else if (company.address) {
                addressHtml = company.address;
            } else {
                addressHtml = "Alamat tidak tersedia";
            }
            $("#detailAddress").html(addressHtml);
            
            if (statistics.total_branches > 0 || branches.branches_count > 0) {
                $("#detailBranchesList").html(
                    "<div class=\"alert alert-info\">" +
                    "<i class=\"bi bi-info-circle me-2\"></i>" +
                    "Perusahaan ini memiliki " + (statistics.total_branches || branches.branches_count || 0) + " cabang" +
                    "</div>"
                );
            } else {
                $("#detailBranchesList").html("<div class=\"text-muted\">Belum ada cabang</div>");
            }
            
            $("#editFromDetailBtn").off("click").on("click", function() {
                $("#companyDetailModal").modal("hide");
                editCompany(company.id_company);
            });
        }
        
        function showNotification(message, type) {
            // Simple alert for now - can be replaced with toast
            alert(message);
        }
        
        // Make functions globally accessible
        window.loadCompanies = loadCompanies;
        window.editCompany = editCompany;
        window.deleteCompany = deleteCompany;
        window.refreshCompanies = refreshCompanies;
        window.toggleCompanyStatus = toggleCompanyStatus;
        window.viewCompanyDetails = viewCompanyDetails;
        
        // Initialize when jQuery is ready
        if (typeof $ !== "undefined") {
            $(document).ready(function() {
                initCompaniesPage();
            });
        } else {
            var checkJQuery = setInterval(function() {
                if (typeof $ !== "undefined") {
                    clearInterval(checkJQuery);
                    $(document).ready(function() {
                        initCompaniesPage();
                    });
                }
            }, 100);
        }
    })();
    </script>
</body>
</html>';

file_put_contents('app/views/companies/index.php', $complete_file);
echo "Complete companies page restored successfully!\n";
?>
