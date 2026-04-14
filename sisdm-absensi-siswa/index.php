<?php
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        redirect('modules/admin/dashboard.php');
    } else {
        redirect('modules/petugas/dashboard.php');
    }
}

$school = getSchoolInfo();
$page_title = 'Beranda';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($school['nama_sekolah'] ?? 'SISDM Absensi Siswa'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <nav class="navbar">
            <div class="navbar-brand">
                <?php if (!empty($school['logo_path'])): ?>
                    <img src="assets/images/<?php echo htmlspecialchars($school['logo_path']); ?>" 
                         alt="Logo" class="navbar-logo">
                <?php endif; ?>
                <span><?php echo htmlspecialchars($school['nama_sekolah'] ?? 'SISDM Absensi Siswa'); ?></span>
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
            </ul>
        </nav>
        
        <main class="main-content">
            <div class="login-container" style="min-height: auto; padding: 4rem 2rem;">
                <div class="login-box" style="max-width: 500px;">
                    <div class="login-header">
                        <?php if (!empty($school['logo_path'])): ?>
                            <img src="assets/images/<?php echo htmlspecialchars($school['logo_path']); ?>" 
                                 alt="Logo Sekolah" class="login-logo">
                        <?php endif; ?>
                        <h2>Selamat Datang</h2>
                        <p><?php echo htmlspecialchars($school['nama_sekolah'] ?? 'SISDM Absensi Siswa'); ?></p>
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">
                            Sistem Informasi Manajemen Absensi Siswa
                        </p>
                    </div>
                    
                    <div class="card" style="box-shadow: none; border: 1px solid var(--border-color);">
                        <div class="card-header text-center">Silakan Login</div>
                        
                        <div class="d-flex gap-1 mb-2">
                            <a href="modules/auth/login.php?role=admin" class="btn btn-primary" style="flex: 1; justify-content: center;">
                                👨‍💼 Administrator
                            </a>
                            <a href="modules/auth/login.php?role=petugas" class="btn btn-success" style="flex: 1; justify-content: center;">
                                📝 Petugas Absensi
                            </a>
                        </div>
                        
                        <div style="text-align: center; margin-top: 1.5rem;">
                            <p style="color: var(--text-secondary); font-size: 0.875rem;">
                                Pilih role login di atas untuk melanjutkan
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-2 text-center" style="color: var(--text-secondary); font-size: 0.875rem;">
                        <p><?php echo htmlspecialchars($school['alamat'] ?? ''); ?></p>
                        <p>Telp: <?php echo htmlspecialchars($school['telepon'] ?? '-'); ?> | 
                           Web: <?php echo htmlspecialchars($school['website'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </main>
        
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($school['nama_sekolah'] ?? 'SISDM Absensi Siswa'); ?>. 
               All rights reserved.</p>
        </footer>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Auto-redirect based on URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const role = urlParams.get('role');
        if (role) {
            window.location.href = 'modules/auth/login.php?role=' + role;
        }
    </script>
</body>
</html>
