<?php
/**
 * Dashboard Module
 */

$user = getCurrentUser();
$today = date('Y-m-d');

// Get attendance statistics for today
$todayStats = $db->fetchAll("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick,
        SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission,
        SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha,
        SUM(CASE WHEN status = 'early_leave' THEN 1 ELSE 0 END) as early_leave
    FROM attendances 
    WHERE date = ?
", [$today]);

// Get total students
$totalStudents = $db->fetchOne("SELECT COUNT(*) as count FROM students WHERE is_active = 1")['count'];

// Get active officer today
$activeOfficer = getActiveOfficer();

// Recent attendances
$recentAttendances = $db->fetchAll("
    SELECT a.*, s.full_name, s.nipd, c.name as class_name
    FROM attendances a
    JOIN students s ON a.student_id = s.id
    JOIN classes c ON s.class_id = c.id
    WHERE a.date = ?
    ORDER BY a.check_in DESC
    LIMIT 10
", [$today]);
?>

<div class="sidebar">
    <div class="text-center mb-3">
        <?php if ($school['logo_path']): ?>
            <img src="<?= htmlspecialchars($school['logo_path']) ?>" alt="Logo" style="max-width: 80px; border-radius: 50%;">
        <?php endif; ?>
        <h6 class="mt-2"><?= htmlspecialchars($school['school_name'] ?? 'SISDM') ?></h6>
    </div>
    
    <ul class="sidebar-menu">
        <li class="sidebar-item">
            <a href="?page=dashboard" class="sidebar-link <?= $page === 'dashboard' ? 'active' : '' ?>">
                📊 Beranda/Dashboard
            </a>
        </li>
        
        <?php if (isAdmin()): ?>
        <li class="sidebar-item">
            <a href="?page=students" class="sidebar-link <?= $page === 'students' ? 'active' : '' ?>">
                👨‍🎓 Data Siswa
            </a>
        </li>
        <?php endif; ?>
        
        <li class="sidebar-item">
            <a href="?page=attendance" class="sidebar-link <?= $page === 'attendance' ? 'active' : '' ?>">
                ✅ Data Absensi
            </a>
        </li>
        
        <?php if (isAdmin()): ?>
        <li class="sidebar-item">
            <a href="?page=reports" class="sidebar-link <?= $page === 'reports' ? 'active' : '' ?>">
                📋 Rekap Absensi
            </a>
        </li>
        
        <li class="sidebar-item">
            <a href="?page=officers" class="sidebar-link <?= $page === 'officers' ? 'active' : '' ?>">
                👮 Petugas Absensi
            </a>
        </li>
        
        <li class="sidebar-item">
            <a href="?page=settings" class="sidebar-link <?= $page === 'settings' ? 'active' : '' ?>">
                ⚙️ Pengaturan
            </a>
        </li>
        <?php endif; ?>
        
        <li class="sidebar-item mt-3">
            <a href="?page=logout" class="sidebar-link" style="color: var(--danger-color);">
                🚪 Logout
            </a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="d-flex justify-between align-center mb-3">
        <div>
            <h2>Beranda</h2>
            <p class="text-muted">Selamat datang, <?= htmlspecialchars($user['full_name']) ?>!</p>
        </div>
        <div class="text-right">
            <p><strong><?= date('l, d F Y') ?></strong></p>
            <p class="text-muted">Petugas: <?= htmlspecialchars($activeOfficer['full_name'] ?? '-') ?></p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">👨‍🎓</div>
            <div class="stat-info">
                <h3><?= $totalStudents ?></h3>
                <p>Total Siswa</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">✅</div>
            <div class="stat-info">
                <h3><?= $todayStats[0]['present'] ?? 0 ?></h3>
                <p>Hadir Hari Ini</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">⏰</div>
            <div class="stat-info">
                <h3><?= $todayStats[0]['late'] ?? 0 ?></h3>
                <p>Terlambat</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon danger">❌</div>
            <div class="stat-info">
                <h3><?= $todayStats[0]['alpha'] ?? 0 ?></h3>
                <p>Tanpa Keterangan</p>
            </div>
        </div>
    </div>

    <!-- Today's Summary -->
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <h4 class="card-title">Absensi Hari Ini - <?= date('d/m/Y') ?></h4>
            <span class="badge badge-info">Total: <?= $todayStats[0]['total'] ?? 0 ?></span>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>NIPD</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentAttendances)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data absensi hari ini</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recentAttendances as $att): ?>
                        <tr>
                            <td><?= htmlspecialchars($att['nipd']) ?></td>
                            <td><?= htmlspecialchars($att['full_name']) ?></td>
                            <td><?= htmlspecialchars($att['class_name']) ?></td>
                            <td><?= $att['check_in'] ? date('H:i', strtotime($att['check_in'])) : '-' ?></td>
                            <td><?= $att['check_out'] ? date('H:i', strtotime($att['check_out'])) : '-' ?></td>
                            <td>
                                <?php
                                $statusBadges = [
                                    'present' => 'success',
                                    'late' => 'warning',
                                    'sick' => 'info',
                                    'permission' => 'secondary',
                                    'alpha' => 'danger',
                                    'early_leave' => 'warning'
                                ];
                                $statusLabels = [
                                    'present' => 'Hadir',
                                    'late' => 'Telat',
                                    'sick' => 'Sakit',
                                    'permission' => 'Izin',
                                    'alpha' => 'Alfa',
                                    'early_leave' => 'Pulang Awal'
                                ];
                                ?>
                                <span class="badge badge-<?= $statusBadges[$att['status']] ?>">
                                    <?= $statusLabels[$att['status']] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($att['notes'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
