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


-- Dumping database structure for absensi
CREATE DATABASE IF NOT EXISTS `absensi` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `absensi`;

-- Dumping structure for table absensi.activity_logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.activity_logs: ~0 rows (approximately)

-- Dumping structure for table absensi.announcements
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_created_by_foreign` (`created_by`),
  CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.announcements: ~0 rows (approximately)
INSERT INTO `announcements` (`id`, `title`, `content`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
	(1, 'Pengumuman Absensi', 'Mohon seluruh karyawan melakukan absensi sesuai jadwal shift.', 1, 1, '2026-09-08 08:38:26', '2026-09-08 08:38:26'),
	(2, 'Kedisiplinan Karyawan', 'Pastikan datang tepat waktu sesuai jadwal kerja.', 1, 1, '2026-09-08 08:38:26', '2026-09-08 08:38:26'),
	(3, 'Pemeliharaan Sistem', 'Sistem absensi akan menjalani pemeliharaan pada akhir pekan.', 1, 1, '2026-09-08 08:38:26', '2026-09-08 08:38:26');

-- Dumping structure for table absensi.attendances
CREATE TABLE IF NOT EXISTS `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `shift_id` bigint unsigned DEFAULT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `lat_in` decimal(10,8) DEFAULT NULL,
  `lng_in` decimal(11,8) DEFAULT NULL,
  `accuracy_in` int DEFAULT NULL,
  `address_in` text COLLATE utf8mb4_unicode_ci,
  `lat_out` decimal(10,8) DEFAULT NULL,
  `lng_out` decimal(11,8) DEFAULT NULL,
  `accuracy_out` int DEFAULT NULL,
  `address_out` text COLLATE utf8mb4_unicode_ci,
  `distance_in_meter` int DEFAULT NULL,
  `distance_out_meter` int DEFAULT NULL,
  `photo_in` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_out` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('hadir','terlambat','pulang_cepat','alpha') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hadir',
  `late_minutes` int NOT NULL DEFAULT '0',
  `overtime_hours` decimal(4,2) NOT NULL DEFAULT '0.00',
  `is_fake_gps` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_user_id_date_unique` (`user_id`,`date`),
  KEY `attendances_shift_id_foreign` (`shift_id`),
  KEY `attendances_date_index` (`date`),
  KEY `attendances_status_index` (`status`),
  CONSTRAINT `attendances_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.attendances: ~3 rows (approximately)
INSERT INTO `attendances` (`id`, `user_id`, `date`, `shift_id`, `check_in`, `check_out`, `lat_in`, `lng_in`, `accuracy_in`, `address_in`, `lat_out`, `lng_out`, `accuracy_out`, `address_out`, `distance_in_meter`, `distance_out_meter`, `photo_in`, `photo_out`, `status`, `late_minutes`, `overtime_hours`, `is_fake_gps`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
	(1, 1, '2026-09-08', 1, '2026-09-08 07:55:00', '2026-09-08 17:05:00', -6.20880000, 106.84560000, 10, 'Head Office', -6.20880000, 106.84560000, 10, 'Head Office', 20, 15, NULL, NULL, 'hadir', 0, 0.00, 0, '192.168.1.10', 'Mozilla/5.0', '2026-09-08 08:38:17', '2026-09-08 08:38:17'),
	(2, 2, '2026-09-08', 2, '2026-09-08 13:20:00', '2026-09-08 22:00:00', -6.20880000, 106.84560000, 12, 'Head Office', -6.20880000, 106.84560000, 12, 'Head Office', 30, 20, NULL, NULL, 'terlambat', 20, 0.00, 0, '192.168.1.11', 'Mozilla/5.0', '2026-09-08 08:38:17', '2026-09-08 08:38:17'),
	(3, 4, '2026-09-08', 1, NULL, NULL, -6.74130281, 108.22608601, 88, 'Branch Office', -6.74130275, 108.22605929, 87, 'Branch Office', 52, 54, 'attendances/4_2026-09-08_in.jpg', 'attendances/4_2026-09-08_out.jpg', 'terlambat', 88, 0.00, 0, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '2026-09-08 08:38:17', '2026-09-08 02:44:36');

-- Dumping structure for table absensi.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.departments: ~0 rows (approximately)
INSERT INTO `departments` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'Human Resources', 'Departemen sumber daya manusia', '2026-09-08 08:37:49', '2026-09-08 08:37:49'),
	(2, 'IT', 'Departemen teknologi informasi', '2026-09-08 08:37:49', '2026-09-08 08:37:49'),
	(3, 'Finance', 'Departemen keuangan', '2026-09-08 08:37:49', '2026-09-08 08:37:49');

-- Dumping structure for table absensi.employees
CREATE TABLE IF NOT EXISTS `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `position_id` bigint unsigned DEFAULT NULL,
  `shift_id` bigint unsigned DEFAULT NULL,
  `office_location_id` bigint unsigned DEFAULT NULL,
  `employee_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `join_date` date NOT NULL,
  `employment_status` enum('kontrak','tetap','magang','probation','resigned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kontrak',
  `contract_end_date` date DEFAULT NULL,
  `resign_date` date DEFAULT NULL,
  `bank_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_kes` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_tk` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_user_id_unique` (`user_id`),
  UNIQUE KEY `employees_employee_code_unique` (`employee_code`),
  KEY `employees_department_id_foreign` (`department_id`),
  KEY `employees_position_id_foreign` (`position_id`),
  KEY `employees_shift_id_foreign` (`shift_id`),
  KEY `employees_office_location_id_foreign` (`office_location_id`),
  CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_office_location_id_foreign` FOREIGN KEY (`office_location_id`) REFERENCES `office_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.employees: ~0 rows (approximately)
INSERT INTO `employees` (`id`, `user_id`, `department_id`, `position_id`, `shift_id`, `office_location_id`, `employee_code`, `phone`, `address`, `join_date`, `employment_status`, `contract_end_date`, `resign_date`, `bank_name`, `bank_account`, `bpjs_kes`, `bpjs_tk`, `is_active`, `photo`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, 1, 1, 'EMP001', '081234567001', 'Jakarta', '2025-01-10', 'tetap', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'photos/employees/bZ3TXD0NLSFFhYTrDzA5bPXBR2xRyx6TNswyZ9pS.png', '2026-09-08 08:38:09', '2026-09-08 02:25:55'),
	(2, 2, 2, 2, 2, 1, 'EMP002', '081234567002', 'Tangerang', '2025-02-15', 'resigned', NULL, '2026-09-08', NULL, NULL, NULL, NULL, 0, NULL, '2026-09-08 08:38:09', '2026-09-08 02:11:00'),
	(3, 4, 2, 3, 1, 2, 'EMP003', '081234567003', 'Tangerang', '2025-03-20', 'kontrak', NULL, NULL, 'Mandiri', '73981273927395454', NULL, NULL, 1, 'photos/employees/LoEwbnWMWj9Cc41yXPipX9fjk3N3ThU7FeXwdEtn.png', '2026-09-08 08:38:09', '2026-09-08 06:57:26');

-- Dumping structure for table absensi.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table absensi.leaves
CREATE TABLE IF NOT EXISTS `leaves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `supervisor_id` bigint unsigned DEFAULT NULL,
  `backup_user_id` bigint unsigned DEFAULT NULL,
  `leave_type_id` bigint unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_supervisor` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `status_hrd` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `final_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `supervisor_note` text COLLATE utf8mb4_unicode_ci,
  `hrd_note` text COLLATE utf8mb4_unicode_ci,
  `approved_by_supervisor_id` bigint unsigned DEFAULT NULL,
  `approved_by_hrd_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leaves_user_id_foreign` (`user_id`),
  KEY `leaves_leave_type_id_foreign` (`leave_type_id`),
  KEY `leaves_approved_by_supervisor_id_foreign` (`approved_by_supervisor_id`),
  KEY `leaves_approved_by_hrd_id_foreign` (`approved_by_hrd_id`),
  KEY `leaves_supervisor_id_foreign` (`supervisor_id`),
  KEY `leaves_backup_user_id_foreign` (`backup_user_id`),
  CONSTRAINT `leaves_approved_by_hrd_id_foreign` FOREIGN KEY (`approved_by_hrd_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leaves_approved_by_supervisor_id_foreign` FOREIGN KEY (`approved_by_supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leaves_backup_user_id_foreign` FOREIGN KEY (`backup_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leaves_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leaves_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leaves_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.leaves: ~0 rows (approximately)
INSERT INTO `leaves` (`id`, `user_id`, `supervisor_id`, `backup_user_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `document_path`, `status_supervisor`, `status_hrd`, `final_status`, `supervisor_note`, `hrd_note`, `approved_by_supervisor_id`, `approved_by_hrd_id`, `created_at`, `updated_at`) VALUES
	(1, 3, NULL, NULL, 1, '2026-09-10', '2026-09-11', 2, 'Keperluan keluarga', NULL, 'approved', 'approved', 'approved', 'Disetujui', 'Disetujui HRD', 2, 1, '2026-09-08 08:38:21', '2026-09-08 08:38:21'),
	(2, 2, NULL, NULL, 2, '2026-09-15', '2026-09-15', 1, 'Sakit', NULL, 'approved', 'pending', 'pending', 'Disetujui supervisor', NULL, 2, NULL, '2026-09-08 08:38:21', '2026-09-08 08:38:21'),
	(3, 3, NULL, NULL, 1, '2026-09-20', '2026-09-20', 1, 'Keperluan pribadi', NULL, 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 08:38:21', '2026-09-08 08:38:21'),
	(4, 4, NULL, NULL, 1, '2026-09-09', '2026-09-10', 2, 'cuti', 'leave_docs/hR0N8Xqx2wJAGLu2r1fDdb8nQ3gy2X0IlBgApYNH.png', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 01:55:44', '2026-09-08 01:55:44');

-- Dumping structure for table absensi.leave_types
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quota_days` int NOT NULL DEFAULT '0',
  `is_paid` tinyint(1) NOT NULL DEFAULT '1',
  `requires_document` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.leave_types: ~0 rows (approximately)
INSERT INTO `leave_types` (`id`, `name`, `quota_days`, `is_paid`, `requires_document`, `created_at`, `updated_at`) VALUES
	(1, 'Cuti Tahunan', 12, 1, 0, '2026-09-08 08:38:13', '2026-09-08 08:38:13'),
	(2, 'Cuti Sakit', 12, 1, 1, '2026-09-08 08:38:13', '2026-09-08 08:38:13'),
	(3, 'Cuti Melahirkan', 90, 1, 1, '2026-09-08 08:38:13', '2026-09-08 08:38:13');

-- Dumping structure for table absensi.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.migrations: ~0 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2019_08_19_000000_create_failed_jobs_table', 1),
	(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(5, '2026_09_08_080712_create_permission_tables', 1),
	(6, '2026_09_08_081000_create_departments_table', 1),
	(7, '2026_09_08_081100_create_positions_table', 1),
	(8, '2026_09_08_081200_create_shifts_table', 1),
	(9, '2026_09_08_081300_create_office_locations_table', 1),
	(10, '2026_09_08_081400_create_employees_table', 1),
	(11, '2026_09_08_081500_create_attendances_table', 1),
	(12, '2026_09_08_081600_create_leave_types_table', 1),
	(13, '2026_09_08_081700_create_leaves_table', 1),
	(14, '2026_09_08_081800_create_payrolls_table', 1),
	(15, '2026_09_08_081900_create_announcements_table', 1),
	(16, '2026_09_08_090000_add_supervisor_backup_to_leaves', 2);

-- Dumping structure for table absensi.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.model_has_permissions: ~0 rows (approximately)

-- Dumping structure for table absensi.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.model_has_roles: ~0 rows (approximately)

-- Dumping structure for table absensi.office_locations
CREATE TABLE IF NOT EXISTS `office_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius_meter` int NOT NULL DEFAULT '100',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.office_locations: ~0 rows (approximately)
INSERT INTO `office_locations` (`id`, `name`, `address`, `latitude`, `longitude`, `radius_meter`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Head Office', 'Jl. Sudirman No. 10, Jakarta', -6.20880000, 106.84560000, 100, 1, '2026-09-08 08:37:59', '2026-09-08 08:37:59'),
	(2, 'Branch Office', 'Jl. Gatot Subroto No. 20, Jakarta', -6.74140529, 108.45500442, 100, 1, '2026-09-08 08:37:59', '2026-09-08 07:04:26'),
	(3, 'Factory', 'Jl. Industri No. 5, Tangerang', -6.17830000, 106.63190000, 150, 1, '2026-09-08 08:37:59', '2026-09-08 08:37:59');

-- Dumping structure for table absensi.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table absensi.payrolls
CREATE TABLE IF NOT EXISTS `payrolls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `period` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','locked','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total_employees` int NOT NULL DEFAULT '0',
  `total_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_by` bigint unsigned DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payrolls_period_unique` (`period`),
  KEY `payrolls_created_by_foreign` (`created_by`),
  CONSTRAINT `payrolls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.payrolls: ~0 rows (approximately)
INSERT INTO `payrolls` (`id`, `period`, `start_date`, `end_date`, `status`, `total_employees`, `total_amount`, `created_by`, `locked_at`, `created_at`, `updated_at`) VALUES
	(1, '2026-07', '2026-07-01', '2026-07-31', 'locked', 3, 19000000.00, 1, '2026-07-08 09:55:25', '2026-09-08 08:38:30', '2026-09-08 02:55:25'),
	(2, '2026-08', '2026-08-01', '2026-08-31', 'locked', 2, 12570000.00, 1, '2026-08-08 09:55:25', '2026-09-08 08:38:30', '2026-09-08 02:55:25'),
	(3, '2026-09', '2026-09-01', '2026-09-30', 'draft', 2, 3090000.00, 1, NULL, '2026-09-08 08:38:30', '2026-09-08 03:02:12');

-- Dumping structure for table absensi.payroll_details
CREATE TABLE IF NOT EXISTS `payroll_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `allowances` json DEFAULT NULL,
  `overtime_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `bonus` decimal(12,2) NOT NULL DEFAULT '0.00',
  `thr` decimal(12,2) NOT NULL DEFAULT '0.00',
  `deductions` json DEFAULT NULL,
  `gross_salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `attendance_summary` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `slip_pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_details_payroll_id_user_id_unique` (`payroll_id`,`user_id`),
  KEY `payroll_details_user_id_foreign` (`user_id`),
  CONSTRAINT `payroll_details_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.payroll_details: ~0 rows (approximately)
INSERT INTO `payroll_details` (`id`, `payroll_id`, `user_id`, `basic_salary`, `allowances`, `overtime_pay`, `bonus`, `thr`, `deductions`, `gross_salary`, `total_deduction`, `net_salary`, `attendance_summary`, `notes`, `slip_pdf_path`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 5000000.00, '{"transport": 500000}', 0.00, 500000.00, 0.00, '{"bpjs": 100000}', 6000000.00, 100000.00, 5900000.00, '{"alpha": 0, "hadir": 22, "terlambat": 0}', NULL, NULL, '2026-09-08 08:38:34', '2026-09-08 08:38:34'),
	(2, 1, 2, 8000000.00, '{"transport": 500000}', 250000.00, 500000.00, 0.00, '{"bpjs": 150000}', 9250000.00, 150000.00, 9100000.00, '{"alpha": 0, "hadir": 22, "terlambat": 1}', NULL, NULL, '2026-09-08 08:38:34', '2026-09-08 08:38:34'),
	(3, 1, 4, 6000000.00, '{"transport": 500000}', 0.00, 300000.00, 0.00, '{"bpjs": 120000}', 6800000.00, 120000.00, 6680000.00, '{"alpha": 0, "hadir": 21, "terlambat": 1}', NULL, NULL, '2026-09-08 08:38:34', '2026-09-08 08:38:34'),
	(4, 3, 1, 5000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 4350000, "bpjs_tk": 100000, "bpjs_kes": 50000, "terlambat": 0}', 6000000.00, 4500000.00, 1500000.00, '{"alpha": 29, "hadir": 1, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-08 02:54:56', '2026-09-08 02:54:56'),
	(5, 3, 4, 6000000.00, '{"makan": "500000", "jabatan": "200000", "transport": "300000"}', 0.00, 0.00, 0.00, '{"lain": "0", "alpha": "4350000", "bpjs_tk": "120000", "bpjs_kes": "60000", "terlambat": "880000"}', 7000000.00, 5410000.00, 1590000.00, '{"alpha": 29, "hadir": 1, "terlambat": 1, "late_minutes": 88, "overtime_hours": 0}', NULL, NULL, '2026-09-08 02:54:56', '2026-09-08 03:02:01'),
	(6, 2, 1, 5000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 0, "bpjs_tk": 100000, "bpjs_kes": 50000, "terlambat": 50000}', 6000000.00, 200000.00, 5800000.00, '{"alpha": 0, "hadir": 22, "terlambat": 1, "late_minutes": 15, "overtime_hours": 2}', NULL, NULL, '2026-09-08 02:55:25', '2026-09-08 02:55:25'),
	(7, 2, 4, 6000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 0, "bpjs_tk": 120000, "bpjs_kes": 60000, "terlambat": 50000}', 7000000.00, 230000.00, 6770000.00, '{"alpha": 0, "hadir": 22, "terlambat": 1, "late_minutes": 15, "overtime_hours": 2}', NULL, NULL, '2026-09-08 02:55:25', '2026-09-08 02:55:25');

-- Dumping structure for table absensi.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.permissions: ~0 rows (approximately)

-- Dumping structure for table absensi.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.personal_access_tokens: ~0 rows (approximately)

-- Dumping structure for table absensi.positions
CREATE TABLE IF NOT EXISTS `positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `basic_salary_default` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `positions_department_id_foreign` (`department_id`),
  CONSTRAINT `positions_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.positions: ~0 rows (approximately)
INSERT INTO `positions` (`id`, `department_id`, `name`, `basic_salary_default`, `created_at`, `updated_at`) VALUES
	(1, 1, 'HR Staff', 5000000.00, '2026-09-08 08:37:54', '2026-09-08 08:37:54'),
	(2, 2, 'Software Engineer', 8000000.00, '2026-09-08 08:37:54', '2026-09-08 08:37:54'),
	(3, 3, 'Finance Staff', 6000000.00, '2026-09-08 08:37:54', '2026-09-08 02:05:34');

-- Dumping structure for table absensi.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.roles: ~0 rows (approximately)

-- Dumping structure for table absensi.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.role_has_permissions: ~0 rows (approximately)

-- Dumping structure for table absensi.shifts
CREATE TABLE IF NOT EXISTS `shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `tolerance_late` int NOT NULL DEFAULT '15',
  `is_overnight` tinyint(1) NOT NULL DEFAULT '0',
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0d6efd',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.shifts: ~0 rows (approximately)
INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `tolerance_late`, `is_overnight`, `color`, `created_at`, `updated_at`) VALUES
	(1, 'Shift Pagi', '08:00:00', '17:00:00', 15, 0, '#0d6efd', '2026-09-08 08:34:31', '2026-09-08 08:34:31'),
	(2, 'Shift Siang', '13:00:00', '22:00:00', 15, 0, '#198754', '2026-09-08 08:34:31', '2026-09-08 08:34:31'),
	(3, 'Shift Malam', '22:00:00', '07:00:00', 15, 1, '#6f42c1', '2026-09-08 08:34:31', '2026-09-08 08:34:31');

-- Dumping structure for table absensi.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('hrd','supervisor','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nik_unique` (`nik`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.users: ~4 rows (approximately)
INSERT INTO `users` (`id`, `name`, `nik`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Budi Santoso', '3273010101900001', 'budi.hrd@example.com', 'hrd', '2026-09-08 08:26:14', '$2y$10$JzpncilAmPo5HAtIJfIniO09mr.1V692vzS6yw0mA/jdQHDbXchdS', NULL, '2026-09-08 08:26:14', '2026-09-08 08:26:14'),
	(2, 'Andi Wijaya', '3273010202910002', 'andi.supervisor@example.com', 'supervisor', '2026-09-08 08:26:14', '$2y$10$JzpncilAmPo5HAtIJfIniO09mr.1V692vzS6yw0mA/jdQHDbXchdS', NULL, '2026-09-08 08:26:14', '2026-09-08 08:26:14'),
	(3, 'Citra Lestari', '3273010303950003', 'citra.staff@example.com', 'staff', '2026-09-08 08:26:14', '$2y$10$JzpncilAmPo5HAtIJfIniO09mr.1V692vzS6yw0mA/jdQHDbXchdS', NULL, '2026-09-08 08:26:14', '2026-09-08 08:26:14'),
	(4, 'Diky Anwar', '3273010303950004', 'dikyanwar22@gmail.com', 'staff', NULL, '$2y$10$JzpncilAmPo5HAtIJfIniO09mr.1V692vzS6yw0mA/jdQHDbXchdS', NULL, '2026-09-08 01:33:22', '2026-09-08 01:33:22');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
