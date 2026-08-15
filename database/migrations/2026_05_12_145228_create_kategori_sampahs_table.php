<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_material', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_normalized')->unique();
            $table->decimal('faktor_emisi_kgco2e_per_kg', 16, 6)->nullable();
            $table->text('sumber_faktor_emisi')->nullable();
            $table->string('versi_faktor_emisi')->nullable();
            $table->date('tanggal_berlaku_faktor_emisi')->nullable();
            $table->text('catatan_faktor_emisi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('jenis_sampah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_material_id')->constrained('kelompok_material')->restrictOnDelete();
            $table->string('nama');
            $table->string('nama_normalized')->unique();
            $table->string('satuan_pencatatan', 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_sampah');
        Schema::dropIfExists('kelompok_material');
    }
};
