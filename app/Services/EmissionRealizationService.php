<?php

namespace App\Services;

use App\Enums\AllocationSource;
use App\Enums\Co2Status;
use App\Models\AlokasiPenjualan;
use App\Models\ItemJual;
use App\Models\KategoriSampah;
use App\Models\TransaksiJual;
use Illuminate\Support\Facades\DB;

class EmissionRealizationService
{
    public function realizePendingForCategory(KategoriSampah $kategori): int
    {
        $factor = $kategori->faktorEmisi;
        if (! $factor) {
            return 0;
        }

        return DB::transaction(function () use ($kategori, $factor): int {
            $allocations = AlokasiPenjualan::query()
                ->where('sumber_tipe', AllocationSource::Setoran->value)
                ->where('co2_status', Co2Status::Pending->value)
                ->whereHas('itemJual', fn ($query) => $query->where('kategori_id', $kategori->id))
                ->lockForUpdate()
                ->get();

            $itemJualIds = [];
            foreach ($allocations as $allocation) {
                $co2 = round((float) $allocation->berat_kg * (float) $factor->faktor_kgco2e_per_kg, 6);
                $allocation->update([
                    'faktor_emisi_id' => $factor->id,
                    'faktor_emisi_snapshot' => $factor->faktor_kgco2e_per_kg,
                    'co2_terealisasi' => $co2,
                    'co2_status' => Co2Status::Realized,
                ]);
                $itemJualIds[] = $allocation->item_jual_id;
            }

            $this->refreshAggregates($itemJualIds);

            return $allocations->count();
        });
    }

    public function refreshAggregates(array $itemJualIds): void
    {
        $saleIds = [];
        foreach (array_unique($itemJualIds) as $itemJualId) {
            $item = ItemJual::find($itemJualId);
            if (! $item) {
                continue;
            }
            $sum = AlokasiPenjualan::where('item_jual_id', $item->id)->sum('co2_terealisasi');
            $item->update(['total_co2_terealisasi' => $sum > 0 ? round((float) $sum, 6) : null]);
            $saleIds[] = $item->transaksi_jual_id;
        }

        foreach (array_unique($saleIds) as $saleId) {
            $sum = ItemJual::where('transaksi_jual_id', $saleId)->sum('total_co2_terealisasi');
            TransaksiJual::whereKey($saleId)->update([
                'total_co2_terealisasi' => $sum > 0 ? round((float) $sum, 6) : null,
            ]);
        }
    }
}
