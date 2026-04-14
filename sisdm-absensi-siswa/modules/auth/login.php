<?php
require_once '../../config/database.php';

$error = '';
$success = '';
$role = $_GET['role'] ?? $_POST['role'] ?? 'admin';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
            $stmt->execute([$username, $role]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('../petugas/dashboard.php');
                }
            } else {
                $error = 'Username atau password salah!';
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}

$school = getSchoolInfo();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($school['nama_sekolah'] ?? 'SISDM Absensi'); ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <?php if (!empty($school['logo_path'])): ?>
                    <img src="../../assets/images/<?php echo htmlspecialchars($school['logo_path']); ?>" 
                         alt="Logo Sekolah" class="login-logo">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($school['nama_sekolah'] ?? 'SISDM Absensi Siswa'); ?></h2>
                <p>Silakan login untuk melanjutkan</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                
                <div class="form-group">
                    <label class="form-label">Login Sebagai</label>
                    <div class="card" style="padding: 1rem; text-align: center;">
                        <strong><?php echo $role === 'admin' ? '👨‍💼 Administrator' : '📝 Petugas Absensi'; ?></strong>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Masukkan username" required autofocus 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Login
                </button>
            </form>
            
            <div class="mt-2 text-center">
                <small>Kembali ke <a href="../../index.php">Beranda</a></small>
            </div>
            
            <div class="mt-2" style="font-size: 0.8rem; color: var(--text-secondary);">
                <p><strong>Demo Credentials:</strong></p>
                <p>Admin: admin / admin123</p>
                <p>Petugas: petugas / petugas123</p>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
