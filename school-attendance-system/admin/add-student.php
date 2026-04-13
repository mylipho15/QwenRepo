<?php
include '../includes/admin-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nipd = sanitize($_POST['nipd']);
    $name = sanitize($_POST['name']);
    $class = sanitize($_POST['class']);
    $major = sanitize($_POST['major']);

    // Check if NIPD already exists
    $existing = $conn->query("SELECT id FROM students WHERE nipd = '$nipd'");
    
    if ($existing->num_rows > 0) {
        $message = 'NIPD sudah terdaftar!';
        $message_type = 'danger';
    } else {
        $conn->query("INSERT INTO students (nipd, name, class, major) 
                      VALUES ('$nipd', '$name', '$class', '$major')");
        $message = 'Data siswa berhasil ditambahkan!';
        $message_type = 'success';
    }
}

// Get all majors for reference
$majors = $conn->query("SELECT DISTINCT major FROM students ORDER BY major");

$conn->close();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-plus"></i> Tambah Data Siswa</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label" for="nipd">
                        <i class="fas fa-id-card"></i> NIPD
                    </label>
                    <input type="text" name="nipd" id="nipd" class="form-control" 
                           placeholder="Masukkan NIPD" required 
                           pattern="[0-9]+" title="Hanya angka yang diperbolehkan">
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">
                        <i class="fas fa-user"></i> Nama Lengkap
                    </label>
                    <input type="text" name="name" id="name" class="form-control" 
                           placeholder="Masukkan nama lengkap" required>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label" for="class">
                        <i class="fas fa-chalkboard"></i> Kelas
                    </label>
                    <input type="text" name="class" id="class" class="form-control" 
                           placeholder="Contoh: X RPL 1" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="major">
                        <i class="fas fa-graduation-cap"></i> Jurusan
                    </label>
                    <input type="text" name="major" id="major" class="form-control" 
                           placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                    <small class="text-muted">Jurusan populer: RPL, TKJ, AKL, MM, dll.</small>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="students.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle"></i> Panduan</h3>
    </div>
    <div class="card-body">
        <ul>
            <li><strong>NIPD:</strong> Nomor Induk Peserta Didik, harus unik dan hanya berisi angka.</li>
            <li><strong>Nama:</strong> Nama lengkap siswa sesuai dokumen resmi.</li>
            <li><strong>Kelas:</strong> Format bebas, contoh: X RPL 1, XI TKJ 2, XII AKL 3.</li>
            <li><strong>Jurusan:</strong> Program keahlian yang diambil siswa.</li>
        </ul>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
