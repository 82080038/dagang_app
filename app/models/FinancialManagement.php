<?php

class FinancialManagement extends Model
{
    protected $table = 'chart_of_accounts';
    
    protected $fillable = [
        'id_account',
        'account_code',
        'account_name',
        'account_type',
        'account_category',
        'parent_id',
        'level',
        'balance_type', // debit or credit
        'current_balance',
        'opening_balance',
        'is_active',
        'description',
        'company_id',
        'created_by',
        'created_at',
        'updated_at'
    ];

    /**
     * Chart of Accounts Management
     */
    
    public function createAccount($accountData)
    {
        $account = [
            'id_account' => $this->generateAccountId(),
            'account_code' => $accountData['account_code'],
            'account_name' => $accountData['account_name'],
            'account_type' => $accountData['account_type'],
            'account_category' => $accountData['account_category'],
            'parent_id' => $accountData['parent_id'] ?? null,
            'level' => $accountData['level'] ?? 1,
            'balance_type' => $accountData['balance_type'],
            'current_balance' => $accountData['opening_balance'] ?? 0,
            'opening_balance' => $accountData['opening_balance'] ?? 0,
            'is_active' => 1,
            'description' => $accountData['description'] ?? '',
            'company_id' => $_SESSION['company_id'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($account);
    }

    public function getAccountById($accountId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id_account = :account_id AND company_id = :company_id";
        
        return $this->queryOne($sql, [
            'account_id' => $accountId,
            'company_id' => $_SESSION['company_id'] ?? null
        ]);
    }

    public function getAccountsByType($accountType, $companyId = null)
    {
        $companyId = $companyId ?? $_SESSION['company_id'] ?? null;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE account_type = :account_type 
                AND company_id = :company_id 
                AND is_active = 1
                ORDER BY account_code";
        
        return $this->query($sql, [
            'account_type' => $accountType,
            'company_id' => $companyId
        ]);
    }

    public function getAccountHierarchy($companyId = null)
    {
        $companyId = $companyId ?? $_SESSION['company_id'] ?? null;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id AND is_active = 1
                ORDER BY level, account_code";
        
        return $this->query($sql, ['company_id' => $companyId]);
    }

    /**
     * Journal Entries Management
     */
    
    public function createJournalEntry($journalData)
    {
        // Start transaction
        $this->beginFinancialTransaction();
        
        try {
            // Create journal entry header
            $journalHeader = [
                'id_journal' => $this->generateJournalId(),
                'journal_number' => $this->generateJournalNumber(),
                'transaction_date' => $journalData['transaction_date'],
                'description' => $journalData['description'],
                'reference_type' => $journalData['reference_type'] ?? 'manual',
                'reference_id' => $journalData['reference_id'] ?? null,
                'total_debit' => 0,
                'total_credit' => 0,
                'status' => 'posted',
                'company_id' => $_SESSION['company_id'] ?? null,
                'branch_id' => $_SESSION['branch_id'] ?? null,
                'created_by' => $_SESSION['user_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->execute("INSERT INTO journal_entries SET " . $this->buildSetClause($journalHeader), $journalHeader);

            $totalDebit = 0;
            $totalCredit = 0;

            // Create journal entry lines
            foreach ($journalData['entries'] as $entry) {
                $journalLine = [
                    'id_journal_line' => $this->generateJournalLineId(),
                    'id_journal' => $journalHeader['id_journal'],
                    'account_id' => $entry['account_id'],
                    'description' => $entry['description'] ?? '',
                    'debit_amount' => $entry['debit_amount'] ?? 0,
                    'credit_amount' => $entry['credit_amount'] ?? 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $this->execute("INSERT INTO journal_entry_lines SET " . $this->buildSetClause($journalLine), $journalLine);

                $totalDebit += $entry['debit_amount'] ?? 0;
                $totalCredit += $entry['credit_amount'] ?? 0;

                // Update account balances
                $this->updateAccountBalance($entry['account_id'], $entry['debit_amount'] ?? 0, $entry['credit_amount'] ?? 0);
            }

            // Update journal header with totals
            $this->execute("UPDATE journal_entries SET total_debit = :total_debit, total_credit = :total_credit WHERE id_journal = :id_journal", [
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'id_journal' => $journalHeader['id_journal']
            ]);

            // Validate double-entry principle
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception('Journal entry must balance. Debit and credit amounts must be equal.');
            }

            $this->commitFinancialTransaction();
            return $journalHeader['id_journal'];

        } catch (Exception $e) {
            $this->rollbackFinancialTransaction();
            throw $e;
        }
    }

    public function getJournalEntries($filters = [], $limit = 50, $offset = 0)
    {
        $whereClause = "WHERE je.company_id = :company_id";
        $params = ['company_id' => $_SESSION['company_id'] ?? null];

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND je.transaction_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND je.transaction_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['account_id'])) {
            $whereClause .= " AND jel.account_id = :account_id";
            $params['account_id'] = $filters['account_id'];
        }

        $sql = "SELECT je.*, jel.*, coa.account_name, coa.account_code
                FROM journal_entries je
                LEFT JOIN journal_entry_lines jel ON je.id_journal = jel.id_journal
                LEFT JOIN chart_of_accounts coa ON jel.account_id = coa.id_account
                {$whereClause}
                ORDER BY je.transaction_date DESC, je.created_at DESC
                LIMIT :limit OFFSET :offset";

        return $this->query($sql, $params);
    }

    /**
     * Financial Reports
     */
    
    public function generateTrialBalance($date, $companyId = null)
    {
        $companyId = $companyId ?? $_SESSION['company_id'] ?? null;
        
        $sql = "SELECT coa.*, 
                    COALESCE(SUM(jel.debit_amount), 0) as total_debit,
                    COALESCE(SUM(jel.credit_amount), 0) as total_credit,
                    (COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0)) as balance
                FROM chart_of_accounts coa
                LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal 
                    AND je.transaction_date <= :date AND je.status = 'posted'
                WHERE coa.company_id = :company_id AND coa.is_active = 1
                GROUP BY coa.id_account
                ORDER BY coa.account_code";

        return $this->query($sql, [
            'date' => $date,
            'company_id' => $companyId
        ]);
    }

    public function generateIncomeStatement($startDate, $endDate, $companyId = null)
    {
        $companyId = $companyId ?? $_SESSION['company_id'] ?? null;
        
        // Get revenue accounts
        $revenueSql = "SELECT coa.account_name, coa.account_code,
                        COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0) as amount
                      FROM chart_of_accounts coa
                      LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                      LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal 
                          AND je.transaction_date BETWEEN :start_date AND :end_date 
                          AND je.status = 'posted'
                      WHERE coa.company_id = :company_id 
                          AND coa.account_type = 'revenue' 
                          AND coa.is_active = 1
                      GROUP BY coa.id_account
                      ORDER BY coa.account_code";

        // Get expense accounts
        $expenseSql = "SELECT coa.account_name, coa.account_code,
                       COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0) as amount
                     FROM chart_of_accounts coa
                     LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                     LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal 
                         AND je.transaction_date BETWEEN :start_date AND :end_date 
                         AND je.status = 'posted'
                     WHERE coa.company_id = :company_id 
                         AND coa.account_type = 'expense' 
                         AND coa.is_active = 1
                     GROUP BY coa.id_account
                     ORDER BY coa.account_code";

