# 📚 SISTEM ABSENSI SISWA BERBASIS WEB

Sistem manajemen absensi siswa lengkap dengan panel Admin dan Petugas, mendukung multi-theme, light/dark mode, dan rotasi petugas harian.

## ✨ Fitur Utama

### 🔐 Sistem Login Multi-Role
- **Administrator**: Akses penuh ke semua fitur sistem
- **Petugas Absensi**: Akses untuk input absensi harian dan izin siswa

### 🏫 Panel Administrator
- **Dashboard**: Statistik real-time (siswa, kehadiran, keterlambatan, dll)
- **Data Absensi**: CRUD lengkap (Add/Edit/Update/Remove)
- **Data Siswa**: Manajemen data siswa dengan NIPD, Nama, Kelas, Jurusan
- **Rekap Absensi**: Laporan bulanan dan mingguan dengan export
- **Pengaturan Lengkap**:
  - Identitas Sekolah (NPSN, Nama, Alamat, Website, Telepon)
  - Jam Operasional Absensi
  - Pengaturan Petugas (Jadwal Rotasi Harian)
  - Tampilan & Tema (Light/Dark Mode, Multi-Theme CSS)
  - Upload Logo Sekolah (Max 500x500px, <3MB)
  - Custom Background Image (JPG/PNG)
  - Kontrol Transparansi & Blur Background
  - Ganti Password Admin & Petugas

### 👨‍💼 Panel Petugas Absensi
- **Dashboard**: Ringkasan aktivitas harian
- **Check In/Out**: Input absensi masuk dan pulang siswa
- **Absensi Berhalangan**: Input sakit/izin/alfa
- **Izin Khusus**: Telat, keluar lingkungan sekolah, pulang awal

### 🎨 Customization
- **4 Mode Warna**: White, Light Gray, Dark Gray, Black
- **4 Tema CSS**: Fluent UI (Default), Material UI, Glassmorphism, Cyberpunk
- **Background Custom**: Upload gambar sendiri dengan kontrol opacity & blur
- **Logo Sekolah**: Upload logo dengan validasi otomatis

### 📊 Database & Automation
- Auto-create database & tables saat pertama kali akses
- SQL schema lengkap dengan dummy data
- Foreign key constraints untuk integritas data
- Jadwal petugas harian dengan rotasi otomatis

---

## 📁 Struktur File

```
school-attendance-system/
├── admin/                      # Panel Administrator
│   ├── dashboard.php          # Dashboard utama
│   ├── attendance.php         # Manajemen absensi (CRUD)
│   ├── students.php           # Manajemen siswa (CRUD)
│   ├── add-student.php        # Form tambah siswa
│   ├── add-attendance.php     # Form tambah absensi
│   ├── reports.php            # Rekap & laporan
│   ├── settings.php           # Pengaturan lengkap
│   └── manage-petugas.php     # Kelola jadwal petugas
│
├── petugas/                    # Panel Petugas
│   ├── dashboard.php          # Dashboard petugas
│   ├── check-in-out.php       # Check in/out siswa
│   ├── permission.php         # Izin sakit/alfa/izin
│   └── special-permission.php # Izin telat/keluar/pulang awal
│
├── config/                     # Konfigurasi
│   └── database.php           # Koneksi DB & helper functions
│
├── includes/                   # Komponen UI
│   ├── admin-header.php       # Header admin
│   ├── admin-footer.php       # Footer admin
│   ├── petugas-header.php     # Header petugas
│   └── petugas-footer.php     # Footer petugas
│
├── css/                        # Stylesheet
│   └── styles.css             # Multi-theme CSS (879 baris)
│
├── js/                         # JavaScript
│   └── main.js                # Interaksi UI (377 baris)
│
├── sql/                        # Database Schema
│   ├── install.sql            # Schema lengkap + dummy data
│   ├── schema.sql             # Schema only
│   └── update_schema.sql      # Update script
│
├── libs/                       # Library Helper
│   ├── DatabaseHelper.php     # DB abstraction
│   └── ThemeManager.php       # Theme management
│
├── scripts/                    # Automation Scripts
│   ├── setup.php              # Setup wizard
│   └── install-db.php         # Database installer
│
├── uploads/                    # User Uploads
│   ├── logos/                 # Logo sekolah
│   └── backgrounds/           # Background images
│
├── index.php                   # Landing page & login
├── login.php                   # Proses login
├── logout.php                  # Proses logout
├── composer.json               # PHP dependencies
├── README.md                   # Dokumentasi ini
└── INSTALL.md                  # Panduan instalasi detail
```

**Total Files**: 32 files  
**Lines of Code**: ~6,000+ baris

---

## 🚀 Instalasi

### Metode 1: Otomatis (Recommended)

1. **Copy ke Web Server**
   ```bash
   cp -r school-attendance-system /var/www/html/
   ```

2. **Akses Browser**
   ```
   http://localhost/school-attendance-system/
   ```
   Database akan dibuat otomatis!

