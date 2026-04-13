<?php
include '../includes/admin-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $date = $_POST['date'];
    $check_in = $_POST['check_in'] ?: null;
    $check_out = $_POST['check_out'] ?: null;
    $status = $_POST['status'];
    $is_late = isset($_POST['is_late']) ? 1 : 0;
    $left_early = isset($_POST['left_early']) ? 1 : 0;
    $notes = sanitize($_POST['notes']);
    $recorded_by = $_SESSION['user_id'];

    // Check if attendance already exists
    $existing = $conn->query("SELECT id FROM attendance WHERE student_id = $student_id AND date = '$date'");
    
    if ($existing->num_rows > 0) {
        // Update existing
        $conn->query("UPDATE attendance SET 
            check_in = " . ($check_in ? "'$check_in'" : 'NULL') . ",
            check_out = " . ($check_out ? "'$check_out'" : 'NULL') . ",
            status = '$status',
            is_late = $is_late,
            left_early = $left_early,
            notes = '" . $conn->real_escape_string($notes) . "',
            recorded_by = $recorded_by
            WHERE student_id = $student_id AND date = '$date'
        ");
        $message = 'Data absensi berhasil diperbarui!';
        $message_type = 'success';
    } else {
        // Insert new
        $conn->query("INSERT INTO attendance (student_id, date, check_in, check_out, status, is_late, left_early, notes, recorded_by) 
                      VALUES ($student_id, '$date', " . ($check_in ? "'$check_in'" : 'NULL') . ", " . ($check_out ? "'$check_out'" : 'NULL') . ", 
                              '$status', $is_late, $left_early, '" . $conn->real_escape_string($notes) . "', $recorded_by)");
        $message = 'Data absensi berhasil ditambahkan!';
        $message_type = 'success';
    }
}

// Get all students
$students = $conn->query("SELECT * FROM students ORDER BY class, name");

$conn->close();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Tambah Data Absensi</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label" for="student_id">
                        <i class="fas fa-user"></i> Siswa
                    </label>
                    <select name="student_id" id="student_id" class="form-control" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <option value="<?php echo $student['id']; ?>">
                                <?php echo htmlspecialchars($student['name']); ?> - 
                                <?php echo htmlspecialchars($student['class']); ?> (<?php echo htmlspecialchars($student['nipd']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="date">
                        <i class="fas fa-calendar"></i> Tanggal
                    </label>
                    <input type="date" name="date" id="date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label" for="check_in">
                        <i class="fas fa-clock"></i> Jam Masuk
                    </label>
                    <input type="time" name="check_in" id="check_in" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label" for="check_out">
                        <i class="fas fa-clock"></i> Jam Pulang
                    </label>
                    <input type="time" name="check_out" id="check_out" class="form-control">
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label" for="status">
                        <i class="fas fa-check-circle"></i> Status
                    </label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="present">Hadir</option>
                        <option value="sick">Sakit</option>
                        <option value="permission">Izin</option>
                        <option value="alpha">Alfa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Khusus</label>
                    <div style="display: flex; gap: 20px; align-items: center; height: 42px;">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="is_late" id="is_late"> Telat Datang
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="left_early" id="left_early"> Pulang Awal
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">
                    <i class="fas fa-sticky-note"></i> Catatan
                </label>
                <textarea name="notes" id="notes" class="form-control" rows="3" 
                          placeholder="Masukkan catatan tambahan..."></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="attendance.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-detect late based on check-in time
document.getElementById('check_in').addEventListener('change', function() {
    const checkInTime = this.value;
    const lateThreshold = '08:00'; // Configurable
    
    if (checkInTime && checkInTime > lateThreshold) {
        document.getElementById('is_late').checked = true;
        Notification.warning('Siswa telat berdasarkan jam masuk!');
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?>
