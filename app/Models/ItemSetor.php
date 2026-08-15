<?php

namespace App\Models;

use App\Enums\SettlementStatus;
use Illuminate\Database\Eloquent\Model;

class ItemSetor extends Model
{
    protected $table = 'item_setor';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'berat_kg' => 'decimal:2',
            'berat_teralokasi_kg' => 'decimal:2',
            'status' => SettlementStatus::class,
            'jumlah_pcs' => 'integer',
        ];
    }

    public function getBeratPendingAttribute(): string
    {
        return number_format(max(0, (float) $this->berat_kg - (float) $this->berat_teralokasi_kg), 2, '.', '');
    }

    public function transaksi()
    {
        return $this->belongsTo(TransaksiSetor::class, 'transaksi_setor_id');
    }

    public function jenisSampah()
    {
        return $this->belongsTo(JenisSampah::class)->withTrashed();
    }

    public function alokasi()
    {
        return $this->hasMany(AlokasiPenjualan::class);
    }
}
