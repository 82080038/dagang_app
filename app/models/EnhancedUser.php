<?php

namespace Model;

use Core\Model;

class EnhancedUser extends Model
{
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
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    /**
     * Get user with role assignments and permissions
     */
    public function getUserWithRoles($userId)
    {
        $sql = "SELECT 
                m.*,
                ur.id_role,
                ur.role_name,
                ur.role_code,
                ur.role_level,
                ura.assigned_at,
                ura.expires_at,
                ura.is_active as assignment_active,
                b.branch_name,
                c.company_name
                FROM {$this->table} m
                LEFT JOIN user_role_assignments ura ON m.id_member = ura.user_id AND ura.is_active = 1
                LEFT JOIN user_roles ur ON ura.role_id = ur.id_role
                LEFT JOIN branches b ON m.branch_id = b.id_branch
                LEFT JOIN companies c ON b.company_id = c.id_company
                WHERE m.id_member = :user_id";
        
        return $this->queryOne($sql, ['user_id' => $userId]);
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        $sql = "SELECT DISTINCT 
                p.permission_code,
                p.permission_name,
                p.permission_group,
                p.description
                FROM permissions p
                JOIN role_permissions rp ON p.id_permission = rp.permission_id
                JOIN user_role_assignments ura ON rp.role_id = ura.role_id
                WHERE ura.user_id = :user_id 
                AND ura.is_active = 1
                AND (ura.expires_at IS NULL OR ura.expires_at > CURRENT_TIMESTAMP)
                ORDER BY p.permission_group, p.permission_name";
        
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($userId, $permissionCode)
    {
        $sql = "SELECT COUNT(*) as has_permission
                FROM permissions p
                JOIN role_permissions rp ON p.id_permission = rp.permission_id
                JOIN user_role_assignments ura ON rp.role_id = ura.role_id
                WHERE ura.user_id = :user_id 
                AND p.permission_code = :permission_code
                AND ura.is_active = 1
                AND (ura.expires_at IS NULL OR ura.expires_at > CURRENT_TIMESTAMP)";
        
        $result = $this->queryOne($sql, ['user_id' => $userId, 'permission_code' => $permissionCode]);
        return $result['has_permission'] > 0;
    }

    /**
     * Get users with role-based filtering
     */
    public function getUsersWithRoles($limit = 10, $offset = 0, $search = '', $roleId = '', $branchId = '', $status = '')
    {
        $sql = "SELECT 
                m.*,
                ur.id_role,
                ur.role_name,
                ur.role_code,
                ur.role_level,
                b.branch_name,
                c.company_name,
                ura.assigned_at,
                CASE 
                    WHEN ura.expires_at IS NOT NULL AND ura.expires_at < CURRENT_TIMESTAMP THEN 'expired'
                    WHEN ura.is_active = 0 THEN 'inactive'
                    ELSE 'active'
                END as assignment_status
                FROM {$this->table} m
                LEFT JOIN user_role_assignments ura ON m.id_member = ura.user_id
                LEFT JOIN user_roles ur ON ura.role_id = ur.id_role
                LEFT JOIN branches b ON m.branch_id = b.id_branch
                LEFT JOIN companies c ON b.company_id = c.id_company
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (m.member_name LIKE :search OR m.member_code LIKE :search OR m.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($roleId) {
            $sql .= " AND ur.id_role = :role_id";
            $params['role_id'] = $roleId;
        }
        
        if ($branchId) {
            $sql .= " AND m.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        if ($status !== '') {
            if ($status === 'active') {
                $sql .= " AND m.is_active = 1";
            } elseif ($status === 'inactive') {
                $sql .= " AND m.is_active = 0";
            }
        }
        
        $sql .= " ORDER BY m.member_name ASC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }

    /**
     * Get total count for pagination
     */
    public function getUsersCount($search = '', $roleId = '', $branchId = '', $status = '')
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} m
                LEFT JOIN user_role_assignments ura ON m.id_member = ura.user_id
                LEFT JOIN user_roles ur ON ura.role_id = ur.id_role
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (m.member_name LIKE :search OR m.member_code LIKE :search OR m.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($roleId) {
            $sql .= " AND ur.id_role = :role_id";
            $params['role_id'] = $roleId;
        }
        
        if ($branchId) {
            $sql .= " AND m.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        if ($status !== '') {
            if ($status === 'active') {
                $sql .= " AND m.is_active = 1";
            } elseif ($status === 'inactive') {
                $sql .= " AND m.is_active = 0";
            }
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId, $assignedBy = null, $expiresAt = null)
    {
        // Deactivate existing assignments for this user
        $this->query("UPDATE user_role_assignments SET is_active = 0 WHERE user_id = :user_id", ['user_id' => $userId]);
        
        // Create new assignment
        $sql = "INSERT INTO user_role_assignments 
                (user_id, role_id, assigned_by, expires_at, is_active) 
                VALUES (:user_id, :role_id, :assigned_by, :expires_at, 1)";
        
        return $this->query($sql, [
            'user_id' => $userId,
            'role_id' => $roleId,
            'assigned_by' => $assignedBy,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Remove role from user
     */
    public function removeRole($userId, $roleId)
    {
        $sql = "UPDATE user_role_assignments 
                SET is_active = 0 
                WHERE user_id = :user_id AND role_id = :role_id";
        
        return $this->query($sql, ['user_id' => $userId, 'role_id' => $roleId]);
    }

    /**
     * Get user activity log
     */
    public function getUserActivity($userId, $limit = 50, $offset = 0, $activityType = '', $startDate = '', $endDate = '')
    {
        $sql = "SELECT * FROM user_activity_log 
                WHERE user_id = :user_id";
        
        $params = ['user_id' => $userId];
        
        if ($activityType) {
            $sql .= " AND activity_type = :activity_type";
            $params['activity_type'] = $activityType;
        }
        
        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }

    /**
     * Log user activity
     */
    public function logActivity($userId, $activityType, $description, $moduleName = '', $actionDetails = null, $ipAddress = null, $userAgent = null, $sessionId = null)
    {
        $sql = "INSERT INTO user_activity_log 
                (user_id, activity_type, activity_description, module_name, action_details, ip_address, user_agent, session_id) 
                VALUES (:user_id, :activity_type, :activity_description, :module_name, :action_details, :ip_address, :user_agent, :session_id)";
        
        return $this->query($sql, [
            'user_id' => $userId,
            'activity_type' => $activityType,
            'activity_description' => $description,
            'module_name' => $moduleName,
            'action_details' => $actionDetails ? json_encode($actionDetails) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Get user preferences
     */
    public function getUserPreferences($userId)
    {
        $sql = "SELECT preference_key, preference_value, preference_type 
                FROM user_preferences 
                WHERE user_id = :user_id";
        
        $results = $this->query($sql, ['user_id' => $userId]);
        $preferences = [];
        
        foreach ($results as $row) {
            $preferences[$row['preference_key']] = $this->castPreferenceValue($row['preference_value'], $row['preference_type']);
        }
        
        return $preferences;
    }

    /**
     * Set user preference
     */
    public function setUserPreference($userId, $key, $value, $type = 'string')
    {
        $sql = "INSERT INTO user_preferences (user_id, preference_key, preference_value, preference_type)
                VALUES (:user_id, :preference_key, :preference_value, :preference_type)
                ON DUPLICATE KEY UPDATE preference_value = :preference_value, preference_type = :preference_type, updated_at = CURRENT_TIMESTAMP";
        
        return $this->query($sql, [
            'user_id' => $userId,
            'preference_key' => $key,
            'preference_value' => is_array($value) || is_object($value) ? json_encode($value) : $value,
            'preference_type' => $type
        ]);
    }

    /**
     * Cast preference value to appropriate type
     */
    private function castPreferenceValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
                return (int)$value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Create user with role assignment
     */
    public function createUserWithRole($userData, $roleId, $assignedBy = null)
    {
        $this->beginTransaction();
        
        try {
            // Create user
            $userId = $this->create($userData);
            
            if (!$userId) {
                throw new \Exception('Failed to create user');
            }
            
            // Assign role
            $this->assignRole($userId, $roleId, $assignedBy);
            
            // Log activity
            $this->logActivity($userId, 'create', "User account created: {$userData['member_name']} ({$userData['member_code']})", 'user_management', [
                'member_id' => $userId,
                'member_code' => $userData['member_code'],
                'member_name' => $userData['member_name'],
                'email' => $userData['email'] ?? '',
                'position' => $userData['position'] ?? '',
                'role_id' => $roleId
            ]);
            
            $this->commit();
            return $userId;
            
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Update user with activity logging
     */
    public function updateUserWithActivity($userId, $userData, $updatedBy = null)
    {
        // Get old data for comparison
        $oldData = $this->getById($userId);
        
        // Update user
        $success = $this->update($userId, $userData);
        
        if ($success && $oldData) {
            // Check what changed
            $changes = [];
            foreach ($userData as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = ['old' => $oldData[$key], 'new' => $value];
                }
            }
            
            if (!empty($changes)) {
                $this->logActivity($userId, 'update', "User account updated: {$oldData['member_name']} ({$oldData['member_code']})", 'user_management', [
                    'member_id' => $userId,
                    'member_code' => $oldData['member_code'],
                    'changes' => $changes,
                    'updated_by' => $updatedBy
                ]);
            }
        }
        
        return $success;
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics()
    {
        $sql = "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN is_online = 1 THEN 1 ELSE 0 END) as online_users,
                COUNT(DISTINCT ur.id_role) as unique_roles_assigned,
                COUNT(DISTINCT m.branch_id) as branches_with_users
                FROM {$this->table} m
                LEFT JOIN user_role_assignments ura ON m.id_member = ura.user_id AND ura.is_active = 1
                LEFT JOIN user_roles ur ON ura.role_id = ur.id_role";
        
        return $this->queryOne($sql);
    }

    /**
     * Get role distribution
     */
    public function getRoleDistribution()
    {
        $sql = "SELECT 
                ur.role_name,
                ur.role_code,
                COUNT(*) as user_count,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM user_role_assignments WHERE is_active = 1), 2) as percentage
                FROM user_role_assignments ura
                JOIN user_roles ur ON ura.role_id = ur.id_role
                WHERE ura.is_active = 1
                GROUP BY ur.id_role, ur.role_name, ur.role_code
                ORDER BY user_count DESC";
        
        return $this->query($sql);
    }

    /**
     * Get activity statistics
     */
    public function getActivityStatistics($days = 30)
    {
        $sql = "SELECT 
                activity_type,
                COUNT(*) as count,
                DATE(created_at) as activity_date
                FROM user_activity_log
                WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL :days DAY)
                GROUP BY activity_type, DATE(created_at)
                ORDER BY activity_date DESC, count DESC";
        
        return $this->query($sql, ['days' => $days]);
    }

    /**
     * Bulk import users
     */
    public function bulkImportUsers($usersData, $defaultRoleId, $assignedBy = null)
    {
        $results = [
            'success' => 0,
            'errors' => 0,
            'error_details' => []
        ];
        
        foreach ($usersData as $index => $userData) {
            try {
                // Validate required fields
                if (empty($userData['member_code']) || empty($userData['member_name'])) {
                    throw new \Exception("Row " . ($index + 1) . ": Member code and name are required");
                }
                
                // Check if member code already exists
                $existing = $this->queryOne("SELECT id_member FROM {$this->table} WHERE member_code = :member_code", ['member_code' => $userData['member_code']]);
                if ($existing) {
                    throw new \Exception("Row " . ($index + 1) . ": Member code already exists");
                }
                
                // Prepare user data
                $userRecord = [
                    'member_code' => trim($userData['member_code']),
                    'member_name' => trim($userData['member_name']),
                    'email' => trim($userData['email'] ?? ''),
                    'phone' => trim($userData['phone'] ?? ''),
                    'position' => trim($userData['position'] ?? 'staff'),
                    'branch_id' => (int)($userData['branch_id'] ?? 0),
                    'password_hash' => password_hash($userData['password'] ?? 'password123', PASSWORD_DEFAULT),
                    'join_date' => $userData['join_date'] ?? date('Y-m-d'),
                    'is_active' => 1,
                    'created_by' => $assignedBy
                ];
                
                // Create user with role
                $userId = $this->createUserWithRole($userRecord, $defaultRoleId, $assignedBy);
                
                if ($userId) {
                    $results['success']++;
                } else {
                    throw new \Exception("Failed to create user");
                }
                
            } catch (\Exception $e) {
                $results['errors']++;
                $results['error_details'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        return $results;
    }

    /**
     * Export users data
     */
    public function exportUsers($filters = [])
    {
        $sql = "SELECT 
                m.member_code,
                m.member_name,
                m.email,
                m.phone,
                m.position,
                m.join_date,
                m.is_active,
                m.last_login_at,
                b.branch_name,
                ur.role_name,
                ur.role_code,
                ura.assigned_at
                FROM {$this->table} m
                LEFT JOIN user_role_assignments ura ON m.id_member = ura.user_id AND ura.is_active = 1
                LEFT JOIN user_roles ur ON ura.role_id = ur.id_role
                LEFT JOIN branches b ON m.branch_id = b.id_branch
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['role_id'])) {
            $sql .= " AND ur.id_role = :role_id";
            $params['role_id'] = $filters['role_id'];
        }
        
        if (!empty($filters['branch_id'])) {
            $sql .= " AND m.branch_id = :branch_id";
            $params['branch_id'] = $filters['branch_id'];
        }
        
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $sql .= " AND m.is_active = 1";
            } elseif ($filters['status'] === 'inactive') {
                $sql .= " AND m.is_active = 0";
            }
        }
        
        $sql .= " ORDER BY m.member_name";
        
        return $this->query($sql, $params);
    }

    /**
     * Validate user data
     */
    public function validateUser($data, $userId = null)
    {
        $errors = [];
        
        // Required fields
        if (empty($data['member_name'])) {
            $errors['member_name'] = 'Member name is required';
        }
        
        if (empty($data['member_code'])) {
            $errors['member_code'] = 'Member code is required';
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Phone validation (basic)
        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone format';
        }
        
        // Check unique member code
        if (!empty($data['member_code'])) {
            $sql = "SELECT id_member FROM {$this->table} WHERE member_code = :member_code";
            $params = ['member_code' => $data['member_code']];
            
            if ($userId) {
                $sql .= " AND id_member != :user_id";
                $params['user_id'] = $userId;
            }
            
            $existing = $this->queryOne($sql, $params);
            if ($existing) {
                $errors['member_code'] = 'Member code already exists';
            }
        }
        
        // Check unique email
        if (!empty($data['email'])) {
            $sql = "SELECT id_member FROM {$this->table} WHERE email = :email";
            $params = ['email' => $data['email']];
            
            if ($userId) {
                $sql .= " AND id_member != :user_id";
                $params['user_id'] = $userId;
            }
            
            $existing = $this->queryOne($sql, $params);
            if ($existing) {
                $errors['email'] = 'Email already exists';
            }
        }
        
        return $errors;
    }
}
