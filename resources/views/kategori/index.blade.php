@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Master Data Kategori Sampah</h1>
        <p class="text-sm text-gray-500">Kelola jenis sampah, harga beli, dan estimasi reduksi emisi.</p>
    </div>
    <button type="button" onclick="bukaModalCreate()" class="bg-emerald-600 text-white px-6 py-2.5 rounded-full font-bold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
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
                            <button type="button" onclick="bukaModalEdit({{ $item->id }}, '{{ addslashes($item->nama) }}', {{ $item->harga_beli_per_kg }}, {{ $item->faktor_emisi }})" class="text-emerald-500 hover:text-emerald-700 transition p-2 bg-emerald-50 rounded-lg hover:bg-emerald-100 tooltip" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            
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
                    <input type="number" name="harga_beli_per_kg" required min="0" placeholder="Contoh: 1500"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
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
                    <input type="number" name="harga_beli_per_kg" id="editHarga" required min="0"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
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
        // Set action form URL secara dinamis sesuai ID yang diedit
        formEdit.action = `/kategori/${id}`;
        
        // Isi inputan dengan data yang dilempar dari tombol
        document.getElementById('editNama').value = nama;
        document.getElementById('editHarga').value = harga;
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
</script>
@endpush
@endsection