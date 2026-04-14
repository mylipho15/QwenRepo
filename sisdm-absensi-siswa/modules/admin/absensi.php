<?php
require_once '../../config/database.php';
checkAuth('admin');

$db = Database::getInstance()->getConnection();
$school = getSchoolInfo();
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_absensi':
            $siswa_id = $_POST['siswa_id'];
            $tanggal = $_POST['tanggal'];
            $jam_datang = $_POST['jam_datang'] ?? null;
            $jam_pulang = $_POST['jam_pulang'] ?? null;
            $keterangan = $_POST['keterangan'];
            $catatan = $_POST['catatan'] ?? '';
            $petugas_id = $_SESSION['user_id'];
            
            // Check if already exists
            $check = $db->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
            $check->execute([$siswa_id, $tanggal]);
            
            if ($check->fetch()) {
                // Update existing
                $stmt = $db->prepare("UPDATE absensi SET jam_datang=?, jam_pulang=?, keterangan=?, catatan=?, petugas_id=? 
                                     WHERE siswa_id=? AND tanggal=?");
                $stmt->execute([$jam_datang, $jam_pulang, $keterangan, $catatan, $petugas_id, $siswa_id, $tanggal]);
            } else {
                // Insert new
                $stmt = $db->prepare("INSERT INTO absensi (siswa_id, tanggal, jam_datang, jam_pulang, keterangan, catatan, petugas_id) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$siswa_id, $tanggal, $jam_datang, $jam_pulang, $keterangan, $catatan, $petugas_id]);
            }
            $success = 'Data absensi berhasil disimpan!';
            break;
            
        case 'delete_absensi':
            $id = $_POST['id'];
            $stmt = $db->prepare("DELETE FROM absensi WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Data absensi berhasil dihapus!';
            break;
    }
}

// Get filters
$filter_tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$filter_kelas = $_GET['kelas'] ?? '';

// Build query
$where = ["a.tanggal = ?"];
$params = [$filter_tanggal];

if ($filter_kelas) {
    $where[] = "s.kelas_id = ?";
    $params[] = $filter_kelas;
}

$sql = "SELECT a.*, s.nipd, s.nama, k.nama_kelas, 
               CASE WHEN a.jam_datang > '07:30:00' THEN 'telat' ELSE 'tepat_waktu' END as status_datang_calc,
               CASE WHEN a.jam_pulang < '15:30:00' AND a.keterangan = 'hadir' THEN 'pulang_awal' ELSE 'tepat_waktu' END as status_pulang_calc
        FROM absensi a
        JOIN siswa s ON a.siswa_id = s.id
        LEFT JOIN kelas k ON s.kelas_id = k.id
        WHERE " . implode(' AND ', $where);

$stmt = $db->prepare($sql);
$stmt->execute($params);
$absensi_list = $stmt->fetchAll();

// Get all students for dropdown
$siswa_stmt = $db->query("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.nipd");
$siswa_list = $siswa_stmt->fetchAll();

// Get all classes for filter
$kelas_stmt = $db->query("SELECT * FROM kelas ORDER BY nama_kelas");
$kelas_list = $kelas_stmt->fetchAll();

$page_title = 'Data Absensi - Admin';
include '../../includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between align-center">
            <span>📝 Data Absensi</span>
            <button class="btn btn-primary" onclick="ModalManager.open('modal-add-absensi')">
                + Input Absensi
            </button>
        </div>
    </div>
    
    <div class="mb-2 d-flex gap-1">
        <form method="GET" class="d-flex gap-1">
            <input type="date" name="tanggal" class="form-control" value="<?php echo $filter_tanggal; ?>">
            <select name="kelas" class="form-control">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelas_list as $k): ?>
                <option value="<?php echo $k['id']; ?>" <?php echo $filter_kelas == $k['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($k['nama_kelas']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIPD</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Jam Datang</th>
                    <th>Status</th>
                    <th>Jam Pulang</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($absensi_list as $a): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($a['nipd']); ?></td>
                    <td><?php echo htmlspecialchars($a['nama']); ?></td>
                    <td><?php echo htmlspecialchars($a['nama_kelas'] ?? '-'); ?></td>
                    <td><?php echo $a['jam_datang'] ? date('H:i', strtotime($a['jam_datang'])) : '-'; ?></td>
                    <td>
                        <?php if ($a['status_datang'] === 'telat' || $a['status_datang_calc'] === 'telat'): ?>
                            <span style="color: var(--warning);">⏰ Telat</span>
                        <?php else: ?>
                            <span style="color: var(--success);">✓ Tepat Waktu</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $a['jam_pulang'] ? date('H:i', strtotime($a['jam_pulang'])) : '-'; ?></td>
                    <td>
                        <?php
                        $badge_color = 'success';
                        $badge_text = $a['keterangan'];
                        if ($a['keterangan'] === 'sakit') $badge_color = 'warning';
                        if ($a['keterangan'] === 'izin' || $a['keterangan'] === 'alfa') $badge_color = 'danger';
                        ?>
                        <span style="color: var(--<?php echo $badge_color; ?>);">
                            <?php echo strtoupper($badge_text); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('Yakin ingin menghapus?')">
                            <input type="hidden" name="action" value="delete_absensi">
                            <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($absensi_list)): ?>
                <tr>
                    <td colspan="9" class="text-center">Belum ada data absensi untuk tanggal ini</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Absensi -->
<div id="modal-add-absensi" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h3>Input Absensi</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_absensi">
            
            <div class="form-group">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Siswa</label>
                <select class="form-control" name="siswa_id" required>
                    <option value="">Pilih Siswa</option>
                    <?php foreach ($siswa_list as $s): ?>
                    <option value="<?php echo $s['id']; ?>">
                        <?php echo htmlspecialchars($s['nipd'] . ' - ' . $s['nama'] . ' (' . ($s['nama_kelas'] ?? '-') . ')'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Jam Datang</label>
                <input type="time" class="form-control" name="jam_datang">
            </div>
            
            <div class="form-group">
                <label class="form-label">Jam Pulang</label>
                <input type="time" class="form-control" name="jam_pulang">
            </div>
            
            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <select class="form-control" name="keterangan" required>
                    <option value="hadir">Hadir</option>
                    <option value="sakit">Sakit</option>
                    <option value="izin">Izin</option>
                    <option value="alfa">Alfa</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea class="form-control" name="catatan" rows="3"></textarea>
            </div>
            
            <div class="d-flex gap-1 justify-between mt-2">
                <button type="button" class="btn btn-secondary" onclick="ModalManager.close('modal-add-absensi')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
