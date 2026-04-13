-- ============================================
-- School Attendance System Database Schema
-- ============================================
-- Database: school_attendance
-- Version: 1.0.0
-- Generated: 2024
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS `school_attendance` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `school_attendance`;

-- ============================================
-- Table: users
-- Description: User accounts for admin and petugas
-- ============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'petugas') NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: students
-- Description: Student data
-- ============================================
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nipd` VARCHAR(20) UNIQUE NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `class` VARCHAR(20) NOT NULL,
  `major` VARCHAR(50) NOT NULL,
  `gender` ENUM('L', 'P') DEFAULT 'L',
  `birth_date` DATE DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `parent_name` VARCHAR(100) DEFAULT NULL,
  `parent_phone` VARCHAR(20) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_nipd` (`nipd`),
  INDEX `idx_class` (`class`),
  INDEX `idx_major` (`major`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: attendance
-- Description: Daily attendance records
-- ============================================
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `check_in` TIME DEFAULT NULL,
  `check_out` TIME DEFAULT NULL,
  `status` ENUM('present', 'sick', 'permission', 'alpha') DEFAULT 'present',
  `is_late` BOOLEAN DEFAULT FALSE,
  `left_early` BOOLEAN DEFAULT FALSE,
  `late_minutes` INT DEFAULT 0,
  `notes` TEXT DEFAULT NULL,
  `recorded_by` INT DEFAULT NULL,
  `check_in_location` VARCHAR(255) DEFAULT NULL,
  `check_out_location` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_attendance` (`student_id`, `date`),
  INDEX `idx_date` (`date`),
  INDEX `idx_status` (`status`),
  INDEX `idx_student_date` (`student_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: permissions
-- Description: Special permission records (late, leave, early leave)
-- ============================================
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `permission_type` ENUM('late', 'leave_school', 'early_leave') NOT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `approved_by` INT DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `recorded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_student` (`student_id`),
  INDEX `idx_type` (`permission_type`),
  INDEX `idx_status` (`status`),
  INDEX `idx_start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: settings
-- Description: Application settings
-- ============================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) UNIQUE NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` VARCHAR(20) DEFAULT 'string',
  `setting_label` VARCHAR(100) DEFAULT NULL,
  `setting_group` VARCHAR(50) DEFAULT 'general',
  `is_public` BOOLEAN DEFAULT FALSE,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_key` (`setting_key`),
  INDEX `idx_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: school_identity
-- Description: School identity information
-- ============================================
DROP TABLE IF EXISTS `school_identity`;
CREATE TABLE `school_identity` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `npsn` VARCHAR(20) DEFAULT NULL,
  `school_name` VARCHAR(200) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: activity_logs
-- Description: System activity logs
-- ============================================
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(50) DEFAULT NULL,
  `record_id` INT DEFAULT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: dashboard_widgets
-- Description: Customizable dashboard widgets
-- ============================================
DROP TABLE IF EXISTS `dashboard_widgets`;
CREATE TABLE `dashboard_widgets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `widget_name` VARCHAR(50) NOT NULL,
  `widget_type` VARCHAR(50) NOT NULL,
  `position` INT DEFAULT 0,
  `is_visible` BOOLEAN DEFAULT TRUE,
  `config` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Default Data
-- ============================================

-- Default admin user (password: admin123)
INSERT INTO `users` (`username`, `password`, `full_name`, `role`, `email`, `is_active`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'admin@school.local', TRUE);

-- Default petugas user (password: petugas123)
INSERT INTO `users` (`username`, `password`, `full_name`, `role`, `email`, `is_active`) 
VALUES ('petugas', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Petugas Absensi', 'petugas', 'petugas@school.local', TRUE);

-- School Identity
INSERT INTO `school_identity` (`npsn`, `school_name`, `address`, `website`, `phone`, `email`) 
VALUES ('12345678', 'SMK Teknologi Nusantara', 'Jl. Pendidikan No. 123, Jakarta', 'https://smkteknologi.sch.id', '(021) 1234-5678', 'info@smkteknologi.sch.id');

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_label`, `setting_group`, `is_public`) VALUES
('check_in_start', '07:00', 'time', 'Jam Mulai Check-in', 'attendance', FALSE),
('check_in_end', '08:00', 'time', 'Jam Akhir Check-in', 'attendance', FALSE),
('check_out_start', '15:00', 'time', 'Jam Mulai Check-out', 'attendance', FALSE),
('check_out_end', '16:00', 'time', 'Jam Akhir Check-out', 'attendance', FALSE),
('late_threshold', '15', 'number', 'Toleransi Keterlambatan (menit)', 'attendance', FALSE),
('theme', 'fluent', 'string', 'Tema Aplikasi', 'appearance', TRUE),
('mode', 'light', 'string', 'Mode Warna', 'appearance', TRUE),
('school_name', 'SMK Teknologi Nusantara', 'string', 'Nama Sekolah', 'school', TRUE),
('npsn', '12345678', 'string', 'NPSN', 'school', FALSE),
('allow_self_checkin', '1', 'boolean', 'Izinkan Check-in Mandiri', 'attendance', FALSE),
('require_photo', '0', 'boolean', 'Wajibkan Foto saat Check-in', 'attendance', FALSE),
('enable_notifications', '1', 'boolean', 'Aktifkan Notifikasi', 'general', FALSE);

