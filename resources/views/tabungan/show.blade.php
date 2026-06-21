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
        
        <div class="lg:col-span-2 order-2 lg:order-1 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-2xl">
                <h3 class="font-bold text-gray-800"><i class="fa-solid fa-clock-rotate-left mr-2 text-gray-400"></i>Riwayat Transaksi</h3>
                
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

                        <button type="button" class="custom-select-trigger w-full bg-white border border-emerald-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500 flex items-center justify-between gap-2 text-left text-xs">
                            <span class="custom-select-label text-gray-600">Semua Bulan</span>
                            <i class="fa-solid fa-chevron-down custom-select-chevron text-emerald-400 text-[10px] transition-transform duration-200"></i>
                        </button>

                        <div class="custom-select-dropdown bg-white border border-emerald-200 rounded-lg shadow-lg overflow-hidden z-50">
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="0">Semua Bulan</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="1">Januari</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="2">Februari</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="3">Maret</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="4">April</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="5">Mei</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="6">Juni</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="7">Juli</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="8">Agustus</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="9">September</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="10">Oktober</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="11">November</div>
                            <div class="custom-select-option px-3 py-2 text-xs text-gray-700 border-b border-gray-50 hover:bg-emerald-50" data-value="12">Desember</div>
                        </div>
                    </div>

                    <a href="{{ route('tabungan.pdf', ['id' => $nasabah->id, 'bulan' => $bulan]) }}" target="_blank" class="text-xs font-bold bg-white border border-gray-200 text-gray-600 hover:text-emerald-600 hover:border-emerald-300 px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center gap-2 tooltip" title="Fitur Ekspor PDF">
                        <i class="fa-solid fa-file-pdf text-red-500"></i> <span class="hidden sm:inline">Ekspor PDF</span>
                    </a>
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto overflow-x-hidden rounded-b-2xl divide-y divide-gray-100">
                @forelse($mutasi as $m)
                @php
                    $tgl = \Carbon\Carbon::parse($m->tanggal);
                    $bulanSingkat = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    $tglFormat = $tgl->day . ' ' . $bulanSingkat[$tgl->month - 1] . ' ' . $tgl->year;
                    $isKredit = $m->jenis == 'kredit';
                    $items = $isKredit && $m->transaksiSetor ? $m->transaksiSetor->items : null;
                @endphp
                <div class="p-4 hover:bg-gray-50/30 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-3 sm:gap-5">
                        {{-- Kolom Tanggal --}}
                        <div class="sm:w-28 shrink-0">
                            <span class="inline-block bg-gray-100 text-gray-700 text-xs font-bold px-2.5 py-1 rounded-md">{{ $tglFormat }}</span>
                        </div>

                        {{-- Kolom Detail --}}
                        <div class="flex-1 min-w-0">
                            {{-- Badge jenis transaksi --}}
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $isKredit ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    <i class="fa-solid {{ $isKredit ? 'fa-arrow-down text-emerald-500' : 'fa-arrow-up text-red-500' }} text-[9px]"></i>
                                    {{ $isKredit ? 'Setor' : 'Tarik' }}
                                </span>
                                <span class="text-sm font-bold {{ $isKredit ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $isKredit ? '+' : '-' }} Rp {{ number_format($m->jumlah, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Rincian item (hanya untuk setor) --}}
                            @if($isKredit && $items && $items->count())
                            <div class="space-y-1 mt-2">
                                @foreach($items as $item)
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-gray-500 bg-gray-50 rounded-lg px-3 py-1.5">
                                    <span class="font-medium text-gray-700 truncate max-w-[180px]">{{ $item->kategori ? $item->kategori->nama : '—' }}</span>
                                    <span class="whitespace-nowrap">{{ number_format($item->berat_kg, 2, ',', '.') }} kg</span>
                                    <span class="whitespace-nowrap ml-auto font-semibold text-gray-600">Rp {{ number_format($item->nilai, 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Keterangan --}}
                            @if($m->keterangan)
                            <p class="text-xs text-gray-400 mt-1.5 italic">{{ $m->keterangan }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400 italic">Belum ada riwayat transaksi.</div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-1 order-1 lg:order-2 space-y-6">
            
            <div class="relative bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white shadow-lg shadow-emerald-200">
                
                <button type="button" onclick="bukaModalEdit()" class="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center transition-colors backdrop-blur-sm tooltip" title="Edit Profil Nasabah">
                    <i class="fa-solid fa-pen text-sm"></i>
                </button>

                <div class="flex items-center gap-3 mb-6 pr-8 mt-2">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-xl backdrop-blur-sm shrink-0">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h2 class="font-bold text-lg leading-tight truncate">{{ $nasabah->nama }}</h2>
                        <p class="text-emerald-100 text-sm opacity-90 truncate">{{ $nasabah->kode }} • RT {{ $nasabah->rt }}/RW {{ $nasabah->rw }}</p>
                    </div>
                </div>
                
                <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider mb-1">Total Saldo Aktif</p>
                <h1 class="text-4xl font-black tracking-tight truncate">
                    Rp {{ number_format($nasabah->tabungan ? $nasabah->tabungan->saldo_saat_ini : 0, 0, ',', '.') }}
                </h1>

                <div class="mt-6 pt-5 border-t border-emerald-400/30 flex items-center gap-3">
                    
                    <button type="button" onclick="bukaModalTarik()" class="flex-1 bg-white text-emerald-700 hover:bg-emerald-50 font-black py-3.5 rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar text-lg"></i> Tarik Tunai
                    </button>

                    <a href="{{ route('tabungan.idcard', $nasabah->id) }}" target="_blank" class="w-14 h-[52px] bg-emerald-600 hover:bg-emerald-500 border border-emerald-400/50 text-white rounded-xl transition-colors shadow-sm flex items-center justify-center shrink-0 tooltip" title="Cetak QR ID Card">
                        <i class="fa-solid fa-qrcode text-xl"></i>
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

<div id="modalEditNasabah" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalEditBox">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Edit Profil Nasabah</h3>
            <button type="button" onclick="tutupModalEdit()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('nasabah.update', $nasabah->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" required value="{{ $nasabah->nama }}"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">RT</label>
                        <input type="text" name="rt" required maxlength="3" value="{{ $nasabah->rt }}"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none text-center">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">RW</label>
                        <input type="text" name="rw" required maxlength="3" value="{{ $nasabah->rw }}"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none text-center">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="{{ $nasabah->no_hp }}"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 bg-gray-50/50">
                <button type="button" onclick="tutupModalEdit()" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 py-2.5 rounded-xl font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-xl font-bold transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalTarikTunai" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalTarikBox">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-money-bill-transfer mr-2 text-emerald-500"></i>Form Tarik Tunai</h3>
            <button type="button" onclick="tutupModalTarik()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formTarikTunai" action="{{ route('tabungan.tarik', $nasabah->id) }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center mb-2">
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-1">Saldo Tersedia</p>
                    <h3 class="text-2xl font-black text-emerald-700">Rp {{ number_format($nasabah->tabungan ? $nasabah->tabungan->saldo_saat_ini : 0, 0, ',', '.') }}</h3>
                </div>

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
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 bg-gray-50/50">
                <button type="button" onclick="tutupModalTarik()" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 py-2.5 rounded-xl font-bold transition-colors">
                    Batal
                </button>
                <button type="button" onclick="prosesTarik()" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-xl font-bold transition-colors shadow-sm">
                    Proses Tarik
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // === MODAL EDIT ===
    const modalEdit = document.getElementById('modalEditNasabah');
    const modalEditBox = document.getElementById('modalEditBox');

    function bukaModalEdit() {
        modalEdit.classList.remove('hidden');
        modalEdit.classList.add('flex');
        setTimeout(() => {
            modalEdit.classList.remove('opacity-0');
            modalEditBox.classList.remove('scale-95');
        }, 10);
    }

    function tutupModalEdit() {
        modalEdit.classList.add('opacity-0');
        modalEditBox.classList.add('scale-95');
        setTimeout(() => {
            modalEdit.classList.add('hidden');
            modalEdit.classList.remove('flex');
        }, 300);
    }

    // === MODAL TARIK TUNAI ===
    const modalTarik = document.getElementById('modalTarikTunai');
    const modalTarikBox = document.getElementById('modalTarikBox');

    function bukaModalTarik() {
        modalTarik.classList.remove('hidden');
        modalTarik.classList.add('flex');
        setTimeout(() => {
            modalTarik.classList.remove('opacity-0');
            modalTarikBox.classList.remove('scale-95');
        }, 10);
    }

    function tutupModalTarik() {
        modalTarik.classList.add('opacity-0');
        modalTarikBox.classList.add('scale-95');
        setTimeout(() => {
            modalTarik.classList.add('hidden');
            modalTarik.classList.remove('flex');
        }, 300);
    }

    async function prosesTarik() {
        const confirmed = await showConfirm(
            'Proses penarikan dana ini? Saldo nasabah akan berkurang dan tercatat di kas induk.',
            'Konfirmasi Tarik Tunai',
            'emerald'
        );
        if (confirmed) {
            document.getElementById('formTarikTunai').submit();
        }
    }

    function filterPerBulan() {
        const bulan = document.getElementById('filterBulan').value;
        window.location.href = '{{ route("tabungan.show", $nasabah->id) }}?bulan=' + bulan;
    }
</script>
@endpush
@endsection