<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tambah Cabang Baru</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?page=branches" class="btn btn-sm btn-outline-secondary" id="branch-back-btn">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Cabang</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($_SESSION['errors'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($_SESSION['errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>

                    <form id="branch-create-form" action="index.php?page=branches&action=create" method="POST">
                        <?php echo Csrf::input(); ?>
                    
                    <div id="staff-requirement-info" style="display: none;"></div>
                    
                    <div class="mb-3">
                            <label class="form-label">Perusahaan Induk <span class="text-danger">*</span></label>
                            <select id="branch-company-select" name="company_id" class="form-select" required>
                                <option value="">Pilih Perusahaan</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo $company['id_company']; ?>" <?php echo (isset($_SESSION['old_input']['company_id']) && $_SESSION['old_input']['company_id'] == $company['id_company']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($company['company_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($userCompanyId): ?>
                                <small class="form-text text-muted">Sebagai pemilik perusahaan, Anda hanya dapat menambah cabang untuk perusahaan Anda sendiri.</small>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Cabang <span class="text-danger">*</span></label>
                                <input type="text" id="branch-code-input" name="branch_code" class="form-control" required 
                                       placeholder="Contoh: CAB-JKT-001"
                                       value="<?php echo $_SESSION['old_input']['branch_code'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Cabang <span class="text-danger">*</span></label>
                                <input type="text" id="branch-name-input" name="branch_name" class="form-control" required
                                       placeholder="Nama Cabang"
                                       value="<?php echo $_SESSION['old_input']['branch_name'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label">Tipe Operasi Cabang <span class="text-danger">*</span></label>
                                <select id="branch-operation-type-select" name="branch_operation_type" class="form-select" required onchange="toggleStaffRequirement()">
                                    <option value="">Pilih Tipe Operasi</option>
                                    <option value="solo">Solo Operation (Tanpa Staff)</option>
                                    <option value="minimal">Minimal Operation (1-2 Staff)</option>
                                    <option value="with_staff">Standard Operation (Dengan Staff)</option>
                                    <option value="full_staff">Full Operation (Staff Lengkap)</option>
                                </select>
                                <small class="form-text text-muted">Pilih tipe operasi cabang sesuai kebutuhan bisnis Anda</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tipe Usaha <span class="text-danger">*</span></label>
                                <select id="branch-type-select" name="branch_type" class="form-select" required>
                                    <option value="">Pilih Tipe Usaha</option>
                                    <option value="toko">Toko</option>
                                    <option value="warung">Warung</option>
                                    <option value="minimarket">Minimarket</option>
                                    <option value="gerai">Gerai</option>
                                    <option value="kios">Kios</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Penanggung Jawab <span class="text-danger">*</span></label>
                                <input type="text" id="branch-owner-input" name="owner_name" class="form-control" required
                                       value="<?php echo $_SESSION['old_input']['owner_name'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" id="branch-email-input" name="email" class="form-control"
                                       value="<?php echo $_SESSION['old_input']['email'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" id="branch-phone-input" name="phone" class="form-control"
                                       value="<?php echo $_SESSION['old_input']['phone'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <select id="branch-province-select" name="province_id" class="form-select" required>
                                    <option value="">Pilih Provinsi</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                                <select id="branch-regency-select" name="regency_id" class="form-select" required disabled>
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                <select id="branch-district-select" name="district_id" class="form-select" required disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Desa/Kelurahan</label>
                                <select id="branch-village-select" name="village_id" class="form-select" disabled>
                                    <option value="">Ikuti Desa/Kelurahan Perusahaan Induk (opsional)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Jalan <span class="text-danger">*</span></label>
                            <textarea id="branch-address-input" name="address_detail" class="form-control" rows="2" required><?php echo $_SESSION['old_input']['address_detail'] ?? ''; ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Pos</label>
                                <div id="branch-postal-code-display" class="form-control-plaintext text-dark-mode">-</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?page=branches" class="btn btn-light me-md-2" id="branch-cancel-btn">Batal</a>
                            <button type="submit" class="btn btn-primary" id="branch-save-btn">Simpan Cabang</button>
                        </div>
                    </form>
                    <?php if(isset($_SESSION['old_input'])) unset($_SESSION['old_input']); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                    <p class="small text-muted mb-0">
                        Pastikan memilih Perusahaan Induk yang benar. Kode Cabang harus unik dalam satu perusahaan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    function clearOptions(select){ while(select.options.length>1){ select.remove(1);} }
    function loadProvinces(){
        fetch('index.php?page=address&action=get-provinces').then(r=>r.json()).then(res=>{
            if(res.status==='success'){
                var select=document.getElementById('branch-province-select'); clearOptions(select);
                res.data.forEach(function(p){ var o=document.createElement('option'); o.value=p.id; o.textContent=p.name; select.appendChild(o); });
            }
        });
    }
    function loadRegencies(pid){
        fetch('index.php?page=address&action=get-regencies&province_id='+encodeURIComponent(pid)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){
                var s=document.getElementById('branch-regency-select'); clearOptions(s);
                res.data.forEach(function(rg){ var o=document.createElement('option'); o.value=rg.id; o.textContent=rg.name; s.appendChild(o); }); s.disabled=false;
            }
        });
    }
    function loadDistricts(rid){
        fetch('index.php?page=address&action=get-districts&regency_id='+encodeURIComponent(rid)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){
                var s=document.getElementById('branch-district-select'); clearOptions(s);
                res.data.forEach(function(dc){ var o=document.createElement('option'); o.value=dc.id; o.textContent=dc.name; s.appendChild(o); }); s.disabled=false;
            }
        });
    }
    function loadVillages(did){
        fetch('index.php?page=address&action=get-villages&district_id='+encodeURIComponent(did)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){
                var s=document.getElementById('branch-village-select'); clearOptions(s);
                res.data.forEach(function(vl){ var o=document.createElement('option'); o.value=vl.id; o.textContent=vl.name; if(vl.postal_code){ o.setAttribute('data-postal-code', vl.postal_code);} s.appendChild(o); }); s.disabled=false;
            }
        });
    }
    function prefillFromCompany(companyId){
        fetch('index.php?page=companies&action=get&id='+companyId).then(r=>r.json()).then(res=>{
            if(res.status==='success'){
                var company=res.data.company; var addressId=company.address_id;
                if(addressId){
                    fetch('index.php?page=address&action=get&id='+addressId).then(r=>r.json()).then(ar=>{
                        if(ar.status==='success'){
                            var a=ar.data.address;
                            var provinceSelect=document.getElementById('branch-province-select');
                            var regencySelect=document.getElementById('branch-regency-select');
                            var districtSelect=document.getElementById('branch-district-select');
                            var villageSelect=document.getElementById('branch-village-select');
                            loadProvinces();
                            setTimeout(function(){
                                provinceSelect.value=a.province_id; regencySelect.disabled=true; districtSelect.disabled=true; villageSelect.disabled=true;
                                loadRegencies(a.province_id);
                                setTimeout(function(){
                                    regencySelect.value=a.regency_id;
                                    loadDistricts(a.regency_id);
                                    setTimeout(function(){
                                        districtSelect.value=a.district_id;
                                        loadVillages(a.district_id);
                                        setTimeout(function(){
                                            villageSelect.value=a.village_id || '';
                                            provinceSelect.disabled=true; regencySelect.disabled=true; districtSelect.disabled=true; villageSelect.disabled=false;
                                        },300);
                                    },300);
                                },300);
                            },300);
                        }
                    });
                } else {
                    var provinceSelect=document.getElementById('branch-province-select');
                    var regencySelect=document.getElementById('branch-regency-select');
                    var districtSelect=document.getElementById('branch-district-select');
                    var villageSelect=document.getElementById('branch-village-select');
                    provinceSelect.disabled=false; regencySelect.disabled=true; districtSelect.disabled=true; villageSelect.disabled=true;
                    clearOptions(regencySelect); clearOptions(districtSelect); clearOptions(villageSelect);
                }
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function(){
        loadProvinces();
        var province=document.getElementById('branch-province-select');
        var regency=document.getElementById('branch-regency-select');
        var district=document.getElementById('branch-district-select');
        var village=document.getElementById('branch-village-select');
        province.addEventListener('change', function(){ var v=this.value; regency.disabled=true; district.disabled=true; village.disabled=true; clearOptions(regency); clearOptions(district); clearOptions(village); document.getElementById('branch-postal-code-display').textContent='-'; if(v){ loadRegencies(v);} });
        regency.addEventListener('change', function(){ var v=this.value; district.disabled=true; village.disabled=true; clearOptions(district); clearOptions(village); document.getElementById('branch-postal-code-display').textContent='-'; if(v){ loadDistricts(v);} });
        district.addEventListener('change', function(){ var v=this.value; village.disabled=true; clearOptions(village); document.getElementById('branch-postal-code-display').textContent='-'; if(v){ loadVillages(v);} });
        village.addEventListener('change', function(){ 
            var v=this.value; 
            var selectedOption=document.querySelector('#branch-village-select option[value="'+v+'"]'); 
            var pc=selectedOption? selectedOption.getAttribute('data-postal-code') : null; 
            var display=document.getElementById('branch-postal-code-display'); 
            
            // Always clear postal code display first when village changes
            display.textContent='-';
            
            if(pc){ 
                display.textContent=pc; 
            } else if(v){ 
                fetch('index.php?page=address&action=get-postal-code&village_id='+encodeURIComponent(v))
                    .then(r=>r.json())
                    .then(res=>{ 
                        if(res.status==='success'){ 
                            display.textContent=res.data.postal_code || '-'; 
                        } else {
                            // Keep display as '-' if no postal code found
                            display.textContent='-';
                        }
                    })
                    .catch(function() {
                        // Keep display as '-' on error
                        display.textContent='-';
                    });
            }
            // If village is empty/null, display remains '-' (cleared above)
        });
        var companySelect=document.getElementById('branch-company-select');
        companySelect.addEventListener('change', function(){ var cid=this.value; if(cid){ prefillFromCompany(cid);} });
        
        // Toggle staff requirement based on operation type
        function toggleStaffRequirement() {
            var operationType = document.getElementById('branch-operation-type-select').value;
            var staffInfo = document.getElementById('staff-requirement-info');
            
            if (staffInfo) {
                var messages = {
                    'solo': 'Cabang dapat beroperasi tanpa staff. Owner dapat mengelola operasi langsung.',
                    'minimal': 'Cabang memerlukan 1-2 staff untuk operasi dasar (kasir/penjaga toko).',
                    'with_staff': 'Cabang memerlukan staff lengkap untuk operasi normal (manager, kasir, staff).',
                    'full_staff': 'Cabang memerlukan staff lengkap dengan berbagai posisi untuk operasi maksimal.'
                };
                
                var alertTypes = {
                    'solo': 'info',
                    'minimal': 'warning', 
                    'with_staff': 'success',
                    'full_staff': 'primary'
                };
                
                if (operationType && messages[operationType]) {
                    staffInfo.innerHTML = '<div class="alert alert-' + alertTypes[operationType] + ' mb-3">' +
                        '<i class="fas fa-info-circle me-2"></i>' + messages[operationType] + '</div>';
                    staffInfo.style.display = 'block';
                } else {
                    staffInfo.style.display = 'none';
                }
            }
        }
        
        // Form submission
        document.getElementById('branch-create-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            // Show loading state
            var submitBtn = document.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
            
            fetch('index.php?page=branches&action=create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('Cabang berhasil ditambahkan!', 'success');
                    } else {
                        alert('Cabang berhasil ditambahkan!');
                    }
                    // Redirect to branches list
                    setTimeout(function() {
                        window.location.href = 'index.php?page=branches';
                    }, 1500);
                } else {
                    // Show error message
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + (data.message || 'Terjadi kesalahan'), 'error');
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('Terjadi kesalahan saat menyimpan cabang', 'error');
                } else {
                    alert('Terjadi kesalahan saat menyimpan cabang');
                }
            })
            .finally(function() {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });
})();
</script>
