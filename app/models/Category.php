<?php
/**
 * Category Model
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Model.php';

class Category extends Model {
    protected $table = 'categories';
    protected $primaryKey = 'id_category';
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'parent_id',
        'is_active',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Get all categories with company info
     */
    public function getAll($limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT c.*, co.company_name, p.name as parent_name
                FROM {$this->table} c
                LEFT JOIN companies co ON c.company_id = co.id_company
                LEFT JOIN categories p ON c.parent_id = p.id_category
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (c.name LIKE :search OR c.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $sql .= " AND c.company_id = :company_id";
            $params['company_id'] = $filters['company_id'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND c.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY c.name ASC LIMIT $limit OFFSET $offset";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get Total Count with filters
     */
    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} c WHERE 1=1";
        $params = [];
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (c.name LIKE :search OR c.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $sql .= " AND c.company_id = :company_id";
            $params['company_id'] = $filters['company_id'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND c.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Get categories by Company
     */
    public function getByCompany($companyId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id 
                AND is_active = 1 
                ORDER BY name";
        
        return $this->query($sql, ['company_id' => $companyId]);
    }
}
