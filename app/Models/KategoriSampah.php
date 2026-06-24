<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriSampah extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_sampah';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function stok()
    {
        return $this->hasOne(Stok::class, 'kategori_id');
    }

    public function itemSetor()
    {
        return $this->hasMany(ItemSetor::class, 'kategori_id');
    }

    public function itemJual()
    {
        return $this->hasMany(ItemJual::class, 'kategori_id');
    }
}