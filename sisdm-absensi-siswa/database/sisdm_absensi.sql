-- Database: sisdm_absensi
-- MySQL 8.0.30 Compatible

CREATE DATABASE IF NOT EXISTS `sisdm_absensi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sisdm_absensi`;

-- Table: sekolah (Identitas Sekolah)
CREATE TABLE `sekolah` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `npsn` VARCHAR(20),
  `nama_sekolah` VARCHAR(255),
  `alamat` TEXT,
  `website` VARCHAR(255),
  `telepon` VARCHAR(20),
  `logo_path` VARCHAR(255),
  `background_path` VARCHAR(255),
  `transparency` DECIMAL(3,2) DEFAULT 0.95,
  `blur` INT DEFAULT 0,
  `theme` VARCHAR(50) DEFAULT 'fluent',
  `mode` VARCHAR(20) DEFAULT 'light'
);

-- Table: users (Administrator & Petugas)
CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(100) UNIQUE,
  `password` VARCHAR(255),
  `role` ENUM('admin', 'petugas'),
  `nama_lengkap` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: petugas_harian (Petugas Absensi per Hari)
CREATE TABLE `petugas_harian` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `tanggal` DATE,
  `user_id` INT,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Table: jurusan
CREATE TABLE `jurusan` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `kode_jurusan` VARCHAR(20),
  `nama_jurusan` VARCHAR(100)
);

-- Table: kelas
CREATE TABLE `kelas` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nama_kelas` VARCHAR(20),
  `jurusan_id` INT,
  FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan`(`id`) ON DELETE SET NULL
);

-- Table: siswa
CREATE TABLE `siswa` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nipd` VARCHAR(50) UNIQUE,
  `nama` VARCHAR(255),
  `kelas_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`kelas_id`) REFERENCES `kelas`(`id`) ON DELETE SET NULL
);

-- Table: absensi
CREATE TABLE `absensi` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `siswa_id` INT,
  `tanggal` DATE,
  `jam_datang` TIME,
  `jam_pulang` TIME,
  `status_datang` ENUM('tepat_waktu', 'telat') DEFAULT 'tepat_waktu',
  `status_pulang` ENUM('tepat_waktu', 'pulang_awal') DEFAULT 'tepat_waktu',
  `keterangan` ENUM('hadir', 'sakit', 'izin', 'alfa') DEFAULT 'hadir',
  `catatan` TEXT,
  `petugas_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`petugas_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Table: izin_keluar
CREATE TABLE `izin_keluar` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `siswa_id` INT,
  `tanggal` DATE,
  `jenis_izin` ENUM('telat', 'keluar_lingkungan', 'pulang_awal'),
  `jam_keluar` TIME,
  `jam_kembali` TIME,
  `alasan` TEXT,
  `petugas_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`petugas_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Sample Data: Sekolah
INSERT INTO `sekolah` (`npsn`, `nama_sekolah`, `alamat`, `website`, `telepon`) 
VALUES ('12345678', 'SMA Negeri 1 Contoh', 'Jl. Pendidikan No. 123, Jakarta', 'https://sman1contoh.sch.id', '021-1234567');

-- Sample Data: Users (Password: admin123 dan petugas123)
INSERT INTO `users` (`username`, `password`, `role`, `nama_lengkap`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator'),
('petugas', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'petugas', 'Petugas Absensi');

-- Sample Data: Jurusan
INSERT INTO `jurusan` (`kode_jurusan`, `nama_jurusan`) VALUES
('IPA', 'Ilmu Pengetahuan Alam'),
('IPS', 'Ilmu Pengetahuan Sosial'),
('TKJ', 'Teknik Komputer Jaringan'),
('RPL', 'Rekayasa Perangkat Lunak');

-- Sample Data: Kelas
INSERT INTO `kelas` (`nama_kelas`, `jurusan_id`) VALUES
('X-A', 1), ('X-B', 1), ('XI-A', 1), ('XI-B', 2), ('XII-A', 3), ('XII-B', 4);

-- Sample Data: Siswa
INSERT INTO `siswa` (`nipd`, `nama`, `kelas_id`) VALUES
('2024001', 'Ahmad Rizki', 1),
('2024002', 'Budi Santoso', 1),
('2024003', 'Citra Dewi', 2),
('2024004', 'Diana Putri', 3),
('2024005', 'Eko Prasetyo', 4),
('2024006', 'Fani Amelia', 5),
('2024007', 'Gilang Ramadhan', 6);

-- Sample Data: Absensi
INSERT INTO `absensi` (`siswa_id`, `tanggal`, `jam_datang`, `jam_pulang`, `status_datang`, `status_pulang`, `keterangan`, `petugas_id`) VALUES
(1, CURDATE(), '07:15:00', '15:30:00', 'tepat_waktu', 'tepat_waktu', 'hadir', 2),
(2, CURDATE(), '07:45:00', '15:30:00', 'telat', 'tepat_waktu', 'hadir', 2),
(3, CURDATE(), '07:20:00', '15:00:00', 'tepat_waktu', 'pulang_awal', 'hadir', 2),
(4, CURDATE(), NULL, NULL, 'tepat_waktu', 'tepat_waktu', 'sakit', 2),
(5, CURDATE(), '07:10:00', '15:30:00', 'tepat_waktu', 'tepat_waktu', 'hadir', 2);

-- Sample Data: Izin Keluar
INSERT INTO `izin_keluar` (`siswa_id`, `tanggal`, `jenis_izin`, `jam_keluar`, `jam_kembali`, `alasan`, `petugas_id`) VALUES
(2, CURDATE(), 'telat', '07:45:00', NULL, 'Ban bocor di jalan', 2);
