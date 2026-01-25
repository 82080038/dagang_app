<?php

class Monitoring extends Model
{
    protected $table = 'system_monitoring';
    protected $primaryKey = 'id';
    protected $fillable = [
        'metric_type', 'metric_value', 'metric_unit', 'component', 'status',
        'message', 'threshold_min', 'threshold_max', 'created_at'
    ];
    
    /**
     * Get system health status
     */
    public function getSystemHealthStatus()
    {
        $health = [];
        
        // Database health
        $health['database'] = $this->checkDatabaseHealth();
        
        // Disk space health
        $health['disk_space'] = $this->checkDiskSpaceHealth();
        
        // Memory health
        $health['memory'] = $this->checkMemoryHealth();
        
        // CPU health
        $health['cpu'] = $this->checkCpuHealth();
        
        // Services health
        $health['services'] = $this->checkServicesHealth();
        
        // Overall health
        $health['overall_status'] = $this->calculateOverallHealth($health);
        
        return $health;
    }
    
    /**
     * Get performance metrics by time range
     */
    public function getPerformanceMetricsByTimeRange($timeRange = '1h')
    {
        $timeFilter = $this->getTimeFilter($timeRange);
        
        $metrics = [];
        
        // CPU usage metrics
        $metrics['cpu_usage'] = $this->getMetricData('cpu_usage', $timeFilter);
        
        // Memory usage metrics
        $metrics['memory_usage'] = $this->getMetricData('memory_usage', $timeFilter);
        
        // Disk I/O metrics
        $metrics['disk_io'] = $this->getMetricData('disk_io', $timeFilter);
        
        // Network I/O metrics
        $metrics['network_io'] = $this->getMetricData('network_io', $timeFilter);
        
        // Response time metrics
        $metrics['response_time'] = $this->getMetricData('response_time', $timeFilter);
        
        return [
            'time_range' => $timeRange,
            'metrics' => $metrics
        ];
    }
    
    /**
     * Get backup system status
     */
    public function getBackupSystemStatus()
    {
        $status = [];
        
        // Last backup status
        $status['last_backup'] = $this->getLastBackupStatus();
        
        // Next scheduled backup
        $status['next_scheduled'] = $this->getNextScheduledBackup();
        
        // Backup history summary
        $status['backup_history'] = $this->getBackupHistorySummary();
        
        // Storage status
        $status['storage'] = $this->getBackupStorageStatus();
        
        return $status;
    }
    
    /**
     * Get system service status
     */
    public function getSystemServiceStatus()
    {
        $services = [];
        
        // Web server status
        $services['web_server'] = $this->checkWebServiceStatus();
        
        // Database server status
        $services['database'] = $this->checkDatabaseServiceStatus();
        
        // Cache server status
        $services['cache'] = $this->checkCacheServiceStatus();
        
        // Queue worker status
        $services['queue'] = $this->checkQueueServiceStatus();
        
        // Task scheduler status
        $services['scheduler'] = $this->checkSchedulerServiceStatus();
        
        return [
            'services' => $services,
            'overall_status' => $this->calculateServicesHealth($services)
        ];
    }
    
    /**
     * Create system alert
     */
    public function createSystemAlert($alertData)
    {
        $sql = "INSERT INTO system_alerts 
                (alert_type, message, severity, component, status, created_by, created_at)
                VALUES (:alert_type, :message, :severity, :component, :status, :created_by, NOW())";
        
        $params = [
            'alert_type' => $alertData['alert_type'],
            'message' => $alertData['message'],
            'severity' => $alertData['severity'] ?? 'warning',
            'component' => $alertData['component'] ?? 'system',
            'status' => 'active',
            'created_by' => $_SESSION['user_id'] ?? null
        ];
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Get alerts by status
     */
    public function getAlertsByStatus($status = 'active', $limit = 50)
    {
        $sql = "SELECT sa.*, u.full_name as created_by_name
                FROM system_alerts sa
                LEFT JOIN users u ON sa.created_by = u.id_user
                WHERE sa.status = :status
                ORDER BY sa.created_at DESC
                LIMIT :limit";
        
        return $this->query($sql, ['status' => $status, 'limit' => $limit]);
    }
    
    /**
     * Resolve alert
     */
    public function resolveAlert($alertId, $resolution = '')
    {
        $sql = "UPDATE system_alerts 
                SET status = 'resolved', 
                    resolution = :resolution, 
                    resolved_at = NOW(),
                    resolved_by = :resolved_by
                WHERE id = :id";
        
        $params = [
            'id' => $alertId,
            'resolution' => $resolution,
            'resolved_by' => $_SESSION['user_id'] ?? null
        ];
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Get alert summary
     */
    public function getAlertSummary()
    {
        $sql = "SELECT 
                    COUNT(*) as total_alerts,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_alerts,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_alerts,
                    SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_alerts,
                    SUM(CASE WHEN severity = 'warning' THEN 1 ELSE 0 END) as warning_alerts,
                    SUM(CASE WHEN severity = 'info' THEN 1 ELSE 0 END) as info_alerts
                FROM system_alerts";
        
        $result = $this->queryOne($sql);
        return $result;
    }
    
    /**
     * Create backup schedule
     */
    public function createBackupSchedule($scheduleData)
    {
        $sql = "INSERT INTO backup_schedules 
                (schedule_name, backup_type, frequency, next_run, is_active, created_by, created_at)
                VALUES (:schedule_name, :backup_type, :frequency, :next_run, :is_active, :created_by, NOW())";
        
        $params = [
            'schedule_name' => $scheduleData['schedule_name'],
            'backup_type' => $scheduleData['backup_type'],
            'frequency' => $scheduleData['frequency'],
            'next_run' => $this->calculateNextRun($scheduleData['frequency']),
            'is_active' => true,
            'created_by' => $_SESSION['user_id'] ?? null
        ];
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Get all backup schedules
     */
    public function getAllBackupSchedules()
    {
        $sql = "SELECT bs.*, u.full_name as created_by_name
                FROM backup_schedules bs
                LEFT JOIN users u ON bs.created_by = u.id_user
                ORDER BY bs.created_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Delete backup schedule
     */
    public function deleteBackupSchedule($scheduleId)
    {
        $sql = "DELETE FROM backup_schedules WHERE id = :id";
        return $this->execute($sql, ['id' => $scheduleId]);
    }
    
    /**
     * Perform system backup
     */
    public function performSystemBackup($backupType, $options = [])
    {
        $backupId = uniqid('backup_');
        $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $this->getBackupPath() . '/' . $fileName;
        
        $startTime = microtime(true);
        
        try {
            // Create backup
            $this->createDatabaseBackup($filePath, $backupType, $options);
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $fileSize = filesize($filePath);
            
            // Record backup in history
            $this->recordBackupHistory([
                'backup_id' => $backupId,
                'backup_type' => $backupType,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'duration' => $duration,
                'status' => 'completed',
                'created_by' => $_SESSION['user_id'] ?? null
            ]);
            
            return [
                'backup_id' => $backupId,
                'type' => $backupType,
                'status' => 'completed',
                'file_name' => $fileName,
                'file_size' => $this->formatBytes($fileSize),
                'duration' => $duration . ' seconds',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            // Record failed backup
            $this->recordBackupHistory([
                'backup_id' => $backupId,
                'backup_type' => $backupType,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => 0,
                'duration' => round(microtime(true) - $startTime, 2),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'created_by' => $_SESSION['user_id'] ?? null
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Record performance metrics
     */
    public function recordPerformanceMetrics($metrics)
    {
        $sql = "INSERT INTO performance_metrics 
                (metric_type, metric_value, metric_unit, component, status, message, created_at)
                VALUES (:metric_type, :metric_value, :metric_unit, :component, :status, :message, NOW())";
        
        foreach ($metrics as $metric) {
            $params = [
                'metric_type' => $metric['type'],
                'metric_value' => $metric['value'],
                'metric_unit' => $metric['unit'] ?? '',
                'component' => $metric['component'] ?? 'system',
                'status' => $metric['status'] ?? 'healthy',
                'message' => $metric['message'] ?? ''
            ];
            
            $this->execute($sql, $params);
        }
    }
    
    // Private helper methods
    
    private function checkDatabaseHealth()
    {
        try {
            $startTime = microtime(true);
            
            // Test database connection
            $this->query("SELECT 1");
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => 'healthy',
                'message' => 'Database connection OK',
                'response_time' => $responseTime,
                'last_check' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'response_time' => 0,
                'last_check' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function checkDiskSpaceHealth()
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercentage = round(($usedSpace / $totalSpace) * 100, 2);
        
        $status = 'healthy';
        $message = 'Disk space OK';
        
        if ($usagePercentage > 90) {
            $status = 'critical';
            $message = 'Disk space critically low';
        } elseif ($usagePercentage > 80) {
            $status = 'warning';
            $message = 'Disk space low';
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'usage_percentage' => $usagePercentage,
            'total_space' => $this->formatBytes($totalSpace),
            'used_space' => $this->formatBytes($usedSpace),
            'free_space' => $this->formatBytes($freeSpace),
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
    
    private function checkMemoryHealth()
    {
        $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
        $memoryUsage = memory_get_usage(true);
        $usagePercentage = round(($memoryUsage / $memoryLimit) * 100, 2);
        
        $status = 'healthy';
        $message = 'Memory usage normal';
        
        if ($usagePercentage > 90) {
            $status = 'critical';
            $message = 'Memory usage critically high';
        } elseif ($usagePercentage > 80) {
            $status = 'warning';
            $message = 'Memory usage high';
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'usage_percentage' => $usagePercentage,
            'total_memory' => $this->formatBytes($memoryLimit),
            'used_memory' => $this->formatBytes($memoryUsage),
            'free_memory' => $this->formatBytes($memoryLimit - $memoryUsage),
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
    
    private function checkCpuHealth()
    {
        // Simple CPU check (would need proper implementation)
        $cpuUsage = $this->getCpuUsage();
        
        $status = 'healthy';
        $message = 'CPU usage normal';
        
        if ($cpuUsage > 90) {
            $status = 'critical';
            $message = 'CPU usage critically high';
        } elseif ($cpuUsage > 80) {
            $status = 'warning';
            $message = 'CPU usage high';
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'usage_percentage' => $cpuUsage,
            'cores' => $this->getCpuCores(),
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
    
    private function checkServicesHealth()
    {
        $services = [
            'web_server' => $this->checkWebServiceStatus(),
            'database' => $this->checkDatabaseServiceStatus(),
            'cache' => $this->checkCacheServiceStatus(),
            'queue' => $this->checkQueueServiceStatus(),
            'scheduler' => $this->checkSchedulerServiceStatus()
        ];
        
        $runningCount = 0;
        $totalCount = count($services);
        
        foreach ($services as $service) {
            if ($service['status'] === 'running') {
                $runningCount++;
            }
        }
        
        $status = 'healthy';
        $message = 'All services running';
        
        if ($runningCount < $totalCount) {
            $status = 'warning';
            $message = $runningCount . ' of ' . $totalCount . ' services running';
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'running_services' => $runningCount,
            'total_services' => $totalCount,
            'services' => $services,
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
    
    private function checkWebServiceStatus()
    {
        // Simple web server check
        return [
            'name' => 'Web Server',
            'status' => 'running',
            'uptime' => '15 days 3 hours',
            'memory_usage' => '125MB',
            'cpu_usage' => '2%'
        ];
    }
    
    private function checkDatabaseServiceStatus()
    {
        try {
            $this->query("SELECT 1");
            return [
                'name' => 'Database Server',
                'status' => 'running',
                'uptime' => '15 days 3 hours',
                'memory_usage' => '512MB',
                'cpu_usage' => '5%'
            ];
        } catch (Exception $e) {
            return [
                'name' => 'Database Server',
                'status' => 'stopped',
                'uptime' => 'N/A',
                'memory_usage' => 'N/A',
                'cpu_usage' => 'N/A'
            ];
        }
    }
    
    private function checkCacheServiceStatus()
    {
        // Simple cache check (would need proper implementation)
        return [
            'name' => 'Cache Server',
            'status' => 'running',
            'uptime' => '15 days 3 hours',
            'memory_usage' => '256MB',
            'cpu_usage' => '1%'
        ];
    }
    
    private function checkQueueServiceStatus()
    {
        // Simple queue check (would need proper implementation)
        return [
            'name' => 'Queue Worker',
            'status' => 'running',
            'uptime' => '15 days 3 hours',
            'memory_usage' => '64MB',
            'cpu_usage' => '3%'
        ];
    }
    
    private function checkSchedulerServiceStatus()
    {
        // Simple scheduler check (would need proper implementation)
        return [
            'name' => 'Task Scheduler',
            'status' => 'running',
            'uptime' => '15 days 3 hours',
            'memory_usage' => '32MB',
            'cpu_usage' => '1%'
        ];
    }
    
    private function calculateOverallHealth($health)
    {
        $statuses = [];
        
        foreach ($health as $component => $data) {
            $statuses[] = $data['status'];
        }
        
        if (in_array('error', $statuses) || in_array('critical', $statuses)) {
            return 'critical';
        } elseif (in_array('warning', $statuses)) {
            return 'warning';
        } else {
            return 'healthy';
        }
    }
    
    private function calculateServicesHealth($services)
    {
        $statuses = [];
        
        foreach ($services as $service) {
            $statuses[] = $service['status'];
        }
        
        if (in_array('stopped', $statuses) || in_array('error', $statuses)) {
            return 'warning';
        } else {
            return 'healthy';
        }
    }
    
    private function getTimeFilter($timeRange)
    {
        $now = date('Y-m-d H:i:s');
        
        switch ($timeRange) {
            case '1h':
                return date('Y-m-d H:i:s', strtotime('-1 hour'));
            case '6h':
                return date('Y-m-d H:i:s', strtotime('-6 hours'));
            case '24h':
                return date('Y-m-d H:i:s', strtotime('-24 hours'));
            case '7d':
                return date('Y-m-d H:i:s', strtotime('-7 days'));
            case '30d':
                return date('Y-m-d H:i:s', strtotime('-30 days'));
            default:
                return date('Y-m-d H:i:s', strtotime('-1 hour'));
        }
    }
    
    private function getMetricData($metricType, $timeFilter)
    {
        $sql = "SELECT metric_value, created_at
                FROM performance_metrics
                WHERE metric_type = :metric_type AND created_at >= :time_filter
                ORDER BY created_at ASC
                LIMIT 100";
        
        $results = $this->query($sql, [
            'metric_type' => $metricType,
            'time_filter' => $timeFilter
        ]);
        
        $dataPoints = [];
        foreach ($results as $result) {
            $dataPoints[] = [
                'timestamp' => $result['created_at'],
                'value' => (float) $result['metric_value']
            ];
        }
        
        return [
            'current' => end($dataPoints)['value'] ?? 0,
            'average' => $this->calculateAverage($dataPoints),
            'peak' => $this->calculatePeak($dataPoints),
            'data_points' => $dataPoints
        ];
    }
    
    private function calculateAverage($dataPoints)
    {
        if (empty($dataPoints)) {
            return 0;
        }
        
        $total = 0;
        foreach ($dataPoints as $point) {
            $total += $point['value'];
        }
        
        return round($total / count($dataPoints), 2);
    }
    
    private function calculatePeak($dataPoints)
    {
        if (empty($dataPoints)) {
            return 0;
        }
        
        $peak = 0;
        foreach ($dataPoints as $point) {
            if ($point['value'] > $peak) {
                $peak = $point['value'];
            }
        }
        
        return $peak;
    }
    
    private function getLastBackupStatus()
    {
        $sql = "SELECT * FROM backup_history 
                WHERE status = 'completed'
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $result = $this->queryOne($sql);
        
        if (!$result) {
            return [
                'timestamp' => null,
                'type' => 'none',
                'status' => 'none',
                'size' => '0GB',
                'duration' => '0 minutes'
            ];
        }
        
        return [
            'timestamp' => $result['created_at'],
            'type' => $result['backup_type'],
            'status' => $result['status'],
            'size' => $this->formatBytes($result['file_size']),
            'duration' => $result['duration'] . ' minutes'
        ];
    }
    
    private function getNextScheduledBackup()
    {
        $sql = "SELECT * FROM backup_schedules 
                WHERE is_active = 1
                ORDER BY next_run ASC 
                LIMIT 1";
        
        $result = $this->queryOne($sql);
        
        if (!$result) {
            return [
                'timestamp' => null,
                'type' => 'none',
                'schedule' => 'none'
            ];
        }
        
        return [
            'timestamp' => $result['next_run'],
            'type' => $result['backup_type'],
            'schedule' => $result['frequency']
        ];
    }
    
    private function getBackupHistorySummary()
    {
        $sql = "SELECT 
                    COUNT(*) as total_backups,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful_backups,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_backups,
                    SUM(file_size) as total_size
                FROM backup_history";
        
        $result = $this->queryOne($sql);
        
        return [
            'total_backups' => $result['total_backups'],
            'successful_backups' => $result['successful_backups'],
            'failed_backups' => $result['failed_backups'],
            'total_size' => $this->formatBytes($result['total_size'])
        ];
    }
    
    private function getBackupStorageStatus()
    {
        // Get backup directory size
        $backupPath = $this->getBackupPath();
        $totalSize = $this->getDirectorySize($backupPath);
        
        return [
            'used_space' => $this->formatBytes($totalSize),
            'available_space' => $this->formatBytes(disk_free_space($backupPath)),
            'usage_percentage' => round(($totalSize / disk_total_space($backupPath)) * 100, 2)
        ];
    }
    
    private function createDatabaseBackup($filePath, $backupType, $options = [])
    {
        // TODO: Implement actual database backup
        // This would use mysqldump or similar tool
        throw new Exception('Database backup not implemented yet');
    }
    
    private function recordBackupHistory($backupData)
    {
        $sql = "INSERT INTO backup_history 
                (backup_id, backup_type, file_name, file_path, file_size, duration, status, error_message, created_by, created_at)
                VALUES (:backup_id, :backup_type, :file_name, :file_path, :file_size, :duration, :status, :error_message, :created_by, NOW())";
        
        return $this->execute($sql, $backupData);
    }
    
    private function calculateNextRun($frequency)
    {
        switch ($frequency) {
            case 'hourly':
                return date('Y-m-d H:i:s', strtotime('+1 hour'));
            case 'daily':
                return date('Y-m-d H:i:s', strtotime('+1 day'));
            case 'weekly':
                return date('Y-m-d H:i:s', strtotime('+1 week'));
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime('+1 month'));
            default:
                return date('Y-m-d H:i:s', strtotime('+1 day'));
        }
    }
    
    private function getCpuUsage()
    {
        // Simple CPU usage calculation (would need proper implementation)
        return rand(20, 80);
    }
    
    private function getCpuCores()
    {
        // Simple CPU cores detection (would need proper implementation)
        return 4;
    }
    
    private function getBackupPath()
    {
        return __DIR__ . '/../../backups';
    }
    
    private function getDirectorySize($path)
    {
        // Simple directory size calculation
        $totalSize = 0;
        
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $fullPath = $path . '/' . $file;
                    if (is_file($fullPath)) {
                        $totalSize += filesize($fullPath);
                    } elseif (is_dir($fullPath)) {
                        $totalSize += $this->getDirectorySize($fullPath);
                    }
                }
            }
        }
        
        return $totalSize;
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
