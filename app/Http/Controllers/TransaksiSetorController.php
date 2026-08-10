<?php

namespace App\Http\Controllers;

use App\Models\FaktorEmisi;
use App\Models\KategoriSampah;
use App\Models\Nasabah;
use App\Services\SetoranService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransaksiSetorController extends Controller
{
    public function index()
    {
        return redirect()->route('setor.create');
    }

    public function create()
    {
        $nasabah = Nasabah::with('tabungan')->orderBy('nama')->get();
        $kategori = KategoriSampah::with('faktorEmisi')->orderBy('nama')->get();
        $faktorEmisi = FaktorEmisi::where('aktif', true)->orderBy('nama_material')->get();

        return view('setor.create', compact('nasabah', 'kategori', 'faktorEmisi'));
    }

    public function store(Request $request, SetoranService $service)
    {
        $data = $request->validate([
            'nasabah_id' => ['required', 'integer', 'exists:nasabah,id'],
            'tanggal' => ['required', 'date'],
            'cart_data' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);
        $cart = json_decode($data['cart_data'], true);
        if (! is_array($cart) || $cart === []) {
            throw ValidationException::withMessages(['cart_data' => 'Keranjang setor masih kosong.']);
        }

        $transaction = $service->create((int) $data['nasabah_id'], $data['tanggal'], $cart, $data['catatan'] ?? null);
        $totalWeight = $transaction->items->sum(fn ($item) => (float) $item->berat_kg);
        $details = $transaction->items->map(fn ($item) => sprintf(
            '- %s - %s kg', $item->kategori->nama, number_format((float) $item->berat_kg, 2, ',', '.')
        ))->implode("\n");
        $message = "*[BUKTI SETOR BANK SAMPAH]*\n\nHalo *{$transaction->nasabah->nama}*,\n\n"
            ."Setoran Anda telah diterima pada {$data['tanggal']}.\n\nRincian:\n{$details}\n\n"
            .'Total berat: '.number_format($totalWeight, 2, ',', '.')." kg\n\n"
            ."Status: Menunggu penjualan ke pengepul.\nNilai rupiah belum ditentukan. "
            ."Saldo tabungan akan diperbarui setelah barang terkait terjual dan dilakukan settlement.\n\nTerima kasih.";
        $phone = $transaction->nasabah->no_hp ?? '';
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return redirect()->route('setor.create')
            ->with('success', 'Setoran dicatat sebagai pending. Saldo nasabah tidak berubah.')
            ->with('wa_url', "https://wa.me/{$phone}?text=".urlencode($message))
            ->with('wa_nasabah', $transaction->nasabah->nama);
    }
}
