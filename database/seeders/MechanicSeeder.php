<?php

namespace Database\Seeders;

use App\Models\{Petugas, ServiceSpecialization, ServiceStaffProfile, TechnicianWorkingHour};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash};

class MechanicSeeder extends Seeder
{
    /**
     * Seed 3 active mechanics with user accounts assigned to the `mechanic` role,
     * technician profiles, specializations, and Monday-Friday working hours.
     */
    public function run(): void
    {
        $roleId = DB::table('roles')->where('nama_role', 'mechanic')->value('id_role');
        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'nama_role' => 'mechanic',
                'deskripsi' => 'Pekerjaan mekanik dan work order terassign',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_role');
        }

        $mechanics = [
            [
                'username' => 'mekanik.andi',
                'email' => 'andi.mekanik@yza.local',
                'kode_petugas' => 'PTG-MEK-001',
                'nama_lengkap' => 'Andi Pratama',
                'no_hp' => '081288990001',
                'alamat' => 'Jakarta Selatan',
                'jabatan' => 'Senior Mekanik Mesin',
                'experience_years' => 7,
                'specializations' => ['General Service', 'Engine', 'Transmission'],
            ],
            [
                'username' => 'mekanik.budi',
                'email' => 'budi.mekanik@yza.local',
                'kode_petugas' => 'PTG-MEK-002',
                'nama_lengkap' => 'Budi Santoso',
                'no_hp' => '081288990002',
                'alamat' => 'Tangerang Selatan',
                'jabatan' => 'Spesialis Kelistrikan & AC',
                'experience_years' => 5,
                'specializations' => ['Electrical', 'AC', 'General Service'],
            ],
            [
                'username' => 'mekanik.dedi',
                'email' => 'dedi.mekanik@yza.local',
                'kode_petugas' => 'PTG-MEK-003',
                'nama_lengkap' => 'Dedi Kurniawan',
                'no_hp' => '081288990003',
                'alamat' => 'Bekasi',
                'jabatan' => 'Spesialis Rem & Kaki-kaki',
                'experience_years' => 6,
                'specializations' => ['Brake', 'General Service', 'Engine'],
            ],
        ];

        foreach ($mechanics as $item) {
            $userId = DB::table('users')->where('username', $item['username'])->value('id_user');
            if (!$userId) {
                $userId = DB::table('users')->insertGetId([
                    'id_role' => $roleId,
                    'username' => $item['username'],
                    'email' => $item['email'],
                    'password' => Hash::make('mekanik123'),
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'id_user');
            } else {
                DB::table('users')->where('id_user', $userId)->update([
                    'id_role' => $roleId,
                    'email' => $item['email'],
                    'status' => 'aktif',
                    'updated_at' => now(),
                ]);
            }

            $petugas = Petugas::updateOrCreate(
                ['kode_petugas' => $item['kode_petugas']],
                [
                    'id_user' => $userId,
                    'nama_lengkap' => $item['nama_lengkap'],
                    'no_hp' => $item['no_hp'],
                    'alamat' => $item['alamat'],
                    'jabatan' => $item['jabatan'],
                    'status' => 'aktif',
                ]
            );

            $profile = ServiceStaffProfile::updateOrCreate(
                ['id_petugas' => $petugas->id_petugas],
                [
                    'can_be_technician' => true,
                    'can_be_service_advisor' => false,
                    'experience_years' => $item['experience_years'],
                    'is_bookable' => true,
                ]
            );

            $specIds = ServiceSpecialization::whereIn('name', $item['specializations'])
                ->pluck('id_service_specialization')
                ->all();
            if ($specIds !== []) {
                $profile->specializations()->sync($specIds);
            }

            foreach (range(1, 5) as $day) {
                TechnicianWorkingHour::updateOrCreate(
                    ['id_petugas' => $petugas->id_petugas, 'day_of_week' => $day],
                    ['is_working' => true, 'open_time' => '08:00:00', 'close_time' => '17:00:00']
                );
            }
            foreach (range(6, 7) as $day) {
                TechnicianWorkingHour::updateOrCreate(
                    ['id_petugas' => $petugas->id_petugas, 'day_of_week' => $day],
                    ['is_working' => false, 'open_time' => null, 'close_time' => null]
                );
            }
        }
    }
}
