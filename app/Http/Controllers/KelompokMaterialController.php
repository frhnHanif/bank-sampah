<?php

namespace App\Http\Controllers;

use App\Models\KelompokMaterial;
use App\Services\EmissionRealizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KelompokMaterialController extends Controller
{
    public function index()
    {
        $kelompok = KelompokMaterial::withCount('jenisSampah')->orderByDesc('is_active')->orderBy('nama')->get();

        return view('faktor-emisi.index', compact('kelompok'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['nama_normalized'] = $this->normalize($data['nama']);
        KelompokMaterial::create($data);

        return back()->with('success', 'Kelompok material berhasil ditambahkan.');
    }

    public function update(Request $request, KelompokMaterial $kelompokMaterial, EmissionRealizationService $service)
    {
        $data = $this->validated($request);
        $normalized = $this->normalize($data['nama']);
        if (KelompokMaterial::withTrashed()->where('nama_normalized', $normalized)->whereKeyNot($kelompokMaterial->id)->exists()) {
            throw ValidationException::withMessages(['nama' => 'Kelompok material dengan nama yang sama sudah ada.']);
        }
        $kelompokMaterial->update($data + ['nama_normalized' => $normalized]);
        $count = $service->realizePendingForGroup($kelompokMaterial->fresh());

        return back()->with('success', "Kelompok material diperbarui. {$count} emisi tertunda direalisasikan.");
    }

    public function toggle(KelompokMaterial $kelompokMaterial)
    {
        if ($kelompokMaterial->is_active && $kelompokMaterial->jenisSampah()->where('is_active', true)->exists()) {
            return back()->withErrors(['error' => 'Nonaktifkan semua jenis sampah dalam kelompok ini terlebih dahulu.']);
        } $kelompokMaterial->update(['is_active' => ! $kelompokMaterial->is_active]);

        return back()->with('success', 'Status kelompok material diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['nama' => ['required', 'string', 'max:255'], 'faktor_emisi_kgco2e_per_kg' => ['nullable', 'numeric', 'min:0'], 'sumber_faktor_emisi' => ['nullable', 'string'], 'versi_faktor_emisi' => ['nullable', 'string', 'max:255'], 'tanggal_berlaku_faktor_emisi' => ['nullable', 'date'], 'catatan_faktor_emisi' => ['nullable', 'string']]);
    }

    private function normalize(string $name): string
    {
        return Str::lower(preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name));
    }
}
