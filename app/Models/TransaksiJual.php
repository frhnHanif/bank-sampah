<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiJual extends Model
{
    protected $table = 'transaksi_jual';
    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(ItemJual::class, 'transaksi_jual_id');
    }
}