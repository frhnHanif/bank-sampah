@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto"><div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-6"><div><a href="{{ route('jenis-sampah.index') }}" class="text-sm text-emerald-700 font-bold">← Jenis Sampah</a><h1 class="text-2xl font-black mt-2">Kelompok Material</h1><p class="text-sm text-gray-500">Faktor emisi dan sumbernya dikelola pada level kelompok.</p></div><button onclick="openGroup()" class="bg-emerald-600 hover:bg-emerald-700 shadow-sm transition text-white rounded-xl px-5 py-3 font-bold focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300 focus-visible:ring-offset-2"><i class="fa-solid fa-plus mr-2"></i>Tambah Kelompok</button></div>
@if(session('success'))<div class="bg-emerald-50 border p-4 rounded-xl mb-5">{{ session('success') }}</div>@endif @if($errors->any())<div class="bg-red-50 border p-4 rounded-xl mb-5">{{ $errors->first() }}</div>@endif
<div class="space-y-3">
 @forelse($kelompok as $item)
  <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 relative overflow-hidden hover:shadow-md transition-shadow {{ !$item->is_active?'opacity-60':'' }}">
   <div class="pt-1 flex-1 min-w-0">
    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Kelompok Material</span>
    <h2 class="font-bold text-gray-800 leading-tight truncate" title="{{ $item->nama }}">{{ $item->nama }}</h2>
    <div class="mt-2 flex flex-wrap items-center gap-2">
     @if($item->faktor_emisi_kgco2e_per_kg === null)
      <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-[11px] font-bold text-red-600"><i class="fa-solid fa-circle-exclamation"></i>Faktor emisi kosong</span>
     @else
      <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700"><i class="fa-solid fa-leaf"></i>{{ number_format((float) $item->faktor_emisi_kgco2e_per_kg, 3, ',', '.') }} kgCO₂e/kg</span>
     @endif
     <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-[11px] font-bold text-gray-600">{{ $item->jenis_sampah_count }} jenis</span>
    </div>
   </div>
   <div class="flex shrink-0 items-center justify-end gap-2">
    <button type="button" onclick='openGroup(@json($item))' class="w-11 h-11 flex items-center justify-center bg-blue-50 text-blue-500 hover:bg-blue-100 rounded-xl transition-colors focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 focus-visible:ring-offset-2" title="Edit kelompok material" aria-label="Edit kelompok material"><i class="fa-solid fa-pen-to-square"></i></button>
    <form action="{{ route('kelompok-material.toggle',$item) }}" method="POST">@csrf @method('PATCH')
     <button class="w-11 h-11 flex items-center justify-center {{ $item->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} rounded-xl transition-colors" title="{{ $item->is_active?'Nonaktifkan':'Aktifkan' }} kelompok material" aria-label="{{ $item->is_active?'Nonaktifkan':'Aktifkan' }} kelompok material"><i class="fa-solid {{ $item->is_active ? 'fa-power-off' : 'fa-circle-check' }}"></i></button>
    </form>
   </div>
  </article>
 @empty
  <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center"><h3 class="text-lg font-bold text-gray-700">Belum ada kelompok material</h3><p class="text-gray-500 mt-1">Tambahkan kelompok material untuk mengelola faktor emisi.</p></div>
 @endforelse
</div></div>
<div id="groupModal" data-keyboard-modal class="fixed inset-0 z-[120] hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-300"><form id="groupForm" method="POST" class="bg-white rounded-2xl p-6 w-full max-w-xl transform scale-95 transition-transform duration-300" data-modal-box>@csrf<input id="groupMethod" type="hidden" name="_method"><h2 id="groupTitle" class="font-black text-xl">Tambah Kelompok Material</h2><div class="grid sm:grid-cols-2 gap-4 mt-5"><div><label class="text-xs font-black text-gray-400 uppercase">Nama</label><input id="groupName" name="nama" required class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"></div><div><label class="text-xs font-black text-gray-400 uppercase">Faktor kgCO₂e/kg</label><input id="groupFactor" name="faktor_emisi_kgco2e_per_kg" type="number" min="0" step="0.000001" class="mt-2 w-full bg-gray-50 border-gray-200 border rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Boleh kosong"></div><div><label class="text-xs font-black text-gray-400 uppercase">Versi</label><input id="groupVersion" name="versi_faktor_emisi" class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"></div><div><label class="text-xs font-black text-gray-400 uppercase">Tanggal berlaku</label><input id="groupDate" name="tanggal_berlaku_faktor_emisi" type="date" class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"></div></div><label class="block text-xs font-black text-gray-400 uppercase mt-4">Sumber</label><textarea id="groupSource" name="sumber_faktor_emisi" class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"></textarea><label class="block text-xs font-black text-gray-400 uppercase mt-4">Catatan</label><textarea id="groupNotes" name="catatan_faktor_emisi" class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"></textarea><div class="flex gap-3 mt-6"><button type="button" data-modal-dismiss onclick="closeGroup()" class="flex-1 bg-gray-100 rounded-xl py-3 font-bold">Batal</button><button class="flex-1 bg-emerald-600 text-white rounded-xl py-3 font-bold">Simpan</button></div></form></div>
@push('scripts')
<script>
    const groupModal = document.getElementById('groupModal');
    const groupModalBox = groupModal.querySelector('[data-modal-box]');

    function openGroup(x=null) {
        groupTitle.textContent=x?'Edit Kelompok Material':'Tambah Kelompok Material';
        groupForm.action=x?@json(url('/kelompok-material'))+'/'+x.id:@json(route('kelompok-material.store'));
        groupMethod.value=x?'PUT':'POST';groupName.value=x?.nama||'';
        groupFactor.value=x?.faktor_emisi_kgco2e_per_kg||'';groupVersion.value=x?.versi_faktor_emisi||'';
        groupDate.value=x?.tanggal_berlaku_faktor_emisi?.substring(0,10)||'';
        groupSource.value=x?.sumber_faktor_emisi||'';groupNotes.value=x?.catatan_faktor_emisi||'';
        groupModal.classList.remove('hidden');
        groupModal.classList.add('flex');
        setTimeout(() => {
            groupModal.classList.remove('opacity-0');
            groupModalBox.classList.remove('scale-95');
        }, 10);
    }

    function closeGroup() {
        groupModal.classList.add('opacity-0');
        groupModalBox.classList.add('scale-95');
        setTimeout(() => {
            groupModal.classList.add('hidden');
            groupModal.classList.remove('flex');
        }, 300);
    }
</script>
@endpush
@endsection
