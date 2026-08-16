<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nasabah extends Model
{
    use SoftDeletes;

    protected $table = 'nasabah';

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

    public static function accountNumber(string|int $rt, string|int $rw, int $id): string
    {
        if ($id < 1 || $id > 9999) {
            throw new \InvalidArgumentException('ID nasabah harus berada dalam rentang 1–9999.');
        }

        return str_pad((string) (int) $rt, 3, '0', STR_PAD_LEFT)
            .str_pad((string) (int) $rw, 3, '0', STR_PAD_LEFT)
            .str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    public function tabungan()
    {
        return $this->hasOne(Tabungan::class);
    }

    public function transaksiSetor()
    {
        return $this->hasMany(TransaksiSetor::class);
    }
}
