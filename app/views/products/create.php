<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tambah Produk Baru</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?page=products" class="btn btn-sm btn-outline-secondary">
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

                    <form action="index.php?page=products&action=create" method="POST" enctype="multipart/form-data">
                        <?php echo Csrf::input(); ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Info -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                                        <input type="text" name="product_code" class="form-control" required 
                                               value="<?php echo htmlspecialchars($generated_code); ?>" readonly>
                                        <div class="form-text">Kode otomatis dibuat sistem</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Barcode / SKU</label>
                                        <input type="text" name="barcode" class="form-control" placeholder="Scan barcode..."
                                               value="">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" name="product_name" class="form-control" required
                                           placeholder="Contoh: Indomie Goreng"
                                           value="">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-select" required>
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
                                        <input type="text" name="unit" class="form-control" required
                                               placeholder="pcs, kg, pack..."
                                               value="pcs">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
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
                                                <input type="number" name="purchase_price" class="form-control text-end" 
                                                       value="0" min="0" step="100">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" name="selling_price" class="form-control text-end fw-bold" 
                                                       value="0" min="0" step="100" required>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Batas Stok Minimum</label>
                                            <input type="number" name="low_stock_threshold" class="form-control" 
                                                   value="10" min="0">
                                            <div class="form-text">Notifikasi jika stok di bawah ini</div>
                                        </div>
                                        
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                                            <label class="form-check-label" for="isActive">Produk Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php?page=products" class="btn btn-light me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">Simpan Produk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
