<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RawamangunDummyStudentsSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $dummyStudents = [
            ['num' => 1, 'name' => 'Siswa Dummy Rawamangun 01', 'kelas' => '4', 'bidang' => 'Matematika', 'level' => 'Berbakat A', 'school' => 'SDN Rawamangun 01 Pagi'],
            ['num' => 2, 'name' => 'Siswa Dummy Rawamangun 02', 'kelas' => '4', 'bidang' => 'IPA', 'level' => 'Berbakat A', 'school' => 'SDN Rawamangun 02 Pagi'],
            ['num' => 3, 'name' => 'Siswa Dummy Rawamangun 03', 'kelas' => '5', 'bidang' => 'Matematika', 'level' => 'Berbakat A', 'school' => 'SDI Al-Azhar Rawamangun'],
            ['num' => 4, 'name' => 'Siswa Dummy Rawamangun 04', 'kelas' => '5', 'bidang' => 'IPA', 'level' => 'Berbakat B', 'school' => 'SD Muhamadiyah 24 Rawamangun'],
            ['num' => 5, 'name' => 'Siswa Dummy Rawamangun 05', 'kelas' => '6', 'bidang' => 'Matematika & IPA', 'level' => 'Berbakat A', 'school' => 'SDS Labschool Rawamangun'],
            ['num' => 6, 'name' => 'Siswa Dummy Rawamangun 06', 'kelas' => '6', 'bidang' => 'Matematika', 'level' => 'Berbakat B', 'school' => 'SDN Rawamangun 12 Pagi'],
            ['num' => 7, 'name' => 'Siswa Dummy Rawamangun 07', 'kelas' => '7', 'bidang' => 'Matematika', 'level' => 'Berbakat A', 'school' => 'SMP Labschool Jakarta Rawamangun'],
            ['num' => 8, 'name' => 'Siswa Dummy Rawamangun 08', 'kelas' => '7', 'bidang' => 'IPA', 'level' => 'Berbakat A', 'school' => 'SMPN 74 Rawamangun Jakarta'],
            ['num' => 9, 'name' => 'Siswa Dummy Rawamangun 09', 'kelas' => '8', 'bidang' => 'Matematika & IPA', 'level' => 'Berbakat B', 'school' => 'SMPN 92 Rawamangun Jakarta'],
            ['num' => 10, 'name' => 'Siswa Dummy Rawamangun 10', 'kelas' => '9', 'bidang' => 'Matematika', 'level' => 'Berbakat A', 'school' => 'SMP Al-Azhar 12 Rawamangun'],
        ];

        foreach ($dummyStudents as $dummy) {
            $idx = $dummy['num'];
            $padded = str_pad((string) $idx, 2, '0', STR_PAD_LEFT);
            User::updateOrCreate(
                ['email' => "siswa.rwm{$idx}@adikaka.com"],
                [
                    'username' => "siswarwm{$idx}",
                    'name' => $dummy['name'],
                    'student_name' => $dummy['name'],
                    'password' => $password,
                    'role' => 'user',
                    'student_class' => $dummy['kelas'],
                    'bidang' => $dummy['bidang'],
                    'level' => $dummy['level'],
                    'school_name' => $dummy['school'],
                    'phone' => '0813800000'.$padded,
                    'address' => 'Cabang Rawamangun, Jakarta Timur',
                    'parent_name' => 'Orang Tua Rawamangun '.$padded,
                    'parent_phone' => '0813900000'.$padded,
                    'is_verified' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
