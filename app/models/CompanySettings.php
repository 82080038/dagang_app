<?php
/**
 * Company Settings Model
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Model.php';

class CompanySettings extends Model {
    protected $table = 'company_settings';
    protected $primaryKey = 'id_setting';
    protected $fillable = [
        'company_id',
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_active',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Get Setting by Company and Key
     */
    public function getSetting($companyId, $key) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id AND setting_key = :key 
                AND is_active = 1";
        
        $params = ['company_id' => $companyId, 'key' => $key];
        return $this->queryOne($sql, $params);
    }
    
    /**
     * Get All Settings for Company
     */
    public function getCompanySettings($companyId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id 
                AND is_active = 1 
                ORDER BY setting_key";
        
        return $this->query($sql, ['company_id' => $companyId]);
    }
    
    /**
     * Get Settings by Type
     */
    public function getSettingsByType($companyId, $type) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id 
                AND setting_type = :type 
                AND is_active = 1 
                ORDER BY setting_key";
        
        return $this->query($sql, ['company_id' => $companyId, 'type' => $type]);
    }
    
    /**
     * Update Setting
     */
    public function updateSetting($companyId, $key, $value, $type = 'string', $description = '') {
        // Check if setting exists
        $existing = $this->getSetting($companyId, $key);
        
        if ($existing) {
            // Update existing setting
            return $this->update($existing['id_setting'], [
                'setting_value' => is_array($value) ? json_encode($value) : $value,
                'setting_type' => $type,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Create new setting
            return $this->create([
                'company_id' => $companyId,
                'setting_key' => $key,
                'setting_value' => is_array($value) ? json_encode($value) : $value,
                'setting_type' => $type,
                'description' => $description,
                'is_active' => 1
            ]);
        }
    }
    
    /**
     * Delete Setting
     */
    public function deleteSetting($companyId, $key) {
        $setting = $this->getSetting($companyId, $key);
        
        if ($setting) {
            return $this->delete($setting['id_setting']);
        }
        
        return false;
    }
    
    /**
     * Get Module Settings
     */
    public function getModuleSettings($companyId) {
        return $this->getSettingsByType($companyId, 'module');
    }
    
    /**
     * Update Module Setting
     */
    public function updateModuleSetting($companyId, $moduleCode, $isActive) {
        return $this->updateSetting($companyId, 'module_' . $moduleCode, $isActive, 'boolean', "Module {$moduleCode} activation status");
    }
    
    /**
     * Get Active Modules for Company
     */
    public function getActiveModules($companyId) {
        $settings = $this->getModuleSettings($companyId);
        $activeModules = [];
        
        foreach ($settings as $setting) {
            if ($setting['setting_value'] == '1' || $setting['setting_value'] === true) {
                $activeModules[] = str_replace('module_', '', $setting['setting_key']);
            }
        }
        
        return $activeModules;
    }
    
    /**
     * Check if Module is Active for Company
     */
    public function isModuleActive($companyId, $moduleCode) {
        $setting = $this->getSetting($companyId, 'module_' . $moduleCode);
        
        return $setting && ($setting['setting_value'] == '1' || $setting['setting_value'] === true);
    }
    
    /**
     * Get Feature Settings
     */
    public function getFeatureSettings($companyId) {
        return $this->getSettingsByType($companyId, 'feature');
    }
    
    /**
     * Update Feature Setting
     */
    public function updateFeatureSetting($companyId, $featureKey, $value) {
        return $this->updateSetting($companyId, $featureKey, $value, 'boolean', "Feature {$featureKey} setting");
    }
    
    /**
     * Get UI Settings
     */
    public function getUISettings($companyId) {
        return $this->getSettingsByType($companyId, 'ui');
    }
    
    /**
     * Update UI Setting
     */
    public function updateUISetting($companyId, $uiKey, $value) {
        return $this->updateSetting($companyId, $uiKey, $value, 'string', "UI {$uiKey} setting");
    }
    
    /**
     * Get Notification Settings
     */
    public function getNotificationSettings($companyId) {
        return $this->getSettingsByType($companyId, 'notification');
    }
    
    /**
     * Update Notification Setting
     */
    public function updateNotificationSetting($companyId, $notificationKey, $value) {
        return $this->updateSetting($companyId, $notificationKey, $value, 'boolean', "Notification {$notificationKey} setting");
    }
    
    /**
     * Get Security Settings
     */
    public function getSecuritySettings($companyId) {
        return $this->getSettingsByType($companyId, 'security');
    }
    
    /**
     * Update Security Setting
     */
    public function updateSecuritySetting($companyId, $securityKey, $value) {
        return $this->updateSetting($companyId, $securityKey, $value, 'boolean', "Security {$securityKey} setting");
    }
    
    /**
     * Get Integration Settings
     */
    public function getIntegrationSettings($companyId) {
        return $this->getSettingsByType($companyId, 'integration');
    }
    
    /**
     * Update Integration Setting
     */
    public function updateIntegrationSetting($companyId, $integrationKey, $value) {
        return $this->updateSetting($companyId, $integrationKey, $value, 'string', "Integration {$integrationKey} setting");
    }
    
    /**
     * Get Company Preferences
     */
    public function getPreferences($companyId) {
        return $this->getSettingsByType($companyId, 'preference');
    }
    
    /**
     * Update Preference
     */
    public function updatePreference($companyId, $preferenceKey, $value) {
        return $this->updateSetting($companyId, $preferenceKey, $value, 'string', "Preference {$preferenceKey}");
    }
    
    /**
     * Bulk Update Settings
     */
    public function bulkUpdateSettings($companyId, $settings) {
        $this->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $this->updateSetting($companyId, $key, $value);
            }
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Reset Settings to Default
     */
    public function resetToDefault($companyId, $type = null) {
        $sql = "DELETE FROM {$this->table} WHERE company_id = :company_id";
        $params = ['company_id' => $companyId];
        
        if ($type) {
            $sql .= " AND setting_type = :type";
            $params['type'] = $type;
        }
        
        return $this->query($sql, $params);
    }
    
    /**
     * Export Settings
     */
    public function exportSettings($companyId) {
        $settings = $this->getCompanySettings($companyId);
        
        $exportData = [];
        foreach ($settings as $setting) {
            $exportData[$setting['setting_key']] = [
                'value' => $setting['setting_value'],
                'type' => $setting['setting_type'],
                'description' => $setting['description']
            ];
        }
        
        return $exportData;
    }
    
    /**
     * Import Settings
     */
    public function importSettings($companyId, $settings) {
        $this->beginTransaction();
        
        try {
            foreach ($settings as $key => $data) {
                $this->updateSetting(
                    $companyId,
                    $key,
                    $data['value'],
                    $data['type'] ?? 'string',
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
     * Validate Setting Value
     */
    public function validateSettingValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false']);
            case 'integer':
                return is_numeric($value) && filter_var($value, FILTER_VALIDATE_INT) !== false;
            case 'float':
                return is_numeric($value) && filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'json':
                json_decode($value);
                return json_last_error() === JSON_ERROR_NONE;
            default:
                return true; // string type accepts any value
        }
    }
    
    /**
     * Get Setting Value with Type Casting
     */
    public function getSettingValue($companyId, $key, $default = null) {
        $setting = $this->getSetting($companyId, $key);
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting['setting_value'];
        $type = $setting['setting_type'];
        
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    /**
     * Get All Settings as Key-Value Pairs
     */
    public function getAllSettingsAsKeyValue($companyId) {
        $settings = $this->getCompanySettings($companyId);
        $keyValue = [];
        
        foreach ($settings as $setting) {
            $keyValue[$setting['setting_key']] = $this->getSettingValue($companyId, $setting['setting_key']);
        }
        
        return $keyValue;
    }
}
?>
