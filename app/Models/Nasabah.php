<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tabungan; // Tambahkan baris ini

class Nasabah extends Model
{
    protected $table = 'nasabah';
    protected $guarded = ['id'];

    public function tabungan()
    {
        return $this->hasOne(Tabungan::class);
    }
}