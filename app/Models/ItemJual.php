<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemJual extends Model
{
    protected $table = 'item_jual';
    protected $guarded = ['id'];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiJual::class, 'transaksi_jual_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class);
    }
}