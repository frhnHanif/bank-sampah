<?php

namespace App\Http\Controllers;

use App\Models\FaktorEmisi;
use App\Models\KategoriSampah;
use App\Services\EmissionRealizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KategoriSampahController extends Controller
{
    public function index()
    {
        $kategori = KategoriSampah::with('faktorEmisi')->orderBy('nama')->get();
        $faktorEmisi = FaktorEmisi::where('aktif', true)->orderBy('nama_material')->get();

        return view('kategori.index', compact('kategori', 'faktorEmisi'));
    }

    public function store(Request $request)
    {
        $kategori = $this->persist($request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Jenis sampah siap digunakan.',
                'kategori' => $kategori->load('faktorEmisi'),
            ], 201);
        }

        return redirect()->route('kategori.index')->with('success', 'Jenis sampah berhasil ditambahkan.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        $kategori = $this->persist($request);

        return response()->json([
            'message' => 'Jenis sampah siap digunakan.',
            'kategori' => $kategori->load('faktorEmisi'),
        ], 201);
    }

    public function update(Request $request, KategoriSampah $kategori, EmissionRealizationService $emissions)
    {
        $data = $this->validated($request);
        $normalized = self::normalizeName($data['nama']);
        $duplicate = KategoriSampah::withTrashed()
            ->where('nama_normalized', $normalized)->whereKeyNot($kategori->id)->first();
        if ($duplicate) {
            throw ValidationException::withMessages(['nama' => 'Jenis sampah dengan nama yang sama sudah ada.']);
        }

        $kategori->update([
            'nama' => self::displayName($data['nama']),
            'nama_normalized' => $normalized,
            'faktor_emisi_id' => $data['faktor_emisi_id'] ?? null,
        ]);
        $emissions->realizePendingForCategory($kategori->fresh('faktorEmisi'));

        return redirect()->route('kategori.index')->with('success', 'Jenis sampah berhasil diperbarui.');
    }

    public function destroy(KategoriSampah $kategori)
    {
        $stokTersedia = (float) ($kategori->stok?->total_berat_kg ?? 0);
        if ($stokTersedia > 0) {
            return back()->withErrors(['error' => sprintf(
                'Jenis "%s" masih memiliki stok %s kg. Jual stok terlebih dahulu.',
                $kategori->nama,
                number_format($stokTersedia, 2, ',', '.')
            )]);
        }

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Jenis sampah dinonaktifkan.');
    }

    private function persist(Request $request): KategoriSampah
    {
        $data = $this->validated($request);
        $normalized = self::normalizeName($data['nama']);
        $existing = KategoriSampah::withTrashed()->where('nama_normalized', $normalized)->first();

        if ($existing && ! $existing->trashed()) {
            throw ValidationException::withMessages(['nama' => 'Jenis sampah dengan nama yang sama sudah aktif.']);
        }
        if ($existing?->trashed()) {
            $existing->restore();
            $existing->update(['faktor_emisi_id' => $data['faktor_emisi_id'] ?? $existing->faktor_emisi_id]);

            return $existing;
        }

        return KategoriSampah::create([
            'nama' => self::displayName($data['nama']),
            'nama_normalized' => $normalized,
            'faktor_emisi_id' => $data['faktor_emisi_id'] ?? null,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'faktor_emisi_id' => ['nullable', 'integer', 'exists:faktor_emisi,id'],
        ]);
    }

    public static function normalizeName(string $name): string
    {
        return Str::lower(preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name));
    }

    private static function displayName(string $name): string
    {
        return preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);
    }
}
