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

<div class="bg-white shadow-sm border border-gray-100 rounded-2xl">
    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center rounded-t-2xl">
        <h3 class="font-bold text-gray-800"><i class="fa-solid fa-list-check mr-2 text-gray-400"></i>Jurnal Mutasi Kas Induk</h3>
        
        <div class="flex items-center gap-3">
            <!-- Filter Bulan & Tahun -->
            @php
                $namaBulan = [0 => 'Semua Bulan', 1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                $labelBulan = $namaBulan[$bulan] ?? 'Semua Bulan';
                $labelTahun = $tahun > 0 ? $tahun : 'Semua Tahun';
                $labelFilter = ($bulan > 0 || $tahun > 0) ? $labelBulan . ' ' . $labelTahun : 'Semua Waktu';
            @endphp
            <button type="button" onclick="bukaModalFilterBulan()" class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-600 hover:border-amber-300 focus:ring-2 focus:ring-amber-500 outline-none flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-calendar text-amber-400"></i> {{ $labelFilter }}
            </button>

            <a href="{{ route('keuangan.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" class="text-xs font-bold bg-white border border-gray-200 text-gray-600 hover:text-emerald-600 hover:border-emerald-300 px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center gap-2 tooltip" title="Ekspor PDF Keuangan">
                <i class="fa-solid fa-file-pdf text-red-500"></i> <span class="hidden sm:inline">Ekspor PDF</span>
            </a>
        </div>
    </div>

    {{-- Mobile: Card Layout --}}
    <div class="lg:hidden divide-y divide-gray-100">
        @forelse($mutasiKas as $m)
        @php
            $tgl = \Carbon\Carbon::parse($m->tanggal);
            $bulanSingkat = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $tglFormat = $tgl->day . ' ' . $bulanSingkat[$tgl->month - 1] . ' ' . $tgl->year;
            $isPemasukan = $m->tipe == 'pemasukan';
            $katColor = match($m->kategori) {
                'Penjualan' => 'bg-amber-100 text-amber-700',
                'Tarik Tunai Nasabah' => 'bg-blue-100 text-blue-700',
                'Operasional' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-600',
            };
            $katIcon = match($m->kategori) {
                'Penjualan' => 'fa-store',
                'Tarik Tunai Nasabah' => 'fa-hand-holding-dollar',
                'Operasional' => 'fa-screwdriver-wrench',
                default => 'fa-file-invoice',
            };
        @endphp
        <div class="p-4 hover:bg-gray-50/30 transition-colors">
            {{-- Baris atas: tanggal kiri, nominal kanan --}}
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="inline-block bg-gray-100 text-gray-700 text-xs font-bold px-2.5 py-1 rounded-md shrink-0">{{ $tglFormat }}</span>
                <span class="text-sm font-bold text-right {{ $isPemasukan ? 'text-emerald-600' : 'text-red-500' }}">
                    {{ $isPemasukan ? '+' : '−' }} Rp {{ number_format($m->nominal, 0, ',', '.') }}
                </span>
            </div>
            {{-- Baris bawah: badge kategori + keterangan --}}
            <div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $katColor }} mb-1.5">
                    <i class="fa-solid {{ $katIcon }} text-[9px]"></i>
                    {{ $m->kategori }}
                </span>
                <p class="text-sm text-gray-600">{{ $m->keterangan }}</p>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-gray-400 italic">Belum ada riwayat keuangan kas induk.</div>
        @endforelse
    </div>

    {{-- Desktop: Table Layout --}}
    <div class="hidden lg:block overflow-x-auto overflow-hidden rounded-b-2xl">
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

<div id="modalFilterBulan" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-xs mx-4 overflow-hidden transform scale-95 transition-transform duration-300 shadow-xl" id="modalFilterBulanBox">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-sm"><i class="fa-solid fa-calendar mr-2 text-amber-500"></i>Filter Waktu</h3>
            <button type="button" onclick="tutupModalFilterBulan()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="p-3 space-y-3">
            {{-- Tahun --}}
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Tahun</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="pilihFilter({{ $bulan }}, 0)" 
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                        {{ $tahun == 0 ? 'bg-amber-100 text-amber-700 font-bold' : 'text-gray-500 hover:bg-gray-50' }}">
                        Semua
                    </button>
                    @foreach($tahunTersedia as $th)
                    <button type="button" onclick="pilihFilter({{ $bulan }}, {{ $th }})" 
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                        {{ $tahun == $th ? 'bg-amber-100 text-amber-700 font-bold' : 'text-gray-500 hover:bg-gray-50' }}">
                        {{ $th }}
                    </button>
                    @endforeach
                </div>
            </div>
            {{-- Bulan --}}
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Bulan</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($namaBulan as $val => $nama)
                        <button type="button" onclick="pilihFilter({{ $val }}, {{ $tahun }})" 
                            class="text-left px-3 py-2 rounded-xl text-sm font-medium transition-colors
                            {{ $bulan == $val ? 'bg-amber-100 text-amber-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $nama }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
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

    // === MODAL FILTER BULAN ===
    const modalFB = document.getElementById('modalFilterBulan');
    const modalFBBox = document.getElementById('modalFilterBulanBox');

    function bukaModalFilterBulan() {
        modalFB.classList.remove('hidden');
        modalFB.classList.add('flex');
        setTimeout(() => {
            modalFB.classList.remove('opacity-0');
            modalFBBox.classList.remove('scale-95');
        }, 10);
    }

    function tutupModalFilterBulan() {
        modalFB.classList.add('opacity-0');
        modalFBBox.classList.add('scale-95');
        setTimeout(() => {
            modalFB.classList.add('hidden');
            modalFB.classList.remove('flex');
        }, 300);
    }

    function pilihFilter(bulan, tahun) {
        const params = new URLSearchParams();
        if (bulan > 0) params.set('bulan', bulan);
        if (tahun > 0) params.set('tahun', tahun);
        const qs = params.toString();
        window.location.href = '{{ route("keuangan.index") }}' + (qs ? '?' + qs : '');
    }
</script>
@endpush
@endsection