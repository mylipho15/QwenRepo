-- Update Schema untuk Fitur Baru: Petugas, Rotasi, Logo, Background Custom
-- Jalankan file ini jika database sudah ada, atau gunakan install.sql untuk instalasi baru

-- 1. Tabel Petugas Absensi
CREATE TABLE IF NOT EXISTS petugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data Default Petugas (Password: petugas123)
INSERT INTO petugas (username, password, nama_lengkap) 
VALUES ('petugas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Hari Ini')
ON DUPLICATE KEY UPDATE nama_lengkap=nama_lengkap;

-- 2. Tabel Jadwal Rotasi Petugas (Agar petugas bisa ganti tiap hari)
CREATE TABLE IF NOT EXISTS jadwal_petugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    petugas_id INT NOT NULL,
    keterangan VARCHAR(255),
    FOREIGN KEY (petugas_id) REFERENCES petugas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tanggal (tanggal)
);

-- 3. Update Tabel Settings untuk Logo, Background, Blur, Transparency
ALTER TABLE settings 
ADD COLUMN IF NOT EXISTS logo_path VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS bg_image_path VARCHAR(255) DEFAULT '',
ADD COLUMN IF NOT EXISTS bg_opacity FLOAT DEFAULT 0.9,
ADD COLUMN IF NOT EXISTS bg_blur INT DEFAULT 0;

-- Set default values jika kolom baru saja ditambahkan
UPDATE settings SET 
    bg_opacity = 0.9, 
    bg_blur = 0 
WHERE bg_opacity IS NULL OR bg_blur IS NULL;

-- 4. Update Tabel Admin untuk mendukung ganti password yang lebih aman (kolom sudah ada, pastikan tipe data cukup)
-- Pastikan kolom password di tabel admin cukup panjang (sudah varchar 255 di schema awal)
