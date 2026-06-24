@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Data Nasabah</h1>
        <p class="text-sm text-gray-500">Kelola informasi warga yang terdaftar sebagai nasabah bank sampah.</p>
    </div>
    <button type="button" onclick="bukaModalCreate()" class="bg-emerald-600 text-white px-6 py-2.5 rounded-full font-bold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2 w-full sm:w-auto justify-center">
        <i class="fa-solid fa-user-plus"></i> Tambah Nasabah
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
    <input type="text" id="searchInput" onkeyup="cariNasabah()" placeholder="Cari nama nasabah, kode ID, RT, atau No HP..." 
        class="w-full bg-white border border-gray-200 text-gray-700 rounded-2xl pl-12 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all font-medium text-sm sm:text-base">
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="nasabahGrid">
    @forelse($nasabah as $item)
    <div class="nasabah-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden group hover:shadow-md transition-shadow" 
         data-search="{{ strtolower($item->kode . ' ' . $item->nama . ' rt ' . $item->rt . ' rw ' . $item->rw . ' ' . $item->no_hp) }}">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>

        <div class="flex items-start gap-4 mb-5 pt-2">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="overflow-hidden">
                <span class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">{{ $item->kode }}</span>
                <h3 class="font-bold text-gray-800 leading-tight truncate" title="{{ $item->nama }}">{{ $item->nama }}</h3>
            </div>
        </div>

        <div class="space-y-2 mb-6 flex-1">
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <div class="w-6 text-center text-gray-400"><i class="fa-solid fa-map-location-dot"></i></div>
                <span>RT {{ $item->rt }} / RW {{ $item->rw }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <div class="w-6 text-center text-gray-400"><i class="fa-solid fa-phone"></i></div>
                <span>{{ $item->no_hp ?? 'Belum ada No. HP' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2 pt-4 border-t border-gray-50">
            <a href="{{ route('tabungan.show', $item->id) }}" class="flex-1 flex items-center justify-center gap-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 py-2.5 rounded-xl text-sm font-bold transition-colors">
                <i class="fa-solid fa-book"></i> Tabungan
            </a>
            <button type="button" onclick="bukaModalEdit({{ $item->id }}, '{{ addslashes($item->nama) }}', '{{ $item->rt }}', '{{ $item->rw }}', '{{ $item->no_hp }}')" class="w-11 h-11 flex items-center justify-center bg-blue-50 text-blue-500 hover:bg-blue-100 rounded-xl transition-colors shrink-0" title="Edit">
                <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <form action="{{ route('nasabah.destroy', $item->id) }}" method="POST" class="shrink-0 delete-form">
                @csrf
                @method('DELETE')
                <button type="button" onclick="hapusNasabah(this)" class="w-11 h-11 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-100 rounded-xl transition-colors" title="Hapus">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center flex flex-col items-center justify-center">
        <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4 text-2xl">
            <i class="fa-solid fa-users-slash"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-700">Belum ada nasabah terdaftar</h3>
        <p class="text-gray-500 mt-1 mb-6">Silakan daftarkan warga sebagai nasabah baru untuk memulai transaksi.</p>
        <button type="button" onclick="bukaModalCreate()" class="bg-emerald-100 text-emerald-700 px-6 py-2.5 rounded-full font-bold hover:bg-emerald-200 transition">
            Daftarkan Nasabah Pertama
        </button>
    </div>
    @endforelse

    <div id="emptySearchState" class="hidden col-span-full bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center flex-col items-center justify-center">
        <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4 text-2xl mx-auto">
            <i class="fa-solid fa-magnifying-glass-minus"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-700">Pencarian tidak ditemukan</h3>
        <p class="text-gray-500 mt-1">Kata kunci yang kamu ketik tidak cocok dengan nasabah manapun.</p>
    </div>
</div>

<div id="modalCreate" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalCreateBox">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="font-bold text-gray-800">Daftarkan Nasabah Baru</h3>
                <p class="text-[10px] text-gray-500">Kode nasabah akan di-generate otomatis.</p>
            </div>
            <button type="button" onclick="tutupModalCreate()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('nasabah.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" required placeholder="Sesuai KTP"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">RT</label>
                        <input type="text" name="rt" placeholder="001" required maxlength="3"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none text-center">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">RW</label>
                        <input type="text" name="rw" placeholder="005" required maxlength="3"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none text-center">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" placeholder="0812xxxx (Opsional)"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 bg-gray-50/50">
                <button type="button" onclick="tutupModalCreate()" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 py-2.5 rounded-xl font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-xl font-bold transition-colors shadow-sm">
                    Simpan & Daftarkan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalEditBox">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Edit Data Nasabah</h3>
            <button type="button" onclick="tutupModalEdit()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditNasabah" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">RT</label>
                        <input type="text" name="rt" id="editRt" required maxlength="3"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none text-center">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">RW</label>
                        <input type="text" name="rw" id="editRw" required maxlength="3"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none text-center">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" id="editNoHp"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 bg-gray-50/50">
                <button type="button" onclick="tutupModalEdit()" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 py-2.5 rounded-xl font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-xl font-bold transition-colors shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // === FITUR PENCARIAN NASABAH REAL-TIME ===
    function cariNasabah() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let cards = document.querySelectorAll('.nasabah-card');
        let emptyState = document.getElementById('emptySearchState');
        let hasResult = false;

        cards.forEach(card => {
            let textData = card.getAttribute('data-search');
            
            // Jika teks pencarian cocok dengan data di kartu
            if (textData.includes(input)) {
                card.classList.remove('hidden');
                card.classList.add('flex'); // kembalikan ke format flex bawaan tailwind
                hasResult = true;
            } else {
                card.classList.add('hidden');
                card.classList.remove('flex');
            }
        });

        // Menampilkan pesan jika tidak ada kartu yang cocok
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
    const formEdit = document.getElementById('formEditNasabah');

    function bukaModalEdit(id, nama, rt, rw, no_hp) {
        formEdit.action = `/nasabah/${id}`;
        document.getElementById('editNama').value = nama;
        document.getElementById('editRt').value = rt;
        document.getElementById('editRw').value = rw;
        document.getElementById('editNoHp').value = no_hp || ''; 

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

    async function hapusNasabah(btn) {
        const confirmed = await showConfirm(
            'Nonaktifkan nasabah ini? Data transaksi dan tabungan tetap tersimpan, namun nasabah tidak akan muncul lagi di daftar aktif.',
            'Konfirmasi Nonaktifkan Nasabah',
            'red'
        );
        if (confirmed) {
            btn.closest('.delete-form').submit();
        }
    }
</script>
@endpush
@endsection