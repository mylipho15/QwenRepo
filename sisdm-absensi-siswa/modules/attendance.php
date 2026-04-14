<?php
/**
 * Attendance Management Module
 * For Petugas Absensi - Check In/Out, Sick/Permission/Alpha, Special Permissions
 */

$db = Database::getInstance();
$message = '';
$messageType = '';
$today = date('Y-m-d');
$currentTime = date('H:i:s');

// Get active officer
$activeOfficer = getActiveOfficer();
if (!$activeOfficer && !isAdmin()) {
    $message = 'Anda bukan petugas absensi hari ini!';
    $messageType = 'warning';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($activeOfficer || isAdmin())) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'check_in' || $action === 'check_out') {
        $student_id = $_POST['student_id'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        
        // Check if already recorded today
        $existing = $db->fetchOne("SELECT * FROM attendances WHERE student_id = ? AND date = ?", [$student_id, $today]);
        
        if ($action === 'check_in') {
            $lateMinutes = 0;
            $settingsLateThreshold = $settings['late_threshold'] ?? '07:30';
            if (strtotime($currentTime) > strtotime($settingsLateThreshold)) {
                $lateMinutes = floor((strtotime($currentTime) - strtotime($settingsLateThreshold)) / 60);
            }
            
            $status = $lateMinutes > 0 ? 'late' : 'present';
            
            if ($existing) {
                $db->update('attendances', [
                    'check_in' => $currentTime,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'officer_id' => $_SESSION['user_id'],
                    'notes' => $notes
                ], 'id = ?', [$existing['id']]);
            } else {
                $db->insert('attendances', [
                    'student_id' => $student_id,
                    'date' => $today,
                    'check_in' => $currentTime,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'officer_id' => $_SESSION['user_id'],
                    'notes' => $notes
                ]);
            }
            $message = 'Absen masuk berhasil dicatat!';
            $messageType = 'success';
        }
        
        if ($action === 'check_out') {
            if ($existing) {
                $db->update('attendances', [
                    'check_out' => $currentTime,
                    'officer_id' => $_SESSION['user_id']
                ], 'id = ?', [$existing['id']]);
                $message = 'Absen pulang berhasil dicatat!';
                $messageType = 'success';
            } else {
                $message = 'Siswa belum absen masuk hari ini!';
                $messageType = 'warning';
            }
        }
    }
    
    if ($action === 'mark_status') {
        $student_id = $_POST['student_id'] ?? 0;
        $status = $_POST['status'] ?? 'alpha';
        $notes = $_POST['notes'] ?? '';
        
        $existing = $db->fetchOne("SELECT * FROM attendances WHERE student_id = ? AND date = ?", [$student_id, $today]);
        
        if ($existing) {
            $db->update('attendances', [
                'status' => $status,
                'notes' => $notes,
                'officer_id' => $_SESSION['user_id']
            ], 'id = ?', [$existing['id']]);
        } else {
            $db->insert('attendances', [
                'student_id' => $student_id,
                'date' => $today,
                'status' => $status,
                'notes' => $notes,
                'officer_id' => $_SESSION['user_id']
            ]);
        }
        $message = 'Status absensi berhasil diperbarui!';
        $messageType = 'success';
    }
    
    if ($action === 'special_permission') {
        $student_id = $_POST['student_id'] ?? 0;
        $permission_type = $_POST['permission_type'] ?? 'late';
        $reason = $_POST['reason'] ?? '';
        $start_time = $_POST['start_time'] ?? null;
        $end_time = $_POST['end_time'] ?? null;
        
        $db->insert('special_permissions', [
            'student_id' => $student_id,
            'permission_type' => $permission_type,
            'date' => $today,
            'reason' => $reason,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'approved_by' => $_SESSION['user_id'],
            'status' => 'approved'
        ]);
        $message = 'Izin khusus berhasil dicatat!';
        $messageType = 'success';
    }
}

