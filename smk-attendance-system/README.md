# SMK Attendance System - Sistem Absensi Siswa SMK

Sistem absensi berbasis web untuk siswa SMK dengan fitur pencatatan jam datang dan jam pulang.

## 🚀 Fitur Utama

### 1. **Absensi Real-time**
- Check-in (Jam Datang) dengan QR Code, GPS, dan Foto Selfie
- Check-out (Jam Pulang) dengan validasi lokasi
- Deteksi keterlambatan otomatis
- Geofencing untuk memastikan siswa berada di lingkungan sekolah

### 2. **Multi-Role Access**
- **Admin**: Manajemen penuh sistem
- **Guru**: Verifikasi absensi, lihat laporan kelas
- **Siswa**: Lakukan absensi, lihat riwayat
- **Orang Tua**: Monitoring kehadiran anak

### 3. **Dashboard & Laporan**
- Statistik real-time
- Grafik kehadiran 7 hari terakhir
- Kalender bulanan absensi
- Export laporan (PDF, Excel, CSV)

### 4. **Keamanan**
- Validasi QR Code unik per siswa
- GPS geofencing
- Foto selfie saat absensi
- Enkripsi password
- Role-based access control

## 📁 Struktur Direktori

```
smk-attendance-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AttendanceController.php    # CRUD Absensi
│   │   │   ├── CheckInController.php       # Check-in/out logic
│   │   │   ├── DashboardController.php     # Dashboard semua role
│   │   │   └── ...
│   │   └── Middleware/
│   │       └── CheckRole.php               # Role checker
│   └── Models/
│       ├── User.php                        # Model user
│       ├── Student.php                     # Model siswa
│       ├── Attendance.php                  # Model absensi
│       └── ...
├── config/
│   ├── attendance.php                      # Konfigurasi absensi
│   └── database.php                        # Konfigurasi database
├── database/
│   └── migrations/                         # Migration files
├── public/
│   └── uploads/                            # Upload foto & QR
├── resources/
│   └── views/                              # Blade templates
├── routes/
│   └── web.php                             # Routing aplikasi
└── README.md
```

## 🛠️ Teknologi

- **Backend**: Laravel 10 (PHP 8.2+)
- **Frontend**: Blade Templates + Tailwind CSS
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Authentication**: Laravel Sanctum

## ⚙️ Konfigurasi

File `config/attendance.php`:
- Jam masuk sekolah: 07:00
- Toleransi keterlambatan: 15 menit
- Radius geofencing: 100 meter
- Wajib QR Code, GPS, dan Foto

## 📋 Instalasi

```bash
# Clone repository
git clone <repository-url>
cd smk-attendance-system

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Setup database
php artisan migrate --seed

# Create storage link
php artisan storage:link

# Run development server
php artisan serve
```

## 🔐 Default Login

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@smk.sch.id | password |
| Guru | guru@smk.sch.id | password |
| Siswa | siswa@smk.sch.id | password |

## 📊 Database Schema

Tabel utama:
- `users` - Data pengguna semua role
- `students` - Data siswa
- `teachers` - Data guru
- `parents` - Data orang tua
- `attendances` - Record absensi
- `class_rooms` - Data kelas
- `majors` - Jurusan
- `settings` - Pengaturan sistem

## 🎯 Alur Absensi Siswa

1. Login ke sistem
2. Buka halaman Check-in
3. Scan QR Code pribadi
4. Ambil foto selfie
5. Sistem validasi GPS
6. Absensi berhasil → Status: Hadir/Telat

Untuk pulang:
1. Buka halaman Check-out
2. Ambil foto selfie
3. Sistem validasi GPS
4. Absensi pulang berhasil

## 📱 Responsive Design

Aplikasi dapat diakses melalui:
- Desktop/Laptop
- Tablet
- Smartphone (mobile-friendly)

## 🔒 Keamanan

- Password hashing dengan bcrypt
- CSRF protection
- SQL injection prevention
- XSS protection
- Rate limiting untuk API

## 📄 License

MIT License

---

**Dibuat untuk SMK Indonesia** © 2024
