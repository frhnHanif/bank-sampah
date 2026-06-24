@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Master Data Kategori Sampah</h1>
        <p class="text-sm text-gray-500">Kelola jenis sampah, harga beli, dan estimasi reduksi emisi.</p>
    </div>
    <button type="button" onclick="bukaModalCreate()" class="bg-emerald-600 text-white px-6 py-2.5 rounded-full font-bold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2 w-full sm:w-auto justify-center">
        <i class="fa-solid fa-plus"></i> Tambah Kategori
    </button>
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

<div class="mb-6 relative">
    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
        <i class="fa-solid fa-magnifying-glass text-gray-400 text-lg"></i>
    </div>
    <input type="text" id="searchInput" onkeyup="cariKategori()" placeholder="Cari nama kategori sampah..." 
        class="w-full bg-white border border-gray-200 text-gray-700 rounded-2xl pl-12 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all font-medium text-sm sm:text-base">
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="kategoriGrid">
    @forelse($kategori as $item)
    <div class="kategori-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden group hover:shadow-md transition-shadow" 
         data-search="{{ strtolower($item->nama) }}">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>

        <div class="flex items-start gap-4 mb-5 pt-2">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div class="overflow-hidden pt-1">
                <h3 class="font-bold text-gray-800 leading-tight truncate" title="{{ $item->nama }}">{{ $item->nama }}</h3>
            </div>
        </div>

        <div class="space-y-4 mb-6 flex-1">
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Harga Beli</p>
                <p class="text-xl font-black text-emerald-600">Rp {{ number_format($item->harga_beli_per_kg, 0, ',', '.') }}<span class="text-sm font-medium text-gray-500">/kg</span></p>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Reduksi Emisi (kg CO₂/kg)</p>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-leaf text-emerald-500"></i>
                    <span class="text-sm font-bold text-gray-700">{{ $item->faktor_emisi }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 pt-4 border-t border-gray-50">
            <button type="button" onclick="bukaModalEdit({{ $item->id }}, '{{ addslashes($item->nama) }}', {{ $item->harga_beli_per_kg }}, {{ $item->faktor_emisi }})" class="flex-1 flex items-center justify-center gap-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 py-2.5 rounded-xl text-sm font-bold transition-colors">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </button>
            <form action="{{ route('kategori.destroy', $item->id) }}" method="POST" class="shrink-0 delete-form">
                @csrf
                @method('DELETE')
                <button type="button" onclick="hapusKategori(this)" class="w-11 h-11 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-100 rounded-xl transition-colors" title="Hapus">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center flex flex-col items-center justify-center">
        <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4 text-2xl">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-700">Belum ada kategori sampah</h3>
        <p class="text-gray-500 mt-1 mb-6">Silakan tambahkan jenis sampah beserta harganya terlebih dahulu.</p>
        <button type="button" onclick="bukaModalCreate()" class="bg-emerald-100 text-emerald-700 px-6 py-2.5 rounded-full font-bold hover:bg-emerald-200 transition">
            Tambah Kategori Pertama
        </button>
    </div>
    @endforelse

    <div id="emptySearchState" class="hidden col-span-full bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center flex-col items-center justify-center">
        <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4 text-2xl mx-auto">
            <i class="fa-solid fa-magnifying-glass-minus"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-700">Pencarian tidak ditemukan</h3>
        <p class="text-gray-500 mt-1">Kata kunci yang kamu ketik tidak cocok dengan kategori manapun.</p>
    </div>
</div>

<div id="modalCreate" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalCreateBox">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Tambah Kategori Baru</h3>
            <button type="button" onclick="tutupModalCreate()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('kategori.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Sampah</label>
                    <input type="text" name="nama" required placeholder="Misal: Kardus Bekas"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Harga Beli per Kg (Rp)</label>
                    <input type="text" inputmode="numeric" name="harga_beli_per_kg" required placeholder="Contoh: 1.500"
                        class="input-rupiah w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Faktor Emisi (kg CO₂/kg)</label>
                    <input type="number" step="0.0001" name="faktor_emisi" required min="0" placeholder="Contoh: 0.5"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                    <p class="text-[10px] text-gray-400 mt-1">Nilai konversi pengurangan emisi karbon per kilogram.</p>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 bg-gray-50/50">
                <button type="button" onclick="tutupModalCreate()" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 py-2.5 rounded-xl font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-xl font-bold transition-colors shadow-sm">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalEditBox">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Edit Kategori Sampah</h3>
            <button type="button" onclick="tutupModalEdit()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditKategori" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Sampah</label>
                    <input type="text" name="nama" id="editNama" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Harga Beli per Kg (Rp)</label>
                    <input type="text" inputmode="numeric" name="harga_beli_per_kg" id="editHarga" required
                        class="input-rupiah w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Faktor Emisi (kg CO₂/kg)</label>
                    <input type="number" step="0.0001" name="faktor_emisi" id="editEmisi" required min="0"
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

@push('scripts')
<script>
    // === FITUR PENCARIAN KATEGORI REAL-TIME ===
    function cariKategori() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let cards = document.querySelectorAll('.kategori-card');
        let emptyState = document.getElementById('emptySearchState');
        let hasResult = false;

        cards.forEach(card => {
            let textData = card.getAttribute('data-search');
            
            if (textData.includes(input)) {
                card.classList.remove('hidden');
                card.classList.add('flex');
                hasResult = true;
            } else {
                card.classList.add('hidden');
                card.classList.remove('flex');
            }
        });

        if (cards.length > 0) {
            if (!hasResult) {
                emptyState.classList.remove('hidden');
                emptyState.classList.add('flex');
            } else {
                emptyState.classList.add('hidden');
                emptyState.classList.remove('flex');
            }
        }
    }

    // === MODAL CREATE ===
    const modalCreate = document.getElementById('modalCreate');
    const modalCreateBox = document.getElementById('modalCreateBox');

    function bukaModalCreate() {
        modalCreate.classList.remove('hidden');
        modalCreate.classList.add('flex');
        setTimeout(() => {
            modalCreate.classList.remove('opacity-0');
            modalCreateBox.classList.remove('scale-95');
        }, 10);
    }

    function tutupModalCreate() {
        modalCreate.classList.add('opacity-0');
        modalCreateBox.classList.add('scale-95');
        setTimeout(() => {
            modalCreate.classList.add('hidden');
            modalCreate.classList.remove('flex');
        }, 300);
    }

    // === MODAL EDIT ===
    const modalEdit = document.getElementById('modalEdit');
    const modalEditBox = document.getElementById('modalEditBox');
    const formEdit = document.getElementById('formEditKategori');

    function bukaModalEdit(id, nama, harga, emisi) {
        formEdit.action = `/kategori/${id}`;
        
        document.getElementById('editNama').value = nama;
        document.getElementById('editHarga').value = parseInt(harga).toLocaleString('id-ID');
        document.getElementById('editEmisi').value = emisi;

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

    async function hapusKategori(btn) {
        const confirmed = await showConfirm(
            'Nonaktifkan kategori ini? Data transaksi tetap tersimpan, namun kategori tidak akan muncul lagi di daftar aktif.',
            'Konfirmasi Nonaktifkan Kategori',
            'red'
        );
        if (confirmed) {
            btn.closest('.delete-form').submit();
        }
    }
</script>
@endpush
@endsection