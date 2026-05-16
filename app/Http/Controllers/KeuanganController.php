<?php

namespace App\Http\Controllers;

use App\Models\MutasiKas;
use App\Models\TransaksiSetor;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KeuanganController extends Controller
{
    public function index()
    {
        // 1. Hitung Saldo Kas Riil (Pemasukan - Pengeluaran)
        $totalPemasukan = MutasiKas::where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = MutasiKas::where('tipe', 'pengeluaran')->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        // 2. Hitung Statistik untuk Analisis Keuntungan Bisnis
        $totalPenjualanPengepul = MutasiKas::where('kategori', 'Penjualan')->sum('nominal');
        
        // Total nilai sampah yang dibeli dari warga (Beban Pokok)
        $totalBeliSampah = TransaksiSetor::sum('total_nilai');

        // Total biaya operasional pengelola
        $totalOperasional = MutasiKas::where('kategori', 'Operasional')->sum('nominal');

        // Rumus Laba Bersih = Uang dari Pengepul - Hak Uang Warga - Operasional
        $estimasiKeuntungan = $totalPenjualanPengepul - $totalBeliSampah - $totalOperasional;

        // 3. Ambil semua riwayat transaksi kas induk
        $mutasiKas = MutasiKas::orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        return view('keuangan.index', compact(
            'saldoKas',
            'totalPenjualanPengepul',
            'totalBeliSampah',
            'estimasiKeuntungan',
            'mutasiKas'
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
    public function exportPdf()
    {
        // Ambil data dari awal sampai akhir (Ascending untuk cetakan buku)
        $mutasiKas = MutasiKas::orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get();

        $totalPemasukan = MutasiKas::where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = MutasiKas::where('tipe', 'pengeluaran')->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $pdf = Pdf::loadView('keuangan.pdf', compact('mutasiKas', 'saldoKas', 'totalPemasukan', 'totalPengeluaran'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Kas_Induk_' . date('Y-m-d') . '.pdf');
    }
}