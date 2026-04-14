-- Database: sisdm_absensi
-- MySQL 8.0.30 Compatible

CREATE DATABASE IF NOT EXISTS `sisdm_absensi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sisdm_absensi`;

-- Table: sekolah (Identitas Sekolah)
CREATE TABLE `sekolah` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `npsn` VARCHAR(20) DEFAULT NULL,
  `nama_sekolah` VARCHAR(255) DEFAULT NULL,
  `alamat` TEXT,
  `website` VARCHAR(255) DEFAULT NULL,
  `telepon` VARCHAR(20) DEFAULT NULL,
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `background_path` VARCHAR(255) DEFAULT NULL,
  `transparency` DECIMAL(3,2) DEFAULT 0.95,
  `blur` INT(11) DEFAULT 10,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users (Admin & Petugas)
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','petugas') NOT NULL,
  `nama_lengkap` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: petugas_harian (Jadwal Petugas Harian)
CREATE TABLE `petugas_harian` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tanggal` DATE NOT NULL,
  `user_id` INT(11) NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: kelas
CREATE TABLE `kelas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kelas` VARCHAR(50) NOT NULL,
  `jurusan` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: siswa
CREATE TABLE `siswa` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nipd` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `kelas_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`kelas_id`) REFERENCES `kelas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: absensi
CREATE TABLE `absensi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` INT(11) NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_datang` TIME DEFAULT NULL,
  `jam_pulang` TIME DEFAULT NULL,
  `status_telat` TINYINT(1) DEFAULT 0,
  `status_pulang_awal` TINYINT(1) DEFAULT 0,
  `keterangan` ENUM('hadir','sakit','izin','alfa') DEFAULT 'hadir',
  `ket_detail` VARCHAR(255) DEFAULT NULL,
  `petugas_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`petugas_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_siswa_tanggal` (`siswa_id`, `tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: izin_keluar (Telat / Keluar Lingkungan / Pulang Awal saat jam sekolah)
CREATE TABLE `izin_keluar` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` INT(11) NOT NULL,
  `tanggal` DATE NOT NULL,
  `jenis_izin` ENUM('telat','keluar_lingkungan','pulang_awal') NOT NULL,
  `jam_izin` TIME NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `petugas_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`petugas_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: settings_absensi (Pengaturan Program Absensi)
CREATE TABLE `settings_absensi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `jam_masuk` TIME DEFAULT '07:00:00',
  `jam_pulang` TIME DEFAULT '15:00:00',
  `toleransi_telat` INT(11) DEFAULT 15,
  `theme` VARCHAR(50) DEFAULT 'fluent',
  `mode` VARCHAR(50) DEFAULT 'light',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SAMPLE DATA

-- Sekolah
INSERT INTO `sekolah` (`npsn`, `nama_sekolah`, `alamat`, `website`, `telepon`, `transparency`, `blur`) VALUES
('12345678', 'SMA Negeri 1 Contoh', 'Jl. Pendidikan No. 123, Jakarta', 'https://sman1contoh.sch.id', '021-1234567', 0.95, 10);

-- Users (Password: admin123 dan petugas123 - hashed with password_hash PHP)
INSERT INTO `users` (`username`, `password`, `role`, `nama_lengkap`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator'),
('petugas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas', 'Petugas Absensi 1'),
('petugas2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas', 'Petugas Absensi 2');

-- Kelas
INSERT INTO `kelas` (`nama_kelas`, `jurusan`) VALUES
('X-A', 'IPA'),
('X-B', 'IPS'),
('XI-A', 'IPA'),
('XI-B', 'IPS'),
('XII-A', 'IPA'),
('XII-B', 'IPS');

-- Siswa Sample
INSERT INTO `siswa` (`nipd`, `nama`, `kelas_id`) VALUES
('2024001', 'Ahmad Rizki', 1),
('2024002', 'Budi Santoso', 1),
('2024003', 'Citra Dewi', 2),
('2024004', 'Diana Putri', 3),
('2024005', 'Eko Prasetyo', 3),
('2024006', 'Fani Rahmawati', 4),
('2024007', 'Gilang Ramadhan', 5),
('2024008', 'Hana Safitri', 5),
('2024009', 'Indra Wijaya', 6),
('2024010', 'Juli Kartika', 6);

-- Settings Absensi Default
INSERT INTO `settings_absensi` (`jam_masuk`, `jam_pulang`, `toleransi_telat`, `theme`, `mode`) VALUES
('07:00:00', '15:00:00', 15, 'fluent', 'light');

-- Petugas Harian Sample (Hari ini)
INSERT INTO `petugas_harian` (`tanggal`, `user_id`, `keterangan`) VALUES
(CURDATE(), 2, 'Petugas hari ini'),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), 3, 'Petugas besok');

-- Sample Absensi
INSERT INTO `absensi` (`siswa_id`, `tanggal`, `jam_datang`, `jam_pulang`, `status_telat`, `status_pulang_awal`, `keterangan`, `petugas_id`) VALUES
(1, CURDATE(), '06:55:00', '15:05:00', 0, 0, 'hadir', 2),
(2, CURDATE(), '07:20:00', '15:00:00', 1, 0, 'hadir', 2),
(3, CURDATE(), '06:50:00', '14:30:00', 0, 1, 'hadir', 2),
(4, CURDATE(), NULL, NULL, 0, 0, 'sakit', 2),
(5, CURDATE(), '07:00:00', '15:00:00', 0, 0, 'hadir', 2);

-- Sample Izin Keluar
INSERT INTO `izin_keluar` (`siswa_id`, `tanggal`, `jenis_izin`, `jam_izin`, `keterangan`, `petugas_id`) VALUES
(2, CURDATE(), 'telat', '07:20:00', 'Ban bocor di jalan', 2),
(3, CURDATE(), 'pulang_awal', '14:30:00', 'Ada keperluan keluarga', 2);
