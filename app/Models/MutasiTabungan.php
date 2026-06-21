<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiTabungan extends Model
{
    protected $table = 'mutasi_tabungan';
    protected $guarded = ['id'];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function transaksiSetor()
    {
        return $this->belongsTo(TransaksiSetor::class, 'ref_transaksi_setor_id');
    }
}