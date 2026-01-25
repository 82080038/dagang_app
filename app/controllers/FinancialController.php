<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/database.php';

class FinancialController extends Controller {
    private $pdo;
    
    public function __construct()
    {
        parent::__construct();
        $this->pdo = new PDO('mysql:host=localhost;dbname=perdagangan_system', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function index() {
        $this->requireAuth();
        $this->requirePermission(ROLE_MANAGER);
        $this->requireFeature('financial_management');
        
        $data = [
            'title' => 'Financial Management',
            'companies' => $this->getAllCompanies(),
            'branches' => $this->getAllBranches(),
            'user_role' => $this->getUserRole()
        ];
        
        $this->render('financial/index', $data);
    }
    
    /**
     * Get chart of accounts
     */
    public function getChartOfAccounts() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $accountType = $_GET['account_type'] ?? null;
        
        try {
            $sql = "SELECT * FROM chart_of_accounts WHERE is_active = 1";
            $params = [];
            
            if ($companyId) {
                $sql .= " AND (company_id = ? OR company_id IS NULL)";
                $params[] = $companyId;
            }
            
            if ($accountType) {
                $sql .= " AND account_type = ?";
                $params[] = $accountType;
            }
            
            $sql .= " ORDER BY account_code";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Build hierarchical structure
            $hierarchical = $this->buildAccountHierarchy($accounts);
            
            $this->json([
                'status' => 'success',
                'data' => $hierarchical
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to get chart of accounts: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create journal entry
     */
    public function createJournalEntry() {
        $this->requireAuthJson();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $this->pdo->beginTransaction();
            
            // Generate journal ID and number
            $journalId = 'JRNL_' . uniqid();
            $journalNumber = $this->generateJournalNumber($data['company_id'] ?? null);
            
            // Insert journal entry
            $stmt = $this->pdo->prepare("
                INSERT INTO journal_entries (id_journal, journal_number, transaction_date, description, reference_type, reference_id, total_debit, total_credit, status, company_id, branch_id, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?, ?)
            ");
            
            $stmt->execute([
                $journalId,
                $journalNumber,
                $data['transaction_date'],
                $data['description'],
                $data['reference_type'] ?? 'manual',
                $data['reference_id'] ?? null,
                $data['total_debit'],
                $data['total_credit'],
                $data['company_id'] ?? null,
                $data['branch_id'] ?? null,
                $_SESSION['user_id']
            ]);
            
            // Insert journal entry lines
            foreach ($data['lines'] as $line) {
                $lineId = 'LINE_' . uniqid();
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO journal_entry_lines (id_journal_line, id_journal, account_id, description, debit_amount, credit_amount)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $lineId,
                    $journalId,
                    $line['account_id'],
                    $line['description'] ?? null,
                    $line['debit_amount'] ?? 0,
                    $line['credit_amount'] ?? 0
                ]);
            }
            
            $this->pdo->commit();
            
            $this->json([
                'status' => 'success',
                'message' => 'Journal entry created successfully',
                'journal_id' => $journalId,
                'journal_number' => $journalNumber
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            
            $this->json([
                'status' => 'error',
                'message' => 'Failed to create journal entry: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Post journal entry
     */
    public function postJournalEntry($journalId) {
        $this->requireAuthJson();
        
        try {
            $this->pdo->beginTransaction();
            
            // Get journal entry
            $stmt = $this->pdo->prepare("SELECT * FROM journal_entries WHERE id_journal = ? AND status = 'draft'");
            $stmt->execute([$journalId]);
            $journal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$journal) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Journal entry not found or already posted'
                ], 404);
                return;
            }
            
            // Validate journal entry balance
            if ($journal['total_debit'] != $journal['total_credit']) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Journal entry must be balanced (debits must equal credits)'
                ], 400);
                return;
            }
            
            // Get journal lines
            $stmt = $this->pdo->prepare("SELECT * FROM journal_entry_lines WHERE id_journal = ?");
            $stmt->execute([$journalId]);
            $lines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Update account balances
            foreach ($lines as $line) {
                $this->updateAccountBalance($line['account_id'], $line['debit_amount'], $line['credit_amount']);
            }
            
            // Update journal status
            $stmt = $this->pdo->prepare("
                UPDATE journal_entries 
                SET status = 'posted', posted_by = ?, posted_at = CURRENT_TIMESTAMP 
                WHERE id_journal = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $journalId]);
            
            $this->pdo->commit();
            
            $this->json([
                'status' => 'success',
                'message' => 'Journal entry posted successfully'
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            
            $this->json([
                'status' => 'error',
                'message' => 'Failed to post journal entry: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get journal entries
     */
    public function getJournalEntries() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(50, intval($_GET['limit'] ?? 20));
        $offset = ($page - 1) * $limit;
        
        try {
            $sql = "SELECT je.*, u.user_name as created_by_name, u2.user_name as posted_by_name
                    FROM journal_entries je
                    LEFT JOIN users u ON je.created_by = u.id_user
                    LEFT JOIN users u2 ON je.posted_by = u2.id_user
                    WHERE je.transaction_date BETWEEN ? AND ?";
            
            $params = [$startDate, $endDate];
            
            if ($companyId) {
                $sql .= " AND je.company_id = ?";
                $params[] = $companyId;
            }
            
            if ($branchId) {
                $sql .= " AND je.branch_id = ?";
                $params[] = $branchId;
            }
            
            if ($status) {
                $sql .= " AND je.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY je.transaction_date DESC, je.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get journal lines for each entry
            foreach ($entries as &$entry) {
                $stmt = $this->pdo->prepare("
                    SELECT jel.*, coa.account_name, coa.account_code
                    FROM journal_entry_lines jel
                    JOIN chart_of_accounts coa ON jel.account_id = coa.id_account
                    WHERE jel.id_journal = ?
                    ORDER BY jel.debit_amount DESC, jel.credit_amount DESC
                ");
                $stmt->execute([$entry['id_journal']]);
                $entry['lines'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM journal_entries WHERE transaction_date BETWEEN ? AND ?";
            $countParams = [$startDate, $endDate];
            
            if ($companyId) {
                $countSql .= " AND company_id = ?";
                $countParams[] = $companyId;
            }
            
            if ($branchId) {
                $countSql .= " AND branch_id = ?";
                $countParams[] = $branchId;
            }
            
            if ($status) {
                $countSql .= " AND status = ?";
                $countParams[] = $status;
            }
            
            $stmt = $this->pdo->prepare($countSql);
            $stmt->execute($countParams);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $this->json([
                'status' => 'success',
                'data' => $entries,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to get journal entries: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate trial balance
     */
    public function generateTrialBalance() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');
        
        try {
            $sql = "SELECT coa.*, 
                           COALESCE(SUM(jel.debit_amount), 0) as total_debit,
                           COALESCE(SUM(jel.credit_amount), 0) as total_credit,
                           CASE 
                               WHEN coa.balance_type = 'debit' THEN 
                                   coa.opening_balance + COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0)
                               ELSE 
                                   coa.opening_balance + COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0)
                           END as current_balance
                    FROM chart_of_accounts coa
                    LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                    LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal AND je.status = 'posted' AND je.transaction_date <= ?
                    WHERE coa.is_active = 1";
            
            $params = [$asOfDate];
            
            if ($companyId) {
                $sql .= " AND (coa.company_id = ? OR coa.company_id IS NULL)";
                $params[] = $companyId;
            }
            
            $sql .= " GROUP BY coa.id_account ORDER BY coa.account_code";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate totals
            $totalDebits = array_sum(array_column($accounts, 'total_debit'));
            $totalCredits = array_sum(array_column($accounts, 'total_credit'));
            
            $this->json([
                'status' => 'success',
                'data' => $accounts,
                'summary' => [
                    'total_debits' => $totalDebits,
                    'total_credits' => $totalCredits,
                    'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
                    'as_of_date' => $asOfDate
                ]
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate trial balance: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate income statement
     */
    public function generateIncomeStatement() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        try {
            // Get revenue accounts
            $revenueSql = "SELECT coa.*, 
                                  COALESCE(SUM(jel.credit_amount), 0) as amount
                           FROM chart_of_accounts coa
                           LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                           LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal AND je.status = 'posted' AND je.transaction_date BETWEEN ? AND ?
                           WHERE coa.account_type = 'revenue' AND coa.is_active = 1";
            
            $params = [$startDate, $endDate];
            
            if ($companyId) {
                $revenueSql .= " AND (coa.company_id = ? OR coa.company_id IS NULL)";
                $params[] = $companyId;
            }
            
            $revenueSql .= " GROUP BY coa.id_account ORDER BY coa.account_code";
            
            $stmt = $this->pdo->prepare($revenueSql);
            $stmt->execute($params);
            $revenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get expense accounts
            $expenseSql = "SELECT coa.*, 
                                 COALESCE(SUM(jel.debit_amount), 0) as amount
                          FROM chart_of_accounts coa
                          LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                          LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal AND je.status = 'posted' AND je.transaction_date BETWEEN ? AND ?
                          WHERE coa.account_type = 'expense' AND coa.is_active = 1";
            
            $expenseParams = [$startDate, $endDate];
            
            if ($companyId) {
                $expenseSql .= " AND (coa.company_id = ? OR coa.company_id IS NULL)";
                $expenseParams[] = $companyId;
            }
            
            $expenseSql .= " GROUP BY coa.id_account ORDER BY coa.account_code";
            
            $stmt = $this->pdo->prepare($expenseSql);
            $stmt->execute($expenseParams);
            $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate totals
            $totalRevenue = array_sum(array_column($revenues, 'amount'));
            $totalExpenses = array_sum(array_column($expenses, 'amount'));
            $netIncome = $totalRevenue - $totalExpenses;
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'revenues' => $revenues,
                    'expenses' => $expenses,
                    'summary' => [
                        'total_revenue' => $totalRevenue,
                        'total_expenses' => $totalExpenses,
                        'net_income' => $netIncome,
                        'profit_margin' => $totalRevenue > 0 ? ($netIncome / $totalRevenue) * 100 : 0,
                        'period_start' => $startDate,
                        'period_end' => $endDate
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate income statement: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate balance sheet
     */
    public function generateBalanceSheet() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-t');
        
        try {
            // Get asset accounts
            $assetSql = "SELECT coa.*, 
                               CASE 
                                   WHEN coa.balance_type = 'debit' THEN 
                                       coa.opening_balance + COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0)
                                   ELSE 
                                       coa.opening_balance + COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0)
                               END as balance
                        FROM chart_of_accounts coa
                        LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                        LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal AND je.status = 'posted' AND je.transaction_date <= ?
                        WHERE coa.account_type = 'asset' AND coa.is_active = 1";
            
            $params = [$asOfDate];
            
            if ($companyId) {
                $assetSql .= " AND (coa.company_id = ? OR coa.company_id IS NULL)";
                $params[] = $companyId;
            }
            
            $assetSql .= " GROUP BY coa.id_account ORDER BY coa.account_code";
            
            $stmt = $this->pdo->prepare($assetSql);
            $stmt->execute($params);
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get liability accounts
            $liabilitySql = "SELECT coa.*, 
                                   CASE 
                                       WHEN coa.balance_type = 'credit' THEN 
                                           coa.opening_balance + COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0)
                                       ELSE 
                                           coa.opening_balance + COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0)
                                   END as balance
                            FROM chart_of_accounts coa
                            LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                            LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal AND je.status = 'posted' AND je.transaction_date <= ?
                            WHERE coa.account_type = 'liability' AND coa.is_active = 1";
            
            $liabilityParams = [$asOfDate];
            
            if ($companyId) {
                $liabilitySql .= " AND (coa.company_id = ? OR coa.company_id IS NULL)";
                $liabilityParams[] = $companyId;
            }
            
            $liabilitySql .= " GROUP BY coa.id_account ORDER BY coa.account_code";
            
            $stmt = $this->pdo->prepare($liabilitySql);
            $stmt->execute($liabilityParams);
            $liabilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get equity accounts
            $equitySql = "SELECT coa.*, 
                                CASE 
                                    WHEN coa.balance_type = 'credit' THEN 
                                        coa.opening_balance + COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0)
                                    ELSE 
                                        coa.opening_balance + COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0)
                                END as balance
                         FROM chart_of_accounts coa
                         LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                         LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal AND je.status = 'posted' AND je.transaction_date <= ?
                         WHERE coa.account_type = 'equity' AND coa.is_active = 1";
            
            $equityParams = [$asOfDate];
            
            if ($companyId) {
                $equitySql .= " AND (coa.company_id = ? OR coa.company_id IS NULL)";
                $equityParams[] = $companyId;
            }
            
            $equitySql .= " GROUP BY coa.id_account ORDER BY coa.account_code";
            
            $stmt = $this->pdo->prepare($equitySql);
            $stmt->execute($equityParams);
            $equities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate totals
            $totalAssets = array_sum(array_column($assets, 'balance'));
            $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
            $totalEquity = array_sum(array_column($equities, 'balance'));
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'assets' => $assets,
                    'liabilities' => $liabilities,
                    'equities' => $equities,
                    'summary' => [
                        'total_assets' => $totalAssets,
                        'total_liabilities' => $totalLiabilities,
                        'total_equity' => $totalEquity,
                        'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
                        'as_of_date' => $asOfDate
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate balance sheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate cash flow statement
     */
    public function generateCashFlow() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        try {
            // Get cash accounts (simplified cash flow)
            $cashSql = "SELECT coa.*, 
                              COALESCE(SUM(jel.debit_amount), 0) as cash_in,
                              COALESCE(SUM(jel.credit_amount), 0) as cash_out
                       FROM chart_of_accounts coa
                       LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                       LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal AND je.status = 'posted' AND je.transaction_date BETWEEN ? AND ?
                       WHERE coa.account_type = 'asset' AND coa.account_category IN ('cash', 'bank', 'cash_equivalents') AND coa.is_active = 1";
            
            $params = [$startDate, $endDate];
            
            if ($companyId) {
                $cashSql .= " AND (coa.company_id = ? OR coa.company_id IS NULL)";
                $params[] = $companyId;
            }
            
            $cashSql .= " GROUP BY coa.id_account ORDER BY coa.account_code";
            
            $stmt = $this->pdo->prepare($cashSql);
            $stmt->execute($params);
            $cashAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate cash flow
            $totalCashIn = array_sum(array_column($cashAccounts, 'cash_in'));
            $totalCashOut = array_sum(array_column($cashAccounts, 'cash_out'));
            $netCashFlow = $totalCashIn - $totalCashOut;
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'cash_accounts' => $cashAccounts,
                    'summary' => [
                        'total_cash_in' => $totalCashIn,
                        'total_cash_out' => $totalCashOut,
                        'net_cash_flow' => $netCashFlow,
                        'period_start' => $startDate,
                        'period_end' => $endDate
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to generate cash flow statement: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get cash accounts
     */
    public function getCashAccounts() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? null;
        
        try {
            $sql = "SELECT * FROM cash_accounts WHERE is_active = 1";
            $params = [];
            
            if ($companyId) {
                $sql .= " AND company_id = ?";
                $params[] = $companyId;
            }
            
            if ($branchId) {
                $sql .= " AND branch_id = ?";
                $params[] = $branchId;
            }
            
            $sql .= " ORDER BY account_type, account_name";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json([
                'status' => 'success',
                'data' => $accounts
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to get cash accounts: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create budget
     */
    public function createBudget() {
        $this->requireAuthJson();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $budgetId = 'BUD_' . uniqid();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO budgets (id_budget, budget_name, budget_type, account_id, period_start, period_end, budgeted_amount, status, description, company_id, branch_id, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $budgetId,
                $data['budget_name'],
                $data['budget_type'],
                $data['account_id'],
                $data['period_start'],
                $data['period_end'],
                $data['budgeted_amount'],
                $data['description'] ?? null,
                $data['company_id'] ?? null,
                $data['branch_id'] ?? null,
                $_SESSION['user_id']
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Budget created successfully',
                'budget_id' => $budgetId
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to create budget: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get budgets
     */
    public function getBudgets() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? null;
        $budgetType = $_GET['budget_type'] ?? null;
        
        try {
            $sql = "SELECT b.*, coa.account_name, coa.account_code
                    FROM budgets b
                    JOIN chart_of_accounts coa ON b.account_id = coa.id_account
                    WHERE b.status IN ('active', 'completed')";
            $params = [];
            
            if ($companyId) {
                $sql .= " AND b.company_id = ?";
                $params[] = $companyId;
            }
            
            if ($branchId) {
                $sql .= " AND b.branch_id = ?";
                $params[] = $branchId;
            }
            
            if ($budgetType) {
                $sql .= " AND b.budget_type = ?";
                $params[] = $budgetType;
            }
            
            $sql .= " ORDER BY b.period_start DESC, b.budget_name";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate actual amounts and variances
            foreach ($budgets as &$budget) {
                $actualAmount = $this->getBudgetActualAmount($budget['account_id'], $budget['period_start'], $budget['period_end'], $budget['budget_type']);
                $budget['actual_amount'] = $actualAmount;
                $budget['variance_amount'] = $budget['budgeted_amount'] - $actualAmount;
                $budget['variance_percentage'] = $budget['budgeted_amount'] > 0 ? ($budget['variance_amount'] / $budget['budgeted_amount']) * 100 : 0;
                
                // Update budget with actual amounts
                $stmt = $this->pdo->prepare("
                    UPDATE budgets 
                    SET actual_amount = ?, variance_amount = ?, variance_percentage = ?
                    WHERE id_budget = ?
                ");
                $stmt->execute([$actualAmount, $budget['variance_amount'], $budget['variance_percentage'], $budget['id_budget']]);
            }
            
            $this->json([
                'status' => 'success',
                'data' => $budgets
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to get budgets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get tax configuration
     */
    public function getTaxConfiguration() {
        $this->requireAuthJson();
        
        $companyId = $_GET['company_id'] ?? null;
        
        try {
            $sql = "SELECT * FROM tax_configuration WHERE is_active = 1";
            $params = [];
            
            if ($companyId) {
                $sql .= " AND (company_id = ? OR company_id IS NULL)";
                $params[] = $companyId;
            }
            
            $sql .= " ORDER BY tax_type, tax_name";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $taxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json([
                'status' => 'success',
                'data' => $taxes
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to get tax configuration: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Helper methods
     */
    private function getAllCompanies() {
        try {
            $stmt = $this->pdo->query("SELECT id_company, company_name FROM companies WHERE is_active = 1 ORDER BY company_name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getAllBranches() {
        try {
            $stmt = $this->pdo->query("SELECT id_branch, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function buildAccountHierarchy($accounts) {
        $hierarchy = [];
        $indexed = [];
        
        // Index accounts by ID
        foreach ($accounts as $account) {
            $indexed[$account['id_account']] = $account;
            $indexed[$account['id_account']]['children'] = [];
        }
        
        // Build hierarchy
        foreach ($indexed as $id => &$account) {
            if ($account['parent_id'] && isset($indexed[$account['parent_id']])) {
                $indexed[$account['parent_id']]['children'][] = &$account;
            } else {
                $hierarchy[] = &$account;
            }
        }
        
        return $hierarchy;
    }
    
    private function generateJournalNumber($companyId) {
        $prefix = 'JRNL';
        if ($companyId) {
            $stmt = $this->pdo->prepare("SELECT company_code FROM companies WHERE id_company = ?");
            $stmt->execute([$companyId]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($company) {
                $prefix = $company['company_code'];
            }
        }
        
        $date = date('Ym');
        $sequence = $this->getNextJournalSequence($companyId);
        
        return $prefix . '/' . $date . '/' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    
    private function getNextJournalSequence($companyId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM journal_entries 
            WHERE DATE_FORMAT(created_at, '%Y%m') = DATE_FORMAT(CURDATE(), '%Y%m')" . 
            ($companyId ? " AND company_id = ?" : "")
        );
        
        $params = $companyId ? [$companyId] : [];
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($result['count'] ?? 0) + 1;
    }
    
    private function updateAccountBalance($accountId, $debitAmount, $creditAmount) {
        $stmt = $this->pdo->prepare("SELECT balance_type, current_balance FROM chart_of_accounts WHERE id_account = ?");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) return;
        
        $newBalance = $account['current_balance'];
        
        if ($account['balance_type'] === 'debit') {
            $newBalance += $debitAmount - $creditAmount;
        } else {
            $newBalance += $creditAmount - $debitAmount;
        }
        
        $stmt = $this->pdo->prepare("UPDATE chart_of_accounts SET current_balance = ? WHERE id_account = ?");
        $stmt->execute([$newBalance, $accountId]);
    }
    
    private function getBudgetActualAmount($accountId, $startDate, $endDate, $budgetType) {
        $sql = "SELECT COALESCE(SUM(jel.debit_amount), 0) as debit, COALESCE(SUM(jel.credit_amount), 0) as credit
                FROM journal_entry_lines jel
                JOIN journal_entries je ON jel.id_journal = je.id_journal
                WHERE jel.account_id = ? AND je.status = 'posted' AND je.transaction_date BETWEEN ? AND ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$accountId, $startDate, $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // For revenue budgets, use credit amounts; for expense budgets, use debit amounts
        if ($budgetType === 'revenue') {
            return $result['credit'];
        } else {
            return $result['debit'];
        }
    }
}
