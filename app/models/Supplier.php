<?php

namespace Model;

use Core\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id_supplier';
    
    protected $fillable = [
        'supplier_code',
        'supplier_name',
        'supplier_type',
        'business_category',
        'tax_id',
        'tax_name',
        'is_tax_registered',
        'contact_person',
        'phone',
        'mobile',
        'email',
        'website',
        'address_id',
        'address_detail',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'postal_code',
        'business_license',
        'business_registration',
        'establishment_date',
        'capital_amount',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_branch',
        'supplier_category',
        'supplier_level',
        'total_orders',
        'total_amount',
        'average_delivery_time',
        'on_time_delivery_rate',
        'quality_score',
        'overall_score',
        'payment_terms',
        'credit_limit',
        'current_balance',
        'is_active',
        'is_blacklisted',
        'blacklist_reason',
        'notes',
        'created_by'
    ];

    /**
     * Generate unique supplier code
     */
    public function generateSupplierCode($supplierName = null)
    {
        $prefix = 'SUP';
        
        if ($supplierName) {
            // Generate from supplier name (first 3 letters)
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $supplierName);
            $prefix = strtoupper(substr($cleanName, 0, 3));
        }
        
        // Find the next available number
        $sql = "SELECT MAX(CAST(SUBSTRING(supplier_code, 4) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE supplier_code LIKE :prefix";
        $result = $this->query($sql, ['prefix' => $prefix . '%']);
        
        $nextNum = ($result[0]['max_num'] ?? 0) + 1;
        
        // Ensure 4-digit padding
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get suppliers with pagination and filtering
     */
    public function getAll($limit = 10, $offset = 0, $search = '', $category = '', $level = '', $status = '')
    {
        $sql = "SELECT s.*, 
                p.name as province_name, r.name as regency_name, 
                d.name as district_name, v.name as village_name,
                CASE 
                    WHEN s.overall_score >= 90 THEN 'Excellent'
                    WHEN s.overall_score >= 80 THEN 'Very Good'
                    WHEN s.overall_score >= 70 THEN 'Good'
                    WHEN s.overall_score >= 60 THEN 'Fair'
                    WHEN s.overall_score >= 50 THEN 'Poor'
                    ELSE 'Very Poor'
                END as performance_rating
                FROM {$this->table} s
                LEFT JOIN alamat_db.provinces p ON s.province_id = p.id_province
                LEFT JOIN alamat_db.regencies r ON s.regency_id = r.id_regency  
                LEFT JOIN alamat_db.districts d ON s.district_id = d.id_district
                LEFT JOIN alamat_db.villages v ON s.village_id = v.id_village
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (s.supplier_name LIKE :search OR s.supplier_code LIKE :search OR s.contact_person LIKE :search OR s.phone LIKE :search OR s.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($category) {
            $sql .= " AND s.supplier_category = :category";
            $params['category'] = $category;
        }
        
        if ($level) {
            $sql .= " AND s.supplier_level = :level";
            $params['level'] = $level;
        }
        
        if ($status) {
            if ($status === 'active') {
                $sql .= " AND s.is_active = 1";
            } elseif ($status === 'inactive') {
                $sql .= " AND s.is_active = 0";
            } elseif ($status === 'blacklisted') {
                $sql .= " AND s.is_blacklisted = 1";
            }
        }
        
        $sql .= " ORDER BY s.supplier_name ASC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }

    /**
     * Get total count for pagination
     */
    public function getTotalCount($search = '', $category = '', $level = '', $status = '')
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (supplier_name LIKE :search OR supplier_code LIKE :search OR contact_person LIKE :search OR phone LIKE :search OR email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($category) {
            $sql .= " AND supplier_category = :category";
            $params['category'] = $category;
        }
        
        if ($level) {
            $sql .= " AND supplier_level = :level";
            $params['level'] = $level;
        }
        
        if ($status) {
            if ($status === 'active') {
                $sql .= " AND is_active = 1";
            } elseif ($status === 'inactive') {
                $sql .= " AND is_active = 0";
            } elseif ($status === 'blacklisted') {
                $sql .= " AND is_blacklisted = 1";
            }
        }
        
        $result = $this->query($sql, $params);
        return $result[0]['total'] ?? 0;
    }

    /**
     * Get supplier by code
     */
    public function getByCode($supplierCode)
    {
        $sql = "SELECT s.*, 
                p.name as province_name, r.name as regency_name, 
                d.name as district_name, v.name as village_name
                FROM {$this->table} s
                LEFT JOIN alamat_db.provinces p ON s.province_id = p.id_province
                LEFT JOIN alamat_db.regencies r ON s.regency_id = r.id_regency  
                LEFT JOIN alamat_db.districts d ON s.district_id = d.id_district
                LEFT JOIN alamat_db.villages v ON s.village_id = v.id_village
                WHERE s.supplier_code = :supplier_code";
        
        $result = $this->query($sql, ['supplier_code' => $supplierCode]);
        return $result[0] ?? null;
    }

    /**
     * Get supplier with complete details
     */
    public function getSupplierWithDetails($id)
    {
        $sql = "SELECT s.*, 
                p.name as province_name, r.name as regency_name, 
                d.name as district_name, v.name as village_name,
                m.member_name as created_by_name
                FROM {$this->table} s
                LEFT JOIN alamat_db.provinces p ON s.province_id = p.id_province
                LEFT JOIN alamat_db.regencies r ON s.regency_id = r.id_regency  
                LEFT JOIN alamat_db.districts d ON s.district_id = d.id_district
                LEFT JOIN alamat_db.villages v ON s.village_id = v.id_village
                LEFT JOIN members m ON s.created_by = m.id_member
                WHERE s.id_supplier = :id";
        
        $result = $this->query($sql, ['id' => $id]);
        return $result[0] ?? null;
    }

    /**
     * Get supplier contacts
     */
    public function getSupplierContacts($supplierId)
    {
        $sql = "SELECT * FROM supplier_contacts 
                WHERE supplier_id = :supplier_id AND is_active = 1 
                ORDER BY is_primary DESC, contact_name ASC";
        
        return $this->query($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get supplier products
     */
    public function getSupplierProducts($supplierId)
    {
        $sql = "SELECT sp.*, p.product_name, p.unit 
                FROM supplier_products sp
                LEFT JOIN products p ON sp.product_id = p.id_product
                WHERE sp.supplier_id = :supplier_id AND sp.is_active = 1
                ORDER BY sp.supplier_product_name ASC";
        
        return $this->query($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Create supplier with validation
     */
    public function createSupplier($data)
    {
        // Validate required fields
        if (empty($data['supplier_name'])) {
            throw new \Exception('Supplier name is required');
        }
        
        // Generate supplier code if not provided
        if (empty($data['supplier_code'])) {
            $data['supplier_code'] = $this->generateSupplierCode($data['supplier_name']);
        } else {
            // Check if code already exists
            if ($this->getByCode($data['supplier_code'])) {
                throw new \Exception('Supplier code already exists');
            }
        }
        
        // Set default values
        $data['total_orders'] = $data['total_orders'] ?? 0;
        $data['total_amount'] = $data['total_amount'] ?? 0;
        $data['average_delivery_time'] = $data['average_delivery_time'] ?? 0;
        $data['on_time_delivery_rate'] = $data['on_time_delivery_rate'] ?? 0;
        $data['quality_score'] = $data['quality_score'] ?? 0;
        $data['overall_score'] = $data['overall_score'] ?? 0;
        $data['current_balance'] = $data['current_balance'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['is_blacklisted'] = $data['is_blacklisted'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Update supplier with validation
     */
    public function updateSupplier($id, $data)
    {
        $supplier = $this->getById($id);
        if (!$supplier) {
            throw new \Exception('Supplier not found');
        }
        
        // Check if changing supplier code and if it already exists
        if (isset($data['supplier_code']) && $data['supplier_code'] !== $supplier['supplier_code']) {
            if ($this->getByCode($data['supplier_code'])) {
                throw new \Exception('Supplier code already exists');
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    /**
     * Soft delete supplier (deactivate)
     */
    public function deactivateSupplier($id)
    {
        return $this->update($id, [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Activate supplier
     */
    public function activateSupplier($id)
    {
        return $this->update($id, [
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Blacklist supplier
     */
    public function blacklistSupplier($id, $reason = '')
    {
        return $this->update($id, [
            'is_blacklisted' => 1,
            'blacklist_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Remove from blacklist
     */
    public function unblacklistSupplier($id)
    {
        return $this->update($id, [
            'is_blacklisted' => 0,
            'blacklist_reason' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update supplier performance metrics
     */
    public function updatePerformanceMetrics($supplierId, $metrics)
    {
        $supplier = $this->getById($supplierId);
        if (!$supplier) {
            return false;
        }
        
        $updateData = [];
        
        if (isset($metrics['delivery_score'])) {
            $updateData['quality_score'] = $metrics['delivery_score'];
        }
        
        if (isset($metrics['quality_score'])) {
            $updateData['quality_score'] = $metrics['quality_score'];
        }
        
        if (isset($metrics['price_score'])) {
            $updateData['quality_score'] = $metrics['price_score'];
        }
        
        if (isset($metrics['service_score'])) {
            $updateData['quality_score'] = $metrics['service_score'];
        }
        
        if (isset($metrics['overall_score'])) {
            $updateData['overall_score'] = $metrics['overall_score'];
        }
        
        if (isset($metrics['average_delivery_time'])) {
            $updateData['average_delivery_time'] = $metrics['average_delivery_time'];
        }
        
        if (isset($metrics['on_time_delivery_rate'])) {
            $updateData['on_time_delivery_rate'] = $metrics['on_time_delivery_rate'];
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($supplierId, $updateData);
        }
        
        return true;
    }

    /**
     * Get supplier statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                COUNT(*) as total_suppliers,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_suppliers,
                SUM(CASE WHEN is_blacklisted = 1 THEN 1 ELSE 0 END) as blacklisted_suppliers,
                SUM(total_orders) as total_orders,
                SUM(total_amount) as total_amount,
                AVG(overall_score) as avg_performance_score,
                AVG(average_delivery_time) as avg_delivery_time,
                AVG(on_time_delivery_rate) as avg_on_time_rate,
                SUM(CASE WHEN supplier_category = 'preferred' THEN 1 ELSE 0 END) as preferred_suppliers,
                SUM(CASE WHEN supplier_category = 'strategic' THEN 1 ELSE 0 END) as strategic_suppliers
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result[0] ?? [];
    }

    /**
     * Get supplier category distribution
     */
    public function getCategoryDistribution()
    {
        $sql = "SELECT supplier_category, COUNT(*) as count, SUM(total_amount) as total_value
                FROM {$this->table} 
                WHERE is_active = 1
                GROUP BY supplier_category
                ORDER BY count DESC";
        
        return $this->query($sql);
    }

    /**
     * Get supplier level distribution
     */
    public function getLevelDistribution()
    {
        $sql = "SELECT supplier_level, COUNT(*) as count, AVG(overall_score) as avg_score
                FROM {$this->table} 
                WHERE is_active = 1
                GROUP BY supplier_level
                ORDER BY 
                    CASE supplier_level
                        WHEN 'platinum' THEN 1
                        WHEN 'gold' THEN 2
                        WHEN 'silver' THEN 3
                        WHEN 'basic' THEN 4
                    END";
        
        return $this->query($sql);
    }

    /**
     * Get top suppliers by performance
     */
    public function getTopSuppliers($limit = 10, $metric = 'total_amount')
    {
        $sql = "SELECT id_supplier, supplier_code, supplier_name, supplier_category, 
                overall_score, total_orders, total_amount, average_delivery_time, on_time_delivery_rate
                FROM {$this->table}
                WHERE is_active = 1 AND is_blacklisted = 0";
        
        switch ($metric) {
            case 'performance':
                $sql .= " ORDER BY overall_score DESC, total_amount DESC";
                break;
            case 'orders':
                $sql .= " ORDER BY total_orders DESC, overall_score DESC";
                break;
            case 'delivery':
                $sql .= " ORDER BY on_time_delivery_rate DESC, overall_score DESC";
                break;
            default:
                $sql .= " ORDER BY total_amount DESC, overall_score DESC";
        }
        
        $sql .= " LIMIT :limit";
        
        return $this->query($sql, ['limit' => $limit]);
    }

    /**
     * Get suppliers at risk (performance issues)
     */
    public function getAtRiskSuppliers($days = 90)
    {
        $sql = "SELECT s.id_supplier, s.supplier_code, s.supplier_name, s.phone, s.email,
                s.overall_score, s.total_orders, s.last_order_date,
                s.on_time_delivery_rate, s.average_delivery_time
                FROM {$this->table} s
                WHERE s.is_active = 1 
                AND s.is_blacklisted = 0
                AND (s.overall_score < 60 OR s.on_time_delivery_rate < 70)
                ORDER BY s.overall_score ASC, s.on_time_delivery_rate ASC";
        
        return $this->query($sql);
    }

    /**
     * Search suppliers by multiple criteria
     */
    public function searchSuppliers($criteria)
    {
        $sql = "SELECT s.*, 
                p.name as province_name, r.name as regency_name, 
                d.name as district_name, v.name as village_name
                FROM {$this->table} s
                LEFT JOIN alamat_db.provinces p ON s.province_id = p.id_province
                LEFT JOIN alamat_db.regencies r ON s.regency_id = r.id_regency  
                LEFT JOIN alamat_db.districts d ON s.district_id = d.id_district
                LEFT JOIN alamat_db.villages v ON s.village_id = v.id_village
                WHERE s.is_active = 1";
        
        $params = [];
        
        if (!empty($criteria['name'])) {
            $sql .= " AND s.supplier_name LIKE :name";
            $params['name'] = "%{$criteria['name']}%";
        }
        
        if (!empty($criteria['contact_person'])) {
            $sql .= " AND s.contact_person LIKE :contact_person";
            $params['contact_person'] = "%{$criteria['contact_person']}%";
        }
        
        if (!empty($criteria['phone'])) {
            $sql .= " AND s.phone LIKE :phone";
            $params['phone'] = "%{$criteria['phone']}%";
        }
        
        if (!empty($criteria['email'])) {
            $sql .= " AND s.email LIKE :email";
            $params['email'] = "%{$criteria['email']}%";
        }
        
        if (!empty($criteria['category'])) {
            $sql .= " AND s.supplier_category = :category";
            $params['category'] = $criteria['category'];
        }
        
        if (!empty($criteria['level'])) {
            $sql .= " AND s.supplier_level = :level";
            $params['level'] = $criteria['level'];
        }
        
        if (!empty($criteria['province_id'])) {
            $sql .= " AND s.province_id = :province_id";
            $params['province_id'] = $criteria['province_id'];
        }
        
        $sql .= " ORDER BY s.supplier_name LIMIT 50";
        
        return $this->query($sql, $params);
    }

    /**
     * Validate supplier data
     */
    public function validateSupplier($data)
    {
        $errors = [];
        
        // Required fields
        if (empty($data['supplier_name'])) {
            $errors['supplier_name'] = 'Supplier name is required';
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Phone validation (basic)
        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone format';
        }
        
        // Tax ID validation (Indonesian NPWP format)
        if (!empty($data['tax_id']) && !preg_match('/^[0-9]{2}\.[0-9]{3}\.[0-9]{3}\.[0-9]-[0-9]{3}\.[0-9]{3}$/', $data['tax_id'])) {
            $errors['tax_id'] = 'Invalid NPWP format (XX.XXX.XXX.X-XXX.XXX)';
        }
        
        // Credit limit validation
        if (!empty($data['credit_limit']) && $data['credit_limit'] < 0) {
            $errors['credit_limit'] = 'Credit limit cannot be negative';
        }
        
        return $errors;
    }

    /**
     * Add supplier contact
     */
    public function addSupplierContact($supplierId, $contactData)
    {
        $sql = "INSERT INTO supplier_contacts 
                (supplier_id, contact_name, contact_position, contact_department, phone, mobile, email, is_primary, notes)
                VALUES (:supplier_id, :contact_name, :contact_position, :contact_department, :phone, :mobile, :email, :is_primary, :notes)";
        
        // If this is primary, unset existing primary
        if (!empty($contactData['is_primary'])) {
            $this->query("UPDATE supplier_contacts SET is_primary = 0 WHERE supplier_id = :supplier_id", ['supplier_id' => $supplierId]);
        }
        
        return $this->query($sql, array_merge(['supplier_id' => $supplierId], $contactData));
    }

    /**
     * Update supplier contact
     */
    public function updateSupplierContact($contactId, $contactData)
    {
        $sql = "UPDATE supplier_contacts SET 
                contact_name = :contact_name,
                contact_position = :contact_position,
                contact_department = :contact_department,
                phone = :phone,
                mobile = :mobile,
                email = :email,
                notes = :notes
                WHERE id_contact = :id_contact";
        
        $contactData['id_contact'] = $contactId;
        
        return $this->query($sql, $contactData);
    }

    /**
     * Delete supplier contact
     */
    public function deleteSupplierContact($contactId)
    {
        $sql = "UPDATE supplier_contacts SET is_active = 0 WHERE id_contact = :id_contact";
        return $this->query($sql, ['id_contact' => $contactId]);
    }

    /**
     * Get supplier purchase history
     */
    public function getPurchaseHistory($supplierId, $limit = 20, $offset = 0)
    {
        $sql = "SELECT po.*, b.branch_name,
                DATEDIFF(po.actual_delivery_date, po.expected_delivery_date) as delivery_delay,
                CASE 
                    WHEN po.actual_delivery_date <= po.expected_delivery_date THEN 'On Time'
                    WHEN po.actual_delivery_date IS NULL THEN 'Pending'
                    ELSE 'Late'
                END as delivery_status
                FROM purchase_orders po
                LEFT JOIN branches b ON po.branch_id = b.id_branch
                WHERE po.supplier_id = :supplier_id
                ORDER BY po.order_date DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->query($sql, ['supplier_id' => $supplierId, 'limit' => $limit, 'offset' => $offset]);
    }
}
