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

    <form id="formTransaksi" action="{{ route('setor.store') }}" method="POST">
        @csrf
        <input type="hidden" name="cart_data" id="cartDataInput">
        <input type="hidden" name="tanggal" value="{{ date('Y-m-d') }}">

        <div class="mb-8">
            <label class="block font-bold text-gray-800 mb-3">Cari Nasabah</label>
            
            <div class="relative flex items-center gap-3" id="nasabahInputContainer">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <select name="nasabah_id" id="nasabahSelect" onchange="handleNasabahSelect()" required class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl pl-10 pr-4 py-3 appearance-none focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all">
                        <option value="" disabled selected>Ketik atau pilih nama nasabah...</option>
                        @foreach($nasabah as $n)
                            <option value="{{ $n->id }}" 
                                data-kode="{{ $n->kode }}" 
                                data-saldo="{{ $n->tabungan ? $n->tabungan->saldo_saat_ini : 0 }}">
                                {{ $n->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition shadow-sm">
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
            <label class="block font-bold text-gray-800 mb-3">Pilih Jenis Sampah</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
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

@push('scripts')
<script>
    // === FORMATTER ===
    const formatRp = (angka) => {
        return parseInt(angka).toLocaleString('id-ID');
    };

    // === LOGIKA NASABAH ===
    function handleNasabahSelect() {
        const select = document.getElementById('nasabahSelect');
        const card = document.getElementById('nasabahCard');
        const container = document.getElementById('nasabahInputContainer');
        
        if (select.value) {
            const option = select.options[select.selectedIndex];
            
            // Set Data ke Card
            document.getElementById('infoNamaNasabah').innerText = option.text;
            document.getElementById('infoKodeNasabah').innerText = option.dataset.kode;
            document.getElementById('infoSaldoNasabah').innerText = formatRp(option.dataset.saldo);
            
            // Sembunyikan Input, Tampilkan Card
            container.classList.add('hidden');
            card.classList.remove('hidden');
        }
    }

    function resetNasabah() {
        document.getElementById('nasabahSelect').value = '';
        document.getElementById('nasabahInputContainer').classList.remove('hidden');
        document.getElementById('nasabahCard').classList.add('hidden');
    }

    // === LOGIKA MODAL & KERANJANG ===
    let cart = [];
    let itemAktif = null;

    const modal = document.getElementById('modalInput');
    const modalBox = document.getElementById('modalBox');

    function bukaModal(id, nama, harga, emisi) {
        itemAktif = { id, nama, harga, emisi };
        
        // Set info di modal
        document.getElementById('modalNamaSampah').innerText = nama;
        document.getElementById('modalHargaSampah').innerText = `Rp ${formatRp(harga)} per kilogram`;
        document.getElementById('inputBeratModal').value = '';
        document.getElementById('modalSubtotalTxt').innerText = 'Rp 0';

        // Animasi buka
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // setTimeout trik agar transisi CSS berjalan
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalBox.classList.remove('scale-95');
        }, 10);
        
        document.getElementById('inputBeratModal').focus();
    }

    function tutupModal() {
        // Animasi tutup
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

        // Cek apakah item sudah ada di keranjang
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

                // Buat Elemen HTML Item Keranjang
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

        // Update Total
        document.getElementById('totalNilaiTxt').innerText = `Rp ${formatRp(grandTotalNilai)}`;
        document.getElementById('totalCo2Txt').innerText = grandTotalCo2.toFixed(2);
        
        // Update input hidden untuk disubmit ke backend
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
    }

    function simpanTransaksi() {
        const nasabahId = document.getElementById('nasabahSelect').value;
        if (!nasabahId) {
            alert('Mohon pilih nasabah terlebih dahulu!');
            return;
        }
        if (cart.length === 0) {
            alert('Keranjang masih kosong!');
            return;
        }
        
        // Submit form
        document.getElementById('formTransaksi').submit();
    }
</script>
@endpush
@endsection