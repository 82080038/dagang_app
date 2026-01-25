<!DOCTYPE html>
<html lang="id" data-theme="dark-blue">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= isset($title) ? $title . ' - ' . APP_NAME : APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/public/assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/public/assets/css/sidebar.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>/index.php?page=dashboard">
                <i class="fas fa-store"></i> <?= APP_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/dashboard') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/products') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=products">
                            <i class="fas fa-box"></i> Produk
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/transactions') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=transactions">
                            <i class="fas fa-exchange-alt"></i> Transaksi
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/reports') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=reports">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/notifications') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=notifications">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if (method_exists($this, 'getUnreadCount') && $this->getUnreadCount() > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $this->getUnreadCount() ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/search') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=search">
                            <i class="fas fa-search"></i> Search
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/files') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=files">
                            <i class="fas fa-file"></i> Files
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= $_SESSION['user_name'] ?? 'User' ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?page=profile">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?page=logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="flex-grow-1">
        <div class="container-fluid py-4">
            <?= isset($content) ? $content : '' ?>
        </div>
    </main>
    <?php else: ?>
    <!-- Login/Register Content -->
    <main class="flex-grow-1">
        <div class="container-fluid py-4">
            <?= isset($content) ? $content : '' ?>
        </div>
    </main>
    <?php endif; ?>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="globalToastContainer"></div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery with local primary and CDN fallbacks -->
    <script src="<?= BASE_URL ?>/public/assets/js/jquery-3.6.0.min.js"></script>
    <script>
        // Check if local jQuery loaded, if not try CDN
        if (typeof $ === 'undefined') {
            console.log('Local jQuery failed, trying CDN fallback...');
            var jqueryScript = document.createElement('script');
            jqueryScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
            jqueryScript.onload = function() {
                console.log('jQuery loaded from CDN fallback');
            };
            jqueryScript.onerror = function() {
                console.log('jQuery CDN failed, trying Google CDN...');
                var googleScript = document.createElement('script');
                googleScript.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js';
                googleScript.onload = function() {
                    console.log('jQuery loaded from Google CDN');
                };
                googleScript.onerror = function() {
                    console.error('All jQuery sources failed');
                    alert('Failed to load jQuery. Please check your internet connection.');
                };
                document.head.appendChild(googleScript);
            };
            document.head.appendChild(jqueryScript);
        } else {
            console.log('Local jQuery loaded successfully');
        }
    </script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/public/assets/js/app_simple.js"></script>
    
    <!-- Global JavaScript Variables -->
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.APP_NAME = '<?= APP_NAME ?>';
    </script>
</body>
</html>
