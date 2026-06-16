@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Buku Kas & Keuangan</h1>
        <p class="text-sm text-gray-500 font-medium">Pantau arus kas masuk, keluar, dan kalkulasi keuntungan bersih bank sampah.</p>
    </div>
    <button type="button" onclick="bukaModalOperasional()" class="bg-amber-500 text-white px-6 py-2.5 rounded-full font-bold hover:bg-amber-600 transition shadow-sm flex items-center gap-2 w-full sm:w-auto justify-center">
        <i class="fa-solid fa-file-invoice-dollar"></i> Catat Operasional
    </button>
</div>

@if(session('success'))
    <div class="bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
        <i class="fa-solid fa-circle-check"></i>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Total Saldo Kas Riil</p>
        <h2 class="text-2xl font-black text-emerald-600">Rp {{ number_format($saldoKas, 0, ',', '.') }}</h2>
        <p class="text-[10px] text-gray-400 mt-2">*Uang tunai fisik yang dipegang pengelola</p>
    </div>
    
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-amber-500"></div>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Omset Penjualan Pengepul</p>
        <h2 class="text-2xl font-bold text-gray-700">Rp {{ number_format($totalPenjualanPengepul, 0, ',', '.') }}</h2>
    </div>
    
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-red-400"></div>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Total Rekening Warga</p>
        <h2 class="text-2xl font-bold text-gray-700">Rp {{ number_format($totalRekeningWarga, 0, ',', '.') }}</h2>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-blue-500"></div>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Estimasi Keuntungan Bersih</p>
        <h2 class="text-2xl font-black text-blue-600">Rp {{ number_format($estimasiKeuntungan, 0, ',', '.') }}</h2>
        <p class="text-[10px] text-gray-400 mt-2">*Hasil penjualan dikurangi beban pokok & operasional</p>
    </div>

</div>

<div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-gray-800"><i class="fa-solid fa-list-check mr-2 text-gray-400"></i>Jurnal Mutasi Kas Induk</h3>
        
        <div class="flex items-center gap-3">
            <!-- Filter Bulan -->
            <div class="custom-select relative w-40">
                <select id="filterBulan" onchange="filterPerBulan()" class="sr-only">
                    <option value="0" {{ $bulan == 0 ? 'selected' : '' }}>Semua Bulan</option>
                    <option value="1" {{ $bulan == 1 ? 'selected' : '' }}>Januari</option>
                    <option value="2" {{ $bulan == 2 ? 'selected' : '' }}>Februari</option>
                    <option value="3" {{ $bulan == 3 ? 'selected' : '' }}>Maret</option>
                    <option value="4" {{ $bulan == 4 ? 'selected' : '' }}>April</option>
                    <option value="5" {{ $bulan == 5 ? 'selected' : '' }}>Mei</option>
                    <option value="6" {{ $bulan == 6 ? 'selected' : '' }}>Juni</option>
                    <option value="7" {{ $bulan == 7 ? 'selected' : '' }}>Juli</option>
                    <option value="8" {{ $bulan == 8 ? 'selected' : '' }}>Agustus</option>
                    <option value="9" {{ $bulan == 9 ? 'selected' : '' }}>September</option>
                    <option value="10" {{ $bulan == 10 ? 'selected' : '' }}>Oktober</option>
                    <option value="11" {{ $bulan == 11 ? 'selected' : '' }}>November</option>
                    <option value="12" {{ $bulan == 12 ? 'selected' : '' }}>Desember</option>
                </select>

                <button type="button" class="custom-select-trigger w-full bg-white border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-amber-500 flex items-center justify-between gap-2 text-left text-xs">
                    <span class="custom-select-label text-gray-600">Semua Bulan</span>
                    <i class="fa-solid fa-chevron-down custom-select-chevron text-amber-400 text-[10px] transition-transform duration-200"></i>
                </button>

                <div class="custom-select-dropdown bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden z-50">
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="0">Semua Bulan</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="1">Januari</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="2">Februari</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="3">Maret</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="4">April</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="5">Mei</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="6">Juni</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="7">Juli</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="8">Agustus</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="9">September</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="10">Oktober</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="11">November</div>
                    <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50" data-value="12">Desember</div>
                </div>
            </div>

            <a href="{{ route('keuangan.pdf', ['bulan' => $bulan]) }}" target="_blank" class="text-xs font-bold bg-white border border-gray-200 text-gray-600 hover:text-emerald-600 hover:border-emerald-300 px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center gap-2 tooltip" title="Ekspor PDF Keuangan">
                <i class="fa-solid fa-file-pdf text-red-500"></i> <span class="hidden sm:inline">Ekspor PDF</span>
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b">
                    <th class="p-4 font-black">Tanggal</th>
                    <th class="p-4 font-black">Kategori</th>
                    <th class="p-4 font-black">Keterangan Transaksi</th>
                    <th class="p-4 font-black text-right">Uang Masuk</th>
                    <th class="p-4 font-black text-right">Uang Keluar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                @forelse($mutasiKas as $m)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="p-4 text-gray-600 font-medium">{{ \Carbon\Carbon::parse($m->tanggal)->format('d/m/Y') }}</td>
                    <td class="p-4">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold 
                            {{ $m->kategori == 'Penjualan' ? 'bg-amber-50 text-amber-600' : '' }}
                            {{ $m->kategori == 'Tarik Tunai Nasabah' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $m->kategori == 'Operasional' ? 'bg-red-50 text-red-600' : '' }}">
                            {{ $m->kategori }}
                        </span>
                    </td>
                    <td class="p-4 text-gray-700 font-medium">{{ $m->keterangan }}</td>
                    <td class="p-4 text-right font-bold text-emerald-600">
                        {{ $m->tipe == 'pemasukan' ? '+ Rp ' . number_format($m->nominal, 0, ',', '.') : '-' }}
                    </td>
                    <td class="p-4 text-right font-bold text-red-500">
                        {{ $m->tipe == 'pengeluaran' ? '- Rp ' . number_format($m->nominal, 0, ',', '.') : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-400 italic">Belum ada riwayat keuangan kas induk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="modalOperasional" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalOperasionalBox">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Catat Pengeluaran Operasional</h3>
            <button type="button" onclick="tutupModalOperasional()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('keuangan.operasional') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nominal Pengeluaran (Rp)</label>
                    <input type="number" name="nominal" required min="100" placeholder="Contoh: 25000"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 outline-none text-lg font-bold text-gray-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Keterangan Pengeluaran</label>
                    <input type="text" name="keterangan" required placeholder="Contoh: Beli bensin roda tiga untuk angkut sampah"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 outline-none text-sm">
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 bg-gray-50/50">
                <button type="button" onclick="tutupModalOperasional()" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 py-2.5 rounded-xl font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl font-bold transition-colors shadow-sm">
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const modalOp = document.getElementById('modalOperasional');
    const modalOpBox = document.getElementById('modalOperasionalBox');

    function bukaModalOperasional() {
        modalOp.classList.remove('hidden');
        modalOp.classList.add('flex');
        setTimeout(() => {
            modalOp.classList.remove('opacity-0');
            modalOpBox.classList.remove('scale-95');
        }, 10);
    }

    function tutupModalOperasional() {
        modalOp.classList.add('opacity-0');
        modalOpBox.classList.add('scale-95');
        setTimeout(() => {
            modalOp.classList.add('hidden');
            modalOp.classList.remove('flex');
        }, 300);
    }

    function filterPerBulan() {
        const bulan = document.getElementById('filterBulan').value;
        window.location.href = '{{ route("keuangan.index") }}?bulan=' + bulan;
    }
</script>
@endpush
@endsection