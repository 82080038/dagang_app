<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear Cache & Refresh Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .status {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Clear Cache & Refresh Dashboard</h1>
        <p>Halaman ini akan membersihkan cache browser dan me-refresh dashboard untuk mengatasi JavaScript errors.</p>
        
        <div class="status" id="status">
            <div class="warning">⚠️ Memproses...</div>
        </div>
        
        <div>
            <button class="btn" onclick="clearCache()">🗑️ Clear Browser Cache</button>
            <button class="btn btn-danger" onclick="hardRefresh()">🔄 Hard Refresh</button>
            <button class="btn" onclick="openDashboard()">📊 Open Dashboard</button>
        </div>
        
        <div>
            <h3>📋 Langkah-langkah Manual:</h3>
            <ol>
                <li><strong>Clear Cache:</strong> Klik tombol "Clear Browser Cache"</li>
                <li><strong>Hard Refresh:</strong> Klik tombol "Hard Refresh" (Ctrl+Shift+R)</li>
                <li><strong>Open Dashboard:</strong> Klik tombol "Open Dashboard"</li>
                <li><strong>Check Console:</strong> Buka Developer Tools (F12) dan lihat Console tab</li>
            </ol>
        </div>
        
        <div>
            <h3>🔧 Troubleshooting:</h3>
            <ul>
                <li><strong>Jika masih error:</strong> Clear cache lagi dan restart browser</li>
                <li><strong>Check Network tab:</strong> Pastikan semua scripts load dengan status 200</li>
                <li><strong>Disable extensions:</strong> Matikan browser extensions yang mungkin mengganggu</li>
                <li><strong>Try incognito mode:</strong> Buka dashboard di incognito/private window</li>
            </ul>
        </div>
        
        <div>
            <h3>📊 Status Check:</h3>
            <div id="checkResults">
                <p>Memeriksa status...</p>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(message, type = 'success') {
            const statusDiv = document.getElementById('status');
            statusDiv.className = 'status ' + type;
            statusDiv.innerHTML = message;
        }

        function clearCache() {
            updateStatus('🗑️ Membersihkan cache browser...', 'warning');
            
            // Clear various types of cache
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    return Promise.all(names.map(function(name) {
                        return caches.delete(name);
                    }));
                }).then(function() {
                    updateStatus('✅ Service Worker cache dibersihkan', 'success');
                });
            }
            
            // Clear localStorage
            localStorage.clear();
            updateStatus('✅ LocalStorage dibersihkan', 'success');
            
            // Clear sessionStorage
            sessionStorage.clear();
            updateStatus('✅ SessionStorage dibersihkan', 'success');
            
            setTimeout(() => {
                updateStatus('✅ Cache dibersihkan! Silakan refresh dashboard.', 'success');
            }, 1000);
        }

        function hardRefresh() {
            updateStatus('🔄 Melakukan hard refresh...', 'warning');
            
            // Add cache-busting parameter
            const url = new URL(window.location.href);
            url.searchParams.set('v', Date.now());
            
            setTimeout(() => {
                window.location.href = url.toString();
            }, 1000);
        }

        function openDashboard() {
            updateStatus('📊 Membuka dashboard...', 'warning');
            
            const dashboardUrl = '<?= BASE_URL ?>/index.php?page=dashboard';
            window.open(dashboardUrl, '_blank');
            
            setTimeout(() => {
                updateStatus('✅ Dashboard dibuka di tab baru', 'success');
            }, 1000);
        }

        function checkStatus() {
            const results = document.getElementById('checkResults');
            
            // Check if jQuery is loaded
            const jQueryLoaded = typeof $ !== 'undefined';
            const ChartLoaded = typeof Chart !== 'undefined';
            
            let html = '<ul>';
            html += '<li><strong>jQuery:</strong> ' + (jQueryLoaded ? '✅ Loaded' : '❌ Not loaded') + '</li>';
            html += '<li><strong>Chart.js:</strong> ' + (ChartLoaded ? '✅ Loaded' : '❌ Not loaded') + '</li>';
            html += '<li><strong>BASE_URL:</strong> ' + (typeof window.BASE_URL !== 'undefined' ? '✅ Available' : '❌ Not available') + '</li>';
            html += '<li><strong>APP_NAME:</strong> ' + (typeof window.APP_NAME !== 'undefined' ? '✅ Available' : '❌ Not available') + '</li>';
            html += '</ul>';
            
            if (jQueryLoaded && ChartLoaded) {
                html += '<div class="status success">✅ Semua dependencies terload dengan benar!</div>';
            } else {
                html += '<div class="status error">❌ Masih ada dependencies yang hilang!</div>';
            }
            
            results.innerHTML = html;
        }

        // Auto-check status when page loads
        setTimeout(checkStatus, 2000);
        
        // Auto-check every 5 seconds
        setInterval(checkStatus, 5000);
    </script>
</body>
</html>
