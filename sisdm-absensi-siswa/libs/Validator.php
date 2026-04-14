<?php
/**
 * SISDM Validator Library
 * Input validation utilities
 */

class Validator {
    
    public static function isEmpty($value) {
        return empty(trim($value));
    }
    
    public static function isEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function isPhone($phone) {
        return preg_match('/^[0-9+\-\s()]+$/', $phone);
    }
    
    public static function isDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    public static function isTime($time) {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }
    
    public static function minLength($value, $min) {
        return strlen(trim($value)) >= $min;
    }
    
    public static function maxLength($value, $max) {
        return strlen(trim($value)) <= $max;
    }
    
    public static function isNumeric($value) {
        return is_numeric($value);
    }
    
    public static function sanitize($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateImage($file, $maxSize = 3145728, $maxDimension = 500) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'File tidak valid'];
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['valid' => false, 'message' => 'Format file harus JPG/PNG'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'Ukuran file maksimal ' . ($maxSize / 1024 / 1024) . 'MB'];
        }
        
        $imgInfo = getimagesize($file['tmp_name']);
        if ($imgInfo[0] > $maxDimension || $imgInfo[1] > $maxDimension) {
            return ['valid' => false, 'message' => 'Dimensi gambar maksimal ' . $maxDimension . 'x' . $maxDimension . ' pixel'];
        }
        
        return ['valid' => true, 'message' => 'Valid'];
    }
}
