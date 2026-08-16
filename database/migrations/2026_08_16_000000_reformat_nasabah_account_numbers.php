<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('nasabah')->orderBy('id')->chunkById(500, function ($nasabah): void {
            foreach ($nasabah as $item) {
                if ($item->id > 9999) {
                    throw new RuntimeException('Format rekening empat digit hanya mendukung maksimal 9.999 nasabah.');
                }

                DB::table('nasabah')->where('id', $item->id)->update([
                    'kode' => str_pad((string) (int) $item->rt, 3, '0', STR_PAD_LEFT)
                        .str_pad((string) (int) $item->rw, 3, '0', STR_PAD_LEFT)
                        .str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                    'rt' => str_pad((string) (int) $item->rt, 3, '0', STR_PAD_LEFT),
                    'rw' => str_pad((string) (int) $item->rw, 3, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('nasabah')->orderBy('id')->chunkById(500, function ($nasabah): void {
            foreach ($nasabah as $item) {
                DB::table('nasabah')->where('id', $item->id)->update([
                    'kode' => 'IMP-'.str_pad((string) $item->id, 5, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }
};
