<?php
/**
 * Product Model
 * Native PHP MVC Pattern
 */

require_once __DIR__ . '/../core/Model.php';

class Product extends Model {
    protected $table = 'products';
    protected $primaryKey = 'id_product';
    protected $fillable = [
        'product_code',
        'product_name',
        'category_id',
        'description',
        'unit',
        'purchase_price',
        'selling_price',
        'barcode',
        'image_url',
        'is_active',
        'created_at',
        'updated_at',
        'last_inventory_update',
        'low_stock_threshold',
        'auto_reorder_enabled',
        'real_time_tracking'
    ];
    
    /**
     * Get all products with category info
     */
    public function getAll($limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT p.*, c.name as category_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id_category
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search OR p.barcode LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND p.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY p.product_name ASC LIMIT $limit OFFSET $offset";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get Total Count with filters
     */
    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} p WHERE 1=1";
        $params = [];
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search OR p.barcode LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND p.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Generate Product Code
     */
    public function generateCode() {
        $prefix = 'PRD';
        $year = date('y');
        $month = date('m');
        
        // Get last code
        $sql = "SELECT product_code FROM {$this->table} 
                WHERE product_code LIKE :prefix 
                ORDER BY id_product DESC LIMIT 1";
        
        $result = $this->queryOne($sql, ['prefix' => "$prefix$year$month%"]);
        
        if ($result) {
            $lastSequence = intval(substr($result['product_code'], -4));
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
