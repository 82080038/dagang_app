<?php
/**
 * Feature Settings Model
 * Manages feature activation/deactivation for companies and users
 */

class FeatureSettings {
    private $db;
    private $table = 'company_settings';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all available features
     */
    public function getAllFeatures() {
        return [
            'operations' => [
                'name' => 'Operasional',
                'description' => 'Fitur operasional harian (transaksi, produk, dll)',
                'features' => [
                    'transactions' => [
                        'name' => 'Transaksi',
                        'description' => 'Manajemen transaksi penjualan/pembelian',
                        'default' => true,
                        'category' => 'operations'
                    ],
                    'products' => [
                        'name' => 'Produk',
                        'description' => 'Manajemen produk dan inventori',
                        'default' => true,
                        'category' => 'operations'
                    ],
                    'inventory' => [
                        'name' => 'Inventori',
                        'description' => 'Manajemen stok dan gudang',
                        'default' => true,
                        'category' => 'operations'
                    ],
                    'suppliers' => [
                        'name' => 'Supplier',
                        'description' => 'Manajemen supplier/pemasok',
                        'default' => true,
                        'category' => 'operations'
                    ],
                    'customers' => [
                        'name' => 'Pelanggan',
                        'description' => 'Manajemen data pelanggan',
                        'default' => true,
                        'category' => 'operations'
                    ]
                ]
            ],
            'accounting' => [
                'name' => 'Akuntansi',
                'description' => 'Fitur akuntansi dan keuangan',
                'features' => [
                    'chart_of_accounts' => [
                        'name' => 'Chart of Accounts',
                        'description' => 'Struktur akun akuntansi',
                        'default' => false,
                        'category' => 'accounting'
                    ],
                    'journal_entries' => [
                        'name' => 'Jurnal Umum',
                        'description' => 'Pencatatan jurnal akuntansi',
                        'default' => false,
                        'category' => 'accounting'
                    ],
                    'financial_reports' => [
                        'name' => 'Laporan Keuangan',
                        'description' => 'Laporan laba rugi, neraca, dll',
                        'default' => false,
                        'category' => 'accounting'
                    ],
                    'cash_management' => [
                        'name' => 'Manajemen Kas',
                        'description' => 'Manajemen rekening kas/bank',
                        'default' => false,
                        'category' => 'accounting'
                    ]
                ]
            ],
            'management' => [
                'name' => 'Manajemen',
                'description' => 'Fitur manajemen bisnis',
                'features' => [
                    'reports' => [
                        'name' => 'Laporan Bisnis',
                        'description' => 'Laporan penjualan, stok, dll',
                        'default' => true,
                        'category' => 'management'
                    ],
                    'analytics' => [
                        'name' => 'Analitik',
                        'description' => 'Analisis data bisnis',
                        'default' => false,
                        'category' => 'management'
                    ],
                    'product_transfers' => [
                        'name' => 'Transfer Produk',
                        'description' => 'Transfer antar cabang',
                        'default' => false,
                        'category' => 'management'
                    ],
                    'branch_management' => [
                        'name' => 'Manajemen Cabang',
                        'description' => 'Manajemen multi-cabang',
                        'default' => true,
                        'category' => 'management'
                    ]
                ]
            ],
            'advanced' => [
                'name' => 'Lanjutan',
                'description' => 'Fitur lanjutan dan integrasi',
                'features' => [
                    'api_access' => [
                        'name' => 'API Access',
                        'description' => 'Akses API untuk integrasi',
                        'default' => false,
                        'category' => 'advanced'
                    ],
                    'webhooks' => [
                        'name' => 'Webhooks',
                        'description' => 'Integrasi webhook',
                        'default' => false,
                        'category' => 'advanced'
                    ],
                    'notifications' => [
                        'name' => 'Notifikasi',
                        'description' => 'Sistem notifikasi',
                        'default' => false,
                        'category' => 'advanced'
                    ],
                    'audit_logs' => [
                        'name' => 'Audit Logs',
                        'description' => 'Log aktivitas pengguna',
                        'default' => false,
                        'category' => 'advanced'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get company feature settings
     */
    public function getCompanyFeatures($companyId) {
        $sql = "SELECT setting_key, setting_value FROM {$this->table} 
                WHERE company_id = :company_id AND setting_key LIKE 'feature_%'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['company_id' => $companyId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $features = [];
        foreach ($results as $row) {
            $features[$row['setting_key']] = json_decode($row['setting_value'], true);
        }
        
        return $features;
    }
    
    /**
     * Get company feature setting
     */
    public function getCompanyFeature($companyId, $featureKey) {
        $sql = "SELECT setting_value FROM {$this->table} 
                WHERE company_id = :company_id AND setting_key = :setting_key";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'setting_key' => $featureKey
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? json_decode($result['setting_value'], true) : null;
    }
    
    /**
     * Update company feature setting
     */
    public function updateCompanyFeature($companyId, $featureKey, $featureData) {
        $sql = "INSERT INTO {$this->table} 
                (company_id, setting_key, setting_value, setting_type, created_at, updated_at) 
                VALUES (:company_id, :setting_key, :setting_value, 'json', NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                setting_value = :setting_value2, updated_at = NOW()";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([
            'company_id' => $companyId,
            'setting_key' => $featureKey,
            'setting_value' => json_encode($featureData),
            'setting_value2' => json_encode($featureData)
        ]);
    }
    
    /**
     * Check if feature is enabled for company
     */
    public function isFeatureEnabled($companyId, $featureKey) {
        $feature = $this->getCompanyFeature($companyId, 'feature_' . $featureKey);
        
        if ($feature === null) {
            // Get default value from feature definition
            $allFeatures = $this->getAllFeatures();
            foreach ($allFeatures as $category) {
                foreach ($category['features'] as $key => $def) {
                    if ($key === $featureKey) {
                        return $def['default'];
                    }
                }
            }
            return false;
        }
        
        return isset($feature['enabled']) ? $feature['enabled'] : false;
    }
    
    /**
     * Get enabled features for company
     */
    public function getEnabledFeatures($companyId) {
        $allFeatures = $this->getAllFeatures();
        $companyFeatures = $this->getCompanyFeatures($companyId);
        $enabled = [];
        
        foreach ($allFeatures as $categoryKey => $category) {
            $enabled[$categoryKey] = [
                'name' => $category['name'],
                'description' => $category['description'],
                'features' => []
            ];
            
            foreach ($category['features'] as $featureKey => $featureDef) {
                $companyFeature = $companyFeatures['feature_' . $featureKey] ?? null;
                
                if ($companyFeature === null) {
                    // Use default value
                    $enabled[$categoryKey]['features'][$featureKey] = [
                        'name' => $featureDef['name'],
                        'description' => $featureDef['description'],
                        'enabled' => $featureDef['default'],
                        'category' => $featureDef['category']
                    ];
                } else {
                    $enabled[$categoryKey]['features'][$featureKey] = [
                        'name' => $featureDef['name'],
                        'description' => $featureDef['description'],
                        'enabled' => $companyFeature['enabled'] ?? false,
                        'settings' => $companyFeature['settings'] ?? [],
                        'category' => $featureDef['category']
                    ];
                }
            }
        }
        
        return $enabled;
    }
    
    /**
     * Batch update company features
     */
    public function batchUpdateFeatures($companyId, $features) {
        $this->db->getConnection()->beginTransaction();
        
        try {
            foreach ($features as $featureKey => $featureData) {
                $this->updateCompanyFeature($companyId, 'feature_' . $featureKey, $featureData);
            }
            
            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw $e;
        }
    }
    
    /**
     * Get feature settings for user (considering company settings)
     */
    public function getUserFeatures($userId, $companyId) {
        $companyFeatures = $this->getEnabledFeatures($companyId);
        
        // Apply user-specific restrictions if needed
        // This can be extended to include user preferences
        
        return $companyFeatures;
    }
    
    /**
     * Initialize default features for new company
     */
    public function initializeCompanyFeatures($companyId) {
        $allFeatures = $this->getAllFeatures();
        $defaultFeatures = [];
        
        foreach ($allFeatures as $category) {
            foreach ($category['features'] as $featureKey => $featureDef) {
                $defaultFeatures['feature_' . $featureKey] = [
                    'enabled' => $featureDef['default'],
                    'settings' => []
                ];
            }
        }
        
        return $this->batchUpdateFeatures($companyId, $defaultFeatures);
    }
}
