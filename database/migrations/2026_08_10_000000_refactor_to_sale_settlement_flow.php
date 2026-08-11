<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faktor_emisi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_material');
            $table->decimal('faktor_kgco2e_per_kg', 16, 6);
            $table->text('sumber')->nullable();
            $table->string('versi')->nullable();
            $table->date('tanggal_berlaku')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('kategori_sampah', function (Blueprint $table) {
            // Kolom legacy dipertahankan nullable hanya untuk rollback/histori.
            // Flow baru tidak pernah membaca atau mengisinya.
            $table->decimal('harga_beli_per_kg', 15, 2)->nullable()->change();
            $table->decimal('faktor_emisi', 8, 4)->nullable()->change();
            $table->string('nama_normalized')->nullable()->after('nama')->index();
            $table->foreignId('faktor_emisi_id')->nullable()->after('nama_normalized')
                ->constrained('faktor_emisi')->nullOnDelete();
        });

        $materialGroup = static function (string $name): string {
            $name = Str::lower($name);

            return match (true) {
                str_contains($name, 'kaca') => 'Kaca',
                str_contains($name, 'kaleng') => 'Kaleng',
                str_contains($name, 'kertas') && preg_match('/kardus|karton|tebal/', $name) => 'Kertas Tebal',
                str_contains($name, 'kertas') => 'Kertas Tipis',
                str_contains($name, 'plastik') && preg_match('/tumbler|botol|ember|galon|tebal/', $name) => 'Plastik Tebal',
                str_contains($name, 'plastik') => 'Plastik Tipis',
                default => Str::title($name),
            };
        };

        DB::table('kategori_sampah')->orderBy('id')->get()->each(function ($kategori) use ($materialGroup): void {
            $normalized = Str::lower(preg_replace('/\s+/u', ' ', trim($kategori->nama)) ?? trim($kategori->nama));
            $factorId = DB::table('faktor_emisi')->insertGetId([
                'nama_material' => $materialGroup($kategori->nama),
                'faktor_kgco2e_per_kg' => $kategori->faktor_emisi,
                'sumber' => 'Migrasi data lama - sumber belum didokumentasikan',
                'versi' => null,
                'catatan' => 'Nilai dipreservasi persis dari kategori_sampah.faktor_emisi.',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('kategori_sampah')->where('id', $kategori->id)->update([
                'nama_normalized' => $normalized,
                'faktor_emisi_id' => $factorId,
            ]);
        });

        Schema::table('transaksi_setor', function (Blueprint $table) {
            $table->unsignedTinyInteger('flow_version')->default(1)->after('id')->index();
            $table->index('tanggal');
        });

        Schema::table('item_setor', function (Blueprint $table) {
            $table->decimal('berat_teralokasi_kg', 10, 2)->default(0)->after('berat_kg');
            $table->string('status', 16)->default('PENDING')->after('berat_teralokasi_kg');
            $table->boolean('is_legacy')->default(false)->after('status');
            $table->index(['kategori_id', 'status']);
            $table->index(['kategori_id', 'is_legacy']);
        });

        DB::table('item_setor')->update([
            'berat_teralokasi_kg' => DB::raw('berat_kg'),
            'status' => 'SETTLED',
            'is_legacy' => true,
        ]);

        Schema::create('legacy_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori_sampah')->restrictOnDelete();
            $table->timestamp('cutover_at');
            $table->decimal('berat_awal_kg', 10, 2);
            $table->decimal('berat_tersisa_kg', 10, 2);
            $table->decimal('cost_basis_per_kg', 15, 2)->default(0);
            $table->decimal('total_cost_basis_awal', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['kategori_id', 'berat_tersisa_kg']);
        });

        DB::table('stok')->where('total_berat_kg', '>', 0)->orderBy('id')->get()->each(function ($stok): void {
            $aggregate = DB::table('item_setor')->where('kategori_id', $stok->kategori_id)
                ->selectRaw('SUM(nilai) AS nilai, SUM(berat_kg) AS berat')->first();
            $costPerKg = ($aggregate && (float) $aggregate->berat > 0)
                ? round((float) $aggregate->nilai / (float) $aggregate->berat, 2)
                : 0;

            DB::table('legacy_inventory')->insert([
                'kategori_id' => $stok->kategori_id,
                'cutover_at' => now(),
                'berat_awal_kg' => $stok->total_berat_kg,
                'berat_tersisa_kg' => $stok->total_berat_kg,
                'cost_basis_per_kg' => $costPerKg,
                'total_cost_basis_awal' => round((float) $stok->total_berat_kg * $costPerKg, 2),
                'catatan' => $costPerKg > 0
                    ? 'Opening stock cutover; cost basis memakai weighted average histori flow lama.'
                    : 'Opening stock cutover; cost basis 0 karena basis harga historis tidak tersedia.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('transaksi_jual', function (Blueprint $table) {
            $table->unsignedTinyInteger('flow_version')->default(1)->after('id');
            $table->decimal('total_hak_nasabah', 15, 2)->default(0)->after('total_nilai');
            $table->decimal('total_cost_basis', 15, 2)->default(0)->after('total_hak_nasabah');
            $table->decimal('total_margin_kotor', 15, 2)->default(0)->after('total_cost_basis');
            $table->decimal('total_co2_terealisasi', 16, 6)->nullable()->after('total_margin_kotor');
            $table->index(['tanggal', 'flow_version']);
        });

        Schema::table('item_jual', function (Blueprint $table) {
            $table->decimal('harga_nasabah_per_kg', 15, 2)->nullable()->after('harga_jual_per_kg');
            $table->decimal('total_hak_nasabah', 15, 2)->default(0)->after('total_nilai');
            $table->decimal('total_cost_basis', 15, 2)->default(0)->after('total_hak_nasabah');
            $table->decimal('margin_kotor', 15, 2)->default(0)->after('total_cost_basis');
            $table->decimal('total_co2_terealisasi', 16, 6)->nullable()->after('margin_kotor');
        });

        Schema::create('alokasi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_jual_id')->constrained('item_jual')->cascadeOnDelete();
            $table->string('sumber_tipe', 24);
            $table->foreignId('item_setor_id')->nullable()->constrained('item_setor')->restrictOnDelete();
            $table->foreignId('legacy_inventory_id')->nullable()->constrained('legacy_inventory')->restrictOnDelete();
            $table->decimal('berat_kg', 10, 2);
            $table->decimal('harga_pengepul_per_kg', 15, 2);
            $table->decimal('harga_nasabah_per_kg', 15, 2)->nullable();
            $table->decimal('nilai_penjualan', 15, 2);
            $table->decimal('nilai_hak_nasabah', 15, 2)->default(0);
            $table->decimal('cost_basis', 15, 2)->default(0);
            $table->decimal('margin_kotor', 15, 2);
            $table->foreignId('faktor_emisi_id')->nullable()->constrained('faktor_emisi')->nullOnDelete();
            $table->decimal('faktor_emisi_snapshot', 16, 6)->nullable();
            $table->decimal('co2_terealisasi', 16, 6)->nullable();
            $table->string('co2_status', 16)->default('PENDING');
            $table->timestamps();
            $table->index('item_setor_id');
            $table->index('item_jual_id');
            $table->index('co2_status');
        });

        Schema::table('mutasi_tabungan', function (Blueprint $table) {
            $table->foreignId('ref_transaksi_jual_id')->nullable()
                ->constrained('transaksi_jual')->nullOnDelete();
        });

        Schema::table('mutasi_kas', function (Blueprint $table) {
            $table->foreignId('ref_transaksi_jual_id')->nullable()
                ->constrained('transaksi_jual')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_kas', fn (Blueprint $table) => $table->dropConstrainedForeignId('ref_transaksi_jual_id'));
        Schema::table('mutasi_tabungan', fn (Blueprint $table) => $table->dropConstrainedForeignId('ref_transaksi_jual_id'));
        Schema::dropIfExists('alokasi_penjualan');
        Schema::table('item_jual', function (Blueprint $table) {
            $table->dropColumn(['harga_nasabah_per_kg', 'total_hak_nasabah', 'total_cost_basis', 'margin_kotor', 'total_co2_terealisasi']);
        });
        Schema::table('transaksi_jual', function (Blueprint $table) {
            $table->dropColumn(['flow_version', 'total_hak_nasabah', 'total_cost_basis', 'total_margin_kotor', 'total_co2_terealisasi']);
        });
        Schema::dropIfExists('legacy_inventory');
        Schema::table('item_setor', function (Blueprint $table) {
            $table->dropColumn(['berat_teralokasi_kg', 'status', 'is_legacy']);
        });
        Schema::table('transaksi_setor', fn (Blueprint $table) => $table->dropColumn('flow_version'));
        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faktor_emisi_id');
            $table->dropColumn('nama_normalized');
        });
        DB::table('kategori_sampah')->whereNull('harga_beli_per_kg')->update(['harga_beli_per_kg' => 0]);
        DB::table('kategori_sampah')->whereNull('faktor_emisi')->update(['faktor_emisi' => 0]);
        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->decimal('harga_beli_per_kg', 15, 2)->nullable(false)->change();
            $table->decimal('faktor_emisi', 8, 4)->nullable(false)->change();
        });
        Schema::dropIfExists('faktor_emisi');
    }
};
