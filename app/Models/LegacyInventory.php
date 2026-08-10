<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyInventory extends Model
{
    protected $table = 'legacy_inventory';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cutover_at' => 'datetime',
            'berat_awal_kg' => 'decimal:2',
            'berat_tersisa_kg' => 'decimal:2',
            'cost_basis_per_kg' => 'decimal:2',
            'total_cost_basis_awal' => 'decimal:2',
        ];
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class);
    }

    public function alokasi()
    {
        return $this->hasMany(AlokasiPenjualan::class);
    }
}
