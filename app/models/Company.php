<?php
/**
 * Company Model
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/Address.php';

class Company extends Model {
    protected $table = 'companies';
    protected $primaryKey = 'id_company';
    protected $fillable = [
        'company_name',
        'company_code',
        'company_type',
        'scalability_level',
        'business_category',
        'owner_name',
        'phone',
        'email',
        'address_id',
        'tax_id',
        'business_license',
        'is_active',
        'last_sync_at',
        'sync_status',
        'api_access_key',
        'webhook_url',
        'auto_refresh_interval',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Get Company by Code
     */
    public function getByCode($code) {
        return $this->findOneBy('company_code', $code);
    }
    
    /**
     * Get Active Companies
     */
    public function getActiveCompanies() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY company_name";
        return $this->query($sql);
    }
    
    /**
     * Get Companies by Type
     */
    public function getByType($type) {
        return $this->findBy('company_type', $type);
    }
    
    /**
     * Get Companies by Business Category
     */
    public function getByBusinessCategory($category) {
        return $this->findBy('business_category', $category);
    }
    
    /**
     * Search Companies
     */
    public function search($keyword) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_name LIKE :keyword 
                OR owner_name LIKE :keyword 
                OR email LIKE :keyword 
                AND is_active = 1
                ORDER BY company_name";
        
        $params = ['keyword' => '%' . $keyword . '%'];
        return $this->query($sql, $params);
    }
    
    /**
     * Get Company Statistics
     */
    public function getStatistics($companyId = null) {
        try {
            if ($companyId) {
                $sql = "
                    SELECT 
                        c.id_company,
                        c.company_name,
                        COUNT(DISTINCT b.id_branch) as total_branches,
                        COUNT(DISTINCT m.member_id) as total_members,
                        COUNT(DISTINCT p.product_id) as total_products,
                        COALESCE(SUM(bi.stock_quantity), 0) as total_inventory,
                        COALESCE(SUM(t.total_amount), 0) as total_sales
                    FROM companies c
                    LEFT JOIN branches b ON c.id_company = b.company_id
                    LEFT JOIN members m ON b.id_branch = m.branch_id AND m.is_active = 1
                    LEFT JOIN branch_inventory bi ON b.id_branch = bi.branch_id
                    LEFT JOIN products p ON bi.product_id = p.product_id
                    LEFT JOIN transactions t ON b.id_branch = t.branch_id AND t.transaction_type = 'SALE'
                    WHERE c.id_company = :company_id
                    GROUP BY c.id_company
                ";
                
                $result = $this->queryOne($sql, ['company_id' => $companyId]);
                
                // Ensure all fields exist with default values
                if ($result) {
                    return array_merge([
                        'total_branches' => 0,
                        'total_members' => 0,
                        'total_products' => 0,
                        'total_inventory' => 0,
                        'total_sales' => 0
                    ], $result);
                }
                
                return [
                    'id_company' => $companyId,
                    'company_name' => '',
                    'total_branches' => 0,
                    'total_members' => 0,
                    'total_products' => 0,
                    'total_inventory' => 0,
                    'total_sales' => 0
                ];
            } else {
                // Return overall statistics
                $sql = "SELECT 
                            COUNT(*) as total_companies,
                            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_companies,
                            COUNT(CASE WHEN company_type = 'individual' THEN 1 END) as individual_count,
                            COUNT(CASE WHEN company_type = 'pusat' THEN 1 END) as pusat_count,
                            COUNT(CASE WHEN company_type = 'cabang' THEN 1 END) as cabang_count,
                            COUNT(CASE WHEN company_type = 'franchise' THEN 1 END) as franchise_count,
                            COUNT(CASE WHEN company_type = 'koperasi' THEN 1 END) as koperasi_count,
                            COUNT(CASE WHEN company_type = 'individual' THEN 1 END) as level_1_count,
                            COUNT(CASE WHEN company_type = 'warung' THEN 1 END) as level_2_count,
                            COUNT(CASE WHEN company_type = 'toko' THEN 1 END) as level_3_count,
                            COUNT(CASE WHEN company_type = 'minimarket' THEN 1 END) as level_4_count,
                            COUNT(CASE WHEN company_type = 'distributor' THEN 1 END) as level_5_count,
                            COUNT(CASE WHEN company_type = 'enterprise' THEN 1 END) as level_6_count
                        FROM {$this->table}";
                
                $result = $this->queryOne($sql);
                
                // Ensure all fields exist with default values
                return array_merge([
                    'total_companies' => 0,
                    'active_companies' => 0,
                    'individual_count' => 0,
                    'pusat_count' => 0,
                    'cabang_count' => 0,
                    'franchise_count' => 0,
                    'koperasi_count' => 0,
                    'level_1_count' => 0,
                    'level_2_count' => 0,
                    'level_3_count' => 0,
                    'level_4_count' => 0,
                    'level_5_count' => 0,
                    'level_6_count' => 0
                ], $result ?: []);
            }
        } catch (Exception $e) {
            // Return default statistics on error
            if ($companyId) {
                return [
                    'id_company' => $companyId,
                    'company_name' => '',
                    'total_branches' => 0,
                    'total_members' => 0,
                    'total_products' => 0,
                    'total_inventory' => 0,
                    'total_sales' => 0
                ];
            } else {
                return [
                    'total_companies' => 0,
                    'active_companies' => 0,
                    'individual_count' => 0,
                    'pusat_count' => 0,
                    'cabang_count' => 0,
                    'franchise_count' => 0,
                    'koperasi_count' => 0,
                    'level_1_count' => 0,
                    'level_2_count' => 0,
                    'level_3_count' => 0,
                    'level_4_count' => 0,
                    'level_5_count' => 0,
                    'level_6_count' => 0
                ];
            }
        }
    }
    
    
    /**
     * Get Company with Branches Count
     */
    public function getWithBranchesCount() {
        $sql = "SELECT c.*, COUNT(b.id_branch) as branches_count
                FROM {$this->table} c
                LEFT JOIN branches b ON c.id_company = b.company_id
                GROUP BY c.id_company
                ORDER BY c.company_name";
        
        return $this->query($sql);
    }
    
    /**
     * Validate Company Data
     */
    public function validateCompany($data) {
        $rules = [
            'company_name' => 'required|min:3|max:200',
            'company_code' => 'required|min:2|max:50',
            'company_type' => 'required',
            'scalability_level' => 'required|in:1,2,3,4,5,6',
            'owner_name' => 'required|min:3|max:200',
            'email' => 'email',
            'phone' => 'min:10|max:20',
            'address_id' => 'integer',
            'street_address' => 'required|min:3|max:255',
            'province_id' => 'required|numeric',
            'regency_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'village_id' => 'required|numeric'
        ];
        
        return $this->validate($data, $rules);
    }
    
    /**
     * Create company with address
     */
    public function createCompany($data) {
        // Handle address creation if address data provided
        if (isset($data['street_address']) && !isset($data['address_id'])) {
            $addressData = [
                'street_address' => $data['street_address'],
                'province_id' => $data['province_id'] ?? null,
                'regency_id' => $data['regency_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'village_id' => $data['village_id'] ?? null,
                'postal_code' => $data['postal_code'] ?? null
            ];
            
            $addressModel = new Address();
            $addressId = $addressModel->createAddress($addressData);
            $data['address_id'] = $addressId;
            
            // Remove address fields from company data
            unset($data['street_address'], $data['province_id'], $data['regency_id'], 
                  $data['district_id'], $data['village_id'], $data['postal_code']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update company with address
     */
    public function updateCompany($id, $data) {
        // Handle address update if address data provided
        if (isset($data['address_detail']) && isset($data['address_id'])) {
            $addressData = [
                'address_detail' => $data['address_detail'],
                'province_id' => $data['province_id'] ?? null,
                'regency_id' => $data['regency_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'village_id' => $data['village_id'] ?? null,
                'postal_code' => $data['postal_code'] ?? null
            ];
            
            $addressModel = new Address();
            $addressModel->updateAddress($data['address_id'], $addressData);
            
            // Remove address fields from company data
            unset($data['street_address'], $data['province_id'], $data['regency_id'], 
                  $data['district_id'], $data['village_id'], $data['postal_code']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Get company with address details
     */
    public function getCompanyWithAddress($id) {
        try {
            $sql = "
                SELECT 
                    c.*,
                    a.street_address as address_detail,
                    a.postal_code,
                    p.name as province_name,
                    r.name as regency_name,
                    d.name as district_name,
                    v.name as village_name,
                    CONCAT(
                        a.street_address, ', ',
                        v.name, ', ',
                        d.name, ', ',
                        r.name, ', ',
                        p.name,
                        IF(a.postal_code IS NOT NULL, CONCAT(' ', a.postal_code), '')
                    ) as full_address
                FROM companies c
                LEFT JOIN addresses a ON c.address_id = a.id_address
                LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
                LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
                LEFT JOIN alamat_db.districts d ON a.district_id = d.id
                LEFT JOIN alamat_db.villages v ON a.village_id = v.id
                WHERE c.id_company = :id
            ";
            
            $result = $this->queryOne($sql, ['id' => $id]);
            
            if ($result) {
                // Ensure all address fields exist with default values
                return array_merge([
                    'address_detail' => null,
                    'postal_code' => null,
                    'province_name' => null,
                    'regency_name' => null,
                    'district_name' => null,
                    'village_name' => null,
                    'full_address' => null
                ], $result);
            }
            
            return null;
        } catch (Exception $e) {
            // Return basic company info if address join fails
            error_log("getCompanyWithAddress error: " . $e->getMessage());
            return $this->getById($id);
        }
    }
    
    /**
     * Check if Company Code Exists
     */
    public function codeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE company_code = :code";
        $params = ['code' => $code];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Activate Company
     */
    public function activate($id) {
        return $this->update($id, ['is_active' => 1]);
    }
    
    /**
     * Deactivate Company
     */
    public function deactivate($id) {
        return $this->update($id, ['is_active' => 0]);
    }
    
    /**
     * Get Company Options for Dropdown
     */
    public function getOptions($activeOnly = true) {
        $sql = "SELECT id_company, company_name FROM {$this->table}";
        $params = [];
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY company_name";
        
        $companies = $this->query($sql, $params);
        $options = [];
        
        foreach ($companies as $company) {
            $options[$company['id_company']] = $company['company_name'];
        }
        
        return $options;
    }
    
    /**
     * Get Company Type Options
     */
    public function getTypeOptions() {
        return [
            'individual' => 'Individu/Personal',
            'personal' => 'Personal',
            'warung' => 'Warung',
            'kios' => 'Kios',
            'toko_kelontong' => 'Toko Kelontong',
            'minimarket' => 'Minimarket',
            'pengusaha_menengah' => 'Pengusaha Menengah',
            'distributor' => 'Distributor',
            'koperasi' => 'Koperasi',
            'perusahaan_besar' => 'Perusahaan Besar',
            'franchise' => 'Franchise',
            'pusat' => 'Pusat'
        ];
    }
    
    /**
     * Get Business Category Options
     */
    public function getBusinessCategoryOptions() {
        return [
            'retail' => 'Retail',
            'wholesale' => 'Wholesale',
            'manufacturing' => 'Manufacturing',
            'agriculture' => 'Agriculture',
            'services' => 'Services',
            'cooperative' => 'Cooperative',
            'online' => 'Online',
            'franchise' => 'Franchise',
            'distributor' => 'Distributor',
            'personal' => 'Personal'
        ];
    }
    
    /**
     * Get Scalability Level Options
     */
    public function getScalabilityLevelOptions() {
        return [
            '1' => 'Level 1 - Individu/Personal',
            '2' => 'Level 2 - Warung/Kios',
            '3' => 'Level 3 - Toko Kelontong',
            '4' => 'Level 4 - Minimarket/Pengusaha Menengah',
            '5' => 'Level 5 - Distributor/Perusahaan Menengah',
            '6' => 'Level 6 - Perusahaan Besar/Franchise'
        ];
    }
    
    /**
     * Get total count of companies
     */
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->queryOne($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get company with branches
     */
    public function getWithBranches($companyId = null) {
        try {
            if ($companyId) {
                $sql = "
                    SELECT 
                        c.*,
                        COUNT(b.id_branch) as branches_count
                    FROM companies c
                    LEFT JOIN branches b ON c.id_company = b.company_id
                    WHERE c.id_company = :company_id
                    GROUP BY c.id_company
                ";
                
                $result = $this->queryOne($sql, ['company_id' => $companyId]);
                
                // Ensure branches_count field exists
                if ($result) {
                    return array_merge(['branches_count' => 0], $result);
                }
                
                return [
                    'id_company' => $companyId,
                    'company_name' => '',
                    'branches_count' => 0
                ];
            } else {
                $sql = "
                    SELECT 
                        c.*,
                        COUNT(b.id_branch) as branches_count
                    FROM companies c
                    LEFT JOIN branches b ON c.id_company = b.company_id
                    GROUP BY c.id_company
                    ORDER BY c.company_name
                ";
                
                $results = $this->query($sql);
                
                // Ensure branches_count field exists in all results
                if ($results) {
                    return array_map(function($company) {
                        return array_merge(['branches_count' => 0], $company);
                    }, $results);
                }
                
                return [];
            }
        } catch (Exception $e) {
            // Return default data on error
            if ($companyId) {
                return [
                    'id_company' => $companyId,
                    'company_name' => '',
                    'branches_count' => 0
                ];
            } else {
                return [];
            }
        }
    }
}
?>
