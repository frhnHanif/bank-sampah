<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_setor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_setor_id')->constrained('transaksi_setor')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori_sampah')->restrictOnDelete();
            $table->decimal('berat_kg', 8, 2);
            $table->decimal('nilai', 15, 2); // berat x harga_beli
            $table->decimal('co2', 10, 4);   // berat x faktor_emisi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_setors');
    }
};
