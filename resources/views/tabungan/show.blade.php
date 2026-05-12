@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('nasabah.index') }}" class="text-emerald-600 font-bold text-sm flex items-center gap-2 hover:underline mb-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Nasabah
            </a>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Buku Tabungan Nasabah</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
            <i class="fa-solid fa-circle-check"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <ul class="list-disc list-inside text-sm font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800"><i class="fa-solid fa-clock-rotate-left mr-2 text-gray-400"></i>Riwayat Transaksi</h3>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b">
                        <tr>
                            <th class="p-4 font-black">Tanggal</th>
                            <th class="p-4 font-black">Keterangan</th>
                            <th class="p-4 font-black text-right">Masuk (Cr)</th>
                            <th class="p-4 font-black text-right">Keluar (Db)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mutasi as $m)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 text-sm text-gray-600 font-medium">{{ \Carbon\Carbon::parse($m->tanggal)->format('d/m/Y') }}</td>
                            <td class="p-4 text-sm text-gray-600">
                                {{ $m->keterangan }}
                                @if($m->ref_transaksi_setor_id)
                                    <a href="#" class="text-xs text-emerald-500 hover:underline ml-1">(Lihat Nota)</a>
                                @endif
                            </td>
                            <td class="p-4 text-sm font-bold text-right text-emerald-600">
                                {{ $m->jenis == 'kredit' ? '+ Rp ' . number_format($m->jumlah, 0, ',', '.') : '-' }}
                            </td>
                            <td class="p-4 text-sm font-bold text-right text-red-500">
                                {{ $m->jenis == 'debit' ? '- Rp ' . number_format($m->jumlah, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-400 italic">Belum ada riwayat transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white shadow-lg shadow-emerald-200">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-xl backdrop-blur-sm">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg leading-tight">{{ $nasabah->nama }}</h2>
                        <p class="text-emerald-100 text-sm opacity-90">{{ $nasabah->kode }} • RT {{ $nasabah->rt }}/RW {{ $nasabah->rw }}</p>
                    </div>
                </div>
                
                <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider mb-1">Total Saldo Aktif</p>
                <h1 class="text-4xl font-black tracking-tight">
                    Rp {{ number_format($nasabah->tabungan ? $nasabah->tabungan->saldo_saat_ini : 0, 0, ',', '.') }}
                </h1>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-money-bill-transfer mr-2 text-emerald-500"></i>Tarik Tunai</h3>
                
                <form action="{{ route('tabungan.tarik', $nasabah->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tanggal Penarikan</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jumlah Tarik (Rp)</label>
                        <input type="number" name="jumlah" placeholder="Contoh: 50000" min="100" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-emerald-500 text-lg font-bold text-gray-700">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Keterangan (Opsional)</label>
                        <input type="text" name="keterangan" placeholder="Contoh: Beli token listrik" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                    </div>

                    <button type="submit" class="w-full bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-bold py-3 rounded-xl transition-colors mt-2" onclick="return confirm('Proses penarikan dana ini?')">
                        Proses Penarikan
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection