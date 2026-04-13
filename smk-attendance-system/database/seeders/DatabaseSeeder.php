<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Parent as ParentModel;
use App\Models\Major;
use App\Models\ClassRoom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@smk.sch.id',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN,
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        // Create sample majors
        $majors = [
            ['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL'],
            ['name' => 'Teknik Komputer dan Jaringan', 'code' => 'TKJ'],
            ['name' => 'Akuntansi dan Keuangan Lembaga', 'code' => 'AKL'],
            ['name' => 'Bisnis Daring dan Pemasaran', 'code' => 'BDP'],
        ];

        foreach ($majors as $major) {
            Major::create($major);
        }

        // Create sample class rooms
        $rpl = Major::where('code', 'RPL')->first();
        $tkj = Major::where('code', 'TKJ')->first();
        
        ClassRoom::create([
            'name' => 'X RPL 1',
            'grade' => 'X',
            'suffix' => '1',
            'major_id' => $rpl->id,
            'academic_year' => 2024,
            'semester' => 'ganjil',
        ]);

        ClassRoom::create([
            'name' => 'XI RPL 1',
            'grade' => 'XI',
            'suffix' => '1',
            'major_id' => $rpl->id,
            'academic_year' => 2024,
            'semester' => 'ganjil',
        ]);

        ClassRoom::create([
            'name' => 'XII RPL 1',
            'grade' => 'XII',
            'suffix' => '1',
            'major_id' => $rpl->id,
            'academic_year' => 2024,
            'semester' => 'ganjil',
        ]);

        ClassRoom::create([
            'name' => 'X TKJ 1',
            'grade' => 'X',
            'suffix' => '1',
            'major_id' => $tkj->id,
            'academic_year' => 2024,
            'semester' => 'ganjil',
        ]);

        // Create sample teacher
        $teacher = User::create([
            'name' => 'Guru Contoh',
            'email' => 'guru@smk.sch.id',
            'password' => Hash::make('guru123'),
            'role' => User::ROLE_TEACHER,
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        Teacher::create([
            'user_id' => $teacher->id,
            'nip' => '199001012020121001',
            'specialization' => 'Produktif RPL',
        ]);

        // Create sample students
        for ($i = 1; $i <= 10; $i++) {
            $studentUser = User::create([
                'name' => "Siswa {$i}",
                'email' => "siswa{$i}@smk.sch.id",
                'password' => Hash::make('siswa123'),
                'role' => User::ROLE_STUDENT,
                'phone' => "08123456789{$i}",
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $studentUser->id,
                'nis' => "NIS" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nisn' => "NISN" . str_pad($i, 8, '0', STR_PAD_LEFT),
                'class_id' => ClassRoom::where('name', 'XII RPL 1')->first()->id,
                'major_id' => $rpl->id,
                'gender' => $i % 2 === 0 ? 'male' : 'female',
                'birth_date' => now()->subYears(rand(16, 18))->format('Y-m-d'),
                'birth_place' => 'Jakarta',
                'address' => "Jl. Contoh No. {$i}, Jakarta",
                'phone' => "08123456789{$i}",
                'parent_name' => "Orang Tua {$i}",
                'parent_phone' => "08198765432{$i}",
                'qr_code' => Str::uuid()->toString(),
                'is_active' => true,
            ]);
        }

        // Create sample parent
        $parent = User::create([
            'name' => 'Orang Tua Siswa',
            'email' => 'parent@smk.sch.id',
            'password' => Hash::make('parent123'),
            'role' => User::ROLE_PARENT,
            'phone' => '081987654321',
            'is_active' => true,
        ]);

        ParentModel::create([
            'user_id' => $parent->id,
            'nik' => '3171234567890001',
            'address' => 'Jl. Orang Tua No. 1, Jakarta',
            'emergency_contact' => '081987654321',
        ]);

        // Link parent to first student
        $firstStudent = Student::first();
        if ($firstStudent) {
            \DB::table('parent_student')->insert([
                'parent_id' => ParentModel::first()->id,
                'student_id' => $firstStudent->id,
                'relationship' => 'father',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed default settings
        $this->call(SettingsSeeder::class);
    }
}
