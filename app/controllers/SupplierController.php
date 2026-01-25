<?php

namespace Controller;

use Core\Controller;
use Model\Supplier;
use Model\PurchaseOrder;

class SupplierController extends Controller
{
    private $supplierModel;
    private $purchaseOrderModel;

    public function __construct()
    {
        parent::__construct();
        $this->supplierModel = new Supplier();
        $this->purchaseOrderModel = new PurchaseOrder();
    }

    /**
     * Display suppliers list
     */
    public function index()
    {
        // Check permissions - Manager and above can view suppliers
        $this->requirePermission(ROLE_MANAGER);

        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $level = $_GET['level'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get suppliers with filtering
        $suppliers = $this->supplierModel->getAll($limit, $offset, $search, $category, $level, $status);
        $totalCount = $this->supplierModel->getTotalCount($search, $category, $level, $status);
        $totalPages = ceil($totalCount / $limit);

        // Get statistics
        $statistics = $this->supplierModel->getStatistics();
        $categoryDistribution = $this->supplierModel->getCategoryDistribution();
        $levelDistribution = $this->supplierModel->getLevelDistribution();

        // Get filter options
        $categories = ['regular', 'preferred', 'strategic', 'backup', 'blacklisted'];
        $levels = ['basic', 'silver', 'gold', 'platinum'];
        $statuses = ['active', 'inactive', 'blacklisted'];

        include_once '../app/views/suppliers/index.php';
    }

    /**
     * Create new supplier
     */
    public function create()
    {
        // Check permissions - Manager and above can create suppliers
        $this->requirePermission(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
            return;
        }

        // Get provinces for address form
        $provinces = $this->getProvinces();
        
        include_once '../app/views/suppliers/create.php';
    }

    /**
     * Handle supplier creation
     */
    private function handleCreate()
    {
        try {
            // Validate input
            $data = $this->validateSupplierData($_POST);
            
            // Create supplier
            $supplierId = $this->supplierModel->createSupplier($data);
            
            if ($supplierId) {
                // Add primary contact if provided
                if (!empty($_POST['contact_name'])) {
                    $contactData = [
                        'contact_name' => $_POST['contact_name'],
                        'contact_position' => $_POST['contact_position'] ?? '',
                        'contact_department' => $_POST['contact_department'] ?? '',
                        'phone' => $_POST['contact_phone'] ?? '',
                        'mobile' => $_POST['contact_mobile'] ?? '',
                        'email' => $_POST['contact_email'] ?? '',
                        'is_primary' => 1,
                        'notes' => $_POST['contact_notes'] ?? ''
                    ];
                    
                    $this->supplierModel->addSupplierContact($supplierId, $contactData);
                }
                
                $_SESSION['flash_success'] = 'Supplier created successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Supplier created successfully',
                    'supplier_id' => $supplierId
                ]);
            } else {
                throw new \Exception('Failed to create supplier');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Edit supplier
     */
    public function edit($id)
    {
        // Check permissions - Manager and above can edit suppliers
        $this->requirePermission(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate($id);
            return;
        }

        $supplier = $this->supplierModel->getSupplierWithDetails($id);
        if (!$supplier) {
            $_SESSION['flash_error'] = 'Supplier not found';
            header('Location: index.php?page=suppliers');
            exit;
        }

        // Get supplier contacts
        $contacts = $this->supplierModel->getSupplierContacts($id);

        // Get provinces for address form
        $provinces = $this->getProvinces();

        include_once '../app/views/suppliers/edit.php';
    }

    /**
     * Handle supplier update
     */
    private function handleUpdate($id)
    {
        try {
            // Validate input
            $data = $this->validateSupplierData($_POST, $id);
            
            // Update supplier
            $success = $this->supplierModel->updateSupplier($id, $data);
            
            if ($success) {
                $_SESSION['flash_success'] = 'Supplier updated successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Supplier updated successfully'
                ]);
            } else {
                throw new \Exception('Failed to update supplier');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * View supplier details
     */
    public function view($id)
    {
        // Check permissions - Staff and above can view supplier details
        $this->requirePermission(ROLE_STAFF);

        $supplier = $this->supplierModel->getSupplierWithDetails($id);
        if (!$supplier) {
            $_SESSION['flash_error'] = 'Supplier not found';
            header('Location: index.php?page=suppliers');
            exit;
        }

        // Get supplier contacts
        $contacts = $this->supplierModel->getSupplierContacts($id);
        
        // Get supplier products
        $products = $this->supplierModel->getSupplierProducts($id);

        // Get purchase history
        $purchaseHistory = $this->supplierModel->getPurchaseHistory($id, 10);

        include_once '../app/views/suppliers/view.php';
    }

    /**
     * Delete supplier (soft delete)
     */
    public function delete($id)
    {
        // Check permissions - Admin and above can delete suppliers
        $this->requirePermission(ROLE_ADMIN);

        try {
            $supplier = $this->supplierModel->getById($id);
            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            // Check if supplier has active purchase orders
            $poCount = $this->getSupplierPOCount($id);
            
            if ($poCount > 0) {
                throw new \Exception("Cannot delete supplier with $poCount active purchase orders. Deactivate instead.");
            }

            $success = $this->supplierModel->deactivateSupplier($id);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Supplier deactivated successfully'
                ]);
            } else {
                throw new \Exception('Failed to deactivate supplier');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Activate supplier
     */
    public function activate($id)
    {
        $this->requirePermission(ROLE_MANAGER);

        try {
            $success = $this->supplierModel->activateSupplier($id);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Supplier activated successfully'
                ]);
            } else {
                throw new \Exception('Failed to activate supplier');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Blacklist supplier
     */
    public function blacklist($id)
    {
        $this->requirePermission(ROLE_ADMIN);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = $_POST['reason'] ?? '';
            
            try {
                $success = $this->supplierModel->blacklistSupplier($id, $reason);
                
                if ($success) {
                    $this->json([
                        'status' => 'success',
                        'message' => 'Supplier blacklisted successfully'
                    ]);
                } else {
                    throw new \Exception('Failed to blacklist supplier');
                }
            } catch (\Exception $e) {
                $this->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 400);
            }
        }
    }

    /**
     * Remove from blacklist
     */
    public function unblacklist($id)
    {
        $this->requirePermission(ROLE_ADMIN);

        try {
            $success = $this->supplierModel->unblacklistSupplier($id);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Supplier removed from blacklist successfully'
                ]);
            } else {
                throw new \Exception('Failed to remove supplier from blacklist');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Purchase Orders Management
     */
    public function purchaseOrders()
    {
        $this->requirePermission(ROLE_MANAGER);

        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $supplier = $_GET['supplier'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $userBranchId = $this->getUserBranchId();
        $userCompanyId = $this->getUserCompanyId();
        $userRole = $this->getUserRole();

        // Filter POs based on user role
        $branchId = null;
        if ($userRole <= ROLE_COMPANY_OWNER) {
            // Company owners can see all POs in their company
            $branchId = null;
        } else {
            // Other users can only see POs in their branch
            $branchId = $userBranchId;
        }

        $purchaseOrders = $this->purchaseOrderModel->getAll($limit, $offset, $search, $supplier, $status, $branchId);
        $totalCount = $this->purchaseOrderModel->getTotalCount($search, $supplier, $status, $branchId);
        $totalPages = ceil($totalCount / $limit);

        // Get statistics
        $statistics = $this->purchaseOrderModel->getStatistics($branchId);
        $statusDistribution = $this->purchaseOrderModel->getStatusDistribution($branchId);

        // Get suppliers for filter
        $suppliers = $this->getSuppliersForFilter();

        include_once '../app/views/suppliers/purchase_orders.php';
    }

    /**
     * Create purchase order
     */
    public function createPO()
    {
        $this->requirePermission(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreatePO();
            return;
        }

        // Get suppliers
        $suppliers = $this->getSuppliersForFilter();
        
        // Get products
        $products = $this->getProductsForPO();

        include_once '../app/views/suppliers/create_po.php';
    }

    /**
     * Handle purchase order creation
     */
    private function handleCreatePO()
    {
        try {
            // Validate PO data
            $poData = $this->validatePOData($_POST);
            
            // Validate PO items
            $items = $this->validatePOItems($_POST['items'] ?? []);
            
            if (empty($items)) {
                throw new \Exception('Purchase order must have at least one item');
            }
            
            // Set user branch
            $poData['branch_id'] = $this->getUserBranchId();
            $poData['requested_by'] = $this->getUserId();
            
            // Create PO
            $poId = $this->purchaseOrderModel->createPurchaseOrder($poData, $items);
            
            if ($poId) {
                $_SESSION['flash_success'] = 'Purchase order created successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Purchase order created successfully',
                    'po_id' => $poId,
                    'po_number' => $poData['po_number'] ?? $this->purchaseOrderModel->getById($poId)['po_number']
                ]);
            } else {
                throw new \Exception('Failed to create purchase order');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * View purchase order details
     */
    public function viewPO($id)
    {
        $this->requirePermission(ROLE_STAFF);

        $po = $this->purchaseOrderModel->getPOWithDetails($id);
        if (!$po) {
            $_SESSION['flash_error'] = 'Purchase order not found';
            header('Location: index.php?page=suppliers&action=purchaseOrders');
            exit;
        }

        // Check access permissions
        if (!$this->canAccessPO($po)) {
            $_SESSION['flash_error'] = 'Access denied';
            header('Location: index.php?page=suppliers&action=purchaseOrders');
            exit;
        }

        // Get PO items
        $items = $this->purchaseOrderModel->getPOItems($id);

        include_once '../app/views/suppliers/view_po.php';
    }

    /**
     * Edit purchase order
     */
    public function editPO($id)
    {
        $this->requirePermission(ROLE_MANAGER);

        $po = $this->purchaseOrderModel->getPOWithDetails($id);
        if (!$po) {
            $_SESSION['flash_error'] = 'Purchase order not found';
            header('Location: index.php?page=suppliers&action=purchaseOrders');
            exit;
        }

        // Check if PO can be edited
        if ($po['status'] !== 'draft') {
            $_SESSION['flash_error'] = 'Only draft purchase orders can be edited';
            header('Location: index.php?page=suppliers&action=viewPO&id=' . $id);
            exit;
        }

        // Check access permissions
        if (!$this->canAccessPO($po)) {
            $_SESSION['flash_error'] = 'Access denied';
            header('Location: index.php?page=suppliers&action=purchaseOrders');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdatePO($id);
            return;
        }

        // Get PO items
        $items = $this->purchaseOrderModel->getPOItems($id);

        // Get suppliers
        $suppliers = $this->getSuppliersForFilter();
        
        // Get products
        $products = $this->getProductsForPO();

        include_once '../app/views/suppliers/edit_po.php';
    }

    /**
     * Handle purchase order update
     */
    private function handleUpdatePO($id)
    {
        try {
            // Validate PO data
            $poData = $this->validatePOData($_POST, $id);
            
            // Validate PO items
            $items = $this->validatePOItems($_POST['items'] ?? []);
            
            if (empty($items)) {
                throw new \Exception('Purchase order must have at least one item');
            }
            
            // Update PO
            $success = $this->purchaseOrderModel->updatePurchaseOrder($id, $poData);
            
            if ($success) {
                $_SESSION['flash_success'] = 'Purchase order updated successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Purchase order updated successfully'
                ]);
            } else {
                throw new \Exception('Failed to update purchase order');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Get supplier details for AJAX
     */
    public function apiGetSupplier($id)
    {
        $this->requirePermission(ROLE_STAFF);

        $supplier = $this->supplierModel->getSupplierWithDetails($id);
        if (!$supplier) {
            $this->json([
                'status' => 'error',
                'message' => 'Supplier not found'
            ], 404);
        }

        $this->json([
            'status' => 'success',
            'supplier' => $supplier
        ]);
    }

    /**
     * API: Search suppliers
     */
    public function apiSearchSuppliers()
    {
        $this->requirePermission(ROLE_STAFF);

        $query = $_GET['q'] ?? '';
        $limit = $_GET['limit'] ?? 20;

        if (strlen($query) < 2) {
            $this->json([
                'status' => 'success',
                'suppliers' => []
            ]);
        }

        $criteria = ['name' => $query];
        $suppliers = $this->supplierModel->searchSuppliers($criteria);

        // Limit results
        $suppliers = array_slice($suppliers, 0, $limit);

        $this->json([
            'status' => 'success',
            'suppliers' => $suppliers
        ]);
    }

    /**
     * API: Get supplier statistics
     */
    public function apiGetStatistics()
    {
        $this->requirePermission(ROLE_MANAGER);

        $statistics = $this->supplierModel->getStatistics();
        $categoryDistribution = $this->supplierModel->getCategoryDistribution();
        $levelDistribution = $this->supplierModel->getLevelDistribution();
        $topSuppliers = $this->supplierModel->getTopSuppliers(10);
        $atRiskSuppliers = $this->supplierModel->getAtRiskSuppliers();

        $this->json([
            'status' => 'success',
            'statistics' => $statistics,
            'category_distribution' => $categoryDistribution,
            'level_distribution' => $levelDistribution,
            'top_suppliers' => $topSuppliers,
            'at_risk_suppliers' => $atRiskSuppliers
        ]);
    }

    /**
     * API: Approve PO
     */
    public function apiApprovePO($id)
    {
        $this->requirePermission(ROLE_MANAGER);

        try {
            $approvalNotes = $_POST['approval_notes'] ?? '';
            $approvedBy = $this->getUserId();
            
            $success = $this->purchaseOrderModel->approvePO($id, $approvedBy, $approvalNotes);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Purchase order approved successfully'
                ]);
            } else {
                throw new \Exception('Failed to approve purchase order');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Cancel PO
     */
    public function apiCancelPO($id)
    {
        $this->requirePermission(ROLE_MANAGER);

        try {
            $reason = $_POST['reason'] ?? '';
            
            $success = $this->purchaseOrderModel->cancelPO($id, $reason);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Purchase order cancelled successfully'
                ]);
            } else {
                throw new \Exception('Failed to cancel purchase order');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Update PO status
     */
    public function apiUpdatePOStatus($id)
    {
        $this->requirePermission(ROLE_MANAGER);

        try {
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            $success = $this->purchaseOrderModel->updateStatus($id, $status, $notes);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Purchase order status updated successfully'
                ]);
            } else {
                throw new \Exception('Failed to update purchase order status');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validate supplier data
     */
    private function validateSupplierData($data, $supplierId = null)
    {
        $errors = $this->supplierModel->validateSupplier($data);
        
        if (!empty($errors)) {
            $errorMessages = [];
            foreach ($errors as $field => $message) {
                $errorMessages[] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $message;
            }
            throw new \Exception(implode(', ', $errorMessages));
        }

        // Clean and prepare data
        $cleanData = [
            'supplier_code' => trim($data['supplier_code'] ?? ''),
            'supplier_name' => trim($data['supplier_name'] ?? ''),
            'supplier_type' => $data['supplier_type'] ?? 'company',
            'business_category' => $data['business_category'] ?? 'wholesale',
            'tax_id' => trim($data['tax_id'] ?? ''),
            'tax_name' => trim($data['tax_name'] ?? ''),
            'is_tax_registered' => isset($data['is_tax_registered']) ? 1 : 0,
            'contact_person' => trim($data['contact_person'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'mobile' => trim($data['mobile'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'website' => trim($data['website'] ?? ''),
            'address_detail' => trim($data['address_detail'] ?? ''),
            'province_id' => (int)($data['province_id'] ?? 0),
            'regency_id' => (int)($data['regency_id'] ?? 0),
            'district_id' => (int)($data['district_id'] ?? 0),
            'village_id' => (int)($data['village_id'] ?? 0),
            'postal_code' => trim($data['postal_code'] ?? ''),
            'business_license' => trim($data['business_license'] ?? ''),
            'business_registration' => trim($data['business_registration'] ?? ''),
            'establishment_date' => $data['establishment_date'] ?? null,
            'capital_amount' => (float)($data['capital_amount'] ?? 0),
            'bank_name' => trim($data['bank_name'] ?? ''),
            'bank_account_number' => trim($data['bank_account_number'] ?? ''),
            'bank_account_name' => trim($data['bank_account_name'] ?? ''),
            'bank_branch' => trim($data['bank_branch'] ?? ''),
            'supplier_category' => $data['supplier_category'] ?? 'regular',
            'supplier_level' => $data['supplier_level'] ?? 'basic',
            'payment_terms' => $data['payment_terms'] ?? '30_days',
            'credit_limit' => (float)($data['credit_limit'] ?? 0),
            'notes' => trim($data['notes'] ?? ''),
            'created_by' => $_SESSION['user_id'] ?? null
        ];

        return $cleanData;
    }

    /**
     * Validate PO data
     */
    private function validatePOData($data, $poId = null)
    {
        $errors = $this->purchaseOrderModel->validatePO($data);
        
        if (!empty($errors)) {
            $errorMessages = [];
            foreach ($errors as $field => $message) {
                $errorMessages[] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $message;
            }
            throw new \Exception(implode(', ', $errorMessages));
        }

        return $data;
    }

    /**
     * Validate PO items
     */
    private function validatePOItems($items)
    {
        $validatedItems = [];
        
        foreach ($items as $item) {
            if (empty($item['product_name']) || $item['quantity_ordered'] <= 0 || $item['unit_price'] <= 0) {
                throw new \Exception('All items must have valid name, quantity, and unit price');
            }
            
            $validatedItems[] = [
                'product_id' => $item['product_id'] ?? null,
                'supplier_product_id' => $item['supplier_product_id'] ?? null,
                'product_code' => $item['product_code'] ?? '',
                'product_name' => $item['product_name'],
                'description' => $item['description'] ?? '',
                'quantity_ordered' => (float)($item['quantity_ordered']),
                'unit_price' => (float)($item['unit_price']),
                'discount_percentage' => (float)($item['discount_percentage'] ?? 0),
                'notes' => $item['notes'] ?? ''
            ];
        }
        
        return $validatedItems;
    }

    /**
     * Check if user can access PO
     */
    private function canAccessPO($po)
    {
        $userRole = $this->getUserRole();
        $userBranchId = $this->getUserBranchId();
        $userCompanyId = $this->getUserCompanyId();
        
        // Application admins can access all POs
        if ($this->getAppRole() <= ROLE_APP_ADMIN) {
            return true;
        }
        
        // Company owners can access all POs in their company
        if ($userRole <= ROLE_COMPANY_OWNER) {
            return true;
        }
        
        // Other users can only access POs in their branch
        return $po['branch_id'] == $userBranchId;
    }

    /**
     * Get supplier PO count
     */
    private function getSupplierPOCount($supplierId)
    {
        $sql = "SELECT COUNT(*) as count FROM purchase_orders 
                WHERE supplier_id = :supplier_id AND status != 'cancelled'";
        
        $result = $this->purchaseOrderModel->query($sql, ['supplier_id' => $supplierId]);
        return $result[0]['count'] ?? 0;
    }

    /**
     * Get provinces for address form
     */
    private function getProvinces()
    {
        try {
            $sql = "SELECT id_province, name FROM alamat_db.provinces ORDER BY name";
            $result = $this->supplierModel->query($sql);
            return $result ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get suppliers for filter
     */
    private function getSuppliersForFilter()
    {
        try {
            $sql = "SELECT id_supplier, supplier_name, supplier_code 
                    FROM suppliers 
                    WHERE is_active = 1 
                    ORDER BY supplier_name ASC";
            $result = $this->supplierModel->query($sql);
            return $result ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get products for PO
     */
    private function getProductsForPO()
    {
        try {
            $userBranchId = $this->getUserBranchId();
            
            $sql = "SELECT p.id_product, p.product_code, p.product_name, p.unit, p.selling_price
                    FROM products p
                    LEFT JOIN branch_inventory bi ON p.id_product = bi.product_id
                    WHERE p.is_active = 1 AND bi.branch_id = :branch_id
                    ORDER BY p.product_name ASC";
            
            $result = $this->supplierModel->query($sql, ['branch_id' => $userBranchId]);
            return $result ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
