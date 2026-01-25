<style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .login-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-form {
            padding: 3rem;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-section i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #667eea;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .feature-list i {
            margin-right: 10px;
            color: #fff;
        }
        
        .register-link {
            color: #667eea;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .register-link:hover {
            color: #764ba2;
            text-decoration: underline !important;
        }
    </style>
<div class="container">
        <div class="login-container">
            <div class="row g-0">
                <!-- Sidebar -->
                <div class="col-lg-5 login-sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-store"></i>
                        <h3>Aplikasi Perdagangan</h3>
                        <p class="lead">Multi-Cabang System</p>
                    </div>
                    
                    <h5 class="mb-3">Fitur Utama:</h5>
                    <ul class="feature-list">
                        <li>
                            <i class="fas fa-building"></i>
                            Management Multi-Cabang
                        </li>
                        <li>
                            <i class="fas fa-cash-register"></i>
                            Point of Sale (POS)
                        </li>
                        <li>
                            <i class="fas fa-warehouse"></i>
                            Inventory Management
                        </li>
                        <li>
                            <i class="fas fa-chart-bar"></i>
                            Laporan & Analytics
                        </li>
                        <li>
                            <i class="fas fa-mobile-alt"></i>
                            Mobile Responsive
                        </li>
                    </ul>
                    
                    <div class="mt-auto">
                        <p class="small mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            Sistem aman & terpercaya untuk bisnis Anda
                        </p>
                    </div>
                </div>
                
                <!-- Login Form -->
                <div class="col-lg-7 login-form">
                    <div class="logo-section">
                        <i class="fas fa-user-circle"></i>
                        <h4>Selamat Datang</h4>
                        <p class="text-muted">Silakan login untuk melanjutkan</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert" id="loginErrorAlert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="index.php?page=login" id="loginForm">
                        <?php echo Csrf::input(); ?>
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">
                                <i class="fas fa-user me-1"></i>Username
                            </label>
                            <input type="text" class="form-control" id="loginUsername" name="username" 
                                   placeholder="Masukkan username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">
                                <i class="fas fa-lock me-1"></i>Password
                            </label>
                            <input type="password" class="form-control" id="loginPassword" name="password" 
                                   placeholder="Masukkan password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="loginRemember">
                            <label class="form-check-label" for="loginRemember">
                                Ingat saya
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login" id="loginSubmitBtn">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Hubungi admin untuk mendapatkan akses login
                        </p>
                        <p class="mb-0">
                            <span class="text-muted">Belum punya akun?</span>
                            <a href="index.php?page=register" class="register-link">
                                Daftar di sini
                            </a>
                        </p>
                    </div>
                    
                    <!-- Demo credentials info -->
                    <div class="alert alert-info mt-3" role="alert">
                        <h6><i class="fas fa-info-circle me-2"></i>Akses Demo:</h6>
                        <small>
                            Username: admin<br>
                            Password: admin123<br>
                            <em>(Untuk testing purposes)</em>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
        // Auto focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loginUsername').focus();
        });
        
        // Clear error message on input
        document.getElementById('loginUsername').addEventListener('input', function() {
            var alert = document.getElementById('loginErrorAlert');
            if (alert) {
                alert.style.display = 'none';
            }
        });
        
        document.getElementById('loginPassword').addEventListener('input', function() {
            var alert = document.getElementById('loginErrorAlert');
            if (alert) {
                alert.style.display = 'none';
            }
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            var username = document.getElementById('loginUsername').value.trim();
            var password = document.getElementById('loginPassword').value;
            
            if (!username || !password) {
                e.preventDefault();
                
                // Create error alert if not exists
                var alertDiv = document.getElementById('loginErrorAlert');
                if (!alertDiv) {
                    alertDiv = document.createElement('div');
                    alertDiv.id = 'loginErrorAlert';
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Username dan password harus diisi';
                    document.querySelector('.logo-section').after(alertDiv);
                }
                
                return false;
            }
        });
    </script>
</html>
