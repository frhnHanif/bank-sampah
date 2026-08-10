<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiJual extends Model
{
    protected $table = 'transaksi_jual';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'flow_version' => 'integer',
            'total_nilai' => 'decimal:2',
            'total_hak_nasabah' => 'decimal:2',
            'total_cost_basis' => 'decimal:2',
            'total_margin_kotor' => 'decimal:2',
            'total_co2_terealisasi' => 'decimal:6',
        ];
    }

    public function items()
    {
        return $this->hasMany(ItemJual::class, 'transaksi_jual_id');
    }
}
