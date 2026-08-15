<?php

namespace App\Services;

use App\Enums\Co2Status;
use App\Models\AlokasiPenjualan;
use App\Models\ItemJual;
use App\Models\KelompokMaterial;
use App\Models\TransaksiJual;
use Illuminate\Support\Facades\DB;

class EmissionRealizationService
{
    public function realizePendingForGroup(KelompokMaterial $kelompok): int
    {
        $factor = $kelompok->faktor_emisi_kgco2e_per_kg;
        if ($factor === null) {
            return 0;
        }

        return DB::transaction(function () use ($kelompok, $factor) {
            $allocations = AlokasiPenjualan::where('co2_status', Co2Status::Pending->value)
                ->whereHas('itemJual.jenisSampah', fn ($q) => $q->where('kelompok_material_id', $kelompok->id))
                ->lockForUpdate()->get();
            foreach ($allocations as $allocation) {
                $allocation->update([
                    'faktor_emisi_snapshot' => $factor,
                    'sumber_faktor_emisi_snapshot' => $kelompok->sumber_faktor_emisi,
                    'versi_faktor_emisi_snapshot' => $kelompok->versi_faktor_emisi,
                    'co2_terealisasi' => round((float) $allocation->berat_kg * (float) $factor, 6),
                    'co2_status' => Co2Status::Realized,
                ]);
            }
            $this->refreshAggregates($allocations->pluck('item_jual_id')->all());

            return $allocations->count();
        });
    }

    private function refreshAggregates(array $itemIds): void
    {
        $saleIds = [];
        foreach (array_unique($itemIds) as $id) {
            $item = ItemJual::find($id);
            if (! $item) {
                continue;
            }
            $sum = AlokasiPenjualan::where('item_jual_id', $id)->sum('co2_terealisasi');
            $item->update(['total_co2_terealisasi' => $sum > 0 ? round((float) $sum, 6) : null]);
            $saleIds[] = $item->transaksi_jual_id;
        }
        foreach (array_unique($saleIds) as $id) {
            $sum = ItemJual::where('transaksi_jual_id', $id)->sum('total_co2_terealisasi');
            TransaksiJual::whereKey($id)->update(['total_co2_terealisasi' => $sum > 0 ? round((float) $sum, 6) : null]);
        }
    }
}
