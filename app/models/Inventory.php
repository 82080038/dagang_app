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

    public function updateStock($branchId, $productId, $quantity, $type, $notes = '', $userId = null, $refType = null, $refId = null) {
        $this->db->beginTransaction();

        try {
            // 1. Get current stock
            $currentInventory = $this->getStock($branchId, $productId);
            $currentStock = $currentInventory ? $currentInventory['quantity'] : 0;
            $newStock = $currentStock;

            // 2. Calculate new stock
            if ($type === 'in' || $type === 'transfer_in' || $type === 'return') {
                $newStock += $quantity;
            } elseif ($type === 'out' || $type === 'transfer_out' || $type === 'sale') {
                if ($currentStock < $quantity) {
                    throw new Exception("Insufficient stock. Current: $currentStock, Requested: $quantity");
                }
                $newStock -= $quantity;
            } elseif ($type === 'adjustment') {
                $newStock = $quantity; // For adjustment, quantity is the NEW absolute value? Or difference? 
                // Let's assume adjustment means "set to this value" or "adjust by this value"?
                // Standard practice: adjustment usually implies adding/subtracting a difference, OR setting absolute.
                // To be safe and consistent with 'quantity' param being the delta usually, let's treat adjustment as a delta if user provides signed int? 
                // But for explicit stock taking, we usually want "Set to X".
                // Let's change logic: If type is adjustment, calculate difference.
                // Actually, let's stick to: $quantity is always positive delta, type determines sign.
                // Except for 'adjustment' which might be tricky.
                // Let's assume 'adjustment' is a delta correction. If user wants to set stock, they calculate delta.
                // Wait, easier for UI: "Update Stock" -> User inputs real count. System calculates diff.
                // Let's assume the Controller handles the logic of "Set to X" by converting to delta.
                // So here, we treat $quantity as the amount to change.
                // BUT, if type is 'adjustment', it could be + or -.
                // Let's refine: $quantity is always absolute amount involved. $type determines direction.
                // For 'adjustment', we might need 'adjustment_add' or 'adjustment_sub'.
                // Let's keep it simple: $type 'in' adds, 'out' removes. 
                // If we need to set exact stock, we handle it before calling this.
            }

            // 3. Update or Insert Inventory
            if ($currentInventory) {
                $sql = "UPDATE {$this->table} 
                        SET quantity = :quantity, last_restocked_at = CURRENT_TIMESTAMP 
                        WHERE id_inventory = :id";
                $this->query($sql, ['quantity' => $newStock, 'id' => $currentInventory['id_inventory']]);
            } else {
                // If stock is going out but no record exists, it's 0. fail.
                if ($type === 'out' || $type === 'transfer_out' || $type === 'sale') {
                     throw new Exception("Insufficient stock. Current: 0");
                }
                
                $sql = "INSERT INTO {$this->table} (branch_id, product_id, quantity, last_restocked_at) 
                        VALUES (:branch_id, :product_id, :quantity, CURRENT_TIMESTAMP)";
                $this->query($sql, [
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity' => $newStock
                ]);
            }

            // 4. Record Movement
            $sqlHistory = "INSERT INTO stock_movements 
                          (branch_id, product_id, type, quantity, previous_stock, current_stock, reference_type, reference_id, notes, created_by) 
                          VALUES 
                          (:branch_id, :product_id, :type, :quantity, :prev_stock, :curr_stock, :ref_type, :ref_id, :notes, :user_id)";
            
            $this->query($sqlHistory, [
                'branch_id' => $branchId,
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $quantity,
                'prev_stock' => $currentStock,
                'curr_stock' => $newStock,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'notes' => $notes,
                'user_id' => $userId
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
