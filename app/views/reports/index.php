<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $title ?></h1>
        <div class="d-flex gap-2">
            <!-- Placeholder: filter/pencarian/ekspor akan ditambahkan di sini -->
            <button class="btn btn-outline-primary btn-sm" id="refreshReportsBtn">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
        </div>
    </div>
    
    <!-- Status: Loading / Error / Empty -->
    <div id="reportsLoading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-2 text-muted small">Memuat laporan...</div>
    </div>
    <div id="reportsError" class="alert alert-danger d-none" role="alert">
        Terjadi kesalahan saat memuat laporan. Silakan coba lagi.
    </div>
    <div id="reportsEmpty" class="alert alert-info d-none" role="alert">
        Belum ada yang bisa dilaporkan.
    </div>
    
    <!-- Konten Laporan -->
    <div id="reportsContent" class="d-none">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Penjualan Hari Ini</h5>
                        <div class="h4 text-primary" id="todayTotal">Rp 0</div>
                        <small class="text-muted"><span id="todayCount">0</span> transaksi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Low Stock</h5>
                        <div class="h4 text-danger" id="lowStockCount">0</div>
                        <small class="text-muted">Produk perlu restock</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Ringkasan 7 Hari</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-end">Total Penjualan</th>
                                <th class="text-end">Jumlah Transaksi</th>
                            </tr>
                        </thead>
                        <tbody id="last7TableBody">
                            <!-- populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Produk 30 Hari</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="topProductsTableBody">
                            <!-- populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// State management sederhana untuk loading/error/empty
document.addEventListener('DOMContentLoaded', function() {
    function setState({loading=false, error=null, empty=false}) {
        document.getElementById('reportsLoading').classList.toggle('d-none', !loading);
        const errEl = document.getElementById('reportsError');
        errEl.classList.toggle('d-none', !error);
        if (error) errEl.textContent = error;
        document.getElementById('reportsEmpty').classList.toggle('d-none', !empty);
        document.getElementById('reportsContent').classList.toggle('d-none', loading || error || empty);
    }
    
    function formatCurrency(n) {
        const num = parseFloat(n || 0);
        return 'Rp ' + num.toLocaleString('id-ID', {maximumFractionDigits: 0});
    }
    
    function loadReports() {
        setState({loading: true});
        UI.tableSkeleton(document.getElementById('last7TableBody'), 3, 7);
        UI.tableSkeleton(document.getElementById('topProductsTableBody'), 3, 5);
        http.fetchJSON(BASE_URL + '/index.php?page=reports&action=api', {errorMessage:'Gagal memuat laporan'})
            .then(json => {
                if (json.status !== 'success') throw new Error(json.message || 'Gagal memuat laporan');
                const data = json.data || {};
                const today = data.today || {total:0, count:0};
                const last7 = Array.isArray(data.last7) ? data.last7 : [];
                const topProducts = Array.isArray(data.topProducts) ? data.topProducts : [];
                const lowStock = data.lowStock || 0;
                
                const isEmpty = (parseFloat(today.total) === 0 && parseInt(today.count) === 0 && lowStock === 0 && last7.length === 0 && topProducts.length === 0);
                if (isEmpty) {
                    setState({loading:false, empty:true});
                    return;
                }
                
                // Populate summary
                document.getElementById('todayTotal').textContent = formatCurrency(today.total);
                document.getElementById('todayCount').textContent = today.count;
                document.getElementById('lowStockCount').textContent = lowStock;
                
                // Populate last7 table
                const last7Tbody = document.getElementById('last7TableBody');
                UI.clearTable(last7Tbody);
                last7.forEach(row => {
                    const tr = document.createElement('tr');
                    const dStr = new Date(row.d).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                    tr.innerHTML = '<td>'+ dStr +'</td>' +
                                   '<td class="text-end">'+ formatCurrency(row.total) +'</td>' +
                                   '<td class="text-end">'+ (row.count || 0) +'</td>';
                    last7Tbody.appendChild(tr);
                });
                
                // Populate top products
                const topTbody = document.getElementById('topProductsTableBody');
                UI.clearTable(topTbody);
                topProducts.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td>'+ (p.product_name || '-') +'</td>' +
                                   '<td class="text-end">'+ (parseFloat(p.qty || 0)).toLocaleString('id-ID') +'</td>' +
                                   '<td class="text-end">'+ formatCurrency(p.revenue) +'</td>';
                    topTbody.appendChild(tr);
                });
                
                setState({loading:false});
            })
            .catch(err => {
                setState({loading:false, error: 'Terjadi kesalahan saat memuat laporan. ' + (err.message || '')});
            });
    }
    
    document.getElementById('refreshReportsBtn').addEventListener('click', loadReports);
    loadReports();
});
</script>
