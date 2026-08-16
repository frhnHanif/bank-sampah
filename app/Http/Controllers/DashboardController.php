<?php

namespace App\Http\Controllers;

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
        $pendingWeight = (float) ItemSetor::selectRaw('COALESCE(SUM(berat_kg - berat_teralokasi_kg), 0) AS total')->value('total');
        $unclassifiedWeight = $this->depositWeightWithoutEmissionFactor();

        $sampahBulanIni = $this->depositWeightForMonth($now);
        $sampahBulanLalu = $this->depositWeightForMonth($lastMonth);
        $trenSampah = $sampahBulanLalu > 0 ? (($sampahBulanIni - $sampahBulanLalu) / $sampahBulanLalu) * 100 : 0;

        $totalCO2 = $this->depositEmissionQuery()->sum(DB::raw('item_setor.berat_kg * kelompok_material.faktor_emisi_kgco2e_per_kg'));
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
        $topKontributor = $this->environmentalContributors($now)->take(5)->values();

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
        return (float) $this->depositEmissionQuery()
            ->whereYear('transaksi_setor.tanggal', $month->year)
            ->whereMonth('transaksi_setor.tanggal', $month->month)
            ->sum(DB::raw('item_setor.berat_kg * kelompok_material.faktor_emisi_kgco2e_per_kg'));
    }

    private function depositEmissionQuery()
    {
        return DB::table('item_setor')
            ->join('transaksi_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
            ->join('jenis_sampah', 'jenis_sampah.id', '=', 'item_setor.jenis_sampah_id')
            ->join('kelompok_material', 'kelompok_material.id', '=', 'jenis_sampah.kelompok_material_id')
            ->whereNotNull('kelompok_material.faktor_emisi_kgco2e_per_kg');
    }

    private function depositWeightWithoutEmissionFactor(): float
    {
        return (float) DB::table('item_setor')
            ->join('jenis_sampah', 'jenis_sampah.id', '=', 'item_setor.jenis_sampah_id')
            ->join('kelompok_material', 'kelompok_material.id', '=', 'jenis_sampah.kelompok_material_id')
            ->whereNull('kelompok_material.faktor_emisi_kgco2e_per_kg')
            ->sum('item_setor.berat_kg');
    }

    private function environmentalContributors(?Carbon $month = null)
    {
        $query = $this->depositEmissionQuery()
            ->join('nasabah', 'nasabah.id', '=', 'transaksi_setor.nasabah_id');

        if ($month) {
            $query->whereYear('transaksi_setor.tanggal', $month->year)
                ->whereMonth('transaksi_setor.tanggal', $month->month);
        }

        return $query
            ->select('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw', DB::raw('SUM(item_setor.berat_kg * kelompok_material.faktor_emisi_kgco2e_per_kg) AS total_co2'))
            ->groupBy('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw')
            ->orderByDesc('total_co2')
            ->get();
    }

    private function environmentalByCategory()
    {
        return $this->depositEmissionQuery()
            ->select('jenis_sampah.id', 'jenis_sampah.nama', DB::raw('SUM(item_setor.berat_kg * kelompok_material.faktor_emisi_kgco2e_per_kg) AS total_co2'))
            ->groupBy('jenis_sampah.id', 'jenis_sampah.nama')->orderByDesc('total_co2')->get();
    }
}
