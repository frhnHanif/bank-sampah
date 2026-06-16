<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSistem extends Model
{
    protected $table = 'pengaturan_sistem';
    protected $guarded = ['id'];

    /**
     * Ambil nilai pengaturan berdasarkan kunci sebagai float.
     */
    public static function ambil(string $kunci, float $default = 0): float
    {
        $item = static::where('kunci', $kunci)->first();
        return $item ? (float) $item->nilai : $default;
    }

    /**
     * Ambil nilai pengaturan berdasarkan kunci sebagai string.
     */
    public static function ambilString(string $kunci, string $default = ''): string
    {
        $item = static::where('kunci', $kunci)->first();
        return $item ? (string) $item->nilai : $default;
    }
}
