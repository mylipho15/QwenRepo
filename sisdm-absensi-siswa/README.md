# SISDM Absensi Siswa

Sistem Absensi Siswa Berbasis Web untuk Sekolah

## 📋 Fitur Utama

### Identitas Sekolah
- NPSN
- Nama Sekolah
- Alamat
- Website
- Telepon
- Logo Sekolah (max 500x500px, < 3MB)

### Panel Admin
- 📊 Beranda/Dashboard dengan statistik real-time
- 👨‍🎓 Data Siswa (CRUD lengkap: Add/Edit/Update/Remove)
- ✅ Data Absensi (Jam Datang, Jam Pulang, Telat, Pulang Awal)
- 📋 Rekap Absensi (Bulanan & Mingguan)
- ⚙️ Pengaturan Program Absensi
- 👮 Pengaturan Petugas Absensi (ganti petugas harian)

### Panel Petugas Absensi
- 📊 Beranda/Dashboard
- 📥 Absensi Jam Masuk
- 📤 Absensi Jam Keluar
- ⚠️ Absensi Siswa Berhalangan (Sakit / Izin / Alfa)
- 🎫 Absensi Izin Khusus (Telat / Keluar Lingkungan / Pulang Awal)

### Panel Index/Home
- Selamat Datang dengan informasi sekolah
- Login (Administrator & Petugas)

### Fitur Tampilan
- 🎨 **Light/Dark Mode**: White, Light Gray, Dark Gray, Black
- 🎭 **Multi-Theme CSS**:
  - Fluent UI (Default)
  - Material UI
  - Glassmorphism
  - Cyberpunk
- 🖼️ **Background Image Support** (JPG/PNG)
- 🔍 **Transparency Control** (50-100%)
- 💫 **Blur Effect** (0-20px)
- 📱 **Responsive Design**

## 🛠️ Teknologi

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **No Framework Dependencies** - Pure PHP!

### Libraries (Built-in)
- `Auth.php` - Authentication & Session Management
- `Database.php` - PDO Database Connection Class
- `Config.php` - Configuration Management
- `Validator.php` - Input Validation Utilities

## 🔒 Fitur Keamanan Baru

### Session Management
- **Single Session Per User**: Setiap user hanya bisa login di satu tempat
- **Force Logout**: Admin dapat memaksa logout semua user atau user tertentu
- **Session Token Validation**: Token disimpan di database dan divalidasi setiap request
- **Auto Logout on Login**: Login baru otomatis logout sesi lama
- **Secure Session Cookies**: HttpOnly, SameSite Strict

## 📦 Instalasi Otomatis

```bash
# Clone atau download repository
cd sisdm-absensi-siswa

# Jalankan instalasi otomatis (requires root/sudo)
sudo bash install.sh
```

## 🔧 Instalasi Manual

### 1. Persyaratan Server
- PHP 7.4 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Web Server (Apache/Nginx)
- Extension: php-mysql, php-gd, php-mbstring

### 2. Setup Database

```sql
-- Buat database
CREATE DATABASE sisdm_absensi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema dan sample data
mysql -u username -p sisdm_absensi < sql/database.sql
```

### 3. Konfigurasi Database

Edit file `config/database.php`:

```php
return [
    'host' => 'localhost',
    'dbname' => 'sisdm_absensi',
    'username' => 'your_db_username',
    'password' => 'your_db_password',
    'charset' => 'utf8mb4',
];
```

### 4. Set Permissions

```bash
chmod -R 755 .
chmod -R 777 assets/images
mkdir -p assets/images/students
```

### 5. Akses Aplikasi

Buka browser dan akses: `http://localhost/sisdm-absensi-siswa/`

## 🔐 Login Default

| Role | Username | Password |
|------|----------|----------|
| Administrator | admin | admin123 |
| Petugas | petugas1 | officer123 |
| Petugas | petugas2 | officer123 |

## 📁 Struktur Folder

```
sisdm-absensi-siswa/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet dengan multi-theme
│   ├── js/
│   │   └── main.js            # JavaScript untuk UI interactions
│   └── images/
│       └── students/          # Upload folder untuk foto siswa
├── config/
│   └── database.php           # Konfigurasi database
├── includes/
│   ├── Database.php           # Database connection class
│   └── auth.php               # Authentication helpers
├── modules/
│   ├── home.php               # Home page
│   ├── login.php              # Login page
│   ├── dashboard.php          # Dashboard
│   ├── students.php           # Student management
│   ├── attendance.php         # Attendance management
│   ├── reports.php            # Reports (monthly/weekly)
│   ├── officers.php           # Officer management
│   ├── settings.php           # System settings
│   └── 404.php                # 404 page
├── sql/
│   └── database.sql           # Single-file SQL dengan sample data
├── index.php                  # Main entry point
└── install.sh                 # Automated installation script
```

## 📊 Database Schema

### Tabel Utama:
- `school_identity` - Identitas sekolah
- `users` - User accounts (admin & officers)
- `attendance_officers` - Jadwal petugas harian
- `majors` - Jurusan
- `classes` - Kelas
- `students` - Data siswa
- `attendances` - Record absensi harian
- `special_permissions` - Izin khusus
- `system_settings` - Pengaturan sistem

## 🎨 Customization

### Mengganti Tema
1. Login sebagai admin
2. Buka menu Pengaturan
3. Pilih tema yang diinginkan
4. Simpan perubahan

### Mengatur Background
1. Siapkan gambar JPG/PNG
2. Upload melalui menu Pengaturan
3. Atur transparansi dan blur effect

### Menambah Logo Sekolah
1. Siapkan logo max 500x500px, < 3MB
2. Upload melalui menu Pengaturan
3. Logo akan tampil di halaman login dan dashboard

## 📝 Fitur Detail

### Manajemen Absensi
- Check-in otomatis deteksi keterlambatan
- Check-out untuk jam pulang
- Status: Hadir, Terlambat, Sakit, Izin, Alfa, Pulang Awal
- Catatan untuk setiap absensi

### Laporan
- Filter berdasarkan bulan/minggu
- Statistik kehadiran per siswa
- Export ke CSV
- Print-friendly layout

### Petugas Harian
- Ganti petugas setiap hari
- Jadwal mingguan
- Notifikasi petugas aktif

## 🔒 Keamanan

- Password hashing dengan bcrypt
- **Single session per user** - mencegah login bersamaan
- **Session token validation** - token disimpan di database
- **Force logout capability** - admin dapat paksa logout user
- Session-based authentication
- Role-based access control
- SQL injection prevention (prepared statements)
- File upload validation
- Secure session cookies (HttpOnly, SameSite)
- XSS protection dengan htmlspecialchars

## 🐛 Bug Fixes (Latest Update)

### Fixed Issues:
1. ✅ **Login tidak berfungsi** - Diperbaiki dengan Auth class baru
2. ✅ **Session tidak valid** - Ditambahkan session token validation
3. ✅ **Username sedang aktif** - Fitur force logout untuk reset sesi
4. ✅ **Password hash tidak cocok** - SQL sample data menggunakan hash yang benar

### New Features:
1. 🔐 **Manajemen Sesi Aktif** - Lihat semua user yang sedang login
2. 🚫 **Force Logout User** - Logout user tertentu dari panel admin
3. 🚨 **Force Logout All** - Logout semua user sekaligus
4. 📊 **Last Login Tracking** - Catat waktu login terakhir setiap user

## 📄 License

Free to use for educational purposes.

## 👨‍💻 Developer

SISDM - Sistem Informasi Siswa Dan Manajemen

---

**Happy Coding! 🚀**
