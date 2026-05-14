@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Kategori Sampah</h1>

    <form action="{{ route('kategori.update', $kategori->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-5">
            <label class="block font-bold text-gray-700 mb-2">Nama Sampah</label>
            <input type="text" name="nama" value="{{ old('nama', $kategori->nama) }}" 
                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all" required>
        </div>

        <div class="mb-5">
            <label class="block font-bold text-gray-700 mb-2">Harga Beli (Rp per kg)</label>
            <input type="number" name="harga_beli_per_kg" value="{{ old('harga_beli_per_kg', $kategori->harga_beli_per_kg) }}" 
                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all" required min="0">
        </div>

        <div class="mb-8">
            <label class="block font-bold text-gray-700 mb-2">Faktor Emisi (kg CO₂ per kg)</label>
            <input type="number" step="0.01" name="faktor_emisi" value="{{ old('faktor_emisi', $kategori->faktor_emisi) }}" 
                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all" required min="0">
            <p class="text-sm text-gray-500 mt-2">Nilai konversi pengurangan emisi karbon.</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('kategori.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-all">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 shadow-md shadow-emerald-200 transition-all">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection