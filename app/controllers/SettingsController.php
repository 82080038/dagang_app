<?php
require_once __DIR__ . '/../core/Controller.php';

class SettingsController extends Controller
{
    private $settingsModel;
    private $companyModel;
    private $branchModel;

    public function __construct()
    {
        parent::__construct();
        $this->settingsModel = new \Settings();
        $this->companyModel = new \Company();
        $this->branchModel = new \Branch();
    }

    /**
     * Display settings page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
            return;
        }

        // Check if user has permission to access settings
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $_SESSION['flash_error'] = 'You do not have permission to access settings';
            $this->redirect('dashboard');
            return;
        }

        $data = [
            'title' => 'System Settings',
            'user_role' => $this->getUserRole(),
            'user_company_id' => $this->getUserCompanyId(),
            'user_branch_id' => $this->getUserBranchId(),
            'settings' => $this->getAllSettings(),
            'companies' => $this->getAllCompanies(),
            'branches' => $this->getAllBranches(),
            'email_templates' => $this->getEmailTemplates(),
            'backup_settings' => $this->getBackupSettings(),
            'system_info' => $this->getSystemInfo()
        ];

        $this->render('settings/index', $data);
    }

    /**
     * Get all settings
     */
    public function getSettings()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $settings = $this->getAllSettings();
            
