-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 25, 2026 at 07:26 PM
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
CREATE DEFINER=`root`@`::1` PROCEDURE `cleanup_old_files` (IN `days_old` INT)   BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE file_id_var INT;
    DECLARE file_path_var VARCHAR(500);
    
    DECLARE file_cursor CURSOR FOR 
        SELECT id_file, file_path 
        FROM files 
        WHERE is_active = 1 
        AND created_at < DATE_SUB(NOW(), INTERVAL days_old DAY);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN file_cursor;
    
    read_loop: LOOP
        FETCH file_cursor INTO file_id_var, file_path_var;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        
        UPDATE files SET is_active = 0 WHERE id_file = file_id_var;
        
        
        INSERT INTO file_access_log (file_id, access_type, user_agent)
        VALUES (file_id_var, 'delete', 'SYSTEM_CLEANUP');
        
    END LOOP;
    
    CLOSE file_cursor;
END$$

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

--
-- Functions
--
CREATE DEFINER=`root`@`::1` FUNCTION `format_file_size` (`bytes` BIGINT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC BEGIN
    DECLARE units VARCHAR(50) DEFAULT 'B,KB,MB,GB,TB';
    DECLARE unit_index INT DEFAULT 0;
    DECLARE formatted_size VARCHAR(20);
    
    WHILE bytes >= 1024 AND unit_index < 4 DO
        SET bytes = bytes / 1024;
        SET unit_index = unit_index + 1;
    END WHILE;
    
    SET formatted_size = CONCAT(ROUND(bytes, 2), ' ', SUBSTRING_INDEX(units, ',', unit_index + 1));
    
    RETURN formatted_size;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id_address` int(11) NOT NULL,
  `address_detail` text NOT NULL COMMENT 'Alamat jalan lengkap (manual input)',
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

INSERT INTO `addresses` (`id_address`, `address_detail`, `province_id`, `regency_id`, `district_id`, `village_id`, `postal_code`, `latitude`, `longitude`, `is_active`, `created_at`, `updated_at`) VALUES
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
(20, 'Jl. Hayam Wuruk No. 654, RT 009/RW 010', 2, 5, 5, 5, '10120', NULL, NULL, 1, '2026-01-22 17:22:55', '2026-01-22 17:37:14'),
(21, 'Jl. Merdeka No. 123, RT 001/RW 002', 1, 1, 1, 1, '10310', NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(22, 'Jl. Sudirman No. 456, RT 003/RW 004', 1, 2, 2, 2, '10250', NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(23, 'Jl. Gatot Subroto No. 789, RT 005/RW 006', 1, 3, 3, 3, '10460', NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(24, 'Jl. Thamrin No. 321, RT 007/RW 008', 1, 4, 4, 4, '10110', NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(25, 'Jl. Hayam Wuruk No. 654, RT 009/RW 010', 1, 5, 5, 5, '10120', NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16');

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
-- Table structure for table `advanced_reports`
--

CREATE TABLE `advanced_reports` (
  `id` int(11) NOT NULL,
  `report_id` varchar(50) NOT NULL,
  `report_type` enum('sales','inventory','customer','financial','performance','custom') NOT NULL,
  `report_title` varchar(255) NOT NULL,
  `report_description` text DEFAULT NULL,
  `date_range` varchar(20) DEFAULT '7d',
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `ai_model` varchar(50) DEFAULT 'standard',
  `status` enum('pending','processing','completed','failed','expired') DEFAULT 'pending',
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_models`
--

CREATE TABLE `ai_models` (
  `id` int(11) NOT NULL,
  `model_type` varchar(50) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `version` varchar(20) DEFAULT '1.0',
  `accuracy` decimal(5,4) DEFAULT 0.0000,
  `precision` decimal(5,4) DEFAULT 0.0000,
  `recall` decimal(5,4) DEFAULT 0.0000,
  `f1_score` decimal(5,4) DEFAULT 0.0000,
  `model_size` int(11) DEFAULT 0,
  `training_samples` int(11) DEFAULT 0,
  `last_trained` timestamp NULL DEFAULT NULL,
  `training_time` int(11) DEFAULT 0,
  `status` enum('training','trained','failed','deprecated') DEFAULT 'training',
  `model_path` varchar(500) DEFAULT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `performance_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`performance_metrics`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_models`
--

INSERT INTO `ai_models` (`id`, `model_type`, `model_name`, `description`, `version`, `accuracy`, `precision`, `recall`, `f1_score`, `model_size`, `training_samples`, `last_trained`, `training_time`, `status`, `model_path`, `config`, `performance_metrics`, `created_at`, `updated_at`) VALUES
(1, 'sales_forecasting', 'Sales Forecasting Model', 'Predict future sales based on historical data and trends', '1.0', 0.8500, 0.8200, 0.8800, 0.8480, 0, 1000, '2024-01-15 03:30:00', 0, 'trained', NULL, NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(2, 'inventory_optimization', 'Inventory Optimization Model', 'Optimize inventory levels based on demand patterns', '1.0', 0.7800, 0.7500, 0.8200, 0.7820, 0, 800, '2024-01-15 02:15:00', 0, 'trained', NULL, NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(3, 'customer_segmentation', 'Customer Segmentation Model', 'AI-powered customer behavior analysis and segmentation', '1.0', 0.8200, 0.8000, 0.8500, 0.8240, 0, 600, '2024-01-15 04:30:00', 0, 'trained', NULL, NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(4, 'price_optimization', 'Price Optimization Model', 'AI-powered pricing recommendations based on market data', '1.0', 0.7500, 0.7200, 0.7800, 0.7480, 0, 400, '2024-01-15 05:30:00', 0, 'trained', NULL, NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(5, 'anomaly_detection', 'Anomaly Detection Model', 'Detect unusual patterns and anomalies in business data', '1.0', 0.8800, 0.8500, 0.9100, 0.8790, 0, 1200, '2024-01-15 06:30:00', 0, 'trained', NULL, NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `ai_training_history`
--

CREATE TABLE `ai_training_history` (
  `id` int(11) NOT NULL,
  `model_type` varchar(50) NOT NULL,
  `training_id` varchar(50) NOT NULL,
  `training_samples` int(11) DEFAULT 0,
  `training_time` int(11) DEFAULT 0,
  `accuracy_before` decimal(5,4) DEFAULT 0.0000,
  `accuracy_after` decimal(5,4) DEFAULT 0.0000,
  `precision_before` decimal(5,4) DEFAULT 0.0000,
  `precision_after` decimal(5,4) DEFAULT 0.0000,
  `recall_before` decimal(5,4) DEFAULT 0.0000,
  `recall_after` decimal(5,4) DEFAULT 0.0000,
  `f1_score_before` decimal(5,4) DEFAULT 0.0000,
  `f1_score_after` decimal(5,4) DEFAULT 0.0000,
  `training_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`training_params`)),
  `status` enum('started','completed','failed','cancelled') DEFAULT 'started',
  `error_message` text DEFAULT NULL,
  `started_by` int(11) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id_audit_log` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_history`
--

CREATE TABLE `backup_history` (
  `id` int(11) NOT NULL,
  `backup_type` enum('full','database','files','settings') NOT NULL DEFAULT 'full',
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) DEFAULT 0,
  `status` enum('pending','in_progress','completed','failed') NOT NULL DEFAULT 'completed',
  `error_message` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
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
  `auto_refresh_enabled` tinyint(1) DEFAULT 1,
  `address_detail` text DEFAULT NULL COMMENT 'Alamat jalan lengkap (manual input)',
  `postal_code` varchar(10) DEFAULT NULL COMMENT 'Kode pos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id_branch`, `company_id`, `branch_name`, `branch_code`, `branch_type`, `owner_name`, `phone`, `email`, `address_id`, `location_id`, `province_id`, `regency_id`, `district_id`, `village_id`, `operation_hours`, `is_active`, `created_at`, `updated_at`, `last_sync_at`, `sync_status`, `real_time_status`, `last_ping_at`, `auto_refresh_enabled`, `address_detail`, `postal_code`) VALUES
(1, 1, 'Toko Cabang A', 'TSB001-A', 'toko', 'Budi Santoso', '021-2345-6789', 'cabanga@tokosejahtera.com', NULL, NULL, NULL, NULL, NULL, NULL, '{\"monday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"tuesday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"wednesday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"thursday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"friday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"saturday\":{\"open\":\"08:00\",\"close\":\"21:00\"},\"sunday\":{\"open\":\"09:00\",\"close\":\"20:00\"}}', 1, '2026-01-21 19:39:30', '2026-01-21 19:39:30', NULL, 'synced', 'offline', NULL, 1, NULL, NULL),
(2, 1, 'Toko Cabang B', 'TSB001-B', 'warung', 'Siti Nurhaliza', '021-3456-7890', 'cabangb@tokosejahtera.com', NULL, NULL, NULL, NULL, NULL, NULL, '{\"monday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"tuesday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"wednesday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"thursday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"friday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"saturday\":{\"open\":\"07:00\",\"close\":\"22:00\"},\"sunday\":{\"open\":\"08:00\",\"close\":\"21:00\"}}', 1, '2026-01-21 19:39:30', '2026-01-21 19:39:30', NULL, 'synced', 'offline', NULL, 1, NULL, NULL),
(3, 1, 'Toko Sejahtera Pusat', 'TS001-01', '', 'Budi Santoso', '08123456789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', 'offline', NULL, 1, NULL, NULL),
(4, 1, 'Toko Sejahtera Cabang 1', 'TS001-02', '', 'Budi Santoso', '08123456789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', 'offline', NULL, 1, NULL, NULL),
(5, 2, 'Minimarket Makmur Utama', 'MM001-01', '', 'Siti Nurhaliza', '08234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', 'offline', NULL, 1, NULL, NULL),
(6, 3, 'Distributor Utama Gudang', 'DU001-01', '', 'Ahmad Fauzi', '08345678901', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', 'offline', NULL, 1, NULL, NULL);

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
  `auto_refresh_interval` int(11) DEFAULT 30,
  `address_detail` text DEFAULT NULL COMMENT 'Alamat jalan lengkap (manual input)',
  `postal_code` varchar(10) DEFAULT NULL COMMENT 'Kode pos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id_company`, `company_name`, `company_code`, `company_type`, `scalability_level`, `business_category`, `owner_name`, `phone`, `email`, `address_id`, `address`, `province_id`, `regency_id`, `district_id`, `village_id`, `tax_id`, `business_license`, `is_active`, `created_at`, `updated_at`, `last_sync_at`, `sync_status`, `api_access_key`, `webhook_url`, `auto_refresh_interval`, `address_detail`, `postal_code`) VALUES
(1, 'Toko Sejahtera Bersama', 'TSB001', 'individual', '2', 'retail', 'Ahmad Wijaya', '021-1234-5678', 'info@tokosejahtera.com', NULL, 'Jakarta Pusat', 12, 158, 1959, 25526, NULL, NULL, 0, '2026-01-21 19:39:30', '2026-01-22 16:56:32', NULL, 'synced', NULL, NULL, 30, NULL, NULL),
(2, 'Toko Sejahtera', 'TS001', 'pusat', '1', 'retail', 'Budi Santoso', '08123456789', 'budi@tokosejahtera.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', NULL, NULL, 30, NULL, NULL),
(3, 'Minimarket Makmur', 'MM001', 'pusat', '1', 'retail', 'Siti Nurhaliza', '08234567890', 'siti@minimakmur.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', NULL, NULL, 30, NULL, NULL),
(4, 'Distributor Utama', 'DU001', 'pusat', '1', 'retail', 'Ahmad Fauzi', '08345678901', 'ahmad@distributorutama.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 14:13:06', '2026-01-22 14:13:06', NULL, 'synced', NULL, NULL, 30, NULL, NULL),
(8, 'Test Company 2026-01-22 16:27:37', 'TEST1769095657', 'individual', '1', 'retail', 'Test Owner', '08123456789', 'test@example.com', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 15:27:37', '2026-01-22 15:27:37', NULL, 'synced', NULL, NULL, 30, NULL, NULL),
(9, 'Test Company 2026-01-22 16:27:55', 'TEST1769095675', 'individual', '1', 'retail', 'Test Owner', '08123456789', 'test@example.com', 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 15:27:55', '2026-01-22 15:27:55', NULL, 'synced', NULL, NULL, 30, NULL, NULL),
(10, 'Test Company 2026-01-22 16:30:03', 'TEST1769095803', 'individual', '1', 'retail', 'Test Owner', '08123456789', 'test@example.com', 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 15:30:03', '2026-01-22 15:30:03', NULL, 'synced', NULL, NULL, 30, NULL, NULL),
(11, 'Test Company 2026-01-22 16:30:26', 'TEST1769095826', 'individual', '1', 'retail', 'Test Owner', '08123456789', 'test@example.com', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-22 15:30:26', '2026-01-22 15:30:26', NULL, 'synced', NULL, NULL, 30, NULL, NULL);

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

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id_setting`, `company_id`, `module_id`, `setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'feature_test_feature', '{\"enabled\":true,\"settings\":{\"test\":\"value\"}}', 'json', '2026-01-25 06:53:42', '2026-01-25 06:53:42'),
(2, 1, NULL, 'feature_products', '{\"enabled\":true,\"settings\":[]}', 'json', '2026-01-25 06:53:42', '2026-01-25 06:54:22'),
(3, 1, NULL, 'feature_transactions', '{\"enabled\":true,\"settings\":[]}', 'json', '2026-01-25 06:53:42', '2026-01-25 06:54:22'),
(4, 1, NULL, 'feature_reports', '{\"enabled\":false,\"settings\":[]}', 'json', '2026-01-25 06:53:42', '2026-01-25 06:54:22'),
(5, 1, NULL, 'feature_chart_of_accounts', '{\"enabled\":false,\"settings\":[]}', 'json', '2026-01-25 06:53:42', '2026-01-25 06:54:22'),
(13, 1, NULL, 'feature_suppliers', '{\"enabled\":true,\"settings\":[]}', 'json', '2026-01-25 06:54:22', '2026-01-25 06:54:22'),
(14, 1, NULL, 'feature_customers', '{\"enabled\":true,\"settings\":[]}', 'json', '2026-01-25 06:54:22', '2026-01-25 06:54:22'),
(16, 1, NULL, 'feature_product_transfers', '{\"enabled\":false,\"settings\":[]}', 'json', '2026-01-25 06:54:22', '2026-01-25 06:54:22'),
(18, 1, NULL, 'feature_journal_entries', '{\"enabled\":false,\"settings\":[]}', 'json', '2026-01-25 06:54:22', '2026-01-25 06:54:22'),
(65, 1, NULL, 'feature_financial_reports', '{\"enabled\":true,\"settings\":[]}', 'json', '2026-01-25 06:54:22', '2026-01-25 06:54:22');

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
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id_address` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `address_type` enum('billing','shipping','both') DEFAULT 'both',
  `address_detail` text NOT NULL,
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_contacts`
--

CREATE TABLE `customer_contacts` (
  `id_contact` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `contact_name` varchar(200) NOT NULL,
  `contact_position` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_feedback`
--

CREATE TABLE `customer_feedback` (
  `id_feedback` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `feedback_type` enum('review','complaint','suggestion','compliment') NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `response` text DEFAULT NULL,
  `status` enum('pending','responded','resolved','closed') DEFAULT 'pending',
  `feedback_date` datetime NOT NULL,
  `responded_by` int(11) DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_groups`
--

CREATE TABLE `customer_groups` (
  `id_group` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_description` text DEFAULT NULL,
  `group_type` enum('demographic','behavioral','geographic','psychographic','custom') DEFAULT 'custom',
  `color_code` varchar(7) DEFAULT '#007bff',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_group_memberships`
--

CREATE TABLE `customer_group_memberships` (
  `id_membership` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_interactions`
--

CREATE TABLE `customer_interactions` (
  `id_interaction` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `interaction_type` enum('phone_call','email','whatsapp','sms','visit','complaint','inquiry','support') NOT NULL,
  `interaction_date` datetime NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `outcome` enum('successful','pending','failed','requires_follow_up') DEFAULT 'pending',
  `follow_up_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_tags`
--

CREATE TABLE `customer_tags` (
  `id_tag` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL,
  `tag_color` varchar(7) DEFAULT '#6c757d',
  `tag_description` text DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_tag_assignments`
--

CREATE TABLE `customer_tag_assignments` (
  `id_assignment` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_content` text DEFAULT NULL,
  `text_content` text DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_name`, `subject`, `html_content`, `text_content`, `variables`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'welcome_email', 'Welcome to Perdagangan System', '<h1>Welcome {{user_name}}!</h1><p>Your account has been created successfully.</p><p>Username: {{username}}</p><p>Password: {{password}}</p><p>Login URL: {{login_url}}</p>', 'Welcome {{user_name}}! Your account has been created successfully.\\n\\nUsername: {{username}}\\nPassword: {{password}}\\nLogin URL: {{login_url}}', '[\"user_name\", \"username\", \"password\", \"login_url\"]', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(2, 'password_reset', 'Password Reset Request', '<h1>Password Reset</h1><p>Hi {{user_name}},</p><p>Click here to reset your password: {{reset_link}}</p><p>This link will expire in {{expiry_hours}} hours.</p>', 'Hi {{user_name}},\\n\\nClick here to reset your password: {{reset_link}}\\n\\nThis link will expire in {{expiry_hours}} hours.', '[\"user_name\", \"reset_link\", \"expiry_hours\"]', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(3, 'backup_notification', 'Backup Completed', '<h1>System Backup Completed</h1><p>System backup has been completed successfully.</p><p>File: {{backup_file}}</p><p>Size: {{backup_size}}</p><p>Date: {{backup_date}}</p>', 'System backup has been completed successfully.\\n\\nFile: {{backup_file}}\\nSize: {{backup_size}}\\nDate: {{backup_date}}', '[\"backup_file\", \"backup_size\", \"backup_date\"]', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50');

-- --------------------------------------------------------

--
-- Table structure for table `feature_toggles`
--

CREATE TABLE `feature_toggles` (
  `id` int(11) NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `feature_group` varchar(50) NOT NULL DEFAULT 'general',
  `is_enabled` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `requires_restart` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feature_toggles`
--

INSERT INTO `feature_toggles` (`id`, `feature_name`, `feature_group`, `is_enabled`, `description`, `requires_restart`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'reports', 'business', 1, 'Enable reporting system', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(2, 'notifications', 'system', 1, 'Enable system notifications', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(3, 'backup', 'system', 1, 'Enable backup system', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(4, 'audit_log', 'system', 1, 'Enable audit logging', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(5, 'api_access', 'system', 0, 'Enable API access', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(6, 'maintenance_mode', 'system', 0, 'Enable maintenance mode', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(7, 'registration', 'user', 0, 'Enable user registration', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(8, 'email_verification', 'user', 0, 'Enable email verification', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(9, 'password_reset', 'user', 1, 'Enable password reset', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(10, 'multi_company', 'business', 1, 'Enable multi-company support', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(11, 'multi_branch', 'business', 1, 'Enable multi-branch support', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(12, 'advanced_reports', 'business', 1, 'Enable advanced reporting', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(13, 'financial_reports', 'business', 1, 'Enable financial reports', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(14, 'export_functionality', 'business', 1, 'Enable export functionality', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(15, 'import_functionality', 'business', 1, 'Enable import functionality', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(16, 'real_time_updates', 'system', 1, 'Enable real-time updates', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(17, 'mobile_app', 'system', 0, 'Enable mobile app', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(18, 'websocket', 'system', 0, 'Enable WebSocket', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(19, 'file_management', 'system', 1, 'Enable file management', 0, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id_file` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL COMMENT 'Generated unique filename',
  `original_name` varchar(255) NOT NULL COMMENT 'Original uploaded filename',
  `file_path` varchar(500) NOT NULL COMMENT 'Full path to file',
  `file_size` bigint(20) NOT NULL DEFAULT 0 COMMENT 'File size in bytes',
  `mime_type` varchar(100) NOT NULL COMMENT 'MIME type of file',
  `file_extension` varchar(10) NOT NULL COMMENT 'File extension',
  `file_category` varchar(50) NOT NULL DEFAULT 'general' COMMENT 'File category',
  `description` text DEFAULT NULL COMMENT 'File description',
  `tags` varchar(500) DEFAULT NULL COMMENT 'Comma-separated tags',
  `uploaded_by` int(11) DEFAULT NULL COMMENT 'User who uploaded the file',
  `company_id` int(11) DEFAULT NULL COMMENT 'Company ID (if applicable)',
  `branch_id` int(11) DEFAULT NULL COMMENT 'Branch ID (if applicable)',
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether file is public',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether file is active',
  `download_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of downloads',
  `last_accessed` datetime DEFAULT NULL COMMENT 'Last access timestamp',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File management table';

--
-- Triggers `files`
--
DELIMITER $$
CREATE TRIGGER `log_file_download` AFTER UPDATE ON `files` FOR EACH ROW BEGIN
    IF NEW.download_count > OLD.download_count THEN
        INSERT INTO `file_access_log` (`file_id`, `access_type`, `ip_address`, `user_agent`)
        VALUES (NEW.id_file, 'download', NULL, NULL);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `file_access_log`
--

CREATE TABLE `file_access_log` (
  `id_log` int(11) NOT NULL,
  `file_id` int(11) NOT NULL COMMENT 'Accessed file',
  `user_id` int(11) DEFAULT NULL COMMENT 'User who accessed (null for anonymous)',
  `access_type` enum('view','download','edit','share','delete') NOT NULL DEFAULT 'view' COMMENT 'Type of access',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of access',
  `user_agent` text DEFAULT NULL COMMENT 'User agent string',
  `share_token` varchar(100) DEFAULT NULL COMMENT 'Share token if accessed via share',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File access log table';

-- --------------------------------------------------------

--
-- Table structure for table `file_categories`
--

CREATE TABLE `file_categories` (
  `id_category` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL COMMENT 'Category name',
  `category_description` text DEFAULT NULL COMMENT 'Category description',
  `allowed_extensions` varchar(255) DEFAULT NULL COMMENT 'Comma-separated allowed extensions',
  `max_file_size` bigint(20) DEFAULT NULL COMMENT 'Maximum file size in bytes (null for default)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether category is active',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Sort order',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File categories configuration';

--
-- Dumping data for table `file_categories`
--

INSERT INTO `file_categories` (`id_category`, `category_name`, `category_description`, `allowed_extensions`, `max_file_size`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'general', 'General files and documents', 'pdf,doc,docx,txt,csv', 10485760, 1, 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(2, 'images', 'Image files and graphics', 'jpg,jpeg,png,gif', 5242880, 1, 2, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(3, 'documents', 'Business documents and reports', 'pdf,doc,doc,xls,xlsx,ppt,pptx', 10485760, 1, 3, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(4, 'media', 'Audio and video files', 'mp3,mp4,avi,mov,wmv', 52428800, 1, 4, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(5, 'archives', 'Compressed archives', 'zip,rar,7z,tar,gz', 52428800, 1, 5, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(6, 'templates', 'Document templates', 'doc,docx,xls,xlsx,ppt,pptx,pdf', 10485760, 1, 6, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(7, 'exports', 'System export files', 'csv,xlsx,json,xml', 20971520, 1, 7, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(8, 'backups', 'System backup files', 'zip,sql,gz', 104857600, 1, 8, '2026-01-25 08:11:17', '2026-01-25 08:11:17');

-- --------------------------------------------------------

--
-- Table structure for table `file_settings`
--

CREATE TABLE `file_settings` (
  `id_setting` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL COMMENT 'Setting key',
  `setting_value` text DEFAULT NULL COMMENT 'Setting value',
  `setting_description` text DEFAULT NULL COMMENT 'Setting description',
  `setting_type` enum('string','integer','boolean','json') NOT NULL DEFAULT 'string' COMMENT 'Setting data type',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether setting is active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File management system settings';

--
-- Dumping data for table `file_settings`
--

INSERT INTO `file_settings` (`id_setting`, `setting_key`, `setting_value`, `setting_description`, `setting_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'max_file_size', '10485760', 'Maximum file size in bytes (10MB default)', 'integer', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(2, 'max_image_size', '5242880', 'Maximum image file size in bytes (5MB default)', 'integer', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(3, 'allowed_extensions', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,csv,zip,rar', 'Comma-separated list of allowed extensions', 'string', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(4, 'enable_sharing', '1', 'Enable file sharing functionality', 'boolean', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(5, 'enable_public_uploads', '0', 'Allow public file uploads', 'boolean', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(6, 'auto_cleanup_days', '365', 'Automatically delete files older than X days', 'integer', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(7, 'enable_version_control', '1', 'Enable file version control', 'boolean', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(8, 'thumbnail_sizes', '{\"thumbnail\":{\"width\":150,\"height\":150},\"medium\":{\"width\":800,\"height\":600},\"large\":{\"width\":1200,\"height\":900}}', 'Thumbnail sizes configuration', 'json', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(9, 'enable_access_logging', '1', 'Enable file access logging', 'boolean', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17'),
(10, 'max_concurrent_uploads', '5', 'Maximum concurrent uploads per user', 'integer', 1, '2026-01-25 08:11:17', '2026-01-25 08:11:17');

-- --------------------------------------------------------

--
-- Table structure for table `file_shares`
--

CREATE TABLE `file_shares` (
  `id_share` int(11) NOT NULL,
  `file_id` int(11) NOT NULL COMMENT 'Shared file',
  `shared_by` int(11) NOT NULL COMMENT 'User who shared the file',
  `shared_with` int(11) DEFAULT NULL COMMENT 'User who received access (null for public)',
  `share_type` enum('link','user','company','branch','public') NOT NULL DEFAULT 'link' COMMENT 'Type of sharing',
  `share_token` varchar(100) DEFAULT NULL COMMENT 'Unique share token for link sharing',
  `expires_at` datetime DEFAULT NULL COMMENT 'Share expiration date',
  `can_download` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether download is allowed',
  `can_edit` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether editing is allowed',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether share is active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File sharing table';

-- --------------------------------------------------------

--
-- Stand-in structure for view `file_statistics_view`
-- (See below for the actual view)
--
CREATE TABLE `file_statistics_view` (
`file_category` varchar(50)
,`total_files` bigint(21)
,`total_size` decimal(41,0)
,`avg_size` decimal(23,4)
,`min_size` bigint(20)
,`max_size` bigint(20)
,`total_downloads` decimal(32,0)
,`unique_uploaders` bigint(21)
,`upload_date` date
);

-- --------------------------------------------------------

--
-- Table structure for table `file_versions`
--

CREATE TABLE `file_versions` (
  `id_version` int(11) NOT NULL,
  `file_id` int(11) NOT NULL COMMENT 'Reference to original file',
  `file_type` varchar(50) NOT NULL COMMENT 'Type of version (thumbnail, medium, large, etc.)',
  `file_path` varchar(500) NOT NULL COMMENT 'Path to version file',
  `file_size` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Version file size in bytes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File versions for thumbnails and processed files';

-- --------------------------------------------------------

--
-- Table structure for table `financial_reports`
--

CREATE TABLE `financial_reports` (
  `id_financial_report` int(11) NOT NULL,
  `report_type` enum('income_statement','balance_sheet','cash_flow','trial_balance') NOT NULL,
  `report_period` enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
  `report_date` date NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `generated_by` int(11) NOT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `file_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id_inventory` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 0,
  `max_stock` int(11) DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `total_value` decimal(12,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `loyalty_transactions`
--

CREATE TABLE `loyalty_transactions` (
  `id_transaction` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `transaction_type` enum('earned','redeemed','expired','adjusted') NOT NULL,
  `points` int(11) NOT NULL,
  `reference_type` enum('purchase','redemption','manual_adjustment','bonus') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  `expires_at` date DEFAULT NULL,
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
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `push_notifications_enabled` tinyint(1) DEFAULT 1,
  `username` varchar(50) GENERATED ALWAYS AS (`member_code`) VIRTUAL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id_member`, `branch_id`, `member_code`, `member_name`, `position`, `phone`, `email`, `password_hash`, `salary`, `join_date`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`, `last_login_at`, `last_activity_at`, `session_id`, `is_online`, `push_notifications_enabled`) VALUES
(1, 1, 'ADMIN', 'Administrator', 'owner', '08123456789', 'admin@dagang.com', '$2y$10$nscbO82wYaIPTLR7ZJQy/u6fA4tHzleg9ecLTBqFYA4yKERxVuloS', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-25 16:57:23', NULL, NULL, '2026-01-25 16:57:23', NULL, NULL, 0, 1),
(2, 1, 'MBR001', 'Budi Santoso', 'owner', '08123456789', 'budi@tokosejahtera.com', '\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-22 14:13:41', NULL, NULL, NULL, NULL, NULL, 0, 1),
(3, 1, 'MBR002', 'Andi Wijaya', 'cashier', '08123456788', 'andi@tokosejahtera.com', '\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-22 14:13:41', NULL, NULL, NULL, NULL, NULL, 0, 1),
(4, 2, 'MBR003', 'Cahaya Putri', 'manager', '08123456787', 'cahaya@tokosejahtera.com', '\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-22 14:13:41', NULL, NULL, NULL, NULL, NULL, 0, 1),
(5, 3, 'MBR004', 'Siti Nurhaliza', 'owner', '08234567890', 'siti@minimakmur.com', '\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 1, '2026-01-22 14:13:41', '2026-01-22 14:13:41', NULL, NULL, NULL, NULL, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `migration_history`
--

CREATE TABLE `migration_history` (
  `id` int(11) NOT NULL,
  `migration_name` varchar(255) NOT NULL,
  `version` varchar(50) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migration_history`
--

INSERT INTO `migration_history` (`id`, `migration_name`, `version`, `executed_at`) VALUES
(1, 'create_inventory_tables.sql', '1.0', '2026-01-25 13:25:16'),
(2, 'create_transaction_tables.sql', '1.0', '2026-01-25 13:25:16'),
(3, 'create_advanced_reports_tables.sql', '1.0', '2026-01-25 13:25:17'),
(4, 'create_missing_tables.sql', '1.0', '2026-01-25 13:25:17');

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
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id_notification` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `user_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id_preference` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `push_notifications` tinyint(1) DEFAULT 0,
  `quiet_hours_enabled` tinyint(1) DEFAULT 0,
  `quiet_hours_start` time DEFAULT '22:00:00',
  `quiet_hours_end` time DEFAULT '08:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id_setting` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id_template` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL COMMENT 'Template name',
  `template_type` enum('email','sms','push','in_app') NOT NULL COMMENT 'Notification type',
  `subject` varchar(255) DEFAULT NULL COMMENT 'Email subject',
  `message_body` text NOT NULL COMMENT 'Message template',
  `html_body` text DEFAULT NULL COMMENT 'HTML email template',
  `variables` text DEFAULT NULL COMMENT 'Available variables in JSON',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether template is active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notification templates for different types';

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id_template`, `template_name`, `template_type`, `subject`, `message_body`, `html_body`, `variables`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'user_welcome', 'email', 'Selamat Datang di Perdagangan System', 'Hai {user_name},\n\nSelamat datang di Perdagangan System! Akun Anda telah berhasil dibuat.\n\nEmail: {user_email}\nPeran: {user_role}\nPerusahaan: {company_name}\n\nSilakan login untuk mulai menggunakan sistem.', NULL, '{\"user_name\",\"user_email\",\"user_role\",\"company_name\"}', 1, '2026-01-25 13:25:13', '2026-01-25 13:25:13'),
(2, 'file_uploaded', 'in_app', 'File Berhasil Diunggah', '{user_name} telah mengunggah file \"{file_name}\" ({file_size})', NULL, '{\"user_name\",\"file_name\",\"file_size\"}', 1, '2026-01-25 13:25:13', '2026-01-25 13:25:13'),
(3, 'file_downloaded', 'in_app', 'File Diunduh', '{user_name} telah mengunduh file \"{file_name}\"', NULL, '{\"user_name\",\"file_name\"}', 1, '2026-01-25 13:25:13', '2026-01-25 13:25:13'),
(4, 'transaction_created', 'in_app', 'Transaksi Baru', 'Transaksi baru telah dibuat dengan total Rp {transaction_total}', NULL, '{\"transaction_total\"}', 1, '2026-01-25 13:25:13', '2026-01-25 13:25:13'),
(5, 'low_stock_alert', 'email', 'Peringatan Stok Menipis', 'Produk \"{product_name}\" stok menipis! Sisa stok: {current_stock} {unit}', NULL, '{\"product_name\",\"current_stock\",\"unit\"}', 1, '2026-01-25 13:25:13', '2026-01-25 13:25:13'),
(6, 'system_maintenance', 'email', 'Pemeliharaan Sistem', 'Sistem akan dalam pemeliharaan pada {maintenance_time} hingga {end_time}.', NULL, '{\"maintenance_time\",\"end_time\"}', 1, '2026-01-25 13:25:13', '2026-01-25 13:25:13');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id_permission` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_code` varchar(100) NOT NULL,
  `permission_group` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system_permission` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id_permission`, `permission_name`, `permission_code`, `permission_group`, `description`, `is_system_permission`, `created_at`) VALUES
(1, 'View Companies', 'companies.view', 'companies', 'View company information', 1, '2026-01-25 09:12:15'),
(2, 'Create Companies', 'companies.create', 'companies', 'Create new companies', 1, '2026-01-25 09:12:15'),
(3, 'Update Companies', 'companies.update', 'companies', 'Update company information', 1, '2026-01-25 09:12:15'),
(4, 'Delete Companies', 'companies.delete', 'companies', 'Delete companies', 1, '2026-01-25 09:12:15'),
(5, 'Manage All Companies', 'companies.view_all', 'companies', 'View all companies regardless of ownership', 1, '2026-01-25 09:12:15'),
(6, 'View Branches', 'branches.view', 'branches', 'View branch information', 1, '2026-01-25 09:12:15'),
(7, 'Create Branches', 'branches.create', 'branches', 'Create new branches', 1, '2026-01-25 09:12:15'),
(8, 'Update Branches', 'branches.update', 'branches', 'Update branch information', 1, '2026-01-25 09:12:15'),
(9, 'Delete Branches', 'branches.delete', 'branches', 'Delete branches', 1, '2026-01-25 09:12:15'),
(10, 'Manage All Branches', 'branches.view_all', 'branches', 'View all branches regardless of ownership', 1, '2026-01-25 09:12:15'),
(11, 'View Own Branch', 'branches.view_own', 'branches', 'View own branch only', 1, '2026-01-25 09:12:15'),
(12, 'View Users', 'users.view', 'users', 'View user information', 1, '2026-01-25 09:12:15'),
(13, 'Create Users', 'users.create', 'users', 'Create new users', 1, '2026-01-25 09:12:15'),
(14, 'Update Users', 'users.update', 'users', 'Update user information', 1, '2026-01-25 09:12:15'),
(15, 'Delete Users', 'users.delete', 'users', 'Delete users', 1, '2026-01-25 09:12:15'),
(16, 'Manage Roles', 'users.manage_roles', 'users', 'Assign and manage user roles', 1, '2026-01-25 09:12:15'),
(17, 'Manage Permissions', 'users.manage_permissions', 'users', 'Manage role permissions', 1, '2026-01-25 09:12:15'),
(18, 'View All Users', 'users.view_all', 'users', 'View all users regardless of branch', 1, '2026-01-25 09:12:15'),
(19, 'Import Users', 'users.import', 'users', 'Import users from file', 1, '2026-01-25 09:12:15'),
(20, 'Export Users', 'users.export', 'users', 'Export users to file', 1, '2026-01-25 09:12:15'),
(21, 'View Products', 'products.view', 'products', 'View product information', 1, '2026-01-25 09:12:15'),
(22, 'Create Products', 'products.create', 'products', 'Create new products', 1, '2026-01-25 09:12:15'),
(23, 'Update Products', 'products.update', 'products', 'Update product information', 1, '2026-01-25 09:12:15'),
(24, 'Delete Products', 'products.delete', 'products', 'Delete products', 1, '2026-01-25 09:12:15'),
(25, 'Manage Inventory', 'products.manage_inventory', 'products', 'Manage product inventory', 1, '2026-01-25 09:12:15'),
(26, 'View Transactions', 'transactions.view', 'transactions', 'View transaction information', 1, '2026-01-25 09:12:15'),
(27, 'Create Transactions', 'transactions.create', 'transactions', 'Create new transactions', 1, '2026-01-25 09:12:15'),
(28, 'Update Transactions', 'transactions.update', 'transactions', 'Update transaction information', 1, '2026-01-25 09:12:15'),
(29, 'Delete Transactions', 'transactions.delete', 'transactions', 'Delete transactions', 1, '2026-01-25 09:12:15'),
(30, 'View All Transactions', 'transactions.view_all', 'transactions', 'View all transactions regardless of branch', 1, '2026-01-25 09:12:15'),
(31, 'View Own Transactions', 'transactions.view_own', 'transactions', 'View own branch transactions only', 1, '2026-01-25 09:12:15'),
(32, 'View Customers', 'customers.view', 'customers', 'View customer information', 1, '2026-01-25 09:12:15'),
(33, 'Create Customers', 'customers.create', 'customers', 'Create new customers', 1, '2026-01-25 09:12:15'),
(34, 'Update Customers', 'customers.update', 'customers', 'Update customer information', 1, '2026-01-25 09:12:15'),
(35, 'Delete Customers', 'customers.delete', 'customers', 'Delete customers', 1, '2026-01-25 09:12:15'),
(36, 'Manage Customer Credit', 'customers.manage_credit', 'customers', 'Manage customer credit limits', 1, '2026-01-25 09:12:15'),
(37, 'View Suppliers', 'suppliers.view', 'suppliers', 'View supplier information', 1, '2026-01-25 09:12:15'),
(38, 'Create Suppliers', 'suppliers.create', 'suppliers', 'Create new suppliers', 1, '2026-01-25 09:12:15'),
(39, 'Update Suppliers', 'suppliers.update', 'suppliers', 'Update supplier information', 1, '2026-01-25 09:12:15'),
(40, 'Delete Suppliers', 'suppliers.delete', 'suppliers', 'Delete suppliers', 1, '2026-01-25 09:12:15'),
(41, 'Manage Purchase Orders', 'suppliers.manage_po', 'suppliers', 'Create and manage purchase orders', 1, '2026-01-25 09:12:15'),
(42, 'View Basic Reports', 'reports.view_basic', 'reports', 'View basic reports', 1, '2026-01-25 09:12:15'),
(43, 'View Advanced Reports', 'reports.view_advanced', 'reports', 'View advanced reports', 1, '2026-01-25 09:12:15'),
(44, 'View Financial Reports', 'reports.view_financial', 'reports', 'View financial reports', 1, '2026-01-25 09:12:15'),
(45, 'Export Reports', 'reports.export', 'reports', 'Export reports to file', 1, '2026-01-25 09:12:15'),
(46, 'View Settings', 'settings.view', 'settings', 'View system settings', 1, '2026-01-25 09:12:15'),
(47, 'Update Basic Settings', 'settings.update_basic', 'settings', 'Update basic settings', 1, '2026-01-25 09:12:15'),
(48, 'Update Advanced Settings', 'settings.update_advanced', 'settings', 'Update advanced settings', 1, '2026-01-25 09:12:15'),
(49, 'System Configuration', 'settings.system_config', 'settings', 'System-level configuration', 1, '2026-01-25 09:12:15'),
(50, 'View Audit Logs', 'system.view_audit', 'system', 'View audit logs', 1, '2026-01-25 09:12:15'),
(51, 'Manage System', 'system.manage', 'system', 'System administration', 1, '2026-01-25 09:12:15'),
(52, 'View System Info', 'system.view_info', 'system', 'View system information', 1, '2026-01-25 09:12:15');

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
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id_po` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `status` enum('draft','sent','confirmed','partial_received','received','cancelled','closed') DEFAULT 'draft',
  `subtotal` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `shipping_cost` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_terms` enum('cod','7_days','14_days','30_days','45_days','60_days','90_days') DEFAULT '30_days',
  `payment_status` enum('unpaid','partial','paid','overdue') DEFAULT 'unpaid',
  `due_date` date DEFAULT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `delivery_address` text DEFAULT NULL,
  `delivery_contact_person` varchar(200) DEFAULT NULL,
  `delivery_phone` varchar(50) DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id_po_item` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_product_id` int(11) DEFAULT NULL,
  `product_code` varchar(100) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity_ordered` decimal(10,2) NOT NULL,
  `quantity_received` decimal(10,2) DEFAULT 0.00,
  `unit_price` decimal(15,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('ordered','partial_received','received','cancelled') DEFAULT 'ordered',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_analytics`
--

CREATE TABLE `report_analytics` (
  `id` int(11) NOT NULL,
  `report_id` varchar(50) NOT NULL,
  `analytics_type` varchar(50) NOT NULL,
  `analytics_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`analytics_data`)),
  `insights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`insights`)),
  `confidence_score` decimal(5,4) DEFAULT 0.0000,
  `data_points` int(11) DEFAULT 0,
  `anomalies_detected` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_history`
--

CREATE TABLE `report_history` (
  `id` int(11) NOT NULL,
  `report_template_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_format` enum('pdf','excel','csv') NOT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `file_size` int(11) DEFAULT NULL,
  `generated_at` datetime NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_permissions`
--

CREATE TABLE `report_permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_group` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_permissions`
--

INSERT INTO `report_permissions` (`id`, `permission_name`, `permission_group`, `description`, `is_active`, `created_at`) VALUES
(1, 'view_basic', 'reports', 'View basic reports', 1, '2026-01-25 13:25:17'),
(2, 'view_advanced', 'reports', 'View advanced reports', 1, '2026-01-25 13:25:17'),
(3, 'view_financial', 'reports', 'View financial reports', 1, '2026-01-25 13:25:17'),
(4, 'export', 'reports', 'Export reports', 1, '2026-01-25 13:25:17'),
(5, 'create', 'reports', 'Create custom reports', 1, '2026-01-25 13:25:17'),
(6, 'manage_templates', 'reports', 'Manage report templates', 1, '2026-01-25 13:25:17'),
(7, 'schedule', 'reports', 'Schedule automated reports', 1, '2026-01-25 13:25:17'),
(8, 'delete', 'reports', 'Delete reports', 1, '2026-01-25 13:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `report_predictions`
--

CREATE TABLE `report_predictions` (
  `id` int(11) NOT NULL,
  `report_id` varchar(50) NOT NULL,
  `prediction_type` varchar(50) NOT NULL,
  `prediction_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`prediction_data`)),
  `confidence_score` decimal(5,4) DEFAULT 0.0000,
  `accuracy_score` decimal(5,4) DEFAULT 0.0000,
  `prediction_period` varchar(20) NOT NULL,
  `model_used` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_recommendations`
--

CREATE TABLE `report_recommendations` (
  `id` int(11) NOT NULL,
  `report_id` varchar(50) NOT NULL,
  `recommendation_type` varchar(50) NOT NULL,
  `recommendation_text` text NOT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `confidence_score` decimal(5,4) DEFAULT 0.0000,
  `actionable` tinyint(1) DEFAULT 1,
  `implemented` tinyint(1) DEFAULT 0,
  `implemented_by` int(11) DEFAULT NULL,
  `implemented_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_schedules`
--

CREATE TABLE `report_schedules` (
  `id` int(11) NOT NULL,
  `report_template_id` int(11) NOT NULL,
  `schedule_type` enum('daily','weekly','monthly','yearly') NOT NULL,
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`recipients`)),
  `next_run` datetime NOT NULL,
  `last_run` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_templates`
--

CREATE TABLE `report_templates` (
  `id` int(11) NOT NULL,
  `template_id` varchar(50) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_description` text DEFAULT NULL,
  `report_type` varchar(50) NOT NULL,
  `template_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`template_config`)),
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `usage_count` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_templates`
--

INSERT INTO `report_templates` (`id`, `template_id`, `template_name`, `template_description`, `report_type`, `template_config`, `is_default`, `is_active`, `usage_count`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'sales_daily', 'Daily Sales Report', 'Comprehensive daily sales analysis with trends and insights', 'sales', '{\"date_range\": \"1d\", \"include_forecasts\": true, \"include_charts\": true, \"ai_model\": \"sales_forecasting\"}', 1, 1, 0, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(2, 'sales_weekly', 'Weekly Sales Report', 'Weekly sales performance with comparative analysis', 'sales', '{\"date_range\": \"7d\", \"include_forecasts\": true, \"include_charts\": true, \"ai_model\": \"sales_forecasting\"}', 1, 1, 0, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(3, 'sales_monthly', 'Monthly Sales Report', 'Monthly sales analysis with detailed breakdowns', 'sales', '{\"date_range\": \"30d\", \"include_forecasts\": true, \"include_charts\": true, \"ai_model\": \"sales_forecasting\"}', 1, 1, 0, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(4, 'inventory_status', 'Inventory Status Report', 'Current inventory levels and stock analysis', 'inventory', '{\"date_range\": \"7d\", \"include_predictions\": true, \"include_alerts\": true, \"ai_model\": \"inventory_optimization\"}', 1, 1, 0, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(5, 'customer_analysis', 'Customer Analysis Report', 'Customer behavior and segmentation analysis', 'customer', '{\"date_range\": \"30d\", \"include_segments\": true, \"include_insights\": true, \"ai_model\": \"customer_segmentation\"}', 1, 1, 0, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(6, 'financial_summary', 'Financial Summary Report', 'Financial performance and profitability analysis', 'financial', '{\"date_range\": \"30d\", \"include_ratios\": true, \"include_trends\": true, \"ai_model\": \"financial_analysis\"}', 1, 1, 0, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `report_template_permissions`
--

CREATE TABLE `report_template_permissions` (
  `template_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 999,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id_role_permission` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id_role_permission`, `role_id`, `permission_id`, `granted_by`, `granted_at`) VALUES
(1, 2, 1, NULL, '2026-01-25 13:25:28'),
(2, 1, 1, NULL, '2026-01-25 13:25:28'),
(3, 2, 2, NULL, '2026-01-25 13:25:28'),
(4, 1, 2, NULL, '2026-01-25 13:25:28'),
(5, 2, 3, NULL, '2026-01-25 13:25:28'),
(6, 1, 3, NULL, '2026-01-25 13:25:28'),
(7, 2, 4, NULL, '2026-01-25 13:25:28'),
(8, 1, 4, NULL, '2026-01-25 13:25:28'),
(9, 2, 5, NULL, '2026-01-25 13:25:28'),
(10, 1, 5, NULL, '2026-01-25 13:25:28'),
(11, 2, 6, NULL, '2026-01-25 13:25:28'),
(12, 1, 6, NULL, '2026-01-25 13:25:28'),
(13, 2, 7, NULL, '2026-01-25 13:25:28'),
(14, 1, 7, NULL, '2026-01-25 13:25:28'),
(15, 2, 8, NULL, '2026-01-25 13:25:28'),
(16, 1, 8, NULL, '2026-01-25 13:25:28'),
(17, 2, 9, NULL, '2026-01-25 13:25:28'),
(18, 1, 9, NULL, '2026-01-25 13:25:28'),
(19, 2, 10, NULL, '2026-01-25 13:25:28'),
(20, 1, 10, NULL, '2026-01-25 13:25:28'),
(21, 2, 11, NULL, '2026-01-25 13:25:28'),
(22, 1, 11, NULL, '2026-01-25 13:25:28'),
(23, 2, 12, NULL, '2026-01-25 13:25:28'),
(24, 1, 12, NULL, '2026-01-25 13:25:28'),
(25, 2, 13, NULL, '2026-01-25 13:25:28'),
(26, 1, 13, NULL, '2026-01-25 13:25:28'),
(27, 2, 14, NULL, '2026-01-25 13:25:28'),
(28, 1, 14, NULL, '2026-01-25 13:25:28'),
(29, 2, 15, NULL, '2026-01-25 13:25:28'),
(30, 1, 15, NULL, '2026-01-25 13:25:28'),
(31, 2, 16, NULL, '2026-01-25 13:25:28'),
(32, 1, 16, NULL, '2026-01-25 13:25:28'),
(33, 2, 17, NULL, '2026-01-25 13:25:28'),
(34, 1, 17, NULL, '2026-01-25 13:25:28'),
(35, 2, 18, NULL, '2026-01-25 13:25:28'),
(36, 1, 18, NULL, '2026-01-25 13:25:28'),
(37, 2, 19, NULL, '2026-01-25 13:25:28'),
(38, 1, 19, NULL, '2026-01-25 13:25:28'),
(39, 2, 20, NULL, '2026-01-25 13:25:28'),
(40, 1, 20, NULL, '2026-01-25 13:25:28'),
(41, 2, 21, NULL, '2026-01-25 13:25:28'),
(42, 1, 21, NULL, '2026-01-25 13:25:28'),
(43, 2, 22, NULL, '2026-01-25 13:25:28'),
(44, 1, 22, NULL, '2026-01-25 13:25:28'),
(45, 2, 23, NULL, '2026-01-25 13:25:28'),
(46, 1, 23, NULL, '2026-01-25 13:25:28'),
(47, 2, 24, NULL, '2026-01-25 13:25:28'),
(48, 1, 24, NULL, '2026-01-25 13:25:28'),
(49, 2, 25, NULL, '2026-01-25 13:25:28'),
(50, 1, 25, NULL, '2026-01-25 13:25:28'),
(51, 2, 26, NULL, '2026-01-25 13:25:28'),
(52, 1, 26, NULL, '2026-01-25 13:25:28'),
(53, 2, 27, NULL, '2026-01-25 13:25:28'),
(54, 1, 27, NULL, '2026-01-25 13:25:28'),
(55, 2, 28, NULL, '2026-01-25 13:25:28'),
(56, 1, 28, NULL, '2026-01-25 13:25:28'),
(57, 2, 29, NULL, '2026-01-25 13:25:28'),
(58, 1, 29, NULL, '2026-01-25 13:25:28'),
(59, 2, 30, NULL, '2026-01-25 13:25:28'),
(60, 1, 30, NULL, '2026-01-25 13:25:28'),
(61, 2, 31, NULL, '2026-01-25 13:25:28'),
(62, 1, 31, NULL, '2026-01-25 13:25:28'),
(63, 2, 32, NULL, '2026-01-25 13:25:28'),
(64, 1, 32, NULL, '2026-01-25 13:25:28'),
(65, 2, 33, NULL, '2026-01-25 13:25:28'),
(66, 1, 33, NULL, '2026-01-25 13:25:28'),
(67, 2, 34, NULL, '2026-01-25 13:25:28'),
(68, 1, 34, NULL, '2026-01-25 13:25:28'),
(69, 2, 35, NULL, '2026-01-25 13:25:28'),
(70, 1, 35, NULL, '2026-01-25 13:25:28'),
(71, 2, 36, NULL, '2026-01-25 13:25:28'),
(72, 1, 36, NULL, '2026-01-25 13:25:28'),
(73, 2, 37, NULL, '2026-01-25 13:25:28'),
(74, 1, 37, NULL, '2026-01-25 13:25:28'),
(75, 2, 38, NULL, '2026-01-25 13:25:28'),
(76, 1, 38, NULL, '2026-01-25 13:25:28'),
(77, 2, 39, NULL, '2026-01-25 13:25:28'),
(78, 1, 39, NULL, '2026-01-25 13:25:28'),
(79, 2, 40, NULL, '2026-01-25 13:25:28'),
(80, 1, 40, NULL, '2026-01-25 13:25:28'),
(81, 2, 41, NULL, '2026-01-25 13:25:28'),
(82, 1, 41, NULL, '2026-01-25 13:25:28'),
(83, 2, 42, NULL, '2026-01-25 13:25:28'),
(84, 1, 42, NULL, '2026-01-25 13:25:28'),
(85, 2, 43, NULL, '2026-01-25 13:25:28'),
(86, 1, 43, NULL, '2026-01-25 13:25:28'),
(87, 2, 44, NULL, '2026-01-25 13:25:28'),
(88, 1, 44, NULL, '2026-01-25 13:25:28'),
(89, 2, 45, NULL, '2026-01-25 13:25:28'),
(90, 1, 45, NULL, '2026-01-25 13:25:28'),
(91, 2, 46, NULL, '2026-01-25 13:25:28'),
(92, 1, 46, NULL, '2026-01-25 13:25:28'),
(93, 2, 47, NULL, '2026-01-25 13:25:28'),
(94, 1, 47, NULL, '2026-01-25 13:25:28'),
(95, 2, 48, NULL, '2026-01-25 13:25:28'),
(96, 1, 48, NULL, '2026-01-25 13:25:28'),
(97, 2, 49, NULL, '2026-01-25 13:25:28'),
(98, 1, 49, NULL, '2026-01-25 13:25:28'),
(99, 2, 50, NULL, '2026-01-25 13:25:28'),
(100, 1, 50, NULL, '2026-01-25 13:25:28'),
(101, 2, 51, NULL, '2026-01-25 13:25:28'),
(102, 1, 51, NULL, '2026-01-25 13:25:28'),
(103, 2, 52, NULL, '2026-01-25 13:25:28'),
(104, 1, 52, NULL, '2026-01-25 13:25:28'),
(128, 8, 21, NULL, '2026-01-25 13:25:28'),
(129, 6, 21, NULL, '2026-01-25 13:25:28'),
(130, 9, 21, NULL, '2026-01-25 13:25:28'),
(131, 7, 21, NULL, '2026-01-25 13:25:28'),
(132, 8, 26, NULL, '2026-01-25 13:25:28'),
(133, 6, 26, NULL, '2026-01-25 13:25:28'),
(134, 9, 26, NULL, '2026-01-25 13:25:28'),
(135, 7, 26, NULL, '2026-01-25 13:25:28'),
(136, 8, 30, NULL, '2026-01-25 13:25:28'),
(137, 6, 30, NULL, '2026-01-25 13:25:28'),
(138, 9, 30, NULL, '2026-01-25 13:25:28'),
(139, 7, 30, NULL, '2026-01-25 13:25:28'),
(140, 8, 31, NULL, '2026-01-25 13:25:28'),
(141, 6, 31, NULL, '2026-01-25 13:25:28'),
(142, 9, 31, NULL, '2026-01-25 13:25:28'),
(143, 7, 31, NULL, '2026-01-25 13:25:28'),
(144, 8, 32, NULL, '2026-01-25 13:25:28'),
(145, 6, 32, NULL, '2026-01-25 13:25:28'),
(146, 9, 32, NULL, '2026-01-25 13:25:28'),
(147, 7, 32, NULL, '2026-01-25 13:25:28'),
(148, 8, 37, NULL, '2026-01-25 13:25:28'),
(149, 6, 37, NULL, '2026-01-25 13:25:28'),
(150, 9, 37, NULL, '2026-01-25 13:25:28'),
(151, 7, 37, NULL, '2026-01-25 13:25:28');

-- --------------------------------------------------------

--
-- Table structure for table `search_analytics`
--

CREATE TABLE `search_analytics` (
  `id_search_analytics` int(11) NOT NULL,
  `date` date NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `total_searches` int(11) DEFAULT 0,
  `unique_users` int(11) DEFAULT 0,
  `avg_results_per_search` decimal(8,2) DEFAULT 0.00,
  `avg_execution_time_ms` decimal(10,3) DEFAULT 0.000,
  `click_through_rate` decimal(5,3) DEFAULT 0.000,
  `top_queries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`top_queries`)),
  `no_result_queries` int(11) DEFAULT 0,
  `entity_type_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`entity_type_breakdown`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_index`
--

CREATE TABLE `search_index` (
  `id_search_index` int(11) NOT NULL,
  `entity_type` enum('product','customer','supplier','transaction','file','member','company','branch','notification','audit_log') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `indexed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `search_weight` decimal(3,2) DEFAULT 1.00,
  `access_level` enum('public','company','branch','private') DEFAULT 'company'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_indexing_queue`
--

CREATE TABLE `search_indexing_queue` (
  `id_indexing_queue` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `operation` enum('index','update','delete') NOT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_queries`
--

CREATE TABLE `search_queries` (
  `id_search_query` int(11) NOT NULL,
  `query_text` varchar(500) NOT NULL,
  `query_hash` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `entity_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`entity_types`)),
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `sort_by` enum('relevance','date_desc','date_asc','title_asc','title_desc') DEFAULT 'relevance',
  `results_count` int(11) DEFAULT 0,
  `execution_time_ms` decimal(10,3) DEFAULT 0.000,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_results`
--

CREATE TABLE `search_results` (
  `id_search_result` int(11) NOT NULL,
  `search_query_id` int(11) NOT NULL,
  `search_index_id` int(11) NOT NULL,
  `rank_position` int(11) NOT NULL,
  `relevance_score` decimal(5,3) DEFAULT 0.000,
  `clicked` tinyint(1) DEFAULT 0,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_settings`
--

CREATE TABLE `search_settings` (
  `id_search_setting` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `search_settings`
--

INSERT INTO `search_settings` (`id_search_setting`, `setting_key`, `setting_value`, `setting_type`, `description`, `company_id`, `branch_id`, `created_at`, `updated_at`) VALUES
(1, 'search_enabled', 'true', 'boolean', 'Enable search functionality', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(2, 'auto_indexing', 'true', 'boolean', 'Enable automatic content indexing', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(3, 'fulltext_min_word_length', '3', 'number', 'Minimum word length for full-text search', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(4, 'max_search_results', '100', 'number', 'Maximum number of search results to return', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(5, 'search_timeout_seconds', '30', 'number', 'Maximum search execution time in seconds', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(6, 'enable_search_analytics', 'true', 'boolean', 'Enable search analytics tracking', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(7, 'enable_search_suggestions', 'true', 'boolean', 'Enable auto-complete suggestions', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(8, 'suggestion_min_frequency', '3', 'number', 'Minimum frequency for search suggestions', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(9, 'index_batch_size', '100', 'number', 'Number of items to index in one batch', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(10, 'cleanup_old_queries_days', '90', 'number', 'Days to keep old search queries', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(11, 'enable_spell_check', 'false', 'boolean', 'Enable spell checking in search', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(12, 'enable_fuzzy_search', 'true', 'boolean', 'Enable fuzzy matching in search', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(13, 'fuzzy_distance', '2', 'number', 'Maximum edit distance for fuzzy search', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(14, 'boost_recent_content', 'true', 'boolean', 'Boost recent content in search results', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(15, 'recent_content_days', '30', 'number', 'Days to consider content as recent', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(16, 'enable_entity_boosting', 'true', 'boolean', 'Enable entity type boosting', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(17, 'entity_boost_weights', '{\"product\": 1.2, \"transaction\": 1.1, \"customer\": 1.0, \"file\": 0.9}', 'json', 'Boost weights for different entity types', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(18, 'enable_search_logging', 'true', 'boolean', 'Enable detailed search logging', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(19, 'log_slow_queries', 'true', 'boolean', 'Log slow search queries', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(20, 'slow_query_threshold_ms', '1000', 'number', 'Threshold for slow queries in milliseconds', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(21, 'enable_search_caching', 'true', 'boolean', 'Enable search result caching', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(22, 'cache_ttl_seconds', '300', 'number', 'Cache time-to-live in seconds', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(23, 'max_cache_size', '1000', 'number', 'Maximum number of cached results', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(24, 'enable_search_api', 'true', 'boolean', 'Enable search API endpoints', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(25, 'api_rate_limit_per_minute', '60', 'number', 'API rate limit per minute per user', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(26, 'enable_advanced_filters', 'true', 'boolean', 'Enable advanced search filters', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(27, 'enable_date_range_search', 'true', 'boolean', 'Enable date range search', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(28, 'enable_tag_search', 'true', 'boolean', 'Enable tag-based search', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(29, 'enable_content_preview', 'true', 'boolean', 'Enable content preview in results', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(30, 'preview_length', '200', 'number', 'Length of content preview in characters', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(31, 'enable_search_export', 'true', 'boolean', 'Enable search results export', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(32, 'export_max_results', '10000', 'number', 'Maximum results for export', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(33, 'enable_search_history', 'true', 'boolean', 'Enable user search history', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(34, 'history_max_entries', '50', 'number', 'Maximum search history entries per user', NULL, NULL, '2026-01-25 13:25:17', '2026-01-25 13:25:17'),
(35, 'search_enabled', 'true', 'boolean', 'Enable search functionality', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(36, 'auto_indexing', 'true', 'boolean', 'Enable automatic content indexing', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(37, 'fulltext_min_word_length', '3', 'number', 'Minimum word length for full-text search', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(38, 'max_search_results', '100', 'number', 'Maximum number of search results to return', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(39, 'search_timeout_seconds', '30', 'number', 'Maximum search execution time in seconds', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(40, 'enable_search_analytics', 'true', 'boolean', 'Enable search analytics tracking', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(41, 'enable_search_suggestions', 'true', 'boolean', 'Enable auto-complete suggestions', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(42, 'suggestion_min_frequency', '3', 'number', 'Minimum frequency for search suggestions', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(43, 'index_batch_size', '100', 'number', 'Number of items to index in one batch', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(44, 'cleanup_old_queries_days', '90', 'number', 'Days to keep old search queries', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(45, 'enable_spell_check', 'false', 'boolean', 'Enable spell checking in search', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(46, 'enable_fuzzy_search', 'true', 'boolean', 'Enable fuzzy matching in search', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(47, 'fuzzy_distance', '2', 'number', 'Maximum edit distance for fuzzy search', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(48, 'boost_recent_content', 'true', 'boolean', 'Boost recent content in search results', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(49, 'recent_content_days', '30', 'number', 'Days to consider content as recent', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(50, 'enable_entity_boosting', 'true', 'boolean', 'Enable entity type boosting', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(51, 'entity_boost_weights', '{\"product\": 1.2, \"transaction\": 1.1, \"customer\": 1.0, \"file\": 0.9}', 'json', 'Boost weights for different entity types', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(52, 'enable_search_logging', 'true', 'boolean', 'Enable detailed search logging', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(53, 'log_slow_queries', 'true', 'boolean', 'Log slow search queries', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(54, 'slow_query_threshold_ms', '1000', 'number', 'Threshold for slow queries in milliseconds', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(55, 'enable_search_caching', 'true', 'boolean', 'Enable search result caching', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(56, 'cache_ttl_seconds', '300', 'number', 'Cache time-to-live in seconds', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(57, 'max_cache_size', '1000', 'number', 'Maximum number of cached results', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(58, 'enable_search_api', 'true', 'boolean', 'Enable search API endpoints', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(59, 'api_rate_limit_per_minute', '60', 'number', 'API rate limit per minute per user', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(60, 'enable_advanced_filters', 'true', 'boolean', 'Enable advanced search filters', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(61, 'enable_date_range_search', 'true', 'boolean', 'Enable date range search', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(62, 'enable_tag_search', 'true', 'boolean', 'Enable tag-based search', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(63, 'enable_content_preview', 'true', 'boolean', 'Enable content preview in results', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(64, 'preview_length', '200', 'number', 'Length of content preview in characters', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(65, 'enable_search_export', 'true', 'boolean', 'Enable search results export', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(66, 'export_max_results', '10000', 'number', 'Maximum results for export', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(67, 'enable_search_history', 'true', 'boolean', 'Enable user search history', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28'),
(68, 'history_max_entries', '50', 'number', 'Maximum search history entries per user', NULL, NULL, '2026-01-25 13:25:28', '2026-01-25 13:25:28');

-- --------------------------------------------------------

--
-- Table structure for table `search_suggestions`
--

CREATE TABLE `search_suggestions` (
  `id_search_suggestion` int(11) NOT NULL,
  `suggestion_text` varchar(255) NOT NULL,
  `suggestion_type` enum('query','entity','tag','keyword') DEFAULT 'query',
  `entity_type` varchar(50) DEFAULT NULL,
  `frequency` int(11) DEFAULT 1,
  `last_used` timestamp NOT NULL DEFAULT current_timestamp(),
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `supplier_code` varchar(50) NOT NULL,
  `supplier_name` varchar(200) NOT NULL,
  `supplier_type` enum('individual','company','distributor','manufacturer','importer','local_producer') DEFAULT 'company',
  `business_category` enum('retail','wholesale','manufacturing','agriculture','services','distribution','import_export') DEFAULT 'wholesale',
  `company_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `address_detail` text DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `business_license` varchar(100) DEFAULT NULL,
  `business_registration` varchar(100) DEFAULT NULL,
  `establishment_date` date DEFAULT NULL,
  `capital_amount` decimal(15,2) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_account_name` varchar(200) DEFAULT NULL,
  `bank_branch` varchar(100) DEFAULT NULL,
  `supplier_category` enum('regular','preferred','strategic','backup','blacklisted') DEFAULT 'regular',
  `supplier_level` enum('basic','silver','gold','platinum') DEFAULT 'basic',
  `total_orders` int(11) DEFAULT 0,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `average_delivery_time` int(11) DEFAULT 0,
  `on_time_delivery_rate` decimal(5,2) DEFAULT 0.00,
  `quality_score` decimal(5,2) DEFAULT 0.00,
  `overall_score` decimal(5,2) DEFAULT 0.00,
  `payment_terms` enum('cod','7_days','14_days','30_days','45_days','60_days','90_days') DEFAULT '30_days',
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `is_blacklisted` tinyint(1) DEFAULT 0,
  `blacklist_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `tax_name` varchar(200) DEFAULT NULL,
  `is_tax_registered` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_categories`
--

CREATE TABLE `supplier_categories` (
  `id_category` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_categories`
--

INSERT INTO `supplier_categories` (`id_category`, `category_name`, `category_description`, `parent_id`, `is_active`, `created_at`) VALUES
(1, 'Raw Materials', 'Suppliers of raw materials for production', NULL, 1, '2026-01-25 09:06:59'),
(2, 'Finished Goods', 'Suppliers of finished products for resale', NULL, 1, '2026-01-25 09:06:59'),
(3, 'Packaging Materials', 'Suppliers of packaging and shipping materials', NULL, 1, '2026-01-25 09:06:59'),
(4, 'Equipment & Tools', 'Suppliers of business equipment and tools', NULL, 1, '2026-01-25 09:06:59'),
(5, 'Services', 'Service providers and consultants', NULL, 1, '2026-01-25 09:06:59'),
(6, 'Utilities', 'Utility companies and service providers', NULL, 1, '2026-01-25 09:06:59'),
(7, 'Technology', 'IT and technology suppliers', NULL, 1, '2026-01-25 09:06:59'),
(8, 'Maintenance', 'Maintenance and repair services', NULL, 1, '2026-01-25 09:06:59'),
(9, 'Transportation', 'Logistics and transportation providers', NULL, 1, '2026-01-25 09:06:59'),
(10, 'Office Supplies', 'Office and administrative supplies', NULL, 1, '2026-01-25 09:06:59');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_category_assignments`
--

CREATE TABLE `supplier_category_assignments` (
  `id_assignment` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_contacts`
--

CREATE TABLE `supplier_contacts` (
  `id_contact` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `contact_name` varchar(200) NOT NULL,
  `contact_position` varchar(100) DEFAULT NULL,
  `contact_department` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_performance`
--

CREATE TABLE `supplier_performance` (
  `id_performance` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `evaluation_period_start` date NOT NULL,
  `evaluation_period_end` date NOT NULL,
  `delivery_score` decimal(5,2) DEFAULT NULL,
  `quality_score` decimal(5,2) DEFAULT NULL,
  `price_score` decimal(5,2) DEFAULT NULL,
  `service_score` decimal(5,2) DEFAULT NULL,
  `compliance_score` decimal(5,2) DEFAULT NULL,
  `overall_score` decimal(5,2) DEFAULT NULL,
  `total_orders` int(11) DEFAULT 0,
  `on_time_deliveries` int(11) DEFAULT 0,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `average_delivery_time` decimal(5,2) DEFAULT 0.00,
  `evaluator_id` int(11) DEFAULT NULL,
  `evaluation_notes` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_products`
--

CREATE TABLE `supplier_products` (
  `id_supplier_product` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_product_code` varchar(100) DEFAULT NULL,
  `supplier_product_name` varchar(200) DEFAULT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `min_order_quantity` decimal(10,2) DEFAULT 1.00,
  `lead_time_days` int(11) DEFAULT 0,
  `availability` enum('always','seasonal','on_demand','discontinued') DEFAULT 'always',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `description`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'Aplikasi Perdagangan Multi-Cabang', 'general', 'Application name', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(2, 'app_version', '2.0.0', 'general', 'Application version', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(3, 'timezone', 'Asia/Jakarta', 'general', 'Default timezone', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(4, 'date_format', 'd-m-Y', 'general', 'Date format', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(5, 'time_format', 'H:i:s', 'general', 'Time format', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(6, 'currency', 'IDR', 'general', 'Default currency', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(7, 'decimal_places', '2', 'general', 'Number of decimal places for currency', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(8, 'company_logo', '', 'general', 'Company logo file path', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(9, 'company_address', '', 'general', 'Company address', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(10, 'company_phone', '', 'general', 'Company phone', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(11, 'company_email', 'info@perdagangan.com', 'general', 'Company email', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(12, 'session_timeout', '7200', 'security', 'Session timeout in seconds', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(13, 'max_login_attempts', '5', 'security', 'Maximum login attempts before lockout', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(14, 'lockout_duration', '900', 'security', 'Account lockout duration in seconds', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(15, 'password_min_length', '6', 'security', 'Minimum password length', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(16, 'password_require_uppercase', '1', 'security', 'Require uppercase in password', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(17, 'password_require_lowercase', '1', 'security', 'Require lowercase in password', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(18, 'password_require_numbers', '1', 'security', 'Require numbers in password', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(19, 'password_require_special', '1', 'security', 'Require special characters in password', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(20, 'require_password_change', '0', 'security', 'Require password change on first login', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(21, 'password_expiry_days', '90', 'security', 'Password expiry in days', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(22, 'enable_2fa', '0', 'security', 'Enable two-factor authentication', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(23, 'enable_ip_whitelist', '0', 'security', 'Enable IP whitelist', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(24, 'ip_whitelist', '[]', 'security', 'Allowed IP addresses', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(25, 'smtp_host', '', 'email', 'SMTP server hostname', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(26, 'smtp_port', '587', 'email', 'SMTP server port', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(27, 'smtp_username', '', 'email', 'SMTP username', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(28, 'smtp_password', '', 'email', 'SMTP password', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(29, 'smtp_encryption', 'tls', 'email', 'SMTP encryption type (tls, ssl, none)', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(30, 'from_email', 'noreply@perdagangan.com', 'email', 'Default from email address', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(31, 'from_name', 'Perdagangan System', 'email', 'Default from name', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(32, 'email_queue_enabled', '0', 'email', 'Enable email queue system', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(33, 'batch_email_limit', '100', 'email', 'Maximum emails per batch', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(34, 'auto_backup', '0', 'backup', 'Enable automatic backup', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(35, 'backup_frequency', 'daily', 'backup', 'Backup frequency (daily, weekly, monthly)', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(36, 'backup_retention', '30', 'backup', 'Number of days to keep backups', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(37, 'backup_path', '/backups', 'backup', 'Backup storage path', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(38, 'backup_compression', '1', 'backup', 'Compress backup files', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(39, 'backup_include_files', '1', 'backup', 'Include files in backup', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(40, 'backup_encryption', '0', 'backup', 'Encrypt backup files', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(41, 'backup_encryption_key', '', 'backup', 'Backup encryption key', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(42, 'enable_reports', '1', 'features', 'Enable reporting system', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(43, 'enable_notifications', '1', 'features', 'Enable system notifications', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(44, 'enable_backup', '1', 'features', 'Enable backup system', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(45, 'enable_audit_log', '1', 'features', 'Enable audit logging', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(46, 'enable_api_access', '0', 'features', 'Enable API access', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(47, 'enable_maintenance_mode', '0', 'features', 'Enable maintenance mode', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(48, 'enable_registration', '0', 'features', 'Enable user registration', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(49, 'enable_email_verification', '0', 'features', 'Enable email verification', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(50, 'enable_password_reset', '1', 'features', 'Enable password reset', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(51, 'enable_multi_company', '1', 'features', 'Enable multi-company support', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(52, 'enable_multi_branch', '1', 'features', 'Enable multi-branch support', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(53, 'theme', 'dark-blue', 'ui', 'Default theme', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(54, 'language', 'id', 'ui', 'Default language', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(55, 'items_per_page', '25', 'ui', 'Items per page in tables', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(56, 'enable_animations', '1', 'ui', 'Enable UI animations', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(57, 'enable_tooltips', '1', 'ui', 'Enable tooltips', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50'),
(58, 'sidebar_collapsed', '0', 'ui', 'Sidebar collapsed by default', 1, NULL, NULL, '2026-01-25 09:14:50', '2026-01-25 09:14:50');

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
  `real_time_update` tinyint(1) DEFAULT 1,
  `supplier_id` int(11) DEFAULT NULL,
  `reference_type` enum('po','direct','other') DEFAULT 'direct',
  `reference_id` int(11) DEFAULT NULL
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
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id_log` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('login','logout','create','update','delete','view','export','import','print','download','upload','approve','reject','other') NOT NULL,
  `activity_description` text NOT NULL,
  `module_name` varchar(100) DEFAULT NULL,
  `action_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`action_details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_file_statistics_view`
-- (See below for the actual view)
--
CREATE TABLE `user_file_statistics_view` (
`id_user` int(11)
,`username` varchar(50)
,`total_files` bigint(21)
,`total_size` decimal(41,0)
,`total_downloads` decimal(32,0)
,`categories_used` bigint(21)
,`last_upload` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `user_import_export_templates`
--

CREATE TABLE `user_import_export_templates` (
  `id_template` int(11) NOT NULL,
  `template_name` varchar(200) NOT NULL,
  `template_type` enum('import','export') NOT NULL,
  `template_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`template_fields`)),
  `created_by` int(11) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_import_export_templates`
--

INSERT INTO `user_import_export_templates` (`id_template`, `template_name`, `template_type`, `template_fields`, `created_by`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'User Import Template', 'import', '{\"member_code\": {\"required\": true, \"label\": \"Member Code\"}, \"member_name\": {\"required\": true, \"label\": \"Full Name\"}, \"email\": {\"required\": false, \"label\": \"Email\"}, \"phone\": {\"required\": false, \"label\": \"Phone\"}, \"position\": {\"required\": false, \"label\": \"Position\"}, \"branch_id\": {\"required\": true, \"label\": \"Branch ID\"}}', 1, 1, '2026-01-25 09:12:16', '2026-01-25 09:12:16'),
(2, 'User Export Template', 'export', '{\"member_code\": {\"label\": \"Member Code\"}, \"member_name\": {\"label\": \"Full Name\"}, \"email\": {\"label\": \"Email\"}, \"phone\": {\"label\": \"Phone\"}, \"position\": {\"label\": \"Position\"}, \"branch_name\": {\"label\": \"Branch\"}, \"role_name\": {\"label\": \"Role\"}, \"is_active\": {\"label\": \"Active\"}}', 1, 1, '2026-01-25 09:12:16', '2026-01-25 09:12:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id_preference` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text DEFAULT NULL,
  `preference_type` enum('string','boolean','integer','json') DEFAULT 'string',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id_preference`, `user_id`, `preference_key`, `preference_value`, `preference_type`, `updated_at`) VALUES
(1, 1, 'theme', 'default', 'string', '2026-01-25 09:12:16'),
(2, 2, 'theme', 'default', 'string', '2026-01-25 09:12:16'),
(3, 5, 'theme', 'default', 'string', '2026-01-25 09:12:16'),
(4, 4, 'theme', 'default', 'string', '2026-01-25 09:12:16'),
(5, 3, 'theme', 'default', 'string', '2026-01-25 09:12:16'),
(8, 1, 'language', 'id', 'string', '2026-01-25 09:12:16'),
(9, 2, 'language', 'id', 'string', '2026-01-25 09:12:16'),
(10, 5, 'language', 'id', 'string', '2026-01-25 09:12:16'),
(11, 4, 'language', 'id', 'string', '2026-01-25 09:12:16'),
(12, 3, 'language', 'id', 'string', '2026-01-25 09:12:16'),
(15, 1, 'notifications_enabled', '1', 'boolean', '2026-01-25 09:12:16'),
(16, 2, 'notifications_enabled', '1', 'boolean', '2026-01-25 09:12:16'),
(17, 5, 'notifications_enabled', '1', 'boolean', '2026-01-25 09:12:16'),
(18, 4, 'notifications_enabled', '1', 'boolean', '2026-01-25 09:12:16'),
(19, 3, 'notifications_enabled', '1', 'boolean', '2026-01-25 09:12:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id_role` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_code` varchar(50) NOT NULL,
  `role_description` text DEFAULT NULL,
  `role_level` int(11) NOT NULL DEFAULT 0,
  `is_system_role` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id_role`, `role_name`, `role_code`, `role_description`, `role_level`, `is_system_role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Administrator', 'SUPER_ADMIN', 'Full system access with all privileges', 1, 1, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(2, 'Administrator', 'ADMIN', 'System administration with limited privileges', 2, 1, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(3, 'Company Owner', 'COMPANY_OWNER', 'Owns and manages entire company', 10, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(4, 'Branch Owner', 'BRANCH_OWNER', 'Owns and manages specific branch', 11, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(5, 'Director', 'DIRECTOR', 'Company director with multi-branch access', 12, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(6, 'Manager', 'MANAGER', 'Branch manager with operational control', 13, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(7, 'Supervisor', 'SUPERVISOR', 'Department supervisor with limited access', 14, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(8, 'Cashier', 'CASHIER', 'Point of sale and transaction operations', 15, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(9, 'Staff', 'STAFF', 'General staff with basic operations', 16, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(10, 'Security', 'SECURITY', 'Security and monitoring only', 17, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(11, 'Viewer', 'VIEWER', 'Read-only access to assigned areas', 18, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15'),
(12, 'Customer', 'CUSTOMER', 'External customer access', 20, 0, 1, '2026-01-25 09:12:15', '2026-01-25 09:12:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_role_assignments`
--

CREATE TABLE `user_role_assignments` (
  `id_assignment` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
,`address_detail` text
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
,`village_postal_code` varchar(10)
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
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_popular_searches`
-- (See below for the actual view)
--
CREATE TABLE `v_popular_searches` (
`query_text` varchar(500)
,`search_count` bigint(21)
,`unique_users` bigint(21)
,`avg_results` decimal(14,4)
,`avg_execution_time` decimal(14,7)
,`last_searched` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_search_performance`
-- (See below for the actual view)
--
CREATE TABLE `v_search_performance` (
`search_date` date
,`total_searches` bigint(21)
,`unique_users` bigint(21)
,`avg_results` decimal(14,4)
,`avg_execution_time` decimal(14,7)
,`no_result_searches` decimal(22,0)
,`slow_searches` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_user_activity_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_user_activity_summary` (
`user_id` int(11)
,`member_name` varchar(200)
,`member_code` varchar(50)
,`total_activities` bigint(21)
,`login_count` bigint(21)
,`logout_count` bigint(21)
,`modification_count` bigint(21)
,`last_activity` timestamp
,`activities_last_7_days` bigint(21)
,`activities_last_30_days` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_user_assignments`
-- (See below for the actual view)
--
CREATE TABLE `v_user_assignments` (
`user_id` int(11)
,`member_code` varchar(50)
,`user_name` varchar(200)
,`email` varchar(100)
,`phone` varchar(50)
,`position` enum('owner','manager','cashier','staff','security')
,`user_active` tinyint(1)
,`id_role` int(11)
,`role_name` varchar(100)
,`role_code` varchar(50)
,`role_level` int(11)
,`branch_name` varchar(200)
,`company_name` varchar(200)
,`assigned_at` timestamp
,`expires_at` timestamp
,`assignment_active` tinyint(1)
,`assignment_status` varchar(8)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `websocket_active_connections`
-- (See below for the actual view)
--
CREATE TABLE `websocket_active_connections` (
`connection_id` varchar(255)
,`user_id` int(11)
,`member_name` varchar(200)
,`email` varchar(100)
,`company_id` int(11)
,`company_name` varchar(200)
,`branch_id` int(11)
,`branch_name` varchar(200)
,`ip_address` varchar(45)
,`connection_type` enum('web','mobile','api')
,`connected_at` timestamp
,`last_activity` timestamp
,`duration_seconds` bigint(21)
,`channel_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `websocket_channels`
--

CREATE TABLE `websocket_channels` (
  `id_channel` int(11) NOT NULL,
  `channel_name` varchar(100) NOT NULL,
  `channel_type` enum('public','private','company','branch','user') NOT NULL,
  `description` text DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `websocket_channels`
--

INSERT INTO `websocket_channels` (`id_channel`, `channel_name`, `channel_type`, `description`, `owner_id`, `company_id`, `branch_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'global', 'public', 'Global system announcements', NULL, NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(2, 'notifications', 'public', 'System notifications channel', NULL, NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(3, 'file-uploads', 'public', 'File upload notifications', NULL, NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(4, 'system-alerts', 'public', 'System alerts and warnings', NULL, NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(5, 'user-presence', 'public', 'User presence updates', NULL, NULL, NULL, 1, '2026-01-25 13:25:16', '2026-01-25 13:25:16');

-- --------------------------------------------------------

--
-- Table structure for table `websocket_channel_subscriptions`
--

CREATE TABLE `websocket_channel_subscriptions` (
  `id_subscription` int(11) NOT NULL,
  `connection_id` varchar(255) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_message_id` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websocket_connections`
--

CREATE TABLE `websocket_connections` (
  `id_connection` int(11) NOT NULL,
  `connection_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `connected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive','disconnected') DEFAULT 'active',
  `connection_type` enum('web','mobile','api') DEFAULT 'web',
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websocket_events`
--

CREATE TABLE `websocket_events` (
  `id_event` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_data`)),
  `source_user_id` int(11) DEFAULT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','processed','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websocket_messages`
--

CREATE TABLE `websocket_messages` (
  `id_message` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_type` enum('text','json','notification','system','file','image') DEFAULT 'text',
  `message_content` longtext DEFAULT NULL,
  `message_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`message_data`)),
  `recipient_id` int(11) DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_delivered` tinyint(1) DEFAULT 0,
  `delivery_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websocket_message_delivery`
--

CREATE TABLE `websocket_message_delivery` (
  `id_delivery` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `connection_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `delivered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_status` enum('pending','delivered','failed','expired') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websocket_settings`
--

CREATE TABLE `websocket_settings` (
  `id_setting` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `websocket_settings`
--

INSERT INTO `websocket_settings` (`id_setting`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_system`, `company_id`, `branch_id`, `created_at`, `updated_at`) VALUES
(1, 'websocket_enabled', 'true', 'boolean', 'Enable WebSocket functionality', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(2, 'websocket_port', '8080', 'number', 'WebSocket server port', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(3, 'websocket_host', 'localhost', 'string', 'WebSocket server host', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(4, 'max_connections_per_user', '5', 'number', 'Maximum connections per user', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(5, 'connection_timeout', '300', 'number', 'Connection timeout in seconds', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(6, 'message_retention_days', '30', 'number', 'Message retention period in days', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(7, 'enable_file_sharing', 'true', 'boolean', 'Enable file sharing through WebSocket', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(8, 'max_file_size_mb', '10', 'number', 'Maximum file size for sharing in MB', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(9, 'enable_private_messages', 'true', 'boolean', 'Enable private messaging', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(10, 'enable_channel_creation', 'false', 'boolean', 'Allow users to create channels', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(11, 'auto_cleanup_connections', 'true', 'boolean', 'Automatically cleanup inactive connections', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(12, 'cleanup_interval_minutes', '5', 'number', 'Cleanup interval for inactive connections', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(13, 'enable_message_encryption', 'false', 'boolean', 'Enable message encryption', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(14, 'enable_rate_limiting', 'true', 'boolean', 'Enable rate limiting for messages', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(15, 'max_messages_per_minute', '30', 'number', 'Maximum messages per minute per user', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(16, 'enable_presence_tracking', 'true', 'boolean', 'Enable user presence tracking', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(17, 'enable_typing_indicators', 'true', 'boolean', 'Enable typing indicators', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(18, 'enable_read_receipts', 'true', 'boolean', 'Enable read receipts', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16'),
(19, 'enable_message_history', 'true', 'boolean', 'Enable message history storage', 1, NULL, NULL, '2026-01-25 13:25:16', '2026-01-25 13:25:16');

-- --------------------------------------------------------

--
-- Table structure for table `websocket_statistics`
--

CREATE TABLE `websocket_statistics` (
  `id_stat` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `total_connections` int(11) DEFAULT 0,
  `active_connections` int(11) DEFAULT 0,
  `total_messages` int(11) DEFAULT 0,
  `delivered_messages` int(11) DEFAULT 0,
  `failed_messages` int(11) DEFAULT 0,
  `average_response_time` decimal(10,3) DEFAULT 0.000,
  `peak_connections` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Structure for view `file_statistics_view`
--
DROP TABLE IF EXISTS `file_statistics_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `file_statistics_view`  AS SELECT `f`.`file_category` AS `file_category`, count(0) AS `total_files`, sum(`f`.`file_size`) AS `total_size`, avg(`f`.`file_size`) AS `avg_size`, min(`f`.`file_size`) AS `min_size`, max(`f`.`file_size`) AS `max_size`, sum(`f`.`download_count`) AS `total_downloads`, count(distinct `f`.`uploaded_by`) AS `unique_uploaders`, cast(`f`.`created_at` as date) AS `upload_date` FROM `files` AS `f` WHERE `f`.`is_active` = 1 GROUP BY `f`.`file_category`, cast(`f`.`created_at` as date) ;

-- --------------------------------------------------------

--
-- Structure for view `user_file_statistics_view`
--
DROP TABLE IF EXISTS `user_file_statistics_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `user_file_statistics_view`  AS SELECT `u`.`id_user` AS `id_user`, `u`.`username` AS `username`, count(`f`.`id_file`) AS `total_files`, sum(`f`.`file_size`) AS `total_size`, sum(`f`.`download_count`) AS `total_downloads`, count(distinct `f`.`file_category`) AS `categories_used`, max(`f`.`created_at`) AS `last_upload` FROM (`users` `u` left join `files` `f` on(`u`.`id_user` = `f`.`uploaded_by` and `f`.`is_active` = 1)) GROUP BY `u`.`id_user`, `u`.`username` ;

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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_complete_addresses`  AS SELECT `a`.`id_address` AS `id_address`, `a`.`address_detail` AS `address_detail`, `a`.`postal_code` AS `postal_code`, `a`.`latitude` AS `latitude`, `a`.`longitude` AS `longitude`, `a`.`is_active` AS `is_active`, `a`.`created_at` AS `created_at`, `a`.`updated_at` AS `updated_at`, `p`.`name` AS `province_name`, `r`.`name` AS `regency_name`, `d`.`name` AS `district_name`, `v`.`name` AS `village_name`, `v`.`postal_code` AS `village_postal_code`, concat(`a`.`address_detail`,', ',`v`.`name`,', ',`d`.`name`,', ',`r`.`name`,', ',`p`.`name`,if(`a`.`postal_code` is not null,concat(' ',`a`.`postal_code`),'')) AS `full_address` FROM ((((`addresses` `a` left join `alamat_db`.`provinces` `p` on(`a`.`province_id` = `p`.`id`)) left join `alamat_db`.`regencies` `r` on(`a`.`regency_id` = `r`.`id`)) left join `alamat_db`.`districts` `d` on(`a`.`district_id` = `d`.`id`)) left join `alamat_db`.`villages` `v` on(`a`.`village_id` = `v`.`id`)) WHERE `a`.`is_active` = 1 ;

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

-- --------------------------------------------------------

--
-- Structure for view `v_popular_searches`
--
DROP TABLE IF EXISTS `v_popular_searches`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_popular_searches`  AS SELECT `search_queries`.`query_text` AS `query_text`, count(0) AS `search_count`, count(distinct `search_queries`.`user_id`) AS `unique_users`, avg(`search_queries`.`results_count`) AS `avg_results`, avg(`search_queries`.`execution_time_ms`) AS `avg_execution_time`, max(`search_queries`.`created_at`) AS `last_searched` FROM `search_queries` WHERE `search_queries`.`created_at` >= current_timestamp() - interval 30 day GROUP BY `search_queries`.`query_text` HAVING `search_count` >= 3 ORDER BY count(0) DESC, max(`search_queries`.`created_at`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_search_performance`
--
DROP TABLE IF EXISTS `v_search_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_search_performance`  AS SELECT cast(`search_queries`.`created_at` as date) AS `search_date`, count(0) AS `total_searches`, count(distinct `search_queries`.`user_id`) AS `unique_users`, avg(`search_queries`.`results_count`) AS `avg_results`, avg(`search_queries`.`execution_time_ms`) AS `avg_execution_time`, sum(case when `search_queries`.`results_count` = 0 then 1 else 0 end) AS `no_result_searches`, count(case when `search_queries`.`execution_time_ms` > 1000 then 1 end) AS `slow_searches` FROM `search_queries` GROUP BY cast(`search_queries`.`created_at` as date) ORDER BY cast(`search_queries`.`created_at` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_user_activity_summary`
--
DROP TABLE IF EXISTS `v_user_activity_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_user_activity_summary`  AS SELECT `ual`.`user_id` AS `user_id`, `m`.`member_name` AS `member_name`, `m`.`member_code` AS `member_code`, count(0) AS `total_activities`, count(case when `ual`.`activity_type` = 'login' then 1 end) AS `login_count`, count(case when `ual`.`activity_type` = 'logout' then 1 end) AS `logout_count`, count(case when `ual`.`activity_type` in ('create','update','delete') then 1 end) AS `modification_count`, max(`ual`.`created_at`) AS `last_activity`, count(case when `ual`.`created_at` >= curdate() - interval 7 day then 1 end) AS `activities_last_7_days`, count(case when `ual`.`created_at` >= curdate() - interval 30 day then 1 end) AS `activities_last_30_days` FROM (`user_activity_log` `ual` left join `members` `m` on(`ual`.`user_id` = `m`.`id_member`)) GROUP BY `ual`.`user_id`, `m`.`member_name`, `m`.`member_code` ;

-- --------------------------------------------------------

--
-- Structure for view `v_user_assignments`
--
DROP TABLE IF EXISTS `v_user_assignments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `v_user_assignments`  AS SELECT `m`.`id_member` AS `user_id`, `m`.`member_code` AS `member_code`, `m`.`member_name` AS `user_name`, `m`.`email` AS `email`, `m`.`phone` AS `phone`, `m`.`position` AS `position`, `m`.`is_active` AS `user_active`, `ur`.`id_role` AS `id_role`, `ur`.`role_name` AS `role_name`, `ur`.`role_code` AS `role_code`, `ur`.`role_level` AS `role_level`, `b`.`branch_name` AS `branch_name`, `c`.`company_name` AS `company_name`, `ura`.`assigned_at` AS `assigned_at`, `ura`.`expires_at` AS `expires_at`, `ura`.`is_active` AS `assignment_active`, CASE WHEN `ura`.`expires_at` is not null AND `ura`.`expires_at` < current_timestamp() THEN 'expired' WHEN `ura`.`is_active` = 0 THEN 'inactive' ELSE 'active' END AS `assignment_status` FROM ((((`members` `m` left join `user_role_assignments` `ura` on(`m`.`id_member` = `ura`.`user_id` and `ura`.`is_active` = 1)) left join `user_roles` `ur` on(`ura`.`role_id` = `ur`.`id_role`)) left join `branches` `b` on(`m`.`branch_id` = `b`.`id_branch`)) left join `companies` `c` on(`b`.`company_id` = `c`.`id_company`)) WHERE `m`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `websocket_active_connections`
--
DROP TABLE IF EXISTS `websocket_active_connections`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`::1` SQL SECURITY DEFINER VIEW `websocket_active_connections`  AS SELECT `c`.`connection_id` AS `connection_id`, `c`.`user_id` AS `user_id`, `m`.`member_name` AS `member_name`, `m`.`email` AS `email`, `c`.`company_id` AS `company_id`, `co`.`company_name` AS `company_name`, `c`.`branch_id` AS `branch_id`, `b`.`branch_name` AS `branch_name`, `c`.`ip_address` AS `ip_address`, `c`.`connection_type` AS `connection_type`, `c`.`connected_at` AS `connected_at`, `c`.`last_activity` AS `last_activity`, timestampdiff(SECOND,`c`.`connected_at`,current_timestamp()) AS `duration_seconds`, count(`cs`.`id_subscription`) AS `channel_count` FROM ((((`websocket_connections` `c` left join `members` `m` on(`c`.`user_id` = `m`.`id_member`)) left join `companies` `co` on(`c`.`company_id` = `co`.`id_company`)) left join `branches` `b` on(`c`.`branch_id` = `b`.`id_branch`)) left join `websocket_channel_subscriptions` `cs` on(`c`.`connection_id` = `cs`.`connection_id` and `cs`.`is_active` = 1)) WHERE `c`.`status` = 'active' GROUP BY `c`.`connection_id` ;

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
-- Indexes for table `advanced_reports`
--
ALTER TABLE `advanced_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_report_id` (`report_id`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_ai_model` (`ai_model`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_advanced_reports_composite` (`report_type`,`status`,`created_at`);

--
-- Indexes for table `ai_models`
--
ALTER TABLE `ai_models`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_model_type_version` (`model_type`,`version`),
  ADD KEY `idx_model_type` (`model_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_last_trained` (`last_trained`),
  ADD KEY `idx_ai_models_composite` (`model_type`,`status`,`last_trained`);

--
-- Indexes for table `ai_training_history`
--
ALTER TABLE `ai_training_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_training_id` (`training_id`),
  ADD KEY `idx_model_type` (`model_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_started_by` (`started_by`),
  ADD KEY `idx_started_at` (`started_at`);

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
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id_audit_log`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity_type` (`entity_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_backup_type` (`backup_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id_address`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_address_type` (`address_type`),
  ADD KEY `idx_primary` (`is_primary`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `customer_contacts`
--
ALTER TABLE `customer_contacts`
  ADD PRIMARY KEY (`id_contact`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_primary` (`is_primary`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `customer_feedback`
--
ALTER TABLE `customer_feedback`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `responded_by` (`responded_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_feedback_type` (`feedback_type`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_feedback_date` (`feedback_date`);

--
-- Indexes for table `customer_groups`
--
ALTER TABLE `customer_groups`
  ADD PRIMARY KEY (`id_group`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_group_name` (`group_name`),
  ADD KEY `idx_group_type` (`group_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `customer_group_memberships`
--
ALTER TABLE `customer_group_memberships`
  ADD PRIMARY KEY (`id_membership`),
  ADD UNIQUE KEY `uk_customer_group` (`customer_id`,`group_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_group` (`group_id`);

--
-- Indexes for table `customer_interactions`
--
ALTER TABLE `customer_interactions`
  ADD PRIMARY KEY (`id_interaction`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_interaction_type` (`interaction_type`),
  ADD KEY `idx_interaction_date` (`interaction_date`),
  ADD KEY `idx_outcome` (`outcome`),
  ADD KEY `idx_follow_up` (`follow_up_date`);

--
-- Indexes for table `customer_tags`
--
ALTER TABLE `customer_tags`
  ADD PRIMARY KEY (`id_tag`),
  ADD KEY `idx_tag_name` (`tag_name`),
  ADD KEY `idx_usage_count` (`usage_count`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `customer_tag_assignments`
--
ALTER TABLE `customer_tag_assignments`
  ADD PRIMARY KEY (`id_assignment`),
  ADD UNIQUE KEY `uk_customer_tag` (`customer_id`,`tag_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_tag` (`tag_id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_name` (`template_name`),
  ADD KEY `idx_template_name` (`template_name`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `feature_toggles`
--
ALTER TABLE `feature_toggles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `feature_name` (`feature_name`),
  ADD KEY `idx_feature_name` (`feature_name`),
  ADD KEY `idx_feature_group` (`feature_group`),
  ADD KEY `idx_is_enabled` (`is_enabled`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id_file`),
  ADD KEY `idx_files_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_files_company_id` (`company_id`),
  ADD KEY `idx_files_branch_id` (`branch_id`),
  ADD KEY `idx_files_category` (`file_category`),
  ADD KEY `idx_files_mime_type` (`mime_type`),
  ADD KEY `idx_files_is_active` (`is_active`),
  ADD KEY `idx_files_created_at` (`created_at`),
  ADD KEY `idx_files_filename` (`filename`),
  ADD KEY `idx_files_composite` (`is_active`,`company_id`,`branch_id`);

--
-- Indexes for table `file_access_log`
--
ALTER TABLE `file_access_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_file_access_log_file_id` (`file_id`),
  ADD KEY `idx_file_access_log_user_id` (`user_id`),
  ADD KEY `idx_file_access_log_access_type` (`access_type`),
  ADD KEY `idx_file_access_log_created_at` (`created_at`),
  ADD KEY `idx_file_access_log_share_token` (`share_token`),
  ADD KEY `idx_file_access_log_composite` (`file_id`,`created_at`);

--
-- Indexes for table `file_categories`
--
ALTER TABLE `file_categories`
  ADD PRIMARY KEY (`id_category`),
  ADD UNIQUE KEY `uk_file_categories_name` (`category_name`),
  ADD KEY `idx_file_categories_active` (`is_active`),
  ADD KEY `idx_file_categories_sort` (`sort_order`);

--
-- Indexes for table `file_settings`
--
ALTER TABLE `file_settings`
  ADD PRIMARY KEY (`id_setting`),
  ADD UNIQUE KEY `uk_file_settings_key` (`setting_key`),
  ADD KEY `idx_file_settings_active` (`is_active`);

--
-- Indexes for table `file_shares`
--
ALTER TABLE `file_shares`
  ADD PRIMARY KEY (`id_share`),
  ADD KEY `idx_file_shares_file_id` (`file_id`),
  ADD KEY `idx_file_shares_shared_by` (`shared_by`),
  ADD KEY `idx_file_shares_shared_with` (`shared_with`),
  ADD KEY `idx_file_shares_token` (`share_token`),
  ADD KEY `idx_file_shares_expires` (`expires_at`),
  ADD KEY `idx_file_shares_composite` (`file_id`,`is_active`,`expires_at`);

--
-- Indexes for table `file_versions`
--
ALTER TABLE `file_versions`
  ADD PRIMARY KEY (`id_version`),
  ADD KEY `idx_file_versions_file_id` (`file_id`),
  ADD KEY `idx_file_versions_type` (`file_type`);

--
-- Indexes for table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD PRIMARY KEY (`id_financial_report`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_report_period` (`report_period`),
  ADD KEY `idx_report_date` (`report_date`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id_inventory`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `idx_quantity` (`quantity`);

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
-- Indexes for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD PRIMARY KEY (`id_transaction`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id_member`),
  ADD UNIQUE KEY `uk_branch_member` (`branch_id`,`member_code`),
  ADD KEY `idx_member_code` (`member_code`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`);
ALTER TABLE `members` ADD FULLTEXT KEY `member_name` (`member_name`,`member_code`,`email`);

--
-- Indexes for table `migration_history`
--
ALTER TABLE `migration_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_migration` (`migration_name`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id_module`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id_preference`),
  ADD KEY `idx_user_id` (`user_id`);

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
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id_setting`),
  ADD UNIQUE KEY `uk_setting_key` (`setting_key`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id_template`),
  ADD UNIQUE KEY `uk_notification_templates_name` (`template_name`,`template_type`),
  ADD KEY `idx_notification_templates_type` (`template_type`),
  ADD KEY `idx_notification_templates_active` (`is_active`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id_permission`),
  ADD UNIQUE KEY `permission_name` (`permission_name`),
  ADD UNIQUE KEY `permission_code` (`permission_code`),
  ADD KEY `idx_permission_code` (`permission_code`),
  ADD KEY `idx_permission_group` (`permission_group`);

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
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id_po`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_po_number` (`po_number`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_branch` (`branch_id`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_po_composite` (`supplier_id`,`status`,`order_date`);
ALTER TABLE `purchase_orders` ADD FULLTEXT KEY `po_number_2` (`po_number`,`notes`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id_po_item`),
  ADD KEY `supplier_product_id` (`supplier_product_id`),
  ADD KEY `idx_po` (`po_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `report_analytics`
--
ALTER TABLE `report_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_id` (`report_id`),
  ADD KEY `idx_analytics_type` (`analytics_type`),
  ADD KEY `idx_confidence_score` (`confidence_score`),
  ADD KEY `idx_report_analytics_composite` (`report_id`,`analytics_type`,`confidence_score`);

--
-- Indexes for table `report_history`
--
ALTER TABLE `report_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_generated_at` (`generated_at`),
  ADD KEY `idx_template_id` (`report_template_id`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `report_permissions`
--
ALTER TABLE `report_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`),
  ADD KEY `idx_permission_group` (`permission_group`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `report_predictions`
--
ALTER TABLE `report_predictions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_id` (`report_id`),
  ADD KEY `idx_prediction_type` (`prediction_type`),
  ADD KEY `idx_confidence_score` (`confidence_score`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_report_predictions_composite` (`report_id`,`prediction_type`,`confidence_score`);

--
-- Indexes for table `report_recommendations`
--
ALTER TABLE `report_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_id` (`report_id`),
  ADD KEY `idx_recommendation_type` (`recommendation_type`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_actionable` (`actionable`),
  ADD KEY `idx_implemented` (`implemented`),
  ADD KEY `idx_report_recommendations_composite` (`report_id`,`priority`,`actionable`);

--
-- Indexes for table `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_template_id` (`report_template_id`),
  ADD KEY `idx_next_run` (`next_run`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_schedule_type` (`schedule_type`);

--
-- Indexes for table `report_templates`
--
ALTER TABLE `report_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_template_id` (`template_id`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_usage_count` (`usage_count`);

--
-- Indexes for table `report_template_permissions`
--
ALTER TABLE `report_template_permissions`
  ADD PRIMARY KEY (`template_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id_role_permission`),
  ADD UNIQUE KEY `uk_role_permission` (`role_id`,`permission_id`),
  ADD KEY `granted_by` (`granted_by`),
  ADD KEY `idx_role` (`role_id`),
  ADD KEY `idx_permission` (`permission_id`),
  ADD KEY `idx_role_permissions_composite` (`role_id`,`permission_id`);

--
-- Indexes for table `search_analytics`
--
ALTER TABLE `search_analytics`
  ADD PRIMARY KEY (`id_search_analytics`),
  ADD UNIQUE KEY `uk_date_company_branch` (`date`,`company_id`,`branch_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `idx_total_searches` (`total_searches`),
  ADD KEY `idx_unique_users` (`unique_users`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `search_index`
--
ALTER TABLE `search_index`
  ADD PRIMARY KEY (`id_search_index`),
  ADD UNIQUE KEY `uk_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_active_weight` (`is_active`,`search_weight`),
  ADD KEY `idx_access_level` (`access_level`),
  ADD KEY `idx_indexed_at` (`indexed_at`),
  ADD KEY `branch_id` (`branch_id`);
ALTER TABLE `search_index` ADD FULLTEXT KEY `ft_search_content` (`title`,`content`,`summary`,`keywords`);

--
-- Indexes for table `search_indexing_queue`
--
ALTER TABLE `search_indexing_queue`
  ADD PRIMARY KEY (`id_indexing_queue`),
  ADD KEY `idx_status_priority` (`status`,`priority`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_operation` (`operation`),
  ADD KEY `idx_attempts` (`attempts`);

--
-- Indexes for table `search_queries`
--
ALTER TABLE `search_queries`
  ADD PRIMARY KEY (`id_search_query`),
  ADD KEY `idx_query_hash` (`query_hash`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_results_count` (`results_count`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `search_results`
--
ALTER TABLE `search_results`
  ADD PRIMARY KEY (`id_search_result`),
  ADD KEY `idx_search_query` (`search_query_id`),
  ADD KEY `idx_search_index` (`search_index_id`),
  ADD KEY `idx_rank_position` (`rank_position`),
  ADD KEY `idx_clicked` (`clicked`),
  ADD KEY `idx_clicked_at` (`clicked_at`);

--
-- Indexes for table `search_settings`
--
ALTER TABLE `search_settings`
  ADD PRIMARY KEY (`id_search_setting`),
  ADD UNIQUE KEY `uk_setting_key_company_branch` (`setting_key`,`company_id`,`branch_id`),
  ADD KEY `idx_setting_key` (`setting_key`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `search_suggestions`
--
ALTER TABLE `search_suggestions`
  ADD PRIMARY KEY (`id_search_suggestion`),
  ADD KEY `idx_suggestion_text` (`suggestion_text`),
  ADD KEY `idx_suggestion_type` (`suggestion_type`),
  ADD KEY `idx_entity_type` (`entity_type`),
  ADD KEY `idx_frequency` (`frequency`),
  ADD KEY `idx_last_used` (`last_used`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `branch_id` (`branch_id`);

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
  ADD UNIQUE KEY `supplier_code` (`supplier_code`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `idx_supplier_code` (`supplier_code`),
  ADD KEY `idx_supplier_name` (`supplier_name`),
  ADD KEY `idx_supplier_type` (`supplier_type`),
  ADD KEY `idx_supplier_category` (`supplier_category`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_payment_terms` (`payment_terms`),
  ADD KEY `idx_overall_score` (`overall_score`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_blacklisted` (`is_blacklisted`);

--
-- Indexes for table `supplier_categories`
--
ALTER TABLE `supplier_categories`
  ADD PRIMARY KEY (`id_category`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `supplier_category_assignments`
--
ALTER TABLE `supplier_category_assignments`
  ADD PRIMARY KEY (`id_assignment`),
  ADD UNIQUE KEY `uk_supplier_category` (`supplier_id`,`category_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_category` (`category_id`);

--
-- Indexes for table `supplier_contacts`
--
ALTER TABLE `supplier_contacts`
  ADD PRIMARY KEY (`id_contact`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_primary` (`is_primary`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  ADD PRIMARY KEY (`id_performance`),
  ADD KEY `evaluator_id` (`evaluator_id`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_evaluation_period` (`evaluation_period_start`,`evaluation_period_end`),
  ADD KEY `idx_overall_score` (`overall_score`),
  ADD KEY `idx_performance_composite` (`supplier_id`,`overall_score`,`evaluation_period_end`);

--
-- Indexes for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD PRIMARY KEY (`id_supplier_product`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_active` (`is_active`);

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
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`),
  ADD KEY `idx_setting_group` (`setting_group`),
  ADD KEY `idx_is_active` (`is_active`);

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
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_transactions_branch` (`branch_id`),
  ADD KEY `idx_transactions_date` (`created_at`);

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
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_module` (`module_name`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_user_activity_composite` (`user_id`,`activity_type`,`created_at`);
ALTER TABLE `user_activity_log` ADD FULLTEXT KEY `activity_description` (`activity_description`,`module_name`);

--
-- Indexes for table `user_import_export_templates`
--
ALTER TABLE `user_import_export_templates`
  ADD PRIMARY KEY (`id_template`),
  ADD KEY `idx_template_type` (`template_type`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_default` (`is_default`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id_preference`),
  ADD UNIQUE KEY `uk_user_preference` (`user_id`,`preference_key`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_preference_key` (`preference_key`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `role_name` (`role_name`),
  ADD UNIQUE KEY `role_code` (`role_code`),
  ADD KEY `idx_role_code` (`role_code`),
  ADD KEY `idx_role_level` (`role_level`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  ADD PRIMARY KEY (`id_assignment`),
  ADD UNIQUE KEY `uk_user_role_active` (`user_id`,`role_id`,`is_active`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_role` (`role_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_user_assignments_composite` (`user_id`,`role_id`,`is_active`);

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
-- Indexes for table `websocket_channels`
--
ALTER TABLE `websocket_channels`
  ADD PRIMARY KEY (`id_channel`),
  ADD UNIQUE KEY `channel_name` (`channel_name`),
  ADD KEY `idx_channel_name` (`channel_name`),
  ADD KEY `idx_channel_type` (`channel_type`),
  ADD KEY `idx_owner` (`owner_id`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `websocket_channel_subscriptions`
--
ALTER TABLE `websocket_channel_subscriptions`
  ADD PRIMARY KEY (`id_subscription`),
  ADD KEY `idx_connection_channel` (`connection_id`,`channel_id`),
  ADD KEY `idx_user_channel` (`user_id`,`channel_id`),
  ADD KEY `idx_subscribed_at` (`subscribed_at`),
  ADD KEY `channel_id` (`channel_id`);

--
-- Indexes for table `websocket_connections`
--
ALTER TABLE `websocket_connections`
  ADD PRIMARY KEY (`id_connection`),
  ADD UNIQUE KEY `connection_id` (`connection_id`),
  ADD KEY `idx_connection_id` (`connection_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `websocket_events`
--
ALTER TABLE `websocket_events`
  ADD PRIMARY KEY (`id_event`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_event_name` (`event_name`),
  ADD KEY `idx_source_user` (`source_user_id`),
  ADD KEY `idx_target_user` (`target_user_id`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `websocket_messages`
--
ALTER TABLE `websocket_messages`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `idx_channel_id` (`channel_id`),
  ADD KEY `idx_sender_id` (`sender_id`),
  ADD KEY `idx_recipient_id` (`recipient_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `websocket_message_delivery`
--
ALTER TABLE `websocket_message_delivery`
  ADD PRIMARY KEY (`id_delivery`),
  ADD KEY `idx_message_connection` (`message_id`,`connection_id`),
  ADD KEY `idx_user_delivery` (`user_id`,`delivery_status`),
  ADD KEY `idx_delivered_at` (`delivered_at`);

--
-- Indexes for table `websocket_settings`
--
ALTER TABLE `websocket_settings`
  ADD PRIMARY KEY (`id_setting`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `websocket_statistics`
--
ALTER TABLE `websocket_statistics`
  ADD PRIMARY KEY (`id_stat`),
  ADD UNIQUE KEY `unique_date_company_branch` (`stat_date`,`company_id`,`branch_id`),
  ADD KEY `idx_stat_date` (`stat_date`),
  ADD KEY `idx_company_branch` (`company_id`,`branch_id`),
  ADD KEY `branch_id` (`branch_id`);

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
  MODIFY `id_address` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `address_usage`
--
ALTER TABLE `address_usage`
  MODIFY `id_usage` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `advanced_reports`
--
ALTER TABLE `advanced_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_models`
--
ALTER TABLE `ai_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ai_training_history`
--
ALTER TABLE `ai_training_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id_audit_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup_history`
--
ALTER TABLE `backup_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id_customer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `id_address` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_contacts`
--
ALTER TABLE `customer_contacts`
  MODIFY `id_contact` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_feedback`
--
ALTER TABLE `customer_feedback`
  MODIFY `id_feedback` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_groups`
--
ALTER TABLE `customer_groups`
  MODIFY `id_group` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_group_memberships`
--
ALTER TABLE `customer_group_memberships`
  MODIFY `id_membership` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_interactions`
--
ALTER TABLE `customer_interactions`
  MODIFY `id_interaction` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_tags`
--
ALTER TABLE `customer_tags`
  MODIFY `id_tag` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_tag_assignments`
--
ALTER TABLE `customer_tag_assignments`
  MODIFY `id_assignment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feature_toggles`
--
ALTER TABLE `feature_toggles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id_file` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_access_log`
--
ALTER TABLE `file_access_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_categories`
--
ALTER TABLE `file_categories`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `file_settings`
--
ALTER TABLE `file_settings`
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `file_shares`
--
ALTER TABLE `file_shares`
  MODIFY `id_share` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_versions`
--
ALTER TABLE `file_versions`
  MODIFY `id_version` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_reports`
--
ALTER TABLE `financial_reports`
  MODIFY `id_financial_report` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id_inventory` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  MODIFY `id_transaction` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id_member` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migration_history`
--
ALTER TABLE `migration_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id_module` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id_preference` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_queue`
--
ALTER TABLE `notification_queue`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id_template` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id_permission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id_po` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id_po_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_analytics`
--
ALTER TABLE `report_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_history`
--
ALTER TABLE `report_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_permissions`
--
ALTER TABLE `report_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `report_predictions`
--
ALTER TABLE `report_predictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_recommendations`
--
ALTER TABLE `report_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_schedules`
--
ALTER TABLE `report_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_templates`
--
ALTER TABLE `report_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id_role_permission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `search_analytics`
--
ALTER TABLE `search_analytics`
  MODIFY `id_search_analytics` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `search_index`
--
ALTER TABLE `search_index`
  MODIFY `id_search_index` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `search_indexing_queue`
--
ALTER TABLE `search_indexing_queue`
  MODIFY `id_indexing_queue` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `search_queries`
--
ALTER TABLE `search_queries`
  MODIFY `id_search_query` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `search_results`
--
ALTER TABLE `search_results`
  MODIFY `id_search_result` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `search_settings`
--
ALTER TABLE `search_settings`
  MODIFY `id_search_setting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `search_suggestions`
--
ALTER TABLE `search_suggestions`
  MODIFY `id_search_suggestion` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `supplier_categories`
--
ALTER TABLE `supplier_categories`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `supplier_category_assignments`
--
ALTER TABLE `supplier_category_assignments`
  MODIFY `id_assignment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_contacts`
--
ALTER TABLE `supplier_contacts`
  MODIFY `id_contact` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  MODIFY `id_performance` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_products`
--
ALTER TABLE `supplier_products`
  MODIFY `id_supplier_product` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

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
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_import_export_templates`
--
ALTER TABLE `user_import_export_templates`
  MODIFY `id_template` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id_preference` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  MODIFY `id_assignment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id_session` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websocket_channels`
--
ALTER TABLE `websocket_channels`
  MODIFY `id_channel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `websocket_channel_subscriptions`
--
ALTER TABLE `websocket_channel_subscriptions`
  MODIFY `id_subscription` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websocket_connections`
--
ALTER TABLE `websocket_connections`
  MODIFY `id_connection` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websocket_events`
--
ALTER TABLE `websocket_events`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websocket_messages`
--
ALTER TABLE `websocket_messages`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websocket_message_delivery`
--
ALTER TABLE `websocket_message_delivery`
  MODIFY `id_delivery` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websocket_settings`
--
ALTER TABLE `websocket_settings`
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `websocket_statistics`
--
ALTER TABLE `websocket_statistics`
  MODIFY `id_stat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `_backup_product_categories`
--
ALTER TABLE `_backup_product_categories`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id_member`),
  ADD CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`),
  ADD CONSTRAINT `audit_logs_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`);

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
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE;

--
-- Constraints for table `customer_contacts`
--
ALTER TABLE `customer_contacts`
  ADD CONSTRAINT `customer_contacts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE;

--
-- Constraints for table `customer_feedback`
--
ALTER TABLE `customer_feedback`
  ADD CONSTRAINT `customer_feedback_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_feedback_ibfk_2` FOREIGN KEY (`responded_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `customer_groups`
--
ALTER TABLE `customer_groups`
  ADD CONSTRAINT `customer_groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `customer_group_memberships`
--
ALTER TABLE `customer_group_memberships`
  ADD CONSTRAINT `customer_group_memberships_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_group_memberships_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `customer_groups` (`id_group`) ON DELETE CASCADE;

--
-- Constraints for table `customer_interactions`
--
ALTER TABLE `customer_interactions`
  ADD CONSTRAINT `customer_interactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_interactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `customer_tag_assignments`
--
ALTER TABLE `customer_tag_assignments`
  ADD CONSTRAINT `customer_tag_assignments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_tag_assignments_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `customer_tags` (`id_tag`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_tag_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `fk_files_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_files_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_files_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `file_access_log`
--
ALTER TABLE `file_access_log`
  ADD CONSTRAINT `fk_file_access_log_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id_file`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_file_access_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `file_shares`
--
ALTER TABLE `file_shares`
  ADD CONSTRAINT `fk_file_shares_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id_file`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_file_shares_shared_by` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_file_shares_shared_with` FOREIGN KEY (`shared_with`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `file_versions`
--
ALTER TABLE `file_versions`
  ADD CONSTRAINT `fk_file_versions_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id_file`) ON DELETE CASCADE;

--
-- Constraints for table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD CONSTRAINT `financial_reports_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`),
  ADD CONSTRAINT `financial_reports_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`),
  ADD CONSTRAINT `financial_reports_ibfk_3` FOREIGN KEY (`generated_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`);

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
-- Constraints for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD CONSTRAINT `loyalty_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id_customer`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `members` (`id_member`);

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
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id_supplier`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`),
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`requested_by`) REFERENCES `members` (`id_member`),
  ADD CONSTRAINT `purchase_orders_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id_po`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`),
  ADD CONSTRAINT `purchase_order_items_ibfk_3` FOREIGN KEY (`supplier_product_id`) REFERENCES `supplier_products` (`id_supplier_product`);

--
-- Constraints for table `report_analytics`
--
ALTER TABLE `report_analytics`
  ADD CONSTRAINT `report_analytics_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `advanced_reports` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `report_history`
--
ALTER TABLE `report_history`
  ADD CONSTRAINT `report_history_ibfk_1` FOREIGN KEY (`report_template_id`) REFERENCES `report_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_history_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `report_schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `report_predictions`
--
ALTER TABLE `report_predictions`
  ADD CONSTRAINT `report_predictions_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `advanced_reports` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `report_recommendations`
--
ALTER TABLE `report_recommendations`
  ADD CONSTRAINT `report_recommendations_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `advanced_reports` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD CONSTRAINT `report_schedules_ibfk_1` FOREIGN KEY (`report_template_id`) REFERENCES `report_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_template_permissions`
--
ALTER TABLE `report_template_permissions`
  ADD CONSTRAINT `report_template_permissions_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `report_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_template_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `report_permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id_role`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id_permission`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_3` FOREIGN KEY (`granted_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `search_analytics`
--
ALTER TABLE `search_analytics`
  ADD CONSTRAINT `search_analytics_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
  ADD CONSTRAINT `search_analytics_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL;

--
-- Constraints for table `search_index`
--
ALTER TABLE `search_index`
  ADD CONSTRAINT `search_index_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
  ADD CONSTRAINT `search_index_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL,
  ADD CONSTRAINT `search_index_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `members` (`id_member`) ON DELETE SET NULL;

--
-- Constraints for table `search_queries`
--
ALTER TABLE `search_queries`
  ADD CONSTRAINT `search_queries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id_member`) ON DELETE SET NULL,
  ADD CONSTRAINT `search_queries_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
  ADD CONSTRAINT `search_queries_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL;

--
-- Constraints for table `search_results`
--
ALTER TABLE `search_results`
  ADD CONSTRAINT `search_results_ibfk_1` FOREIGN KEY (`search_query_id`) REFERENCES `search_queries` (`id_search_query`) ON DELETE CASCADE,
  ADD CONSTRAINT `search_results_ibfk_2` FOREIGN KEY (`search_index_id`) REFERENCES `search_index` (`id_search_index`) ON DELETE CASCADE;

--
-- Constraints for table `search_settings`
--
ALTER TABLE `search_settings`
  ADD CONSTRAINT `search_settings_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE,
  ADD CONSTRAINT `search_settings_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE;

--
-- Constraints for table `search_suggestions`
--
ALTER TABLE `search_suggestions`
  ADD CONSTRAINT `search_suggestions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
  ADD CONSTRAINT `search_suggestions_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL;

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
-- Constraints for table `supplier_categories`
--
ALTER TABLE `supplier_categories`
  ADD CONSTRAINT `supplier_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `supplier_categories` (`id_category`);

--
-- Constraints for table `supplier_category_assignments`
--
ALTER TABLE `supplier_category_assignments`
  ADD CONSTRAINT `supplier_category_assignments_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id_supplier`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_category_assignments_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `supplier_categories` (`id_category`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_category_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `supplier_contacts`
--
ALTER TABLE `supplier_contacts`
  ADD CONSTRAINT `supplier_contacts_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id_supplier`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  ADD CONSTRAINT `supplier_performance_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id_supplier`),
  ADD CONSTRAINT `supplier_performance_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD CONSTRAINT `supplier_products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id_supplier`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`) ON DELETE SET NULL;

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
-- Constraints for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id_member`) ON DELETE CASCADE;

--
-- Constraints for table `user_import_export_templates`
--
ALTER TABLE `user_import_export_templates`
  ADD CONSTRAINT `user_import_export_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id_member`) ON DELETE CASCADE;

--
-- Constraints for table `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  ADD CONSTRAINT `user_role_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id_member`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_role_assignments_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id_role`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_role_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `members` (`id_member`);

--
-- Constraints for table `websocket_channels`
--
ALTER TABLE `websocket_channels`
  ADD CONSTRAINT `websocket_channels_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `members` (`id_member`) ON DELETE SET NULL,
  ADD CONSTRAINT `websocket_channels_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
  ADD CONSTRAINT `websocket_channels_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL;

--
-- Constraints for table `websocket_channel_subscriptions`
--
ALTER TABLE `websocket_channel_subscriptions`
  ADD CONSTRAINT `websocket_channel_subscriptions_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `websocket_channels` (`id_channel`) ON DELETE CASCADE,
  ADD CONSTRAINT `websocket_channel_subscriptions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `members` (`id_member`) ON DELETE CASCADE;

--
-- Constraints for table `websocket_connections`
--
ALTER TABLE `websocket_connections`
  ADD CONSTRAINT `websocket_connections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id_member`) ON DELETE CASCADE,
  ADD CONSTRAINT `websocket_connections_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
  ADD CONSTRAINT `websocket_connections_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL;

--
-- Constraints for table `websocket_events`
--
ALTER TABLE `websocket_events`
  ADD CONSTRAINT `websocket_events_ibfk_1` FOREIGN KEY (`source_user_id`) REFERENCES `members` (`id_member`) ON DELETE SET NULL,
  ADD CONSTRAINT `websocket_events_ibfk_2` FOREIGN KEY (`target_user_id`) REFERENCES `members` (`id_member`) ON DELETE SET NULL,
  ADD CONSTRAINT `websocket_events_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE SET NULL,
  ADD CONSTRAINT `websocket_events_ibfk_4` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE SET NULL;

--
-- Constraints for table `websocket_messages`
--
ALTER TABLE `websocket_messages`
  ADD CONSTRAINT `websocket_messages_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `websocket_channels` (`id_channel`) ON DELETE CASCADE,
  ADD CONSTRAINT `websocket_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `members` (`id_member`) ON DELETE CASCADE,
  ADD CONSTRAINT `websocket_messages_ibfk_3` FOREIGN KEY (`recipient_id`) REFERENCES `members` (`id_member`) ON DELETE SET NULL;

--
-- Constraints for table `websocket_message_delivery`
--
ALTER TABLE `websocket_message_delivery`
  ADD CONSTRAINT `websocket_message_delivery_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `websocket_messages` (`id_message`) ON DELETE CASCADE;

--
-- Constraints for table `websocket_settings`
--
ALTER TABLE `websocket_settings`
  ADD CONSTRAINT `websocket_settings_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE,
  ADD CONSTRAINT `websocket_settings_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE;

--
-- Constraints for table `websocket_statistics`
--
ALTER TABLE `websocket_statistics`
  ADD CONSTRAINT `websocket_statistics_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id_company`) ON DELETE CASCADE,
  ADD CONSTRAINT `websocket_statistics_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id_branch`) ON DELETE CASCADE;

--
-- Constraints for table `_backup_product_categories`
--
ALTER TABLE `_backup_product_categories`
  ADD CONSTRAINT `_backup_product_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `_backup_product_categories` (`id_category`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
