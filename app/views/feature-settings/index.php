<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-toggle-on me-2"></i>Pengaturan Fitur</h1>
                <button class="btn btn-primary" onclick="saveFeatureSettings()">
                    <i class="fas fa-save me-2"></i>Simpan Pengaturan
                </button>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Info:</strong> Aktifkan atau non-aktifkan fitur sesuai kebutuhan bisnis Anda. 
                Fitur yang dinon-aktifkan tidak akan muncul di menu dan tidak dapat diakses oleh pengguna.
            </div>
            
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['flash_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
            
            <form id="feature-settings-form">
                <?php foreach ($enabledFeatures as $categoryKey => $category): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-layer-group me-2"></i><?= $category['name'] ?>
                                </h5>
                            <small class="text-white-50"><?= $category['description'] ?></small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($category['features'] as $featureKey => $feature): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border <?= $feature['enabled'] ? 'border-success' : 'border-secondary' ?>">
                                            <div class="card-body">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input feature-toggle" 
                                                           type="checkbox" 
                                                           id="feature-<?= $featureKey ?>" 
                                                           name="features[<?= $featureKey ?>][enabled]" 
                                                           value="true" 
                                                           <?= $feature['enabled'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="feature-<?= $featureKey ?>">
                                                        <strong><?= $feature['name'] ?></strong>
                                                    </label>
                                                </div>
                                                
                                                <p class="text-muted small mb-2"><?= $feature['description'] ?></p>
                                                
                                                <div class="feature-status">
                                                    <?php if ($feature['enabled']): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Aktif
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-times me-1"></i>Non-aktif
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <span class="badge bg-info ms-1">
                                                        <?= ucfirst($feature['category']) ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Feature Settings (can be expanded later) -->
                                                <div class="feature-settings mt-2" style="display: none;">
                                                    <small class="text-muted">Pengaturan lanjutan akan tersedia segera</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle feature toggle changes
    $('.feature-toggle').on('change', function() {
        var featureKey = $(this).attr('id').replace('feature-', '');
        var isEnabled = $(this).is(':checked');
        var $card = $(this).closest('.card');
        var $statusBadge = $card.find('.feature-status .badge:first');
        
        if (isEnabled) {
            $card.removeClass('border-secondary').addClass('border-success');
            $statusBadge.removeClass('bg-secondary').addClass('bg-success')
                .html('<i class="fas fa-check me-1"></i>Aktif');
        } else {
            $card.removeClass('border-success').addClass('border-secondary');
            $statusBadge.removeClass('bg-success').addClass('bg-secondary')
                .html('<i class="fas fa-times me-1"></i>Non-aktif');
        }
    });
});

function saveFeatureSettings() {
    var formData = $('#feature-settings-form').serialize();
    
    $.ajax({
        url: 'index.php?page=feature-settings&action=update',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('button[onclick="saveFeatureSettings()"]').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        },
        success: function(response) {
            if (response.status === 'success') {
                // Show success message
                if ($('.alert-success').length === 0) {
                    $('.container-fluid').prepend(
                        '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-check-circle me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>'
                    );
                }
                
                // Scroll to top
                window.scrollTo(0, 0);
            } else {
                // Show error message
                if ($('.alert-danger').length === 0) {
                    $('.container-fluid').prepend(
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-exclamation-circle me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>'
                    );
                }
            }
        },
        error: function(xhr, status, error) {
            // Show error message
            if ($('.alert-danger').length === 0) {
                $('.container-fluid').prepend(
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat menyimpan pengaturan' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>'
                );
            }
        },
        complete: function() {
            $('button[onclick="saveFeatureSettings()"]').prop('disabled', false)
                .html('<i class="fas fa-save me-2"></i>Simpan Pengaturan');
        }
    });
}
</script>

<style>
.feature-toggle:checked {
    background-color: #198754;
    border-color: #198754;
}

.feature-toggle:checked:focus {
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}

.card.border-success {
    box-shadow: 0 0 10px rgba(25, 135, 84, 0.1);
}

.card.border-secondary {
    opacity: 0.8;
}

.feature-status {
    margin-top: 10px;
}

.feature-status .badge {
    font-size: 0.75rem;
}

.form-check-label {
    cursor: pointer;
}

.form-check-label strong {
    color: #333;
}

.card-body {
    padding: 1rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>
