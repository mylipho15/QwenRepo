<?php
// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
checkAuth('admin');


$db = Database::getInstance()->getConnection();
$school = getSchoolInfo();

// Get filter parameters
$periode = $_GET['periode'] ?? 'mingguan';
$tanggal_start = $_GET['tanggal_start'] ?? date('Y-m-d', strtotime('monday this week'));
$tanggal_end = $_GET['tanggal_end'] ?? date('Y-m-d', strtotime('sunday this week'));

if ($periode === 'bulanan') {
    $tanggal_start = date('Y-m-01');
    $tanggal_end = date('Y-m-t');

// Build query
$sql = "SELECT s.nipd, s.nama, k.nama_kelas, j.nama_jurusan,
               COUNT(a.id) as total_hari,
               SUM(CASE WHEN a.keterangan = 'hadir' THEN 1 ELSE 0 END) as hadir,
               SUM(CASE WHEN a.keterangan = 'sakit' THEN 1 ELSE 0 END) as sakit,
               SUM(CASE WHEN a.keterangan = 'izin' THEN 1 ELSE 0 END) as izin,
               SUM(CASE WHEN a.keterangan = 'alfa' THEN 1 ELSE 0 END) as alfa,
               SUM(CASE WHEN a.status_datang = 'telat' OR a.jam_datang > '07:30:00' THEN 1 ELSE 0 END) as telat,
               SUM(CASE WHEN a.status_pulang = 'pulang_awal' OR (a.jam_pulang < '15:30:00' AND a.keterangan = 'hadir') THEN 1 ELSE 0 END) as pulang_awal
        FROM siswa s
        LEFT JOIN kelas k ON s.kelas_id = k.id
        LEFT JOIN jurusan j ON k.jurusan_id = j.id
        LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal BETWEEN ? AND ?
        GROUP BY s.id
        ORDER BY s.nipd";

$stmt = $db->prepare($sql);
$stmt->execute([$tanggal_start, $tanggal_end]);
$rekap_list = $stmt->fetchAll();

$page_title = 'Rekap Absensi - Admin';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">📋 Rekap Absensi</div>
    
    <form method="GET" class="d-flex gap-1 mb-2">
        <select name="periode" class="form-control" onchange="this.form.submit()">
            <option value="mingguan" <?php echo $periode === 'mingguan' ? 'selected' : ''; ?>>Mingguan</option>
            <option value="bulanan" <?php echo $periode === 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
        </select>
        
        <?php if ($periode === 'mingguan'): ?>
        <input type="date" name="tanggal_start" class="form-control" value="<?php echo $tanggal_start; ?>">
        <span>s/d</span>
        <input type="date" name="tanggal_end" class="form-control" value="<?php echo $tanggal_end; ?>">
        <?php else: ?>
        <input type="month" name="bulan" class="form-control" value="<?php echo date('Y-m'); ?>" 
               onchange="window.location.href='?periode=bulanan&bulan='+this.value">
        <?php endif; ?>
        
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    
    <p><strong>Periode:</strong> <?php echo date('d/m/Y', strtotime($tanggal_start)); ?> - <?php echo date('d/m/Y', strtotime($tanggal_end)); ?></p>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIPD</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th>Total Hari</th>
                    <th>Hadir</th>
                    <th>Sakit</th>
                    <th>Izin</th>
                    <th>Alfa</th>
                    <th>Telat</th>
                    <th>Pulang Awal</th>
                    <th>% Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($rekap_list as $r): 
                    $persentase = $r['total_hari'] > 0 ? round(($r['hadir'] / $r['total_hari']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($r['nipd']); ?></td>
                    <td><?php echo htmlspecialchars($r['nama']); ?></td>
                    <td><?php echo htmlspecialchars($r['nama_kelas'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($r['nama_jurusan'] ?? '-'); ?></td>
                    <td><?php echo $r['total_hari'] ?? 0; ?></td>
                    <td style="color: var(--success);"><?php echo $r['hadir'] ?? 0; ?></td>
                    <td style="color: var(--warning);"><?php echo $r['sakit'] ?? 0; ?></td>
                    <td style="color: var(--info);"><?php echo $r['izin'] ?? 0; ?></td>
                    <td style="color: var(--danger);"><?php echo $r['alfa'] ?? 0; ?></td>
                    <td style="color: var(--warning);"><?php echo $r['telat'] ?? 0; ?></td>
                    <td style="color: var(--danger);"><?php echo $r['pulang_awal'] ?? 0; ?></td>
                    <td>
                        <strong style="color: <?php echo $persentase >= 75 ? 'var(--success)' : 'var(--danger)'; ?>">
                            <?php echo $persentase; ?>%
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-2">
    <div class="card-header">Export Data</div>
    <div class="d-flex gap-1">
        <button class="btn btn-success" onclick="window.print()">📄 Print / PDF</button>
        <button class="btn btn-success" onclick="exportToCSV()">📊 Export CSV</button>
    </div>
</div>

<script>
function exportToCSV() {
    let csv = [];
    const headers = ['No', 'NIPD', 'Nama', 'Kelas', 'Jurusan', 'Total Hari', 'Hadir', 'Sakit', 'Izin', 'Alfa', 'Telat', 'Pulang Awal', 'Kehadiran %'];
    csv.push(headers.join(','));
    
    document.querySelectorAll('.data-table tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = Array.from(cells).map(cell => `"${cell.textContent.trim()}"`).join(',');
        csv.push(rowData);
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'rekap_absensi_<?php echo date('Y-m-d'); ?>.csv';
    link.click();
</script>

<?php include '../../includes/footer.php'; ?>
