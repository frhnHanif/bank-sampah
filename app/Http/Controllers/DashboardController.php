<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSistem;
use App\Models\Nasabah;
use App\Models\Tabungan;
use App\Models\MutasiKas;
use App\Models\ItemSetor;
use App\Models\TransaksiSetor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // 1. Ringkasan Keseluruhan & Tren Bulan Lalu
        $totalSampah = ItemSetor::sum('berat_kg');
        $sampahBulanIni = ItemSetor::whereMonth('created_at', $now->month)->sum('berat_kg');
        $sampahBulanLalu = ItemSetor::whereMonth('created_at', $lastMonth->month)->sum('berat_kg');
        $trenSampah = $sampahBulanLalu > 0 ? (($sampahBulanIni - $sampahBulanLalu) / $sampahBulanLalu) * 100 : 100;

        $totalCO2 = ItemSetor::sum('co2');
        $co2BulanIni = ItemSetor::whereMonth('created_at', $now->month)->sum('co2');
        $co2BulanLalu = ItemSetor::whereMonth('created_at', $lastMonth->month)->sum('co2');
        $trenCO2 = $co2BulanLalu > 0 ? (($co2BulanIni - $co2BulanLalu) / $co2BulanLalu) * 100 : 100;

        // Akumulasi Ekonomi (Semua Pemasukan Kas dari Penjualan)
        $nilaiEkonomi = MutasiKas::where('tipe', 'pemasukan')->sum('nominal');

        $totalNasabah = Nasabah::count();
        $nasabahAktif = TransaksiSetor::select('nasabah_id')->distinct()->count();

        // 2. Target & Dampak CO2
        // Target dihitung otomatis: rata-rata 3 bulan sebelumnya × faktor pertumbuhan
        $faktorPertumbuhan = PengaturanSistem::ambil('faktor_pertumbuhan_target', 1.1);
        $rata3Bulan = 0;
        $countBulan = 0;
        for ($i = 1; $i <= 3; $i++) {
            $bulanTarget = $now->copy()->subMonths($i);
            $nilaiBulan = ItemSetor::whereYear('created_at', $bulanTarget->year)
                ->whereMonth('created_at', $bulanTarget->month)
                ->sum('co2');
            if ($nilaiBulan > 0) {
                $rata3Bulan += $nilaiBulan;
                $countBulan++;
            }
        }
        $targetCO2 = $countBulan > 0 ? round(($rata3Bulan / $countBulan) * $faktorPertumbuhan, 0) : 100;
        $persenTarget = $targetCO2 > 0 ? min(100, round(($co2BulanIni / $targetCO2) * 100)) : 0;

        // Ekuivalen dari total CO2 — baca dari pengaturan_sistem
        $ekuivalenPohon = round($totalCO2 / PengaturanSistem::ambil('co2_per_pohon', 11));
        $ekuivalenBensin = round($totalCO2 / PengaturanSistem::ambil('co2_per_km_mobil', 0.167));
        $ekuivalenListrik = round($totalCO2 / PengaturanSistem::ambil('co2_per_bulan_listrik', 141), 1);

        // 3. Distribusi RT (Memperbaiki nama tabel menjadi singular: nasabah, transaksi_setor, item_setor)
        $reduksiPerRT = DB::table('nasabah')
            ->join('transaksi_setor', 'nasabah.id', '=', 'transaksi_setor.nasabah_id')
            ->join('item_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
            ->select('nasabah.rt', DB::raw('SUM(item_setor.co2) as total_co2'))
            ->groupBy('nasabah.rt')
            ->orderByDesc('total_co2')
            ->get();
            
        // Cari nilai tertinggi untuk persentase bar RT
        $maxRT = $reduksiPerRT->max('total_co2') ?: 1;

        // 4. Top Kontributor Bulan Ini
        $topKontributor = DB::table('nasabah')
            ->join('transaksi_setor', 'nasabah.id', '=', 'transaksi_setor.nasabah_id')
            ->join('item_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
            ->select('nasabah.nama', 'nasabah.rt', 'nasabah.rw', DB::raw('SUM(item_setor.co2) as total_co2'))
            ->whereMonth('transaksi_setor.tanggal', $now->month)
            ->groupBy('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw')
            ->orderByDesc('total_co2')
            ->limit(5)
            ->get();

        // 5. Tren Reduksi CO2 per bulan (6 bulan terakhir)
        $co2BulananRaw = DB::table('item_setor')
            ->select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(co2) as total_co2')
            )
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', '>=', max(1, $now->month - 5))
            ->whereMonth('created_at', '<=', $now->month)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $co2Bulanan = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = $now->copy()->subMonths($i);
            $idx = intval($bulan->month);
            $co2Bulanan[] = [
                'label' => $namaBulan[$idx - 1],
                'total' => isset($co2BulananRaw[$idx]) ? round($co2BulananRaw[$idx]->total_co2, 1) : 0,
            ];
        }
        $maxCO2Bulanan = max(array_column($co2Bulanan, 'total')) ?: 1;

        // 6. Kontribusi CO2 per Kategori Sampah
        $co2PerKategori = DB::table('item_setor')
            ->join('kategori_sampah', 'item_setor.kategori_id', '=', 'kategori_sampah.id')
            ->select('kategori_sampah.nama', DB::raw('SUM(item_setor.co2) as total_co2'))
            ->groupBy('kategori_sampah.id', 'kategori_sampah.nama')
            ->orderByDesc('total_co2')
            ->get();

        $totalCO2Kategori = $co2PerKategori->sum('total_co2') ?: 1;
        $paletteKategori = ['#3B6D11', '#185FA5', '#BA7517', '#B4B2A9', '#6B4E9E', '#C73E3A'];

        // Baca pengaturan untuk label ekuivalen di view
        $setPohon = PengaturanSistem::where('kunci', 'co2_per_pohon')->first();
        $setMobil = PengaturanSistem::where('kunci', 'co2_per_km_mobil')->first();
        $setListrik = PengaturanSistem::where('kunci', 'co2_per_bulan_listrik')->first();

        return view('dashboard.index', compact(
            'totalSampah', 'trenSampah',
            'totalCO2', 'co2BulanIni', 'trenCO2',
            'nilaiEkonomi',
            'totalNasabah', 'nasabahAktif',
            'targetCO2', 'persenTarget',
            'ekuivalenPohon', 'ekuivalenBensin', 'ekuivalenListrik',
            'reduksiPerRT', 'maxRT', 'topKontributor',
            'co2Bulanan', 'maxCO2Bulanan',
            'co2PerKategori', 'totalCO2Kategori', 'paletteKategori',
            'setPohon', 'setMobil', 'setListrik'
        ));
    }
}