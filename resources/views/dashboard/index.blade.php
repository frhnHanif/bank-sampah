@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-8">

    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Pusat Kendali Dampak & Keuangan</h1>
            <p class="text-gray-500 text-sm font-medium mt-1">Metrik sirkularitas, ekuivalen reduksi emisi, dan neraca kas.</p>
        </div>
        <span class="bg-emerald-50 border border-emerald-200 text-emerald-600 text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span> Live Data
        </span>
    </div>

    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Ringkasan Keseluruhan</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        
        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-500 mb-1">Total sampah terkumpul</p>
            <div class="flex items-baseline gap-1">
                <h3 class="text-2xl font-black text-gray-800">{{ number_format($totalSampah, 0, ',', '.') }}</h3>
                <span class="text-sm font-bold text-gray-400">kg</span>
            </div>
            <p class="text-[10px] font-bold mt-2 flex items-center gap-1 {{ $trenSampah >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                <i class="fa-solid {{ $trenSampah >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                {{ abs(round($trenSampah)) }}% vs bulan lalu
            </p>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-black text-emerald-700 mb-1">Reduksi emisi CO₂</p>
            <div class="flex items-baseline gap-1">
                <h3 class="text-2xl font-black text-emerald-700">{{ number_format($totalCO2, 0, ',', '.') }}</h3>
                <span class="text-sm font-bold text-emerald-600/70">kg</span>
            </div>
            <p class="text-[10px] font-bold mt-2 flex items-center gap-1 {{ $trenCO2 >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                <i class="fa-solid {{ $trenCO2 >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                {{ abs(round($trenCO2)) }}% vs bulan lalu
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-500 mb-1">Nilai ekonomi</p>
            <div class="flex items-baseline gap-1">
                <span class="text-sm font-bold text-gray-400">Rp</span>
                <h3 class="text-2xl font-black text-gray-800">{{ number_format($nilaiEkonomi/1000000, 1, ',', '.') }}</h3>
                <span class="text-sm font-bold text-gray-400">jt</span>
            </div>
            <p class="text-[10px] font-bold mt-2 text-gray-400">Akumulasi seluruh periode</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-500 mb-1">Nasabah aktif</p>
            <div class="flex items-baseline gap-1">
                <h3 class="text-2xl font-black text-gray-800">{{ $nasabahAktif }}</h3>
                <span class="text-sm font-bold text-gray-400">warga</span>
            </div>
            <p class="text-[10px] font-bold mt-2 text-gray-400">dari {{ $totalNasabah }} terdaftar</p>
        </div>
    </div>

    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Target & Dampak CO₂ — Fokus CSR</p>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-emerald-600"></i>
                <h3 class="font-bold text-gray-800 text-sm">Capaian target bulanan</h3>
            </div>
            <div class="p-6 flex-1 flex flex-col justify-center">
                <div class="relative w-32 h-32 mx-auto mb-6">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#ecfdf5" stroke-width="10"></circle>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="10" stroke-dasharray="251.2" stroke-dashoffset="{{ 251.2 - (251.2 * $persenTarget / 100) }}" stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-black text-emerald-600 tracking-tighter">{{ $persenTarget }}<span class="text-xl">%</span></span>
                    </div>
                </div>
                
                <div class="w-full bg-gray-100 rounded-full h-2.5 mb-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2.5 rounded-full" style="width: {{ $persenTarget }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] font-bold text-gray-500">
                    <span class="text-emerald-600">{{ number_format($co2BulanIni, 0, ',', '.') }} kg tercapai</span>
                    <span>Target: {{ number_format($targetCO2, 0, ',', '.') }} kg</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-tree-city text-emerald-600"></i>
                <h3 class="font-bold text-gray-800 text-sm">Dampak setara — dalam satuan nyata</h3>
            </div>
            <div class="p-0 flex-1 flex flex-col justify-center">
                <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0"><i class="fa-solid fa-tree"></i></div>
                    <div class="flex-1">
                        <p class="text-[11px] font-bold text-gray-500 uppercase">Setara pohon yang ditanam</p>
                        <h4 class="text-lg font-black text-emerald-600">{{ number_format($ekuivalenPohon, 0, ',', '.') }} pohon</h4>
                    </div>
                    <div class="text-[10px] text-gray-400 font-bold text-right hidden sm:block bg-gray-50 px-3 py-1.5 rounded-lg">@ {{ $setPohon?->nilai ?? 11 }} kg CO₂/pohon/tahun</div>
                </div>
                <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0"><i class="fa-solid fa-car"></i></div>
                    <div class="flex-1">
                        <p class="text-[11px] font-bold text-gray-500 uppercase">Setara jarak perjalanan mobil</p>
                        <h4 class="text-lg font-black text-blue-600">{{ number_format($ekuivalenBensin, 0, ',', '.') }} km dihemat</h4>
                    </div>
                    <div class="text-[10px] text-gray-400 font-bold text-right hidden sm:block bg-gray-50 px-3 py-1.5 rounded-lg">@ {{ rtrim(rtrim(number_format($setMobil?->nilai ?? 0.167, 3, ',', '.'), '0'), ',') }} kg CO₂/km</div>
                </div>
                <div class="flex items-center gap-4 px-6 py-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0"><i class="fa-solid fa-bolt"></i></div>
                    <div class="flex-1">
                        <p class="text-[11px] font-bold text-gray-500 uppercase">Setara konsumsi listrik rumah tangga</p>
                        <h4 class="text-lg font-black text-amber-600">{{ number_format($ekuivalenListrik, 1, ',', '.') }} bulan dihemat</h4>
                    </div>
                    <div class="text-[10px] text-gray-400 font-bold text-right hidden sm:block bg-gray-50 px-3 py-1.5 rounded-lg">@ {{ $setListrik?->nilai ?? 141 }} kg CO₂/bulan/rumah</div>
                </div>
            </div>
        </div>
    </div>

    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Tren & Distribusi Emisi</p>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-emerald-600"></i>
                <h3 class="font-bold text-gray-800 text-sm">Tren reduksi CO₂ (6 bulan)</h3>
            </div>
            <div class="p-6 flex-1">
                @php
                    $totalPoints = count($co2Bulanan);
                    $svgWidth = 280; $svgHeight = 100;
                    $chartPadLeft = 28; $chartPadRight = 16; $chartPadTop = 10; $chartPadBottom = 20;
                    $chartW = $svgWidth - $chartPadLeft - $chartPadRight;
                    $chartH = $svgHeight - $chartPadTop - $chartPadBottom;
                @endphp
                <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" width="100%" style="display:block;overflow:visible;">
                    <defs>
                        <linearGradient id="co2Grad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#059669" stop-opacity="0.15"/>
                            <stop offset="100%" stop-color="#059669" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    {{-- Grid lines --}}
                    <line x1="{{ $chartPadLeft }}" y1="{{ $chartPadTop }}" x2="{{ $chartPadLeft }}" y2="{{ $chartPadTop + $chartH }}" stroke="#d1fae5" stroke-width="0.5"/>
                    <line x1="{{ $chartPadLeft }}" y1="{{ $chartPadTop + $chartH }}" x2="{{ $chartPadLeft + $chartW }}" y2="{{ $chartPadTop + $chartH }}" stroke="#d1fae5" stroke-width="0.5"/>
                    <line x1="{{ $chartPadLeft }}" y1="{{ $chartPadTop + $chartH * 0.33 }}" x2="{{ $chartPadLeft + $chartW }}" y2="{{ $chartPadTop + $chartH * 0.33 }}" stroke="#d1fae5" stroke-width="0.5" stroke-dasharray="3,3"/>
                    <line x1="{{ $chartPadLeft }}" y1="{{ $chartPadTop + $chartH * 0.66 }}" x2="{{ $chartPadLeft + $chartW }}" y2="{{ $chartPadTop + $chartH * 0.66 }}" stroke="#d1fae5" stroke-width="0.5" stroke-dasharray="3,3"/>
                    @if($maxCO2Bulanan > 0 && $totalPoints > 0)
                        @php
                            $points = [];
                            foreach ($co2Bulanan as $i => $d) {
                                $x = $chartPadLeft + ($i / max(1, $totalPoints - 1)) * $chartW;
                                $y = $chartPadTop + $chartH - ($d['total'] / $maxCO2Bulanan) * $chartH;
                                $points[] = number_format($x, 1, '.', '') . ',' . number_format($y, 1, '.', '');
                            }
                            $polylinePoints = implode(' ', $points);
                        @endphp
                        <polyline points="{{ $polylinePoints }}" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polygon points="{{ $polylinePoints }} {{ $chartPadLeft + $chartW }},{{ $chartPadTop + $chartH }} {{ $chartPadLeft }},{{ $chartPadTop + $chartH }}" fill="url(#co2Grad)"/>
                        @foreach ($co2Bulanan as $i => $d)
                            @php
                                $x = $chartPadLeft + ($i / max(1, $totalPoints - 1)) * $chartW;
                                $y = $chartPadTop + $chartH - ($d['total'] / $maxCO2Bulanan) * $chartH;
                                $isLast = $i === $totalPoints - 1;
                            @endphp
                            <circle cx="{{ number_format($x, 1, '.', '') }}" cy="{{ number_format($y, 1, '.', '') }}" r="{{ $isLast ? 3.5 : 3 }}" fill="#059669" {{ $isLast ? 'stroke="white" stroke-width="1.5"' : '' }}/>
                            <text x="{{ number_format($x, 1, '.', '') }}" y="{{ $chartPadTop + $chartH + 14 }}" fill="#9ca3af" font-size="9" text-anchor="middle" font-family="Inter, sans-serif">{{ $d['label'] }}</text>
                            @if($isLast && $d['total'] > 0)
                                <text x="{{ number_format($x, 1, '.', '') }}" y="{{ number_format($y - 8, 1, '.', '') }}" fill="#059669" font-size="9" font-weight="600" text-anchor="middle" font-family="Inter, sans-serif">{{ number_format($d['total'], 0, ',', '.') }}</text>
                            @endif
                        @endforeach
                    @else
                        <text x="{{ $svgWidth / 2 }}" y="{{ $svgHeight / 2 }}" fill="#9ca3af" font-size="12" text-anchor="middle" font-family="Inter, sans-serif">Belum ada data</text>
                    @endif
                </svg>
                <div class="text-[11px] text-gray-400 font-bold mt-2">Satuan: kg CO₂ ter-offset per bulan</div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-emerald-600"></i>
                <h3 class="font-bold text-gray-800 text-sm">Kontribusi CO₂ per kategori sampah</h3>
            </div>
            <div class="p-6 flex-1 flex items-center">
                @if($co2PerKategori->count() > 0)
                    @php
                        $donutRadius = 38; $donutStroke = 16;
                        $circumference = 2 * M_PI * $donutRadius;
                        $cumulativeOffset = 0;
                    @endphp
                    <div class="flex items-center gap-6 w-full">
                        <svg viewBox="0 0 100 100" width="110" height="110" style="flex-shrink:0;">
                            @php $cumulativeOffset = 0; @endphp
                            @foreach($co2PerKategori as $i => $kat)
                                @php
                                    $pct = $kat->total_co2 / $totalCO2Kategori;
                                    $dashLen = $pct * $circumference;
                                    $color = $paletteKategori[$i % count($paletteKategori)];
                                @endphp
                                <circle cx="50" cy="50" r="{{ $donutRadius }}" fill="none" stroke="{{ $color }}" stroke-width="{{ $donutStroke }}"
                                    stroke-dasharray="{{ number_format($dashLen, 1, '.', '') }} {{ number_format($circumference - $dashLen, 1, '.', '') }}"
                                    stroke-dashoffset="{{ number_format(-$cumulativeOffset, 1, '.', '') }}"
                                    transform="rotate(-90 50 50)"/>
                                @php $cumulativeOffset += $dashLen; @endphp
                            @endforeach
                            <circle cx="50" cy="50" r="{{ $donutRadius - $donutStroke / 2 + 2 }}" fill="white"/>
                            <text x="50" y="46" text-anchor="middle" font-size="10" font-weight="600" fill="#1f2937" font-family="Inter, sans-serif">Total</text>
                            <text x="50" y="58" text-anchor="middle" font-size="9" fill="#6b7280" font-family="Inter, sans-serif">{{ number_format($totalCO2Kategori, 0, ',', '.') }} kg</text>
                        </svg>
                        <div class="flex-1 space-y-2">
                            @foreach($co2PerKategori as $i => $kat)
                                @php
                                    $pct = round(($kat->total_co2 / $totalCO2Kategori) * 100);
                                    $color = $paletteKategori[$i % count($paletteKategori)];
                                @endphp
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $color }};"></span>
                                    <span class="flex-1 text-gray-500">{{ $kat->nama }}</span>
                                    <span class="font-semibold text-gray-700">{{ $pct }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center text-sm text-gray-400 py-8 w-full font-medium italic">Belum ada data kategori</div>
                @endif
            </div>
        </div>
    </div>

    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Kontribusi Wilayah & Nasabah</p>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                <h3 class="font-bold text-gray-800 text-sm">Reduksi CO₂ per RT</h3>
            </div>
            <div class="p-6 flex-1 space-y-4">
                @php
                    $colors = ['bg-emerald-600', 'bg-emerald-500', 'bg-emerald-400', 'bg-emerald-300', 'bg-emerald-200'];
                @endphp
                @forelse($reduksiPerRT as $index => $rt)
                    @php 
                        $percentage = ($rt->total_co2 / $maxRT) * 100; 
                        $bgColor = $colors[$index % 5] ?? 'bg-emerald-200';
                        $isLow = $percentage < 30;
                    @endphp
                    <div class="flex items-center gap-4">
                        <div class="w-10 text-xs font-black text-gray-500">RT {{ str_pad($rt->rt, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden flex">
                            <div class="h-full rounded-full {{ $isLow ? 'bg-emerald-100' : $bgColor }}" style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="w-20 text-right text-xs font-bold {{ $isLow ? 'text-gray-400' : 'text-gray-700' }}">
                            {{ number_format($rt->total_co2, 0, ',', '.') }} kg
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-gray-400 py-4 font-medium italic">Belum ada data distribusi RT</div>
                @endforelse
                
                <div class="pt-4 mt-2 border-t border-gray-50 text-[10px] font-bold text-gray-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-emerald-500 text-xs"></i> Data diurutkan berdasarkan kontribusi reduksi tertinggi
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-medal text-amber-500"></i>
                    <h3 class="font-bold text-gray-800 text-sm">Top kontributor CO₂</h3>
                </div>
                <span class="text-[9px] font-black text-amber-600 bg-amber-50 px-2 py-1 rounded-md uppercase tracking-wider">Bulan Ini</span>
            </div>
            <div class="p-0">
                @forelse($topKontributor as $index => $nasabah)
                    <div class="flex items-center justify-between px-6 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black shadow-sm
                                {{ $index == 0 ? 'bg-amber-100 text-amber-600 border border-amber-200' : 
                                  ($index == 1 ? 'bg-gray-100 text-gray-600 border border-gray-200' : 
                                  ($index == 2 ? 'bg-orange-50 text-orange-600 border border-orange-100' : 'bg-gray-50 text-gray-400')) }}">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-800">{{ $nasabah->nama }}</div>
                                <div class="text-[10px] font-bold text-gray-400">RT {{ str_pad($nasabah->rt, 2, '0', STR_PAD_LEFT) }} / RW {{ str_pad($nasabah->rw, 2, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-black text-emerald-600">{{ number_format($nasabah->total_co2, 1, ',', '.') }} <span class="text-[10px] text-emerald-500 font-bold">kg CO₂</span></div>
                            <div class="text-[10px] font-bold text-gray-400">= {{ max(1, round($nasabah->total_co2 / ($setPohon?->nilai ?? 11))) }} pohon</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-gray-400 py-10 font-medium italic">Belum ada kontributor bulan ini</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection