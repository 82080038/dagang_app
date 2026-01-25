<?php
/**
 * Auth Controller
 * 
 * Simple authentication system untuk aplikasi
 * Login/Logout dengan session
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/Address.php';

class AuthController extends Controller {
    private $memberModel;
    private $companyModel;
    private $branchModel;
    
    public function __construct() {
        parent::__construct();
        $this->memberModel = new Member();
        $this->companyModel = new Company();
        $this->branchModel = new Branch();
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
    
                        // Set session with proper dual role system
                        $_SESSION['user_id'] = $user['id_member'];
                        $_SESSION['username'] = $user['member_code'];
                        $_SESSION['user_name'] = $user['member_name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_position'] = $user['position'];
                        $_SESSION['business_role'] = $this->mapPositionToRole($user['position']);
                        $_SESSION['app_role'] = $this->determineApplicationRole($user);
                        $_SESSION['company_id'] = $user['company_id'] ?? null;
                        $_SESSION['branch_id'] = $user['branch_id'] ?? null;
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
        
        // Handle AJAX request for member code checking
        if (isset($_GET['action']) && $_GET['action'] === 'check_member_code') {
            $this->checkMemberCodeAvailability();
            return;
        }
        
        // Handle AJAX request for company code checking
        if (isset($_GET['action']) && $_GET['action'] === 'check_company_code') {
            $this->checkCompanyCodeAvailability();
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
                $error = 'Sesi kadaluarsa, silakan refresh halaman';
            } else {
                $registrationType = $_POST['registration_type'] ?? '';
                
                switch ($registrationType) {
                    case 'individual':
                        $this->registerIndividualBusiness();
                        break;
                    case 'join_company':
                        $this->registerJoinCompany();
                        break;
                    case 'create_company':
                        $this->registerCreateCompany();
                        break;
                    default:
                        $error = 'Pilih tipe pendaftaran yang valid';
                }
            }
        }
        
        // Load register view
        $this->render('auth/register', ['error' => $error ?? '']);
    }
    
    /**
     * Check member code availability (AJAX endpoint)
     */
    private function checkMemberCodeAvailability() {
        $memberCode = $_GET['member_code'] ?? '';
        
        header('Content-Type: application/json');
        
        if (empty($memberCode)) {
            echo json_encode(['available' => false, 'message' => 'Member code is required']);
            return;
        }
        
        try {
            $exists = $this->memberModel->memberCodeExists($memberCode);
            
            if ($exists) {
                echo json_encode(['available' => false, 'message' => 'Member code already exists']);
            } else {
                echo json_encode(['available' => true, 'message' => 'Member code available']);
            }
        } catch (Exception $e) {
            echo json_encode(['available' => false, 'message' => 'Error checking member code: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Check company code availability and return company details (AJAX endpoint)
     */
    private function checkCompanyCodeAvailability() {
        $companyCode = $_GET['company_code'] ?? '';
        
        header('Content-Type: application/json');
        
        if (empty($companyCode)) {
            echo json_encode(['found' => false, 'message' => 'Company code is required']);
            return;
        }
        
        try {
            // Find company by code
            $company = $this->companyModel->getByCode($companyCode);
            
            if (!$company) {
                echo json_encode(['found' => false, 'message' => 'Company code not found']);
                return;
            }
            
            // Get first branch of the company
            $branches = $this->branchModel->getByCompany($company['id_company']);
            $branch = $branches[0] ?? null;
            
            if (!$branch) {
                echo json_encode(['found' => false, 'message' => 'Company has no branches']);
                return;
            }
            
            // Get branch address details
            $branchWithAddress = $this->branchModel->getBranchWithAddress($branch['id_branch']);
            
            // Prepare response with company and branch details
            $response = [
                'found' => true,
                'message' => 'Company found',
                'company' => [
                    'id_company' => $company['id_company'],
                    'company_name' => $company['company_name'],
                    'company_code' => $company['company_code'],
                    'company_type' => $company['company_type'],
                    'branch_name' => $branch['branch_name'],
                    'branch_code' => $branch['branch_code'],
                    'village_name' => $branchWithAddress['village_name'] ?? null,
                    'district_name' => $branchWithAddress['district_name'] ?? null,
                    'regency_name' => $branchWithAddress['regency_name'] ?? null,
                    'province_name' => $branchWithAddress['province_name'] ?? null
                ]
            ];
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            echo json_encode(['found' => false, 'message' => 'Error checking company code: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Register Individual Business
     */
    private function registerIndividualBusiness() {
        $businessName = $_POST['business_name'] ?? '';
        $businessType = $_POST['business_type'] ?? 'individual';
        $memberCode = $_POST['member_code'] ?? '';
        $memberName = $_POST['member_name'] ?? $businessName;
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $position = $_POST['position'] ?? 'owner';
        $addressDetail = $_POST['address_detail'] ?? '';
        $provinceId = $_POST['province_id'] ?? '';
        $regencyId = $_POST['regency_id'] ?? '';
        $districtId = $_POST['district_id'] ?? '';
        $villageId = $_POST['village_id'] ?? '';
        
        // Validate required fields
        if (empty($businessName) || empty($memberCode) || empty($password)) {
            $error = 'Nama usaha, kode pengguna, dan password harus diisi';
            return;
        }
        
        try {
            // Create individual company
            $companyData = [
                'company_name' => $businessName,
                'company_code' => $this->generateCompanyCode($businessName),
                'company_type' => $businessType,
                'scalability_level' => '1',
                'owner_name' => $memberName,
                'phone' => $phone,
                'email' => $email,
                'address_detail' => $addressDetail,
                'province_id' => $provinceId,
                'regency_id' => $regencyId,
                'district_id' => $districtId,
                'village_id' => $villageId,
                'is_active' => 1
            ];
            
            $companyId = $this->companyModel->create($companyData);
            
            if ($companyId) {
                // Create main branch (same as company for individual business)
                $branchData = [
                    'company_id' => $companyId,
                    'branch_name' => $businessName . ' (Cabang Utama)',
                    'branch_code' => $this->generateBranchCode($businessName, 'MAIN'),
                    'branch_type' => $businessType,
                    'business_segment' => 'ultra_mikro',
                    'owner_name' => $memberName,
                    'phone' => $phone,
                    'email' => $email,
                    'is_active' => 1
                ];
                
                $branchId = $this->branchModel->create($branchData);
                
                if ($branchId) {
                    // Create member as owner
                    $memberData = [
                        'branch_id' => $branchId,
                        'member_code' => $memberCode,
                        'member_name' => $memberName,
                        'position' => $position,
                        'phone' => $phone,
                        'email' => $email,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'is_active' => 1
                    ];
                    
                    $memberId = $this->memberModel->create($memberData);
                    
                    if ($memberId) {
                        // Auto-login
                        $this->autoLogin($memberId, $companyId, $branchId, $position);
                        header('Location: index.php?page=dashboard');
                        exit;
                    } else {
                        $error = 'Gagal membuat akun pengguna';
                    }
                } else {
                    $error = 'Gagal membuat cabang utama';
                }
            } else {
                $error = 'Gagal membuat perusahaan';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan saat mendaftarkan usaha: ' . $e->getMessage();
        }
    }
    
    /**
     * Register Join Company
     */
    private function registerJoinCompany() {
        $companyCode = $_POST['company_code'] ?? '';
        $memberCode = $_POST['member_code'] ?? '';
        $memberName = $_POST['member_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $position = $_POST['position'] ?? 'staff';
        
        if (empty($companyCode) || empty($memberCode) || empty($password)) {
            $error = 'Kode perusahaan, kode pengguna, dan password harus diisi';
            return;
        }
        
        // Find company by code
        $company = $this->companyModel->getByCode($companyCode);
        
        if (!$company) {
            $error = 'Kode perusahaan tidak ditemukan';
            return;
        }
        
        // Get first branch of the company
        $branches = $this->branchModel->getByCompany($company['id_company']);
        $branch = $branches[0] ?? null;
        
        if (!$branch) {
            $error = 'Perusahaan tidak memiliki cabang';
            return;
        }
        
        try {
            // Create member
            $memberData = [
                'branch_id' => $branch['id_branch'],
                'member_code' => $memberCode,
                'member_name' => $memberName,
                'position' => $position,
                'phone' => $phone,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'is_active' => 1
            ];
            
            $memberId = $this->memberModel->create($memberData);
            
            if ($memberId) {
                // Auto-login
                $this->autoLogin($memberId, $company['id_company'], $branch['id_branch'], $position);
                header('Location: index.php?page=dashboard');
                exit;
            } else {
                $error = 'Gagal membuat akun pengguna';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan saat mendaftarkan: ' . $e->getMessage();
        }
    }
    
    /**
     * Register Create Company
     */
    private function registerCreateCompany() {
        $companyName = $_POST['company_name'] ?? '';
        $companyType = $_POST['company_type'] ?? 'pusat';
        $memberCode = $_POST['member_code'] ?? '';
        $memberName = $_POST['member_name'] ?? $companyName;
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $position = $_POST['position'] ?? 'owner';
        $addressDetail = $_POST['address_detail'] ?? '';
        $provinceId = $_POST['province_id'] ?? '';
        $regencyId = $_POST['regency_id'] ?? '';
        $districtId = $_POST['district_id'] ?? '';
        $villageId = $_POST['village_id'] ?? '';
        
        if (empty($companyName) || empty($memberCode) || empty($password)) {
            $error = 'Nama perusahaan, kode pengguna, dan password harus diisi';
            return;
        }
        
        try {
            // Create company
            $companyData = [
                'company_name' => $companyName,
                'company_code' => $this->generateCompanyCode($companyName),
                'company_type' => $companyType,
                'scalability_level' => '3',
                'owner_name' => $memberName,
                'phone' => $phone,
                'email' => $email,
                'address_detail' => $addressDetail,
                'province_id' => $provinceId,
                'regency_id' => $regencyId,
                'district_id' => $districtId,
                'village_id' => $villageId,
                'is_active' => 1
            ];
            
            $companyId = $this->companyModel->create($companyData);
            
            if ($companyId) {
                // Create main branch
                $branchData = [
                    'company_id' => $companyId,
                    'branch_name' => $companyName . ' (Cabang Pusat)',
                    'branch_code' => $this->generateBranchCode($companyName, 'HQ'),
                    'branch_type' => $companyType,
                    'business_segment' => 'menengah',
                    'owner_name' => $memberName,
                    'phone' => $phone,
                    'email' => $email,
                    'is_active' => 1
                ];
                
                $branchId = $this->branchModel->create($branchData);
                
                if ($branchId) {
                    // Create member as owner
                    $memberData = [
                        'branch_id' => $branchId,
                        'member_code' => $memberCode,
                        'member_name' => $memberName,
                        'position' => $position,
                        'phone' => $phone,
                        'email' => $email,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'is_active' => 1
                    ];
                    
                    $memberId = $this->memberModel->create($memberData);
                    
                    if ($memberId) {
                        // Auto-login
                        $this->autoLogin($memberId, $companyId, $branchId, $position);
                        header('Location: index.php?page=dashboard');
                        exit;
                    } else {
                        $error = 'Gagal membuat akun pengguna';
                    }
                } else {
                    $error = 'Gagal membuat cabang pusat';
                }
            } else {
                $error = 'Gagal membuat perusahaan';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan saat mendaftarkan perusahaan: ' . $e->getMessage();
        }
    }
    
    /**
     * Generate company code
     */
    private function generateCompanyCode($companyName) {
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $companyName), 0, 3));
        $code .= rand(100, 999);
        
        // Ensure uniqueness
        while ($this->companyModel->getByCode($code)) {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $companyName), 0, 3));
            $code .= rand(100, 999);
        }
        
        return $code;
    }
    
    /**
     * Generate branch code
     */
    private function generateBranchCode($companyName, $suffix) {
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $companyName), 0, 3));
        $code .= '-' . $suffix;
        
        // Ensure uniqueness
        while ($this->branchModel->getByCode($code)) {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $companyName), 0, 3));
            $code .= '-' . $suffix . rand(10, 99);
        }
        
        return $code;
    }
    
    /**
     * Auto-login after registration
     */
    private function autoLogin($memberId, $companyId, $branchId, $position) {
        $member = $this->memberModel->getById($memberId);
        
        if ($member) {
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $member['id_member'];
            $_SESSION['username'] = $member['member_code'];
            $_SESSION['user_name'] = $member['member_name'];
            $_SESSION['user_email'] = $member['email'];
            $_SESSION['user_position'] = $member['position'];
            $_SESSION['business_role'] = $this->mapPositionToRole($member['position']);
            $_SESSION['app_role'] = $this->determineApplicationRole($member);
            $_SESSION['company_id'] = $companyId;
            $_SESSION['branch_id'] = $branchId;
            $_SESSION['logged_in'] = true;
        }
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
     * Map position to business role
     */
    private function mapPositionToRole($position) {
        $roleMapping = [
            'owner' => ROLE_COMPANY_OWNER,
            'manager' => ROLE_MANAGER,
            'cashier' => ROLE_CASHIER,
            'staff' => ROLE_STAFF,
            'security' => ROLE_STAFF
        ];
        
        return $roleMapping[$position] ?? ROLE_STAFF;
    }
    
    /**
     * Determine application role based on user
     */
    private function determineApplicationRole($user) {
        // Special application owners (hardcoded for now)
        $appOwners = ['admin', 'superadmin', 'developer'];
        
        if (in_array(strtolower($user['member_code']), $appOwners)) {
            return ROLE_APP_OWNER;
        }
        
        // System administrators
        $appAdmins = ['sysadmin', 'administrator'];
        if (in_array(strtolower($user['member_code']), $appAdmins)) {
            return ROLE_APP_ADMIN;
        }
        
        // Default: no application role (business users only)
        return null;
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
