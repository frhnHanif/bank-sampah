<?php

namespace App\Http\Controllers;

use App\Models\TransaksiSetor;
use App\Models\ItemSetor;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Berat Terkumpul (Keseluruhan)
        $totalBerat = ItemSetor::sum('berat_kg');

        // 2. Estimasi CO2 ter-offset
        $totalCO2 = TransaksiSetor::sum('total_co2');

        // 3. Total Nilai Ekonomi (Total uang dari sampah)
        $totalNilaiEkonomi = TransaksiSetor::sum('total_nilai');

        // 4. Total Nasabah
        $totalNasabah = Nasabah::count();

        // 5. Rekap Kontribusi per Nasabah (Top 5 Nasabah Penyumbang Terbesar)
        // Kita menggunakan Query Builder untuk men-join nasabah, transaksi, dan menghitung total berat per nasabah
        $topNasabah = Nasabah::select('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw')
            ->join('transaksi_setor', 'nasabah.id', '=', 'transaksi_setor.nasabah_id')
            ->join('item_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
            ->selectRaw('SUM(item_setor.berat_kg) as total_berat_disetor')
            ->selectRaw('SUM(item_setor.nilai) as total_nilai_didapat')
            ->groupBy('nasabah.id', 'nasabah.nama', 'nasabah.rt', 'nasabah.rw')
            ->orderByDesc('total_berat_disetor')
            ->limit(5)
            ->get();

        // 6. Transaksi Terbaru (Hanya untuk overview di tabel samping)
        $transaksiTerbaru = TransaksiSetor::with('nasabah')
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalBerat', 'totalCO2', 'totalNilaiEkonomi', 'totalNasabah', 'topNasabah', 'transaksiTerbaru'
        ));
    }
}