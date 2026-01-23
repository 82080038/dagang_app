<?php
/**
 * Companies Index Page
 * 
 * This view displays the companies management interface with:
 * - Search and filter functionality
 * - Companies table with pagination
 * - CRUD operations (Create, Read, Update, Delete)
 * - Company details modal
 * - Status toggle functionality
 */

// Check if user is logged in
if (!$this->isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}
?>

<div class="container-fluid">
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
                    
                    <!-- Address Information -->
                    <hr>
                    <h6 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Informasi Alamat</h6>
                    
                    <!-- Administrative Address (Dropdown) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="province_id" class="form-label">Provinsi *</label>
                                <select class="form-select" id="province_id" name="province_id" required>
                                    <option value="">Pilih Provinsi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="regency_id" class="form-label">Kabupaten/Kota *</label>
                                <select class="form-select" id="regency_id" name="regency_id" required disabled>
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="district_id" class="form-label">Kecamatan *</label>
                                <select class="form-select" id="district_id" name="district_id" required disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="village_id" class="form-label">Kelurahan/Desa *</label>
                                <select class="form-select" id="village_id" name="village_id" required disabled>
                                    <option value="">Pilih Kelurahan/Desa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Street Address (Manual) -->
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="address_detail" class="form-label">Alamat Lengkap *</label>
                                <textarea class="form-control" id="address_detail" name="address_detail" rows="2" required placeholder="Masukkan alamat lengkap (jalan, nomor rumah, RT/RW, dll)"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Postal Code -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Kode Pos</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Contoh: 12345">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tax_id" class="form-label">NPWP</label>
                                <input type="text" class="form-control" id="tax_id" name="tax_id" placeholder="Contoh: 12.345.678.9-123.000">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="business_license" class="form-label">SIUP/SIUP TDP</label>
                                <input type="text" class="form-control" id="business_license" name="business_license" placeholder="Nomor SIUP atau SIUP TDP">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address_id" class="form-label">ID Alamat (Opsional)</label>
                                <input type="text" class="form-control" id="address_id" name="address_id" placeholder="ID referensi alamat">
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
        
        // Address dropdown functionality
        loadProvinces();
        
        $("#province_id").on("change", function() {
            var provinceId = $(this).val();
            if (provinceId) {
                loadRegencies(provinceId);
                $("#regency_id").prop("disabled", false);
                $("#district_id").prop("disabled", true).empty().append('<option value="">Pilih Kecamatan</option>');
                $("#village_id").prop("disabled", true).empty().append('<option value="">Pilih Kelurahan/Desa</option>');
            } else {
                $("#regency_id").prop("disabled", true).empty().append('<option value="">Pilih Kabupaten/Kota</option>');
                $("#district_id").prop("disabled", true).empty().append('<option value="">Pilih Kecamatan</option>');
                $("#village_id").prop("disabled", true).empty().append('<option value="">Pilih Kelurahan/Desa</option>');
            }
        });
        
        $("#regency_id").on("change", function() {
            var regencyId = $(this).val();
            if (regencyId) {
                loadDistricts(regencyId);
                $("#district_id").prop("disabled", false);
                $("#village_id").prop("disabled", true).empty().append('<option value="">Pilih Kelurahan/Desa</option>');
            } else {
                $("#district_id").prop("disabled", true).empty().append('<option value="">Pilih Kecamatan</option>');
                $("#village_id").prop("disabled", true).empty().append('<option value="">Pilih Kelurahan/Desa</option>');
            }
        });
        
        $("#district_id").on("change", function() {
            var districtId = $(this).val();
            if (districtId) {
                loadVillages(districtId);
                $("#village_id").prop("disabled", false);
            } else {
                $("#village_id").prop("disabled", true).empty().append('<option value="">Pilih Kelurahan/Desa</option>');
                $("#postal_code").val(""); // Clear postal code
            }
        });
        
        $("#village_id").on("change", function() {
            var selectedOption = $(this).find("option:selected");
            var postalCode = selectedOption.data("postal-code");
            var villageId = $(this).val();
            
            // Always clear postal code first when village changes
            $("#postal_code").val("");
            
            if (villageId && postalCode) {
                // Set postal code if available in option data
                $("#postal_code").val(postalCode);
            } else if (villageId) {
                // Fetch from API if village is selected but no postal code in data
                $.ajax({
                    url: "index.php?page=address&action=get-postal-code&village_id=" + villageId,
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        if (response.status == "success" && response.data.postal_code) {
                            $("#postal_code").val(response.data.postal_code);
                        }
                        // If no postal code found, keep field empty (already cleared above)
                    },
                    error: function() {
                        // Keep field empty on error (already cleared above)
                        console.log("Failed to fetch postal code for village: " + villageId);
                    }
                });
            }
            // If villageId is empty/null, postal code remains empty (cleared above)
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
                        Toast.success(response.message);
                        $("#deleteModal").modal("hide");
                        loadCompanies();
                    } else {
                        Toast.error("Gagal menghapus perusahaan");
                    }
                },
                error: function() {
                    Toast.error("Terjadi kesalahan");
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
                            "<button class=\"btn btn-outline-danger btn-sm\" onclick=\"deleteCompany(" + company.id_company + ", '" + company.company_name + "')\" title=\"Hapus\">" +
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
        var companyId = $("#company_id").val();
        var url = companyId ? 
            "index.php?page=companies&action=update" : 
            "index.php?page=companies&action=create";
        
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "json",
            beforeSend: function() {
                $("#saveCompanyBtn").prop("disabled", true).html("<i class=\"bi bi-spinner fa-spin me-1\"></i> Menyimpan...");
            },
            success: function(response) {
                if (response.status == "success") {
                    Toast.success(response.message);
                    $("#companyModal").modal("hide");
                    $("#companyForm")[0].reset();
                    loadCompanies();
                } else {
                    var message = response.message ? response.message : "Terjadi kesalahan";
                    Toast.error(message);
                }
            },
            error: function() {
                Toast.error("Terjadi kesalahan");
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
                    
                    // Fill address fields
                    $("#address_detail").val(company.address_detail || "");
                    $("#postal_code").val(company.postal_code || "");
                    $("#tax_id").val(company.tax_id || "");
                    $("#business_license").val(company.business_license || "");
                    $("#address_id").val(company.address_id || "");
                    
                    // Load and set address dropdowns
                    if (company.province_id) {
                        loadProvinces(function() {
                            $("#province_id").val(company.province_id);
                            if (company.regency_id) {
                                loadRegencies(company.province_id, function() {
                                    $("#regency_id").val(company.regency_id);
                                    $("#regency_id").prop("disabled", false);
                                    if (company.district_id) {
                                        loadDistricts(company.regency_id, function() {
                                            $("#district_id").val(company.district_id);
                                            $("#district_id").prop("disabled", false);
                                            if (company.village_id) {
                                                loadVillages(company.district_id, function() {
                                                    $("#village_id").val(company.village_id);
                                                    $("#village_id").prop("disabled", false);
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                    
                    $("#companyModal").modal("show");
                } else {
                    Toast.error("Terjadi kesalahan");
                }
            },
            error: function() {
                Toast.error("Terjadi kesalahan");
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
        Toast.info("Data berhasil dimuat ulang");
    }
    
    function toggleCompanyStatus(companyId) {
        $.ajax({
            url: "index.php?page=companies&action=toggle-status&id=" + companyId,
            type: "POST",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    Toast.success(response.message);
                    loadCompanies();
                } else {
                    var message = response.message ? response.message : "Gagal mengubah status";
                    Toast.error(message);
                }
            },
            error: function() {
                Toast.error("Terjadi kesalahan");
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
            $("#detailScalabilityLevel").text("Level " + level).removeClass().addClass("badge " + levelBadge);
        } else {
            $("#detailScalabilityLevel").text("-").removeClass().addClass("badge bg-light text-dark");
        }
        
        $("#detailStatus").text(company.is_active ? "Aktif" : "Non-aktif")
            .removeClass().addClass("badge " + (company.is_active ? "bg-success" : "bg-danger"));
        
        $("#detailEmail").text(company.email || "-").attr("href", company.email ? "mailto:" + company.email : "#");
        $("#detailPhone").text(company.phone || "-");
        $("#detailTaxId").text(company.tax_id || "-");
        $("#detailBusinessLicense").text(company.business_license || "-");
        
        // Build address
        var addressParts = [];
        if (company.address_detail) addressParts.push(company.address_detail);
        if (company.village_name) addressParts.push(company.village_name);
        if (company.district_name) addressParts.push(company.district_name);
        if (company.regency_name) addressParts.push(company.regency_name);
        if (company.province_name) addressParts.push(company.province_name);
        if (company.postal_code) addressParts.push(company.postal_code);
        
        $("#detailAddress").text(addressParts.join(", ") || "-");
        
        // Statistics
        $("#detailBranchCount").text(statistics.total_branches || branches.branches_count || 0);
        $("#detailMemberCount").text(statistics.total_members || 0);
        $("#detailProductCount").text(statistics.total_products || 0);
        
        // Branches list
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
    
    // Address loading functions (using existing AddressController)
    function loadProvinces(callback) {
        $.ajax({
            url: "index.php?page=address&action=get-provinces",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var html = "<option value=\"\">Pilih Provinsi</option>";
                    response.data.forEach(function(province) {
                        html += "<option value=\"" + province.id + "\">" + province.name + "</option>";
                    });
                    $("#province_id").html(html);
                    if (callback) callback();
                } else {
                    Toast.error("Gagal memuat data provinsi");
                }
            },
            error: function() {
                Toast.error("Terjadi kesalahan saat memuat provinsi");
            }
        });
    }
    
    function loadRegencies(provinceId, callback) {
        if (!provinceId) return;
        
        $.ajax({
            url: "index.php?page=address&action=get-regencies&province_id=" + provinceId,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var html = "<option value=\"\">Pilih Kabupaten/Kota</option>";
                    response.data.forEach(function(regency) {
                        html += "<option value=\"" + regency.id + "\">" + regency.name + "</option>";
                    });
                    $("#regency_id").html(html);
                    if (callback) callback();
                } else {
                    Toast.error("Gagal memuat data kabupaten/kota");
                }
            },
            error: function() {
                Toast.error("Terjadi kesalahan saat memuat kabupaten/kota");
            }
        });
    }
    
    function loadDistricts(regencyId, callback) {
        if (!regencyId) return;
        
        $.ajax({
            url: "index.php?page=address&action=get-districts&regency_id=" + regencyId,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var html = "<option value=\"\">Pilih Kecamatan</option>";
                    response.data.forEach(function(district) {
                        html += "<option value=\"" + district.id + "\">" + district.name + "</option>";
                    });
                    $("#district_id").html(html);
                    if (callback) callback();
                } else {
                    Toast.error("Gagal memuat data kecamatan");
                }
            },
            error: function() {
                Toast.error("Terjadi kesalahan saat memuat kecamatan");
            }
        });
    }
    
    function loadVillages(districtId, callback) {
        if (!districtId) return;
        
        $.ajax({
            url: "index.php?page=address&action=get-villages&district_id=" + districtId,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    var html = "<option value=\"\">Pilih Kelurahan/Desa</option>";
                    response.data.forEach(function(village) {
                        html += "<option value=\"" + village.id + "\" data-postal-code=\"" + (village.postal_code || "") + "\">" + village.name + "</option>";
                    });
                    $("#village_id").html(html);
                    if (callback) callback();
                } else {
                    Toast.error("Gagal memuat data kelurahan/desa");
                }
            },
            error: function() {
                Toast.error("Terjadi kesalahan saat memuat kelurahan/desa");
            }
        });
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
