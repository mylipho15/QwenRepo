<?php
include '../includes/petugas-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nipd = sanitize($_POST['nipd']);
    $status = $_POST['status']; // sick, permission, alpha
    $date = $_POST['date'];
    $notes = sanitize($_POST['notes']);
    
    // Get student data
    $student = $conn->query("SELECT * FROM students WHERE nipd = '$nipd'")->fetch_assoc();
    
    if (!$student) {
        $message = 'Siswa dengan NIPD tersebut tidak ditemukan!';
        $message_type = 'danger';
    } else {
        $student_id = $student['id'];
        $user_id = $_SESSION['user_id'];
        
        // Check if attendance already exists for this date
        $existing = $conn->query("SELECT * FROM attendance WHERE student_id = $student_id AND date = '$date'");
        
        if ($existing->num_rows > 0) {
            // Update existing record
            $conn->query("UPDATE attendance SET status = '$status', notes = '$notes' 
                          WHERE student_id = $student_id AND date = '$date'");
            $message = "Data absensi berhalangan untuk {$student['name']} berhasil diperbarui!";
            $message_type = 'success';
        } else {
            // Insert new record
            $conn->query("INSERT INTO attendance (student_id, date, status, notes, recorded_by) 
                          VALUES ($student_id, '$date', '$status', '$notes', $user_id)");
            $message = "Data absensi berhalangan untuk {$student['name']} berhasil ditambahkan!";
            $message_type = 'success';
        }
    }
}

// Get recent berhalangan records
$recent = $conn->query("
    SELECT a.*, s.name, s.class, s.nipd 
    FROM attendance a 
    JOIN students s ON a.student_id = s.id 
    WHERE a.status IN ('sick', 'permission', 'alpha')
    ORDER BY a.date DESC 
    LIMIT 20
");

$conn->close();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-medical"></i> Absensi Berhalangan (Sakit/Izin/Alfa)</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label" for="nipd">
                        <i class="fas fa-id-card"></i> NIPD Siswa
                    </label>
                    <input type="text" name="nipd" id="nipd" class="form-control" 
                           placeholder="Masukkan NIPD siswa" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="date">
                        <i class="fas fa-calendar"></i> Tanggal
                    </label>
                    <input type="date" name="date" id="date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="status">
                    <i class="fas fa-clipboard-list"></i> Status Berhalangan
                </label>
                <select name="status" id="status" class="form-control" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="sick">Sakit</option>
                    <option value="permission">Izin</option>
                    <option value="alpha">Alfa (Tanpa Keterangan)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">
                    <i class="fas fa-comment"></i> Keterangan
                </label>
                <textarea name="notes" id="notes" class="form-control" 
                          placeholder="Masukkan keterangan (contoh: sakit demam, izin keluarga, dll)" 
                          rows="4"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Absensi Berhalangan</h3>
    </div>
    <div class="card-body">
        <?php if ($recent->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>NIPD</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nipd']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['class']); ?></td>
                                <td>
                                    <?php
                                    $badge_class = 'secondary';
                                    $status_label = ucfirst($row['status']);
                                    
                                    switch($row['status']) {
                                        case 'sick':
                                            $badge_class = 'warning';
                                            $status_label = 'Sakit';
                                            break;
                                        case 'permission':
                                            $badge_class = 'info';
                                            $status_label = 'Izin';
                                            break;
                                        case 'alpha':
                                            $badge_class = 'danger';
                                            $status_label = 'Alfa';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?>"><?php echo $status_label; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['notes'] ?? '-'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">Belum ada data absensi berhalangan.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle"></i> Panduan</h3>
    </div>
    <div class="card-body">
        <ul>
            <li><strong>Sakit:</strong> Siswa tidak hadir karena alasan kesehatan (dengan surat dokter jika diperlukan).</li>
            <li><strong>Izin:</strong> Siswa tidak hadir karena alasan tertentu yang telah disetujui (surat izin dari orang tua/wali).</li>
            <li><strong>Alfa:</strong> Siswa tidak hadir tanpa keterangan/tanpa izin.</li>
            <li>Pastikan mengisi keterangan dengan jelas untuk memudahkan tracking.</li>
        </ul>
    </div>
</div>

<script>
// Auto-focus on NIPD input after form submission
document.addEventListener('DOMContentLoaded', function() {
    const nipdInput = document.getElementById('nipd');
    
    if (nipdInput && !nipdInput.value) {
        nipdInput.focus();
    }
});
</script>

<?php include '../includes/petugas-footer.php'; ?>
