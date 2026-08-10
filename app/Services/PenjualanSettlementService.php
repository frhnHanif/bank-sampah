<?php

namespace App\Services;

use App\Enums\AllocationSource;
use App\Enums\Co2Status;
use App\Enums\SettlementStatus;
use App\Exceptions\InventoryConsistencyException;
use App\Models\AlokasiPenjualan;
use App\Models\ItemJual;
use App\Models\ItemSetor;
use App\Models\KategoriSampah;
use App\Models\LegacyInventory;
use App\Models\MutasiKas;
use App\Models\MutasiTabungan;
use App\Models\Stok;
use App\Models\Tabungan;
use App\Models\TransaksiJual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PenjualanSettlementService
{
    private const WEIGHT_TOLERANCE = 0.009;

    public function create(string $tanggal, array $items, ?string $catatan = null): TransaksiJual
    {
        return DB::transaction(function () use ($tanggal, $items, $catatan) {
            $sale = TransaksiJual::create([
                'flow_version' => 2,
                'tanggal' => $tanggal,
                'total_nilai' => 0,
                'total_hak_nasabah' => 0,
                'total_cost_basis' => 0,
                'total_margin_kotor' => 0,
                'total_co2_terealisasi' => null,
                'catatan' => $catatan,
            ]);

            $totals = ['revenue' => 0.0, 'rights' => 0.0, 'cost' => 0.0, 'margin' => 0.0, 'co2' => 0.0];
            $credits = [];

            foreach ($items as $payload) {
                $result = $this->allocateItem($sale, $payload, $credits);
                foreach ($totals as $key => $value) {
                    $totals[$key] += $result[$key];
                }
            }

            foreach ($credits as $nasabahId => $credit) {
                $tabungan = Tabungan::query()->where('nasabah_id', $nasabahId)->lockForUpdate()->first();
                if (! $tabungan) {
                    $tabungan = Tabungan::create(['nasabah_id' => $nasabahId, 'saldo_saat_ini' => 0]);
                }
                $jumlah = round($credit['amount'], 2);
                $tabungan->increment('saldo_saat_ini', $jumlah);
                MutasiTabungan::create([
                    'nasabah_id' => $nasabahId,
                    'tanggal' => $tanggal,
                    'jenis' => 'kredit',
                    'jumlah' => $jumlah,
                    'keterangan' => 'Settlement hasil penjualan sampah #'.$sale->id,
                    'ref_transaksi_jual_id' => $sale->id,
                ]);
            }

            $sale->update([
                'total_nilai' => round($totals['revenue'], 2),
                'total_hak_nasabah' => round($totals['rights'], 2),
                'total_cost_basis' => round($totals['cost'], 2),
                'total_margin_kotor' => round($totals['margin'], 2),
                'total_co2_terealisasi' => $totals['co2'] > 0 ? round($totals['co2'], 6) : null,
            ]);

            MutasiKas::create([
                'tanggal' => $tanggal,
                'tipe' => 'pemasukan',
                'kategori' => 'Penjualan',
                'nominal' => round($totals['revenue'], 2),
                'keterangan' => 'Penjualan ke pengepul: '.($catatan ?: '#'.$sale->id),
                'ref_transaksi_jual_id' => $sale->id,
            ]);

            return $sale->load('items.alokasi.itemSetor.transaksi.nasabah');
        }, 3);
    }

    private function allocateItem(TransaksiJual $sale, array $payload, array &$credits): array
    {
        $kategori = KategoriSampah::query()->with('faktorEmisi')->find($payload['kategori_id'] ?? null);
        $weight = round((float) ($payload['berat'] ?? 0), 2);
        $salePrice = round((float) ($payload['harga_jual'] ?? -1), 2);
        $customerPrice = array_key_exists('harga_nasabah', $payload)
            ? round((float) $payload['harga_nasabah'], 2)
            : null;

        if (! $kategori || $weight <= 0 || $salePrice < 0 || ($customerPrice !== null && $customerPrice < 0)) {
            throw ValidationException::withMessages(['cart_data' => 'Data item penjualan tidak valid.']);
        }

        $stock = Stok::query()->where('kategori_id', $kategori->id)->lockForUpdate()->first();
        if (! $stock || (float) $stock->total_berat_kg + self::WEIGHT_TOLERANCE < $weight) {
            throw ValidationException::withMessages([
                'cart_data' => sprintf(
                    'Stok %s tidak mencukupi. Tersedia: %s kg, diminta: %s kg.',
                    $kategori->nama,
                    number_format((float) ($stock?->total_berat_kg ?? 0), 2, ',', '.'),
                    number_format($weight, 2, ',', '.')
                ),
            ]);
        }

        $legacyWeight = (float) LegacyInventory::where('kategori_id', $kategori->id)->sum('berat_tersisa_kg');
        $pendingWeight = (float) ItemSetor::query()->where('kategori_id', $kategori->id)
            ->where('is_legacy', false)
            ->selectRaw('COALESCE(SUM(berat_kg - berat_teralokasi_kg), 0) AS pending')->value('pending');
        $expectedStock = round($legacyWeight + $pendingWeight, 2);

        if (abs((float) $stock->total_berat_kg - $expectedStock) > self::WEIGHT_TOLERANCE) {
            Log::error('Inventory settlement invariant mismatch', [
                'kategori_id' => $kategori->id,
                'kategori' => $kategori->nama,
                'requested_weight' => $weight,
                'stock_summary' => $stock->total_berat_kg,
                'legacy_remaining' => $legacyWeight,
                'new_pending' => $pendingWeight,
            ]);
            throw new InventoryConsistencyException(
                "Data stok {$kategori->nama} tidak konsisten dengan lot pending. Jalankan audit atau hubungi admin."
            );
        }

        $itemJual = ItemJual::create([
            'transaksi_jual_id' => $sale->id,
            'kategori_id' => $kategori->id,
            'berat_kg' => $weight,
            'harga_jual_per_kg' => $salePrice,
            'harga_nasabah_per_kg' => $customerPrice,
            'total_nilai' => round($weight * $salePrice, 2),
            'total_hak_nasabah' => 0,
            'total_cost_basis' => 0,
            'margin_kotor' => 0,
            'total_co2_terealisasi' => null,
        ]);

        $remaining = $weight;
        $totals = ['revenue' => 0.0, 'rights' => 0.0, 'cost' => 0.0, 'margin' => 0.0, 'co2' => 0.0];

        $legacyLots = LegacyInventory::query()->where('kategori_id', $kategori->id)
            ->where('berat_tersisa_kg', '>', 0)->orderBy('cutover_at')->orderBy('id')->lockForUpdate()->get();
        foreach ($legacyLots as $legacy) {
            if ($remaining <= self::WEIGHT_TOLERANCE) {
                break;
            }
            $take = round(min($remaining, (float) $legacy->berat_tersisa_kg), 2);
            $revenue = round($take * $salePrice, 2);
            $cost = round($take * (float) $legacy->cost_basis_per_kg, 2);
            $margin = round($revenue - $cost, 2);

            AlokasiPenjualan::create([
                'item_jual_id' => $itemJual->id,
                'sumber_tipe' => AllocationSource::LegacyOpening,
                'legacy_inventory_id' => $legacy->id,
                'berat_kg' => $take,
                'harga_pengepul_per_kg' => $salePrice,
                'harga_nasabah_per_kg' => null,
                'nilai_penjualan' => $revenue,
                'nilai_hak_nasabah' => 0,
                'cost_basis' => $cost,
                'margin_kotor' => $margin,
                'co2_status' => Co2Status::NotApplicable,
            ]);
            $legacy->update(['berat_tersisa_kg' => round((float) $legacy->berat_tersisa_kg - $take, 2)]);
            $remaining = round($remaining - $take, 2);
            $totals['revenue'] += $revenue;
            $totals['cost'] += $cost;
            $totals['margin'] += $margin;
        }

        if ($remaining > self::WEIGHT_TOLERANCE) {
            if ($customerPrice === null) {
                throw ValidationException::withMessages(['cart_data' => "Harga nasabah untuk {$kategori->nama} wajib diisi."]);
            }
            if ($customerPrice > $salePrice) {
                throw ValidationException::withMessages(['cart_data' => "Harga nasabah {$kategori->nama} tidak boleh melebihi harga pengepul."]);
            }

            $pendingItems = ItemSetor::query()
                ->select('item_setor.*')
                ->join('transaksi_setor', 'transaksi_setor.id', '=', 'item_setor.transaksi_setor_id')
                ->where('item_setor.kategori_id', $kategori->id)
                ->where('item_setor.is_legacy', false)
                ->whereColumn('item_setor.berat_teralokasi_kg', '<', 'item_setor.berat_kg')
                ->with('transaksi.nasabah')
                ->orderBy('transaksi_setor.tanggal')->orderBy('item_setor.id')
                ->lockForUpdate()->get();

            foreach ($pendingItems as $depositItem) {
                if ($remaining <= self::WEIGHT_TOLERANCE) {
                    break;
                }
                $available = round((float) $depositItem->berat_kg - (float) $depositItem->berat_teralokasi_kg, 2);
                $take = round(min($remaining, $available), 2);
                $revenue = round($take * $salePrice, 2);
                $right = round($take * $customerPrice, 2);
                $margin = round($revenue - $right, 2);
                $factor = $kategori->faktorEmisi;
                $co2 = $factor ? round($take * (float) $factor->faktor_kgco2e_per_kg, 6) : null;

                AlokasiPenjualan::create([
                    'item_jual_id' => $itemJual->id,
                    'sumber_tipe' => AllocationSource::Setoran,
                    'item_setor_id' => $depositItem->id,
                    'berat_kg' => $take,
                    'harga_pengepul_per_kg' => $salePrice,
                    'harga_nasabah_per_kg' => $customerPrice,
                    'nilai_penjualan' => $revenue,
                    'nilai_hak_nasabah' => $right,
                    'cost_basis' => $right,
                    'margin_kotor' => $margin,
                    'faktor_emisi_id' => $factor?->id,
                    'faktor_emisi_snapshot' => $factor?->faktor_kgco2e_per_kg,
                    'co2_terealisasi' => $co2,
                    'co2_status' => $factor ? Co2Status::Realized : Co2Status::Pending,
                ]);

                $allocated = round((float) $depositItem->berat_teralokasi_kg + $take, 2);
                $status = $allocated + self::WEIGHT_TOLERANCE >= (float) $depositItem->berat_kg
                    ? SettlementStatus::Settled : SettlementStatus::Partial;
                $depositItem->update(['berat_teralokasi_kg' => $allocated, 'status' => $status]);

                $nasabahId = $depositItem->transaksi->nasabah_id;
                $credits[$nasabahId] ??= ['amount' => 0.0];
                $credits[$nasabahId]['amount'] += $right;
                $remaining = round($remaining - $take, 2);
                $totals['revenue'] += $revenue;
                $totals['rights'] += $right;
                $totals['cost'] += $right;
                $totals['margin'] += $margin;
                $totals['co2'] += $co2 ?? 0;
            }
        }

        if ($remaining > self::WEIGHT_TOLERANCE) {
            throw new InventoryConsistencyException("Lot FIFO {$kategori->nama} tidak cukup untuk penjualan ini.");
        }

        $stock->update(['total_berat_kg' => round((float) $stock->total_berat_kg - $weight, 2)]);
        $itemJual->update([
            'total_hak_nasabah' => round($totals['rights'], 2),
            'total_cost_basis' => round($totals['cost'], 2),
            'margin_kotor' => round($totals['margin'], 2),
            'total_co2_terealisasi' => $totals['co2'] > 0 ? round($totals['co2'], 6) : null,
        ]);

        return $totals;
    }
}
