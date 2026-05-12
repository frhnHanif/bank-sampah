@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Data Nasabah</h1>
        <p class="text-sm text-gray-500">Kelola informasi warga yang terdaftar sebagai nasabah bank sampah.</p>
    </div>
    <a href="{{ route('nasabah.create') }}" class="bg-emerald-600 text-white px-6 py-2.5 rounded-full font-bold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
        <i class="fa-solid fa-user-plus"></i> Tambah Nasabah
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
                    <th class="p-4 font-bold text-sm uppercase tracking-wider">Kode</th>
                    <th class="p-4 font-bold text-sm uppercase tracking-wider">Nama Lengkap</th>
                    <th class="p-4 font-bold text-sm uppercase tracking-wider text-center">RT/RW</th>
                    <th class="p-4 font-bold text-sm uppercase tracking-wider">No. HP</th>
                    <th class="p-4 font-bold text-sm uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($nasabah as $item)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="p-4 font-mono text-sm font-bold text-emerald-600">{{ $item->kode }}</td>
                    <td class="p-4 font-semibold text-gray-700">{{ $item->nama }}</td>
                    <td class="p-4 text-center text-gray-600">
                        <span class="px-2 py-1 bg-gray-100 rounded-md text-xs font-bold">{{ $item->rt }} / {{ $item->rw }}</span>
                    </td>
                    <td class="p-4 text-gray-600">{{ $item->no_hp ?? '-' }}</td>
                    
                    <td class="p-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('tabungan.show', $item->id) }}" class="text-emerald-500 hover:text-emerald-700 transition p-2 bg-emerald-50 rounded-lg hover:bg-emerald-100 tooltip" title="Buku Tabungan">
                                <i class="fa-solid fa-book"></i>
                            </a>
                            
                            <form action="{{ route('nasabah.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus nasabah ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition p-2 bg-red-50 rounded-lg hover:bg-red-100">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-400 italic">
                        Belum ada nasabah terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection