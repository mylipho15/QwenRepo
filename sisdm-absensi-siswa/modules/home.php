<?php
/**
 * Home Page Module
 * Selamat Datang & Login Options
 */
?>

<div class="login-container">
    <div class="login-box fade-in">
        <div class="login-header">
            <?php if ($school['logo_path']): ?>
                <img src="<?= htmlspecialchars($school['logo_path']) ?>" alt="Logo Sekolah" class="login-logo">
            <?php endif; ?>
            <h1><?= htmlspecialchars($school['school_name'] ?? 'SISDM Absensi') ?></h1>
            <p class="text-muted">Sistem Absensi Siswa Berbasis Web</p>
        </div>

        <div class="card mb-2">
            <h3 class="card-title text-center">Selamat Datang</h3>
            <p class="text-center text-muted">
                Sistem informasi absensi siswa untuk <?= htmlspecialchars($school['school_name'] ?? 'sekolah Anda') ?>
            </p>
            
            <?php if ($school['address']): ?>
                <div class="mt-2 text-center">
                    <small class="text-muted">
                        📍 <?= htmlspecialchars($school['address']) ?><br>
                        <?php if ($school['phone']): ?>
                            📞 <?= htmlspecialchars($school['phone']) ?><br>
                        <?php endif; ?>
                        <?php if ($school['website']): ?>
                            🌐 <?= htmlspecialchars($school['website']) ?>
                        <?php endif; ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2" style="flex-direction: column;">
            <a href="?page=login&role=admin" class="btn btn-primary btn-block">
                👤 Login Administrator
            </a>
            <a href="?page=login&role=officer" class="btn btn-secondary btn-block">
                📝 Login Petugas Absensi
            </a>
        </div>

        <div class="mt-3 text-center">
            <button type="button" class="btn btn-outline" data-modal-target="themeModal">
                🎨 Pengaturan Tampilan
            </button>
        </div>

        <div class="mt-2 text-center">
            <small class="text-muted">
                NPSN: <?= htmlspecialchars($school['npsn'] ?? '-') ?>
            </small>
        </div>
    </div>
</div>

<!-- Theme Settings Modal -->
<div id="themeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Pengaturan Tampilan</h4>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Tema</label>
                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                    <button class="btn btn-outline" data-theme-option="fluent-ui">Fluent UI</button>
                    <button class="btn btn-outline" data-theme-option="material-ui">Material UI</button>
                    <button class="btn btn-outline" data-theme-option="glassmorphism">Glassmorphism</button>
                    <button class="btn btn-outline" data-theme-option="cyberpunk">Cyberpunk</button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mode Warna</label>
                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                    <button class="btn btn-outline" data-color-mode-option="light">⚪ Putih</button>
                    <button class="btn btn-outline" data-color-mode-option="light-gray">🌫️ Abu Terang</button>
                    <button class="btn btn-outline" data-color-mode-option="dark-gray">🌙 Abu Gelap</button>
                    <button class="btn btn-outline" data-color-mode-option="dark">⚫ Hitam</button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Transparansi: <span id="transparencyValue">95</span>%</label>
                <input type="range" id="transparencySlider" min="50" max="100" value="95" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Blur Effect: <span id="blurValue">10</span>px</label>
                <input type="range" id="blurSlider" min="0" max="20" value="10" class="form-control">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close">Tutup</button>
        </div>
    </div>
</div>

<script>
// Update slider values display
document.getElementById('transparencySlider')?.addEventListener('input', function() {
    document.getElementById('transparencyValue').textContent = this.value;
});

document.getElementById('blurSlider')?.addEventListener('input', function() {
    document.getElementById('blurValue').textContent = this.value;
});
</script>
