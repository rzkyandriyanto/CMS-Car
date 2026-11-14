<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('promo_member', 'points_cost')) {
            Schema::table('promo_member', function (Blueprint $table) {
                $table->unsignedInteger('points_cost')->default(0)->after('member_only');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE riwayat_poin DROP CONSTRAINT IF EXISTS riwayat_poin_jenis_check");
            DB::statement("ALTER TABLE riwayat_poin ADD CONSTRAINT riwayat_poin_jenis_check CHECK (jenis IN ('perolehan', 'pembalikan', 'penukaran'))");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('promo_member', 'points_cost')) {
            Schema::table('promo_member', function (Blueprint $table) {
                $table->dropColumn('points_cost');
            });
        }
    }
};

