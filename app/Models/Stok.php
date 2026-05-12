<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    protected $table = 'stok';
    protected $guarded = ['id'];

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class);
    }
}
