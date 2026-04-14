<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Sistem Absensi Siswa</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            padding: 2rem;
        }
        .hero-content {
            max-width: 800px;
        }
        .hero-title {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }
        .feature-card {
            background: var(--bg-primary);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body data-theme="fluent" data-mode="white">
    <?php
    require_once 'config/config.php';
    $school = getSchoolInfo();
    $settings = getSettings();
    ?>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            <?php if ($school && $school['logo_path']): ?>
                <img src="<?= BASE_URL . $school['logo_path'] ?>" alt="Logo" class="navbar-logo">
            <?php endif; ?>
            <span><?= htmlspecialchars($school['nama_sekolah'] ?? APP_NAME) ?></span>
        </div>
        <ul class="navbar-nav">
            <li><a href="#home">Beranda</a></li>
            <li><a href="#features">Fitur</a></li>
            <li><a href="#about">Tentang</a></li>
            <li>
                <a href="<?= BASE_URL ?>modules/auth/login.php" class="btn btn-primary">Login</a>
            </li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-content">
            <div style="font-size: 5rem; margin-bottom: 1rem;">🏫</div>
            <h1 class="hero-title">Selamat Datang di<br><?= htmlspecialchars($school['nama_sekolah'] ?? APP_NAME) ?></h1>
            <p class="hero-subtitle">Sistem Informasi Absensi Siswa Digital</p>
            
            <div class="hero-buttons">
                <a href="<?= BASE_URL ?>modules/auth/login.php?role=admin" class="btn btn-primary btn-lg">
                    🔐 Login Administrator
                </a>
                <a href="<?= BASE_URL ?>modules/auth/login.php?role=petugas" class="btn btn-secondary btn-lg">
                    📋 Login Petugas
                </a>
            </div>

            <div class="feature-grid" id="features">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Dashboard Real-time</h3>
                    <p>Monitoring absensi siswa secara real-time dengan statistik lengkap</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎨</div>
                    <h3>Multi Theme</h3>
                    <p>4 tema berbeda: Fluent UI, Material UI, Glassmorphism, Cyberpunk</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌓</div>
                    <h3>Light/Dark Mode</h3>
                    <p>4 mode warna: White, Light Gray, Dark Gray, Black</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Responsive Design</h3>
                    <p>Tampilan optimal di semua perangkat desktop, tablet, dan mobile</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Multi User System</h3>
                    <p>Panel terpisah untuk Administrator dan Petugas Absensi</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Rekap Otomatis</h3>
                    <p>Laporan absensi bulanan dan mingguan otomatis</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" style="padding: 4rem 2rem; background: var(--bg-secondary);">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="text-align: center; margin-bottom: 2rem; color: var(--accent-color);">Informasi Sekolah</h2>
            
            <?php if ($school): ?>
                <div class="dashboard-grid">
                    <div class="card">
                        <h3>🏫 Identitas Sekolah</h3>
                        <p><strong>NPSN:</strong> <?= htmlspecialchars($school['npsn'] ?? '-') ?></p>
                        <p><strong>Nama:</strong> <?= htmlspecialchars($school['nama_sekolah'] ?? '-') ?></p>
                        <p><strong>Alamat:</strong> <?= htmlspecialchars($school['alamat'] ?? '-') ?></p>
                        <p><strong>Website:</strong> <?= htmlspecialchars($school['website'] ?? '-') ?></p>
                        <p><strong>Telepon:</strong> <?= htmlspecialchars($school['telepon'] ?? '-') ?></p>
                    </div>
                    
                    <div class="card">
                        <h3>⚙️ Pengaturan Sistem</h3>
                        <p><strong>Jam Masuk:</strong> <?= date('H:i', strtotime($settings['jam_masuk'] ?? '07:00')) ?></p>
                        <p><strong>Jam Pulang:</strong> <?= date('H:i', strtotime($settings['jam_pulang'] ?? '15:00')) ?></p>
                        <p><strong>Toleransi Telat:</strong> <?= $settings['toleransi_telat'] ?? 15 ?> menit</p>
                        <p><strong>Tema Aktif:</strong> <?= ucfirst($settings['theme'] ?? 'fluent') ?></p>
                        <p><strong>Mode Aktif:</strong> <?= ucfirst(str_replace('-', ' ', $settings['mode'] ?? 'white')) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: var(--bg-tertiary); padding: 2rem; text-align: center; margin-top: 2rem;">
        <p style="color: var(--text-secondary);">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($school['nama_sekolah'] ?? APP_NAME) ?>. 
            All rights reserved. | Version <?= APP_VERSION ?>
        </p>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">
            Theme: 
            <select id="theme-select" style="padding: 0.4rem; border-radius: var(--radius);">
                <option value="fluent">Fluent UI</option>
                <option value="material">Material UI</option>
                <option value="glassmorphism">Glassmorphism</option>
                <option value="cyberpunk">Cyberpunk</option>
            </select>
            | Mode:
            <select id="mode-select" style="padding: 0.4rem; border-radius: var(--radius);">
                <option value="white">White</option>
                <option value="light-gray">Light Gray</option>
                <option value="dark-gray">Dark Gray</option>
                <option value="black">Black</option>
            </select>
        </p>
    </footer>

    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
