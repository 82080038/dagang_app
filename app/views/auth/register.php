<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Registrasi Pengguna</div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form action="index.php?page=register" method="POST">
                        <?php echo Csrf::input(); ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Pengguna *</label>
                                <input type="text" name="member_code" class="form-control" required value="<?php echo htmlspecialchars($old['member_code'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" name="member_name" class="form-control" required value="<?php echo htmlspecialchars($old['member_name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Posisi *</label>
                                <select name="position" class="form-select" required>
                                    <option value="owner">Owner/Pemilik</option>
                                    <option value="manager">Manager</option>
                                    <option value="cashier">Kasir</option>
                                    <option value="staff">Staff/Karyawan</option>
                                </select>
                            </div>
                        </div>
                        <h6 class="mt-3"><i class="bi bi-geo-alt me-2"></i>Alamat Pengguna</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provinsi *</label>
                                <select id="province_id" name="province_id" class="form-select" required>
                                    <option value="">Pilih Provinsi</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kabupaten/Kota *</label>
                                <select id="regency_id" name="regency_id" class="form-select" required disabled>
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kecamatan *</label>
                                <select id="district_id" name="district_id" class="form-select" required disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Desa/Kelurahan *</label>
                                <select id="village_id" name="village_id" class="form-select" required disabled>
                                    <option value="">Pilih Desa/Kelurahan</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Pos</label>
                                <div id="postalCodeDisplay" class="form-control-plaintext">-</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Jalan *</label>
                            <textarea name="address_detail" id="address_detail" class="form-control" rows="2" required><?php echo htmlspecialchars($old['address_detail'] ?? ''); ?></textarea>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?page=login" class="btn btn-light me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        </div>
                    </form>
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
            if(res.status==='success'){ var s=document.getElementById('province_id'); clearOptions(s); res.data.forEach(function(p){ var o=document.createElement('option'); o.value=p.id; o.textContent=p.name; s.appendChild(o); }); }
        });
    }
    function loadRegencies(pid){
        fetch('index.php?page=address&action=get-regencies&province_id='+encodeURIComponent(pid)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){ var s=document.getElementById('regency_id'); clearOptions(s); res.data.forEach(function(it){ var o=document.createElement('option'); o.value=it.id; o.textContent=it.name; s.appendChild(o); }); s.disabled=false; }
        });
    }
    function loadDistricts(rid){
        fetch('index.php?page=address&action=get-districts&regency_id='+encodeURIComponent(rid)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){ var s=document.getElementById('district_id'); clearOptions(s); res.data.forEach(function(it){ var o=document.createElement('option'); o.value=it.id; o.textContent=it.name; s.appendChild(o); }); s.disabled=false; }
        });
    }
    function loadVillages(did){
        fetch('index.php?page=address&action=get-villages&district_id='+encodeURIComponent(did)).then(r=>r.json()).then(res=>{
            if(res.status==='success'){ var s=document.getElementById('village_id'); clearOptions(s); res.data.forEach(function(it){ var o=document.createElement('option'); o.value=it.id; o.textContent=it.name; s.appendChild(o); }); s.disabled=false; }
        });
    }
    document.addEventListener('DOMContentLoaded', function(){
        loadProvinces();
        var province=document.getElementById('province_id');
        var regency=document.getElementById('regency_id');
        var district=document.getElementById('district_id');
        var village=document.getElementById('village_id');
        var postalCodeDisplay=document.getElementById('postalCodeDisplay');
        province.addEventListener('change', function(){ var v=this.value; regency.disabled=true; district.disabled=true; village.disabled=true; clearOptions(regency); clearOptions(district); clearOptions(village); if(v){ loadRegencies(v);} });
        regency.addEventListener('change', function(){ var v=this.value; district.disabled=true; village.disabled=true; clearOptions(district); clearOptions(village); if(v){ loadDistricts(v);} });
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
})();
</script>