            $this->json([
                'status' => 'success',
                'data' => $settings
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to get settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update settings
     */
    public function updateSettings()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Validate settings data
            $this->validateSettingsData($data);

            // Update settings
            $this->updateSettingsData($data);

            // Log activity
            $this->settingsModel->logSystemActivity('settings_updated', 'System settings updated', [
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->json([
                'status' => 'success',
                'message' => 'Settings updated successfully'
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to update settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update company settings
     */
    public function updateCompanySettings()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Validate company settings
            $this->validateCompanySettings($data);

            // Update company settings
            $this->updateCompanyData($data);

            // Log activity
            $this->settingsModel->logSystemActivity('company_settings_updated', 'Company settings updated', [
                'company_id' => $data['company_id'] ?? 0,
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->json([
                'status' => 'success',
                'message' => 'Company settings updated successfully'
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to update company settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update email settings
     */
    public function updateEmailSettings()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Validate email settings
            $this->validateEmailSettings($data);

            // Update email settings
            $this->updateEmailData($data);

            // Test email connection if requested
            $testResult = null;
            if (isset($data['test_connection']) && $data['test_connection']) {
                $testResult = $this->testEmailConnection($data);
            }

            // Log activity
            $this->settingsModel->logSystemActivity('email_settings_updated', 'Email settings updated', [
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->json([
                'status' => 'success',
                'message' => 'Email settings updated successfully',
                'test_result' => $testResult
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to update email settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update backup settings
     */
    public function updateBackupSettings()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Validate backup settings
            $this->validateBackupSettings($data);

            // Update backup settings
            $this->updateBackupData($data);

            // Log activity
            $this->settingsModel->logSystemActivity('backup_settings_updated', 'Backup settings updated', [
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->json([
                'status' => 'success',
                'message' => 'Backup settings updated successfully'
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to update backup settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create backup
     */
    public function createBackup()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $backupType = $_POST['backup_type'] ?? 'full';
            $backupResult = $this->performBackup($backupType);

            // Log activity
            $this->settingsModel->logSystemActivity('backup_created', 'Backup created: ' . $backupType, [
                'backup_type' => $backupType,
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->json([
                'status' => 'success',
                'message' => 'Backup created successfully',
                'backup_file' => $backupResult
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to create backup: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get backup history
     */
    public function getBackupHistory()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $backups = $this->getBackupList();

            $this->json([
                'status' => 'success',
                'data' => $backups
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to get backup history: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download backup
     */
    public function downloadBackup()
    {
        $this->requireAuth();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $_SESSION['error'] = 'You do not have permission to download backups';
            $this->redirect('settings');
            return;
        }

        $backupFile = $_GET['file'] ?? '';

        try {
            $this->downloadBackupFile($backupFile);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to download backup: ' . $e->getMessage();
            $this->redirect('settings');
        }
    }

    /**
     * Delete backup
     */
    public function deleteBackup()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $backupFile = $_POST['file'] ?? '';

        try {
            $this->deleteBackupFile($backupFile);

            // Log activity
            $this->settingsModel->logSystemActivity('backup_deleted', 'Backup deleted: ' . $backupFile, [
                'backup_file' => $backupFile,
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->json([
                'status' => 'success',
                'message' => 'Backup deleted successfully'
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to delete backup: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get system logs
     */
    public function getSystemLogs()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $logType = $_GET['log_type'] ?? 'error';
        $limit = $_GET['limit'] ?? 100;

        try {
            $logs = $this->getLogs($logType, $limit);

            $this->json([
                'status' => 'success',
                'data' => $logs
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to get system logs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Clear system logs
     */
    public function clearSystemLogs()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $logType = $_POST['log_type'] ?? 'all';

        try {
            $this->clearLogs($logType);

            // Log activity
            $this->settingsModel->logSystemActivity('logs_cleared', 'System logs cleared: ' . $logType, [
                'log_type' => $logType,
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->json([
                'status' => 'success',
                'message' => 'System logs cleared successfully'
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to clear system logs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get system status
     */
    public function getSystemStatus()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $status = $this->getSystemHealthStatus();

            $this->json([
                'status' => 'success',
                'data' => $status
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to get system status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update feature toggles
     */
    public function updateFeatureToggles()
    {
        $this->requireAuthJson();
        
        if (!$this->hasAppPermission(ROLE_APP_ADMIN)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $this->updateFeatures($data);

            // Log activity
            $this->settingsModel->logSystemActivity('features_updated', 'Feature toggles updated', [
                'features' => $data,
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->json([
                'status' => 'success',
                'message' => 'Feature toggles updated successfully'
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to update feature toggles: ' . $e->getMessage()], 500);
        }
    }

    // Private helper methods

    private function getAllSettings()
    {
        return $this->settingsModel->getAllSettings();
    }

    private function getAllCompanies()
    {
        try {
            return $this->companyModel->getAll();
        } catch (Exception $e) {
            return [];
        }
    }

    private function getAllBranches()
    {
        try {
            return $this->branchModel->getAll();
        } catch (Exception $e) {
            return [];
        }
    }

    private function getEmailTemplates()
    {
        return [
            'welcome_email' => [
                'subject' => 'Welcome to Perdagangan System',
                'template' => 'Welcome {{user_name}}! Your account has been created successfully.'
            ],
            'password_reset' => [
                'subject' => 'Password Reset Request',
                'template' => 'Hi {{user_name}}, click here to reset your password: {{reset_link}}'
            ],
            'backup_notification' => [
                'subject' => 'Backup Completed',
                'template' => 'System backup has been completed successfully. File: {{backup_file}}'
            ]
        ];
    }

    private function getBackupSettings()
    {
        return [
            'auto_backup' => false,
            'backup_frequency' => 'daily',
            'backup_retention' => 30,
            'last_backup' => null,
            'next_backup' => null,
            'backup_size' => 0
        ];
    }

    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'mysql_version' => 'MySQL 8.0',
            'server_os' => PHP_OS,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'disk_space' => $this->getDiskSpace(),
            'uptime' => $this->getServerUptime()
        ];
    }

    private function getDiskSpace()
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        
        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }

    private function getServerUptime()
    {
        // Simple uptime calculation
        $uptime = shell_exec('uptime');
        return $uptime ?: 'Unknown';
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function validateSettingsData($data)
    {
        $errors = [];
        
        // Validate general settings
        if (isset($data['general'])) {
            if (empty($data['general']['app_name'])) {
                $errors['app_name'] = 'Application name is required';
            }
            
            if (empty($data['general']['timezone'])) {
                $errors['timezone'] = 'Timezone is required';
            }
        }
        
        // Validate security settings
        if (isset($data['security'])) {
            if (!is_numeric($data['security']['session_timeout']) || $data['security']['session_timeout'] < 300) {
                $errors['session_timeout'] = 'Session timeout must be at least 300 seconds';
            }
            
            if (!is_numeric($data['security']['max_login_attempts']) || $data['security']['max_login_attempts'] < 1) {
                $errors['max_login_attempts'] = 'Max login attempts must be at least 1';
            }
        }
        
        if (!empty($errors)) {
            throw new Exception('Validation failed: ' . implode(', ', $errors));
        }
    }

    private function validateCompanySettings($data)
    {
        // TODO: Implement company settings validation
        return true;
    }

    private function validateEmailSettings($data)
    {
        // TODO: Implement email settings validation
        return true;
    }

    private function validateBackupSettings($data)
    {
        // TODO: Implement backup settings validation
        return true;
    }

    private function updateSettingsData($data)
    {
        $settings = [];
        foreach ($data as $group => $groupData) {
            foreach ($groupData as $key => $value) {
                $settings[$key] = [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'group' => $group
                ];
            }
        }
        return $this->settingsModel->updateSettings($settings);
    }

    private function updateCompanyData($data)
    {
        // TODO: Implement company settings update
        return true;
    }

    private function updateEmailData($data)
    {
        // TODO: Implement email settings update
        return true;
    }

    private function updateBackupData($data)
    {
        // TODO: Implement backup settings update
        return true;
    }

    private function testEmailConnection($emailSettings)
    {
        // TODO: Implement email connection test
        return ['success' => false, 'message' => 'Email test not implemented yet'];
    }

    private function performBackup($backupType)
    {
        // TODO: Implement backup creation
        return ['file' => 'backup_' . date('Y-m-d_H-i-s') . '.sql', 'size' => 0];
    }

    private function getBackupList()
    {
        return $this->settingsModel->getBackupHistory();
    }

    private function downloadBackupFile($backupFile)
    {
        // TODO: Implement backup file download
        throw new Exception('Backup download not implemented yet');
    }

    private function deleteBackupFile($backupFile)
    {
        // TODO: Implement backup file deletion
        return true;
    }

    private function getLogs($logType, $limit)
    {
        return $this->settingsModel->getSystemLogs($logType, $limit);
    }

    private function clearLogs($logType)
    {
        return $this->settingsModel->clearSystemLogs($logType);
    }

    private function getSystemHealthStatus()
    {
        return $this->settingsModel->getSystemHealthStatus();
    }

    private function updateFeatures($features)
    {
        // TODO: Implement feature toggle updates
        return true;
    }

    protected function getUserRole()
    {
        return $_SESSION['user_role'] ?? 'staff';
    }

    protected function getUserCompanyId()
    {
        return $_SESSION['company_id'] ?? null;
    }

    protected function getUserBranchId()
    {
        return $_SESSION['branch_id'] ?? null;
    }

    protected function requireAuthJson()
    {
        if (!$this->isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
