<?php

namespace App\Console\Commands;

use App\Models\PromoMember;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class YzaPromoDemoCommand extends Command
{
    protected $signature = 'yza:promo-demo';
    protected $description = 'Membuat atau memperbarui katalog demo promo YZA Member';
    public function handle(): int
    {
        $start = Carbon::now('Asia/Jakarta')->startOfDay(); $end = $start->copy()->addYear();
        $items = [
            ['kode_promo' => 'YZA-SRV50', 'nama' => 'Promo Servis 50%', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 50, 'berlaku_untuk' => 'servis', 'points_cost' => 250, 'deskripsi' => 'Diskon 50% biaya jasa servis berkala resmi & tune up YZA.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 250 YZA Points. Berlaku untuk booking servis kendaraan member.'],
            ['kode_promo' => 'YZA-TUNEUP', 'nama' => 'Promo Tune Up Mesin', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 50, 'berlaku_untuk' => 'servis', 'points_cost' => 250, 'deskripsi' => 'Paket tune up mesin komprehensif, ganti oli & filter oli resmi YZA.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 250 YZA Points. Berlaku untuk layanan tune up mesin kendaraan member.'],
            ['kode_promo' => 'YZA-WASHVIP', 'nama' => 'Voucher Car Wash Express', 'jenis_benefit' => 'nominal', 'nilai_benefit' => 75000, 'berlaku_untuk' => 'cuci_mobil', 'points_cost' => 100, 'deskripsi' => 'Gratis cuci hidrolik premium & detailing wax exterior.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 100 YZA Points. Berlaku untuk booking car wash YZA.'],
            ['kode_promo' => 'YZA-BRAKES', 'nama' => 'Promo Ganti Kampas Rem', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 30, 'berlaku_untuk' => 'servis', 'points_cost' => 200, 'deskripsi' => 'Diskon 30% penggantian kampas rem depan/belakang & inspeksi sistem pengereman.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 200 YZA Points. Berlaku untuk penggantian kampas rem member.'],
            ['kode_promo' => 'YZA-COAT30', 'nama' => 'Promo Nano Coating 30%', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 30, 'berlaku_untuk' => 'servis', 'points_cost' => 200, 'deskripsi' => 'Diskon 30% proteksi cat nano ceramic & interior detail.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 200 YZA Points. Berlaku untuk layanan detailing & coating.'],
            ['kode_promo' => 'YZA-TRADE5JT', 'nama' => 'Voucher Trade-In Rp 5.000.000', 'jenis_benefit' => 'nominal', 'nilai_benefit' => 5000000, 'berlaku_untuk' => 'trade_in', 'points_cost' => 500, 'deskripsi' => 'Ekstra subsidi tukar tambah unit lama ke mobil impian.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 500 YZA Points. Berlaku untuk transaksi trade-in mobil.'],
            ['kode_promo' => 'YZA-SERVICE10', 'nama' => 'Promo Servis Member', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 10, 'berlaku_untuk' => 'servis', 'points_cost' => 0, 'deskripsi' => 'Benefit katalog servis untuk YZA Member.'],
            ['kode_promo' => 'YZA-CARWASH20', 'nama' => 'Promo Car Wash Member', 'jenis_benefit' => 'nominal', 'nilai_benefit' => 20000, 'berlaku_untuk' => 'cuci_mobil', 'points_cost' => 0, 'deskripsi' => 'Gratis cuci hidrolik berkala untuk YZA Member.'],
            ['kode_promo' => 'YZA-PARTS5', 'nama' => 'Promo Spare Part Member', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 5, 'berlaku_untuk' => 'penjualan_sparepart', 'points_cost' => 0, 'deskripsi' => 'Benefit katalog spare part untuk YZA Member.'],
            ['kode_promo' => 'YZA-SPOORING', 'nama' => 'Promo Spooring & Balancing', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 40, 'berlaku_untuk' => 'servis', 'points_cost' => 150, 'deskripsi' => 'Diskon 40% 3D computer spooring & wheel balancing 4 roda.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 150 YZA Points.'],
            ['kode_promo' => 'YZA-ACCARE', 'nama' => 'Servis & Evaporator AC', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 35, 'berlaku_untuk' => 'servis', 'points_cost' => 200, 'deskripsi' => 'Diskon 35% deep cleaning evaporator, freon & cabin filter replacement.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 200 YZA Points.'],
            ['kode_promo' => 'YZA-TIRES15', 'nama' => 'Voucher Ban Michelin / Pirelli', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 15, 'berlaku_untuk' => 'penjualan_sparepart', 'points_cost' => 300, 'deskripsi' => 'Diskon 15% pembelian ban performa tinggi Michelin atau Pirelli.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 300 YZA Points.'],
            ['kode_promo' => 'YZA-TESTDRIVE', 'nama' => 'Voucher Test Drive VIP Supercar', 'jenis_benefit' => 'nominal', 'nilai_benefit' => 500000, 'berlaku_untuk' => 'penjualan_mobil', 'points_cost' => 100, 'deskripsi' => 'Akses prioritas test drive supercar impian didampingi instruktur resmi YZA.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 100 YZA Points. Berlaku untuk booking test drive mobil sport pilihan.'],
            ['kode_promo' => 'YZA-INTERIOR', 'nama' => 'Interior Detailing & Sanitasi Ozon', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 50, 'berlaku_untuk' => 'cuci_mobil', 'points_cost' => 250, 'deskripsi' => 'Diskon 50% pembersihan jok kulit, plafon & sterilisasi kabin ozon.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 250 YZA Points.'],
            ['kode_promo' => 'YZA-DYNOTEST', 'nama' => 'Dyno Test Run & ECU Scan', 'jenis_benefit' => 'nominal', 'nilai_benefit' => 750000, 'berlaku_untuk' => 'servis', 'points_cost' => 350, 'deskripsi' => 'Free 1x uji tenaga dynometer & full diagnostic scanning ECU.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 350 YZA Points.'],
            ['kode_promo' => 'YZA-VKMDETAIL', 'nama' => 'Kaca Film V-Kool / 3M Eksklusif', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 30, 'berlaku_untuk' => 'penjualan_sparepart', 'points_cost' => 300, 'deskripsi' => 'Diskon 30% pemasangan kaca film tolak panas grade tertinggi.', 'syarat_ketentuan' => 'Kupon dapat ditukarkan dengan 300 YZA Points.'],
        ];
        foreach ($items as $item) PromoMember::updateOrCreate(['kode_promo' => $item['kode_promo']], array_merge([
            'minimum_transaksi' => 0,
            'maksimum_benefit' => null,
            'mulai_at' => $start,
            'selesai_at' => $end,
            'syarat_ketentuan' => 'Promo tersedia sebagai benefit katalog YZA Member.',
            'status' => 'aktif',
            'member_only' => true,
        ], $item));
        $this->info('Katalog promo demo diperbarui secara idempotent.');
        return self::SUCCESS;
    }
}
