<?php
include '../includes/admin-header.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM attendance WHERE id = $id");
    $message = 'Data absensi berhasil dihapus!';
    $message_type = 'success';
}

// Get filter parameters
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filter_class = isset($_GET['class']) ? $_GET['class'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where = ["a.date = '$filter_date'"];
if ($filter_class) {
    $where[] = "s.class = '" . $conn->real_escape_string($filter_class) . "'";
}
if ($filter_status) {
    if ($filter_status === 'late') {
        $where[] = "a.is_late = 1";
    } elseif ($filter_status === 'early') {
        $where[] = "a.left_early = 1";
    } else {
        $where[] = "a.status = '" . $conn->real_escape_string($filter_status) . "'";
    }
}

$where_clause = implode(' AND ', $where);

// Get all classes for filter
$classes = $conn->query("SELECT DISTINCT class FROM students ORDER BY class");

// Get attendance data
$attendance_data = $conn->query("
    SELECT a.*, s.name, s.nipd, s.class, s.major, u.full_name as recorded_by_name
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    LEFT JOIN users u ON a.recorded_by = u.id
    WHERE $where_clause
    ORDER BY a.check_in DESC
");

$conn->close();
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Filter Data Absensi</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="d-flex gap-2" style="flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Tanggal</label>
                <input type="date" name="date" class="form-control" value="<?php echo $filter_date; ?>">
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
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="present" <?php echo $filter_status === 'present' ? 'selected' : ''; ?>>Hadir</option>
                    <option value="sick" <?php echo $filter_status === 'sick' ? 'selected' : ''; ?>>Sakit</option>
                    <option value="permission" <?php echo $filter_status === 'permission' ? 'selected' : ''; ?>>Izin</option>
                    <option value="alpha" <?php echo $filter_status === 'alpha' ? 'selected' : ''; ?>>Alfa</option>
                    <option value="late" <?php echo $filter_status === 'late' ? 'selected' : ''; ?>>Telat</option>
                    <option value="early" <?php echo $filter_status === 'early' ? 'selected' : ''; ?>>Pulang Awal</option>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="attendance.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Data Absensi - <?php echo date('d/m/Y', strtotime($filter_date)); ?></h3>
        <div class="d-flex gap-2">
            <a href="add-attendance.php" class="btn btn-sm btn-success">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <button onclick="window.print()" class="btn btn-sm btn-secondary">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if ($attendance_data->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIPD</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = $attendance_data->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nipd']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['class']); ?></td>
                                <td><?php echo htmlspecialchars($row['major']); ?></td>
                                <td>
                                    <?php 
                                    if ($row['check_in']) {
                                        echo date('H:i', strtotime($row['check_in']));
                                        if ($row['is_late']) {
                                            echo ' <span class="badge badge-warning">Telat</span>';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($row['check_out']) {
                                        echo date('H:i', strtotime($row['check_out']));
                                        if ($row['left_early']) {
                                            echo ' <span class="badge badge-warning">Awal</span>';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    switch($row['status']) {
                                        case 'present':
                                            echo '<span class="badge badge-success">Hadir</span>';
                                            break;
                                        case 'sick':
                                            echo '<span class="badge badge-warning">Sakit</span>';
                                            break;
                                        case 'permission':
                                            echo '<span class="badge badge-info">Izin</span>';
                                            break;
                                        case 'alpha':
                                            echo '<span class="badge badge-danger">Alfa</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['notes'] ?: '-'); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="edit-attendance.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary btn-edit" data-id="<?php echo $row['id']; ?>" data-type="attendance">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['id']; ?>&date=<?php echo $filter_date; ?>" 
                                           class="btn btn-sm btn-danger btn-delete" 
                                           onclick="return confirm('Yakin ingin menghapus data ini?')"
                                           data-id="<?php echo $row['id']; ?>" data-type="attendance">
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
            <p class="text-muted text-center">Tidak ada data absensi untuk tanggal ini.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
