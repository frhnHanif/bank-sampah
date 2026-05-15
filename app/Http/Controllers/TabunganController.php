<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\MutasiTabungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TabunganController extends Controller
{
    // Menampilkan halaman buku tabungan nasabah
    public function show($id)
    {
        $nasabah = Nasabah::with('tabungan')->findOrFail($id);
        
        // Ambil riwayat mutasi, urutkan dari yang terbaru
        $mutasi = MutasiTabungan::where('nasabah_id', $id)
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();

        return view('tabungan.show', compact('nasabah', 'mutasi'));
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

            DB::commit();
            return back()->with('success', 'Penarikan dana sebesar Rp ' . number_format($request->jumlah, 0, ',', '.') . ' berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    // FUNGSI BARU UNTUK EXPORT PDF
    public function exportPdf($id)
    {
        $nasabah = Nasabah::with('tabungan')->findOrFail($id);
        
        $mutasi = MutasiTabungan::where('nasabah_id', $id)
                    ->orderBy('tanggal', 'asc') // Urutkan dari yang terlama untuk di buku cetak
                    ->get();

        // Load view HTML khusus PDF
        $pdf = Pdf::loadView('tabungan.pdf', compact('nasabah', 'mutasi'));
        
        // Atur ukuran kertas ke A4 Portrait
        $pdf->setPaper('A4', 'portrait');

        // Download otomatis dengan nama file dinamis
        return $pdf->stream('Buku_Tabungan_' . str_replace(' ', '_', $nasabah->nama) . '.pdf');
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