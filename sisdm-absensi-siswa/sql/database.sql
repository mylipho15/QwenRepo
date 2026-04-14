-- SISDM Absensi Siswa Database Schema
-- Single File SQL dengan Sample Data

CREATE DATABASE IF NOT EXISTS sisdm_absensi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sisdm_absensi;

-- Tabel Identitas Sekolah
CREATE TABLE IF NOT EXISTS school_identity (
    id INT PRIMARY KEY AUTO_INCREMENT,
    npsn VARCHAR(20) UNIQUE,
    school_name VARCHAR(255) NOT NULL,
    address TEXT,
    website VARCHAR(255),
    phone VARCHAR(20),
    logo_path VARCHAR(255),
    background_image VARCHAR(255),
    transparency DECIMAL(3,2) DEFAULT 0.95,
    blur_effect INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Users (Admin dan Petugas)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'officer') NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    session_token VARCHAR(64) DEFAULT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Petugas Absensi Harian
CREATE TABLE IF NOT EXISTS attendance_officers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_date_officer (date, user_id)
);

-- Tabel Jurusan
CREATE TABLE IF NOT EXISTS majors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kelas
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    major_id INT,
    academic_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE SET NULL
);

-- Tabel Siswa
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nipd VARCHAR(50) UNIQUE NOT NULL,
    nisn VARCHAR(50) UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    class_id INT NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    birth_place VARCHAR(100),
    birth_date DATE,
    address TEXT,
    phone VARCHAR(20),
    parent_name VARCHAR(255),
    parent_phone VARCHAR(20),
    photo_path VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Tabel Absensi Siswa
CREATE TABLE IF NOT EXISTS attendances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME,
    check_out TIME,
    status ENUM('present', 'sick', 'permission', 'alpha', 'late', 'early_leave') DEFAULT 'present',
    late_minutes INT DEFAULT 0,
    early_leave_minutes INT DEFAULT 0,
    notes TEXT,
    officer_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (officer_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_student_date (student_id, date)
);

-- Tabel Izin Khusus (Telat, Keluar Lingkungan, Pulang Awal)
CREATE TABLE IF NOT EXISTS special_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    permission_type ENUM('late', 'leave_school', 'early_leave') NOT NULL,
    date DATE NOT NULL,
    reason TEXT NOT NULL,
    start_time TIME,
    end_time TIME,
    approved_by INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Pengaturan Sistem
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Sample Data - Identitas Sekolah
INSERT INTO school_identity (npsn, school_name, address, website, phone, transparency, blur_effect) VALUES
('12345678', 'SMA Negeri 1 Teknologi', 'Jl. Pendidikan No. 123, Jakarta Selatan', 'https://sman1teknologi.sch.id', '+62-21-12345678', 0.95, 10);

-- Insert Sample Data - Users (Password: admin123 untuk admin, officer123 untuk petugas)
-- Password hash generated with password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, full_name, role, email, phone) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Sistem', 'admin', 'admin@sman1teknologi.sch.id', '081234567890'),
('petugas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Absensi 1', 'officer', 'petugas1@sman1teknologi.sch.id', '081234567891'),
('petugas2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Absensi 2', 'officer', 'petugas2@sman1teknologi.sch.id', '081234567892');

-- Insert Sample Data - Jurusan
INSERT INTO majors (code, name, description) VALUES
('TKJ', 'Teknik Komputer dan Jaringan', 'Jurusan teknik komputer dan jaringan'),
('RPL', 'Rekayasa Perangkat Lunak', 'Jurusan rekayasa perangkat lunak'),
('AKL', 'Akuntansi Keuangan Lembaga', 'Jurusan akuntansi keuangan lembaga'),
('BC', 'Broadcasting', 'Jurusan broadcasting dan multimedia');

-- Insert Sample Data - Kelas
INSERT INTO classes (name, major_id, academic_year) VALUES
('X-TKJ-1', 1, '2024/2025'),
('X-TKJ-2', 1, '2024/2025'),
('XI-RPL-1', 2, '2024/2025'),
('XI-RPL-2', 2, '2024/2025'),
('XII-AKL-1', 3, '2024/2025'),
('XII-BC-1', 4, '2024/2025');

