<?php
include '../includes/petugas-header.php';

$conn = getDBConnection();

// Get today's statistics
$today = date('Y-m-d');
$today_count = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today'")->fetch_assoc()['count'];
$present_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = 'present'")->fetch_assoc()['count'];
$late_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND is_late = 1")->fetch_assoc()['count'];

// Recent attendance
$recent = $conn->query("
    SELECT a.*, s.name, s.class 
    FROM attendance a 
    JOIN students s ON a.student_id = s.id 
    WHERE a.date = '$today'
    ORDER BY a.created_at DESC 
    LIMIT 5
");

$conn->close();
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-value"><?php echo $today_count; ?></div>
        <div class="stat-label">Absensi Hari Ini</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-value"><?php echo $present_today; ?></div>
        <div class="stat-label">Hadir</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-value"><?php echo $late_today; ?></div>
        <div class="stat-label">Telat</div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Menu Cepat</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="check-in-out.php" class="btn btn-lg btn-primary" style="height: 100px; flex-direction: column;">
                    <i class="fas fa-clock" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    Absensi Masuk/Keluar
                </a>
                <a href="permission.php" class="btn btn-lg btn-warning" style="height: 100px; flex-direction: column;">
                    <i class="fas fa-file-medical" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    Absensi Berhalangan
                </a>
                <a href="special-permission.php" class="btn btn-lg btn-info" style="height: 100px; flex-direction: column;">
                    <i class="fas fa-passport" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    Izin Khusus
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> Aktivitas Terbaru</h3>
        </div>
        <div class="card-body">
            <?php if ($recent->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <?php while ($row = $recent->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['class']); ?></small>
                                    </td>
                                    <td class="text-right">
                                        <?php echo $row['check_in'] ? date('H:i', strtotime($row['check_in'])) : '-'; ?>
                                        <br>
                                        <?php
                                        if ($row['is_late']) {
                                            echo '<span class="badge badge-warning">Telat</span>';
                                        } elseif ($row['status'] === 'present') {
                                            echo '<span class="badge badge-success">Hadir</span>';
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

<?php include '../includes/petugas-footer.php'; ?>
