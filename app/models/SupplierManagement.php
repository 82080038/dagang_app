<?php

class SupplierManagement extends Model
{
    protected $table = 'suppliers';
    
    protected $fillable = [
        'id_supplier',
        'supplier_code',
        'supplier_name',
        'supplier_type',
        'business_category',
        'contact_person',
        'phone',
        'email',
        'website',
        'address_id',
        'tax_id',
        'business_license',
        'payment_terms',
        'credit_limit',
        'currency',
        'is_active',
        'is_preferred',
        'rating',
        'notes',
        'company_id',
        'created_by',
        'created_at',
        'updated_at'
    ];

    /**
     * Supplier CRUD Operations
     */
    
    public function createSupplier($supplierData)
    {
        $supplier = [
            'id_supplier' => $this->generateSupplierId(),
            'supplier_code' => $this->generateSupplierCode($supplierData['supplier_name']),
            'supplier_name' => $supplierData['supplier_name'],
            'supplier_type' => $supplierData['supplier_type'] ?? 'regular',
            'business_category' => $supplierData['business_category'] ?? 'general',
            'contact_person' => $supplierData['contact_person'] ?? '',
            'phone' => $supplierData['phone'] ?? '',
            'email' => $supplierData['email'] ?? '',
            'website' => $supplierData['website'] ?? '',
            'address_id' => $supplierData['address_id'] ?? null,
            'tax_id' => $supplierData['tax_id'] ?? '',
            'business_license' => $supplierData['business_license'] ?? '',
            'payment_terms' => $supplierData['payment_terms'] ?? '30',
            'credit_limit' => $supplierData['credit_limit'] ?? 0,
            'currency' => $supplierData['currency'] ?? 'IDR',
            'is_active' => 1,
            'is_preferred' => $supplierData['is_preferred'] ?? 0,
            'rating' => $supplierData['rating'] ?? 3,
            'notes' => $supplierData['notes'] ?? '',
            'company_id' => $_SESSION['company_id'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($supplier);
    }

    public function updateSupplier($supplierId, $supplierData)
    {
        $updateData = [
            'supplier_name' => $supplierData['supplier_name'],
            'supplier_type' => $supplierData['supplier_type'],
            'business_category' => $supplierData['business_category'],
            'contact_person' => $supplierData['contact_person'],
            'phone' => $supplierData['phone'],
            'email' => $supplierData['email'],
            'website' => $supplierData['website'],
            'address_id' => $supplierData['address_id'],
            'tax_id' => $supplierData['tax_id'],
            'business_license' => $supplierData['business_license'],
            'payment_terms' => $supplierData['payment_terms'],
            'credit_limit' => $supplierData['credit_limit'],
            'currency' => $supplierData['currency'],
            'is_preferred' => $supplierData['is_preferred'] ?? 0,
            'rating' => $supplierData['rating'],
            'notes' => $supplierData['notes'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($supplierId, $updateData);
    }

    public function getSupplierById($supplierId)
    {
        $sql = "SELECT s.*, a.street_address as address_detail, a.postal_code,
                       p.name as province_name, r.name as regency_name, 
                       d.name as district_name, v.name as village_name
                FROM {$this->table} s
                LEFT JOIN addresses a ON s.address_id = a.id_address
                LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
                LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
                LEFT JOIN alamat_db.districts d ON a.district_id = d.id
                LEFT JOIN alamat_db.villages v ON a.village_id = v.id
                WHERE s.id_supplier = :supplier_id AND s.company_id = :company_id";
        
        return $this->queryOne($sql, [
            'supplier_id' => $supplierId,
            'company_id' => $_SESSION['company_id'] ?? null
        ]);
    }

    public function getSuppliers($filters = [], $limit = 50, $offset = 0)
    {
        $whereClause = "WHERE s.company_id = :company_id";
        $params = ['company_id' => $_SESSION['company_id'] ?? null];

        if (!empty($filters['supplier_type'])) {
            $whereClause .= " AND s.supplier_type = :supplier_type";
            $params['supplier_type'] = $filters['supplier_type'];
        }

        if (!empty($filters['business_category'])) {
            $whereClause .= " AND s.business_category = :business_category";
            $params['business_category'] = $filters['business_category'];
        }

        if (!empty($filters['is_active'])) {
            $whereClause .= " AND s.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        if (!empty($filters['is_preferred'])) {
            $whereClause .= " AND s.is_preferred = :is_preferred";
            $params['is_preferred'] = $filters['is_preferred'];
        }

        if (!empty($filters['search'])) {
            $whereClause .= " AND (s.supplier_name LIKE :search OR s.contact_person LIKE :search OR s.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql = "SELECT s.*, a.street_address as address_detail, a.postal_code,
                       p.name as province_name, r.name as regency_name, 
                       d.name as district_name, v.name as village_name
                FROM {$this->table} s
                LEFT JOIN addresses a ON s.address_id = a.id_address
                LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
                LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
                LEFT JOIN alamat_db.districts d ON a.district_id = d.id
                LEFT JOIN alamat_db.villages v ON a.village_id = v.id
                {$whereClause}
                ORDER BY s.is_preferred DESC, s.supplier_name ASC
                LIMIT :limit OFFSET :offset";

        return $this->query($sql, $params);
    }

    public function getPreferredSuppliers()
    {
        $sql = "SELECT s.*, a.street_address as address_detail, a.postal_code,
                       p.name as province_name, r.name as regency_name, 
                       d.name as district_name, v.name as village_name
                FROM {$this->table} s
                LEFT JOIN addresses a ON s.address_id = a.id_address
                LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
                LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
                LEFT JOIN alamat_db.districts d ON a.district_id = d.id
                LEFT JOIN alamat_db.villages v ON a.village_id = v.id
                WHERE s.company_id = :company_id AND s.is_active = 1 AND s.is_preferred = 1
                ORDER BY s.supplier_name ASC";

        return $this->query($sql, ['company_id' => $_SESSION['company_id'] ?? null]);
    }

    public function toggleSupplierStatus($supplierId)
    {
        $supplier = $this->getSupplierById($supplierId);
        if (!$supplier) {
            return false;
        }

        $newStatus = $supplier['is_active'] ? 0 : 1;
        return $this->updateByField('id_supplier', $supplierId, [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function togglePreferredStatus($supplierId)
    {
        $supplier = $this->getSupplierById($supplierId);
        if (!$supplier) {
            return false;
        }

        $newStatus = $supplier['is_preferred'] ? 0 : 1;
        return $this->updateByField('id_supplier', $supplierId, [
            'is_preferred' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Supplier Products Management
     */
    
    public function getSupplierProducts($supplierId)
    {
        $sql = "SELECT sp.*, p.product_name, p.product_code, p.unit, p.price
                FROM supplier_products sp
                JOIN products p ON sp.product_id = p.id_product
                WHERE sp.supplier_id = :supplier_id AND sp.is_active = 1
                ORDER BY p.product_name";

        return $this->query($sql, ['supplier_id' => $supplierId]);
    }

    public function addSupplierProduct($supplierId, $productId, $productData)
    {
        $supplierProduct = [
            'id_supplier_product' => $this->generateSupplierProductId(),
            'supplier_id' => $supplierId,
            'product_id' => $productId,
            'supplier_sku' => $productData['supplier_sku'] ?? '',
            'supplier_price' => $productData['supplier_price'] ?? 0,
            'min_order_qty' => $productData['min_order_qty'] ?? 1,
            'lead_time_days' => $productData['lead_time_days'] ?? 0,
            'is_active' => 1,
            'notes' => $productData['notes'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->execute("INSERT INTO supplier_products SET " . $this->buildSetClause($supplierProduct), $supplierProduct);
    }

    /**
     * Purchase Orders Management
     */
    
    public function createPurchaseOrder($poData)
    {
        $purchaseOrder = [
            'id_purchase_order' => $this->generatePurchaseOrderId(),
            'po_number' => $this->generatePONumber(),
            'supplier_id' => $poData['supplier_id'],
            'order_date' => $poData['order_date'] ?? date('Y-m-d'),
            'expected_delivery_date' => $poData['expected_delivery_date'],
            'payment_terms' => $poData['payment_terms'],
            'total_amount' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'grand_total' => 0,
            'status' => 'draft',
            'notes' => $poData['notes'] ?? '',
            'company_id' => $_SESSION['company_id'] ?? null,
            'branch_id' => $_SESSION['branch_id'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->execute("INSERT INTO purchase_orders SET " . $this->buildSetClause($purchaseOrder), $purchaseOrder);

        $totalAmount = 0;
        $taxAmount = 0;

        // Add purchase order items
        foreach ($poData['items'] as $item) {
            $poItem = [
                'id_po_item' => $this->generatePOItemId(),
                'po_id' => $purchaseOrder['id_purchase_order'],
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'tax_percent' => $item['tax_percent'] ?? 0,
                'total_price' => $item['quantity'] * $item['unit_price'],
                'notes' => $item['notes'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->execute("INSERT INTO purchase_order_items SET " . $this->buildSetClause($poItem), $poItem);

            $totalAmount += $poItem['total_price'];
        }

        // Update purchase order totals
        $grandTotal = $totalAmount + $taxAmount - $poData['discount_amount'];
        
        $this->execute("UPDATE purchase_orders SET total_amount = :total_amount, tax_amount = :tax_amount, grand_total = :grand_total WHERE id_purchase_order = :po_id", [
            'total_amount' => $totalAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'po_id' => $purchaseOrder['id_purchase_order']
        ]);

        return $purchaseOrder['id_purchase_order'];
    }

    public function getPurchaseOrders($filters = [], $limit = 50, $offset = 0)
    {
        $whereClause = "WHERE po.company_id = :company_id";
        $params = ['company_id' => $_SESSION['company_id'] ?? null];

        if (!empty($filters['supplier_id'])) {
            $whereClause .= " AND po.supplier_id = :supplier_id";
            $params['supplier_id'] = $filters['supplier_id'];
        }

        if (!empty($filters['status'])) {
            $whereClause .= " AND po.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND po.order_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND po.order_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql = "SELECT po.*, s.supplier_name, s.supplier_code,
                       COUNT(poi.id_po_item) as item_count
                FROM purchase_orders po
                JOIN suppliers s ON po.supplier_id = s.id_supplier
                LEFT JOIN purchase_order_items poi ON po.id_purchase_order = poi.po_id
                {$whereClause}
                GROUP BY po.id_purchase_order
                ORDER BY po.created_at DESC
                LIMIT :limit OFFSET :offset";

        return $this->query($sql, $params);
    }

    /**
     * Supplier Analytics
     */
    
    public function getSupplierAnalytics($supplierId = null, $dateRange = '30d')
    {
        $companyId = $_SESSION['company_id'] ?? null;
        $dateCondition = $this->getDateCondition($dateRange);

        $supplierCondition = "";
        $params = ['company_id' => $companyId];

        if ($supplierId) {
            $supplierCondition = "AND po.supplier_id = :supplier_id";
            $params['supplier_id'] = $supplierId;
        }

        $sql = "SELECT s.supplier_name, s.supplier_code,
                       COUNT(DISTINCT po.id_purchase_order) as total_orders,
                       SUM(po.grand_total) as total_purchases,
                       AVG(po.grand_total) as avg_order_value,
                       COUNT(DISTINCT CASE WHEN po.status = 'completed' THEN po.id_purchase_order END) as completed_orders,
                       COUNT(DISTINCT CASE WHEN po.status = 'pending' THEN po.id_purchase_order END) as pending_orders
                FROM suppliers s
                LEFT JOIN purchase_orders po ON s.id_supplier = po.supplier_id 
                    AND po.order_date >= {$dateCondition}
                WHERE s.company_id = :company_id {$supplierCondition}
                GROUP BY s.id_supplier
                ORDER BY total_purchases DESC";

        return $this->query($sql, $params);
    }

    public function getSupplierPerformanceMetrics($supplierId)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT CASE WHEN po.status = 'completed' THEN po.id_purchase_order END) as completed_orders,
                    COUNT(DISTINCT CASE WHEN po.status = 'cancelled' THEN po.id_purchase_order END) as cancelled_orders,
                    AVG(DATEDIFF(po.actual_delivery_date, po.expected_delivery_date)) as avg_delivery_days,
                    AVG(po.grand_total) as avg_order_value,
                    COUNT(DISTINCT po.id_purchase_order) as total_orders
                FROM purchase_orders po
                WHERE po.supplier_id = :supplier_id 
                AND po.order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";

        $result = $this->queryOne($sql, ['supplier_id' => $supplierId]);

        if ($result) {
            $result['on_time_delivery_rate'] = $result['total_orders'] > 0 ? 
                ($result['completed_orders'] / $result['total_orders']) * 100 : 0;
            $result['cancellation_rate'] = $result['total_orders'] > 0 ? 
                ($result['cancelled_orders'] / $result['total_orders']) * 100 : 0;
        }

        return $result;
    }

    /**
     * Helper Methods
     */
    
    private function generateSupplierId()
    {
        return 'SUP_' . strtoupper(uniqid()) . '_' . date('Ymd');
    }

    private function generateSupplierCode($supplierName)
    {
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $supplierName), 0, 3));
        $code .= rand(100, 999);
        
        // Ensure uniqueness
        $exists = $this->queryOne("SELECT id_supplier FROM {$this->table} WHERE supplier_code = :code", ['code' => $code]);
        while ($exists) {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $supplierName), 0, 3));
            $code .= rand(100, 999);
            $exists = $this->queryOne("SELECT id_supplier FROM {$this->table} WHERE supplier_code = :code", ['code' => $code]);
        }
        
        return $code;
    }

    private function generatePurchaseOrderId()
    {
        return 'PO_' . strtoupper(uniqid()) . '_' . date('Ymd');
    }

    private function generatePONumber()
    {
        $prefix = 'PO-' . date('Ym');
        $sequence = $this->getNextPOSequence($prefix);
        return $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    private function generateSupplierProductId()
    {
        return 'SP_' . strtoupper(uniqid()) . '_' . date('Ymd');
    }

    private function generatePOItemId()
    {
        return 'POI_' . strtoupper(uniqid()) . '_' . date('Ymd');
    }

    private function getNextPOSequence($prefix)
    {
        $sql = "SELECT COUNT(*) as count FROM purchase_orders WHERE po_number LIKE :prefix";
        $result = $this->queryOne($sql, ['prefix' => $prefix . '%']);
        return ($result['count'] ?? 0) + 1;
    }

    private function getDateCondition($dateRange)
    {
        switch ($dateRange) {
            case '7d':
                return "DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30d':
                return "DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90d':
                return "DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case '1y':
                return "DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }

    private function buildSetClause($data)
    {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :$key";
        }
        return implode(', ', $setParts);
    }
}
