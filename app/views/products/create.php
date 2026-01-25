<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tambah Produk Baru</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?page=products" class="btn btn-sm btn-outline-secondary" id="product-back-btn">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Produk</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <?php $msg = $_SESSION['flash_message']; ?>
                        <div class="alert alert-<?php echo $msg['type']; ?>">
                            <?php echo htmlspecialchars($msg['message']); ?>
                        </div>
                        <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <form id="product-create-form" action="index.php?page=products&action=create" method="POST" enctype="multipart/form-data">
                        <?php echo Csrf::input(); ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Info -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                                        <input type="text" id="product-code-input" name="product_code" class="form-control" required 
                                               value="<?php echo htmlspecialchars($generated_code); ?>" readonly>
                                        <div class="form-text">Kode otomatis dibuat sistem</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Barcode / SKU</label>
                                        <input type="text" id="product-barcode-input" name="barcode" class="form-control" placeholder="Scan barcode..."
                                               value="">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" id="product-name-input" name="product_name" class="form-control" required
                                           placeholder="Contoh: Indomie Goreng"
                                           value="">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                        <select id="product-category-select" name="category_id" class="form-select" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php if (isset($categories)): ?>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['id_category']; ?>">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <input type="text" id="product-unit-input" name="unit" class="form-control" required
                                               placeholder="pcs, kg, pack..."
                                               value="pcs">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea id="product-description-input" name="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Pricing & Settings -->
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">Harga & Stok</h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Harga Beli</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" id="product-purchase-price-input" name="purchase_price" class="form-control text-end" 
                                                       value="0" min="0" step="100">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" id="product-selling-price-input" name="selling_price" class="form-control text-end fw-bold" 
                                                       value="0" min="0" step="100" required>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Batas Stok Minimum</label>
                                            <input type="number" id="product-low-stock-input" name="low_stock_threshold" class="form-control" 
                                                   value="10" min="0">
                                            <div class="form-text">Notifikasi jika stok di bawah ini</div>
                                        </div>
                                        
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="product-is-active" checked>
                                            <label class="form-check-label" for="product-is-active">Produk Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php?page=products" class="btn btn-light me-md-2" id="product-cancel-btn">Batal</a>
                            <button type="submit" class="btn btn-primary px-4" id="product-save-btn">Simpan Produk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Product Management Module
var ProductModule = {
    init: function() {
        this.bindEvents();
        this.initializeForm();
    },
    
    bindEvents: function() {
        // Form submission
        $('#product-create-form').on('submit', this.handleCreate);
        
        // Barcode scanning
        $('#product-barcode-input').on('keypress', this.handleBarcodeScan);
        
        // Price calculation
        $('#product-purchase-price-input').on('input', this.calculateMargin);
        $('#product-selling-price-input').on('input', this.calculateMargin);
        
        // Category change
        $('#product-category-select').on('change', this.handleCategoryChange);
    },
    
    initializeForm: function() {
        // Auto-focus on product name
        $('#product-name-input').focus();
        
        // Add input masks for prices
        $('#product-purchase-price-input, #product-selling-price-input').on('input', function() {
            var value = parseFloat($(this).val()) || 0;
            $(this).val(value.toLocaleString('id-ID'));
        });
    },
    
    handleCreate: function(e) {
        e.preventDefault();
        var formData = new FormData(e.target);
        
        // Show loading state
        $('#product-save-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        
        fetch('index.php?page=products&action=create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                ProductModule.showNotification('Produk berhasil ditambahkan!', 'success');
                setTimeout(function() {
                    window.location.href = 'index.php?page=products';
                }, 1500);
            } else {
                ProductModule.showNotification('Error: ' + (data.message || 'Terjadi kesalahan'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ProductModule.showNotification('Terjadi kesalahan saat menyimpan produk', 'error');
        })
        .finally(function() {
            // Reset button state
            $('#product-save-btn').prop('disabled', false).html('Simpan Produk');
        });
    },
    
    handleBarcodeScan: function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            var barcode = $(e.target).val().trim();
            if (barcode) {
                ProductModule.lookupBarcode(barcode);
            }
        }
    },
    
    lookupBarcode: function(barcode) {
        // Simulate barcode lookup (replace with actual API call)
        console.log('Looking up barcode:', barcode);
        // In real implementation, call API to get product info
    },
    
    calculateMargin: function() {
        var purchasePrice = parseFloat($('#product-purchase-price-input').val().replace(/\./g, '')) || 0;
        var sellingPrice = parseFloat($('#product-selling-price-input').val().replace(/\./g, '')) || 0;
        
        if (purchasePrice > 0 && sellingPrice > 0) {
            var margin = ((sellingPrice - purchasePrice) / purchasePrice * 100).toFixed(2);
            // You can display margin somewhere if needed
            console.log('Margin:', margin + '%');
        }
    },
    
    handleCategoryChange: function() {
        var categoryId = $(this).val();
        if (categoryId) {
            // Load category-specific settings if needed
            console.log('Category changed to:', categoryId);
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
    ProductModule.init();
});
</script>