-- Insert Sample Data - Siswa
INSERT INTO students (nipd, nisn, full_name, class_id, gender, birth_place, birth_date, address, phone, parent_name, parent_phone) VALUES
('2024001', '1234567890', 'Ahmad Rizki Pratama', 1, 'male', 'Jakarta', '2008-05-15', 'Jl. Merdeka No. 1', '081234567801', 'Budi Pratama', '081234567802'),
('2024002', '1234567891', 'Siti Nurhaliza', 1, 'female', 'Bandung', '2008-07-20', 'Jl. Sudirman No. 2', '081234567803', 'Asep Suryadi', '081234567804'),
('2024003', '1234567892', 'Muhammad Fikri', 2, 'male', 'Surabaya', '2008-03-10', 'Jl. Ahmad Yani No. 3', '081234567805', 'Hasan Abdullah', '081234567806'),
('2024004', '1234567893', 'Dewi Sartika', 3, 'female', 'Yogyakarta', '2007-11-25', 'Jl. Malioboro No. 4', '081234567807', 'Joko Susilo', '081234567808'),
('2024005', '1234567894', 'Andi Saputra', 3, 'male', 'Makassar', '2007-09-05', 'Jl. Pettarani No. 5', '081234567809', 'Syamsul Bahri', '081234567810'),
('2024006', '1234567895', 'Putri Anggraini', 4, 'female', 'Semarang', '2007-12-18', 'Jl. Pandanaran No. 6', '081234567811', 'Wahyu Hidayat', '081234567812'),
('2024007', '1234567896', 'Rina Kartika', 5, 'female', 'Medan', '2006-04-22', 'Jl. Gatot Subroto No. 7', '081234567813', 'Rudi Hartono', '081234567814'),
('2024008', '1234567897', 'Bambang Wijaya', 6, 'male', 'Denpasar', '2006-08-30', 'Jl. Gajah Mada No. 8', '081234567815', 'Made Sutrisna', '081234567816');

-- Insert Sample Data - Petugas Absensi Hari Ini
INSERT INTO attendance_officers (user_id, date, status, notes) VALUES
(2, CURDATE(), 'active', 'Petugas absensi hari ini');

-- Insert Sample Data - Absensi (Contoh data minggu ini)
INSERT INTO attendances (student_id, date, check_in, check_out, status, late_minutes, officer_id, notes) VALUES
(1, CURDATE(), '07:15:00', '15:30:00', 'present', 0, 2, ''),
(2, CURDATE(), '07:25:00', '15:30:00', 'late', 10, 2, 'Terlambat karena macet'),
(3, CURDATE(), '07:10:00', '15:30:00', 'present', 0, 2, ''),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:20:00', '15:30:00', 'present', 0, 2, ''),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:15:00', NULL, 'sick', 0, 2, 'Izin sakit'),
(6, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '07:15:00', '14:00:00', 'early_leave', 0, 2, 'Pulang awal ada keperluan keluarga'),
(7, DATE_SUB(CURDATE(), INTERVAL 3 DAY), NULL, NULL, 'alpha', 0, 2, 'Tanpa keterangan'),
(8, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '07:15:00', '15:30:00', 'present', 0, 2, '');

-- Insert Sample Data - Pengaturan Sistem
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('school_logo', '', 'text', 'Path logo sekolah'),
('background_image', '', 'text', 'Path background image'),
('transparency', '0.95', 'number', 'Transparency level (0-1)'),
('blur_effect', '10', 'number', 'Blur effect in pixels'),
('theme', 'fluent-ui', 'text', 'Active theme (fluent-ui, material-ui, glassmorphism, cyberpunk)'),
('color_mode', 'light', 'text', 'Color mode (light, dark-gray, dark, light-gray)'),
('check_in_start', '06:30', 'text', 'Waktu mulai absen masuk'),
('check_in_end', '07:30', 'text', 'Waktu akhir absen masuk (telat setelah ini)'),
('check_out_start', '15:00', 'text', 'Waktu mulai absen pulang'),
('late_threshold', '07:30', 'text', 'Batas waktu terlambat'),
('enable_notifications', '1', 'boolean', 'Enable/disable notifications');

-- Create Indexes for Better Performance
CREATE INDEX idx_attendances_date ON attendances(date);
CREATE INDEX idx_attendances_student ON attendances(student_id);
CREATE INDEX idx_students_class ON students(class_id);
CREATE INDEX idx_special_permissions_date ON special_permissions(date);
CREATE INDEX idx_users_role ON users(role);
