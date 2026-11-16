<?php

namespace Database\Seeders;

use App\Models\KategoriSparepart;
use App\Models\Sparepart;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SparepartDummySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'oli' => KategoriSparepart::updateOrCreate(
                ['nama_kategori' => 'Oli & Pelumas'],
                ['deskripsi' => 'Oli mesin, oli gardan, oli transmisi, minyak rem, dan cairan pelumas.', 'status' => 'aktif']
            ),
            'filter' => KategoriSparepart::updateOrCreate(
                ['nama_kategori' => 'Filter Kendaraan'],
                ['deskripsi' => 'Filter oli, filter udara, filter bahan bakar, dan filter kabin AC.', 'status' => 'aktif']
            ),
            'rem' => KategoriSparepart::updateOrCreate(
                ['nama_kategori' => 'Sistem Pengereman'],
                ['deskripsi' => 'Kampas rem depan, kampas rem belakang, piringan cakram, dan seal rem.', 'status' => 'aktif']
            ),
            'listrik' => KategoriSparepart::updateOrCreate(
                ['nama_kategori' => 'Pengapian & Kelistrikan'],
                ['deskripsi' => 'Busi, koil pengapian, aki, sekring, dan sensor kelistrikan.', 'status' => 'aktif']
            ),
            'kaki' => KategoriSparepart::updateOrCreate(
                ['nama_kategori' => 'Kaki-kaki & Suspensi'],
                ['deskripsi' => 'Shock absorber, ball joint, tie rod, link stabilizer, dan bearing roda.', 'status' => 'aktif']
            ),
            'pendingin' => KategoriSparepart::updateOrCreate(
                ['nama_kategori' => 'Pendingin & Sabuk Mesin'],
                ['deskripsi' => 'Fan belt, timing belt, radiator coolant, thermostat, dan selang radiator.', 'status' => 'aktif']
            ),
            'transmisi' => KategoriSparepart::updateOrCreate(
                ['nama_kategori' => 'Kopling & Transmisi'],
                ['deskripsi' => 'Kampas kopling, dekrup, release bearing, dan komponen gardan.', 'status' => 'aktif']
            ),
        ];

        $suppliers = [
            Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-YZA-001'],
                ['nama_supplier' => 'PT Pelumas Nusantara', 'no_hp' => '021-5551201', 'email' => 'order@pelumasnusantara.example', 'alamat' => 'Tangerang Selatan', 'kontak_person' => 'Budi Santoso', 'status' => 'aktif']
            ),
            Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-YZA-002'],
                ['nama_supplier' => 'CV Suku Cadang Prima', 'no_hp' => '021-5551202', 'email' => 'sales@sukucadangprima.example', 'alamat' => 'Jakarta Barat', 'kontak_person' => 'Rina Wijaya', 'status' => 'aktif']
            ),
            Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-YZA-003'],
                ['nama_supplier' => 'PT Otomotif Mandiri', 'no_hp' => '021-5551203', 'email' => 'supply@otomotifmandiri.example', 'alamat' => 'Bekasi', 'kontak_person' => 'Hendra Kusuma', 'status' => 'aktif']
            ),
        ];

        // 50 sparepart items with stok = 20
        $items = [
            // Oli & Pelumas (10 items — Sport/Performance & Standar bermerek)
            ['SP-YZA-001', 'oli', 'Shell Helix Ultra Full Synthetic 5W-40 (1L)', 'Shell', 145000, 195000, 'botol'],
            ['SP-YZA-002', 'oli', 'Eneos Sustina Racing Full Synthetic 0W-50 (1L)', 'Eneos', 240000, 315000, 'botol'],
            ['SP-YZA-003', 'oli', 'Motul 300V Competition Ester Core 15W-50 (2L)', 'Motul', 520000, 675000, 'kaleng'],
            ['SP-YZA-004', 'oli', 'Mobil 1 FS European Car Formula 0W-40 (1L)', 'Mobil 1', 185000, 245000, 'botol'],
            ['SP-YZA-005', 'oli', 'Shell Helix HX7 Semi Synthetic 10W-40 (4L)', 'Shell', 295000, 385000, 'galon'],
            ['SP-YZA-006', 'oli', 'Eneos Touring Pro Fully Synthetic 5W-30 (4L)', 'Eneos', 340000, 445000, 'galon'],
            ['SP-YZA-007', 'oli', 'Castrol Magnatec Stop-Start 10W-40 (4L)', 'Castrol', 310000, 410000, 'galon'],
            ['SP-YZA-008', 'oli', 'Motul Gear 300 LS 75W-90 Oli Gardan Sport (1L)', 'Motul', 235000, 310000, 'botol'],
            ['SP-YZA-009', 'oli', 'Liqui Moly Top Tec ATF 1800 Oli Transmisi (1L)', 'Liqui Moly', 210000, 280000, 'botol'],
            ['SP-YZA-010', 'oli', 'Brembo Sport Evo 500++ Minyak Rem DOT 4 (500ml)', 'Brembo', 115000, 160000, 'botol'],

            // Filter Kendaraan (8 items)
            ['SP-YZA-011', 'filter', 'Filter Oli Denso Spin-On DXE-1001', 'Denso', 38000, 60000, 'pcs'],
            ['SP-YZA-012', 'filter', 'Filter Oli Mann-Filter W 610/3 OEM', 'Mann-Filter', 85000, 125000, 'pcs'],
            ['SP-YZA-013', 'filter', 'Filter Oli Performance K&N Wrench-Off HP-1010', 'K&N', 225000, 295000, 'pcs'],
            ['SP-YZA-014', 'filter', 'Filter Oli Bosch Premium P3312', 'Bosch', 55000, 85000, 'pcs'],
            ['SP-YZA-015', 'filter', 'Filter Udara Mesin Sakura High-Flow A-1140', 'Sakura', 85000, 128000, 'pcs'],
            ['SP-YZA-016', 'filter', 'Filter Kabin AC Denso Activated Carbon', 'Denso', 95000, 140000, 'pcs'],
            ['SP-YZA-017', 'filter', 'Filter Bahan Bakar / Fuel Filter Bosch F-026', 'Bosch', 165000, 225000, 'pcs'],
            ['SP-YZA-018', 'filter', 'Filter Udara Sport K&N Replacement Panel', 'K&N', 780000, 990000, 'pcs'],

            // Sistem Pengereman (8 items)
            ['SP-YZA-019', 'rem', 'Kampas Rem Depan Bendix General CT Ceramic (Set)', 'Bendix', 240000, 330000, 'set'],
            ['SP-YZA-020', 'rem', 'Kampas Rem Belakang Akebono ProACT (Set)', 'Akebono', 215000, 295000, 'set'],
            ['SP-YZA-021', 'rem', 'Kampas Rem Depan Brembo Prime Ceramic Sport (Set)', 'Brembo', 580000, 760000, 'set'],
            ['SP-YZA-022', 'rem', 'Kampas Rem Belakang Nissin Semi-Metallic (Set)', 'Nissin', 225000, 310000, 'set'],
            ['SP-YZA-023', 'rem', 'Piringan Cakram Depan Brembo UV Coated Slotted', 'Brembo', 890000, 1150000, 'pcs'],
            ['SP-YZA-024', 'rem', 'Master Rem Atas / Brake Master Cylinder', 'Aisin', 460000, 610000, 'pcs'],
            ['SP-YZA-025', 'rem', 'Selang Rem Fleksibel Depan', 'Advics', 110000, 155000, 'pcs'],
            ['SP-YZA-026', 'rem', 'Brake Cleaner Spray (500ml)', '3M', 48000, 75000, 'kaleng'],

            // Pengapian & Kelistrikan (8 items)
            ['SP-YZA-027', 'listrik', 'Busi Iridium IX (Set 4 pcs)', 'NGK Iridium', 320000, 420000, 'set'],
            ['SP-YZA-028', 'listrik', 'Busi Nickel Standard (1 pcs)', 'Denso', 24000, 40000, 'pcs'],
            ['SP-YZA-029', 'listrik', 'Koil Pengapian / Ignition Coil', 'Denso', 365000, 485000, 'pcs'],
            ['SP-YZA-030', 'listrik', 'Aki Kering Maintenance Free NS40ZL', 'GS Astra', 760000, 960000, 'unit'],
            ['SP-YZA-031', 'listrik', 'Aki Kering Maintenance Free NS60LS', 'Amaron', 920000, 1180000, 'unit'],
            ['SP-YZA-032', 'listrik', 'Bohlam Lampu Utama H4 Halogen 12V', 'Philips X-tremeVision', 145000, 198000, 'set'],
            ['SP-YZA-033', 'listrik', 'Klakson Keong Waterproof 12V', 'Bosch Europa', 175000, 240000, 'set'],
            ['SP-YZA-034', 'listrik', 'Karet Wiper Frameless 22" + 16"', 'Bosch Clear Advantage', 125000, 175000, 'set'],

            // Kaki-kaki & Suspensi (8 items)
            ['SP-YZA-035', 'kaki', 'Shockbreaker Depan Gas (Set Kanan-Kiri)', 'KYB Excel-G', 1050000, 1380000, 'set'],
            ['SP-YZA-036', 'kaki', 'Shockbreaker Belakang (Set Kanan-Kiri)', 'KYB Excel-G', 680000, 890000, 'set'],
            ['SP-YZA-037', 'kaki', 'Ball Joint Bawah / Lower Arm', '555 Japan', 185000, 255000, 'pcs'],
            ['SP-YZA-038', 'kaki', 'Tie Rod End Kemudi', '555 Japan', 165000, 230000, 'pcs'],
            ['SP-YZA-039', 'kaki', 'Rack End / Long Tie Rod', '555 Japan', 195000, 265000, 'pcs'],
            ['SP-YZA-040', 'kaki', 'Link Stabilizer Depan', 'TRW', 135000, 190000, 'pcs'],
            ['SP-YZA-041', 'kaki', 'Bearing Roda Depan ABS', 'Koyo Japan', 295000, 395000, 'pcs'],
            ['SP-YZA-042', 'kaki', 'Bushing Lower Arm Besar', 'RBI', 95000, 145000, 'pcs'],

            // Pendingin & Sabuk Mesin (4 items)
            ['SP-YZA-043', 'pendingin', 'Fan Belt / V-Ribbed Belt 6PK', 'Bando', 115000, 165000, 'pcs'],
            ['SP-YZA-044', 'pendingin', 'Timing Belt Kit + Tensioner', 'Gates', 620000, 820000, 'set'],
            ['SP-YZA-045', 'pendingin', 'Thermostat Mesin + Gasket', 'Tama Japan', 135000, 190000, 'pcs'],
            ['SP-YZA-046', 'pendingin', 'Water Pump Mesin', 'Aisin', 410000, 545000, 'pcs'],

            // Kopling & Transmisi (4 items)
            ['SP-YZA-047', 'transmisi', 'Kampas Kopling / Clutch Disc', 'Exedy', 490000, 650000, 'pcs'],
            ['SP-YZA-048', 'transmisi', 'Dekrup / Clutch Cover', 'Exedy', 520000, 690000, 'pcs'],
            ['SP-YZA-049', 'transmisi', 'Release Bearing Kopling', 'NSK Japan', 165000, 225000, 'pcs'],
            ['SP-YZA-050', 'transmisi', 'CV Joint Outer + Boot As Roda', 'LKS', 340000, 455000, 'pcs'],

            // Tambahan Oli Mesin Sport & Standar (8 items)
            ['SP-YZA-051', 'oli', 'Liqui Moly Synthoil Race Tech GT1 10W-60 (1L)', 'Liqui Moly', 265000, 345000, 'botol'],
            ['SP-YZA-052', 'oli', 'Castrol EDGE Supercar 10W-60 Full Synthetic (1L)', 'Castrol', 250000, 325000, 'botol'],
            ['SP-YZA-053', 'oli', 'Idemitsu IFG 7 Full Synthetic 0W-20 (4L)', 'Idemitsu', 385000, 495000, 'galon'],
            ['SP-YZA-054', 'oli', 'Idemitsu IRG 7 Racing Ester 5W-40 (1L)', 'Idemitsu', 195000, 260000, 'botol'],
            ['SP-YZA-055', 'oli', 'Pertamina Fastron Gold Full Synthetic 5W-30 (4L)', 'Pertamina', 350000, 460000, 'galon'],
            ['SP-YZA-056', 'oli', 'Pertamina Fastron Techno 10W-40 (4L)', 'Pertamina', 245000, 320000, 'galon'],
            ['SP-YZA-057', 'oli', 'Shell Helix Eco Fully Synthetic 0W-20 (3.5L)', 'Shell', 290000, 380000, 'galon'],
            ['SP-YZA-058', 'oli', 'Total Quartz 9000 Future GF-6 0W-20 (4L)', 'TotalEnergies', 360000, 470000, 'galon'],

            // Tambahan Oli Gardan / Differential Oil (6 items)
            ['SP-YZA-059', 'oli', 'Shell Spirax S5 ATE 75W-90 Oli Gardan Synthetic (1L)', 'Shell', 175000, 235000, 'botol'],
            ['SP-YZA-060', 'oli', 'Castrol Transmax Axle EPX 80W-90 Oli Gardan (1L)', 'Castrol', 110000, 150000, 'botol'],
            ['SP-YZA-061', 'oli', 'Castrol Transmax Limited Slip 75W-140 Oli Gardan Sport (1L)', 'Castrol', 275000, 360000, 'botol'],
            ['SP-YZA-062', 'oli', 'Liqui Moly Hypoid Gear Oil GL-5 SAE 85W-90 (1L)', 'Liqui Moly', 155000, 210000, 'botol'],
            ['SP-YZA-063', 'oli', 'Eneos Gear Oil GL-5 80W-90 Oli Gardan (1L)', 'Eneos', 98000, 135000, 'botol'],
            ['SP-YZA-064', 'oli', 'Pertamina Rored EPA SAE 90 GL-4 Oli Gardan (1L)', 'Pertamina', 68000, 98000, 'botol'],
        ];

        foreach ($items as $idx => [$code, $catKey, $name, $brand, $buy, $sell, $unit]) {
            $supplier = $suppliers[$idx % count($suppliers)];

            $part = Sparepart::updateOrCreate(
                ['kode_sparepart' => $code],
                [
                    'id_kategori' => $categories[$catKey]->id_kategori,
                    'id_supplier' => $supplier->id_supplier,
                    'nama_sparepart' => $name,
                    'merk' => $brand,
                    'harga_beli' => $buy,
                    'harga_jual' => $sell,
                    'stok' => 20,
                    'stok_minimum' => 5,
                    'satuan' => $unit,
                    'status' => 'aktif',
                ]
            );

            if (!$part->fitments()->whereNull('merk_kendaraan')->whereNull('model_kendaraan')->exists()) {
                $part->fitments()->create([
                    'merk_kendaraan' => null,
                    'model_kendaraan' => null,
                    'status_verifikasi' => 'verified_fit',
                    'verified_at' => now(),
                ]);
            }
        }
    }
}
