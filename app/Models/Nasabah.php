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

    public function tabungan()
    {
        return $this->hasOne(Tabungan::class);
    }

    public function transaksiSetor()
    {
        return $this->hasMany(TransaksiSetor::class);
    }
}
