@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard Utama</h1>
    <p class="text-gray-500 mt-1">Ringkasan operasional dan dampak lingkungan komunitas.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-2xl">
            <i class="fa-solid fa-weight-scale"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium mb-1">Total Sampah</p>
            <h3 class="text-2xl font-black text-gray-800">{{ number_format($totalBerat, 1, ',', '.') }} <span class="text-sm font-semibold text-gray-500">kg</span></h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-2xl">
            <i class="fa-solid fa-leaf"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium mb-1">Reduksi Emisi CO₂</p>
            <h3 class="text-2xl font-black text-gray-800">{{ number_format($totalCO2, 2, ',', '.') }} <span class="text-sm font-semibold text-gray-500">kg</span></h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-2xl">
            <i class="fa-solid fa-coins"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium mb-1">Nilai Ekonomi</p>
            <h3 class="text-2xl font-black text-gray-800"><span class="text-sm font-semibold text-gray-500">Rp</span> {{ number_format($totalNilaiEkonomi, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-2xl">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium mb-1">Total Nasabah</p>
            <h3 class="text-2xl font-black text-gray-800">{{ $totalNasabah }} <span class="text-sm font-semibold text-gray-500">Warga</span></h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-trophy text-amber-500 mr-2"></i> Nasabah Paling Aktif</h3>
        </div>
        <div class="p-0">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3 border-b">Peringkat</th>
                        <th class="px-6 py-3 border-b">Nama & Alamat</th>
                        <th class="px-6 py-3 border-b text-right">Total Setor (Kg)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topNasabah as $nasabah)
                    <tr class="hover:bg-emerald-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold {{ $loop->iteration <= 3 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                #{{ $loop->iteration }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-800">{{ $nasabah->nama }}</p>
                            <p class="text-xs text-gray-500">RT {{ $nasabah->rt }} / RW {{ $nasabah->rw }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full font-bold text-sm">
                                {{ number_format($nasabah->total_berat_disetor, 1, ',', '.') }} kg
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">Belum ada data penyetoran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-clock-rotate-left text-emerald-500 mr-2"></i> Setoran Terbaru</h3>
            <a href="{{ route('setor.create') }}" class="text-sm font-semibold text-emerald-600 hover:underline">Setor Baru</a>
        </div>
        <div class="p-6">
            <div class="space-y-5">
                @forelse($transaksiTerbaru as $trx)
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-gray-400 uppercase leading-none">{{ \Carbon\Carbon::parse($trx->tanggal)->format('M') }}</span>
                        <span class="text-lg font-black text-emerald-600 leading-none mt-1">{{ \Carbon\Carbon::parse($trx->tanggal)->format('d') }}</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm">{{ $trx->nasabah->nama }}</h4>
                        <p class="text-xs text-gray-500 mt-1">Reduksi: {{ number_format($trx->total_co2, 2, ',', '.') }} kg CO₂</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-emerald-600 text-sm">Rp {{ number_format($trx->total_nilai, 0, ',', '.') }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-6">
                    <p class="text-gray-400">Belum ada transaksi penyetoran dicatat.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection