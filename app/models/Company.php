<?php
/**
 * Company Model
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Model.php';

class Company extends Model {
    protected $table = 'companies';
    protected $primaryKey = 'id_company';
    protected $fillable = [
        'company_name',
        'company_code',
        'company_type',
        'business_category',
        'scalability_level',
        'owner_name',
        'phone',
        'email',
        'address',
        'tax_id',
        'business_license',
        'is_active'
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
     * Get Companies by Scalability Level
     */
    public function getByScalabilityLevel($level) {
        return $this->findBy('scalability_level', $level);
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
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_companies,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_companies,
                    COUNT(CASE WHEN scalability_level = 1 THEN 1 END) as level_1_count,
                    COUNT(CASE WHEN scalability_level = 2 THEN 1 END) as level_2_count,
                    COUNT(CASE WHEN scalability_level = 3 THEN 1 END) as level_3_count,
                    COUNT(CASE WHEN scalability_level = 4 THEN 1 END) as level_4_count,
                    COUNT(CASE WHEN scalability_level = 5 THEN 1 END) as level_5_count,
                    COUNT(CASE WHEN scalability_level = 6 THEN 1 END) as level_6_count
                FROM {$this->table}";
        
        return $this->queryOne($sql);
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
            'owner_name' => 'required|min:3|max:200',
            'email' => 'email',
            'phone' => 'min:10|max:20'
        ];
        
        return $this->validate($data, $rules);
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
}
?>
