<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiSetor extends Model
{
    protected $table = 'transaksi_setor';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function items()
    {
        return $this->hasMany(ItemSetor::class);
    }

    public function getSettlementStatusAttribute(): string
    {
        $statuses = $this->relationLoaded('items') ? $this->items->pluck('status.value') : $this->items()->pluck('status');
        if ($statuses->isEmpty() || $statuses->every(fn ($status) => $status === 'PENDING')) {
            return 'PENDING';
        }
        if ($statuses->every(fn ($status) => $status === 'SETTLED')) {
            return 'SETTLED';
        }

        return 'PARTIAL';
    }
}
