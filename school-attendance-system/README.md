# Sistem Absensi Siswa Berbasis Web

## Deskripsi
Sistem Absensi Siswa adalah aplikasi web lengkap untuk mengelola absensi siswa di sekolah dengan fitur multi-theme dan multi-mode.

## Fitur Utama

### Identitas Sekolah
- NPSN
- Nama Sekolah
- Alamat
- Website
- Telepon

### Panel Admin
- **Beranda/Dashboard** - Statistik dan ringkasan absensi
- **Data Absensi** - Kelola jam datang, jam pulang, telat, pulang awal
- **Data Siswa** - Kelola data siswa (NIPD, Nama, Kelas, Jurusan)
- **Rekap Absensi** - Laporan bulanan dan mingguan
- **Pengaturan** - Konfigurasi sistem absensi

### Panel Petugas Absensi
- **Beranda/Dashboard** - Ringkasan aktivitas hari ini
- **Absensi Jam Masuk/Keluar** - Input absensi harian
- **Absensi Berhalangan** - Catat sakit/izin/alfa
- **Izin Khusus** - Telat/keluar lingkungan/pulang awal

### Fitur Tampilan
- **Light/Dark Mode**: White, Light Gray, Dark Gray, Black
- **Multi-Theme CSS**: 
  - Fluent UI (Default)
  - Material UI
  - Glassmorphism
  - Cyberpunk
- **Customizable Dashboard** - Panel yang dapat disesuaikan

### Manajemen Data
- Full CRUD untuk Data Absensi (Add/Edit/Update/Remove)
- Full CRUD untuk Data Siswa (Add/Edit/Update/Remove)

## Teknologi
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6
- **Session Management**: PHP Sessions

## Instalasi

### Persyaratan
1. Web Server (Apache/Nginx)
2. PHP 7.4 atau lebih tinggi
3. MySQL 5.7 atau MariaDB 10.3

### Langkah Instalasi

1. **Clone atau Extract** project ke folder web server Anda
   ```bash
   cp -r school-attendance-system /var/www/html/
   ```

2. **Konfigurasi Database**
   - Edit file `config/database.php`
   - Sesuaikan konfigurasi database:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'school_attendance');
     ```

3. **Buat Database**
   - Database akan dibuat otomatis saat pertama kali diakses
   - Atau buat manual:
     ```sql
     CREATE DATABASE school_attendance;
     ```

4. **Akses Aplikasi**
   - Buka browser dan akses: `http://localhost/school-attendance-system/`

5. **Login Default**
   - **Username**: admin
   - **Password**: admin123

## Struktur Folder

```
school-attendance-system/
├── admin/                  # Panel Administrator
│   ├── dashboard.php
│   ├── attendance.php
│   ├── students.php
│   ├── add-student.php
│   ├── add-attendance.php
│   ├── reports.php
│   └── settings.php
├── petugas/                # Panel Petugas Absensi
│   ├── dashboard.php
│   ├── check-in-out.php
│   ├── permission.php
│   └── special-permission.php
├── config/                 # Konfigurasi
│   └── database.php
├── css/                    # Stylesheet
│   └── styles.css
├── js/                     # JavaScript
│   └── main.js
├── includes/               # Komponen UI
│   ├── admin-header.php
│   ├── admin-footer.php
│   ├── petugas-header.php
│   └── petugas-footer.php
├── images/                 # Asset gambar
├── scripts/                # Script instalasi
│   └── setup.php
├── composer.json           # Dependency management
├── index.php               # Halaman login
├── login.php               # Proses login
└── logout.php              # Proses logout
```

## Penggunaan

### Admin Panel
1. Login dengan akun administrator
2. Kelola data siswa di menu "Data Siswa"
3. Lihat dan kelola absensi di menu "Data Absensi"
4. Cetak laporan di menu "Rekap Absensi"
5. Atur konfigurasi di menu "Pengaturan"

### Petugas Panel
1. Login dengan akun petugas
2. Input absensi masuk/keluar
3. Catat ketidakhadiran (sakit/izin/alfa)
4. Kelola izin khusus

### Ganti Tema
- Klik tombol palet warna di header untuk mengganti tema
- Shortcut keyboard: `Ctrl + T`

### Ganti Mode
- Klik tombol bulan/matahari di header untuk mengganti mode
- Shortcut keyboard: `Ctrl + M`

## Customization

### Menambah Tema Baru
Edit file `css/styles.css` dan tambahkan variabel CSS baru:

```css
[data-theme="your-theme"] {
    --bg-primary: #ffffff;
    --accent-primary: #0078d4;
    /* ... variabel lainnya */
}
```

### Menambah Mode Baru
Edit file `css/styles.css` dan tambahkan mode baru:

```css
[data-mode="your-mode"] {
    --bg-primary: #f5f5f5;
    --text-primary: #333333;
    /* ... variabel lainnya */
}
```

## Keamanan

1. **Password Hashing**: Menggunakan bcrypt untuk password
2. **SQL Injection Prevention**: Prepared statements untuk semua query
3. **XSS Protection**: Sanitasi input menggunakan htmlspecialchars()
4. **Session Management**: Session-based authentication

## Troubleshooting

### Database Connection Failed
- Pastikan MySQL/MariaDB berjalan
- Periksa kredensial di `config/database.php`
- Pastikan user database memiliki akses yang cukup

### Page Not Loading
- Periksa error log PHP
- Pastikan mod_rewrite aktif (jika menggunakan Apache)
- Periksa permission folder

### Theme Tidak Berfungsi
- Clear browser cache
- Pastikan JavaScript diaktifkan
- Periksa console browser untuk error

## License
Software ini dibuat untuk keperluan edukasi dan dapat digunakan secara gratis.

## Support
Untuk bantuan teknis, silakan hubungi administrator sistem.

---
**Versi**: 1.0.0  
**Tanggal**: 2024
