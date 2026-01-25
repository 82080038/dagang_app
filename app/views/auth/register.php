<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Global Functions for Registration Form -->
<script>
// Global function to toggle registration fields based on type
function toggleRegistrationFields() {
    var registrationType = document.getElementById('registration-type').value;
    var individualFields = document.getElementById('individual-fields');
    var joinFields = document.getElementById('join-fields');
    var createFields = document.getElementById('create-fields');
    
    // Hide all fields first
    if (individualFields) individualFields.style.display = 'none';
    if (joinFields) joinFields.style.display = 'none';
    if (createFields) createFields.style.display = 'none';
    
    // Show relevant fields
    switch(registrationType) {
        case 'individual':
            if (individualFields) individualFields.style.display = 'block';
            syncPositionSelects('individual');
            break;
        case 'join_company':
            if (joinFields) joinFields.style.display = 'block';
            syncPositionSelects('join');
            break;
        case 'create_company':
            if (createFields) createFields.style.display = 'block';
            syncPositionSelects('create');
            break;
    }
}

// Global function to sync position selects based on registration type
function syncPositionSelects(type) {
    var individualSelect = document.getElementById('individual-position-select');
    var joinSelect = document.getElementById('join-position-select');
    var createSelect = document.getElementById('create-position-select');
    
    // Hide all position selects and remove name attribute
    var allSelects = document.querySelectorAll('[id$="-position-select"]');
    allSelects.forEach(function(select) {
        select.style.display = 'none';
        select.removeAttribute('name');
    });
    
    // Show relevant position select and set name attribute
    var targetSelect = document.getElementById(type + '-position-select');
    if (targetSelect) {
        targetSelect.style.display = 'block';
        targetSelect.setAttribute('name', 'position');
    }
}

// Global function to validate password confirmation
function validatePasswordConfirmation() {
    var password = document.getElementById('auth-password-input').value;
    var confirmPassword = document.getElementById('auth-confirm-password-input').value;
    var feedback = document.getElementById('password-feedback');
    
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.id = 'password-feedback';
        feedback.className = 'form-text small mt-1';
        var confirmInput = document.getElementById('auth-confirm-password-input');
        if (confirmInput) {
            confirmInput.parentNode.appendChild(feedback);
        }
    }
    
    if (confirmPassword === '') {
        feedback.textContent = '';
        feedback.className = 'form-text small mt-1';
        document.getElementById('auth-confirm-password-input').classList.remove('is-valid', 'is-invalid');
    } else if (password === confirmPassword) {
        feedback.textContent = '✓ Password cocok';
        feedback.className = 'form-text small mt-1 text-success';
        document.getElementById('auth-confirm-password-input').classList.remove('is-invalid');
        document.getElementById('auth-confirm-password-input').classList.add('is-valid');
    } else {
        feedback.textContent = '✗ Password tidak cocok';
        feedback.className = 'form-text small mt-1 text-danger';
        document.getElementById('auth-confirm-password-input').classList.remove('is-valid');
        document.getElementById('auth-confirm-password-input').classList.add('is-invalid');
    }
}

// Global function to check company code availability
function checkCompanyCode(companyCode) {
    var input = document.getElementById('company-code');
    var feedback = document.getElementById('company-code-feedback');
    
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.id = 'company-code-feedback';
        feedback.className = 'form-text small mt-1';
        if (input) input.parentNode.appendChild(feedback);
    }
    
    if (!input || !feedback) return;
    
    feedback.textContent = 'Memeriksa kode perusahaan...';
    feedback.className = 'form-text small mt-1 text-muted';
    
    // Debounce the check
    clearTimeout(window.companyCodeCheckTimeout);
    window.companyCodeCheckTimeout = setTimeout(function() {
        fetch('index.php?page=register&action=check_company_code&company_code=' + encodeURIComponent(companyCode))
            .then(response => response.json())
            .then(data => {
                if (data.found) {
                    feedback.textContent = '✓ Perusahaan ditemukan';
                    feedback.className = 'form-text small mt-1 text-success';
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                    
                    showCompanyDetails(data.company);
                    var joinSelect = document.getElementById('join-position-select');
                    if (joinSelect) joinSelect.disabled = false;
                } else {
                    feedback.textContent = '✗ Kode perusahaan tidak ditemukan';
                    feedback.className = 'form-text small mt-1 text-danger';
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                    
                    hideCompanyDetails();
                    var joinSelect = document.getElementById('join-position-select');
                    if (joinSelect) joinSelect.disabled = true;
                }
            })
            .catch(error => {
                feedback.textContent = '⚠ Gagal memeriksa kode perusahaan';
                feedback.className = 'form-text small mt-1 text-warning';
                input.classList.remove('is-valid', 'is-invalid');
                hideCompanyDetails();
                var joinSelect = document.getElementById('join-position-select');
                if (joinSelect) joinSelect.disabled = true;
            });
    }, 500);
}

