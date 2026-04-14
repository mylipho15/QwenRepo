<?php
/**
 * Main Configuration
 * SISDM Absensi Siswa
 */

session_start();
date_default_timezone_set('Asia/Jakarta');

// Database Config
$db_config = require_once __DIR__ . '/database.php';

// Application Settings
define('APP_NAME', 'SISDM Absensi Siswa');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/sisdm-absensi-siswa/');
define('UPLOAD_DIR', __DIR__ . '/../assets/images/uploads/');
define('MAX_LOGO_SIZE', 3 * 1024 * 1024); // 3 MB
define('MAX_LOGO_DIMENSION', 500);

// Create upload directory if not exists
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Database Connection using PDO
try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper Functions
function redirect($url) {
    header("Location: " . $url);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isPetugas() {
    return isLoggedIn() && $_SESSION['role'] === 'petugas';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'modules/auth/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = 'Akses ditolak. Hanya administrator yang dapat mengakses halaman ini.';
        redirect(BASE_URL . 'index.php');
    }
}

function requirePetugas() {
    requireLogin();
    if (!isPetugas() && !isAdmin()) {
        $_SESSION['error'] = 'Akses ditolak. Hanya petugas yang dapat mengakses halaman ini.';
        redirect(BASE_URL . 'index.php');
    }
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function getSchoolInfo() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM sekolah LIMIT 1");
    return $stmt->fetch();
}

function getSettings() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM settings_absensi LIMIT 1");
    return $stmt->fetch();
}

function getCurrentDate() {
    return date('Y-m-d');
}

function getCurrentTime() {
    return date('H:i:s');
}
