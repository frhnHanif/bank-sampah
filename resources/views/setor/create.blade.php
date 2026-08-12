@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Penerimaan Setoran</h1>
            <p class="text-sm text-gray-500">Catat jenis dan berat. Nilai rupiah ditentukan setelah barang terjual.</p>
        </div>
        <!-- <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm font-bold">
            <i class="fa-solid fa-scale-balanced mr-2"></i> Setoran bukan event finansial
        </div> -->
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-5">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form id="depositForm" action="{{ route('setor.store') }}" method="POST">
        @csrf
        <input type="hidden" name="cart_data" id="cartData">
        <input type="hidden" name="nasabah_id" id="customerId">
        <div class="grid lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7 space-y-6">
                <section class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase">Tanggal</label>
                            <input name="tanggal" type="date" required value="{{ date('Y-m-d') }}" class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase">Catatan (opsional)</label>
                            <input name="catatan" maxlength="1000" class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Kondisi atau keterangan setoran">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-xs font-black text-gray-400 uppercase">Nasabah</label>
                        <div id="customerSearchWrap" class="relative mt-2 flex gap-2">
                            <input id="customerSearch" autocomplete="off" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Cari nama atau kode nasabah">
                            <button type="button" onclick="openQr()" class="w-12 shrink-0 rounded-xl border border-gray-200 text-emerald-600 hover:bg-emerald-50" title="Pindai QR"><i class="fa-solid fa-qrcode"></i></button>
                            <div id="customerResults" class="hidden absolute top-full left-0 right-14 mt-2 z-30 bg-white border border-gray-100 rounded-xl shadow-xl max-h-56 overflow-auto"></div>
                        </div>
                        <div id="selectedCustomer" class="hidden mt-3 bg-emerald-50 border border-emerald-200 p-4 rounded-xl items-center gap-3">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <div><strong id="selectedName"></strong><p id="selectedMeta" class="text-xs text-emerald-700"></p></div>
                            <button type="button" onclick="clearCustomer()" class="ml-auto text-xs font-bold text-emerald-700">Ganti</button>
                        </div>
                    </div>
                </section>

                <section class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div><h2 class="font-black text-gray-800">Jenis Sampah</h2><p class="text-xs text-gray-500">Pilih jenis sampah yang akan ditimbang.</p></div>
                        <button type="button" onclick="openQuickCategory()" class="shrink-0 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-4 py-2.5 rounded-xl hover:bg-emerald-100"><i class="fa-solid fa-plus mr-2"></i> Tambah Jenis Baru</button>
                    </div>
                    <div id="categoryGrid" class="grid sm:grid-cols-2 gap-3"></div>
                </section>
            </div>

            <aside class="lg:col-span-5">
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-28">
                    <div class="flex items-center gap-3 p-5 border-b border-gray-100">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white"><i class="fa-solid fa-basket-shopping"></i></div>
                        <div><h2 class="font-black text-gray-800">Ringkasan Setoran</h2><p id="cartCount" class="text-xs text-gray-500">0 jenis sampah dipilih</p></div>
                    </div>
                    <div id="cartEmpty" class="m-0 flex min-h-40 flex-col items-center justify-center border-b border-dashed border-gray-200 px-6 py-8 text-center text-gray-400">
                        <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100"><i class="fa-solid fa-scale-balanced text-xl"></i></div>
                        <p class="font-bold text-gray-600">Belum ada sampah</p>
                        <p class="mt-1 text-xs">Pilih jenis sampah untuk mulai menimbang.</p>
                    </div>
                    <div id="cartList" class="hidden divide-y"></div>
                    <div class="space-y-4 bg-gray-50 p-5">
                        <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3.5">
                            <div><span class="block text-xs font-bold uppercase tracking-wide text-gray-400">Total berat</span><span class="mt-1 block text-xs text-gray-500">Seluruh setoran</span></div>
                            <strong class="text-2xl text-emerald-700"><span id="totalWeight">0,00</span> <small class="text-sm">kg</small></strong>
                        </div>
                        <button id="depositSubmit" type="button" onclick="submitDeposit()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-black shadow-sm disabled:opacity-50">Simpan Setoran <i class="fa-solid fa-arrow-right ml-1"></i></button>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>

