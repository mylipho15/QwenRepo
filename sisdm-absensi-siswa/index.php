<?php
/**
 * SISDM Absensi Siswa - Main Entry Point
 * Sistem Absensi Siswa Berbasis Web
 */

// Load dependencies - Order matters!
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/libs/Config.php';
require_once __DIR__ . '/libs/Validator.php';

// Initialize Auth (will start session internally)
$auth = Auth::getInstance();

// Get page parameter
$page = $_GET['page'] ?? 'home';

// Handle logout action
if ($page === 'logout') {
    $auth->logout();
    header('Location: index.php');
    exit;
}

// Get database instance to test connection
$db = Database::getInstance();

// Get school identity
$school = $db->fetchOne("SELECT * FROM school_identity LIMIT 1");
$settings = [];
$settingsQuery = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings");
foreach ($settingsQuery as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Theme and color mode from settings or default
$theme = $settings['theme'] ?? 'fluent-ui';
$colorMode = $settings['color_mode'] ?? 'light';
$transparency = $settings['transparency'] ?? 0.95;
$blur = $settings['blur_effect'] ?? 10;
$bgImage = $settings['background_image'] ?? '';

?>
<!DOCTYPE html>
<html lang="id" data-theme="<?= htmlspecialchars($theme) ?>" data-color-mode="<?= htmlspecialchars($colorMode) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($school['school_name'] ?? 'SISDM Absensi') ?> - Sistem Absensi Siswa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --app-transparency: <?= $transparency ?>;
            --app-blur: <?= $blur ?>px;
        }
        <?php if ($bgImage): ?>
        body {
            background-image: url('<?= htmlspecialchars($bgImage) ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        <?php endif; ?>
    </style>
</head>
<body class="<?= $bgImage ? 'bg-image' : '' ?>">
    
    <?php if ($page === 'home'): ?>
        <!-- Home Page -->
        <?php include 'modules/home.php'; ?>
        
    <?php elseif ($page === 'login'): ?>
        <!-- Login Page -->
        <?php include 'modules/login.php'; ?>
        
    <?php elseif ($page === 'dashboard'): ?>
        <!-- Dashboard -->
        <?php $auth->requireLogin(); ?>
        <?php include 'modules/dashboard.php'; ?>
        
    <?php elseif ($page === 'students'): ?>
        <!-- Student Management -->
        <?php $auth->requireAdmin(); ?>
        <?php include 'modules/students.php'; ?>
        
    <?php elseif ($page === 'attendance'): ?>
        <!-- Attendance Management -->
        <?php $auth->requireLogin(); ?>
        <?php include 'modules/attendance.php'; ?>
        
    <?php elseif ($page === 'reports'): ?>
        <!-- Reports -->
        <?php $auth->requireAdmin(); ?>
        <?php include 'modules/reports.php'; ?>
        
    <?php elseif ($page === 'settings'): ?>
        <!-- Settings -->
        <?php $auth->requireAdmin(); ?>
        <?php include 'modules/settings.php'; ?>
        
    <?php elseif ($page === 'officers'): ?>
        <!-- Officer Management -->
        <?php $auth->requireAdmin(); ?>
        <?php include 'modules/officers.php'; ?>
        
    <?php else: ?>
        <!-- 404 Page -->
        <?php include 'modules/404.php'; ?>
    <?php endif; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
