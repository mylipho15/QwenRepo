<?php
/**
 * Login Page - SISDM Absensi Siswa
 */

require_once '../../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(BASE_URL . 'modules/admin/dashboard.php');
    } else {
        redirect(BASE_URL . 'modules/petugas/dashboard.php');
    }
}

$school = getSchoolInfo();
$settings = getSettings();
$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } elseif (empty($role)) {
        $error = 'Silakan pilih role login!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
            $stmt->execute([$username, $role]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect(BASE_URL . 'modules/admin/dashboard.php');
                } else {
                    redirect(BASE_URL . 'modules/petugas/dashboard.php');
                }
            } else {
                $error = 'Username atau password salah!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        body {
            background-image: url('<?= BASE_URL ?>assets/images/bg-default.jpg');
            background-size: cover;
            background-position: center;
        }
        .login-container {
            background: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body data-theme="<?= $settings['theme'] ?? 'fluent' ?>" data-mode="<?= $settings['mode'] ?? 'white' ?>">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <?php if ($school && $school['logo_path']): ?>
                    <img src="<?= BASE_URL . $school['logo_path'] ?>" alt="Logo Sekolah">
                <?php else: ?>
                    <div style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;">🏫</div>
                <?php endif; ?>
                <h2><?= $school['nama_sekolah'] ?? APP_NAME ?></h2>
                <p style="color: var(--text-secondary);">Sistem Absensi Siswa</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Role Login</label>
                    <select name="role" class="form-control" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Administrator</option>
                        <option value="petugas">Petugas Absensi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <span style="margin-right: 8px;">🔐</span> Login
                </button>

                <div class="text-center" style="margin-top: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
                    <p>Demo Login:</p>
                    <p>Admin: <strong>admin / admin123</strong></p>
                    <p>Petugas: <strong>petugas1 / petugas123</strong></p>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