// Global function to show company details
function showCompanyDetails(company) {
    var detailsDiv = document.getElementById('company-details');
    var companyNameDisplay = document.getElementById('company-name-display');
    var companyTypeDisplay = document.getElementById('company-type-display');
    var branchNameDisplay = document.getElementById('branch-name-display');
    var branchLocationDisplay = document.getElementById('branch-location-display');
    
    if (!detailsDiv || !companyNameDisplay || !companyTypeDisplay || !branchNameDisplay || !branchLocationDisplay) return;
    
    // Update company details
    companyNameDisplay.textContent = company.company_name || '-';
    companyTypeDisplay.textContent = getCompanyTypeLabel(company.company_type) || '-';
    branchNameDisplay.textContent = company.branch_name || '-';
    
    // Build location string
    var locationParts = [];
    if (company.village_name) locationParts.push(company.village_name);
    if (company.district_name) locationParts.push(company.district_name);
    if (company.regency_name) locationParts.push(company.regency_name);
    if (company.province_name) locationParts.push(company.province_name);
    
    branchLocationDisplay.textContent = locationParts.join(', ') || '-';
    
    // Show details section
    detailsDiv.style.display = 'block';
}

// Global function to hide company details
function hideCompanyDetails() {
    var detailsDiv = document.getElementById('company-details');
    var positionSelect = document.getElementById('join-position-select');
    
    if (detailsDiv) detailsDiv.style.display = 'none';
    if (positionSelect) {
        positionSelect.disabled = true;
        positionSelect.value = '';
    }
}

// Global function to get company type label
function getCompanyTypeLabel(type) {
    const types = {
        'individual': 'Individu/Personal',
        'personal': 'Personal/Home-based',
        'warung': 'Warung',
        'kios': 'Kios',
        'toko_kelontong': 'Toko Kelontong',
        'pusat': 'Perusahaan Pusat',
        'franchise': 'Franchise',
        'distributor': 'Distributor',
        'koperasi': 'Koperasi',
        'perusahaan_besar': 'Perusahaan Besar'
    };
    return types[type] || type;
}

