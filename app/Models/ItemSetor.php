<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSetor extends Model
{
    protected $table = 'item_setor';
    protected $guarded = ['id'];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiSetor::class, 'transaksi_setor_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class);
    }
}
