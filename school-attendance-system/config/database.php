<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_attendance');

// School Identity
define('SCHOOL_NPSN', '12345678');
define('SCHOOL_NAME', 'SMK Teknologi Nusantara');
define('SCHOOL_ADDRESS', 'Jl. Pendidikan No. 123, Jakarta');
define('SCHOOL_WEBSITE', 'https://smkteknologi.sch.id');
define('SCHOOL_PHONE', '(021) 1234-5678');

// Application Settings
define('APP_NAME', 'Sistem Absensi Siswa');
define('APP_VERSION', '1.0.0');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Initialize database
function initializeDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database
    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->select_db(DB_NAME);
    
    // Create tables
    $tables = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'petugas') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "students" => "CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nipd VARCHAR(20) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            class VARCHAR(20) NOT NULL,
            major VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "attendance" => "CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            date DATE NOT NULL,
            check_in TIME NULL,
            check_out TIME NULL,
            status ENUM('present', 'sick', 'permission', 'alpha') DEFAULT 'present',
            is_late BOOLEAN DEFAULT FALSE,
            left_early BOOLEAN DEFAULT FALSE,
            notes TEXT NULL,
            recorded_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_attendance (student_id, date)
        )",
        
        "settings" => "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) UNIQUE NOT NULL,
            setting_value TEXT NULL,
            setting_type VARCHAR(20) DEFAULT 'string',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "permissions" => "CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            permission_type ENUM('late', 'leave_school', 'early_leave') NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME NULL,
            reason TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            recorded_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
        )"
    ];
    
    foreach ($tables as $table_name => $sql) {
        $conn->query($sql);
    }
    
    // Insert default admin user (password: admin123)
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO users (username, password, full_name, role) 
                  VALUES ('admin', '$admin_password', 'Administrator', 'admin')");
    
    // Insert default settings
    $settings = [
        ['check_in_start', '07:00', 'time'],
        ['check_in_end', '08:00', 'time'],
        ['check_out_start', '15:00', 'time'],
        ['check_out_end', '16:00', 'time'],
        ['late_threshold', '15', 'number'],
        ['theme', 'fluent', 'string'],
        ['mode', 'light', 'string']
    ];
    
    foreach ($settings as $setting) {
        $conn->query("INSERT IGNORE INTO settings (setting_key, setting_value, setting_type) 
                      VALUES ('{$setting[0]}', '{$setting[1]}', '{$setting[2]}')");
    }
    
    $conn->close();
}

// Session management
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isPetugas() {
    return isLoggedIn() && $_SESSION['role'] === 'petugas';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}

function logout() {
    startSession();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getSetting($key, $default = null) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        $conn->close();
        return $row['setting_value'];
    }
    
    $stmt->close();
    $conn->close();
    return $default;
}

function updateSetting($key, $value) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) 
                            VALUES (?, ?) 
                            ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("sss", $key, $value, $value);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

// Initialize database on first load
initializeDatabase();
?>
