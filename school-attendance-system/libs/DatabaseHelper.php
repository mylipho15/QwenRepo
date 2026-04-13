<?php
/**
 * Database Helper Library
 * Provides additional database utility functions
 */

namespace SchoolAttendance\Libs;

class DatabaseHelper {
    private static $instance = null;
    private $conn;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        require_once __DIR__ . '/../../config/database.php';
        $this->conn = getDBConnection();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute a query and return result
     */
    public function query($sql, $params = []) {
        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        } else {
            return $this->conn->query($sql);
        }
    }
    
    /**
     * Fetch all rows from a query
     */
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result === false) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Fetch single row from a query
     */
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result === false) {
            return null;
        }
        return $result->fetch_assoc();
    }
    
    /**
     * Insert data into table
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        // Replace named placeholders with ?
        $sql = preg_replace('/:\w+/', '?', $sql);
        
        $values = array_values($data);
        $types = str_repeat('s', count($values));
        
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new \Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param($types, ...$values);
        $success = $stmt->execute();
        $insert_id = $this->conn->insert_id;
        $stmt->close();
        
        return $success ? $insert_id : false;
    }
    
    /**
     * Update data in table
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new \Exception("Prepare failed: " . $this->conn->error);
        }
        
        $values = array_values($data);
        $allParams = array_merge($values, $whereParams);
        
        $types = str_repeat('s', count($allParams));
        $stmt->bind_param($types, ...$allParams);
        $success = $stmt->execute();
        $affected = $this->conn->affected_rows;
        $stmt->close();
        
        return $success ? $affected : false;
    }
    
    /**
     * Delete data from table
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new \Exception("Prepare failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $success = $stmt->execute();
        $affected = $this->conn->affected_rows;
        $stmt->close();
        
        return $success ? $affected : false;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }
    
    /**
     * Escape string for safe SQL usage
     */
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

/**
 * Validator Class
 * Provides validation functions for form data
 */
class Validator {
    
    /**
     * Validate required field
     */
    public static function required($value) {
        return !empty(trim($value));
    }
    
    /**
     * Validate email format
     */
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate minimum length
     */
    public static function minLength($value, $min) {
        return strlen($value) >= $min;
    }
    
    /**
     * Validate maximum length
     */
    public static function maxLength($value, $max) {
        return strlen($value) <= $max;
    }
    
    /**
     * Validate numeric value
     */
    public static function numeric($value) {
        return is_numeric($value);
    }
    
    /**
     * Validate phone number (Indonesian format)
     */
    public static function phone($value) {
        return preg_match('/^(\+62|62|0)[0-9]{9,12}$/', preg_replace('/[^0-9]/', '', $value));
    }
    
    /**
     * Validate NIS/NIPD format
     */
    public static function nipd($value) {
        return preg_match('/^[0-9]{6,20}$/', $value);
    }
    
    /**
     * Validate date format
     */
    public static function date($value, $format = 'Y-m-d') {
        $dateTime = \DateTime::createFromFormat($format, $value);
        return $dateTime && $dateTime->format($format) === $value;
    }
    
    /**
     * Validate time format
     */
    public static function time($value) {
        return preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $value);
    }
    
    /**
     * Validate against custom regex
     */
    public static function regex($value, $pattern) {
        return preg_match($pattern, $value) === 1;
    }
    
    /**
     * Validate multiple rules
     */
    public static function validate($value, $rules) {
        $errors = [];
        
        foreach ($rules as $rule => $param) {
            $method = $rule;
            $args = [$value];
            
            if ($param !== null) {
                $args[] = $param;
            }
            
            if (!call_user_func_array([self::class, $method], $args)) {
                $errors[] = self::getErrorMessage($rule, $param);
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Get error message for rule
     */
    private static function getErrorMessage($rule, $param = null) {
        $messages = [
            'required' => 'Field ini wajib diisi',
            'email' => 'Format email tidak valid',
            'minLength' => "Panjang minimal adalah $param karakter",
            'maxLength' => "Panjang maksimal adalah $param karakter",
            'numeric' => 'Harus berupa angka',
            'phone' => 'Format nomor telepon tidak valid',
            'nipd' => 'Format NIPD tidak valid',
            'date' => 'Format tanggal tidak valid',
            'time' => 'Format waktu tidak valid'
        ];
        
        return $messages[$rule] ?? "Validasi gagal untuk aturan: $rule";
    }
}

/**
 * Session Manager
 * Enhanced session management utilities
 */
class SessionManager {
    
    /**
     * Start session if not started
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Set session value
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     */
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }
    
    /**
     * Flash message - set once, retrieve once
     */
    public static function flash($key, $value = null) {
        self::start();
        
        if ($value === null) {
            // Get and remove
            $flashKey = "flash_$key";
            $flashValue = $_SESSION[$flashKey] ?? null;
            unset($_SESSION[$flashKey]);
            return $flashValue;
        } else {
            // Set
            $_SESSION["flash_$key"] = $value;
        }
    }
    
    /**
     * Regenerate session ID (for security after login)
     */
    public static function regenerate() {
        self::start();
        session_regenerate_id(true);
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        self::start();
        session_destroy();
    }
    
    /**
     * Clear all session data
     */
    public static function clear() {
        self::start();
        $_SESSION = [];
    }
}

/**
 * Logger Class
 * Simple logging utility
 */
class Logger {
    private static $logFile = __DIR__ . '/../../logs/app.log';
    
    /**
     * Log a message
     */
    public static function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";
        
        // Create logs directory if not exists
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Log info message
     */
    public static function info($message) {
        self::log($message, 'INFO');
    }
    
    /**
     * Log warning message
     */
    public static function warning($message) {
        self::log($message, 'WARNING');
    }
    
    /**
     * Log error message
     */
    public static function error($message) {
        self::log($message, 'ERROR');
    }
    
    /**
     * Log debug message
     */
    public static function debug($message) {
        self::log($message, 'DEBUG');
    }
}
