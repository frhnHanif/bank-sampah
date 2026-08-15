<?php

namespace App\Services;

use App\Enums\SettlementStatus;
use App\Enums\UnitPencatatan;
use App\Models\ItemSetor;
use App\Models\JenisSampah;
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
            $transaksi = TransaksiSetor::create(['nasabah_id' => $nasabahId, 'tanggal' => $tanggal, 'catatan' => $catatan]);

            foreach ($items as $item) {
                $jenis = JenisSampah::query()->where('is_active', true)->find($item['jenis_sampah_id'] ?? null);
                $berat = round((float) ($item['berat_kg'] ?? $item['berat'] ?? 0), 2);
                if (! $jenis || $berat <= 0) {
                    throw ValidationException::withMessages(['cart_data' => 'Jenis sampah aktif dan berat lebih dari 0 wajib dipilih.']);
                }

                $jumlahPcs = $item['jumlah_pcs'] ?? null;
                if ($jenis->satuan_pencatatan === UnitPencatatan::Pcs) {
                    if (filter_var($jumlahPcs, FILTER_VALIDATE_INT) === false || (int) $jumlahPcs <= 0) {
                        throw ValidationException::withMessages(['cart_data' => "Jumlah (pcs) {$jenis->nama} wajib berupa bilangan bulat lebih dari 0."]);
                    }
                    $jumlahPcs = (int) $jumlahPcs;
                } else {
                    $jumlahPcs = null;
                }

                ItemSetor::create([
                    'transaksi_setor_id' => $transaksi->id,
                    'jenis_sampah_id' => $jenis->id,
                    'jumlah_pcs' => $jumlahPcs,
                    'berat_kg' => $berat,
                    'berat_teralokasi_kg' => 0,
                    'status' => SettlementStatus::Pending,
                ]);

                $stok = Stok::firstOrCreate(['jenis_sampah_id' => $jenis->id], ['total_berat_kg' => 0]);
                $stok->increment('total_berat_kg', $berat);
            }

            Tabungan::firstOrCreate(['nasabah_id' => $nasabahId], ['saldo_saat_ini' => 0]);

            return $transaksi->load('items.jenisSampah', 'nasabah');
        });
    }
}
