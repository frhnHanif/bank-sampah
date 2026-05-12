<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tabungan extends Model
{
    protected $table = 'tabungan';
    protected $guarded = ['id'];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class);
    }
}