-- Dashboard Widgets for Admin
INSERT INTO `dashboard_widgets` (`user_id`, `widget_name`, `widget_type`, `position`, `is_visible`) VALUES
(1, 'today_attendance', 'stats', 1, TRUE),
(1, 'late_students', 'list', 2, TRUE),
(1, 'monthly_summary', 'chart', 3, TRUE),
(1, 'quick_actions', 'actions', 4, TRUE);

-- Dashboard Widgets for Petugas
INSERT INTO `dashboard_widgets` (`user_id`, `widget_name`, `widget_type`, `position`, `is_visible`) VALUES
(2, 'today_attendance', 'stats', 1, TRUE),
(2, 'quick_checkin', 'form', 2, TRUE),
(2, 'recent_activity', 'list', 3, TRUE);

-- ============================================
-- Sample Students Data (Optional)
-- ============================================
INSERT INTO `students` (`nipd`, `name`, `class`, `major`, `gender`, `birth_date`, `phone`, `parent_name`, `parent_phone`) VALUES
('2024001', 'Ahmad Rizki', 'X RPL 1', 'Rekayasa Perangkat Lunak', 'L', '2008-05-15', '081234567890', 'Budi Santoso', '081234567891'),
('2024002', 'Siti Nurhaliza', 'X RPL 1', 'Rekayasa Perangkat Lunak', 'P', '2008-07-20', '081234567892', 'Hasan Abdullah', '081234567893'),
('2024003', 'Muhammad Fajar', 'X TKJ 1', 'Teknik Komputer Jaringan', 'L', '2008-03-10', '081234567894', 'Rahmat Hidayat', '081234567895'),
('2024004', 'Aisha Putri', 'X DKV 1', 'Desain Komunikasi Visual', 'P', '2008-09-05', '081234567896', 'Dewi Lestari', '081234567897'),
('2024005', 'Andi Pratama', 'XI RPL 2', 'Rekayasa Perangkat Lunak', 'L', '2007-11-25', '081234567898', 'Joko Widodo', '081234567899');

-- ============================================
-- Sample Attendance Data (Optional - Today)
-- ============================================
INSERT INTO `attendance` (`student_id`, `date`, `check_in`, `check_out`, `status`, `is_late`, `recorded_by`) 
SELECT s.id, CURDATE(), '07:15:00', '15:30:00', 'present', FALSE, 1
FROM students s WHERE s.nipd IN ('2024001', '2024002');

INSERT INTO `attendance` (`student_id`, `date`, `check_in`, `check_out`, `status`, `is_late`, `late_minutes`, `recorded_by`) 
SELECT s.id, CURDATE(), '08:20:00', NULL, 'present', TRUE, 20, 1
FROM students s WHERE s.nipd = '2024003';

-- ============================================
-- Views for Reporting
-- ============================================

-- View: Daily Attendance Summary
DROP VIEW IF EXISTS `v_daily_attendance`;
CREATE VIEW `v_daily_attendance` AS
SELECT 
    a.date,
    COUNT(DISTINCT a.student_id) as total_students,
    SUM(CASE WHEN a.status = 'present' AND a.is_late = FALSE THEN 1 ELSE 0 END) as present_on_time,
    SUM(CASE WHEN a.status = 'present' AND a.is_late = TRUE THEN 1 ELSE 0 END) as present_late,
    SUM(CASE WHEN a.status = 'sick' THEN 1 ELSE 0 END) as sick,
    SUM(CASE WHEN a.status = 'permission' THEN 1 ELSE 0 END) as permission,
    SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as alpha
