<?php
// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
checkAuth('petugas');


$db = Database::getInstance()->getConnection();
$school = getSchoolInfo();
$success = '';
$petugas_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id = $_POST['siswa_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $catatan = $_POST['catatan'] ?? '';
    
    // Check if already exists
    $check = $db->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
    $check->execute([$siswa_id, $tanggal]);
    
    if ($check->fetch()) {
        $stmt = $db->prepare("UPDATE absensi SET keterangan = ?, catatan = ?, petugas_id = ?, 
                             jam_datang = NULL, jam_pulang = NULL
                             WHERE siswa_id = ? AND tanggal = ?");
        $stmt->execute([$keterangan, $catatan, $petugas_id, $siswa_id, $tanggal]);
    } else {
        $stmt = $db->prepare("INSERT INTO absensi (siswa_id, tanggal, keterangan, catatan, petugas_id) 
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$siswa_id, $tanggal, $keterangan, $catatan, $petugas_id]);
    }
    $success = 'Absensi berhalangan berhasil dicatat!';

// Get all students
$stmt = $db->query("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.nipd");
$siswa_list = $stmt->fetchAll();

$page_title = 'Siswa Berhalangan';
include '../../includes/header_petugas.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">🏥 Input Absensi Siswa Berhalangan</div>
    <p>Catat siswa yang tidak hadir karena sakit, izin, atau alfa.</p>
    
    <form method="POST" class="settings-grid">
        <div class="form-group">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Siswa</label>
            <select name="siswa_id" class="form-control" required>
                <option value="">Pilih Siswa</option>
                <?php foreach ($siswa_list as $s): ?>
                <option value="<?php echo $s['id']; ?>">
                    <?php echo htmlspecialchars($s['nipd'] . ' - ' . $s['nama'] . ' (' . ($s['nama_kelas'] ?? '-') . ')'); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Keterangan</label>
            <select name="keterangan" class="form-control" required>
                <option value="sakit">😷 Sakit</option>
                <option value="izin">📝 Izin</option>
                <option value="alfa">❌ Alfa (Tanpa Keterangan)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Catatan</label>
            <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Surat dokter terlampir..."></textarea>
        </div>
        
        <div class="form-group" style="grid-column: 1 / -1;">
            <button type="submit" class="btn btn-danger">Simpan</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">Riwayat Siswa Berhalangan Hari Ini</div>
    <?php
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT a.*, s.nipd, s.nama, k.nama_kelas 
                          FROM absensi a 
                          JOIN siswa s ON a.siswa_id = s.id 
                          LEFT JOIN kelas k ON s.kelas_id = k.id 
                          WHERE a.tanggal = ? AND a.keterangan IN ('sakit', 'izin', 'alfa')
                          ORDER BY a.created_at DESC");
    $stmt->execute([$today]);
    $riwayat = $stmt->fetchAll();
    ?>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>NIPD</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Keterangan</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($riwayat as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['nipd']); ?></td>
                <td><?php echo htmlspecialchars($r['nama']); ?></td>
                <td><?php echo htmlspecialchars($r['nama_kelas'] ?? '-'); ?></td>
                <td>
                    <?php
                    $badge = 'danger';
                    $icon = '❌';
                    if ($r['keterangan'] === 'sakit') { $badge = 'warning'; $icon = '😷'; }
                    if ($r['keterangan'] === 'izin') { $badge = 'info'; $icon = '📝'; }
                    ?>
                    <span style="color: var(--<?php echo $badge; ?>);">
                        <?php echo $icon . ' ' . strtoupper($r['keterangan']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($r['catatan'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($riwayat)): ?>
            <tr>
                <td colspan="5" class="text-center">Tidak ada siswa berhalangan hari ini</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
