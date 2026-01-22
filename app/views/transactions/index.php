<div class="container-fluid h-100">
    <div class="row h-100">
        <!-- Left Panel: Products -->
        <div class="col-md-8 d-flex flex-column h-100">
            <div class="card shadow mb-4 flex-grow-1">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary me-3">Katalog Produk</h6>
                        <select id="branchSelector" class="form-select form-select-sm" style="width: 200px;">
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['id_branch'] ?>" <?= $selected_branch == $branch['id_branch'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($branch['branch_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="productSearch" class="form-control" placeholder="Cari produk (nama/kode)..." autofocus>
                        <button class="btn btn-primary" type="button" id="searchBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body overflow-auto" style="height: 60vh;">
                    <div class="row" id="productList">
                        <!-- Products will be loaded here -->
                        <div class="col-12 text-center mt-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Cart -->
        <div class="col-md-4 d-flex flex-column h-100">
            <div class="card shadow mb-4 flex-grow-1">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Keranjang Belanja</h6>
                </div>
                <div class="card-body p-0 d-flex flex-column" style="height: 60vh;">
                    <div class="table-responsive flex-grow-1 overflow-auto">
                        <table class="table table-striped mb-0" id="cartTable">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Produk</th>
                                    <th style="width: 80px;">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <!-- Cart items -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-3 border-top bg-light">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="fw-bold" id="cartSubtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h4">Total:</span>
                            <span class="h4 text-primary" id="cartTotal">Rp 0</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Metode Pembayaran</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">Tunai (Cash)</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <textarea class="form-control" id="transactionNotes" placeholder="Catatan transaksi..." rows="1"></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg" id="checkoutBtn" disabled>
                                <i class="bi bi-cart-check"></i> Bayar Sekarang
                            </button>
                            <button class="btn btn-outline-danger btn-sm" id="clearCartBtn">
                                <i class="bi bi-trash"></i> Kosongkan Keranjang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaksi Berhasil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetCart()"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                </div>
                <h6 id="receiptCode" class="fw-bold mb-3"></h6>
                <p>Transaksi telah berhasil disimpan.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="printReceipt()">
                        <i class="bi bi-printer"></i> Cetak Struk
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetCart()">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(run){
    if (window.jQuery) { run(); }
    else {
        var i = setInterval(function(){
            if (window.jQuery) { clearInterval(i); run(); }
        }, 30);
        setTimeout(function(){ clearInterval(i); }, 5000);
    }
})(function() {
$(function() {
    let currentBranchId = $('#branchSelector').val();
    let cart = [];
    let products = []; // Cache loaded products

    // Load Products
    function loadProducts(query = '') {
        $('#productList').html('<div class="col-12 text-center mt-5"><div class="spinner-border text-primary"></div></div>');
        
        $.ajax({
            url: 'index.php?page=transactions&action=searchProducts',
            data: { branch_id: currentBranchId, q: query },
            success: function(response) {
                if (response.status === 'success') {
                    products = response.data;
                    renderProducts(products);
                }
            }
        });
    }

    function renderProducts(items) {
        const container = $('#productList');
        container.empty();
        
        if (items.length === 0) {
            container.append('<div class="col-12 text-center text-muted mt-5">Produk tidak ditemukan atau stok kosong</div>');
            return;
        }
        
        items.forEach(item => {
            // Check stock
            const stock = parseFloat(item.quantity);
            const price = parseFloat(item.price) || 0; // Assuming price is in products table or inventory? 
            // Wait, price is usually in products table. Let's assume inventory query joins products.
            // In Inventory.php: SELECT i.*, p.product_name, p.product_code... does it select price?
            // Need to check Inventory.php getBranchInventory SQL.
            
            const card = `
                <div class="col-md-4 col-lg-3 mb-3">
                    <div class="card h-100 product-card cursor-pointer" onclick="addToCart(${item.product_id})" style="cursor: pointer;">
                        <div class="card-body p-2 text-center">
                            <div class="h1 text-secondary mb-2"><i class="bi bi-box-seam"></i></div>
                            <h6 class="card-title text-truncate" title="${item.product_name}">${item.product_name}</h6>
                            <p class="card-text small text-muted mb-1">${item.product_code}</p>
                            <div class="fw-bold text-primary">Rp ${price.toLocaleString()}</div>
                            <small class="text-${stock > 0 ? 'success' : 'danger'}">Stok: ${stock}</small>
                        </div>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    // Cart Functions
    window.addToCart = function(productId) {
        const product = products.find(p => p.product_id == productId);
        if (!product) return;
        
        if (parseFloat(product.quantity) <= 0) {
            showToast('warning','Stok habis!');
            return;
        }

        const existingItem = cart.find(item => item.product_id == productId);
        
        if (existingItem) {
            if (existingItem.quantity + 1 > parseFloat(product.quantity)) {
                showToast('warning','Stok tidak mencukupi!');
                return;
            }
            existingItem.quantity++;
        } else {
            // Price fallback if not in join
            // Ideally price should be in products table
            const price = parseFloat(product.sell_price || product.price || 0); 
            
            cart.push({
                product_id: productId,
                name: product.product_name,
                price: price,
                quantity: 1,
                max_stock: parseFloat(product.quantity)
            });
        }
        
        renderCart();
    };

    window.removeFromCart = function(index) {
        cart.splice(index, 1);
        renderCart();
    };

    window.updateQty = function(index, delta) {
        const item = cart[index];
        const newQty = item.quantity + delta;
        
        if (newQty <= 0) {
            removeFromCart(index);
            return;
        }
        
        if (newQty > item.max_stock) {
            showToast('warning','Stok maksimum tercapai');
            return;
        }
        
        item.quantity = newQty;
        renderCart();
    };

    function renderCart() {
        const tbody = $('#cartItems');
        tbody.empty();
        
        let total = 0;
        
        cart.forEach((item, index) => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            
            const tr = `
                <tr>
                    <td>
                        <div class="text-truncate" style="max-width: 150px;">${item.name}</div>
                        <small class="text-muted">@ ${item.price.toLocaleString()}</small>
                    </td>
                    <td>
                        <div class="input-group input-group-sm" style="width: 80px;">
                            <button class="btn btn-outline-secondary px-1" onclick="updateQty(${index}, -1)">-</button>
                            <input type="text" class="form-control text-center px-1" value="${item.quantity}" readonly>
                            <button class="btn btn-outline-secondary px-1" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td class="text-end">${subtotal.toLocaleString()}</td>
                    <td>
                        <button class="btn btn-link text-danger p-0" onclick="removeFromCart(${index})">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });
        
        $('#cartSubtotal').text('Rp ' + total.toLocaleString());
        $('#cartTotal').text('Rp ' + total.toLocaleString());
        $('#checkoutBtn').prop('disabled', cart.length === 0);
    }

    window.resetCart = function() {
        cart = [];
        renderCart();
        $('#transactionNotes').val('');
        loadProducts($('#productSearch').val()); // Reload stock
    };

    // Events
    $('#branchSelector').change(function() {
        currentBranchId = $(this).val();
        cart = []; // Clear cart on branch change
        renderCart();
        loadProducts();
    });

    $('#productSearch').on('input', function() {
        const query = $(this).val();
        // Debounce?
        loadProducts(query);
    });

    $('#clearCartBtn').click(resetCart);

    $('#checkoutBtn').click(function() {
        if (cart.length === 0) return;
        
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        const data = {
            branch_id: currentBranchId,
            total_amount: total,
            payment_method: $('#paymentMethod').val(),
            notes: $('#transactionNotes').val(),
            items: cart
        };

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');

        $.ajax({
            url: 'index.php?page=transactions&action=create',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.status === 'success') {
                    $('#receiptCode').text(response.data.transaction_code);
                    new bootstrap.Modal(document.getElementById('receiptModal')).show();
                } else {
                    showToast('error','Transaksi Gagal: ' + response.message);
                }
            },
            error: function() {
                showToast('error','Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#checkoutBtn').prop('disabled', false).html('<i class="bi bi-cart-check"></i> Bayar Sekarang');
            }
        });
    });

    // Initial Load
    loadProducts();
});
});
</script>
