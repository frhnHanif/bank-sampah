<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use App\Models\KelompokMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JenisSampahController extends Controller
{
    public function index()
    {
        $jenis = JenisSampah::with('kelompokMaterial')->orderBy('nama')->get();
        $kelompok = KelompokMaterial::where('is_active', true)->orderBy('nama')->get();

        return view('kategori.index', compact('jenis', 'kelompok'));
    }

    public function store(Request $request)
    {
        $this->persist($request);

        return back()->with('success', 'Jenis sampah berhasil ditambahkan.');
    }

    public function update(Request $request, JenisSampah $jenisSampah)
    {
        $data = $this->validated($request);
        $normalized = self::normalize($data['nama']);
        if (JenisSampah::withTrashed()->where('nama_normalized', $normalized)->whereKeyNot($jenisSampah->id)->exists()) {
            throw ValidationException::withMessages(['nama' => 'Jenis sampah dengan nama yang sama sudah ada.']);
        }
        $jenisSampah->update($data + ['nama' => self::display($data['nama']), 'nama_normalized' => $normalized]);

        return back()->with('success', 'Jenis sampah berhasil diperbarui.');
    }

    public function destroy(JenisSampah $jenisSampah)
    {
        if ((float) ($jenisSampah->stok?->total_berat_kg ?? 0) > 0) {
            return back()->withErrors(['error' => 'Jenis sampah masih memiliki stok.']);
        }
        $jenisSampah->update(['is_active' => false]);

        return back()->with('success', 'Jenis sampah dinonaktifkan.');
    }

    private function persist(Request $request): JenisSampah
    {
        $data = $this->validated($request);
        $normalized = self::normalize($data['nama']);
        if (JenisSampah::withTrashed()->where('nama_normalized', $normalized)->exists()) {
            throw ValidationException::withMessages(['nama' => 'Jenis sampah dengan nama yang sama sudah ada.']);
        }

        return JenisSampah::create($data + ['nama' => self::display($data['nama']), 'nama_normalized' => $normalized, 'is_active' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate(['nama' => ['required', 'string', 'max:255'], 'kelompok_material_id' => ['required', 'exists:kelompok_material,id'],
            'satuan_pencatatan' => ['required', 'in:KG,PCS']]);
    }

    public static function normalize(string $name): string
    {
        return Str::lower(self::display($name));
    }

    private static function display(string $name): string
    {
        return preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);
    }
}
