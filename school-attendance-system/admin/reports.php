<?php
include '../includes/admin-header.php';

$conn = getDBConnection();

// Get statistics for reports
$today = date('Y-m-d');
$first_day_of_week = date('Y-m-d', strtotime('monday this week'));
$last_day_of_week = date('Y-m-d', strtotime('sunday this week'));
$first_day_of_month = date('Y-m-01');
$last_day_of_month = date('Y-m-t');

// Weekly stats
$weekly = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick,
        SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission,
        SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha,
        SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN left_early = 1 THEN 1 ELSE 0 END) as early
    FROM attendance 
    WHERE date BETWEEN '$first_day_of_week' AND '$last_day_of_week'
")->fetch_assoc();

// Monthly stats
$monthly = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick,
        SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission,
        SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha,
        SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN left_early = 1 THEN 1 ELSE 0 END) as early
    FROM attendance 
    WHERE date BETWEEN '$first_day_of_month' AND '$last_day_of_month'
")->fetch_assoc();

// Daily breakdown for current month
$daily_data = $conn->query("
    SELECT 
        date,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late
    FROM attendance 
    WHERE date >= '$first_day_of_month'
    GROUP BY date
    ORDER BY date DESC
    LIMIT 30
");

// Top late students (monthly)
$top_late = $conn->query("
    SELECT s.name, s.class, s.nipd, COUNT(*) as late_count
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE a.date >= '$first_day_of_month' AND a.is_late = 1
    GROUP BY a.student_id
    ORDER BY late_count DESC
    LIMIT 10
");

// Attendance by class (monthly)
$class_stats = $conn->query("
    SELECT s.class, 
           COUNT(DISTINCT a.student_id) as students,
           COUNT(a.id) as total_attendance,
           SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
           SUM(CASE WHEN a.is_late = 1 THEN 1 ELSE 0 END) as late,
           ROUND(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id), 2) as percentage
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE a.date >= '$first_day_of_month'
    GROUP BY s.class
    ORDER BY percentage DESC
");

$conn->close();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-week"></i> Rekap Mingguan</h3>
        <small><?php echo date('d/m/Y', strtotime($first_day_of_week)); ?> - <?php echo date('d/m/Y', strtotime($last_day_of_week)); ?></small>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-value"><?php echo $weekly['total']; ?></div>
                <div class="stat-label">Total Absensi</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $weekly['present']; ?></div>
                <div class="stat-label">Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $weekly['late']; ?></div>
                <div class="stat-label">Telat</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon error">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-value"><?php echo $weekly['alpha']; ?></div>
                <div class="stat-label">Alfa</div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Rekap Bulanan</h3>
        <small><?php echo date('F Y'); ?></small>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-value"><?php echo $monthly['total']; ?></div>
                <div class="stat-label">Total Absensi</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $monthly['present']; ?></div>
                <div class="stat-label">Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?php echo $monthly['sick']; ?></div>
                <div class="stat-label">Sakit</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-value"><?php echo $monthly['permission']; ?></div>
                <div class="stat-label">Izin</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon error">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-value"><?php echo $monthly['alpha']; ?></div>
                <div class="stat-label">Alfa</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $monthly['late']; ?></div>
                <div class="stat-label">Telat</div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid mt-3">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-line"></i> Statistik per Kelas</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Siswa</th>
                            <th>Total Absensi</th>
                            <th>Hadir</th>
                            <th>Telat</th>
                            <th>% Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $class_stats->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['class']); ?></strong></td>
                                <td><?php echo $row['students']; ?></td>
                                <td><?php echo $row['total_attendance']; ?></td>
                                <td><?php echo $row['present']; ?></td>
                                <td><?php echo $row['late']; ?></td>
                                <td>
                                    <span class="badge <?php echo $row['percentage'] >= 90 ? 'badge-success' : ($row['percentage'] >= 75 ? 'badge-warning' : 'badge-danger'); ?>">
                                        <?php echo $row['percentage']; ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-clock"></i> Siswa Paling Sering Telat</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>NIPD</th>
                            <th>Jumlah Telat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = $top_late->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['class']); ?></td>
                                <td><?php echo htmlspecialchars($row['nipd']); ?></td>
                                <td><span class="badge badge-warning"><?php echo $row['late_count']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-print"></i> Cetak Laporan</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="print-report.php" target="_blank" class="d-flex gap-2" style="flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Jenis Laporan</label>
                <select name="type" class="form-control">
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $first_day_of_month; ?>">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $last_day_of_month; ?>">
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
