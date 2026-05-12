<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\KategoriSampah;
use App\Models\TransaksiSetor;
use App\Models\ItemSetor;
use App\Models\Stok;
use App\Models\Tabungan; // Tambahan baru
use App\Models\MutasiTabungan; // Tambahan baru
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiSetorController extends Controller
{
    public function index()
    {
        return redirect()->route('setor.create'); 
    }

    public function create()
    {
        $nasabah = Nasabah::with('tabungan')->orderBy('nama')->get();
        $kategori = KategoriSampah::orderBy('nama')->get();
        
        return view('setor.create', compact('nasabah', 'kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:nasabah,id',
            'tanggal'    => 'required|date',
            'cart_data'  => 'required|string', 
        ]);

        $cart = json_decode($request->cart_data, true);
        
        if (empty($cart)) {
            return back()->withErrors(['cart_data' => 'Keranjang setor masih kosong!']);
        }

        DB::beginTransaction();
        try {
            $total_nilai = 0;
            $total_co2 = 0;

            // 1. Buat Header Transaksi
            $transaksi = TransaksiSetor::create([
                'nasabah_id' => $request->nasabah_id,
                'tanggal'    => $request->tanggal,
                'total_nilai'=> 0, 
                'total_co2'  => 0, 
                'catatan'    => $request->catatan,
            ]);

            // 2. Looping Keranjang & Simpan Detail
            foreach ($cart as $item) {
                $kategori = KategoriSampah::find($item['kategori_id']);
                $nilai = $item['berat'] * $kategori->harga_beli_per_kg;
                $co2 = $item['berat'] * $kategori->faktor_emisi;

                ItemSetor::create([
                    'transaksi_setor_id' => $transaksi->id,
                    'kategori_id'        => $kategori->id,
                    'berat_kg'           => $item['berat'],
                    'nilai'              => $nilai,
                    'co2'                => $co2,
                ]);

                $total_nilai += $nilai;
                $total_co2 += $co2;

                // 3. Tambah / Update Stok Gudang
                $stok = Stok::firstOrCreate(['kategori_id' => $kategori->id]);
                $stok->increment('total_berat_kg', $item['berat']);
            }

            // 4. Update Total di Header Transaksi
            $transaksi->update([
                'total_nilai' => $total_nilai,
                'total_co2'   => $total_co2,
            ]);

            // ==========================================
            // 5. TAMBAHAN BARU: LOGIKA TABUNGAN & MUTASI
            // ==========================================
            
            // Cek apakah nasabah sudah punya tabungan, jika belum buatkan
            $tabungan = Tabungan::firstOrCreate(
                ['nasabah_id' => $request->nasabah_id],
                ['saldo_saat_ini' => 0]
            );

            // Tambah saldo nasabah
            $tabungan->increment('saldo_saat_ini', $total_nilai);

            // Catat riwayat uang masuk (Kredit)
            MutasiTabungan::create([
                'nasabah_id' => $request->nasabah_id,
                'tanggal'    => $request->tanggal,
                'jenis'      => 'kredit',
                'jumlah'     => $total_nilai,
                'keterangan' => 'Penyetoran sampah (' . count($cart) . ' item)',
                'ref_transaksi_setor_id' => $transaksi->id,
            ]);
            // ==========================================

            DB::commit();
            return redirect()->route('setor.create')->with('success', 'Transaksi berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }
}