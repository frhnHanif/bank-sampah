<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\MutasiTabungan;
use App\Models\MutasiKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TabunganController extends Controller
{
    // Menampilkan halaman buku tabungan nasabah
    public function show(Request $request, $id)
    {
        $nasabah = Nasabah::with('tabungan')->findOrFail($id);
        
        // Filter bulan (1-12), default 0 = semua
        $bulan = $request->query('bulan', 0);

        // Ambil riwayat mutasi, urutkan dari yang terbaru
        $query = MutasiTabungan::with('transaksiSetor.items.kategori')
            ->where('nasabah_id', $id);
        if ($bulan > 0 && $bulan <= 12) {
            $query->whereMonth('tanggal', $bulan);
        }
        $mutasi = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        return view('tabungan.show', compact('nasabah', 'mutasi', 'bulan'));
    }

    // Memproses penarikan saldo
    public function tarik(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:100',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $nasabah = Nasabah::with('tabungan')->findOrFail($id);
        $saldo_saat_ini = $nasabah->tabungan ? $nasabah->tabungan->saldo_saat_ini : 0;

        // Validasi jika saldo tidak cukup
        if ($request->jumlah > $saldo_saat_ini) {
            return back()->withErrors(['jumlah' => 'Gagal! Saldo nasabah tidak mencukupi. Saldo saat ini: Rp ' . number_format($saldo_saat_ini, 0, ',', '.')]);
        }

        DB::beginTransaction();
        try {
            // 1. Kurangi saldo di tabel tabungan
            $nasabah->tabungan->decrement('saldo_saat_ini', $request->jumlah);

            // 2. Catat mutasi keluar (Debit)
            MutasiTabungan::create([
                'nasabah_id' => $id,
                'tanggal' => $request->tanggal,
                'jenis' => 'debit',
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan ?? 'Penarikan Saldo',
            ]);

            // Catat ke Buku Kas Induk sebagai Pengeluaran
            MutasiKas::create([
                'tanggal'    => $request->tanggal,
                'tipe'       => 'pengeluaran',
                'kategori'   => 'Tarik Tunai Nasabah',
                'nominal'    => $request->jumlah,
                'keterangan' => 'Penarikan tunai oleh nasabah: ' . $nasabah->nama,
            ]);

            DB::commit();
            return back()->with('success', 'Penarikan dana sebesar Rp ' . number_format($request->jumlah, 0, ',', '.') . ' berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    // FUNGSI BARU UNTUK EXPORT PDF
    public function exportPdf(Request $request, $id)
    {
        $nasabah = Nasabah::with('tabungan')->findOrFail($id);
        
        // Filter bulan (1-12), default 0 = semua
        $bulan = $request->query('bulan', 0);

        $query = MutasiTabungan::with('transaksiSetor.items.kategori')
            ->where('nasabah_id', $id);
        if ($bulan > 0 && $bulan <= 12) {
            $query->whereMonth('tanggal', $bulan);
        }
        $mutasi = $query->orderBy('tanggal', 'asc')->get();

        // Nama bulan untuk judul PDF
        $namaBulan = [1 => 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $labelBulan = ($bulan > 0) ? '_' . $namaBulan[$bulan] : '';

        // Load view HTML khusus PDF
        $pdf = Pdf::loadView('tabungan.pdf', compact('nasabah', 'mutasi', 'bulan'));
        
        // Atur ukuran kertas ke A4 Portrait
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Buku_Tabungan_' . str_replace(' ', '_', $nasabah->nama) . $labelBulan . '.pdf');
    }

    public function generateIdCard($id)
    {
        $nasabah = Nasabah::findOrFail($id);
        
        // Generate QR Code dalam bentuk Base64 agar bisa dirender DomPDF
        $qrcode = base64_encode(QrCode::format('svg')->size(150)->errorCorrection('H')->generate($nasabah->kode));

        $pdf = Pdf::loadView('tabungan.id_card', compact('nasabah', 'qrcode'));
        
        // Atur ukuran kertas ke A6 Portrait (105mm x 148mm)
        $pdf->setPaper([0, 0, 297.64, 419.53], 'portrait'); 

        return $pdf->stream('ID_Card_' . $nasabah->kode . '.pdf');
    }
}