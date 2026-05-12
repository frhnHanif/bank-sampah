<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiSetor extends Model
{
    protected $table = 'transaksi_setor';
    protected $guarded = ['id'];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function items()
    {
        return $this->hasMany(ItemSetor::class);
    }
}
