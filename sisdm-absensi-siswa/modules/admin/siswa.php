<?php
require_once '../../config/database.php';
checkAuth('admin');

$db = Database::getInstance()->getConnection();
$school = getSchoolInfo();
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_siswa':
                $nipd = trim($_POST['nipd']);
                $nama = trim($_POST['nama']);
                $kelas_id = $_POST['kelas_id'];
                
                try {
                    $stmt = $db->prepare("INSERT INTO siswa (nipd, nama, kelas_id) VALUES (?, ?, ?)");
                    $stmt->execute([$nipd, $nama, $kelas_id]);
                    $success = 'Siswa berhasil ditambahkan!';
                } catch (PDOException $e) {
                    $error = 'NIPD sudah terdaftar!';
                }
                break;
                
            case 'update_siswa':
                $id = $_POST['id'];
                $nipd = trim($_POST['nipd']);
                $nama = trim($_POST['nama']);
                $kelas_id = $_POST['kelas_id'];
                
                $stmt = $db->prepare("UPDATE siswa SET nipd=?, nama=?, kelas_id=? WHERE id=?");
                $stmt->execute([$nipd, $nama, $kelas_id, $id]);
                $success = 'Data siswa berhasil diupdate!';
                break;
                
            case 'delete_siswa':
                $id = $_POST['id'];
                $stmt = $db->prepare("DELETE FROM siswa WHERE id=?");
                $stmt->execute([$id]);
                $success = 'Siswa berhasil dihapus!';
                break;
        }
    }
}

// Get all students with class info
$stmt = $db->query("SELECT s.*, k.nama_kelas, j.nama_jurusan 
                    FROM siswa s 
                    LEFT JOIN kelas k ON s.kelas_id = k.id 
                    LEFT JOIN jurusan j ON k.jurusan_id = j.id 
                    ORDER BY s.nipd");
$siswa_list = $stmt->fetchAll();

// Get all classes
$kelas_stmt = $db->query("SELECT k.*, j.nama_jurusan FROM kelas k LEFT JOIN jurusan j ON k.jurusan_id = j.id");
$kelas_list = $kelas_stmt->fetchAll();

$page_title = 'Data Siswa - Admin';
include '../../includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between align-center">
            <span>👨‍🎓 Data Siswa</span>
            <button class="btn btn-primary" onclick="ModalManager.open('modal-add-siswa')">
                + Tambah Siswa
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <input type="text" id="siswa-table-search" class="form-control mb-2" placeholder="Cari siswa...">
        <table class="data-table" id="siswa-table" data-table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIPD</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($siswa_list as $s): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($s['nipd']); ?></td>
                    <td><?php echo htmlspecialchars($s['nama']); ?></td>
                    <td><?php echo htmlspecialchars($s['nama_kelas'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($s['nama_jurusan'] ?? '-'); ?></td>
                    <td>
                        <button class="btn btn-warning" style="padding: 0.4rem 0.8rem;" 
                                onclick="editSiswa(<?php echo htmlspecialchars(json_encode($s)); ?>)">
                            Edit
                        </button>
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('Yakin ingin menghapus?')">
                            <input type="hidden" name="action" value="delete_siswa">
                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add/Edit Siswa -->
<div id="modal-add-siswa" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h3 id="modal-title">Tambah Siswa</h3>
        <form method="POST" id="form-siswa">
            <input type="hidden" name="action" id="form-action" value="add_siswa">
            <input type="hidden" name="id" id="siswa-id">
            
            <div class="form-group">
                <label class="form-label">NIPD</label>
                <input type="text" class="form-control" name="nipd" id="siswa-nipd" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" name="nama" id="siswa-nama" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Kelas</label>
                <select class="form-control" name="kelas_id" id="siswa-kelas" required>
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($kelas_list as $k): ?>
                    <option value="<?php echo $k['id']; ?>">
                        <?php echo htmlspecialchars($k['nama_kelas'] . ' - ' . ($k['nama_jurusan'] ?? 'Umum')); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="d-flex gap-1 justify-between mt-2">
                <button type="button" class="btn btn-secondary" onclick="ModalManager.close('modal-add-siswa')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSiswa(data) {
    document.getElementById('modal-title').textContent = 'Edit Siswa';
    document.getElementById('form-action').value = 'update_siswa';
    document.getElementById('siswa-id').value = data.id;
    document.getElementById('siswa-nipd').value = data.nipd;
    document.getElementById('siswa-nama').value = data.nama;
    document.getElementById('siswa-kelas').value = data.kelas_id;
    ModalManager.open('modal-add-siswa');
}

// Reset form when closing modal
document.getElementById('modal-add-siswa').addEventListener('click', function(e) {
    if (e.target === this) {
        document.getElementById('form-siswa').reset();
        document.getElementById('modal-title').textContent = 'Tambah Siswa';
        document.getElementById('form-action').value = 'add_siswa';
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
