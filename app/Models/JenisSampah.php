<?php

namespace App\Models;

use App\Enums\UnitPencatatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisSampah extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_sampah';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['satuan_pencatatan' => UnitPencatatan::class, 'is_active' => 'boolean'];
    }

    public function kelompokMaterial()
    {
        return $this->belongsTo(KelompokMaterial::class);
    }

    public function stok()
    {
        return $this->hasOne(Stok::class);
    }

    public function itemSetor()
    {
        return $this->hasMany(ItemSetor::class);
    }

    public function itemJual()
    {
        return $this->hasMany(ItemJual::class);
    }
}
