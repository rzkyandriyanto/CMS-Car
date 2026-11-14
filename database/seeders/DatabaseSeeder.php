<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            ['id_role' => 1, 'nama_role' => 'admin', 'deskripsi' => 'Akses penuh untuk manajemen sistem YZA Automotive'],
            ['id_role' => 2, 'nama_role' => 'petugas', 'deskripsi' => 'Akses operasional dealer'],
            ['id_role' => 3, 'nama_role' => 'pelanggan', 'deskripsi' => 'Portal pelanggan'],
            ['id_role' => 4, 'nama_role' => 'super_admin', 'deskripsi' => 'Akses penuh seluruh sistem'],
            ['id_role' => 5, 'nama_role' => 'owner', 'deskripsi' => 'Pimpinan bisnis dan laporan read-only'],
        ];

        foreach ($roles as $role) {
            \Illuminate\Support\Facades\DB::table('roles')->updateOrInsert(
                ['id_role' => $role['id_role']],
                ['nama_role' => $role['nama_role'], 'deskripsi' => $role['deskripsi'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $adminId = \Illuminate\Support\Facades\DB::table('users')->where('username', 'admin')->value('id_user');
        if (!$adminId) {
            $adminId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
                'id_role' => 1,
                'username' => 'admin',
                'email' => 'admin@yza.local',
                'password' => bcrypt('admin123'),
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_user');

            \Illuminate\Support\Facades\DB::table('petugas')->updateOrInsert(
                ['id_user' => $adminId],
                [
                    'kode_petugas' => 'PTG-ADMIN-01',
                    'nama_lengkap' => 'Administrator YZA',
                    'jabatan' => 'Super Admin',
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->call([
            ServiceCatalogSeeder::class,
            MechanicSeeder::class,
            PremiumPerformanceSeeder::class,
        ]);
    }
}
