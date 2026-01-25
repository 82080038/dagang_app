<?php

require_once __DIR__ . '/../core/Controller.php';

class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../models/Member.php';
        $this->userModel = new Member();
    }

    /**
     * Display users management page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
            return;
        }

        // Check if user has permission to manage users
        if (!$this->hasPermission(ROLE_MANAGER)) {
            $_SESSION['error'] = 'You do not have permission to access user management';
            $this->redirect('dashboard');
            return;
        }

        $page = $_GET['page_num'] ?? 1;
        $search = $_GET['search'] ?? '';

        try {
            $users = $this->userModel->getAll($limit = 10, $offset = ($page - 1) * 10, $search);
            $totalUsers = count($users); // Simple count for now

            $data = [
                'title' => 'User Management',
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => 10,
                    'total' => $totalUsers,
                    'pages' => ceil($totalUsers / 10)
                ],
                'search' => $search
            ];

            $this->view('users/index', $data);

        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to load users: ' . $e->getMessage();
            $this->view('users/index', ['users' => [], 'pagination' => []]);
        }
    }

    /**
     * Create new user
     */
    public function create()
    {
        $this->requireAuthJson();
        
        if (!$this->hasPermission(ROLE_MANAGER)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Validate required fields
            $requiredFields = ['member_code', 'member_name', 'email', 'password', 'position'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field {$field} is required");
                }
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Create user
            $userData = [
                'branch_id' => $_SESSION['branch_id'],
                'member_code' => $data['member_code'],
                'member_name' => $data['member_name'],
                'email' => $data['email'],
                'password_hash' => $hashedPassword,
                'position' => $data['position'],
                'phone' => $data['phone'] ?? '',
                'is_active' => 1
            ];

            $userId = $this->userModel->create($userData);

            $this->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'user_id' => $userId
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to create user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update user
     */
    public function update()
    {
        $this->requireAuthJson();
        
        if (!$this->hasPermission(ROLE_MANAGER)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['id'] ?? null;

        if (!$userId) {
            $this->json(['error' => 'User ID is required'], 400);
            return;
        }

        try {
            // Prepare update data
            $updateData = [];

            $allowedFields = ['member_code', 'member_name', 'email', 'position', 'phone', 'is_active'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            // Hash password if being updated
            if (!empty($data['password'])) {
                $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // Update user
            $this->userModel->update($userId, $updateData);

            $this->json([
                'status' => 'success',
                'message' => 'User updated successfully'
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to update user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete user
     */
    public function delete()
    {
        $this->requireAuthJson();
        
        if (!$this->hasPermission(ROLE_MANAGER)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $userId = $_GET['id'] ?? null;

        if (!$userId) {
            $this->json(['error' => 'User ID is required'], 400);
            return;
        }

        try {
            // Prevent deletion of self
            if ($userId == $_SESSION['user_id']) {
                throw new Exception('Cannot delete your own account');
            }

            $this->userModel->delete($userId);

            $this->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get user details
     */
    public function get()
    {
        $this->requireAuthJson();
        
        $userId = $_GET['id'] ?? null;

        if (!$userId) {
            $this->json(['error' => 'User ID is required'], 400);
            return;
        }

        try {
            $user = $this->userModel->getById($userId);

            if (!$user) {
                $this->json(['error' => 'User not found'], 404);
                return;
            }

            // Remove sensitive data
            unset($user['password_hash']);

            $this->json([
                'status' => 'success',
                'data' => $user
            ]);

        } catch (Exception $e) {
            $this->json(['error' => 'Failed to get user: ' . $e->getMessage()], 500);
        }
    }
}
?>