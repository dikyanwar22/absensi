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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.announcements: ~0 rows (approximately)

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.attendances: ~2 rows (approximately)
INSERT INTO `attendances` (`id`, `user_id`, `date`, `shift_id`, `check_in`, `check_out`, `lat_in`, `lng_in`, `accuracy_in`, `address_in`, `lat_out`, `lng_out`, `accuracy_out`, `address_out`, `distance_in_meter`, `distance_out_meter`, `photo_in`, `photo_out`, `status`, `late_minutes`, `overtime_hours`, `is_fake_gps`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
	(2, 6, '2026-09-09', 1, '2026-09-09 01:13:42', NULL, -6.74127736, 108.22598543, 105, NULL, NULL, NULL, NULL, NULL, 90, NULL, 'attendances/6_2026-09-09_in.jpg', NULL, 'hadir', 0, 0.00, 0, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 18:13:42', '2026-09-08 18:13:42');

-- Dumping structure for table absensi.company_settings
CREATE TABLE IF NOT EXISTS `company_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PT. AbsensiKu',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.company_settings: ~1 rows (approximately)
INSERT INTO `company_settings` (`id`, `name`, `address`, `phone`, `email`, `logo_path`, `website`, `created_at`, `updated_at`) VALUES
	(1, 'PT Dicky Anwar', 'Jl. Contoh No.1, Jakarta - Indonesia', '021-12345678', 'dicky@gmail.com', NULL, NULL, '2026-09-10 03:15:42', '2026-09-10 03:16:03');

-- Dumping structure for table absensi.deduction_types
CREATE TABLE IF NOT EXISTS `deduction_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deduction_types_name_unique` (`name`),
  KEY `deduction_types_created_by_foreign` (`created_by`),
  KEY `deduction_types_is_active_index` (`is_active`),
  CONSTRAINT `deduction_types_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.deduction_types: ~3 rows (approximately)
INSERT INTO `deduction_types` (`id`, `name`, `default_amount`, `is_active`, `description`, `created_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
	(1, 'Union Fee', 50000.00, 1, 'Tes', NULL, NULL, '2026-09-10 02:59:17', '2026-09-10 03:06:41'),
	(2, 'Potongan A', 150000.00, 1, NULL, NULL, NULL, '2026-09-10 02:59:17', '2026-09-10 03:07:13'),
	(3, 'Potongan B', 13000.00, 1, NULL, 1, NULL, '2026-09-10 03:07:10', '2026-09-10 03:07:10');

-- Dumping structure for table absensi.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.departments: ~2 rows (approximately)
INSERT INTO `departments` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'IT', 'Teknologi Informasi', '2026-09-08 17:56:16', '2026-09-08 17:56:16'),
	(2, 'HRD', 'Human Resource', '2026-09-08 17:56:16', '2026-09-08 17:56:16'),
	(3, 'Produksi', 'Produksi', '2026-09-08 17:56:16', '2026-09-08 17:56:16'),
	(4, 'Finance', 'Finance', '2026-09-08 19:22:57', '2026-09-08 19:22:57');

-- Dumping structure for table absensi.employees
CREATE TABLE IF NOT EXISTS `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `position_id` bigint unsigned DEFAULT NULL,
  `shift_id` bigint unsigned DEFAULT NULL,
  `office_location_id` bigint unsigned DEFAULT NULL,
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
  KEY `employees_department_id_foreign` (`department_id`),
  KEY `employees_position_id_foreign` (`position_id`),
  KEY `employees_shift_id_foreign` (`shift_id`),
  KEY `employees_office_location_id_foreign` (`office_location_id`),
  CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_office_location_id_foreign` FOREIGN KEY (`office_location_id`) REFERENCES `office_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.employees: ~7 rows (approximately)
INSERT INTO `employees` (`id`, `user_id`, `department_id`, `position_id`, `shift_id`, `office_location_id`, `phone`, `address`, `join_date`, `employment_status`, `contract_end_date`, `resign_date`, `bank_name`, `bank_account`, `bpjs_kes`, `bpjs_tk`, `is_active`, `photo`, `created_at`, `updated_at`) VALUES
	(1, 1, 2, 4, 1, 1, NULL, NULL, '2023-01-01', 'tetap', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(2, 2, 2, 3, 1, 1, NULL, NULL, '2023-06-01', 'tetap', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(3, 3, 1, 2, 1, 1, NULL, NULL, '2023-02-01', 'resigned', NULL, '2026-09-09', NULL, NULL, NULL, NULL, 0, NULL, '2026-09-08 17:56:17', '2026-09-08 18:53:25'),
	(5, 5, 1, 5, 1, 1, NULL, NULL, '2024-01-15', 'kontrak', '2026-12-31', NULL, NULL, NULL, NULL, NULL, 1, NULL, '2026-09-08 17:56:18', '2026-09-08 20:52:08'),
	(6, 6, 3, 5, 1, 1, NULL, NULL, '2024-02-01', 'kontrak', '2027-02-01', NULL, NULL, NULL, NULL, NULL, 1, NULL, '2026-09-08 17:56:18', '2026-09-08 17:56:18'),
	(8, 18, 4, 6, 1, 1, NULL, NULL, '2026-09-09', 'kontrak', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2026-09-08 19:07:18', '2026-09-08 20:33:43'),
	(9, 21, 2, 6, 1, 1, '0893239824343', 'Plumbon', '2026-09-09', 'kontrak', '2026-12-31', NULL, 'Mandiri', '03829302', '45454', '645', 1, 'photos/employees/1789006102_jhnEzhmFKT.jpg', '2026-09-08 20:46:52', '2026-09-10 02:08:22');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.leaves: ~0 rows (approximately)

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.leave_types: ~4 rows (approximately)
INSERT INTO `leave_types` (`id`, `name`, `quota_days`, `is_paid`, `requires_document`, `created_at`, `updated_at`) VALUES
	(1, 'Cuti Tahunan', 12, 1, 0, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(2, 'Sakit', 0, 1, 1, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(3, 'Izin', 0, 0, 0, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(4, 'Cuti Penting', 3, 1, 0, '2026-09-08 17:56:17', '2026-09-08 17:56:17');

-- Dumping structure for table absensi.menus
CREATE TABLE IF NOT EXISTS `menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.menus: ~22 rows (approximately)
INSERT INTO `menus` (`id`, `key`, `name`, `group`, `route_name`, `url`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'dashboard', 'Dashboard', '', 'admin.dashboard', '/admin/dashboard', 'fa-tachometer-alt', 10, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(2, 'departments', 'Departemen', 'MASTER DATA', 'admin.departments.index', '/admin/departments', 'fa-building', 20, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(3, 'positions', 'Jabatan', 'MASTER DATA', 'admin.positions.index', '/admin/positions', 'fa-briefcase', 21, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(4, 'shifts', 'Shift Kerja', 'MASTER DATA', 'admin.shifts.index', '/admin/shifts', 'fa-clock', 22, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(5, 'office_locations', 'Lokasi Kantor', 'MASTER DATA', 'admin.office-locations.index', '/admin/office-locations', 'fa-map-marker-alt', 23, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(6, 'employees', 'Data Karyawan', 'KARYAWAN', 'admin.employees.index', '/admin/employees', 'fa-users', 30, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(7, 'employees_pending', 'Akun Pending', 'KARYAWAN', 'admin.employees.pending', '/admin/employees/pending', 'fa-user-clock', 31, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(8, 'employees_resigned', 'Resign / Cut-Off', 'KARYAWAN', 'admin.employees.resigned', '/admin/employees/resigned', 'fa-user-times', 32, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(9, 'menu_settings', 'Setting Menu', 'KARYAWAN', 'admin.menu-settings.index', '/admin/menu-settings', 'fa-cogs', 33, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(10, 'attendances', 'Harian', 'ABSENSI', 'admin.attendances.index', '/admin/attendances', 'fa-calendar-check', 40, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(11, 'live_map', 'Live Map Hari Ini', 'ABSENSI', 'admin.attendances.live-map', '/admin/attendances/live-map', 'fa-map', 41, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(12, 'leaves', 'Pengajuan Cuti', 'CUTI & PAYROLL', 'admin.leaves.index', '/admin/leaves', 'fa-envelope', 50, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(13, 'payrolls', 'Periode Gaji', 'CUTI & PAYROLL', 'admin.payrolls.index', '/admin/payrolls', 'fa-money-bill', 51, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(14, 'reports', 'Laporan & Export', 'CUTI & PAYROLL', 'admin.reports.index', '/admin/reports', 'fa-file-excel', 52, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(15, 'admin_profile', 'Profile (Admin)', 'CUTI & PAYROLL', 'admin.profile.index', '/admin/profile', 'fa-user-circle', 53, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(16, 'employee_home', 'Absen Hari Ini', 'ABSENSI SAYA', 'employee.home', '/employee/home', 'fa-fingerprint', 60, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(17, 'employee_history', 'Riwayat Absen Saya', 'ABSENSI SAYA', 'employee.history', '/employee/history', 'fa-calendar-check', 61, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(18, 'employee_leaves', 'Cuti / Izin Saya', 'ABSENSI SAYA', 'employee.leaves.index', '/employee/leaves', 'fa-calendar-plus', 62, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(19, 'employee_leaves_create', 'Ajukan Cuti', 'ABSENSI SAYA', 'employee.leaves.create', '/employee/leaves/create', 'fa-plus-circle', 63, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(20, 'employee_payslip', 'Slip Gaji Saya', 'ABSENSI SAYA', 'employee.payslip', '/employee/payslip', 'fa-wallet', 64, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(21, 'employee_menu', 'Menu Mobile Saya', 'ABSENSI SAYA', 'employee.menu', '/employee/menu', 'fa-th-large', 65, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(22, 'employee_profile', 'Profil Saya', 'ABSENSI SAYA', 'employee.profile', '/employee/profile', 'fa-id-badge', 66, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(23, 'akses_mobile', 'Akses Mobile', 'AKSES', 'employee.home', '/employee/home', 'fa-mobile-alt', 5, 1, '2026-09-08 21:00:04', '2026-09-08 21:00:04');

-- Dumping structure for table absensi.menu_settings
CREATE TABLE IF NOT EXISTS `menu_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_id` bigint unsigned NOT NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_settings_role_menu_id_unique` (`role`,`menu_id`),
  KEY `menu_settings_menu_id_foreign` (`menu_id`),
  CONSTRAINT `menu_settings_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.menu_settings: ~101 rows (approximately)
INSERT INTO `menu_settings` (`id`, `role`, `menu_id`, `is_allowed`, `created_at`, `updated_at`) VALUES
	(1, 'hrd', 15, 1, '2026-09-08 19:59:09', '2026-09-08 20:26:55'),
	(2, 'hrd', 10, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(3, 'hrd', 1, 1, '2026-09-08 19:59:09', '2026-09-08 20:18:35'),
	(4, 'hrd', 2, 1, '2026-09-08 19:59:09', '2026-09-08 21:18:55'),
	(5, 'hrd', 17, 1, '2026-09-08 19:59:09', '2026-09-08 21:12:24'),
	(6, 'hrd', 16, 1, '2026-09-08 19:59:09', '2026-09-08 21:12:24'),
	(7, 'hrd', 18, 1, '2026-09-08 19:59:09', '2026-09-08 21:12:24'),
	(8, 'hrd', 19, 1, '2026-09-08 19:59:09', '2026-09-08 21:12:24'),
	(9, 'hrd', 21, 1, '2026-09-08 19:59:09', '2026-09-08 21:12:24'),
	(10, 'hrd', 20, 1, '2026-09-08 19:59:09', '2026-09-08 21:12:24'),
	(11, 'hrd', 22, 1, '2026-09-08 19:59:09', '2026-09-08 21:12:24'),
	(12, 'hrd', 6, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(13, 'hrd', 7, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(14, 'hrd', 8, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(15, 'hrd', 12, 1, '2026-09-08 19:59:09', '2026-09-08 21:18:55'),
	(16, 'hrd', 11, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(17, 'hrd', 9, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(18, 'hrd', 5, 1, '2026-09-08 19:59:09', '2026-09-08 21:18:55'),
	(19, 'hrd', 13, 1, '2026-09-08 19:59:09', '2026-09-08 21:18:55'),
	(20, 'hrd', 3, 1, '2026-09-08 19:59:09', '2026-09-08 20:37:06'),
	(21, 'hrd', 14, 1, '2026-09-08 19:59:09', '2026-09-08 21:18:55'),
	(22, 'hrd', 4, 1, '2026-09-08 19:59:09', '2026-09-08 21:18:55'),
	(23, 'supervisor', 1, 1, '2026-09-08 19:59:09', '2026-09-08 20:18:14'),
	(24, 'supervisor', 6, 0, '2026-09-08 19:59:09', '2026-09-08 20:16:49'),
	(25, 'supervisor', 7, 0, '2026-09-08 19:59:09', '2026-09-08 20:16:49'),
	(26, 'supervisor', 8, 0, '2026-09-08 19:59:09', '2026-09-08 20:16:49'),
	(27, 'supervisor', 10, 0, '2026-09-08 19:59:09', '2026-09-08 20:16:49'),
	(28, 'supervisor', 11, 0, '2026-09-08 19:59:09', '2026-09-08 20:16:49'),
	(29, 'supervisor', 12, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(30, 'supervisor', 13, 0, '2026-09-08 19:59:09', '2026-09-08 20:16:49'),
	(31, 'supervisor', 14, 0, '2026-09-08 19:59:09', '2026-09-08 20:16:49'),
	(32, 'supervisor', 15, 1, '2026-09-08 19:59:09', '2026-09-08 20:22:41'),
	(33, 'supervisor', 16, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(34, 'supervisor', 17, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(35, 'supervisor', 18, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(36, 'supervisor', 19, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(37, 'supervisor', 20, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(38, 'supervisor', 21, 1, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(39, 'supervisor', 22, 0, '2026-09-08 19:59:09', '2026-09-08 20:16:49'),
	(40, 'supervisor', 2, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(41, 'supervisor', 3, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(42, 'supervisor', 4, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(43, 'supervisor', 5, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(44, 'supervisor', 9, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(45, 'staff', 1, 0, '2026-09-08 19:59:09', '2026-09-08 20:13:13'),
	(46, 'staff', 16, 0, '2026-09-08 19:59:09', '2026-09-08 20:13:13'),
	(47, 'staff', 17, 0, '2026-09-08 19:59:09', '2026-09-08 20:13:13'),
	(48, 'staff', 18, 0, '2026-09-08 19:59:09', '2026-09-08 20:13:13'),
	(49, 'staff', 19, 0, '2026-09-08 19:59:09', '2026-09-08 20:13:13'),
	(50, 'staff', 20, 0, '2026-09-08 19:59:09', '2026-09-08 20:13:13'),
	(51, 'staff', 21, 0, '2026-09-08 19:59:09', '2026-09-08 20:13:13'),
	(52, 'staff', 22, 0, '2026-09-08 19:59:09', '2026-09-08 20:13:13'),
	(53, 'staff', 15, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(54, 'staff', 10, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(55, 'staff', 2, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(56, 'staff', 6, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(57, 'staff', 7, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(58, 'staff', 8, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(59, 'staff', 12, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(60, 'staff', 11, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(61, 'staff', 9, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(62, 'staff', 5, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(63, 'staff', 13, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(64, 'staff', 3, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(65, 'staff', 14, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(66, 'staff', 4, 0, '2026-09-08 19:59:09', '2026-09-08 19:59:09'),
	(67, 'manajer_finance', 1, 1, '2026-09-08 20:10:22', '2026-09-08 20:10:22'),
	(68, 'finance_spesialis', 15, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(69, 'finance_spesialis', 10, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(70, 'finance_spesialis', 1, 1, '2026-09-08 20:34:24', '2026-09-08 20:34:39'),
	(71, 'finance_spesialis', 2, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(72, 'finance_spesialis', 17, 1, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(73, 'finance_spesialis', 16, 1, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(74, 'finance_spesialis', 18, 1, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(75, 'finance_spesialis', 19, 1, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(76, 'finance_spesialis', 21, 1, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(77, 'finance_spesialis', 20, 1, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(78, 'finance_spesialis', 22, 1, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(79, 'finance_spesialis', 6, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(80, 'finance_spesialis', 7, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(81, 'finance_spesialis', 8, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(82, 'finance_spesialis', 12, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(83, 'finance_spesialis', 11, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(84, 'finance_spesialis', 9, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(85, 'finance_spesialis', 5, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(86, 'finance_spesialis', 13, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(87, 'finance_spesialis', 3, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(88, 'finance_spesialis', 14, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(89, 'finance_spesialis', 4, 0, '2026-09-08 20:34:24', '2026-09-08 20:34:24'),
	(90, 'hrd', 23, 0, '2026-09-08 21:00:04', '2026-09-08 21:00:04'),
	(91, 'supervisor', 23, 0, '2026-09-08 21:00:04', '2026-09-08 21:00:04'),
	(92, 'staff', 23, 1, '2026-09-08 21:00:04', '2026-09-08 21:02:29'),
	(93, 'finance_spesialis', 23, 1, '2026-09-08 21:00:04', '2026-09-08 21:02:29'),
	(94, 'staff_it', 23, 1, '2026-09-08 21:00:04', '2026-09-08 21:02:29'),
	(95, 'supervisor_it', 23, 0, '2026-09-08 21:00:04', '2026-09-08 21:00:04'),
	(96, 'staff_hrd', 23, 1, '2026-09-08 21:00:04', '2026-09-08 21:02:29'),
	(97, 'manager_hrd', 23, 0, '2026-09-08 21:00:04', '2026-09-08 21:00:04'),
	(98, 'staff_produksi', 23, 1, '2026-09-08 21:00:04', '2026-09-08 21:02:29'),
	(99, 'supervisor_finance', 23, 0, '2026-09-08 21:00:04', '2026-09-08 21:00:04'),
	(100, 'manajer_finance', 23, 0, '2026-09-08 21:00:04', '2026-09-08 21:00:04'),
	(101, 'office_boy', 23, 1, '2026-09-08 21:06:49', '2026-09-08 21:06:49');

-- Dumping structure for table absensi.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.migrations: ~19 rows (approximately)
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
	(16, '2026_09_08_090000_add_supervisor_backup_to_leaves', 1),
	(17, '2026_09_08_145200_add_notifications_read_at_to_users', 1),
	(18, '2026_09_09_100000_add_status_account_to_users', 2),
	(19, '2026_09_09_100001_update_users_email_nik_constraints', 3),
	(20, '2026_09_10_000000_create_menu_settings_tables', 4),
	(21, '2026_09_10_000001_update_users_role_string', 5),
	(22, '2026_09_10_000002_add_akses_mobile_menu', 6),
	(23, '2026_09_10_000003_remove_employee_code_from_employees', 7),
	(24, '2026_09_10_100000_create_deduction_types_and_payroll_deduction_items', 8),
	(25, '2026_09_10_110000_create_company_settings_table', 9);

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

-- Dumping data for table absensi.model_has_roles: ~7 rows (approximately)
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(1, 'App\\Models\\User', 1),
	(1, 'App\\Models\\User', 2),
	(2, 'App\\Models\\User', 3),
	(6, 'App\\Models\\User', 5),
	(3, 'App\\Models\\User', 6),
	(5, 'App\\Models\\User', 18),
	(5, 'App\\Models\\User', 21);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.office_locations: ~0 rows (approximately)
INSERT INTO `office_locations` (`id`, `name`, `address`, `latitude`, `longitude`, `radius_meter`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Kantor Pusat', 'Jl. Contoh No.1 Jakarta', -6.74059909, 108.22124673, 100, 1, '2026-09-08 17:56:17', '2026-09-09 18:42:07');

-- Dumping structure for table absensi.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.password_reset_tokens: ~2 rows (approximately)
INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
	('dikyanwar22@gmail.com', '$2y$10$kgvCz8fg8zcFrAHXzoNhUOvsZEgE7vKMI.ivFeLBw5gvPiqBDwhVa', '2026-09-08 19:05:04'),
	('hrd@example.com', '$2y$10$VYje1YVphD6j7jQ1blJ4sOmTleYVg5N9UiotYlCEfoeQ/ZW8cyX/y', '2026-09-08 19:04:37');

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
	(1, '2026-09', '2026-09-01', '2026-09-30', 'draft', 6, 13100000.00, 1, NULL, '2026-09-08 21:19:05', '2026-09-08 21:19:05'),
	(3, '2026-10', '2026-09-01', '2026-09-10', 'draft', 6, 29792000.00, 1, NULL, '2026-09-10 03:09:16', '2026-09-10 03:10:52');

-- Dumping structure for table absensi.payroll_deduction_items
CREATE TABLE IF NOT EXISTS `payroll_deduction_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_detail_id` bigint unsigned NOT NULL,
  `deduction_type_id` bigint unsigned DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_deduction_items_deduction_type_id_foreign` (`deduction_type_id`),
  KEY `payroll_deduction_items_payroll_detail_id_index` (`payroll_detail_id`),
  CONSTRAINT `payroll_deduction_items_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_deduction_items_payroll_detail_id_foreign` FOREIGN KEY (`payroll_detail_id`) REFERENCES `payroll_details` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.payroll_deduction_items: ~43 rows (approximately)
INSERT INTO `payroll_deduction_items` (`id`, `payroll_detail_id`, `deduction_type_id`, `name`, `amount`, `created_at`, `updated_at`) VALUES
	(18, 9, NULL, 'terlambat', 0.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(19, 9, NULL, 'alpha', 1500000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(20, 9, NULL, 'bpjs_kes', 100000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(21, 9, NULL, 'bpjs_tk', 200000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(22, 9, 1, 'Union Fee', 50000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(23, 9, 2, 'Potongan A', 150000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(24, 9, 3, 'Potongan B', 13000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(25, 10, NULL, 'terlambat', 0.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(26, 10, NULL, 'alpha', 1500000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(27, 10, NULL, 'bpjs_kes', 60000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(28, 10, NULL, 'bpjs_tk', 120000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(29, 10, 1, 'Union Fee', 50000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(30, 10, 2, 'Potongan A', 150000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(31, 10, 3, 'Potongan B', 13000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(32, 11, NULL, 'terlambat', 0.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(33, 11, NULL, 'alpha', 1500000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(34, 11, NULL, 'bpjs_kes', 45000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(35, 11, NULL, 'bpjs_tk', 90000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(36, 11, 1, 'Union Fee', 50000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(37, 11, 2, 'Potongan A', 150000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(38, 11, 3, 'Potongan B', 13000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(39, 12, NULL, 'terlambat', 0.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(40, 12, NULL, 'alpha', 1350000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(41, 12, NULL, 'bpjs_kes', 45000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(42, 12, NULL, 'bpjs_tk', 90000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(43, 12, 1, 'Union Fee', 50000.00, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(44, 12, 2, 'Potongan A', 150000.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(45, 12, 3, 'Potongan B', 13000.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(46, 13, NULL, 'terlambat', 0.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(47, 13, NULL, 'alpha', 1500000.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(48, 13, NULL, 'bpjs_kes', 50000.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(49, 13, NULL, 'bpjs_tk', 100000.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(50, 13, 1, 'Union Fee', 50000.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(51, 13, 2, 'Potongan A', 150000.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(52, 13, 3, 'Potongan B', 13000.00, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(60, 14, NULL, 'terlambat', 0.00, '2026-09-10 03:10:52', '2026-09-10 03:10:52'),
	(61, 14, NULL, 'alpha', 1500000.00, '2026-09-10 03:10:52', '2026-09-10 03:10:52'),
	(62, 14, NULL, 'bpjs_kes', 50000.00, '2026-09-10 03:10:52', '2026-09-10 03:10:52'),
	(63, 14, NULL, 'bpjs_tk', 100000.00, '2026-09-10 03:10:52', '2026-09-10 03:10:52'),
	(64, 14, 1, 'Union Fee', 50000.00, '2026-09-10 03:10:52', '2026-09-10 03:10:52'),
	(65, 14, 2, 'Potongan A', 150000.00, '2026-09-10 03:10:52', '2026-09-10 03:10:52'),
	(66, 14, 3, 'Potongan B', 13000.00, '2026-09-10 03:10:52', '2026-09-10 03:10:52'),
	(67, 14, NULL, 'Potongan khusus', 30000.00, '2026-09-10 03:10:52', '2026-09-10 03:10:52');

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.payroll_details: ~6 rows (approximately)
INSERT INTO `payroll_details` (`id`, `payroll_id`, `user_id`, `basic_salary`, `allowances`, `overtime_pay`, `bonus`, `thr`, `deductions`, `gross_salary`, `total_deduction`, `net_salary`, `attendance_summary`, `notes`, `slip_pdf_path`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 10000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 4500000, "bpjs_tk": 200000, "bpjs_kes": 100000, "terlambat": 0}', 11000000.00, 4800000.00, 6200000.00, '{"alpha": 30, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-08 21:19:05', '2026-09-08 21:19:05'),
	(2, 1, 2, 6000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 4500000, "bpjs_tk": 120000, "bpjs_kes": 60000, "terlambat": 0}', 7000000.00, 4680000.00, 2320000.00, '{"alpha": 30, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-08 21:19:05', '2026-09-08 21:19:05'),
	(3, 1, 5, 4500000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 4500000, "bpjs_tk": 90000, "bpjs_kes": 45000, "terlambat": 0}', 5500000.00, 4635000.00, 865000.00, '{"alpha": 30, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-08 21:19:05', '2026-09-08 21:19:05'),
	(4, 1, 6, 4500000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 4350000, "bpjs_tk": 90000, "bpjs_kes": 45000, "terlambat": 0}', 5500000.00, 4485000.00, 1015000.00, '{"alpha": 29, "hadir": 1, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-08 21:19:05', '2026-09-08 21:19:05'),
	(5, 1, 18, 5000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 4500000, "bpjs_tk": 100000, "bpjs_kes": 50000, "terlambat": 0}', 6000000.00, 4650000.00, 1350000.00, '{"alpha": 30, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-08 21:19:05', '2026-09-08 21:19:05'),
	(6, 1, 21, 5000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 4500000, "bpjs_tk": 100000, "bpjs_kes": 50000, "terlambat": 0}', 6000000.00, 4650000.00, 1350000.00, '{"alpha": 30, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-08 21:19:05', '2026-09-08 21:19:05'),
	(9, 3, 1, 10000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 1500000, "bpjs_tk": 200000, "bpjs_kes": 100000, "Union Fee": 50000, "terlambat": 0, "Potongan A": 150000, "Potongan B": 13000}', 11000000.00, 2013000.00, 8987000.00, '{"alpha": 10, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(10, 3, 2, 6000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 1500000, "bpjs_tk": 120000, "bpjs_kes": 60000, "Union Fee": 50000, "terlambat": 0, "Potongan A": 150000, "Potongan B": 13000}', 7000000.00, 1893000.00, 5107000.00, '{"alpha": 10, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(11, 3, 5, 4500000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 1500000, "bpjs_tk": 90000, "bpjs_kes": 45000, "Union Fee": 50000, "terlambat": 0, "Potongan A": 150000, "Potongan B": 13000}', 5500000.00, 1848000.00, 3652000.00, '{"alpha": 10, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(12, 3, 6, 4500000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 1350000, "bpjs_tk": 90000, "bpjs_kes": 45000, "Union Fee": 50000, "terlambat": 0, "Potongan A": 150000, "Potongan B": 13000}', 5500000.00, 1698000.00, 3802000.00, '{"alpha": 9, "hadir": 1, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-10 03:09:16', '2026-09-10 03:09:16'),
	(13, 3, 18, 5000000.00, '{"makan": 500000, "jabatan": 200000, "transport": 300000}', 0.00, 0.00, 0.00, '{"alpha": 1500000, "bpjs_tk": 100000, "bpjs_kes": 50000, "Union Fee": 50000, "terlambat": 0, "Potongan A": 150000, "Potongan B": 13000}', 6000000.00, 1863000.00, 4137000.00, '{"alpha": 10, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-10 03:09:17', '2026-09-10 03:09:17'),
	(14, 3, 21, 5000000.00, '{"makan": "500000", "jabatan": "200000", "transport": "300000"}', 0.00, 0.00, 0.00, '{"alpha": "1500000", "bpjs_tk": "100000", "bpjs_kes": "50000", "Union Fee": 50000, "terlambat": "0", "Potongan A": 150000, "Potongan B": 13000, "Potongan khusus": 30000}', 6000000.00, 1893000.00, 4107000.00, '{"alpha": 10, "hadir": 0, "terlambat": 0, "late_minutes": 0, "overtime_hours": 0}', NULL, NULL, '2026-09-10 03:09:17', '2026-09-10 03:10:52');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.positions: ~9 rows (approximately)
INSERT INTO `positions` (`id`, `department_id`, `name`, `basic_salary_default`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Staff IT', 5000000.00, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(2, 1, 'Supervisor IT', 8000000.00, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(3, 2, 'Staff HRD', 6000000.00, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(4, 2, 'Manager HRD', 10000000.00, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(5, 3, 'Staff Produksi', 4500000.00, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(6, 4, 'Finance Spesialis', 5000000.00, '2026-09-08 19:23:15', '2026-09-08 19:23:15'),
	(7, 4, 'Supervisor Finance', 5000000.00, '2026-09-08 19:23:27', '2026-09-08 19:23:27'),
	(8, 4, 'Manajer Finance', 8000000.00, '2026-09-08 19:25:08', '2026-09-08 19:25:08'),
	(9, 2, 'Office Boy', 5000000.00, '2026-09-08 21:06:11', '2026-09-08 21:06:11');

-- Dumping structure for table absensi.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.roles: ~7 rows (approximately)
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'hrd', 'web', '2026-09-08 17:56:16', '2026-09-08 17:56:16'),
	(2, 'supervisor', 'web', '2026-09-08 17:56:16', '2026-09-08 17:56:16'),
	(3, 'staff', 'web', '2026-09-08 17:56:16', '2026-09-08 17:56:16'),
	(4, 'manajer_finance', 'web', '2026-09-08 20:32:35', '2026-09-08 20:32:35'),
	(5, 'finance_spesialis', 'web', '2026-09-08 20:33:43', '2026-09-08 20:33:43'),
	(6, 'staff_produksi', 'web', '2026-09-08 20:52:08', '2026-09-08 20:52:08'),
	(7, 'office_boy', 'web', '2026-09-08 21:06:35', '2026-09-08 21:06:35'),
	(8, 'staff_hrd', 'web', '2026-09-08 21:27:17', '2026-09-08 21:27:17');

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

-- Dumping data for table absensi.shifts: ~3 rows (approximately)
INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `tolerance_late`, `is_overnight`, `color`, `created_at`, `updated_at`) VALUES
	(1, 'Pagi', '07:00:00', '15:00:00', 15, 0, '#198754', '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(2, 'Siang', '14:00:00', '22:00:00', 15, 0, '#ffc107', '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(3, 'Malam', '22:00:00', '06:00:00', 15, 1, '#6f42c1', '2026-09-08 17:56:17', '2026-09-08 17:56:17');

-- Dumping structure for table absensi.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `status_account` tinyint NOT NULL DEFAULT '0' COMMENT '0=pending menunggu ACC HRD, 1=aktif bisa login',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `notifications_read_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_nik_unique` (`nik`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi.users: ~7 rows (approximately)
INSERT INTO `users` (`id`, `name`, `nik`, `email`, `role`, `status_account`, `email_verified_at`, `notifications_read_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'HRD Admin', 'HRD001', 'hrd@example.com', 'hrd', 1, '2026-09-08 17:56:17', NULL, '$2y$10$S0sZ3K31HsFA9shEHvCw..W02SwT0kfu/7D7pu5y6atOPqpHgdskG', NULL, '2026-09-08 17:56:17', '2026-09-08 17:56:17'),
	(2, 'Siti HRD', 'HRD002', 'siti.hrd@example.com', 'hrd', 1, '2026-09-08 17:56:17', NULL, '$2y$10$S0sZ3K31HsFA9shEHvCw..W02SwT0kfu/7D7pu5y6atOPqpHgdskG', NULL, '2026-09-08 17:56:17', '2026-09-08 18:56:04'),
	(3, 'Supervisor IT', 'SPV001', 'spv@example.com', 'supervisor', 1, '2026-09-08 17:56:17', '2026-09-08 20:20:08', '$2y$10$S0sZ3K31HsFA9shEHvCw..W02SwT0kfu/7D7pu5y6atOPqpHgdskG', NULL, '2026-09-08 17:56:17', '2026-09-08 20:20:07'),
	(5, 'Budi Karyawan', 'STF001', 'budi@example.com', 'staff', 1, '2026-09-08 17:56:17', NULL, '$2y$10$S0sZ3K31HsFA9shEHvCw..W02SwT0kfu/7D7pu5y6atOPqpHgdskG', NULL, '2026-09-08 17:56:17', '2026-09-08 20:52:08'),
	(6, 'Andi Produksi', 'STF002', 'andi@example.com', 'staff', 1, '2026-09-08 17:56:18', '2026-09-08 18:13:28', '$2y$10$S0sZ3K31HsFA9shEHvCw..W02SwT0kfu/7D7pu5y6atOPqpHgdskG', NULL, '2026-09-08 17:56:18', '2026-09-08 18:51:22'),
	(7, 'Dicky Anwar', NULL, 'dikyanwar22@gmail.com', 'staff', 1, NULL, NULL, '$2y$10$S0sZ3K31HsFA9shEHvCw..W02SwT0kfu/7D7pu5y6atOPqpHgdskG', NULL, '2026-09-08 18:30:08', '2026-09-08 18:30:08'),
	(18, 'Arif Budiman', '889447342', 'arif.budi75@gmail.com', 'finance_spesialis', 1, NULL, '2026-09-08 19:08:59', '$2y$10$S0sZ3K31HsFA9shEHvCw..W02SwT0kfu/7D7pu5y6atOPqpHgdskG', NULL, '2026-09-08 19:07:18', '2026-09-08 20:33:43'),
	(21, 'Ahid Lila', '408451929', 'ahid.lila@gmail.com', 'finance_spesialis', 1, NULL, '2026-09-09 18:48:13', '$2y$10$N03Prb3M9jMFDVc3TNfmIO.I0ew61ZXoMUjYpbjWeagx6uFq0rjMa', NULL, '2026-09-08 20:46:52', '2026-09-09 18:48:12');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