        $revenues = $this->query($revenueSql, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'company_id' => $companyId
        ]);

        $expenses = $this->query($expenseSql, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'company_id' => $companyId
        ]);

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => array_sum(array_column($revenues, 'amount')),
            'total_expenses' => array_sum(array_column($expenses, 'amount')),
            'net_income' => array_sum(array_column($revenues, 'amount')) - array_sum(array_column($expenses, 'amount'))
        ];
    }

    public function generateBalanceSheet($date, $companyId = null)
    {
        $companyId = $companyId ?? $_SESSION['company_id'] ?? null;
        
        // Get assets
        $assetsSql = "SELECT coa.account_name, coa.account_code,
                     (COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0)) as amount
                   FROM chart_of_accounts coa
                   LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                   LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal 
                       AND je.transaction_date <= :date AND je.status = 'posted'
                   WHERE coa.company_id = :company_id 
                       AND coa.account_type = 'asset' 
                       AND coa.is_active = 1
                   GROUP BY coa.id_account
                   HAVING amount != 0
                   ORDER BY coa.account_code";

        // Get liabilities
        $liabilitiesSql = "SELECT coa.account_name, coa.account_code,
                          (COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0)) as amount
                        FROM chart_of_accounts coa
                        LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                        LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal 
                            AND je.transaction_date <= :date AND je.status = 'posted'
                        WHERE coa.company_id = :company_id 
                            AND coa.account_type = 'liability' 
                            AND coa.is_active = 1
                        GROUP BY coa.id_account
                        HAVING amount != 0
                        ORDER BY coa.account_code";

        // Get equity
        $equitySql = "SELECT coa.account_name, coa.account_code,
                      (COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0)) as amount
                    FROM chart_of_accounts coa
                    LEFT JOIN journal_entry_lines jel ON coa.id_account = jel.account_id
                    LEFT JOIN journal_entries je ON jel.id_journal = je.id_journal 
                        AND je.transaction_date <= :date AND je.status = 'posted'
                    WHERE coa.company_id = :company_id 
                        AND coa.account_type = 'equity' 
                        AND coa.is_active = 1
                    GROUP BY coa.id_account
                    HAVING amount != 0
                    ORDER BY coa.account_code";

        $assets = $this->query($assetsSql, ['date' => $date, 'company_id' => $companyId]);
        $liabilities = $this->query($liabilitiesSql, ['date' => $date, 'company_id' => $companyId]);
        $equity = $this->query($equitySql, ['date' => $date, 'company_id' => $companyId]);

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => array_sum(array_column($assets, 'amount')),
            'total_liabilities' => array_sum(array_column($liabilities, 'amount')),
            'total_equity' => array_sum(array_column($equity, 'amount'))
        ];
    }

    /**
     * Cash Flow Management
     */
    
    public function generateCashFlowStatement($startDate, $endDate, $companyId = null)
    {
        $companyId = $companyId ?? $_SESSION['company_id'] ?? null;
        
        // Get cash account
        $cashAccount = $this->queryOne("SELECT id_account FROM chart_of_accounts WHERE account_code LIKE '100%' AND company_id = :company_id LIMIT 1", ['company_id' => $companyId]);
        
        if (!$cashAccount) {
            throw new Exception('Cash account not found');
        }

        $cashAccountId = $cashAccount['id_account'];

        // Get cash flows from journal entries
        $sql = "SELECT je.*, jel.description as line_description,
                       CASE 
                           WHEN jel.debit_amount > 0 THEN jel.debit_amount
                           ELSE -jel.credit_amount
                       END as cash_amount
                FROM journal_entries je
                JOIN journal_entry_lines jel ON je.id_journal = jel.id_journal
                WHERE jel.account_id = :cash_account_id
                  AND je.transaction_date BETWEEN :start_date AND :end_date
                  AND je.status = 'posted'
                  AND je.company_id = :company_id
                ORDER BY je.transaction_date";

        $cashFlows = $this->query($sql, [
            'cash_account_id' => $cashAccountId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'company_id' => $companyId
        ]);

        // Categorize cash flows
        $operating = 0;
        $investing = 0;
        $financing = 0;

        foreach ($cashFlows as $flow) {
            // Simple categorization based on description patterns
            $description = strtolower($flow['description'] . ' ' . $flow['line_description']);
            
            if (strpos($description, 'sales') !== false || strpos($description, 'purchase') !== false || strpos($description, 'expense') !== false) {
                $operating += $flow['cash_amount'];
            } elseif (strpos($description, 'asset') !== false || strpos($description, 'equipment') !== false) {
                $investing += $flow['cash_amount'];
            } elseif (strpos($description, 'loan') !== false || strpos($description, 'capital') !== false || strpos($description, 'dividend') !== false) {
                $financing += $flow['cash_amount'];
            } else {
                $operating += $flow['cash_amount'];
            }
        }

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'operating_cash_flow' => $operating,
            'investing_cash_flow' => $investing,
            'financing_cash_flow' => $financing,
            'net_cash_flow' => $operating + $investing + $financing,
            'details' => $cashFlows
        ];
    }

    /**
     * Helper Methods
     */
    
    private function updateAccountBalance($accountId, $debitAmount, $creditAmount)
    {
        $account = $this->getAccountById($accountId);
        if (!$account) {
            throw new Exception('Account not found: ' . $accountId);
        }

        $newBalance = $account['current_balance'];
        
        if ($account['balance_type'] === 'debit') {
            $newBalance += $debitAmount - $creditAmount;
        } else {
            $newBalance += $creditAmount - $debitAmount;
        }

        $this->execute("UPDATE chart_of_accounts SET current_balance = :new_balance, updated_at = NOW() WHERE id_account = :account_id", [
            'new_balance' => $newBalance,
            'account_id' => $accountId
        ]);
    }

    private function generateAccountId()
    {
        return 'ACC_' . strtoupper(uniqid()) . '_' . date('Ymd');
    }

    private function generateJournalId()
    {
        return 'JNL_' . strtoupper(uniqid()) . '_' . date('Ymd');
    }

    private function generateJournalNumber()
    {
        $prefix = 'JE-' . date('Ym');
        $sequence = $this->getNextJournalSequence($prefix);
        return $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    private function generateJournalLineId()
    {
        return 'JEL_' . strtoupper(uniqid()) . '_' . date('Ymd');
    }

    private function getNextJournalSequence($prefix)
    {
        $sql = "SELECT COUNT(*) as count FROM journal_entries WHERE journal_number LIKE :prefix";
        $result = $this->queryOne($sql, ['prefix' => $prefix . '%']);
        return ($result['count'] ?? 0) + 1;
    }

    private function buildSetClause($data)
    {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :$key";
        }
        return implode(', ', $setParts);
    }

    private function beginFinancialTransaction()
    {
        $this->execute("START TRANSACTION");
    }

    private function commitFinancialTransaction()
    {
        $this->execute("COMMIT");
    }

    private function rollbackFinancialTransaction()
    {
        $this->execute("ROLLBACK");
    }
}
