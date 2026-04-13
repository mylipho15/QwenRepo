<?php
include '../includes/admin-header.php';

// Get statistics
$conn = getDBConnection();

// Total students
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];

// Today's attendance
$today = date('Y-m-d');
$today_attendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today'")->fetch_assoc()['count'];

// Present today
$present_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = 'present'")->fetch_assoc()['count'];

// Late today
$late_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND is_late = 1")->fetch_assoc()['count'];

// Sick/Permission/Alpha today
$sick_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = 'sick'")->fetch_assoc()['count'];
$permission_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = 'permission'")->fetch_assoc()['count'];
$alpha_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = 'alpha'")->fetch_assoc()['count'];

// Left early today
$left_early = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND left_early = 1")->fetch_assoc()['count'];

// Recent attendance
$recent_attendance = $conn->query("
    SELECT a.*, s.name, s.nipd, s.class, s.major 
    FROM attendance a 
    JOIN students s ON a.student_id = s.id 
    WHERE a.date = '$today'
    ORDER BY a.check_in DESC 
    LIMIT 10
");

$conn->close();
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-value"><?php echo $total_students; ?></div>
        <div class="stat-label">Total Siswa</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-value"><?php echo $present_today; ?></div>
        <div class="stat-label">Hadir Hari Ini</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-value"><?php echo $late_today; ?></div>
        <div class="stat-label">Telat Hari Ini</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon error">
            <i class="fas fa-user-times"></i>
        </div>
        <div class="stat-value"><?php echo $alpha_today; ?></div>
        <div class="stat-label">Alfa Hari Ini</div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Ringkasan Hari Ini</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <tbody>
                    <tr>
                        <td>Total Absensi</td>
                        <td class="text-right"><strong><?php echo $today_attendance; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Hadir</td>
                        <td class="text-right"><span class="badge badge-success"><?php echo $present_today; ?></span></td>
                    </tr>
                    <tr>
                        <td>Sakit</td>
                        <td class="text-right"><span class="badge badge-warning"><?php echo $sick_today; ?></span></td>
                    </tr>
                    <tr>
                        <td>Izin</td>
                        <td class="text-right"><span class="badge badge-info"><?php echo $permission_today; ?></span></td>
                    </tr>
                    <tr>
                        <td>Alfa</td>
                        <td class="text-right"><span class="badge badge-danger"><?php echo $alpha_today; ?></span></td>
                    </tr>
                    <tr>
                        <td>Telat</td>
                        <td class="text-right"><span class="badge badge-warning"><?php echo $late_today; ?></span></td>
                    </tr>
                    <tr>
                        <td>Pulang Awal</td>
                        <td class="text-right"><span class="badge badge-warning"><?php echo $left_early; ?></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> Aktivitas Terbaru</h3>
            <a href="attendance.php" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="card-body">
            <?php if ($recent_attendance->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Jam Masuk</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_attendance->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['class']); ?></td>
                                    <td><?php echo $row['check_in'] ? date('H:i', strtotime($row['check_in'])) : '-'; ?></td>
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
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Sekolah</h3>
    </div>
    <div class="card-body">
        <div class="dashboard-grid">
            <div>
                <p><strong>NPSN:</strong> <?php echo SCHOOL_NPSN; ?></p>
                <p><strong>Nama Sekolah:</strong> <?php echo SCHOOL_NAME; ?></p>
                <p><strong>Alamat:</strong> <?php echo SCHOOL_ADDRESS; ?></p>
            </div>
            <div>
                <p><strong>Telepon:</strong> <?php echo SCHOOL_PHONE; ?></p>
                <p><strong>Website:</strong> <?php echo SCHOOL_WEBSITE; ?></p>
                <p><strong>Versi Aplikasi:</strong> <?php echo APP_VERSION; ?></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
