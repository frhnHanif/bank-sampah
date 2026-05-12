@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-sm">
    <h1 class="text-2xl font-bold text-gray-700 mb-6">Tambah Kategori Sampah Baru</h1>

    <form action="{{ route('kategori.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Nama Sampah (Misal: Kardus Bekas)</label>
            <input type="text" name="nama" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 border p-2">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700">Harga Beli per Kg (Rp)</label>
            <input type="number" name="harga_beli_per_kg" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 border p-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Faktor Emisi (kg CO₂ yang dihemat per kg sampah)</label>
            <input type="number" step="0.0001" name="faktor_emisi" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 border p-2">
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-md hover:bg-emerald-700 transition">Simpan Data</button>
            <a href="{{ route('kategori.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition">Batal</a>
        </div>
    </form>
</div>
@endsection