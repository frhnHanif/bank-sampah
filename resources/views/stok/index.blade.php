@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-6">
    <div><h1 class="text-2xl font-black text-gray-800">Stok Sampah</h1><p class="text-sm text-gray-500">Pantau sisa stok dan jumlah yang sudah terjual.</p></div>
    <button type="button" onclick="openSale()" class="bg-amber-500 hover:bg-amber-600 text-white rounded-xl px-5 py-3 font-black"><i class="fa-solid fa-truck-ramp-box mr-2"></i>Jual ke Pengepul</button>
</div>
@if(session('success'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-5">{{ session('success') }}</div>@endif
@if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
@forelse($stok as $row)
    <article class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center gap-3"><span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 grid place-items-center"><i class="fa-solid fa-boxes-stacked"></i></span><h2 class="font-black text-gray-800">{{ $row->jenisSampah?->nama ?? 'Jenis nonaktif' }}</h2></div>
        <div class="mt-5"><p class="text-[10px] uppercase font-black text-gray-400">Sisa stok</p><p class="text-3xl font-black text-blue-600">{{ number_format($row->total_berat_kg,2,',','.') }} <small class="text-sm">kg</small></p></div>
        <div class="mt-4 text-xs"><div class="bg-emerald-50 rounded-xl p-3"><span class="text-emerald-700"><i class="fa-solid fa-truck-ramp-box mr-1"></i>Terjual</span><strong class="block text-emerald-900 text-sm mt-1">{{ number_format((float)($sold[$row->jenis_sampah_id] ?? 0),2,',','.') }} kg</strong></div></div>
    </article>
@empty
    <div class="col-span-full bg-white border border-gray-100 shadow-sm rounded-2xl p-12 text-center text-gray-500">Gudang masih kosong.</div>
@endforelse
</div>

<style>
@media (max-width: 1023px) {
    #saleModal {
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 0;
    }

    #saleModalBox {
        position: absolute;
        top: 6rem;
        bottom: 6.75rem;
        left: 1rem;
        right: 1rem;
        width: auto;
        height: auto;
        max-height: none;
    }

    #saleForm {
        overscroll-behavior: contain;
    }
}
</style>

