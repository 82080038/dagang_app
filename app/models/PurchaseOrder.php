<?php

namespace Model;

use Core\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'id_po';
    
    protected $fillable = [
        'po_number',
        'supplier_id',
        'branch_id',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'total_amount',
        'payment_terms',
        'payment_status',
        'due_date',
        'paid_amount',
        'delivery_address',
        'delivery_contact_person',
        'delivery_phone',
        'delivery_instructions',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'notes'
    ];

    /**
     * Generate unique PO number
     */
    public function generatePONumber()
    {
        $year = date('Y');
        
        // Find the next available number for this year
        $sql = "SELECT MAX(CAST(SUBSTRING(po_number, 4) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE po_number LIKE 'PO-%' 
                AND YEAR(order_date) = :year";
        
        $result = $this->query($sql, ['year' => $year]);
        $nextNum = ($result[0]['max_num'] ?? 0) + 1;
        
        return 'PO-' . $year . '-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get purchase orders with pagination and filtering
     */
    public function getAll($limit = 10, $offset = 0, $search = '', $supplier = '', $status = '', $branchId = null)
    {
        $sql = "SELECT po.*, s.supplier_name, s.supplier_code, b.branch_name,
                m1.member_name as requested_by_name,
                m2.member_name as approved_by_name,
                DATEDIFF(po.actual_delivery_date, po.expected_delivery_date) as delivery_delay,
                CASE 
                    WHEN po.actual_delivery_date <= po.expected_delivery_date THEN 'On Time'
                    WHEN po.actual_delivery_date IS NULL THEN 'Pending'
                    ELSE 'Late'
                END as delivery_status
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id_supplier
                LEFT JOIN branches b ON po.branch_id = b.id_branch
                LEFT JOIN members m1 ON po.requested_by = m1.id_member
                LEFT JOIN members m2 ON po.approved_by = m2.id_member
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (po.po_number LIKE :search OR s.supplier_name LIKE :search OR po.notes LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($supplier) {
            $sql .= " AND po.supplier_id = :supplier";
            $params['supplier'] = $supplier;
        }
        
        if ($status) {
            $sql .= " AND po.status = :status";
            $params['status'] = $status;
        }
        
        if ($branchId) {
            $sql .= " AND po.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY po.order_date DESC, po.po_number DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }

    /**
     * Get total count for pagination
     */
    public function getTotalCount($search = '', $supplier = '', $status = '', $branchId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id_supplier
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (po.po_number LIKE :search OR s.supplier_name LIKE :search OR po.notes LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($supplier) {
            $sql .= " AND po.supplier_id = :supplier";
            $params['supplier'] = $supplier;
        }
        
        if ($status) {
            $sql .= " AND po.status = :status";
            $params['status'] = $status;
        }
        
        if ($branchId) {
            $sql .= " AND po.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $result = $this->query($sql, $params);
        return $result[0]['total'] ?? 0;
    }

    /**
     * Get PO by number
     */
    public function getByNumber($poNumber)
    {
        $sql = "SELECT po.*, s.supplier_name, s.supplier_code, b.branch_name,
                m1.member_name as requested_by_name,
                m2.member_name as approved_by_name
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id_supplier
                LEFT JOIN branches b ON po.branch_id = b.id_branch
                LEFT JOIN members m1 ON po.requested_by = m1.id_member
                LEFT JOIN members m2 ON po.approved_by = m2.id_member
                WHERE po.po_number = :po_number";
        
        $result = $this->query($sql, ['po_number' => $poNumber]);
        return $result[0] ?? null;
    }

    /**
     * Get PO with complete details
     */
    public function getPOWithDetails($id)
    {
        $sql = "SELECT po.*, s.supplier_name, s.supplier_code, s.contact_person, s.phone, s.email,
                s.payment_terms as supplier_payment_terms,
                b.branch_name, b.branch_code,
                m1.member_name as requested_by_name,
                m2.member_name as approved_by_name
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id_supplier
                LEFT JOIN branches b ON po.branch_id = b.id_branch
                LEFT JOIN members m1 ON po.requested_by = m1.id_member
                LEFT JOIN members m2 ON po.approved_by = m2.id_member
                WHERE po.id_po = :id";
        
        $result = $this->query($sql, ['id' => $id]);
        return $result[0] ?? null;
    }

    /**
     * Get PO items
     */
    public function getPOItems($poId)
    {
        $sql = "SELECT poi.*, p.product_name, p.unit, p.barcode,
                sp.supplier_product_code, sp.supplier_product_name
                FROM purchase_order_items poi
                LEFT JOIN products p ON poi.product_id = p.id_product
                LEFT JOIN supplier_products sp ON poi.supplier_product_id = sp.id_supplier_product
                WHERE poi.po_id = :po_id
                ORDER BY poi.id_po_item";
        
        return $this->query($sql, ['po_id' => $poId]);
    }

    /**
     * Create purchase order with items
     */
    public function createPurchaseOrder($data, $items = [])
    {
        // Generate PO number if not provided
        if (empty($data['po_number'])) {
            $data['po_number'] = $this->generatePONumber();
        } else {
            // Check if PO number already exists
            if ($this->getByNumber($data['po_number'])) {
                throw new \Exception('PO number already exists');
            }
        }
        
        // Set default values
        $data['order_date'] = $data['order_date'] ?? date('Y-m-d');
        $data['status'] = $data['status'] ?? 'draft';
        $data['payment_status'] = $data['payment_status'] ?? 'unpaid';
        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['shipping_cost'] = $data['shipping_cost'] ?? 0;
        $data['total_amount'] = $data['total_amount'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Calculate due date based on payment terms
        if (!empty($data['payment_terms']) && empty($data['due_date'])) {
            $days = $this->getPaymentDays($data['payment_terms']);
            $data['due_date'] = date('Y-m-d', strtotime($data['order_date'] . " +{$days} days"));
        }
        
        // Start transaction
        $this->beginTransaction();
        
        try {
            // Create PO
            $poId = $this->create($data);
            
            // Create PO items
            if (!empty($items)) {
                foreach ($items as $item) {
                    $item['po_id'] = $poId;
                    $item['subtotal'] = ($item['quantity_ordered'] * $item['unit_price']) - 
                                   (($item['quantity_ordered'] * $item['unit_price']) * ($item['discount_percentage'] / 100));
                    $item['tax_amount'] = $item['subtotal'] * 0.11; // 11% tax
                    $item['total_amount'] = $item['subtotal'] + $item['tax_amount'];
                    
                    $this->createPOItem($item);
                }
            }
            
            $this->commit();
            return $poId;
            
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Create PO item
     */
    private function createPOItem($itemData)
    {
        $sql = "INSERT INTO purchase_order_items 
                (po_id, product_id, supplier_product_id, product_code, product_name, description,
                 quantity_ordered, quantity_received, unit_price, discount_percentage, subtotal, tax_amount, total_amount, status, notes)
                VALUES (:po_id, :product_id, :supplier_product_id, :product_code, :product_name, :description,
                        :quantity_ordered, :quantity_received, :unit_price, :discount_percentage, :subtotal, :tax_amount, :total_amount, :status, :notes)";
        
        return $this->query($sql, $itemData);
    }

    /**
     * Update purchase order
     */
    public function updatePurchaseOrder($id, $data)
    {
        $po = $this->getById($id);
        if (!$po) {
            throw new \Exception('Purchase order not found');
        }
        
        // Check if PO can be updated
        if ($po['status'] === 'confirmed' || $po['status'] === 'sent') {
            throw new \Exception('Cannot update PO that has been confirmed or sent');
        }
        
        // Check if changing PO number and if it already exists
        if (isset($data['po_number']) && $data['po_number'] !== $po['po_number']) {
            if ($this->getByNumber($data['po_number'])) {
                throw new \Exception('PO number already exists');
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    /**
     * Update PO status
     */
    public function updateStatus($id, $status, $notes = '')
    {
        $updateData = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($notes) {
            $updateData['notes'] = $notes;
        }
        
        // Set actual delivery date when received
        if ($status === 'received') {
            $updateData['actual_delivery_date'] = date('Y-m-d');
        }
        
        return $this->update($id, $updateData);
    }

    /**
     * Approve PO
     */
    public function approvePO($id, $approvedBy, $approvalNotes = '')
    {
        $updateData = [
            'status' => 'confirmed',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($approvalNotes) {
            $updateData['approval_notes'] = $approvalNotes;
        }
        
        return $this->update($id, $updateData);
    }

    /**
     * Cancel PO
     */
    public function cancelPO($id, $reason = '')
    {
        $po = $this->getById($id);
        if (!$po) {
            throw new \Exception('Purchase order not found');
        }
        
        // Check if PO can be cancelled
        if ($po['status'] === 'received' || $po['status'] === 'partial_received') {
            throw new \Exception('Cannot cancel PO that has been received');
        }
        
        $updateData = [
            'status' => 'cancelled',
            'notes' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($id, $updateData);
    }

    /**
     * Update PO items received quantity
     */
    public function updateReceivedQuantity($poId, $items)
    {
        $this->beginTransaction();
        
        try {
            $totalReceived = 0;
            $allItemsReceived = true;
            
            foreach ($items as $item) {
                $sql = "UPDATE purchase_order_items 
                        SET quantity_received = :quantity_received, status = :status
                        WHERE id_po_item = :id_po_item";
                
                $status = ($item['quantity_received'] >= $item['quantity_ordered']) ? 'received' : 'partial_received';
                
                $this->query($sql, [
                    'quantity_received' => $item['quantity_received'],
                    'status' => $status,
                    'id_po_item' => $item['id_po_item']
                ]);
                
                $totalReceived += $item['quantity_received'];
                
                if ($item['quantity_received'] < $item['quantity_ordered']) {
                    $allItemsReceived = false;
                }
            }
            
            // Update PO status
            $poStatus = $allItemsReceived ? 'received' : 'partial_received';
            $this->updateStatus($poId, $poStatus);
            
            $this->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($id, $paidAmount)
    {
        $po = $this->getById($id);
        if (!$po) {
            throw new \Exception('Purchase order not found');
        }
        
        $newPaidAmount = $po['paid_amount'] + $paidAmount;
        $paymentStatus = 'unpaid';
        
        if ($newPaidAmount >= $po['total_amount']) {
            $paymentStatus = 'paid';
        } elseif ($newPaidAmount > 0) {
            $paymentStatus = 'partial';
        }
        
        $updateData = [
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($id, $updateData);
    }

    /**
     * Get PO statistics
     */
    public function getStatistics($branchId = null)
    {
        $sql = "SELECT 
                COUNT(*) as total_pos,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_pos,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_pos,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_pos,
                SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as received_pos,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_pos,
                SUM(total_amount) as total_value,
                SUM(paid_amount) as total_paid,
                SUM(CASE WHEN payment_status = 'unpaid' THEN total_amount ELSE 0 END) as unpaid_amount,
                SUM(CASE WHEN payment_status = 'overdue' THEN total_amount ELSE 0 END) as overdue_amount,
                AVG(DATEDIFF(actual_delivery_date, expected_delivery_date)) as avg_delivery_delay
                FROM {$this->table}";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " WHERE branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $result = $this->query($sql, $params);
        return $result[0] ?? [];
    }

    /**
     * Get PO status distribution
     */
    public function getStatusDistribution($branchId = null)
    {
        $sql = "SELECT status, COUNT(*) as count, SUM(total_amount) as total_value
                FROM {$this->table}
                WHERE 1=1";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY status ORDER BY 
                    CASE status
                        WHEN 'draft' THEN 1
                        WHEN 'confirmed' THEN 2
                        WHEN 'sent' THEN 3
                        WHEN 'partial_received' THEN 4
                        WHEN 'received' THEN 5
                        WHEN 'cancelled' THEN 6
                    END";
        
        return $this->query($sql, $params);
    }

    /**
     * Get monthly PO trends
     */
    public function getMonthlyTrends($months = 12, $branchId = null)
    {
        $sql = "SELECT 
                DATE_FORMAT(order_date, '%Y-%m') as month,
                COUNT(*) as pos_count,
                SUM(total_amount) as total_value,
                AVG(total_amount) as avg_value
                FROM {$this->table}
                WHERE order_date >= DATE_SUB(CURRENT_DATE, INTERVAL :months MONTH)";
        
        $params = ['months' => $months];
        
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                  ORDER BY month ASC";
        
        return $this->query($sql, $params);
    }

    /**
     * Get supplier performance from POs
     */
    public function getSupplierPerformance($supplierId, $months = 12)
    {
        $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_value,
                AVG(total_amount) as avg_order_value,
                AVG(DATEDIFF(actual_delivery_date, expected_delivery_date)) as avg_delivery_delay,
                SUM(CASE WHEN actual_delivery_date <= expected_delivery_date THEN 1 ELSE 0 END) as on_time_deliveries,
                SUM(CASE WHEN actual_delivery_date IS NOT NULL THEN 1 ELSE 0 END) as completed_deliveries
                FROM {$this->table}
                WHERE supplier_id = :supplier_id
                AND order_date >= DATE_SUB(CURRENT_DATE, INTERVAL :months MONTH)
                AND status IN ('received', 'partial_received')";
        
        $result = $this->query($sql, ['supplier_id' => $supplierId, 'months' => $months]);
        $data = $result[0] ?? [];
        
        if ($data && $data['completed_deliveries'] > 0) {
            $data['on_time_rate'] = ($data['on_time_deliveries'] / $data['completed_deliveries']) * 100;
        } else {
            $data['on_time_rate'] = 0;
        }
        
        return $data;
    }

    /**
     * Get overdue POs
     */
    public function getOverduePOs($branchId = null)
    {
        $sql = "SELECT po.*, s.supplier_name, s.supplier_code,
                DATEDIFF(CURRENT_DATE, due_date) as days_overdue
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id_supplier
                WHERE po.payment_status IN ('unpaid', 'partial')
                AND po.due_date < CURRENT_DATE
                AND po.status != 'cancelled'";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND po.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY po.due_date ASC";
        
        return $this->query($sql, $params);
    }

    /**
     * Search POs
     */
    public function searchPOs($criteria, $branchId = null)
    {
        $sql = "SELECT po.*, s.supplier_name, s.supplier_code, b.branch_name,
                m1.member_name as requested_by_name,
                m2.member_name as approved_by_name
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id_supplier
                LEFT JOIN branches b ON po.branch_id = b.id_branch
                LEFT JOIN members m1 ON po.requested_by = m1.id_member
                LEFT JOIN members m2 ON po.approved_by = m2.id_member
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($criteria['po_number'])) {
            $sql .= " AND po.po_number LIKE :po_number";
            $params['po_number'] = "%{$criteria['po_number']}%";
        }
        
        if (!empty($criteria['supplier_name'])) {
            $sql .= " AND s.supplier_name LIKE :supplier_name";
            $params['supplier_name'] = "%{$criteria['supplier_name']}%";
        }
        
        if (!empty($criteria['status'])) {
            $sql .= " AND po.status = :status";
            $params['status'] = $criteria['status'];
        }
        
        if (!empty($criteria['payment_status'])) {
            $sql .= " AND po.payment_status = :payment_status";
            $params['payment_status'] = $criteria['payment_status'];
        }
        
        if (!empty($criteria['date_from'])) {
            $sql .= " AND po.order_date >= :date_from";
            $params['date_from'] = $criteria['date_from'];
        }
        
        if (!empty($criteria['date_to'])) {
            $sql .= " AND po.order_date <= :date_to";
            $params['date_to'] = $criteria['date_to'];
        }
        
        if ($branchId) {
            $sql .= " AND po.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY po.order_date DESC, po.po_number DESC LIMIT 50";
        
        return $this->query($sql, $params);
    }

    /**
     * Get payment days from payment terms
     */
    private function getPaymentDays($paymentTerms)
    {
        $daysMap = [
            'cod' => 0,
            '7_days' => 7,
            '14_days' => 14,
            '30_days' => 30,
            '45_days' => 45,
            '60_days' => 60,
            '90_days' => 90
        ];
        
        return $daysMap[$paymentTerms] ?? 30;
    }

    /**
     * Validate PO data
     */
    public function validatePO($data)
    {
        $errors = [];
        
        // Required fields
        if (empty($data['supplier_id'])) {
            $errors['supplier_id'] = 'Supplier is required';
        }
        
        if (empty($data['branch_id'])) {
            $errors['branch_id'] = 'Branch is required';
        }
        
        if (empty($data['order_date'])) {
            $errors['order_date'] = 'Order date is required';
        }
        
        // Date validation
        if (!empty($data['order_date']) && !strtotime($data['order_date'])) {
            $errors['order_date'] = 'Invalid order date format';
        }
        
        if (!empty($data['expected_delivery_date']) && !strtotime($data['expected_delivery_date'])) {
            $errors['expected_delivery_date'] = 'Invalid expected delivery date format';
        }
        
        if (!empty($data['order_date']) && !empty($data['expected_delivery_date'])) {
            if (strtotime($data['expected_delivery_date']) < strtotime($data['order_date'])) {
                $errors['expected_delivery_date'] = 'Expected delivery date must be after order date';
            }
        }
        
        // Amount validation
        if (!empty($data['total_amount']) && $data['total_amount'] < 0) {
            $errors['total_amount'] = 'Total amount cannot be negative';
        }
        
        return $errors;
    }
}
