<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Sampah — Cek Rekening Nasabah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('img/logo-kiri.png') }}" alt="Bank Sampah" class="h-12 mx-auto object-contain mb-3">
            </a>
            <h1 class="text-lg font-bold text-gray-800">Bank Sampah</h1>
            <p class="text-xs text-gray-400 mt-1">Sistem manajemen bank sampah</p>
        </div>

        {{-- Label --}}
        <div class="text-center mb-4">
            <span class="inline-block bg-sky-50 border border-sky-200 text-sky-700 text-xs font-bold px-4 py-1.5 rounded-full">
                <i class="fa-solid fa-book-open mr-1.5"></i> Cek Rekening Nasabah
            </span>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium px-4 py-3 rounded-xl">
                <i class="fa-solid fa-circle-check mr-1.5"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-xs font-medium px-4 py-3 rounded-xl">
                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ========== FORM CEK REKENING NASABAH ========== --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            @if($errors->has('kode') || $errors->has('no_hp'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-xs font-medium px-4 py-3 rounded-xl">
                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ $errors->first() }}
                </div>
            @endif

            <p class="text-xs text-gray-400 mb-5 text-center">Masukkan ID dan No HP Anda untuk melihat buku tabungan</p>

            <form action="{{ route('nasabah.cek') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">ID / Kode Nasabah</label>
                        <input type="text" name="kode" value="{{ old('kode') }}" required
                               placeholder="Contoh: 001001001"
                               class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">No HP / WhatsApp</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}" required
                               placeholder="Contoh: 08123456789"
                               class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full mt-5 bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold py-2.5 rounded-xl transition-colors">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Lihat Rekening
                </button>
            </form>
        </div>

        <p class="text-center mt-6 text-xs text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke dashboard
            </a>
        </p>
    </div>

</body>
</html>
