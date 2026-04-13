# School Attendance System - Instalasi dan Konfigurasi

## Persyaratan Sistem

- **PHP**: Versi 7.4 atau lebih tinggi
- **MySQL/MariaDB**: Versi 5.7 atau lebih tinggi
- **Web Server**: Apache/Nginx dengan mod_rewrite
- **Extensions PHP**: mysqli, json, session

## Langkah-Langkah Instalasi

### Opsi 1: Instalasi Manual (Recommended)

1. **Copy File ke Web Server**
   ```bash
   # Copy folder ke document root web server Anda
   cp -r school-attendance-system /var/www/html/
   ```

2. **Konfigurasi Database**
   
   Edit file `config/database.php` sesuai dengan konfigurasi database Anda:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'school_attendance');
   ```

3. **Set Permissions**
   ```bash
   chmod -R 755 /var/www/html/school-attendance-system/
   chmod -R 777 /var/www/html/school-attendance-system/config/
   chmod -R 777 /var/www/html/school-attendance-system/images/
   ```

4. **Akses Aplikasi**
   
   Buka browser dan akses: `http://localhost/school-attendance-system/`
   
   Database akan dibuat otomatis saat pertama kali diakses.

5. **Login**
   - Username: `admin`
   - Password: `admin123`

### Opsi 2: Menggunakan Composer

1. **Install Dependencies**
   ```bash
   cd school-attendance-system
   composer install
   ```

2. **Jalankan Setup Script**
   ```bash
   php scripts/setup.php
   ```

3. **Konfigurasi Database** (sama seperti opsi manual)

4. **Akses Aplikasi** (sama seperti opsi manual)

## Struktur Database

Sistem ini akan membuat tabel-tabel berikut secara otomatis:

- `users` - Data pengguna (admin dan petugas)
- `students` - Data siswa
- `attendance` - Data absensi harian
- `settings` - Pengaturan aplikasi
- `permissions` - Izin khusus (telat, keluar sekolah, pulang awal)

## Konfigurasi Identitas Sekolah

Edit file `config/database.php` untuk mengubah identitas sekolah:

```php
define('SCHOOL_NPSN', '12345678');
define('SCHOOL_NAME', 'SMK Teknologi Nusantara');
define('SCHOOL_ADDRESS', 'Jl. Pendidikan No. 123, Jakarta');
define('SCHOOL_WEBSITE', 'https://smkteknologi.sch.id');
define('SCHOOL_PHONE', '(021) 1234-5678');
```

## Fitur Utama

### Panel Admin
- Dashboard dengan statistik real-time
- Manajemen data absensi (CRUD)
- Manajemen data siswa (CRUD)
- Rekap absensi (bulanan dan mingguan)
- Pengaturan sistem

### Panel Petugas
- Dashboard aktivitas
- Check-in/Check-out siswa
- Absensi berhalangan (Sakit/Izin/Alfa)
- Izin khusus (Telat/Keluar Sekolah/Pulang Awal)

### Fitur Tambahan
- Light/Dark Mode (White, Light Gray, Dark Gray, Black)
- Multi-Theme CSS (Fluent UI, Material UI, Glassmorphism, Cyberpunk)
- Responsive design untuk mobile dan desktop
- Keyboard shortcuts (Ctrl+T untuk tema, Ctrl+M untuk mode)

## Troubleshooting

### Database tidak bisa dibuat otomatis
1. Pastikan user MySQL memiliki privilege CREATE DATABASE
2. Cek koneksi database di `config/database.php`
3. Pastikan ekstensi mysqli aktif

### Error "Connection failed"
1. Pastikan MySQL service berjalan
2. Periksa kredensial database
3. Cek firewall jika menggunakan remote database

### Tampilan tidak sesuai
1. Clear browser cache
2. Pastikan file CSS ter-load dengan benar
3. Cek console browser untuk error JavaScript

## Keamanan

**PENTING**: Setelah instalasi pertama kali:
1. Ganti password default admin
2. Buat user petugas baru
3. Backup database secara berkala
4. Update aplikasi secara berkala

## Backup & Restore

### Backup Database
```bash
mysqldump -u root -p school_attendance > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
mysql -u root -p school_attendance < backup_20240101.sql
```

## Lisensi

Sistem ini dibuat untuk keperluan pendidikan dan dapat dimodifikasi sesuai kebutuhan.

## Dukungan

Untuk bantuan lebih lanjut, silakan hubungi administrator sistem atau developer.
