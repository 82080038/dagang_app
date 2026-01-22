<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php?page=transactions" class="btn btn-primary">
                <i class="bi bi-cart-plus"></i> Transaksi Baru (POS)
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Transaksi</h6>
            <form class="d-flex" method="GET" action="index.php">
                <input type="hidden" name="page" value="transactions">
                <input type="hidden" name="action" value="history">
                
                <select name="branch_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    <option value="">Semua Cabang</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?= $branch['id_branch'] ?>" <?= $selected_branch == $branch['id_branch'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($branch['branch_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Cabang</th>
                            <th>Kasir</th>
                            <th class="text-end">Total</th>
                            <th>Metode</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr><td colspan="7" class="text-center">Belum ada transaksi</td></tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $trx): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="fw-bold text-primary btn-details" data-id="<?= $trx['id_transaction'] ?>">
                                            <?= htmlspecialchars($trx['transaction_code']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($trx['branch_name']) ?></td>
                                    <td><?= htmlspecialchars($trx['username']) ?></td>
                                    <td class="text-end">Rp <?= number_format($trx['total_amount'], 0, ',', '.') ?></td>
                                    <td><span class="badge bg-secondary"><?= strtoupper($trx['payment_method']) ?></span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-info btn-details" data-id="<?= $trx['id_transaction'] ?>" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total'] > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                            <li class="page-item <?= $pagination['current'] == $i ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?page=transactions&action=history&page_num=<?= $i ?>&branch_id=<?= $selected_branch ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="printBtn">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.btn-details').click(function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        $('#detailContent').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        new bootstrap.Modal(document.getElementById('detailModal')).show();
        
        // Fetch details (reuse receipt view partial or JSON?)
        // Let's use details endpoint returning JSON and build HTML here for flexibility, 
        // OR simply load the receipt view in an iframe or partial.
        // For now, let's fetch JSON and build a simple table.
        
        $.ajax({
            url: 'index.php?page=transactions&action=details&id=' + id,
            success: function(response) {
                if (response.status === 'success') {
                    renderDetails(response.data);
                }
            }
        });
    });
    
    function renderDetails(data) {
        let itemsHtml = '';
        data.items.forEach(item => {
            itemsHtml += `
                <tr>
                    <td>${item.product_name} <br> <small class="text-muted">${item.product_code}</small></td>
                    <td class="text-end">${parseFloat(item.quantity).toLocaleString()}</td>
                    <td class="text-end">${parseFloat(item.price).toLocaleString()}</td>
                    <td class="text-end">${parseFloat(item.subtotal).toLocaleString()}</td>
                </tr>
            `;
        });

        const html = `
            <div class="text-center mb-4">
                <h5 class="fw-bold">${data.branch_name}</h5>
                <p class="mb-1">Kode: ${data.transaction_code}</p>
                <p class="small text-muted">${data.created_at}</p>
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                    <tfoot class="fw-bold border-top">
                        <tr>
                            <td colspan="3" class="text-end">Total</td>
                            <td class="text-end">Rp ${parseFloat(data.total_amount).toLocaleString()}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3 small">
                <p class="mb-1"><strong>Metode Pembayaran:</strong> ${data.payment_method.toUpperCase()}</p>
                <p class="mb-1"><strong>Kasir:</strong> ${data.username}</p>
                ${data.notes ? `<p><strong>Catatan:</strong> ${data.notes}</p>` : ''}
            </div>
        `;
        
        $('#detailContent').html(html);
        $('#printBtn').attr('onclick', `window.open('index.php?page=transactions&action=details&id=${data.id_transaction}', '_blank')`);
    }
});
</script>
