/**
 * Enhanced Registration Form JavaScript
 * Multi-step registration with validation and real-time feedback
 */

class EnhancedRegistration {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 3;
        this.formData = {};
        this.validationRules = this.getValidationRules();
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeProgress();
        this.loadCachedData();
        this.setupFormValidation();
    }
    
    getValidationRules() {
        return {
            member_code: {
                required: true,
                minLength: 3,
                maxLength: 20,
                pattern: /^[A-Z0-9]+$/,
                unique: true,
                message: 'Kode pengguna harus 3-20 karakter alphanumeric'
            },
            member_name: {
                required: true,
                minLength: 2,
                maxLength: 100,
                pattern: /^[a-zA-Z\s\-\.\']+$/,
                message: 'Nama hanya boleh huruf, spasi, dan karakter standar'
            },
            email: {
                required: true,
                email: true,
                maxLength: 255,
                message: 'Format email tidak valid'
            },
            phone: {
                required: true,
                pattern: /^(\+62|62|08)[0-9]{8,13}$/,
                message: 'Format nomor telepon Indonesia tidak valid'
            },
            password: {
                required: true,
                minLength: 8,
                maxLength: 128,
                pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
                message: 'Password harus 8-128 karakter dengan huruf besar, kecil, angka, dan simbol'
            },
            confirm_password: {
                required: true,
                match: 'password',
                message: 'Password konfirmasi tidak cocok'
            }
        };
    }
    
    bindEvents() {
        // Registration type selection
        document.querySelectorAll('input[name="registration_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.handleRegistrationTypeChange(e));
        });
        
        // Form validation events
        this.setupValidationEvents();
        
        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', (e) => this.togglePasswordVisibility(e));
        });
        
        // Address cascade
        this.setupAddressCascade();
        
        // Company code validation
        document.getElementById('company-code')?.addEventListener('input', (e) => {
            this.debounce(() => this.checkCompanyCode(e.target.value), 500);
        });
        
        // Member code validation
        document.getElementById('member-code')?.addEventListener('input', (e) => {
            this.debounce(() => this.checkMemberCode(e.target.value), 500);
        });
        
        // Form submission
        document.getElementById('auth-register-form')?.addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });
        
        // Auto-save form data
        this.setupAutoSave();
        
        // Keyboard navigation
        this.setupKeyboardNavigation();
    }
    
    setupValidationEvents() {
        Object.keys(this.validationRules).forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('blur', () => this.validateField(fieldName));
                field.addEventListener('input', () => this.clearFieldError(fieldName));
                
                // Special handling for password strength
                if (fieldName === 'password') {
                    field.addEventListener('input', () => this.checkPasswordStrength(field.value));
                }
                
                // Special handling for password confirmation
                if (fieldName === 'confirm_password') {
                    field.addEventListener('input', () => this.validatePasswordConfirmation());
                }
            }
        });
    }
    
    validateField(fieldName) {
        const field = document.getElementById(fieldName);
        const rules = this.validationRules[fieldName];
        const value = field.value.trim();
        
        if (!rules) return true;
        
        // Required validation
        if (rules.required && !value) {
            this.showFieldError(fieldName, 'Field ini wajib diisi');
            return false;
        }
        
        // Length validation
        if (rules.minLength && value.length < rules.minLength) {
            this.showFieldError(fieldName, `Minimal ${rules.minLength} karakter`);
            return false;
        }
        
        if (rules.maxLength && value.length > rules.maxLength) {
            this.showFieldError(fieldName, `Maksimal ${rules.maxLength} karakter`);
            return false;
        }
        
        // Pattern validation
        if (rules.pattern && !rules.pattern.test(value)) {
            this.showFieldError(fieldName, rules.message);
            return false;
        }
        
        // Email validation
        if (rules.email && !this.isValidEmail(value)) {
            this.showFieldError(fieldName, 'Format email tidak valid');
            return false;
        }
        
        // Password confirmation validation
        if (rules.match) {
            const matchField = document.getElementById(rules.match);
            if (matchField && value !== matchField.value) {
                this.showFieldError(fieldName, rules.message);
                return false;
            }
        }
        
        // Unique validation (AJAX)
        if (rules.unique && value) {
            this.checkUniqueField(fieldName, value);
        }
        
        this.clearFieldError(fieldName);
        return true;
    }
    
    showFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);
        
        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            field.setAttribute('aria-invalid', 'true');
        }
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }
    
    clearFieldError(fieldName) {
        const field = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);
        
        if (field) {
            field.classList.remove('is-invalid');
            field.setAttribute('aria-invalid', 'false');
        }
        
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    checkPasswordStrength(password) {
        const strengthElement = document.getElementById('password-strength');
        if (!strengthElement) return;
        
        let strength = 0;
        let feedback = [];
        
        // Length check
        if (password.length >= 8) strength++;
        else feedback.push('Minimal 8 karakter');
        
        // Complexity checks
        if (/[a-z]/.test(password)) strength++;
        else feedback.push('Huruf kecil');
        
        if (/[A-Z]/.test(password)) strength++;
        else feedback.push('Huruf besar');
        
        if (/\d/.test(password)) strength++;
        else feedback.push('Angka');
        
        if (/[@$!%*?&]/.test(password)) strength++;
        else feedback.push('Simbol (@$!%*?&)');
        
        // Update UI
        const strengthLevels = ['Sangat Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
        const strengthColors = ['danger', 'warning', 'info', 'success', 'success'];
        
        const level = Math.min(strength, 4);
        strengthElement.innerHTML = `
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-${strengthColors[level]}" style="width: ${(level + 1) * 20}%"></div>
            </div>
            <small class="text-${strengthColors[level]}">Kekuatan: ${strengthLevels[level]}</small>
            ${feedback.length > 0 ? `<small class="text-muted d-block">Kurang: ${feedback.join(', ')}</small>` : ''}
        `;
    }
    
    validatePasswordConfirmation() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const feedback = document.getElementById('password-feedback');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = 'password-feedback';
            feedback.className = 'form-text small mt-1';
            document.getElementById('confirm-password').parentNode.appendChild(feedback);
        }
        
        if (confirmPassword === '') {
            feedback.textContent = '';
            feedback.className = 'form-text small mt-1';
            document.getElementById('confirm-password').classList.remove('is-valid', 'is-invalid');
        } else if (password === confirmPassword) {
            feedback.textContent = '✓ Password cocok';
            feedback.className = 'form-text small mt-1 text-success';
            document.getElementById('confirm-password').classList.add('is-valid');
            document.getElementById('confirm-password').classList.remove('is-invalid');
        } else {
            feedback.textContent = '✗ Password tidak cocok';
            feedback.className = 'form-text small mt-1 text-danger';
            document.getElementById('confirm-password').classList.add('is-invalid');
            document.getElementById('confirm-password').classList.remove('is-valid');
        }
    }
    
    togglePasswordVisibility(e) {
        const button = e.currentTarget;
        const targetId = button.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    handleRegistrationTypeChange(e) {
        const type = e.target.value;
        
        // Hide all type-specific fields
        document.querySelectorAll('.type-specific-fields').forEach(field => {
            field.style.display = 'none';
        });
        
        // Remove active class from all cards
        document.querySelectorAll('.registration-type-card').forEach(card => {
            card.classList.remove('active', 'border-primary');
        });
        
        // Show relevant fields and highlight card
        if (type) {
            const fieldsElement = document.getElementById(`${type}-fields`);
            if (fieldsElement) {
                fieldsElement.style.display = 'block';
            }
            
            const cardElement = document.querySelector(`[data-type="${type}"]`);
            if (cardElement) {
                cardElement.classList.add('active', 'border-primary');
            }
        }
    }
    
    setupAddressCascade() {
        const provinceSelect = document.getElementById('province');
        const regencySelect = document.getElementById('regency');
        const districtSelect = document.getElementById('district');
        const villageSelect = document.getElementById('village');
        
        provinceSelect?.addEventListener('change', () => {
            this.loadRegencies(provinceSelect.value);
            regencySelect.disabled = true;
            districtSelect.disabled = true;
            villageSelect.disabled = true;
        });
        
        regencySelect?.addEventListener('change', () => {
            this.loadDistricts(regencySelect.value);
            districtSelect.disabled = true;
            villageSelect.disabled = true;
        });
        
        districtSelect?.addEventListener('change', () => {
            this.loadVillages(districtSelect.value);
            villageSelect.disabled = true;
        });
        
        villageSelect?.addEventListener('change', () => {
            this.updatePostalCode(villageSelect.value);
        });
        
        // Load provinces on init
        this.loadProvinces();
    }
    
    async loadProvinces() {
        const cacheKey = 'provinces';
        const cached = this.getFromCache(cacheKey);
        
        if (cached) {
            this.populateProvinceSelect(cached);
            return;
        }
        
        try {
            const response = await fetch('/api/address/provinces');
            const data = await response.json();
            this.setCache(cacheKey, data);
            this.populateProvinceSelect(data);
        } catch (error) {
            console.error('Error loading provinces:', error);
            this.showError('Gagal memuat data provinsi');
        }
    }
    
    async loadRegencies(provinceId) {
        if (!provinceId) return;
        
        const cacheKey = `regencies_${provinceId}`;
        const cached = this.getFromCache(cacheKey);
        
        if (cached) {
            this.populateRegencySelect(cached);
            return;
        }
        
        try {
            const response = await fetch(`/api/address/regencies/${provinceId}`);
            const data = await response.json();
            this.setCache(cacheKey, data);
            this.populateRegencySelect(data);
        } catch (error) {
            console.error('Error loading regencies:', error);
            this.showError('Gagal memuat data kabupaten/kota');
        }
    }
    
    async loadDistricts(regencyId) {
        if (!regencyId) return;
        
        const cacheKey = `districts_${regencyId}`;
        const cached = this.getFromCache(cacheKey);
        
        if (cached) {
            this.populateDistrictSelect(cached);
            return;
        }
        
        try {
            const response = await fetch(`/api/address/districts/${regencyId}`);
            const data = await response.json();
            this.setCache(cacheKey, data);
            this.populateDistrictSelect(data);
        } catch (error) {
            console.error('Error loading districts:', error);
            this.showError('Gagal memuat data kecamatan');
        }
    }
    
    async loadVillages(districtId) {
        if (!districtId) return;
        
        const cacheKey = `villages_${districtId}`;
        const cached = this.getFromCache(cacheKey);
        
        if (cached) {
            this.populateVillageSelect(cached);
            return;
        }
        
        try {
            const response = await fetch(`/api/address/villages/${districtId}`);
            const data = await response.json();
            this.setCache(cacheKey, data);
            this.populateVillageSelect(data);
        } catch (error) {
            console.error('Error loading villages:', error);
            this.showError('Gagal memuat data desa/kelurahan');
        }
    }
    
    populateProvinceSelect(provinces) {
        const select = document.getElementById('province');
        if (!select) return;
        
        select.innerHTML = '<option value="">Pilih Provinsi</option>';
        provinces.forEach(province => {
            select.innerHTML += `<option value="${province.id}">${province.name}</option>`;
        });
    }
    
    populateRegencySelect(regencies) {
        const select = document.getElementById('regency');
        if (!select) return;
        
        select.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
        regencies.forEach(regency => {
            select.innerHTML += `<option value="${regency.id}">${regency.name}</option>`;
        });
        select.disabled = false;
    }
    
    populateDistrictSelect(districts) {
        const select = document.getElementById('district');
        if (!select) return;
        
        select.innerHTML = '<option value="">Pilih Kecamatan</option>';
        districts.forEach(district => {
            select.innerHTML += `<option value="${district.id}">${district.name}</option>`;
        });
        select.disabled = false;
    }
    
    populateVillageSelect(villages) {
        const select = document.getElementById('village');
        if (!select) return;
        
        select.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
        villages.forEach(village => {
            select.innerHTML += `<option value="${village.id}" data-postal="${village.postal_code || ''}">${village.name}</option>`;
        });
        select.disabled = false;
    }
    
    updatePostalCode(villageId) {
        const select = document.getElementById('village');
        const display = document.getElementById('postal-code-display');
        const option = select.querySelector(`option[value="${villageId}"]`);
        
        if (display && option) {
            const postalCode = option.getAttribute('data-postal');
            display.textContent = postalCode || '-';
        }
    }
    
    async checkMemberCode(memberCode) {
        if (!memberCode || memberCode.length < 3) return;
        
        const input = document.getElementById('member-code');
        const feedback = document.getElementById('member-code-feedback');
        
        if (!feedback) {
            const feedbackDiv = document.createElement('div');
            feedbackDiv.id = 'member-code-feedback';
            feedbackDiv.className = 'form-text small mt-1';
            input.parentNode.appendChild(feedbackDiv);
        }
        
        feedback.textContent = 'Memeriksa ketersediaan...';
        feedback.className = 'form-text small mt-1 text-muted';
        
        try {
            const response = await fetch(`index.php?page=register&action=check_member_code&member_code=${encodeURIComponent(memberCode)}`);
            const data = await response.json();
            
            if (data.available) {
                feedback.textContent = '✓ Kode tersedia';
                feedback.className = 'form-text small mt-1 text-success';
                input.classList.add('is-valid');
                input.classList.remove('is-invalid');
            } else {
                feedback.textContent = '✗ Kode sudah digunakan';
                feedback.className = 'form-text small mt-1 text-danger';
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
            }
        } catch (error) {
            feedback.textContent = '⚠ Gagal memeriksa ketersediaan';
            feedback.className = 'form-text small mt-1 text-warning';
            input.classList.remove('is-valid', 'is-invalid');
        }
    }
    
    async checkCompanyCode(companyCode) {
        if (!companyCode || companyCode.length < 3) {
            this.hideCompanyDetails();
            return;
        }
        
        const input = document.getElementById('company-code');
        const feedback = document.getElementById('company-code-feedback');
        const positionSelect = document.getElementById('join-position');
        
        if (!feedback) {
            const feedbackDiv = document.createElement('div');
            feedbackDiv.id = 'company-code-feedback';
            feedbackDiv.className = 'form-text small mt-1';
            input.parentNode.appendChild(feedbackDiv);
        }
        
        feedback.textContent = 'Memeriksa kode perusahaan...';
        feedback.className = 'form-text small mt-1 text-muted';
        positionSelect.disabled = true;
        
        try {
            const response = await fetch(`index.php?page=register&action=check_company_code&company_code=${encodeURIComponent(companyCode)}`);
            const data = await response.json();
            
            if (data.found) {
                feedback.textContent = '✓ Perusahaan ditemukan';
                feedback.className = 'form-text small mt-1 text-success';
                input.classList.add('is-valid');
                input.classList.remove('is-invalid');
                
                this.showCompanyDetails(data.company);
                positionSelect.disabled = false;
            } else {
                feedback.textContent = '✗ Kode perusahaan tidak ditemukan';
                feedback.className = 'form-text small mt-1 text-danger';
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                
                this.hideCompanyDetails();
                positionSelect.disabled = true;
            }
        } catch (error) {
            feedback.textContent = '⚠ Gagal memeriksa kode perusahaan';
            feedback.className = 'form-text small mt-1 text-warning';
            input.classList.remove('is-valid', 'is-invalid');
            this.hideCompanyDetails();
            positionSelect.disabled = true;
        }
    }
    
    showCompanyDetails(company) {
        const detailsDiv = document.getElementById('company-details');
        const companyNameDisplay = document.getElementById('company-name-display');
        const companyTypeDisplay = document.getElementById('company-type-display');
        const branchNameDisplay = document.getElementById('branch-name-display');
        const branchLocationDisplay = document.getElementById('branch-location-display');
        
        // Update company details
        companyNameDisplay.textContent = company.company_name || '-';
        companyTypeDisplay.textContent = this.getCompanyTypeLabel(company.company_type) || '-';
        branchNameDisplay.textContent = company.branch_name || '-';
        
        // Build location string
        const locationParts = [];
        if (company.village_name) locationParts.push(company.village_name);
        if (company.district_name) locationParts.push(company.district_name);
        if (company.regency_name) locationParts.push(company.regency_name);
        if (company.province_name) locationParts.push(company.province_name);
        
        branchLocationDisplay.textContent = locationParts.join(', ') || '-';
        
        // Show details section
        detailsDiv.style.display = 'block';
    }
    
    hideCompanyDetails() {
        const detailsDiv = document.getElementById('company-details');
        const positionSelect = document.getElementById('join-position');
        
        detailsDiv.style.display = 'none';
        positionSelect.disabled = true;
        positionSelect.value = '';
    }
    
    getCompanyTypeLabel(type) {
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
    
    // Step Navigation
    nextStep(currentStep) {
        if (!this.validateStep(currentStep)) {
            return;
        }
        
        this.saveStepData(currentStep);
        this.hideStep(currentStep);
        this.currentStep = currentStep + 1;
        this.showStep(this.currentStep);
        this.updateProgress();
        
        if (this.currentStep === 3) {
            this.populateSummary();
        }
    }
    
    previousStep(currentStep) {
        this.hideStep(currentStep);
        this.currentStep = currentStep - 1;
        this.showStep(this.currentStep);
        this.updateProgress();
    }
    
    validateStep(step) {
        let isValid = true;
        
        switch(step) {
            case 1:
                // Validate basic information fields
                const basicFields = ['member_code', 'member_name', 'email', 'phone', 'password', 'confirm_password'];
                basicFields.forEach(field => {
                    if (!this.validateField(field)) {
                        isValid = false;
                    }
                });
                break;
                
            case 2:
                // Validate registration type and specific fields
                const registrationType = document.querySelector('input[name="registration_type"]:checked');
                if (!registrationType) {
                    this.showError('Pilih tipe pendaftaran');
                    isValid = false;
                } else {
                    // Validate type-specific fields
                    const type = registrationType.value;
                    if (type === 'individual') {
                        if (!this.validateField('business-name') || !this.validateField('business-type')) {
                            isValid = false;
                        }
                    } else if (type === 'join_company') {
                        if (!this.validateField('company-code') || !document.getElementById('join-position').value) {
                            isValid = false;
                        }
                    } else if (type === 'create_company') {
                        if (!this.validateField('company-name') || !this.validateField('company-type')) {
                            isValid = false;
                        }
                    }
                    
                    // Validate address fields
                    const addressFields = ['province', 'regency', 'district', 'village', 'address-detail'];
                    addressFields.forEach(field => {
                        if (!this.validateField(field)) {
                            isValid = false;
                        }
                    });
                }
                break;
        }
        
        if (!isValid) {
            this.showError('Perbaiki error yang ditandai sebelum melanjutkan');
        }
        
        return isValid;
    }
    
    saveStepData(step) {
        const formData = new FormData(document.getElementById('auth-register-form'));
        
        switch(step) {
            case 1:
                this.formData.basic = {
                    member_code: formData.get('member_code'),
                    member_name: formData.get('member_name'),
                    email: formData.get('email'),
                    phone: formData.get('phone'),
                    password: formData.get('password')
                };
                break;
                
            case 2:
                this.formData.registration = {
                    registration_type: formData.get('registration_type'),
                    business_name: formData.get('business_name'),
                    business_type: formData.get('business_type'),
                    company_code: formData.get('company_code'),
                    company_name: formData.get('company_name'),
                    company_type: formData.get('company_type'),
                    position: formData.get('position'),
                    address_detail: formData.get('address_detail'),
                    province_id: formData.get('province_id'),
                    regency_id: formData.get('regency_id'),
                    district_id: formData.get('district_id'),
                    village_id: formData.get('village_id')
                };
                break;
        }
        
        // Cache form data
        this.setCache('registration_form_data', this.formData);
    }
    
    loadCachedData() {
        const cached = this.getFromCache('registration_form_data');
        if (cached) {
            this.formData = cached;
            // Restore form fields if needed
        }
    }
    
    populateSummary() {
        // Basic information summary
        document.getElementById('summary-member-code').textContent = this.formData.basic?.member_code || '-';
        document.getElementById('summary-member-name').textContent = this.formData.basic?.member_name || '-';
        document.getElementById('summary-email').textContent = this.formData.basic?.email || '-';
        document.getElementById('summary-phone').textContent = this.formData.basic?.phone || '-';
        
        // Registration details summary
        const registrationDetails = document.getElementById('summary-registration-details');
        const regType = this.formData.registration?.registration_type;
        
        let detailsHTML = '<table class="table table-sm table-borderless">';
        
        switch(regType) {
            case 'individual':
                detailsHTML += `
                    <tr><td><strong>Tipe Pendaftaran:</strong></td><td>🏪 Usaha Perseorangan</td></tr>
                    <tr><td><strong>Nama Usaha:</strong></td><td>${this.formData.registration?.business_name || '-'}</td></tr>
                    <tr><td><strong>Tipe Usaha:</strong></td><td>${this.formData.registration?.business_type || '-'}</td></tr>
                    <tr><td><strong>Posisi:</strong></td><td>👑 Owner/Pemilik Usaha</td></tr>
                `;
                break;
                
            case 'join_company':
                detailsHTML += `
                    <tr><td><strong>Tipe Pendaftaran:</strong></td><td>👥 Bergabung dengan Perusahaan</td></tr>
                    <tr><td><strong>Kode Perusahaan:</strong></td><td>${this.formData.registration?.company_code || '-'}</td></tr>
                    <tr><td><strong>Posisi:</strong></td><td>${this.formData.registration?.position || '-'}</td></tr>
                `;
                break;
                
            case 'create_company':
                detailsHTML += `
                    <tr><td><strong>Tipe Pendaftaran:</strong></td><td>🏢 Buat Perusahaan Baru</td></tr>
                    <tr><td><strong>Nama Perusahaan:</strong></td><td>${this.formData.registration?.company_name || '-'}</td></tr>
                    <tr><td><strong>Tipe Perusahaan:</strong></td><td>${this.formData.registration?.company_type || '-'}</td></tr>
                    <tr><td><strong>Posisi:</strong></td><td>👑 Owner/Pemilik Perusahaan</td></tr>
                `;
                break;
        }
        
        detailsHTML += '</table>';
        registrationDetails.innerHTML = detailsHTML;
        
        // Address summary
        const addressSummary = this.buildAddressSummary();
        document.getElementById('summary-address').textContent = addressSummary;
    }
    
    buildAddressSummary() {
        const address = this.formData.registration;
        if (!address) return '-';
        
        const parts = [
            address.address_detail,
            address.village_id ? this.getSelectedText('village') : '',
            address.district_id ? this.getSelectedText('district') : '',
            address.regency_id ? this.getSelectedText('regency') : '',
            address.province_id ? this.getSelectedText('province') : ''
        ].filter(part => part && part.trim());
        
        return parts.join(', ') || '-';
    }
    
    getSelectedText(selectId) {
        const select = document.getElementById(selectId);
        return select ? select.options[select.selectedIndex]?.text : '';
    }
    
    showStep(step) {
        const stepElement = document.getElementById(`step-${step}`);
        if (stepElement) {
            stepElement.style.display = 'block';
            stepElement.classList.add('fade-in');
        }
    }
    
    hideStep(step) {
        const stepElement = document.getElementById(`step-${step}`);
        if (stepElement) {
            stepElement.style.display = 'none';
        }
    }
    
    updateProgress() {
        document.querySelectorAll('.progress-step').forEach((step, index) => {
            const stepNumber = index + 1;
            if (stepNumber <= this.currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    }
    
    initializeProgress() {
        this.updateProgress();
    }
    
    // Form submission
    async handleFormSubmit(e) {
        e.preventDefault();
        
        // Final validation
        if (!this.validateStep(3)) {
            this.showError('Perbaiki error yang ditandai sebelum submit');
            return;
        }
        
        // Check terms acceptance
        const termsAccepted = document.getElementById('terms-accepted')?.checked;
        if (!termsAccepted) {
            this.showError('Anda harus menyetujui syarat dan ketentuan');
            return;
        }
        
        // Check CAPTCHA
        const captchaResponse = document.querySelector('[name="h-captcha-response"]')?.value;
        if (!captchaResponse) {
            this.showError('Selesaikan verifikasi CAPTCHA');
            return;
        }
        
        // Show loading state
        this.setSubmitButtonLoading(true);
        
        try {
            const formData = new FormData(e.target);
            
            const response = await fetch('index.php?page=register', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.text();
            
            // Check if response contains success message
            if (result.includes('alert-success') || result.includes('berhasil')) {
                this.showSuccess('Registrasi berhasil! Mengalihkan ke halaman login...');
                
                // Clear cache
                this.clearCache();
                
                // Redirect after delay
                setTimeout(() => {
                    window.location.href = 'index.php?page=login';
                }, 2000);
            } else {
                // Extract error message
                const errorMatch = result.match(/<div class="alert alert-danger">(.*?)<\/div>/);
                const errorMessage = errorMatch ? errorMatch[1] : 'Registrasi gagal. Silakan coba lagi.';
                this.showError(errorMessage);
            }
        } catch (error) {
            console.error('Registration error:', error);
            this.showError('Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
        } finally {
            this.setSubmitButtonLoading(false);
        }
    }
    
    setSubmitButtonLoading(loading) {
        const submitBtn = document.getElementById('submit-btn');
        const progressSpan = submitBtn?.querySelector('.btn-progress');
        
        if (loading) {
            submitBtn?.setAttribute('disabled', 'true');
            progressSpan?.classList.remove('d-none');
        } else {
            submitBtn?.removeAttribute('disabled');
            progressSpan?.classList.add('d-none');
        }
    }
    
    // Utility functions
    debounce(func, wait) {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(func, wait);
    }
    
    setCache(key, data) {
        this.cache.set(key, {
            data: data,
            timestamp: Date.now()
        });
    }
    
    getFromCache(key) {
        const cached = this.cache.get(key);
        if (cached && (Date.now() - cached.timestamp) < this.cacheTimeout) {
            return cached.data;
        }
        return null;
    }
    
    clearCache() {
        this.cache.clear();
    }
    
    showError(message) {
        // Create or update error alert
        let errorAlert = document.querySelector('.alert-danger');
        if (!errorAlert) {
            errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show';
            errorAlert.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span class="error-message"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(errorAlert, document.querySelector('.registration-progress'));
        }
        
        errorAlert.querySelector('.error-message').textContent = message;
        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    showSuccess(message) {
        // Create success alert
        const successAlert = document.createElement('div');
        successAlert.className = 'alert alert-success alert-dismissible fade show';
        successAlert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.card-body').insertBefore(successAlert, document.querySelector('.registration-progress'));
        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    setupAutoSave() {
        // Auto-save form data every 30 seconds
        setInterval(() => {
            this.saveStepData(this.currentStep);
        }, 30000);
        
        // Save on page unload
        window.addEventListener('beforeunload', () => {
            this.saveStepData(this.currentStep);
        });
    }
    
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Enter key to proceed to next step
            if (e.key === 'Enter' && !e.shiftKey && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                if (this.currentStep < this.totalSteps) {
                    this.nextStep(this.currentStep);
                }
            }
            
            // Escape key to go to previous step
            if (e.key === 'Escape' && this.currentStep > 1) {
                this.previousStep(this.currentStep);
            }
        });
    }
    
    setupFormValidation() {
        // Add custom validation methods
        const form = document.getElementById('auth-register-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        }
    }
}

// Social login functions
function socialLogin(provider) {
    // Implement social login logic
    console.log(`Social login with ${provider}`);
    // This would redirect to OAuth provider
}

// Modal functions
function showTermsModal() {
    const modal = new bootstrap.Modal(document.getElementById('termsModal'));
    // Load terms content
    fetch('/terms-and-conditions')
        .then(response => response.text())
        .then(html => {
            document.querySelector('#termsModal .modal-body').innerHTML = html;
            modal.show();
        });
}

function showPrivacyModal() {
    const modal = new bootstrap.Modal(document.getElementById('privacyModal'));
    // Load privacy content
    fetch('/privacy-policy')
        .then(response => response.text())
        .then(html => {
            document.querySelector('#privacyModal .modal-body').innerHTML = html;
            modal.show();
        });
}

// Global functions for step navigation
function nextStep(step) {
    window.enhancedRegistration.nextStep(step);
}

function previousStep(step) {
    window.enhancedRegistration.previousStep(step);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.enhancedRegistration = new EnhancedRegistration();
});
