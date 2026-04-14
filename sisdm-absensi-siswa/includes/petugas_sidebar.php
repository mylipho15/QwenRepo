<?php
/**
 * Petugas Sidebar Include
 */

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<aside class="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="<?= BASE_URL ?>modules/petugas/dashboard.php" class="<?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <i>🏠</i> Beranda
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/petugas/absensi_masuk.php" class="<?= $current_page === 'absensi_masuk' ? 'active' : '' ?>">
                <i>🌅</i> Absen Masuk
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/petugas/absensi_pulang.php" class="<?= $current_page === 'absensi_pulang' ? 'active' : '' ?>">
                <i>🌇</i> Absen Pulang
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/petugas/berhalangan.php" class="<?= $current_page === 'berhalangan' ? 'active' : '' ?>">
                <i>😷</i> Berhalangan
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/petugas/izin_keluar.php" class="<?= $current_page === 'izin_keluar' ? 'active' : '' ?>">
                <i>🚶</i> Izin Keluar
            </a>
        </li>
        <li style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
            <a href="<?= BASE_URL ?>index.php" target="_blank">
                <i>🌐</i> Lihat Website
            </a>
        </li>
    </ul>
</aside>
