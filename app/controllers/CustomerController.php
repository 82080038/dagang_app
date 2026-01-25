<?php

namespace Controller;

use Core\Controller;
use Model\Customer;

class CustomerController extends Controller
{
    private $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel = new Customer();
    }

    /**
     * Display customers list
     */
    public function index()
    {
        // Check permissions - Manager and above can view customers
        $this->requirePermission(ROLE_MANAGER);

        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $segment = $_GET['segment'] ?? '';
        $tier = $_GET['tier'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get customers with filtering
        $customers = $this->customerModel->getAll($limit, $offset, $search, $segment, $tier, $status);
        $totalCount = $this->customerModel->getTotalCount($search, $segment, $tier, $status);
        $totalPages = ceil($totalCount / $limit);

        // Get statistics
        $statistics = $this->customerModel->getStatistics();
        $segmentDistribution = $this->customerModel->getSegmentDistribution();
        $tierDistribution = $this->customerModel->getLoyaltyTierDistribution();

        // Get filter options
        $segments = ['regular', 'vip', 'premium', 'wholesale', 'corporate'];
        $tiers = ['bronze', 'silver', 'gold', 'platinum', 'diamond'];
        $statuses = ['active', 'inactive', 'blacklisted'];

        include_once '../app/views/customers/index.php';
    }

    /**
     * Create new customer
     */
    public function create()
    {
        // Check permissions - Manager and above can create customers
        $this->requirePermission(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
            return;
        }

        // Get provinces for address form
        $provinces = $this->getProvinces();
        
        include_once '../app/views/customers/create.php';
    }

    /**
     * Handle customer creation
     */
    private function handleCreate()
    {
        try {
            // Validate input
            $data = $this->validateCustomerData($_POST);
            
            // Create customer
            $customerId = $this->customerModel->createCustomer($data);
            
            if ($customerId) {
                $_SESSION['flash_success'] = 'Customer created successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Customer created successfully',
                    'customer_id' => $customerId
                ]);
            } else {
                throw new \Exception('Failed to create customer');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Edit customer
     */
    public function edit($id)
    {
        // Check permissions - Manager and above can edit customers
        $this->requirePermission(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate($id);
            return;
        }

        $customer = $this->customerModel->getCustomerWithDetails($id);
        if (!$customer) {
            $_SESSION['flash_error'] = 'Customer not found';
            header('Location: index.php?page=customers');
            exit;
        }

        // Get provinces for address form
        $provinces = $this->getProvinces();

        include_once '../app/views/customers/edit.php';
    }

    /**
     * Handle customer update
     */
    private function handleUpdate($id)
    {
        try {
            // Validate input
            $data = $this->validateCustomerData($_POST, $id);
            
            // Update customer
            $success = $this->customerModel->updateCustomer($id, $data);
            
            if ($success) {
                $_SESSION['flash_success'] = 'Customer updated successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Customer updated successfully'
                ]);
            } else {
                throw new \Exception('Failed to update customer');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * View customer details
     */
    public function view($id)
    {
        // Check permissions - Staff and above can view customer details
        $this->requirePermission(ROLE_STAFF);

        $customer = $this->customerModel->getCustomerWithDetails($id);
        if (!$customer) {
            $_SESSION['flash_error'] = 'Customer not found';
            header('Location: index.php?page=customers');
            exit;
        }

        // Get customer transaction history (simplified - would integrate with transaction model)
        $transactionHistory = $this->getCustomerTransactionHistory($id);
        
        // Get loyalty history
        $loyaltyHistory = $this->getCustomerLoyaltyHistory($id);

        include_once '../app/views/customers/view.php';
    }

    /**
     * Delete customer (soft delete)
     */
    public function delete($id)
    {
        // Check permissions - Admin and above can delete customers
        $this->requirePermission(ROLE_ADMIN);

        try {
            $customer = $this->customerModel->getById($id);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            // Check if customer has active transactions
            if ($customer['total_transactions'] > 0) {
                throw new \Exception('Cannot delete customer with active transactions. Deactivate instead.');
            }

            $success = $this->customerModel->deactivateCustomer($id);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Customer deactivated successfully'
                ]);
            } else {
                throw new \Exception('Failed to deactivate customer');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Activate customer
     */
    public function activate($id)
    {
        $this->requirePermission(ROLE_MANAGER);

        try {
            $success = $this->customerModel->activateCustomer($id);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Customer activated successfully'
                ]);
            } else {
                throw new \Exception('Failed to activate customer');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Blacklist customer
     */
    public function blacklist($id)
    {
        $this->requirePermission(ROLE_ADMIN);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = $_POST['reason'] ?? '';
            
            try {
                $success = $this->customerModel->blacklistCustomer($id, $reason);
                
                if ($success) {
                    $this->json([
                        'status' => 'success',
                        'message' => 'Customer blacklisted successfully'
                    ]);
                } else {
                    throw new \Exception('Failed to blacklist customer');
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
            $success = $this->customerModel->unblacklistCustomer($id);
            
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Customer removed from blacklist successfully'
                ]);
            } else {
                throw new \Exception('Failed to remove customer from blacklist');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Add loyalty points
     */
    public function addLoyaltyPoints($id)
    {
        $this->requirePermission(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $points = (int)($_POST['points'] ?? 0);
            $referenceType = $_POST['reference_type'] ?? 'manual_adjustment';
            $description = $_POST['description'] ?? '';
            
            try {
                if ($points == 0) {
                    throw new \Exception('Points cannot be zero');
                }
                
                $newPoints = $this->customerModel->addLoyaltyPoints($id, $points, $referenceType, null, $description);
                
                $this->json([
                    'status' => 'success',
                    'message' => 'Loyalty points updated successfully',
                    'new_points' => $newPoints
                ]);
            } catch (\Exception $e) {
                $this->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 400);
            }
        }
    }

    /**
     * API: Get customer details for AJAX
     */
    public function apiGetCustomer($id)
    {
        $this->requirePermission(ROLE_STAFF);

        $customer = $this->customerModel->getCustomerWithDetails($id);
        if (!$customer) {
            $this->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        $this->json([
            'status' => 'success',
            'customer' => $customer
        ]);
    }

    /**
     * API: Search customers
     */
    public function apiSearchCustomers()
    {
        $this->requirePermission(ROLE_STAFF);

        $query = $_GET['q'] ?? '';
        $limit = $_GET['limit'] ?? 20;

        if (strlen($query) < 2) {
            $this->json([
                'status' => 'success',
                'customers' => []
            ]);
        }

        $criteria = ['name' => $query];
        $customers = $this->customerModel->searchCustomers($criteria);

        // Limit results
        $customers = array_slice($customers, 0, $limit);

        $this->json([
            'status' => 'success',
            'customers' => $customers
        ]);
    }

    /**
     * API: Get customer statistics
     */
    public function apiGetStatistics()
    {
        $this->requirePermission(ROLE_MANAGER);

        $statistics = $this->customerModel->getStatistics();
        $segmentDistribution = $this->customerModel->getSegmentDistribution();
        $tierDistribution = $this->customerModel->getLoyaltyTierDistribution();
        $topCustomers = $this->customerModel->getTopCustomers(10);
        $atRiskCustomers = $this->customerModel->getAtRiskCustomers(90);

        $this->json([
            'status' => 'success',
            'statistics' => $statistics,
            'segment_distribution' => $segmentDistribution,
            'tier_distribution' => $tierDistribution,
            'top_customers' => $topCustomers,
            'at_risk_customers' => $atRiskCustomers
        ]);
    }

    /**
     * Customer Relationship Management
     */
    public function crm()
    {
        $this->requirePermission(ROLE_MANAGER);

        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $segment = $_GET['segment'] ?? '';
        $tier = $_GET['tier'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get customers with CRM data
        $customers = $this->customerModel->getAllWithCRM($limit, $offset, $search, $segment, $tier, $status);
        $totalCount = $this->customerModel->getTotalCount($search, $segment, $tier, $status);
        $totalPages = ceil($totalCount / $limit);

        // Get CRM statistics
        $statistics = $this->customerModel->getCRMStatistics();
        $segmentDistribution = $this->customerModel->getSegmentDistribution();
        $tierDistribution = $this->customerModel->getLoyaltyTierDistribution();
        $recentActivities = $this->customerModel->getRecentCRMActivities(10);

        include_once '../app/views/customers/crm.php';
    }

    /**
     * Customer interaction log
     */
    public function interactions($customerId)
    {
        $this->requirePermission(ROLE_STAFF);

        $customer = $this->customerModel->getCustomerWithDetails($customerId);
        if (!$customer) {
            $_SESSION['flash_error'] = 'Customer not found';
            header('Location: index.php?page=customers');
            exit;
        }

        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get interaction history
        $interactions = $this->customerModel->getCustomerInteractions($customerId, $limit, $offset);
        $totalCount = $this->customerModel->getInteractionCount($customerId);
        $totalPages = ceil($totalCount / $limit);

        // Get interaction types
        $interactionTypes = ['call', 'email', 'meeting', 'visit', 'complaint', 'inquiry', 'follow_up', 'support'];

        include_once '../app/views/customers/interactions.php';
    }

    /**
     * Add customer interaction
     */
    public function addInteraction($customerId)
    {
        $this->requirePermission(ROLE_STAFF);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAddInteraction($customerId);
            return;
        }

        $customer = $this->customerModel->getCustomerWithDetails($customerId);
        if (!$customer) {
            $_SESSION['flash_error'] = 'Customer not found';
            header('Location: index.php?page=customers');
            exit;
        }

        $interactionTypes = ['call', 'email', 'meeting', 'visit', 'complaint', 'inquiry', 'follow_up', 'support'];
        include_once '../app/views/customers/add_interaction.php';
    }

    /**
     * Handle adding interaction
     */
    private function handleAddInteraction($customerId)
    {
        try {
            $data = [
                'customer_id' => $customerId,
                'interaction_type' => $_POST['interaction_type'],
                'interaction_date' => $_POST['interaction_date'] ?? date('Y-m-d H:i:s'),
                'subject' => trim($_POST['subject'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'outcome' => trim($_POST['outcome'] ?? ''),
                'next_action' => trim($_POST['next_action'] ?? ''),
                'next_action_date' => $_POST['next_action_date'] ?? null,
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            $interactionId = $this->customerModel->addInteraction($data);

            if ($interactionId) {
                $_SESSION['flash_success'] = 'Interaction added successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Interaction added successfully',
                    'interaction_id' => $interactionId
                ]);
            } else {
                throw new \Exception('Failed to add interaction');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Loyalty Programs Management
     */
    public function loyaltyPrograms()
    {
        $this->requirePermission(ROLE_MANAGER);

        // Get loyalty programs
        $programs = $this->customerModel->getLoyaltyPrograms();
        
        // Get loyalty statistics
        $statistics = $this->customerModel->getLoyaltyStatistics();
        $tierDistribution = $this->customerModel->getLoyaltyTierDistribution();
        $recentRedemptions = $this->customerModel->getRecentRedemptions(10);

        include_once '../app/views/customers/loyalty_programs.php';
    }

    /**
     * Customer loyalty details
     */
    public function loyaltyDetails($customerId)
    {
        $this->requirePermission(ROLE_STAFF);

        $customer = $this->customerModel->getCustomerWithDetails($customerId);
        if (!$customer) {
            $_SESSION['flash_error'] = 'Customer not found';
            header('Location: index.php?page=customers');
            exit;
        }

        // Get loyalty history
        $loyaltyHistory = $this->customerModel->getCustomerLoyaltyHistory($customerId);
        
        // Get available rewards
        $availableRewards = $this->customerModel->getAvailableRewards($customer['loyalty_tier']);

        include_once '../app/views/customers/loyalty_details.php';
    }

    /**
     * Redeem loyalty points
     */
    public function redeemPoints($customerId)
    {
        $this->requirePermission(ROLE_STAFF);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rewardId = $_POST['reward_id'] ?? null;
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            try {
                $customer = $this->customerModel->getCustomerWithDetails($customerId);
                if (!$customer) {
                    throw new \Exception('Customer not found');
                }

                $reward = $this->customerModel->getRewardById($rewardId);
                if (!$reward) {
                    throw new \Exception('Reward not found');
                }

                $totalPoints = $reward['points_required'] * $quantity;
                if ($customer['loyalty_points'] < $totalPoints) {
                    throw new \Exception('Insufficient loyalty points');
                }

                $success = $this->customerModel->redeemReward($customerId, $rewardId, $quantity);

                if ($success) {
                    $_SESSION['flash_success'] = 'Reward redeemed successfully';
                    $this->json([
                        'status' => 'success',
                        'message' => 'Reward redeemed successfully'
                    ]);
                } else {
                    throw new \Exception('Failed to redeem reward');
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
     * Customer Analytics
     */
    public function analytics()
    {
        $this->requirePermission(ROLE_MANAGER);

        $period = $_GET['period'] ?? '30d';
        $segment = $_GET['segment'] ?? 'all';

        // Get analytics data
        $analytics = $this->customerModel->getCustomerAnalytics($period, $segment);
        $trends = $this->customerModel->getCustomerTrends($period);
        $segmentation = $this->customerModel->getCustomerSegmentation();
        $retention = $this->customerModel->getCustomerRetention($period);

        include_once '../app/views/customers/analytics.php';
    }

    /**
     * Customer segmentation
     */
    public function segmentation()
    {
        $this->requirePermission(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSegmentation();
            return;
        }

        // Get current segmentation
        $segments = $this->customerModel->getCustomerSegments();
        $segmentRules = $this->customerModel->getSegmentRules();

        include_once '../app/views/customers/segmentation.php';
    }

    /**
     * Handle customer segmentation
     */
    private function handleSegmentation()
    {
        try {
            $rules = $_POST['segment_rules'] ?? [];
            $autoUpdate = isset($_POST['auto_update']) ? 1 : 0;

            $success = $this->customerModel->updateCustomerSegmentation($rules, $autoUpdate);

            if ($success) {
                $_SESSION['flash_success'] = 'Customer segmentation updated successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Customer segmentation updated successfully'
                ]);
            } else {
                throw new \Exception('Failed to update segmentation');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Marketing campaigns
     */
    public function campaigns()
    {
        $this->requirePermission(ROLE_MANAGER);

        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get campaigns
        $campaigns = $this->customerModel->getCampaigns($limit, $offset, $status);
        $totalCount = $this->customerModel->getCampaignCount($status);
        $totalPages = ceil($totalCount / $limit);

        include_once '../app/views/customers/campaigns.php';
    }

    /**
     * Create campaign
     */
    public function createCampaign()
    {
        $this->requirePermission(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreateCampaign();
            return;
        }

        // Get customer segments for targeting
        $segments = $this->customerModel->getCustomerSegments();
        $campaignTypes = ['email', 'sms', 'push', 'in_app', 'social'];

        include_once '../app/views/customers/create_campaign.php';
    }

    /**
     * Handle campaign creation
     */
    private function handleCreateCampaign()
    {
        try {
            $data = [
                'campaign_name' => trim($_POST['campaign_name'] ?? ''),
                'campaign_type' => $_POST['campaign_type'] ?? 'email',
                'target_segments' => $_POST['target_segments'] ?? [],
                'subject' => trim($_POST['subject'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
                'scheduled_date' => $_POST['scheduled_date'] ?? null,
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            $campaignId = $this->customerModel->createCampaign($data);

            if ($campaignId) {
                $_SESSION['flash_success'] = 'Campaign created successfully';
                $this->json([
                    'status' => 'success',
                    'message' => 'Campaign created successfully',
                    'campaign_id' => $campaignId
                ]);
            } else {
                throw new \Exception('Failed to create campaign');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Get customer interactions
     */
    public function apiGetInteractions($customerId)
    {
        $this->requirePermission(ROLE_STAFF);

        $limit = $_GET['limit'] ?? 10;
        $interactions = $this->customerModel->getCustomerInteractions($customerId, $limit);

        $this->json([
            'status' => 'success',
            'interactions' => $interactions
        ]);
    }

    /**
     * API: Get customer loyalty data
     */
    public function apiGetLoyaltyData($customerId)
    {
        $this->requirePermission(ROLE_STAFF);

        $customer = $this->customerModel->getCustomerWithDetails($customerId);
        $loyaltyHistory = $this->customerModel->getCustomerLoyaltyHistory($customerId);
        $availableRewards = $this->customerModel->getAvailableRewards($customer['loyalty_tier']);

        $this->json([
            'status' => 'success',
            'customer' => $customer,
            'loyalty_history' => $loyaltyHistory,
            'available_rewards' => $availableRewards
        ]);
    }

    /**
     * API: Get customer analytics data
     */
    public function apiGetAnalyticsData()
    {
        $this->requirePermission(ROLE_MANAGER);

        $period = $_GET['period'] ?? '30d';
        $segment = $_GET['segment'] ?? 'all';

        $analytics = $this->customerModel->getCustomerAnalytics($period, $segment);
        $trends = $this->customerModel->getCustomerTrends($period);

        $this->json([
            'status' => 'success',
            'analytics' => $analytics,
            'trends' => $trends
        ]);
    }

    /**
     * API: Update customer segment
     */
    public function apiUpdateSegment($customerId)
    {
        $this->requirePermission(ROLE_MANAGER);

        try {
            $segment = $_POST['segment'] ?? '';
            $reason = $_POST['reason'] ?? 'Manual update';

            $success = $this->customerModel->updateCustomerSegment($customerId, $segment, $reason);

            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Customer segment updated successfully'
                ]);
            } else {
                throw new \Exception('Failed to update segment');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Send campaign
     */
    public function apiSendCampaign($campaignId)
    {
        $this->requirePermission(ROLE_MANAGER);

        try {
            $success = $this->customerModel->sendCampaign($campaignId);

            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Campaign sent successfully'
                ]);
            } else {
                throw new \Exception('Failed to send campaign');
            }
        } catch (\Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validate customer data
     */
    private function validateCustomerData($data, $customerId = null)
    {
        $errors = $this->customerModel->validateCustomer($data);
        
        if (!empty($errors)) {
            $errorMessages = [];
            foreach ($errors as $field => $message) {
                $errorMessages[] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $message;
            }
            throw new \Exception(implode(', ', $errorMessages));
        }

        // Clean and prepare data
        $cleanData = [
            'customer_code' => trim($data['customer_code'] ?? ''),
            'customer_name' => trim($data['customer_name'] ?? ''),
            'customer_type' => $data['customer_type'] ?? 'individual',
            'business_name' => trim($data['business_name'] ?? ''),
            'tax_id' => trim($data['tax_id'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'whatsapp' => trim($data['whatsapp'] ?? ''),
            'address_detail' => trim($data['address_detail'] ?? ''),
            'province_id' => (int)($data['province_id'] ?? 0),
            'regency_id' => (int)($data['regency_id'] ?? 0),
            'district_id' => (int)($data['district_id'] ?? 0),
            'village_id' => (int)($data['village_id'] ?? 0),
            'postal_code' => trim($data['postal_code'] ?? ''),
            'customer_segment' => $data['customer_segment'] ?? 'regular',
            'customer_category' => $data['customer_category'] ?? 'walk_in',
            'credit_limit' => (float)($data['credit_limit'] ?? 0),
            'payment_terms' => $data['payment_terms'] ?? 'cash',
            'preferred_contact' => $data['preferred_contact'] ?? 'phone',
            'marketing_consent' => isset($data['marketing_consent']) ? 1 : 0,
            'notification_consent' => isset($data['notification_consent']) ? 1 : 0,
            'notes' => trim($data['notes'] ?? ''),
            'created_by' => $_SESSION['user_id'] ?? null
        ];

        return $cleanData;
    }

    /**
     * Get provinces for address form
     */
    private function getProvinces()
    {
        try {
            $sql = "SELECT id_province, name FROM alamat_db.provinces ORDER BY name";
            $result = $this->customerModel->query($sql);
            return $result ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get customer transaction history (placeholder)
     */
    private function getCustomerTransactionHistory($customerId)
    {
        // This would integrate with the transaction model
        // For now, return empty array
        return [];
    }

    /**
     * Get customer loyalty history (placeholder)
     */
    private function getCustomerLoyaltyHistory($customerId)
    {
        try {
            $sql = "SELECT * FROM loyalty_transactions 
                    WHERE customer_id = :customer_id 
                    ORDER BY transaction_date DESC 
                    LIMIT 10";
            $result = $this->customerModel->query($sql, ['customer_id' => $customerId]);
            return $result ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
