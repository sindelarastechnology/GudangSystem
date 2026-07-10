<?php

namespace Tests\Unit\Services;

use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\UnitConversion;
use App\Models\Warehouse;
use App\Services\StockLedgerService;
use App\Services\DocumentNumberGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockLedgerService $service;
    private RawMaterial $item;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StockLedgerService::class);

        $cat = \App\Models\MaterialCategory::create(['name' => 'Test', 'code' => 'TST']);
        $unit = \App\Models\Unit::create(['name' => 'Meter', 'symbol' => 'm']);

        $this->item = RawMaterial::create([
            'code' => 'TST-001',
            'name' => 'Test Item',
            'material_category_id' => $cat->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Gudang',
            'code' => 'TST-GDG',
            'is_active' => true,
        ]);
    }

    public function test_convert_to_base_unit_same_unit(): void
    {
        $result = $this->service->convertToBaseUnit($this->item, $this->item->unit_id, 10);
        $this->assertEquals(10, $result);
    }

    public function test_convert_to_base_unit_with_conversion(): void
    {
        $rollUnit = \App\Models\Unit::create(['name' => 'Roll', 'symbol' => 'rol']);
        UnitConversion::create([
            'raw_material_id' => $this->item->id,
            'from_unit_id' => $rollUnit->id,
            'to_unit_id' => $this->item->unit_id,
            'conversion_factor' => 25,
        ]);

        $result = $this->service->convertToBaseUnit($this->item, $rollUnit->id, 3);
        $this->assertEquals(75, $result);
    }

    public function test_convert_to_base_unit_no_conversion_throws(): void
    {
        $dusUnit = \App\Models\Unit::create(['name' => 'Dus', 'symbol' => 'dus']);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->convertToBaseUnit($this->item, $dusUnit->id, 1);
    }

    public function test_record_in_lazy_creates_stock(): void
    {
        $this->assertDatabaseMissing('material_stocks', [
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
        ]);

        $this->service->recordIn($this->item, $this->warehouse, 100, 50000, 'test', 1, Carbon::now());

        $this->assertDatabaseHas('material_stocks', [
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 100,
            'current_avg_cost' => 50000,
        ]);
    }

    public function test_record_in_moving_average(): void
    {
        $this->service->recordIn($this->item, $this->warehouse, 100, 50000, 'test', 1, Carbon::now());
        $this->service->recordIn($this->item, $this->warehouse, 50, 60000, 'test', 2, Carbon::now());

        $expectedAvg = round((100 * 50000 + 50 * 60000) / 150, 4);
        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertEquals(150, $stock->current_stock);
        $this->assertEquals($expectedAvg, $stock->current_avg_cost);
    }

    public function test_record_out_does_not_change_avg_cost(): void
    {
        $this->service->recordIn($this->item, $this->warehouse, 100, 50000, 'test', 1, Carbon::now());
        $avgBefore = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('current_avg_cost');

        $this->service->recordOut($this->item, $this->warehouse, 30, 'test', 2, Carbon::now());

        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertEquals(70, $stock->current_stock);
        $this->assertEquals($avgBefore, $stock->current_avg_cost);
    }

    public function test_record_out_returns_cost_at_issue(): void
    {
        $this->service->recordIn($this->item, $this->warehouse, 100, 50000, 'test', 1, Carbon::now());

        $cost = $this->service->recordOut($this->item, $this->warehouse, 30, 'test', 2, Carbon::now());
        $this->assertEquals(50000, $cost);
    }

    public function test_record_out_insufficient_stock_throws(): void
    {
        $this->service->recordIn($this->item, $this->warehouse, 10, 50000, 'test', 1, Carbon::now());

        $this->expectException(\InvalidArgumentException::class);
        $this->service->recordOut($this->item, $this->warehouse, 99, 'test', 2, Carbon::now());
    }

    public function test_record_out_no_stock_throws(): void
    {
        $otherWarehouse = Warehouse::create(['name' => 'Lain', 'code' => 'Lain', 'is_active' => true]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->recordOut($this->item, $otherWarehouse, 1, 'test', 1, Carbon::now());
    }

    public function test_reset_notified_at_when_stock_above_min(): void
    {
        $stock = MaterialStock::create([
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'min_stock' => 10,
            'current_stock' => 5,
            'last_notified_at' => now(),
        ]);

        $this->service->recordIn($this->item, $this->warehouse, 20, 1000, 'test', 1, Carbon::now());

        $stock->refresh();
        $this->assertNull($stock->last_notified_at);
    }

    public function test_ledger_entries_created(): void
    {
        $this->service->recordIn($this->item, $this->warehouse, 100, 50000, 'test', 1, Carbon::now());
        $this->service->recordOut($this->item, $this->warehouse, 30, 'test', 2, Carbon::now());

        $this->assertDatabaseCount('stock_ledgers', 2);
    }
}
