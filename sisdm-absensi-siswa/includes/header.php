<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'SISDM Absensi Siswa'; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <nav class="navbar">
            <div class="navbar-brand">
                <?php if (!empty($school['logo_path'])): ?>
                    <img src="../../assets/images/<?php echo htmlspecialchars($school['logo_path']); ?>" 
                         alt="Logo" class="navbar-logo">
                <?php endif; ?>
                <span><?php echo htmlspecialchars($school['nama_sekolah'] ?? 'SISDM Absensi'); ?></span>
            </div>
            <ul class="navbar-nav">
                <li>
                    <select id="theme-select" class="form-control" style="padding: 0.4rem;">
                        <option value="fluent">Fluent UI</option>
                        <option value="material">Material UI</option>
                        <option value="glassmorphism">Glassmorphism</option>
                        <option value="cyberpunk">Cyberpunk</option>
                    </select>
                </li>
                <li>
                    <select id="mode-select" class="form-control" style="padding: 0.4rem;">
                        <option value="white">White</option>
                        <option value="light-gray">Light Gray</option>
                        <option value="dark-gray">Dark Gray</option>
                        <option value="black">Black</option>
                        <option value="dark">Dark</option>
                    </select>
                </li>
                <li>
                    <span class="nav-link">Halo, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                </li>
                <li>
                    <a href="../../modules/auth/logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </nav>
        
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="absensi.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'absensi.php' ? 'active' : ''; ?>">
                        📝 Data Absensi
                    </a>
                </li>
                <li>
                    <a href="siswa.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'siswa.php' ? 'active' : ''; ?>">
                        👨‍🎓 Data Siswa
                    </a>
                </li>
                <li>
                    <a href="rekap.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'rekap.php' ? 'active' : ''; ?>">
                        📋 Rekap Absensi
                    </a>
                </li>
                <li>
                    <a href="petugas.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'petugas.php' ? 'active' : ''; ?>">
                        👤 Petugas Harian
                    </a>
                </li>
                <li>
                    <a href="pengaturan.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'pengaturan.php' ? 'active' : ''; ?>">
                        ⚙️ Pengaturan
                    </a>
                </li>
            </ul>
        </aside>
        
        <main class="content-with-sidebar">
