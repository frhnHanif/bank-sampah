<?php

namespace App\Http\Controllers;

use App\Models\MutasiKas;
use App\Models\Tabungan;
use App\Models\ItemSetor;
use App\Models\ItemJual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        // Filter bulan (0=semua, 1-12) & tahun (0=semua)
        $bulan = (int) $request->query('bulan', 0);
        $tahun = (int) $request->query('tahun', 0);

        // 1. Hitung Saldo Kas Riil (Pemasukan - Pengeluaran)
        $totalPemasukan = MutasiKas::where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = MutasiKas::where('tipe', 'pengeluaran')->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        // 2. Hitung Statistik untuk Analisis Keuntungan Bisnis
        $totalPenjualanPengepul = MutasiKas::where('kategori', 'Penjualan')->sum('nominal');

        // Total saldo aktif seluruh nasabah saat ini (kewajiban riil ke warga)
        $totalRekeningWarga = Tabungan::sum('saldo_saat_ini');

        // --- COGS: Harga Pokok barang yang SUDAH TERJUAL (bukan semua setoran) ---
        // Rata-rata harga beli per kg untuk setiap kategori
        $avgBeliPerKategori = ItemSetor::select('kategori_id',
                DB::raw('SUM(nilai) / NULLIF(SUM(berat_kg), 0) as avg_harga_per_kg'))
            ->groupBy('kategori_id')
            ->pluck('avg_harga_per_kg', 'kategori_id');

        // Hitung COGS = Σ (berat_terjual × rata2_harga_beli_kategori)
        $cogsTerjual = 0;
        $itemsTerjual = ItemJual::all();
        foreach ($itemsTerjual as $item) {
            $avgHarga = $avgBeliPerKategori[$item->kategori_id] ?? 0;
            $cogsTerjual += $item->berat_kg * $avgHarga;
        }

        // Total biaya operasional pengelola
        $totalOperasional = MutasiKas::where('kategori', 'Operasional')->sum('nominal');

        // Rumus Laba Bersih = Uang dari Pengepul - Beban Pokok Barang Terjual - Operasional
        $estimasiKeuntungan = $totalPenjualanPengepul - $cogsTerjual - $totalOperasional;

        // 3. Ambil semua riwayat transaksi kas induk (dengan filter bulan & tahun)
        $queryMutasi = MutasiKas::orderBy('tanggal', 'desc')->orderBy('id', 'desc');
        if ($bulan > 0 && $bulan <= 12) {
            $queryMutasi->whereMonth('tanggal', $bulan);
        }
        if ($tahun > 0) {
            $queryMutasi->whereYear('tanggal', $tahun);
        }
        $mutasiKas = $queryMutasi->get();

        // Ambil daftar tahun yang tersedia
        $tahunTersedia = MutasiKas::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('keuangan.index', compact(
            'saldoKas',
            'totalPenjualanPengepul',
            'totalRekeningWarga',
            'cogsTerjual',
            'estimasiKeuntungan',
            'mutasiKas',
            'bulan',
            'tahun',
            'tahunTersedia'
        ));
    }

    // Mencatat biaya operasional pengelola
    public function storeOperasional(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'nominal'    => 'required|numeric|min:100',
            'keterangan' => 'required|string|max:255',
        ]);

        MutasiKas::create([
            'tanggal'    => $request->tanggal,
            'tipe'       => 'pengeluaran',
            'kategori'   => 'Operasional',
            'nominal'    => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('keuangan.index')->with('success', 'Biaya operasional berhasil dicatat ke Kas Induk!');
    }


    // Export laporan keuangan ke PDF
    public function exportPdf(Request $request)
    {
        $bulan = (int) $request->query('bulan', 0);
        $tahun = (int) $request->query('tahun', 0);

        $queryMutasi = MutasiKas::orderBy('tanggal', 'asc')->orderBy('id', 'asc');
        if ($bulan > 0 && $bulan <= 12) {
            $queryMutasi->whereMonth('tanggal', $bulan);
        }
        if ($tahun > 0) {
            $queryMutasi->whereYear('tanggal', $tahun);
        }
        $mutasiKas = $queryMutasi->get();

        $totalPemasukan = MutasiKas::where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = MutasiKas::where('tipe', 'pengeluaran')->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $namaBulan = [1 => 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $labelBulan = ($bulan > 0) ? '_' . $namaBulan[$bulan] : '';
        $labelTahun = ($tahun > 0) ? '_' . $tahun : '';

        $pdf = Pdf::loadView('keuangan.pdf', compact('mutasiKas', 'saldoKas', 'totalPemasukan', 'totalPengeluaran', 'bulan'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Kas_Induk_' . date('Y-m-d') . $labelBulan . $labelTahun . '.pdf');
    }
}