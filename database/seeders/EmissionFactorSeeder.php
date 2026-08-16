<?php

namespace Database\Seeders;

use App\Models\KelompokMaterial;
use App\Services\EmissionRealizationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmissionFactorSeeder extends Seeder
{
    public function run(): void
    {
        // WARM menyajikan kredit daur ulang dalam MTCO2e/short ton sebagai angka
        // negatif. Nilai positif kgCO2e/kg di bawah = nilai absolut x 1.10231131.
        $packaging = 'U.S. EPA, WARM, Containers, Packaging, and Non-Durable Goods Materials Chapters: https://www.epa.gov/system/files/documents/2023-12/warm_containers_packaging_and_non-durable_goods_materials_v16_dec.pdf';
        $electronics = 'U.S. EPA, WARM, Electronics: https://www.epa.gov/system/files/documents/2023-12/warm_electronics_v16_dec.pdf';

        $factors = [
            'Plastik' => [1.025150, $packaging, 'Proxy EPA WARM Mixed Plastics (kredit 0,93 MTCO2e/short ton). Campuran model: HDPE, LDPE, dan PET; hasil aktual bergantung pada resin, kontaminasi, jarak angkut, dan proses lokal.'],
            'Kertas/Karton' => [3.913205, $packaging, 'Proxy EPA WARM Mixed Paper (general), kredit 3,55 MTCO2e/short ton. Nilai mencakup kredit penyimpanan karbon hutan; komposisi dan kondisi kehutanan lokal dapat berbeda.'],
            'Logam' => [4.839147, $packaging, 'Proxy EPA WARM Mixed Metals (kredit 4,39 MTCO2e/short ton), bukan faktor khusus satu jenis kaleng. Pisahkan aluminium dan baja bila material diketahui.'],
            'Kaca' => [0.308647, $packaging, 'EPA WARM Glass (kredit 0,28 MTCO2e/short ton). Berlaku sebagai proxy kaca kemasan yang benar-benar masuk proses daur ulang.'],
            'Elektronik' => [0.992080, $electronics, 'Proxy EPA WARM Mixed Electronics (kredit 0,90 MTCO2e/short ton). Kipas angin tidak identik dengan bauran WARM; gunakan sementara sampai tersedia faktor LCA lokal atau khusus peralatan.'],
            'Lainnya' => [null, null, 'Faktor belum diisi karena komposisi material tidak terdefinisi. Klasifikasikan material terlebih dahulu agar estimasi tidak menyesatkan.'],
        ];

        DB::transaction(function () use ($factors): void {
            $service = app(EmissionRealizationService::class);

            foreach ($factors as $name => [$factor, $source, $notes]) {
                $group = KelompokMaterial::where('nama_normalized', mb_strtolower($name))->lockForUpdate()->firstOrFail();
                $group->update([
                    'faktor_emisi_kgco2e_per_kg' => $factor,
                    'sumber_faktor_emisi' => $source,
                    'versi_faktor_emisi' => $factor === null ? null : 'EPA WARM v16 (Desember 2023)',
                    'tanggal_berlaku_faktor_emisi' => $factor === null ? null : '2023-12-01',
                    'catatan_faktor_emisi' => $notes,
                ]);

                if ($factor !== null) {
                    $service->realizePendingForGroup($group);
                }
            }
        });
    }
}
