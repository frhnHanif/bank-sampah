<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'rt' => ['required', 'regex:/^\d{1,3}$/'],
            'rw' => ['required', 'regex:/^\d{1,3}$/'],
            'no_hp' => 'nullable|string|max:15',
        ]);

        $rt = str_pad((string) (int) $data['rt'], 3, '0', STR_PAD_LEFT);
        $rw = str_pad((string) (int) $data['rw'], 3, '0', STR_PAD_LEFT);
        $nasabah = DB::transaction(function () use ($data, $rt, $rw): Nasabah {
            // Kode sementara diperlukan sampai auto-increment ID tersedia.
            $nasabah = Nasabah::create([
                'kode' => 'TMP-'.Str::uuid(),
                'nama' => $data['nama'],
                'rt' => $rt,
                'rw' => $rw,
                'no_hp' => $data['no_hp'] ?? null,
            ]);
            $nasabah->update(['kode' => Nasabah::accountNumber($rt, $rw, $nasabah->id)]);

            return $nasabah;
        });

        return redirect()->route('nasabah.index')->with('success', "Nasabah berhasil didaftarkan dengan nomor rekening: {$nasabah->kode}");
    }

    public function update(Request $request, Nasabah $nasabah)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'rt' => ['required', 'regex:/^\d{1,3}$/'],
            'rw' => ['required', 'regex:/^\d{1,3}$/'],
            'no_hp' => 'nullable|string|max:15',
        ]);

        $rt = str_pad((string) (int) $data['rt'], 3, '0', STR_PAD_LEFT);
        $rw = str_pad((string) (int) $data['rw'], 3, '0', STR_PAD_LEFT);

        $nasabah->update([
            'kode' => Nasabah::accountNumber($rt, $rw, $nasabah->id),
            'nama' => $data['nama'],
            'rt' => $rt,
            'rw' => $rw,
            'no_hp' => $data['no_hp'] ?? null,
        ]);

        // Karena kita mengubahnya dari halaman Buku Tabungan, kita kembalikan user ke halaman tersebut
        return back()->with('success', 'Data profil nasabah berhasil diperbarui!');
    }

    public function destroy(Nasabah $nasabah)
    {
        // Cek saldo aktif — jangan izinkan nonaktifkan kalau masih ada saldo
        $saldo = $nasabah->tabungan ? $nasabah->tabungan->saldo_saat_ini : 0;
        if ($saldo > 0) {
            return back()->withErrors([
                'error' => 'Nasabah «'.$nasabah->nama.'» masih memiliki saldo aktif Rp '
                    .number_format($saldo, 0, ',', '.')
                    .'. Tarik saldo terlebih dahulu sebelum menonaktifkan.',
            ]);
        }

        $nasabah->delete(); // soft delete — data transaksi tetap utuh

        return redirect()->route('nasabah.index')->with('success', 'Nasabah '.$nasabah->nama.' berhasil dinonaktifkan.');
    }
}
