<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaktorEmisi extends Model
{
    use SoftDeletes;

    protected $table = 'faktor_emisi';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'faktor_kgco2e_per_kg' => 'decimal:6',
            'tanggal_berlaku' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function kategori()
    {
        return $this->hasMany(KategoriSampah::class);
    }
}
