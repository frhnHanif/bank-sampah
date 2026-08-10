@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-6">
    <div><h1 class="text-2xl font-black text-gray-800">Stok & Settlement</h1><p class="text-sm text-gray-500">Stok fisik = opening legacy + lot setoran baru yang belum teralokasi.</p></div>
    <button type="button" onclick="openSale()" class="bg-amber-500 hover:bg-amber-600 text-white rounded-xl px-5 py-3 font-black"><i class="fa-solid fa-truck-ramp-box mr-2"></i>Jual ke Pengepul</button>
</div>
@if(session('success'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-5">{{ session('success') }}</div>@endif
@if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
@forelse($stok as $row)
    <article class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center gap-3"><span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 grid place-items-center"><i class="fa-solid fa-boxes-stacked"></i></span><h2 class="font-black text-gray-800">{{ $row->kategori?->nama ?? 'Kategori nonaktif' }}</h2></div>
        <div class="mt-5"><p class="text-[10px] uppercase font-black text-gray-400">Stok fisik</p><p class="text-3xl font-black text-blue-600">{{ number_format($row->total_berat_kg,2,',','.') }} <small class="text-sm">kg</small></p></div>
        <div class="grid grid-cols-2 gap-2 mt-4 text-xs"><div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500">Legacy</span><strong class="block text-gray-800">{{ number_format((float)($legacy[$row->kategori_id] ?? 0),2,',','.') }} kg</strong></div><div class="bg-amber-50 rounded-xl p-3"><span class="text-amber-700">Pending baru</span><strong class="block text-amber-900">{{ number_format((float)($pending[$row->kategori_id] ?? 0),2,',','.') }} kg</strong></div></div>
    </article>
@empty
    <div class="col-span-full bg-white border rounded-2xl p-12 text-center text-gray-500">Gudang masih kosong.</div>
@endforelse
</div>

<div id="saleModal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-gray-900/60 p-4 overflow-y-auto">
<div class="bg-gray-50 rounded-2xl w-full max-w-6xl shadow-xl overflow-hidden my-auto">
    <div class="bg-white border-b p-5 flex justify-between"><div><h2 class="font-black text-xl">Penjualan & Settlement FIFO</h2><p class="text-xs text-gray-500">Harga nasabah hanya diterapkan pada lot setoran baru.</p></div><button type="button" onclick="closeSale()"><i class="fa-solid fa-xmark text-xl"></i></button></div>
    <form id="saleForm" action="{{ route('jual.store') }}" method="POST" class="p-5">@csrf<input type="hidden" name="cart_data" id="saleCartData">
        <div class="grid lg:grid-cols-12 gap-5">
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white border rounded-2xl p-4 grid sm:grid-cols-2 gap-3"><div><label class="text-xs font-black text-gray-400 uppercase">Tanggal</label><input name="tanggal" type="date" required value="{{ date('Y-m-d') }}" class="mt-2 w-full border rounded-xl px-3 py-2.5"></div><div><label class="text-xs font-black text-gray-400 uppercase">Pengepul / catatan</label><input name="catatan" class="mt-2 w-full border rounded-xl px-3 py-2.5" placeholder="Nama pengepul"></div></div>
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 space-y-3">
                    <select id="saleCategory" onchange="updateSalePreview()" class="w-full border border-amber-200 rounded-xl px-3 py-3"><option value="">Pilih jenis sampah</option>@foreach($stok as $row)@if($row->kategori && $row->total_berat_kg>0)<option value="{{ $row->kategori_id }}">{{ $row->kategori->nama }}</option>@endif @endforeach</select>
                    <div class="grid grid-cols-3 gap-2 text-xs"><div class="bg-white rounded-xl p-2">Stok<strong id="stockInfo" class="block">0 kg</strong></div><div class="bg-white rounded-xl p-2">Legacy<strong id="legacyInfo" class="block">0 kg</strong></div><div class="bg-white rounded-xl p-2">Pending<strong id="pendingInfo" class="block">0 kg</strong></div></div>
                    <div class="grid sm:grid-cols-3 gap-3"><div><label class="text-xs font-bold">Berat dijual (kg)</label><input id="saleWeight" oninput="updateSalePreview()" type="number" min="0.01" step="0.01" class="mt-1 w-full border rounded-xl px-3 py-2.5"></div><div><label class="text-xs font-bold">Harga pengepul/kg</label><input id="collectorPrice" oninput="updateSalePreview()" type="text" inputmode="numeric" class="input-rupiah mt-1 w-full border rounded-xl px-3 py-2.5"></div><div><label class="text-xs font-bold">Harga nasabah/kg</label><input id="customerPrice" oninput="updateSalePreview()" type="text" inputmode="numeric" class="input-rupiah mt-1 w-full border rounded-xl px-3 py-2.5"></div></div>
                    <div class="bg-white rounded-xl p-3 text-xs grid grid-cols-2 gap-2"><span>Alokasi legacy<strong id="previewLegacy" class="block text-gray-800">0 kg</strong></span><span>Settlement lot baru<strong id="previewNew" class="block text-emerald-700">0 kg</strong></span><span>Total pengepul<strong id="previewRevenue" class="block">Rp 0</strong></span><span>Hak nasabah baru<strong id="previewRights" class="block text-emerald-700">Rp 0</strong></span><span>Cost basis legacy<strong id="previewLegacyCost" class="block">Rp 0</strong></span><span>Margin kotor<strong id="previewMargin" class="block text-amber-700">Rp 0</strong></span></div>
                    <button type="button" onclick="addSaleItem()" class="w-full bg-amber-500 text-white rounded-xl py-3 font-black">Tambahkan ke Penjualan</button>
                </div>
            </div>
            <div class="lg:col-span-7 bg-white border rounded-2xl overflow-hidden flex flex-col"><div class="p-4 border-b"><h3 class="font-black">Rincian Penjualan</h3></div><div id="saleCart" class="divide-y flex-1"><p class="p-10 text-center text-gray-400">Belum ada item.</p></div><div class="bg-gray-900 text-white p-5 grid sm:grid-cols-4 gap-4"><div><small class="text-gray-400">Penerimaan</small><strong class="block text-amber-400" id="cartRevenue">Rp 0</strong></div><div><small class="text-gray-400">Hak nasabah</small><strong class="block" id="cartRights">Rp 0</strong></div><div><small class="text-gray-400">Margin preview</small><strong class="block" id="cartMargin">Rp 0</strong></div><button id="saleSubmit" type="button" onclick="submitSale()" class="bg-amber-500 text-gray-900 rounded-xl py-3 font-black">Proses Atomic</button></div></div>
        </div>
    </form>
</div></div>

@if(session('settlement_wa'))
<div class="fixed bottom-5 right-5 z-[150] bg-white border shadow-xl rounded-2xl p-4 max-w-sm"><strong class="block mb-2">Kirim ringkasan settlement</strong><div class="space-y-2">@foreach(session('settlement_wa') as $wa)<a target="_blank" href="{{ $wa['url'] }}" class="block bg-emerald-600 text-white rounded-xl px-4 py-2 text-sm font-bold"><i class="fa-brands fa-whatsapp mr-2"></i>{{ $wa['name'] }}</a>@endforeach</div></div>
@endif

@push('scripts')
<script>
const stockRows = @json($stockData);
let saleItems=[];
const money=n=>'Rp '+Math.round(Number(n)||0).toLocaleString('id-ID'); const weight=n=>Number(n||0).toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2})+' kg';
function rawMoney(el){return Number(el.value.replace(/\./g,''))||0;}
function openSale(){saleModal.classList.remove('hidden');saleModal.classList.add('flex');}
function closeSale(){saleModal.classList.add('hidden');saleModal.classList.remove('flex');}
function preview(){const row=stockRows.find(r=>String(r.id)===saleCategory.value);const w=Number(saleWeight.value)||0,p=rawMoney(collectorPrice),c=rawMoney(customerPrice);if(!row)return {row:null,w,p,c,legacy:0,fresh:0,revenue:0,rights:0,legacyCost:0,margin:0};const legacy=Math.min(w,row.legacy),fresh=Math.max(0,w-legacy),legacyCost=row.legacy>0?legacy/row.legacy*row.legacyCost:0,revenue=w*p,rights=fresh*c;return {row,w,p,c,legacy,fresh,revenue,rights,legacyCost,margin:revenue-rights-legacyCost};}
function updateSalePreview(){const x=preview();stockInfo.textContent=weight(x.row?.stock);legacyInfo.textContent=weight(x.row?.legacy);pendingInfo.textContent=weight(x.row?.pending);previewLegacy.textContent=weight(x.legacy);previewNew.textContent=weight(x.fresh);previewRevenue.textContent=money(x.revenue);previewRights.textContent=money(x.rights);previewLegacyCost.textContent=money(x.legacyCost);previewMargin.textContent=money(x.margin);}
function addSaleItem(){const x=preview();if(!x.row||x.w<=0||x.w>x.row.stock||x.p<0||x.c<0){showToast('Lengkapi data dan pastikan berat tidak melebihi stok.','warning');return;}if(x.fresh>0&&x.c>x.p){showToast('Harga nasabah tidak boleh melebihi harga pengepul.','error');return;}if(saleItems.some(i=>i.kategori_id===x.row.id)){showToast('Kategori sudah ada dalam rincian. Hapus dahulu untuk mengganti.','warning');return;}saleItems.push({kategori_id:x.row.id,nama:x.row.name,berat:x.w,harga_jual:x.p,harga_nasabah:x.c,legacy:x.legacy,fresh:x.fresh,revenue:x.revenue,rights:x.rights,legacyCost:x.legacyCost,margin:x.margin});saleCategory.value='';saleWeight.value='';collectorPrice.value='';customerPrice.value='';updateSalePreview();renderSaleCart();}
function renderSaleCart(){saleCart.innerHTML=saleItems.length?saleItems.map((i,x)=>`<div class="p-4 flex gap-3"><div class="flex-1"><strong>${i.nama}</strong><p class="text-xs text-gray-500">${weight(i.berat)} | legacy ${weight(i.legacy)} | baru ${weight(i.fresh)}</p><p class="text-xs">Pengepul ${money(i.harga_jual)}/kg - Nasabah ${money(i.harga_nasabah)}/kg</p></div><div class="text-right"><strong>${money(i.revenue)}</strong><button type="button" onclick="saleItems.splice(${x},1);renderSaleCart()" class="block ml-auto text-red-400 mt-2"><i class="fa-solid fa-trash"></i></button></div></div>`).join(''):'<p class="p-10 text-center text-gray-400">Belum ada item.</p>';cartRevenue.textContent=money(saleItems.reduce((s,i)=>s+i.revenue,0));cartRights.textContent=money(saleItems.reduce((s,i)=>s+i.rights,0));cartMargin.textContent=money(saleItems.reduce((s,i)=>s+i.margin,0));saleCartData.value=JSON.stringify(saleItems.map(({kategori_id,berat,harga_jual,harga_nasabah})=>({kategori_id,berat,harga_jual,harga_nasabah})));}
async function submitSale(){if(!saleItems.length){showToast('Tambahkan item penjualan.','warning');return;}if(await showConfirm('Sistem akan mengalokasikan legacy lalu setoran baru secara FIFO, mengkredit saldo, dan mencatat kas. Lanjutkan?','Konfirmasi Settlement')){saleSubmit.disabled=true;saleForm.submit();}}
renderSaleCart();updateSalePreview();
</script>
@endpush
@endsection
