<?php
/**
 * Member Model
 * 
 * Model untuk mengelola data member/anggota/karyawan
 * Digunakan untuk authentication dan user management
 */

require_once __DIR__ . '/../core/Model.php';

class Member extends Model {
    protected $table = 'members';
    protected $primaryKey = 'id_member';
    protected $fillable = [
        'branch_id',
        'member_code',
        'member_name',
        'email',
        'phone',
        'password_hash',
        'position',
        'salary',
        'join_date',
        'is_active',
        'last_login_at',
        'last_activity_at',
        'session_id',
        'is_online',
        'push_notifications_enabled',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE UPPER(member_code) = UPPER(:username) 
                AND is_active = 1 
                LIMIT 1";
        
        $user = $this->queryOne($sql, ['username' => $username]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->update($user['id_member'], [
                'last_login_at' => date('Y-m-d H:i:s')
            ]);
            
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get member by code
     */
    public function getByUsername($username) {
        return $this->findOneBy('member_code', $username);
    }
    
    /**
     * Get members by branch
     */
    public function getByBranch($branchId) {
        return $this->findBy('branch_id', $branchId);
    }
    
    /**
     * Get active members
     */
    public function getActive() {
        return $this->findBy('is_active', 1);
    }
    
    /**
     * Get members by position
     */
    public function getByRole($role) {
        return $this->findBy('position', $role);
    }
    
    /**
     * Create new member
     */
    public function createMember($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        // Set default values
        $data['is_active'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    /**
     * Update member
     */
    public function updateMember($memberId, $data) {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        } else {
            unset($data['password']); // Don't update password if empty
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($memberId, $data);
    }
    
    /**
     * Check if member code exists
     */
    public function memberCodeExists($memberCode, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE member_code = :member_code";
        $params = ['member_code' => $memberCode];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get member statistics
     */
    public function getStatistics($branchId = null) {
        $sql = "SELECT 
                    COUNT(*) as total_members,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_members,
                    COUNT(CASE WHEN position = 'owner' THEN 1 END) as owner_count,
                    COUNT(CASE WHEN position = 'manager' THEN 1 END) as manager_count,
                    COUNT(CASE WHEN position = 'cashier' THEN 1 END) as cashier_count,
                    COUNT(CASE WHEN position = 'staff' THEN 1 END) as staff_count
                FROM {$this->table}";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " WHERE branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        return $this->queryOne($sql, $params);
    }
    
    /**
     * Get members with branch info
     */
    public function getWithBranch($limit = null) {
        $sql = "SELECT 
                    m.*,
                    b.branch_name,
                    c.company_name
                FROM {$this->table} m
                LEFT JOIN branches b ON m.branch_id = b.branch_id
                LEFT JOIN companies c ON b.company_id = c.company_id
                ORDER BY m.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        return $this->query($sql);
    }
    
    /**
     * Activate member
     */
    public function activate($id) {
        return $this->update($id, ['is_active' => 1]);
    }
    
    /**
     * Deactivate member
     */
    public function deactivate($id) {
        return $this->update($id, ['is_active' => 0]);
    }
    
    /**
     * Change password
     */
    public function changePassword($memberId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($memberId, [
            'password_hash' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get position options
     */
    public function getRoleOptions() {
        return [
            'owner' => 'Owner/Pemilik',
            'manager' => 'Manager',
            'cashier' => 'Kasir',
            'staff' => 'Staff/Karyawan',
            'security' => 'Security'
        ];
    }
    
    /**
     * Validate member data
     */
    public function validateMember($data) {
        $rules = [
            'member_code' => 'required|min:3|max:50',
            'member_name' => 'required|min:3|max:100',
            'email' => 'email',
            'phone' => 'min:10|max:15',
            'position' => 'required'
        ];
        
        $errors = $this->validate($data, $rules);
        
        // Check unique member code
        if (!empty($data['member_code'])) {
            if ($this->memberCodeExists($data['member_code'], $data['id_member'] ?? null)) {
                $errors['member_code'] = 'Member code already exists';
            }
        }
        
        // Validate password for new member
        if (empty($data['id_member']) && !empty($data['password_hash'])) {
            if (strlen($data['password_hash']) < 6) {
                $errors['password_hash'] = 'Password must be at least 6 characters';
            }
        }
        
        return $errors;
    }
    
    /**
     * Search members
     */
    public function search($keyword, $branchId = null) {
        $sql = "SELECT 
                    m.*,
                    b.branch_name,
                    c.company_name
                FROM {$this->table} m
                LEFT JOIN branches b ON m.branch_id = b.branch_id
                LEFT JOIN companies c ON b.company_id = c.id_company
                WHERE (m.member_code LIKE :keyword 
                    OR m.member_name LIKE :keyword 
                    OR m.email LIKE :keyword)";
        
        $params = ['keyword' => '%' . $keyword . '%'];
        
        if ($branchId) {
            $sql .= " AND m.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY m.member_name";
        
        return $this->query($sql, $params);
    }
}

?>
