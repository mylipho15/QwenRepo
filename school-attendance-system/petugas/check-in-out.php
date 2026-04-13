<?php
include '../includes/petugas-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle check-in/check-out submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $nipd = sanitize($_POST['nipd']);
    
    // Get student data
    $student = $conn->query("SELECT * FROM students WHERE nipd = '$nipd'")->fetch_assoc();
    
    if (!$student) {
        $message = 'Siswa dengan NIPD tersebut tidak ditemukan!';
        $message_type = 'danger';
    } else {
        $today = date('Y-m-d');
        $now = date('H:i:s');
        $student_id = $student['id'];
        $user_id = $_SESSION['user_id'];
        
        // Get attendance settings
        $check_in_end = getSetting('check_in_end', '08:00');
        $late_threshold = (int)getSetting('late_threshold', 15);
        
        if ($action === 'check_in') {
            // Check if already checked in today
            $existing = $conn->query("SELECT * FROM attendance WHERE student_id = $student_id AND date = '$today'");
            
            if ($existing->num_rows > 0) {
                $message = 'Siswa ini sudah melakukan check-in hari ini!';
                $message_type = 'warning';
            } else {
                // Check if late
                $is_late = 0;
                $check_in_time = strtotime($now);
                $deadline = strtotime($check_in_end);
                
                if ($check_in_time > $deadline) {
                    $diff_minutes = ($check_in_time - $deadline) / 60;
                    if ($diff_minutes > $late_threshold) {
                        $is_late = 1;
                    }
                }
                
                $conn->query("INSERT INTO attendance (student_id, date, check_in, status, is_late, recorded_by) 
                              VALUES ($student_id, '$today', '$now', 'present', $is_late, $user_id)");
                
                $message = "Check-in berhasil untuk {$student['name']} ({$student['class']})";
                if ($is_late) {
                    $message .= ' - <span class="text-warning">TERLAMBAT</span>';
                }
                $message_type = 'success';
            }
        } elseif ($action === 'check_out') {
            // Check if checked in today
            $existing = $conn->query("SELECT * FROM attendance WHERE student_id = $student_id AND date = '$today'");
            
            if ($existing->num_rows === 0) {
                $message = 'Siswa ini belum melakukan check-in hari ini!';
                $message_type = 'warning';
            } else {
                $attendance = $existing->fetch_assoc();
                
                if ($attendance['check_out']) {
                    $message = 'Siswa ini sudah melakukan check-out hari ini!';
                    $message_type = 'warning';
                } else {
                    $conn->query("UPDATE attendance SET check_out = '$now' WHERE id = {$attendance['id']}");
                    $message = "Check-out berhasil untuk {$student['name']} ({$student['class']})";
                    $message_type = 'success';
                }
            }
        }
    }
}

// Get today's recent attendance
$today = date('Y-m-d');
$recent = $conn->query("
    SELECT a.*, s.name, s.class, s.nipd 
    FROM attendance a 
    JOIN students s ON a.student_id = s.id 
    WHERE a.date = '$today'
    ORDER BY a.created_at DESC 
    LIMIT 10
");

$conn->close();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clock"></i> Check-In (Jam Masuk)</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="check_in">
                <div class="form-group">
                    <label class="form-label" for="nipd_checkin">
                        <i class="fas fa-id-card"></i> NIPD Siswa
                    </label>
                    <input type="text" name="nipd" id="nipd_checkin" class="form-control" 
                           placeholder="Masukkan NIPD siswa" required autofocus>
                </div>
                <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Check-In
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-door-open"></i> Check-Out (Jam Pulang)</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="check_out">
                <div class="form-group">
                    <label class="form-label" for="nipd_checkout">
                        <i class="fas fa-id-card"></i> NIPD Siswa
                    </label>
                    <input type="text" name="nipd" id="nipd_checkout" class="form-control" 
                           placeholder="Masukkan NIPD siswa" required>
                </div>
                <button type="submit" class="btn btn-info btn-lg" style="width: 100%;">
                    <i class="fas fa-sign-out-alt"></i> Check-Out
                </button>
            </form>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Aktivitas Hari Ini</h3>
    </div>
    <div class="card-body">
        <?php if ($recent->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>NIPD</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nipd']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['class']); ?></td>
                                <td><?php echo $row['check_in'] ? date('H:i', strtotime($row['check_in'])) : '-'; ?></td>
                                <td><?php echo $row['check_out'] ? date('H:i', strtotime($row['check_out'])) : '-'; ?></td>
                                <td>
                                    <?php
                                    if ($row['is_late']) {
                                        echo '<span class="badge badge-warning">Telat</span>';
                                    } elseif ($row['status'] === 'present') {
                                        echo '<span class="badge badge-success">Hadir</span>';
                                    } else {
                                        echo '<span class="badge badge-secondary">' . ucfirst($row['status']) . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">Belum ada absensi hari ini.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle"></i> Panduan</h3>
    </div>
    <div class="card-body">
        <ul>
            <li><strong>Check-In:</strong> Masukkan NIPD siswa untuk mencatat jam masuk.</li>
            <li><strong>Check-Out:</strong> Masukkan NIPD siswa untuk mencatat jam pulang.</li>
            <li>Siswa yang check-in setelah waktu yang ditentukan akan ditandai sebagai <strong>TERLAMBAT</strong>.</li>
            <li>Pastikan siswa sudah check-in sebelum melakukan check-out.</li>
        </ul>
    </div>
</div>

<script>
// Auto-focus on NIPD input after form submission
document.addEventListener('DOMContentLoaded', function() {
    const checkinInput = document.getElementById('nipd_checkin');
    const checkoutInput = document.getElementById('nipd_checkout');
    
    if (checkinInput && !checkinInput.value) {
        checkinInput.focus();
    }
});
</script>

<?php include '../includes/petugas-footer.php'; ?>
