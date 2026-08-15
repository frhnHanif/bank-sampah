<?php

namespace Tests\Feature;

use App\Enums\Co2Status;
use App\Models\AlokasiPenjualan;
use App\Models\JenisSampah;
use App\Models\KelompokMaterial;
use App\Models\Nasabah;
use App\Models\Stok;
use App\Models\User;
use App\Services\EmissionRealizationService;
use App\Services\PenjualanSettlementService;
use App\Services\SetoranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SettlementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kg_and_pcs_deposits_update_weight_inventory_without_credit(): void
    {
        [$customer,$paper,$fan] = $this->fixtures();
        $deposit = app(SetoranService::class)->create($customer->id, '2026-08-15', [
            ['jenis_sampah_id' => $paper->id, 'berat_kg' => 3.92, 'jumlah_pcs' => 99],
            ['jenis_sampah_id' => $fan->id, 'jumlah_pcs' => 2, 'berat_kg' => 7.45],
        ]);
        $this->assertNull($deposit->items->firstWhere('jenis_sampah_id', $paper->id)->jumlah_pcs);
        $this->assertSame(2, $deposit->items->firstWhere('jenis_sampah_id', $fan->id)->jumlah_pcs);
        $this->assertEquals(3.92, Stok::where('jenis_sampah_id', $paper->id)->value('total_berat_kg'));
        $this->assertEquals(7.45, Stok::where('jenis_sampah_id', $fan->id)->value('total_berat_kg'));
        $this->assertEquals(0, $customer->tabungan()->first()->saldo_saat_ini);
    }

    public function test_pcs_requires_positive_integer_and_weight(): void
    {
        [$customer,,$fan] = $this->fixtures();
        foreach ([null, 1.5, 0] as $pieces) {
            try {
                app(SetoranService::class)->create($customer->id, '2026-08-15', [['jenis_sampah_id' => $fan->id, 'jumlah_pcs' => $pieces, 'berat_kg' => 2]]);
                $this->fail('Validation expected');
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
        $this->expectException(ValidationException::class);
        app(SetoranService::class)->create($customer->id, '2026-08-15', [['jenis_sampah_id' => $fan->id, 'jumlah_pcs' => 2, 'berat_kg' => 0]]);
    }

    public function test_fifo_sale_credits_once_and_keeps_inventory_per_type(): void
    {
        [$first,$paper] = $this->fixtures();
        $second = $this->customer('B');
        app(SetoranService::class)->create($first->id, '2026-08-10', [['jenis_sampah_id' => $paper->id, 'berat_kg' => 3]]);
        app(SetoranService::class)->create($second->id, '2026-08-11', [['jenis_sampah_id' => $paper->id, 'berat_kg' => 4]]);
        $sale = app(PenjualanSettlementService::class)->create('2026-08-15', [['jenis_sampah_id' => $paper->id, 'berat_kg' => 5, 'harga_jual' => 10000, 'harga_nasabah' => 8000]]);
        $this->assertEquals([3, 2], $sale->items->first()->alokasi->pluck('berat_kg')->map(fn ($v) => (float) $v)->all());
        $this->assertEquals(24000, $first->tabungan->fresh()->saldo_saat_ini);
        $this->assertEquals(16000, $second->tabungan->fresh()->saldo_saat_ini);
        $this->assertEquals(2, Stok::where('jenis_sampah_id', $paper->id)->value('total_berat_kg'));
    }

    public function test_emission_is_pending_then_realized_and_snapshot_stays_fixed(): void
    {
        [$customer,$paper,,$group] = $this->fixtures();
        app(SetoranService::class)->create($customer->id, '2026-08-10', [['jenis_sampah_id' => $paper->id, 'berat_kg' => 2]]);
        app(PenjualanSettlementService::class)->create('2026-08-15', [['jenis_sampah_id' => $paper->id, 'berat_kg' => 2, 'harga_jual' => 10, 'harga_nasabah' => 8]]);
        $allocation = AlokasiPenjualan::first();
        $this->assertSame(Co2Status::Pending, $allocation->co2_status);
        $this->assertNull($allocation->co2_terealisasi);
        $group->update(['faktor_emisi_kgco2e_per_kg' => 1.25]);
        app(EmissionRealizationService::class)->realizePendingForGroup($group->fresh());
        $this->assertEquals(2.5, $allocation->fresh()->co2_terealisasi);
        $group->update(['faktor_emisi_kgco2e_per_kg' => 9]);
        $this->assertEquals(1.25, $allocation->fresh()->faktor_emisi_snapshot);
    }

    public function test_dashboard_and_operational_pages_render_with_new_relations(): void
    {
        $this->fixtures();
        $user = User::create(['name' => 'Admin', 'email' => 'test@example.com', 'password' => 'secret']);

        $this->get('/')->assertOk()->assertSee('Sampah');
        $this->actingAs($user)->get('/setor/create')->assertOk()->assertSee('Jumlah (pcs)');
        $this->actingAs($user)->get('/stok')->assertOk()->assertSee('Jenis sampah');
        $this->actingAs($user)->get('/jenis-sampah')->assertOk()->assertSee('Kelompok Material');
    }

    private function fixtures(): array
    {
        $group = KelompokMaterial::create(['nama' => 'Kertas/Karton', 'nama_normalized' => 'kertas/karton', 'is_active' => true]);
        $electronics = KelompokMaterial::create(['nama' => 'Elektronik', 'nama_normalized' => 'elektronik', 'is_active' => true]);

        return [$this->customer('A'), JenisSampah::create(['kelompok_material_id' => $group->id, 'nama' => 'Marga', 'nama_normalized' => 'marga', 'satuan_pencatatan' => 'KG', 'is_active' => true]),
            JenisSampah::create(['kelompok_material_id' => $electronics->id, 'nama' => 'Kipas Angin', 'nama_normalized' => 'kipas angin', 'satuan_pencatatan' => 'PCS', 'is_active' => true]), $group];
    }

    private function customer(string $suffix): Nasabah
    {
        return Nasabah::create(['kode' => 'N'.$suffix, 'nama' => 'Nasabah '.$suffix, 'rt' => '001', 'rw' => '001']);
    }
}