<div id="weightModal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-gray-900/50 p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
        <h3 class="font-black text-lg" id="weightTitle"></h3><p class="text-sm text-gray-500 mt-1">Masukkan berat hasil penimbangan.</p>
        <input id="weightInput" type="number" min="0.01" step="0.01" class="mt-5 w-full border border-emerald-300 rounded-xl px-4 py-3 text-xl text-center outline-none focus:ring-2 focus:ring-emerald-500" placeholder="0,00">
        <div class="flex gap-3 mt-5"><button type="button" onclick="closeWeight()" class="flex-1 bg-gray-100 py-3 rounded-xl font-bold">Batal</button><button type="button" onclick="addWeight()" class="flex-1 bg-emerald-600 text-white py-3 rounded-xl font-bold">Tambahkan</button></div>
    </div>
</div>

<div id="quickModal" class="fixed inset-0 z-[125] hidden items-center justify-center bg-gray-900/50 p-4">
    <form id="quickForm" class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h3 class="font-black text-lg">Tambah Jenis Sampah Baru</h3><p class="text-sm text-gray-500 mt-1">Jenis langsung tersedia tanpa menghapus isi keranjang.</p>
        <label class="block text-xs font-black text-gray-400 uppercase mt-5">Nama jenis sampah</label>
        <input name="nama" required maxlength="255" class="mt-2 w-full border rounded-xl px-4 py-3" placeholder="Contoh: Dinamo Bekas">
        <label class="block text-xs font-black text-gray-400 uppercase mt-4">Kelompok faktor emisi</label>
        <select name="faktor_emisi_id" class="mt-2 w-full border rounded-xl px-4 py-3"><option value="">Belum diklasifikasikan</option>@foreach($faktorEmisi as $factor)<option value="{{ $factor->id }}">{{ $factor->nama_material }}</option>@endforeach</select>
        <p id="quickError" class="hidden text-sm text-red-600 mt-3"></p>
        <div class="flex gap-3 mt-5"><button type="button" onclick="closeQuickCategory()" class="flex-1 bg-gray-100 py-3 rounded-xl font-bold">Batal</button><button id="quickSubmit" class="flex-1 bg-emerald-600 text-white py-3 rounded-xl font-bold">Simpan & Pilih</button></div>
    </form>
</div>

<div id="qrModal" class="fixed inset-0 z-[130] hidden items-center justify-center bg-gray-900/70 p-4"><div class="bg-white rounded-2xl p-5 w-full max-w-md"><div class="flex justify-between mb-3"><strong>Pindai QR Nasabah</strong><button type="button" onclick="closeQr()"><i class="fa-solid fa-xmark"></i></button></div><video id="qrVideo" playsinline class="w-full h-64 bg-black rounded-xl object-cover"></video><canvas id="qrCanvas" class="hidden"></canvas><p id="qrStatus" class="text-sm text-gray-500 mt-3 text-center">Arahkan kamera ke QR ID Card.</p></div></div>

@if(session('wa_url'))
<div class="fixed inset-0 z-[150] flex items-center justify-center bg-gray-900/60 p-4" id="waModal"><div class="bg-white max-w-md w-full rounded-2xl p-6 text-center"><i class="fa-brands fa-whatsapp text-5xl text-emerald-500"></i><h3 class="font-black text-xl mt-3">Setoran berhasil dicatat</h3><p class="text-sm text-gray-500 mt-2">Kirim bukti penerimaan pending kepada {{ session('wa_nasabah') }}.</p><div class="flex gap-3 mt-5"><button onclick="document.getElementById('waModal').remove()" class="flex-1 bg-gray-100 rounded-xl py-3 font-bold">Tutup</button><a target="_blank" href="{{ session('wa_url') }}" class="flex-1 bg-emerald-600 text-white rounded-xl py-3 font-bold">Kirim WA</a></div></div></div>
@endif

