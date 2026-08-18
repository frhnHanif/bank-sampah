@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto">
 <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-6"><div><h1 class="text-2xl font-black text-gray-800">Jenis Sampah</h1><p class="text-sm text-gray-500">Pilihan operasional untuk transaksi setoran dan penjualan.</p></div><div class="flex gap-2"><a href="{{ route('kelompok-material.index') }}" class="bg-white border border-gray-100 shadow-sm rounded-xl px-4 py-3 font-bold"><i class="fa-solid fa-layer-group mr-2 text-emerald-600"></i>Kelompok Material</a><button onclick="openType()" class="bg-emerald-600 text-white rounded-xl px-4 py-3 font-bold"><i class="fa-solid fa-plus mr-2"></i>Tambah Jenis</button></div></div>
 @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-5">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5">{{ $errors->first() }}</div>@endif
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
  @forelse($jenis as $item)
   @php
    $editData = ['id'=>$item->id,'name'=>$item->nama,'group'=>$item->kelompok_material_id,'unit'=>$item->satuan_pencatatan->value];
    $factor = $item->kelompokMaterial->faktor_emisi_kgco2e_per_kg;
   @endphp
   <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden group hover:shadow-md transition-shadow {{ !$item->is_active?'opacity-60':'' }}">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>

    <div class="flex items-start gap-4 pt-2">
     <div class="w-12 h-12 {{ $factor !== null ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }} rounded-full flex items-center justify-center text-xl shrink-0">
      <i class="fa-solid fa-recycle"></i>
     </div>
     <div class="min-w-0">
      <span class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">{{ $item->kelompokMaterial->nama }}</span>
      <h2 class="font-bold text-gray-800 leading-tight truncate" title="{{ $item->nama }}">{{ $item->nama }}</h2>
     </div>
    </div>

    <div class="my-5 flex w-full flex-wrap items-center justify-start gap-2">
     <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-[11px] font-bold text-gray-600">
      {{ $item->satuan_pencatatan->value }}
     </span>
     @if($factor !== null)
      <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700">
       <i class="fa-solid fa-leaf"></i>{{ number_format((float) $factor, 3, ',', '.') }} kgCO₂e/kg
      </span>
     @else
      <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-[11px] font-bold text-red-600">
       <i class="fa-solid fa-circle-exclamation"></i>Faktor emisi kosong
      </span>
     @endif
    </div>

    <div class="flex-1 min-h-4"></div>
    <div class="flex items-center gap-2 pt-4 border-t border-gray-50">
     <button onclick='openType(@json($editData))' class="flex-1 flex items-center justify-center gap-2 bg-blue-50 text-blue-500 hover:bg-blue-100 py-2.5 rounded-xl text-sm font-bold transition-colors">
      <i class="fa-solid fa-pen-to-square"></i>Edit
     </button>
     @if($item->is_active)
      <form action="{{ route('jenis-sampah.destroy',$item) }}" method="POST" class="shrink-0">@csrf @method('DELETE')
       <button class="w-11 h-11 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-100 rounded-xl transition-colors" title="Nonaktifkan">
        <i class="fa-solid fa-trash-can"></i>
       </button>
      </form>
     @endif
    </div>
   </article>
  @empty
   <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center flex flex-col items-center justify-center">
    <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4 text-2xl"><i class="fa-solid fa-recycle"></i></div>
    <h3 class="text-lg font-bold text-gray-700">Belum ada jenis sampah</h3>
    <p class="text-gray-500 mt-1">Tambahkan jenis sampah agar dapat digunakan dalam transaksi.</p>
   </div>
  @endforelse
 </div>
</div>
<div id="typeModal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-gray-900/50 p-4"><form id="typeForm" method="POST" class="bg-white rounded-2xl p-6 w-full max-w-md">@csrf<input id="typeMethod" type="hidden" name="_method" value="POST"><h2 id="typeTitle" class="font-black text-xl">Tambah Jenis Sampah</h2><label class="block text-xs font-black text-gray-400 uppercase mt-5">Nama jenis sampah</label><input id="typeName" name="nama" required class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"><label class="block text-xs font-black text-gray-400 uppercase mt-4">Kelompok Material</label><select id="typeGroup" name="kelompok_material_id" required class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"><option value="">Pilih kelompok</option>@foreach($kelompok as $group)<option value="{{ $group->id }}">{{ $group->nama }}</option>@endforeach</select><label class="block text-xs font-black text-gray-400 uppercase mt-4">Satuan pencatatan</label><select id="typeUnit" name="satuan_pencatatan" required class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"><option value="KG">KG</option><option value="PCS">PCS</option></select><div class="flex gap-3 mt-6"><button type="button" onclick="typeModal.classList.add('hidden')" class="flex-1 bg-gray-100 rounded-xl py-3 font-bold">Batal</button><button class="flex-1 bg-emerald-600 text-white rounded-xl py-3 font-bold">Simpan</button></div></form></div>
@push('scripts')<script>function openType(item=null){typeTitle.textContent=item?'Edit Jenis Sampah':'Tambah Jenis Sampah';typeForm.action=item?@json(url('/jenis-sampah'))+'/'+item.id:@json(route('jenis-sampah.store'));typeMethod.value=item?'PUT':'POST';typeName.value=item?.name||'';typeGroup.value=item?.group||'';typeUnit.value=item?.unit||'KG';typeModal.classList.remove('hidden');typeModal.classList.add('flex')}</script>@endpush
@endsection
