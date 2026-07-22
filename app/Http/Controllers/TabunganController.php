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
        
        $bulan = (int) $request->query('bulan', 0);
        $tahun = (int) $request->query('tahun', 0);

        $query = MutasiTabungan::with('transaksiSetor.items.kategori')
            ->where('nasabah_id', $id);
        if ($bulan > 0 && $bulan <= 12) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun > 0) {
            $query->whereYear('tanggal', $tahun);
        }
        $mutasi = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        // Tahun tersedia untuk filter
        $tahunTersedia = MutasiTabungan::where('nasabah_id', $id)
            ->selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('tabungan.show', compact('nasabah', 'mutasi', 'bulan', 'tahun', 'tahunTersedia'))
            ->with('isNasabahView', !auth()->check());
    }

    // Memproses penarikan saldo
    public function tarik(Request $request, $id)
    {
        // Bersihkan format ribuan dari input-rupiah
        if ($request->has('jumlah')) {
            $request->merge(['jumlah' => (int) str_replace('.', '', $request->jumlah)]);
        }

        $request->validate([
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:100',
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

            // === LOGIKA WHATSAPP NOTA TARIK TUNAI ===
            $saldo_terbaru = $nasabah->tabungan->fresh()->saldo_saat_ini;
            $no_hp = $nasabah->no_hp ?? '';

            if (str_starts_with($no_hp, '0')) {
                $no_hp = '62' . substr($no_hp, 1);
            }

            $pesanWa = "*[Invoice Tarik Tunai Bank Sampah]*\n\n";
            $pesanWa .= "Halo *{$nasabah->nama}*,\n";
            $pesanWa .= "Penarikan tunai telah berhasil diproses. Berikut rinciannya:\n\n";
            $pesanWa .= "📅 Tanggal : {$request->tanggal}\n";
            $pesanWa .= "💸 Jumlah Tarik : Rp " . number_format($request->jumlah, 0, ',', '.') . "\n";
            if ($request->keterangan) {
                $pesanWa .= "📝 Keterangan : {$request->keterangan}\n";
            }
            $pesanWa .= "💰 Sisa Saldo : Rp " . number_format($saldo_terbaru, 0, ',', '.') . "\n\n";
            $pesanWa .= "Terima kasih telah menggunakan layanan Bank Sampah. ♻️ \n\n";
            $pesanWa .= "Terimakasih.\nPengurus Bank Sampah Ngudia Wilujeng";

            $wa_url = "https://wa.me/{$no_hp}?text=" . urlencode($pesanWa);
            // ==========================================

            return back()
                ->with('success', 'Penarikan dana sebesar Rp ' . number_format($request->jumlah, 0, ',', '.') . ' berhasil dicatat.')
                ->with('wa_url', $wa_url)
                ->with('wa_nasabah', $nasabah->nama);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    // FUNGSI BARU UNTUK EXPORT PDF
    public function exportPdf(Request $request, $id)
    {
        $nasabah = Nasabah::with('tabungan')->findOrFail($id);
        
        $bulan = (int) $request->query('bulan', 0);
        $tahun = (int) $request->query('tahun', 0);

        $query = MutasiTabungan::with('transaksiSetor.items.kategori')
            ->where('nasabah_id', $id);
        if ($bulan > 0 && $bulan <= 12) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun > 0) {
            $query->whereYear('tanggal', $tahun);
        }
        $mutasi = $query->orderBy('tanggal', 'asc')->get();

        $namaBulan = [1 => 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $labelBulan = ($bulan > 0) ? '_' . $namaBulan[$bulan] : '';
        $labelTahun = ($tahun > 0) ? '_' . $tahun : '';

        $pdf = Pdf::loadView('tabungan.pdf', compact('nasabah', 'mutasi', 'bulan'));
        
        // Atur ukuran kertas ke A4 Portrait
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Buku_Tabungan_' . str_replace(' ', '_', $nasabah->nama) . $labelBulan . $labelTahun . '.pdf');
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