// Global function to check member code availability
function checkMemberCodeAvailability(memberCode) {
    if (!memberCode || memberCode.length < 3) {
        return;
    }
    
    // Show checking indicator
    var input = document.getElementById('auth-member-code-input');
    var feedback = document.getElementById('member-code-feedback');
    
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.id = 'member-code-feedback';
        feedback.className = 'form-text small mt-1';
        if (input) input.parentNode.appendChild(feedback);
    }
    
    if (!input || !feedback) return;
    
    feedback.textContent = 'Memeriksa ketersediaan...';
    feedback.className = 'form-text small mt-1 text-muted';
    
    // Debounce the check (wait 500ms after user stops typing)
    clearTimeout(window.memberCodeCheckTimeout);
    window.memberCodeCheckTimeout = setTimeout(function() {
        fetch('index.php?page=register&action=check_member_code&member_code=' + encodeURIComponent(memberCode))
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    feedback.textContent = '✓ Kode tersedia';
                    feedback.className = 'form-text small mt-1 text-success';
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    feedback.textContent = '✗ Kode sudah digunakan';
                    feedback.className = 'form-text small mt-1 text-danger';
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            })
            .catch(error => {
                feedback.textContent = '⚠ Gagal memeriksa ketersediaan';
                feedback.className = 'form-text small mt-1 text-warning';
                input.classList.remove('is-valid', 'is-invalid');
            });
    }, 500);
}
</script>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Registrasi Pengguna</div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form id="auth-register-form" action="index.php?page=register" method="POST">
                        <?php echo Csrf::input(); ?>
                        
                        <!-- Common Fields for All Types -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Pengguna *</label>
                                <input type="text" id="auth-member-code-input" name="member_code" class="form-control" required value="<?php echo htmlspecialchars($old['member_code'] ?? ''); ?>">
                                <div id="member-code-feedback" class="form-text small mt-1"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" id="auth-member-name-input" name="member_name" class="form-control" required value="<?php echo htmlspecialchars($old['member_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipe Pendaftaran *</label>
                            <select id="registration-type" name="registration_type" class="form-select" required onchange="toggleRegistrationFields()">
                                <option value="">Pilih tipe pendaftaran</option>
                                <option value="individual">🏪 Usaha Perseorangan</option>
                                <option value="join_company">👥 Bergabung dengan Perusahaan</option>
                                <option value="create_company">🏢 Buat Perusahaan Baru</option>
                            </select>
                        </div>
                        
                        <!-- Individual Business Fields -->
                        <div id="individual-fields" style="display: none;">
                            <div class="card border-info mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-store me-2"></i>Detail Usaha Perseorangan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Usaha *</label>
                                        <input type="text" id="business-name" name="business_name" class="form-control" placeholder="Contoh: Toko Makmur">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tipe Usaha *</label>
                                        <select id="business-type" name="business_type" class="form-select">
                                            <option value="individual">Individu/Personal</option>
                                            <option value="personal">Personal/Home-based</option>
                                            <option value="warung">Warung</option>
                                            <option value="kios">Kios</option>
                                            <option value="toko_kelontong">Toko Kelontong</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Posisi *</label>
                                        <select id="individual-position-select" name="position" class="form-select" required>
                                            <option value="owner">👑 Owner/Pemilik Usaha</option>
                                        </select>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Info:</strong> Usaha perseorangan akan dibuatkan sebagai perusahaan dengan 1 cabang utama. Nantinya Anda bisa menambah cabang dan staff saat usaha berkembang.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Join Company Fields -->
                        <div id="join-fields" style="display: none;">
                            <div class="card border-primary mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-building me-2"></i>Bergabung dengan Perusahaan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Kode Perusahaan *</label>
                                        <input type="text" id="company-code" name="company_code" class="form-control" placeholder="Contoh: COMP123">
                                        <small class="form-text text-muted">Dapatkan kode ini dari pemilik/perusahaan Anda</small>
                                        <div id="company-code-feedback" class="form-text small mt-1"></div>
                                    </div>
                                    
                                    <!-- Company Details (Hidden until valid code) -->
                                    <div id="company-details" style="display: none;">
                                        <div class="alert alert-success">
                                            <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Perusahaan Ditemukan</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Nama Perusahaan:</strong><br>
                                                    <span id="company-name-display">-</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Tipe Perusahaan:</strong><br>
                                                    <span id="company-type-display">-</span>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-md-6">
                                                    <strong>Cabang Utama:</strong><br>
                                                    <span id="branch-name-display">-</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Lokasi:</strong><br>
                                                    <span id="branch-location-display">-</span>
                                                </div>
                                            </div>
                                            <hr>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Anda akan bergabung dengan perusahaan di atas sebagai karyawan.
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Posisi *</label>
                                        <select id="join-position-select" name="position" class="form-select" required disabled>
                                            <option value="">Pilih posisi</option>
                                            <option value="manager">👔 Manager</option>
                                            <option value="cashier">💰 Kasir</option>
                                            <option value="staff">👨‍💼 Staff/Karyawan</option>
                                        </select>
                                        <small class="form-text text-muted">Pilih posisi sesuai dengan yang ditawarkan</small>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Perhatian:</strong> Pastikan Anda sudah mendapatkan izin dari pemilik perusahaan sebelum mendaftar.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Create Company Fields -->
                        <div id="create-fields" style="display: none;">
                            <div class="card border-success mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-building me-2"></i>Detail Perusahaan Baru</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Perusahaan *</label>
                                        <input type="text" id="company-name" name="company_name" class="form-control" placeholder="Contoh: PT Makmur Sejahtera">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tipe Perusahaan *</label>
                                        <select id="company-type" name="company_type" class="form-select">
                                            <option value="">Pilih tipe perusahaan</option>
                                            <option value="pusat">🏢 Perusahaan Pusat</option>
                                            <option value="franchise">🍔 Franchise</option>
                                            <option value="distributor">📦 Distributor</option>
                                            <option value="koperasi">🤝 Koperasi</option>
                                            <option value="perusahaan_besar">🏭 Perusahaan Besar</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Posisi *</label>
                                        <select id="create-position-select" name="position" class="form-select" required>
                                            <option value="owner">👑 Owner/Pemilik Perusahaan</option>
                                        </select>
                                    </div>
                                    <div class="alert alert-success">
                                        <i class="fas fa-lightbulb me-2"></i>
                                        <strong>Info:</strong> Perusahaan baru akan dibuat dengan cabang utama. Anda bisa menambah cabang lain setelah pendaftaran.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information (Common for all) -->
                        <div class="card border-secondary mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-address-card me-2"></i>Informasi Kontak</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" id="auth-email-input" name="email" class="form-control" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Telepon *</label>
                                        <input type="text" id="auth-phone-input" name="phone" class="form-control" required value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password *</label>
                                        <input type="password" id="auth-password-input" name="password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Konfirmasi Password *</label>
                                        <input type="password" id="auth-confirm-password-input" name="confirm_password" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Information (Common for all) -->
                        <div class="card border-secondary mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Alamat Lengkap</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Provinsi *</label>
                                        <select id="auth-province-select" name="province_id" class="form-select" required>
                                            <option value="">Pilih Provinsi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kabupaten/Kota *</label>
                                        <select id="auth-regency-select" name="regency_id" class="form-select" required disabled>
                                            <option value="">Pilih Kabupaten/Kota</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kecamatan *</label>
                                        <select id="auth-district-select" name="district_id" class="form-select" required disabled>
                                            <option value="">Pilih Kecamatan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Desa/Kelurahan *</label>
                                        <select id="auth-village-select" name="village_id" class="form-select" required disabled>
                                            <option value="">Pilih Desa/Kelurahan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kode Pos</label>
                                        <div id="auth-postal-code-display" class="form-control-plaintext text-dark-mode">-</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat Jalan *</label>
                                    <textarea id="auth-address-input" name="address_detail" class="form-control" rows="2" required><?php echo htmlspecialchars($old['address_detail'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?page=login" class="btn btn-light me-md-2" id="auth-cancel-btn">Batal</a>
                            <button type="submit" class="btn btn-primary" id="auth-register-btn">Daftar</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="text-muted mb-0">
                            Sudah punya akun? 
                            <a href="index.php?page=login" class="text-decoration-none">
                                <strong>Login di sini</strong>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    function clearOptions(s){ while(s.options.length>1){ s.remove(1);} }
    function loadProvinces(){
        fetch('index.php?page=address&action=get-provinces').then(r=>r.json()).then(res=>{
            if(res.status==='success'){ var s=document.getElementById('auth-province-select'); clearOptions(s); res.data.forEach(function(p){ var o=document.createElement('option'); o.value=p.id; o.textContent=p.name; s.appendChild(o); }); }
        });
    }
    function loadRegencies(pid){
        fetch('index.php?page=address&action=get-regencies&province_id='+encodeURIComponent(pid)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){ var s=document.getElementById('auth-regency-select'); clearOptions(s); res.data.forEach(function(it){ var o=document.createElement('option'); o.value=it.id; o.textContent=it.name; s.appendChild(o); }); s.disabled=false; }
        });
    }
    function loadDistricts(rid){
        fetch('index.php?page=address&action=get-districts&regency_id='+encodeURIComponent(rid)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){ var s=document.getElementById('auth-district-select'); clearOptions(s); res.data.forEach(function(it){ var o=document.createElement('option'); o.value=it.id; o.textContent=it.name; s.appendChild(o); }); s.disabled=false; }
        });
    }
    function loadVillages(did){
        fetch('index.php?page=address&action=get-villages&district_id='+encodeURIComponent(did)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){ var s=document.getElementById('auth-village-select'); clearOptions(s); res.data.forEach(function(it){ var o=document.createElement('option'); o.value=it.id; o.textContent=it.name; s.appendChild(o); }); s.disabled=false; }
        });
    }
    document.addEventListener('DOMContentLoaded', function(){
        loadProvinces();
        var province=document.getElementById('auth-province-select');
        var regency=document.getElementById('auth-regency-select');
        var district=document.getElementById('auth-district-select');
        var village=document.getElementById('auth-village-select');
        var postalCodeDisplay=document.getElementById('auth-postal-code-display');
        province.addEventListener('change', function(){ var v=this.value; regency.disabled=true; district.disabled=true; village.disabled=true; clearOptions(regency); clearOptions(district); clearOptions(village); postalCodeDisplay.textContent='-'; if(v){ loadRegencies(v);} });
        regency.addEventListener('change', function(){ var v=this.value; district.disabled=true; village.disabled=true; clearOptions(district); clearOptions(village); postalCodeDisplay.textContent='-'; if(v){ loadDistricts(v);} });
        district.addEventListener('change', function(){ var v=this.value; village.disabled=true; clearOptions(village); postalCodeDisplay.textContent='-'; if(v){ loadVillages(v);} });
        village.addEventListener('change', function(){ 
            var v=this.value; 
            // Always clear postal code display first when village changes
            postalCodeDisplay.textContent='-';
            
            if(!v){ 
                return; 
            }
            
            fetch('index.php?page=address&action=get-postal-code&village_id='+encodeURIComponent(v))
                .then(r=>r.json())
                .then(res=>{ 
                    if(res.status==='success'){ 
                        postalCodeDisplay.textContent=res.data.postal_code || '-'; 
                    } else {
                        // Keep display as '-' if no postal code found
                        postalCodeDisplay.textContent='-';
                    }
                })
                .catch(function() {
                    // Keep display as '-' on error
                    postalCodeDisplay.textContent='-';
                });
        });
    });
    
    // Toggle registration fields based on selection
    function toggleRegistrationFields() {
        var registrationType = document.getElementById('registration-type').value;
        var individualFields = document.getElementById('individual-fields');
        var joinFields = document.getElementById('join-fields');
        var createFields = document.getElementById('create-fields');
        
        // Hide all fields first
        individualFields.style.display = 'none';
        joinFields.style.display = 'none';
        createFields.style.display = 'none';
        
        // Show relevant fields
        switch(registrationType) {
            case 'individual':
                individualFields.style.display = 'block';
                // Set position field to individual position select
                syncPositionSelects('individual');
                break;
            case 'join_company':
                joinFields.style.display = 'block';
                // Set position field to join position select
                syncPositionSelects('join');
                break;
            case 'create_company':
                createFields.style.display = 'block';
                // Set position field to create position select
                syncPositionSelects('create');
                break;
        }
    }
    
    // Sync position selects based on registration type
    function syncPositionSelects(type) {
        var mainPositionSelect = document.getElementById('auth-position-select');
        var individualSelect = document.getElementById('individual-position-select');
        var joinSelect = document.getElementById('join-position-select');
        var createSelect = document.getElementById('create-position-select');
        
        // Hide all position selects
        if (mainPositionSelect) mainPositionSelect.style.display = 'none';
        if (individualSelect) individualSelect.style.display = 'none';
        if (joinSelect) joinSelect.style.display = 'none';
        if (createSelect) createSelect.style.display = 'none';
        
        // Show relevant position select
        switch(type) {
            case 'individual':
                if (individualSelect) {
                    individualSelect.style.display = 'block';
                    individualSelect.setAttribute('name', 'position');
                }
                break;
            case 'join':
                if (joinSelect) {
                    joinSelect.style.display = 'block';
                    joinSelect.setAttribute('name', 'position');
                }
                break;
            case 'create':
                if (createSelect) {
                    createSelect.style.display = 'block';
                    createSelect.setAttribute('name', 'position');
                }
                break;
        }
    }
    
    // Password confirmation validation
    function validatePasswordConfirmation() {
        var password = document.getElementById('auth-password-input').value;
        var confirmPassword = document.getElementById('auth-confirm-password-input').value;
        var feedback = document.getElementById('password-feedback');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = 'password-feedback';
            feedback.className = 'form-text small mt-1';
            document.getElementById('auth-confirm-password-input').parentNode.appendChild(feedback);
        }
        
        if (confirmPassword === '') {
            feedback.textContent = '';
            feedback.className = 'form-text small mt-1';
            document.getElementById('auth-confirm-password-input').classList.remove('is-valid', 'is-invalid');
        } else if (password === confirmPassword) {
            feedback.textContent = '✓ Password cocok';
            feedback.className = 'form-text small mt-1 text-success';
            document.getElementById('auth-confirm-password-input').classList.remove('is-invalid');
            document.getElementById('auth-confirm-password-input').classList.add('is-valid');
        } else {
            feedback.textContent = '✗ Password tidak cocok';
            feedback.className = 'form-text small mt-1 text-danger';
            document.getElementById('auth-confirm-password-input').classList.remove('is-valid');
            document.getElementById('auth-confirm-password-input').classList.add('is-invalid');
        }
    }
    
    // Auto-generate business code from business name
    document.getElementById('business-name')?.addEventListener('input', function(e) {
        var businessName = e.target.value;
        if (businessName) {
            // Simple code generation: take first 3 letters and add random numbers
            var code = businessName.toUpperCase().replace(/[^A-Z]/g, '').substring(0, 3);
            var randomNum = Math.floor(Math.random() * 1000);
            var suggestedCode = code + randomNum;
            
            // Check availability in real-time
            checkMemberCodeAvailability(suggestedCode);
        }
    });
    
    // Auto-generate company code from company name
    document.getElementById('company-name')?.addEventListener('input', function(e) {
        var companyName = e.target.value;
        if (companyName) {
            var code = companyName.toUpperCase().replace(/[^A-Z]/g, '').substring(0, 3);
            var randomNum = Math.floor(Math.random() * 1000);
            var suggestedCode = code + randomNum;
            
            // Check availability in real-time
            checkMemberCodeAvailability(suggestedCode);
        }
    });
    
    // Real-time validation when user manually changes member code
    document.getElementById('auth-member-code-input')?.addEventListener('input', function(e) {
        var memberCode = e.target.value;
        if (memberCode) {
            checkMemberCodeAvailability(memberCode);
        }
    });
    
    // Company code validation for join company
    document.getElementById('company-code')?.addEventListener('input', function(e) {
        var companyCode = e.target.value;
        if (companyCode && companyCode.length >= 3) {
            checkCompanyCode(companyCode);
        } else {
            hideCompanyDetails();
        }
    });
    
    // Password confirmation validation
    document.getElementById('auth-password-input')?.addEventListener('input', validatePasswordConfirmation);
    document.getElementById('auth-confirm-password-input')?.addEventListener('input', validatePasswordConfirmation);
    
    // Function to check company code and display details
    function checkCompanyCode(companyCode) {
        var input = document.getElementById('company-code');
        var feedback = document.getElementById('company-code-feedback');
        var detailsDiv = document.getElementById('company-details');
        var positionSelect = document.getElementById('join-position-select');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = 'company-code-feedback';
            feedback.className = 'form-text small mt-1';
            input.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = 'Memeriksa kode perusahaan...';
        feedback.className = 'form-text small mt-1 text-muted';
        
        // Disable position select during checking
        positionSelect.disabled = true;
        
        // Debounce the check
        clearTimeout(window.companyCodeCheckTimeout);
        window.companyCodeCheckTimeout = setTimeout(function() {
            fetch('index.php?page=register&action=check_company_code&company_code=' + encodeURIComponent(companyCode))
                .then(response => response.json())
                .then(data => {
                    if (data.found) {
                        feedback.textContent = '✓ Perusahaan ditemukan';
                        feedback.className = 'form-text small mt-1 text-success';
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                        
                        // Show company details
                        showCompanyDetails(data.company);
                        
                        // Enable position select
                        positionSelect.disabled = false;
                    } else {
                        feedback.textContent = '✗ Kode perusahaan tidak ditemukan';
                        feedback.className = 'form-text small mt-1 text-danger';
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                        
                        // Hide company details
                        hideCompanyDetails();
                        
                        // Disable position select
                        positionSelect.disabled = true;
                    }
                })
                .catch(error => {
                    feedback.textContent = '⚠ Gagal memeriksa kode perusahaan';
                    feedback.className = 'form-text small mt-1 text-warning';
                    input.classList.remove('is-valid', 'is-invalid');
                    hideCompanyDetails();
                    positionSelect.disabled = true;
                });
        }, 500);
    }
    
    // Function to show company details
    function showCompanyDetails(company) {
        var detailsDiv = document.getElementById('company-details');
        var companyNameDisplay = document.getElementById('company-name-display');
        var companyTypeDisplay = document.getElementById('company-type-display');
        var branchNameDisplay = document.getElementById('branch-name-display');
        var branchLocationDisplay = document.getElementById('branch-location-display');
        
        // Update company details
        companyNameDisplay.textContent = company.company_name || '-';
        companyTypeDisplay.textContent = getCompanyTypeLabel(company.company_type) || '-';
        branchNameDisplay.textContent = company.branch_name || '-';
        
        // Build location string
        var locationParts = [];
        if (company.village_name) locationParts.push(company.village_name);
        if (company.district_name) locationParts.push(company.district_name);
        if (company.regency_name) locationParts.push(company.regency_name);
        if (company.province_name) locationParts.push(company.province_name);
        
        branchLocationDisplay.textContent = locationParts.join(', ') || '-';
        
        // Show details section
        detailsDiv.style.display = 'block';
    }
    
    // Function to hide company details
    function hideCompanyDetails() {
        var detailsDiv = document.getElementById('company-details');
        var positionSelect = document.getElementById('join-position-select');
        
        detailsDiv.style.display = 'none';
        positionSelect.disabled = true;
        positionSelect.value = '';
    }
    
    // Function to get company type label
    function getCompanyTypeLabel(type) {
        const types = {
            'individual': 'Individu/Personal',
            'personal': 'Personal/Home-based',
            'warung': 'Warung',
            'kios': 'Kios',
            'toko_kelontong': 'Toko Kelontong',
            'pusat': 'Perusahaan Pusat',
            'franchise': 'Franchise',
            'distributor': 'Distributor',
            'koperasi': 'Koperasi',
            'perusahaan_besar': 'Perusahaan Besar'
        };
        return types[type] || type;
    }
    
    // Function to toggle registration fields based on type
    function toggleRegistrationFields() {
        var registrationType = document.getElementById('registration-type').value;
        
        // Hide all type-specific fields
        document.getElementById('individual-fields').style.display = 'none';
        document.getElementById('join-fields').style.display = 'none';
        document.getElementById('create-fields').style.display = 'none';
        
        // Show relevant fields based on selection
        switch(registrationType) {
            case 'individual':
                document.getElementById('individual-fields').style.display = 'block';
                syncPositionSelects('individual');
                break;
            case 'join_company':
                document.getElementById('join-fields').style.display = 'block';
                syncPositionSelects('join');
                break;
            case 'create_company':
                document.getElementById('create-fields').style.display = 'block';
                syncPositionSelects('create');
                break;
        }
    }
    
    // Function to sync position selects
    function syncPositionSelects(type) {
        // Hide all position selects and remove name attribute
        var allSelects = document.querySelectorAll('[id$="-position-select"]');
        allSelects.forEach(function(select) {
            select.style.display = 'none';
            select.removeAttribute('name');
        });
        
        // Show relevant position select and set name attribute
        var targetSelect = document.getElementById(type + '-position-select');
        if (targetSelect) {
            targetSelect.style.display = 'block';
            targetSelect.setAttribute('name', 'position');
        }
    }
    
    // Password confirmation validation
    function validatePasswordConfirmation() {
        var password = document.getElementById('auth-password-input').value;
        var confirmPassword = document.getElementById('auth-confirm-password-input').value;
        var feedback = document.getElementById('password-feedback');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = 'password-feedback';
            feedback.className = 'form-text small mt-1';
            document.getElementById('auth-confirm-password-input').parentNode.appendChild(feedback);
        }
        
        if (confirmPassword === '') {
            feedback.textContent = '';
            feedback.className = 'form-text small mt-1';
            document.getElementById('auth-confirm-password-input').classList.remove('is-valid', 'is-invalid');
        } else if (password === confirmPassword) {
            feedback.textContent = '✓ Password cocok';
            feedback.className = 'form-text small mt-1 text-success';
            document.getElementById('auth-confirm-password-input').classList.remove('is-invalid');
            document.getElementById('auth-confirm-password-input').classList.add('is-valid');
        } else {
            feedback.textContent = '✗ Password tidak cocok';
            feedback.className = 'form-text small mt-1 text-danger';
            document.getElementById('auth-confirm-password-input').classList.remove('is-valid');
            document.getElementById('auth-confirm-password-input').classList.add('is-invalid');
        }
    }
    
    // Auto-generate business code from business name
    document.getElementById('business-name')?.addEventListener('input', function(e) {
        var businessName = e.target.value;
        if (businessName) {
            // Simple code generation: take first 3 letters and add random numbers
            var code = businessName.toUpperCase().replace(/[^A-Z]/g, '').substring(0, 3);
            var randomNum = Math.floor(Math.random() * 1000);
            document.getElementById('auth-member-code-input').value = code + randomNum;
        }
    });
    
    // Add event listeners for password confirmation
    document.getElementById('auth-password-input')?.addEventListener('input', validatePasswordConfirmation);
    document.getElementById('auth-confirm-password-input')?.addEventListener('input', validatePasswordConfirmation);
    
    // Company code validation
    document.getElementById('company-code')?.addEventListener('input', function(e) {
        var companyCode = e.target.value;
        if (companyCode.length >= 3) {
            checkCompanyCode(companyCode);
        } else {
            hideCompanyDetails();
        }
    });
    
    // Member code validation
    document.getElementById('auth-member-code-input')?.addEventListener('input', function(e) {
        var memberCode = e.target.value;
        if (memberCode.length >= 3) {
            checkMemberCodeAvailability(memberCode);
        }
    });
    
    // Function to check company code availability
    function checkCompanyCode(companyCode) {
        var input = document.getElementById('company-code');
        var feedback = document.getElementById('company-code-feedback');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = 'company-code-feedback';
            feedback.className = 'form-text small mt-1';
            input.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = 'Memeriksa kode perusahaan...';
        feedback.className = 'form-text small mt-1 text-muted';
        
        // Debounce the check
        clearTimeout(window.companyCodeCheckTimeout);
        window.companyCodeCheckTimeout = setTimeout(function() {
            fetch('index.php?page=register&action=check_company_code&company_code=' + encodeURIComponent(companyCode))
                .then(response => response.json())
                .then(data => {
                    if (data.found) {
                        feedback.textContent = '✓ Perusahaan ditemukan';
                        feedback.className = 'form-text small mt-1 text-success';
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                        
                        showCompanyDetails(data.company);
                        document.getElementById('join-position-select').disabled = false;
                    } else {
                        feedback.textContent = '✗ Kode perusahaan tidak ditemukan';
                        feedback.className = 'form-text small mt-1 text-danger';
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                        
                        hideCompanyDetails();
                        document.getElementById('join-position-select').disabled = true;
                    }
                })
                .catch(error => {
                    feedback.textContent = '⚠ Gagal memeriksa kode perusahaan';
                    feedback.className = 'form-text small mt-1 text-warning';
                    input.classList.remove('is-valid', 'is-invalid');
                    hideCompanyDetails();
                    document.getElementById('join-position-select').disabled = true;
                });
        }, 500);
    }
    
    // Function to show company details
    function showCompanyDetails(company) {
        var detailsDiv = document.getElementById('company-details');
        var companyNameDisplay = document.getElementById('company-name-display');
        var companyTypeDisplay = document.getElementById('company-type-display');
        var branchNameDisplay = document.getElementById('branch-name-display');
        var branchLocationDisplay = document.getElementById('branch-location-display');
        
        // Update company details
        companyNameDisplay.textContent = company.company_name || '-';
        companyTypeDisplay.textContent = getCompanyTypeLabel(company.company_type) || '-';
        branchNameDisplay.textContent = company.branch_name || '-';
        
        // Build location string
        var locationParts = [];
        if (company.village_name) locationParts.push(company.village_name);
        if (company.district_name) locationParts.push(company.district_name);
        if (company.regency_name) locationParts.push(company.regency_name);
        if (company.province_name) locationParts.push(company.province_name);
        
        branchLocationDisplay.textContent = locationParts.join(', ') || '-';
        
        // Show details section
        detailsDiv.style.display = 'block';
    }
    
    // Function to hide company details
    function hideCompanyDetails() {
        var detailsDiv = document.getElementById('company-details');
        var positionSelect = document.getElementById('join-position-select');
        
        detailsDiv.style.display = 'none';
        positionSelect.disabled = true;
        positionSelect.value = '';
    }
    
    // Function to get company type label
    function getCompanyTypeLabel(type) {
        const types = {
            'individual': 'Individu/Personal',
            'personal': 'Personal/Home-based',
            'warung': 'Warung',
            'kios': 'Kios',
            'toko_kelontong': 'Toko Kelontong',
            'pusat': 'Perusahaan Pusat',
            'franchise': 'Franchise',
            'distributor': 'Distributor',
            'koperasi': 'Koperasi',
            'perusahaan_besar': 'Perusahaan Besar'
        };
        return types[type] || type;
    }
    
    // Function to check member code availability
    function checkMemberCodeAvailability(memberCode) {
        if (!memberCode || memberCode.length < 3) {
            return;
        }
        
        // Show checking indicator
        var input = document.getElementById('auth-member-code-input');
        var feedback = document.getElementById('member-code-feedback');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = 'member-code-feedback';
            feedback.className = 'form-text small mt-1';
            input.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = 'Memeriksa ketersediaan...';
        feedback.className = 'form-text small mt-1 text-muted';
        
        // Debounce the check (wait 500ms after user stops typing)
        clearTimeout(window.memberCodeCheckTimeout);
        window.memberCodeCheckTimeout = setTimeout(function() {
            fetch('index.php?page=register&action=check_member_code&member_code=' + encodeURIComponent(memberCode))
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        feedback.textContent = '✓ Kode tersedia';
                        feedback.className = 'form-text small mt-1 text-success';
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        feedback.textContent = '✗ Kode sudah digunakan';
                        feedback.className = 'form-text small mt-1 text-danger';
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                    }
                })
                .catch(error => {
                    feedback.textContent = '⚠ Gagal memeriksa ketersediaan';
                    feedback.className = 'form-text small mt-1 text-warning';
                    input.classList.remove('is-valid', 'is-invalid');
                });
        }, 500);
    }
})();

