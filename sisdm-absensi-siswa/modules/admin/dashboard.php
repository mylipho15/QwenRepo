<?php
require_once '../../config/database.php';
checkAuth('admin');

$db = Database::getInstance()->getConnection();
$school = getSchoolInfo();
$user_nama = $_SESSION['nama_lengkap'];

// Get statistics
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as total FROM siswa");
$stmt->execute();
$total_siswa = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ?");
$stmt->execute([$today]);
$hadir_hari_ini = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND status_datang = 'telat'");
$stmt->execute([$today]);
$telat = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND keterangan IN ('sakit', 'izin', 'alfa')");
$stmt->execute([$today]);
$berhalangan = $stmt->fetch()['total'];

include '../../includes/header.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-label">Total Siswa</div>
        <div class="stat-number"><?php echo $total_siswa; ?></div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--success), #0c5e0c);">
        <div class="stat-label">Hadir Hari Ini</div>
        <div class="stat-number"><?php echo $hadir_hari_ini; ?></div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--warning), #cc9500);">
        <div class="stat-label">Telat Datang</div>
        <div class="stat-number"><?php echo $telat; ?></div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--danger), #a82a2e);">
        <div class="stat-label">Berhalangan</div>
        <div class="stat-number"><?php echo $berhalangan; ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Selamat Datang, <?php echo htmlspecialchars($user_nama); ?>!</div>
    <p>Ini adalah dashboard administrator SISDM Absensi Siswa. Gunakan menu di samping untuk mengelola:</p>
    <ul style="margin: 1rem 0 1rem 2rem;">
        <li>Data Absensi (Jam Datang, Jam Pulang, Telat, Pulang Awal)</li>
        <li>Data Siswa (NIPD, Nama, Kelas, Jurusan)</li>
        <li>Rekap Absensi (Bulanan, Mingguan)</li>
        <li>Pengaturan Program Absensi</li>
        <li>Pengaturan Petugas Absensi Harian</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">Informasi Sekolah</div>
    <div class="settings-grid">
        <div>
            <strong>NPSN:</strong> <?php echo htmlspecialchars($school['npsn'] ?? '-'); ?>
        </div>
        <div>
            <strong>Nama Sekolah:</strong> <?php echo htmlspecialchars($school['nama_sekolah'] ?? '-'); ?>
        </div>
        <div>
            <strong>Alamat:</strong> <?php echo htmlspecialchars($school['alamat'] ?? '-'); ?>
        </div>
        <div>
            <strong>Telepon:</strong> <?php echo htmlspecialchars($school['telepon'] ?? '-'); ?>
        </div>
        <div>
            <strong>Website:</strong> <?php echo htmlspecialchars($school['website'] ?? '-'); ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
