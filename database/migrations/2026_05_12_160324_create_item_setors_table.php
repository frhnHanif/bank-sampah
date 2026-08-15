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
            $table->foreignId('jenis_sampah_id')->constrained('jenis_sampah')->restrictOnDelete();
            $table->unsignedInteger('jumlah_pcs')->nullable();
            $table->decimal('berat_kg', 10, 2);
            $table->decimal('berat_teralokasi_kg', 10, 2)->default(0);
            $table->string('status', 16)->default('PENDING');
            $table->timestamps();
            $table->index(['jenis_sampah_id', 'status']);
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
