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
        $request->validate([
            'nama' => 'required|string|max:255',
            'harga_beli_per_kg' => 'required|numeric|min:0',
            'faktor_emisi' => 'required|numeric|min:0',
        ]);

        KategoriSampah::create($request->all());

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function update(Request $request, KategoriSampah $kategori)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'harga_beli_per_kg' => 'required|numeric|min:0',
            'faktor_emisi' => 'required|numeric|min:0',
        ]);

        $kategori->update($request->all());

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(KategoriSampah $kategori)
    {
        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}