<?php include_once '../views/layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Add New Customer</h1>
            <p class="text-muted mb-0">Register a new customer in the system</p>
        </div>
        <div>
            <a href="index.php?page=customers" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Customers
            </a>
        </div>
    </div>

    <!-- Customer Creation Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Customer Information</h5>
        </div>
        <div class="card-body">
            <form id="customer-create-form" method="POST" action="index.php?page=customers&action=create">
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-12">
                        <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer-name-input" class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer-name-input" name="customer_name" 
                                   required maxlength="200" placeholder="Enter customer name">
                            <small class="form-text text-muted">Full name of the customer or business</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer-code-input" class="form-label">Customer Code</label>
                            <input type="text" class="form-control" id="customer-code-input" name="customer_code" 
                                   maxlength="50" placeholder="Auto-generated if empty">
                            <small class="form-text text-muted">Leave empty to auto-generate</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer-type-select" class="form-label">Customer Type</label>
                            <select class="form-select" id="customer-type-select" name="customer_type">
                                <option value="individual">Individual</option>
                                <option value="business">Business</option>
                                <option value="corporate">Corporate</option>
                                <option value="government">Government</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="business-name-input" class="form-label">Business Name</label>
                            <input type="text" class="form-control" id="business-name-input" name="business_name" 
                                   maxlength="200" placeholder="Business name (if applicable)">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone-input" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone-input" name="phone" 
                                   maxlength="50" placeholder="e.g., 08123456789">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email-input" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email-input" name="email" 
                                   maxlength="100" placeholder="customer@example.com">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="whatsapp-input" class="form-label">WhatsApp Number</label>
                            <input type="tel" class="form-control" id="whatsapp-input" name="whatsapp" 
                                   maxlength="50" placeholder="e.g., 08123456789">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tax-id-input" class="form-label">Tax ID (NPWP)</label>
                            <input type="text" class="form-control" id="tax-id-input" name="tax_id" 
                                   maxlength="50" placeholder="e.g., 12.345.678.9-012.345">
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="border-bottom pb-2 mb-3">Address Information</h6>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="address-detail-input" class="form-label">Address Details <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address-detail-input" name="address_detail" 
                                      rows="2" required placeholder="Street address, building number, etc."></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="customer-province-select" class="form-label">Province <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer-province-select" name="province_id" required>
                                <option value="">Select Province</option>
                                <?php foreach ($provinces as $province): ?>
                                <option value="<?= $province['id_province'] ?>"><?= htmlspecialchars($province['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="customer-regency-select" class="form-label">Regency/City <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer-regency-select" name="regency_id" required disabled>
                                <option value="">Select Regency</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="customer-district-select" class="form-label">District <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer-district-select" name="district_id" required disabled>
                                <option value="">Select District</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="customer-village-select" class="form-label">Village <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer-village-select" name="village_id" required disabled>
                                <option value="">Select Village</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="postal-code-input" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal-code-input" name="postal_code" 
                                   maxlength="10" placeholder="Auto-filled">
                            <small class="form-text text-muted">Will be auto-filled based on village</small>
                        </div>
                    </div>
                </div>

                <!-- Customer Classification -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="border-bottom pb-2 mb-3">Customer Classification</h6>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="customer-segment-select" class="form-label">Customer Segment</label>
                            <select class="form-select" id="customer-segment-select" name="customer_segment">
                                <option value="regular">Regular</option>
                                <option value="vip">VIP</option>
                                <option value="premium">Premium</option>
                                <option value="wholesale">Wholesale</option>
                                <option value="corporate">Corporate</option>
                            </select>
                            <small class="form-text text-muted">Customer business segment</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="customer-category-select" class="form-label">Customer Category</label>
                            <select class="form-select" id="customer-category-select" name="customer_category">
                                <option value="walk_in">Walk-in</option>
                                <option value="frequent">Frequent</option>
                                <option value="loyal">Loyal</option>
                                <option value="high_value">High Value</option>
                                <option value="at_risk">At Risk</option>
                            </select>
                            <small class="form-text text-muted">Customer purchase behavior</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="preferred-contact-select" class="form-label">Preferred Contact</label>
                            <select class="form-select" id="preferred-contact-select" name="preferred_contact">
                                <option value="phone">Phone</option>
                                <option value="email">Email</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="sms">SMS</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Credit Management -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="border-bottom pb-2 mb-3">Credit Management</h6>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="credit-limit-input" class="form-label">Credit Limit</label>
                            <input type="number" class="form-control" id="credit-limit-input" name="credit_limit" 
                                   min="0" step="0.01" value="0" placeholder="0.00">
                            <small class="form-text text-muted">Maximum credit amount (0 = no credit)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment-terms-select" class="form-label">Payment Terms</label>
                            <select class="form-select" id="payment-terms-select" name="payment_terms">
                                <option value="cash">Cash</option>
                                <option value="7_days">7 Days</option>
                                <option value="14_days">14 Days</option>
                                <option value="30_days">30 Days</option>
                                <option value="60_days">60 Days</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Communication Preferences -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="border-bottom pb-2 mb-3">Communication Preferences</h6>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="marketing-consent" name="marketing_consent">
                                <label class="form-check-label" for="marketing-consent">
                                    Marketing Communications
                                </label>
                                <small class="form-text text-muted d-block">Allow marketing emails and promotions</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notification-consent" name="notification_consent" checked>
                                <label class="form-check-label" for="notification-consent">
                                    Transaction Notifications
                                </label>
                                <small class="form-text text-muted d-block">Send transaction and payment reminders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="notes-textarea" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes-textarea" name="notes" rows="3" 
                                      placeholder="Any additional notes about this customer..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <a href="index.php?page=customers" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="CustomerModule.resetForm()">
                                    <i class="fas fa-redo me-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary" id="customer-create-btn">
                                    <i class="fas fa-save me-2"></i>Create Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../views/layouts/footer.php'; ?>

<!-- JavaScript -->
<script>
// Customer Create Module
var CustomerCreateModule = {
    init: function() {
        this.bindEvents();
        this.initializeAddressCascade();
        this.initializeAutoCode();
    },
    
    bindEvents: function() {
        // Form submission
        $('#customer-create-form').on('submit', this.handleCreate);
        
        // Customer type change
        $('#customer-type-select').on('change', this.handleCustomerTypeChange);
        
        // Credit limit change
        $('#credit-limit-input').on('input', this.handleCreditLimitChange);
    },
    
    initializeAddressCascade: function() {
        // Province change
        $('#customer-province-select').on('change', function() {
            var provinceId = $(this).val();
            CustomerCreateModule.loadRegencies(provinceId);
        });
        
        // Regency change
        $('#customer-regency-select').on('change', function() {
            var regencyId = $(this).val();
            CustomerCreateModule.loadDistricts(regencyId);
        });
        
        // District change
        $('#customer-district-select').on('change', function() {
            var districtId = $(this).val();
            CustomerCreateModule.loadVillages(districtId);
        });
        
        // Village change
        $('#customer-village-select').on('change', function() {
            var villageId = $(this).val();
            CustomerCreateModule.updatePostalCode(villageId);
        });
    },
    
    initializeAutoCode: function() {
        // Auto-generate customer code from name
        $('#customer-name-input').on('input', function() {
            var customerName = $(this).val();
            if (customerName && !$('#customer-code-input').val()) {
                var code = CustomerCreateModule.generateCustomerCode(customerName);
                $('#customer-code-input').val(code);
            }
        });
    },
    
    handleCreate: function(e) {
        e.preventDefault();
        
        var submitBtn = $('#customer-create-btn');
        var originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creating...');
        
        // Submit form via AJAX
        var formData = new FormData(e.target);
        
        fetch('index.php?page=customers&action=create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Toast.success(data.message);
                setTimeout(function() {
                    window.location.href = 'index.php?page=customers';
                }, 1500);
            } else {
                Toast.error(data.message);
                submitBtn.prop('disabled', false).html(originalText);
            }
        })
        .catch(error => {
            Toast.error('An error occurred while creating customer');
            submitBtn.prop('disabled', false).html(originalText);
        });
    },
    
    handleCustomerTypeChange: function() {
        var customerType = $(this).val();
        var businessNameField = $('#business-name-input');
        var taxIdField = $('#tax-id-input');
        
        if (customerType === 'individual') {
            businessNameField.prop('disabled', true).val('');
            taxIdField.prop('disabled', true).val('');
        } else {
            businessNameField.prop('disabled', false);
            taxIdField.prop('disabled', false);
        }
    },
    
    handleCreditLimitChange: function() {
        var creditLimit = parseFloat($(this).val()) || 0;
        var paymentTermsField = $('#payment-terms-select');
        
        if (creditLimit > 0) {
            paymentTermsField.prop('disabled', false);
        } else {
            paymentTermsField.prop('disabled', true).val('cash');
        }
    },
    
    loadRegencies: function(provinceId) {
        if (!provinceId) {
            $('#customer-regency-select').html('<option value="">Select Regency</option>').prop('disabled', true);
            $('#customer-district-select').html('<option value="">Select District</option>').prop('disabled', true);
            $('#customer-village-select').html('<option value="">Select Village</option>').prop('disabled', true);
            return;
        }
        
        fetch(`api/get_regencies.php?province_id=${provinceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                var options = '<option value="">Select Regency</option>';
                data.regencies.forEach(function(regency) {
                    options += `<option value="${regency.id_regency}">${regency.name}</option>`;
                });
                $('#customer-regency-select').html(options).prop('disabled', false);
            }
        })
        .catch(error => {
            console.error('Failed to load regencies:', error);
        });
    },
    
    loadDistricts: function(regencyId) {
        if (!regencyId) {
            $('#customer-district-select').html('<option value="">Select District</option>').prop('disabled', true);
            $('#customer-village-select').html('<option value="">Select Village</option>').prop('disabled', true);
            return;
        }
        
        fetch(`api/get_districts.php?regency_id=${regencyId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                var options = '<option value="">Select District</option>';
                data.districts.forEach(function(district) {
                    options += `<option value="${district.id_district}">${district.name}</option>`;
                });
                $('#customer-district-select').html(options).prop('disabled', false);
            }
        })
        .catch(error => {
            console.error('Failed to load districts:', error);
        });
    },
    
    loadVillages: function(districtId) {
        if (!districtId) {
            $('#customer-village-select').html('<option value="">Select Village</option>').prop('disabled', true);
            return;
        }
        
        fetch(`api/get_villages.php?district_id=${districtId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                var options = '<option value="">Select Village</option>';
                data.villages.forEach(function(village) {
                    options += `<option value="${village.id_village}" data-postal-code="${village.postal_code || ''}">${village.name}</option>`;
                });
                $('#customer-village-select').html(options).prop('disabled', false);
            }
        })
        .catch(error => {
            console.error('Failed to load villages:', error);
        });
    },
    
    updatePostalCode: function(villageId) {
        // Clear postal code first
        $('#postal-code-input').val('');
        
        if (!villageId) {
            return;
        }
        
        var selectedOption = $('#customer-village-select option:selected');
        var postalCode = selectedOption.data('postal-code');
        
        if (postalCode) {
            $('#postal-code-input').val(postalCode);
        }
    },
    
    generateCustomerCode: function(customerName) {
        // Generate code from customer name
        var cleanName = customerName.toUpperCase().replace(/[^A-Z0-9]/g, '');
        var prefix = cleanName.substring(0, 3);
        var randomNum = Math.floor(Math.random() * 1000);
        return prefix + randomNum.toString().padStart(3, '0');
    },
    
    resetForm: function() {
        if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
            $('#customer-create-form')[0].reset();
            $('#customer-regency-select, #customer-district-select, #customer-village-select').prop('disabled', true);
            $('#postal-code-input').val('');
            Toast.info('Form has been reset');
        }
    }
};

// Initialize module when DOM is ready
$(document).ready(function() {
    CustomerCreateModule.init();
});
</script>
