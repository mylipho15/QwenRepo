<?php
/**
 * Settings Module - Pengaturan Program Absensi
 * School Identity, Logo Upload, Background, Theme, Transparency, Blur
 */

$db = Database::getInstance();
$message = '';
$messageType = '';

// Get current settings
$school = $db->fetchOne("SELECT * FROM school_identity LIMIT 1");
$settingsList = [];
$settingsQuery = $db->fetchAll("SELECT * FROM system_settings");
foreach ($settingsQuery as $s) {
    $settingsList[$s['setting_key']] = $s;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_school') {
        $npsn = $_POST['npsn'] ?? '';
        $school_name = $_POST['school_name'] ?? '';
        $address = $_POST['address'] ?? '';
        $website = $_POST['website'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        // Handle logo upload
        $logo_path = $school['logo_path'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 3 * 1024 * 1024; // 3MB
            
            if (in_array($_FILES['logo']['type'], $allowed) && $_FILES['logo']['size'] <= $maxSize) {
                // Check image dimensions
                $imgInfo = getimagesize($_FILES['logo']['tmp_name']);
                if ($imgInfo[0] <= 500 && $imgInfo[1] <= 500) {
                    $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $logo_path = 'assets/images/logo_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['logo']['tmp_name'], '../' . $logo_path);
                } else {
                    $message = 'Ukuran logo maksimal 500x500 pixel!';
                    $messageType = 'danger';
                }
            } else {
                $message = 'Format logo harus JPG/PNG dan ukuran maksimal 3MB!';
                $messageType = 'danger';
            }
        }
        
        // Handle background upload
        $background_image = $school['background_image'] ?? '';
        if (isset($_FILES['background']) && $_FILES['background']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024; // 5MB for background
            
            if (in_array($_FILES['background']['type'], $allowed) && $_FILES['background']['size'] <= $maxSize) {
                $ext = pathinfo($_FILES['background']['name'], PATHINFO_EXTENSION);
                $background_image = 'assets/images/bg_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['background']['tmp_name'], '../' . $background_image);
            }
        }
        
        $transparency = $_POST['transparency'] ?? 0.95;
        $blur_effect = $_POST['blur_effect'] ?? 10;
        
        if (empty($message)) {
            if ($school) {
                $db->update('school_identity', [
                    'npsn' => $npsn,
                    'school_name' => $school_name,
                    'address' => $address,
                    'website' => $website,
                    'phone' => $phone,
                    'logo_path' => $logo_path,
                    'background_image' => $background_image,
                    'transparency' => $transparency,
                    'blur_effect' => $blur_effect
                ], 'id = ?', [$school['id']]);
            } else {
                $db->insert('school_identity', [
                    'npsn' => $npsn,
                    'school_name' => $school_name,
                    'address' => $address,
                    'website' => $website,
                    'phone' => $phone,
                    'logo_path' => $logo_path,
                    'background_image' => $background_image,
                    'transparency' => $transparency,
                    'blur_effect' => $blur_effect
                ]);
            }
            $message = 'Identitas sekolah berhasil diperbarui!';
            $messageType = 'success';
        }
    }
    
    if ($action === 'update_settings') {
        $settingsToUpdate = [
            'theme' => $_POST['theme'] ?? 'fluent-ui',
            'color_mode' => $_POST['color_mode'] ?? 'light',
            'check_in_start' => $_POST['check_in_start'] ?? '06:30',
            'check_in_end' => $_POST['check_in_end'] ?? '07:30',
            'late_threshold' => $_POST['late_threshold'] ?? '07:30',
            'check_out_start' => $_POST['check_out_start'] ?? '15:00'
        ];
        
        foreach ($settingsToUpdate as $key => $value) {
            $db->update('system_settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
        }
        $message = 'Pengaturan sistem berhasil diperbarui!';
        $messageType = 'success';
    }
}
?>

<div class="sidebar">
    <div class="text-center mb-3">
        <?php if ($school['logo_path']): ?>
            <img src="<?= htmlspecialchars($school['logo_path']) ?>" alt="Logo" style="max-width: 80px; border-radius: 50%;">
        <?php endif; ?>
        <h6 class="mt-2"><?= htmlspecialchars($school['school_name'] ?? 'SISDM') ?></h6>
    </div>
    
    <ul class="sidebar-menu">
        <li class="sidebar-item"><a href="?page=dashboard" class="sidebar-link">📊 Beranda/Dashboard</a></li>
        <li class="sidebar-item"><a href="?page=students" class="sidebar-link">👨‍🎓 Data Siswa</a></li>
        <li class="sidebar-item"><a href="?page=attendance" class="sidebar-link">✅ Data Absensi</a></li>
        <li class="sidebar-item"><a href="?page=reports" class="sidebar-link">📋 Rekap Absensi</a></li>
        <li class="sidebar-item"><a href="?page=officers" class="sidebar-link">👮 Petugas Absensi</a></li>
        <li class="sidebar-item"><a href="?page=settings" class="sidebar-link active">⚙️ Pengaturan</a></li>
        <li class="sidebar-item mt-3"><a href="?page=logout" class="sidebar-link" style="color: var(--danger-color);">🚪 Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="mb-3">
        <h2>Pengaturan</h2>
        <p class="text-muted">Kelola identitas sekolah dan pengaturan sistem</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <!-- School Identity -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h4>🏫 Identitas Sekolah</h4>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_school">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">NPSN</label>
                        <input type="text" name="npsn" class="form-control" value="<?= htmlspecialchars($school['npsn'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama Sekolah</label>
                        <input type="text" name="school_name" class="form-control" required value="<?= htmlspecialchars($school['school_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($school['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($school['website'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($school['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Logo Sekolah (Max 500x500px, < 3MB)</label>
                        <input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                        <?php if ($school['logo_path']): ?>
                            <small>Logo saat ini: <?= basename($school['logo_path']) ?></small><br>
                            <img src="../<?= htmlspecialchars($school['logo_path']) ?>" style="max-width: 100px; margin-top: 10px;">
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Background Image (JPG/PNG)</label>
                        <input type="file" name="background" class="form-control" accept="image/jpeg,image/png,image/jpg">
                        <?php if ($school['background_image']): ?>
                            <small>Background saat ini: <?= basename($school['background_image']) ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Transparansi: <?= round(($school['transparency'] ?? 0.95) * 100) ?>%</label>
                        <input type="range" name="transparency" min="50" max="100" step="5" class="form-control" value="<?= ($school['transparency'] ?? 0.95) * 100 ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Blur Effect: <?= $school['blur_effect'] ?? 10 ?>px</label>
                        <input type="range" name="blur_effect" min="0" max="20" class="form-control" value="<?= $school['blur_effect'] ?? 10 ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary mt-2">💾 Simpan Perubahan</button>
            </form>
        </div>

        <!-- System Settings -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h4>⚙️ Pengaturan Sistem</h4>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Tema</label>
                        <select name="theme" class="form-control">
                            <option value="fluent-ui" <?= ($settingsList['theme']['setting_value'] ?? '') === 'fluent-ui' ? 'selected' : '' ?>>Fluent UI</option>
                            <option value="material-ui" <?= ($settingsList['theme']['setting_value'] ?? '') === 'material-ui' ? 'selected' : '' ?>>Material UI</option>
                            <option value="glassmorphism" <?= ($settingsList['theme']['setting_value'] ?? '') === 'glassmorphism' ? 'selected' : '' ?>>Glassmorphism</option>
                            <option value="cyberpunk" <?= ($settingsList['theme']['setting_value'] ?? '') === 'cyberpunk' ? 'selected' : '' ?>>Cyberpunk</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Mode Warna</label>
                        <select name="color_mode" class="form-control">
                            <option value="light" <?= ($settingsList['color_mode']['setting_value'] ?? '') === 'light' ? 'selected' : '' ?>>Putih (Light)</option>
                            <option value="light-gray" <?= ($settingsList['color_mode']['setting_value'] ?? '') === 'light-gray' ? 'selected' : '' ?>>Abu Terang</option>
                            <option value="dark-gray" <?= ($settingsList['color_mode']['setting_value'] ?? '') === 'dark-gray' ? 'selected' : '' ?>>Abu Gelap</option>
                            <option value="dark" <?= ($settingsList['color_mode']['setting_value'] ?? '') === 'dark' ? 'selected' : '' ?>>Hitam (Dark)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Jam Mulai Absen Masuk</label>
                        <input type="time" name="check_in_start" class="form-control" value="<?= $settingsList['check_in_start']['setting_value'] ?? '06:30' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Batas Waktu Absen Masuk</label>
                        <input type="time" name="check_in_end" class="form-control" value="<?= $settingsList['check_in_end']['setting_value'] ?? '07:30' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Batas Terlambat</label>
                        <input type="time" name="late_threshold" class="form-control" value="<?= $settingsList['late_threshold']['setting_value'] ?? '07:30' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Jam Mulai Absen Pulang</label>
                        <input type="time" name="check_out_start" class="form-control" value="<?= $settingsList['check_out_start']['setting_value'] ?? '15:00' ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary mt-2">💾 Simpan Pengaturan</button>
            </form>
        </div>
    </div>
</div>
