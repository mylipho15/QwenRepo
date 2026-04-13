# Konsep Software Absensi Siswa SMK (Web-Based)

## 1. Deskripsi Umum
Sistem absensi berbasis web untuk siswa SMK yang mencatat **Jam Datang** dan **Jam Pulang** secara real-time. Sistem ini dirancang untuk memudahkan administrasi sekolah dalam memantau kehadiran siswa dengan fitur modern seperti QR Code, notifikasi otomatis, dan dashboard analitik.

---

## 2. Fitur Utama

### A. Modul Authentication & Authorization
- **Login Multi-Role**: Admin, Guru, Siswa, Orang Tua
- **SSO Integration** (opsional): Google Workspace for Education
- **Password Recovery** dengan verifikasi email/SMS

### B. Modul Absensi
#### Untuk Siswa:
- **Check-in (Jam Datang)**: 
  - Scan QR Code di gerbang sekolah
  - GPS validation (opsional)
  - Foto selfie otomatis (anti-proxy)
  - Timestamp otomatis
- **Check-out (Jam Pulang)**:
  - Scan QR Code saat pulang
  - Validasi jam pulang sesuai jadwal
  - Notifikasi jika pulang lebih awal

#### Untuk Guru/Admin:
- **Manual Entry**: Input absensi manual untuk kasus khusus
- **Bulk Import**: Upload Excel untuk data massal
- **Real-time Monitoring**: Dashboard live kehadiran per kelas
- **Rekap Harian/Mingguan/Bulanan**

### C. Modul Manajemen Data
- **Data Siswa**: NIS, Nama, Kelas, Jurusan, Foto
- **Data Kelas & Jadwal**: Mapping siswa ke kelas dan jadwal pelajaran
- **Data Guru**: Wali kelas, guru mata pelajaran
- **Pengaturan Jam Sekolah**: Flexible scheduling per jurusan

### D. Modul Notifikasi
- **WhatsApp/Email Gateway**: 
  - Notifikasi ke orang tua jika siswa belum datang (after 15 menit)
  - Laporan harian/mingguan
- **Push Notification** (jika ada mobile app)

### E. Modul Reporting & Analytics
- **Laporan Kehadiran**: 
  - Hadir, Izin, Sakit, Alpha
  - Persentase kehadiran per siswa/kelas
- **Trend Analysis**: Grafik kehadiran bulanan
- **Export Data**: PDF, Excel, CSV
- **Integrasi dengan SIAPK/Sistem Nilai**

---

## 3. Arsitektur Teknis

### Frontend
- **Framework**: React.js / Vue.js / Next.js
- **UI Library**: Tailwind CSS / Bootstrap 5
- **State Management**: Redux / Pinia
- **Responsive Design**: Mobile-first approach

### Backend
- **Language**: PHP (Laravel) / Node.js (Express) / Python (Django)
- **API**: RESTful API / GraphQL
- **Authentication**: JWT / OAuth2

### Database
- **Primary**: MySQL / PostgreSQL
- **Caching**: Redis (untuk session dan real-time data)
- **Backup**: Automated daily backup

### Infrastruktur
- **Hosting**: Cloud (AWS/GCP/Azure) atau On-Premise
- **Web Server**: Nginx / Apache
- **SSL Certificate**: HTTPS wajib
- **CDN**: Untuk asset statis (foto, QR code)

---

## 4. Alur Kerja (Workflow)

### Skenario Check-in (Jam Datang):
```
1. Siswa tiba di sekolah (07:00 - 07:30)
2. Buka aplikasi web / scan QR Code di gerbang
3. Sistem validasi:
   - Wajah cocok dengan foto database? (jika pakai face recognition)
   - Lokasi GPS dalam radius sekolah?
   - Belum absen hari ini?
4. Jika valid → Simpan timestamp "Jam Datang"
5. Tampilkan konfirmasi + kirim notifikasi ke orang tua
6. Jika terlambat (>07:30) → Catat sebagai "Terlambat"
```

### Skenario Check-out (Jam Pulang):
```
1. Siswa akan pulang (sesuai jadwal, misal 15:00)
2. Scan QR Code di gerbang / klik tombol "Pulang" di app
3. Sistem validasi:
   - Sudah check-in hari ini?
   - Jam pulang sesuai jadwal?
   - Ada izin khusus?
4. Jika valid → Simpan timestamp "Jam Pulang"
5. Hitung durasi kehadiran otomatis
6. Kirim notifikasi ke orang tua: "Ananda telah pulang"
```

---

## 5. Struktur Database (Schema Design)

### Tabel Utama:

#### `users`
```sql
id, email, password, role (admin/guru/siswa/orang_tua), created_at
```

