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
REPLACE INTO `wo_l1` (`id`, `bom_l1_id`, `item_id`, `unit_id`, `brand_id`, `art_color`, `date_order`, `x_factory_date`, `no_wo`, `kategori_wo`, `total_size_qty`, `notes`, `created_at`) VALUES
	(3, 26, 1, 2, 1, 'wed - 123213', '2025-09-17', '2025-09-30', 'wo-001', 'Cementing', 6.000000, '', '2025-09-18 09:19:58'),
	(4, 27, 1, 2, 1, 'test', '2025-09-22', '2025-09-30', 'wo-002', 'Injection', 10.000000, '', '2025-09-22 06:17:49'),
	(5, 28, 2, 2, 2, 'EG555 - Black', '2025-09-22', '2025-09-30', 'test', 'Injection', 4.000000, '', '2025-09-22 09:58:14'),
	(6, 28, 2, 2, 2, 'EG555 - Black', '2025-09-23', '2025-09-30', 'new', 'Injection', 4.000000, '', '2025-09-23 02:47:48');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
