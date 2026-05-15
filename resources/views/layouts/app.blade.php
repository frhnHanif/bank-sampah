<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Bank Sampah</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#10b981">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body { background-color: #F8F9FA; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="text-gray-800 antialiased font-sans relative">

    <nav class="fixed top-4 left-4 right-4 z-50 bg-white/90 backdrop-blur-md shadow-sm border border-gray-100 rounded-[2rem] px-4 sm:px-6 py-2.5 flex justify-between items-center max-w-screen-2xl mx-auto">
        
        <div class="flex items-center shrink-0">
            <a href="{{ route('dashboard') }}" class="block">
                <img src="{{ asset('img/logo-kiri.png') }}" alt="Bank Sampah Ngudia Wilujeng" class="h-9 sm:h-11 object-contain">
            </a>
        </div>

        <div class="hidden lg:flex items-center bg-gray-50 p-1 rounded-full border border-gray-200 shadow-inner">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 font-medium' }} px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-border-all"></i> Dashboard
            </a>

            <a href="{{ route('setor.create') }}" class="{{ request()->routeIs('setor.*') ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 font-medium' }} px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-cash-register"></i> Kasir
            </a>
            
            <a href="{{ route('kategori.index') }}" class="{{ request()->routeIs('kategori.*') ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 font-medium' }} px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-tags"></i> Kategori
            </a>
            
            <a href="{{ route('nasabah.index') }}" class="{{ request()->routeIs('nasabah.*') || request()->routeIs('tabungan.*') ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 font-medium' }} px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-users"></i> Nasabah
            </a>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <div class="bg-white px-2 py-1.5 sm:px-3 sm:py-2 rounded-full border border-gray-200 shadow-sm flex items-center justify-center">
                <img src="{{ asset('img/desktop_icon.png') }}" alt="Sponsor Logos Desktop" class="hidden md:block h-7 object-contain">
                
                <img src="{{ asset('img/mobile_icon.png') }}" alt="Sponsor Logos Mobile" class="block md:hidden h-5 object-contain">
            </div>

            <div class="w-10 h-10 bg-gray-50 border border-gray-200 rounded-full hidden sm:flex items-center justify-center text-gray-400 cursor-default shadow-inner">
                <i class="fa-solid fa-user-shield text-sm"></i>
            </div>
        </div>
    </nav>

    <nav class="lg:hidden fixed bottom-4 left-4 right-4 z-50 bg-gray-50/95 backdrop-blur-xl p-1.5 rounded-[2rem] border border-gray-200 shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-full transition-all duration-300 text-gray-400">
            <i class="fa-solid fa-border-all text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-wide">Home</span>
        </a>
        <a href="{{ route('setor.create') }}" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-full transition-all duration-300 {{ request()->routeIs('setor.*') ? 'bg-white shadow-sm text-emerald-500 font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }}">
            <i class="fa-solid fa-cash-register text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-wide">Kasir</span>
        </a>
        <a href="{{ route('kategori.index') }}" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-full transition-all duration-300 {{ request()->routeIs('kategori.*') ? 'bg-white shadow-sm text-emerald-500 font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }}">
            <i class="fa-solid fa-tags text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-wide">Kategori</span>
        </a>
        <a href="{{ route('nasabah.index') }}" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-full transition-all duration-300 {{ request()->routeIs('nasabah.*') ? 'bg-white shadow-sm text-emerald-500 font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }}">
            <i class="fa-solid fa-users text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-wide">Nasabah</span>
        </a>
    </nav>

    <main class="pt-28 pb-28 lg:pb-12 px-4 sm:px-6 max-w-screen-2xl mx-auto relative z-30">
        @yield('content')
    </main>

    @stack('scripts')    
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('ServiceWorker terdaftar sukses dengan scope: ', registration.scope);
                })
                .catch(err => {
                    console.log('Pendaftaran ServiceWorker gagal: ', err);
                });
        });
    }
</script>
</body>
</html>