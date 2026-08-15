<?php

namespace App\Services;

use App\Enums\Co2Status;
use App\Enums\SettlementStatus;
use App\Exceptions\InventoryConsistencyException;
use App\Models\AlokasiPenjualan;
use App\Models\ItemJual;
use App\Models\ItemSetor;
use App\Models\JenisSampah;
use App\Models\MutasiKas;
use App\Models\MutasiTabungan;
use App\Models\Stok;
use App\Models\Tabungan;
use App\Models\TransaksiJual;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenjualanSettlementService
{
    private const TOLERANCE = 0.009;

    public function create(string $tanggal, array $items, ?string $catatan = null): TransaksiJual
    {
        return DB::transaction(function () use ($tanggal, $items, $catatan) {
            $sale = TransaksiJual::create(['tanggal' => $tanggal, 'catatan' => $catatan]);
            $totals = ['revenue' => 0.0, 'rights' => 0.0, 'cost' => 0.0, 'margin' => 0.0, 'co2' => 0.0];
            $credits = [];
            foreach ($items as $payload) {
                foreach ($this->allocate($sale, $payload, $credits) as $key => $value) {
                    $totals[$key] += $value;
                }
            }
            foreach ($credits as $nasabahId => $amount) {
                $tabungan = Tabungan::query()->where('nasabah_id', $nasabahId)->lockForUpdate()->first()
                    ?? Tabungan::create(['nasabah_id' => $nasabahId, 'saldo_saat_ini' => 0]);
                $amount = round($amount, 2);
                $tabungan->increment('saldo_saat_ini', $amount);
                MutasiTabungan::create(['nasabah_id' => $nasabahId, 'tanggal' => $tanggal, 'jenis' => 'kredit', 'jumlah' => $amount,
                    'keterangan' => 'Hasil penjualan sampah #'.$sale->id, 'ref_transaksi_jual_id' => $sale->id]);
            }
            $sale->update(['total_nilai' => round($totals['revenue'], 2), 'total_hak_nasabah' => round($totals['rights'], 2),
                'total_cost_basis' => round($totals['cost'], 2), 'total_margin_kotor' => round($totals['margin'], 2),
                'total_co2_terealisasi' => $totals['co2'] > 0 ? round($totals['co2'], 6) : null]);
            MutasiKas::create(['tanggal' => $tanggal, 'tipe' => 'pemasukan', 'kategori' => 'Penjualan',
                'nominal' => round($totals['revenue'], 2), 'keterangan' => 'Penjualan ke pengepul: '.($catatan ?: '#'.$sale->id),
                'ref_transaksi_jual_id' => $sale->id]);

            return $sale->load('items.alokasi.itemSetor.transaksi.nasabah');
        }, 3);
    }

    private function allocate(TransaksiJual $sale, array $payload, array &$credits): array
    {
        $jenis = JenisSampah::with('kelompokMaterial')->where('is_active', true)->find($payload['jenis_sampah_id'] ?? null);
        $weight = round((float) ($payload['berat_kg'] ?? $payload['berat'] ?? 0), 2);
        $salePrice = round((float) ($payload['harga_jual'] ?? -1), 2);
        $customerPrice = round((float) ($payload['harga_nasabah'] ?? -1), 2);
        if (! $jenis || $weight <= 0 || $salePrice < 0 || $customerPrice < 0 || $customerPrice > $salePrice) {
            throw ValidationException::withMessages(['cart_data' => 'Data item penjualan tidak valid.']);
        }
        $stock = Stok::where('jenis_sampah_id', $jenis->id)->lockForUpdate()->first();
        if (! $stock || (float) $stock->total_berat_kg + self::TOLERANCE < $weight) {
            throw ValidationException::withMessages(['cart_data' => "Stok {$jenis->nama} tidak mencukupi."]);
        }
        $pendingWeight = (float) ItemSetor::where('jenis_sampah_id', $jenis->id)
            ->selectRaw('COALESCE(SUM(berat_kg - berat_teralokasi_kg), 0) AS pending')->value('pending');
        if (abs((float) $stock->total_berat_kg - $pendingWeight) > self::TOLERANCE) {
            throw new InventoryConsistencyException("Data stok {$jenis->nama} tidak konsisten dengan setoran belum terjual.");
        }
        $itemJual = ItemJual::create(['transaksi_jual_id' => $sale->id, 'jenis_sampah_id' => $jenis->id,
            'berat_kg' => $weight, 'harga_jual_per_kg' => $salePrice, 'harga_nasabah_per_kg' => $customerPrice,
            'total_nilai' => round($weight * $salePrice, 2)]);
        $remaining = $weight;
        $totals = ['revenue' => 0.0, 'rights' => 0.0, 'cost' => 0.0, 'margin' => 0.0, 'co2' => 0.0];
        $lots = ItemSetor::select('item_setor.*')->join('transaksi_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
            ->where('item_setor.jenis_sampah_id', $jenis->id)->whereColumn('berat_teralokasi_kg', '<', 'berat_kg')
            ->with('transaksi.nasabah')->orderBy('transaksi_setor.tanggal')->orderBy('item_setor.id')->lockForUpdate()->get();
        foreach ($lots as $lot) {
            if ($remaining <= self::TOLERANCE) {
                break;
            }
            $take = round(min($remaining, (float) $lot->berat_kg - (float) $lot->berat_teralokasi_kg), 2);
            $revenue = round($take * $salePrice, 2);
            $right = round($take * $customerPrice, 2);
            $margin = $revenue - $right;
            $factor = $jenis->kelompokMaterial->faktor_emisi_kgco2e_per_kg;
            $co2 = $factor === null ? null : round($take * (float) $factor, 6);
            AlokasiPenjualan::create(['item_jual_id' => $itemJual->id, 'item_setor_id' => $lot->id, 'berat_kg' => $take,
                'harga_pengepul_per_kg' => $salePrice, 'harga_nasabah_per_kg' => $customerPrice, 'nilai_penjualan' => $revenue,
                'nilai_hak_nasabah' => $right, 'cost_basis' => $right, 'margin_kotor' => $margin,
                'faktor_emisi_snapshot' => $factor, 'sumber_faktor_emisi_snapshot' => $factor === null ? null : $jenis->kelompokMaterial->sumber_faktor_emisi,
                'versi_faktor_emisi_snapshot' => $factor === null ? null : $jenis->kelompokMaterial->versi_faktor_emisi,
                'co2_terealisasi' => $co2, 'co2_status' => $factor === null ? Co2Status::Pending : Co2Status::Realized]);
            $allocated = round((float) $lot->berat_teralokasi_kg + $take, 2);
            $lot->update(['berat_teralokasi_kg' => $allocated, 'status' => $allocated + self::TOLERANCE >= (float) $lot->berat_kg ? SettlementStatus::Settled : SettlementStatus::Partial]);
            $credits[$lot->transaksi->nasabah_id] = ($credits[$lot->transaksi->nasabah_id] ?? 0) + $right;
            $remaining = round($remaining - $take, 2);
            $totals['revenue'] += $revenue;
            $totals['rights'] += $right;
            $totals['cost'] += $right;
            $totals['margin'] += $margin;
            $totals['co2'] += $co2 ?? 0;
        }
        if ($remaining > self::TOLERANCE) {
            throw new InventoryConsistencyException("Lot FIFO {$jenis->nama} tidak cukup.");
        }
        $stock->update(['total_berat_kg' => round((float) $stock->total_berat_kg - $weight, 2)]);
        $itemJual->update(['total_hak_nasabah' => round($totals['rights'], 2), 'total_cost_basis' => round($totals['cost'], 2),
            'margin_kotor' => round($totals['margin'], 2), 'total_co2_terealisasi' => $totals['co2'] > 0 ? round($totals['co2'], 6) : null]);

        return $totals;
    }
}
