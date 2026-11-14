<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE promo_member DROP CONSTRAINT IF EXISTS promo_member_berlaku_untuk_check;");
            DB::statement("ALTER TABLE promo_member ADD CONSTRAINT promo_member_berlaku_untuk_check CHECK (berlaku_untuk IN ('semua','servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in'));");
        } else {
            DB::statement("ALTER TABLE promo_member MODIFY berlaku_untuk ENUM('semua','servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in') NOT NULL DEFAULT 'semua'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE promo_member DROP CONSTRAINT IF EXISTS promo_member_berlaku_untuk_check;");
            DB::statement("ALTER TABLE promo_member ADD CONSTRAINT promo_member_berlaku_untuk_check CHECK (berlaku_untuk IN ('semua','servis','cuci_mobil','penjualan_sparepart','penjualan_mobil'));");
        } else {
            DB::statement("ALTER TABLE promo_member MODIFY berlaku_untuk ENUM('semua','servis','cuci_mobil','penjualan_sparepart','penjualan_mobil') NOT NULL DEFAULT 'semua'");
        }
    }
};
