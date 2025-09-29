-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table ai_erp.data_count_defect
CREATE TABLE IF NOT EXISTS `data_count_defect` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_defect` int DEFAULT NULL,
  `id_df` int DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `nama_defect` varchar(50) DEFAULT NULL,
  `qty` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_dfariat` (`id_df`) USING BTREE,
  KEY `id_defect` (`id_defect`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ai_erp.data_count_defect: ~7 rows (approximately)
INSERT INTO `data_count_defect` (`id`, `id_defect`, `id_df`, `brand`, `nama_defect`, `qty`) VALUES
	(3, 3, 39, 'ARIAT', 'POOR LEATHER', 3),
	(4, 4, 39, 'ARIAT', 'OFF CENTER', 1),
	(5, 5, 39, 'ARIAT', 'STAIN', 1),
	(6, 3, 40, 'ARIAT', 'POOR LEATHER', 2),
	(7, 4, 40, 'ARIAT', 'OFF CENTER', 3),
	(8, 5, 40, 'ARIAT', 'STAIN', 2),
	(9, 6, 40, 'ARIAT', 'contoh', 1);

-- Dumping structure for table ai_erp.data_defect
CREATE TABLE IF NOT EXISTS `data_defect` (
  `id_defect` int NOT NULL AUTO_INCREMENT,
  `nama_defect` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `desc_database` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_defect`),
  UNIQUE KEY `nama_defect` (`nama_defect`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ai_erp.data_defect: ~4 rows (approximately)
INSERT INTO `data_defect` (`id_defect`, `nama_defect`, `brand`, `desc_database`, `created_at`) VALUES
	(3, 'POOR LEATHER', 'ARIAT', 'poor_leather', '2025-09-23 08:32:49'),
	(4, 'OFF CENTER', 'ARIAT', 'off_center', '2025-09-23 08:42:48'),
	(5, 'STAIN', 'ARIAT', 'stain', '2025-09-24 02:36:27'),
	(6, 'contoh', 'ARIAT', 'contoh', '2025-09-25 07:41:14');

-- Dumping structure for table ai_erp.defect_spk
CREATE TABLE IF NOT EXISTS `defect_spk` (
  `id_spk` int NOT NULL AUTO_INCREMENT,
  `po_number` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `xfd` date NOT NULL,
  `brand` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `artcolor_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `total_qty` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  KEY `id_spk` (`id_spk`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table ai_erp.defect_spk: ~2 rows (approximately)
INSERT INTO `defect_spk` (`id_spk`, `po_number`, `xfd`, `brand`, `artcolor_name`, `total_qty`, `created_at`) VALUES
	(1, '343424223', '1111-11-11', 'ARIAT', '10074223 GREY HIPPO PRINT / BURGUNDY HAZE', '1001', '2025-09-23 08:05:45'),
	(2, '4500182604', '2222-02-22', 'ARIAT', '10016292 BLACK DEERTAN', '91', '2025-09-23 08:08:56');

-- Dumping structure for table ai_erp.menus
CREATE TABLE IF NOT EXISTS `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `uq_menus_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ai_erp.menus: ~8 rows (approximately)
INSERT INTO `menus` (`id`, `name`, `slug`, `url`, `icon`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
	(1, 'Dashboard', 'dashboard', 'dashboard', 'fa fa-dashboard', 1, 1, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(2, 'User Management', 'user-management', NULL, 'fa fa-laptop', 1, 2, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(3, 'Master Data', 'master-data', NULL, 'fa fa-laptop', 1, 3, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(5, 'SPK', 'SPK', NULL, 'fa fa-list', 1, 4, '2025-09-17 11:44:54', '2025-09-17 11:46:00'),
	(6, 'Warehouse', 'warehouse', NULL, 'fa fa-shopping-cart', 1, 5, '2025-09-18 12:50:47', '2025-09-18 12:53:35'),
	(8, 'Production', 'production', NULL, 'fa fa-laptop', 1, 6, '2025-09-19 09:25:16', '2025-09-19 09:25:16'),
	(9, 'Executive', 'executive', NULL, 'fa fa-tablet', 1, 7, '2025-09-22 15:22:34', '2025-09-22 15:22:34'),
	(10, 'Ariat Page', 'ariat-page', NULL, 'fa fa-asterisk', 1, 8, '2025-09-23 09:22:29', '2025-09-23 09:34:18');

-- Dumping structure for table ai_erp.submenus
CREATE TABLE IF NOT EXISTS `submenus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `uq_submenus_menu_sort` (`menu_id`,`sort_order`),
  CONSTRAINT `fk_submenus_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ai_erp.submenus: ~25 rows (approximately)
INSERT INTO `submenus` (`id`, `menu_id`, `name`, `slug`, `url`, `icon`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
	(1, 2, 'List User', 'listuser', 'listuser', 'fa fa-angle-right', 1, 1, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(2, 2, 'Role Access', 'roleaccess', 'roleaccess', 'fa fa-angle-right', 1, 2, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(3, 3, 'Category', 'category', 'category', 'fa fa-angle-right', 1, 1, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(4, 3, 'Item', 'item', 'item', 'fa fa-angle-right', 1, 2, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(5, 3, 'Unit', 'unit', 'unit', 'fa fa-angle-right', 1, 3, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(6, 3, 'Brand', 'brand', 'brand', 'fa fa-angle-right', 1, 4, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(7, 3, 'Departement', 'departement', 'departement', 'fa fa-angle-right', 1, 5, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(8, 3, 'Supplier', 'supplier', 'supplier', 'fa fa-angle-right', 1, 6, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(9, 3, 'Menu Mgmt', 'menu', 'menu', 'fa fa-angle-right', 1, 7, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(10, 3, 'Sub Menu', 'submenu', 'submenu', 'fa fa-angle-right', 1, 8, '2025-09-16 09:11:43', '2025-09-16 09:11:43'),
	(12, 5, 'Build Of Material', 'build-of-material', 'bom', ' fa fa-angle-right', 1, 1, '2025-09-17 11:47:07', '2025-09-18 10:24:38'),
	(13, 5, 'Work Order', 'work-order', 'wo', ' fa fa-angle-right', 1, 2, '2025-09-18 10:24:09', '2025-09-18 10:24:44'),
	(14, 6, 'Stock Item', 'stock-item', 'stock', ' fa fa-angle-right', 1, 1, '2025-09-18 12:54:39', '2025-09-18 12:54:39'),
	(15, 6, 'Checkin Item', 'checkin-item', 'checkin', ' fa fa-angle-right', 1, 2, '2025-09-18 12:55:10', '2025-09-18 12:55:10'),
	(16, 6, 'Checkout Item', 'checkout-item', 'checkout', ' fa fa-angle-right', 1, 3, '2025-09-18 12:55:49', '2025-09-18 12:55:49'),
	(17, 8, 'Request Order', 'request-order', 'Ro', 'fa fa-angle-right', 1, 1, '2025-09-19 09:26:16', '2025-09-19 09:26:16'),
	(18, 8, 'Laporan Production', 'laporan-production', 'Production', ' fa fa-angle-right', 1, 2, '2025-09-19 09:27:17', '2025-09-19 09:27:17'),
	(19, 9, 'PPS', 'pps', 'pps', ' fa fa-angle-right', 1, 1, '2025-09-22 15:23:04', '2025-09-22 15:23:04'),
	(20, 10, 'Daily Report Defect', 'daily-report', 'daily', 'fa fa-male', 1, 1, '2025-09-23 09:40:06', '2025-09-24 13:15:58'),
	(21, 10, 'Report by P.O.', 'report-po', 'defect_report_po', 'fa fa-clipboard', 1, 2, '2025-09-23 09:52:25', '2025-09-25 16:46:27'),
	(22, 10, 'Incoming Defect Pict', 'defect-pict', 'ariat_defect_pict', 'fa fa-camera', 1, 3, '2025-09-23 09:54:32', '2025-09-23 10:05:17'),
	(23, 10, 'Display', 'display', 'ariat_display', 'fa fa-laptop', 1, 4, '2025-09-23 09:58:47', '2025-09-23 10:02:19'),
	(24, 10, 'Action Plan', 'action-plan', 'action_plan', 'fa fa-pencil', 1, 5, '2025-09-23 10:01:55', '2025-09-23 10:01:55'),
	(25, 10, 'Data Defect', 'data-defect', 'defect', 'fa fa-unlink', 1, 6, '2025-09-23 11:44:38', '2025-09-23 12:55:44'),
	(26, 10, 'SPK', 'spk', 'spk_defect', 'fa fa-list', 1, 7, '2025-09-23 14:13:26', '2025-09-23 14:45:05');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
