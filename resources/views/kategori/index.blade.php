@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-700">Master Data Kategori Sampah</h1>
    <a href="{{ route('kategori.create') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 transition">
        + Tambah Kategori
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-100 text-gray-600">
            <tr>
                <th class="p-4 border-b">No</th>
                <th class="p-4 border-b">Nama Sampah</th>
                <th class="p-4 border-b">Harga Beli (Rp/kg)</th>
                <th class="p-4 border-b">Faktor Emisi (kg CO₂/kg)</th>
                <th class="p-4 border-b">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kategori as $item)
            <tr class="hover:bg-gray-50 border-b">
                <td class="p-4">{{ $loop->iteration }}</td>
                <td class="p-4 font-medium">{{ $item->nama }}</td>
                <td class="p-4">Rp {{ number_format($item->harga_beli_per_kg, 0, ',', '.') }}</td>
                <td class="p-4">{{ $item->faktor_emisi }}</td>
                <td class="p-4 flex gap-4">
                    <a href="{{ route('kategori.edit', $item->id) }}" class="text-blue-500 hover:text-blue-700 font-semibold">Edit</a>
                    
                    <form action="{{ route('kategori.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection