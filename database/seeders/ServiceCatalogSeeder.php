<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $serviceTypes = [
            [
                'nama_servis' => 'Servis Berkala',
                'deskripsi' => 'Pemeriksaan menyeluruh dan servis berkala kendaraan YZA',
                'estimasi_harga' => 350000,
                'duration_minutes' => 120,
            ],
            [
                'nama_servis' => 'Ganti Oli',
                'deskripsi' => 'Penggantian oli mesin dan filter oli berkala',
                'estimasi_harga' => 150000,
                'duration_minutes' => 60,
            ],
            [
                'nama_servis' => 'Tune Up',
                'deskripsi' => 'Penyetelan dan optimalisasi sistem pembakaran',
                'estimasi_harga' => 400000,
                'duration_minutes' => 120,
            ],
            [
                'nama_servis' => 'Servis Rem',
                'deskripsi' => 'Pembersihan kampas dan bleed minyak rem',
                'estimasi_harga' => 300000,
                'duration_minutes' => 120,
            ],
            [
                'nama_servis' => 'Servis AC',
                'deskripsi' => 'Pembersihan evaporator dan ganti filter kabin',
                'estimasi_harga' => 300000,
                'duration_minutes' => 120,
            ],
            [
                'nama_servis' => 'Kelistrikan',
                'deskripsi' => 'Pemeriksaan aki dan kelistrikan body',
                'estimasi_harga' => 250000,
                'duration_minutes' => 120,
            ],
            [
                'nama_servis' => 'Pengecekan Kendaraan',
                'deskripsi' => 'Pemeriksaan menyeluruh kondisi kendaraan terlebih dahulu oleh teknisi YZA sebelum menentukan tindakan servis.',
                'estimasi_harga' => 0,
                'duration_minutes' => 60,
            ],
        ];

        foreach ($serviceTypes as $st) {
            $exists = DB::table('jenis_servis')->where('nama_servis', $st['nama_servis'])->first();
            if ($exists) {
                DB::table('jenis_servis')
                    ->where('id_jenis_servis', $exists->id_jenis_servis)
                    ->update([
                        'deskripsi' => $st['deskripsi'],
                        'estimasi_harga' => $st['estimasi_harga'],
                        'duration_minutes' => $st['duration_minutes'],
                        'status' => 'aktif',
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('jenis_servis')->insert([
                    'nama_servis' => $st['nama_servis'],
                    'deskripsi' => $st['deskripsi'],
                    'estimasi_harga' => $st['estimasi_harga'],
                    'duration_minutes' => $st['duration_minutes'],
                    'status' => 'aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