@push('scripts')
<script>
const customers = @json($nasabah).map(n => ({id:n.id, name:n.nama, code:n.kode, balance:Number(n.tabungan?.saldo_saat_ini || 0)}));
let categories = @json($kategori).map(k => ({
    id:k.id,
    name:k.nama,
    emissionFactor:k.faktor_emisi ? Number(k.faktor_emisi.faktor_kgco2e_per_kg) : null
}));
let cart = [], activeCategory = null, qrStream = null, qrFrame = null;
const rupiah = n => Number(n).toLocaleString('id-ID');
const kg = n => Number(n).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2});
const emissionFactor = n => Number(n).toLocaleString('id-ID', {maximumFractionDigits:3});

function renderCategories() {
    categoryGrid.innerHTML = categories.map(c => {
        const hasFactor = c.emissionFactor !== null;
        const capsule = hasFactor
            ? `<span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700"><i class="fa-solid fa-leaf"></i>${emissionFactor(c.emissionFactor)} kgCO₂e/kg</span>`
            : `<span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-600"><i class="fa-solid fa-circle-exclamation"></i>Faktor emisi kosong</span>`;

        return `<button type="button" onclick="openWeight(${c.id})" class="group min-h-28 rounded-2xl border border-gray-200 p-3 text-left transition hover:border-emerald-400 hover:bg-emerald-50/30">
            <div class="flex items-start justify-between">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl ${hasFactor?'bg-emerald-50 text-emerald-600':'bg-red-50 text-red-500'}"><i class="fa-solid fa-recycle"></i></span>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-300 transition group-hover:bg-emerald-100 group-hover:text-emerald-600"><i class="fa-solid fa-plus text-xs"></i></span>
            </div>
            <div class="mt-2 font-black text-gray-800">${escapeHtml(c.name)}</div>${capsule}
        </button>`;
    }).join('');
}
function escapeHtml(value) {const d=document.createElement('div'); d.textContent=value; return d.innerHTML; }
customerSearch.addEventListener('input', () => {
    const q=customerSearch.value.toLowerCase().trim();
    if(q.length<2){customerResults.classList.add('hidden');return;}
    const found=customers.filter(n=>n.name.toLowerCase().includes(q)||n.code.toLowerCase().includes(q)).slice(0,10);
    customerResults.innerHTML=found.length?found.map(n=>`<button type="button" class="w-full text-left p-3 hover:bg-emerald-50" onclick="selectCustomer(${n.id})"><strong>${escapeHtml(n.name)}</strong><small class="block text-gray-500">${escapeHtml(n.code)}</small></button>`).join(''):'<p class="p-4 text-sm text-gray-500">Tidak ditemukan.</p>';
    customerResults.classList.remove('hidden');
});
function selectCustomer(id) {
    const n=customers.find(x=>x.id===id);
    if(!n)return;customerId.value=n.id;
    selectedName.textContent=n.name;
    selectedMeta.textContent=`${n.code} - Saldo tersedia Rp ${rupiah(n.balance)}`;
    customerSearchWrap.classList.add('hidden');
    selectedCustomer.classList.remove('hidden');
    selectedCustomer.classList.add('flex');
    customerResults.classList.add('hidden');
}
function clearCustomer() {
    customerId.value='';
    customerSearch.value='';
    selectedCustomer.classList.add('hidden');
    selectedCustomer.classList.remove('flex');
    customerSearchWrap.classList.remove('hidden');
}
function openWeight(id) {
    if(!customerId.value) {
        showToast('Pilih nasabah terlebih dahulu.','warning');
        return;
    }
    activeCategory=categories.find(c=>c.id===id);
    weightTitle.textContent=activeCategory.name;
    weightInput.value='';
    weightModal.classList.remove('hidden');
    weightModal.classList.add('flex');
    setTimeout(()=>weightInput.focus(),50);
}
function closeWeight() {
    weightModal.classList.add('hidden');
    weightModal.classList.remove('flex');
}
function addWeight() {
    const weight=Math.round(Number(weightInput.value)*100)/100;
    if(!weight||weight<=0) {
        showToast('Berat harus lebih dari 0.','warning');
        return;
    }
    const found=cart.find(i=>i.kategori_id===activeCategory.id);
    if(found)found.berat=Math.round((found.berat+weight)*100)/100;
    else cart.push({
        kategori_id:activeCategory.id,
        nama:activeCategory.name,
        berat:weight
    });
    closeWeight();
    renderCart();
}
function renderCart() {
    cartEmpty.classList.toggle('hidden',cart.length>0);
    cartList.classList.toggle('hidden',cart.length===0);
    cartCount.textContent=`${cart.length} jenis sampah dipilih`;
    cartList.innerHTML=cart.map((i,x)=>`<div class="p-4 flex items-center gap-3"><div class="flex-1"><strong>${escapeHtml(i.nama)}</strong><p class="text-xs text-gray-500">Nilai belum ditentukan</p></div><b>${kg(i.berat)} kg</b><button type="button" onclick="cart.splice(${x},1);renderCart()" class="text-red-400"><i class="fa-solid fa-trash"></i></button></div>`).join('');
    totalWeight.textContent=kg(cart.reduce((s,i)=>s+i.berat,0));
    cartData.value=JSON.stringify(cart);
}
async function submitDeposit() {
    if(!customerId.value||!cart.length) {
        showToast('Pilih nasabah dan tambahkan setoran.','warning');
        return;
    }
    if(await showConfirm('Catat barang dan berat sebagai setoran pending? Saldo tidak akan berubah.','Konfirmasi Setoran','emerald')) {
        depositSubmit.disabled=true;
        depositForm.submit();
    }
}
function openQuickCategory() {
    quickError.classList.add('hidden');
    quickModal.classList.remove('hidden');
    quickModal.classList.add('flex');
}
function closeQuickCategory() {
    quickModal.classList.add('hidden');
    quickModal.classList.remove('flex');
}
quickForm.addEventListener(
    'submit',async e=>{ 
        e.preventDefault();
        quickSubmit.disabled=true;
        quickError.classList.add('hidden');
        const body=new FormData(quickForm);
        try {
            const res=await fetch(@json(route('kategori.quick-store')),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},body});
            const data=await res.json();
            if(!res.ok)throw new Error(Object.values(data.errors||{}).flat()[0]||data.message);
            const k=data.kategori;
            categories.push({id:k.id,name:k.nama,emissionFactor:k.faktor_emisi?Number(k.faktor_emisi.faktor_kgco2e_per_kg):null});
            categories.sort((a,b)=>a.name.localeCompare(b.name,'id'));
            renderCategories();
            closeQuickCategory();
            quickForm.reset();
            showToast('Jenis baru ditambahkan tanpa menghapus keranjang.','success');
            openWeight(k.id);
        }
        catch(err) {
            quickError.textContent=err.message;
            quickError.classList.remove('hidden');
        }
        finally {
            quickSubmit.disabled=false;
        }
    }
);
async function openQr() {
    qrModal.classList.remove('hidden');
    qrModal.classList.add('flex');
    try {
        qrStream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'}});
        qrVideo.srcObject=qrStream;
        await qrVideo.play();
        scanQr();
    }
    catch(e){ 
        qrStatus.textContent='Kamera tidak dapat diakses. Gunakan pencarian nama/kode.';
    }
}
function scanQr() {
    if(qrVideo.readyState===qrVideo.HAVE_ENOUGH_DATA) {
        qrCanvas.width=qrVideo.videoWidth;
        qrCanvas.height=qrVideo.videoHeight;
        const ctx=qrCanvas.getContext('2d');
        ctx.drawImage(qrVideo,0,0);
        const img=ctx.getImageData(0,0,qrCanvas.width,qrCanvas.height);
        const code=window.jsQR?.(img.data,img.width,img.height);
        if(code) {
            const n=customers.find(x=>x.code===code.data.trim());
            if(n) {
                selectCustomer(n.id);
                closeQr();
                showToast('Nasabah ditemukan dari QR.','success');
                return;
            }
            qrStatus.textContent='Kode QR tidak terdaftar.';
        }
    }
    qrFrame=requestAnimationFrame(scanQr);
}
function closeQr() {
    if(qrFrame)cancelAnimationFrame(qrFrame);
    if(qrStream)qrStream.getTracks().forEach(t=>t.stop());
    qrModal.classList.add('hidden');
    qrModal.classList.remove('flex');
}
renderCategories();
renderCart();
</script>
@endpush
@endsection
