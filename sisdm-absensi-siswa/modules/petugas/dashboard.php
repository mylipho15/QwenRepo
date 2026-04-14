<?php
require_once '../../config/database.php';
checkAuth('petugas');

$db = Database::getInstance()->getConnection();
$school = getSchoolInfo();
$user_nama = $_SESSION['nama_lengkap'];
$petugas_id = $_SESSION['user_id'];

// Get statistics for today
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND petugas_id = ?");
$stmt->execute([$today, $petugas_id]);
$total_input = $stmt->fetch()['total'];

$page_title = 'Dashboard Petugas';
include '../../includes/header_petugas.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-label">Absensi Dimasukkan Hari Ini</div>
        <div class="stat-number"><?php echo $total_input; ?></div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--success), #0c5e0c);">
        <div class="stat-label">Jam Sekarang</div>
        <div class="stat-number" id="current-time"><?php echo date('H:i'); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Selamat Datang, <?php echo htmlspecialchars($user_nama); ?>!</div>
    <p>Anda login sebagai <strong>Petugas Absensi</strong>. Gunakan menu di samping untuk:</p>
    <ul style="margin: 1rem 0 1rem 2rem;">
        <li>Absensi Jam Masuk dan Jam Keluar Siswa</li>
        <li>Input Absensi Siswa Berhalangan (Sakit / Izin / Alfa)</li>
        <li>Input Izin Khusus (Telat / Keluar Lingkungan / Pulang Awal)</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">Aksi Cepat</div>
    <div class="d-flex gap-2">
        <a href="absensi_masuk.php" class="btn btn-success">✅ Input Jam Masuk</a>
        <a href="absensi_keluar.php" class="btn btn-warning">🚪 Input Jam Keluar</a>
        <a href="berhalangan.php" class="btn btn-danger">🏥 Siswa Berhalangan</a>
        <a href="izin_khusus.php" class="btn btn-info" style="background: var(--info); color: white;">📝 Izin Khusus</a>
    </div>
</div>

<script>
// Update time every second
setInterval(function() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('current-time').textContent = hours + ':' + minutes;
}, 1000);
</script>

<?php include '../../includes/footer.php'; ?>
