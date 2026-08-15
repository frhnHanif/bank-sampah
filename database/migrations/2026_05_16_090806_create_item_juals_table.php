<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_jual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_jual_id')->constrained('transaksi_jual')->cascadeOnDelete();
            $table->foreignId('jenis_sampah_id')->constrained('jenis_sampah')->restrictOnDelete();
            $table->decimal('berat_kg', 10, 2);
            $table->decimal('harga_jual_per_kg', 15, 2);
            $table->decimal('harga_nasabah_per_kg', 15, 2);
            $table->decimal('total_nilai', 15, 2);
            $table->decimal('total_hak_nasabah', 15, 2)->default(0);
            $table->decimal('total_cost_basis', 15, 2)->default(0);
            $table->decimal('margin_kotor', 15, 2)->default(0);
            $table->decimal('total_co2_terealisasi', 16, 6)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_jual');
    }
};
