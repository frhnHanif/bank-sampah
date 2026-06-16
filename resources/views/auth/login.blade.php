<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pengurus — Bank Sampah</title>
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

    <div class="w-full max-w-sm">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('img/logo-kiri.png') }}" alt="Bank Sampah" class="h-12 mx-auto object-contain mb-3">
            </a>
            <h1 class="text-lg font-bold text-gray-800">Login Pengurus</h1>
            <p class="text-xs text-gray-400 mt-1">Masuk untuk mengelola bank sampah</p>
        </div>

        {{-- Form --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-xs font-medium px-4 py-3 rounded-xl">
                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Password</label>
                        <input type="password" name="password" required
                               class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="remember" class="text-xs text-gray-500">Ingat saya</label>
                    </div>
                </div>
                <button type="submit" class="w-full mt-5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2.5 rounded-xl transition-colors">
                    <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Masuk
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
