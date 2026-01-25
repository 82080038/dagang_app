<?php include_once '../views/layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Customer</h1>
            <p class="text-muted mb-0">Update customer information</p>
        </div>
        <div>
            <a href="index.php?page=customers" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Customers
            </a>
        </div>
    </div>

    <!-- Customer Edit Form -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Edit Customer Information</h5>
            <div>
                <span class="badge bg-primary me-2"><?= htmlspecialchars($customer['customer_code']) ?></span>
                <?php if ($customer['is_blacklisted']): ?>
                <span class="badge bg-danger">Blacklisted</span>
                <?php elseif (!$customer['is_active']): ?>
                <span class="badge bg-secondary">Inactive</span>
                <?php else: ?>
                <span class="badge bg-success">Active</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <form id="customer-edit-form" method="POST" action="index.php?page=customers&action=edit&id=<?= $customer['id_customer'] ?>">
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-12">
                        <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer-name-input" class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer-name-input" name="customer_name" 
                                   required maxlength="200" value="<?= htmlspecialchars($customer['customer_name']) ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer-code-input" class="form-label">Customer Code</label>
                            <input type="text" class="form-control" id="customer-code-input" name="customer_code" 
                                   maxlength="50" value="<?= htmlspecialchars($customer['customer_code']) ?>">
                            <small class="form-text text-muted">Changing code may affect records</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer-type-select" class="form-label">Customer Type</label>
                            <select class="form-select" id="customer-type-select" name="customer_type">
                                <option value="individual" <?= $customer['customer_type'] === 'individual' ? 'selected' : '' ?>>Individual</option>
                                <option value="business" <?= $customer['customer_type'] === 'business' ? 'selected' : '' ?>>Business</option>
                                <option value="corporate" <?= $customer['customer_type'] === 'corporate' ? 'selected' : '' ?>>Corporate</option>
                                <option value="government" <?= $customer['customer_type'] === 'government' ? 'selected' : '' ?>>Government</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="business-name-input" class="form-label">Business Name</label>
                            <input type="text" class="form-control" id="business-name-input" name="business_name" 
                                   maxlength="200" value="<?= htmlspecialchars($customer['business_name'] ?? '') ?>"
                                   <?= $customer['customer_type'] === 'individual' ? 'disabled' : '' ?>>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone-input" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone-input" name="phone" 
                                   maxlength="50" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email-input" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email-input" name="email" 
                                   maxlength="100" value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="whatsapp-input" class="form-label">WhatsApp Number</label>
                            <input type="tel" class="form-control" id="whatsapp-input" name="whatsapp" 
                                   maxlength="50" value="<?= htmlspecialchars($customer['whatsapp'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tax-id-input" class="form-label">Tax ID (NPWP)</label>
                            <input type="text" class="form-control" id="tax-id-input" name="tax_id" 
                                   maxlength="50" value="<?= htmlspecialchars($customer['tax_id'] ?? '') ?>"
                                   <?= $customer['customer_type'] === 'individual' ? 'disabled' : '' ?>>
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
                                      rows="2" required><?= htmlspecialchars($customer['address_detail'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="customer-province-select" class="form-label">Province <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer-province-select" name="province_id" required>
                                <option value="">Select Province</option>
                                <?php foreach ($provinces as $province): ?>
                                <option value="<?= $province['id_province'] ?>" 
                                        <?= ($customer['province_id'] ?? '') == $province['id_province'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($province['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="customer-regency-select" class="form-label">Regency/City <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer-regency-select" name="regency_id" required>
                                <option value="">Select Regency</option>
                                <!-- Will be populated by JavaScript -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="customer-district-select" class="form-label">District <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer-district-select" name="district_id" required>
                                <option value="">Select District</option>
                                <!-- Will be populated by JavaScript -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="customer-village-select" class="form-label">Village <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer-village-select" name="village_id" required>
                                <option value="">Select Village</option>
                                <!-- Will be populated by JavaScript -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="postal-code-input" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal-code-input" name="postal_code" 
                                   maxlength="10" value="<?= htmlspecialchars($customer['postal_code'] ?? '') ?>">
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
                                <option value="regular" <?= $customer['customer_segment'] === 'regular' ? 'selected' : '' ?>>Regular</option>
                                <option value="vip" <?= $customer['customer_segment'] === 'vip' ? 'selected' : '' ?>>VIP</option>
                                <option value="premium" <?= $customer['customer_segment'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                                <option value="wholesale" <?= $customer['customer_segment'] === 'wholesale' ? 'selected' : '' ?>>Wholesale</option>
                                <option value="corporate" <?= $customer['customer_segment'] === 'corporate' ? 'selected' : '' ?>>Corporate</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="customer-category-select" class="form-label">Customer Category</label>
                            <select class="form-select" id="customer-category-select" name="customer_category">
                                <option value="walk_in" <?= $customer['customer_category'] === 'walk_in' ? 'selected' : '' ?>>Walk-in</option>
                                <option value="frequent" <?= $customer['customer_category'] === 'frequent' ? 'selected' : '' ?>>Frequent</option>
                                <option value="loyal" <?= $customer['customer_category'] === 'loyal' ? 'selected' : '' ?>>Loyal</option>
                                <option value="high_value" <?= $customer['customer_category'] === 'high_value' ? 'selected' : '' ?>>High Value</option>
                                <option value="at_risk" <?= $customer['customer_category'] === 'at_risk' ? 'selected' : '' ?>>At Risk</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="preferred-contact-select" class="form-label">Preferred Contact</label>
                            <select class="form-select" id="preferred-contact-select" name="preferred_contact">
                                <option value="phone" <?= $customer['preferred_contact'] === 'phone' ? 'selected' : '' ?>>Phone</option>
                                <option value="email" <?= $customer['preferred_contact'] === 'email' ? 'selected' : '' ?>>Email</option>
                                <option value="whatsapp" <?= $customer['preferred_contact'] === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                                <option value="sms" <?= $customer['preferred_contact'] === 'sms' ? 'selected' : '' ?>>SMS</option>
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
                                   min="0" step="0.01" value="<?= number_format($customer['credit_limit'] ?? 0, 2, '.', '') ?>">
                            <small class="form-text text-muted">Maximum credit amount (0 = no credit)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment-terms-select" class="form-label">Payment Terms</label>
                            <select class="form-select" id="payment-terms-select" name="payment_terms">
                                <option value="cash" <?= $customer['payment_terms'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="7_days" <?= $customer['payment_terms'] === '7_days' ? 'selected' : '' ?>>7 Days</option>
                                <option value="14_days" <?= $customer['payment_terms'] === '14_days' ? 'selected' : '' ?>>14 Days</option>
                                <option value="30_days" <?= $customer['payment_terms'] === '30_days' ? 'selected' : '' ?>>30 Days</option>
                                <option value="60_days" <?= $customer['payment_terms'] === '60_days' ? 'selected' : '' ?>>60 Days</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Current Debt</label>
                            <div class="form-control-plaintext">
                                <?= number_format($customer['current_debt'] ?? 0, 0, ',', '.') ?>
                            </div>
                            <small class="form-text text-muted">Cannot be edited here</small>
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
                                <input class="form-check-input" type="checkbox" id="marketing-consent" name="marketing_consent" 
                                       <?= $customer['marketing_consent'] ? 'checked' : '' ?>>
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
                                <input class="form-check-input" type="checkbox" id="notification-consent" name="notification_consent" 
                                       <?= $customer['notification_consent'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notification-consent">
                                    Transaction Notifications
                                </label>
                                <small class="form-text text-muted d-block">Send transaction and payment reminders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Statistics (Read-only) -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="border-bottom pb-2 mb-3">Customer Statistics</h6>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Total Purchases</label>
                            <div class="form-control-plaintext">
                                <?= number_format($customer['total_purchases'] ?? 0, 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Total Transactions</label>
                            <div class="form-control-plaintext">
                                <?= $customer['total_transactions'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Loyalty Points</label>
                            <div class="form-control-plaintext">
                                <?= number_format($customer['loyalty_points'] ?? 0) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Loyalty Tier</label>
                            <div class="form-control-plaintext">
                                <span class="loyalty-badge loyalty-<?= $customer['loyalty_tier'] ?? 'bronze' ?>">
                                    <?= ucfirst($customer['loyalty_tier'] ?? 'bronze') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="notes-textarea" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes-textarea" name="notes" rows="3"><?= htmlspecialchars($customer['notes'] ?? '') ?></textarea>
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
                                <button type="submit" class="btn btn-primary" id="customer-update-btn">
                                    <i class="fas fa-save me-2"></i>Update Customer
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

<!-- Custom CSS for Loyalty Badges -->
<style>
.loyalty-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}
.loyalty-bronze { background-color: #CD7F32; color: white; }
.loyalty-silver { background-color: #C0C0C0; color: black; }
.loyalty-gold { background-color: #FFD700; color: black; }
.loyalty-platinum { background-color: #E5E4E2; color: black; }
.loyalty-diamond { background-color: #B9F2FF; color: black; }
</style>

<!-- JavaScript -->
<script>
// Customer Edit Module
var CustomerEditModule = {
    init: function() {
        this.bindEvents();
        this.initializeAddressCascade();
        this.loadAddressData();
        this.handleCustomerTypeChange();
        this.handleCreditLimitChange();
    },
    
    bindEvents: function() {
        // Form submission
        $('#customer-edit-form').on('submit', this.handleUpdate);
        
        // Customer type change
        $('#customer-type-select').on('change', this.handleCustomerTypeChange);
        
        // Credit limit change
        $('#credit-limit-input').on('input', this.handleCreditLimitChange);
    },
    
    initializeAddressCascade: function() {
        // Province change
        $('#customer-province-select').on('change', function() {
            var provinceId = $(this).val();
            CustomerEditModule.loadRegencies(provinceId);
        });
        
        // Regency change
        $('#customer-regency-select').on('change', function() {
            var regencyId = $(this).val();
            CustomerEditModule.loadDistricts(regencyId);
        });
        
        // District change
        $('#customer-district-select').on('change', function() {
            var districtId = $(this).val();
            CustomerEditModule.loadVillages(districtId);
        });
        
        // Village change
        $('#customer-village-select').on('change', function() {
            var villageId = $(this).val();
            CustomerEditModule.updatePostalCode(villageId);
        });
    },
    
    loadAddressData: function() {
        var provinceId = <?= $customer['province_id'] ?? 'null' ?>;
        var regencyId = <?= $customer['regency_id'] ?? 'null' ?>;
        var districtId = <?= $customer['district_id'] ?? 'null' ?>;
        var villageId = <?= $customer['village_id'] ?? 'null' ?>;
        
        if (provinceId) {
            this.loadRegencies(provinceId, regencyId);
        }
        
        if (regencyId) {
            this.loadDistricts(regencyId, districtId);
        }
        
        if (districtId) {
            this.loadVillages(districtId, villageId);
        }
    },
    
    handleUpdate: function(e) {
        e.preventDefault();
        
        var submitBtn = $('#customer-update-btn');
        var originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
        
        // Submit form via AJAX
        var formData = new FormData(e.target);
        
        fetch('index.php?page=customers&action=edit&id=<?= $customer['id_customer'] ?>', {
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
            Toast.error('An error occurred while updating customer');
            submitBtn.prop('disabled', false).html(originalText);
        });
    },
    
    handleCustomerTypeChange: function() {
        var customerType = $('#customer-type-select').val();
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
    
    loadRegencies: function(provinceId, selectedRegencyId) {
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
                    var selected = regency.id_regency == selectedRegencyId ? 'selected' : '';
                    options += `<option value="${regency.id_regency}" ${selected}>${regency.name}</option>`;
                });
                $('#customer-regency-select').html(options).prop('disabled', false);
            }
        })
        .catch(error => {
            console.error('Failed to load regencies:', error);
        });
    },
    
    loadDistricts: function(regencyId, selectedDistrictId) {
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
                    var selected = district.id_district == selectedDistrictId ? 'selected' : '';
                    options += `<option value="${district.id_district}" ${selected}>${district.name}</option>`;
                });
                $('#customer-district-select').html(options).prop('disabled', false);
            }
        })
        .catch(error => {
            console.error('Failed to load districts:', error);
        });
    },
    
    loadVillages: function(districtId, selectedVillageId) {
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
                    var selected = village.id_village == selectedVillageId ? 'selected' : '';
                    var postalCode = village.postal_code || '';
                    options += `<option value="${village.id_village}" data-postal-code="${postalCode}" ${selected}>${village.name}</option>`;
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
    }
};

// Initialize module when DOM is ready
$(document).ready(function() {
    CustomerEditModule.init();
});
</script>
