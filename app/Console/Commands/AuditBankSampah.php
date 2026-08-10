<?php

namespace App\Console\Commands;

use App\Enums\Co2Status;
use App\Enums\SettlementStatus;
use App\Models\AlokasiPenjualan;
use App\Models\ItemJual;
use App\Models\ItemSetor;
use App\Models\LegacyInventory;
use App\Models\MutasiTabungan;
use App\Models\Stok;
use App\Models\Tabungan;
use Illuminate\Console\Command;

class AuditBankSampah extends Command
{
    protected $signature = 'bank-sampah:audit';

    protected $description = 'Memeriksa invariant stok, settlement, tabungan, dan snapshot CO2e';

    public function handle(): int
    {
        $errors = [];
        $tolerance = 0.009;

        foreach (Stok::with('kategori')->get() as $stock) {
            if ((float) $stock->total_berat_kg < -$tolerance) {
                $errors[] = "Stok {$stock->kategori?->nama} negatif.";
            }
            $legacy = (float) LegacyInventory::where('kategori_id', $stock->kategori_id)->sum('berat_tersisa_kg');
            $pending = (float) ItemSetor::where('kategori_id', $stock->kategori_id)->where('is_legacy', false)
                ->selectRaw('COALESCE(SUM(berat_kg - berat_teralokasi_kg), 0) AS total')->value('total');
            if (abs((float) $stock->total_berat_kg - ($legacy + $pending)) > $tolerance) {
                $errors[] = "Ringkasan stok {$stock->kategori?->nama} tidak sama dengan legacy + lot pending.";
            }
        }

        foreach (ItemSetor::where('is_legacy', false)->get() as $item) {
            $weight = (float) $item->berat_kg;
            $allocated = (float) $item->berat_teralokasi_kg;
            if ($allocated < -$tolerance || $allocated - $weight > $tolerance) {
                $errors[] = "Item setor #{$item->id} memiliki berat teralokasi invalid.";
            }
            $expected = $allocated <= $tolerance ? SettlementStatus::Pending
                : ($weight - $allocated <= $tolerance ? SettlementStatus::Settled : SettlementStatus::Partial);
            if ($item->status !== $expected) {
                $errors[] = "Status item setor #{$item->id} tidak sesuai berat.";
            }
            $allocationWeight = (float) AlokasiPenjualan::where('item_setor_id', $item->id)->sum('berat_kg');
            if (abs($allocationWeight - $allocated) > $tolerance) {
                $errors[] = "Audit trail item setor #{$item->id} tidak sesuai.";
            }
            if ($item->status === SettlementStatus::Pending && $weight - $allocated <= $tolerance) {
                $errors[] = "Item setor #{$item->id} pending tanpa sisa.";
            }
            if ($item->status === SettlementStatus::Settled && $weight - $allocated > $tolerance) {
                $errors[] = "Item setor #{$item->id} settled tetapi masih bersisa.";
            }
        }

        foreach (ItemJual::whereHas('transaksi', fn ($q) => $q->where('flow_version', 2))->get() as $item) {
            $allocated = (float) AlokasiPenjualan::where('item_jual_id', $item->id)->sum('berat_kg');
            if (abs($allocated - (float) $item->berat_kg) > $tolerance) {
                $errors[] = "Item jual #{$item->id} tidak teralokasi penuh.";
            }
        }

        foreach (Tabungan::all() as $account) {
            $credits = (float) MutasiTabungan::where('nasabah_id', $account->nasabah_id)->where('jenis', 'kredit')->sum('jumlah');
            $debits = (float) MutasiTabungan::where('nasabah_id', $account->nasabah_id)->where('jenis', 'debit')->sum('jumlah');
            if (abs((float) $account->saldo_saat_ini - ($credits - $debits)) > 0.009) {
                $errors[] = "Saldo tabungan nasabah #{$account->nasabah_id} tidak sesuai mutasi.";
            }
        }

        foreach (AlokasiPenjualan::all() as $allocation) {
            if ($allocation->co2_status === Co2Status::Realized && $allocation->faktor_emisi_snapshot === null) {
                $errors[] = "Allocation #{$allocation->id} realized tanpa snapshot faktor.";
            }
            if ($allocation->co2_status === Co2Status::Pending && $allocation->co2_terealisasi !== null) {
                $errors[] = "Allocation #{$allocation->id} pending tetapi memiliki nilai CO2e.";
            }
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }
            $this->error('Audit gagal: '.count($errors).' invariant bermasalah.');

            return self::FAILURE;
        }

        $this->info('Audit bank sampah lulus: seluruh invariant kritis konsisten.');

        return self::SUCCESS;
    }
}
