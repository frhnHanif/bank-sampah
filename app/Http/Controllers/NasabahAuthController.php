<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NasabahAuthController extends Controller
{
    /**
     * Tampilkan form cek rekening nasabah.
     * Jika sudah login & belum expired (15 menit), langsung ke tabungan.
     */
    public function showLogin()
    {
        // Nasabah sudah login & session belum expired?
        if ($this->isSessionActive()) {
            return redirect()->route('tabungan.show', session('nasabah_id'));
        }

        return view('auth.nasabah-login');
    }

    /**
     * Cek apakah sesi nasabah masih aktif (dalam 15 menit terakhir).
     */
    private function isSessionActive(): bool
    {
        if (! session('nasabah_id')) {
            return false;
        }

        $loginAt = session('nasabah_login_at');
        if (! $loginAt) {
            return false;
        }

        // Timeout 15 menit (900 detik)
        return (time() - (int) $loginAt) < 900;
    }

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
            'nasabah_id'       => $nasabah->id,
            'nasabah_kode'     => $nasabah->kode,
            'nasabah_nama'     => $nasabah->nama,
            'nasabah_login_at' => time(),
        ]);

        return redirect()->route('tabungan.show', $nasabah->id);
    }

    /**
     * Logout nasabah (hapus sesi).
     */
    public function logout()
    {
        session()->forget(['nasabah_id', 'nasabah_kode', 'nasabah_nama', 'nasabah_login_at']);
        return redirect()->route('nasabah.login')->with('success', 'Anda telah keluar dari cek rekening.');
    }
}
