<?php
/**
 * Product Transfer Model
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Model.php';

class ProductTransfer extends Model {
    protected $table = 'product_transfers';
    protected $primaryKey = 'id_transfer';
    
    protected $fillable = [
        'product_id',
        'from_type',
        'from_id',
        'to_type',
        'to_id',
        'quantity',
        'transfer_date',
        'notes',
        'status',
        'created_by',
        'processed_by',
        'cancelled_by',
        'created_at',
        'processed_at',
        'cancelled_at'
    ];
    
    /**
     * Get all transfers with filters
     */
    public function getAll($page = 1, $search = '', $transferType = '', $dateFrom = '', $dateTo = '', $companyId = null) {
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT pt.*, 
                    p.product_name, p.product_code, p.unit,
                    from_loc.name as from_location_name,
                    to_loc.name as to_location_name,
                    creator.user_name as created_by_name,
                    processor.user_name as processed_by_name,
                    canceller.user_name as cancelled_by_name
                FROM {$this->table} pt
                LEFT JOIN products p ON pt.product_id = p.id_product
                LEFT JOIN (
                    SELECT 'company' as type, id_company as id, company_name as name 
                    FROM companies
                    UNION ALL
                    SELECT 'branch' as type, id_branch as id, branch_name as name 
                    FROM branches
                ) from_loc ON pt.from_type = from_loc.type AND pt.from_id = from_loc.id
                LEFT JOIN (
                    SELECT 'company' as type, id_company as id, company_name as name 
                    FROM companies
                    UNION ALL
                    SELECT 'branch' as type, id_branch as id, branch_name as name 
                    FROM branches
                ) to_loc ON pt.to_type = to_loc.type AND pt.to_id = to_loc.id
                LEFT JOIN members creator ON pt.created_by = creator.id_member
                LEFT JOIN members processor ON pt.processed_by = processor.id_member
                LEFT JOIN members canceller ON pt.cancelled_by = canceller.id_member
                WHERE 1=1";
        
        $params = [];
        
        if ($companyId) {
            $sql .= " AND ((pt.from_type = 'company' AND pt.from_id = :company_id) 
                      OR (pt.to_type = 'company' AND pt.to_id = :company_id)
                      OR (pt.from_type = 'branch' AND pt.from_id IN (SELECT id_branch FROM branches WHERE company_id = :company_id))
                      OR (pt.to_type = 'branch' AND pt.to_id IN (SELECT id_branch FROM branches WHERE company_id = :company_id)))";
            $params['company_id'] = $companyId;
        }
        
        if ($search) {
            $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search OR pt.notes LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        if ($transferType) {
            if ($transferType === 'company_to_branch') {
                $sql .= " AND pt.from_type = 'company' AND pt.to_type = 'branch'";
            } elseif ($transferType === 'branch_to_company') {
                $sql .= " AND pt.from_type = 'branch' AND pt.to_type = 'company'";
            } elseif ($transferType === 'branch_to_branch') {
                $sql .= " AND pt.from_type = 'branch' AND pt.to_type = 'branch'";
            }
        }
        
        if ($dateFrom) {
            $sql .= " AND pt.transfer_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND pt.transfer_date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $sql .= " ORDER BY pt.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get transfers by branch
     */
    public function getByBranch($branchId, $page = 1, $search = '', $transferType = '', $dateFrom = '', $dateTo = '') {
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT pt.*, 
                    p.product_name, p.product_code, p.unit,
                    from_loc.name as from_location_name,
                    to_loc.name as to_location_name,
                    creator.user_name as created_by_name,
                    processor.user_name as processed_by_name,
                    canceller.user_name as cancelled_by_name
                FROM {$this->table} pt
                LEFT JOIN products p ON pt.product_id = p.id_product
                LEFT JOIN (
                    SELECT 'company' as type, id_company as id, company_name as name 
                    FROM companies
                    UNION ALL
                    SELECT 'branch' as type, id_branch as id, branch_name as name 
                    FROM branches
                ) from_loc ON pt.from_type = from_loc.type AND pt.from_id = from_loc.id
                LEFT JOIN (
                    SELECT 'company' as type, id_company as id, company_name as name 
                    FROM companies
                    UNION ALL
                    SELECT 'branch' as type, id_branch as id, branch_name as name 
                    FROM branches
                ) to_loc ON pt.to_type = to_loc.type AND pt.to_id = to_loc.id
                LEFT JOIN members creator ON pt.created_by = creator.id_member
                LEFT JOIN members processor ON pt.processed_by = processor.id_member
                LEFT JOIN members canceller ON pt.cancelled_by = canceller.id_member
                WHERE (pt.from_type = 'branch' AND pt.from_id = :branch_id) 
                   OR (pt.to_type = 'branch' AND pt.to_id = :branch_id)";
        
        $params = ['branch_id' => $branchId];
        
        if ($search) {
            $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search OR pt.notes LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        if ($transferType) {
            if ($transferType === 'company_to_branch') {
                $sql .= " AND pt.from_type = 'company' AND pt.to_type = 'branch'";
            } elseif ($transferType === 'branch_to_company') {
                $sql .= " AND pt.from_type = 'branch' AND pt.to_type = 'company'";
            } elseif ($transferType === 'branch_to_branch') {
                $sql .= " AND pt.from_type = 'branch' AND pt.to_type = 'branch'";
            }
        }
        
        if ($dateFrom) {
            $sql .= " AND pt.transfer_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND pt.transfer_date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $sql .= " ORDER BY pt.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get total count with filters
     */
    public function getTotalCount($search = '', $transferType = '', $dateFrom = '', $dateTo = '', $companyId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} pt
                LEFT JOIN products p ON pt.product_id = p.id_product
                WHERE 1=1";
        
        $params = [];
        
        if ($companyId) {
            $sql .= " AND ((pt.from_type = 'company' AND pt.from_id = :company_id) 
                      OR (pt.to_type = 'company' AND pt.to_id = :company_id)
                      OR (pt.from_type = 'branch' AND pt.from_id IN (SELECT id_branch FROM branches WHERE company_id = :company_id))
                      OR (pt.to_type = 'branch' AND pt.to_id IN (SELECT id_branch FROM branches WHERE company_id = :company_id)))";
            $params['company_id'] = $companyId;
        }
        
        if ($search) {
            $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search OR pt.notes LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        if ($transferType) {
            if ($transferType === 'company_to_branch') {
                $sql .= " AND pt.from_type = 'company' AND pt.to_type = 'branch'";
            } elseif ($transferType === 'branch_to_company') {
                $sql .= " AND pt.from_type = 'branch' AND pt.to_type = 'company'";
            } elseif ($transferType === 'branch_to_branch') {
                $sql .= " AND pt.from_type = 'branch' AND pt.to_type = 'branch'";
            }
        }
        
        if ($dateFrom) {
            $sql .= " AND pt.transfer_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND pt.transfer_date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get total count by branch
     */
    public function getByBranchCount($branchId, $search = '', $transferType = '', $dateFrom = '', $dateTo = '') {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} pt
                LEFT JOIN products p ON pt.product_id = p.id_product
                WHERE (pt.from_type = 'branch' AND pt.from_id = :branch_id) 
                   OR (pt.to_type = 'branch' AND pt.to_id = :branch_id)";
        
        $params = ['branch_id' => $branchId];
        
        if ($search) {
            $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search OR pt.notes LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        if ($transferType) {
            if ($transferType === 'company_to_branch') {
                $sql .= " AND pt.from_type = 'company' AND pt.to_type = 'branch'";
            } elseif ($transferType === 'branch_to_company') {
                $sql .= " AND pt.from_type = 'branch' AND pt.to_type = 'company'";
            } elseif ($transferType === 'branch_to_branch') {
                $sql .= " AND pt.from_type = 'branch' AND pt.to_type = 'branch'";
            }
        }
        
        if ($dateFrom) {
            $sql .= " AND pt.transfer_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND pt.transfer_date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get transfer statistics
     */
    public function getStatistics($companyId = null, $branchId = null, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    COUNT(*) as total_transfers,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_transfers,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_transfers,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_transfers,
                    COUNT(CASE WHEN from_type = 'company' AND to_type = 'branch' THEN 1 END) as company_to_branch,
                    COUNT(CASE WHEN from_type = 'branch' AND to_type = 'company' THEN 1 END) as branch_to_company,
                    COUNT(CASE WHEN from_type = 'branch' AND to_type = 'branch' THEN 1 END) as branch_to_branch,
                    SUM(CASE WHEN status = 'completed' THEN quantity ELSE 0 END) as total_quantity_transferred
                FROM {$this->table}";
        
        $params = [];
        $conditions = [];
        
        if ($companyId) {
            $conditions[] = "((from_type = 'company' AND from_id = :company_id) 
                           OR (to_type = 'company' AND to_id = :company_id)
                           OR (from_type = 'branch' AND from_id IN (SELECT id_branch FROM branches WHERE company_id = :company_id))
                           OR (to_type = 'branch' AND to_id IN (SELECT id_branch FROM branches WHERE company_id = :company_id)))";
            $params['company_id'] = $companyId;
        }
        
        if ($branchId) {
            $conditions[] = "((from_type = 'branch' AND from_id = :branch_id) 
                           OR (to_type = 'branch' AND to_id = :branch_id))";
            $params['branch_id'] = $branchId;
        }
        
        if ($dateFrom) {
            $conditions[] = "transfer_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $conditions[] = "transfer_date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $result = $this->queryOne($sql, $params);
        
        // Set default values
        return array_merge([
            'total_transfers' => 0,
            'completed_transfers' => 0,
            'pending_transfers' => 0,
            'cancelled_transfers' => 0,
            'company_to_branch' => 0,
            'branch_to_company' => 0,
            'branch_to_branch' => 0,
            'total_quantity_transferred' => 0
        ], $result ?: []);
    }
    
    /**
     * Validate transfer data
     */
    public function validateTransfer($data) {
        $rules = [
            'product_id' => 'required|numeric',
            'from_type' => 'required|in:company,branch',
            'from_id' => 'required|numeric',
            'to_type' => 'required|in:company,branch',
            'to_id' => 'required|numeric',
            'quantity' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
            'notes' => 'max:500'
        ];
        
        $errors = [];
        
        // Check if from and to are different
        if ($data['from_type'] === $data['to_type'] && $data['from_id'] === $data['to_id']) {
            $errors['transfer'] = 'Lokasi asal dan tujuan tidak boleh sama';
        }
        
        // Validate quantity
        if (isset($data['quantity']) && $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity harus lebih dari 0';
        }
        
        return array_merge($errors, $this->validate($data, $rules));
    }
    
    /**
     * Get recent transfers
     */
    public function getRecent($limit = 10, $companyId = null, $branchId = null) {
        $sql = "SELECT pt.*, 
                    p.product_name, p.product_code,
                    from_loc.name as from_location_name,
                    to_loc.name as to_location_name
                FROM {$this->table} pt
                LEFT JOIN products p ON pt.product_id = p.id_product
                LEFT JOIN (
                    SELECT 'company' as type, id_company as id, company_name as name 
                    FROM companies
                    UNION ALL
                    SELECT 'branch' as type, id_branch as id, branch_name as name 
                    FROM branches
                ) from_loc ON pt.from_type = from_loc.type AND pt.from_id = from_loc.id
                LEFT JOIN (
                    SELECT 'company' as type, id_company as id, company_name as name 
                    FROM companies
                    UNION ALL
                    SELECT 'branch' as type, id_branch as id, branch_name as name 
                    FROM branches
                ) to_loc ON pt.to_type = to_loc.type AND pt.to_id = to_loc.id
                WHERE 1=1";
        
        $params = [];
        
        if ($companyId) {
            $sql .= " AND ((pt.from_type = 'company' AND pt.from_id = :company_id) 
                      OR (pt.to_type = 'company' AND pt.to_id = :company_id)
                      OR (pt.from_type = 'branch' AND pt.from_id IN (SELECT id_branch FROM branches WHERE company_id = :company_id))
                      OR (pt.to_type = 'branch' AND pt.to_id IN (SELECT id_branch FROM branches WHERE company_id = :company_id)))";
            $params['company_id'] = $companyId;
        }
        
        if ($branchId) {
            $sql .= " AND ((pt.from_type = 'branch' AND pt.from_id = :branch_id) 
                      OR (pt.to_type = 'branch' AND pt.to_id = :branch_id))";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY pt.created_at DESC LIMIT :limit";
        $params['limit'] = $limit;
        
        return $this->query($sql, $params);
    }
}
?>
