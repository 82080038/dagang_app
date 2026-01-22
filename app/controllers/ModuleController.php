<?php
/**
 * Module Controller
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ModuleManager.php';
require_once __DIR__ . '/../models/CompanySettings.php';

class ModuleController extends Controller {
    private $moduleManager;
    private $companySettings;
    
    public function __construct() {
        parent::__construct();
        $this->moduleManager = new ModuleManager();
        $this->companySettings = new CompanySettings();
    }
    
    /**
     * Module Management Index
     */
    public function index() {
        $this->requirePermission(ROLE_ADMIN);
        
        $companyId = $this->getUserId(); // Assuming user ID is company ID for simplicity
        $activeModules = $this->companySettings->getActiveModules($companyId);
        $allModules = $this->moduleManager->getAllModules();
        $statistics = $this->moduleManager->getStatistics();
        
        $data = [
            'title' => 'Module Management',
            'activeModules' => $activeModules,
            'allModules' => $allModules,
            'statistics' => $statistics
        ];
        
        $this->render('modules/index', $data);
    }
    
    /**
     * Available Modules
     */
    public function available() {
        $this->requirePermission(ROLE_ADMIN);
        
        $availableModules = $this->moduleManager->getAvailableModules();
        
        $data = [
            'title' => 'Available Modules',
            'modules' => $availableModules
        ];
        
        $this->render('modules/available', $data);
    }
    
    /**
     * Install Module
     */
    public function install() {
        $this->requirePermission(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $moduleData = $this->post();
            
            try {
                $moduleId = $this->moduleManager->install($moduleData);
                
                // Update company settings to activate module
                $companyId = $this->getUserId();
                $this->companySettings->updateModuleSetting($companyId, $moduleData['module_code'], true);
                
                $this->logActivity('module_installed', "Installed module: " . $moduleData['module_name']);
                $this->success('Module installed successfully');
                
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }
    
    /**
     * Uninstall Module
     */
    public function uninstall($id) {
        $this->requirePermission(ROLE_ADMIN);
        
        try {
            $module = $this->moduleManager->getById($id);
            
            if (!$module) {
                $this->error('Module not found', 404);
            }
            
            $this->moduleManager->uninstall($id);
            
            // Update company settings to deactivate module
            $companyId = $this->getUserId();
            $this->companySettings->updateModuleSetting($companyId, $module['module_code'], false);
            
            $this->logActivity('module_uninstalled', "Uninstalled module: {$module['module_name']}");
            $this->success('Module uninstalled successfully');
            
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Activate Module
     */
    public function activate($id) {
        $this->requirePermission(ROLE_ADMIN);
        
        try {
            $module = $this->moduleManager->getById($id);
            
            if (!$module) {
                $this->error('Module not found', 404);
            }
            
            $this->moduleManager->activate($id);
            
            // Update company settings
            $companyId = $this->getUserId();
            $this->companySettings->updateModuleSetting($companyId, $module['module_code'], true);
            
            $this->logActivity('module_activated', "Activated module: {$module['module_name']}");
            $this->success('Module activated successfully');
            
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Deactivate Module
     */
    public function deactivate($id) {
        $this->requirePermission(ROLE_ADMIN);
        
        try {
            $module = $this->moduleManager->getById($id);
            
            if (!$module) {
                $this->error('Module not found', 404);
            }
            
            $this->moduleManager->deactivate($id);
            
            // Update company settings
            $companyId = $this->getUserId();
            $this->companySettings->updateModuleSetting($companyId, $module['module_code'], false);
            
            $this->logActivity('module_deactivated', "Deactivated module: {$module['module_name']}");
            $this->success('Module deactivated successfully');
            
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Module Details
     */
    public function details($id) {
        $this->requirePermission(ROLE_ADMIN);
        
        $module = $this->moduleManager->getById($id);
        
        if (!$module) {
            $this->error('Module not found', 404);
        }
        
        // Get dependencies
        $dependencies = $this->moduleManager->getDependencies($id);
        
        // Get dependents
        $dependents = $this->moduleManager->getDependents($id);
        
        // Get settings
        $settings = $this->moduleManager->getModuleSettings($id);
        
        // Check if module is active for current company
        $companyId = $this->getUserId();
        $isActive = $this->companySettings->isModuleActive($companyId, $module['module_code']);
        
        $data = [
            'title' => 'Module Details',
            'module' => $module,
            'dependencies' => $dependencies,
            'dependents' => $dependents,
            'settings' => $settings,
            'isActive' => $isActive
        ];
        
        $this->render('modules/details', $data);
    }
    
    /**
     * Module Settings
     */
    public function settings($id) {
        $this->requirePermission(ROLE_ADMIN);
        
        $module = $this->moduleManager->getById($id);
        
        if (!$module) {
            $this->error('Module not found', 404);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = $this->post('settings');
            
            try {
                $this->moduleManager->updateModuleSettings($id, $settings);
                
                $this->logActivity('module_settings_updated', "Updated settings for module: {$module['module_name']}");
                $this->success('Module settings updated successfully');
                
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
        
        $currentSettings = $this->moduleManager->getModuleSettings($id);
        
        $data = [
            'title' => 'Module Settings',
            'module' => $module,
            'settings' => $currentSettings
        ];
        
        $this->render('modules/settings', $data);
    }
    
    /**
     * Company Module Settings
     */
    public function companySettings() {
        $this->requirePermission(ROLE_ADMIN);
        
        $companyId = $this->getUserId();
        $allSettings = $this->companySettings->getCompanySettings($companyId);
        
        // Group settings by type
        $groupedSettings = [];
        foreach ($allSettings as $setting) {
            $type = $setting['setting_type'];
            if (!isset($groupedSettings[$type])) {
                $groupedSettings[$type] = [];
            }
            $groupedSettings[$type][] = $setting;
        }
        
        $data = [
            'title' => 'Company Module Settings',
            'settings' => $groupedSettings
        ];
        
        $this->render('modules/company_settings', $data);
    }
    
    /**
     * Update Company Setting
     */
    public function updateCompanySetting() {
        $this->requirePermission(ROLE_ADMIN);
        
        $companyId = $this->getUserId();
        $key = $this->post('key');
        $value = $this->post('value');
        $type = $this->post('type', 'string');
        $description = $this->post('description', '');
        
        try {
            $this->companySettings->updateSetting($companyId, $key, $value, $type, $description);
            
            $this->logActivity('company_setting_updated', "Updated setting: {$key}");
            $this->success('Setting updated successfully');
            
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * API: Get Module Statistics
     */
    public function apiStats() {
        $this->requirePermission(ROLE_ADMIN);
        
        $statistics = $this->moduleManager->getStatistics();
        
        $this->success('Module statistics retrieved', $statistics);
    }
    
    /**
     * API: Get Active Modules
     */
    public function apiActiveModules() {
        $this->requirePermission(ROLE_ADMIN);
        
        $companyId = $this->getUserId();
        $activeModules = $this->companySettings->getActiveModules($companyId);
        
        $this->success('Active modules retrieved', $activeModules);
    }
    
    /**
     * API: Get Module Dependencies
     */
    public function apiDependencies($id) {
        $this->requirePermission(ROLE_ADMIN);
        
        try {
            $dependencies = $this->moduleManager->getDependencies($id);
            $this->success('Dependencies retrieved', $dependencies);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * API: Check Dependencies
     */
    public function apiCheckDependencies($id) {
        $this->requirePermission(ROLE_ADMIN);
        
        try {
            $satisfied = $this->moduleManager->checkDependencies($id);
            $this->success('Dependencies check', ['satisfied' => $satisfied]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Search Modules
     */
    public function search() {
        $this->requirePermission(ROLE_ADMIN);
        
        $keyword = $this->input('keyword');
        
        if (empty($keyword)) {
            $this->error('Search keyword is required');
        }
        
        $results = $this->moduleManager->search($keyword);
        
        $data = [
            'title' => 'Search Results',
            'keyword' => $keyword,
            'results' => $results
        ];
        
        $this->render('modules/search', $data);
    }
    
    /**
     * Export Module Configuration
     */
    public function export() {
        $this->requirePermission(ROLE_ADMIN);
        
        $format = $this->input('format', 'json');
        $companyId = $this->getUserId();
        
        try {
            $data = [
                'export_date' => date('Y-m-d H:i:s'),
                'company_id' => $companyId,
                'active_modules' => $this->companySettings->getActiveModules($companyId),
                'all_settings' => $this->companySettings->exportSettings($companyId)
            ];
            
            switch ($format) {
                case 'json':
                    header('Content-Type: application/json');
                    echo json_encode($data, JSON_PRETTY_PRINT);
                    break;
                    
                case 'array':
                    header('Content-Type: text/plain');
                    print_r($data);
                    break;
                    
                default:
                    $this->error('Invalid export format');
            }
            
        } catch (Exception $e) {
            $this->error('Export failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Import Module Configuration
     */
    public function import() {
        $this->requirePermission(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = $this->getUserId();
            
            if (isset($_FILES['import_file'])) {
                $file = $_FILES['import_file'];
                
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $content = file_get_contents($file['tmp_name']);
                    $data = json_decode($content, true);
                    
                    if ($data) {
                        $this->companySettings->importSettings($companyId, $data);
                        
                        $this->logActivity('settings_imported', 'Imported company settings');
                        $this->success('Settings imported successfully');
                    } else {
                        $this->error('Invalid JSON format');
                    }
                } else {
                    $this->error('File upload error');
                }
            } else {
                $this->error('No file uploaded');
            }
        }
        
        $data = [
            'title' => 'Import Settings'
        ];
        
        $this->render('modules/import', $data);
    }
    
    /**
     * Reset to Default Settings
     */
    public function reset() {
        $this->requirePermission(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $this->post('type', 'all');
            $companyId = $this->getUserId();
            
            try {
                $this->companySettings->resetToDefault($companyId, $type);
                
                $this->logActivity('settings_reset', "Reset settings to default (type: {$type})");
                $this->success('Settings reset to default successfully');
                
            } catch (Exception $e) {
                $this->error('Reset failed: ' . $e->getMessage());
            }
        }
    }
}
?>
