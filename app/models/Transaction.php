<?php
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/models/Inventory.php';

class Transaction extends Model {
    protected $table = 'transactions';
    protected $primaryKey = 'id_transaction';
    protected $inventoryModel;

    public function __construct() {
        parent::__construct();
        $this->inventoryModel = new Inventory();
    }

    public function generateCode() {
        $prefix = 'TRX';
        $year = date('y');
        $month = date('m');
        $datePrefix = $prefix . $year . $month;
        
        $sql = "SELECT transaction_code FROM {$this->table} 
                WHERE transaction_code LIKE :prefix 
                ORDER BY id_transaction DESC LIMIT 1";
        
        $result = $this->queryOne($sql, ['prefix' => "$datePrefix%"]);
        
        if ($result) {
            $lastCode = $result['transaction_code'];
            $sequence = intval(substr($lastCode, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $datePrefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function createTransaction($data, $items) {
        $this->db->beginTransaction();

        try {
            // 1. Generate Code
            $code = $this->generateCode();
            
            // 2. Insert Header
            $sql = "INSERT INTO {$this->table} 
                    (branch_id, user_id, transaction_code, total_amount, payment_method, payment_status, notes) 
                    VALUES 
                    (:branch_id, :user_id, :code, :total, :method, :status, :notes)";
            
            $this->query($sql, [
                'branch_id' => $data['branch_id'],
                'user_id' => $data['user_id'],
                'code' => $code,
                'total' => $data['total_amount'],
                'method' => $data['payment_method'] ?? 'cash',
                'status' => 'paid',
                'notes' => $data['notes'] ?? ''
            ]);
            
            $transactionId = $this->db->lastInsertId();

            // 3. Insert Items and Update Stock
            $sqlItem = "INSERT INTO transaction_items 
                        (transaction_id, product_id, quantity, price, subtotal) 
                        VALUES 
                        (:trx_id, :prod_id, :qty, :price, :subtotal)";

            foreach ($items as $item) {
                // Insert Item
                $this->query($sqlItem, [
                    'trx_id' => $transactionId,
                    'prod_id' => $item['product_id'],
                    'qty' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price']
                ]);

                // Update Stock (Reduce)
                $this->inventoryModel->updateStock(
                    $data['branch_id'],
                    $item['product_id'],
                    $item['quantity'],
                    'sale', // Type
                    "Transaction $code", // Note
                    $data['user_id'],
                    'transaction', // Ref Type
                    $transactionId // Ref ID
                );
            }

            $this->db->commit();
            return [
                'status' => 'success',
                'transaction_id' => $transactionId,
                'transaction_code' => $code
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAll($limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT t.*, b.branch_name, u.username 
                FROM {$this->table} t
                JOIN branches b ON t.branch_id = b.id_branch
                JOIN users u ON t.user_id = u.id_user
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['branch_id'])) {
            $sql .= " AND t.branch_id = :branch_id";
            $params['branch_id'] = $filters['branch_id'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(t.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(t.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT $limit OFFSET $offset";

        return $this->query($sql, $params);
    }

    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} t WHERE 1=1";
        $params = [];

        if (!empty($filters['branch_id'])) {
            $sql .= " AND t.branch_id = :branch_id";
            $params['branch_id'] = $filters['branch_id'];
        }

        // Add date filters if needed
        
        $result = $this->queryOne($sql, $params);
        return $result['total'];
    }

    public function getDetails($id) {
        // Get Header
        $sql = "SELECT t.*, b.branch_name, u.username 
                FROM {$this->table} t
                JOIN branches b ON t.branch_id = b.id_branch
                JOIN users u ON t.user_id = u.id_user
                WHERE t.id_transaction = :id";
        $transaction = $this->queryOne($sql, ['id' => $id]);

        if (!$transaction) return null;

        // Get Items
        $sqlItems = "SELECT ti.*, p.product_name, p.product_code 
                     FROM transaction_items ti
                     JOIN products p ON ti.product_id = p.id_product
                     WHERE ti.transaction_id = :id";
        $items = $this->query($sqlItems, ['id' => $id]);

        $transaction['items'] = $items;
        return $transaction;
    }
}
