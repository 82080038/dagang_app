-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 04:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perdagangan_system`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`::1` PROCEDURE `sp_get_dashboard_stats` ()   BEGIN
    SELECT * FROM v_dashboard_realtime LIMIT 1;
END$$

CREATE DEFINER=`root`@`::1` PROCEDURE `sp_log_ajax_request` (IN `p_session_id` VARCHAR(255), IN `p_user_id` INT, IN `p_request_type` VARCHAR(50), IN `p_endpoint` VARCHAR(255), IN `p_request_data` JSON, IN `p_response_status` VARCHAR(20), IN `p_response_time_ms` INT, IN `p_ip_address` VARCHAR(45), IN `p_user_agent` TEXT)   BEGIN
    INSERT INTO ajax_requests (
        session_id, user_id, request_type, endpoint, 
        request_data, response_status, response_time_ms, 
        ip_address, user_agent
    ) VALUES (
        p_session_id, p_user_id, p_request_type, p_endpoint,
        p_request_data, p_response_status, p_response_time_ms,
        p_ip_address, p_user_agent
    );
END$$

CREATE DEFINER=`root`@`::1` PROCEDURE `sp_update_realtime_stats` ()   BEGIN
    
    UPDATE branches 
    SET real_time_status = 'offline',
        last_ping_at = NULL
    WHERE last_ping_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE);
    
    
    DELETE FROM user_sessions 
    WHERE expires_at < NOW() OR is_active = FALSE;
    
    
    DELETE FROM cache_data 
    WHERE expires_at < NOW();
    
    
    DELETE FROM notification_queue 
    WHERE status = 'sent' AND sent_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    
    DELETE FROM ajax_requests 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id_address` int(11) NOT NULL,
  `street_address` text NOT NULL COMMENT 'Alamat jalan lengkap (manual input)',
  `province_id` int(11) NOT NULL COMMENT 'Reference to alamat_db.provinces.id',
  `regency_id` int(11) NOT NULL COMMENT 'Reference to alamat_db.regencies.id',
  `district_id` int(11) NOT NULL COMMENT 'Reference to alamat_db.districts.id',
  `village_id` int(11) NOT NULL COMMENT 'Reference to alamat_db.villages.id',
  `postal_code` varchar(10) DEFAULT NULL COMMENT 'Kode pos (optional)',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Koordinat latitude (optional)',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Koordinat longitude (optional)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif alamat',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel terpusat untuk data alamat dengan referensi ke alamat_db';

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id_address`, `street_address`, `province_id`, `regency_id`, `district_id`, `village_id`, `postal_code`, `latitude`, `longitude`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Jl. Merdeka No. 123, RT 001/RW 002', 2, 1, 1, 1, '10310', NULL, NULL, 1, '2026-01-22 15:23:54', '2026-01-22 17:37:14'),
(2, 'Jl. Sudirman No. 456, RT 003/RW 004', 2, 2, 2, 2, '10250', NULL, NULL, 1, '2026-01-22 15:23:54', '2026-01-22 17:37:14'),
(3, 'Jl. Gatot Subroto No. 789, RT 005/RW 006', 2, 3, 3, 3, '10460', NULL, NULL, 1, '2026-01-22 15:23:54', '2026-01-22 17:37:14'),
(4, 'Test Address 2026-01-22 16:26:48', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:26:48', '2026-01-22 17:37:14'),
(5, 'Test Address 2026-01-22 16:26:58', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:26:58', '2026-01-22 17:37:14'),
(6, 'Company Test Address', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:26:58', '2026-01-22 17:37:14'),
(7, 'Test Address 2026-01-22 16:27:15', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:27:15', '2026-01-22 17:37:14'),
(8, 'Company Test Address', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:27:15', '2026-01-22 17:37:14'),
(9, 'Test Address 2026-01-22 16:27:37', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:27:37', '2026-01-22 17:37:14'),
(10, 'Company Test Address', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:27:37', '2026-01-22 17:37:14'),
(11, 'Test Address 2026-01-22 16:27:55', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:27:55', '2026-01-22 17:37:14'),
(12, 'Company Test Address', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:27:55', '2026-01-22 17:37:14'),
(13, 'Test Address 2026-01-22 16:30:03', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:30:03', '2026-01-22 17:37:14'),
(14, 'Company Test Address', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:30:03', '2026-01-22 17:37:14'),
(15, 'Test Address 2026-01-22 16:30:26', 2, 1, 1, 1, '12345', NULL, NULL, 1, '2026-01-22 15:30:26', '2026-01-22 17:37:14'),
(16, 'Jl. Merdeka No. 123, RT 001/RW 002', 2, 1, 1, 1, '10310', NULL, NULL, 1, '2026-01-22 17:22:55', '2026-01-22 17:37:14'),
(17, 'Jl. Sudirman No. 456, RT 003/RW 004', 2, 2, 2, 2, '10250', NULL, NULL, 1, '2026-01-22 17:22:55', '2026-01-22 17:37:14'),
(18, 'Jl. Gatot Subroto No. 789, RT 005/RW 006', 2, 3, 3, 3, '10460', NULL, NULL, 1, '2026-01-22 17:22:55', '2026-01-22 17:37:14'),
(19, 'Jl. Thamrin No. 321, RT 007/RW 008', 2, 4, 4, 4, '10110', NULL, NULL, 1, '2026-01-22 17:22:55', '2026-01-22 17:37:14'),
(20, 'Jl. Hayam Wuruk No. 654, RT 009/RW 010', 2, 5, 5, 5, '10120', NULL, NULL, 1, '2026-01-22 17:22:55', '2026-01-22 17:37:14');

-- --------------------------------------------------------

--
-- Table structure for table `address_usage`
--

CREATE TABLE `address_usage` (
  `id_usage` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `entity_type` enum('company','branch','member','supplier','customer') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `usage_type` enum('primary','billing','shipping','contact') DEFAULT 'primary',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracking penggunaan alamat oleh berbagai entitas';

-- --------------------------------------------------------

--
-- Table structure for table `ajax_requests`
--

CREATE TABLE `ajax_requests` (
  `id_request` int(11) NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `request_type` varchar(50) DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_data`)),
  `response_status` varchar(20) DEFAULT NULL,
  `response_time_ms` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_rate_limits`
--

CREATE TABLE `api_rate_limits` (
  `id_limit` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `identifier_type` enum('ip','user','session') DEFAULT 'ip',
  `endpoint` varchar(255) DEFAULT NULL,
  `request_count` int(11) DEFAULT 1,
  `window_start` timestamp NOT NULL DEFAULT current_timestamp(),
  `window_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_blocked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id_branch` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `branch_name` varchar(200) NOT NULL,
  `branch_code` varchar(50) NOT NULL,
  `branch_type` enum('toko','warung','minimarket','gerai','kios','online') DEFAULT 'toko',
  `owner_name` varchar(200) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `operation_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operation_hours`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `sync_status` enum('synced','pending','error') DEFAULT 'synced',
  `real_time_status` enum('online','offline','maintenance') DEFAULT 'offline',
  `last_ping_at` timestamp NULL DEFAULT NULL,
  `auto_refresh_enabled` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id_branch`, `company_id`, `branch_name`, `branch_code`, `branch_type`, `owner_name`, `phone`, `email`, `address_id`, `location_id`, `province_id`, `regency_id`, `district_id`, `village_id`, `operation_hours`, `is_active`, `created_at`, `updated_at`, `last_sync_at`, `sync_status`, `real_time_status`, `last_ping_at`, `auto_refresh_enabled`) VALUES