<div id="saleModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm px-4 pt-24 pb-24 lg:px-5 lg:pt-24 lg:pb-5 opacity-0 transition-opacity duration-300">
    <div id="saleModalBox" class="bg-white rounded-2xl w-full max-w-6xl max-h-full shadow-2xl overflow-hidden flex flex-col transform scale-95 transition-transform duration-300">
        <div class="shrink-0 px-5 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div><h2 class="font-bold text-gray-800">Penjualan Sampah</h2><p class="text-[10px] text-gray-500 mt-0.5">Masukkan berat dan harga saat sampah dijual ke pengepul.</p></div>
            <button type="button" onclick="closeSale()" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="saleForm" action="{{ route('jual.store') }}" method="POST" class="flex-1 min-h-0 overflow-y-auto bg-gray-50 p-4 sm:p-6">@csrf<input type="hidden" name="cart_data" id="saleCartData">
            <div class="grid lg:grid-cols-12 gap-5">
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm grid sm:grid-cols-2 gap-3">
                        <div><label class="text-[10px] font-black text-gray-400 uppercase tracking-wide">Tanggal</label><input name="tanggal" type="date" required value="{{ date('Y-m-d') }}" class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-amber-400"></div>
                        <div><label class="text-[10px] font-black text-gray-400 uppercase tracking-wide">Pengepul / catatan</label><input name="catatan" class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-amber-400" placeholder="Nama pengepul"></div>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm space-y-4">
                        <div><label class="text-[10px] font-black text-gray-400 uppercase tracking-wide">Jenis sampah</label><select id="saleCategory" onchange="updateSalePreview()" class="mt-2 w-full bg-amber-50 border border-amber-200 rounded-xl px-3 py-3 outline-none focus:ring-2 focus:ring-amber-400"><option value="">Pilih jenis sampah</option>@foreach($stok as $row)@if($row->jenisSampah && $row->total_berat_kg>0)<option value="{{ $row->jenis_sampah_id }}">{{ $row->jenisSampah->nama }}</option>@endif @endforeach</select></div>
                        <div class="rounded-xl bg-blue-50 p-3 flex items-center gap-3 overflow-hidden"><span class="w-10 h-10 aspect-square shrink-0 rounded-lg bg-blue-100 text-blue-600 grid place-items-center"><i class="fa-solid fa-boxes-stacked text-xs"></i></span><div class="min-w-0"><span class="block text-[10px] font-bold uppercase text-blue-500">Sisa stok tersedia</span><strong id="stockInfo" class="block text-blue-800">0,00 kg</strong></div></div>
                        <div class="grid sm:grid-cols-3 gap-3"><div class="min-w-0"><label class="text-[10px] font-black text-gray-500 uppercase">Berat dijual</label><div class="relative mt-1.5 overflow-hidden rounded-xl"><input id="saleWeight" oninput="updateSalePreview()" type="number" min="0.01" step="0.01" class="block w-full min-w-0 border border-gray-200 rounded-xl pl-3 pr-10 py-2.5 outline-none focus:ring-2 focus:ring-amber-400"><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-400">kg</span></div></div><div class="min-w-0"><label class="text-[10px] font-black text-gray-500 uppercase">Harga pengepul/kg</label><input id="collectorPrice" oninput="updateSalePreview()" type="text" inputmode="numeric" class="input-rupiah mt-1.5 block w-full min-w-0 border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-amber-400" placeholder="Rp 0"></div><div class="min-w-0"><label class="text-[10px] font-black text-gray-500 uppercase">Harga nasabah/kg</label><input id="customerPrice" oninput="updateSalePreview()" type="text" inputmode="numeric" class="input-rupiah mt-1.5 block w-full min-w-0 border border-gray-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-amber-400" placeholder="Rp 0"></div></div>
                        <div class="grid grid-cols-2 gap-2"><div class="rounded-xl bg-gray-50 p-3"><span class="text-[10px] text-gray-400">Berat dijual</span><strong id="previewWeight" class="block text-sm text-gray-800">0,00 kg</strong></div><div class="rounded-xl bg-amber-50 p-3"><span class="text-[10px] text-amber-600">Total pengepul</span><strong id="previewRevenue" class="block text-sm text-amber-800">Rp 0</strong></div><div class="rounded-xl bg-emerald-50 p-3"><span class="text-[10px] text-emerald-600">Hak nasabah</span><strong id="previewRights" class="block text-sm text-emerald-800">Rp 0</strong></div><div class="rounded-xl bg-orange-50 p-3"><span class="text-[10px] text-orange-600">Margin</span><strong id="previewMargin" class="block text-sm text-orange-800">Rp 0</strong></div></div>
                        <button type="button" onclick="addSaleItem()" class="w-full bg-amber-500 hover:bg-amber-600 text-white rounded-xl py-3.5 font-black shadow-sm shadow-amber-200 transition-colors"><i class="fa-solid fa-plus mr-2"></i>Tambahkan ke Penjualan</button>
                    </div>
                </div>
                <div class="lg:col-span-7 bg-white border border-gray-100 rounded-2xl overflow-hidden flex flex-col shadow-sm min-h-80 lg:min-h-[480px]">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between"><div><h3 class="font-black text-gray-800">Rincian Penjualan</h3><p class="text-xs text-gray-400"><span id="saleItemCount">0</span> jenis sampah</p></div><span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 grid place-items-center"><i class="fa-solid fa-receipt"></i></span></div>
                    <div id="saleCart" class="p-4 space-y-3 flex-1 overflow-y-auto"></div>
                    <div class="bg-gray-900 text-white p-5 grid grid-cols-3 sm:grid-cols-4 gap-4 items-center"><div><small class="text-gray-400">Penerimaan</small><strong class="block text-amber-400" id="cartRevenue">Rp 0</strong></div><div><small class="text-gray-400">Hak nasabah</small><strong class="block" id="cartRights">Rp 0</strong></div><div><small class="text-gray-400">Margin</small><strong class="block" id="cartMargin">Rp 0</strong></div><button id="saleSubmit" type="button" onclick="submitSale()" class="col-span-3 sm:col-span-1 bg-amber-500 hover:bg-amber-400 text-gray-900 rounded-xl py-3 font-black transition-colors disabled:opacity-40 disabled:cursor-not-allowed">Proses Penjualan</button></div>
                </div>
            </div>
        </form>
    </div>
</div>

@if(session('settlement_wa'))
<div class="fixed bottom-5 right-5 z-[150] bg-white border shadow-xl rounded-2xl p-4 max-w-sm"><strong class="block mb-2">Kirim ringkasan penjualan</strong><div class="space-y-2">@foreach(session('settlement_wa') as $wa)<a target="_blank" href="{{ $wa['url'] }}" class="block bg-emerald-600 text-white rounded-xl px-4 py-2 text-sm font-bold"><i class="fa-brands fa-whatsapp mr-2"></i>{{ $wa['name'] }}</a>@endforeach</div></div>
@endif

