<?php
/**
 * Reports Module - Rekap Absensi Bulanan dan Mingguan
 */

$db = Database::getInstance();
$reportType = $_GET['type'] ?? 'monthly';
$selectedMonth = $_GET['month'] ?? date('Y-m');
$selectedWeek = $_GET['week'] ?? date('W');
$selectedYear = $_GET['year'] ?? date('Y');

// Monthly report data
if ($reportType === 'monthly') {
    $monthData = $db->fetchAll("
        SELECT 
            s.nipd,
            s.full_name,
            c.name as class_name,
            COUNT(a.id) as total_days,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN a.status = 'sick' THEN 1 ELSE 0 END) as sick,
            SUM(CASE WHEN a.status = 'permission' THEN 1 ELSE 0 END) as permission,
            SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as alpha,
            SUM(CASE WHEN a.status = 'early_leave' THEN 1 ELSE 0 END) as early_leave
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN attendances a ON s.id = a.student_id AND DATE_FORMAT(a.date, '%Y-%m') = ?
        WHERE s.is_active = 1
        GROUP BY s.id
        ORDER BY c.name, s.full_name
    ", [$selectedMonth]);
}

// Weekly report data
if ($reportType === 'weekly') {
    $weekData = $db->fetchAll("
        SELECT 
            s.nipd,
            s.full_name,
            c.name as class_name,
            COUNT(a.id) as total_days,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN a.status = 'sick' THEN 1 ELSE 0 END) as sick,
            SUM(CASE WHEN a.status = 'permission' THEN 1 ELSE 0 END) as permission,
            SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as alpha
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN attendances a ON s.id = a.student_id AND YEARWEEK(a.date, 1) = ?
        WHERE s.is_active = 1
        GROUP BY s.id
        ORDER BY c.name, s.full_name
    ", [$selectedYear . $selectedWeek]);
}

