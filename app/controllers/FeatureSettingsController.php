<?php
/**
 * Feature Settings Controller
 * Manages feature activation/deactivation for companies
 */

require_once __DIR__ . '/../config/constants.php';

class FeatureSettingsController extends Controller {
    private $featureSettings;
    
    public function __construct() {
        parent::__construct();
        $this->featureSettings = new \FeatureSettings();
    }
    
    /**
     * Display feature settings page
     */
    public function index() {
        // Only company owners and app admins can access
        $this->requirePermission(ROLE_COMPANY_OWNER);
        
        $companyId = $this->getUserCompanyId();
        $enabledFeatures = $this->featureSettings->getEnabledFeatures($companyId);
        
        $this->data['enabledFeatures'] = $enabledFeatures;
        $this->data['pageTitle'] = 'Pengaturan Fitur';
        
        $this->view->render('feature-settings/index', $this->data);
    }
    
    /**
     * Update feature settings
     */
    public function update() {
        // Only company owners and app admins can update
        $this->requirePermission(ROLE_COMPANY_OWNER);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }
        
        $companyId = $this->getUserCompanyId();
        $features = $_POST['features'] ?? [];
        
        try {
            // Validate features data
            $validatedFeatures = [];
            foreach ($features as $featureKey => $featureData) {
                $validatedFeatures[$featureKey] = [
                    'enabled' => isset($featureData['enabled']) && $featureData['enabled'] === 'true',
                    'settings' => $featureData['settings'] ?? []
                ];
            }
            
            // Update features
            $this->featureSettings->batchUpdateFeatures($companyId, $validatedFeatures);
            
            $this->success('Pengaturan fitur berhasil diperbarui');
            
        } catch (Exception $e) {
            $this->error('Gagal memperbarui pengaturan fitur: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Check if feature is enabled (AJAX endpoint)
     */
    public function checkFeature() {
        $this->requireAuth();
        
        $featureKey = $_GET['feature'] ?? '';
        $companyId = $this->getUserCompanyId();
        
        if (empty($featureKey)) {
            $this->error('Feature key is required', 400);
            return;
        }
        
        $isEnabled = $this->featureSettings->isFeatureEnabled($companyId, $featureKey);
        
        $this->success('Feature status retrieved', [
            'feature' => $featureKey,
            'enabled' => $isEnabled
        ]);
    }
    
    /**
     * Get all available features (AJAX endpoint)
     */
    public function getFeatures() {
        $this->requireAuth();
        
        $companyId = $this->getUserCompanyId();
        $features = $this->featureSettings->getEnabledFeatures($companyId);
        
        $this->success('Features retrieved', $features);
    }
    
    /**
     * Initialize features for new company
     */
    public function initializeCompany($companyId) {
        try {
            return $this->featureSettings->initializeCompanyFeatures($companyId);
        } catch (Exception $e) {
            error_log("Failed to initialize features for company {$companyId}: " . $e->getMessage());
            return false;
        }
    }
}
