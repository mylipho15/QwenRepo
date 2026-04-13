<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General settings
            ['key' => 'school_name', 'value' => 'SMK Negeri Contoh', 'type' => 'string', 'group' => 'general', 'description' => 'Nama sekolah'],
            ['key' => 'school_address', 'value' => 'Jl. Pendidikan No. 1, Jakarta', 'type' => 'string', 'group' => 'general', 'description' => 'Alamat sekolah'],
            ['key' => 'school_phone', 'value' => '021-12345678', 'type' => 'string', 'group' => 'general', 'description' => 'Nomor telepon sekolah'],
            ['key' => 'school_email', 'value' => 'info@smk.sch.id', 'type' => 'string', 'group' => 'general', 'description' => 'Email sekolah'],
            
            // Attendance settings
            ['key' => 'school_start_time', 'value' => '07:00', 'type' => 'string', 'group' => 'attendance', 'description' => 'Jam masuk sekolah'],
            ['key' => 'school_end_time', 'value' => '15:00', 'type' => 'string', 'group' => 'attendance', 'description' => 'Jam pulang sekolah'],
            ['key' => 'late_threshold', 'value' => '15', 'type' => 'integer', 'group' => 'attendance', 'description' => 'Toleransi keterlambatan (menit)'],
            ['key' => 'school_latitude', 'value' => '-6.2088', 'type' => 'string', 'group' => 'attendance', 'description' => 'Koordinat latitude sekolah'],
            ['key' => 'school_longitude', 'value' => '106.8456', 'type' => 'string', 'group' => 'attendance', 'description' => 'Koordinat longitude sekolah'],
            ['key' => 'max_distance_meters', 'value' => '100', 'type' => 'integer', 'group' => 'attendance', 'description' => 'Radius maksimal absensi (meter)'],
            ['key' => 'require_qr_code', 'value' => 'true', 'type' => 'boolean', 'group' => 'attendance', 'description' => 'Wajibkan QR code untuk absensi'],
            ['key' => 'require_photo', 'value' => 'true', 'type' => 'boolean', 'group' => 'attendance', 'description' => 'Wajibkan foto untuk absensi'],
            ['key' => 'require_gps', 'value' => 'true', 'type' => 'boolean', 'group' => 'attendance', 'description' => 'Wajibkan GPS untuk absensi'],
            ['key' => 'auto_absent_time', 'value' => '09:00', 'type' => 'string', 'group' => 'attendance', 'description' => 'Otomatis markir absen setelah jam ini'],
            ['key' => 'minimum_study_hours', 'value' => '6', 'type' => 'integer', 'group' => 'attendance', 'description' => 'Durasi minimal belajar sebelum checkout'],
            
            // Notification settings
            ['key' => 'send_whatsapp_notification', 'value' => 'false', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Aktifkan notifikasi WhatsApp'],
            ['key' => 'send_email_notification', 'value' => 'true', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Aktifkan notifikasi email'],
            ['key' => 'notify_parents', 'value' => 'true', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Kirim notifikasi ke orang tua'],
            ['key' => 'notify_on_late', 'value' => 'true', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Notifikasi jika siswa terlambat'],
            ['key' => 'notify_on_absent', 'value' => 'true', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Notifikasi jika siswa absen'],
            
            // WhatsApp gateway settings
            ['key' => 'wa_gateway_url', 'value' => '', 'type' => 'string', 'group' => 'whatsapp', 'description' => 'URL WhatsApp Gateway'],
            ['key' => 'wa_gateway_token', 'value' => '', 'type' => 'string', 'group' => 'whatsapp', 'description' => 'Token WhatsApp Gateway', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert([
                ...$setting,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
