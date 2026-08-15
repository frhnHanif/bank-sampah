<?php

namespace App\Models;

use App\Enums\Co2Status;
use Illuminate\Database\Eloquent\Model;

class AlokasiPenjualan extends Model
{
    protected $table = 'alokasi_penjualan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'co2_status' => Co2Status::class,
            'berat_kg' => 'decimal:2',
            'harga_pengepul_per_kg' => 'decimal:2',
            'harga_nasabah_per_kg' => 'decimal:2',
            'nilai_penjualan' => 'decimal:2',
            'nilai_hak_nasabah' => 'decimal:2',
            'cost_basis' => 'decimal:2',
            'margin_kotor' => 'decimal:2',
            'faktor_emisi_snapshot' => 'decimal:6',
            'co2_terealisasi' => 'decimal:6',
        ];
    }

    public function itemJual()
    {
        return $this->belongsTo(ItemJual::class);
    }

    public function itemSetor()
    {
        return $this->belongsTo(ItemSetor::class);
    }
}
