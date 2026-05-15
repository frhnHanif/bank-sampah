@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Kasir Penyetoran</h1>
        <p class="text-sm text-gray-500">Pilih nasabah dan masukkan berat sampah.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-100 text-emerald-700 px-4 py-3 rounded-xl mb-6 font-medium">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-xl mb-6 font-medium border border-red-200">
            <div class="flex items-center mb-2">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                <strong>Transaksi Gagal Disimpan:</strong>
            </div>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formTransaksi" action="{{ route('setor.store') }}" method="POST">
        @csrf
        <input type="hidden" name="cart_data" id="cartDataInput">
        <input type="hidden" name="tanggal" value="{{ date('Y-m-d') }}">

        <div class="mb-8">
            <label class="block font-bold text-gray-800 mb-3">Cari Nasabah</label>
            
            <div class="relative flex items-center gap-3" id="nasabahInputContainer">
                <div class="relative flex-1" id="searchWrapper">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    
                    <input type="hidden" name="nasabah_id" id="nasabahIdInput" required>
                    
                    <input type="text" id="searchInput" autocomplete="off" 
                        placeholder="Ketik ID atau nama nasabah..." 
                        oninput="handleSearch()"
                        onfocus="handleSearch()"
                        class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all">
                    
                    <div id="searchResults" class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto divide-y divide-gray-50">
                        </div>
                </div>

                <button type="button" onclick="bukaModalQR()" class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition shadow-sm flex-shrink-0">
                    <i class="fa-solid fa-qrcode"></i>
                </button>
            </div>

            <div id="nasabahCard" class="hidden mt-3 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 transition-all">
                <i class="fa-regular fa-circle-check text-emerald-500 text-xl"></i>
                <div>
                    <h4 class="font-bold text-emerald-600" id="infoNamaNasabah">Nama Nasabah</h4>
                    <p class="text-sm text-emerald-500/80">ID: <span id="infoKodeNasabah">000</span> • Saldo: Rp <span id="infoSaldoNasabah">0</span></p>
                </div>
                <button type="button" onclick="resetNasabah()" class="ml-auto text-emerald-600/50 hover:text-emerald-600 text-sm underline">Ganti</button>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex justify-between items-end mb-3">
                <label class="block font-bold text-gray-800">Pilih Jenis Sampah</label>
                <span id="kategoriWarning" class="text-xs text-red-500 font-medium bg-red-50 px-2 py-1 rounded">Pilih nasabah terlebih dahulu!</span>
            </div>
            
            <div id="kategoriContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 opacity-50 transition-all duration-300">
                @foreach($kategori as $k)
                    <button type="button" onclick="bukaModal({{ $k->id }}, '{{ $k->nama }}', {{ $k->harga_beli_per_kg }}, {{ $k->faktor_emisi }})" 
                        class="bg-white border border-gray-200 rounded-2xl p-6 text-center hover:border-emerald-500 hover:shadow-md transition-all group focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <div class="w-12 h-12 mx-auto bg-emerald-500 text-white rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm mb-1">{{ $k->nama }}</h4>
                        <p class="text-xs text-emerald-600 font-medium">Rp {{ number_format($k->harga_beli_per_kg, 0, ',', '.') }}/kg</p>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm mb-8">
            <h3 class="font-bold text-gray-800 mb-4">Ringkasan Transaksi</h3>
            
            <div id="cartEmptyState" class="py-12 text-center border-2 border-dashed border-gray-200 rounded-xl">
                <p class="text-gray-400">Belum ada item yang ditambahkan</p>
            </div>

            <div id="cartList" class="hidden">
                <div class="space-y-3" id="cartItemsContainer">
                    </div>
                
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Nilai</p>
                        <h2 class="text-3xl font-black text-emerald-600" id="totalNilaiTxt">Rp 0</h2>
                        <p class="text-xs text-gray-400 mt-1">Estimasi Reduksi: <span id="totalCo2Txt">0</span> kg CO₂</p>
                    </div>
                    <button type="button" onclick="simpanTransaksi()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all">
                        Simpan Transaksi
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="modalInput" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalBox">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Input Berat Sampah</h3>
            <button type="button" onclick="tutupModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="p-6">
            <div class="bg-gray-50 rounded-xl p-4 text-center mb-6 border border-gray-100">
                <h4 class="font-bold text-gray-800 text-lg" id="modalNamaSampah">Nama Sampah</h4>
                <p class="text-sm text-gray-500" id="modalHargaSampah">Rp 0 per kilogram</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Berat (kg)</label>
                <input type="number" id="inputBeratModal" step="0.01" min="0.1" placeholder="0.00" 
                    oninput="hitungSubtotal()"
                    class="w-full border border-emerald-500 rounded-xl px-4 py-3 text-center text-lg focus:outline-none focus:ring-4 focus:ring-emerald-50 transition-all font-medium">
            </div>

            <div class="bg-emerald-50 rounded-xl p-4 text-center mb-6 border border-emerald-100">
                <p class="text-sm text-emerald-600/70 font-medium mb-1">Subtotal</p>
                <h3 class="text-2xl font-bold text-emerald-600" id="modalSubtotalTxt">Rp 0</h3>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="tutupModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-bold transition-colors">
                    Batal
                </button>
                <button type="button" onclick="tambahItem()" class="flex-1 bg-emerald-400 hover:bg-emerald-500 text-white py-3 rounded-xl font-bold transition-colors shadow-md shadow-emerald-200">
                    Tambah
                </button>
            </div>
        </div>
    </div>
