<?php
require_once '../config/database.php';
requireLogin();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id" data-theme="fluent" data-mode="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Panel Petugas</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-school"></i> <?php echo SCHOOL_NAME; ?></h2>
            <p>Panel Petugas Absensi</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Beranda
                </a>
            </li>
            <li class="nav-item">
                <a href="check-in-out.php" class="nav-link <?php echo $current_page === 'check-in-out' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Absensi Masuk/Keluar
                </a>
            </li>
            <li class="nav-item">
                <a href="permission.php" class="nav-link <?php echo $current_page === 'permission' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Absensi Berhalangan
                </a>
            </li>
            <li class="nav-item">
                <a href="special-permission.php" class="nav-link <?php echo $current_page === 'special-permission' ? 'active' : ''; ?>">
                    <i class="fas fa-passport"></i> Izin Khusus
                </a>
            </li>
            <li class="nav-item" style="margin-top: 20px;">
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="sidebar-toggle btn btn-secondary" style="display: none;">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 id="page-title">Dashboard</h1>
            </div>
            <div class="header-right">
                <button class="theme-toggle" title="Ganti Tema (Ctrl+T)">
                    <i class="fas fa-palette"></i>
                </button>
                <button class="mode-toggle" title="Ganti Mode (Ctrl+M)">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 2)); ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
                        <br><small class="text-muted">Petugas Absensi</small>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="container">
