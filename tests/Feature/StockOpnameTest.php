<?php

namespace Tests\Feature;

use App\Models\MaterialCategory;
use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\StockLedger;
use App\Models\StockOpname;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockInService;
use App\Services\StockOpnameService;
use App\Services\StockOutService;
use App\Services\StockTransferService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Warehouse $warehouse;
    private RawMaterial $item;
    private Unit $baseUnit;
    private StockOpnameService $opnameService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $cat = MaterialCategory::create(['name' => 'Test', 'code' => 'TST']);
        $this->baseUnit = Unit::create(['name' => 'Meter', 'symbol' => 'm']);

        $this->item = RawMaterial::create([
            'code' => 'OPNAME-TEST',
            'name' => 'Opname Test Item',
            'material_category_id' => $cat->id,
            'unit_id' => $this->baseUnit->id,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Gudang Opname',
            'code' => 'TST-OPN',
            'is_active' => true,
        ]);

        MaterialStock::create([
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 100,
            'current_avg_cost' => 50000,
            'current_asset_value' => 5000000,
            'min_stock' => 10,
        ]);

        $this->opnameService = app(StockOpnameService::class);
    }

    public function test_open_session_on_locked_warehouse_rejected(): void
    {
        $this->warehouse->update(['is_locked' => true, 'locked_at' => now()]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('sedang dalam proses opname');

        $this->opnameService->openSession(
            $this->warehouse->id,
            Carbon::parse('2026-07-15'),
            'Test opname'
        );
    }

    public function test_transactions_blocked_during_opname(): void
    {
        $opname = $this->opnameService->openSession(
            $this->warehouse->id,
            Carbon::parse('2026-07-15'),
            null
        );

        $this->assertTrue($opname->isCounting());
        $this->warehouse->refresh();
        $this->assertTrue($this->warehouse->is_locked);

        $stockInService = app(StockInService::class);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('opname');
        $stockInService->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-16'),
            referenceNumber: null,
            attachment: null,
            notes: null,
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 10, 'unit_price' => 50000, 'notes' => null],
            ],
        );
    }

    public function test_finalize_refetches_system_qty_with_lock(): void
    {
        $opname = $this->opnameService->openSession(
            $this->warehouse->id,
            Carbon::parse('2026-07-15'),
            null
        );

        $this->opnameService->saveDraftDetails($opname, [
            [
                'raw_material_id' => $this->item->id,
                'physical_qty_unit_id' => $this->baseUnit->id,
                'physical_qty' => 120,
                'notes' => null,
            ],
        ]);

        MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->update(['current_stock' => 105]);

        $this->opnameService->finalize($opname);

        $opname->refresh();
        $this->assertTrue($opname->isFinalized());
        
        $detail = $opname->details->first();
        $this->assertEquals(105, $detail->system_qty);
        $this->assertEquals(120, $detail->physical_qty_base);
        $this->assertEquals(15, $detail->difference_qty);

        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(120, $stock->current_stock);

        $this->assertDatabaseHas('stock_ledgers', [
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'source_type' => 'opname_adjustment',
            'direction' => 'in',
            'qty' => 15,
        ]);

        $this->warehouse->refresh();
        $this->assertFalse($this->warehouse->is_locked);
        $this->assertNull($this->warehouse->locked_by_opname_id);
    }

    public function test_cancel_no_ledger_no_stock_change(): void
    {
        $opname = $this->opnameService->openSession(
            $this->warehouse->id,
            Carbon::parse('2026-07-15'),
            null
        );

        $this->opnameService->saveDraftDetails($opname, [
            [
                'raw_material_id' => $this->item->id,
                'physical_qty_unit_id' => $this->baseUnit->id,
                'physical_qty' => 80,
                'notes' => null,
            ],
        ]);

        $stockBefore = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $ledgerCountBefore = StockLedger::count();

        $this->opnameService->cancel($opname);

        $opname->refresh();
        $this->assertTrue($opname->isCancelled());
        $this->assertNotNull($opname->cancelled_at);

        $stockAfter = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        
        $this->assertEquals($stockBefore->current_stock, $stockAfter->current_stock);
        $this->assertEquals($stockBefore->current_avg_cost, $stockAfter->current_avg_cost);
        $this->assertEquals($ledgerCountBefore, StockLedger::count());

        $this->warehouse->refresh();
        $this->assertFalse($this->warehouse->is_locked);
    }

    public function test_auto_cancel_stale_sessions(): void
    {
        $staleOpname = StockOpname::create([
            'opname_number' => 'OPN-TEST-001',
            'opname_date' => Carbon::parse('2026-07-10'),
            'warehouse_id' => $this->warehouse->id,
            'status' => 'counting',
            'started_at' => now()->subHours(25),
            'created_by' => $this->user->id,
        ]);

        $this->warehouse->update([
            'is_locked' => true,
            'locked_by_opname_id' => $staleOpname->id,
            'locked_at' => now()->subHours(25),
        ]);

        $this->artisan('opname:auto-cancel-stale')
            ->expectsOutput('Found 1 stale opname session(s). Cancelling...')
            ->assertSuccessful();

        $staleOpname->refresh();
        $this->assertTrue($staleOpname->isCancelled());

        $this->warehouse->refresh();
        $this->assertFalse($this->warehouse->is_locked);
        $this->assertNull($this->warehouse->locked_by_opname_id);
    }

    public function test_cannot_cancel_finalized_session(): void
    {
        $opname = $this->opnameService->openSession(
            $this->warehouse->id,
            Carbon::parse('2026-07-15'),
            null
        );

        $this->opnameService->saveDraftDetails($opname, [
            [
                'raw_material_id' => $this->item->id,
                'physical_qty_unit_id' => $this->baseUnit->id,
                'physical_qty' => 100,
                'notes' => null,
            ],
        ]);

        $this->opnameService->finalize($opname);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tidak dapat membatalkan');
        
        $this->opnameService->cancel($opname);
    }

    public function test_new_item_without_material_stocks_can_be_added(): void
    {
        $newItem = RawMaterial::create([
            'code' => 'NEW-ITEM',
            'name' => 'New Item Without Stock',
            'material_category_id' => MaterialCategory::first()->id,
            'unit_id' => $this->baseUnit->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseMissing('material_stocks', [
            'raw_material_id' => $newItem->id,
            'warehouse_id' => $this->warehouse->id,
        ]);

        $opname = $this->opnameService->openSession(
            $this->warehouse->id,
            Carbon::parse('2026-07-15'),
            null
        );

        $this->opnameService->saveDraftDetails($opname, [
            [
                'raw_material_id' => $newItem->id,
                'physical_qty_unit_id' => $this->baseUnit->id,
                'physical_qty' => 50,
                'notes' => null,
            ],
        ]);

        $this->opnameService->finalize($opname);

        $this->assertDatabaseHas('material_stocks', [
            'raw_material_id' => $newItem->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 50,
        ]);

        $this->assertDatabaseHas('stock_ledgers', [
            'raw_material_id' => $newItem->id,
            'warehouse_id' => $this->warehouse->id,
            'source_type' => 'opname_adjustment',
            'direction' => 'in',
            'qty' => 50,
        ]);
    }
}
