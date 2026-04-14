<?php
/**
 * Admin Sidebar Include
 */

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<aside class="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="<?= BASE_URL ?>modules/admin/dashboard.php" class="<?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <i>🏠</i> Beranda
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/admin/absensi.php" class="<?= $current_page === 'absensi' ? 'active' : '' ?>">
                <i>📋</i> Data Absensi
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/admin/siswa.php" class="<?= $current_page === 'siswa' ? 'active' : '' ?>">
                <i>👨‍🎓</i> Data Siswa
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/admin/rekap.php" class="<?= $current_page === 'rekap' ? 'active' : '' ?>">
                <i>📊</i> Rekap Absensi
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/admin/petugas.php" class="<?= $current_page === 'petugas' ? 'active' : '' ?>">
                <i>👤</i> Petugas Harian
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/admin/sekolah.php" class="<?= $current_page === 'sekolah' ? 'active' : '' ?>">
                <i>🏫</i> Identitas Sekolah
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/admin/pengaturan.php" class="<?= $current_page === 'pengaturan' ? 'active' : '' ?>">
                <i>⚙️</i> Pengaturan Program
            </a>
        </li>
        <li style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
            <a href="<?= BASE_URL ?>index.php" target="_blank">
                <i>🌐</i> Lihat Website
            </a>
        </li>
    </ul>
</aside>
