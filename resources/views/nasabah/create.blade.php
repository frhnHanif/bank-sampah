@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('nasabah.index') }}" class="text-emerald-600 font-bold text-sm flex items-center gap-2 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
        <h1 class="text-2xl font-black text-gray-800 mb-2 tracking-tight">Daftarkan Nasabah</h1>
        <p class="text-gray-500 text-sm mb-8">Masukkan data diri nasabah baru untuk mulai transaksi tabungan.</p>

        <form action="{{ route('nasabah.store') }}" method="POST" class="space-y-6">
            @csrf
            <!-- <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Kode Nasabah (Unik)</label>
                <input type="text" name="kode" placeholder="Contoh: NSB-001" required value="{{ old('kode') }}"
                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all outline-none">
                @error('kode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div> -->

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                <input type="text" name="nama" required value="{{ old('nama') }}"
                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">RT</label>
                    <input type="text" name="rt" placeholder="001" required maxlength="3" value="{{ old('rt') }}"
                        class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all outline-none text-center">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">RW</label>
                    <input type="text" name="rw" placeholder="005" required maxlength="3" value="{{ old('rw') }}"
                        class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all outline-none text-center">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nomor HP / WhatsApp</label>
                <input type="text" name="no_hp" placeholder="0812xxxx" value="{{ old('no_hp') }}"
                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all outline-none">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-4 rounded-2xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all active:scale-[0.98]">
                    Simpan & Daftarkan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection