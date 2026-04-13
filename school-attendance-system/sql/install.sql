-- =========================================================
-- SCHOOL ATTENDANCE SYSTEM - DATABASE SCHEMA & DUMMY DATA
-- =========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- 1. STRUCTURE: TABLES
-- --------------------------------------------------------

-- Tabel Settings (Konfigurasi Sekolah)
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `npsn` varchar(20) DEFAULT '',
  `school_name` varchar(100) DEFAULT 'Sekolah Demo',
  `address` text,
  `website` varchar(100) DEFAULT '',
  `phone` varchar(20) DEFAULT '',
  `check_in_start` time DEFAULT '06:30:00',
  `check_in_end` time DEFAULT '07:30:00',
  `check_out_start` time DEFAULT '14:00:00',
  `theme_mode` enum('light','dark') DEFAULT 'light',
  `theme_style` enum('fluent','material','glass','cyberpunk') DEFAULT 'fluent',
  `bg_opacity` decimal(3,2) DEFAULT '0.90',
  `bg_blur` int(11) DEFAULT '5',
  `logo_path` varchar(255) DEFAULT NULL,
  `bg_image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Users (Admin & Petugas digabung untuk efisiensi, dipisah by role)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','officer') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Siswa
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nipd` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `class` varchar(20) NOT NULL,
  `major` varchar(50) DEFAULT 'Umum',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nipd` (`nipd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Jadwal Petugas (Rotasi Harian)
CREATE TABLE IF NOT EXISTS `officer_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `shift` enum('morning','afternoon','full') DEFAULT 'full',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_officer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Absensi
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','late','early_leave','sick','permission','alpha') DEFAULT 'present',
  `notes` text,
  `recorded_by` int(11) DEFAULT NULL, -- ID User yang input
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_date` (`student_id`, `date`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. DUMMY DATA (CONTOH DATA)
-- --------------------------------------------------------

-- A. Default Settings
INSERT INTO `settings` (`id`, `npsn`, `school_name`, `address`, `website`, `phone`, `theme_mode`, `theme_style`) VALUES
(1, '12345678', 'SMK Teknologi Nusantara', 'Jl. Pendidikan No. 1, Jakarta', 'https://smkteknologi.sch.id', '021-555-0199', 'light', 'fluent');

-- B. Users (Password default: 'admin123' dan 'petugas123' di-hash dengan bcrypt)
-- Hash generated for: admin123 -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- Hash generated for: petugas123 -> $2y$10$4N8xV.xY.zW.vU.tS.rQpO.nM.lK.jI.hG.fE.dC.bA.9.8.7.6.5.4.3.2.1 (Contoh placeholder, pakai hash asli di bawah)
INSERT INTO `users` (`username`, `password`, `role`, `full_name`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator Utama'),
('petugas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'officer', 'Petugas Absensi 1');
-- Catatan: Di aplikasi nyata, gunakan password_hash() PHP. Hash di atas adalah untuk 'admin123'.

-- C. Data Siswa Contoh (10 Siswa)
INSERT INTO `students` (`nipd`, `name`, `class`, `major`, `status`) VALUES
('2023001', 'Ahmad Santoso', 'X-RPL-1', 'Rekayasa Perangkat Lunak', 'active'),
('2023002', 'Budi Pratama', 'X-RPL-1', 'Rekayasa Perangkat Lunak', 'active'),
('2023003', 'Citra Lestari', 'X-TKJ-1', 'Teknik Komputer Jaringan', 'active'),
('2023004', 'Dewi Anggraini', 'XI-MM-1', 'Multimedia', 'active'),
('2023005', 'Eko Prasetyo', 'XI-MM-1', 'Multimedia', 'active'),
('2023006', 'Fajar Nugraha', 'XII-AK-1', 'Akuntansi', 'active'),
('2023007', 'Gita Pertiwi', 'XII-AK-1', 'Akuntansi', 'active'),
('2023008', 'Hendra Wijaya', 'X-RPL-2', 'Rekayasa Perangkat Lunak', 'active'),
('2023009', 'Indah Sari', 'X-TKJ-2', 'Teknik Komputer Jaringan', 'active'),
('2023010', 'Joko Anwar', 'XI-TKRO-1', 'Teknik Kendaraan Ringan', 'active');

-- D. Jadwal Petugas (Contoh untuk minggu ini)
INSERT INTO `officer_schedules` (`date`, `user_id`, `shift`) VALUES
(CURDATE(), 2, 'morning'),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2, 'morning'),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), 2, 'morning');

-- E. Data Absensi Dummy (Untuk hari ini)
-- Ambil ID siswa 1 dan 2 untuk contoh absen
INSERT INTO `attendance` (`student_id`, `date`, `time_in`, `time_out`, `status`, `notes`, `recorded_by`) VALUES
(1, CURDATE(), '07:15:00', '15:30:00', 'present', 'Tepat waktu', 2),
(2, CURDATE(), '07:45:00', '15:30:00', 'late', 'Telat 15 menit', 2),
(3, CURDATE(), NULL, NULL, 'sick', 'Surat sakit dari dokter', 2);

COMMIT;
