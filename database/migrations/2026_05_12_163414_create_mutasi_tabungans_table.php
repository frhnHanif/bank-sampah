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
        // Pastikan parameter pertamanya 'mutasi_tabungan'
        Schema::create('mutasi_tabungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('nasabah')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('jenis', ['kredit', 'debit']);
            $table->decimal('jumlah', 15, 2);
            $table->string('keterangan');
            $table->foreignId('ref_transaksi_setor_id')->nullable()->constrained('transaksi_setor')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_tabungan');
    }
};
