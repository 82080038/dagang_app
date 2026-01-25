<?php

namespace Model;

use Core\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id_customer';
    
    protected $fillable = [
        'customer_code',
        'customer_name', 
        'customer_type',
        'business_name',
        'tax_id',
        'phone',
        'email',
        'whatsapp',
        'address_id',
        'address_detail',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'postal_code',
        'customer_segment',
        'customer_category',
        'total_purchases',
        'total_transactions',
        'average_transaction_value',
        'last_purchase_date',
        'first_purchase_date',
        'credit_limit',
        'current_debt',
        'credit_status',
        'payment_terms',
        'loyalty_points',
        'loyalty_tier',
        'membership_date',
        'preferred_contact',
        'marketing_consent',
        'notification_consent',
        'is_active',
        'is_blacklisted',
        'blacklist_reason',
        'notes',
        'created_by'
    ];

    /**
     * Generate unique customer code
     */
    public function generateCustomerCode($customerName = null)
    {
        $prefix = 'CUS';
        
        if ($customerName) {
            // Generate from customer name (first 3 letters)
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $customerName);
            $prefix = strtoupper(substr($cleanName, 0, 3));
        }
        
        // Find the next available number
        $sql = "SELECT MAX(CAST(SUBSTRING(customer_code, 4) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE customer_code LIKE :prefix";
        $result = $this->query($sql, ['prefix' => $prefix . '%']);
        
        $nextNum = ($result[0]['max_num'] ?? 0) + 1;
        
        // Ensure 4-digit padding
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get customers with pagination and filtering
     */
    public function getAll($limit = 10, $offset = 0, $search = '', $segment = '', $tier = '', $status = '')
    {
        $sql = "SELECT c.*, 
                p.name as province_name, r.name as regency_name, 
                d.name as district_name, v.name as village_name,
                CASE 
                    WHEN c.last_purchase_date IS NULL THEN 'Never Purchased'
                    WHEN DATEDIFF(CURRENT_DATE, c.last_purchase_date) <= 30 THEN 'Active'
                    WHEN DATEDIFF(CURRENT_DATE, c.last_purchase_date) <= 90 THEN 'Recent'
                    WHEN DATEDIFF(CURRENT_DATE, c.last_purchase_date) <= 180 THEN 'At Risk'
                    ELSE 'Inactive'
                END as activity_status
                FROM {$this->table} c
                LEFT JOIN alamat_db.provinces p ON c.province_id = p.id_province
                LEFT JOIN alamat_db.regencies r ON c.regency_id = r.id_regency  
                LEFT JOIN alamat_db.districts d ON c.district_id = d.id_district
                LEFT JOIN alamat_db.villages v ON c.village_id = v.id_village
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (c.customer_name LIKE :search OR c.customer_code LIKE :search OR c.phone LIKE :search OR c.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($segment) {
            $sql .= " AND c.customer_segment = :segment";
            $params['segment'] = $segment;
        }
        
        if ($tier) {
            $sql .= " AND c.loyalty_tier = :tier";
            $params['tier'] = $tier;
        }
        
        if ($status) {
            if ($status === 'active') {
                $sql .= " AND c.is_active = 1";
            } elseif ($status === 'inactive') {
                $sql .= " AND c.is_active = 0";
            } elseif ($status === 'blacklisted') {
                $sql .= " AND c.is_blacklisted = 1";
            }
        }
        
        $sql .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->query($sql, $params);
    }

    /**
     * Get total count for pagination
     */
    public function getTotalCount($search = '', $segment = '', $tier = '', $status = '')
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (customer_name LIKE :search OR customer_code LIKE :search OR phone LIKE :search OR email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($segment) {
            $sql .= " AND customer_segment = :segment";
            $params['segment'] = $segment;
        }
        
        if ($tier) {
            $sql .= " AND loyalty_tier = :tier";
            $params['tier'] = $tier;
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
     * Get customer by code
     */
    public function getByCode($customerCode)
    {
        $sql = "SELECT c.*, 
                p.name as province_name, r.name as regency_name, 
                d.name as district_name, v.name as village_name
                FROM {$this->table} c
                LEFT JOIN alamat_db.provinces p ON c.province_id = p.id_province
                LEFT JOIN alamat_db.regencies r ON c.regency_id = r.id_regency  
                LEFT JOIN alamat_db.districts d ON c.district_id = d.id_district
                LEFT JOIN alamat_db.villages v ON c.village_id = v.id_village
                WHERE c.customer_code = :customer_code";
        
        $result = $this->query($sql, ['customer_code' => $customerCode]);
        return $result[0] ?? null;
    }

    /**
     * Get customer with complete details
     */
    public function getCustomerWithDetails($id)
    {
        $sql = "SELECT c.*, 
                p.name as province_name, r.name as regency_name, 
                d.name as district_name, v.name as village_name,
                m.member_name as created_by_name
                FROM {$this->table} c
                LEFT JOIN alamat_db.provinces p ON c.province_id = p.id_province
                LEFT JOIN alamat_db.regencies r ON c.regency_id = r.id_regency  
                LEFT JOIN alamat_db.districts d ON c.district_id = d.id_district
                LEFT JOIN alamat_db.villages v ON c.village_id = v.id_village
                LEFT JOIN members m ON c.created_by = m.id_member
                WHERE c.id_customer = :id";
        
        $result = $this->query($sql, ['id' => $id]);
        return $result[0] ?? null;
    }

    /**
     * Create customer with validation
     */
    public function createCustomer($data)
    {
        // Validate required fields
        if (empty($data['customer_name'])) {
            throw new \Exception('Customer name is required');
        }
        
        // Generate customer code if not provided
        if (empty($data['customer_code'])) {
            $data['customer_code'] = $this->generateCustomerCode($data['customer_name']);
        } else {
            // Check if code already exists
            if ($this->getByCode($data['customer_code'])) {
                throw new \Exception('Customer code already exists');
            }
        }
        
        // Set default values
        $data['total_purchases'] = $data['total_purchases'] ?? 0;
        $data['total_transactions'] = $data['total_transactions'] ?? 0;
        $data['average_transaction_value'] = $data['average_transaction_value'] ?? 0;
        $data['current_debt'] = $data['current_debt'] ?? 0;
        $data['loyalty_points'] = $data['loyalty_points'] ?? 0;
        $data['membership_date'] = $data['membership_date'] ?? date('Y-m-d');
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['is_blacklisted'] = $data['is_blacklisted'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Update customer with validation
     */
    public function updateCustomer($id, $data)
    {
        $customer = $this->getById($id);
        if (!$customer) {
            throw new \Exception('Customer not found');
        }
        
        // Check if changing customer code and if it already exists
        if (isset($data['customer_code']) && $data['customer_code'] !== $customer['customer_code']) {
            if ($this->getByCode($data['customer_code'])) {
                throw new \Exception('Customer code already exists');
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    /**
     * Soft delete customer (deactivate)
     */
    public function deactivateCustomer($id)
    {
        return $this->update($id, [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Activate customer
     */
    public function activateCustomer($id)
    {
        return $this->update($id, [
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Blacklist customer
     */
    public function blacklistCustomer($id, $reason = '')
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
    public function unblacklistCustomer($id)
    {
        return $this->update($id, [
            'is_blacklisted' => 0,
            'blacklist_reason' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update customer metrics after transaction
     */
    public function updateMetrics($customerId, $transactionAmount)
    {
        $customer = $this->getById($customerId);
        if (!$customer) {
            return false;
        }
        
        $newTotal = $customer['total_purchases'] + $transactionAmount;
        $newTransactions = $customer['total_transactions'] + 1;
        $avgTransaction = $newTransactions > 0 ? $newTotal / $newTransactions : 0;
        
        return $this->update($customerId, [
            'total_purchases' => $newTotal,
            'total_transactions' => $newTransactions,
            'average_transaction_value' => $avgTransaction,
            'last_purchase_date' => date('Y-m-d'),
            'first_purchase_date' => $customer['first_purchase_date'] ?? date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update loyalty tier based on points
     */
    public function updateLoyaltyTier($customerId)
    {
        $customer = $this->getById($customerId);
        if (!$customer) {
            return false;
        }
        
        $points = $customer['loyalty_points'];
        $tier = 'bronze';
        
        if ($points >= 10000) {
            $tier = 'diamond';
        } elseif ($points >= 5000) {
            $tier = 'platinum';
        } elseif ($points >= 2000) {
            $tier = 'gold';
        } elseif ($points >= 500) {
            $tier = 'silver';
        }
        
        return $this->update($customerId, [
            'loyalty_tier' => $tier,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Add loyalty points
     */
    public function addLoyaltyPoints($customerId, $points, $referenceType = 'purchase', $referenceId = null, $description = '')
    {
        $customer = $this->getById($customerId);
        if (!$customer) {
            throw new \Exception('Customer not found');
        }
        
        // Update customer points
        $newPoints = $customer['loyalty_points'] + $points;
        $this->update($customerId, [
            'loyalty_points' => $newPoints,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Record loyalty transaction
        $sql = "INSERT INTO loyalty_transactions 
                (customer_id, transaction_type, points, reference_type, reference_id, description, transaction_date, created_by)
                VALUES (:customer_id, :transaction_type, :points, :reference_type, :reference_id, :description, :transaction_date, :created_by)";
        
        $this->query($sql, [
            'customer_id' => $customerId,
            'transaction_type' => $points > 0 ? 'earned' : 'redeemed',
            'points' => $points,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'transaction_date' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user_id'] ?? null
        ]);
        
        // Update loyalty tier
        $this->updateLoyaltyTier($customerId);
        
        return $newPoints;
    }

    /**
     * Get customer statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                COUNT(*) as total_customers,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_customers,
                SUM(CASE WHEN is_blacklisted = 1 THEN 1 ELSE 0 END) as blacklisted_customers,
                SUM(CASE WHEN last_purchase_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_30_days,
                SUM(CASE WHEN last_purchase_date >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY) THEN 1 ELSE 0 END) as active_90_days,
                SUM(total_purchases) as total_revenue,
                AVG(total_purchases) as avg_customer_value,
                SUM(loyalty_points) as total_loyalty_points,
                SUM(CASE WHEN credit_status != 'no_credit' THEN 1 ELSE 0 END) as credit_customers,
                SUM(current_debt) as total_debt
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result[0] ?? [];
    }

    /**
     * Get customer segment distribution
     */
    public function getSegmentDistribution()
    {
        $sql = "SELECT customer_segment, COUNT(*) as count, SUM(total_purchases) as total_revenue
                FROM {$this->table} 
                WHERE is_active = 1
                GROUP BY customer_segment
                ORDER BY count DESC";
        
        return $this->query($sql);
    }

    /**
     * Get loyalty tier distribution
     */
    public function getLoyaltyTierDistribution()
    {
        $sql = "SELECT loyalty_tier, COUNT(*) as count, SUM(loyalty_points) as total_points
                FROM {$this->table} 
                WHERE is_active = 1
                GROUP BY loyalty_tier
                ORDER BY 
                    CASE loyalty_tier
                        WHEN 'diamond' THEN 1
                        WHEN 'platinum' THEN 2
                        WHEN 'gold' THEN 3
                        WHEN 'silver' THEN 4
                        WHEN 'bronze' THEN 5
                    END";
        
        return $this->query($sql);
    }

    /**
     * Get top customers by purchase amount
     */
    public function getTopCustomers($limit = 10, $period = 'all')
    {
        $sql = "SELECT c.id_customer, c.customer_code, c.customer_name, c.customer_type,
                c.total_purchases, c.total_transactions, c.loyalty_tier, c.last_purchase_date
                FROM {$this->table} c
                WHERE c.is_active = 1";
        
        if ($period === '30days') {
            $sql .= " AND c.last_purchase_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
        } elseif ($period === '90days') {
            $sql .= " AND c.last_purchase_date >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)";
        }
        
        $sql .= " ORDER BY c.total_purchases DESC LIMIT :limit";
        
        return $this->query($sql, ['limit' => $limit]);
    }

    /**
     * Get customers at risk (no recent purchases)
     */
    public function getAtRiskCustomers($days = 90)
    {
        $sql = "SELECT c.id_customer, c.customer_code, c.customer_name, c.phone, c.email,
                c.last_purchase_date, c.total_purchases, c.loyalty_tier,
                DATEDIFF(CURRENT_DATE, c.last_purchase_date) as days_since_last_purchase
                FROM {$this->table} c
                WHERE c.is_active = 1 
                AND c.last_purchase_date IS NOT NULL
                AND DATEDIFF(CURRENT_DATE, c.last_purchase_date) > :days
                ORDER BY days_since_last_purchase DESC";
        
        return $this->query($sql, ['days' => $days]);
    }

    /**
     * Search customers by multiple criteria
     */
    public function searchCustomers($criteria)
    {
        $sql = "SELECT c.*, 
                p.name as province_name, r.name as regency_name, 
                d.name as district_name, v.name as village_name
                FROM {$this->table} c
                LEFT JOIN alamat_db.provinces p ON c.province_id = p.id_province
                LEFT JOIN alamat_db.regencies r ON c.regency_id = r.id_regency  
                LEFT JOIN alamat_db.districts d ON c.district_id = d.id_district
                LEFT JOIN alamat_db.villages v ON c.village_id = v.id_village
                WHERE c.is_active = 1";
        
        $params = [];
        
        if (!empty($criteria['name'])) {
            $sql .= " AND c.customer_name LIKE :name";
            $params['name'] = "%{$criteria['name']}%";
        }
        
        if (!empty($criteria['phone'])) {
            $sql .= " AND c.phone LIKE :phone";
            $params['phone'] = "%{$criteria['phone']}%";
        }
        
        if (!empty($criteria['email'])) {
            $sql .= " AND c.email LIKE :email";
            $params['email'] = "%{$criteria['email']}%";
        }
        
        if (!empty($criteria['segment'])) {
            $sql .= " AND c.customer_segment = :segment";
            $params['segment'] = $criteria['segment'];
        }
        
        if (!empty($criteria['tier'])) {
            $sql .= " AND c.loyalty_tier = :tier";
            $params['tier'] = $criteria['tier'];
        }
        
        if (!empty($criteria['province_id'])) {
            $sql .= " AND c.province_id = :province_id";
            $params['province_id'] = $criteria['province_id'];
        }
        
        $sql .= " ORDER BY c.customer_name LIMIT 50";
        
        return $this->query($sql, $params);
    }

    /**
     * Validate customer data
     */
    public function validateCustomer($data)
    {
        $errors = [];
        
        // Required fields
        if (empty($data['customer_name'])) {
            $errors['customer_name'] = 'Customer name is required';
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Phone validation (basic)
        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone format';
        }
        
        // Credit limit validation
        if (!empty($data['credit_limit']) && $data['credit_limit'] < 0) {
            $errors['credit_limit'] = 'Credit limit cannot be negative';
        }
        
        return $errors;
    }
}
