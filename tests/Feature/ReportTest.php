<?php

namespace Tests\Feature;

use App\Models\MaterialCategory;
use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\StockLedger;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockInService;
use App\Services\StockOutService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Warehouse $warehouse;
    private RawMaterial $item;
    private Unit $baseUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $cat = MaterialCategory::create(['name' => 'Test', 'code' => 'TST']);
        $this->baseUnit = Unit::create(['name' => 'Meter', 'symbol' => 'm']);

        $this->item = RawMaterial::create([
            'code' => 'RPT-TEST',
            'name' => 'Report Test Item',
            'material_category_id' => $cat->id,
            'unit_id' => $this->baseUnit->id,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Gudang Report',
            'code' => 'TST-RPT',
            'is_active' => true,
        ]);
    }

    public function test_current_asset_value_matches_manual_calculation(): void
    {
        MaterialStock::create([
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 50,
            'current_avg_cost' => 10000,
            'current_asset_value' => 500000,
        ]);

        $stocks = MaterialStock::all();
        $manualTotal = $stocks->sum(fn ($s) => $s->current_stock * $s->current_avg_cost);
        $systemTotal = $stocks->sum('current_asset_value');

        $this->assertEquals($manualTotal, $systemTotal);
    }

    public function test_stock_card_shows_correct_running_balance_chronological_order(): void
    {
        MaterialStock::create([
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 0,
            'current_avg_cost' => 0,
            'current_asset_value' => 0,
        ]);

        $stockInService = app(StockInService::class);
        $stockInService->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-14'),
            referenceNumber: null,
            attachment: null,
            notes: 'Pembelian pertama',
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 100, 'unit_price' => 50000, 'notes' => null],
            ],
        );

        $stockOutService = app(StockOutService::class);
        $stockOutService->store(
            warehouseId: $this->warehouse->id,
            type: 'production_usage',
            date: Carbon::parse('2026-07-15'),
            destination: 'Line 1',
            notes: 'Pemakaian pertama',
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 30, 'notes' => null],
            ],
        );

        $ledgers = StockLedger::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->get();

        $this->assertCount(2, $ledgers);
        $this->assertEquals(100, $ledgers[0]->running_qty_balance);
        $this->assertEquals(70, $ledgers[1]->running_qty_balance);
        $this->assertEquals(50000, $ledgers[1]->running_avg_cost);
    }

    public function test_stock_opname_report_excludes_cancelled_sessions(): void
    {
        $opnameFinalized = \App\Models\StockOpname::create([
            'opname_number' => 'OPN-RPT-001',
            'opname_date' => Carbon::parse('2026-07-14'),
            'warehouse_id' => $this->warehouse->id,
            'status' => 'finalized',
            'started_at' => now(),
            'finalized_at' => now(),
            'created_by' => $this->user->id,
        ]);

        $opnameCancelled = \App\Models\StockOpname::create([
            'opname_number' => 'OPN-RPT-002',
            'opname_date' => Carbon::parse('2026-07-15'),
            'warehouse_id' => $this->warehouse->id,
            'status' => 'cancelled',
            'started_at' => now(),
            'cancelled_at' => now(),
            'created_by' => $this->user->id,
        ]);

        $this->assertCount(1, \App\Models\StockOpname::where('status', 'finalized')->get());
        $this->assertCount(1, \App\Models\StockOpname::where('status', 'cancelled')->get());

        $reportQuery = \App\Models\StockOpname::where('status', 'finalized')
            ->where('opname_date', '>=', Carbon::parse('2026-07-01'))
            ->where('opname_date', '<=', Carbon::parse('2026-07-31'))
            ->get();

        $this->assertCount(1, $reportQuery);
        $this->assertTrue($reportQuery->contains('id', $opnameFinalized->id));
        $this->assertFalse($reportQuery->contains('id', $opnameCancelled->id));
    }
}
