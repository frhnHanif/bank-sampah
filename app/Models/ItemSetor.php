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
            'is_legacy' => 'boolean',
            'nilai' => 'decimal:2',
            'co2' => 'decimal:4',
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

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class)->withTrashed();
    }

    public function alokasi()
    {
        return $this->hasMany(AlokasiPenjualan::class);
    }
}
