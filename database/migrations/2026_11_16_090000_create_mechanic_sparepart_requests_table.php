<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mechanic_sparepart_requests', function (Blueprint $table): void {
            $table->id('id_request');
            $table->unsignedBigInteger('id_servis');
            $table->unsignedBigInteger('id_petugas')->nullable();
            $table->string('nama_sparepart', 160);
            $table->string('spesifikasi', 160)->nullable();
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('satuan', 30)->default('pcs');
            $table->text('catatan_kebutuhan')->nullable();
            $table->string('status', 30)->default('menunggu_gudang');
            $table->timestamps();

            $table->index('id_servis', 'idx_mech_sp_req_servis');
            $table->index('id_petugas', 'idx_mech_sp_req_petugas');
            $table->foreign('id_servis')
                ->references('id_servis')
                ->on('servis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('id_petugas')
                ->references('id_petugas')
                ->on('petugas')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mechanic_sparepart_requests');
    }
};