3. **Login**
   - Admin: `admin` / `admin123`
   - Petugas: `petugas` / `petugas123`

### Metode 2: Manual dengan Composer

```bash
cd school-attendance-system
composer install
php scripts/setup.php
# Akses via browser
```

### Metode 3: Import SQL Manual

```bash
# 1. Buat database
mysql -u root -p -e "CREATE DATABASE school_attendance"

# 2. Import schema
mysql -u root -p school_attendance < sql/install.sql

# 3. Akses aplikasi
http://localhost/school-attendance-system/
```

### Persyaratan Server

- **PHP**: 7.4 atau lebih tinggi
- **MySQL/MariaDB**: 5.7 atau lebih tinggi
- **Web Server**: Apache/Nginx
- **Extensions**: mysqli, gd, fileinfo
- **Mod Rewrite**: Enabled (Apache)

---

## ⚙️ Konfigurasi Database

Edit file `config/database.php` jika perlu:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_attendance');
```

---

## 🎯 Cara Menggunakan Fitur Baru

### 1. Upload Logo Sekolah
1. Login sebagai Admin
2. Masuk ke **Settings** → **Tampilan & Background**
3. Upload file JPG/PNG (max 500x500px, <3MB)
4. Klik **Upload Logo**

### 2. Custom Background
1. Login sebagai Admin
2. Masuk ke **Settings** → **Tampilan & Background**
3. Upload gambar background (JPG/PNG, max 5MB)
4. Atur slider **Transparansi** (0.1 - 1.0)
5. Atur slider **Blur** (0 - 20px)
6. Simpan perubahan

### 3. Atur Jadwal Petugas
1. Login sebagai Admin
2. Masuk ke **Settings** → **Pengaturan Petugas**
3. Pilih tanggal dari kalender
4. Pilih petugas yang bertugas
5. Pilih shift (Pagi/Siang/Full Day)
6. Klik **Tambah Jadwal**

### 4. Ganti Password
1. Login sebagai Admin
2. Masuk ke **Settings** → **Keamanan**
3. Pilih role (Admin/Petugas)
4. Masukkan password baru (min 6 karakter)
5. Konfirmasi password
6. Klik **Ganti Password**

### 5. Rotasi Petugas Harian
- Sistem otomatis menampilkan petugas yang bertugas hari ini di dashboard
- Jadwal dapat diatur jauh-jauh hari
- Petugas dapat melihat jadwal mereka di dashboard

---

## 🗄️ Struktur Database

### Tables

| Table | Description |
|-------|-------------|
| `settings` | Konfigurasi sekolah & tampilan |
| `users` | User accounts (admin & officer) |
| `students` | Data siswa |
| `officer_schedules` | Jadwal tugas petugas harian |
| `attendance` | Record absensi siswa |

### Default Users

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| petugas | petugas123 | Petugas Absensi |

### Dummy Data

SQL schema sudah include contoh data:
- 10 siswa sample
- 3 record absensi sample
- Jadwal petugas minggu ini
- Settings default

---

## 🎨 Tema & Customization

### Available Themes
1. **Fluent UI** (Default) - Modern Microsoft-style
2. **Material UI** - Google Material Design
3. **Glassmorphism** - Frosted glass effect
4. **Cyberpunk** - Futuristic neon style

### Color Modes
- **White**: Clean white background
- **Light Gray**: Soft gray tones
- **Dark Gray**: Dark gray theme
- **Black**: Pure black OLED-friendly

### CSS Variables
```css
:root {
    --theme-mode: light;
    --theme-style: fluent;
    --bg-opacity: 0.90;
    --bg-blur: 5px;
    --primary-color: #0078d4;
}
```

---

## 🔒 Keamanan

- Password hashing dengan `password_hash()` (bcrypt)
- Prepared statements untuk mencegah SQL injection
- XSS protection dengan `htmlspecialchars()`
- Session-based authentication
- File upload validation (type & size)
- Role-based access control (RBAC)

---

## 📝 Changelog

### Version 2.0 (Latest)
✅ Penambahan pengaturan identitas sekolah  
✅ Upload logo sekolah (500x500px, <3MB)  
✅ Custom background image dengan opacity & blur  
✅ Pengaturan jadwal petugas harian  
✅ Fitur ganti password admin & petugas  
✅ Panel login dengan opsi admin/petugas  
✅ Perbaikan query SQL & struktur database  
✅ Dummy data lengkap untuk testing  
✅ Auto database initialization  

### Version 1.0
- Initial release
- Basic CRUD operations
- Single theme support

---

## 🤝 Support & Contribution

Jika menemukan bug atau ingin menambahkan fitur:
1. Fork repository ini
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

---

## 📄 License

Sistem Absensi Siswa - Open Source for Educational Purpose

---

## 📞 Contact

Developed for Indonesian School Attendance Management System

**Happy Coding! 🎉**
