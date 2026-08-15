<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutasi_tabungan', function (Blueprint $table) {
            $table->foreignId('ref_transaksi_jual_id')->nullable()->constrained('transaksi_jual')->nullOnDelete();
        });

        Schema::create('alokasi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_jual_id')->constrained('item_jual')->cascadeOnDelete();
            $table->foreignId('item_setor_id')->constrained('item_setor')->restrictOnDelete();
            $table->decimal('berat_kg', 10, 2);
            $table->decimal('harga_pengepul_per_kg', 15, 2);
            $table->decimal('harga_nasabah_per_kg', 15, 2);
            $table->decimal('nilai_penjualan', 15, 2);
            $table->decimal('nilai_hak_nasabah', 15, 2);
            $table->decimal('cost_basis', 15, 2);
            $table->decimal('margin_kotor', 15, 2);
            $table->decimal('faktor_emisi_snapshot', 16, 6)->nullable();
            $table->text('sumber_faktor_emisi_snapshot')->nullable();
            $table->string('versi_faktor_emisi_snapshot')->nullable();
            $table->decimal('co2_terealisasi', 16, 6)->nullable();
            $table->string('co2_status', 16)->default('PENDING');
            $table->timestamps();
            $table->index('co2_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_penjualan');
        Schema::table('mutasi_tabungan', fn (Blueprint $table) => $table->dropConstrainedForeignId('ref_transaksi_jual_id'));
    }
};
