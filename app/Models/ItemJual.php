<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemJual extends Model
{
    protected $table = 'item_jual';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'berat_kg' => 'decimal:2',
            'harga_jual_per_kg' => 'decimal:2',
            'harga_nasabah_per_kg' => 'decimal:2',
            'total_nilai' => 'decimal:2',
            'total_hak_nasabah' => 'decimal:2',
            'total_cost_basis' => 'decimal:2',
            'margin_kotor' => 'decimal:2',
            'total_co2_terealisasi' => 'decimal:6',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(TransaksiJual::class, 'transaksi_jual_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class)->withTrashed();
    }

    public function alokasi()
    {
        return $this->hasMany(AlokasiPenjualan::class);
    }
}
