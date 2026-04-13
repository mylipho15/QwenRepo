<?php
include '../includes/admin-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submission for attendance settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'attendance') {
    $settings = [
        'check_in_start' => $_POST['check_in_start'],
        'check_in_end' => $_POST['check_in_end'],
        'check_out_start' => $_POST['check_out_start'],
        'check_out_end' => $_POST['check_out_end'],
        'late_threshold' => (int)$_POST['late_threshold']
    ];

    foreach ($settings as $key => $value) {
        updateSetting($key, $value);
    }

    $message = 'Pengaturan jam absensi berhasil disimpan!';
    $message_type = 'success';
}

// Handle form submission for appearance settings (theme, mode, bg)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'appearance') {
    $settings = [
        'theme' => $_POST['theme'],
        'mode' => $_POST['mode'],
        'bg_opacity' => floatval($_POST['bg_opacity']),
        'bg_blur' => (int)$_POST['bg_blur']
    ];

    foreach ($settings as $key => $value) {
        updateSetting($key, $value);
    }

    // Handle logo upload
    if (isset($_FILES['logo_upload']) && $_FILES['logo_upload']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 3 * 1024 * 1024; // 3MB
        $max_dim = 500;
        
        $file_tmp = $_FILES['logo_upload']['tmp_name'];
        $file_name = $_FILES['logo_upload']['name'];
        $file_size = $_FILES['logo_upload']['size'];
        $file_type = $_FILES['logo_upload']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $message = 'Format file harus JPG atau PNG!';
            $message_type = 'error';
        } elseif ($file_size > $max_size) {
            $message = 'Ukuran file maksimal 3 MB!';
            $message_type = 'error';
        } else {
            // Check image dimensions
            $img_info = getimagesize($file_tmp);
            if ($img_info[0] > $max_dim || $img_info[1] > $max_dim) {
                $message = 'Dimensi gambar maksimal 500x500 pixel!';
                $message_type = 'error';
            } else {
                // Generate unique filename
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_filename = 'logo_' . time() . '.' . $ext;
                $upload_path = '../uploads/logos/' . $new_filename;
                
                // Create uploads directory if not exists
                if (!file_exists('../uploads/logos')) {
                    mkdir('../uploads/logos', 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    updateSetting('logo_path', $upload_path);
                    $message = 'Logo berhasil diupload!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengupload logo!';
                    $message_type = 'error';
                }
            }
        }
    }
    
    // Handle background image upload
    if (isset($_FILES['bg_upload']) && $_FILES['bg_upload']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_tmp = $_FILES['bg_upload']['tmp_name'];
        $file_name = $_FILES['bg_upload']['name'];
        $file_size = $_FILES['bg_upload']['size'];
        $file_type = $_FILES['bg_upload']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $message = 'Format file background harus JPG atau PNG!';
            $message_type = 'error';
        } elseif ($file_size > $max_size) {
            $message = 'Ukuran file background maksimal 5 MB!';
            $message_type = 'error';
        } else {
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = 'bg_' . time() . '.' . $ext;
            $upload_path = '../uploads/backgrounds/' . $new_filename;
            
            if (!file_exists('../uploads/backgrounds')) {
                mkdir('../uploads/backgrounds', 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                updateSetting('bg_image_path', $upload_path);
                $message = 'Background berhasil diupload!';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengupload background!';
                $message_type = 'error';
            }
        }
    }

    if (empty($message)) {
        $message = 'Pengaturan tampilan berhasil disimpan!';
        $message_type = 'success';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'password') {
    $role = $_POST['role'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $message = 'Password baru dan konfirmasi tidak cocok!';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Password minimal 6 karakter!';
        $message_type = 'error';
    } else {
        if ($role === 'admin') {
            // Verify current password
            $result = $conn->query("SELECT * FROM users WHERE id = " . $_SESSION['user_id']);
            $user = $result->fetch_assoc();
            
            if (password_verify($current_password, $user['password'])) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $message = 'Password Administrator berhasil diubah!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengubah password!';
                    $message_type = 'error';
                }
                $stmt->close();
            } else {
                $message = 'Password saat ini salah!';
                $message_type = 'error';
            }
        } elseif ($role === 'petugas') {
            // For petugas, admin can change without current password verification
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE role = 'petugas'");
            $stmt->bind_param("s", $hashed);
            if ($stmt->execute()) {
                $message = 'Password Petugas berhasil diubah!';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengubah password!';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Get current settings
$current_settings = [];
$result = $conn->query("SELECT * FROM settings");
while ($row = $result->fetch_assoc()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

// Get petugas list for rotation
$petugas_list = [];
$result = $conn->query("SELECT * FROM petugas ORDER BY nama_lengkap ASC");
while ($row = $result->fetch_assoc()) {
    $petugas_list[] = $row;
}

// Get today's petugas
$today = date('Y-m-d');
$todays_petugas = null;
$result = $conn->query("SELECT p.*, j.keterangan FROM jadwal_petugas j JOIN petugas p ON j.petugas_id = p.id WHERE j.tanggal = '$today'");
if ($row = $result->fetch_assoc()) {
    $todays_petugas = $row;
}

$conn->close();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clock"></i> Pengaturan Jam Absensi</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="attendance">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Jam Masuk Mulai</label>
                    <input type="time" name="check_in_start" class="form-control" 
                           value="<?php echo $current_settings['check_in_start'] ?? '07:00'; ?>">
                    <small class="text-muted">Waktu mulai absensi masuk</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Jam Masuk Akhir</label>
                    <input type="time" name="check_in_end" class="form-control" 
                           value="<?php echo $current_settings['check_in_end'] ?? '08:00'; ?>">
                    <small class="text-muted">Batas akhir absensi masuk (setelah ini dianggap telat)</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Jam Pulang Mulai</label>
                    <input type="time" name="check_out_start" class="form-control" 
                           value="<?php echo $current_settings['check_out_start'] ?? '15:00'; ?>">
                    <small class="text-muted">Waktu mulai absensi pulang</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Jam Pulang Akhir</label>
                    <input type="time" name="check_out_end" class="form-control" 
                           value="<?php echo $current_settings['check_out_end'] ?? '16:00'; ?>">
                    <small class="text-muted">Batas akhir absensi pulang</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Toleransi Keterlambatan (menit)</label>
                    <input type="number" name="late_threshold" class="form-control" 
                           value="<?php echo $current_settings['late_threshold'] ?? '15'; ?>" min="0" max="60">
                    <small class="text-muted">Toleransi waktu sebelum dianggap telat</small>
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan Pengaturan Jam
            </button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-palette"></i> Pengaturan Tampilan & Background</h3>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="appearance">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Tema Aplikasi</label>
                    <select name="theme" class="form-control">
                        <option value="fluent" <?php echo ($current_settings['theme'] ?? 'fluent') === 'fluent' ? 'selected' : ''; ?>>Fluent UI (Default)</option>
                        <option value="material" <?php echo ($current_settings['theme'] ?? 'fluent') === 'material' ? 'selected' : ''; ?>>Material UI</option>
                        <option value="glassmorphism" <?php echo ($current_settings['theme'] ?? 'fluent') === 'glassmorphism' ? 'selected' : ''; ?>>Glassmorphism</option>
                        <option value="cyberpunk" <?php echo ($current_settings['theme'] ?? 'fluent') === 'cyberpunk' ? 'selected' : ''; ?>>Cyberpunk</option>
                    </select>
                    <small class="text-muted">Pilih tema tampilan aplikasi</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Mode Warna</label>
                    <select name="mode" class="form-control">
                        <option value="light" <?php echo ($current_settings['mode'] ?? 'light') === 'light' ? 'selected' : ''; ?>>White (Terang)</option>
                        <option value="light-gray" <?php echo ($current_settings['mode'] ?? 'light') === 'light-gray' ? 'selected' : ''; ?>>Light Gray</option>
                        <option value="dark-gray" <?php echo ($current_settings['mode'] ?? 'light') === 'dark-gray' ? 'selected' : ''; ?>>Dark Gray</option>
                        <option value="black" <?php echo ($current_settings['mode'] ?? 'light') === 'black' ? 'selected' : ''; ?>>Black (Gelap)</option>
                    </select>
                    <small class="text-muted">Pilih mode warna latar belakang</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Transparansi Background (0.1 - 1.0)</label>
                    <input type="range" name="bg_opacity" min="0.1" max="1.0" step="0.1" 
                           value="<?php echo $current_settings['bg_opacity'] ?? '0.9'; ?>" 
                           oninput="this.nextElementSibling.value = this.value">
                    <output><?php echo $current_settings['bg_opacity'] ?? '0.9'; ?></output>
                    <small class="text-muted">Atur tingkat transparansi background</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Blur Background (0 - 20px)</label>
                    <input type="range" name="bg_blur" min="0" max="20" step="1" 
                           value="<?php echo $current_settings['bg_blur'] ?? '0'; ?>" 
                           oninput="this.nextElementSibling.value = this.value + 'px'">
                    <output><?php echo $current_settings['bg_blur'] ?? '0'; ?>px</output>
                    <small class="text-muted">Atur efek blur pada background</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Logo Sekolah (Max 500x500px, 3MB)</label>
                    <input type="file" name="logo_upload" class="form-control" accept="image/jpeg,image/png">
                    <?php if (!empty($current_settings['logo_path'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo $current_settings['logo_path']; ?>" alt="Logo" style="max-width: 150px; max-height: 150px;">
                        </div>
                    <?php endif; ?>
                    <small class="text-muted">Format: JPG/PNG, Max 500x500px, Max 3MB</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Background Image (JPG/PNG)</label>
                    <input type="file" name="bg_upload" class="form-control" accept="image/jpeg,image/png">
                    <?php if (!empty($current_settings['bg_image_path'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo $current_settings['bg_image_path']; ?>" alt="Background" style="max-width: 200px; max-height: 150px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <small class="text-muted">Format: JPG/PNG, Max 5MB</small>
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan Pengaturan Tampilan
            </button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-shield"></i> Pengaturan Petugas Absensi</h3>
    </div>
    <div class="card-body">
        <div class="dashboard-grid">
            <div>
                <h4>Petugas Hari Ini</h4>
                <?php if ($todays_petugas): ?>
                    <div class="alert alert-info">
                        <strong><?php echo htmlspecialchars($todays_petugas['nama_lengkap']); ?></strong><br>
                        <small>Username: <?php echo htmlspecialchars($todays_petugas['username']); ?></small><br>
                        <?php if (!empty($todays_petugas['keterangan'])): ?>
                            <small>Keterangan: <?php echo htmlspecialchars($todays_petugas['keterangan']); ?></small>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">Belum ada petugas yang dijadwalkan untuk hari ini.</div>
                <?php endif; ?>
            </div>
            
            <div>
                <h4>Jadwal Petugas</h4>
                <form method="POST" action="manage-petugas.php">
                    <div class="form-group">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Petugas</label>
                        <select name="petugas_id" class="form-control" required>
                            <?php foreach ($petugas_list as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_lengkap']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Atur Jadwal Petugas
                    </button>
                </form>
                <a href="manage-petugas.php" class="btn btn-secondary mt-2">
                    <i class="fas fa-list"></i> Lihat Semua Jadwal
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-key"></i> Ganti Password</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="password">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Ganti Password Untuk</label>
                    <select name="role" class="form-control" id="passwordRole" onchange="togglePasswordFields()">
                        <option value="admin">Administrator</option>
                        <option value="petugas">Petugas Absensi</option>
                    </select>
                </div>
                
                <div class="form-group" id="currentPasswordField">
                    <label class="form-label">Password Saat Ini (Khusus Admin)</label>
                    <input type="password" name="current_password" class="form-control">
                    <small class="text-muted">Kosongkan jika mengganti password petugas</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" class="form-control" minlength="6" required>
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-key"></i> Ubah Password
            </button>
        </form>
    </div>
</div>

<script>
function togglePasswordFields() {
    const role = document.getElementById('passwordRole').value;
    const currentField = document.getElementById('currentPasswordField');
    if (role === 'petugas') {
        currentField.style.display = 'none';
        currentField.querySelector('input').required = false;
    } else {
        currentField.style.display = 'block';
        currentField.querySelector('input').required = true;
    }
}
</script>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-school"></i> Informasi Sekolah</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">NPSN</label>
                    <input type="text" class="form-control" value="<?php echo SCHOOL_NPSN; ?>" readonly>
                    <small class="text-muted">NPSN tidak dapat diubah dari sini</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Sekolah</label>
                    <input type="text" name="school_name" class="form-control" value="<?php echo SCHOOL_NAME; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <textarea name="school_address" class="form-control" rows="2"><?php echo SCHOOL_ADDRESS; ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="school_phone" class="form-control" value="<?php echo SCHOOL_PHONE; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="url" name="school_website" class="form-control" value="<?php echo SCHOOL_WEBSITE; ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan Informasi Sekolah
            </button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Aplikasi</h3>
    </div>
    <div class="card-body">
        <div class="dashboard-grid">
            <div>
                <p><strong>Nama Aplikasi:</strong> <?php echo APP_NAME; ?></p>
                <p><strong>Versi:</strong> <?php echo APP_VERSION; ?></p>
                <p><strong>Tanggal Instalasi:</strong> <?php echo date('d/m/Y'); ?></p>
            </div>
            <div>
                <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
