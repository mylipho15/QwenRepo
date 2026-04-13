<?php
include '../includes/petugas-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nipd = sanitize($_POST['nipd']);
    $permission_type = $_POST['permission_type']; // late, leave_school, early_leave
    $start_time = $_POST['start_time'];
    $end_time = isset($_POST['end_time']) && $_POST['end_time'] ? $_POST['end_time'] : null;
    $reason = sanitize($_POST['reason']);
    
    // Get student data
    $student = $conn->query("SELECT * FROM students WHERE nipd = '$nipd'")->fetch_assoc();
    
    if (!$student) {
        $message = 'Siswa dengan NIPD tersebut tidak ditemukan!';
        $message_type = 'danger';
    } else {
        $student_id = $student['id'];
        $user_id = $_SESSION['user_id'];
        
        // Insert permission record
        $end_time_sql = $end_time ? "'$end_time'" : 'NULL';
        $conn->query("INSERT INTO permissions (student_id, permission_type, start_time, end_time, reason, status, recorded_by) 
                      VALUES ($student_id, '$permission_type', '$start_time', $end_time_sql, '$reason', 'approved', $user_id)");
        
        // If it's a late permission, update attendance record
        if ($permission_type === 'late') {
            $date = date('Y-m-d', strtotime($start_time));
            $check_in_time = date('H:i:s', strtotime($start_time));
            
            $existing = $conn->query("SELECT * FROM attendance WHERE student_id = $student_id AND date = '$date'");
            
            if ($existing->num_rows > 0) {
                $conn->query("UPDATE attendance SET is_late = 1, notes = CONCAT(IFNULL(notes, ''), ' - Izin Telat: $reason') 
                              WHERE student_id = $student_id AND date = '$date'");
            } else {
                $conn->query("INSERT INTO attendance (student_id, date, check_in, status, is_late, notes, recorded_by) 
                              VALUES ($student_id, '$date', '$check_in_time', 'present', 1, 'Izin Telat: $reason', $user_id)");
            }
        }
        
        // If it's an early leave, update attendance record
        if ($permission_type === 'early_leave' && $end_time) {
            $date = date('Y-m-d', strtotime($start_time));
            
            $conn->query("UPDATE attendance SET left_early = 1, check_out = '" . date('H:i:s', strtotime($end_time)) . ", 
                          notes = CONCAT(IFNULL(notes, ''), ' - Pulang Awal: $reason') 
                          WHERE student_id = $student_id AND date = '$date'");
        }
        
        $message = "Izin khusus untuk {$student['name']} berhasil dicatat!";
        $message_type = 'success';
    }
}

// Get recent permissions
$recent = $conn->query("
    SELECT p.*, s.name, s.class, s.nipd 
    FROM permissions p 
    JOIN students s ON p.student_id = s.id 
    ORDER BY p.created_at DESC 
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
        <h3 class="card-title"><i class="fas fa-passport"></i> Izin Khusus (Telat/Keluar Sekolah/Pulang Awal)</h3>
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
                    <label class="form-label" for="permission_type">
                        <i class="fas fa-clipboard-list"></i> Jenis Izin
                    </label>
                    <select name="permission_type" id="permission_type" class="form-control" required onchange="toggleEndTime()">
                        <option value="">-- Pilih Jenis Izin --</option>
                        <option value="late">Izin Telat Datang</option>
                        <option value="leave_school">Keluar Lingkungan Sekolah (Saat Jam Belajar)</option>
                        <option value="early_leave">Izin Pulang Awal</option>
                    </select>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label" for="start_time">
                        <i class="fas fa-clock"></i> Waktu Mulai/Terjadi
                    </label>
                    <input type="datetime-local" name="start_time" id="start_time" class="form-control" required>
                </div>

                <div class="form-group" id="end_time_group" style="display: none;">
                    <label class="form-label" for="end_time">
                        <i class="fas fa-clock"></i> Waktu Kembali/Pulang
                    </label>
                    <input type="datetime-local" name="end_time" id="end_time" class="form-control">
                    <small class="text-muted">Wajib untuk izin keluar sekolah dan pulang awal</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="reason">
                    <i class="fas fa-comment"></i> Alasan
                </label>
                <textarea name="reason" id="reason" class="form-control" 
                          placeholder="Masukkan alasan izin" 
                          rows="4" required></textarea>
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
        <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Izin Khusus</h3>
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
                            <th>Jenis Izin</th>
                            <th>Waktu</th>
                            <th>Alasan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['start_time'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nipd']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['class']); ?></td>
                                <td>
                                    <?php
                                    $badge_class = 'secondary';
                                    $type_label = ucfirst(str_replace('_', ' ', $row['permission_type']));
                                    
                                    switch($row['permission_type']) {
                                        case 'late':
                                            $badge_class = 'warning';
                                            $type_label = 'Telat';
                                            break;
                                        case 'leave_school':
                                            $badge_class = 'info';
                                            $type_label = 'Keluar Sekolah';
                                            break;
                                        case 'early_leave':
                                            $badge_class = 'danger';
                                            $type_label = 'Pulang Awal';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?>"><?php echo $type_label; ?></span>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('H:i', strtotime($row['start_time'])); ?>
                                        <?php if ($row['end_time']): ?>
                                            - <?php echo date('H:i', strtotime($row['end_time'])); ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td><small><?php echo htmlspecialchars($row['reason']); ?></small></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status'] === 'approved' ? 'success' : ($row['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">Belum ada data izin khusus.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle"></i> Panduan</h3>
    </div>
    <div class="card-body">
        <ul>
            <li><strong>Izin Telat:</strong> Untuk siswa yang datang terlambat dengan alasan yang dapat diterima.</li>
            <li><strong>Keluar Lingkungan Sekolah:</strong> Untuk siswa yang perlu keluar sekolah saat jam belajar (urusan keluarga, berobat, dll).</li>
            <li><strong>Pulang Awal:</strong> Untuk siswa yang diizinkan pulang sebelum waktu yang ditentukan.</li>
            <li>Pastikan mengisi waktu dan alasan dengan jelas untuk dokumentasi.</li>
        </ul>
    </div>
</div>

<script>
function toggleEndTime() {
    const permissionType = document.getElementById('permission_type').value;
    const endTimeGroup = document.getElementById('end_time_group');
    const endTimeInput = document.getElementById('end_time');
    
    if (permission_type === 'leave_school' || permission_type === 'early_leave') {
        endTimeGroup.style.display = 'block';
        endTimeInput.required = true;
    } else {
        endTimeGroup.style.display = 'none';
        endTimeInput.required = false;
        endTimeInput.value = '';
    }
}

// Set default start time to current time
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('start_time').value = now.toISOString().slice(0, 16);
});
</script>

<?php include '../includes/petugas-footer.php'; ?>
