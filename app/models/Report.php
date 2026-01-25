<?php

class Report extends Model
{
    /**
     * Get all report templates
     */
    public function getReportTemplates()
    {
        try {
            $sql = "SELECT rt.*, 
                           GROUP_CONCAT(CONCAT(rpc.permission_group, ':', rpc.permission_name) SEPARATOR ',') as permissions
                    FROM report_templates rt
                    LEFT JOIN report_template_permissions rtp ON rt.id = rtp.template_id
                    LEFT JOIN report_permissions rpc ON rtp.permission_id = rpc.id
                    WHERE rt.is_active = 1
                    GROUP BY rt.id
                    ORDER BY rt.name";
            
            return $this->query($sql);
        } catch (Exception $e) {
            error_log("Error getting report templates: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate sales report
     */
    public function generateSalesReport($startDate, $endDate, $companyId = null, $branchId = null, $reportType = 'summary')
    {
        try {
            $whereClause = "WHERE t.created_at BETWEEN :start_date AND :end_date";
            $params = ['start_date' => $startDate, 'end_date' => $endDate];
            
            if ($companyId) {
                $whereClause .= " AND c.id_company = :company_id";
                $params['company_id'] = $companyId;
            }
            
            if ($branchId) {
                $whereClause .= " AND b.id_branch = :branch_id";
                $params['branch_id'] = $branchId;
            }

            switch ($reportType) {
                case 'summary':
                    $sql = "SELECT 
                            COUNT(*) as total_transactions,
                            COALESCE(SUM(t.total_amount), 0) as total_revenue,
                            COALESCE(AVG(t.total_amount), 0) as avg_transaction_value,
                            COUNT(DISTINCT DATE(t.created_at)) as active_days,
                            COUNT(DISTINCT t.branch_id) as active_branches
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause";
                    break;
                    
                case 'daily':
                    $sql = "SELECT 
                            DATE(t.created_at) as date,
                            COUNT(*) as transactions,
                            COALESCE(SUM(t.total_amount), 0) as revenue,
                            COALESCE(AVG(t.total_amount), 0) as avg_transaction
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            GROUP BY DATE(t.created_at)
                            ORDER BY DATE(t.created_at)";
                    break;
                    
                case 'weekly':
                    $sql = "SELECT 
                            YEARWEEK(t.created_at) as week,
                            DATE(DATE_FORMAT(t.created_at, '%Y-%m-01')) as week_start,
                            COUNT(*) as transactions,
                            COALESCE(SUM(t.total_amount), 0) as revenue,
                            COALESCE(AVG(t.total_amount), 0) as avg_transaction
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            GROUP BY YEARWEEK(t.created_at), DATE(DATE_FORMAT(t.created_at, '%Y-%m-01'))
                            ORDER BY week";
                    break;
                    
                case 'monthly':
                    $sql = "SELECT 
                            DATE_FORMAT(t.created_at, '%Y-%m') as month,
                            DATE_FORMAT(t.created_at, '%M %Y') as month_name,
                            COUNT(*) as transactions,
                            COALESCE(SUM(t.total_amount), 0) as revenue,
                            COALESCE(AVG(t.total_amount), 0) as avg_transaction
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            GROUP BY DATE_FORMAT(t.created_at, '%Y-%m'), DATE_FORMAT(t.created_at, '%M %Y')
                            ORDER BY month";
                    break;
                    
                case 'by_branch':
                    $sql = "SELECT 
                            c.company_name,
                            b.branch_name,
                            COUNT(*) as transactions,
                            COALESCE(SUM(t.total_amount), 0) as revenue,
                            COALESCE(AVG(t.total_amount), 0) as avg_transaction,
                            COUNT(DISTINCT DATE(t.created_at)) as active_days
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            GROUP BY c.id_company, c.company_name, b.id_branch, b.branch_name
                            ORDER BY revenue DESC";
                    break;
                    
                case 'top_products':
                    $priceColRow = $this->queryOne(
                        "SELECT COLUMN_NAME 
                         FROM information_schema.columns 
                         WHERE table_schema = DATABASE() 
                           AND table_name = 'transaction_items' 
                           AND COLUMN_NAME IN ('total_price','price','unit_price')
                         ORDER BY FIELD(COLUMN_NAME, 'total_price','price','unit_price')
                         LIMIT 1"
                    );
                    $priceCol = $priceColRow['COLUMN_NAME'] ?? 'price';
                    $revenueExpr = ($priceCol === 'total_price')
                        ? "SUM(ti.total_price)"
                        : "SUM(ti.quantity * ti.{$priceCol})";
                    
                    $sql = "SELECT 
                            p.product_name,
                            p.product_code,
                            COUNT(DISTINCT ti.transaction_id) as transactions,
                            SUM(ti.quantity) as total_quantity,
                            {$revenueExpr} as revenue,
                            COALESCE(AVG(ti.quantity), 0) as avg_quantity
                            FROM transaction_items ti
                            JOIN transactions t ON ti.transaction_id = t.id_transaction
                            JOIN products p ON ti.product_id = p.id_product
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            GROUP BY ti.product_id, p.product_name, p.product_code
                            ORDER BY revenue DESC
                            LIMIT 20";
                    break;
                    
                default:
                    throw new Exception("Invalid report type: $reportType");
            }

            return $this->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error generating sales report: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate inventory report
     */
    public function generateInventoryReport($branchId = null, $categoryId = null, $reportType = 'stock_levels')
    {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($branchId) {
                $whereClause .= " AND bi.branch_id = :branch_id";
                $params['branch_id'] = $branchId;
            }
            
            if ($categoryId) {
                $whereClause .= " AND p.category_id = :category_id";
                $params['category_id'] = $categoryId;
            }

            // Get the correct stock column name
            $colRow = $this->queryOne(
                "SELECT COLUMN_NAME 
                 FROM information_schema.columns 
                 WHERE table_schema = DATABASE() 
                   AND table_name = 'branch_inventory' 
                   AND COLUMN_NAME IN ('quantity','stock_quantity')
                 ORDER BY FIELD(COLUMN_NAME, 'quantity','stock_quantity')
                 LIMIT 1"
            );
            $stockCol = $colRow['COLUMN_NAME'] ?? 'quantity';

            switch ($reportType) {
                case 'stock_levels':
                    $sql = "SELECT 
                            p.product_name,
                            p.product_code,
                            pc.category_name,
                            b.branch_name,
                            bi.{$stockCol} as current_stock,
                            COALESCE(bi.min_stock, 0) as min_stock,
                            COALESCE(bi.max_stock, 0) as max_stock,
                            CASE 
                                WHEN bi.{$stockCol} = 0 THEN 'Out of Stock'
                                WHEN bi.{$stockCol} <= COALESCE(bi.min_stock, 0) THEN 'Low Stock'
                                WHEN bi.{$stockCol} >= COALESCE(bi.max_stock, 999999) THEN 'Overstock'
                                ELSE 'Normal' 
                            END as stock_status,
                            COALESCE(p.purchase_price, 0) as unit_cost,
                            (bi.{$stockCol} * COALESCE(p.purchase_price, 0)) as total_value
                            FROM branch_inventory bi
                            JOIN products p ON bi.product_id = p.id_product
                            JOIN branches b ON bi.branch_id = b.id_branch
                            LEFT JOIN product_categories pc ON p.category_id = pc.id_category
                            $whereClause
                            ORDER BY stock_status, p.product_name";
                    break;
                    
                case 'valuation':
                    $sql = "SELECT 
                            p.product_name,
                            p.product_code,
                            pc.category_name,
                            SUM(bi.{$stockCol}) as total_quantity,
                            COALESCE(p.purchase_price, 0) as unit_cost,
                            SUM(bi.{$stockCol} * COALESCE(p.purchase_price, 0)) as total_value,
                            COALESCE(p.selling_price, 0) as selling_price,
                            SUM(bi.{$stockCol} * (COALESCE(p.selling_price, 0) - COALESCE(p.purchase_price, 0))) as potential_profit
                            FROM branch_inventory bi
                            JOIN products p ON bi.product_id = p.id_product
                            LEFT JOIN product_categories pc ON p.category_id = pc.id_category
                            $whereClause
                            GROUP BY p.id_product, p.product_name, p.product_code, pc.category_name, p.purchase_price, p.selling_price
                            HAVING total_quantity > 0
                            ORDER BY total_value DESC";
                    break;
                    
                case 'movements':
                    $sql = "SELECT 
                            p.product_name,
                            p.product_code,
                            b.branch_name,
                            'Stock In' as movement_type,
                            COALESCE(SUM(CASE WHEN ti.quantity > 0 THEN ti.quantity ELSE 0 END), 0) as quantity_in,
                            COALESCE(SUM(CASE WHEN ti.quantity < 0 THEN ABS(ti.quantity) ELSE 0 END), 0) as quantity_out,
                            COALESCE(SUM(ti.quantity), 0) as net_movement
                            FROM transaction_items ti
                            JOIN transactions t ON ti.transaction_id = t.id_transaction
                            JOIN products p ON ti.product_id = p.id_product
                            JOIN branches b ON t.branch_id = b.id_branch
                            WHERE t.created_at BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) AND CURRENT_DATE
                            " . ($branchId ? "AND b.id_branch = :branch_id" : "") . "
                            GROUP BY ti.product_id, p.product_name, p.product_code, b.branch_name
                            ORDER BY net_movement DESC";
                    
                    if ($branchId) {
                        $params['branch_id'] = $branchId;
                    }
                    break;
                    
                default:
                    throw new Exception("Invalid report type: $reportType");
            }

            return $this->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error generating inventory report: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport($startDate, $endDate, $reportType = 'profit_loss', $companyId = null)
    {
        try {
            $whereClause = "WHERE t.created_at BETWEEN :start_date AND :end_date";
            $params = ['start_date' => $startDate, 'end_date' => $endDate];
            
            if ($companyId) {
                $whereClause .= " AND c.id_company = :company_id";
                $params['company_id'] = $companyId;
            }

            switch ($reportType) {
                case 'profit_loss':
                    $sql = "SELECT 
                            'Revenue' as category,
                            'Sales Revenue' as subcategory,
                            COALESCE(SUM(t.total_amount), 0) as amount,
                            'credit' as type
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            
                            UNION ALL
                            
                            SELECT 
                            'Revenue' as category,
                            'Other Revenue' as subcategory,
                            0 as amount,
                            'credit' as type
                            
                            UNION ALL
                            
                            SELECT 
                            'Cost of Goods Sold' as category,
                            'Direct Costs' as subcategory,
                            0 as amount, -- TODO: Calculate from purchase orders
                            'debit' as type
                            
                            UNION ALL
                            
                            SELECT 
                            'Operating Expenses' as category,
                            'Salaries' as subcategory,
                            0 as amount, -- TODO: Calculate from payroll
                            'debit' as type
                            
                            UNION ALL
                            
                            SELECT 
                            'Operating Expenses' as category,
                            'Rent & Utilities' as subcategory,
                            0 as amount, -- TODO: Calculate from expenses
                            'debit' as type
                            
                            UNION ALL
                            
                            SELECT 
                            'Net Profit/Loss' as category,
                            'Net Result' as subcategory,
                            (SELECT COALESCE(SUM(t.total_amount), 0) FROM transactions t LEFT JOIN branches b ON t.branch_id = b.id_branch LEFT JOIN companies c ON b.company_id = c.id_company $whereClause) as amount,
                            CASE WHEN (SELECT COALESCE(SUM(t.total_amount), 0) FROM transactions t LEFT JOIN branches b ON t.branch_id = b.id_branch LEFT JOIN companies c ON b.company_id = c.id_company $whereClause) >= 0 THEN 'credit' ELSE 'debit' END as type";
                    break;
                    
                case 'cash_flow':
                    $sql = "SELECT 
                            'Operating Activities' as category,
                            'Cash from Sales' as subcategory,
                            COALESCE(SUM(t.total_amount), 0) as amount,
                            'inflow' as flow_type
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            
                            UNION ALL
                            
                            SELECT 
                            'Operating Activities' as category,
                            'Cash Payments' as subcategory,
                            0 as amount, -- TODO: Calculate from expenses
                            'outflow' as flow_type";
                    break;
                    
                default:
                    throw new Exception("Invalid report type: $reportType");
            }

            return $this->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error generating financial report: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate customer report
     */
    public function generateCustomerReport($startDate, $endDate, $reportType = 'purchase_history')
    {
        // TODO: Implement when customer management system is built
        return [
            'message' => 'Customer reports will be available after customer management system implementation'
        ];
    }

    /**
     * Generate supplier report
     */
    public function generateSupplierReport($startDate, $endDate, $reportType = 'purchase_history')
    {
        // TODO: Implement when supplier management system is built
        return [
            'message' => 'Supplier reports will be available after supplier management system implementation'
        ];
    }

    /**
     * Generate branch performance report
     */
    public function generateBranchPerformanceReport($startDate, $endDate, $companyId = null, $reportType = 'comparison')
    {
        try {
            $whereClause = "WHERE t.created_at BETWEEN :start_date AND :end_date";
            $params = ['start_date' => $startDate, 'end_date' => $endDate];
            
            if ($companyId) {
                $whereClause .= " AND c.id_company = :company_id";
                $params['company_id'] = $companyId;
            }

            switch ($reportType) {
                case 'comparison':
                    $sql = "SELECT 
                            c.company_name,
                            b.branch_name,
                            b.branch_location,
                            COUNT(*) as transactions,
                            COALESCE(SUM(t.total_amount), 0) as revenue,
                            COALESCE(AVG(t.total_amount), 0) as avg_transaction,
                            COUNT(DISTINCT DATE(t.created_at)) as active_days,
                            COUNT(DISTINCT t.customer_id) as unique_customers,
                            (SELECT COUNT(*) FROM branch_inventory bi WHERE bi.branch_id = b.id_branch) as product_count
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            GROUP BY c.id_company, c.company_name, b.id_branch, b.branch_name, b.branch_location
                            ORDER BY revenue DESC";
                    break;
                    
                case 'trends':
                    $sql = "SELECT 
                            b.branch_name,
                            DATE(t.created_at) as date,
                            COUNT(*) as transactions,
                            COALESCE(SUM(t.total_amount), 0) as daily_revenue
                            FROM transactions t
                            LEFT JOIN branches b ON t.branch_id = b.id_branch
                            LEFT JOIN companies c ON b.company_id = c.id_company
                            $whereClause
                            GROUP BY b.id_branch, b.branch_name, DATE(t.created_at)
                            ORDER BY b.branch_name, date";
                    break;
                    
                default:
                    throw new Exception("Invalid report type: $reportType");
            }

            return $this->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error generating branch performance report: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save report template
     */
    public function saveTemplate($data)
    {
        try {
            $this->table = 'report_templates';
            return $this->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'query_template' => $data['query_template'],
                'parameters' => json_encode($data['parameters'] ?? []),
                'created_by' => $_SESSION['user_id'] ?? 1
            ]);
        } catch (Exception $e) {
            error_log("Error saving report template: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Schedule report
     */
    public function scheduleReport($data)
    {
        try {
            $this->table = 'report_schedules';
            
            // Calculate next run time based on schedule type
            $nextRun = $this->calculateNextRun($data['schedule_type']);
            
            return $this->create([
                'report_template_id' => $data['report_template_id'],
                'schedule_type' => $data['schedule_type'],
                'recipients' => json_encode($data['recipients'] ?? []),
                'next_run' => $nextRun
            ]);
        } catch (Exception $e) {
            error_log("Error scheduling report: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get scheduled reports
     */
    public function getScheduledReports()
    {
        try {
            $sql = "SELECT rs.*, rt.name as template_name 
                    FROM report_schedules rs
                    JOIN report_templates rt ON rs.report_template_id = rt.id
                    WHERE rs.is_active = 1
                    ORDER BY rs.next_run";
            
            return $this->query($sql);
        } catch (Exception $e) {
            error_log("Error getting scheduled reports: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete scheduled report
     */
    public function deleteScheduledReport($id)
    {
        try {
            $sql = "UPDATE report_schedules SET is_active = 0 WHERE id = :id";
            return $this->execute($sql, ['id' => $id]);
        } catch (Exception $e) {
            error_log("Error deleting scheduled report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Export report
     */
    public function exportReport($reportType, $format, $filters)
    {
        try {
            // TODO: Implement export functionality in Phase 2
            return [
                'success' => false,
                'error' => 'Export functionality will be implemented in Phase 2'
            ];
        } catch (Exception $e) {
            error_log("Error exporting report: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate next run time for scheduled reports
     */
    private function calculateNextRun($scheduleType)
    {
        switch ($scheduleType) {
            case 'daily':
                return date('Y-m-d H:i:s', strtotime('tomorrow 00:00'));
            case 'weekly':
                return date('Y-m-d H:i:s', strtotime('next monday 00:00'));
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime('first day of next month 00:00'));
            case 'yearly':
                return date('Y-m-d H:i:s', strtotime('first day of january next year 00:00'));
            default:
                return date('Y-m-d H:i:s', strtotime('+1 day'));
        }
    }
}