// Get students who haven't checked in today
$studentsNotIn = $db->fetchAll("
    SELECT s.*, c.name as class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE s.is_active = 1
    AND s.id NOT IN (SELECT student_id FROM attendances WHERE date = ? AND check_in IS NOT NULL)
    ORDER BY c.name, s.full_name
", [$today]);

// Get today's attendances
$todayAttendances = $db->fetchAll("
    SELECT a.*, s.full_name, s.nipd, c.name as class_name
    FROM attendances a
    JOIN students s ON a.student_id = s.id
    JOIN classes c ON s.class_id = c.id
    WHERE a.date = ?
    ORDER BY a.check_in DESC
", [$today]);

// Get all students for dropdown
$allStudents = $db->fetchAll("
    SELECT s.*, c.name as class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE s.is_active = 1
    ORDER BY c.name, s.full_name
");
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
            <a href="?page=dashboard" class="sidebar-link">📊 Beranda/Dashboard</a>
        </li>
        <?php if (isAdmin()): ?>
        <li class="sidebar-item">
            <a href="?page=students" class="sidebar-link">👨‍🎓 Data Siswa</a>
        </li>
        <?php endif; ?>
        <li class="sidebar-item">
            <a href="?page=attendance" class="sidebar-link active">✅ Data Absensi</a>
        </li>
        <?php if (isAdmin()): ?>
        <li class="sidebar-item">
            <a href="?page=reports" class="sidebar-link">📋 Rekap Absensi</a>
        </li>
        <li class="sidebar-item">
            <a href="?page=officers" class="sidebar-link">👮 Petugas Absensi</a>
        </li>
        <li class="sidebar-item">
            <a href="?page=settings" class="sidebar-link">⚙️ Pengaturan</a>
        </li>
        <?php endif; ?>
        <li class="sidebar-item mt-3">
            <a href="?page=logout" class="sidebar-link" style="color: var(--danger-color);">🚪 Logout</a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="d-flex justify-between align-center mb-3">
        <div>
            <h2>Absensi Siswa</h2>
            <p class="text-muted">
                <?= date('l, d F Y') ?> | 
                Petugas: <?= htmlspecialchars($activeOfficer['full_name'] ?? $_SESSION['full_name']) ?>
            </p>
        </div>
        <div class="text-right">
            <span class="badge badge-info">Jam: <?= date('H:i') ?></span>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$activeOfficer && !isAdmin()): ?>
        <div class="alert alert-warning">
            ⚠️ Anda bukan petugas absensi yang ditunjuk untuk hari ini. Hubungi administrator untuk perubahan petugas.
        </div>
    <?php else: ?>
        <!-- Quick Actions -->
        <div class="stats-grid mb-2">
            <div class="card">
                <h4>📥 Absen Masuk</h4>
                <form method="POST" class="mt-2">
                    <input type="hidden" name="action" value="check_in">
                    <select name="student_id" class="form-control mb-2" required>
                        <option value="">Pilih Siswa</option>
                        <?php foreach ($allStudents as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['class_name']) ?> - <?= htmlspecialchars($s['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="notes" class="form-control mb-2" placeholder="Keterangan (opsional)"></textarea>
                    <button type="submit" class="btn btn-success btn-block">✅ Catat Kehadiran</button>
                </form>
            </div>

            <div class="card">
                <h4>📤 Absen Pulang</h4>
                <form method="POST" class="mt-2">
                    <input type="hidden" name="action" value="check_out">
                    <select name="student_id" class="form-control mb-2" required>
                        <option value="">Pilih Siswa</option>
                        <?php foreach ($todayAttendances as $a): ?>
                            <option value="<?= $a['student_id'] ?>"><?= htmlspecialchars($a['class_name']) ?> - <?= htmlspecialchars($a['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-block">🏠 Catat Pulang</button>
                </form>
            </div>

            <div class="card">
                <h4>⚠️ Status Khusus</h4>
                <form method="POST" class="mt-2">
                    <input type="hidden" name="action" value="mark_status">
                    <select name="student_id" class="form-control mb-2" required>
                        <option value="">Pilih Siswa</option>
                        <?php foreach ($studentsNotIn as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['class_name']) ?> - <?= htmlspecialchars($s['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="form-control mb-2" required>
                        <option value="sick">🤒 Sakit</option>
                        <option value="permission">📝 Izin</option>
                        <option value="alpha">❌ Alfa (Tanpa Keterangan)</option>
                    </select>
                    <textarea name="notes" class="form-control mb-2" placeholder="Keterangan"></textarea>
                    <button type="submit" class="btn btn-warning btn-block">Simpan Status</button>
                </form>
            </div>

            <div class="card">
                <h4>🎫 Izin Khusus</h4>
                <form method="POST" class="mt-2">
                    <input type="hidden" name="action" value="special_permission">
                    <select name="student_id" class="form-control mb-2" required>
                        <option value="">Pilih Siswa</option>
                        <?php foreach ($allStudents as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['class_name']) ?> - <?= htmlspecialchars($s['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="permission_type" class="form-control mb-2" required>
                        <option value="late">⏰ Telat</option>
                        <option value="leave_school">🚶 Keluar Lingkungan Sekolah</option>
                        <option value="early_leave">🏃 Pulang Awal</option>
                    </select>
                    <input type="time" name="start_time" class="form-control mb-2">
                    <input type="time" name="end_time" class="form-control mb-2">
                    <textarea name="reason" class="form-control mb-2" placeholder="Alasan" required></textarea>
                    <button type="submit" class="btn btn-info btn-block">Catat Izin</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Today's Attendance Table -->
    <div class="card">
        <div class="card-header">
            <h4>📋 Daftar Absensi Hari Ini</h4>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>NIPD</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($todayAttendances)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data absensi hari ini</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($todayAttendances as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['nipd']) ?></td>
                            <td><?= htmlspecialchars($a['full_name']) ?></td>
                            <td><?= htmlspecialchars($a['class_name']) ?></td>
                            <td><?= $a['check_in'] ? date('H:i', strtotime($a['check_in'])) : '-' ?></td>
                            <td><?= $a['check_out'] ? date('H:i', strtotime($a['check_out'])) : '-' ?></td>
                            <td>
                                <?php
                                $badges = [
                                    'present' => ['success', 'Hadir'],
                                    'late' => ['warning', 'Telat'],
                                    'sick' => ['info', 'Sakit'],
                                    'permission' => ['secondary', 'Izin'],
                                    'alpha' => ['danger', 'Alfa'],
                                    'early_leave' => ['warning', 'Pulang Awal']
                                ];
                                $b = $badges[$a['status']] ?? ['secondary', $a['status']];
                                ?>
                                <span class="badge badge-<?= $b[0] ?>"><?= $b[1] ?></span>
                                <?php if ($a['late_minutes'] > 0): ?>
                                    <small>(+<?= $a['late_minutes'] ?> menit)</small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($a['notes'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