@push('scripts')
<script>
    const stockRows = @json($stockData);
    let saleItems=[];
    const money=n=>'Rp '+Math.round(Number(n)||0).toLocaleString('id-ID');
    const weight=n=>Number(n||0).toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2})+' kg';
    function rawMoney(el) {
        return Number(el.value.replace(/\./g,''))||0;
    }
    function openSale() {
        saleForm.scrollTop=0;
        saleModal.classList.remove('hidden');
        saleModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        requestAnimationFrame(()=>{
            saleModal.classList.remove('opacity-0');
            saleModalBox.classList.remove('scale-95');
        });
    }
    function closeSale() {
        saleModal.classList.add('opacity-0');
        saleModalBox.classList.add('scale-95');
        document.body.classList.remove('overflow-hidden');
        setTimeout(()=>{
            saleModal.classList.add('hidden');
            saleModal.classList.remove('flex');
        },300);
    }
    function preview() {
        const row=stockRows.find(r=>String(r.id)===saleCategory.value);
        const w=Number(saleWeight.value)||0,p=rawMoney(collectorPrice),c=rawMoney(customerPrice);
        if(!row) return {row:null,w,p,c,revenue:0,rights:0,margin:0};
        const revenue=w*p,rights=w*c;
        return {row,w,p,c,revenue,rights,margin:revenue-rights};
    }
    function updateSalePreview() {
        const x=preview();
        stockInfo.textContent=weight(x.row?.stock);
        previewWeight.textContent=weight(x.w);
        previewRevenue.textContent=money(x.revenue);
        previewRights.textContent=money(x.rights);
        previewMargin.textContent=money(x.margin);
    }
    function addSaleItem() {
        const x=preview();
        if(!x.row||x.w<=0||x.w>x.row.stock||x.p<0||x.c<0){showToast('Lengkapi data dan pastikan berat tidak melebihi stok.','warning');return;}if(x.c>x.p){showToast('Harga nasabah tidak boleh melebihi harga pengepul.','error');return;}if(saleItems.some(i=>i.jenis_sampah_id===x.row.id)){showToast('Jenis sampah sudah ada dalam rincian.','warning');return;}saleItems.push({jenis_sampah_id:x.row.id,nama:x.row.name,berat_kg:x.w,harga_jual:x.p,harga_nasabah:x.c,revenue:x.revenue,rights:x.rights,margin:x.margin});saleCategory.value='';saleWeight.value='';collectorPrice.value='';customerPrice.value='';updateSalePreview();renderSaleCart();}
    function renderSaleCart() {
        saleCart.innerHTML=saleItems.length?saleItems.map((i,x)=>`<div class="border border-gray-100 rounded-xl p-4 flex items-center gap-3 shadow-sm"><span class="w-10 h-10 shrink-0 rounded-xl bg-amber-50 text-amber-600 grid place-items-center"><i class="fa-solid fa-recycle"></i></span><div class="flex-1 min-w-0"><strong class="block text-sm text-gray-800 truncate">${i.nama}</strong><p class="text-xs text-gray-400 mt-0.5">${weight(i.berat_kg)} · Pengepul ${money(i.harga_jual)}/kg</p><p class="text-xs text-emerald-600">Nasabah ${money(i.harga_nasabah)}/kg</p></div><div class="text-right shrink-0"><strong class="block text-sm text-gray-800">${money(i.revenue)}</strong><button type="button" onclick="saleItems.splice(${x},1);renderSaleCart()" class="mt-2 w-8 h-8 rounded-lg text-gray-300 hover:bg-red-50 hover:text-red-500 transition-colors" title="Hapus"><i class="fa-solid fa-trash-can text-xs"></i></button></div></div>`).join(''):'<div class="h-full min-h-72 grid place-items-center text-center px-6"><div><span class="mx-auto w-14 h-14 rounded-full bg-gray-100 text-gray-400 grid place-items-center"><i class="fa-solid fa-receipt text-xl"></i></span><h4 class="font-bold text-gray-600 mt-3">Belum ada penjualan</h4><p class="text-xs text-gray-400 mt-1">Pilih jenis sampah dan masukkan detail penjualan.</p></div></div>';
        saleItemCount.textContent=saleItems.length;
        cartRevenue.textContent=money(saleItems.reduce((s,i)=>s+i.revenue,0));
        cartRights.textContent=money(saleItems.reduce((s,i)=>s+i.rights,0));
        cartMargin.textContent=money(saleItems.reduce((s,i)=>s+i.margin,0));
        saleCartData.value=JSON.stringify(saleItems.map(({jenis_sampah_id,berat_kg,harga_jual,harga_nasabah})=>({jenis_sampah_id,berat_kg,harga_jual,harga_nasabah})));
        saleSubmit.disabled=saleItems.length===0;
    }
    async function submitSale() {
        if(!saleItems.length) {
            showToast('Tambahkan item penjualan.','warning');
            return;
        }
        if(await showConfirm('Sistem akan memproses penjualan, memperbarui saldo nasabah, dan mencatat kas. Lanjutkan?','Konfirmasi Penjualan')) {
            saleSubmit.disabled=true;
            saleForm.submit();
        }
    }
    renderSaleCart();
    updateSalePreview();
</script>
@endpush
@endsection
