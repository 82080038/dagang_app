<?php

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $fillable = [
        'username', 'email', 'full_name', 'password', 'role_id', 'branch_id', 
        'company_id', 'phone', 'address', 'is_active', 'created_by', 'updated_by'
    ];
    protected $hidden = ['password'];

    /**
     * Get users with filtering and pagination
     */
    public function getUsersWithFilters($page = 1, $limit = 10, $filters = [])
    {
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        // Add search filter
        if (!empty($filters['search'])) {
            $whereClause .= " AND (username LIKE :search OR email LIKE :search OR full_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Add role filter
        if (!empty($filters['role_filter'])) {
            $whereClause .= " AND role_id = :role_id";
            $params['role_id'] = $filters['role_filter'];
        }
        
        // Add branch filter
        if (!empty($filters['branch_filter'])) {
            $whereClause .= " AND branch_id = :branch_id";
            $params['branch_id'] = $filters['branch_filter'];
        }
        
        // Add status filter
        if ($filters['status_filter'] !== '') {
            $whereClause .= " AND is_active = :status_filter";
            $params['status_filter'] = $filters['status_filter'];
        }
        
        // Apply user's access control
        $userRole = $_SESSION['user_role'] ?? 'staff';
        $userCompanyId = $_SESSION['company_id'] ?? null;
        $userBranchId = $_SESSION['branch_id'] ?? null;
        
        // Non-admin users can only see users from their company/branch
        if ($userRole != 'super_admin' && $userRole != 'admin') {
            if ($userCompanyId) {
                $whereClause .= " AND company_id = :user_company_id";
                $params['user_company_id'] = $userCompanyId;
            }
            if ($userBranchId) {
                $whereClause .= " AND branch_id = :user_branch_id";
                $params['user_branch_id'] = $userBranchId;
            }
        }
        
        $sql = "SELECT u.*, r.name as role_name, b.branch_name, c.company_name
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN branches b ON u.branch_id = b.id_branch
                LEFT JOIN companies c ON u.company_id = c.id_company
                {$whereClause}
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }
    
    /**
     * Count total users with filters
     */
    public function countUsers($filters = [])
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        // Add search filter
        if (!empty($filters['search'])) {
            $whereClause .= " AND (username LIKE :search OR email LIKE :search OR full_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Add role filter
        if (!empty($filters['role_filter'])) {
            $whereClause .= " AND role_id = :role_id";
            $params['role_id'] = $filters['role_filter'];
        }
        
        // Add branch filter
        if (!empty($filters['branch_filter'])) {
            $whereClause .= " AND branch_id = :branch_id";
            $params['branch_id'] = $filters['branch_filter'];
        }
        
        // Add status filter
        if ($filters['status_filter'] !== '') {
            $whereClause .= " AND is_active = :status_filter";
            $params['status_filter'] = $filters['status_filter'];
        }
        
        // Apply user's access control
        $userRole = $_SESSION['user_role'] ?? 'staff';
        $userCompanyId = $_SESSION['company_id'] ?? null;
        $userBranchId = $_SESSION['branch_id'] ?? null;
        
        // Non-admin users can only see users from their company/branch
        if ($userRole != 'super_admin' && $userRole != 'admin') {
            if ($userCompanyId) {
                $whereClause .= " AND company_id = :user_company_id";
                $params['user_company_id'] = $userCompanyId;
            }
            if ($userBranchId) {
                $whereClause .= " AND branch_id = :user_branch_id";
                $params['user_branch_id'] = $userBranchId;
            }
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";
        
        $result = $this->queryOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    /**
     * Check if username exists
     */
    public function isUsernameExists($username, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = :username";
        $params = ['username' => $username];
        
        if ($excludeId) {
            $sql .= " AND id_user != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Check if email exists
     */
    public function isEmailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id_user != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Create user with validation
     */
    public function createUser($userData)
    {
        // Validate required fields
        $requiredFields = ['username', 'email', 'full_name', 'password', 'role_id'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                throw new Exception("Field {$field} is required");
            }
        }
        
        // Check for duplicates
        if ($this->isUsernameExists($userData['username'])) {
            throw new Exception('Username already exists');
        }
        
        if ($this->isEmailExists($userData['email'])) {
            throw new Exception('Email already exists');
        }
        
        // Hash password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Set defaults
        $userData['is_active'] = $userData['is_active'] ?? 1;
        $userData['created_at'] = date('Y-m-d H:i:s');
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($userData);
    }
    
    /**
     * Update user with validation
     */
    public function updateUser($userId, $userData)
    {
        // Check if user exists
        $existingUser = $this->getById($userId);
        if (!$existingUser) {
            throw new Exception('User not found');
        }
        
        // Check for username uniqueness if being updated
        if (isset($userData['username']) && $userData['username'] !== $existingUser['username']) {
            if ($this->isUsernameExists($userData['username'], $userId)) {
                throw new Exception('Username already exists');
            }
        }
        
        // Check for email uniqueness if being updated
        if (isset($userData['email']) && $userData['email'] !== $existingUser['email']) {
            if ($this->isEmailExists($userData['email'], $userId)) {
                throw new Exception('Email already exists');
            }
        }
        
        // Hash password if being updated
        if (!empty($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        
        // Set updated timestamp
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($userId, $userData);
    }
    
    /**
     * Delete user with validation
     */
    public function deleteUser($userId)
    {
        // Check if user exists
        $user = $this->getById($userId);
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Prevent deletion of super admin
        if ($user['role_id'] == 1) {
            throw new Exception('Cannot delete super admin user');
        }
        
        return $this->delete($userId);
    }
    
    /**
     * Get user with role and company/branch info
     */
    public function getUserWithDetails($userId)
    {
        $sql = "SELECT u.*, r.name as role_name, r.level as role_level,
                       b.branch_name, b.branch_location,
                       c.company_name, c.company_address
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN branches b ON u.branch_id = b.id_branch
                LEFT JOIN companies c ON u.company_id = c.id_company
                WHERE u.id_user = :id";
        
        return $this->queryOne($sql, ['id' => $userId]);
    }
    
    /**
     * Get user statistics
     */
    public function getUserStatistics()
    {
        $userRole = $_SESSION['user_role'] ?? 'staff';
        $userCompanyId = $_SESSION['company_id'] ?? null;
        $userBranchId = $_SESSION['branch_id'] ?? null;
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        // Apply user's access control
        if ($userRole != 'super_admin' && $userRole != 'admin') {
            if ($userCompanyId) {
                $whereClause .= " AND company_id = :user_company_id";
                $params['user_company_id'] = $userCompanyId;
            }
            if ($userBranchId) {
                $whereClause .= " AND branch_id = :user_branch_id";
                $params['user_branch_id'] = $userBranchId;
            }
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_users,
                    COUNT(CASE WHEN is_active = 0 THEN 1 END) as inactive_users,
                    role_id,
                    r.name as role_name
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                {$whereClause}
                GROUP BY role_id, r.name
                ORDER BY r.level";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get user activity log
     */
    public function getUserActivityLog($userId, $limit = 50)
    {
        $sql = "SELECT al.*, 
                       CASE WHEN al.activity_type = 'user_created' THEN 'User Created'
                            WHEN al.activity_type = 'user_updated' THEN 'User Updated'
                            WHEN al.activity_type = 'user_deleted' THEN 'User Deleted'
                            WHEN al.activity_type = 'user_status_toggled' THEN 'Status Changed'
                            ELSE al.activity_type
                       END as activity_display,
                       u.full_name as user_name
                FROM audit_logs al
                LEFT JOIN {$this->table} u ON al.entity_id = u.id_user
                WHERE al.entity_type = 'user' 
                  AND (al.entity_id = :user_id OR al.created_by = :user_id)
                ORDER BY al.created_at DESC
                LIMIT :limit";
        
        return $this->query($sql, ['user_id' => $userId, 'limit' => $limit]);
    }
    
    /**
     * Validate user data
     */
    public function validateUser($userData, $isUpdate = false)
    {
        $errors = [];
        
        // Username validation
        if (empty($userData['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($userData['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $userData['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores';
        }
        
        // Email validation
        if (empty($userData['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Full name validation
        if (empty($userData['full_name'])) {
            $errors['full_name'] = 'Full name is required';
        } elseif (strlen($userData['full_name']) < 2) {
            $errors['full_name'] = 'Full name must be at least 2 characters';
        }
        
        // Password validation (only for create)
        if (!$isUpdate) {
            if (empty($userData['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($userData['password']) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }
        }
        
        // Role validation
        if (empty($userData['role_id'])) {
            $errors['role_id'] = 'Role is required';
        } elseif (!is_numeric($userData['role_id'])) {
            $errors['role_id'] = 'Invalid role';
        }
        
        return $errors;
    }
    
    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        $sql = "SELECT p.permission_name, p.permission_group
                FROM user_permissions up
                JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = :user_id
                AND p.is_active = 1";
        
        $permissions = $this->query($sql, ['user_id' => $userId]);
        
        $permissionList = [];
        foreach ($permissions as $permission) {
            $permissionList[] = $permission['permission_name'];
        }
        
        return $permissionList;
    }
    
    /**
     * Assign permissions to user
     */
    public function assignPermissions($userId, $permissionIds)
    {
        // Remove existing permissions
        $this->execute("DELETE FROM user_permissions WHERE user_id = :user_id", ['user_id' => $userId]);
        
        // Add new permissions
        foreach ($permissionIds as $permissionId) {
            $this->execute(
                "INSERT INTO user_permissions (user_id, permission_id) VALUES (:user_id, :permission_id)",
                ['user_id' => $userId, 'permission_id' => $permissionId]
            );
        }
        
        return true;
    }
}
