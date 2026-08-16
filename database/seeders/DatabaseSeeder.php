<?php

namespace Database\Seeders;

use App\Models\JenisSampah;
use App\Models\KelompokMaterial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@ngudiawilujeng.com',
            'password' => Hash::make('SeringLupaPassword'),
        ]);
        $masters = [
            'Plastik' => [['Botol Plastik Minuman', 'KG'], ['Galon Plastik', 'PCS']],
            'Kertas/Karton' => [['Marga', 'KG'], ['Dus Coklat', 'KG'], ['Kardus', 'KG'], ['Kertas', 'KG']],
            'Logam' => [['Kaleng', 'PCS']], 'Kaca' => [], 'Elektronik' => [['Kipas Angin', 'PCS']], 'Lainnya' => [],
        ];
        foreach ($masters as $groupName => $types) {
            $group = KelompokMaterial::create(['nama' => $groupName, 'nama_normalized' => Str::lower($groupName),
                'faktor_emisi_kgco2e_per_kg' => null, 'is_active' => true]);
            foreach ($types as [$name,$unit]) {
                JenisSampah::create(['kelompok_material_id' => $group->id, 'nama' => $name,
                    'nama_normalized' => Str::lower($name), 'satuan_pencatatan' => $unit, 'is_active' => true]);
            }
        }

        $this->call(EmissionFactorSeeder::class);
    }
}
