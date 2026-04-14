<?php
/**
 * Database Configuration
 * Compatible with Laragon 6.0.0 (MySQL 8.0.30)
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sisdm_absensi');
define('DB_CHARSET', 'utf8mb4');

// Create database connection
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// Helper functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isPetugas() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'petugas';
}

function checkAuth($requiredRole = null) {
    session_start();
    if(!isLoggedIn()) {
        redirect('../auth/login.php');
    }
    if($requiredRole === 'admin' && !isAdmin()) {
        redirect('../auth/unauthorized.php');
    }
    if($requiredRole === 'petugas' && !isPetugas() && !isAdmin()) {
        redirect('../auth/unauthorized.php');
    }
}

function getSchoolInfo() {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM sekolah LIMIT 1");
    return $stmt->fetch();
}

function getCurrentDate() {
    return date('Y-m-d');
}

function getCurrentTime() {
    return date('H:i:s');
}
?>
