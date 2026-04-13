<?php
include '../includes/admin-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM students WHERE id = $id");
    $message = 'Data siswa berhasil dihapus!';
    $message_type = 'success';
}

// Get filter parameters
$filter_class = isset($_GET['class']) ? $_GET['class'] : '';
$filter_major = isset($_GET['major']) ? $_GET['major'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$where = [];
if ($filter_class) {
    $where[] = "class = '" . $conn->real_escape_string($filter_class) . "'";
}
if ($filter_major) {
    $where[] = "major = '" . $conn->real_escape_string($filter_major) . "'";
}
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where[] = "(name LIKE '%$search_esc%' OR nipd LIKE '%$search_esc%')";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get all classes and majors for filter
$classes = $conn->query("SELECT DISTINCT class FROM students ORDER BY class");
$majors = $conn->query("SELECT DISTINCT major FROM students ORDER BY major");

// Get students data
$students_data = $conn->query("SELECT * FROM students $where_clause ORDER BY class, name");

// Count total
$total_students = $conn->query("SELECT COUNT(*) as count FROM students $where_clause")->fetch_assoc()['count'];

$conn->close();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Filter Data Siswa</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="d-flex gap-2" style="flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Cari</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Nama atau NIPD" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Kelas</label>
                <select name="class" class="form-control">
                    <option value="">Semua Kelas</option>
                    <?php 
                    $classes->data_seek(0);
                    while ($class = $classes->fetch_assoc()): 
                    ?>
                        <option value="<?php echo htmlspecialchars($class['class']); ?>" 
                                <?php echo $filter_class === $class['class'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Jurusan</label>
                <select name="major" class="form-control">
                    <option value="">Semua Jurusan</option>
                    <?php 
                    $majors->data_seek(0);
                    while ($major = $majors->fetch_assoc()): 
                    ?>
                        <option value="<?php echo htmlspecialchars($major['major']); ?>" 
                                <?php echo $filter_major === $major['major'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($major['major']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="students.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Data Siswa (Total: <?php echo $total_students; ?>)</h3>
        <div class="d-flex gap-2">
            <a href="add-student.php" class="btn btn-sm btn-success">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <button onclick="window.print()" class="btn btn-sm btn-secondary">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if ($students_data->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
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
                        <?php 
                        $no = 1;
                        while ($row = $students_data->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nipd']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['class']); ?></td>
                                <td><?php echo htmlspecialchars($row['major']); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="edit-student.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus siswa ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">Tidak ada data siswa.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
