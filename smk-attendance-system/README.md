# SMK Attendance System

Sistem Absensi Siswa SMK Berbasis Web dengan Fitur Jam Datang dan Jam Pulang.

## 🚀 Fitur Utama

- **Absensi QR Code**: Scan QR code untuk check-in/check-out
- **GPS Geofencing**: Validasi lokasi siswa berada di lingkungan sekolah
- **Photo Verification**: Upload foto selfie saat absensi
- **Auto Late Detection**: Deteksi otomatis keterlambatan
- **Multi-role Dashboard**: Admin, Guru, Siswa, Orang Tua
- **Real-time Monitoring**: Dashboard real-time untuk monitoring kehadiran
- **Laporan & Export**: Export ke PDF, Excel, CSV
- **Notifikasi**: Integrasi WhatsApp dan Email

## 📋 Requirements

- PHP >= 8.2
- MySQL 8.0 atau MariaDB 10.6+
- Composer
- Node.js & NPM (untuk frontend assets)
- Redis (opsional, untuk caching)

## 🔧 Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd smk-attendance-system
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` sesuai konfigurasi database Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smk_attendance
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Database Migration & Seeding

```bash
php artisan migrate --seed
```

### 5. Storage Link

```bash
php artisan storage:link
```

### 6. Build Assets (Optional)

```bash
npm run build
# atau untuk development
npm run dev
```

### 7. Run Development Server

```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

## 👤 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@smk.sch.id | admin123 |
| Teacher | guru@smk.sch.id | guru123 |
| Student | siswa1@smk.sch.id | siswa123 |
| Parent | parent@smk.sch.id | parent123 |

## 📁 Struktur Direktori

```
smk-attendance-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AttendanceController.php
│   │   │   ├── CheckInController.php
│   │   │   ├── DashboardController.php
│   │   │   └── ...
│   │   └── Middleware/
│   │       └── CheckRole.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Student.php
│   │   ├── Attendance.php
│   │   └── ...
│   └── Providers/
├── bootstrap/
├── config/
│   ├── attendance.php
│   └── database.php
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   └── views/
├── routes/
│   ├── web.php
│   └── api.php
└── ...
```

## 🗄️ Database Schema

### Tabel Utama:
1. **users** - Data pengguna (admin, guru, siswa, orang tua)
2. **students** - Data detail siswa
3. **teachers** - Data detail guru
4. **parents** - Data orang tua
5. **class_rooms** - Data kelas
6. **majors** - Data jurusan
7. **attendances** - Data absensi (check-in & check-out)
8. **parent_student** - Relasi orang tua dan siswa
9. **settings** - Konfigurasi sistem
10. **activity_logs** - Log aktivitas

## ⚙️ Konfigurasi Absensi

Edit file `config/attendance.php` atau melalui menu Settings di dashboard admin:

- Jam masuk sekolah
- Jam pulang sekolah
- Toleransi keterlambatan
- Koordinat GPS sekolah
- Radius geofencing
- Wajib/tidak foto dan QR code

## 📊 API Endpoints

### Check-in
```http
POST /api/check-in
Content-Type: multipart/form-data

{
    "qr_code": "student-qr-code",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "photo": [image file]
}
```

### Check-out
```http
POST /api/check-out
Content-Type: multipart/form-data

{
    "latitude": -6.2088,
    "longitude": 106.8456,
    "photo": [image file]
}
```

### Attendance History
```http
GET /api/attendance/history
Authorization: Bearer {token}
```

## 🔐 Security Features

- Password hashing dengan bcrypt
- CSRF Protection
- Role-based Access Control (RBAC)
- IP Address logging
- Device fingerprinting
- GPS validation
- Photo verification

## 📝 License

MIT License

## 👨‍💻 Developer

Developed for SMK Attendance Management System

---

**Catatan**: Pastikan server Anda memiliki ekstensi PHP berikut:
- pdo_mysql
- gd (untuk image processing)
- json
- mbstring
- openssl
