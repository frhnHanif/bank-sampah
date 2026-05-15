@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Data Nasabah</h1>
        <p class="text-sm text-gray-500">Kelola informasi warga yang terdaftar sebagai nasabah bank sampah.</p>
    </div>
    <button type="button" onclick="bukaModalCreate()" class="bg-emerald-600 text-white px-6 py-2.5 rounded-full font-bold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
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

                            <button type="button" onclick="bukaModalEdit({{ $item->id }}, '{{ addslashes($item->nama) }}', '{{ $item->rt }}', '{{ $item->rw }}', '{{ $item->no_hp }}')" class="text-blue-500 hover:text-blue-700 transition p-2 bg-blue-50 rounded-lg hover:bg-blue-100 tooltip" title="Edit Nasabah">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            
                            <form action="{{ route('nasabah.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus nasabah ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition p-2 bg-red-50 rounded-lg hover:bg-red-100 tooltip" title="Hapus">
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
    const formEdit = document.getElementById('formEditNasabah');

    function bukaModalEdit(id, nama, rt, rw, no_hp) {
        // Set action form URL dinamis
        formEdit.action = `/nasabah/${id}`;
        
        // Isi inputan
        document.getElementById('editNama').value = nama;
        document.getElementById('editRt').value = rt;
        document.getElementById('editRw').value = rw;
        document.getElementById('editNoHp').value = no_hp || ''; // handle null

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