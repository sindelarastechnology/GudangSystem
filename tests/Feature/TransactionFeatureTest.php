<?php

namespace Tests\Feature;

use App\Models\MaterialCategory;
use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\StockInTransaction;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DocumentNumberGenerator;
use App\Services\StockInService;
use App\Services\StockOutService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Warehouse $warehouse;
    private RawMaterial $item;
    private Unit $baseUnit;
    private Unit $rollUnit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $cat = MaterialCategory::create(['name' => 'Test', 'code' => 'TST']);
        $this->baseUnit = Unit::create(['name' => 'Meter', 'symbol' => 'm']);
        $this->rollUnit = Unit::create(['name' => 'Roll', 'symbol' => 'rol']);

        $this->item = RawMaterial::create([
            'code' => 'SI-TEST',
            'name' => 'Stock In Test Item',
            'material_category_id' => $cat->id,
            'unit_id' => $this->baseUnit->id,
            'is_active' => true,
        ]);

        UnitConversion::create([
            'raw_material_id' => $this->item->id,
            'from_unit_id' => $this->rollUnit->id,
            'to_unit_id' => $this->baseUnit->id,
            'conversion_factor' => 25,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Gudang SI',
            'code' => 'TST-SI',
            'is_active' => true,
        ]);
    }

    public function test_stock_in_with_base_unit(): void
    {
        $service = app(StockInService::class);

        $transaction = $service->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-15'),
            referenceNumber: 'INV-001',
            attachment: null,
            notes: 'Test purchase',
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 100,
                    'unit_price' => 50000,
                    'notes' => null,
                ],
            ],
        );

        $this->assertNotNull($transaction);
        $this->assertStringStartsWith('SIN-', $transaction->transaction_number);
        $this->assertEquals(1, $transaction->details()->count());

        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(100, $stock->current_stock);
        $this->assertEquals(50000, $stock->current_avg_cost);
    }

    public function test_stock_in_with_converted_unit(): void
    {
        $service = app(StockInService::class);

        $transaction = $service->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-15'),
            referenceNumber: null,
            attachment: null,
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->rollUnit->id,
                    'qty' => 4,
                    'unit_price' => 1250000,
                    'notes' => null,
                ],
            ],
        );

        $detail = $transaction->details()->first();
        $this->assertEquals(4, $detail->qty);
        $this->assertEquals(100, $detail->qty_base);
        $this->assertEquals(5000000, $detail->subtotal);

        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(100, $stock->current_stock);
        $this->assertEquals(50000, $stock->current_avg_cost, 'Avg cost should be 50000 per meter (5000000 / 100)');
        $this->assertEquals(5000000, $stock->current_asset_value);
    }

    public function test_stock_in_multiple_items(): void
    {
        $service = app(StockInService::class);
        $item2 = RawMaterial::create([
            'code' => 'SI-TEST2',
            'name' => 'Test Item 2',
            'material_category_id' => MaterialCategory::first()->id,
            'unit_id' => $this->baseUnit->id,
            'is_active' => true,
        ]);

        $service->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-15'),
            referenceNumber: null,
            attachment: null,
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 50,
                    'unit_price' => 10000,
                    'notes' => null,
                ],
                [
                    'raw_material_id' => $item2->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 30,
                    'unit_price' => 20000,
                    'notes' => null,
                ],
            ],
        );

        $stock1 = MaterialStock::where('raw_material_id', $this->item->id)->first();
        $stock2 = MaterialStock::where('raw_material_id', $item2->id)->first();

        $this->assertEquals(50, $stock1->current_stock);
        $this->assertEquals(30, $stock2->current_stock);
    }

    public function test_stock_in_rejects_invalid_conversion(): void
    {
        $service = app(StockInService::class);
        $unknownUnit = Unit::create(['name' => 'Dus', 'symbol' => 'dus']);

        $this->expectException(\InvalidArgumentException::class);
        $service->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-15'),
            referenceNumber: null,
            attachment: null,
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $unknownUnit->id,
                    'qty' => 10,
                    'unit_price' => 50000,
                    'notes' => null,
                ],
            ],
        );
    }

    public function test_stock_out_full_flow(): void
    {
        $stockIn = app(StockInService::class);
        $stockOut = app(StockOutService::class);

        $stockIn->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-15'),
            referenceNumber: null,
            attachment: null,
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 100,
                    'unit_price' => 50000,
                    'notes' => null,
                ],
            ],
        );

        $transaction = $stockOut->store(
            warehouseId: $this->warehouse->id,
            type: 'production_usage',
            date: Carbon::parse('2026-07-16'),
            destination: 'Line 1',
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 30,
                    'notes' => null,
                ],
            ],
        );

        $this->assertStringStartsWith('SOUT-', $transaction->transaction_number);
        $detail = $transaction->details()->first();
        $this->assertEquals(50000, $detail->cost_at_issue);

        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(70, $stock->current_stock);
        $this->assertEquals(50000, $stock->current_avg_cost);
    }

    public function test_stock_out_rejects_insufficient_stock(): void
    {
        $stockOut = app(StockOutService::class);

        $this->expectException(\InvalidArgumentException::class);
        $stockOut->store(
            warehouseId: $this->warehouse->id,
            type: 'production_usage',
            date: Carbon::parse('2026-07-16'),
            destination: null,
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 999,
                    'notes' => null,
                ],
            ],
        );
    }

    public function test_stock_out_rejects_locked_warehouse(): void
    {
        $this->warehouse->update(['is_locked' => true, 'locked_at' => now()]);

        $stockOut = app(StockOutService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('opname');
        $stockOut->store(
            warehouseId: $this->warehouse->id,
            type: 'production_usage',
            date: Carbon::parse('2026-07-16'),
            destination: null,
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 1,
                    'notes' => null,
                ],
            ],
        );
    }

    public function test_transfer_normal_flow(): void
    {
        $wh2 = Warehouse::create(['name' => 'Gudang Tujuan', 'code' => 'TST-TUJUAN', 'is_active' => true]);

        $stockIn = app(StockInService::class);
        $stockIn->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-15'),
            referenceNumber: null,
            attachment: null,
            notes: null,
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 100, 'unit_price' => 50000, 'notes' => null],
            ],
        );

        $transfer = app(\App\Services\StockTransferService::class)->store(
            fromWarehouseId: $this->warehouse->id,
            toWarehouseId: $wh2->id,
            date: Carbon::parse('2026-07-16'),
            notes: 'Test transfer',
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 30, 'notes' => null],
            ],
        );

        $this->assertStringStartsWith('TRF-', $transfer->transfer_number);
        $detail = $transfer->details()->first();
        $this->assertEquals(50000, $detail->cost_at_transfer);
        $this->assertEquals(30, $detail->qty_base);

        $stockFrom = MaterialStock::where('raw_material_id', $this->item->id)->where('warehouse_id', $this->warehouse->id)->first();
        $stockTo = MaterialStock::where('raw_material_id', $this->item->id)->where('warehouse_id', $wh2->id)->first();

        $this->assertEquals(70, $stockFrom->current_stock);
        $this->assertEquals(50000, $stockFrom->current_avg_cost);
        $this->assertEquals(30, $stockTo->current_stock);
        $this->assertEquals(50000, $stockTo->current_avg_cost);
    }

    public function test_transfer_rejects_same_warehouse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(\App\Services\StockTransferService::class)->store(
            fromWarehouseId: $this->warehouse->id,
            toWarehouseId: $this->warehouse->id,
            date: Carbon::parse('2026-07-16'),
            notes: null,
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 1, 'notes' => null],
            ],
        );
    }

    public function test_transfer_rejects_no_stock(): void
    {
        $wh2 = Warehouse::create(['name' => 'Gudang Tujuan 2', 'code' => 'TST-TUJ2', 'is_active' => true]);

        $this->expectException(\InvalidArgumentException::class);
        app(\App\Services\StockTransferService::class)->store(
            fromWarehouseId: $this->warehouse->id,
            toWarehouseId: $wh2->id,
            date: Carbon::parse('2026-07-16'),
            notes: null,
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 1, 'notes' => null],
            ],
        );
    }

    public function test_transfer_rejects_locked_warehouse(): void
    {
        $wh2 = Warehouse::create(['name' => 'Gudang Tujuan 3', 'code' => 'TST-TUJ3', 'is_active' => true]);
        $this->warehouse->update(['is_locked' => true, 'locked_at' => now()]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('opname');
        app(\App\Services\StockTransferService::class)->store(
            fromWarehouseId: $this->warehouse->id,
            toWarehouseId: $wh2->id,
            date: Carbon::parse('2026-07-16'),
            notes: null,
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 1, 'notes' => null],
            ],
        );
    }

    public function test_transfer_insufficient_stock(): void
    {
        $wh2 = Warehouse::create(['name' => 'Gudang Tujuan 4', 'code' => 'TST-TUJ4', 'is_active' => true]);

        app(StockInService::class)->store(
            warehouseId: $this->warehouse->id,
            supplierId: null,
            type: 'purchase',
            date: Carbon::parse('2026-07-15'),
            referenceNumber: null,
            attachment: null,
            notes: null,
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 10, 'unit_price' => 50000, 'notes' => null],
            ],
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('tidak cukup');
        app(\App\Services\StockTransferService::class)->store(
            fromWarehouseId: $this->warehouse->id,
            toWarehouseId: $wh2->id,
            date: Carbon::parse('2026-07-16'),
            notes: null,
            createdBy: $this->user,
            details: [
                ['raw_material_id' => $this->item->id, 'unit_id' => $this->baseUnit->id, 'qty' => 99, 'notes' => null],
            ],
        );
    }
}
