<div class="container-fluid h-100 p-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-building me-2"></i>Manajemen Perusahaan</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#companyModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Perusahaan
        </button>
    </div>
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
            <button class="btn btn-outline-secondary" id="refreshBtn">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
        </div>
    </div>
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
            <tbody id="companiesTableBody"></tbody>
        </table>
    </div>
    <div id="companiesPagination"></div>
</div>
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
                    <div class="row">
                        <div class="col-12">
                            <h6><i class="bi bi-geo-alt me-2"></i>Alamat</h6>
                        </div>
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
                                <select class="form-select" id="regency_id" name="regency_id" disabled required>
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="district_id" class="form-label">Kecamatan *</label>
                                <select class="form-select" id="district_id" name="district_id" disabled required>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="village_id" class="form-label">Desa/Kelurahan *</label>
                                <select class="form-select" id="village_id" name="village_id" disabled required>
                                    <option value="">Pilih Desa/Kelurahan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="street_address" class="form-label">Alamat Jalan *</label>
                                <textarea class="form-control" id="street_address" name="street_address" rows="2" placeholder="Nama jalan, nomor, RT/RW" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kode Pos</label>
                                <div id="postalCodeDisplay" class="form-control-plaintext">-</div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="company_id" name="company_id">
                    <input type="hidden" id="address_id" name="address_id">
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
<div class="modal fade" id="companyDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-building me-2"></i>Detail Perusahaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="companyDetailLoading" class="text-center py-4"><i class="bi bi-spinner fa-spin me-2"></i> Memuat data...</div>
                <div id="companyDetailError" class="alert alert-danger" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="companyDetailErrorText">Gagal memuat data perusahaan</span>
                </div>
                <div id="companyDetailContent" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h4 class="mb-3" id="detailCompanyName"></h4>
                            <div class="row">
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>Kode:</strong> <code id="detailCompanyCode"></code></p>
                                    <p class="mb-2"><strong>Tipe:</strong> <span id="detailCompanyType" class="badge bg-primary"></span></p>
                                    <p class="mb-2"><strong>Level:</strong> <span id="detailScalabilityLevel" class="badge bg-info"></span></p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>Pemilik:</strong> <span id="detailOwnerName"></span></p>
                                    <p class="mb-2"><strong>Kategori:</strong> <span id="detailBusinessCategory"></span></p>
                                    <p class="mb-2"><strong>Status:</strong> <span id="detailStatus" class="badge"></span></p>
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
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><i class="bi bi-telephone me-2"></i>Kontak</h6>
                            <p class="mb-2"><strong>Email:</strong> <a href="#" id="detailEmail"></a></p>
                            <p class="mb-2"><strong>Telepon:</strong> <span id="detailPhone"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-file-text me-2"></i>Dokumen</h6>
                            <p class="mb-2"><strong>NPWP:</strong> <span id="detailTaxId"></span></p>
                            <p class="mb-2"><strong>SIUP:</strong> <span id="detailBusinessLicense"></span></p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6><i class="bi bi-geo-alt me-2"></i>Alamat</h6>
                            <div id="detailAddress" class="bg-light p-3 rounded"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6><i class="bi bi-diagram-2 me-2"></i>Daftar Cabang</h6>
                            <div id="detailBranchesList"><div class="text-muted">Belum ada cabang</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="editFromDetailBtn"><i class="bi bi-pencil me-1"></i> Edit Perusahaan</button>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    function initCompaniesPage() {
        loadCompanies();
        loadProvinces();
        document.getElementById('searchCompaniesInput').addEventListener('input', function() {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(function() { loadCompanies(); }, 300);
        });
        document.getElementById('filterCompanyType').addEventListener('change', function() { loadCompanies(); });
        document.getElementById('refreshBtn').addEventListener('click', function() { loadCompanies(); showToast('success','Data berhasil dimuat ulang'); });
        document.getElementById('companyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitCompanyForm();
        });
        var provinceSelect = document.getElementById('province_id');
        var regencySelect = document.getElementById('regency_id');
        var districtSelect = document.getElementById('district_id');
        var villageSelect = document.getElementById('village_id');
        var postalCodeDisplay = document.getElementById('postalCodeDisplay');
        provinceSelect.addEventListener('change', function() {
            var provinceId = this.value;
            regencySelect.disabled = true; districtSelect.disabled = true; villageSelect.disabled = true;
            clearSelectOptions(regencySelect); clearSelectOptions(districtSelect); clearSelectOptions(villageSelect);
            postalCodeDisplay.textContent = '-';
            if (provinceId) { loadRegencies(provinceId); }
        });
        regencySelect.addEventListener('change', function() {
            var regencyId = this.value;
            districtSelect.disabled = true; villageSelect.disabled = true;
            clearSelectOptions(districtSelect); clearSelectOptions(villageSelect);
            postalCodeDisplay.textContent = '-';
            if (regencyId) { loadDistricts(regencyId); }
        });
        districtSelect.addEventListener('change', function() {
            var districtId = this.value;
            villageSelect.disabled = true;
            clearSelectOptions(villageSelect);
            postalCodeDisplay.textContent = '-';
            if (districtId) { loadVillages(districtId); }
        });
        villageSelect.addEventListener('change', function() {
            var villageId = this.value;
            postalCodeDisplay.textContent = '-';
            if (villageId) { loadPostalCode(villageId); }
        });
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            var companyId = this.getAttribute('data-company-id');
            fetch('index.php?page=companies&action=delete&id=' + companyId, { method: 'POST' })
                .then(r => r.json()).then(response => {
                    if (response.status === 'success') { alert(response.message); var m = bootstrap.Modal.getInstance(document.getElementById('deleteModal')); m.hide(); loadCompanies(); }
                    else { alert('Gagal menghapus perusahaan'); }
                }).catch(() => alert('Terjadi kesalahan'));
        });
        document.getElementById('companyModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('companyForm').reset();
            document.getElementById('company_id').value = '';
            document.getElementById('companyModalTitle').textContent = 'Tambah Perusahaan Baru';
            clearSelectOptions(regencySelect); clearSelectOptions(districtSelect); clearSelectOptions(villageSelect);
            regencySelect.disabled = true; districtSelect.disabled = true; villageSelect.disabled = true;
            postalCodeDisplay.textContent = '-';
        });
    }
    function clearSelectOptions(select) {
        while (select.options.length > 1) { select.remove(1); }
    }
    function loadCompanies(page) {
        page = page || 1;
        var search = document.getElementById('searchCompaniesInput').value;
        var filterType = document.getElementById('filterCompanyType').value;
        var params = new URLSearchParams({ page_num: page, q: search, type: filterType });
        var tbody = document.getElementById('companiesTableBody');
        tbody.innerHTML = '';
        var table = tbody.parentElement;
        UI.loadingShow(table);
        http.fetchJSON('index.php?page=companies&' + params.toString(), {errorMessage:'Gagal memuat data perusahaan'})
            .then(function(response) {
                if (response.status === 'success') { renderCompaniesTable(response.data.companies); renderPagination(response.data.pagination); }
                else { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>'; }
            })
            .catch(function(){ tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Terjadi kesalahan</td></tr>'; })
            .finally(function(){ UI.loadingHide(table); });
    }
    function renderCompaniesTable(companies) {
        var html = '';
        if (!companies || companies.length === 0) { html = '<tr><td colspan="8" class="text-center text-muted">Tidak ada data perusahaan</td></tr>'; }
        else {
            companies.forEach(function(company) {
                var statusBadge = company.is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-aktif</span>';
                var level = company.scalability_level; var levelBadge = '<span class="badge bg-light text-dark">-</span>';
                if (level) {
                    var map = {1:'bg-primary',2:'bg-info',3:'bg-warning',4:'bg-secondary',5:'bg-dark',6:'bg-danger'};
                    levelBadge = '<span class="badge ' + (map[parseInt(level)] || 'bg-light text-dark') + '">Level ' + level + '</span>';
                }
                html += '<tr>' +
                    '<td><div class="d-flex align-items-center"><div class="me-2"><i class="bi bi-building text-primary"></i></div><div><div class="fw-bold">' + (company.company_name || '-') + '</div><small class="text-muted">' + (company.business_category || '-') + '</small></div></div></td>' +
                    '<td><code>' + (company.company_code || '-') + '</code></td>' +
                    '<td>' + (company.company_type || '-') + '</td>' +
                    '<td>' + (company.owner_name || '-') + '</td>' +
                    '<td>' + (company.phone || '-') + '</td>' +
                    '<td>' + levelBadge + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td><div class="btn-group btn-group-sm" role="group">' +
                        '<button class="btn btn-outline-primary btn-sm" onclick="editCompany(' + company.id_company + ')" title="Edit"><i class="bi bi-pencil"></i></button>' +
                        '<button class="btn btn-outline-info btn-sm" onclick="viewCompanyDetails(' + company.id_company + ')" title="Detail"><i class="bi bi-eye"></i></button>' +
                        '<button class="btn btn-outline-warning btn-sm" onclick="toggleCompanyStatus(' + company.id_company + ')" title="Toggle Status"><i class="bi bi-toggle-on"></i></button>' +
                        '<button class="btn btn-outline-danger btn-sm" onclick="deleteCompany(' + company.id_company + ', \'' + (company.company_name || '') + '\')" title="Hapus"><i class="bi bi-trash"></i></button>' +
                    '</div></td>' +
                '</tr>';
            });
        }
        document.getElementById('companiesTableBody').innerHTML = html;
    }
    function renderPagination(pagination) {
        if (!pagination || pagination.total_pages <= 1) { document.getElementById('companiesPagination').innerHTML = ''; return; }
        var html = '<nav><ul class="pagination justify-content-center">';
        if (pagination.current_page > 1) { html += '<li class="page-item"><a class="page-link" href="#" onclick="loadCompanies(' + (pagination.current_page - 1) + ')">Previous</a></li>'; }
        else { html += '<li class="page-item disabled"><span class="page-link">Previous</span></li>'; }
        var start = Math.max(1, pagination.current_page - 2); var end = Math.min(pagination.total_pages, pagination.current_page + 2);
        for (var i = start; i <= end; i++) { var active = i === pagination.current_page ? 'active' : ''; html += '<li class="page-item ' + active + '"><a class="page-link" href="#" onclick="loadCompanies(' + i + ')">' + i + '</a></li>'; }
        if (pagination.current_page < pagination.total_pages) { html += '<li class="page-item"><a class="page-link" href="#" onclick="loadCompanies(' + (pagination.current_page + 1) + ')">Next</a></li>'; }
        else { html += '<li class="page-item disabled"><span class="page-link">Next</span></li>'; }
        html += '</ul></nav>'; document.getElementById('companiesPagination').innerHTML = html;
    }
    function submitCompanyForm() {
        var formData = new URLSearchParams(new FormData(document.getElementById('companyForm'))).toString();
        var companyId = document.getElementById('company_id').value;
        var url = companyId ? 'index.php?page=companies&action=update' : 'index.php?page=companies&action=create';
        fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: formData })
            .then(r => r.json()).then(response => {
                if (response.status === 'success') { showToast('success', response.message || 'Berhasil'); var m = bootstrap.Modal.getInstance(document.getElementById('companyModal')); m && m.hide(); document.getElementById('companyForm').reset(); loadCompanies(); }
                else { showToast('error', response.message || 'Terjadi kesalahan'); }
            }).catch(() => showToast('error','Terjadi kesalahan')).finally(() => {
                document.getElementById('saveCompanyBtn').disabled = false;
                document.getElementById('saveCompanyBtn').innerHTML = '<i class="bi bi-save me-1"></i> Simpan';
            });
    }
    function loadProvinces() {
        fetch('index.php?page=address&action=get-provinces')
            .then(r => r.json()).then(response => {
                if (response.status === 'success') {
                    var select = document.getElementById('province_id');
                    clearSelectOptions(select);
                    response.data.forEach(function(p){ var opt = document.createElement('option'); opt.value = p.id; opt.textContent = p.name; select.appendChild(opt); });
                }
            }).catch(function(){});
    }
    function loadRegencies(provinceId) {
        fetch('index.php?page=address&action=get-regencies&province_id=' + encodeURIComponent(provinceId))
            .then(r => r.json()).then(response => {
                if (response.status === 'success') {
                    var select = document.getElementById('regency_id');
                    clearSelectOptions(select);
                    response.data.forEach(function(rg){ var opt = document.createElement('option'); opt.value = rg.id; opt.textContent = rg.name; select.appendChild(opt); });
                    select.disabled = false;
                }
            }).catch(function(){});
    }
    function loadDistricts(regencyId) {
        fetch('index.php?page=address&action=get-districts&regency_id=' + encodeURIComponent(regencyId))
            .then(r => r.json()).then(response => {
                if (response.status === 'success') {
                    var select = document.getElementById('district_id');
                    clearSelectOptions(select);
                    response.data.forEach(function(dc){ var opt = document.createElement('option'); opt.value = dc.id; opt.textContent = dc.name; select.appendChild(opt); });
                    select.disabled = false;
                }
            }).catch(function(){});
    }
    function loadVillages(districtId) {
        fetch('index.php?page=address&action=get-villages&district_id=' + encodeURIComponent(districtId))
            .then(r => r.json()).then(response => {
                if (response.status === 'success') {
                    var select = document.getElementById('village_id');
                    clearSelectOptions(select);
                    response.data.forEach(function(vl){
                        var opt = document.createElement('option');
                        opt.value = vl.id; opt.textContent = vl.name;
                        if (vl.postal_code) { opt.setAttribute('data-postal-code', vl.postal_code); }
                        select.appendChild(opt);
                    });
                    select.disabled = false;
                }
            }).catch(function(){});
    }
    function loadPostalCode(villageId) {
        var selected = document.querySelector('#village_id option[value="' + villageId + '"]');
        var pc = selected ? selected.getAttribute('data-postal-code') : null;
        var display = document.getElementById('postalCodeDisplay');
        if (pc) { display.textContent = pc; return; }
        fetch('index.php?page=address&action=get-postal-code&village_id=' + encodeURIComponent(villageId))
            .then(r => r.json()).then(response => {
                if (response.status === 'success') {
                    display.textContent = response.data.postal_code || '-';
                    if (selected) { selected.setAttribute('data-postal-code', response.data.postal_code || ''); }
                } else {
                    display.textContent = '-';
                }
            }).catch(function(){ display.textContent = '-'; });
    }
    window.editCompany = function(companyId) {
        fetch('index.php?page=companies&action=get&id=' + companyId)
            .then(r => r.json()).then(response => {
                if (response.status === 'success') {
                    var company = response.data.company;
                    document.getElementById('companyModalTitle').textContent = 'Edit Perusahaan';
                    document.getElementById('company_id').value = company.id_company;
                    document.getElementById('address_id').value = company.address_id || '';
                    document.getElementById('company_name').value = company.company_name || '';
                    document.getElementById('company_code').value = company.company_code || '';
                    document.getElementById('company_type').value = company.company_type || '';
                    document.getElementById('scalability_level').value = company.scalability_level || '';
                    document.getElementById('owner_name').value = company.owner_name || '';
                    document.getElementById('business_category').value = company.business_category || '';
                    document.getElementById('email').value = company.email || '';
                    document.getElementById('phone').value = company.phone || '';
                    var addressId = company.address_id;
                    if (addressId) {
                        fetch('index.php?page=address&action=get&id=' + addressId)
                            .then(r => r.json()).then(ares => {
                                if (ares.status === 'success') {
                                    var a = ares.data.address;
                                    document.getElementById('street_address').value = a.street_address || '';
                                    // reload provinces and set cascade selections
                                    loadProvinces();
                                    setTimeout(function(){
                                        var provinceSelect = document.getElementById('province_id');
                                        var regencySelect = document.getElementById('regency_id');
                                        var districtSelect = document.getElementById('district_id');
                                        var villageSelect = document.getElementById('village_id');
                    var postalCodeDisplay = document.getElementById('postalCodeDisplay');
                                        provinceSelect.value = a.province_id || '';
                                        if (a.province_id) {
                                            loadRegencies(a.province_id);
                                            setTimeout(function(){
                                                regencySelect.value = a.regency_id || '';
                                                if (a.regency_id) {
                                                    loadDistricts(a.regency_id);
                                                    setTimeout(function(){
                                                        districtSelect.value = a.district_id || '';
                                                        if (a.district_id) {
                                                            loadVillages(a.district_id);
                                                            setTimeout(function(){
                                                                villageSelect.value = a.village_id || '';
                                                                postalCodeDisplay.textContent = a.postal_code || '-';
                                                            },300);
                                                        }
                                                    },300);
                                                }
                                            },300);
                                        }
                                    },300);
                                }
                            }).catch(()=>{});
                    }
                    new bootstrap.Modal(document.getElementById('companyModal')).show();
                } else { showToast('error','Terjadi kesalahan'); }
            }).catch(() => showToast('error','Terjadi kesalahan'));
    };
    window.deleteCompany = function(companyId, companyName) {
        document.getElementById('deleteCompanyName').textContent = companyName || '';
        document.getElementById('confirmDeleteBtn').setAttribute('data-company-id', companyId);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    };
    window.toggleCompanyStatus = function(companyId) {
        fetch('index.php?page=companies&action=toggle-status&id=' + companyId, { method: 'POST' })
            .then(r => r.json()).then(response => {
                if (response.status === 'success') { showToast('success', response.message || 'Berhasil'); loadCompanies(); }
                else { showToast('warning', response.message || 'Gagal mengubah status'); }
            }).catch(() => showToast('error','Terjadi kesalahan'));
    };
    window.viewCompanyDetails = function(companyId) {
        document.getElementById('companyDetailLoading').style.display = '';
        document.getElementById('companyDetailError').style.display = 'none';
        document.getElementById('companyDetailContent').style.display = 'none';
        new bootstrap.Modal(document.getElementById('companyDetailModal')).show();
        fetch('index.php?page=companies&action=details&id=' + companyId)
            .then(r => r.json()).then(response => {
                if (response.status === 'success') {
                    populateCompanyDetails(response.data);
                    document.getElementById('companyDetailLoading').style.display = 'none';
                    document.getElementById('companyDetailContent').style.display = '';
                } else {
                    document.getElementById('companyDetailLoading').style.display = 'none';
                    document.getElementById('companyDetailError').style.display = '';
                    document.getElementById('companyDetailErrorText').textContent = response.message || 'Gagal memuat data perusahaan';
                }
            }).catch(err => {
                document.getElementById('companyDetailLoading').style.display = 'none';
                document.getElementById('companyDetailError').style.display = '';
                document.getElementById('companyDetailErrorText').textContent = 'Terjadi kesalahan: ' + err;
            });
    };
    function populateCompanyDetails(data) {
        var company = data.company; var statistics = data.statistics || {}; var branches = data.branches || {};
        document.getElementById('detailCompanyName').textContent = company.company_name || '-';
        document.getElementById('detailCompanyCode').textContent = company.company_code || '-';
        var level = company.scalability_level; var levelClass = 'bg-light text-dark';
        if (level) { var map = {1:'bg-primary',2:'bg-info',3:'bg-warning',4:'bg-secondary',5:'bg-dark',6:'bg-danger'}; levelClass = map[parseInt(level)] || levelClass; }
        document.getElementById('detailCompanyType').textContent = company.company_type || '-';
        document.getElementById('detailCompanyType').className = 'badge bg-primary';
        document.getElementById('detailScalabilityLevel').textContent = 'Level ' + (level || '-');
        document.getElementById('detailScalabilityLevel').className = 'badge ' + levelClass;
        var statusBadge = company.is_active ? 'bg-success' : 'bg-danger'; var statusText = company.is_active ? 'Aktif' : 'Non-aktif';
        document.getElementById('detailStatus').textContent = statusText; document.getElementById('detailStatus').className = 'badge ' + statusBadge;
        document.getElementById('detailBranchCount').textContent = statistics.total_branches || branches.branches_count || 0;
        document.getElementById('detailMemberCount').textContent = statistics.total_members || 0;
        document.getElementById('detailProductCount').textContent = statistics.total_products || 0;
        var email = company.email || '-';
        if (email !== '-') { document.getElementById('detailEmail').setAttribute('href', 'mailto:' + email); document.getElementById('detailEmail').textContent = email; }
        else { document.getElementById('detailEmail').setAttribute('href', '#'); document.getElementById('detailEmail').textContent = email; }
        document.getElementById('detailPhone').textContent = company.phone || '-';
        document.getElementById('detailTaxId').textContent = company.tax_id || '-';
        document.getElementById('detailBusinessLicense').textContent = company.business_license || '-';
        var addressHtml = company.full_address || company.address || 'Alamat tidak tersedia';
        document.getElementById('detailAddress').innerHTML = addressHtml;
        document.getElementById('editFromDetailBtn').onclick = function() {
            bootstrap.Modal.getInstance(document.getElementById('companyDetailModal')).hide();
            editCompany(company.id_company);
        };
        var branchesInfo = statistics.total_branches || branches.branches_count || 0;
        if (branchesInfo > 0) { document.getElementById('detailBranchesList').innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Perusahaan ini memiliki ' + branchesInfo + ' cabang</div>'; }
        else { document.getElementById('detailBranchesList').innerHTML = '<div class="text-muted">Belum ada cabang</div>'; }
    }
    window.loadCompanies = loadCompanies;
    document.addEventListener('DOMContentLoaded', initCompaniesPage);
})();
</script>
