<?php

namespace Tests\Feature;

use App\Models\AssetValueSnapshot;
use App\Models\MaterialCategory;
use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\Unit;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SnapshotTest extends TestCase
{
    use RefreshDatabase;

    private Warehouse $warehouse;
    private RawMaterial $item;

    protected function setUp(): void
    {
        parent::setUp();

        $cat = MaterialCategory::create(['name' => 'Test', 'code' => 'TST']);
        $unit = Unit::create(['name' => 'Meter', 'symbol' => 'm']);

        $this->item = RawMaterial::create([
            'code' => 'SNAP-TEST',
            'name' => 'Snapshot Test Item',
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

    public function test_snapshot_matches_material_stocks(): void
    {
        MaterialStock::create([
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 100,
            'current_avg_cost' => 50000,
            'current_asset_value' => 5000000,
        ]);

        $this->artisan('snapshot:monthly-asset-value')->assertSuccessful();

        $snapshotDate = now()->subMonth()->endOfMonth()->startOfDay();

        $this->assertDatabaseHas('asset_value_snapshots', [
            'snapshot_date' => $snapshotDate,
            'warehouse_id' => $this->warehouse->id,
            'raw_material_id' => $this->item->id,
            'qty' => 100,
            'avg_cost' => 50000,
            'asset_value' => 5000000,
        ]);
    }

    public function test_snapshot_is_immutable_after_creation(): void
    {
        MaterialStock::create([
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 100,
            'current_avg_cost' => 50000,
            'current_asset_value' => 5000000,
        ]);

        $this->artisan('snapshot:monthly-asset-value')->assertSuccessful();

        MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->update([
                'current_stock' => 50,
                'current_asset_value' => 2500000,
            ]);

        $snapshot = AssetValueSnapshot::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertEquals(100, $snapshot->qty);
        $this->assertEquals(50000, $snapshot->avg_cost);
        $this->assertEquals(5000000, $snapshot->asset_value);
    }

    public function test_snapshot_skips_if_already_exists(): void
    {
        MaterialStock::create([
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 100,
            'current_avg_cost' => 50000,
            'current_asset_value' => 5000000,
        ]);

        $this->artisan('snapshot:monthly-asset-value')->assertSuccessful();

        $this->artisan('snapshot:monthly-asset-value')
            ->expectsOutputToContain('already exists')
            ->assertSuccessful();

        $this->assertEquals(1, AssetValueSnapshot::count());
    }
}
