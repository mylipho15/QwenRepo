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
    $jam_pulang = $_POST['jam_pulang'];
    
    // Check if already exists
    $check = $db->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
    $check->execute([$siswa_id, $tanggal]);
    $existing = $check->fetch();
    
    if ($existing) {
        // Update existing - check for early departure
        $stmt = $db->prepare("UPDATE absensi SET jam_pulang = ?, petugas_id = ?,
                             status_pulang = CASE WHEN ? < '15:30:00' THEN 'pulang_awal' ELSE 'tepat_waktu' END
                             WHERE siswa_id = ? AND tanggal = ?");
        $stmt->execute([$jam_pulang, $petugas_id, $jam_pulang, $siswa_id, $tanggal]);
        $success = 'Jam pulang berhasil diupdate!';
    } else {
        // Insert new (student didn't check in but checking out)
        $status = $jam_pulang < '15:30:00' ? 'pulang_awal' : 'tepat_waktu';
        $stmt = $db->prepare("INSERT INTO absensi (siswa_id, tanggal, jam_pulang, keterangan, status_pulang, petugas_id) 
                             VALUES (?, ?, ?, 'hadir', ?, ?)");
        $stmt->execute([$siswa_id, $tanggal, $jam_pulang, $status, $petugas_id]);
        $success = 'Jam pulang berhasil dicatat!';
    }

// Get all students
$stmt = $db->query("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.nipd");
$siswa_list = $stmt->fetchAll();

$page_title = 'Absensi Jam Keluar';
include '../../includes/header_petugas.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">🚪 Input Absensi Jam Keluar</div>
    
    <form method="POST" class="d-flex gap-1 align-center mb-2">
        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        <input type="time" name="jam_pulang" class="form-control" value="<?php echo date('H:i'); ?>" required>
        <select name="siswa_id" class="form-control" required>
            <option value="">Pilih Siswa</option>
            <?php foreach ($siswa_list as $s): ?>
            <option value="<?php echo $s['id']; ?>">
                <?php echo htmlspecialchars($s['nipd'] . ' - ' . $s['nama'] . ' (' . ($s['nama_kelas'] ?? '-') . ')'); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-warning">Catat Jam Keluar</button>
    </form>
</div>

<div class="card">
    <div class="card-header">Riwayat Jam Keluar Hari Ini</div>
    <?php
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT a.*, s.nipd, s.nama, k.nama_kelas 
                          FROM absensi a 
                          JOIN siswa s ON a.siswa_id = s.id 
                          LEFT JOIN kelas k ON s.kelas_id = k.id 
                          WHERE a.tanggal = ? AND a.petugas_id = ? AND a.jam_pulang IS NOT NULL
                          ORDER BY a.jam_pulang DESC");
    $stmt->execute([$today, $petugas_id]);
    $riwayat = $stmt->fetchAll();
    ?>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Jam</th>
                <th>NIPD</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($riwayat as $r): ?>
            <tr>
                <td><?php echo date('H:i', strtotime($r['jam_pulang'])); ?></td>
                <td><?php echo htmlspecialchars($r['nipd']); ?></td>
                <td><?php echo htmlspecialchars($r['nama']); ?></td>
                <td><?php echo htmlspecialchars($r['nama_kelas'] ?? '-'); ?></td>
                <td>
                    <?php if ($r['status_pulang'] === 'pulang_awal'): ?>
                        <span style="color: var(--danger);">⚠️ Pulang Awal</span>
                    <?php else: ?>
                        <span style="color: var(--success);">✓ Tepat Waktu</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($riwayat)): ?>
            <tr>
                <td colspan="5" class="text-center">Belum ada input hari ini</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
