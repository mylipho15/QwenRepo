<?php
/**
 * Petugas Dashboard - SISDM Absensi Siswa
 */

require_once '../../config/config.php';
requirePetugas();

$school = getSchoolInfo();
$settings = getSettings();
$user = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];
$today = date('Y-m-d');
$now = date('H:i:s');

// Get settings for jam masuk and pulang
$jam_masuk = $settings['jam_masuk'] ?? '07:00:00';
$jam_pulang = $settings['jam_pulang'] ?? '15:00:00';
$toleransi_telat = $settings['toleransi_telat'] ?? 15;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'absen_masuk') {
            $nipd = trim($_POST['nipd'] ?? '');
            
            // Get siswa data
            $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.nipd = ?");
            $stmt->execute([$nipd]);
            $siswa = $stmt->fetch();
            
            if ($siswa) {
                // Check if already absented today
                $stmt = $pdo->prepare("SELECT * FROM absensi WHERE siswa_id = ? AND tanggal = ?");
                $stmt->execute([$siswa['id'], $today]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    flashMessage('warning', 'Siswa sudah melakukan absensi masuk hari ini!');
                } else {
                    // Check if telat
                    $status_telat = 0;
                    $current_time = date('H:i:s');
                    $jam_masuk_plus_tolerance = date('H:i:s', strtotime($jam_masuk . " + {$toleransi_telat} minutes"));
                    
                    if ($current_time > $jam_masuk_plus_tolerance) {
                        $status_telat = 1;
                    }
                    
                    // Insert absensi
                    $stmt = $pdo->prepare("INSERT INTO absensi (siswa_id, tanggal, jam_datang, status_telat, keterangan, petugas_id) 
                                          VALUES (?, ?, ?, ?, 'hadir', ?)");
                    $stmt->execute([$siswa['id'], $today, $now, $status_telat, $_SESSION['user_id']]);
                    
                    // If telat, also record in izin_keluar
                    if ($status_telat) {
                        $stmt = $pdo->prepare("INSERT INTO izin_keluar (siswa_id, tanggal, jenis_izin, jam_izin, keterangan, petugas_id) 
                                              VALUES (?, ?, 'telat', ?, 'Terlambat datang', ?)");
                        $stmt->execute([$siswa['id'], $today, $now, $_SESSION['user_id']]);
                    }
                    
                    flashMessage('success', "Absensi masuk berhasil untuk {$siswa['nama']} ({$siswa['nama_kelas']})");
                }
            } else {
                flashMessage('danger', 'NIPD tidak ditemukan!');
            }
            
        } elseif ($action === 'absen_pulang') {
            $nipd = trim($_POST['nipd'] ?? '');
            
            $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.nipd = ?");
            $stmt->execute([$nipd]);
            $siswa = $stmt->fetch();
            
            if ($siswa) {
                $stmt = $pdo->prepare("SELECT * FROM absensi WHERE siswa_id = ? AND tanggal = ?");
                $stmt->execute([$siswa['id'], $today]);
                $absensi = $stmt->fetch();
                
                if ($absensi && empty($absensi['jam_pulang'])) {
                    // Check if pulang awal
                    $status_pulang_awal = 0;
                    if ($now < $jam_pulang) {
                        $status_pulang_awal = 1;
                    }
                    
                    $stmt = $pdo->prepare("UPDATE absensi SET jam_pulang = ?, status_pulang_awal = ? WHERE id = ?");
                    $stmt->execute([$now, $status_pulang_awal, $absensi['id']]);
                    
                    // If pulang awal, record in izin_keluar
                    if ($status_pulang_awal) {
                        $stmt = $pdo->prepare("INSERT INTO izin_keluar (siswa_id, tanggal, jenis_izin, jam_izin, keterangan, petugas_id) 
                                              VALUES (?, ?, 'pulang_awal', ?, 'Pulang lebih awal', ?)");
                        $stmt->execute([$siswa['id'], $today, $now, $_SESSION['user_id']]);
                    }
                    
                    flashMessage('success', "Absensi pulang berhasil untuk {$siswa['nama']}");
                } elseif ($absensi && !empty($absensi['jam_pulang'])) {
                    flashMessage('warning', 'Siswa sudah melakukan absensi pulang hari ini!');
                } else {
                    flashMessage('danger', 'Siswa belum melakukan absensi masuk hari ini!');
                }
            } else {
                flashMessage('danger', 'NIPD tidak ditemukan!');
            }
            
        } elseif ($action === 'berhalangan') {
            $nipd = trim($_POST['nipd'] ?? '');
            $keterangan = $_POST['keterangan'] ?? 'izin';
            $ket_detail = trim($_POST['ket_detail'] ?? '');
            
            $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.nipd = ?");
            $stmt->execute([$nipd]);
            $siswa = $stmt->fetch();
            
            if ($siswa) {
                $stmt = $pdo->prepare("SELECT * FROM absensi WHERE siswa_id = ? AND tanggal = ?");
                $stmt->execute([$siswa['id'], $today]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    flashMessage('warning', 'Siswa sudah memiliki data absensi hari ini!');
                } else {
                    $stmt = $pdo->prepare("INSERT INTO absensi (siswa_id, tanggal, keterangan, ket_detail, petugas_id) 
                                          VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$siswa['id'], $today, $keterangan, $ket_detail, $_SESSION['user_id']]);
                    
                    flashMessage('success', "Absensi {$keterangan} berhasil untuk {$siswa['nama']}");
                }
            } else {
                flashMessage('danger', 'NIPD tidak ditemukan!');
            }
        }
        
        redirect(BASE_URL . 'modules/petugas/dashboard.php');
        
    } catch (PDOException $e) {
        flashMessage('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        redirect(BASE_URL . 'modules/petugas/dashboard.php');
    }
}

// Get today's absensi summary
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ?");
$stmt->execute([$today]);
$total_absen_hari_ini = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND status_telat = 1");
$stmt->execute([$today]);
$total_telat = $stmt->fetch()['total'];

$page_title = 'Dashboard Petugas';
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
    <?php include '../../includes/petugas_header.php'; ?>
    
    <div class="app-container">
        <?php include '../../includes/petugas_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="card mb-2">
                <div class="card-header">
                    <h2 style="margin: 0;">📋 Dashboard Petugas Absensi</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Selamat datang, <strong><?= htmlspecialchars($user) ?></strong>!<br>
                        Hari ini: <span id="realtime-clock"></span><br>
                        Jam Masuk: <strong><?= date('H:i', strtotime($jam_masuk)) ?></strong> | 
                        Jam Pulang: <strong><?= date('H:i', strtotime($jam_pulang)) ?></strong> | 
                        Toleransi Telat: <strong><?= $toleransi_telat ?> menit</strong>
                    </div>

                    <!-- Quick Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><?= $total_absen_hari_ini ?></h3>
                            <p>Total Absen Hari Ini</p>
                        </div>
                        <div class="stat-card">
                            <h3 style="color: #ffaa44;"><?= $total_telat ?></h3>
                            <p>Siswa Telat</p>
                        </div>
                    </div>

                    <!-- Absensi Forms -->
                    <div class="dashboard-grid">
                        <!-- Absen Masuk -->
                        <div class="card">
                            <div class="card-header">
                                <h3>🌅 Absen Masuk</h3>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="absen_masuk">
                                <div class="form-group">
                                    <label class="form-label">NIPD Siswa</label>
                                    <input type="text" name="nipd" class="form-control" placeholder="Masukkan NIPD" required autofocus>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    ✅ Catat Kehadiran Masuk
                                </button>
                            </form>
                        </div>

                        <!-- Absen Pulang -->
                        <div class="card">
                            <div class="card-header">
                                <h3>🌇 Absen Pulang</h3>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="absen_pulang">
                                <div class="form-group">
                                    <label class="form-label">NIPD Siswa</label>
                                    <input type="text" name="nipd" class="form-control" placeholder="Masukkan NIPD" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    ✅ Catat Kepulangan
                                </button>
                            </form>
                        </div>

                        <!-- Berhalangan -->
                        <div class="card">
                            <div class="card-header">
                                <h3>😷 Absensi Berhalangan</h3>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="berhalangan">
                                <div class="form-group">
                                    <label class="form-label">NIPD Siswa</label>
                                    <input type="text" name="nipd" class="form-control" placeholder="Masukkan NIPD" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Keterangan</label>
                                    <select name="keterangan" class="form-control" required>
                                        <option value="sakit">Sakit</option>
                                        <option value="izin">Izin</option>
                                        <option value="alfa">Alfa (Tanpa Keterangan)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Detail (Opsional)</label>
                                    <input type="text" name="ket_detail" class="form-control" placeholder="Contoh: Surat dokter terlampir">
                                </div>
                                <button type="submit" class="btn btn-warning w-100">
                                    📝 Catat Ketidakhadiran
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
