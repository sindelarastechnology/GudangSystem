<?php

namespace App\Services;

use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\StockLedger;
use App\Models\UnitConversion;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\LowStockNotification;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class StockLedgerService
{
    public function convertToBaseUnit(RawMaterial $item, int $unitId, float $qty): float
    {
        if ($unitId === $item->unit_id) {
            return $qty;
        }

        $conversion = UnitConversion::where('raw_material_id', $item->id)
            ->where('from_unit_id', $unitId)
            ->where('to_unit_id', $item->unit_id)
            ->first();

        if (!$conversion) {
            $conversion = UnitConversion::where('raw_material_id', $item->id)
                ->where('from_unit_id', $item->unit_id)
                ->where('to_unit_id', $unitId)
                ->first();

            if ($conversion) {
                return $qty / $conversion->conversion_factor;
            }

            throw new InvalidArgumentException(
                "No conversion rule found for item '{$item->name}' from unit ID {$unitId} to base unit ID {$item->unit_id}."
            );
        }

        return $qty * $conversion->conversion_factor;
    }

    public function recordIn(
        RawMaterial $item,
        Warehouse $warehouse,
        float $qtyBase,
        float $unitPrice,
        string $sourceType,
        int $sourceId,
        Carbon $date,
        ?string $notes = null,
    ): void {
        DB::transaction(function () use ($item, $warehouse, $qtyBase, $unitPrice, $sourceType, $sourceId, $date, $notes) {
            $stock = $this->lockOrCreateStock($item, $warehouse);

            $oldQty = (float) $stock->current_stock;
            $oldAvgCost = (float) $stock->current_avg_cost;

            $newQty = $oldQty + $qtyBase;
            $newValue = ($oldQty * $oldAvgCost) + ($qtyBase * $unitPrice);
            $newAvgCost = $newQty > 0 ? round($newValue / $newQty, 4) : 0;
            $newAssetValue = round($newQty * $newAvgCost, 2);

            StockLedger::create([
                'raw_material_id' => $item->id,
                'warehouse_id' => $warehouse->id,
                'transaction_date' => $date,
                'direction' => 'in',
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'qty' => $qtyBase,
                'unit_cost' => $unitPrice,
                'running_qty_balance' => $newQty,
                'running_avg_cost' => $newAvgCost,
                'running_asset_value' => $newAssetValue,
                'notes' => $notes,
            ]);

            $updates = [
                'current_stock' => $newQty,
                'current_avg_cost' => $newAvgCost,
                'current_asset_value' => $newAssetValue,
            ];

            if ($oldQty <= $stock->min_stock && $newQty > $stock->min_stock) {
                $updates['last_notified_at'] = null;
            }

            $stock->update($updates);
        });
    }

    public function recordOut(
        RawMaterial $item,
        Warehouse $warehouse,
        float $qtyBase,
        string $sourceType,
        int $sourceId,
        Carbon $date,
        ?string $notes = null,
    ): float {
        return DB::transaction(function () use ($item, $warehouse, $qtyBase, $sourceType, $sourceId, $date, $notes) {
            $stock = $this->lockStock($item, $warehouse);

            if (!$stock) {
                throw new InvalidArgumentException(
                    "Item '{$item->name}' has no stock in warehouse '{$warehouse->name}'. Nothing to deduct."
                );
            }

            $currentStock = (float) $stock->current_stock;
            if ($qtyBase > $currentStock) {
                throw new InvalidArgumentException(
                    "Insufficient stock for '{$item->name}' in '{$warehouse->name}': "
                    . "requested {$qtyBase}, available {$currentStock}."
                );
            }

            $costAtIssue = (float) $stock->current_avg_cost;
            $newQty = $currentStock - $qtyBase;
            $newAssetValue = round($newQty * $costAtIssue, 2);

            StockLedger::create([
                'raw_material_id' => $item->id,
                'warehouse_id' => $warehouse->id,
                'transaction_date' => $date,
                'direction' => 'out',
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'qty' => $qtyBase,
                'unit_cost' => $costAtIssue,
                'running_qty_balance' => $newQty,
                'running_avg_cost' => $costAtIssue,
                'running_asset_value' => $newAssetValue,
                'notes' => $notes,
            ]);

            $stock->update([
                'current_stock' => $newQty,
                'current_asset_value' => $newAssetValue,
            ]);

            if ($newQty <= $stock->min_stock) {
                $cooldownDays = config('warehouse.low_stock_notification_cooldown_days', 3);
                $shouldNotify = is_null($stock->last_notified_at) || 
                                $stock->last_notified_at->diffInDays(now()) >= $cooldownDays;
                
                if ($shouldNotify) {
                    $stock->update(['last_notified_at' => now()]);
                    
                    $users = User::whereHas('roles', function ($q) {
                        $q->whereIn('name', ['super_admin', 'admin']);
                    })->get();
                    
                    Notification::send($users, new LowStockNotification($stock));
                }
            }

            return $costAtIssue;
        });
    }

    private function lockOrCreateStock(RawMaterial $item, Warehouse $warehouse): MaterialStock
    {
        $stock = MaterialStock::where('raw_material_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            try {
                $stock = MaterialStock::create([
                    'raw_material_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'min_stock' => 0,
                    'current_stock' => 0,
                    'current_avg_cost' => 0,
                    'current_asset_value' => 0,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                $stock = MaterialStock::where('raw_material_id', $item->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }
        }

        return $stock;
    }

    private function lockStock(RawMaterial $item, Warehouse $warehouse): ?MaterialStock
    {
        return MaterialStock::where('raw_material_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->lockForUpdate()
            ->first();
    }
}
