<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengguna - Aplikasi Perdagangan Multi-Cabang</title>
    <meta name="description" content="Daftar sebagai pengguna di aplikasi perdagangan multi-cabang. Mendukung usaha perseorangan, karyawan, dan perusahaan besar.">
    <meta name="keywords" content="registrasi, aplikasi perdagangan, multi-cabang, bisnis, usaha kecil">
    <meta name="author" content="PT Dagang Indonesia">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Registrasi - Aplikasi Perdagangan">
    <meta property="og:description" content="Daftar di aplikasi perdagangan multi-cabang untuk mengelola bisnis Anda">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= BASE_URL ?>/register">
    <meta property="og:image" content="<?= BASE_URL ?>/assets/images/register-og.jpg">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Registrasi - Aplikasi Perdagangan">
    <meta name="twitter:description" content="Daftar di aplikasi perdagangan multi-cabang">
    <meta name="twitter:image" content="<?= BASE_URL ?>/assets/images/register-twitter.jpg">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "Aplikasi Perdagangan - Registrasi",
        "description": "Sistem registrasi multi-cabang untuk aplikasi perdagangan",
        "url": "<?= BASE_URL ?>/register",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Any",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR"
        },
        "creator": {
            "@type": "Organization",
            "name": "PT Dagang Indonesia"
        }
    }
    </script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/register.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-gradient text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            Registrasi Pengguna
                        </h4>
                        <p class="mb-0 small opacity-75">Bergabung dengan sistem perdagangan multi-cabang</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Progress Indicator -->
                        <div class="registration-progress mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="progress-step active" data-step="1">
                                    <div class="step-number">1</div>
                                    <div class="step-title">Informasi Dasar</div>
                                </div>
                                <div class="progress-line flex-grow-1"></div>
                                <div class="progress-step" data-step="2">
                                    <div class="step-number">2</div>
                                    <div class="step-title">Detail Pendaftaran</div>
                                </div>
                                <div class="progress-line flex-grow-1"></div>
                                <div class="progress-step" data-step="3">
                                    <div class="step-number">3</div>
                                    <div class="step-title">Konfirmasi</div>
                                </div>
                            </div>
                        </div>
                        
                        <form id="auth-register-form" action="index.php?page=register" method="POST" novalidate>
                            <?php echo Csrf::input(); ?>
                            
                            <!-- Step 1: Basic Information -->
                            <div id="step-1" class="registration-step">
                                <h5 class="mb-4">
                                    <i class="fas fa-user me-2"></i>
                                    Informasi Dasar Pengguna
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="member-code" class="form-label">
                                            Kode Pengguna <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                            <input type="text" 
                                                   id="member-code" 
                                                   name="member_code" 
                                                   class="form-control" 
                                                   required 
                                                   value="<?php echo htmlspecialchars($old['member_code'] ?? ''); ?>"
                                                   placeholder="Contoh: USER123"
                                                   aria-describedby="member-code-help member-code-error"
                                                   aria-invalid="false">
                                        </div>
                                        <div id="member-code-help" class="form-text">
                                            Kode unik untuk identifikasi pengguna Anda (3+ karakter alphanumeric)
                                        </div>
                                        <div id="member-code-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
                                        <div id="member-code-feedback" class="form-text small mt-1"></div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="member-name" class="form-label">
                                            Nama Lengkap <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" 
                                                   id="member-name" 
                                                   name="member_name" 
                                                   class="form-control" 
                                                   required 
                                                   value="<?php echo htmlspecialchars($old['member_name'] ?? ''); ?>"
                                                   placeholder="Contoh: Ahmad Wijaya"
                                                   aria-describedby="member-name-help member-name-error"
                                                   aria-invalid="false">
                                        </div>
                                        <div id="member-name-help" class="form-text">
                                            Nama lengkap sesuai identitas resmi
                                        </div>
                                        <div id="member-name-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" 
                                                   id="email" 
                                                   name="email" 
                                                   class="form-control" 
                                                   required 
                                                   value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                                                   placeholder="email@example.com"
                                                   aria-describedby="email-help email-error"
                                                   aria-invalid="false">
                                        </div>
                                        <div id="email-help" class="form-text">
                                            Email akan digunakan untuk verifikasi dan komunikasi
                                        </div>
                                        <div id="email-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            Telepon <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" 
                                                   id="phone" 
                                                   name="phone" 
                                                   class="form-control" 
                                                   required 
                                                   value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>"
                                                   placeholder="08123456789"
                                                   aria-describedby="phone-help phone-error"
                                                   aria-invalid="false">
                                        </div>
                                        <div id="phone-help" class="form-text">
                                            Nomor telepon aktif WhatsApp/SMS
                                        </div>
                                        <div id="phone-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            Password <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" 
                                                   id="password" 
                                                   name="password" 
                                                   class="form-control password-input" 
                                                   required 
                                                   placeholder="Minimal 8 karakter"
                                                   aria-describedby="password-help password-error password-strength"
                                                   aria-invalid="false">
                                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div id="password-help" class="form-text">
                                            Password minimal 8 karakter dengan huruf besar, kecil, angka, dan simbol
                                        </div>
                                        <div id="password-strength" class="password-strength mt-2"></div>
                                        <div id="password-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm-password" class="form-label">
                                            Konfirmasi Password <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" 
                                                   id="confirm-password" 
                                                   name="confirm_password" 
                                                   class="form-control password-input" 
                                                   required 
                                                   placeholder="Ulangi password"
                                                   aria-describedby="confirm-password-help confirm-password-error"
                                                   aria-invalid="false">
                                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm-password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div id="confirm-password-help" class="form-text">
                                            Masukkan password yang sama untuk konfirmasi
                                        </div>
                                        <div id="confirm-password-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
                                        <div id="password-feedback" class="form-text small mt-1"></div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep(1)">
                                        <i class="fas fa-arrow-right me-2"></i>
                                        Lanjut
                                        <span class="btn-progress d-none">
                                            <i class="fas fa-spinner fa-spin ms-2"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Registration Type & Specific Fields -->
                            <div id="step-2" class="registration-step" style="display: none;">
                                <h5 class="mb-4">
                                    <i class="fas fa-building me-2"></i>
                                    Detail Pendaftaran
                                </h5>
                                
                                <div class="mb-4">
                                    <label for="registration-type" class="form-label">
                                        Tipe Pendaftaran <span class="text-danger">*</span>
                                    </label>
                                    <div class="registration-type-cards">
                                        <div class="card registration-type-card" data-type="individual">
                                            <div class="card-body text-center">
                                                <div class="type-icon mb-3">
                                                    <i class="fas fa-store fa-3x text-primary"></i>
                                                </div>
                                                <h6 class="card-title">🏪 Usaha Perseorangan</h6>
                                                <p class="card-text small">Untuk bisnis solo/individual tanpa cabang</p>
                                                <div class="type-radio">
                                                    <input type="radio" name="registration_type" value="individual" id="type-individual" required>
                                                    <label for="type-individual" class="stretched-link"></label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card registration-type-card" data-type="join_company">
                                            <div class="card-body text-center">
                                                <div class="type-icon mb-3">
                                                    <i class="fas fa-users fa-3x text-success"></i>
                                                </div>
                                                <h6 class="card-title">👥 Bergabung dengan Perusahaan</h6>
                                                <p class="card-text small">Untuk karyawan yang ingin join perusahaan existing</p>
                                                <div class="type-radio">
                                                    <input type="radio" name="registration_type" value="join_company" id="type-join-company" required>
                                                    <label for="type-join-company" class="stretched-link"></label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card registration-type-card" data-type="create_company">
                                            <div class="card-body text-center">
                                                <div class="type-icon mb-3">
                                                    <i class="fas fa-building fa-3x text-warning"></i>
                                                </div>
                                                <h6 class="card-title">🏢 Buat Perusahaan Baru</h6>
                                                <p class="card-text small">Untuk entrepreneur yang ingin mulai multi-cabang</p>
                                                <div class="type-radio">
                                                    <input type="radio" name="registration_type" value="create_company" id="type-create-company" required>
                                                    <label for="type-create-company" class="stretched-link"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Individual Business Fields -->
                                <div id="individual-fields" class="type-specific-fields" style="display: none;">
                                    <div class="card border-info mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-store me-2"></i>
                                                Detail Usaha Perseorangan
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="business-name" class="form-label">
                                                        Nama Usaha <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" 
                                                           id="business-name" 
                                                           name="business_name" 
                                                           class="form-control" 
                                                           placeholder="Contoh: Toko Makmur">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="business-type" class="form-label">
                                                        Tipe Usaha <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="business-type" name="business_type" class="form-select">
                                                        <option value="individual">Individu/Personal</option>
                                                        <option value="personal">Personal/Home-based</option>
                                                        <option value="warung">Warung</option>
                                                        <option value="kios">Kios</option>
                                                        <option value="toko_kelontong">Toko Kelontong</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="individual-position" class="form-label">
                                                    Posisi <span class="text-danger">*</span>
                                                </label>
                                                <select id="individual-position" name="position" class="form-select" required>
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
                                <div id="join-company-fields" class="type-specific-fields" style="display: none;">
                                    <div class="card border-primary mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-building me-2"></i>
                                                Bergabung dengan Perusahaan
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="company-code" class="form-label">
                                                    Kode Perusahaan <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" 
                                                       id="company-code" 
                                                       name="company_code" 
                                                       class="form-control" 
                                                       placeholder="Contoh: COMP123">
                                                <small class="form-text text-muted">Dapatkan kode ini dari pemilik/perusahaan Anda</small>
                                                <div id="company-code-feedback" class="form-text small mt-1"></div>
                                            </div>
                                            
                                            <!-- Company Details (Hidden until valid code) -->
                                            <div id="company-details" style="display: none;">
                                                <div class="alert alert-success">
                                                    <h6 class="alert-heading">
                                                        <i class="fas fa-check-circle me-2"></i>
                                                        Perusahaan Ditemukan
                                                    </h6>
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
                                                <label for="join-position" class="form-label">
                                                    Posisi <span class="text-danger">*</span>
                                                </label>
                                                <select id="join-position" name="position" class="form-select" required disabled>
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
                                <div id="create-company-fields" class="type-specific-fields" style="display: none;">
                                    <div class="card border-success mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-building me-2"></i>
                                                Detail Perusahaan Baru
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="company-name" class="form-label">
                                                        Nama Perusahaan <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" 
                                                           id="company-name" 
                                                           name="company_name" 
                                                           class="form-control" 
                                                           placeholder="Contoh: PT Makmur Sejahtera">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="company-type" class="form-label">
                                                        Tipe Perusahaan <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="company-type" name="company_type" class="form-select">
                                                        <option value="">Pilih tipe perusahaan</option>
                                                        <option value="pusat">🏢 Perusahaan Pusat</option>
                                                        <option value="franchise">🍔 Franchise</option>
                                                        <option value="distributor">📦 Distributor</option>
                                                        <option value="koperasi">🤝 Koperasi</option>
                                                        <option value="perusahaan_besar">🏭 Perusahaan Besar</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="create-position" class="form-label">
                                                    Posisi <span class="text-danger">*</span>
                                                </label>
                                                <select id="create-position" name="position" class="form-select" required>
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
                                
                                <!-- Address Information -->
                                <div class="card border-secondary mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            Alamat Lengkap
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="province" class="form-label">
                                                    Provinsi <span class="text-danger">*</span>
                                                </label>
                                                <select id="province" name="province_id" class="form-select" required>
                                                    <option value="">Pilih Provinsi</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="regency" class="form-label">
                                                    Kabupaten/Kota <span class="text-danger">*</span>
                                                </label>
                                                <select id="regency" name="regency_id" class="form-select" required disabled>
                                                    <option value="">Pilih Kabupaten/Kota</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="district" class="form-label">
                                                    Kecamatan <span class="text-danger">*</span>
                                                </label>
                                                <select id="district" name="district_id" class="form-select" required disabled>
                                                    <option value="">Pilih Kecamatan</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="village" class="form-label">
                                                    Desa/Kelurahan <span class="text-danger">*</span>
                                                </label>
                                                <select id="village" name="village_id" class="form-select" required disabled>
                                                    <option value="">Pilih Desa/Kelurahan</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="postal-code" class="form-label">Kode Pos</label>
                                                <div id="postal-code-display" class="form-control-plaintext">-</div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="address-detail" class="form-label">
                                                Alamat Jalan <span class="text-danger">*</span>
                                            </label>
                                            <textarea id="address-detail" 
                                                      name="address_detail" 
                                                      class="form-control" 
                                                      rows="2" 
                                                      required
                                                      placeholder="Contoh: Jl. Sudirman No. 123, RT 01/RW 02"><?php echo htmlspecialchars($old['address_detail'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary btn-lg" onclick="previousStep(2)">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep(2)">
                                        <i class="fas fa-arrow-right me-2"></i>
                                        Lanjut
                                        <span class="btn-progress d-none">
                                            <i class="fas fa-spinner fa-spin ms-2"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Review & Confirm -->
                            <div id="step-3" class="registration-step" style="display: none;">
                                <h5 class="mb-4">
                                    <i class="fas fa-check-double me-2"></i>
                                    Konfirmasi Data
                                </h5>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Periksa kembali data Anda sebelum submit.</strong> Pastikan semua informasi sudah benar.
                                </div>
                                
                                <!-- Data Summary -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user me-2"></i>
                                                    Informasi Pengguna
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <td><strong>Kode Pengguna:</strong></td>
                                                        <td id="summary-member-code">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Nama Lengkap:</strong></td>
                                                        <td id="summary-member-name">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Email:</strong></td>
                                                        <td id="summary-email">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Telepon:</strong></td>
                                                        <td id="summary-phone">-</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-building me-2"></i>
                                                    Detail Pendaftaran
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="summary-registration-details">
                                                    <!-- Will be populated dynamically -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Address Summary -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            Alamat
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p id="summary-address" class="mb-0">-</p>
                                    </div>
                                </div>
                                
                                <!-- Terms and Conditions -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   id="terms-accepted" 
                                                   name="terms_accepted" 
                                                   class="form-check-input" 
                                                   required>
                                            <label class="form-check-label" for="terms-accepted">
                                                Saya menyetujui <a href="#" onclick="showTermsModal()">Syarat & Ketentuan</a> dan <a href="#" onclick="showPrivacyModal()">Kebijakan Privasi</a>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   id="newsletter-subscribed" 
                                                   name="newsletter_subscribed" 
                                                   class="form-check-input">
                                            <label class="form-check-label" for="newsletter-subscribed">
                                                Saya ingin menerima newsletter dan informasi promo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- CAPTCHA -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        Verifikasi Human <span class="text-danger">*</span>
                                    </label>
                                    <div class="h-captcha" data-sitekey="YOUR_SITE_KEY"></div>
                                    <div id="captcha-feedback" class="form-text small mt-1"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary btn-lg" onclick="previousStep(3)">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </button>
                                    <button type="submit" class="btn btn-success btn-lg" id="submit-btn">
                                        <i class="fas fa-check me-2"></i>
                                        Daftar Sekarang
                                        <span class="btn-progress d-none">
                                            <i class="fas fa-spinner fa-spin ms-2"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Social Login Section -->
                        <div class="text-center mt-4">
                            <div class="divider">
                                <span>atau daftar dengan</span>
                            </div>
                            <div class="social-login-buttons mt-3">
                                <button type="button" class="btn btn-outline-danger me-2" onclick="socialLogin('google')">
                                    <i class="fab fa-google me-2"></i>
                                    Google
                                </button>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="socialLogin('facebook')">
                                    <i class="fab fa-facebook-f me-2"></i>
                                    Facebook
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="socialLogin('linkedin')">
                                    <i class="fab fa-linkedin-in me-2"></i>
                                    LinkedIn
                                </button>
                            </div>
                        </div>
                        
                        <!-- Login Link -->
                        <div class="text-center mt-4">
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
    
    <!-- Terms & Privacy Modals -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Syarat & Ketentuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Terms content will be loaded here -->
                    <p>Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="privacyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kebijakan Privasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Privacy content will be loaded here -->
                    <p>Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- hCaptcha -->
    <script src="https://www.hCaptcha.com/1/api.js" async defer></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/assets/js/register_enhanced.js"></script>
</body>
</html>
