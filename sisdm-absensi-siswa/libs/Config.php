<?php
/**
 * SISDM Configuration Library
 * Central configuration management
 */

class Config {
    private static $instance = null;
    private $config = [];
    
    private function __construct() {
        $this->loadConfig();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadConfig() {
        $this->config = [
            'app_name' => 'SISDM Absensi Siswa',
            'app_version' => '1.0.0',
            'base_url' => '/',
            'upload_max_size' => 3 * 1024 * 1024,
            'allowed_image_types' => ['image/jpeg', 'image/png', 'image/jpg'],
            'max_logo_dimension' => 500,
            'session_timeout' => 3600,
            'password_min_length' => 6,
            'default_theme' => 'fluent-ui',
            'default_color_mode' => 'light'
        ];
    }
    
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    public function getAll() {
        return $this->config;
    }
    
    public function set($key, $value) {
        $this->config[$key] = $value;
    }
}