// Get summary statistics
$summaryStats = $db->fetchOne("
    SELECT 
        COUNT(DISTINCT student_id) as total_students,
        COUNT(*) as total_records,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick,
        SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission,
        SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
    FROM attendances
    WHERE " . ($reportType === 'monthly' ? "DATE_FORMAT(date, '%Y-%m') = ?" : "YEARWEEK(date, 1) = ?")
", [$reportType === 'monthly' ? $selectedMonth : $selectedYear . $selectedWeek]);

// Get all classes for filter
$classes = $db->fetchAll("SELECT * FROM classes ORDER BY name");
?>

<div class="sidebar">
    <div class="text-center mb-3">
        <?php if ($school['logo_path']): ?>
            <img src="<?= htmlspecialchars($school['logo_path']) ?>" alt="Logo" style="max-width: 80px; border-radius: 50%;">
        <?php endif; ?>
        <h6 class="mt-2"><?= htmlspecialchars($school['school_name'] ?? 'SISDM') ?></h6>
    </div>
    
    <ul class="sidebar-menu">
        <li class="sidebar-item"><a href="?page=dashboard" class="sidebar-link">📊 Beranda/Dashboard</a></li>
        <?php if (isAdmin()): ?>
        <li class="sidebar-item"><a href="?page=students" class="sidebar-link">👨‍🎓 Data Siswa</a></li>
        <?php endif; ?>
        <li class="sidebar-item"><a href="?page=attendance" class="sidebar-link">✅ Data Absensi</a></li>
        <li class="sidebar-item"><a href="?page=reports" class="sidebar-link active">📋 Rekap Absensi</a></li>
        <?php if (isAdmin()): ?>
        <li class="sidebar-item"><a href="?page=officers" class="sidebar-link">👮 Petugas Absensi</a></li>
        <li class="sidebar-item"><a href="?page=settings" class="sidebar-link">⚙️ Pengaturan</a></li>
        <?php endif; ?>
        <li class="sidebar-item mt-3"><a href="?page=logout" class="sidebar-link" style="color: var(--danger-color);">🚪 Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="mb-3">
        <h2>Rekap Absensi</h2>
        <p class="text-muted">Laporan absensi bulanan dan mingguan</p>
    </div>

    <!-- Filter Options -->
    <div class="card mb-2">
        <div class="d-flex gap-2 align-center" style="flex-wrap: wrap;">
            <form method="GET" class="d-flex gap-2 align-center">
                <input type="hidden" name="page" value="reports">
                
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="monthly" <?= $reportType === 'monthly' ? 'selected' : '' ?>>📅 Bulanan</option>
                    <option value="weekly" <?= $reportType === 'weekly' ? 'selected' : '' ?>>📆 Mingguan</option>
                </select>

                <?php if ($reportType === 'monthly'): ?>
                    <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($selectedMonth) ?>" onchange="this.form.submit()">
                <?php else: ?>
                    <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($selectedYear) ?>" min="2020" max="2100" style="width: 100px;" onchange="this.form.submit()">
                    <select name="week" class="form-control" onchange="this.form.submit()">
                        <?php for ($w = 1; $w <= 52; $w++): ?>
                            <option value="<?= $w ?>" <?= $selectedWeek == $w ? 'selected' : '' ?>>Minggu <?= $w ?></option>
                        <?php endfor; ?>
                    </select>
                <?php endif; ?>
            </form>

            <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Laporan</button>
            <button onclick="exportToCSV()" class="btn btn-success">📊 Export CSV</button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid mb-2">
        <div class="stat-card">
            <div class="stat-icon primary">👥</div>
            <div class="stat-info">
                <h3><?= $summaryStats['total_students'] ?? 0 ?></h3>
                <p>Total Siswa</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">✅</div>
            <div class="stat-info">
                <h3><?= $summaryStats['present'] ?? 0 ?></h3>
                <p>Hadir</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">⏰</div>
            <div class="stat-info">
                <h3><?= $summaryStats['late'] ?? 0 ?></h3>
                <p>Terlambat</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger">❌</div>
            <div class="stat-info">
                <h3><?= $summaryStats['alpha'] ?? 0 ?></h3>
                <p>Alfa</p>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card">
        <div class="card-header">
            <h4>
                📋 Laporan Absensi 
                <?php if ($reportType === 'monthly'): ?>
                    <?= date('F Y', strtotime($selectedMonth . '-01')) ?>
                <?php else: ?>
                    Minggu <?= $selectedWeek ?>, <?= $selectedYear ?>
                <?php endif; ?>
            </h4>
        </div>
        <div class="card-body">
            <table class="table" id="reportTable">
                <thead>
                    <tr>
                        <th>NIPD</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Total Hari</th>
                        <th>Hadir</th>
                        <th>Terlambat</th>
                        <th>Sakit</th>
                        <th>Izin</th>
                        <th>Alfa</th>
                        <th>Pulang Awal</th>
                        <th>% Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $data = $reportType === 'monthly' ? $monthData : $weekData;
                    foreach ($data as $row): 
                        $percentage = $row['total_days'] > 0 ? round(($row['present'] + $row['late']) / $row['total_days'] * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nipd']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['class_name'] ?? '-') ?></td>
                        <td><?= $row['total_days'] ?? 0 ?></td>
                        <td><span class="badge badge-success"><?= $row['present'] ?? 0 ?></span></td>
                        <td><span class="badge badge-warning"><?= $row['late'] ?? 0 ?></span></td>
                        <td><span class="badge badge-info"><?= $row['sick'] ?? 0 ?></span></td>
                        <td><span class="badge badge-secondary"><?= $row['permission'] ?? 0 ?></span></td>
                        <td><span class="badge badge-danger"><?= $row['alpha'] ?? 0 ?></span></td>
                        <td><span class="badge badge-warning"><?= $row['early_leave'] ?? 0 ?></span></td>
                        <td>
                            <strong style="color: <?= $percentage >= 75 ? 'var(--success-color)' : 'var(--danger-color)' ?>">
                                <?= $percentage ?>%
                            </strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    const table = document.getElementById('reportTable');
    let csv = [];
    
    // Headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push('"' + th.textContent.trim() + '"');
    });
    csv.push(headers.join(','));
    
    // Rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.trim() + '"');
        });
        csv.push(row.join(','));
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'laporan_absensi_<?= $reportType ?>_<?= date('Y-m-d') ?>.csv';
    a.click();
}
</script>

<style>
@media print {
    .sidebar, .btn, form { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>
