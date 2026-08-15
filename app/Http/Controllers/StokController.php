<?php

namespace App\Http\Controllers;

use App\Models\ItemJual;
use App\Models\ItemSetor;
use App\Models\Stok;

class StokController extends Controller
{
    public function index()
    {
        $stok = Stok::with('jenisSampah.kelompokMaterial')->orderByDesc('total_berat_kg')->get();
        $pending = ItemSetor::selectRaw('jenis_sampah_id, SUM(berat_kg - berat_teralokasi_kg) AS berat')->groupBy('jenis_sampah_id')->pluck('berat', 'jenis_sampah_id');
        $sold = ItemJual::selectRaw('jenis_sampah_id, SUM(berat_kg) AS berat')->groupBy('jenis_sampah_id')->pluck('berat', 'jenis_sampah_id');
        $stockData = $stok->map(fn ($row) => ['id' => $row->jenis_sampah_id, 'name' => $row->jenisSampah?->nama,
            'stock' => (float) $row->total_berat_kg, 'awaitingSale' => (float) ($pending[$row->jenis_sampah_id] ?? 0)])->values();

        return view('stok.index', compact('stok', 'sold', 'stockData'));
    }
}
