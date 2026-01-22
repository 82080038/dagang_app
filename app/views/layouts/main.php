<!DOCTYPE html>
<html lang="id" data-theme="dark-blue">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= isset($title) ? $title . ' - ' . APP_NAME : APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/public/assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/public/assets/css/sidebar-compact.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php if ($this->isLoggedIn()): ?>
    <!-- Mobile Navigation Toggle -->
    <nav class="navbar navbar-dark bg-dark d-md-none fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>/index.php?page=dashboard">
                <i class="fas fa-store me-2"></i><?= APP_NAME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <!-- Main Layout Container -->
    <div class="container-fluid p-0 vh-100 d-flex flex-row">
        <!-- Sidebar - Desktop -->
        <nav class="sidebar d-none d-md-block col-md-2 col-lg-1 bg-dark text-white">
            <div class="position-sticky pt-3 pt-md-5">
                <div class="text-center mb-4">
                    <h5 class="text-white">
                        <i class="fas fa-store me-2"></i><?= APP_NAME ?>
                    </h5>
                    <small class="text-muted">Multi-Cabang System</small>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/dashboard') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=dashboard" title="Dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="d-none d-lg-inline"> Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('companies') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=companies" title="Perusahaan">
                            <i class="fas fa-building"></i>
                            <span class="d-none d-lg-inline"> Perusahaan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('branches') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=branches" title="Cabang">
                            <i class="fas fa-store"></i>
                            <span class="d-none d-lg-inline"> Cabang</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('products') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=products" title="Produk">
                            <i class="fas fa-box"></i>
                            <span class="d-none d-lg-inline"> Produk</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('transactions') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=transactions" title="Transaksi">
                            <i class="fas fa-cash-register"></i>
                            <span class="d-none d-lg-inline"> Transaksi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('inventory') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=inventory" title="Inventaris">
                            <i class="fas fa-warehouse"></i>
                            <span class="d-none d-lg-inline"> Inventaris</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('reports') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=reports" title="Laporan">
                            <i class="fas fa-chart-bar"></i>
                            <span class="d-none d-lg-inline"> Laporan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('settings') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=settings" title="Pengaturan">
                            <i class="fas fa-cog"></i>
                            <span class="d-none d-lg-inline"> Pengaturan</span>
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link text-danger" href="<?= BASE_URL ?>/index.php?page=logout" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="d-none d-lg-inline"> Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Mobile Sidebar Offcanvas -->
        <div class="offcanvas offcanvas-start bg-dark text-white d-md-none" tabindex="-1" id="sidebarOffcanvas">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">
                    <i class="fas fa-store me-2"></i><?= APP_NAME ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isActive('/dashboard') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=dashboard" data-bs-dismiss="offcanvas">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('companies') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=companies" data-bs-dismiss="offcanvas">
                            <i class="fas fa-building me-2"></i>
                            <span>Perusahaan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('branches') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=branches" data-bs-dismiss="offcanvas">
                            <i class="fas fa-store me-2"></i>
                            <span>Cabang</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('products') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=products" data-bs-dismiss="offcanvas">
                            <i class="fas fa-box me-2"></i>
                            <span>Produk</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('transactions') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=transactions" data-bs-dismiss="offcanvas">
                            <i class="fas fa-cash-register me-2"></i>
                            <span>Transaksi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('inventory') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=inventory" data-bs-dismiss="offcanvas">
                            <i class="fas fa-warehouse me-2"></i>
                            <span>Inventaris</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->isMenuActive('reports') ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/index.php?page=reports" data-bs-dismiss="offcanvas">
                            <i class="fas fa-chart-bar me-2"></i>
                            <span>Laporan</span>
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link text-danger" href="<?= BASE_URL ?>/index.php?page=logout" data-bs-dismiss="offcanvas">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content flex-grow-1 d-flex flex-column">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pt-md-4 pb-2 mb-3 border-bottom">
                <h1 class="h2 h3-md"><?= isset($title) ? $title : 'Dashboard' ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="dropdown me-2">
                        <button class="btn btn-outline-secondary btn-sm btn-md-regular dropdown-toggle d-flex align-items-center gap-2" type="button" id="themeDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" data-bs-display="static">
                            <i class="bi bi-palette"></i>
                            <span id="themeCurrentLabel">Tema</span>
                            <span id="themeCurrentSwatch" class="theme-swatch-btn ms-1"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeDropdown">
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button" data-theme-select="dark-blue">
                                    <span class="theme-swatch" style="background:#1e3a8a"></span>Gelap (Biru)
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button" data-theme-select="light-orange">
                                    <span class="theme-swatch" style="background:#fd7e14"></span>Terang (Oranye)
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button" data-theme-select="high-contrast">
                                    <span class="theme-swatch" style="background:#ffbf00"></span>Kontras Tinggi
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button" data-theme-select="pastel">
                                    <span class="theme-swatch" style="background:#a5b4fc"></span>Pastel
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button" data-theme-select="brand-indigo">
                                    <span class="theme-swatch" style="background:#4f46e5"></span>Brand Indigo
                                </button>
                            </li>
                        </ul>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm btn-md-regular" type="button" id="accessibilityToggle" aria-pressed="false" title="Tingkatkan Aksesibilitas">
                        <i class="bi bi-universal-access"></i>
                        <span id="accessibilityLabel">Aksesibilitas: Off</span>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm btn-md-regular dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= $_SESSION['name'] ?? 'User' ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/index.php?page=logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?= $this->displayFlash() ?>

            <!-- Page Content -->
            <div class="flex-grow-1 overflow-auto">
                <?= isset($content) ? $content : '' ?>
            </div>
        </main>
    </div>
    <?php else: ?>
    <!-- Simple layout for non-authenticated pages -->
    <div class="container-fluid py-4">
        <?= isset($content) ? $content : '' ?>
    </div>
    <?php endif; ?>

    <div id="globalToastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
    <footer class="app-footer mt-auto py-3 py-md-4">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand d-flex align-items-center">
                    <i class="fas fa-store me-2"></i>
                    <span><?= APP_NAME ?></span>
                </div>
                <div class="footer-links">
                    <a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a>
                    <a href="<?= BASE_URL ?>/index.php?page=companies">Perusahaan</a>
                    <a href="<?= BASE_URL ?>/index.php?page=branches">Cabang</a>
                    <a href="<?= BASE_URL ?>/index.php?page=reports">Laporan</a>
                </div>
                <div class="footer-meta">
                    <span>&copy; <?= date('Y') ?> <?= APP_NAME ?></span>
                    <span class="ms-2">Versi <?= APP_VERSION ?></span>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery (must be loaded before Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_GET['page']) && $_GET['page'] === 'dashboard'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/http.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/ui.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/theme.js"></script>
    
    <!-- Global JavaScript Variables -->
    <script>
        var BASE_URL = '<?= BASE_URL ?>';
        var APP_CONFIG = {
            browserSupport: <?php echo json_encode(BrowserDetector::getBrowserSupportData()); ?>,
            isModernBrowser: (function() {
                return typeof Promise !== 'undefined' && 
                       typeof fetch !== 'undefined' && 
                       typeof localStorage !== 'undefined';
            })()
        };
    </script>
    
    <!-- Browser Compatibility Polyfills -->
    <script>
        // Polyfill for older browsers
        if (!window.Promise) {
            window.Promise = function(executor) {
                var resolve, reject;
                this.then = function(onFulfilled, onRejected) {
                    if (typeof onFulfilled === 'function') onFulfilled(resolve);
                    if (typeof onRejected === 'function') onRejected(reject);
                };
                this.catch = function(onRejected) {
                    if (typeof onRejected === 'function') onRejected(reject);
                };
                executor(function(value) { resolve(value); }, function(reason) { reject(reason); });
            };
        }
        
        // Polyfill for fetch API
        if (!window.fetch) {
            window.fetch = function(url, options) {
                options = options || {};
                return new Promise(function(resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open(options.method || 'GET', url);
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            resolve({
                                status: xhr.status,
                                statusText: xhr.statusText,
                                json: function() { return JSON.parse(xhr.responseText); }
                            });
                        } else {
                            reject(new Error('Request failed'));
                        }
                    };
                    xhr.onerror = function() {
                        reject(new Error('Network error'));
                    };
                    xhr.send(options.body);
                });
            };
        }
        
        // Polyfill for localStorage
        if (!window.localStorage) {
            window.localStorage = {
                getItem: function(key) { return window.name[key]; },
                setItem: function(key, value) { window.name[key] = value; },
                removeItem: function(key) { delete window.name[key]; }
            };
        }
        
        // Polyfill for Object.assign
        if (!Object.assign) {
            Object.assign = function(target) {
                if (target == null) {
                    throw new TypeError('Cannot convert undefined or null to object');
                }
                
                var to = Object(target);
                for (var i = 1; i < arguments.length; i++) {
                    var nextSource = arguments[i];
                    if (nextSource != null) {
                        for (var nextKey in nextSource) {
                            if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                                to[nextKey] = nextSource[nextKey];
                            }
                        }
                    }
                }
                return to;
            };
        }
        
        // Polyfill for console methods
        if (!window.console) {
            window.console = {
                log: function() {},
                error: function() {},
                warn: function() {},
                info: function() {}
            };
        }
        
        // Browser compatibility warnings
        (function() {
            var support = APP_CONFIG.browserSupport;
            var status = support.support_status;
            
            if (!status.supported && status.reason === 'outdated') {
                console.warn('Browser version is outdated. Some features may not work properly.');
                console.warn('Recommended: Update to ' + status.browser_name + ' version ' + status.min_version + ' or higher.');
            } else if (!status.supported && status.reason === 'deprecated') {
                console.warn('Browser is deprecated and not supported.');
                console.warn('Please upgrade to a modern browser for the best experience.');
            }
        })();
    </script>
    
    <!-- Custom App JS -->
    <script src="<?= BASE_URL ?>/public/assets/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/jquery-ajax-compatible.js"></script>
    <script>
        (function(){
            var alerts=document.querySelectorAll('.alert[data-flash="true"]');
            alerts.forEach(function(a){
                var t='primary';
                if (a.className.indexOf('alert-success')>-1) t='success';
                else if (a.className.indexOf('alert-danger')>-1) t='error';
                else if (a.className.indexOf('alert-warning')>-1) t='warning';
                var msg=a.textContent.trim();
                if (msg) showToast(t,msg);
                a.remove();
            });
        })();
    </script>
</body>
</html>
