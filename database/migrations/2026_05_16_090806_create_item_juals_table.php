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
            $table->foreignId('kategori_id')->constrained('kategori_sampah')->restrictOnDelete();
            $table->decimal('berat_kg', 10, 2);
            $table->decimal('harga_jual_per_kg', 15, 2);
            $table->decimal('total_nilai', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_jual');
    }
};