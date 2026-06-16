<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIN Admin — Bank Sampah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-xs">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-2xl text-gray-400"></i>
            </div>
            <h1 class="text-lg font-bold text-gray-800">Konfigurasi Sistem</h1>
            <p class="text-xs text-gray-400 mt-1">Masukkan PIN admin untuk melanjutkan</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            @if(session('pin_error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-xs font-medium px-4 py-3 rounded-xl flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ session('pin_error') }}
                </div>
            @endif

            <form action="{{ route('konfigurasi.pin.verify') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">PIN Admin</label>
                    <input type="password" name="pin" required autofocus maxlength="20" inputmode="numeric"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm text-center tracking-[0.3em] font-mono text-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                           placeholder="••••••">
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2.5 rounded-xl transition-colors">
                    <i class="fa-solid fa-lock-open mr-2"></i> Buka Konfigurasi
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