</div>

<div id="modalQrScanner" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[110] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300 shadow-xl" id="modalQrBox">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-camera"></i> Scan QR Code
            </h3>
            <button type="button" onclick="tutupModalQR()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="p-6 space-y-4">
            <div class="relative w-full h-64 bg-black rounded-xl flex items-center justify-center overflow-hidden shadow-inner">
                <video id="qrVideo" class="w-full h-full object-cover hidden" playsinline></video>
                
                <div id="qrLoading" class="text-center">
                    <div class="animate-spin h-8 w-8 border-2 border-emerald-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                    <p class="text-gray-300 text-sm">Menyalakan kamera...</p>
                </div>

                <div id="qrError" class="hidden text-center p-4">
                    <p class="text-red-400 text-sm" id="qrErrorMsg"></p>
                </div>

                <div id="qrOverlay" class="hidden absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-48 h-48 border-2 border-white/50 rounded-lg relative">
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-emerald-500 rounded-tl"></div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-emerald-500 rounded-tr"></div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-emerald-500 rounded-bl"></div>
                        <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-emerald-500 rounded-br"></div>
                        <div class="absolute inset-x-0 top-1/2 h-0.5 bg-emerald-500/70 animate-pulse"></div>
                    </div>
                </div>
            </div>
            
            <canvas id="qrCanvas" class="hidden"></canvas>
            
            <div class="text-center">
                <p class="text-sm text-gray-500" id="qrStatusText">
                    Arahkan kamera ke QR code untuk memindai.
                </p>
            </div>
            
            <button type="button" onclick="tutupModalQR()" class="w-full border border-gray-200 hover:bg-gray-50 text-gray-700 py-3 rounded-xl font-bold transition-colors mt-2">
                Tutup Scanner
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
    // === FORMATTER ===
    const formatRp = (angka) => {
        return parseInt(angka).toLocaleString('id-ID');
    };

    // === LOGIKA NASABAH ===
    // === DATA NASABAH (Dikirim dari Controller) ===
    const nasabahMentah = @json($nasabah);
    const daftarNasabah = nasabahMentah.map(n => ({
        id: n.id,
        kode: n.kode,
        nama: n.nama,
        saldo: n.tabungan ? parseFloat(n.tabungan.saldo_saat_ini) : 0
    }));

    // === LOGIKA LIVE SEARCH NASABAH ===
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const nasabahIdInput = document.getElementById('nasabahIdInput');
    const container = document.getElementById('nasabahInputContainer');
    const card = document.getElementById('nasabahCard');

    function handleSearch() {
        const query = searchInput.value.toLowerCase();
        searchResults.innerHTML = ''; // Kosongkan hasil sebelumnya
        
        // Minimal ketik 2 huruf baru mencari
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            if (query.length === 0) nasabahIdInput.value = ''; // Reset jika dihapus
            return;
        }
        
        // Filter array data nasabah
        const filtered = daftarNasabah.filter(n => 
            n.nama.toLowerCase().includes(query) || 
            n.kode.toLowerCase().includes(query)
        );
        
        // Render Hasil
        if (filtered.length > 0) {
            filtered.forEach(n => {
                const div = document.createElement('div');
                div.className = 'p-3 hover:bg-emerald-50 cursor-pointer transition-colors';
                div.innerHTML = `
                    <p class="font-bold text-gray-800">${n.nama}</p>
                    <p class="text-xs text-gray-500">ID: ${n.kode}</p>
                `;
                // Jika diklik, pilih nasabah ini
                div.onclick = () => pilihNasabah(n);
                searchResults.appendChild(div);
            });
        } else {
            searchResults.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Nasabah tidak ditemukan.</div>';
        }
        
        searchResults.classList.remove('hidden');
    }

    function pilihNasabah(nasabah) {
        // Set Nilai Input
        nasabahIdInput.value = nasabah.id;
        searchInput.value = nasabah.nama;
        searchResults.classList.add('hidden');
        
        // Set Info Card
        document.getElementById('infoNamaNasabah').innerText = nasabah.nama;
        document.getElementById('infoKodeNasabah').innerText = nasabah.kode;
        document.getElementById('infoSaldoNasabah').innerText = formatRp(nasabah.saldo);
        
        // Sembunyikan Input, Tampilkan Card
        container.classList.add('hidden');
        card.classList.remove('hidden');

        // Buka Kunci Menu Sampah
        document.getElementById('kategoriContainer').classList.remove('opacity-50');
        document.getElementById('kategoriWarning').classList.add('hidden');
    }

    function resetNasabah() {
        nasabahIdInput.value = '';
        searchInput.value = '';
        container.classList.remove('hidden');
        card.classList.add('hidden');

        // Kunci Kembali Menu Sampah
        document.getElementById('kategoriContainer').classList.add('opacity-50');
        document.getElementById('kategoriWarning').classList.remove('hidden');
    }

    // Event Listener: Tutup dropdown jika klik di luar area pencarian
    document.addEventListener('click', function(event) {
        if (!document.getElementById('searchWrapper').contains(event.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // === LOGIKA QR SCANNER ===
    let videoStream = null;
    let scanAnimation = null;
    const qrVideo = document.getElementById('qrVideo');
    const qrCanvas = document.getElementById('qrCanvas');
    const qrContext = qrCanvas.getContext('2d', { willReadFrequently: true });
    
    const modalQr = document.getElementById('modalQrScanner');
    const modalQrBox = document.getElementById('modalQrBox');

    function bukaModalQR() {
        modalQr.classList.remove('hidden');
        modalQr.classList.add('flex');
        setTimeout(() => {
            modalQr.classList.remove('opacity-0');
            modalQrBox.classList.remove('scale-95');
        }, 10);
        
        mulaiKamera();
    }

    function tutupModalQR() {
        hentiKamera();
        
        modalQr.classList.add('opacity-0');
        modalQrBox.classList.add('scale-95');
        setTimeout(() => {
            modalQr.classList.add('hidden');
            modalQr.classList.remove('flex');
        }, 300);
    }

    async function mulaiKamera() {
        const loadingDiv = document.getElementById('qrLoading');
        const errorDiv = document.getElementById('qrError');
        const overlayDiv = document.getElementById('qrOverlay');
        
        loadingDiv.classList.remove('hidden');
        errorDiv.classList.add('hidden');
        qrVideo.classList.add('hidden');
        overlayDiv.classList.add('hidden');
        document.getElementById('qrStatusText').innerText = "Menyalakan kamera...";

        try {
            videoStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: "environment", width: { ideal: 640 }, height: { ideal: 480 } }
            });
            
            qrVideo.srcObject = videoStream;
            qrVideo.play();
            
            qrVideo.onloadedmetadata = () => {
                loadingDiv.classList.add('hidden');
                qrVideo.classList.remove('hidden');
                overlayDiv.classList.remove('hidden');
                document.getElementById('qrStatusText').innerText = "Arahkan kamera ke QR code untuk memindai.";
                scanFrame(); // Mulai scanning
            };
        } catch (err) {
            console.error("Error accessing camera:", err);
            loadingDiv.classList.add('hidden');
            errorDiv.classList.remove('hidden');
            document.getElementById('qrStatusText').innerText = "Jika kamera tidak muncul, periksa izin kamera pada browser.";
            
            if (err.name === "NotAllowedError" || err.name === "PermissionDeniedError") {
                document.getElementById('qrErrorMsg').innerText = "Izin akses kamera ditolak. Silakan berikan izin di browser Anda.";
            } else {
                document.getElementById('qrErrorMsg').innerText = "Tidak dapat mengakses kamera. Pastikan kamera tidak sedang digunakan.";
            }
        }
    }

    function hentiKamera() {
        if (scanAnimation) cancelAnimationFrame(scanAnimation);
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            qrVideo.srcObject = null;
        }
    }

    function scanFrame() {
        if (qrVideo.readyState === qrVideo.HAVE_ENOUGH_DATA) {
            qrCanvas.width = qrVideo.videoWidth;
            qrCanvas.height = qrVideo.videoHeight;
            
            qrContext.drawImage(qrVideo, 0, 0, qrCanvas.width, qrCanvas.height);
            const imageData = qrContext.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
            
            try {
                if (typeof jsQR !== 'undefined') {
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert",
                    });
                    
                    if (code && code.data) {
                        prosesHasilQR(code.data);
                        return; // Berhenti looping jika QR ditemukan
                    }
                }
            } catch (err) {
                console.error("Error QR parsing:", err);
            }
        }
        scanAnimation = requestAnimationFrame(scanFrame);
    }

    function prosesHasilQR(kodeHasil) {
        tutupModalQR();
        
        // Cari nasabah di dalam array daftarNasabah berdasarkan kode QR
        const nasabahDitemukan = daftarNasabah.find(n => n.kode === kodeHasil);

        if (nasabahDitemukan) {
            pilihNasabah(nasabahDitemukan); // Render UI
        } else {
            alert(`Nasabah dengan kode QR "${kodeHasil}" tidak ditemukan.`);
        }
    }

    // === LOGIKA MODAL INPUT & KERANJANG ===
    let cart = [];
    let itemAktif = null;

    const modal = document.getElementById('modalInput');
    const modalBox = document.getElementById('modalBox');

    function bukaModal(id, nama, harga, emisi) {
        // VALIDASI BARU: Cegah modal terbuka jika nasabah kosong
        if (!document.getElementById('nasabahIdInput').value) {
            // Berikan efek highlight/kedip pada teks peringatan agar user menyadari kesalahannya
            const warning = document.getElementById('kategoriWarning');
            warning.classList.remove('hidden');
            warning.classList.add('animate-pulse', 'ring-2', 'ring-red-200');
            setTimeout(() => {
                warning.classList.remove('animate-pulse', 'ring-2', 'ring-red-200');
            }, 1500);
            return; // Batalkan proses buka modal
        }

        itemAktif = { id, nama, harga, emisi };
        
        document.getElementById('modalNamaSampah').innerText = nama;
        document.getElementById('modalHargaSampah').innerText = `Rp ${formatRp(harga)} per kilogram`;
        document.getElementById('inputBeratModal').value = '';
        document.getElementById('modalSubtotalTxt').innerText = 'Rp 0';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalBox.classList.remove('scale-95');
        }, 10);
        
        document.getElementById('inputBeratModal').focus();
    }

    function tutupModal() {
        modal.classList.add('opacity-0');
        modalBox.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            itemAktif = null;
        }, 300);
    }

    function hitungSubtotal() {
        const berat = parseFloat(document.getElementById('inputBeratModal').value) || 0;
        const subtotal = berat * itemAktif.harga;
        document.getElementById('modalSubtotalTxt').innerText = `Rp ${formatRp(subtotal)}`;
    }

    function tambahItem() {
        const berat = parseFloat(document.getElementById('inputBeratModal').value);
        
        if (isNaN(berat) || berat <= 0) {
            alert('Masukkan berat yang valid!');
            return;
        }

        const existingIndex = cart.findIndex(item => item.kategori_id === itemAktif.id);
        
        if (existingIndex > -1) {
            cart[existingIndex].berat += berat;
        } else {
            cart.push({
                kategori_id: itemAktif.id,
                nama: itemAktif.nama,
                harga: itemAktif.harga,
                emisi: itemAktif.emisi,
                berat: berat
            });
        }

        renderCart();
        tutupModal();
    }

    function hapusItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function renderCart() {
        const emptyState = document.getElementById('cartEmptyState');
        const cartList = document.getElementById('cartList');
        const container = document.getElementById('cartItemsContainer');
        
        container.innerHTML = '';
        let grandTotalNilai = 0;
        let grandTotalCo2 = 0;

        if (cart.length === 0) {
            emptyState.classList.remove('hidden');
            cartList.classList.add('hidden');
        } else {
            emptyState.classList.add('hidden');
            cartList.classList.remove('hidden');

            cart.forEach((item, index) => {
                const subTotalNilai = item.berat * item.harga;
                const subTotalCo2 = item.berat * item.emisi;

                grandTotalNilai += subTotalNilai;
                grandTotalCo2 += subTotalCo2;

                container.innerHTML += `
                    <div class="flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-box"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">${item.nama}</h4>
                                <p class="text-sm text-gray-500">${item.berat} kg × Rp ${formatRp(item.harga)}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <p class="font-bold text-emerald-600">Rp ${formatRp(subTotalNilai)}</p>
                            </div>
                            <button type="button" onclick="hapusItem(${index})" class="text-red-300 hover:text-red-500 bg-white border border-red-100 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm transition-colors">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        document.getElementById('totalNilaiTxt').innerText = `Rp ${formatRp(grandTotalNilai)}`;
        document.getElementById('totalCo2Txt').innerText = grandTotalCo2.toFixed(2);
        
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
    }

    function simpanTransaksi() {
        const nasabahId = document.getElementById('nasabahIdInput').value;
        if (!nasabahId) {
            alert('Mohon pilih nasabah terlebih dahulu!');
            return;
        }
        if (cart.length === 0) {
            alert('Keranjang masih kosong!');
            return;
        }
        
        document.getElementById('formTransaksi').submit();
    }
</script>
@endpush
@endsection