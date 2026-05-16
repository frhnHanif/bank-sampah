<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use App\Models\TransaksiJual;
use App\Models\ItemJual;
use App\Models\MutasiKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class TransaksiJualController extends Controller
{
    public function create()
    {
        // Hanya ambil kategori sampah yang stoknya > 0 (tidak kosong)
        $stok = Stok::with('kategori')->where('total_berat_kg', '>', 0)->get();
        return view('jual.create', compact('stok'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'cart_data'  => 'required|string',
        ]);

        $cart = json_decode($request->cart_data, true);
        
        if (empty($cart)) {
            return back()->withErrors(['cart_data' => 'Keranjang penjualan masih kosong!']);
        }

        DB::beginTransaction();
        try {
            $total_nilai = 0;

            // 1. Buat Header Transaksi Jual
            $transaksi = TransaksiJual::create([
                'tanggal'    => $request->tanggal,
                'total_nilai'=> 0, 
                'catatan'    => $request->catatan,
            ]);

            // 2. Looping Keranjang
            foreach ($cart as $item) {
                // Cek ulang stok di database untuk mencegah kecurangan/bug
                $stok = Stok::where('kategori_id', $item['kategori_id'])->first();
                
                if (!$stok || $stok->total_berat_kg < $item['berat']) {
                    throw new \Exception("Gagal! Stok " . $item['nama'] . " tidak mencukupi (Sisa: " . ($stok->total_berat_kg ?? 0) . " Kg)");
                }

                $nilai = $item['berat'] * $item['harga_jual'];

                // Simpan Item Jual
                ItemJual::create([
                    'transaksi_jual_id'  => $transaksi->id,
                    'kategori_id'        => $item['kategori_id'],
                    'berat_kg'           => $item['berat'],
                    'harga_jual_per_kg'  => $item['harga_jual'],
                    'total_nilai'        => $nilai,
                ]);

                $total_nilai += $nilai;

                // 3. KURANGI STOK GUDANG
                $stok->decrement('total_berat_kg', $item['berat']);
            }

            // 4. Update Total Nilai Penjualan
            $transaksi->update(['total_nilai' => $total_nilai]);

            // 5. Catat ke Buku Kas Induk sebagai Pemasukan
            MutasiKas::create([
                'tanggal'    => $request->tanggal,
                'tipe'       => 'pemasukan',
                'kategori'   => 'Penjualan',
                'nominal'    => $total_nilai,
                'keterangan' => 'Penjualan ke Pengepul: ' . $request->catatan,
            ]);

            DB::commit();
            return redirect()->route('stok.index')->with('success', 'Transaksi penjualan ke pengepul berhasil! Stok gudang telah dikurangi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}