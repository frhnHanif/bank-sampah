<?php

namespace App\Console\Commands;

use App\Models\ItemSetor;
use App\Models\Stok;
use App\Models\Tabungan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditBankSampah extends Command
{
    protected $signature = 'bank-sampah:audit';

    protected $description = 'Audit konsistensi stok dan saldo bank sampah';

    public function handle(): int
    {
        $errors = [];
        foreach (Stok::all() as $stock) {
            $pending = (float) ItemSetor::where('jenis_sampah_id', $stock->jenis_sampah_id)->sum(DB::raw('berat_kg - berat_teralokasi_kg'));
            if (abs((float) $stock->total_berat_kg - $pending) > 0.009) {
                $errors[] = "Stok jenis #{$stock->jenis_sampah_id} tidak konsisten.";
            }
        }
        foreach (Tabungan::all() as $saving) {
            $balance = (float) DB::table('mutasi_tabungan')->where('nasabah_id', $saving->nasabah_id)
                ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'kredit' THEN jumlah ELSE -jumlah END), 0) total")->value('total');
            if (abs((float) $saving->saldo_saat_ini - $balance) > 0.009) {
                $errors[] = "Saldo nasabah #{$saving->nasabah_id} tidak konsisten.";
            }
        }
        foreach ($errors as $error) {
            $this->error($error);
        }
        if ($errors) {
            return self::FAILURE;
        }
        $this->info('Audit stok dan saldo lulus.');

        return self::SUCCESS;
    }
}
