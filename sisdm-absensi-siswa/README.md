# SISDM Absensi Siswa

Sistem Informasi Manajemen Absensi Siswa Berbasis Web

## 📋 Fitur Utama

### Identitas Sekolah
- NPSN, Nama Sekolah, Alamat, Website, Telepon
- Logo Sekolah (max 500x500px, < 3MB)
- Background Image Support (JPG/PNG)
- Transparency & Blur Settings

### Panel Admin
- Dashboard dengan statistik real-time
- Data Absensi (Jam Datang, Jam Pulang, Telat, Pulang Awal)
- Data Siswa (NIPD, Nama, Kelas, Jurusan) - CRUD Lengkap
- Rekap Absensi (Bulanan & Mingguan) dengan Export CSV/PDF
- Pengaturan Program Absensi
- Pengaturan Petugas Harian (ganti petugas tiap hari)

### Panel Petugas Absensi
- Dashboard Petugas
- Input Jam Masuk & Jam Keluar
- Absensi Siswa Berhalangan (Sakit/Izin/Alfa)
- Izin Khusus (Telat/Keluar Lingkungan/Pulang Awal)

### Fitur UI/UX
- **Multi-Theme CSS**: Fluent UI (Default), Material UI, Glassmorphism, Cyberpunk
- **Light/Dark Mode**: White, Light Gray, Dark Gray, Black, Dark
- Customizable Dashboard
- Responsive Design
- Background Image Support dengan Transparency & Blur control

## 🛠️ Spesifikasi Teknis

- **PHP**: 8.1.10
- **Apache**: 2.4.54
- **MySQL**: 8.0.30
- **Hosting**: Laragon 6.0.0

## 📦 Instalasi Otomatis

### 1. Clone/Copy ke Folder Laragon
```bash
# Copy folder sisdm-absensi-siswa ke:
# C:\laragon\www\sisdm-absensi-siswa
```

### 2. Buat Database
```bash
# Buka phpMyAdmin atau MySQL CLI
# Import file database/sisdm_absensi.sql
```

### 3. Konfigurasi Database
Edit file `config/database.php` jika diperlukan:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sisdm_absensi');
```

### 4. Akses Aplikasi
```
http://localhost/sisdm-absensi-siswa
```

## 👤 Default Login Credentials

### Administrator
- Username: `admin`
- Password: `admin123`

### Petugas Absensi
- Username: `petugas`
- Password: `petugas123`

## 📁 Struktur Folder

```
sisdm-absensi-siswa/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet dengan multi-theme
│   ├── js/
│   │   └── main.js            # JavaScript untuk theme & interactions
│   └── images/                # Logo & background images
├── config/
│   └── database.php           # Database configuration
├── includes/
│   ├── header.php             # Admin header template
│   ├── header_petugas.php     # Petugas header template
│   └── footer.php             # Footer template
├── modules/
│   ├── auth/
│   │   ├── login.php          # Login page
│   │   └── logout.php         # Logout handler
│   ├── admin/
│   │   ├── dashboard.php      # Admin dashboard
│   │   ├── absensi.php        # Data absensi management
│   │   ├── siswa.php          # Data siswa management
│   │   ├── rekap.php          # Rekap absensi
│   │   ├── petugas.php        # Pengaturan petugas harian
│   │   └── pengaturan.php     # Pengaturan sistem
│   └── petugas/
│       ├── dashboard.php      # Petugas dashboard
│       ├── absensi_masuk.php  # Input jam masuk
│       ├── absensi_keluar.php # Input jam keluar
│       ├── berhalangan.php    # Siswa berhalangan
│       └── izin_khusus.php    # Izin khusus
├── database/
│   └── sisdm_absensi.sql      # Database schema & sample data
└── index.php                  # Homepage
```

## 🎨 Theme & Mode

### Available Themes
1. **Fluent UI** - Modern Microsoft-style design (Default)
2. **Material UI** - Google Material Design
3. **Glassmorphism** - Frosted glass effect
4. **Cyberpunk** - Futuristic neon style

### Available Modes
1. **White** - Clean white background
2. **Light Gray** - Soft gray background
3. **Dark Gray** - Medium dark background
4. **Black** - Pure black background
5. **Dark** - Standard dark mode

## 📊 Database Tables

- `sekolah` - Identitas sekolah & pengaturan tampilan
- `users` - User accounts (admin & petugas)
- `petugas_harian` - Jadwal petugas per hari
- `jurusan` - Daftar jurusan
- `kelas` - Daftar kelas
- `siswa` - Data siswa
- `absensi` - Record absensi harian
- `izin_keluar` - Izin khusus siswa

## 🔐 Security Features

- Password hashing dengan bcrypt
- Session-based authentication
- Role-based access control (RBAC)
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)

## 📝 License

© 2024 SISDM Absensi Siswa - All Rights Reserved

---

**Developed for Laragon 6.0.0 Environment**
