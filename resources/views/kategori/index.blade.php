@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-6">
        <div><h1 class="text-2xl font-black text-gray-800">Jenis Sampah</h1><p class="text-sm text-gray-500">Master komoditas tanpa harga. Harga selalu ditentukan pada settlement penjualan.</p></div>
        <div class="flex gap-2"><a href="{{ route('faktor-emisi.index') }}" class="bg-white border rounded-xl px-4 py-3 font-bold text-gray-700"><i class="fa-solid fa-leaf mr-2 text-emerald-600"></i>Master Faktor</a><button onclick="openCreate()" class="bg-emerald-600 text-white rounded-xl px-4 py-3 font-bold"><i class="fa-solid fa-plus mr-2"></i>Tambah Jenis</button></div>
    </div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($kategori as $item)
        <article class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start gap-3"><span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center"><i class="fa-solid fa-recycle"></i></span><div class="flex-1"><h2 class="font-black text-gray-800">{{ $item->nama }}</h2><p class="text-xs text-gray-400 mt-1">Key: {{ $item->nama_normalized }}</p></div></div>
            <div class="mt-4">
                @if($item->faktorEmisi)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700"><i class="fa-solid fa-leaf"></i>{{ rtrim(rtrim(number_format($item->faktorEmisi->faktor_kgco2e_per_kg, 3, ',', '.'), '0'), ',') }} kgCO₂e/kg</span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600"><i class="fa-solid fa-circle-exclamation"></i>Faktor emisi kosong</span>
                @endif
            </div>
            <div class="flex gap-2 mt-4"><button onclick='openEdit(@json(["id"=>$item->id,"name"=>$item->nama,"factor"=>$item->faktor_emisi_id]))' class="flex-1 bg-gray-100 rounded-xl py-2.5 font-bold text-sm">Edit</button><form action="{{ route('kategori.destroy',$item) }}" method="POST" class="shrink-0" onsubmit="return confirm('Nonaktifkan jenis ini?')">@csrf @method('DELETE')<button class="w-11 h-11 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-100 rounded-xl transition-colors" title="Nonaktifkan"><i class="fa-solid fa-trash-can"></i></button></form></div>
        </article>
    @empty <div class="col-span-full bg-white border rounded-2xl p-10 text-center text-gray-500">Belum ada jenis sampah.</div> @endforelse
    </div>
</div>

<div id="categoryModal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-gray-900/50 p-4"><form id="categoryForm" method="POST" class="bg-white rounded-2xl p-6 w-full max-w-md">@csrf<input id="methodField" type="hidden" name="_method" value="POST"><h2 id="categoryTitle" class="font-black text-xl">Tambah Jenis Sampah</h2><label class="block text-xs font-black text-gray-400 uppercase mt-5">Nama jenis sampah</label><input id="categoryName" name="nama" required maxlength="255" class="mt-2 w-full border rounded-xl px-4 py-3"><p class="text-xs text-gray-500 mt-1">Spasi dirapikan dan nama duplikat case-insensitive ditolak.</p><label class="block text-xs font-black text-gray-400 uppercase mt-4">Kelompok / faktor emisi</label><select id="categoryFactor" name="faktor_emisi_id" class="mt-2 w-full border rounded-xl px-4 py-3"><option value="">Belum diklasifikasikan</option>@foreach($faktorEmisi as $factor)<option value="{{ $factor->id }}">{{ $factor->nama_material }}</option>@endforeach</select><div class="flex gap-3 mt-6"><button type="button" onclick="closeCategory()" class="flex-1 bg-gray-100 rounded-xl py-3 font-bold">Batal</button><button class="flex-1 bg-emerald-600 text-white rounded-xl py-3 font-bold">Simpan</button></div></form></div>
@push('scripts')<script>
function openCreate(){categoryTitle.textContent='Tambah Jenis Sampah';categoryForm.action=@json(route('kategori.store'));methodField.value='POST';categoryName.value='';categoryFactor.value='';categoryModal.classList.remove('hidden');categoryModal.classList.add('flex');}
function openEdit(item){categoryTitle.textContent='Edit Jenis Sampah';categoryForm.action=@json(url('/kategori'))+'/'+item.id;methodField.value='PUT';categoryName.value=item.name;categoryFactor.value=item.factor||'';categoryModal.classList.remove('hidden');categoryModal.classList.add('flex');}
function closeCategory(){categoryModal.classList.add('hidden');categoryModal.classList.remove('flex');}
</script>@endpush
@endsection
