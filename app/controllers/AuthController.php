<?php
/**
 * Auth Controller
 * 
 * Simple authentication system untuk aplikasi
 * Login/Logout dengan session
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Address.php';

class AuthController extends Controller {
    private $memberModel;
    
    public function __construct() {
        parent::__construct();
        $this->memberModel = new Member();
    }
    
    /**
     * Login page
     */
    public function login() {
        // Jika sudah login, redirect ke dashboard
        if ($this->isLoggedIn()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        // Proses login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF Token
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
                $error = 'Sesi kadaluarsa, silakan refresh halaman';
            } else {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if (empty($username) || empty($password)) {
                    $error = 'Username dan password harus diisi';
                } else {
                    $user = $this->memberModel->authenticate($username, $password);
                    
                    if ($user) {
                        // Prevent Session Fixation
                        session_regenerate_id(true);
    
                        // Set session
                        $_SESSION['user_id'] = $user['id_member'];
                        $_SESSION['username'] = $user['member_code'];
                        $_SESSION['user_name'] = $user['member_name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_position'] = $user['position'];
                        $_SESSION['branch_id'] = $user['branch_id'];
                        $_SESSION['logged_in'] = true;
                        
                        // Redirect ke dashboard
                        header('Location: index.php?page=dashboard');
                        exit;
                    } else {
                        $error = 'Username atau password salah';
                    }
                }
            }
        }
        
        // Load login view
        $this->render('auth/login', ['error' => $error ?? '']);
    }
    
    /**
     * Logout
     */
    public function logout() {
        // Destroy session
        session_destroy();
        
        // Redirect ke login
        header('Location: index.php?page=login');
        exit;
    }
    
    public function register() {
        if ($this->isLoggedIn()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
                $error = 'Sesi kadaluarsa, silakan refresh halaman';
            } else {
                $memberCode = $_POST['member_code'] ?? '';
                $memberName = $_POST['member_name'] ?? '';
                $email = $_POST['email'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $password = $_POST['password'] ?? '';
                $position = $_POST['position'] ?? 'owner';
                $branchId = $_POST['branch_id'] ?? null;
                $street = $_POST['address_detail'] ?? '';
                $provinceId = $_POST['province_id'] ?? '';
                $regencyId = $_POST['regency_id'] ?? '';
                $districtId = $_POST['district_id'] ?? '';
                $villageId = $_POST['village_id'] ?? '';
                $errors = [];
                if (empty($memberCode) || strlen($memberCode) < 3) { $errors['member_code'] = 'Kode minimal 3 karakter'; }
                if (empty($memberName) || strlen($memberName) < 3) { $errors['member_name'] = 'Nama minimal 3 karakter'; }
                if (empty($password) || strlen($password) < 6) { $errors['password'] = 'Password minimal 6 karakter'; }
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Email tidak valid'; }
                if (empty($street) || empty($provinceId) || empty($regencyId) || empty($districtId) || empty($villageId)) {
                    $errors['address'] = 'Alamat lengkap wajib diisi';
                }
                if (!empty($errors)) {
                    $this->render('auth/register', ['error' => 'Validasi gagal', 'old' => $_POST]);
                    return;
                }
                try {
                    $memberId = $this->memberModel->createMember([
                        'member_code' => $memberCode,
                        'member_name' => $memberName,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $password,
                        'position' => $position,
                        'branch_id' => $branchId
                    ]);
                    $addressModel = new Address();
                    $addressId = $addressModel->createAddress([
                        'address_detail' => $street,
                        'province_id' => $provinceId,
                        'regency_id' => $regencyId,
                        'district_id' => $districtId,
                        'village_id' => $villageId
                    ]);
                    $addressModel->linkAddressToEntity($addressId, 'member', $memberId, 'primary');
                    $_SESSION['flash']['success'] = 'Registrasi berhasil, silakan login';
                    header('Location: index.php?page=login');
                    exit;
                } catch (Exception $e) {
                    $this->render('auth/register', ['error' => 'Gagal registrasi', 'old' => $_POST]);
                    return;
                }
            }
        }
        $this->render('auth/register', ['error' => $error ?? '', 'old' => []]);
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'user_name' => $_SESSION['user_name'],
                'user_email' => $_SESSION['user_email'],
                'user_position' => $_SESSION['user_position'],
                'branch_id' => $_SESSION['branch_id']
            ];
        }
        return null;
    }
    
    /**
     * Require authentication
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
    }
    
    /**
     * Check user role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Require specific role
     */
    public function requireRole($role) {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            echo 'Access denied';
            exit;
        }
    }
}

?>
