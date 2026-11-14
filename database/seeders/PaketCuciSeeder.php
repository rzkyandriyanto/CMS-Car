<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaketCuciSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $packages = [
            ['Express Care', 'Cuci eksterior cepat, bilas hidrolik, dan keringkan microfiber.', 50000, 30],
            ['Signature Wash', 'Cuci eksterior + vakum kabin interior, semir ban, dan pembersih kaca.', 90000, 60],
            ['Ultimate Detail', 'Cuci menyeluruh, foam pH-balanced, detailing interior, dan wax proteksi.', 175000, 120],
            ['Ceramic Gloss', 'Detail lengkap dengan lapisan ceramic gloss untuk kilap tahan lama.', 350000, 180],
        ];

        foreach ($packages as [$nama, $deskripsi, $harga, $menit]) {
            $exists = DB::table('paket_cuci')->where('nama_paket', $nama)->exists();
            $data = ['deskripsi' => $deskripsi, 'harga' => $harga, 'estimasi_menit' => $menit, 'status' => 'aktif', 'updated_at' => $now];
            if ($exists) {
                DB::table('paket_cuci')->where('nama_paket', $nama)->update($data);
            } else {
                DB::table('paket_cuci')->insert($data + ['nama_paket' => $nama, 'created_at' => $now]);
            }
        }
    }
}
