<?php

namespace Database\Seeders;

use App\Models\KategoriSampah;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
        ]);

        foreach (['Besi', 'Plastik', 'Kertas', 'Kardus'] as $nama) {
            KategoriSampah::firstOrCreate(
                ['nama_normalized' => mb_strtolower($nama)],
                ['nama' => $nama, 'faktor_emisi_id' => null]
            );
        }
    }
}
