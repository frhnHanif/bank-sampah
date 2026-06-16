<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_sistem', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique();
            $table->string('label');
            $table->string('nilai');
            $table->string('satuan')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // Seed default konstanta ekuivalen CO2
        DB::table('pengaturan_sistem')->insert([
            [
                'kunci' => 'co2_per_pohon',
                'label' => 'Ekuivalen Pohon',
                'nilai' => '11',
                'satuan' => 'kg CO₂/pohon/tahun',
                'keterangan' => 'Rata-rata CO₂ yang diserap satu pohon per tahun.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kunci' => 'co2_per_km_mobil',
                'label' => 'Ekuivalen Mobil',
                'nilai' => '0.167',
                'satuan' => 'kg CO₂/km',
                'keterangan' => 'Rata-rata emisi CO₂ mobil per kilometer.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kunci' => 'co2_per_bulan_listrik',
                'label' => 'Ekuivalen Listrik',
                'nilai' => '141',
                'satuan' => 'kg CO₂/bulan/rumah',
                'keterangan' => 'Rata-rata emisi CO₂ konsumsi listrik satu rumah tangga per bulan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kunci' => 'faktor_pertumbuhan_target',
                'label' => 'Faktor Pertumbuhan Target',
                'nilai' => '1.1',
                'satuan' => 'kali',
                'keterangan' => 'Faktor pengali rata-rata 3 bulan sebelumnya untuk menentukan target bulanan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kunci' => 'admin_pin',
                'label' => 'PIN Admin',
                'nilai' => '123456',
                'satuan' => null,
                'keterangan' => 'PIN untuk mengakses halaman konfigurasi sistem.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
    }
};
