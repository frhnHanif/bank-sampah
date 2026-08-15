<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    protected $table = 'stok';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['total_berat_kg' => 'decimal:2'];
    }

    public function jenisSampah()
    {
        return $this->belongsTo(JenisSampah::class)->withTrashed();
    }
}
