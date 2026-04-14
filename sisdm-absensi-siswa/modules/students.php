<?php
/**
 * Students Management Module
 * Full CRUD Operations for Student Data
 */

$db = Database::getInstance();
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $nipd = $_POST['nipd'] ?? '';
        $nisn = $_POST['nisn'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        $class_id = $_POST['class_id'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $birth_place = $_POST['birth_place'] ?? '';
        $birth_date = $_POST['birth_date'] ?? '';
        $address = $_POST['address'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $parent_name = $_POST['parent_name'] ?? '';
        $parent_phone = $_POST['parent_phone'] ?? '';
        
        // Handle photo upload
        $photo_path = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 3 * 1024 * 1024; // 3MB
            
            if (in_array($_FILES['photo']['type'], $allowed) && $_FILES['photo']['size'] <= $maxSize) {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photo_path = 'assets/images/students/' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $photo_path);
            }
        }
        
        $data = [
            'nipd' => $nipd,
            'nisn' => $nisn,
            'full_name' => $full_name,
            'class_id' => $class_id,
            'gender' => $gender,
            'birth_place' => $birth_place,
            'birth_date' => $birth_date ?: null,
            'address' => $address,
            'phone' => $phone,
            'parent_name' => $parent_name,
            'parent_phone' => $parent_phone,
            'is_active' => 1
        ];
        
        if (!empty($photo_path)) {
            $data['photo_path'] = $photo_path;
        }
        
        try {
            if ($action === 'add') {
                $db->insert('students', $data);
                $message = 'Siswa berhasil ditambahkan!';
                $messageType = 'success';
            } else {
                $id = $_POST['id'] ?? 0;
                $db->update('students', $data, 'id = ?', [$id]);
                $message = 'Data siswa berhasil diperbarui!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $db->delete('students', 'id = ?', [$id]);
            $message = 'Siswa berhasil dihapus!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Get all students with class info
$students = $db->fetchAll("
    SELECT s.*, c.name as class_name, m.name as major_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN majors m ON c.major_id = m.id
    ORDER BY s.full_name ASC
");

// Get all classes for dropdown
$classes = $db->fetchAll("SELECT * FROM classes ORDER BY name ASC");

// Edit mode
$editMode = false;
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $editData = $db->fetchOne("SELECT * FROM students WHERE id = ?", [$editId]);
    if ($editData) {
        $editMode = true;
    }
}
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
        <li class="sidebar-item">
            <a href="?page=students" class="sidebar-link active">👨‍🎓 Data Siswa</a>
        </li>
        <li class="sidebar-item">
            <a href="?page=attendance" class="sidebar-link">✅ Data Absensi</a>
        </li>
        <li class="sidebar-item">
            <a href="?page=reports" class="sidebar-link">📋 Rekap Absensi</a>
        </li>
        <li class="sidebar-item">
            <a href="?page=officers" class="sidebar-link">👮 Petugas Absensi</a>
        </li>
        <li class="sidebar-item">
            <a href="?page=settings" class="sidebar-link">⚙️ Pengaturan</a>
        </li>
        <li class="sidebar-item mt-3">
            <a href="?page=logout" class="sidebar-link" style="color: var(--danger-color);">🚪 Logout</a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="d-flex justify-between align-center mb-3">
        <div>
            <h2>Data Siswa</h2>
            <p class="text-muted">Manajemen data siswa - Tambah, Edit, Hapus</p>
        </div>
        <button class="btn btn-primary" data-modal-target="studentModal" <?= $editMode ? 'style="display:none"' : '' ?>>
            ➕ Tambah Siswa
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Search -->
    <div class="card mb-2">
        <div class="form-group mb-0">
            <input type="text" class="form-control" data-table-search="studentsTable" placeholder="🔍 Cari siswa (NIPD, Nama, Kelas)...">
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-body">
            <table class="table" id="studentsTable">
                <thead>
                    <tr>
                        <th>NIPD</th>
                        <th>NISN</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>L/P</th>
                        <th>No. Telepon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['nipd']) ?></td>
                        <td><?= htmlspecialchars($s['nisn'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['class_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($s['major_name'] ?? '-') ?></td>
                        <td><?= $s['gender'] === 'male' ? 'L' : 'P' ?></td>
                        <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
                        <td>
                            <a href="?page=students&edit=<?= $s['id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️ Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Student Modal -->
<div id="studentModal" class="modal <?= $editMode ? 'active' : '' ?>">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h4><?= $editMode ? 'Edit Siswa' : 'Tambah Siswa Baru' ?></h4>
            <button class="modal-close">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $editMode ? 'edit' : 'add' ?>">
            <?php if ($editMode): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>
            
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">NIPD *</label>
                        <input type="text" name="nipd" class="form-control" required value="<?= htmlspecialchars($editData['nipd'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">NISN</label>
                        <input type="text" name="nisn" class="form-control" value="<?= htmlspecialchars($editData['nisn'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($editData['full_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kelas *</label>
                        <select name="class_id" class="form-control" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($editData['class_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin *</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Pilih</option>
                            <option value="male" <?= ($editData['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="female" <?= ($editData['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="birth_place" class="form-control" value="<?= htmlspecialchars($editData['birth_place'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="birth_date" class="form-control" value="<?= htmlspecialchars($editData['birth_date'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($editData['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">No. Telepon Siswa</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editData['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">No. Telepon Orang Tua</label>
                        <input type="text" name="parent_phone" class="form-control" value="<?= htmlspecialchars($editData['parent_phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama Orang Tua</label>
                        <input type="text" name="parent_name" class="form-control" value="<?= htmlspecialchars($editData['parent_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Foto Siswa (Max 3MB, JPG/PNG)</label>
                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                        <?php if ($editData && $editData['photo_path']): ?>
                            <small>Foto saat ini: <?= basename($editData['photo_path']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>
