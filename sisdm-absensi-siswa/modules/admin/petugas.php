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

// Handle petugas assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $user_id = $_POST['user_id'];
    
    // Check if already exists for this date
    $check = $db->prepare("SELECT id FROM petugas_harian WHERE tanggal = ?");
    $check->execute([$tanggal]);
    
    if ($check->fetch()) {
        $stmt = $db->prepare("UPDATE petugas_harian SET user_id = ? WHERE tanggal = ?");
        $stmt->execute([$user_id, $tanggal]);
    } else {
        $stmt = $db->prepare("INSERT INTO petugas_harian (tanggal, user_id) VALUES (?, ?)");
        $stmt->execute([$tanggal, $user_id]);
    }
    $success = 'Petugas harian berhasil diatur!';

// Get petugas list (only petugas role)
$stmt = $db->query("SELECT * FROM users WHERE role = 'petugas' ORDER BY nama_lengkap");
$petugas_list = $stmt->fetchAll();

// Get current week's assignments
$current_week_start = date('Y-m-d', strtotime('monday this week'));
$stmt = $db->prepare("SELECT p.*, u.nama_lengkap 
                      FROM petugas_harian p 
                      JOIN users u ON p.user_id = u.id 
                      WHERE p.tanggal >= ? 
                      ORDER BY p.tanggal");
$stmt->execute([$current_week_start]);
$assignments = $stmt->fetchAll();

$page_title = 'Petugas Harian - Admin';
include '../../includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">👤 Pengaturan Petugas Absensi Harian</div>
    <p>Atur petugas absensi untuk setiap hari. Petugas dapat diganti setiap harinya.</p>
    
    <form method="POST" class="d-flex gap-1 align-center mb-2">
        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        <select name="user_id" class="form-control" required>
            <option value="">Pilih Petugas</option>
            <?php foreach ($petugas_list as $p): ?>
            <option value="<?php echo $p['id']; ?>">
                <?php echo htmlspecialchars($p['nama_lengkap']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Atur Petugas</button>
    </form>
</div>

<div class="card">
    <div class="card-header">Jadwal Petugas Minggu Ini</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $days = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
                     'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
            for ($i = 0; $i < 7; $i++): 
                $date = date('Y-m-d', strtotime("$current_week_start +$i days"));
                $day_name = $days[date('l', strtotime($date))];
                $assignment = null;
                foreach ($assignments as $a) {
                    if ($a['tanggal'] === $date) {
                        $assignment = $a;
                        break;
                    }
                }
            ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($date)); ?></td>
                <td><?php echo $day_name; ?></td>
                <td>
                    <?php if ($assignment): ?>
                        ✓ <?php echo htmlspecialchars($assignment['nama_lengkap']); ?>
                    <?php else: ?>
                        <em style="color: var(--text-secondary);">Belum diatur</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
