<?php
/**
 * Browser Compatibility Check
 * This page checks browser compatibility and redirects if needed
 */

require_once __DIR__ . '/../utils/BrowserDetector.php';

$browserInfo = BrowserDetector::getBrowserSupportData();
$supportStatus = $browserInfo['support_status'];

// If browser is supported, redirect to dashboard
if ($supportStatus['supported']) {
    header('Location: index.php?page=login');
    exit;
}

// If browser is not supported, show warning page
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kompatibilitas Browser - Aplikasi Perdagangan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .icon {
            font-size: 64px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .browser-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #e74c3c;
        }
        
        .browser-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .browser-version {
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .message {
            color: #e74c3c;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .supported-browsers {
            text-align: left;
            margin: 30px 0;
        }
        
        .supported-browsers h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .browser-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .browser-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .browser-item h4 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .browser-item p {
            color: #7f8c8d;
            font-size: 14px;
            margin: 0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 10px;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-primary {
            background: #27ae60;
        }
        
        .btn-primary:hover {
            background: #229954;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .force-continue {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .force-continue p {
            color: #7f8c8d;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .browser-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚠️</div>
        <h1>Kompatibilitas Browser</h1>
        
        <div class="browser-info">
            <div class="browser-name"><?php echo htmlspecialchars($supportStatus['browser_name']); ?></div>
            <div class="browser-version">Versi: <?php echo htmlspecialchars($supportStatus['browser_version']); ?></div>
            <div class="message"><?php echo htmlspecialchars($supportStatus['message']); ?></div>
        </div>
        
        <div class="supported-browsers">
            <h3>Browser yang Didukung</h3>
            <div class="browser-list">
                <?php foreach ($browserInfo['supported_browsers'] as $key => $browser): ?>
                    <div class="browser-item">
                        <h4><?php echo htmlspecialchars($browser['name']); ?></h4>
                        <p>Versi <?php echo htmlspecialchars($browser['min_version']); ?> atau lebih tinggi</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="force-continue">
            <p>Browser Anda mungkin masih dapat menjalankan aplikasi, namun beberapa fitur mungkin tidak berfungsi dengan baik.</p>
            <a href="index.php?page=login" class="btn btn-primary">Lanjutkan Saja</a>
            <a href="#" onclick="window.location.reload();" class="btn btn-secondary">Periksa Ulang</a>
        </div>
    </div>
    
    <script>
        // Fallback untuk browser sangat lama
        if (typeof window.addEventListener === 'undefined') {
            document.getElementById('force-continue').innerHTML = 
                '<p>Browser Anda terlalu lama dan tidak didukung. Silakan gunakan browser modern.</p>';
        }
        
        // Deteksi JavaScript support
        if (typeof JSON === 'undefined') {
            document.getElementById('force-continue').innerHTML = 
                '<p>Browser Anda tidak mendukung JavaScript yang diperlukan aplikasi ini.</p>';
        }
    </script>
</body>
</html>
