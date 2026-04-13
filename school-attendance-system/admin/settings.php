<?php
include '../includes/admin-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'check_in_start' => $_POST['check_in_start'],
        'check_in_end' => $_POST['check_in_end'],
        'check_out_start' => $_POST['check_out_start'],
        'check_out_end' => $_POST['check_out_end'],
        'late_threshold' => (int)$_POST['late_threshold'],
        'theme' => $_POST['theme'],
        'mode' => $_POST['mode']
    ];

    foreach ($settings as $key => $value) {
        updateSetting($key, $value);
    }

    // Update school identity
    if (isset($_POST['school_name'])) {
        // Note: These are constants in database.php, but we can store overrides in settings
        updateSetting('school_name', sanitize($_POST['school_name']));
        updateSetting('school_address', sanitize($_POST['school_address']));
        updateSetting('school_phone', sanitize($_POST['school_phone']));
        updateSetting('school_website', sanitize($_POST['school_website']));
    }

    $message = 'Pengaturan berhasil disimpan!';
    $message_type = 'success';
}

// Get current settings
$current_settings = [];
$result = $conn->query("SELECT * FROM settings");
while ($row = $result->fetch_assoc()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
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
        <h3 class="card-title"><i class="fas fa-palette"></i> Pengaturan Tampilan</h3>
    </div>
    <div class="card-body">
        <form method="POST">
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
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan Pengaturan Tampilan
            </button>
        </form>
    </div>
</div>

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