(1, 1, 'Toko Cabang A', 'TSB001-A', 'toko', 'Budi Santoso', '021-2345-6789', 'cabanga@tokosejahtera.com', NULL, NULL, NULL, NULL, NULL, NULL, '{\"monday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"tuesday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"wednesday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"thursday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"friday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"saturday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"sunday\":{\"open\":\"09:00\",\"close\":\"20:00\"}}', 1, '2026-01-21 19:39:30', '2026-01-21 19:39:30', NULL, 'synced', 'offline', NULL, 1),
(2, 1, 'Toko Cabang B', 'TSB001-B', 'warung', 'Siti Nurhaliza', '021-3456-7890', 'cabangb@tokosejahtera.com', NULL, NULL, NULL, NULL, NULL, NULL, '{\"monday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"tuesday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"wednesday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"thursday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"friday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"saturday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"sunday\":{\"open\":\"08:00\",\"close\":\"21:00\"}}', 1, '2026-01-21 19:39:30', '2026-01-21 19:39:30', NULL, 'synced', 'offline', NULL, 1),
(3, 1, 'Toko Sejahtera Pusat', 'TS001-01', '', 'Budi Santoso', '08123456789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', 'offline', NULL, 1),
(4, 1, 'Toko Sejahtera Cabang 1', 'TS001-02', '', 'Budi Santoso', '08123456789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', 'offline', NULL, 1),
(5, 2, 'Minimarket Makmur Utama', 'MM001-01', '', 'Siti Nurhaliza', '08234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', 'offline', NULL, 1),
(6, 3, 'Distributor Utama Gudang', 'DU001-01', '', 'Ahmad Fauzi', '08345678901', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', 'offline', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `branch_inventory`
--

CREATE TABLE `branch_inventory` (
  `id_inventory` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `stock_quantity` decimal(15,2) DEFAULT 0.00,
  `min_stock` decimal(15,2) DEFAULT 0.00,
  `max_stock` decimal(15,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_inventory`
--

INSERT INTO `branch_inventory` (`id_inventory`, `branch_id`, `product_id`, `stock_quantity`, `min_stock`, `max_stock`, `last_updated`) VALUES
(1, 1, 1, 20.00, 5.00, 50.00, '2026-01-21 19:40:06'),
(2, 1, 2, 30.00, 8.00, 60.00, '2026-01-21 19:40:06'),
(3, 1, 3, 25.00, 6.00, 40.00, '2026-01-21 19:40:06'),
(4, 1, 4, 15.00, 3.00, 30.00, '2026-01-21 19:40:06'),
(5, 1, 5, 10.00, 2.00, 25.00, '2026-01-21 19:40:06'),
(6, 2, 1, 15.00, 4.00, 40.00, '2026-01-21 19:40:06'),
(7, 2, 2, 25.00, 6.00, 50.00, '2026-01-21 19:40:06'),
(8, 2, 3, 20.00, 5.00, 35.00, '2026-01-21 19:40:06'),
(9, 2, 4, 12.00, 2.00, 25.00, '2026-01-21 19:40:06'),
(10, 2, 5, 8.00, 2.00, 20.00, '2026-01-21 19:40:06'),
(11, 3, 6, 100.00, 0.00, 0.00, '2026-01-22 18:46:07'),
(12, 3, 7, 100.00, 0.00, 0.00, '2026-01-22 18:46:07'),
(13, 3, 8, 100.00, 0.00, 0.00, '2026-01-22 18:46:07'),
(14, 3, 9, 100.00, 0.00, 0.00, '2026-01-22 18:46:07');

-- --------------------------------------------------------

--
-- Table structure for table `branch_locations`
--

CREATE TABLE `branch_locations` (
  `id_location` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch_operations`
--

CREATE TABLE `branch_operations` (
  `id_operation` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `operation_date` date NOT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `status` enum('open','closed','holiday') DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_data`
--

CREATE TABLE `cache_data` (
  `id_cache` int(11) NOT NULL,
  `cache_key` varchar(255) NOT NULL,
  `cache_value` longtext DEFAULT NULL,
  `cache_type` varchar(50) DEFAULT 'general',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_accounts`
--

CREATE TABLE `cash_accounts` (
  `id_cash` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('cash','bank','e_wallet') DEFAULT 'cash',
  `balance` decimal(15,2) DEFAULT 0.00,
  `account_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_branch` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id_category` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id_category`, `company_id`, `name`, `description`, `parent_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Makanan Pokok', 'Bahan makanan pokok sehari-hari', NULL, 1, '2026-01-22 17:49:22', '2026-01-22 17:49:22'),
(2, 1, 'Minuman', 'Berbagai jenis minuman', NULL, 1, '2026-01-22 17:49:22', '2026-01-22 17:49:22'),
(3, 1, 'Makanan Cepat', 'Makanan instan dan cepat saji', NULL, 1, '2026-01-22 17:49:22', '2026-01-22 17:49:22'),
(4, 1, 'Snack', 'Makanan ringan dan camilan', NULL, 1, '2026-01-22 17:49:22', '2026-01-22 17:49:22'),
(5, 1, 'Kebutuhan Rumah Tangga', 'Perlengkapan rumah tangga', NULL, 1, '2026-01-22 17:49:22', '2026-01-22 17:49:22'),
(6, 1, 'Makanan', NULL, NULL, 1, '2026-01-22 18:45:16', '2026-01-22 18:45:16'),
(7, 1, 'Kebutuhan Rumah', NULL, NULL, 1, '2026-01-22 18:45:16', '2026-01-22 18:45:16');

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id_account` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(200) NOT NULL,
  `account_type` enum('asset','liability','equity','revenue','expense') NOT NULL,
  `account_category` varchar(100) DEFAULT NULL,
  `normal_balance` enum('debit','credit') NOT NULL,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id_company` int(11) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `company_code` varchar(50) NOT NULL,
  `company_type` enum('individual','warung','kios','toko_kelontong','minimarket','pengusaha_menengah','distributor','koperasi','perusahaan_besar','franchise','pusat','cabang') NOT NULL DEFAULT 'individual',
  `scalability_level` enum('1','2','3','4','5','6') DEFAULT '1',
  `business_category` enum('retail','wholesale','manufacturing','agriculture','services','cooperative','online','franchise','distributor','personal') DEFAULT 'retail',
  `owner_name` varchar(200) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `business_license` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `sync_status` enum('synced','pending','error') DEFAULT 'synced',
  `api_access_key` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(500) DEFAULT NULL,
  `auto_refresh_interval` int(11) DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id_company`, `company_name`, `company_code`, `company_type`, `scalability_level`, `business_category`, `owner_name`, `phone`, `email`, `address_id`, `address`, `province_id`, `regency_id`, `district_id`, `village_id`, `tax_id`, `business_license`, `is_active`, `created_at`, `updated_at`, `last_sync_at`, `sync_status`, `api_access_key`, `webhook_url`, `auto_refresh_interval`) VALUES
(1, 'Toko Sejahtera Bersama', 'TSB001', 'individual', '2', 'retail', 'Ahmad Wijaya', '021-1234-5678', 'info@tokosejahtera.com', NULL, 'Jakarta Pusat', 12, 158, 1959, 25526, NULL, NULL, 0, '2026-01-21 19:39:30', '2026-01-22 16:56:32', NULL, 'synced', NULL, NULL, 30),
(2, 'Toko Sejahtera', 'TS001', 'pusat', '1', 'retail', 'Budi Santoso', '08123456789', 'budi@tokosejahtera.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', NULL, NULL, 30),
(3, 'Minimarket Makmur', 'MM001', 'pusat', '1', 'retail', 'Siti Nurhaliza', '08234567890', 'siti@minimakmur.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', NULL, NULL, 30),
(4, 'Distributor Utama', 'DU001', 'pusat', '1', 'retail', 'Ahmad Fauzi', '08345678901', 'ahmad@distributorutama.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', NULL, NULL, 30),
(8, 'Test Company 2026-01-22 16:27:37', 'TEST1769095657', 'individual', '1', 'retail', 'Test Owner', '08123456789', 'test@example.com', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 15:27:37', '2026-01-22 15:27:37', NULL, 'synced', NULL, NULL, 30),
(9, 'Test Company 2026-01-22 16:27:55', 'TEST1769095675', 'individual', '1', 'retail', 'Test Owner', '08123456789', 'test@example.com', 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 15:27:55', '2026-01-22 15:27:55', NULL, 'synced', NULL, NULL, 30),
(10, 'Test Company 2026-01-22 16:30:03', 'TEST1769095803', 'individual', '1', 'retail', 'Test Owner', '08123456789', 'test@example.com', 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 15:30:03', '2026-01-22 15:30:03', NULL, 'synced', NULL, NULL, 30),
(11, 'Test Company 2026-01-22 16:30:26', 'TEST1769095826', 'individual', '1', 'retail', 'Test Owner', '08123456789', 'test@example.com', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 15:30:26', '2026-01-22 15:30:26', NULL, 'synced', NULL, NULL, 30);

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id_setting` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `module_id` int(11) DEFAULT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id_customer` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transfers`
--

CREATE TABLE `inventory_transfers` (
  `id_transfer` int(11) NOT NULL,
  `from_branch_id` int(11) NOT NULL,
  `to_branch_id` int(11) NOT NULL,
  `transfer_number` varchar(50) NOT NULL,
  `transfer_date` date NOT NULL,
  `status` enum('draft','in_transit','received','cancelled') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transfer_items`
--

CREATE TABLE `inventory_transfer_items` (
  `id_item` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id_journal` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `journal_number` varchar(50) NOT NULL,
  `journal_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `debit_amount` decimal(15,2) DEFAULT 0.00,
  `credit_amount` decimal(15,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id_member` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `member_code` varchar(50) NOT NULL,
  `member_name` varchar(200) NOT NULL,
  `position` enum('owner','manager','cashier','staff','security') DEFAULT 'staff',
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `push_notifications_enabled` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id_member`, `branch_id`, `member_code`, `member_name`, `position`, `phone`, `email`, `password_hash`, `salary`, `join_date`, `is_active`, `created_at`, `updated_at`, `last_login_at`, `last_activity_at`, `session_id`, `is_online`, `push_notifications_enabled`) VALUES
(1, 1, 'ADMIN', 'Administrator', 'owner', '08123456789', 'admin@dagang.com', '$2y$10$nscbO82wYaIPTLR7ZJQy/u6fA4tHzleg9ecLTBqFYA4yKERxVuloS', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-23 15:02:44', '2026-01-23 15:02:44', NULL, NULL, 0, 1),
(2, 1, 'MBR001', 'Budi Santoso', 'owner', '08123456789', 'budi@tokosejahtera.com', '\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-22 14:13:41', NULL, NULL, NULL, 0, 1),
(3, 1, 'MBR002', 'Andi Wijaya', 'cashier', '08123456788', 'andi@tokosejahtera.com', '\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-22 14:13:41', NULL, NULL, NULL, 0, 1),
(4, 2, 'MBR003', 'Cahaya Putri', 'manager', '08123456787', 'cahaya@tokosejahtera.com', '\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-22 14:13:41', NULL, NULL, NULL, 0, 1),
(5, 3, 'MBR004', 'Siti Nurhaliza', 'owner', '08234567890', 'siti@minimakmur.com', '\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-22 14:13:41', NULL, NULL, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id_module` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `version` varchar(20) DEFAULT '1.0.0',
  `description` text DEFAULT NULL,
  `type` enum('core','addon','plugin') DEFAULT 'addon',
  `dependencies` text DEFAULT NULL,
  `settings` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_queue`
--

CREATE TABLE `notification_queue` (
  `id_notification` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `status` enum('pending','sent','failed','expired') DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id_product` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'PCS',
  `purchase_price` decimal(15,2) DEFAULT NULL,
  `selling_price` decimal(15,2) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_inventory_update` timestamp NULL DEFAULT NULL,
  `low_stock_threshold` int(11) DEFAULT 10,
  `auto_reorder_enabled` tinyint(1) DEFAULT 0,
  `real_time_tracking` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id_product`, `product_code`, `product_name`, `category_id`, `description`, `unit`, `purchase_price`, `selling_price`, `barcode`, `image_url`, `is_active`, `created_at`, `updated_at`, `last_inventory_update`, `low_stock_threshold`, `auto_reorder_enabled`, `real_time_tracking`) VALUES
(1, 'PRD001', 'Beras Premium 5kg', 1, 'Beras kualitas premium kemasan 5kg', 'KG', 55000.00, 65000.00, '888889000001', NULL, 1, '2026-01-21 19:40:02', '2026-01-21 19:40:02', NULL, 10, 0, 1),
(2, 'PRD002', 'Minyak Goreng 2L', 1, 'Minyak goreng kemasan 2 liter', 'LITER', 28000.00, 35000.00, '888889000002', NULL, 1, '2026-01-21 19:40:02', '2026-01-21 19:40:02', NULL, 10, 0, 1),
(3, 'PRD003', 'Gula Pasir 1kg', 1, 'Gula pasir kemasan 1kg', 'KG', 13000.00, 16000.00, '888889000003', NULL, 1, '2026-01-21 19:40:02', '2026-01-21 19:40:02', NULL, 10, 0, 1),
(4, 'PRD004', 'Kopi Sachet', 2, 'Kopi instan sachet 10x20g', 'BOX', 20000.00, 25000.00, '888889000004', NULL, 1, '2026-01-21 19:40:02', '2026-01-21 19:40:02', NULL, 10, 0, 1),
(5, 'PRD005', 'Indomie Mie Goreng', 3, 'Mie instan goreng 40x80g', 'BOX', 80000.00, 95000.00, '888889000005', NULL, 1, '2026-01-21 19:40:02', '2026-01-21 19:40:02', NULL, 10, 0, 1),
(6, 'PRD26010101', 'Air Mineral 600ml', 2, NULL, 'Botol', 2500.00, 4000.00, NULL, NULL, 1, '2026-01-22 18:45:16', '2026-01-22 18:45:16', NULL, 10, 0, 1),
(7, 'PRD26010102', 'Teh Botol 450ml', 2, NULL, 'Botol', 5000.00, 7000.00, NULL, NULL, 1, '2026-01-22 18:45:16', '2026-01-22 18:45:16', NULL, 10, 0, 1),
(8, 'PRD26010103', 'Mi Instan Goreng', 6, NULL, 'Pcs', 2500.00, 3500.00, NULL, NULL, 1, '2026-01-22 18:45:16', '2026-01-22 18:45:16', NULL, 10, 0, 1),
(9, 'PRD26010104', 'Sabun Mandi 90g', 7, NULL, 'Pcs', 4000.00, 6000.00, NULL, NULL, 1, '2026-01-22 18:45:16', '2026-01-22 18:45:16', NULL, 10, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id_movement` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('in','out','adjustment','transfer_in','transfer_out','sale','return') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `previous_stock` decimal(10,2) NOT NULL,
  `current_stock` decimal(10,2) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id_supplier` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id_log` int(11) NOT NULL,
  `log_level` enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') DEFAULT 'INFO',
  `category` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id_transaction` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `transaction_type` enum('sale','purchase','return','transfer','adjustment') NOT NULL,
  `transaction_date` datetime NOT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `final_amount` decimal(15,2) DEFAULT NULL,
  `payment_method` enum('cash','transfer','debit','credit','e_wallet') DEFAULT 'cash',
  `payment_status` enum('pending','paid','partial','refunded') DEFAULT 'pending',
  `status` enum('draft','completed','cancelled','refunded') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sync_status` enum('synced','pending','error') DEFAULT 'synced',
  `processed_at` timestamp NULL DEFAULT NULL,
  `notification_sent` tinyint(1) DEFAULT 0,
  `real_time_update` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_details`
--

CREATE TABLE `transaction_details` (
  `id_detail` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

CREATE TABLE `transaction_items` (
  `id_item` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `total_price` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_items`
--

CREATE TABLE `transfer_items` (
  `id_item` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_requested` decimal(10,2) NOT NULL,
  `quantity_sent` decimal(10,2) DEFAULT 0.00,
  `quantity_received` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','owner','manager','staff','cashier') DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id_session` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_branch_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_branch_summary` (
`id_branch` int(11)
,`branch_name` varchar(200)
,`branch_code` varchar(50)
,`branch_type` enum('toko','warung','minimarket','gerai','kios','online')
,`company_name` varchar(200)
,`address` text
,`province_name` varchar(100)
,`regency_name` varchar(100)
,`district_name` varchar(100)
,`village_name` varchar(100)
,`total_members` bigint(21)
,`total_products` bigint(21)
,`total_stock` decimal(37,2)
,`total_transactions` bigint(21)
,`total_revenue` decimal(37,2)
,`is_active` tinyint(1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_branch_summary_ajax`
-- (See below for the actual view)
--
CREATE TABLE `v_branch_summary_ajax` (
`id_branch` int(11)
,`branch_name` varchar(200)
,`branch_code` varchar(50)
,`branch_type` enum('toko','warung','minimarket','gerai','kios','online')
,`real_time_status` enum('online','offline','maintenance')
,`last_ping_at` timestamp
,`company_name` varchar(200)
,`product_count` bigint(21)
,`total_stock` decimal(37,2)
,`low_stock_count` bigint(21)
,`online_members` bigint(21)
,`is_active` tinyint(1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_complete_addresses`
-- (See below for the actual view)
--
CREATE TABLE `v_complete_addresses` (
`id_address` int(11)
,`street_address` text
,`postal_code` varchar(10)
,`latitude` decimal(10,8)
,`longitude` decimal(11,8)
,`is_active` tinyint(1)
,`created_at` timestamp
,`updated_at` timestamp
,`province_name` varchar(100)
,`regency_name` varchar(100)
,`district_name` varchar(100)
,`village_name` varchar(100)
,`full_address` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_dashboard_realtime`
-- (See below for the actual view)
--
CREATE TABLE `v_dashboard_realtime` (
`total_companies` bigint(21)
,`active_companies` bigint(21)
,`total_branches` bigint(21)
,`active_branches` bigint(21)
,`open_branches_count` bigint(21)
,`today_sales_amount` int(1)
,`today_transactions_count` int(1)
,`low_stock_alerts` bigint(21)
,`last_updated` timestamp
,`active_sessions` bigint(21)
,`recent_ajax_requests` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_entity_addresses`
-- (See below for the actual view)
--
CREATE TABLE `v_entity_addresses` (
`entity_type` enum('company','branch','member','supplier','customer')
,`entity_id` int(11)
,`usage_type` enum('primary','billing','shipping','contact')
,`is_active` tinyint(1)
,`id_address` int(11)
,`street_address` text
,`postal_code` varchar(10)
,`province_name` varchar(100)
,`regency_name` varchar(100)
,`district_name` varchar(100)
,`village_name` varchar(100)
,`full_address` mediumtext
);

-- --------------------------------------------------------

--
-- Table structure for table `_backup_product_categories`
--

CREATE TABLE `_backup_product_categories` (
  `id_category` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `_backup_product_categories`
--

INSERT INTO `_backup_product_categories` (`id_category`, `category_name`, `description`, `parent_id`, `created_at`) VALUES
(1, 'Makanan Pokok', 'Bahan makanan pokok sehari-hari', NULL, '2026-01-21 19:39:58'),
(2, 'Minuman', 'Berbagai jenis minuman', NULL, '2026-01-21 19:39:58'),
(3, 'Makanan Cepat', 'Makanan instan dan cepat saji', NULL, '2026-01-21 19:39:58'),
(4, 'Snack', 'Makanan ringan dan camilan', NULL, '2026-01-21 19:39:58'),
(5, 'Kebutuhan Rumah Tangga', 'Perlengkapan rumah tangga', NULL, '2026-01-21 19:39:58');

-- --------------------------------------------------------

--
-- Structure for view `v_branch_summary`
--
DROP TABLE IF EXISTS `v_branch_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_branch_summary`  AS SELECT `b`.`id_branch` AS `id_branch`, `b`.`branch_name` AS `branch_name`, `b`.`branch_code` AS `branch_code`, `b`.`branch_type` AS `branch_type`, `c`.`company_name` AS `company_name`, `bl`.`address` AS `address`, `p`.`name` AS `province_name`, `r`.`name` AS `regency_name`, `d`.`name` AS `district_name`, `v`.`name` AS `village_name`, count(distinct `m`.`id_member`) AS `total_members`, count(distinct `bi`.`id_inventory`) AS `total_products`, sum(`bi`.`stock_quantity`) AS `total_stock`, count(distinct `t`.`id_transaction`) AS `total_transactions`, coalesce(sum(`t`.`final_amount`),0) AS `total_revenue`, `b`.`is_active` AS `is_active` FROM (((((((((`branches` `b` left join `companies` `c` on(`b`.`company_id` = `c`.`id_company`)) left join `branch_locations` `bl` on(`b`.`id_branch` = `bl`.`branch_id`)) left join `alamat_db`.`provinces` `p` on(`bl`.`province_id` = `p`.`id`)) left join `alamat_db`.`regencies` `r` on(`bl`.`regency_id` = `r`.`id`)) left join `alamat_db`.`districts` `d` on(`bl`.`district_id` = `d`.`id`)) left join `alamat_db`.`villages` `v` on(`bl`.`village_id` = `v`.`id`)) left join `members` `m` on(`b`.`id_branch` = `m`.`branch_id` and `m`.`is_active` = 1)) left join `branch_inventory` `bi` on(`b`.`id_branch` = `bi`.`branch_id`)) left join `transactions` `t` on(`b`.`id_branch` = `t`.`branch_id` and `t`.`status` = 'completed')) GROUP BY `b`.`id_branch`, `b`.`branch_name`, `b`.`branch_code`, `b`.`branch_type`, `c`.`company_name`, `bl`.`address`, `p`.`name`, `r`.`name`, `d`.`name`, `v`.`name`, `b`.`is_active` ;

-- --------------------------------------------------------

--
-- Structure for view `v_branch_summary_ajax`
--
DROP TABLE IF EXISTS `v_branch_summary_ajax`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_branch_summary_ajax`  AS SELECT `b`.`id_branch` AS `id_branch`, `b`.`branch_name` AS `branch_name`, `b`.`branch_code` AS `branch_code`, `b`.`branch_type` AS `branch_type`, `b`.`real_time_status` AS `real_time_status`, `b`.`last_ping_at` AS `last_ping_at`, `c`.`company_name` AS `company_name`, count(distinct `bi`.`product_id`) AS `product_count`, coalesce(sum(`bi`.`stock_quantity`),0) AS `total_stock`, count(distinct case when `bi`.`stock_quantity` <= `bi`.`min_stock` then `bi`.`product_id` end) AS `low_stock_count`, count(distinct case when `m`.`is_online` = 1 then `m`.`id_member` end) AS `online_members`, `b`.`is_active` AS `is_active` FROM ((((`branches` `b` left join `companies` `c` on(`b`.`company_id` = `c`.`id_company`)) left join `branch_inventory` `bi` on(`b`.`id_branch` = `bi`.`branch_id`)) left join `products` `p` on(`bi`.`product_id` = `p`.`id_product`)) left join `members` `m` on(`b`.`id_branch` = `m`.`branch_id`)) GROUP BY `b`.`id_branch`, `b`.`branch_name`, `b`.`branch_code`, `b`.`branch_type`, `b`.`real_time_status`, `b`.`last_ping_at`, `c`.`company_name`, `b`.`is_active` ;

-- --------------------------------------------------------

--
-- Structure for view `v_complete_addresses`
--
DROP TABLE IF EXISTS `v_complete_addresses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_complete_addresses`  AS SELECT `a`.`id_address` AS `id_address`, `a`.`street_address` AS `street_address`, `a`.`postal_code` AS `postal_code`, `a`.`latitude` AS `latitude`, `a`.`longitude` AS `longitude`, `a`.`is_active` AS `is_active`, `a`.`created_at` AS `created_at`, `a`.`updated_at` AS `updated_at`, `p`.`name` AS `province_name`, `r`.`name` AS `regency_name`, `d`.`name` AS `district_name`, `v`.`name` AS `village_name`, concat(`a`.`street_address`,', ',`v`.`name`,', ',`d`.`name`,', ',`r`.`name`,', ',`p`.`name`,if(`a`.`postal_code` is not null,concat(' ',`a`.`postal_code`),'')) AS `full_address` FROM ((((`addresses` `a` left join `alamat_db`.`provinces` `p` on(`a`.`province_id` = `p`.`id`)) left join `alamat_db`.`regencies` `r` on(`a`.`regency_id` = `r`.`id`)) left join `alamat_db`.`districts` `d` on(`a`.`district_id` = `d`.`id`)) left join `alamat_db`.`villages` `v` on(`a`.`village_id` = `v`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_dashboard_realtime`
--
DROP TABLE IF EXISTS `v_dashboard_realtime`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_dashboard_realtime`  AS SELECT (select count(0) from `companies` where `companies`.`is_active` = 1) AS `total_companies`, (select count(0) from `companies` where `companies`.`is_active` = 1) AS `active_companies`, (select count(0) from `branches` where `branches`.`is_active` = 1) AS `total_branches`, (select count(0) from `branches` where `branches`.`is_active` = 1 and `branches`.`real_time_status` = 'online') AS `active_branches`, (select count(0) from `branches` where `branches`.`is_active` = 1 and `branches`.`real_time_status` = 'online') AS `open_branches_count`, 0 AS `today_sales_amount`, 0 AS `today_transactions_count`, (select count(0) from (`branch_inventory` `bi` join `products` `p` on(`bi`.`product_id` = `p`.`id_product`)) where `bi`.`stock_quantity` <= `bi`.`min_stock`) AS `low_stock_alerts`, (select max(`companies`.`updated_at`) from `companies`) AS `last_updated`, (select count(0) from `user_sessions` where `user_sessions`.`is_active` = 1) AS `active_sessions`, (select count(0) from `ajax_requests` where `ajax_requests`.`created_at` >= current_timestamp() - interval 1 hour) AS `recent_ajax_requests` ;

-- --------------------------------------------------------

--
-- Structure for view `v_entity_addresses`
--
DROP TABLE IF EXISTS `v_entity_addresses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_entity_addresses`  AS SELECT `e`.`entity_type` AS `entity_type`, `e`.`entity_id` AS `entity_id`, `e`.`usage_type` AS `usage_type`, `e`.`is_active` AS `is_active`, `a`.`id_address` AS `id_address`, `a`.`street_address` AS `street_address`, `a`.`postal_code` AS `postal_code`, `p`.`name` AS `province_name`, `r`.`name` AS `regency_name`, `d`.`name` AS `district_name`, `v`.`name` AS `village_name`, concat(`a`.`street_address`,', ',`v`.`name`,', ',`d`.`name`,', ',`r`.`name`,', ',`p`.`name`,if(`a`.`postal_code` is not null,concat(' ',`a`.`postal_code`),'')) AS `full_address` FROM (((((`address_usage` `e` join `addresses` `a` on(`e`.`address_id` = `a`.`id_address`)) left join `alamat_db`.`provinces` `p` on(`a`.`province_id` = `p`.`id`)) left join `alamat_db`.`regencies` `r` on(`a`.`regency_id` = `r`.`id`)) left join `alamat_db`.`districts` `d` on(`a`.`district_id` = `d`.`id`)) left join `alamat_db`.`villages` `v` on(`a`.`village_id` = `v`.`id`)) WHERE `e`.`is_active` = 1 AND `a`.`is_active` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id_address`),
  ADD KEY `idx_province_id` (`province_id`),
  ADD KEY `idx_regency_id` (`regency_id`),
  ADD KEY `idx_district_id` (`district_id`),
  ADD KEY `idx_village_id` (`village_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_postal_code` (`postal_code`);

--
-- Indexes for table `address_usage`
--
ALTER TABLE `address_usage`
  ADD PRIMARY KEY (`id_usage`),
  ADD UNIQUE KEY `uk_entity_address_usage` (`entity_type`,`entity_id`,`usage_type`),
  ADD KEY `idx_address_id` (`address_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_usage_type` (`usage_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `ajax_requests`
--
ALTER TABLE `ajax_requests`
  ADD PRIMARY KEY (`id_request`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_request_type` (`request_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `api_rate_limits`
--
ALTER TABLE `api_rate_limits`
  ADD PRIMARY KEY (`id_limit`),
  ADD UNIQUE KEY `uk_identifier_endpoint_window` (`identifier`,`endpoint`,`window_start`),
  ADD KEY `idx_identifier` (`identifier`),
  ADD KEY `idx_identifier_type` (`identifier_type`),
  ADD KEY `idx_endpoint` (`endpoint`),
  ADD KEY `idx_window_end` (`window_end`),
  ADD KEY `idx_is_blocked` (`is_blocked`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id_branch`),
  ADD UNIQUE KEY `uk_company_branch` (`company_id`,`branch_code`),
  ADD UNIQUE KEY `branch_code` (`branch_code`),
  ADD KEY `idx_branch_code` (`branch_code`),
  ADD KEY `idx_branch_type` (`branch_type`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_branch_address_id` (`address_id`);

--
-- Indexes for table `branch_inventory`
--
ALTER TABLE `branch_inventory`
  ADD PRIMARY KEY (`id_inventory`),
  ADD UNIQUE KEY `uk_branch_product` (`branch_id`,`product_id`),
  ADD KEY `idx_branch_stock` (`branch_id`,`stock_quantity`),
  ADD KEY `idx_product_stock` (`product_id`,`stock_quantity`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `branch_locations`
--
ALTER TABLE `branch_locations`
  ADD PRIMARY KEY (`id_location`),
  ADD KEY `idx_branch` (`branch_id`),
  ADD KEY `idx_province` (`province_id`),
  ADD KEY `idx_regency` (`regency_id`);

--
-- Indexes for table `branch_operations`
--
ALTER TABLE `branch_operations`
  ADD PRIMARY KEY (`id_operation`),
  ADD KEY `idx_branch_date` (`branch_id`,`operation_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `cache_data`
--
ALTER TABLE `cache_data`
  ADD PRIMARY KEY (`id_cache`),
  ADD UNIQUE KEY `cache_key` (`cache_key`),
  ADD KEY `idx_cache_key` (`cache_key`),
  ADD KEY `idx_cache_type` (`cache_type`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `cash_accounts`
--
ALTER TABLE `cash_accounts`
  ADD PRIMARY KEY (`id_cash`),
  ADD KEY `idx_branch_cash` (`branch_id`),
  ADD KEY `idx_account_type` (`account_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_category`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`id_account`),
  ADD UNIQUE KEY `uk_branch_account` (`branch_id`,`account_code`),
  ADD KEY `idx_account_code` (`account_code`),
  ADD KEY `idx_account_type` (`account_type`),
  ADD KEY `idx_account_category` (`account_category`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id_company`),
  ADD UNIQUE KEY `company_code` (`company_code`),
  ADD KEY `idx_company_code` (`company_code`),
  ADD KEY `idx_company_type` (`company_type`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_company_address_id` (`address_id`),
  ADD KEY `idx_scalability_level` (`scalability_level`),
  ADD KEY `idx_business_category` (`business_category`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id_setting`),
  ADD UNIQUE KEY `uk_company_setting` (`company_id`,`setting_key`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id_customer`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  ADD PRIMARY KEY (`id_transfer`),
  ADD UNIQUE KEY `uk_transfer_number` (`transfer_number`),
  ADD KEY `from_branch_id` (`from_branch_id`),
  ADD KEY `to_branch_id` (`to_branch_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_transfer_date` (`transfer_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `inventory_transfer_items`
--
ALTER TABLE `inventory_transfer_items`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `idx_transfer` (`transfer_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id_journal`),
  ADD UNIQUE KEY `uk_branch_journal` (`branch_id`,`journal_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_journal_date` (`journal_date`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id_member`),
  ADD UNIQUE KEY `uk_branch_member` (`branch_id`,`member_code`),
  ADD KEY `idx_member_code` (`member_code`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id_module`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `notification_queue`
--
ALTER TABLE `notification_queue`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id_product`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `idx_product_code` (`product_code`),
  ADD KEY `idx_product_name` (`product_name`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id_movement`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_movement_date` (`created_at`),
  ADD KEY `idx_movement_type` (`type`),
  ADD KEY `idx_mov_branch_product` (`branch_id`,`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id_supplier`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_log_level` (`log_level`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id_transaction`),
  ADD UNIQUE KEY `uk_branch_transaction` (`branch_id`,`transaction_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `idx_transaction` (`transaction_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_trx_id` (`transaction_id`),
  ADD KEY `idx_trx_product_id` (`product_id`);

--
-- Indexes for table `transfer_items`
--
ALTER TABLE `transfer_items`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `transfer_id` (`transfer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id_session`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `_backup_product_categories`
--
ALTER TABLE `_backup_product_categories`
  ADD PRIMARY KEY (`id_category`),
  ADD UNIQUE KEY `uk_category_name` (`category_name`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id_address` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `address_usage`
--
ALTER TABLE `address_usage`
  MODIFY `id_usage` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ajax_requests`
--
ALTER TABLE `ajax_requests`
  MODIFY `id_request` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_rate_limits`
--
ALTER TABLE `api_rate_limits`
  MODIFY `id_limit` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id_branch` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `branch_inventory`
--
ALTER TABLE `branch_inventory`
  MODIFY `id_inventory` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `branch_locations`
--
ALTER TABLE `branch_locations`
  MODIFY `id_location` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branch_operations`
--
ALTER TABLE `branch_operations`
  MODIFY `id_operation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cache_data`
--
ALTER TABLE `cache_data`
  MODIFY `id_cache` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_accounts`
--
ALTER TABLE `cash_accounts`
  MODIFY `id_cash` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  MODIFY `id_account` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id_company` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id_customer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  MODIFY `id_transfer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transfer_items`
--
ALTER TABLE `inventory_transfer_items`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id_journal` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id_member` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id_module` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_queue`
--
ALTER TABLE `notification_queue`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id_movement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id_transaction` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_items`
--
ALTER TABLE `transfer_items`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id_session` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `_backup_product_categories`
--
ALTER TABLE `_backup_product_categories`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_branches_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id_address`) ON DELETE SET NULL;

--
-- Constraints for table `branch_inventory`
--
ALTER TABLE `branch_inventory`
  ADD CONSTRAINT `branch_inventory_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_inventory_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`) ON DELETE CASCADE;

--
-- Constraints for table `branch_locations`
--
ALTER TABLE `branch_locations`
  ADD CONSTRAINT `branch_locations_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE;

--
-- Constraints for table `branch_operations`
--
ALTER TABLE `branch_operations`
  ADD CONSTRAINT `branch_operations_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE;

--
-- Constraints for table `cash_accounts`
--
ALTER TABLE `cash_accounts`
  ADD CONSTRAINT `cash_accounts_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id_category`) ON DELETE SET NULL;

--
-- Constraints for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD CONSTRAINT `chart_of_accounts_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `fk_companies_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id_address`) ON DELETE SET NULL;

--
-- Constraints for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD CONSTRAINT `company_settings_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE,
  ADD CONSTRAINT `company_settings_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE,
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id_address`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  ADD CONSTRAINT `inventory_transfers_ibfk_1` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transfers_ibfk_2` FOREIGN KEY (`to_branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transfers_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `inventory_transfer_items`
--
ALTER TABLE `inventory_transfer_items`
  ADD CONSTRAINT `inventory_transfer_items_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `inventory_transfers` (`id_transfer`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transfer_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`) ON DELETE CASCADE;

--
-- Constraints for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD CONSTRAINT `journal_entries_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE,
  ADD CONSTRAINT `journal_entries_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id_category`) ON DELETE SET NULL;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`) ON DELETE CASCADE;

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE,
  ADD CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id_address`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `transaction_details_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id_transaction`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`);

--
-- Constraints for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id_transaction`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`) ON DELETE CASCADE;

--
-- Constraints for table `transfer_items`
--
ALTER TABLE `transfer_items`
  ADD CONSTRAINT `transfer_items_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `inventory_transfers` (`id_transfer`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfer_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE;

--
-- Constraints for table `_backup_product_categories`
--
ALTER TABLE `_backup_product_categories`
  ADD CONSTRAINT `_backup_product_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `_backup_product_categories` (`id_category`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
