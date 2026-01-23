# Sistem Alamat Lengkap - Dokumentasi

## Overview
Sistem alamat yang baru menggabungkan dropdown cascade dari database dengan input manual untuk detail jalan, memberikan solusi alamat yang profesional dan konsisten.

## 🏗️ Komponen Sistem

### 1. **Database Structure**
- **provinces**: Provinsi Indonesia
- **regencies**: Kabupaten/Kota per provinsi  
- **districts**: Kecamatan per kabupaten
- **villages**: Desa/Kelurahan per kecamatan
- **companies**: Tabel perusahaan dengan address fields
- **branches**: Tabel cabang dengan address fields

### 2. **Address Fields**
```sql
-- Companies Table
province_id INT             -- ID Provinsi (wajib)
regency_id INT              -- ID Kabupaten/Kota (wajib)  
district_id INT             -- ID Kecamatan (wajib)
village_id INT              -- ID Desa/Kelurahan (wajib)
address_detail TEXT          -- Alamat jalan manual (wajib)
postal_code VARCHAR(10)     -- Kode Pos (opsional)

-- Branches Table  
province_id INT             -- ID Provinsi (opsional)
regency_id INT              -- ID Kabupaten/Kota (opsional)
district_id INT             -- ID Kecamatan (opsional)
village_id INT              -- ID Desa/Kelurahan (opsional)
address_detail TEXT          -- Alamat jalan manual (opsional)
postal_code VARCHAR(10)     -- Kode Pos (opsional)
```

### 3. **Form Structure**
```
📍 Alamat Lengkap
├── Dropdown Administrasi (Database)
│   ├── Provinsi *
│   ├── Kabupaten/Kota *
│   ├── Kecamatan *
│   └── Desa/Kelurahan *
├── Alamat Jalan (Manual)
│   └── 🏠 Alamat Jalan (Lengkap) *
│       - Jl. Nama Jalan No. 123
│       - RT 001/RW 002
└── 📮 Kode Pos (Opsional)
    - 12345
```

## 🔧 Implementation Details

### **Address Model** (`app/models/Address.php`)
```php
// Methods available:
getProvinces()           // Get all provinces
getRegencies($provinceId) // Get regencies by province
getDistricts($regencyId) // Get districts by regency  
getVillages($districtId) // Get villages by district
getFullAddress(...)      // Get complete address details
createLocalAddressTables() // Create local tables if needed
insertSampleData()       // Insert sample data for testing
```

### **Address Controller** (`app/controllers/AddressController.php`)
```php
// AJAX Endpoints:
GET  ?page=address&action=get-provinces
GET  ?page=address&action=get-regencies&province_id={id}
GET  ?page=address&action=get-districts&regency_id={id}
GET  ?page=address&action=get-villages&district_id={id}
GET  ?page=address&action=get-full-address&...
POST ?page=address&action=setup
```

### **Frontend Integration**
```javascript
// Load cascade dropdowns
loadProvinces()           // Load provinces on page load
loadRegencies(provinceId) // Load regencies when province selected
loadDistricts(regencyId) // Load districts when regency selected
loadVillages(districtId) // Load villages when district selected
```

## 📋 Setup Instructions

### **1. Database Setup**
```bash
# Setup address tables and sample data
http://localhost/dagang/setup_address.php

# Add address fields to existing tables  
http://localhost/dagang/migrate_address_fields.php
```

### **2. Form Integration**
Form sudah terintegrasi di:
- **Companies**: `app/views/companies/index.php`
- **Branches**: (akan diimplementasi saat BranchController dibuat)

### **3. Validation Rules**
```php
// Required fields for companies:
'province_id' => 'required|integer'
'regency_id' => 'required|integer' 
'district_id' => 'required|integer'
'village_id' => 'required|integer'
'address_detail' => 'required|min:5'
'postal_code' => 'nullable|max:10'
```

## 🎯 Usage Examples

### **Frontend - JavaScript**
```javascript
// Load provinces on page load
loadProvinces();

// Handle province change
$('#province_id').on('change', function() {
    const provinceId = $(this).val();
    if (provinceId) {
        loadRegencies(provinceId);
    }
});

// Load company data for edit
function editCompany(companyId) {
    $.ajax({
        url: 'index.php?page=companies&action=get&id=' + companyId,
        success: function(response) {
            // Load address data with cascade
            if (response.data.company.province_id) {
                $('#province_id').val(response.data.company.province_id);
                loadRegencies(response.data.company.province_id);
                // Continue cascade loading...
            }
        }
    });
}
```

