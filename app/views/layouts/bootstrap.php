<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' . APP_NAME : APP_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome (Backup Icons) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= $this->asset('css/bootstrap-custom.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= $this->url('/') ?>">
                <i class="bi bi-shop me-2"></i><?= APP_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('dashboard') ? 'active' : '' ?>" href="<?= $this->url('/dashboard') ?>">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-building me-1"></i> Perusahaan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-shop me-1"></i> Cabang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-box me-1"></i> Produk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-cash-stack me-1"></i> POS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-graph-up me-1"></i> Laporan
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="modulesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-puzzle me-1"></i> Modul
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= $this->url('/modules') ?>">
                                <i class="bi bi-gear me-2"></i> Manajemen Modul
                            </a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/modules/available') ?>">
                                <i class="bi bi-plus-circle me-2"></i> Modul Tersedia
                            </a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/modules/company-settings') ?>">
                                <i class="bi bi-sliders me-2"></i> Pengaturan Perusahaan
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/modules/export') ?>">
                                <i class="bi bi-download me-2"></i> Export Pengaturan
                            </a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/modules/import') ?>">
                                <i class="bi bi-upload me-2"></i> Import Pengaturan
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> User
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-person me-2"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-gear me-2"></i> Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container-fluid mt-3">
        <?= $this->displayFlash() ?>
    </div>

    <!-- Main Content -->
    <main class="container-fluid">
        <?= isset($content) ? $content : '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
            <small>Version <?= APP_VERSION ?> | Native PHP MVC dengan Bootstrap 5</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= $this->asset('js/app.js') ?>"></script>
    
    <!-- Icon Helper Script -->
    <script>
        // Icon fallback system
        function getIcon(iconName) {
            // Try Bootstrap Icons first
            if (typeof bi !== 'undefined' && bi[iconName]) {
                return 'bi ' + iconName;
            }
            
            // Fallback to Font Awesome
            const faMapping = {
                'bi-speedometer2': 'fas fa-tachometer-alt',
                'bi-building': 'fas fa-building',
                'bi-shop': 'fas fa-store',
                'bi-box': 'fas fa-box',
                'bi-cash-stack': 'fas fa-cash-register',
                'bi-graph-up': 'fas fa-chart-line',
                'bi-puzzle': 'fas fa-puzzle-piece',
                'bi-gear': 'fas fa-cog',
                'bi-plus-circle': 'fas fa-plus-circle',
                'bi-sliders': 'fas fa-sliders-h',
                'bi-download': 'fas fa-download',
                'bi-upload': 'fas fa-upload',
                'bi-person-circle': 'fas fa-user-circle',
                'bi-person': 'fas fa-user',
                'bi-gear-wide': 'fas fa-cogs',
                'bi-box-arrow-right': 'fas fa-sign-out-alt'
            };
            
            return faMapping[iconName] || 'fas fa-question';
        }
        
        // Replace icons on page load
        $(document).ready(function() {
            $('[class*="bi-"]').each(function() {
                const $element = $(this);
                const classes = $element.attr('class').split(' ');
                const biClass = classes.find(cls => cls.startsWith('bi-'));
                
                if (biClass) {
                    const faClass = getIcon(biClass);
                    $element.removeClass(biClass).addClass(faClass);
                }
            });
        });
    </script>
</body>
</html>
?>
