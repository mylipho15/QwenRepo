<?php
/**
 * Login Page Module
 */

$role = $_GET['role'] ?? 'admin';
$error = '';
$success = '';

// Get Auth instance
$auth = Auth::getInstance();

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $loginRole = $_POST['role'] ?? 'admin';
    
    // Perform login using Auth class
    $result = $auth->login($username, $password, $loginRole);
    
    if ($result['success']) {
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<div class="login-container">
    <div class="login-box fade-in">
        <div class="login-header">
            <?php if ($school['logo_path']): ?>
                <img src="<?= htmlspecialchars($school['logo_path']) ?>" alt="Logo Sekolah" class="login-logo">
            <?php endif; ?>
            <h2>Login</h2>
            <p class="text-muted"><?= $role === 'admin' ? 'Administrator' : 'Petugas Absensi' ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus placeholder="Masukkan username">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-2">
                🔐 Login
            </button>
        </form>

        <div class="mt-3 text-center">
            <a href="?page=home" class="text-muted">← Kembali ke Beranda</a>
        </div>

        <div class="mt-2 text-center">
            <small class="text-muted">
                <?php if ($role === 'admin'): ?>
                    Demo Admin: <strong>admin</strong> / <strong>admin123</strong>
                <?php else: ?>
                    Demo Petugas: <strong>petugas1</strong> / <strong>officer123</strong>
                <?php endif; ?>
            </small>
        </div>
        
        <!-- Force Logout Section -->
        <?php if ($auth->isAdmin()): ?>
        <div class="mt-4 pt-3 border-top">
            <p class="text-muted mb-2"><small>⚠️ Session Management</small></p>
            <form method="POST" action="index.php?page=settings&tab=sessions" onsubmit="return confirm('Apakah Anda yakin ingin memaksa logout semua pengguna?');">
                <input type="hidden" name="action" value="force_logout_all">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    🚫 Paksa Logout Semua User
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
