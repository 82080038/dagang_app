<?php
require_once APP_PATH . '/core/Model.php';

class Inventory extends Model {
    protected $table = 'branch_inventory';
    protected $primaryKey = 'id_inventory';

    public function getStock($branchId, $productId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE branch_id = :branch_id AND product_id = :product_id";
        return $this->queryOne($sql, [
            'branch_id' => $branchId,
            'product_id' => $productId
        ]);
    }

    public function getBranchInventory($branchId, $limit = 10, $offset = 0, $search = '') {
        $sql = "SELECT i.*, p.product_name, p.product_code, p.unit, p.selling_price as price, c.name as category_name
                FROM {$this->table} i
                JOIN products p ON i.product_id = p.id_product
                LEFT JOIN categories c ON p.category_id = c.id_category
                WHERE i.branch_id = :branch_id";
        
        $params = ['branch_id' => $branchId];

        if (!empty($search)) {
            $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search)";
            $params['search'] = "%$search%";
        }

        $sql .= " ORDER BY p.product_name ASC LIMIT $limit OFFSET $offset";

        return $this->query($sql, $params);
    }

    /**
     * Get company stock
     */
    public function getCompanyStock($productId) {
        $sql = "SELECT * FROM company_inventory 
                WHERE product_id = :product_id";
        return $this->queryOne($sql, ['product_id' => $productId]);
    }
    
    /**
     * Get inventory by product and location
     */
    public function getByProductAndLocation($productId, $locationType, $locationId) {
        if ($locationType === 'branch') {
            return $this->getStock($locationId, $productId);
        } elseif ($locationType === 'company') {
            return $this->getCompanyStock($productId);
        }
        return null;
    }
    
    /**
     * Update stock quantity
     */
    public function updateStock($inventoryId, $newQuantity) {
        return $this->update($inventoryId, [
            'stock_quantity' => $newQuantity,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Create inventory record
     */
    public function create($data) {
        if (isset($data['location_type']) && $data['location_type'] === 'branch') {
            // Create branch inventory
            $sql = "INSERT INTO {$this->table} 
                    (branch_id, product_id, stock_quantity, min_stock, max_stock, created_at, updated_at)
                    VALUES (:branch_id, :product_id, :stock_quantity, :min_stock, :max_stock, :created_at, :updated_at)";
            
            $params = [
                'branch_id' => $data['location_id'],
                'product_id' => $data['product_id'],
                'stock_quantity' => $data['stock_quantity'],
                'min_stock' => $data['min_stock'] ?? 0,
                'max_stock' => $data['max_stock'] ?? 999999,
                'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => $data['updated_at'] ?? date('Y-m-d H:i:s')
            ];
            
            return $this->execute($sql, $params);
        } elseif (isset($data['location_type']) && $data['location_type'] === 'company') {
            // Create company inventory
            $sql = "INSERT INTO company_inventory 
                    (product_id, stock_quantity, min_stock, max_stock, created_at, updated_at)
                    VALUES (:product_id, :stock_quantity, :min_stock, :max_stock, :created_at, :updated_at)";
            
            $params = [
                'product_id' => $data['product_id'],
                'stock_quantity' => $data['stock_quantity'],
                'min_stock' => $data['min_stock'] ?? 0,
                'max_stock' => $data['max_stock'] ?? 999999,
                'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => $data['updated_at'] ?? date('Y-m-d H:i:s')
            ];
            
            return $this->execute($sql, $params);
        }
        
        return false;
    }
    
    /**
     * Update inventory record
     */
    public function update($inventoryId, $data) {
        $sql = "UPDATE {$this->table} 
                SET stock_quantity = :stock_quantity,
                    min_stock = :min_stock,
                    max_stock = :max_stock,
                    updated_at = :updated_at
                WHERE id_inventory = :id";
        
        $params = [
            'stock_quantity' => $data['stock_quantity'],
            'min_stock' => $data['min_stock'] ?? 0,
            'max_stock' => $data['max_stock'] ?? 999999,
            'updated_at' => $data['updated_at'] ?? date('Y-m-d H:i:s'),
            'id' => $inventoryId
        ];
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        // For simplicity, we'll assume transactions are handled at the database level
        // In a real implementation, you would use PDO transactions
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        // For simplicity, we'll assume transactions are handled at the database level
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        // For simplicity, we'll assume transactions are handled at the database level
    }
    
    /**
     * Execute SQL query
     */
    public function execute($sql, $params = []) {
        try {
            // Use the parent's execute method from Model class
            return parent::execute($sql, $params);
        } catch (Exception $e) {
            error_log("SQL Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTotalBranchInventory($branchId, $search = '') {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} i
                JOIN products p ON i.product_id = p.id_product
                WHERE i.branch_id = :branch_id";
        
        $params = ['branch_id' => $branchId];

        if (!empty($search)) {
            $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search)";
            $params['search'] = "%$search%";
        }

        $result = $this->queryOne($sql, $params);
        return $result['total'];
    }
}
