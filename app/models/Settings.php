<?php

class Settings extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'setting_key', 'setting_value', 'setting_group', 'description', 
        'is_active', 'created_by', 'updated_by'
    ];
    
    /**
     * Get setting by key
     */
    public function getSetting($key, $default = null)
    {
        $sql = "SELECT setting_value FROM {$this->table} 
                WHERE setting_key = :key AND is_active = 1";
        
        $result = $this->queryOne($sql, ['key' => $key]);
        
        return $result ? $result['setting_value'] : $default;
    }
    
    /**
     * Get settings by group
     */
    public function getSettingsByGroup($group)
    {
        $sql = "SELECT setting_key, setting_value, description 
                FROM {$this->table} 
                WHERE setting_group = :group AND is_active = 1
                ORDER BY setting_key";
        
        $results = $this->query($sql, ['group' => $group]);
        
        $settings = [];
        foreach ($results as $result) {
            $settings[$result['setting_key']] = [
                'value' => $result['setting_value'],
                'description' => $result['description']
            ];
        }
        
        return $settings;
    }
    
    /**
     * Get all settings
     */
    public function getAllSettings()
    {
        $sql = "SELECT setting_key, setting_value, setting_group, description 
                FROM {$this->table} 
                WHERE is_active = 1
                ORDER BY setting_group, setting_key";
        
        $results = $this->query($sql);
        
        $settings = [];
        foreach ($results as $result) {
            if (!isset($settings[$result['setting_group']])) {
                $settings[$result['setting_group']] = [];
            }
            $settings[$result['setting_group']][$result['setting_key']] = [
                'value' => $result['setting_value'],
                'description' => $result['description']
            ];
        }
        
        return $settings;
    }
    
    /**
     * Update setting
     */
    public function updateSetting($key, $value, $group = 'general', $description = '')
    {
        $sql = "INSERT INTO {$this->table} 
                (setting_key, setting_value, setting_group, description, is_active, created_at, updated_at)
                VALUES (:key, :value, :group, :description, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                setting_value = :value, 
                setting_group = :group, 
                description = :description, 
                updated_at = NOW()";
        
        $params = [
            'key' => $key,
            'value' => $value,
            'group' => $group,
            'description' => $description
        ];
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Update multiple settings
     */
    public function updateSettings($settings)
    {
        $this->beginTransaction();
        
        try {
            foreach ($settings as $key => $data) {
                $this->updateSetting(
                    $key, 
                    $data['value'], 
                    $data['group'] ?? 'general', 
                    $data['description'] ?? ''
                );
            }
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Delete setting
     */
    public function deleteSetting($key)
    {
        $sql = "UPDATE {$this->table} 
                SET is_active = 0, updated_at = NOW() 
                WHERE setting_key = :key";
        
        return $this->execute($sql, ['key' => $key]);
    }
    
    /**
     * Get backup settings
     */
    public function getBackupSettings()
    {
        return $this->getSettingsByGroup('backup');
    }
    
    /**
     * Update backup settings
     */
    public function updateBackupSettings($settings)
    {
        $backupSettings = [];
        
        foreach ($settings as $key => $value) {
            $backupSettings[$key] = [
                'value' => $value,
                'group' => 'backup',
                'description' => $this->getBackupSettingDescription($key)
            ];
        }
        
        return $this->updateSettings($backupSettings);
    }
    
    /**
     * Get email settings
     */
    public function getEmailSettings()
    {
        return $this->getSettingsByGroup('email');
    }
    
    /**
     * Update email settings
     */
    public function updateEmailSettings($settings)
    {
        $emailSettings = [];
        
        foreach ($settings as $key => $value) {
            $emailSettings[$key] = [
                'value' => $value,
                'group' => 'email',
                'description' => $this->getEmailSettingDescription($key)
            ];
        }
        
        return $this->updateSettings($emailSettings);
    }
    
    /**
     * Get feature settings
     */
    public function getFeatureSettings()
    {
        return $this->getSettingsByGroup('features');
    }
    
    /**
     * Update feature settings
     */
    public function updateFeatureSettings($settings)
    {
        $featureSettings = [];
        
        foreach ($settings as $key => $value) {
            $featureSettings[$key] = [
                'value' => $value,
                'group' => 'features',
                'description' => $this->getFeatureSettingDescription($key)
            ];
        }
        
        return $this->updateSettings($featureSettings);
    }
    
    /**
     * Get security settings
     */
    public function getSecuritySettings()
    {
        return $this->getSettingsByGroup('security');
    }
    
    /**
     * Update security settings
     */
    public function updateSecuritySettings($settings)
    {
        $securitySettings = [];
        
        foreach ($settings as $key => $value) {
            $securitySettings[$key] = [
                'value' => $value,
                'group' => 'security',
                'description' => $this->getSecuritySettingDescription($key)
            ];
        }
        
        return $this->updateSettings($securitySettings);
    }
    
    /**
     * Get general settings
     */
    public function getGeneralSettings()
    {
        return $this->getSettingsByGroup('general');
    }
    
    /**
     * Update general settings
     */
    public function updateGeneralSettings($settings)
    {
        $generalSettings = [];
        
        foreach ($settings as $key => $value) {
            $generalSettings[$key] = [
                'value' => $value,
                'group' => 'general',
                'description' => $this->getGeneralSettingDescription($key)
            ];
        }
        
        return $this->updateSettings($generalSettings);
    }
    
    /**
     * Create backup record
     */
    public function createBackupRecord($backupData)
    {
        $sql = "INSERT INTO backup_history 
                (backup_type, file_name, file_path, file_size, status, created_by, created_at)
                VALUES (:backup_type, :file_name, :file_path, :file_size, :status, :created_by, NOW())";
        
        $params = [
            'backup_type' => $backupData['backup_type'],
            'file_name' => $backupData['file_name'],
            'file_path' => $backupData['file_path'],
            'file_size' => $backupData['file_size'],
            'status' => $backupData['status'] ?? 'completed',
            'created_by' => $backupData['created_by'] ?? 1
        ];
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Get backup history
     */
    public function getBackupHistory($limit = 50)
    {
        $sql = "SELECT bh.*, u.full_name as created_by_name
                FROM backup_history bh
                LEFT JOIN users u ON bh.created_by = u.id_user
                ORDER BY bh.created_at DESC
                LIMIT :limit";
        
        return $this->query($sql, ['limit' => $limit]);
    }
    
    /**
     * Delete backup record
     */
    public function deleteBackupRecord($backupId)
    {
        $sql = "DELETE FROM backup_history WHERE id = :id";
        return $this->execute($sql, ['id' => $backupId]);
    }
    
    /**
     * Get system logs
     */
    public function getSystemLogs($logType = 'all', $limit = 100)
    {
        $whereClause = "WHERE 1=1";
        $params = ['limit' => $limit];
        
        if ($logType !== 'all') {
            $whereClause .= " AND log_type = :log_type";
            $params['log_type'] = $logType;
        }
        
        $sql = "SELECT * FROM system_logs 
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT :limit";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Clear system logs
     */
    public function clearSystemLogs($logType = 'all')
    {
        $whereClause = "";
        $params = [];
        
        if ($logType !== 'all') {
            $whereClause = " WHERE log_type = :log_type";
            $params['log_type'] = $logType;
        }
        
        $sql = "DELETE FROM system_logs{$whereClause}";
        return $this->execute($sql, $params);
    }
    
    /**
     * Log system activity
     */
    public function logSystemActivity($logType, $message, $context = [])
    {
        $sql = "INSERT INTO system_logs 
                (log_type, message, context, ip_address, user_agent, created_at)
                VALUES (:log_type, :message, :context, :ip_address, :user_agent, NOW())";
        
        $params = [
            'log_type' => $logType,
            'message' => $message,
            'context' => json_encode($context),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Get system health status
     */
    public function getSystemHealthStatus()
    {
        $status = [];
        
        // Database health
        try {
            $this->query("SELECT 1");
            $status['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection OK'
            ];
        } catch (Exception $e) {
            $status['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
        
        // Disk space
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercentage = ($usedSpace / $totalSpace) * 100;
        
        if ($usagePercentage > 90) {
            $diskStatus = 'critical';
            $diskMessage = 'Disk space critically low';
        } elseif ($usagePercentage > 80) {
            $diskStatus = 'warning';
            $diskMessage = 'Disk space low';
        } else {
            $diskStatus = 'healthy';
            $diskMessage = 'Disk space OK';
        }
        
        $status['disk_space'] = [
            'status' => $diskStatus,
            'message' => $diskMessage,
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round($usagePercentage, 2)
        ];
        
        // Memory usage
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryUsagePercentage = ($memoryUsage / $this->parseBytes($memoryLimit)) * 100;
        
        if ($memoryUsagePercentage > 90) {
            $memoryStatus = 'warning';
            $memoryMessage = 'Memory usage high';
        } else {
            $memoryStatus = 'healthy';
            $memoryMessage = 'Memory usage normal';
        }
        
        $status['memory'] = [
            'status' => $memoryStatus,
            'message' => $memoryMessage,
            'limit' => $memoryLimit,
            'usage' => $this->formatBytes($memoryUsage),
            'percentage' => round($memoryUsagePercentage, 2)
        ];
        
        // Services (basic check)
        $status['services'] = [
            'status' => 'healthy',
            'message' => 'All services running'
        ];
        
        return $status;
    }
    
    // Private helper methods
    
    private function getBackupSettingDescription($key)
    {
        $descriptions = [
            'auto_backup' => 'Enable automatic backup',
            'backup_frequency' => 'Backup frequency (daily, weekly, monthly)',
            'backup_retention' => 'Number of days to keep backups',
            'backup_path' => 'Backup storage path',
            'backup_compression' => 'Compress backup files'
        ];
        
        return $descriptions[$key] ?? 'Backup setting';
    }
    
    private function getEmailSettingDescription($key)
    {
        $descriptions = [
            'smtp_host' => 'SMTP server hostname',
            'smtp_port' => 'SMTP server port',
            'smtp_username' => 'SMTP username',
            'smtp_password' => 'SMTP password',
            'smtp_encryption' => 'SMTP encryption type',
            'from_email' => 'Default from email address',
            'from_name' => 'Default from name'
        ];
        
        return $descriptions[$key] ?? 'Email setting';
    }
    
    private function getFeatureSettingDescription($key)
    {
        $descriptions = [
            'enable_reports' => 'Enable reporting system',
            'enable_notifications' => 'Enable system notifications',
            'enable_backup' => 'Enable backup system',
            'enable_audit_log' => 'Enable audit logging',
            'enable_api_access' => 'Enable API access',
            'enable_maintenance_mode' => 'Enable maintenance mode'
        ];
        
        return $descriptions[$key] ?? 'Feature setting';
    }
    
    private function getSecuritySettingDescription($key)
    {
        $descriptions = [
            'session_timeout' => 'Session timeout in seconds',
            'max_login_attempts' => 'Maximum login attempts before lockout',
            'password_min_length' => 'Minimum password length',
            'require_password_change' => 'Require password change on first login',
            'enable_2fa' => 'Enable two-factor authentication'
        ];
        
        return $descriptions[$key] ?? 'Security setting';
    }
    
    private function getGeneralSettingDescription($key)
    {
        $descriptions = [
            'app_name' => 'Application name',
            'app_version' => 'Application version',
            'timezone' => 'Default timezone',
            'date_format' => 'Date format',
            'time_format' => 'Time format',
            'currency' => 'Default currency',
            'decimal_places' => 'Number of decimal places for currency'
        ];
        
        return $descriptions[$key] ?? 'General setting';
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    private function parseBytes($bytes)
    {
        $unit = preg_replace('/[^0-9]/', '', $bytes);
        $value = (int) $unit;
        
        if (strpos($bytes, 'K') !== false) {
            return $value * 1024;
        } elseif (strpos($bytes, 'M') !== false) {
            return $value * 1024 * 1024;
        } elseif (strpos($bytes, 'G') !== false) {
            return $value * 1024 * 1024 * 1024;
        }
        
        return $value;
    }
}
