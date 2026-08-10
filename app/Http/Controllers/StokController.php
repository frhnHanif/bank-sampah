<?php

namespace App\Http\Controllers;

use App\Models\ItemSetor;
use App\Models\LegacyInventory;
use App\Models\Stok;

class StokController extends Controller
{
    public function index()
    {
        $stok = Stok::with('kategori')->orderByDesc('total_berat_kg')->get();
        $legacy = LegacyInventory::selectRaw('kategori_id, SUM(berat_tersisa_kg) AS berat')
            ->groupBy('kategori_id')->pluck('berat', 'kategori_id');
        $legacyCost = LegacyInventory::all()->groupBy('kategori_id')->map(fn ($lots) => $lots->sum(
            fn ($lot) => (float) $lot->berat_tersisa_kg * (float) $lot->cost_basis_per_kg
        ));
        $pending = ItemSetor::where('is_legacy', false)
            ->selectRaw('kategori_id, SUM(berat_kg - berat_teralokasi_kg) AS berat')
            ->groupBy('kategori_id')->pluck('berat', 'kategori_id');
        $stockData = $stok->map(fn ($row) => [
            'id' => $row->kategori_id,
            'name' => $row->kategori?->nama,
            'stock' => (float) $row->total_berat_kg,
            'legacy' => (float) ($legacy[$row->kategori_id] ?? 0),
            'pending' => (float) ($pending[$row->kategori_id] ?? 0),
            'legacyCost' => (float) ($legacyCost[$row->kategori_id] ?? 0),
        ])->values();

        return view('stok.index', compact('stok', 'legacy', 'legacyCost', 'pending', 'stockData'));
    }
}
