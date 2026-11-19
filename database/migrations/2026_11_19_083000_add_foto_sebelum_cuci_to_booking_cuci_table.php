<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('booking_cuci', 'foto_sebelum_cuci')) {
            Schema::table('booking_cuci', function (Blueprint $table): void {
                $table->string('foto_sebelum_cuci', 255)->nullable()->after('catatan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking_cuci', 'foto_sebelum_cuci')) {
            Schema::table('booking_cuci', function (Blueprint $table): void {
                $table->dropColumn('foto_sebelum_cuci');
            });
        }
    }
};
