<?php

namespace Tests\Feature;

use App\Models\MaterialCategory;
use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\LowStockNotification;
use App\Services\StockInService;
use App\Services\StockOutService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Warehouse $warehouse;
    private RawMaterial $item;
    private Unit $baseUnit;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');
        $this->actingAs($this->user);

        $cat = MaterialCategory::create(['name' => 'Test', 'code' => 'TST']);
        $this->baseUnit = Unit::create(['name' => 'Meter', 'symbol' => 'm']);

        $this->item = RawMaterial::create([
            'code' => 'NOTIF-TEST',
            'name' => 'Notification Test Item',
            'material_category_id' => $cat->id,
            'unit_id' => $this->baseUnit->id,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Gudang Notif',
            'code' => 'TST-NOTIF',
            'is_active' => true,
        ]);

        MaterialStock::create([
            'raw_material_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => 100,
            'current_avg_cost' => 50000,
            'current_asset_value' => 5000000,
            'min_stock' => 20,
            'last_notified_at' => null,
        ]);
    }

    public function test_notification_sent_when_stock_drops_below_minimum(): void
    {
        Notification::fake();

        $stockOutService = app(StockOutService::class);
        $stockOutService->store(
            warehouseId: $this->warehouse->id,
            type: 'production_usage',
            date: Carbon::parse('2026-07-15'),
            destination: 'Test',
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 85,
                    'notes' => null,
                ],
            ],
        );

        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertEquals(15, $stock->current_stock);
        $this->assertNotNull($stock->last_notified_at);

        Notification::assertSentTo(
            [$this->user],
            LowStockNotification::class,
            function ($notification) use ($stock) {
                return $notification->stock->id === $stock->id;
            }
        );
    }

    public function test_notification_not_sent_during_cooldown(): void
    {
        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        
        $stock->update([
            'current_stock' => 15,
            'last_notified_at' => now()->subDays(1),
        ]);

        Notification::fake();

        $stockOutService = app(StockOutService::class);
        $stockOutService->store(
            warehouseId: $this->warehouse->id,
            type: 'production_usage',
            date: Carbon::parse('2026-07-16'),
            destination: 'Test',
            notes: null,
            createdBy: $this->user,
            details: [
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 2,
                    'notes' => null,
                ],
            ],
        );

        $stock->refresh();
        $this->assertEquals(13, $stock->current_stock);

        Notification::assertNothingSent();
    }

    public function test_last_notified_at_reset_when_stock_increases_above_minimum(): void
    {
        $stock = MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        
        $stock->update([
            'current_stock' => 15,
            'last_notified_at' => now()->subDays(2),
        ]);

        $this->assertNotNull($stock->last_notified_at);

        $stockInService = app(StockInService::class);
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
                [
                    'raw_material_id' => $this->item->id,
                    'unit_id' => $this->baseUnit->id,
                    'qty' => 50,
                    'unit_price' => 50000,
                    'notes' => null,
                ],
            ],
        );

        $stock->refresh();
        $this->assertEquals(65, $stock->current_stock);
        $this->assertNull($stock->last_notified_at);
    }

    public function test_daily_digest_respects_cooldown(): void
    {
        MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->update([
                'current_stock' => 10,
                'last_notified_at' => now()->subDays(1),
            ]);

        Notification::fake();

        $this->artisan('stock:daily-low-stock-digest')
            ->expectsOutput('No low stock items found.')
            ->assertSuccessful();

        Notification::assertNothingSent();

        MaterialStock::where('raw_material_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->update([
                'last_notified_at' => now()->subDays(4),
            ]);

        $this->artisan('stock:daily-low-stock-digest')
            ->expectsOutputToContain('Found 1 low stock item(s)')
            ->assertSuccessful();

        Notification::assertSentTo(
            [$this->user],
            LowStockNotification::class
        );
    }
}