// Auth Module
var AuthModule = {
    init: function() {
        this.bindEvents();
    },
    
    bindEvents: function() {
        // Form submission
        $('#auth-register-form').on('submit', this.handleRegister);
        
        // Password strength indicator
        $('#auth-password-input').on('input', this.checkPasswordStrength);
        
        // Email validation
        $('#auth-email-input').on('blur', this.validateEmail);
    },
    
    handleRegister: function(e) {
        e.preventDefault();
        var formData = new FormData(e.target);
        
        // Client-side validation
        var password = formData.get('password');
        var confirmPassword = formData.get('confirm_password');
        
        if (password !== confirmPassword) {
            AuthModule.showNotification('Password dan konfirmasi password tidak cocok', 'error');
            return;
        }
        
        // Show loading state
        $('#auth-register-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mendaftar...');
        
        fetch('index.php?page=register', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Check if response contains error message
            if (html.includes('alert-danger')) {
                // Show error message
                AuthModule.showNotification('Registrasi gagal. Silakan periksa kembali data Anda.', 'error');
            } else {
                // Success - redirect or show success message
                AuthModule.showNotification('Registrasi berhasil! Mengalihkan ke halaman login...', 'success');
                setTimeout(function() {
                    window.location.href = 'index.php?page=login';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            AuthModule.showNotification('Terjadi kesalahan saat mendaftar', 'error');
        })
        .finally(function() {
            // Reset button state
            $('#auth-register-btn').prop('disabled', false).html('Daftar');
        });
    },
    
    checkPasswordStrength: function(e) {
        var password = $(e.target).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        
        // You can add visual feedback here if needed
        console.log('Password strength:', strength);
    },
    
    validateEmail: function(e) {
        var email = $(e.target).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(e.target).addClass('is-invalid');
            AuthModule.showNotification('Format email tidak valid', 'warning');
        } else {
            $(e.target).removeClass('is-invalid');
        }
    },
    
    showNotification: function(message, type) {
        // Create toast notification
        var toastHtml = '<div class="toast align-items-center text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="d-flex">' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '</div>';
        
        // Add to container
        var toastContainer = $('#globalToastContainer');
        if (toastContainer.length === 0) {
            toastContainer = $('<div id="globalToastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            $('body').append(toastContainer);
        }
        
        var toastElement = $(toastHtml);
        toastContainer.append(toastElement);
        
        // Initialize and show toast
        var toast = new bootstrap.Toast(toastElement[0]);
        toast.show();
    }
};

// Initialize when DOM is ready
$(document).ready(function() {
    AuthModule.init();
    
    // Add event listeners for global functions
    // Password confirmation validation
    $('#auth-password-input').on('input', validatePasswordConfirmation);
    $('#auth-confirm-password-input').on('input', validatePasswordConfirmation);
    
    // Auto-generate business code from business name
    $('#business-name').on('input', function(e) {
        var businessName = e.target.value;
        if (businessName) {
            // Simple code generation: take first 3 letters and add random numbers
            var code = businessName.toUpperCase().replace(/[^A-Z]/g, '').substring(0, 3);
            var randomNum = Math.floor(Math.random() * 1000);
            $('#auth-member-code-input').val(code + randomNum);
        }
    });
    
    // Company code validation
    $('#company-code').on('input', function(e) {
        var companyCode = e.target.value;
        if (companyCode.length >= 3) {
            checkCompanyCode(companyCode);
        } else {
            hideCompanyDetails();
        }
    });
    
    // Member code validation
    $('#auth-member-code-input').on('input', function(e) {
        var memberCode = e.target.value;
        if (memberCode.length >= 3) {
            checkMemberCodeAvailability(memberCode);
        }
    });
});
</script>

