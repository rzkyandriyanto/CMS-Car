<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('booking_cuci', 'id_promo')) {
            Schema::table('booking_cuci', function (Blueprint $table): void {
                $table->unsignedBigInteger('id_promo')->nullable()->after('id_paket')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking_cuci', 'id_promo')) {
            Schema::table('booking_cuci', function (Blueprint $table): void {
                $table->dropColumn('id_promo');
            });
        }
    }
};
