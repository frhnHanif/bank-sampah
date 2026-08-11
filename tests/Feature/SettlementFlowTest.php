<?php

namespace Tests\Feature;

use App\Enums\Co2Status;
use App\Enums\SettlementStatus;
use App\Models\AlokasiPenjualan;
use App\Models\FaktorEmisi;
use App\Models\KategoriSampah;
use App\Models\LegacyInventory;
use App\Models\MutasiKas;
use App\Models\MutasiTabungan;
use App\Models\Nasabah;
use App\Models\Stok;
use App\Models\Tabungan;
use App\Models\User;
use App\Services\EmissionRealizationService;
use App\Services\PenjualanSettlementService;
use App\Services\SetoranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SettlementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_page_is_portable_to_sqlite_and_shows_separated_metrics(): void
    {
        $customer = $this->customer('Portabel', 5000);
        MutasiKas::create([
            'tanggal' => '2026-08-10',
            'tipe' => 'pengeluaran',
            'kategori' => 'Operasional',
            'nominal' => 1000,
            'keterangan' => 'Uji portabilitas tahun',
        ]);

        $this->actingAs(User::factory()->create());
        $response = $this->get('/keuangan');

        $response->assertOk()
            ->assertSee('Kas Aktual')
            ->assertSee('Kewajiban Tabungan Nasabah')
            ->assertSee('Laba Setelah Operasional');
        $this->get(route('tabungan.show', $customer))->assertOk()->assertSee('Buku Tabungan Nasabah');
    }

    public function test_setoran_baru_hanya_menambah_pending_stock_tanpa_saldo_atau_mutasi(): void
    {
        $customer = $this->customer('A', 50000);
        $category = $this->category('Besi');

        $deposit = app(SetoranService::class)->create($customer->id, '2026-08-10', [
            ['kategori_id' => $category->id, 'berat' => 3],
        ]);

        $item = $deposit->items->first();
        $this->assertSame(2, $deposit->flow_version);
        $this->assertSame(SettlementStatus::Pending, $item->status);
        $this->assertEquals(0, (float) $item->berat_teralokasi_kg);
        $this->assertEquals(3, (float) Stok::where('kategori_id', $category->id)->value('total_berat_kg'));
        $this->assertEquals(50000, (float) $customer->tabungan->fresh()->saldo_saat_ini);
        $this->assertSame(0, MutasiTabungan::where('nasabah_id', $customer->id)
            ->where('ref_transaksi_setor_id', $deposit->id)->count());
    }

    public function test_fifo_full_and_partial_settlement_matches_main_acceptance_scenario(): void
    {
        $category = $this->category('Besi');
        $a = $this->customer('A');
        $b = $this->customer('B');
        $c = $this->customer('C');
        $deposits = app(SetoranService::class);
        $itemA = $deposits->create($a->id, '2026-08-10', [['kategori_id' => $category->id, 'berat' => 3]])->items->first();
        $itemB = $deposits->create($b->id, '2026-08-10', [['kategori_id' => $category->id, 'berat' => 4]])->items->first();
        $itemC = $deposits->create($c->id, '2026-08-10', [['kategori_id' => $category->id, 'berat' => 5]])->items->first();

        $sale = $this->sale([['kategori_id' => $category->id, 'berat' => 10, 'harga_jual' => 10000, 'harga_nasabah' => 8000]]);

        $this->assertEquals(100000, (float) $sale->total_nilai);
        $this->assertEquals(80000, (float) $sale->total_hak_nasabah);
        $this->assertEquals(20000, (float) $sale->total_margin_kotor);
        $this->assertEquals([3.0, 4.0, 3.0], $sale->items->first()->alokasi->map(fn ($a) => (float) $a->berat_kg)->all());
        $this->assertSame(SettlementStatus::Settled, $itemA->fresh()->status);
        $this->assertSame(SettlementStatus::Settled, $itemB->fresh()->status);
        $this->assertSame(SettlementStatus::Partial, $itemC->fresh()->status);
        $this->assertEquals(3, (float) $itemC->fresh()->berat_teralokasi_kg);
        $this->assertEquals(24000, (float) $a->tabungan->fresh()->saldo_saat_ini);
        $this->assertEquals(32000, (float) $b->tabungan->fresh()->saldo_saat_ini);
        $this->assertEquals(24000, (float) $c->tabungan->fresh()->saldo_saat_ini);
        $this->assertEquals(2, (float) Stok::where('kategori_id', $category->id)->value('total_berat_kg'));
        $this->assertEquals(100000, (float) MutasiKas::where('ref_transaksi_jual_id', $sale->id)->value('nominal'));
        $this->assertSame(3, MutasiTabungan::where('ref_transaksi_jual_id', $sale->id)->count());
    }

    public function test_multi_category_header_status_is_derived_from_item_statuses(): void
    {
        $customer = $this->customer('A');
        $iron = $this->category('Besi');
        $plastic = $this->category('Plastik');
        $deposit = app(SetoranService::class)->create($customer->id, '2026-08-10', [
            ['kategori_id' => $iron->id, 'berat' => 2], ['kategori_id' => $plastic->id, 'berat' => 5],
        ]);
        $this->sale([['kategori_id' => $iron->id, 'berat' => 2, 'harga_jual' => 10000, 'harga_nasabah' => 8000]]);
        $deposit->load('items');

        $this->assertSame('PARTIAL', $deposit->settlement_status);
        $this->assertSame(SettlementStatus::Settled, $deposit->items->firstWhere('kategori_id', $iron->id)->fresh()->status);
        $this->assertSame(SettlementStatus::Pending, $deposit->items->firstWhere('kategori_id', $plastic->id)->fresh()->status);
    }

    public function test_oversell_and_customer_price_above_collector_price_are_rejected_without_side_effects(): void
    {
        $customer = $this->customer('A');
        $category = $this->category('Besi');
        app(SetoranService::class)->create($customer->id, '2026-08-10', [['kategori_id' => $category->id, 'berat' => 3]]);

        try {
            $this->sale([['kategori_id' => $category->id, 'berat' => 4, 'harga_jual' => 10000, 'harga_nasabah' => 8000]]);
            $this->fail('Oversell seharusnya gagal.');
        } catch (ValidationException) {
        }
        $this->assertSame(0, AlokasiPenjualan::count());
        $this->assertEquals(3, (float) Stok::first()->total_berat_kg);

        $this->expectException(ValidationException::class);
        $this->sale([['kategori_id' => $category->id, 'berat' => 2, 'harga_jual' => 10000, 'harga_nasabah' => 12000]]);
    }

    public function test_unknown_factor_does_not_block_finance_and_can_be_realized_later_idempotently(): void
    {
        $customer = $this->customer('A');
        $category = $this->category('Dinamo Bekas');
        app(SetoranService::class)->create($customer->id, '2026-08-10', [['kategori_id' => $category->id, 'berat' => 2]]);
        $sale = $this->sale([['kategori_id' => $category->id, 'berat' => 2, 'harga_jual' => 10000, 'harga_nasabah' => 8000]]);
        $allocation = AlokasiPenjualan::first();

        $this->assertSame(Co2Status::Pending, $allocation->co2_status);
        $this->assertNull($allocation->co2_terealisasi);
        $this->assertEquals(16000, (float) $customer->tabungan->fresh()->saldo_saat_ini);
        $financialCounts = [MutasiTabungan::count(), MutasiKas::count()];

        $factor = FaktorEmisi::create(['nama_material' => 'Material terverifikasi', 'faktor_kgco2e_per_kg' => 1.234567, 'sumber' => 'Sumber uji eksplisit']);
        $category->update(['faktor_emisi_id' => $factor->id]);
        $service = app(EmissionRealizationService::class);
        $this->assertSame(1, $service->realizePendingForCategory($category->fresh('faktorEmisi')));
        $this->assertSame(0, $service->realizePendingForCategory($category->fresh('faktorEmisi')));
        $allocation->refresh();
        $this->assertSame(Co2Status::Realized, $allocation->co2_status);
        $this->assertEqualsWithDelta(2.469134, (float) $allocation->co2_terealisasi, 0.000001);
        $this->assertEquals(1.234567, (float) $allocation->faktor_emisi_snapshot);
        $factor->update(['faktor_kgco2e_per_kg' => 9.999999]);
        $this->assertEquals(1.234567, (float) $allocation->fresh()->faktor_emisi_snapshot);
        $this->assertSame($financialCounts, [MutasiTabungan::count(), MutasiKas::count()]);
        $this->assertEquals(16000, (float) $customer->tabungan->fresh()->saldo_saat_ini);
        $this->assertEqualsWithDelta(2.469134, (float) $sale->fresh()->total_co2_terealisasi, 0.000001);
    }

    public function test_legacy_inventory_is_consumed_first_without_double_credit(): void
    {
        $category = $this->category('Besi');
        Stok::create(['kategori_id' => $category->id, 'total_berat_kg' => 5]);
        $legacy = LegacyInventory::create(['kategori_id' => $category->id, 'cutover_at' => now(), 'berat_awal_kg' => 5, 'berat_tersisa_kg' => 5, 'cost_basis_per_kg' => 6000, 'total_cost_basis_awal' => 30000]);

        $sale = $this->sale([['kategori_id' => $category->id, 'berat' => 3, 'harga_jual' => 10000, 'harga_nasabah' => 8000]]);

        $this->assertEquals(2, (float) $legacy->fresh()->berat_tersisa_kg);
        $this->assertEquals(18000, (float) $sale->total_cost_basis);
        $this->assertEquals(12000, (float) $sale->total_margin_kotor);
        $this->assertSame(0, MutasiTabungan::count());
        $this->assertSame('LEGACY_OPENING', AlokasiPenjualan::first()->sumber_tipe->value);
    }

    public function test_mixed_legacy_and_new_stock_only_credits_new_fifo_weight(): void
    {
        $category = $this->category('Besi');
        $customer = $this->customer('A');
        app(SetoranService::class)->create($customer->id, '2026-08-10', [['kategori_id' => $category->id, 'berat' => 3]]);
        Stok::where('kategori_id', $category->id)->increment('total_berat_kg', 2);
        LegacyInventory::create(['kategori_id' => $category->id, 'cutover_at' => now()->subDay(), 'berat_awal_kg' => 2, 'berat_tersisa_kg' => 2, 'cost_basis_per_kg' => 6000, 'total_cost_basis_awal' => 12000]);

        $sale = $this->sale([['kategori_id' => $category->id, 'berat' => 4, 'harga_jual' => 10000, 'harga_nasabah' => 8000]]);
        $item = $customer->transaksiSetor()->first()->items()->first();

        $this->assertEquals(['LEGACY_OPENING', 'SETORAN'], $sale->items->first()->alokasi->map(fn ($a) => $a->sumber_tipe->value)->all());
        $this->assertEquals(16000, (float) $customer->tabungan->fresh()->saldo_saat_ini);
        $this->assertSame(SettlementStatus::Partial, $item->fresh()->status);
        $this->assertEquals(2, (float) $item->fresh()->berat_teralokasi_kg);
    }

    public function test_failure_on_later_cart_item_rolls_back_entire_sale(): void
    {
        $customer = $this->customer('A');
        $a = $this->category('A');
        $b = $this->category('B');
        app(SetoranService::class)->create($customer->id, '2026-08-10', [
            ['kategori_id' => $a->id, 'berat' => 1], ['kategori_id' => $b->id, 'berat' => 1],
        ]);
        try {
            $this->sale([
                ['kategori_id' => $a->id, 'berat' => 1, 'harga_jual' => 10, 'harga_nasabah' => 8],
                ['kategori_id' => $b->id, 'berat' => 2, 'harga_jual' => 10, 'harga_nasabah' => 8],
            ]);
            $this->fail('Transaksi seharusnya rollback.');
        } catch (ValidationException) {
        }

        $this->assertDatabaseCount('transaksi_jual', 0);
        $this->assertDatabaseCount('alokasi_penjualan', 0);
        $this->assertDatabaseCount('mutasi_kas', 0);
        $this->assertDatabaseCount('mutasi_tabungan', 0);
        $this->assertEquals(2, (float) Stok::sum('total_berat_kg'));
        $this->assertEquals(0, (float) $customer->tabungan->fresh()->saldo_saat_ini);
    }

    public function test_quick_create_normalizes_names_and_restores_soft_deleted_category(): void
    {
        $user = User::factory()->create();
        $first = $this->actingAs($user)->postJson(route('kategori.quick-store'), ['nama' => '  Dinamo   Bekas  ']);
        $first->assertCreated()->assertJsonPath('kategori.nama', 'Dinamo Bekas');
        $category = KategoriSampah::first();
        $category->delete();
        $this->actingAs($user)->postJson(route('kategori.quick-store'), ['nama' => 'DINAMO BEKAS'])->assertCreated();
        $this->assertSame(1, KategoriSampah::withTrashed()->count());
        $this->assertFalse($category->fresh()->trashed());
    }

    public function test_emission_factor_accepts_at_most_three_decimal_places_and_shows_common_templates(): void
    {
        $this->withoutMiddleware();

        $this->post(route('faktor-emisi.store'), [
            'nama_material' => 'Kaca',
            'faktor_kgco2e_per_kg' => '1.234',
            'aktif' => true,
        ])->assertSessionHasNoErrors();

        $this->post(route('faktor-emisi.store'), [
            'nama_material' => 'Plastik Tebal',
            'faktor_kgco2e_per_kg' => '1.2345',
            'aktif' => true,
        ])->assertSessionHasErrors('faktor_kgco2e_per_kg');

        $this->get(route('faktor-emisi.index'))
            ->assertOk()
            ->assertSeeInOrder(['Kaca', 'Plastik Tebal', 'Plastik Tipis', 'Kaleng', 'Kertas Tebal', 'Kertas Tipis'])
            ->assertSee('1,234')
            ->assertDontSee('legacy', false);
    }

    public function test_inventory_and_balance_audit_passes_after_normal_flow(): void
    {
        $customer = $this->customer('A');
        $category = $this->category('Besi');
        app(SetoranService::class)->create($customer->id, '2026-08-10', [['kategori_id' => $category->id, 'berat' => 3]]);
        $this->sale([['kategori_id' => $category->id, 'berat' => 2, 'harga_jual' => 10000, 'harga_nasabah' => 8000]]);
        $this->assertSame(0, Artisan::call('bank-sampah:audit'));
    }

    public function test_pending_deposit_is_not_withdrawable_available_balance(): void
    {
        $user = User::factory()->create();
        $customer = $this->customer('A', 10000);
        $category = $this->category('Besi');
        app(SetoranService::class)->create($customer->id, '2026-08-10', [
            ['kategori_id' => $category->id, 'berat' => 100],
        ]);

        $this->actingAs($user)->post(route('tabungan.tarik', $customer), [
            'tanggal' => '2026-08-11',
            'jumlah' => '12.000',
        ])->assertSessionHasErrors('jumlah');

        $this->assertEquals(10000, (float) $customer->tabungan->fresh()->saldo_saat_ini);
        $this->assertSame(0, MutasiKas::where('tipe', 'pengeluaran')->count());
    }

    private function customer(string $name, float $balance = 0): Nasabah
    {
        static $sequence = 0;
        $sequence++;
        $customer = Nasabah::create(['kode' => '0101'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT), 'nama' => $name, 'rt' => '01', 'rw' => '01']);
        Tabungan::create(['nasabah_id' => $customer->id, 'saldo_saat_ini' => $balance]);
        if ($balance > 0) {
            MutasiTabungan::create(['nasabah_id' => $customer->id, 'tanggal' => '2026-01-01', 'jenis' => 'kredit', 'jumlah' => $balance, 'keterangan' => 'Saldo awal pengujian']);
        }

        return $customer->load('tabungan');
    }

    private function category(string $name): KategoriSampah
    {
        return KategoriSampah::create(['nama' => $name, 'nama_normalized' => mb_strtolower($name), 'faktor_emisi_id' => null]);
    }

    private function sale(array $items)
    {
        return app(PenjualanSettlementService::class)->create('2026-08-15', $items, 'Pengepul pengujian');
    }
}
