<?php

namespace App\Http\Controllers;

use App\Models\AlokasiPenjualan;
use App\Models\ItemJual;
use App\Models\ItemSetor;
use App\Models\MutasiKas;
use App\Models\Tabungan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $metrics = $this->metrics();
        [$mutasiKas, $bulan, $tahun, $tahunTersedia] = $this->filteredCash($request, 'desc');

        return view('keuangan.index', array_merge($metrics, compact('mutasiKas', 'bulan', 'tahun', 'tahunTersedia')));
    }

    public function storeOperasional(Request $request)
    {
        if ($request->has('nominal')) {
            $request->merge(['nominal' => (int) str_replace('.', '', $request->nominal)]);
        }
        $data = $request->validate([
            'tanggal' => ['required', 'date'], 'nominal' => ['required', 'integer', 'min:100'],
            'keterangan' => ['required', 'string', 'max:255'],
        ]);
        MutasiKas::create($data + ['tipe' => 'pengeluaran', 'kategori' => 'Operasional']);

        return back()->with('success', 'Biaya operasional berhasil dicatat.');
    }

    public function exportPdf(Request $request)
    {
        $metrics = $this->metrics();
        [$mutasiKas, $bulan, $tahun] = $this->filteredCash($request, 'asc');
        $pdf = Pdf::loadView('keuangan.pdf', array_merge($metrics, compact('mutasiKas', 'bulan', 'tahun')))->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Keuangan_'.date('Y-m-d').'.pdf');
    }

    private function metrics(): array
    {
        $totalPemasukan = (float) MutasiKas::where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = (float) MutasiKas::where('tipe', 'pengeluaran')->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;
        $totalPenjualanPengepul = (float) MutasiKas::where('tipe', 'pemasukan')->where('kategori', 'Penjualan')->sum('nominal');
        $totalRekeningWarga = (float) Tabungan::sum('saldo_saat_ini');

        $costFlowBaru = (float) AlokasiPenjualan::sum('cost_basis');
        $avgLegacy = ItemSetor::where('is_legacy', true)->select('kategori_id', DB::raw('SUM(nilai) / NULLIF(SUM(berat_kg), 0) AS avg_cost'))
            ->groupBy('kategori_id')->pluck('avg_cost', 'kategori_id');
        $costLegacySales = ItemJual::whereHas('transaksi', fn ($q) => $q->where('flow_version', 1))->get()
            ->sum(fn ($item) => (float) $item->berat_kg * (float) ($avgLegacy[$item->kategori_id] ?? 0));
        $cogsTerjual = round($costFlowBaru + $costLegacySales, 2);
        $totalMarginKotor = round($totalPenjualanPengepul - $cogsTerjual, 2);
        $totalOperasional = (float) MutasiKas::where('tipe', 'pengeluaran')->where('kategori', 'Operasional')->sum('nominal');
        $labaSetelahOperasional = round($totalMarginKotor - $totalOperasional, 2);
        $kasSetelahKewajiban = round($saldoKas - $totalRekeningWarga, 2);
        $estimasiKeuntungan = $labaSetelahOperasional;

        return compact('totalPemasukan', 'totalPengeluaran', 'saldoKas', 'totalPenjualanPengepul',
            'totalRekeningWarga', 'cogsTerjual', 'totalMarginKotor', 'totalOperasional',
            'labaSetelahOperasional', 'kasSetelahKewajiban', 'estimasiKeuntungan');
    }

    private function filteredCash(Request $request, string $direction): array
    {
        $bulan = (int) $request->query('bulan', 0);
        $tahun = (int) $request->query('tahun', 0);
        $query = MutasiKas::orderBy('tanggal', $direction)->orderBy('id', $direction);
        if ($bulan >= 1 && $bulan <= 12) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun > 0) {
            $query->whereYear('tanggal', $tahun);
        }
        // Ekstraksi tahun dilakukan di PHP agar halaman tetap portabel antara
        // MySQL (produksi) dan SQLite (test/audit lokal).
        $years = MutasiKas::query()
            ->orderByDesc('tanggal')
            ->pluck('tanggal')
            ->map(fn ($tanggal) => (int) Carbon::parse($tanggal)->year)
            ->unique()
            ->values();

        return [$query->get(), $bulan, $tahun, $years];
    }
}
