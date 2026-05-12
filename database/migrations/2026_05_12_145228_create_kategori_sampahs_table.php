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
    Schema::create('kategori_sampah', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->decimal('harga_beli_per_kg', 15, 2); 
        $table->decimal('faktor_emisi', 8, 4); // kg CO2/kg sampah
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_sampahs');
    }
};
