<?php
/**
 * Petugas Header Include
 */

$school = getSchoolInfo();
$flash = getFlashMessage();
?>

<nav class="navbar">
    <div class="navbar-brand">
        <?php if ($school && $school['logo_path']): ?>
            <img src="<?= BASE_URL . $school['logo_path'] ?>" alt="Logo" class="navbar-logo">
        <?php endif; ?>
        <span><?= htmlspecialchars($school['nama_sekolah'] ?? APP_NAME) ?></span>
    </div>
    
    <ul class="navbar-nav">
        <li>
            <span style="color: var(--text-secondary); margin-right: 1rem;">
                👤 <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?>
            </span>
        </li>
        <li>
            <select id="theme-select" class="form-control" style="padding: 0.4rem;">
                <option value="fluent" <?= ($settings['theme'] ?? 'fluent') === 'fluent' ? 'selected' : '' ?>>Fluent UI</option>
                <option value="material" <?= ($settings['theme'] ?? 'fluent') === 'material' ? 'selected' : '' ?>>Material UI</option>
                <option value="glassmorphism" <?= ($settings['theme'] ?? 'fluent') === 'glassmorphism' ? 'selected' : '' ?>>Glassmorphism</option>
                <option value="cyberpunk" <?= ($settings['theme'] ?? 'fluent') === 'cyberpunk' ? 'selected' : '' ?>>Cyberpunk</option>
            </select>
        </li>
        <li>
            <select id="mode-select" class="form-control" style="padding: 0.4rem;">
                <option value="white" <?= ($settings['mode'] ?? 'white') === 'white' ? 'selected' : '' ?>>White</option>
                <option value="light-gray" <?= ($settings['mode'] ?? 'white') === 'light-gray' ? 'selected' : '' ?>>Light Gray</option>
                <option value="dark-gray" <?= ($settings['mode'] ?? 'white') === 'dark-gray' ? 'selected' : '' ?>>Dark Gray</option>
                <option value="black" <?= ($settings['mode'] ?? 'white') === 'black' ? 'selected' : '' ?>>Black</option>
            </select>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
        </li>
    </ul>
</nav>

<?php if ($flash): ?>
    <div style="position: fixed; top: 80px; right: 20px; z-index: 3000; min-width: 300px;">
        <div class="alert alert-<?= $flash['type'] ?> alert-auto-dismiss">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    </div>
<?php endif; ?>
