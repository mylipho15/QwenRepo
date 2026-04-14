<?php
/**
 * Login Page Module
 */

$role = $_GET['role'] ?? 'admin';
$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $loginRole = $_POST['role'] ?? 'admin';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === $loginRole || $loginRole === 'any') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: index.php?page=dashboard');
                exit;
            } else {
                $error = 'Anda tidak memiliki akses sebagai ' . ($loginRole === 'admin' ? 'Administrator' : 'Petugas');
            }
        } else {
            $error = 'Username atau password salah!';
        }
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
    </div>
</div>
