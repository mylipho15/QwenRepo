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
    $jenis_izin = $_POST['jenis_izin'];
    $jam_keluar = $_POST['jam_keluar'] ?? null;
    $alasan = $_POST['alasan'] ?? '';
    
    $stmt = $db->prepare("INSERT INTO izin_keluar (siswa_id, tanggal, jenis_izin, jam_keluar, alasan, petugas_id) 
                         VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$siswa_id, $tanggal, $jenis_izin, $jam_keluar, $alasan, $petugas_id]);
    
    // If telat, update absensi
    if ($jenis_izin === 'telat' && $jam_keluar) {
        $check = $db->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
        $check->execute([$siswa_id, $tanggal]);
        
        if ($check->fetch()) {
            $stmt = $db->prepare("UPDATE absensi SET jam_datang = ?, status_datang = 'telat', keterangan = 'hadir' 
                                 WHERE siswa_id = ? AND tanggal = ?");
            $stmt->execute([$jam_keluar, $siswa_id, $tanggal]);
        } else {
            $stmt = $db->prepare("INSERT INTO absensi (siswa_id, tanggal, jam_datang, status_datang, keterangan, petugas_id) 
                                 VALUES (?, ?, ?, 'telat', 'hadir', ?)");
            $stmt->execute([$siswa_id, $tanggal, $jam_keluar, $petugas_id]);
        }
    }
    
    $success = 'Izin khusus berhasil dicatat!';

// Get all students
$stmt = $db->query("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.nipd");
$siswa_list = $stmt->fetchAll();

$page_title = 'Izin Khusus';
include '../../includes/header_petugas.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">📝 Input Izin Khusus</div>
    <p>Catat izin khusus untuk siswa: Telat, Keluar Lingkungan Sekolah, atau Pulang Awal.</p>
    
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
            <label class="form-label">Jenis Izin</label>
            <select name="jenis_izin" class="form-control" required onchange="toggleJamField()">
                <option value="">Pilih Jenis Izin</option>
                <option value="telat">⏰ Telat Datang</option>
                <option value="keluar_lingkungan">🚶 Keluar Lingkungan Sekolah</option>
                <option value="pulang_awal">🏠 Pulang Awal</option>
            </select>
        </div>
        
        <div class="form-group" id="jam-field" style="display: none;">
            <label class="form-label" id="jam-label">Jam</label>
            <input type="time" name="jam_keluar" class="form-control" value="<?php echo date('H:i'); ?>">
        </div>
        
        <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label">Alasan</label>
            <textarea name="alasan" class="form-control" rows="3" placeholder="Jelaskan alasan izin..." required></textarea>
        </div>
        
        <div class="form-group" style="grid-column: 1 / -1;">
            <button type="submit" class="btn btn-primary">Simpan Izin</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">Riwayat Izin Khusus Hari Ini</div>
    <?php
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT i.*, s.nipd, s.nama, k.nama_kelas 
                          FROM izin_keluar i 
                          JOIN siswa s ON i.siswa_id = s.id 
                          LEFT JOIN kelas k ON s.kelas_id = k.id 
                          WHERE i.tanggal = ?
                          ORDER BY i.created_at DESC");
    $stmt->execute([$today]);
    $riwayat = $stmt->fetchAll();
    ?>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Jam</th>
                <th>NIPD</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Jenis Izin</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($riwayat as $r): ?>
            <tr>
                <td><?php echo $r['jam_keluar'] ? date('H:i', strtotime($r['jam_keluar'])) : '-'; ?></td>
                <td><?php echo htmlspecialchars($r['nipd']); ?></td>
                <td><?php echo htmlspecialchars($r['nama']); ?></td>
                <td><?php echo htmlspecialchars($r['nama_kelas'] ?? '-'); ?></td>
                <td>
                    <?php
                    $icons = [
                        'telat' => '⏰ Telat',
                        'keluar_lingkungan' => '🚶 Keluar',
                        'pulang_awal' => '🏠 Pulang Awal'
                    ];
                    echo $icons[$r['jenis_izin']] ?? $r['jenis_izin'];
                    ?>
                </td>
                <td><?php echo htmlspecialchars($r['alasan'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($riwayat)): ?>
            <tr>
                <td colspan="6" class="text-center">Belum ada izin khusus hari ini</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleJamField() {
    const jenis = document.querySelector('select[name="jenis_izin"]').value;
    const field = document.getElementById('jam-field');
    const label = document.getElementById('jam-label');
    
    if (jenis === 'telat') {
        field.style.display = 'block';
        label.textContent = 'Jam Datang (Telat)';
    } else if (jenis === 'keluar_lingkungan') {
        field.style.display = 'block';
        label.textContent = 'Jam Keluar';
    } else if (jenis === 'pulang_awal') {
        field.style.display = 'block';
        label.textContent = 'Jam Pulang';
    } else {
        field.style.display = 'none';
    }
</script>

<?php include '../../includes/footer.php'; ?>
