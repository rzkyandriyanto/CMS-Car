<?php

namespace Database\Seeders;

use App\Models\Mobil;
use App\Services\CatalogVehicleIdentityService;
use App\Services\VehicleGalleryService;
use App\Services\VehicleInventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StandardVehicleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $gallery = app(VehicleGalleryService::class);
        $identity = app(CatalogVehicleIdentityService::class);
        $inventory = app(VehicleInventoryService::class);

        $fallbackColors = [
            'Obsidian Black',
            'Pearl White',
            'Titanium Silver',
            'Midnight Blue',
            'Crimson Red',
        ];

        $catalog = [
            ['merk' => 'Toyota', 'model' => 'Avanza', 'tipe' => '1.5 G CVT', 'tahun' => 2024, 'transmisi' => 'cvt', 'bahan_bakar' => 'bensin', 'kilometer' => 0, 'harga_beli' => 240000000, 'harga_jual' => 275000000, 'kondisi' => 'baru'],
            ['merk' => 'Honda', 'model' => 'Brio', 'tipe' => 'RS CVT', 'tahun' => 2022, 'transmisi' => 'cvt', 'bahan_bakar' => 'bensin', 'kilometer' => 18500, 'harga_beli' => 165000000, 'harga_jual' => 192000000, 'kondisi' => 'bekas'],
            ['merk' => 'Honda', 'model' => 'Civic', 'tipe' => 'RS Turbo', 'tahun' => 2024, 'transmisi' => 'cvt', 'bahan_bakar' => 'bensin', 'kilometer' => 0, 'harga_beli' => 555000000, 'harga_jual' => 615000000, 'kondisi' => 'baru'],
            ['merk' => 'Toyota', 'model' => 'Fortuner', 'tipe' => '2.8 VRZ 4x2', 'tahun' => 2022, 'transmisi' => 'automatic', 'bahan_bakar' => 'diesel', 'kilometer' => 26000, 'harga_beli' => 455000000, 'harga_jual' => 510000000, 'kondisi' => 'bekas'],
            ['merk' => 'Honda', 'model' => 'HR-V', 'tipe' => 'SE CVT', 'tahun' => 2024, 'transmisi' => 'cvt', 'bahan_bakar' => 'bensin', 'kilometer' => 0, 'harga_beli' => 375000000, 'harga_jual' => 415000000, 'kondisi' => 'baru'],
            ['merk' => 'Toyota', 'model' => 'Innova Zenix', 'tipe' => '2.0 Q HV Modellista', 'tahun' => 2024, 'transmisi' => 'cvt', 'bahan_bakar' => 'hybrid', 'kilometer' => 0, 'harga_beli' => 560000000, 'harga_jual' => 620000000, 'kondisi' => 'baru'],
            ['merk' => 'Honda', 'model' => 'Mobilio', 'tipe' => 'RS CVT', 'tahun' => 2021, 'transmisi' => 'cvt', 'bahan_bakar' => 'bensin', 'kilometer' => 32400, 'harga_beli' => 155000000, 'harga_jual' => 182000000, 'kondisi' => 'bekas'],
            ['merk' => 'Toyota', 'model' => 'Raize', 'tipe' => '1.0T GR Sport', 'tahun' => 2024, 'transmisi' => 'cvt', 'bahan_bakar' => 'bensin', 'kilometer' => 0, 'harga_beli' => 265000000, 'harga_jual' => 295000000, 'kondisi' => 'baru'],
            ['merk' => 'Daihatsu', 'model' => 'Xenia', 'tipe' => '1.5 R ASA', 'tahun' => 2022, 'transmisi' => 'cvt', 'bahan_bakar' => 'bensin', 'kilometer' => 21800, 'harga_beli' => 170000000, 'harga_jual' => 198000000, 'kondisi' => 'bekas'],
            ['merk' => 'Suzuki', 'model' => 'XL7', 'tipe' => 'Hybrid Alpha', 'tahun' => 2024, 'transmisi' => 'automatic', 'bahan_bakar' => 'hybrid', 'kilometer' => 0, 'harga_beli' => 272000000, 'harga_jual' => 305000000, 'kondisi' => 'baru'],
            ['merk' => 'Mitsubishi', 'model' => 'Xpander', 'tipe' => 'Ultimate CVT', 'tahun' => 2024, 'transmisi' => 'cvt', 'bahan_bakar' => 'bensin', 'kilometer' => 0, 'harga_beli' => 282000000, 'harga_jual' => 315000000, 'kondisi' => 'baru'],
        ];

        DB::transaction(function () use ($catalog, $gallery, $identity, $inventory, $fallbackColors): void {
            foreach ($catalog as $idx => $item) {
                $sample = new Mobil($item);
                $galleryColors = collect($gallery->for($sample))->pluck('label')->filter()->values();
                $colors = collect();

                foreach ($galleryColors as $c) {
                    if ($colors->count() >= 5) break;
                    if (!$colors->contains(fn ($x) => strtolower($x) === strtolower($c))) {
                        $colors->push($c);
                    }
                }
                foreach ($fallbackColors as $c) {
                    if ($colors->count() >= 5) break;
                    if (!$colors->contains(fn ($x) => strtolower($x) === strtolower($c))) {
                        $colors->push($c);
                    }
                }

                $slugCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', Str::slug($item['merk'] . $item['model'])), 0, 8));
                $seq = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
                $catId = null;

                foreach ($colors as $cIdx => $colorName) {
                    $exists = Mobil::query()
                        ->whereRaw('LOWER(merk) = ? AND LOWER(model) = ? AND LOWER(warna) = ?', [
                            strtolower($item['merk']),
                            strtolower($item['model']),
                            strtolower($colorName),
                        ])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $cSeq = str_pad((string) ($cIdx + 1), 2, '0', STR_PAD_LEFT);
                    $kode = "STD-{$slugCode}-{$seq}-{$cSeq}";
                    $vin = "YZA-STD-VIN-{$slugCode}-{$seq}{$cSeq}";
                    $eng = "YZA-STD-ENG-{$slugCode}-{$seq}{$cSeq}";

                    $created = Mobil::create([
                        'kode_mobil' => $kode,
                        'merk' => $item['merk'],
                        'model' => $item['model'],
                        'tipe' => $item['tipe'],
                        'tahun' => $item['tahun'],
                        'warna' => $colorName,
                        'transmisi' => $item['transmisi'],
                        'bahan_bakar' => $item['bahan_bakar'],
                        'nomor_rangka' => $vin,
                        'nomor_mesin' => $eng,
                        'kilometer' => (int) ($item['kilometer'] ?? 0),
                        'harga_beli' => $item['harga_beli'],
                        'harga_jual' => $item['harga_jual'],
                        'kondisi' => $item['kondisi'],
                        'status' => 'tersedia',
                        'deskripsi' => "{$item['merk']} {$item['model']} {$item['tipe']} dengan inspeksi resmi YZA Automotive.",
                        'tanggal_masuk' => now()->toDateString(),
                        'sumber_mobil' => 'dealer',
                        'is_limited_edition' => false,
                        'catalog_identity' => $catId,
                    ]);

                    if ($catId === null) {
                        $catId = 'mobil:' . $created->id_mobil;
                        $created->update(['catalog_identity' => $catId]);
                    }

                    $inventory->registerPhysicalUnit($created);
                }
            }
        });
    }
}
