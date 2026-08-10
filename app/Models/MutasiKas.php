<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiKas extends Model
{
    protected $table = 'mutasi_kas';

    protected $guarded = ['id'];

    public function transaksiJual()
    {
        return $this->belongsTo(TransaksiJual::class, 'ref_transaksi_jual_id');
    }
}
