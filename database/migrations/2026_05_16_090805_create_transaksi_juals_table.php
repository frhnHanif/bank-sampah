<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_jual', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->decimal('total_nilai', 15, 2)->default(0);
            $table->decimal('total_hak_nasabah', 15, 2)->default(0);
            $table->decimal('total_cost_basis', 15, 2)->default(0);
            $table->decimal('total_margin_kotor', 15, 2)->default(0);
            $table->decimal('total_co2_terealisasi', 16, 6)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_jual');
    }
};
