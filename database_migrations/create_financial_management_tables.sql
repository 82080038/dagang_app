-- Create Financial Management System Tables
-- Double-entry accounting system with comprehensive financial reporting

-- Chart of Accounts table
CREATE TABLE IF NOT EXISTS `chart_of_accounts` (
    `id_account` varchar(50) NOT NULL,
    `account_code` varchar(20) NOT NULL,
    `account_name` varchar(255) NOT NULL,
    `account_type` enum('asset','liability','equity','revenue','expense') NOT NULL,
    `account_category` varchar(100) DEFAULT NULL,
    `parent_id` varchar(50) DEFAULT NULL,
    `level` int(11) DEFAULT 1,
    `balance_type` enum('debit','credit') NOT NULL,
    `current_balance` decimal(20,2) DEFAULT 0.00,
    `opening_balance` decimal(20,2) DEFAULT 0.00,
    `is_active` tinyint(1) DEFAULT 1,
    `description` text,
    `company_id` int(11) DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_account`),
    UNIQUE KEY `unique_account_code_company` (`account_code`, `company_id`),
    KEY `idx_account_type` (`account_type`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_company_id` (`company_id`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal Entries table
CREATE TABLE IF NOT EXISTS `journal_entries` (
    `id_journal` varchar(50) NOT NULL,
    `journal_number` varchar(50) NOT NULL,
    `transaction_date` date NOT NULL,
    `description` text NOT NULL,
    `reference_type` varchar(50) DEFAULT 'manual',
    `reference_id` varchar(50) DEFAULT NULL,
    `total_debit` decimal(20,2) DEFAULT 0.00,
    `total_credit` decimal(20,2) DEFAULT 0.00,
    `status` enum('draft','posted','cancelled') DEFAULT 'draft',
    `company_id` int(11) DEFAULT NULL,
    `branch_id` int(11) DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `posted_by` int(11) DEFAULT NULL,
    `posted_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_journal`),
    UNIQUE KEY `unique_journal_number_company` (`journal_number`, `company_id`),
    KEY `idx_transaction_date` (`transaction_date`),
    KEY `idx_status` (`status`),
    KEY `idx_reference` (`reference_type`, `reference_id`),
    KEY `idx_company_id` (`company_id`),
    KEY `idx_branch_id` (`branch_id`),
    KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal Entry Lines table
CREATE TABLE IF NOT EXISTS `journal_entry_lines` (
    `id_journal_line` varchar(50) NOT NULL,
    `id_journal` varchar(50) NOT NULL,
    `account_id` varchar(50) NOT NULL,
    `description` text,
    `debit_amount` decimal(20,2) DEFAULT 0.00,
    `credit_amount` decimal(20,2) DEFAULT 0.00,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_journal_line`),
    KEY `idx_journal_id` (`id_journal`),
    KEY `idx_account_id` (`account_id`),
    KEY `idx_debit_amount` (`debit_amount`),
    KEY `idx_credit_amount` (`credit_amount`),
    FOREIGN KEY (`id_journal`) REFERENCES `journal_entries`(`id_journal`) ON DELETE CASCADE,
    FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts`(`id_account`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Financial Reports table
CREATE TABLE IF NOT EXISTS `financial_reports` (
    `id_report` varchar(50) NOT NULL,
    `report_type` enum('trial_balance','income_statement','balance_sheet','cash_flow','custom') NOT NULL,
    `report_name` varchar(255) NOT NULL,
    `report_description` text,
    `period_start` date NOT NULL,
    `period_end` date NOT NULL,
    `report_data` json DEFAULT NULL,
    `status` enum('generating','completed','failed') DEFAULT 'generating',
    `file_path` varchar(500) DEFAULT NULL,
    `file_size` int(11) DEFAULT 0,
    `download_count` int(11) DEFAULT 0,
    `company_id` int(11) DEFAULT NULL,
    `branch_id` int(11) DEFAULT NULL,
    `generated_by` int(11) DEFAULT NULL,
    `generated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `expires_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id_report`),
    UNIQUE KEY `unique_report_type_period_company` (`report_type`, `period_start`, `period_end`, `company_id`),
    KEY `idx_report_type` (`report_type`),
    KEY `idx_period` (`period_start`, `period_end`),
    KEY `idx_status` (`status`),
    KEY `idx_company_id` (`company_id`),
    KEY `idx_branch_id` (`branch_id`),
    KEY `idx_generated_by` (`generated_by`),
    KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cash Accounts table
CREATE TABLE IF NOT EXISTS `cash_accounts` (
    `id_cash_account` varchar(50) NOT NULL,
    `account_name` varchar(255) NOT NULL,
    `account_number` varchar(50) DEFAULT NULL,
    `bank_name` varchar(100) DEFAULT NULL,
    `account_type` enum('cash','bank','digital_wallet') NOT NULL,
    `currency` varchar(10) DEFAULT 'IDR',
    `current_balance` decimal(20,2) DEFAULT 0.00,
    `opening_balance` decimal(20,2) DEFAULT 0.00,
    `is_active` tinyint(1) DEFAULT 1,
    `is_default` tinyint(1) DEFAULT 0,
    `description` text,
    `company_id` int(11) DEFAULT NULL,
    `branch_id` int(11) DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cash_account`),
    KEY `idx_account_type` (`account_type`),
    KEY `idx_company_id` (`company_id`),
    KEY `idx_branch_id` (`branch_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Budget Management table
CREATE TABLE IF NOT EXISTS `budgets` (
    `id_budget` varchar(50) NOT NULL,
    `budget_name` varchar(255) NOT NULL,
    `budget_type` enum('revenue','expense','capital') NOT NULL,
    `account_id` varchar(50) NOT NULL,
    `period_start` date NOT NULL,
    `period_end` date NOT NULL,
    `budgeted_amount` decimal(20,2) NOT NULL,
    `actual_amount` decimal(20,2) DEFAULT 0.00,
    `variance_amount` decimal(20,2) DEFAULT 0.00,
    `variance_percentage` decimal(5,2) DEFAULT 0.00,
    `status` enum('active','completed','cancelled') DEFAULT 'active',
    `description` text,
    `company_id` int(11) DEFAULT NULL,
    `branch_id` int(11) DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `approved_by` int(11) DEFAULT NULL,
    `approved_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_budget`),
    KEY `idx_budget_type` (`budget_type`),
    KEY `idx_account_id` (`account_id`),
    KEY `idx_period` (`period_start`, `period_end`),
    KEY `idx_status` (`status`),
    KEY `idx_company_id` (`company_id`),
    KEY `idx_branch_id` (`branch_id`),
    FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts`(`id_account`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tax Configuration table
CREATE TABLE IF NOT EXISTS `tax_configuration` (
    `id_tax` varchar(50) NOT NULL,
    `tax_name` varchar(255) NOT NULL,
    `tax_type` enum('income_tax','vat','withholding_tax','other') NOT NULL,
    `tax_rate` decimal(5,4) NOT NULL,
    `tax_code` varchar(20) DEFAULT NULL,
    `description` text,
    `is_active` tinyint(1) DEFAULT 1,
    `is_default` tinyint(1) DEFAULT 0,
    `effective_from` date NOT NULL,
    `effective_to` date DEFAULT NULL,
    `company_id` int(11) DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_tax`),
    KEY `idx_tax_type` (`tax_type`),
    KEY `idx_tax_rate` (`tax_rate`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_is_default` (`is_default`),
    KEY `idx_effective_period` (`effective_from`, `effective_to`),
    KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default chart of accounts for Indonesian accounting standards
INSERT INTO `chart_of_accounts` (`id_account`, `account_code`, `account_name`, `account_type`, `account_category`, `balance_type`, `company_id`) VALUES
('ACC_ASSET_100', '100', 'Aktiva Lancar', 'asset', 'current_assets', 'debit', NULL),
('ACC_ASSET_110', '110', 'Kas dan Setara Kas', 'asset', 'cash_equivalents', 'debit', NULL),
('ACC_ASSET_111', '111', 'Kas', 'asset', 'cash', 'debit', NULL),
('ACC_ASSET_112', '112', 'Bank', 'asset', 'bank', 'debit', NULL),
('ACC_ASSET_120', '120', 'Piutang Usaha', 'asset', 'accounts_receivable', 'debit', NULL),
('ACC_ASSET_130', '130', 'Persediaan', 'asset', 'inventory', 'debit', NULL),
('ACC_ASSET_140', '140', 'Uang Muka Biaya', 'asset', 'prepaid_expenses', 'debit', NULL),
('ACC_ASSET_200', '200', 'Aktiva Tetap', 'asset', 'fixed_assets', 'debit', NULL),
('ACC_ASSET_210', '210', 'Peralatan', 'asset', 'equipment', 'debit', NULL),
('ACC_ASSET_220', '220', 'Kendaraan', 'asset', 'vehicles', 'debit', NULL),
('ACC_ASSET_230', '230', 'Gedung dan Bangunan', 'asset', 'buildings', 'debit', NULL),
('ACC_ASSET_300', '300', 'Akumulasi Penyusutan', 'asset', 'accumulated_depreciation', 'credit', NULL),

('ACC_LIABILITY_400', '400', 'Kewajiban Jangka Pendek', 'liability', 'current_liabilities', 'credit', NULL),
('ACC_LIABILITY_410', '410', 'Utang Usaha', 'liability', 'accounts_payable', 'credit', NULL),
('ACC_LIABILITY_420', '420', 'Utang Pajak', 'liability', 'tax_payable', 'credit', NULL),
('ACC_LIABILITY_430', '430', 'Utang Gaji', 'liability', 'salary_payable', 'credit', NULL),
('ACC_LIABILITY_500', '500', 'Kewajiban Jangka Panjang', 'liability', 'long_term_liabilities', 'credit', NULL),
('ACC_LIABILITY_510', '510', 'Utang Bank', 'liability', 'bank_loans', 'credit', NULL),

('ACC_EQUITY_600', '600', 'Modal', 'equity', 'capital', 'credit', NULL),
('ACC_EQUITY_610', '610', 'Modal Saham', 'equity', 'share_capital', 'credit', NULL),
('ACC_EQUITY_620', '620', 'Modal Disetor', 'equity', 'paid_in_capital', 'credit', NULL),
('ACC_EQUITY_630', '630', 'Prive', 'equity', 'drawings', 'debit', NULL),
('ACC_EQUITY_640', '640', 'Laba Ditahan', 'equity', 'retained_earnings', 'credit', NULL),

('ACC_REVENUE_700', '700', 'Pendapatan Usaha', 'revenue', 'operating_revenue', 'credit', NULL),
('ACC_REVENUE_710', '710', 'Penjualan', 'revenue', 'sales', 'credit', NULL),
('ACC_REVENUE_720', '720', 'Pendapatan Jasa', 'revenue', 'service_revenue', 'credit', NULL),
('ACC_REVENUE_800', '800', 'Pendapatan Lain-lain', 'revenue', 'other_revenue', 'credit', NULL),

('ACC_EXPENSE_900', '900', 'Beban Usaha', 'expense', 'operating_expenses', 'debit', NULL),
('ACC_EXPENSE_910', '910', 'Harga Pokok Penjualan', 'expense', 'cost_of_goods_sold', 'debit', NULL),
('ACC_EXPENSE_920', '920', 'Beban Gaji', 'expense', 'salary_expenses', 'debit', NULL),
('ACC_EXPENSE_930', '930', 'Beban Sewa', 'expense', 'rent_expenses', 'debit', NULL),
('ACC_EXPENSE_940', '940', 'Beban Listrik dan Air', 'expense', 'utilities_expenses', 'debit', NULL),
('ACC_EXPENSE_950', '950', 'Beban Marketing', 'expense', 'marketing_expenses', 'debit', NULL),
('ACC_EXPENSE_960', '960', 'Beban Administrasi', 'expense', 'administrative_expenses', 'debit', NULL),
('ACC_EXPENSE_1000', '1000', 'Beban Lain-lain', 'expense', 'other_expenses', 'debit', NULL);

-- Insert default tax configuration for Indonesia
INSERT INTO `tax_configuration` (`id_tax`, `tax_name`, `tax_type`, `tax_rate`, `tax_code`, `effective_from`) VALUES
('TAX_VAT_11', 'PPN 11%', 'vat', 0.1100, 'VAT-11', '2022-04-01'),
('TAX_VAT_0', 'PPN 0%', 'vat', 0.0000, 'VAT-0', '2022-04-01'),
('TAX_WHT_23', 'PPh 23', 'withholding_tax', 0.0200, 'WHT-23', '2022-04-01'),
('TAX_WHT_21', 'PPh 21', 'withholding_tax', 0.0500, 'WHT-21', '2022-04-01'),
('TAX_INCOME_25', 'PPh Badan 25%', 'income_tax', 0.2500, 'INC-25', '2022-04-01');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_chart_of_accounts_composite` ON `chart_of_accounts` (`company_id`, `account_type`, `is_active`);
CREATE INDEX IF NOT EXISTS `idx_journal_entries_composite` ON `journal_entries` (`company_id`, `transaction_date`, `status`);
CREATE INDEX IF NOT EXISTS `idx_journal_lines_composite` ON `journal_entry_lines` (`id_journal`, `account_id`);
CREATE INDEX IF NOT EXISTS `idx_financial_reports_composite` ON `financial_reports` (`company_id`, `report_type`, `period_start`, `period_end`);
CREATE INDEX IF NOT EXISTS `idx_budgets_composite` ON `budgets` (`company_id`, `budget_type`, `period_start`, `period_end`);
CREATE INDEX IF NOT EXISTS `idx_cash_accounts_composite` ON `cash_accounts` (`company_id`, `branch_id`, `account_type`, `is_active`);

-- Add foreign key constraints for better data integrity
ALTER TABLE `chart_of_accounts` ADD CONSTRAINT `fk_chart_accounts_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE CASCADE;
ALTER TABLE `journal_entries` ADD CONSTRAINT `fk_journal_entries_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE CASCADE;
ALTER TABLE `journal_entries` ADD CONSTRAINT `fk_journal_entries_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id_branch`) ON DELETE CASCADE;
ALTER TABLE `financial_reports` ADD CONSTRAINT `fk_financial_reports_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE CASCADE;
ALTER TABLE `cash_accounts` ADD CONSTRAINT `fk_cash_accounts_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE CASCADE;
ALTER TABLE `cash_accounts` ADD CONSTRAINT `fk_cash_accounts_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id_branch`) ON DELETE CASCADE;
ALTER TABLE `budgets` ADD CONSTRAINT `fk_budgets_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE CASCADE;
ALTER TABLE `budgets` ADD CONSTRAINT `fk_budgets_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id_branch`) ON DELETE CASCADE;
ALTER TABLE `tax_configuration` ADD CONSTRAINT `fk_tax_configuration_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id_company`) ON DELETE CASCADE;
