@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Master Data Kategori Sampah</h1>
        <p class="text-sm text-gray-500">Kelola jenis sampah, harga beli, dan estimasi reduksi emisi.</p>
    </div>
    <a href="{{ route('kategori.create') }}" class="bg-emerald-600 text-white px-6 py-2.5 rounded-full font-bold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Tambah Kategori
    </a>
</div>

@if(session('success'))
    <div class="bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
        <i class="fa-solid fa-circle-check"></i>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
@endif

<div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-b border-gray-100">
                    <th class="p-4 font-bold text-sm uppercase tracking-wider">No</th>
                    <th class="p-4 font-bold text-sm uppercase tracking-wider">Nama Sampah</th>
                    <th class="p-4 font-bold text-sm uppercase tracking-wider">Harga Beli (Rp/kg)</th>
                    <th class="p-4 font-bold text-sm uppercase tracking-wider">Faktor Emisi (kg CO₂/kg)</th>
                    <th class="p-4 font-bold text-sm uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($kategori as $item)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="p-4 text-sm text-gray-600 font-medium">{{ $loop->iteration }}</td>
                    <td class="p-4 font-bold text-gray-700">{{ $item->nama }}</td>
                    <td class="p-4 text-sm font-bold text-emerald-600">Rp {{ number_format($item->harga_beli_per_kg, 0, ',', '.') }}</td>
                    <td class="p-4 text-sm text-gray-600">{{ $item->faktor_emisi }}</td>
                    
                    <td class="p-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('kategori.edit', $item->id) }}" class="text-emerald-500 hover:text-emerald-700 transition p-2 bg-emerald-50 rounded-lg hover:bg-emerald-100 tooltip" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            
                            <form action="{{ route('kategori.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition p-2 bg-red-50 rounded-lg hover:bg-red-100 tooltip" title="Hapus">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection