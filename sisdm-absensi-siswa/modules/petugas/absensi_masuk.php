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
    $jam_datang = $_POST['jam_datang'];
    
    // Check if already exists
    $check = $db->prepare("SELECT id, jam_datang FROM absensi WHERE siswa_id = ? AND tanggal = ?");
    $check->execute([$siswa_id, $tanggal]);
    $existing = $check->fetch();
    
    if ($existing) {
        // Update existing
        $stmt = $db->prepare("UPDATE absensi SET jam_datang = ?, petugas_id = ?, 
                             status_datang = CASE WHEN ? > '07:30:00' THEN 'telat' ELSE 'tepat_waktu' END,
                             keterangan = 'hadir'
                             WHERE siswa_id = ? AND tanggal = ?");
        $stmt->execute([$jam_datang, $petugas_id, $jam_datang, $siswa_id, $tanggal]);
        $success = 'Jam masuk berhasil diupdate!';
    } else {
        // Insert new
        $status = $jam_datang > '07:30:00' ? 'telat' : 'tepat_waktu';
        $stmt = $db->prepare("INSERT INTO absensi (siswa_id, tanggal, jam_datang, keterangan, status_datang, petugas_id) 
                             VALUES (?, ?, ?, 'hadir', ?, ?)");
        $stmt->execute([$siswa_id, $tanggal, $jam_datang, $status, $petugas_id]);
        $success = 'Jam masuk berhasil dicatat!';
    }

// Get all students
$stmt = $db->query("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.nipd");
$siswa_list = $stmt->fetchAll();

$page_title = 'Absensi Jam Masuk';
include '../../includes/header_petugas.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">✅ Input Absensi Jam Masuk</div>
    
    <form method="POST" class="d-flex gap-1 align-center mb-2">
        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        <input type="time" name="jam_datang" class="form-control" value="<?php echo date('H:i'); ?>" required>
        <select name="siswa_id" class="form-control" required>
            <option value="">Pilih Siswa</option>
            <?php foreach ($siswa_list as $s): ?>
            <option value="<?php echo $s['id']; ?>">
                <?php echo htmlspecialchars($s['nipd'] . ' - ' . $s['nama'] . ' (' . ($s['nama_kelas'] ?? '-') . ')'); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-success">Catat Jam Masuk</button>
    </form>
</div>

<div class="card">
    <div class="card-header">Riwayat Jam Masuk Hari Ini</div>
    <?php
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT a.*, s.nipd, s.nama, k.nama_kelas 
                          FROM absensi a 
                          JOIN siswa s ON a.siswa_id = s.id 
                          LEFT JOIN kelas k ON s.kelas_id = k.id 
                          WHERE a.tanggal = ? AND a.petugas_id = ?
                          ORDER BY a.jam_datang DESC");
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
                <td><?php echo date('H:i', strtotime($r['jam_datang'])); ?></td>
                <td><?php echo htmlspecialchars($r['nipd']); ?></td>
                <td><?php echo htmlspecialchars($r['nama']); ?></td>
                <td><?php echo htmlspecialchars($r['nama_kelas'] ?? '-'); ?></td>
                <td>
                    <?php if ($r['status_datang'] === 'telat'): ?>
                        <span style="color: var(--warning);">⏰ Telat</span>
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
