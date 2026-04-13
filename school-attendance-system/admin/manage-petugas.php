<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

include '../config/database.php';

$message = '';
$message_type = '';

// Handle form submission for adding/updating schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tanggal'])) {
    $tanggal = $_POST['tanggal'];
    $petugas_id = (int)$_POST['petugas_id'];
    $keterangan = sanitize($_POST['keterangan'] ?? '');
    
    // Check if schedule exists for this date
    $check = $conn->query("SELECT id FROM jadwal_petugas WHERE tanggal = '$tanggal'");
    
    if ($check->num_rows > 0) {
        // Update existing
        $stmt = $conn->prepare("UPDATE jadwal_petugas SET petugas_id = ?, keterangan = ? WHERE tanggal = ?");
        $stmt->bind_param("iss", $petugas_id, $keterangan, $tanggal);
        if ($stmt->execute()) {
            $message = 'Jadwal petugas berhasil diperbarui!';
            $message_type = 'success';
        } else {
            $message = 'Gagal memperbarui jadwal!';
            $message_type = 'error';
        }
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO jadwal_petugas (tanggal, petugas_id, keterangan) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $tanggal, $petugas_id, $keterangan);
        if ($stmt->execute()) {
            $message = 'Jadwal petugas berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan jadwal!';
            $message_type = 'error';
        }
    }
    $stmt->close();
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM jadwal_petugas WHERE id = $id")) {
        $message = 'Jadwal berhasil dihapus!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus jadwal!';
        $message_type = 'error';
    }
}

// Get all schedules
$schedules = [];
$result = $conn->query("
    SELECT j.*, p.nama_lengkap, p.username 
    FROM jadwal_petugas j 
    JOIN petugas p ON j.petugas_id = p.id 
    ORDER BY j.tanggal DESC
");
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

// Get all petugas
$petugas_list = [];
$result = $conn->query("SELECT * FROM petugas ORDER BY nama_lengkap ASC");
while ($row = $result->fetch_assoc()) {
    $petugas_list[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Petugas - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo getSetting('mode', 'light'); ?> theme-<?php echo getSetting('theme', 'fluent'); ?>">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="content-wrapper">
        <div class="container">
            <h1><i class="fas fa-user-shield"></i> Kelola Jadwal Petugas Absensi</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Daftar Jadwal Petugas</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Petugas</th>
                                    <th>Username</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($schedules)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada jadwal petugas.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($schedules as $s): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($s['tanggal'])); ?></td>
                                            <td><?php echo htmlspecialchars($s['nama_lengkap']); ?></td>
                                            <td><?php echo htmlspecialchars($s['username']); ?></td>
                                            <td><?php echo htmlspecialchars($s['keterangan'] ?? '-'); ?></td>
                                            <td>
                                                <a href="?delete=<?php echo $s['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Hapus jadwal ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus"></i> Tambah/Edit Jadwal</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="dashboard-grid">
                            <div class="form-group">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Petugas</label>
                                <select name="petugas_id" class="form-control" required>
                                    <?php foreach ($petugas_list as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_lengkap']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Shift Pagi"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Jadwal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/admin-footer.php'; ?>
</body>
</html>
