@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto pb-8">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Konfigurasi Sistem</h1>
            <p class="text-gray-500 text-sm font-medium mt-1">Kelola akun pengurus bank sampah.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="toggleModal('modalKonstanta', true)" class="text-xs font-bold text-gray-500 hover:text-emerald-600 bg-gray-50 hover:bg-emerald-50 border border-gray-200 px-4 py-2 rounded-xl transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-sliders"></i> Konstanta
            </button>
            <a href="{{ route('konfigurasi.pin.logout') }}" 
               class="text-xs font-bold text-red-500 hover:text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 px-4 py-2 rounded-xl transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- MANAJEMEN AKUN PENGURUS --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fa-solid fa-users-gear text-emerald-600"></i>
            <h3 class="font-bold text-gray-800 text-sm">Daftar Akun Pengurus</h3>
            <span class="text-[11px] text-gray-400 ml-auto">{{ $users->count() }} akun</span>
        </div>

        <div class="px-5 pt-4">
            <button onclick="toggleModal('modalTambah', true)" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Akun
            </button>
        </div>

        <div class="p-5 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        <th class="pb-3 pr-4">Nama</th>
                        <th class="pb-3 pr-4">Email</th>
                        <th class="pb-3 pr-4">Dibuat</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                    <tr>
                        <td class="py-3 pr-4 font-semibold text-gray-700">{{ $user->name }}</td>
                        <td class="py-3 pr-4 text-gray-500 text-xs">{{ $user->email }}</td>
                        <td class="py-3 pr-4 text-gray-400 text-xs">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
                                        class="text-xs font-bold text-gray-500 hover:text-emerald-600 bg-gray-50 hover:bg-emerald-50 px-3 py-1.5 rounded-lg transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <form action="{{ route('konfigurasi.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun {{ addslashes($user->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-gray-400 hover:text-red-600 bg-gray-50 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors"
                                            {{ auth()->check() && $user->id === auth()->id() ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-10 text-center text-gray-400 font-medium italic">Belum ada akun pengurus terdaftar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ==================== MODAL TAMBAH AKUN ==================== --}}
<div id="modalTambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h4 class="font-bold text-gray-800 text-base">Tambah Akun Pengurus</h4>
            <button onclick="toggleModal('modalTambah', false)" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
        </div>
        <form action="{{ route('konfigurasi.users.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Nama</label>
                    <input type="text" name="name" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Password</label>
                    <input type="password" name="password" required minlength="6" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modalTambah', false)" class="text-sm font-bold text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">Batal</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2 rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL EDIT AKUN ==================== --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h4 class="font-bold text-gray-800 text-base">Edit Akun Pengurus</h4>
            <button onclick="toggleModal('modalEdit', false)" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
        </div>
        <form id="formEdit" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Nama</label>
                    <input type="text" name="name" id="editName" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Email</label>
                    <input type="email" name="email" id="editEmail" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Password <span class="text-gray-300 font-normal">(kosongkan jika tidak berubah)</span></label>
                    <input type="password" name="password" minlength="6" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modalEdit', false)" class="text-sm font-bold text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">Batal</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2 rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL KONSTANTA ==================== --}}
<div id="modalKonstanta" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[85vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h4 class="font-bold text-gray-800 text-base">Konstanta Ekuivalen CO₂</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Nilai default sesuai standar ilmiah</p>
                </div>
                <button onclick="toggleModal('modalKonstanta', false)" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
            </div>
            <form action="{{ route('konfigurasi.settings.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-50">
                                <th class="pb-3 pr-4">Label</th>
                                <th class="pb-3 pr-4">Nilai</th>
                                <th class="pb-3 pr-4">Satuan</th>
                                <th class="pb-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pengaturan as $set)
                            <tr>
                                <td class="py-3 pr-4 font-semibold text-gray-700 text-xs">{{ $set->label }}</td>
                                <td class="py-3 pr-4">
                                    <input type="{{ $set->kunci === 'admin_pin' ? 'password' : 'text' }}" 
                                           name="nilai[{{ $set->id }}]" 
                                           value="{{ $set->nilai }}"
                                           class="w-36 px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                                </td>
                                <td class="py-3 pr-4 text-gray-400 text-[11px] whitespace-nowrap">{{ $set->satuan ?? '-' }}</td>
                                <td class="py-3 text-gray-400 text-[11px]">{{ $set->keterangan }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-50 flex justify-end gap-3">
                    <button type="button" onclick="toggleModal('modalKonstanta', false)" class="text-sm font-bold text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">Batal</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModal(id, show) {
        document.getElementById(id).classList.toggle('hidden', !show);
        document.getElementById(id).classList.toggle('flex', show);
    }
    function editUser(id, name, email) {
        document.getElementById('formEdit').action = '/konfigurasi/users/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        toggleModal('modalEdit', true);
    }
    document.querySelectorAll('#modalTambah, #modalEdit, #modalKonstanta').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) toggleModal(this.id, false);
        });
    });
</script>
@endsection
