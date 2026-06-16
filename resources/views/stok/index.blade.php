@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Stok Gudang</h1>
        <p class="text-sm text-gray-500">Monitoring ketersediaan sampah yang siap dijual ke pengepul.</p>
    </div>
    
    <button type="button" onclick="bukaModalJual()" class="bg-amber-500 text-white px-6 py-2.5 rounded-full font-bold hover:bg-amber-600 transition shadow-sm flex items-center gap-2 w-full sm:w-auto justify-center tooltip" title="Jual sampah ke Pengepul">
        <i class="fa-solid fa-truck-ramp-box"></i> Jual ke Pengepul
    </button>
</div>

@if(session('success'))
    <div class="bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
        <i class="fa-solid fa-circle-check"></i>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 font-medium text-sm">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
    @forelse($stok as $item)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden group hover:shadow-md transition-all hover:-translate-y-1">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>

        <div class="flex items-center gap-3 mb-6 pt-2">
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="overflow-hidden">
                <h3 class="font-bold text-gray-800 leading-tight truncate" title="{{ $item->kategori->nama ?? 'Kategori Dihapus' }}">
                    {{ $item->kategori->nama ?? 'Kategori Dihapus' }}
                </h3>
            </div>
        </div>

        <div class="mt-auto pb-1">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Total Tersedia</p>
            <div class="flex items-baseline gap-1">
                <h2 class="text-3xl font-black text-blue-600">{{ number_format($item->total_berat_kg, 2, ',', '.') }}</h2>
                <span class="text-sm font-bold text-gray-500">Kg</span>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center flex flex-col items-center justify-center">
        <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4 text-2xl">
            <i class="fa-solid fa-warehouse"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-700">Gudang Masih Kosong</h3>
        <p class="text-gray-500 mt-1">Belum ada sampah yang disetorkan oleh nasabah.</p>
    </div>
    @endforelse
</div>

