<?php

namespace App\Services;

use App\Enums\SettlementStatus;
use App\Models\ItemSetor;
use App\Models\KategoriSampah;
use App\Models\Stok;
use App\Models\Tabungan;
use App\Models\TransaksiSetor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetoranService
{
    public function create(int $nasabahId, string $tanggal, array $items, ?string $catatan = null): TransaksiSetor
    {
        return DB::transaction(function () use ($nasabahId, $tanggal, $items, $catatan) {
            $transaksi = TransaksiSetor::create([
                'nasabah_id' => $nasabahId,
                'flow_version' => 2,
                'tanggal' => $tanggal,
                'total_nilai' => 0,
                'total_co2' => 0,
                'catatan' => $catatan,
            ]);

            foreach ($items as $item) {
                $kategori = KategoriSampah::query()->find($item['kategori_id'] ?? null);
                $berat = round((float) ($item['berat'] ?? 0), 2);
                if (! $kategori || $berat <= 0) {
                    throw ValidationException::withMessages([
                        'cart_data' => 'Kategori atau berat setoran tidak valid.',
                    ]);
                }

                ItemSetor::create([
                    'transaksi_setor_id' => $transaksi->id,
                    'kategori_id' => $kategori->id,
                    'berat_kg' => $berat,
                    'berat_teralokasi_kg' => 0,
                    'status' => SettlementStatus::Pending,
                    'is_legacy' => false,
                    // Kolom legacy dipertahankan untuk histori, tetapi bukan source
                    // of truth untuk flow settlement-after-sale.
                    'nilai' => 0,
                    'co2' => 0,
                ]);

                $stok = Stok::query()->firstOrCreate(
                    ['kategori_id' => $kategori->id],
                    ['total_berat_kg' => 0]
                );
                $stok->increment('total_berat_kg', $berat);
            }

            // Menjaga relasi UI tanpa memberi kredit apa pun saat setoran.
            Tabungan::firstOrCreate(['nasabah_id' => $nasabahId], ['saldo_saat_ini' => 0]);

            return $transaksi->load('items.kategori', 'nasabah');
        });
    }
}
