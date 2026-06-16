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

        /* === Custom Select === */
        .custom-select-trigger { cursor: pointer; user-select: none; }
        .custom-select-dropdown { 
            position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 60;
            max-height: 220px; overflow-y: auto;
            opacity: 0; visibility: hidden; transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
        }
        .custom-select.open .custom-select-dropdown {
            opacity: 1; visibility: visible; transform: translateY(0);
        }
        .custom-select.open .custom-select-chevron { transform: rotate(180deg); }
        .custom-select-option { cursor: pointer; transition: background 0.15s; }
        .custom-select-option:hover { background: #f3f4f6; }
        .custom-select-option.selected { background: #fef3c7; font-weight: 600; color: #92400e; }
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
            
            <a href="{{ route('stok.index') }}" class="{{ request()->routeIs('stok.*') ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 font-medium' }} px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2">
                 <i class="fa-solid fa-boxes-stacked"></i> Stok
            </a>

            <a href="{{ route('keuangan.index') }}" class="{{ request()->routeIs('keuangan.*') ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 font-medium' }} px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-wallet"></i> Keuangan
            </a>

            <a href="{{ route('kategori.index') }}" class="{{ request()->routeIs('kategori.*') ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 font-medium' }} px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-tags"></i> Kategori
            </a>
            
            <a href="{{ route('nasabah.index') }}" class="{{ request()->routeIs('nasabah.*') || request()->routeIs('tabungan.*') ? 'bg-white shadow-sm text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 font-medium' }} px-5 py-2 rounded-full text-sm transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-users"></i> Nasabah
            </a>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <div class="px-2 py-1.5 sm:px-3 sm:py-2  flex items-center justify-center">
                <img src="{{ asset('img/desktop_icon.png') }}" alt="Sponsor Logos Desktop" class="hidden md:block h-7 object-contain">
                
                <img src="{{ asset('img/mobile_icon.png') }}" alt="Sponsor Logos Mobile" class="block md:hidden h-7 object-contain">
            </div>

            <div class="w-10 h-10 bg-gray-50 border border-gray-200 rounded-full flex items-center justify-center text-gray-400 cursor-default shadow-inner">
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
        <a href="{{ route('stok.index') }}" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-full transition-all duration-300 {{ request()->routeIs('stok.*') ? 'bg-white shadow-sm text-emerald-500 font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }}">
            <i class="fa-solid fa-boxes-stacked text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-wide">Stok</span>
        </a>
        <a href="{{ route('keuangan.index') }}" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-full transition-all duration-300 {{ request()->routeIs('keuangan.*') ? 'bg-white shadow-sm text-emerald-500 font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }}">
            <i class="fa-solid fa-wallet text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-wide">Keuangan</span>
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

    <!-- ===== TOAST NOTIFICATION ===== -->
    <div id="toastContainer" class="fixed top-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none"></div>

    <!-- ===== CONFIRM MODAL ===== -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[200] hidden items-center justify-center opacity-0 transition-opacity duration-300 p-4">
        <div id="confirmModalBox" class="bg-white rounded-2xl w-full max-w-md mx-auto overflow-hidden transform scale-95 transition-transform duration-300 shadow-xl">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <h3 id="confirmTitle" class="text-lg font-bold text-gray-800 mb-2">Konfirmasi</h3>
                <p id="confirmMessage" class="text-gray-600 text-sm mb-6">Apakah Anda yakin?</p>
                <div class="flex gap-3 justify-center">
                    <button type="button" id="confirmCancelBtn" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition text-sm">
                        Batal
                    </button>
                    <button type="button" id="confirmOkBtn" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition text-sm">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    // ==================== TOAST SYSTEM ====================
    function showToast(message, type = 'error') {
        const container = document.getElementById('toastContainer');

        const config = {
            error:   { bg: 'bg-red-500',   icon: 'fa-circle-xmark' },
            success: { bg: 'bg-emerald-500', icon: 'fa-circle-check' },
            warning: { bg: 'bg-amber-500',  icon: 'fa-triangle-exclamation' },
            info:    { bg: 'bg-blue-500',   icon: 'fa-circle-info' },
        };
        const { bg, icon } = config[type] || config.error;

        const toast = document.createElement('div');
        toast.className = `${bg} text-white px-5 py-3 rounded-2xl shadow-lg flex items-center gap-3 pointer-events-auto transform translate-x-full opacity-0 transition-all duration-300 max-w-sm`;
        toast.innerHTML = `
            <i class="fa-solid ${icon} shrink-0"></i>
            <span class="text-sm font-medium">${message}</span>
        `;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        });

        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // ==================== CONFIRM MODAL SYSTEM ====================
    function showConfirm(message = 'Apakah Anda yakin?', title = 'Konfirmasi') {
        return new Promise((resolve) => {
            const modal = document.getElementById('confirmModal');
            const box = document.getElementById('confirmModalBox');
            const titleEl = document.getElementById('confirmTitle');
            const msgEl = document.getElementById('confirmMessage');
            const okBtn = document.getElementById('confirmOkBtn');
            const cancelBtn = document.getElementById('confirmCancelBtn');

            titleEl.textContent = title;
            msgEl.textContent = message;

            function cleanup() {
                modal.classList.add('opacity-0');
                box.classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 300);
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
            }

            function onOk() {
                cleanup();
                resolve(true);
            }

            function onCancel() {
                cleanup();
                resolve(false);
            }

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                box.classList.remove('scale-95');
            });
        });
    }

    // ==================== CUSTOM SELECT SYSTEM ====================
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.custom-select').forEach(wrapper => {
            const nativeSelect = wrapper.querySelector('select');
            const trigger = wrapper.querySelector('.custom-select-trigger');
            const label = wrapper.querySelector('.custom-select-label');
            const dropdown = wrapper.querySelector('.custom-select-dropdown');
            const options = dropdown.querySelectorAll('.custom-select-option');

            function findOptionByValue(value) {
                return Array.from(options).find(o => o.dataset.value === value);
            }

            function findOptionIndexByValue(value) {
                return Array.from(options).findIndex(o => o.dataset.value === value);
            }

            function updateLabel() {
                const selectedOption = findOptionByValue(nativeSelect.value);
                if (selectedOption) {
                    label.textContent = selectedOption.textContent.trim();
                    options.forEach(o => o.classList.remove('selected'));
                    selectedOption.classList.add('selected');
                } else {
                    // Kembalikan ke placeholder
                    label.textContent = '-- Pilih Barang --';
                    label.classList.add('text-gray-400');
                    options.forEach(o => o.classList.remove('selected'));
                }
            }

            function open() {
                closeAllSelects();
                wrapper.classList.add('open');
                const selOpt = findOptionByValue(nativeSelect.value);
                if (selOpt) selOpt.scrollIntoView({ block: 'nearest' });
            }

            function close() {
                wrapper.classList.remove('open');
            }

            function selectOption(optionEl) {
                const value = optionEl.dataset.value;
                nativeSelect.value = value;
                label.classList.remove('text-gray-400');
                updateLabel();
                close();
                nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                wrapper.classList.contains('open') ? close() : open();
            });

            options.forEach(opt => {
                opt.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectOption(opt);
                });
            });

            // Keyboard navigation
            trigger.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    wrapper.classList.contains('open') ? close() : open();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!wrapper.classList.contains('open')) open();
                    const curIdx = findOptionIndexByValue(nativeSelect.value);
                    const nextIdx = Math.min(curIdx + 1, options.length - 1);
                    if (nextIdx >= 0) selectOption(options[nextIdx]);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!wrapper.classList.contains('open')) open();
                    const curIdx = findOptionIndexByValue(nativeSelect.value);
                    const prevIdx = Math.max(curIdx - 1, 0);
                    if (prevIdx >= 0) selectOption(options[prevIdx]);
                } else if (e.key === 'Escape') {
                    close();
                }
            });

            updateLabel();
        });

        // Close all on outside click
        document.addEventListener('click', () => closeAllSelects());
    });

    function closeAllSelects() {
        document.querySelectorAll('.custom-select.open').forEach(el => el.classList.remove('open'));
    }
</script>
</body>
</html>