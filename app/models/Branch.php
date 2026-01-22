<?php
/**
 * Branch Model
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Model.php';

class Branch extends Model {
    protected $table = 'branches';
    protected $primaryKey = 'id_branch';
    protected $fillable = [
        'company_id',
        'branch_name',
        'branch_code',
        'branch_type',
        'owner_name',
        'phone',
        'email',
        'location_id',
        'address',
        'address_detail',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'operation_hours',
        'is_active',
        'last_sync_at',
        'sync_status',
        'real_time_status',
        'last_ping_at',
        'auto_refresh_enabled',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Get Branch by Code
     */
    public function getByCode($code) {
        return $this->findOneBy('branch_code', $code);
    }
    
    /**
     * Get Active Branches
     */
    public function getActiveBranches() {
        $sql = "SELECT b.*, c.company_name 
                FROM {$this->table} b
                LEFT JOIN companies c ON b.company_id = c.id_company
                WHERE b.is_active = 1 
                ORDER BY c.company_name, b.branch_name";
        
        return $this->query($sql);
    }
    
    public function getAll($limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT b.*, c.company_name 
                FROM {$this->table} b
                LEFT JOIN companies c ON b.company_id = c.id_company
                WHERE 1=1";
        $params = [];
        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $sql .= " AND b.company_id = :company_id";
            $params['company_id'] = $filters['company_id'];
        }
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (b.branch_name LIKE :search OR b.branch_code LIKE :search OR b.owner_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        $sql .= " ORDER BY b.branch_name ASC LIMIT $limit OFFSET $offset";
        return $this->query($sql, $params);
    }
    
    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} b
                WHERE 1=1";
        $params = [];
        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $sql .= " AND b.company_id = :company_id";
            $params['company_id'] = $filters['company_id'];
        }
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (b.branch_name LIKE :search OR b.branch_code LIKE :search OR b.owner_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        $result = $this->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get Branches by Company
     */
    public function getByCompany($companyId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id 
                AND is_active = 1 
                ORDER BY branch_name";
        
        return $this->query($sql, ['company_id' => $companyId]);
    }
    
    /**
     * Get Branch by Type
     */
    public function getByType($type) {
        return $this->findBy('branch_type', $type);
    }
    
    /**
     * Search Branches
     */
    public function search($keyword, $companyId = null) {
        $sql = "SELECT b.*, c.company_name 
                FROM {$this->table} b
                LEFT JOIN companies c ON b.company_id = c.id_company
                WHERE (b.branch_name LIKE :keyword 
                OR b.owner_name LIKE :keyword 
                OR b.email LIKE :keyword
                OR c.company_name LIKE :keyword)
                AND b.is_active = 1";
        
        $params = ['keyword' => '%' . $keyword . '%'];
        
        if ($companyId) {
            $sql .= " AND b.company_id = :company_id";
            $params['company_id'] = $companyId;
        }
        
        $sql .= " ORDER BY c.company_name, b.branch_name";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get Branch with Inventory Summary
     */
    public function getWithInventorySummary() {
        $sql = "SELECT b.*, c.company_name,
                    COUNT(DISTINCT bi.id_inventory) as product_count,
                    COALESCE(SUM(bi.stock_quantity), 0) as total_stock,
                    COUNT(DISTINCT CASE WHEN bi.stock_quantity <= bi.min_stock THEN 1 END) as low_stock_count
                FROM {$this->table} b
                LEFT JOIN companies c ON b.company_id = c.id_company
                LEFT JOIN branch_inventory bi ON b.id_branch = bi.branch_id
                WHERE b.is_active = 1
                GROUP BY b.id_branch
                ORDER BY c.company_name, b.branch_name";
        
        return $this->query($sql);
    }
    
    /**
     * Get Branch Statistics
     */
    public function getStatistics($companyId = null) {
        $sql = "SELECT 
                    COUNT(*) as total_branches,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_branches,
                    COUNT(CASE WHEN branch_type = 'toko' THEN 1 END) as toko_count,
                    COUNT(CASE WHEN branch_type = 'warung' THEN 1 END) as warung_count,
                    COUNT(CASE WHEN branch_type = 'minimarket' THEN 1 END) as minimarket_count,
                    COUNT(CASE WHEN branch_type = 'gerai' THEN 1 END) as gerai_count,
                    COUNT(CASE WHEN branch_type = 'kios' THEN 1 END) as kios_count,
                    COUNT(CASE WHEN branch_type = 'online' THEN 1 END) as online_count,
                    COUNT(CASE WHEN branch_type = 'warung' THEN 1 END) as ultra_mikro_count,
                    COUNT(CASE WHEN branch_type = 'kios' THEN 1 END) as mikro_count,
                    COUNT(CASE WHEN branch_type = 'toko' THEN 1 END) as kecil_menengah_count,
                    COUNT(CASE WHEN branch_type = 'minimarket' THEN 1 END) as menengah_count,
                    COUNT(CASE WHEN branch_type = 'gerai' THEN 1 END) as besar_count,
                    COUNT(CASE WHEN branch_type = 'online' THEN 1 END) as enterprise_count
                FROM {$this->table}";
        
        $params = [];
        
        if ($companyId) {
            $sql .= " WHERE company_id = :company_id";
            $params['company_id'] = $companyId;
        }
        
        return $this->queryOne($sql, $params);
    }
    
    /**
     * Validate Branch Data
     */
    public function validateBranch($data) {
        $rules = [
            'company_id' => 'required|numeric',
            'branch_name' => 'required|min:3|max:200',
            'branch_code' => 'required|min:2|max:50',
            'branch_type' => 'required',
            'owner_name' => 'required|min:3|max:200',
            'email' => 'email',
            'phone' => 'min:10|max:20',
            'address_detail' => 'required|min:3|max:255',
            'province_id' => 'required|numeric',
            'regency_id' => 'required|numeric',
            'district_id' => 'required|numeric'
        ];
        
        return $this->validate($data, $rules);
    }
    
    /**
     * Check if Branch Code Exists
     */
    public function codeExists($code, $companyId = null, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE branch_code = :code";
        $params = ['code' => $code];
        
        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params['company_id'] = $companyId;
        }
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Activate Branch
     */
    public function activate($id) {
        return $this->update($id, ['is_active' => 1]);
    }
    
    /**
     * Deactivate Branch
     */
    public function deactivate($id) {
        return $this->update($id, ['is_active' => 0]);
    }
    
    /**
     * Get Branch Options for Dropdown
     */
    public function getOptions($companyId = null, $activeOnly = true) {
        $sql = "SELECT b.id_branch, b.branch_name, c.company_name 
                FROM {$this->table} b
                LEFT JOIN companies c ON b.company_id = c.id_company";
        
        $params = [];
        
        if ($companyId) {
            $sql .= " WHERE b.company_id = :company_id";
            $params['company_id'] = $companyId;
        }
        
        if ($activeOnly) {
            $sql .= ($companyId ? " AND" : " WHERE") . " b.is_active = 1";
        }
        
        $sql .= " ORDER BY c.company_name, b.branch_name";
        
        $branches = $this->query($sql, $params);
        $options = [];
        
        foreach ($branches as $branch) {
            $options[$branch['id_branch']] = $branch['company_name'] . ' - ' . $branch['branch_name'];
        }
        
        return $options;
    }
    
    /**
     * Get Branch Type Options
     */
    public function getTypeOptions() {
        return [
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
            'pusat' => 'Pusat',
            'cabang' => 'Cabang',
            'online' => 'Online'
        ];
    }
    
    /**
     * Get Business Segment Options
     */
    public function getBusinessSegmentOptions() {
        return [
            'ultra_mikro' => 'Ultra Mikro',
            'mikro' => 'Mikro',
            'kecil_menengah' => 'Kecil-Menengah',
            'menengah' => 'Menengah',
            'besar' => 'Besar',
            'enterprise' => 'Enterprise'
        ];
    }
    
    /**
     * Update Operation Hours
     */
    public function updateOperationHours($id, $operationHours) {
        return $this->update($id, ['operation_hours' => json_encode($operationHours)]);
    }
    
    /**
     * Get Operation Hours
     */
    public function getOperationHours($id) {
        $branch = $this->getById($id);
        return isset($branch['operation_hours']) ? json_decode($branch['operation_hours'], true) : [];
    }
    
    /**
     * Check if Branch is Open
     */
    public function isOpen($id) {
        $operationHours = $this->getOperationHours($id);
        $currentDay = strtolower(date('l'));
        $currentTime = date('H:i');
        
        if (empty($operationHours) || !isset($operationHours[$currentDay])) {
            return false;
        }
        
        $todayHours = $operationHours[$currentDay];
        
        // Handle complex JSON format: {"open":"08:00","close":"21:00"}
        if (is_array($todayHours) && isset($todayHours['open']) && isset($todayHours['close'])) {
            $openTime = $todayHours['open'];
            $closeTime = $todayHours['close'];
            return $currentTime >= trim($openTime) && $currentTime <= trim($closeTime);
        }
        
        // Handle simple string format: "08:00-21:00"
        if (is_string($todayHours)) {
            if ($todayHours === 'closed') {
                return false;
            }
            
            if (strpos($todayHours, '-') !== false) {
                list($openTime, $closeTime) = explode('-', $todayHours);
                return $currentTime >= trim($openTime) && $currentTime <= trim($closeTime);
            }
        }
        
        // Handle array format: ["08:00", "21:00"]
        if (is_array($todayHours) && count($todayHours) === 2) {
            list($openTime, $closeTime) = $todayHours;
            return $currentTime >= trim($openTime) && $currentTime <= trim($closeTime);
        }
        
        return false;
    }
    
    /**
     * Get Open Branches
     */
    public function getOpenBranches() {
        $branches = $this->getActiveBranches();
        $openBranches = [];
        
        foreach ($branches as $branch) {
            if ($this->isOpen($branch['id_branch'])) {
                $openBranches[] = $branch;
            }
        }
        
        return $openBranches;
    }
}
?>
