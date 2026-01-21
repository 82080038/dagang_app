<?php
/**
 * Module Manager Model
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Model.php';

class ModuleManager extends Model {
    protected $table = 'modules';
    protected $primaryKey = 'id_module';
    protected $fillable = [
        'module_name',
        'module_code',
        'module_type',
        'description',
        'version',
        'is_active',
        'is_required',
        'dependencies',
        'settings',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Get All Modules
     */
    public function getAllModules() {
        $sql = "SELECT * FROM {$this->table} ORDER BY module_name";
        return $this->query($sql);
    }
    
    /**
     * Get Active Modules
     */
    public function getActiveModules() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY module_name";
        return $this->query($sql);
    }
    
    /**
     * Get Required Modules
     */
    public function getRequiredModules() {
        $sql = "SELECT * FROM {$this->table} WHERE is_required = 1 ORDER BY module_name";
        return $this->query($sql);
    }
    
    /**
     * Get Module by Code
     */
    public function getByCode($code) {
        return $this->findOneBy('module_code', $code);
    }
    
    /**
     * Get Module by Type
     */
    public function getByType($type) {
        return $this->findBy('module_type', $type);
    }
    
    /**
     * Search Modules
     */
    public function search($keyword) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE module_name LIKE :keyword 
                OR module_code LIKE :keyword 
                OR description LIKE :keyword
                ORDER BY module_name";
        
        return $this->query($sql, ['keyword' => '%' . $keyword . '%']);
    }
    
    /**
     * Get Module Dependencies
     */
    public function getDependencies($moduleId) {
        $module = $this->getById($moduleId);
        
        if (!$module || empty($module['dependencies'])) {
            return [];
        }
        
        $dependencies = json_decode($module['dependencies'], true);
        
        if (empty($dependencies)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($dependencies));
        $sql = "SELECT * FROM {$this->table} WHERE module_code IN ({$placeholders})";
        
        return $this->query($sql, $dependencies);
    }
    
    /**
     * Check if Module Dependencies are Satisfied
     */
    public function checkDependencies($moduleId) {
        $dependencies = $this->getDependencies($moduleId);
        
        foreach ($dependencies as $dependency) {
            if (!$dependency['is_active']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get Module Settings
     */
    public function getModuleSettings($moduleId) {
        $module = $this->getById($moduleId);
        
        if (!$module || empty($module['settings'])) {
            return [];
        }
        
        return json_decode($module['settings'], true);
    }
    
    /**
     * Update Module Settings
     */
    public function updateModuleSettings($moduleId, $settings) {
        return $this->update($moduleId, [
            'settings' => json_encode($settings),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Activate Module
     */
    public function activate($moduleId) {
        // Check dependencies first
        if (!$this->checkDependencies($moduleId)) {
            throw new Exception('Dependencies not satisfied for module activation');
        }
        
        return $this->update($moduleId, [
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Deactivate Module
     */
    public function deactivate($moduleId) {
        // Check if module is required
        $module = $this->getById($moduleId);
        
        if ($module && $module['is_required']) {
            throw new Exception('Cannot deactivate required module');
        }
        
        // Check if other modules depend on this module
        $dependents = $this->getDependents($moduleId);
        
        if (!empty($dependents)) {
            throw new Exception('Cannot deactivate module that is required by other modules');
        }
        
        return $this->update($moduleId, [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get Modules that Depend on This Module
     */
    public function getDependents($moduleId) {
        $module = $this->getById($moduleId);
        
        if (!$module) {
            return [];
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE JSON_CONTAINS(dependencies, :module_code) 
                AND is_active = 1";
        
        return $this->query($sql, ['module_code' => '"' . $module['module_code'] . '"']);
    }
    
    /**
     * Install Module
     */
    public function install($moduleData) {
        // Validate required fields
        $required = ['module_name', 'module_code', 'module_type'];
        $errors = parent::validateRequired($required, $moduleData);
        
        if (!empty($errors)) {
            throw new Exception('Validation failed: ' . implode(', ', $errors));
        }
        
        // Check if module already exists
        if ($this->getByCode($moduleData['module_code'])) {
            throw new Exception('Module with this code already exists');
        }
        
        // Set default values
        $moduleData['is_active'] = $moduleData['is_active'] ?? 0;
        $moduleData['is_required'] = $moduleData['is_required'] ?? 0;
        $moduleData['dependencies'] = isset($moduleData['dependencies']) ? json_encode($moduleData['dependencies']) : '[]';
        $moduleData['settings'] = isset($moduleData['settings']) ? json_encode($moduleData['settings']) : '{}';
        $moduleData['version'] = $moduleData['version'] ?? '1.0.0';
        
        return $this->create($moduleData);
    }
    
    /**
     * Uninstall Module
     */
    public function uninstall($moduleId) {
        $module = $this->getById($moduleId);
        
        if (!$module) {
            throw new Exception('Module not found');
        }
        
        if ($module['is_required']) {
            throw new Exception('Cannot uninstall required module');
        }
        
        // Check if other modules depend on this module
        $dependents = $this->getDependents($moduleId);
        
        if (!empty($dependents)) {
            throw new Exception('Cannot uninstall module that is required by other modules');
        }
        
        // Remove module settings from company settings
        $this->removeModuleFromCompanySettings($module['module_code']);
        
        return $this->delete($moduleId);
    }
    
    /**
     * Remove Module from Company Settings
     */
    private function removeModuleFromCompanySettings($moduleCode) {
        // This would be implemented in CompanySettings model
        // For now, we'll just log it
        error_log("Removing module {$moduleCode} from company settings");
    }
    
    /**
     * Get Module Statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_modules,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_modules,
                    COUNT(CASE WHEN is_required = 1 THEN 1 END) as required_modules,
                    COUNT(CASE WHEN module_type = 'core' THEN 1 END) as core_modules,
                    COUNT(CASE WHEN module_type = 'business' THEN 1 END) as business_modules,
                    COUNT(CASE WHEN module_type = 'integration' THEN 1 END) as integration_modules,
                    COUNT(CASE WHEN module_type = 'addon' THEN 1 END) as addon_modules
                FROM {$this->table}";
        
        return $this->queryOne($sql);
    }
    
    /**
     * Get Module Type Options
     */
    public function getModuleTypeOptions() {
        return [
            'core' => 'Core Module',
            'business' => 'Business Module',
            'integration' => 'Integration Module',
            'addon' => 'Add-on Module'
        ];
    }
    
    /**
     * Validate Module Data
     */
    public function validateModule($data) {
        $rules = [
            'module_name' => 'required|min:3|max:100',
            'module_code' => 'required|min:2|max:50',
            'module_type' => 'required',
            'description' => 'max:500',
            'version' => 'max:20'
        ];
        
        return $this->validate($data, $rules);
    }
    
    /**
     * Get Available Modules for Installation
     */
    public function getAvailableModules() {
        // This would scan the modules directory for available modules
        // For now, return predefined modules
        return [
            [
                'module_name' => 'Product Management',
                'module_code' => 'products',
                'module_type' => 'business',
                'description' => 'Manage products, categories, and inventory',
                'version' => '1.0.0',
                'is_required' => 1,
                'dependencies' => []
            ],
            [
                'module_name' => 'Point of Sale',
                'module_code' => 'pos',
                'module_type' => 'business',
                'description' => 'POS system for sales transactions',
                'version' => '1.0.0',
                'is_required' => 1,
                'dependencies' => ['products']
            ],
            [
                'module_name' => 'Customer Management',
                'module_code' => 'crm',
                'module_type' => 'business',
                'description' => 'Customer relationship management',
                'version' => '1.0.0',
                'is_required' => 0,
                'dependencies' => []
            ],
            [
                'module_name' => 'Inventory Management',
                'module_code' => 'inventory',
                'module_type' => 'business',
                'description' => 'Advanced inventory management',
                'version' => '1.0.0',
                'is_required' => 0,
                'dependencies' => ['products']
            ],
            [
                'module_name' => 'Financial Management',
                'module_code' => 'finance',
                'module_type' => 'business',
                'description' => 'Financial reporting and accounting',
                'version' => '1.0.0',
                'is_required' => 0,
                'dependencies' => []
            ],
            [
                'module_name' => 'E-commerce Integration',
                'module_code' => 'ecommerce',
                'module_type' => 'integration',
                'description' => 'Integration with e-commerce platforms',
                'version' => '1.0.0',
                'is_required' => 0,
                'dependencies' => ['products', 'inventory']
            ],
            [
                'module_name' => 'Advanced Analytics',
                'module_code' => 'analytics',
                'module_type' => 'addon',
                'description' => 'Advanced business analytics and reporting',
                'version' => '1.0.0',
                'is_required' => 0,
                'dependencies' => []
            ]
        ];
    }
}
?>
