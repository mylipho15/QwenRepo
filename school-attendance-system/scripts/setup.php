#!/usr/bin/env php
<?php
/**
 * Setup Script for School Attendance System
 * 
 * This script helps with the initial setup of the application.
 * Run this script after installing dependencies via Composer.
 */

echo "==============================================\n";
echo "  School Attendance System - Setup Wizard\n";
echo "==============================================\n\n";

// Check PHP version
echo "Checking PHP version...\n";
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "ERROR: PHP 7.4 or higher is required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}
echo "✓ PHP Version: " . PHP_VERSION . "\n\n";

// Check required extensions
$required_extensions = ['mysqli', 'json', 'session'];
echo "Checking required extensions...\n";
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ Extension $ext is loaded\n";
    } else {
        echo "✗ ERROR: Extension $ext is not loaded\n";
        exit(1);
    }
}
echo "\n";

// Check writable directories
echo "Checking directory permissions...\n";
$writable_dirs = [
    __DIR__ . '/../config',
    __DIR__ . '/../images',
];

foreach ($writable_dirs as $dir) {
    if (is_writable($dir)) {
        echo "✓ Directory " . basename($dir) . " is writable\n";
    } else {
        echo "⚠ Warning: Directory " . basename($dir) . " is not writable\n";
    }
}
echo "\n";

// Database configuration
echo "Database Configuration\n";
echo "----------------------\n";
echo "Please configure your database settings in config/database.php\n";
echo "Default settings:\n";
echo "  - Host: localhost\n";
echo "  - User: root\n";
echo "  - Password: (empty)\n";
echo "  - Database: school_attendance\n\n";

// Create sample configuration file if it doesn't exist
$config_file = __DIR__ . '/../config/database.php';
if (!file_exists($config_file)) {
    echo "Creating default database configuration...\n";
    $sample_config = '<?php
// Database Configuration
define(\'DB_HOST\', \'localhost\');
define(\'DB_USER\', \'root\');
define(\'DB_PASS\', \'\');
define(\'DB_NAME\', \'school_attendance\');

// School Identity
define(\'SCHOOL_NPSN\', \'12345678\');
define(\'SCHOOL_NAME\', \'SMK Teknologi Nusantara\');
define(\'SCHOOL_ADDRESS\', \'Jl. Pendidikan No. 123, Jakarta\');
define(\'SCHOOL_WEBSITE\', \'https://smkteknologi.sch.id\');
define(\'SCHOOL_PHONE\', \'(021) 1234-5678\');

// Application Settings
define(\'APP_NAME\', \'Sistem Absensi Siswa\');
define(\'APP_VERSION\', \'1.0.0\');

// ... rest of the configuration
';
    file_put_contents($config_file, $sample_config);
    echo "✓ Configuration file created\n\n";
}

// Instructions
echo "==============================================\n";
echo "  Next Steps\n";
echo "==============================================\n\n";
echo "1. Make sure MySQL/MariaDB is running on your server\n";
echo "2. Update database credentials in config/database.php if needed\n";
echo "3. Copy the application to your web server document root\n";
echo "4. Access the application through your web browser\n";
echo "5. The database will be created automatically on first access\n\n";
echo "Default Login Credentials:\n";
echo "  Username: admin\n";
echo "  Password: admin123\n\n";
echo "==============================================\n";
echo "  Setup Complete!\n";
echo "==============================================\n";

exit(0);
