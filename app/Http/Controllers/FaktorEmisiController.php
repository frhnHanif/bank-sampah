<?php

namespace App\Http\Controllers;

use App\Models\FaktorEmisi;
use App\Services\EmissionRealizationService;
use Illuminate\Http\Request;

class FaktorEmisiController extends Controller
{
    public function index()
    {
        $faktor = FaktorEmisi::withCount('kategori')->orderByDesc('aktif')->orderBy('nama_material')->get();

        return view('faktor-emisi.index', compact('faktor'));
    }

    public function store(Request $request)
    {
        FaktorEmisi::create($this->validated($request));

        return back()->with('success', 'Faktor emisi berhasil ditambahkan.');
    }

    public function update(Request $request, FaktorEmisi $faktorEmisi, EmissionRealizationService $emissions)
    {
        $faktorEmisi->update($this->validated($request));
        $faktorEmisi->load('kategori')->kategori->each(
            fn ($kategori) => $emissions->realizePendingForCategory($kategori->load('faktorEmisi'))
        );

        return back()->with('success', 'Faktor emisi diperbarui. Snapshot histori lama tetap dipertahankan.');
    }

    public function toggle(FaktorEmisi $faktorEmisi)
    {
        $faktorEmisi->update(['aktif' => ! $faktorEmisi->aktif]);

        return back()->with('success', 'Status faktor emisi diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama_material' => ['required', 'string', 'max:255'],
            'faktor_kgco2e_per_kg' => ['required', 'numeric', 'min:0'],
            'sumber' => ['nullable', 'string'],
            'versi' => ['nullable', 'string', 'max:255'],
            'tanggal_berlaku' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string'],
            'aktif' => ['sometimes', 'boolean'],
        ]);
    }
}
