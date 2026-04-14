<?php
/**
 * Admin Dashboard - SISDM Absensi Siswa
 */

require_once '../../config/config.php';
requireAdmin();

$school = getSchoolInfo();
$settings = getSettings();
$user = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];

// Get statistics
try {
    // Total siswa
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM siswa");
    $total_siswa = $stmt->fetch()['total'];

    // Total kelas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kelas");
    $total_kelas = $stmt->fetch()['total'];

    // Hadir hari ini
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND keterangan = 'hadir'");
    $stmt->execute([$today]);
    $hadir_hari_ini = $stmt->fetch()['total'];

    // Telat hari ini
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND status_telat = 1");
    $stmt->execute([$today]);
    $telat_hari_ini = $stmt->fetch()['total'];

    // Sakit/Izin/Alfa hari ini
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND keterangan IN ('sakit', 'izin', 'alfa')");
    $stmt->execute([$today]);
    $berhalangan_hari_ini = $stmt->fetch()['total'];

    // Pulang awal hari ini
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND status_pulang_awal = 1");
    $stmt->execute([$today]);
    $pulang_awal_hari_ini = $stmt->fetch()['total'];

    // Petugas hari ini
    $stmt = $pdo->prepare("SELECT u.nama_lengkap FROM petugas_harian ph 
                           JOIN users u ON ph.user_id = u.id 
                           WHERE ph.tanggal = ?");
    $stmt->execute([$today]);
    $petugas_hari_ini = $stmt->fetchColumn() ?: 'Belum ditentukan';

} catch (PDOException $e) {
    $error = 'Gagal memuat data statistik';
}

// Get recent absensi
$stmt = $pdo->prepare("SELECT a.*, s.nama as nama_siswa, s.nipd, k.nama_kelas 
                       FROM absensi a 
                       JOIN siswa s ON a.siswa_id = s.id 
                       LEFT JOIN kelas k ON s.kelas_id = k.id 
                       WHERE a.tanggal = ? 
                       ORDER BY a.created_at DESC LIMIT 10");
$stmt->execute([$today]);
$recent_absensi = $stmt->fetchAll();

$page_title = 'Dashboard Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body data-theme="<?= $settings['theme'] ?? 'fluent' ?>" data-mode="<?= $settings['mode'] ?? 'white' ?>">
    <?php include '../../includes/admin_header.php'; ?>
    
    <div class="app-container">
        <?php include '../../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="card mb-2">
                <div class="card-header">
                    <h2 style="margin: 0;">📊 Dashboard Administrator</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Selamat datang, <strong><?= htmlspecialchars($user) ?></strong>! 
                        Hari ini: <span id="realtime-clock"></span>
                    </div>

                    <!-- Statistics -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><?= $total_siswa ?></h3>
                            <p>Total Siswa</p>
                        </div>
                        <div class="stat-card">
                            <h3><?= $total_kelas ?></h3>
                            <p>Total Kelas</p>
                        </div>
                        <div class="stat-card">
                            <h3 style="color: #107c10;"><?= $hadir_hari_ini ?></h3>
                            <p>Hadir Hari Ini</p>
                        </div>
                        <div class="stat-card">
                            <h3 style="color: #ffaa44;"><?= $telat_hari_ini ?></h3>
                            <p>Telat Hari Ini</p>
                        </div>
                        <div class="stat-card">
                            <h3 style="color: #d13438;"><?= $berhalangan_hari_ini ?></h3>
                            <p>Sakit/Izin/Alfa</p>
                        </div>
                        <div class="stat-card">
                            <h3 style="color: #0078d4;"><?= $pulang_awal_hari_ini ?></h3>
                            <p>Pulang Awal</p>
                        </div>
                    </div>

                    <!-- Info Petugas -->
                    <div class="card mt-2">
                        <div class="card-header">
                            <h3>👤 Petugas Absensi Hari Ini</h3>
                        </div>
                        <div class="card-body">
                            <p style="font-size: 1.2rem;"><strong><?= htmlspecialchars($petugas_hari_ini) ?></strong></p>
                        </div>
                    </div>

                    <!-- Recent Absensi -->
                    <div class="card mt-2">
                        <div class="card-header d-flex justify-between align-center">
                            <h3>📋 Absensi Terbaru Hari Ini</h3>
                            <a href="<?= BASE_URL ?>modules/admin/absensi.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>NIPD</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
                                            <th>Jam Datang</th>
                                            <th>Jam Pulang</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($recent_absensi) > 0): ?>
                                            <?php foreach ($recent_absensi as $absen): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($absen['nipd']) ?></td>
                                                    <td><?= htmlspecialchars($absen['nama_siswa']) ?></td>
                                                    <td><?= htmlspecialchars($absen['nama_kelas'] ?? '-') ?></td>
                                                    <td><?= $absen['jam_datang'] ?? '-' ?></td>
                                                    <td><?= $absen['jam_pulang'] ?? '-' ?></td>
                                                    <td>
                                                        <?php if ($absen['keterangan'] === 'hadir'): ?>
                                                            <span class="badge badge-success">Hadir</span>
                                                        <?php elseif ($absen['keterangan'] === 'sakit'): ?>
                                                            <span class="badge badge-warning">Sakit</span>
                                                        <?php elseif ($absen['keterangan'] === 'izin'): ?>
                                                            <span class="badge badge-info">Izin</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Alfa</span>
                                                        <?php endif; ?>
                                                        <?php if ($absen['status_telat']): ?>
                                                            <span class="badge badge-warning">Telat</span>
                                                        <?php endif; ?>
                                                        <?php if ($absen['status_pulang_awal']): ?>
                                                            <span class="badge badge-warning">Pulang Awal</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada data absensi hari ini</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