### **Backend - Controller**
```php
// Create company with address
public function create() {
    $data = $_POST;
    
    // Address fields are automatically validated
    $errors = $this->companyModel->validateCompany($data);
    
    if (empty($errors)) {
        $companyId = $this->companyModel->createCompany($data);
        // Success response
    }
}
```

## 🔄 Data Flow

1. **User opens form** → `loadProvinces()` dipanggil
2. **User selects province** → `loadRegencies(provinceId)` dipanggil
3. **User selects regency** → `loadDistricts(regencyId)` dipanggil  
4. **User selects district** → `loadVillages(districtId)` dipanggil
5. **User fills address detail** → Manual input
6. **Form submitted** → All address fields validated and saved

## 🎨 UI Components

### **Address Section HTML**
```html
<div class="mb-4">
    <label class="form-label fw-bold">📍 Alamat Lengkap</label>
    
    <!-- Administrative Address -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="province_id" class="form-label">Provinsi *</label>
            <select class="form-select" id="province_id" name="province_id" required>
                <option value="">Pilih Provinsi</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="regency_id" class="form-label">Kabupaten/Kota *</label>
            <select class="form-select" id="regency_id" name="regency_id" required disabled>
                <option value="">Pilih Kabupaten/Kota</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="district_id" class="form-label">Kecamatan *</label>
            <select class="form-select" id="district_id" name="district_id" required disabled>
                <option value="">Pilih Kecamatan</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="village_id" class="form-label">Desa/Kelurahan *</label>
            <select class="form-select" id="village_id" name="village_id" required disabled>
                <option value="">Pilih Desa/Kelurahan</option>
            </select>
        </div>
    </div>
    
    <!-- Street Address -->
    <div class="mb-3">
        <label for="address_detail" class="form-label">🏠 Alamat Jalan (Lengkap) *</label>
        <textarea class="form-control" id="address_detail" name="address_detail" rows="2" 
                  placeholder="Contoh: Jl. Merdeka No. 123, RT 001/RW 002" 
                  required></textarea>
        <small class="text-muted">Masukkan alamat jalan lengkap termasuk nomor rumah dan RT/RW</small>
    </div>
    
    <!-- Postal Code -->
    <div class="mb-3">
        <label for="postal_code" class="form-label">📮 Kode Pos</label>
        <input type="text" class="form-control" id="postal_code" name="postal_code" 
               placeholder="Contoh: 12345" maxlength="10">
        <small class="text-muted">Masukkan kode pos (opsional)</small>
    </div>
</div>
```

## ✅ Benefits

1. **Data Consistency**: Alamat administrasi dari database (tidak ada typo)
2. **User Friendly**: Dropdown lebih mudah digunakan daripada textarea
3. **Complete Coverage**: Semua level administrasi Indonesia ter-cover
4. **Flexible**: Manual input untuk detail jalan yang spesifik
5. **Professional**: Mengikuti standar aplikasi bisnis modern
6. **Scalable**: Mudah ditambah data lokasi baru
7. **Validated**: Built-in validation untuk semua field

## 🚀 Future Enhancements

1. **Google Maps Integration**: Auto-complete alamat
2. **Postal Code Validation**: Validasi kode pos otomatis
3. **Address Formatting**: Format alamat otomatis untuk cetak
4. **Geolocation**: Koordinat GPS untuk lokasi presisi
5. **Import/Export**: Bulk import data alamat
6. **Multi-language**: Support bahasa Inggris untuk alamat

## 🐛 Troubleshooting

### **Common Issues**
1. **404 Error**: Pastikan routing AddressController sudah di-setup
2. **Empty Dropdown**: Jalankan `setup_address.php` untuk inisialisasi data
3. **Validation Error**: Pastikan semua address fields terisi
4. **Database Error**: Jalankan `migrate_address_fields.php` untuk update schema

### **Debug Commands**
```bash
# Check address tables
mysql -u root -e "USE perdagangan_system; SHOW TABLES LIKE '%address%'"

# Check address data  
mysql -u root -e "USE perdagangan_system; SELECT COUNT(*) FROM provinces"
```

---

*Sistem alamat ini sekarang menjadi standar untuk semua form yang memerlukan informasi lokasi dalam aplikasi.*
