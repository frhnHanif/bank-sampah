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
        $materialGroup = static function (string $name): string {
            $name = preg_replace('/^faktor\s+(?:legacy|awal)\s*-\s*/iu', '', trim($name)) ?? trim($name);
            $normalized = Str::lower($name);

            return match (true) {
                str_contains($normalized, 'kaca') => 'Kaca',
                str_contains($normalized, 'kaleng') => 'Kaleng',
                str_contains($normalized, 'kertas') && preg_match('/kardus|karton|tebal/', $normalized) => 'Kertas Tebal',
                str_contains($normalized, 'kertas') => 'Kertas Tipis',
                str_contains($normalized, 'plastik') && preg_match('/tumbler|botol|ember|galon|tebal/', $normalized) => 'Plastik Tebal',
                str_contains($normalized, 'plastik') => 'Plastik Tipis',
                default => Str::title($name),
            };
        };

        DB::table('faktor_emisi')->get(['id', 'nama_material', 'versi', 'faktor_kgco2e_per_kg'])->each(function ($factor) use ($materialGroup): void {
            DB::table('faktor_emisi')->where('id', $factor->id)->update([
                'nama_material' => $materialGroup($factor->nama_material),
                'faktor_kgco2e_per_kg' => round((float) $factor->faktor_kgco2e_per_kg, 3),
                'versi' => str_contains(Str::lower((string) $factor->versi), 'legacy') ? null : $factor->versi,
                'updated_at' => now(),
            ]);
        });

        Schema::table('faktor_emisi', function (Blueprint $table): void {
            $table->decimal('faktor_kgco2e_per_kg', 16, 3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('faktor_emisi', function (Blueprint $table): void {
            $table->decimal('faktor_kgco2e_per_kg', 16, 6)->change();
        });

        // Perubahan label tidak dikembalikan agar istilah internal tidak tampil lagi.
    }
};
