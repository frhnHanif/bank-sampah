<?php

namespace App\Http\Controllers;

use App\Models\AlokasiPenjualan;
use App\Models\ItemJual;
use App\Models\ItemSetor;
use App\Models\MutasiKas;
use App\Models\Nasabah;
use App\Models\PengaturanSistem;
use App\Models\TransaksiSetor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $lastMonth = $now->copy()->subMonth();

        $totalSampah = (float) ItemSetor::sum('berat_kg');
        $totalTersalurkan = (float) ItemJual::sum('berat_kg');
        $pendingWeight = (float) ItemSetor::where('is_legacy', false)
            ->selectRaw('COALESCE(SUM(berat_kg - berat_teralokasi_kg), 0) AS total')->value('total');
        $unclassifiedWeight = (float) AlokasiPenjualan::where('co2_status', 'PENDING')->sum('berat_kg');

        $sampahBulanIni = $this->depositWeightForMonth($now);
        $sampahBulanLalu = $this->depositWeightForMonth($lastMonth);
        $trenSampah = $sampahBulanLalu > 0 ? (($sampahBulanIni - $sampahBulanLalu) / $sampahBulanLalu) * 100 : 0;

        // Legacy memakai metode lama (setoran); flow baru hanya memakai allocation
        // yang sudah terealisasi saat sale. Keduanya tidak pernah dijumlah dua kali.
        $legacyCO2 = (float) ItemSetor::whereHas('transaksi', fn ($q) => $q->where('flow_version', 1))->sum('co2');
        $newCO2 = (float) AlokasiPenjualan::where('co2_status', 'REALIZED')->sum('co2_terealisasi');
        $totalCO2 = $legacyCO2 + $newCO2;
        $co2BulanIni = $this->co2ForMonth($now);
        $co2BulanLalu = $this->co2ForMonth($lastMonth);
        $trenCO2 = $co2BulanLalu > 0 ? (($co2BulanIni - $co2BulanLalu) / $co2BulanLalu) * 100 : 0;

        $nilaiEkonomi = (float) MutasiKas::where('tipe', 'pemasukan')->where('kategori', 'Penjualan')->sum('nominal');
        $totalNasabah = Nasabah::count();
        $nasabahAktif = TransaksiSetor::distinct('nasabah_id')->count('nasabah_id');

        $monthly = collect(range(1, 3))->map(fn ($offset) => $this->co2ForMonth($now->copy()->subMonths($offset)))->filter();
        $targetCO2 = $monthly->isNotEmpty()
            ? round($monthly->avg() * PengaturanSistem::ambil('faktor_pertumbuhan_target', 1.1))
            : 100;
        $persenTarget = $targetCO2 > 0 ? min(100, round(($co2BulanIni / $targetCO2) * 100)) : 0;

        $ekuivalenPohon = round($totalCO2 / max(PengaturanSistem::ambil('co2_per_pohon', 11), 0.000001));
        $ekuivalenBensin = round($totalCO2 / max(PengaturanSistem::ambil('co2_per_km_mobil', 0.167), 0.000001));
        $ekuivalenListrik = round($totalCO2 / max(PengaturanSistem::ambil('co2_per_bulan_listrik', 141), 0.000001), 1);

        $contributors = $this->environmentalContributors();
        $reduksiPerRT = $contributors->groupBy('rt')->map(fn ($rows, $rt) => (object) [
            'rt' => $rt, 'total_co2' => $rows->sum('total_co2'),
        ])->sortByDesc('total_co2')->values();
        $maxRT = $reduksiPerRT->max('total_co2') ?: 1;
        $topKontributor = $contributors->sortByDesc('total_co2')->take(5)->values();

        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $co2Bulanan = collect(range(5, 0))->map(function ($offset) use ($now, $namaBulan) {
            $month = $now->copy()->subMonths($offset);

            return ['label' => $namaBulan[$month->month - 1], 'total' => round($this->co2ForMonth($month), 1)];
        })->all();
        $maxCO2Bulanan = max(array_column($co2Bulanan, 'total')) ?: 1;

        $co2PerKategori = $this->environmentalByCategory();
        $totalCO2Kategori = $co2PerKategori->sum('total_co2') ?: 1;
        $paletteKategori = ['#3B6D11', '#185FA5', '#BA7517', '#B4B2A9', '#6B4E9E', '#C73E3A'];
        $setPohon = PengaturanSistem::where('kunci', 'co2_per_pohon')->first();
        $setMobil = PengaturanSistem::where('kunci', 'co2_per_km_mobil')->first();
        $setListrik = PengaturanSistem::where('kunci', 'co2_per_bulan_listrik')->first();

        return view('dashboard.index', compact(
            'totalSampah', 'totalTersalurkan', 'pendingWeight', 'unclassifiedWeight', 'trenSampah',
            'totalCO2', 'co2BulanIni', 'trenCO2', 'nilaiEkonomi', 'totalNasabah', 'nasabahAktif',
            'targetCO2', 'persenTarget', 'ekuivalenPohon', 'ekuivalenBensin', 'ekuivalenListrik',
            'reduksiPerRT', 'maxRT', 'topKontributor', 'co2Bulanan', 'maxCO2Bulanan',
            'co2PerKategori', 'totalCO2Kategori', 'paletteKategori', 'setPohon', 'setMobil', 'setListrik'
        ));
    }

    private function depositWeightForMonth(Carbon $month): float
    {
        return (float) ItemSetor::whereHas('transaksi', fn ($q) => $q
            ->whereYear('tanggal', $month->year)->whereMonth('tanggal', $month->month))->sum('berat_kg');
    }

    private function co2ForMonth(Carbon $month): float
    {
        $legacy = (float) ItemSetor::whereHas('transaksi', fn ($q) => $q->where('flow_version', 1)
            ->whereYear('tanggal', $month->year)->whereMonth('tanggal', $month->month))->sum('co2');
        $realized = (float) AlokasiPenjualan::where('co2_status', 'REALIZED')
            ->whereHas('itemJual.transaksi', fn ($q) => $q
                ->whereYear('tanggal', $month->year)->whereMonth('tanggal', $month->month))
            ->sum('co2_terealisasi');

        return $legacy + $realized;
    }

    private function environmentalContributors()
    {
        $legacy = DB::table('item_setor')->join('transaksi_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
            ->join('nasabah', 'nasabah.id', '=', 'transaksi_setor.nasabah_id')
            ->where('transaksi_setor.flow_version', 1)
            ->select('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw', DB::raw('SUM(item_setor.co2) AS total_co2'))
            ->groupBy('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw')->get()->keyBy('id');
        $new = DB::table('alokasi_penjualan')->join('item_setor', 'item_setor.id', '=', 'alokasi_penjualan.item_setor_id')
            ->join('transaksi_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
            ->join('nasabah', 'nasabah.id', '=', 'transaksi_setor.nasabah_id')->where('alokasi_penjualan.co2_status', 'REALIZED')
            ->select('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw', DB::raw('SUM(alokasi_penjualan.co2_terealisasi) AS total_co2'))
            ->groupBy('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw')->get();
        foreach ($new as $row) {
            if ($legacy->has($row->id)) {
                $legacy[$row->id]->total_co2 += $row->total_co2;
            } else {
                $legacy[$row->id] = $row;
            }
        }

        return $legacy->values();
    }

    private function environmentalByCategory()
    {
        $legacy = DB::table('item_setor')->join('transaksi_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
            ->join('kategori_sampah', 'kategori_sampah.id', '=', 'item_setor.kategori_id')->where('transaksi_setor.flow_version', 1)
            ->select('kategori_sampah.id', 'kategori_sampah.nama', DB::raw('SUM(item_setor.co2) AS total_co2'))
            ->groupBy('kategori_sampah.id', 'kategori_sampah.nama')->get()->keyBy('id');
        $new = DB::table('alokasi_penjualan')->join('item_jual', 'item_jual.id', '=', 'alokasi_penjualan.item_jual_id')
            ->join('kategori_sampah', 'kategori_sampah.id', '=', 'item_jual.kategori_id')->where('alokasi_penjualan.co2_status', 'REALIZED')
            ->select('kategori_sampah.id', 'kategori_sampah.nama', DB::raw('SUM(alokasi_penjualan.co2_terealisasi) AS total_co2'))
            ->groupBy('kategori_sampah.id', 'kategori_sampah.nama')->get();
        foreach ($new as $row) {
            if ($legacy->has($row->id)) {
                $legacy[$row->id]->total_co2 += $row->total_co2;
            } else {
                $legacy[$row->id] = $row;
            }
        }

        return $legacy->values()->sortByDesc('total_co2')->values();
    }
}