FROM attendance a
GROUP BY a.date;

-- View: Monthly Attendance Summary
DROP VIEW IF EXISTS `v_monthly_attendance`;
CREATE VIEW `v_monthly_attendance` AS
SELECT 
    YEAR(a.date) as year,
    MONTH(a.date) as month,
    s.id as student_id,
    s.nipd,
    s.name,
    s.class,
    s.major,
    COUNT(a.id) as total_days,
    SUM(CASE WHEN a.status = 'present' AND a.is_late = FALSE THEN 1 ELSE 0 END) as present_on_time,
    SUM(CASE WHEN a.status = 'present' AND a.is_late = TRUE THEN 1 ELSE 0 END) as present_late,
    SUM(CASE WHEN a.status = 'sick' THEN 1 ELSE 0 END) as sick,
    SUM(CASE WHEN a.status = 'permission' THEN 1 ELSE 0 END) as permission,
    SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as alpha
FROM attendance a
JOIN students s ON a.student_id = s.id
GROUP BY YEAR(a.date), MONTH(a.date), s.id;

-- View: Student Attendance Detail
DROP VIEW IF EXISTS `v_student_attendance_detail`;
CREATE VIEW `v_student_attendance_detail` AS
SELECT 
    a.id,
    s.nipd,
    s.name as student_name,
    s.class,
    s.major,
    a.date,
    a.check_in,
    a.check_out,
    a.status,
    a.is_late,
    a.left_early,
    a.late_minutes,
    a.notes,
    u.full_name as recorded_by_name,
    a.created_at
FROM attendance a
JOIN students s ON a.student_id = s.id
LEFT JOIN users u ON a.recorded_by = u.id;

-- ============================================
-- Stored Procedures
-- ============================================

DELIMITER //

-- Procedure: Get Attendance Statistics
DROP PROCEDURE IF EXISTS `sp_get_attendance_stats`//
CREATE PROCEDURE `sp_get_attendance_stats`(IN p_date DATE)
BEGIN
    SELECT 
        COUNT(DISTINCT a.student_id) as total_present,
        SUM(CASE WHEN a.is_late = TRUE THEN 1 ELSE 0 END) as total_late,
        SUM(CASE WHEN a.left_early = TRUE THEN 1 ELSE 0 END) as total_left_early,
        SUM(CASE WHEN a.status = 'sick' THEN 1 ELSE 0 END) as total_sick,
        SUM(CASE WHEN a.status = 'permission' THEN 1 ELSE 0 END) as total_permission,
        SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as total_alpha
    FROM attendance a
    WHERE a.date = p_date;
END//

-- Procedure: Add or Update Attendance
DROP PROCEDURE IF EXISTS `sp_upsert_attendance`//
CREATE PROCEDURE `sp_upsert_attendance`(
    IN p_student_id INT,
    IN p_date DATE,
    IN p_check_in TIME,
    IN p_check_out TIME,
    IN p_status VARCHAR(20),
    IN p_recorded_by INT
)
BEGIN
    DECLARE v_late_threshold INT DEFAULT 15;
    DECLARE v_check_in_end TIME;
    
    -- Get settings
    SELECT setting_value INTO v_late_threshold FROM settings WHERE setting_key = 'late_threshold';
    SELECT setting_value INTO v_check_in_end FROM settings WHERE setting_key = 'check_in_end';
    
    INSERT INTO attendance (student_id, date, check_in, check_out, status, is_late, recorded_by)
    VALUES (p_student_id, p_date, p_check_in, p_check_out, p_status, 
            IF(p_check_in > v_check_in_end, TRUE, FALSE), p_recorded_by)
    ON DUPLICATE KEY UPDATE
        check_in = IFNULL(p_check_in, check_in),
        check_out = IFNULL(p_check_out, check_out),
        status = IFNULL(p_status, status),
        is_late = IF(p_check_in > v_check_in_end, TRUE, is_late),
        recorded_by = IFNULL(p_recorded_by, recorded_by),
        updated_at = CURRENT_TIMESTAMP;
END//

DELIMITER ;

-- ============================================
-- End of Schema
-- ============================================
