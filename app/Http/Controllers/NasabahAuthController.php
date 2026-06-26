<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NasabahAuthController extends Controller
{
    /**
     * Proses cek rekening nasabah via kode + no_hp.
     */
    public function cek(Request $request)
    {
        $request->validate([
            'kode'  => ['required', 'string', 'max:50'],
            'no_hp' => ['required', 'string', 'max:30'],
        ], [
            'kode.required'  => 'ID / Kode nasabah wajib diisi.',
            'no_hp.required' => 'Nomor HP / WhatsApp wajib diisi.',
        ]);

        $nasabah = Nasabah::where('kode', $request->kode)
            ->where('no_hp', $request->no_hp)
            ->first();

        if (! $nasabah) {
            throw ValidationException::withMessages([
                'kode' => 'Data tidak ditemukan. Periksa kembali ID dan No HP Anda.',
            ]);
        }

        // Simpan sesi nasabah
        session([
            'nasabah_id'   => $nasabah->id,
            'nasabah_kode' => $nasabah->kode,
            'nasabah_nama' => $nasabah->nama,
        ]);

        return redirect()->route('tabungan.show', $nasabah->id);
    }

    /**
     * Logout nasabah (hapus sesi).
     */
    public function logout()
    {
        session()->forget(['nasabah_id', 'nasabah_kode', 'nasabah_nama']);
        return redirect()->route('login')->with('success', 'Anda telah keluar dari cek rekening.');
    }
}
