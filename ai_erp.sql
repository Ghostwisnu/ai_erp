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

-- Dumping structure for table ai_erp.wo_l1
CREATE TABLE IF NOT EXISTS `wo_l1` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `bom_l1_id` bigint NOT NULL,
  `item_id` bigint NOT NULL,
  `unit_id` bigint NOT NULL,
  `brand_id` bigint NOT NULL,
  `art_color` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_order` date NOT NULL,
  `x_factory_date` date NOT NULL,
  `no_wo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori_wo` enum('Injection','Cementing','Stitchdown') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Injection',
  `total_size_qty` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wo_l1_boml1` (`bom_l1_id`),
  KEY `idx_wo_l1_item` (`item_id`),
  KEY `idx_wo_l1_brand` (`brand_id`),
  KEY `idx_wo_l1_unit` (`unit_id`),
  CONSTRAINT `fk_wo_l1_bom_l1` FOREIGN KEY (`bom_l1_id`) REFERENCES `bom_l1` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ai_erp.wo_l1: ~4 rows (approximately)
DELETE FROM `wo_l1`;
INSERT INTO `wo_l1` (`id`, `bom_l1_id`, `item_id`, `unit_id`, `brand_id`, `art_color`, `date_order`, `x_factory_date`, `no_wo`, `kategori_wo`, `total_size_qty`, `notes`, `created_at`) VALUES
	(3, 26, 1, 2, 1, 'wed - 123213', '2025-09-17', '2025-09-30', 'wo-001', 'Cementing', 6.000000, '', '2025-09-18 09:19:58'),
	(4, 27, 1, 2, 1, 'test', '2025-09-22', '2025-09-30', 'wo-002', 'Injection', 10.000000, '', '2025-09-22 06:17:49'),
	(5, 28, 2, 2, 2, 'EG555 - Black', '2025-09-22', '2025-09-30', 'test', 'Injection', 4.000000, '', '2025-09-22 09:58:14'),
	(6, 28, 2, 2, 2, 'EG555 - Black', '2025-09-23', '2025-09-30', 'new', 'Injection', 4.000000, '', '2025-09-23 02:47:48');

-- Dumping structure for table ai_erp.wo_l2
CREATE TABLE IF NOT EXISTS `wo_l2` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `wo_l1_id` bigint NOT NULL,
  `item_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wo_l2_wol1` (`wo_l1_id`),
  KEY `idx_wo_l2_item` (`item_id`),
  CONSTRAINT `fk_wo_l2_wo_l1` FOREIGN KEY (`wo_l1_id`) REFERENCES `wo_l1` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ai_erp.wo_l2: ~8 rows (approximately)
DELETE FROM `wo_l2`;
INSERT INTO `wo_l2` (`id`, `wo_l1_id`, `item_id`) VALUES
	(4, 3, 6),
	(5, 3, 7),
	(6, 4, 6),
	(7, 4, 7),
	(8, 4, 8),
	(9, 4, 9),
	(10, 4, 10),
	(11, 4, 11),
	(12, 5, 12),
	(13, 6, 12);

-- Dumping structure for table ai_erp.wo_l3
CREATE TABLE IF NOT EXISTS `wo_l3` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `wo_l2_id` bigint NOT NULL,
  `item_id` bigint NOT NULL,
  `consumption` decimal(18,4) NOT NULL,
  `required_qty` decimal(18,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wo_l3_wol2` (`wo_l2_id`),
  KEY `idx_wo_l3_item` (`item_id`),
  CONSTRAINT `fk_wo_l3_wo_l2` FOREIGN KEY (`wo_l2_id`) REFERENCES `wo_l2` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ai_erp.wo_l3: ~20 rows (approximately)
DELETE FROM `wo_l3`;
INSERT INTO `wo_l3` (`id`, `wo_l2_id`, `item_id`, `consumption`, `required_qty`) VALUES
	(5, 4, 24, 2.0000, 12.00),
	(6, 4, 25, 2.0000, 12.00),
	(7, 5, 6, 1.0000, 6.00),
	(8, 5, 28, 2.0000, 12.00),
	(9, 6, 24, 1.0000, 10.00),
	(10, 6, 25, 1.0000, 10.00),
	(11, 7, 6, 1.0000, 10.00),
	(12, 7, 33, 1.0000, 10.00),
	(13, 8, 7, 1.0000, 10.00),
	(14, 8, 26, 1.0000, 10.00),
	(15, 9, 8, 1.0000, 10.00),
	(16, 9, 30, 1.0000, 10.00),
	(17, 10, 9, 1.0000, 10.00),
	(18, 10, 40, 1.0000, 10.00),
	(19, 11, 10, 1.0000, 10.00),
	(20, 11, 50, 1.0000, 10.00),
	(21, 12, 25, 1.0000, 4.00),
	(22, 12, 26, 1.0000, 4.00),
	(23, 13, 25, 1.0000, 4.00),
	(24, 13, 26, 1.0000, 4.00);

-- Dumping structure for table ai_erp.wo_sizes
CREATE TABLE IF NOT EXISTS `wo_sizes` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `wo_l1_id` bigint NOT NULL,
  `size_id` bigint NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_wo_sizes_wol1` (`wo_l1_id`),
  KEY `idx_wo_sizes_size` (`size_id`),
  CONSTRAINT `fk_wo_sizes_wo_l1` FOREIGN KEY (`wo_l1_id`) REFERENCES `wo_l1` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ai_erp.wo_sizes: ~20 rows (approximately)
DELETE FROM `wo_sizes`;
INSERT INTO `wo_sizes` (`id`, `wo_l1_id`, `size_id`, `qty`) VALUES
	(14, 3, 1, 2),
	(15, 3, 8, 2),
	(16, 3, 9, 2),
	(17, 4, 1, 1),
	(18, 4, 8, 1),
	(19, 4, 9, 1),
	(20, 4, 11, 2),
	(21, 4, 12, 2),
	(22, 4, 14, 1),
	(23, 4, 15, 1),
	(24, 4, 17, 1),
	(25, 5, 2, 1),
	(26, 5, 3, 1),
	(27, 5, 4, 1),
	(28, 5, 5, 1),
	(29, 6, 2, 1),
	(30, 6, 3, 1),
	(31, 6, 4, 1),
	(32, 6, 5, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
