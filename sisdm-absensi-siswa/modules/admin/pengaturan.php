<?php
// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
checkAuth('admin');


$db = Database::getInstance()->getConnection();
$school = getSchoolInfo();
$success = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npsn = trim($_POST['npsn']);
    $nama_sekolah = trim($_POST['nama_sekolah']);
    $alamat = trim($_POST['alamat']);
    $website = trim($_POST['website']);
    $telepon = trim($_POST['telepon']);
    $transparency = $_POST['transparency'];
    $blur = $_POST['blur'];
    $theme = $_POST['theme'];
    
    // Handle logo upload
    $logo_path = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png'];
        $max_size = 3 * 1024 * 1024; // 3MB
        
        if (in_array($_FILES['logo']['type'], $allowed) && $_FILES['logo']['size'] <= $max_size) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo_path = 'logo_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], '../../assets/images/' . $logo_path);
        }
    }
    
    // Handle background upload
    $background_path = null;
    if (isset($_FILES['background']) && $_FILES['background']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png'];
        if (in_array($_FILES['background']['type'], $allowed)) {
            $ext = pathinfo($_FILES['background']['name'], PATHINFO_EXTENSION);
            $background_path = 'bg_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['background']['tmp_name'], '../../assets/images/' . $background_path);
        }
    }
    
    // Update database
    if ($logo_path || $background_path) {
        $fields = [];
        $params = [];
        
        if ($logo_path) {
            $fields[] = "logo_path = ?";
            $params[] = $logo_path;
        }
        if ($background_path) {
            $fields[] = "background_path = ?";
            $params[] = $background_path;
        }
        
        $params[] = $npsn;
        $params[] = $nama_sekolah;
        $params[] = $alamat;
        $params[] = $website;
        $params[] = $telepon;
        $params[] = $transparency;
        $params[] = $blur;
        $params[] = $theme;
        $params[] = 1; // id
        
        $sql = "UPDATE sekolah SET " . implode(', ', $fields) . 
               ", npsn=?, nama_sekolah=?, alamat=?, website=?, telepon=?, transparency=?, blur=?, theme=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $db->prepare("UPDATE sekolah SET npsn=?, nama_sekolah=?, alamat=?, website=?, telepon=?, 
                             transparency=?, blur=?, theme=? WHERE id=?");
        $stmt->execute([$npsn, $nama_sekolah, $alamat, $website, $telepon, $transparency, $blur, $theme, 1]);
    }
    
    $success = 'Pengaturan berhasil disimpan!';
    $school = getSchoolInfo(); // Refresh

$page_title = 'Pengaturan - Admin';
include '../../includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">⚙️ Pengaturan Identitas Sekolah</div>
    <form method="POST" enctype="multipart/form-data" class="settings-grid">
        <div class="form-group">
            <label class="form-label">NPSN</label>
            <input type="text" class="form-control" name="npsn" value="<?php echo htmlspecialchars($school['npsn'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Nama Sekolah</label>
            <input type="text" class="form-control" name="nama_sekolah" value="<?php echo htmlspecialchars($school['nama_sekolah'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Alamat</label>
            <textarea class="form-control" name="alamat" rows="3"><?php echo htmlspecialchars($school['alamat'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Website</label>
            <input type="url" class="form-control" name="website" value="<?php echo htmlspecialchars($school['website'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Telepon</label>
            <input type="text" class="form-control" name="telepon" value="<?php echo htmlspecialchars($school['telepon'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Logo Sekolah (Max 500x500px, < 3MB)</label>
            <input type="file" class="form-control" name="logo" accept="image/jpeg,image/png">
            <?php if (!empty($school['logo_path'])): ?>
                <img src="../../assets/images/<?php echo htmlspecialchars($school['logo_path']); ?>" 
                     alt="Logo" style="max-width: 100px; margin-top: 0.5rem;">
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label">Background Image (JPG/PNG)</label>
            <input type="file" class="form-control" name="background" accept="image/jpeg,image/png" id="bg-image-upload">
            <?php if (!empty($school['background_path'])): ?>
                <img src="../../assets/images/<?php echo htmlspecialchars($school['background_path']); ?>" 
                     alt="Background" style="max-width: 200px; margin-top: 0.5rem;">
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label">Transparency (0.1 - 1.0)</label>
            <input type="range" class="form-control" name="transparency" id="transparency-slider" 
                   min="0.1" max="1" step="0.05" value="<?php echo $school['transparency'] ?? 0.95; ?>">
            <small>Nilai: <span id="transparency-value"><?php echo $school['transparency'] ?? 0.95; ?></span></small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Blur Effect (0-20px)</label>
            <input type="range" class="form-control" name="blur" id="blur-slider" 
                   min="0" max="20" value="<?php echo $school['blur'] ?? 0; ?>">
            <small>Nilai: <span id="blur-value"><?php echo $school['blur'] ?? 0; ?>px</span></small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Theme</label>
            <select class="form-control" name="theme" id="theme-select">
                <option value="fluent" <?php echo ($school['theme'] ?? 'fluent') === 'fluent' ? 'selected' : ''; ?>>Fluent UI (Default)</option>
                <option value="material" <?php echo ($school['theme'] ?? '') === 'material' ? 'selected' : ''; ?>>Material UI</option>
                <option value="glassmorphism" <?php echo ($school['theme'] ?? '') === 'glassmorphism' ? 'selected' : ''; ?>>Glassmorphism</option>
                <option value="cyberpunk" <?php echo ($school['theme'] ?? '') === 'cyberpunk' ? 'selected' : ''; ?>>Cyberpunk</option>
            </select>
        </div>
        
        <div class="form-group" style="grid-column: 1 / -1;">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </div>
    </form>
</div>

<script>
document.getElementById('transparency-slider').addEventListener('input', function(e) {
    document.getElementById('transparency-value').textContent = e.target.value;
});

document.getElementById('blur-slider').addEventListener('input', function(e) {
    document.getElementById('blur-value').textContent = e.target.value + 'px';
});
</script>

<?php include '../../includes/footer.php'; ?>