#### `students`
```sql
id, user_id, nis, nisn, nama_lengkap, kelas_id, jurusan, foto_url, 
alamat, no_hp_orang_tua, created_at
```

#### `classes`
```sql
id, nama_kelas (X-RPL-1, XI-TKJ-2), tahun_ajaran, wali_kelas_id
```

#### `attendance_records`
```sql
id, student_id, tanggal, jam_datang, jam_pulang, 
status (hadir/izin/sakit/alpha/terlambat), 
keterangan, foto_checkin, foto_checkout, 
gps_latitude_in, gps_longitude_in, 
gps_latitude_out, gps_longitude_out,
created_at, updated_at
```

#### `schedules`
```sql
id, kelas_id, hari, jam_masuk, jam_pulang, is_active
```

#### `notifications`
```sql
id, user_id, type, message, is_read, sent_at
```

---

## 6. UI/UX Concept

### Dashboard Admin
- **Cards Statistik**: 
  - Total Siswa Hari Ini
  - Hadir / Terlambat / Alpha
  - Grafik Trend Mingguan
- **Table Real-time**: List siswa yang baru saja absen (live update)
- **Quick Actions**: Export laporan, kirim notifikasi massal

### Dashboard Siswa
- **Status Hari Ini**: Jam datang, jam pulang, durasi
- **Riwayat Absensi**: Kalender interaktif dengan warna status
- **Profil**: Data pribadi, jadwal pelajaran

### Dashboard Orang Tua
- **Monitoring Anak**: Notifikasi real-time
- **Laporan Bulanan**: Download rekap kehadiran
- **Form Izin Online**: Submit surat izin sakit/keperluan

---

## 7. Keamanan & Privasi

- **Enkripsi Data**: Password (bcrypt), data sensitif (AES-256)
- **Rate Limiting**: Mencegah brute force attack
- **Audit Log**: Track semua aktivitas penting
- **GDPR Compliance**: Hak akses data pribadi
- **Backup Strategy**: Daily backup + offsite storage

---

## 8. Teknologi Pendukung

| Komponen | Teknologi Pilihan |
|----------|------------------|
| Frontend | React.js + Tailwind CSS |
| Backend | Laravel 10 (PHP 8.2) |
| Database | MySQL 8.0 |
| Cache | Redis 7.0 |
| Queue | Laravel Horizon + Redis |
| Search | Algolia / Elasticsearch (opsional) |
| File Storage | AWS S3 / Local Storage |
| Email | SMTP / SendGrid |
| WhatsApp | Twilio / Fonnte / Wablas |
| Deployment | Docker + Kubernetes / VPS |
| CI/CD | GitHub Actions / GitLab CI |

---

## 9. Roadmap Pengembangan

### Phase 1 (MVP - 2 Bulan)
- [ ] Authentication system
- [ ] Basic check-in/check-out dengan QR Code
- [ ] Dashboard admin sederhana
- [ ] Laporan harian Excel

### Phase 2 (3-4 Bulan)
- [ ] Integrasi WhatsApp notification
- [ ] Face recognition untuk validasi
- [ ] Mobile responsive design
- [ ] Dashboard orang tua

### Phase 3 (5-6 Bulan)
- [ ] AI Analytics (prediksi alpha)
- [ ] Integrasi dengan sistem nilai
- [ ] Multi-school support
- [ ] Progressive Web App (PWA)

---

## 10. Estimasi Biaya (Opsional)

| Item | Estimasi Cost (IDR) |
|------|---------------------|
| Development (6 bulan) | 150.000.000 |
| Server & Hosting (tahunan) | 15.000.000 |
| Domain & SSL | 500.000 |
| WhatsApp Gateway (bulanan) | 500.000 |
| Maintenance (bulanan) | 5.000.000 |
| **Total Tahun Pertama** | **~230.000.000** |

*Catatan: Biaya dapat bervariasi tergantung kompleksitas dan vendor.*

---

## 11. Kesimpulan

Sistem absensi web-based ini memberikan solusi modern untuk SMK dengan:
✅ **Efisiensi**: Mengurangi beban administrasi manual  
✅ **Akurasi**: Data real-time dengan validasi multi-layer  
✅ **Transparansi**: Orang tua dapat monitoring langsung  
✅ **Scalability**: Dapat dikembangkan untuk multi-cabang  
✅ **Cost-effective**: Mengurangi kertas dan operasional  

Sistem ini siap diimplementasikan dengan teknologi open-source yang matang dan komunitas developer yang besar di Indonesia.

---

**Dibuat oleh**: AI Assistant  
**Tanggal**: 2025  
**Versi Dokumen**: 1.0
