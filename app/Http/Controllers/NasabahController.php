<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use Illuminate\Http\Request;

class NasabahController extends Controller
{
    public function index()
    {
        $nasabah = Nasabah::latest()->get();
        return view('nasabah.index', compact('nasabah'));
    }

    public function create()
    {
        return view('nasabah.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:255',
        'rt'   => 'required|string|max:3',
        'rw'   => 'required|string|max:3',
        'no_hp' => 'nullable|string|max:15',
    ]);

    // 1. Format RT & RW menjadi 2 digit (misal: 1 jadi 01)
    $rwStr = str_pad($request->rw, 2, '0', STR_PAD_LEFT);
    $rtStr = str_pad($request->rt, 2, '0', STR_PAD_LEFT);
    $prefix = $rwStr . $rtStr; // Hasilnya misal: "0301"

    // 2. Cari nomor urut terakhir untuk RT/RW tersebut
    // Kita cari kode yang dimulai dengan prefix 0301
    $lastNasabah = \App\Models\Nasabah::where('kode', 'like', $prefix . '%')
                    ->orderBy('kode', 'desc')
                    ->first();

    if ($lastNasabah) {
        // Ambil 3 digit terakhir, lalu tambah 1
        $lastNumber = substr($lastNasabah->kode, -3);
        $nextNumber = intval($lastNumber) + 1;
    } else {
        // Jika belum ada nasabah di RT/RW tersebut, mulai dari 1
        $nextNumber = 1;
    }

    // 3. Gabungkan jadi kode final (misal: 0301001)
    $finalKode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

    // 4. Simpan ke database
    \App\Models\Nasabah::create([
        'kode' => $finalKode,
        'nama' => $request->nama,
        'rt'   => $request->rt,
        'rw'   => $request->rw,
        'no_hp' => $request->no_hp,
    ]);

    return redirect()->route('nasabah.index')->with('success', "Nasabah berhasil didaftarkan dengan Kode: $finalKode");
    }

    public function destroy(Nasabah $nasabah)
    {
        $nasabah->delete();
        return redirect()->route('nasabah.index')->with('success', 'Data nasabah berhasil dihapus!');
    }
}
