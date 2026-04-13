<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_attendance');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Initialize database with comprehensive schema
function initializeDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database
    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_NAME);
    
    // Read and execute SQL file if exists
    $sqlFile = __DIR__ . '/../sql/install.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $conn->multi_query($sql);
        
        // Process all results
        while ($conn->more_results()) {
            $conn->next_result();
        }
    } else {
        // Fallback to inline table creation if SQL file doesn't exist
        createTablesFallback($conn);
    }
    
    $conn->close();
}

// Fallback table creation
function createTablesFallback($conn) {
    $tables = [
        "settings" => "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            npsn VARCHAR(20) DEFAULT '',
            school_name VARCHAR(100) DEFAULT 'Sekolah Demo',
            address TEXT,
            website VARCHAR(100) DEFAULT '',
            phone VARCHAR(20) DEFAULT '',
            check_in_start TIME DEFAULT '06:30:00',
            check_in_end TIME DEFAULT '07:30:00',
            check_out_start TIME DEFAULT '14:00:00',
            theme_mode ENUM('light','dark') DEFAULT 'light',
            theme_style ENUM('fluent','material','glass','cyberpunk') DEFAULT 'fluent',
            bg_opacity DECIMAL(3,2) DEFAULT '0.90',
            bg_blur INT DEFAULT '5',
            logo_path VARCHAR(255) DEFAULT NULL,
            bg_image_path VARCHAR(255) DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','officer') NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "students" => "CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nipd VARCHAR(20) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            class VARCHAR(20) NOT NULL,
            major VARCHAR(50) DEFAULT 'Umum',
            status ENUM('active','inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "officer_schedules" => "CREATE TABLE IF NOT EXISTS officer_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date DATE NOT NULL,
            user_id INT NOT NULL,
            shift ENUM('morning','afternoon','full') DEFAULT 'full',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_date (date),
            INDEX idx_user (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "attendance" => "CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            date DATE NOT NULL,
            time_in TIME DEFAULT NULL,
            time_out TIME DEFAULT NULL,
            status ENUM('present','late','early_leave','sick','permission','alpha') DEFAULT 'present',
            notes TEXT,
            recorded_by INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_student_date (student_id, date),
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $table_name => $sql) {
        $conn->query($sql);
    }
    
    // Insert default data
    insertDefaultData($conn);
}

// Insert default data
function insertDefaultData($conn) {
    // Default settings
    $conn->query("INSERT INTO settings (id, npsn, school_name, address, website, phone) 
                  VALUES (1, '12345678', 'SMK Teknologi Nusantara', 'Jl. Pendidikan No. 1, Jakarta', 'https://smkteknologi.sch.id', '021-555-0199')
                  ON DUPLICATE KEY UPDATE school_name=school_name");
    
    // Default users (password: admin123 and petugas123)
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $officer_hash = password_hash('petugas123', PASSWORD_DEFAULT);
    
    $conn->query("INSERT INTO users (username, password, role, full_name) 
                  VALUES ('admin', '$admin_hash', 'admin', 'Administrator Utama')
                  ON DUPLICATE KEY UPDATE username=username");
    
    $conn->query("INSERT INTO users (username, password, role, full_name) 
                  VALUES ('petugas', '$officer_hash', 'officer', 'Petugas Absensi 1')
                  ON DUPLICATE KEY UPDATE username=username");
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

function isOfficer() {
    return isLoggedIn() && $_SESSION['role'] === 'officer';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
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

function requireOfficer() {
    requireLogin();
    if (!isOfficer() && !isAdmin()) {
        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}

function logout() {
    startSession();
    session_unset();
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
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function getSetting($key, $default = null) {
    static $cache = [];
    
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        $conn->close();
        
        // Map database columns to setting keys
        $mapping = [
            'npsn' => 'npsn',
            'school_name' => 'school_name',
            'address' => 'address',
            'website' => 'website',
            'phone' => 'phone',
            'check_in_start' => 'check_in_start',
            'check_in_end' => 'check_in_end',
            'check_out_start' => 'check_out_start',
            'theme_mode' => 'theme_mode',
            'theme_style' => 'theme_style',
            'bg_opacity' => 'bg_opacity',
            'bg_blur' => 'bg_blur',
            'logo_path' => 'logo_path',
            'bg_image_path' => 'bg_image_path'
        ];
        
        if (isset($mapping[$key]) && isset($row[$mapping[$key]])) {
            $cache[$key] = $row[$mapping[$key]];
            return $cache[$key];
        }
        
        return $default;
    }
    
    $stmt->close();
    $conn->close();
    return $default;
}

function updateSetting($key, $value) {
    $conn = getDBConnection();
    
    // Map setting keys to database columns
    $mapping = [
        'npsn' => 'npsn',
        'school_name' => 'school_name',
        'address' => 'address',
        'website' => 'website',
        'phone' => 'phone',
        'check_in_start' => 'check_in_start',
        'check_in_end' => 'check_in_end',
        'check_out_start' => 'check_out_start',
        'theme_mode' => 'theme_mode',
        'theme_style' => 'theme_style',
        'bg_opacity' => 'bg_opacity',
        'bg_blur' => 'bg_blur',
        'logo_path' => 'logo_path',
        'bg_image_path' => 'bg_image_path'
    ];
    
    if (!isset($mapping[$key])) {
        $conn->close();
        return false;
    }
    
    $column = $mapping[$key];
    $stmt = $conn->prepare("UPDATE settings SET $column = ? WHERE id = 1");
    $stmt->bind_param("s", $value);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}

function getAllSettings() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM settings WHERE id = 1");
    
    if ($row = $result->fetch_assoc()) {
        $conn->close();
        return $row;
    }
    
    $conn->close();
    return [];
}

// Auto-initialize database on include
initializeDatabase();
?>
