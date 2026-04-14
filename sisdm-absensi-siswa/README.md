# SISDM Absensi Siswa

Sistem Informasi Absensi Siswa Berbasis Web untuk Sekolah

## 📋 Fitur Utama

### Identitas Sekolah
- NPSN, Nama Sekolah, Alamat, Website, Telepon
- Upload Logo Sekolah (max 500x500px, < 3MB)
- Custom Background Image (JPG/PNG)
- Pengaturan Transparency & Blur

### Panel Admin
- Dashboard dengan statistik real-time
- Manajemen Data Absensi (CRUD lengkap)
- Manajemen Data Siswa (CRUD lengkap)
- Rekap Absensi (Bulanan & Mingguan)
- Pengaturan Petugas Harian
- Pengaturan Program Absensi
- Manajemen Identitas Sekolah

### Panel Petugas Absensi
- Dashboard Petugas
- Absensi Jam Masuk
- Absensi Jam Pulang
- Absensi Berhalangan (Sakit/Izin/Alfa)
- Izin Keluar (Telat/Keluar Lingkungan/Pulang Awal)

### Fitur UI/UX
- **4 Tema**: Fluent UI (Default), Material UI, Glassmorphism, Cyberpunk
- **4 Mode Warna**: White, Light Gray, Dark Gray, Black
- Responsive Design (Desktop, Tablet, Mobile)
- Real-time Clock
- Flash Messages
- Modal Dialogs

## 🛠️ Spesifikasi Teknis

### Server Requirements
- PHP 8.1.10+
- MySQL 8.0.30+
- Apache/Nginx
- Laragon 6.0.0 (Recommended)

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 📦 Instalasi Otomatis

### Menggunakan Laragon

1. **Clone Repository**
```bash
cd C:\laragon\www
git clone https://github.com/yourusername/sisdm-absensi-siswa.git
cd sisdm-absensi-siswa
```

2. **Setup Database**
   - Buka HeidiSQL atau DBeaver
   - Connect ke MySQL localhost
   - Import file `sql/database.sql`

3. **Konfigurasi**
   - Edit `config/database.php` jika perlu
   - Default: root tanpa password

4. **Akses Aplikasi**
   - Buka browser: http://localhost/sisdm-absensi-siswa/

### Instalasi Manual

1. Extract ke folder web server Anda
2. Import database dari `sql/database.sql`
3. Konfigurasi koneksi database di `config/database.php`
4. Pastikan folder `assets/images/uploads/` writable (chmod 777)
5. Akses aplikasi melalui browser

## 🔐 Login Credentials (Demo)

### Administrator
- Username: `admin`
- Password: `admin123`

### Petugas Absensi
- Username: `petugas1`
- Password: `petugas123`

## 📁 Struktur Folder

```
sisdm-absensi-siswa/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet dengan multi-theme
│   ├── js/
│   │   └── main.js            # JavaScript untuk interaksi UI
│   └── images/
│       └── uploads/           # Upload directory untuk logo & background
├── config/
│   ├── config.php             # Main configuration
│   └── database.php           # Database connection settings
├── includes/
│   ├── admin_header.php       # Admin panel header
│   ├── admin_sidebar.php      # Admin panel sidebar
│   ├── petugas_header.php     # Petugas panel header
│   └── petugas_sidebar.php    # Petugas panel sidebar
├── modules/
│   ├── auth/
│   │   ├── login.php          # Login page
│   │   └── logout.php         # Logout handler
│   ├── admin/
│   │   ├── dashboard.php      # Admin dashboard
│   │   ├── absensi.php        # Data absensi management
│   │   ├── siswa.php          # Data siswa management
│   │   ├── rekap.php          # Rekap absensi
│   │   ├── petugas.php        # Petugas harian
│   │   ├── sekolah.php        # Identitas sekolah
│   │   └── pengaturan.php     # Pengaturan program
│   └── petugas/
│       ├── dashboard.php      # Petugas dashboard
│       ├── absensi_masuk.php  # Absen masuk
│       ├── absensi_pulang.php # Absen pulang
│       ├── berhalangan.php    # Absensi berhalangan
│       └── izin_keluar.php    # Izin keluar
├── sql/
│   └── database.sql           # Database schema + sample data
├── index.php                  # Homepage
└── README.md                  # This file
```

## 🎨 Theme & Mode

### Themes
1. **Fluent UI** - Modern Microsoft-style design (Default)
2. **Material UI** - Google Material Design
3. **Glassmorphism** - Frosted glass effect
4. **Cyberpunk** - Futuristic neon style

### Color Modes
1. **White** - Clean white background
2. **Light Gray** - Soft gray tones
3. **Dark Gray** - Comfortable dark theme
4. **Black** - Pure black OLED-friendly

## 📊 Database Schema

### Tables
- `sekolah` - Identitas sekolah
- `users` - Admin & Petugas accounts
- `petugas_harian` - Jadwal petugas harian
- `kelas` - Data kelas dan jurusan
- `siswa` - Data siswa
- `absensi` - Record absensi harian
- `izin_keluar` - Izin telat/keluar/pulang awal
- `settings_absensi` - Pengaturan sistem

## 🔧 Customization

### Mengganti Logo
1. Login sebagai Admin
2. Menu Identitas Sekolah
3. Upload logo (max 500x500px, < 3MB)

### Mengganti Background
1. Login sebagai Admin
2. Menu Pengaturan Program
3. Upload background image (JPG/PNG)
4. Atur transparency dan blur sesuai keinginan

### Mengatur Jam Sekolah
1. Login sebagai Admin
2. Menu Pengaturan Program
3. Set jam masuk, jam pulang, toleransi telat

## 🚀 Quick Start Commands

```bash
# Clone repository
git clone https://github.com/yourusername/sisdm-absensi-siswa.git

# Navigate to project
cd sisdm-absensi-siswa

# Import database (using MySQL CLI)
mysql -u root -p < sql/database.sql

# Or using command line in Laragon
# Open Cmder and run:
mysql -u root sisdm_absensi < sql/database.sql
```

## 📝 License

This project is open-source and available for educational purposes.

## 👨‍💻 Developer

Developed for school attendance management system.

## 🤝 Support

For issues and questions, please contact the development team.

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**Compatible with:** Laragon 6.0.0, PHP 8.1.10, MySQL 8.0.30
