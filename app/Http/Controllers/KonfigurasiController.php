<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSistem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KonfigurasiController extends Controller
{
    // ─── PIN ADMIN ──────────────────────────────────────────────

    /**
     * Tampilkan halaman input PIN admin.
     */
    public function showPin()
    {
        return view('konfigurasi.pin');
    }

    /**
     * Verifikasi PIN admin.
     */
    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => ['required', 'string']]);

        $pinTersimpan = PengaturanSistem::ambilString('admin_pin', '123456');

        if ($request->pin === $pinTersimpan) {
            session(['admin_authenticated' => true]);
            return redirect()->route('konfigurasi.index');
        }

        return redirect()->route('konfigurasi.pin')
            ->with('pin_error', 'PIN salah. Coba lagi.');
    }

    /**
     * Logout dari sesi admin.
     */
    public function logoutPin()
    {
        session()->forget('admin_authenticated');
        return redirect()->route('konfigurasi.pin')
            ->with('pin_error', null);
    }

    // ─── HALAMAN UTAMA ──────────────────────────────────────────

    /**
     * Halaman utama: daftar pengaturan + daftar pengurus.
     */
    public function index()
    {
        $pengaturan = PengaturanSistem::orderBy('kunci')->get();
        $users = User::orderBy('name')->get();

        return view('konfigurasi.index', compact('pengaturan', 'users'));
    }

    // ─── SETTING KONSTANTA ──────────────────────────────────────

    /**
     * Simpan semua konstanta sekaligus.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'nilai' => ['required', 'array'],
            'nilai.*' => ['required', 'string'],
        ]);

        foreach ($request->nilai as $id => $nilai) {
            PengaturanSistem::where('id', $id)->update(['nilai' => $nilai]);
        }

        // Jika PIN diubah, tetap pertahankan sesi admin
        return redirect()->route('konfigurasi.index')
            ->with('success', 'Konstanta sistem berhasil diperbarui.');
    }

    // ─── CRUD AKUN PENGURUS ─────────────────────────────────────

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Akun pengurus ' . $validated['name'] . ' berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:6']]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Akun ' . $user->name . ' berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        // Cegah hapus diri sendiri (jika admin juga login sebagai pengurus)
        if (auth()->check() && $user->id === auth()->id()) {
            return redirect()->route('konfigurasi.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $nama = $user->name;
        $user->delete();

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Akun pengurus ' . $nama . ' berhasil dihapus.');
    }
}
