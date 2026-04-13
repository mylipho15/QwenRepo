-- Database Schema untuk Sistem Absensi SMK
-- Versi: 1.0
-- Dibuat: 2025

-- ============================================
-- 1. TABEL USERS (Authentication)
-- ============================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'guru', 'siswa', 'orang_tua') NOT NULL DEFAULT 'siswa',
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. TABEL KELAS
-- ============================================
CREATE TABLE classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL COMMENT 'Contoh: X-RPL-1, XI-TKJ-2',
    tingkat ENUM('X', 'XI', 'XII') NOT NULL,
    jurusan VARCHAR(100) NOT NULL COMMENT 'Contoh: RPL, TKJ, AKL, TSM',
    tahun_ajaran VARCHAR(20) NOT NULL COMMENT 'Contoh: 2024/2025',
    wali_kelas_id BIGINT UNSIGNED NULL,
    kapasitas INT DEFAULT 36,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wali_kelas_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_nama_kelas (nama_kelas),
    INDEX idx_tingkat (tingkat),
    INDEX idx_jurusan (jurusan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABEL SISWA
-- ============================================
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    nis VARCHAR(20) UNIQUE NOT NULL,
    nisn VARCHAR(20) UNIQUE NULL,
    nama_lengkap VARCHAR(255) NOT NULL,
    nama_panggilan VARCHAR(100) NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(100) NULL,
    tanggal_lahir DATE NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    agama VARCHAR(50) NULL,
    alamat TEXT NULL,
    rt_rw VARCHAR(20) NULL,
    kelurahan VARCHAR(100) NULL,
    kecamatan VARCHAR(100) NULL,
    kota VARCHAR(100) NULL,
    provinsi VARCHAR(100) NULL,
    kode_pos VARCHAR(10) NULL,
    foto_url VARCHAR(255) NULL COMMENT 'Path ke foto siswa',
    no_hp_siswa VARCHAR(20) NULL,
    no_hp_orang_tua VARCHAR(20) NOT NULL,
    email_orang_tua VARCHAR(255) NULL,
    nama_ayah VARCHAR(255) NULL,
    nama_ibu VARCHAR(255) NULL,
    pekerjaan_ayah VARCHAR(100) NULL,
    pekerjaan_ibu VARCHAR(100) NULL,
    status_keluarga ENUM('utuh', 'yatim', 'piatu', 'yatim_piatu') DEFAULT 'utuh',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES classes(id) ON DELETE RESTRICT,
    INDEX idx_nis (nis),
    INDEX idx_nisn (nisn),
    INDEX idx_kelas (kelas_id),
    INDEX idx_nama (nama_lengkap)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABEL JADWAL SEKOLAH
-- ============================================
CREATE TABLE schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kelas_id BIGINT UNSIGNED NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    jam_masuk TIME NOT NULL DEFAULT '07:00:00',
    jam_pulang TIME NOT NULL DEFAULT '15:00:00',
    toleransi_terlambat INT DEFAULT 15 COMMENT 'Dalam menit',
    is_active BOOLEAN DEFAULT TRUE,
    tanggal_mulai DATE NULL COMMENT 'Jika ada jadwal khusus periode tertentu',
    tanggal_selesai DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kelas_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_kelas_hari (kelas_id, hari),
    UNIQUE KEY unique_kelas_hari (kelas_id, hari, tanggal_mulai, tanggal_selesai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABEL ABSENSI (MAIN TABLE)
-- ============================================
CREATE TABLE attendance_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    tanggal DATE NOT NULL,
    jam_datang TIME NULL,
    datetime_datang DATETIME NULL,
    jam_pulang TIME NULL,
    datetime_pulang DATETIME NULL,
    status ENUM('hadir', 'izin', 'sakit', 'alpha', 'terlambat', 'pulang_cepat') NOT NULL DEFAULT 'alpha',
    keterangan TEXT NULL,
    
    -- Data Check-in
    foto_checkin_url VARCHAR(255) NULL,
    gps_latitude_in DECIMAL(10, 8) NULL,
    gps_longitude_in DECIMAL(11, 8) NULL,
    device_info_in VARCHAR(255) NULL COMMENT 'Browser, OS, Device',
    ip_address_in VARCHAR(45) NULL,
    method_checkin ENUM('qr_code', 'manual', 'face_recognition', 'mobile_app') NULL,
    
    -- Data Check-out
    foto_checkout_url VARCHAR(255) NULL,
    gps_latitude_out DECIMAL(10, 8) NULL,
    gps_longitude_out DECIMAL(11, 8) NULL,
    device_info_out VARCHAR(255) NULL,
    ip_address_out VARCHAR(45) NULL,
    method_checkout ENUM('qr_code', 'manual', 'mobile_app') NULL,
    
    -- Validasi
    validated_by BIGINT UNSIGNED NULL COMMENT 'ID guru yang validasi jika manual',
    validated_at TIMESTAMP NULL,
    is_valid BOOLEAN DEFAULT TRUE,
    
    -- Durasi (dalam menit, dihitung otomatis)
    durasi_hadir INT NULL COMMENT 'Durasi dalam menit',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_tanggal (tanggal),
    INDEX idx_student_tanggal (student_id, tanggal),
    INDEX idx_status (status),
    UNIQUE KEY unique_student_tanggal (student_id, tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. TABEL IZIN / SURAT KETERANGAN
-- ============================================
CREATE TABLE leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    jenis_izin ENUM('sakit', 'izin', 'dispensasi') NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    alasan TEXT NOT NULL,
    file_bukti VARCHAR(255) NULL COMMENT 'Scan surat dokter/surat izin',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student (student_id),
    INDEX idx_status (status),
    INDEX idx_tanggal (tanggal_mulai, tanggal_selesai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TABEL NOTIFIKASI
-- ============================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('absensi', 'izin', 'pengumuman', 'reminder', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL COMMENT 'Additional data dalam format JSON',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    sent_via ENUM('email', 'whatsapp', 'push_notification', 'sms') NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. TABEL LOG AKTIVITAS (AUDIT TRAIL)
-- ============================================
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL COMMENT 'Contoh: login, checkin, checkout, export_data',
    description TEXT NULL,
    table_name VARCHAR(50) NULL,
    record_id BIGINT NULL,
    old_value JSON NULL,
    new_value JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. TABEL QR CODE (Untuk Dynamic QR)
-- ============================================
CREATE TABLE qr_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) UNIQUE NOT NULL,
    type ENUM('checkin', 'checkout') NOT NULL,
    location VARCHAR(100) NOT NULL COMMENT 'Lokasi QR Code dipasang',
    is_active BOOLEAN DEFAULT TRUE,
    expires_at DATETIME NULL COMMENT 'Jika QR code bersifat temporary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. TABEL PENGATURAN SISTEM
-- ============================================
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    value TEXT NULL,
    type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description VARCHAR(255) NULL,
    category VARCHAR(50) NULL COMMENT 'Contoh: general, attendance, notification',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (key_name),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DATA AWAL (SEEDING)
-- ============================================

-- Admin default
INSERT INTO users (email, password, role, is_active) VALUES 
('admin@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);
-- Password default: password (hash bcrypt)

-- Pengaturan default
INSERT INTO settings (key_name, value, type, description, category) VALUES
('school_name', 'SMK Negeri Contoh', 'string', 'Nama sekolah', 'general'),
('school_address', 'Jl. Pendidikan No. 123', 'string', 'Alamat sekolah', 'general'),
('checkin_start_time', '06:30:00', 'string', 'Jam mulai check-in', 'attendance'),
('checkin_end_time', '07:30:00', 'string', 'Batas akhir check-in (setelah ini terlambat)', 'attendance'),
('checkout_start_time', '14:30:00', 'string', 'Jam mulai check-out', 'attendance'),
('checkout_end_time', '16:00:00', 'string', 'Batas akhir check-out', 'attendance'),
('enable_face_recognition', 'false', 'boolean', 'Aktifkan validasi wajah', 'attendance'),
('enable_gps_validation', 'true', 'boolean', 'Aktifkan validasi GPS', 'attendance'),
('gps_radius_meters', '100', 'number', 'Radius GPS dalam meter', 'attendance'),
('whatsapp_enabled', 'true', 'boolean', 'Aktifkan notifikasi WhatsApp', 'notification'),
('whatsapp_api_url', 'https://api.fonnte.com/send', 'string', 'API URL WhatsApp Gateway', 'notification'),
('whatsapp_token', '', 'string', 'Token WhatsApp Gateway', 'notification'),
('email_enabled', 'true', 'boolean', 'Aktifkan notifikasi email', 'notification'),
('smtp_host', 'smtp.gmail.com', 'string', 'SMTP Server', 'notification'),
('smtp_port', '587', 'number', 'SMTP Port', 'notification'),
('auto_notify_parents', 'true', 'boolean', 'Otomatis kirim notifikasi ke orang tua', 'notification');

-- ============================================
-- VIEW UNTUK REPORTING
-- ============================================

-- View rekap absensi harian per kelas
CREATE OR REPLACE VIEW daily_attendance_summary AS
SELECT 
    c.id AS class_id,
    c.nama_kelas,
    c.jurusan,
    ar.tanggal,
    COUNT(DISTINCT ar.student_id) AS total_siswa,
    SUM(CASE WHEN ar.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
    SUM(CASE WHEN ar.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
    SUM(CASE WHEN ar.status = 'izin' THEN 1 ELSE 0 END) AS izin,
    SUM(CASE WHEN ar.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
    SUM(CASE WHEN ar.status = 'alpha' THEN 1 ELSE 0 END) AS alpha,
    ROUND((SUM(CASE WHEN ar.status IN ('hadir', 'terlambat') THEN 1 ELSE 0 END) / COUNT(DISTINCT ar.student_id)) * 100, 2) AS persentase_kehadiran
FROM attendance_records ar
JOIN students s ON ar.student_id = s.id
JOIN classes c ON s.kelas_id = c.id
GROUP BY c.id, c.nama_kelas, c.jurusan, ar.tanggal;

-- View statistik bulanan per siswa
CREATE OR REPLACE VIEW monthly_student_statistics AS
SELECT 
    s.id AS student_id,
    s.nis,
    s.nama_lengkap,
    c.nama_kelas,
    YEAR(ar.tanggal) AS tahun,
    MONTH(ar.tanggal) AS bulan,
    COUNT(ar.id) AS total_hari,
    SUM(CASE WHEN ar.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
    SUM(CASE WHEN ar.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
    SUM(CASE WHEN ar.status IN ('izin', 'sakit') THEN 1 ELSE 0 END) AS izin_sakit,
    SUM(CASE WHEN ar.status = 'alpha' THEN 1 ELSE 0 END) AS alpha,
    AVG(ar.durasi_hadir) AS rata_rata_durasi,
    MIN(ar.jam_datang) AS earliest_checkin,
    MAX(ar.jam_datang) AS latest_checkin
FROM students s
JOIN classes c ON s.kelas_id = c.id
LEFT JOIN attendance_records ar ON s.id = ar.student_id
WHERE ar.tanggal IS NOT NULL
GROUP BY s.id, s.nis, s.nama_lengkap, c.nama_kelas, YEAR(ar.tanggal), MONTH(ar.tanggal);

-- ============================================
-- TRIGGER UNTUK AUTO-CALCULATE DURASI
-- ============================================

DELIMITER $$

CREATE TRIGGER calculate_attendance_duration
BEFORE UPDATE ON attendance_records
FOR EACH ROW
BEGIN
    IF NEW.jam_pulang IS NOT NULL AND NEW.jam_datang IS NOT NULL THEN
        SET NEW.durasi_hadir = TIMESTAMPDIFF(MINUTE, 
            CONCAT(NEW.tanggal, ' ', NEW.jam_datang),
            CONCAT(NEW.tanggal, ' ', NEW.jam_pulang)
        );
    END IF;
END$$

DELIMITER ;

-- ============================================
-- CATATAN PENTING
-- ============================================
-- 1. Pastikan timezone database sesuai dengan lokasi sekolah
-- 2. Backup database secara berkala (daily/weekly)
-- 3. Index sudah dioptimalkan untuk query umum
-- 4. Gunakan connection pooling untuk performa lebih baik
-- 5. Implementasikan soft delete jika diperlukan
-- 6. Tambahkan partitioning pada tabel attendance_records jika data sudah sangat besar (>1 juta record)
