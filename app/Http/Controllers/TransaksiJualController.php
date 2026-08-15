<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryConsistencyException;
use App\Services\PenjualanSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class TransaksiJualController extends Controller
{
    public function create()
    {
        return redirect()->route('stok.index');
    }

    public function store(Request $request, PenjualanSettlementService $service)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'cart_data' => ['required', 'string'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        $cart = json_decode($data['cart_data'], true);
        if (! is_array($cart) || $cart === []) {
            throw ValidationException::withMessages(['cart_data' => 'Keranjang penjualan masih kosong.']);
        }
        $ids = array_column($cart, 'jenis_sampah_id');
        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages(['cart_data' => 'Satu jenis sampah hanya boleh muncul sekali dalam penjualan.']);
        }

        try {
            $sale = $service->create($data['tanggal'], $cart, $data['catatan'] ?? null);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (InventoryConsistencyException $exception) {
            return back()->withInput()->withErrors(['error' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Sale settlement failed', ['exception' => $exception]);

            return back()->withInput()->withErrors(['error' => 'Penjualan gagal diproses. Tidak ada data yang diubah.']);
        }

        $waLinks = $sale->items->flatMap->alokasi
            ->filter(fn ($allocation) => $allocation->itemSetor)
            ->groupBy(fn ($allocation) => $allocation->itemSetor->transaksi->nasabah_id)
            ->map(function ($allocations) use ($sale) {
                $nasabah = $allocations->first()->itemSetor->transaksi->nasabah;
                $details = $allocations->map(fn ($allocation) => sprintf(
                    '- %s: %s kg x Rp %s = Rp %s',
                    $allocation->itemJual->jenisSampah->nama,
                    number_format((float) $allocation->berat_kg, 2, ',', '.'),
                    number_format((float) $allocation->harga_nasabah_per_kg, 0, ',', '.'),
                    number_format((float) $allocation->nilai_hak_nasabah, 0, ',', '.')
                ))->implode("\n");
                $amount = $allocations->sum(fn ($allocation) => (float) $allocation->nilai_hak_nasabah);
                $phone = $nasabah->no_hp ?? '';
                if (str_starts_with($phone, '0')) {
                    $phone = '62'.substr($phone, 1);
                }
                $message = "*[HASIL PENJUALAN SAMPAH]*\n\nHalo *{$nasabah->nama}*,\n\nSebagian/seluruh setoran Anda telah terjual.\n{$details}\n\n"
                    .'Uang masuk tabungan: Rp '.number_format($amount, 0, ',', '.')."\nTanggal penjualan: {$sale->tanggal->format('d-m-Y')}\n\nTerima kasih.";

                return ['name' => $nasabah->nama, 'url' => "https://wa.me/{$phone}?text=".urlencode($message)];
            })->values()->all();

        return redirect()->route('stok.index')
            ->with('success', 'Penjualan ke Pengepul berhasil dicatat.')
            ->with('settlement_wa', $waLinks);
    }
}
