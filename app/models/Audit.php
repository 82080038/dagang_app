<?php

class Audit extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'activity_type', 'description', 'entity_type', 'entity_id', 'user_id',
        'ip_address', 'user_agent', 'old_values', 'new_values', 'created_at'
    ];
    
    /**
     * Get audit logs with filtering and pagination
     */
    public function getAuditLogsWithFilters($page = 1, $limit = 50, $filters = [])
    {
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        // Add log type filter
        if (!empty($filters['log_type'])) {
            $whereClause .= " AND activity_type = :log_type";
            $params['log_type'] = $filters['log_type'];
        }
        
        // Add entity type filter
        if (!empty($filters['entity_type'])) {
            $whereClause .= " AND entity_type = :entity_type";
            $params['entity_type'] = $filters['entity_type'];
        }
        
        // Add entity ID filter
        if (!empty($filters['entity_id'])) {
            $whereClause .= " AND entity_id = :entity_id";
            $params['entity_id'] = $filters['entity_id'];
        }
        
        // Add user filter
        if (!empty($filters['user_id'])) {
            $whereClause .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        // Add date range filter
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        // Add search filter
        if (!empty($filters['search'])) {
            $whereClause .= " AND (description LIKE :search OR activity_type LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Apply user's access control
        $userRole = $_SESSION['user_role'] ?? 'staff';
        $userCompanyId = $_SESSION['company_id'] ?? null;
        $userBranchId = $_SESSION['branch_id'] ?? null;
        
        // Non-admin users can only see logs from their company/branch
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
        
        $sql = "SELECT al.*, u.full_name as user_name, u.username as user_username,
                       c.company_name, b.branch_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id_user
                LEFT JOIN companies c ON al.company_id = c.id_company
                LEFT JOIN branches b ON al.branch_id = b.id_branch
                {$whereClause}
                ORDER BY al.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }
    
    /**
     * Count total audit logs with filters
     */
    public function countAuditLogs($filters = [])
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        // Add log type filter
        if (!empty($filters['log_type'])) {
            $whereClause .= " AND activity_type = :log_type";
            $params['log_type'] = $filters['log_type'];
        }
        
        // Add entity type filter
        if (!empty($filters['entity_type'])) {
            $whereClause .= " AND entity_type = :entity_type";
            $params['entity_type'] = $filters['entity_type'];
        }
        
        // Add entity ID filter
        if (!empty($filters['entity_id'])) {
            $whereClause .= " AND entity_id = :entity_id";
            $params['entity_id'] = $filters['entity_id'];
        }
        
        // Add user filter
        if (!empty($filters['user_id'])) {
            $whereClause .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        // Add date range filter
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        // Add search filter
        if (!empty($filters['search'])) {
            $whereClause .= " AND (description LIKE :search OR activity_type LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Apply user's access control
        $userRole = $_SESSION['user_role'] ?? 'staff';
        $userCompanyId = $_SESSION['company_id'] ?? null;
        $userBranchId = $_SESSION['branch_id'] ?? null;
        
        // Non-admin users can only see logs from their company/branch
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
     * Get audit log by ID
     */
    public function getAuditLogById($logId)
    {
        $sql = "SELECT al.*, u.full_name as user_name, u.username as user_username,
                       c.company_name, b.branch_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id_user
                LEFT JOIN companies c ON al.company_id = c.id_company
                LEFT JOIN branches b ON al.branch_id = b.id_branch
                WHERE al.id = :id";
        
        return $this->queryOne($sql, ['id' => $logId]);
    }
    
    /**
     * Create audit log entry
     */
    public function createAuditLog($activityType, $description, $entityType, $entityId = null, $oldValues = null, $newValues = null)
    {
        $data = [
            'activity_type' => $activityType,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $_SESSION['user_id'] ?? null,
            'company_id' => $_SESSION['company_id'] ?? null,
            'branch_id' => $_SESSION['branch_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }
    
    /**
     * Get audit log statistics
     */
    public function getAuditLogStatistics()
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
        
        // Total logs
        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $result = $this->queryOne($sql, $params);
        $totalLogs = $result['total'] ?? 0;
        
        // Today's logs
        $sql = "SELECT COUNT(*) as today FROM {$this->table} {$whereClause} AND DATE(created_at) = CURDATE()";
        $result = $this->queryOne($sql, $params);
        $logsToday = $result['today'] ?? 0;
        
        // This week's logs
        $sql = "SELECT COUNT(*) as this_week FROM {$this->table} {$whereClause} AND YEARWEEK(created_at) = YEARWEEK(CURDATE())";
        $result = $this->queryOne($sql, $params);
        $logsThisWeek = $result['this_week'] ?? 0;
        
        // This month's logs
        $sql = "SELECT COUNT(*) as this_month FROM {$this->table} {$whereClause} AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        $result = $this->queryOne($sql, $params);
        $logsThisMonth = $result['this_month'] ?? 0;
        
        // Log types distribution
        $sql = "SELECT activity_type, COUNT(*) as count FROM {$this->table} {$whereClause} GROUP BY activity_type";
        $logTypes = $this->query($sql, $params);
        
        $logTypesArray = [];
        foreach ($logTypes as $type) {
            $logTypesArray[$type['activity_type']] = $type['count'];
        }
        
        // Top activities
        $sql = "SELECT activity_type, description, COUNT(*) as count 
                FROM {$this->table} {$whereClause} 
                GROUP BY activity_type, description 
                ORDER BY count DESC 
                LIMIT 10";
        $topActivities = $this->query($sql, $params);
        
        // Top users
        $sql = "SELECT u.full_name, u.username, COUNT(*) as count 
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id_user
                {$whereClause}
                GROUP BY al.user_id, u.full_name, u.username
                ORDER BY count DESC 
                LIMIT 10";
        $topUsers = $this->query($sql, $params);
        
        // Top entities
        $sql = "SELECT entity_type, COUNT(*) as count 
                FROM {$this->table} {$whereClause}
                GROUP BY entity_type
                ORDER BY count DESC 
                LIMIT 10";
        $topEntities = $this->query($sql, $params);
        
        return [
            'total_logs' => $totalLogs,
            'logs_today' => $logsToday,
            'logs_this_week' => $logsThisWeek,
            'logs_this_month' => $logsThisMonth,
            'log_types' => $logTypesArray,
            'top_activities' => $topActivities,
            'top_users' => $topUsers,
            'top_entities' => $topEntities
        ];
    }
    
    /**
     * Clear audit logs by filters
     */
    public function clearAuditLogs($filters)
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        // Add date range filter
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        // Add log type filter
        if (!empty($filters['log_type'])) {
            $whereClause .= " AND activity_type = :log_type";
            $params['log_type'] = $filters['log_type'];
        }
        
        // Add entity type filter
        if (!empty($filters['entity_type'])) {
            $whereClause .= " AND entity_type = :entity_type";
            $params['entity_type'] = $filters['entity_type'];
        }
        
        // Apply user's access control
        $userRole = $_SESSION['user_role'] ?? 'staff';
        $userCompanyId = $_SESSION['company_id'] ?? null;
        $userBranchId = $_SESSION['branch_id'] ?? null;
        
        // Non-admin users can only clear logs from their company/branch
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
        
        $sql = "DELETE FROM {$this->table} {$whereClause}";
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Generate compliance report
     */
    public function generateComplianceReport($reportType, $dateFrom, $dateTo)
    {
        $userRole = $_SESSION['user_role'] ?? 'staff';
        $userCompanyId = $_SESSION['company_id'] ?? null;
        $userBranchId = $_SESSION['branch_id'] ?? null;
        
        $whereClause = "WHERE created_at BETWEEN :date_from AND :date_to";
        $params = [
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
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
        
        switch ($reportType) {
            case 'summary':
                return $this->generateSummaryReport($whereClause, $params);
            case 'detailed':
                return $this->generateDetailedReport($whereClause, $params);
            case 'security':
                return $this->generateSecurityReport($whereClause, $params);
            default:
                return $this->generateSummaryReport($whereClause, $params);
        }
    }
    
    /**
     * Search audit logs
     */
    public function searchAuditLogs($query, $limit = 20)
    {
        $userRole = $_SESSION['user_role'] ?? 'staff';
        $userCompanyId = $_SESSION['company_id'] ?? null;
        $userBranchId = $_SESSION['branch_id'] ?? null;
        
        $whereClause = "WHERE (description LIKE :query OR activity_type LIKE :query)";
        $params = ['query' => '%' . $query . '%'];
        
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
        
        $sql = "SELECT al.*, u.full_name as user_name, u.username as user_username
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id_user
                {$whereClause}
                ORDER BY al.created_at DESC
                LIMIT :limit";
        
        $params['limit'] = $limit;
        
        return $this->query($sql, $params);
    }
    
    // Private helper methods
    
    private function generateSummaryReport($whereClause, $params)
    {
        // Total activities
        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $result = $this->queryOne($sql, $params);
        $totalActivities = $result['total'] ?? 0;
        
        // Unique users
        $sql = "SELECT COUNT(DISTINCT user_id) as unique_users FROM {$this->table} {$whereClause}";
        $result = $this->queryOne($sql, $params);
        $uniqueUsers = $result['unique_users'] ?? 0;
        
        // Security events
        $sql = "SELECT COUNT(*) as security_events FROM {$this->table} {$whereClause} AND activity_type IN ('login_failed', 'login_success', 'logout', 'password_change', 'permission_denied')";
        $result = $this->queryOne($sql, $params);
        $securityEvents = $result['security_events'] ?? 0;
        
        // System changes
        $sql = "SELECT COUNT(*) as system_changes FROM {$this->table} {$whereClause} AND activity_type IN ('settings_updated', 'feature_updated', 'system_config')";
        $result = $this->queryOne($sql, $params);
        $systemChanges = $result['system_changes'] ?? 0;
        
        // Data access
        $sql = "SELECT COUNT(*) as data_access FROM {$this->table} {$whereClause} AND activity_type IN ('user_created', 'user_updated', 'user_deleted', 'data_exported')";
        $result = $this->queryOne($sql, $params);
        $dataAccess = $result['data_access'] ?? 0;
        
        return [
            'report_type' => 'summary',
            'period' => [
                'from' => $params['date_from'],
                'to' => $params['date_to']
            ],
            'summary' => [
                'total_activities' => $totalActivities,
                'unique_users' => $uniqueUsers,
                'security_events' => $securityEvents,
                'system_changes' => $systemChanges,
                'data_access' => $dataAccess
            ]
        ];
    }
    
    private function generateDetailedReport($whereClause, $params)
    {
        // Get all logs for the period
        $sql = "SELECT al.*, u.full_name as user_name, u.username as user_username,
                       c.company_name, b.branch_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id_user
                LEFT JOIN companies c ON al.company_id = c.id_company
                LEFT JOIN branches b ON al.branch_id = b.id_branch
                {$whereClause}
                ORDER BY al.created_at DESC";
        
        $logs = $this->query($sql, $params);
        
        return [
            'report_type' => 'detailed',
            'period' => [
                'from' => $params['date_from'],
                'to' => $params['date_to']
            ],
            'details' => $logs
        ];
    }
    
    private function generateSecurityReport($whereClause, $params)
    {
        // Security-specific logs
        $securityWhere = $whereClause . " AND activity_type IN ('login_failed', 'login_success', 'logout', 'password_change', 'permission_denied', 'security_breach', 'suspicious_activity')";
        
        // Total security events
        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$securityWhere}";
        $result = $this->queryOne($sql, $params);
        $totalSecurityEvents = $result['total'] ?? 0;
        
        // Failed logins
        $sql = "SELECT COUNT(*) as failed_logins FROM {$this->table} {$securityWhere} AND activity_type = 'login_failed'";
        $result = $this->queryOne($sql, $params);
        $failedLogins = $result['failed_logins'] ?? 0;
        
        // Successful logins
        $sql = "SELECT COUNT(*) as successful_logins FROM {$this->table} {$securityWhere} AND activity_type = 'login_success'";
        $result = $this->queryOne($sql, $params);
        $successfulLogins = $result['successful_logins'] ?? 0;
        
        // Password changes
        $sql = "SELECT COUNT(*) as password_changes FROM {$this->table} {$securityWhere} AND activity_type = 'password_change'";
        $result = $this->queryOne($sql, $params);
        $passwordChanges = $result['password_changes'] ?? 0;
        
        // Permission denied
        $sql = "SELECT COUNT(*) as permission_denied FROM {$this->table} {$securityWhere} AND activity_type = 'permission_denied'";
        $result = $this->queryOne($sql, $params);
        $permissionDenied = $result['permission_denied'] ?? 0;
        
        // Get detailed security events
        $sql = "SELECT al.*, u.full_name as user_name, u.username as user_username,
                       c.company_name, b.branch_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id_user
                LEFT JOIN companies c ON al.company_id = c.id_company
                LEFT JOIN branches b ON al.branch_id = b.id_branch
                {$securityWhere}
                ORDER BY al.created_at DESC
                LIMIT 100";
        
        $securityEvents = $this->query($sql, $params);
        
        return [
            'report_type' => 'security',
            'period' => [
                'from' => $params['date_from'],
                'to' => $params['date_to']
            ],
            'summary' => [
                'total_security_events' => $totalSecurityEvents,
                'failed_logins' => $failedLogins,
                'successful_logins' => $successfulLogins,
                'password_changes' => $passwordChanges,
                'permission_denied' => $permissionDenied
            ],
            'details' => $securityEvents
        ];
    }
}
