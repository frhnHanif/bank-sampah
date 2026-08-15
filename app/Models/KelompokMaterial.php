<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelompokMaterial extends Model
{
    use SoftDeletes;

    protected $table = 'kelompok_material';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'faktor_emisi_kgco2e_per_kg' => 'decimal:6',
            'tanggal_berlaku_faktor_emisi' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function jenisSampah()
    {
        return $this->hasMany(JenisSampah::class);
    }
}
