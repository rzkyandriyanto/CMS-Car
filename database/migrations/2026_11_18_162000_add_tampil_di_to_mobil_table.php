<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mobil', 'tampil_di')) {
            Schema::table('mobil', function (Blueprint $table): void {
                $table->string('tampil_di', 30)->default('keduanya')->after('status')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mobil', 'tampil_di')) {
            Schema::table('mobil', function (Blueprint $table): void {
                $table->dropColumn('tampil_di');
            });
        }
    }
};
