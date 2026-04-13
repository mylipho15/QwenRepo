<?php
require_once 'config/database.php';
startSession();

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('petugas/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="fluent" data-mode="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Selamat Datang</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="school-info">
                <h3><i class="fas fa-school"></i> <?php echo SCHOOL_NAME; ?></h3>
                <p>NPSN: <?php echo SCHOOL_NPSN; ?></p>
                <p><?php echo SCHOOL_ADDRESS; ?></p>
                <p><i class="fas fa-phone"></i> <?php echo SCHOOL_PHONE; ?></p>
                <p><i class="fas fa-globe"></i> <?php echo SCHOOL_WEBSITE; ?></p>
            </div>
            
            <div class="login-header">
                <h1><i class="fas fa-sign-in-alt"></i> Login</h1>
                <p>Silakan masuk untuk melanjutkan</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    switch($_GET['error']) {
                        case 'invalid': echo 'Username atau password salah!'; break;
                        case 'logout': echo 'Anda telah berhasil logout.'; break;
                        case 'unauthorized': echo 'Anda tidak memiliki akses ke halaman ini.'; break;
                        default: echo 'Terjadi kesalahan.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Masukkan username" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="text-center mt-3">
                <p class="text-muted" style="font-size: 0.85rem;">
                    Default Admin: username: <strong>admin</strong>, password: <strong>admin123</strong>
                </p>
            </div>

            <div class="text-center mt-2">
                <button class="theme-toggle btn btn-sm btn-secondary">
                    <i class="fas fa-palette"></i> Ganti Tema
                </button>
                <button class="mode-toggle btn btn-sm btn-secondary">
                    <i class="fas fa-moon"></i> Ganti Mode
                </button>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>
