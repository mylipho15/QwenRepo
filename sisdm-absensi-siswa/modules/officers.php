<?php
/**
 * Officers Management Module - Pengaturan Petugas Absensi
 */

$db = Database::getInstance();
$message = '';
$messageType = '';
$today = date('Y-m-d');

// Get all officers
$officers = $db->fetchAll("SELECT * FROM users WHERE role = 'officer' AND is_active = 1");

// Get today's active officer
$activeOfficer = getActiveOfficer();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'set_officer') {
        $user_id = $_POST['user_id'] ?? 0;
        $date = $_POST['date'] ?? $today;
        $notes = $_POST['notes'] ?? '';
        
        // Deactivate all officers for this date
        $db->query("UPDATE attendance_officers SET status = 'inactive' WHERE date = ?", [$date]);
        
        // Set new active officer
        $db->insert('attendance_officers', [
            'user_id' => $user_id,
            'date' => $date,
            'status' => 'active',
            'notes' => $notes
        ]);
        
        $message = 'Petugas absensi berhasil diatur!';
        $messageType = 'success';
    }
    
    if ($action === 'add_officer') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        if (!empty($username) && !empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                $db->insert('users', [
                    'username' => $username,
                    'password' => $hashedPassword,
                    'full_name' => $full_name,
                    'role' => 'officer',
                    'email' => $email,
                    'phone' => $phone,
                    'is_active' => 1
                ]);
                $message = 'Petugas baru berhasil ditambahkan!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error: Username sudah digunakan!';
                $messageType = 'danger';
            }
        }
    }
    
    if ($action === 'delete_officer') {
        $id = $_POST['id'] ?? 0;
        $db->update('users', ['is_active' => 0], 'id = ?', [$id]);
        $message = 'Petugas berhasil dihapus!';
        $messageType = 'success';
    }
}

// Get officer schedule for this week
$weekSchedule = $db->fetchAll("
    SELECT o.*, u.full_name
    FROM attendance_officers o
    JOIN users u ON o.user_id = u.id
    WHERE o.date BETWEEN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                     AND DATE_ADD(CURDATE(), INTERVAL (6-WEEKDAY(CURDATE())) DAY)
    ORDER BY o.date ASC
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
        <li class="sidebar-item"><a href="?page=dashboard" class="sidebar-link">📊 Beranda/Dashboard</a></li>
        <li class="sidebar-item"><a href="?page=students" class="sidebar-link">👨‍🎓 Data Siswa</a></li>
        <li class="sidebar-item"><a href="?page=attendance" class="sidebar-link">✅ Data Absensi</a></li>
        <li class="sidebar-item"><a href="?page=reports" class="sidebar-link">📋 Rekap Absensi</a></li>
        <li class="sidebar-item"><a href="?page=officers" class="sidebar-link active">👮 Petugas Absensi</a></li>
        <li class="sidebar-item"><a href="?page=settings" class="sidebar-link">⚙️ Pengaturan</a></li>
        <li class="sidebar-item mt-3"><a href="?page=logout" class="sidebar-link" style="color: var(--danger-color);">🚪 Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="mb-3">
        <h2>Petugas Absensi</h2>
        <p class="text-muted">Kelola petugas absensi harian</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <!-- Today's Officer -->
        <div class="card">
            <div class="card-header">
                <h4>📅 Petugas Hari Ini</h4>
            </div>
            <div class="text-center">
                <?php if ($activeOfficer): ?>
                    <div style="font-size: 3rem;">👮</div>
                    <h3><?= htmlspecialchars($activeOfficer['full_name']) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($activeOfficer['notes'] ?? '') ?></p>
                <?php else: ?>
                    <p class="text-muted">Belum ada petugas yang ditunjuk untuk hari ini</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Set Today's Officer -->
        <div class="card">
            <div class="card-header">
                <h4>⚙️ Atur Petugas Hari Ini</h4>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="set_officer">
                <input type="hidden" name="date" value="<?= $today ?>">
                
                <div class="form-group">
                    <label class="form-label">Pilih Petugas</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Pilih Petugas --</option>
                        <?php foreach ($officers as $o): ?>
                            <option value="<?= $o['id'] ?>" <?= ($activeOfficer && $activeOfficer['user_id'] == $o['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan (opsional)"><?= htmlspecialchars($activeOfficer['notes'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">💾 Simpan</button>
            </form>
        </div>

        <!-- Add New Officer -->
        <div class="card">
            <div class="card-header">
                <h4>➕ Tambah Petugas Baru</h4>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_officer">
                
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                
                <button type="submit" class="btn btn-success btn-block">➕ Tambah</button>
            </form>
        </div>

        <!-- Officers List -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h4>👥 Daftar Petugas Aktif</h4>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($officers as $o): ?>
                    <tr>
                        <td><?= htmlspecialchars($o['username']) ?></td>
                        <td><?= htmlspecialchars($o['full_name']) ?></td>
                        <td><?= htmlspecialchars($o['email'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($o['phone'] ?? '-') ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Nonaktifkan petugas ini?')">
                                <input type="hidden" name="action" value="delete_officer">
                                <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️ Nonaktifkan</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Weekly Schedule -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h4>📆 Jadwal Petugas Minggu Ini</h4>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Petugas</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    foreach ($weekSchedule as $s): 
                        $dayName = $days[date('N', strtotime($s['date'])) - 1];
                        $isToday = $s['date'] === $today;
                    ?>
                    <tr style="<?= $isToday ? 'background: var(--bg-tertiary); font-weight: bold;' : '' ?>">
                        <td><?= date('d/m/Y', strtotime($s['date'])) ?></td>
                        <td><?= $dayName ?> <?= $isToday ? '(Hari Ini)' : '' ?></td>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['notes'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($weekSchedule)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada jadwal petugas minggu ini</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
