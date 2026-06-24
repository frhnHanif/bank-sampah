<?php

namespace App\Http\Controllers;

use App\Models\KategoriSampah;
use Illuminate\Http\Request;

class KategoriSampahController extends Controller
{
    public function index()
    {
        $kategori = KategoriSampah::all();
        return view('kategori.index', compact('kategori'));
    }

    // Fungsi create() dan edit() sudah BISA DIHAPUS karena menggunakan Modal di Index.

    public function store(Request $request)
    {
        if ($request->has('harga_beli_per_kg')) {
            $request->merge(['harga_beli_per_kg' => (int) str_replace('.', '', $request->harga_beli_per_kg)]);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'harga_beli_per_kg' => 'required|integer|min:0',
            'faktor_emisi' => 'required|numeric|min:0',
        ]);

        KategoriSampah::create($request->all());

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function update(Request $request, KategoriSampah $kategori)
    {
        if ($request->has('harga_beli_per_kg')) {
            $request->merge(['harga_beli_per_kg' => (int) str_replace('.', '', $request->harga_beli_per_kg)]);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'harga_beli_per_kg' => 'required|integer|min:0',
            'faktor_emisi' => 'required|numeric|min:0',
        ]);

        $kategori->update($request->all());

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(KategoriSampah $kategori)
    {
        // Cek apakah ada stok tersisa
        $stokTersedia = $kategori->stok ? $kategori->stok->total_berat_kg : 0;
        if ($stokTersedia > 0) {
            return back()->withErrors([
                'error' => 'Kategori «' . $kategori->nama . '» masih memiliki stok ' 
                    . number_format($stokTersedia, 2, ',', '.') . ' kg di gudang. '
                    . 'Jual stok terlebih dahulu sebelum menonaktifkan.'
            ]);
        }

        $kategori->delete(); // soft delete — data transaksi tetap utuh
        return redirect()->route('kategori.index')->with('success', 'Kategori ' . $kategori->nama . ' berhasil dinonaktifkan.');
    }
}