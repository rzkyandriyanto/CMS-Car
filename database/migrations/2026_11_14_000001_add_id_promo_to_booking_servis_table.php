<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('booking_servis', 'id_promo')) {
            Schema::table('booking_servis', function (Blueprint $table) {
                $table->unsignedBigInteger('id_promo')->nullable()->after('id_preferred_petugas');
                $table->foreign('id_promo')
                    ->references('id_promo')
                    ->on('promo_member')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking_servis', 'id_promo')) {
            Schema::table('booking_servis', function (Blueprint $table) {
                $table->dropForeign(['id_promo']);
                $table->dropColumn('id_promo');
            });
        }
    }
};
