<?php

namespace Database\Seeders;

use App\Models\Mobil;
use App\Services\CatalogVehicleIdentityService;
use App\Services\VehicleColorStockService;
use App\Services\VehicleInventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleDummyStockSeeder extends Seeder
{
    public function run(): void
    {
        $identity = app(CatalogVehicleIdentityService::class);
        $colorStock = app(VehicleColorStockService::class);
        $inventoryService = app(VehicleInventoryService::class);

        $fallbackPalette = [
            'Obsidian Black',
            'Pearl White',
            'Titanium Silver',
            'Midnight Blue',
            'Crimson Red',
            'Emerald Green',
            'Graphite Gray',
        ];

        $models = $identity->models()
            ->unique(fn (Mobil $car) => $identity->key($car))
            ->values();

        DB::transaction(function () use ($models, $identity, $colorStock, $inventoryService, $fallbackPalette): void {
            foreach ($models as $modelIndex => $template) {
                $existingColors = collect($colorStock->colors($template))
                    ->pluck('label')
                    ->filter()
                    ->values();

                $targetColors = collect();
                foreach ($existingColors as $colorName) {
                    if ($targetColors->count() >= 5) {
                        break;
                    }
                    if (!$targetColors->contains(fn ($c) => $colorStock->normalize($c) === $colorStock->normalize($colorName))) {
                        $targetColors->push($colorName);
                    }
                }

                foreach ($fallbackPalette as $extraColor) {
                    if ($targetColors->count() >= 5) {
                        break;
                    }
                    if (!$targetColors->contains(fn ($c) => $colorStock->normalize($c) === $colorStock->normalize($extraColor))) {
                        $targetColors->push($extraColor);
                    }
                }

                $existingUnits = $colorStock->units($template);
                $slugCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', Str::slug($template->merk . $template->model)), 0, 8));
                $modelSeq = str_pad((string) ($modelIndex + 1), 2, '0', STR_PAD_LEFT);

                foreach ($targetColors as $colorIndex => $colorName) {
                    $alreadyAvailable = $existingUnits->contains(function (Mobil $unit) use ($colorStock, $colorName): bool {
                        return $unit->status === 'tersedia'
                            && $colorStock->normalize($unit->warna) === $colorStock->normalize($colorName)
                            && $colorStock->identified($unit);
                    });

                    if ($alreadyAvailable) {
                        continue;
                    }

                    $colorSeq = str_pad((string) ($colorIndex + 1), 2, '0', STR_PAD_LEFT);
                    $kodeMobil = "STK-{$slugCode}-{$modelSeq}-{$colorSeq}";
                    $vin = "YZA-VIN-{$slugCode}-{$modelSeq}{$colorSeq}";
                    $engine = "YZA-ENG-{$slugCode}-{$modelSeq}{$colorSeq}";

                    // Ensure global uniqueness in case of partial previous runs
                    $attempt = 1;
                    while (
                        Mobil::where('kode_mobil', $kodeMobil)
                            ->orWhere('nomor_rangka', $vin)
                            ->orWhere('nomor_mesin', $engine)
                            ->exists()
                    ) {
                        $attempt++;
                        $kodeMobil = "STK-{$slugCode}-{$modelSeq}-{$colorSeq}-{$attempt}";
                        $vin = "YZA-VIN-{$slugCode}-{$modelSeq}{$colorSeq}-{$attempt}";
                        $engine = "YZA-ENG-{$slugCode}-{$modelSeq}{$colorSeq}-{$attempt}";
                    }

                    $showcaseSlug = $template->showcase_slug;
                    if ($showcaseSlug && Mobil::where('showcase_slug', $showcaseSlug)->exists()) {
                        $showcaseSlug = null;
                    }

                    $created = Mobil::create([
                        'kode_mobil' => $kodeMobil,
                        'merk' => $template->merk,
                        'model' => $template->model,
                        'tipe' => $template->tipe ?: 'Standard',
                        'tahun' => (int) ($template->tahun ?: now()->year),
                        'warna' => $colorName,
                        'transmisi' => $template->transmisi ?: 'automatic',
                        'bahan_bakar' => $template->bahan_bakar ?: 'bensin',
                        'nomor_rangka' => $vin,
                        'nomor_mesin' => $engine,
                        'kilometer' => (int) ($template->kilometer ?? 0),
                        'harga_beli' => (float) ($template->harga_beli ?: 500000000),
                        'harga_jual' => (float) ($template->harga_jual ?: 600000000),
                        'kondisi' => $template->kondisi ?: 'baru',
                        'status' => 'tersedia',
                        'deskripsi' => $template->deskripsi ?: ($template->merk . ' ' . $template->model . ' unit stok gudang.'),
                        'tanggal_masuk' => now()->toDateString(),
                        'sumber_mobil' => $template->sumber_mobil ?: 'dealer',
                        'is_limited_edition' => (bool) ($template->is_limited_edition ?? false),
                        'showcase_slug' => $showcaseSlug ?: null,
                        'catalog_identity' => $template->catalog_identity ?: $identity->key($template),
                    ]);

                    $inventoryService->registerPhysicalUnit($created);
                }
            }
        });
    }
}