<div id="modalJual" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300 overflow-y-auto p-4 sm:p-6">
    <div class="bg-gray-50 rounded-2xl w-full max-w-5xl mx-auto overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-full" id="modalJualBox">
        
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-white shrink-0">
            <div>
                <h3 class="font-black text-gray-800 text-lg"><i class="fa-solid fa-truck-ramp-box text-amber-500 mr-2"></i>Jual ke Pengepul</h3>
                <p class="text-xs text-gray-500">Keluarkan stok sampah dari gudang dan ubah menjadi uang kas.</p>
            </div>
            <button type="button" onclick="tutupModalJual()" class="text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1">
            <form id="formPenjualan" action="{{ route('jual.store') }}" method="POST">
                @csrf
                <input type="hidden" name="cart_data" id="cartDataInput">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Tanggal Penjualan</label>
                                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nama Pengepul / Catatan</label>
                                <input type="text" name="catatan" placeholder="Contoh: Pak Yanto (Pengepul Plastik)" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                        </div>

                        <div class="bg-amber-50 p-5 rounded-2xl border border-amber-100 space-y-4">
                            <h3 class="font-bold text-amber-800"><i class="fa-solid fa-truck-loading mr-2"></i>Pilih Barang</h3>
                            
                            <div>
                                <label class="block text-xs font-bold text-amber-700 mb-2">Pilih Sampah Gudang</label>

                                <!-- Custom Select -->
                                <div class="custom-select relative">
                                    <select id="kategoriInput" onchange="setMaksimalStok()" class="sr-only">
                                        <option value="" disabled selected>-- Pilih Barang --</option>
                                        @foreach($stok as $s)
                                            @if($s->kategori && $s->total_berat_kg > 0) 
                                                <option value="{{ $s->kategori->id }}" data-stok="{{ $s->total_berat_kg }}">
                                                    {{ $s->kategori->nama }} (Sisa: {{ number_format($s->total_berat_kg, 2, ',', '.') }} kg)
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>

                                    <button type="button" class="custom-select-trigger w-full bg-white border border-amber-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-amber-500 flex items-center justify-between gap-2 text-left">
                                        <span class="custom-select-label text-gray-400 text-sm">-- Pilih Barang --</span>
                                        <i class="fa-solid fa-chevron-down custom-select-chevron text-amber-400 text-xs transition-transform duration-200"></i>
                                    </button>

                                    <div class="custom-select-dropdown bg-white border border-amber-200 rounded-xl shadow-lg overflow-hidden">
                                        @foreach($stok as $s)
                                            @if($s->kategori && $s->total_berat_kg > 0)
                                                <div class="custom-select-option px-4 py-2.5 text-sm text-gray-700 border-b border-gray-50 last:border-b-0"
                                                     data-value="{{ $s->kategori->id }}">
                                                    <span class="font-medium">{{ $s->kategori->nama }}</span>
                                                    <span class="text-gray-400 ml-1">(Sisa: {{ number_format($s->total_berat_kg, 2, ',', '.') }} kg)</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <p id="infoStok" class="text-[10px] text-amber-600 mt-1 font-medium hidden">Maksimal bisa dijual: <span id="maxStokTxt">0</span> kg</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-amber-700 mb-2">Berat Keluar (Kg)</label>
                                    <input type="number" id="beratInput" step="0.01" min="0.1" placeholder="0" class="w-full bg-white border border-amber-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-amber-700 mb-2">Harga Jual (Rp/Kg)</label>
                                    <input type="number" id="hargaInput" min="0" placeholder="Harga Pengepul" class="w-full bg-white border border-amber-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                            </div>

                            <button type="button" onclick="tambahKeKeranjang()" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition shadow-sm mt-2">
                                Tambahkan ke Muatan
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-7 flex flex-col">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex-1 flex flex-col overflow-hidden">
                            <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="font-bold text-gray-700">Rincian Muatan (Penjualan)</h3>
                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded font-bold" id="itemCount">0 Barang</span>
                            </div>
                            
                            <div class="flex-1 p-0 overflow-x-auto min-h-[250px]">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-white text-gray-400 text-[10px] uppercase tracking-wider border-b">
                                        <tr>
                                            <th class="p-4 font-black">Barang</th>
                                            <th class="p-4 font-black text-right">Berat Keluar</th>
                                            <th class="p-4 font-black text-right">Harga Jual</th>
                                            <th class="p-4 font-black text-right">Total (Rp)</th>
                                            <th class="p-4 font-black text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cartTableBody" class="divide-y divide-gray-50">
                                        <tr id="emptyCartRow">
                                            <td colspan="5" class="p-8 text-center text-gray-400 italic">Belum ada barang yang dimuat.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="bg-gray-800 text-white p-5 lg:p-6 shrink-0 mt-auto">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-4">
                                    <div>
                                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Pemasukan Kas</p>
                                        <p class="text-3xl font-black text-amber-400">Rp <span id="totalNilaiEl">0</span></p>
                                    </div>
                                    <button type="button" onclick="submitPenjualan()" class="bg-amber-500 hover:bg-amber-400 text-gray-900 font-black px-8 py-3.5 rounded-xl transition shadow-lg w-full sm:w-auto">
                                        SELESAIKAN PENJUALAN
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // === LOGIKA MODAL ===
    const modalJual = document.getElementById('modalJual');
    const modalJualBox = document.getElementById('modalJualBox');

    function bukaModalJual() {
        modalJual.classList.remove('hidden');
        modalJual.classList.add('flex');
        setTimeout(() => {
            modalJual.classList.remove('opacity-0');
            modalJualBox.classList.remove('scale-95');
        }, 10);
    }

    function tutupModalJual() {
        modalJual.classList.add('opacity-0');
        modalJualBox.classList.add('scale-95');
        setTimeout(() => {
            modalJual.classList.add('hidden');
            modalJual.classList.remove('flex');
        }, 300);
    }

    // === LOGIKA KERANJANG PENJUALAN ===
    let cart = [];
    let stokMaksimal = 0;

    const formatRp = (angka) => {
        return parseInt(angka).toLocaleString('id-ID');
    };

    function setMaksimalStok() {
        const selectKat = document.getElementById('kategoriInput');
        if (selectKat.value) {
            const optionSelected = selectKat.options[selectKat.selectedIndex];
            stokMaksimal = parseFloat(optionSelected.dataset.stok);
            
            document.getElementById('infoStok').classList.remove('hidden');
            document.getElementById('maxStokTxt').innerText = stokMaksimal;
            document.getElementById('beratInput').max = stokMaksimal;
        }
    }

    function tambahKeKeranjang() {
        const selectKat = document.getElementById('kategoriInput');
        const inputBerat = document.getElementById('beratInput');
        const inputHarga = document.getElementById('hargaInput');

        const kategori_id = selectKat.value;
        const berat = parseFloat(inputBerat.value);
        const harga_jual = parseFloat(inputHarga.value);

        if (!kategori_id || isNaN(berat) || berat <= 0 || isNaN(harga_jual) || harga_jual <= 0) {
            showToast('Lengkapi barang, berat, dan harga jual dengan benar!', 'warning');
            return;
        }

        if (berat > stokMaksimal) {
            showToast(`Berat melebihi stok gudang! Maksimal: ${stokMaksimal} kg`, 'error');
            return;
        }

        const optionSelected = selectKat.options[selectKat.selectedIndex];
        const nama = optionSelected.text.split(' (')[0]; 

        // Cek jika barang sudah ada di list
        const existingIndex = cart.findIndex(item => item.kategori_id === kategori_id);
        if (existingIndex > -1) {
            // Cek apakah gabungan berat barunya melebihi stok
            if ((cart[existingIndex].berat + berat) > stokMaksimal) {
                showToast(`Total berat item ini di keranjang melebihi stok gudang (${stokMaksimal} kg)!`, 'error');
                return;
            }
            cart[existingIndex].berat += berat;
            cart[existingIndex].harga_jual = harga_jual; // Update dengan harga terbaru
        } else {
            cart.push({
                kategori_id: kategori_id,
                nama: nama,
                berat: berat,
                harga_jual: harga_jual
            });
        }

        // Reset input form
        selectKat.selectedIndex = 0;
        inputBerat.value = '';
        inputHarga.value = '';
        document.getElementById('infoStok').classList.add('hidden');
        stokMaksimal = 0;

        showToast(`${nama} berhasil ditambahkan ke muatan!`, 'success');
        renderCart();
    }

    function hapusItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('cartTableBody');
        const totalNilaiEl = document.getElementById('totalNilaiEl');
        const countEl = document.getElementById('itemCount');
        
        tbody.innerHTML = ''; 
        let grandTotalNilai = 0;

        if (cart.length === 0) {
            tbody.innerHTML = `<tr id="emptyCartRow"><td colspan="5" class="p-8 text-center text-gray-400 italic">Belum ada barang yang dimuat.</td></tr>`;
        } else {
            cart.forEach((item, index) => {
                const subTotal = item.berat * item.harga_jual;
                grandTotalNilai += subTotal;

                const tr = document.createElement('tr');
                tr.className = "hover:bg-gray-50/50";
                tr.innerHTML = `
                    <td class="p-4 font-bold text-gray-700 text-sm">${item.nama}</td>
                    <td class="p-4 text-right text-gray-600 text-sm">${item.berat} kg</td>
                    <td class="p-4 text-right text-gray-600 text-sm">Rp ${formatRp(item.harga_jual)}</td>
                    <td class="p-4 text-right text-amber-600 font-bold text-sm">Rp ${formatRp(subTotal)}</td>
                    <td class="p-4 text-center">
                        <button type="button" onclick="hapusItem(${index})" class="text-red-400 hover:text-red-600 transition tooltip" title="Hapus dari muatan"><i class="fa-solid fa-xmark"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        totalNilaiEl.innerText = formatRp(grandTotalNilai);
        countEl.innerText = cart.length + " Barang";
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
    }

    async function submitPenjualan() {
        if (cart.length === 0) {
            showToast('Muatan kosong! Tambahkan barang dari gudang terlebih dahulu.', 'warning');
            return;
        }

        const confirmed = await showConfirm(
            'Proses penjualan ini? Stok gudang akan otomatis berkurang.',
            'Konfirmasi Penjualan'
        );

        if (confirmed) {
            document.getElementById('formPenjualan').submit();
        }
    }
</script>
@endpush
@endsection