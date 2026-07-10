<?php

namespace App\Services;

use App\Models\MaterialStock;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\LowStockNotification;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class StockTransferService
{
    public function __construct(
        private StockLedgerService $ledger,
        private DocumentNumberGenerator $docNumber,
    ) {}

    public function store(
        int $fromWarehouseId,
        int $toWarehouseId,
        Carbon $date,
        ?string $notes,
        User $createdBy,
        array $details,
    ): StockTransfer {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new InvalidArgumentException('Gudang asal dan tujuan harus berbeda.');
        }

        return DB::transaction(function () use (
            $fromWarehouseId, $toWarehouseId, $date, $notes, $createdBy, $details
        ) {
            $whIds = [$fromWarehouseId, $toWarehouseId];
            sort($whIds);
            $wh1 = Warehouse::lockForUpdate()->findOrFail($whIds[0]);
            $wh2 = Warehouse::lockForUpdate()->findOrFail($whIds[1]);

            $from = $wh1->id === $fromWarehouseId ? $wh1 : $wh2;
            $to = $wh1->id === $toWarehouseId ? $wh1 : $wh2;

            if ($from->is_locked || $to->is_locked) {
                $locked = $from->is_locked ? $from->name : $to->name;
                throw new InvalidArgumentException("Gudang '{$locked}' sedang opname. Transaksi ditunda.");
            }

            $transfer = StockTransfer::create([
                'transfer_number' => $this->docNumber->generate('stock_transfer', $date),
                'transfer_date' => $date,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'notes' => $notes,
                'created_by' => $createdBy->id,
            ]);

            foreach ($details as $item) {
                $rm = \App\Models\RawMaterial::findOrFail($item['raw_material_id']);
                $qtyBase = $this->ledger->convertToBaseUnit($rm, $item['unit_id'], $item['qty']);

                $costAtTransfer = $this->processItem($rm, $from, $to, $qtyBase, $transfer->id, $date);

                StockTransferDetail::create([
                    'stock_transfer_id' => $transfer->id,
                    'raw_material_id' => $rm->id,
                    'unit_id' => $item['unit_id'],
                    'qty' => $item['qty'],
                    'qty_base' => $qtyBase,
                    'cost_at_transfer' => $costAtTransfer,
                ]);
            }

            return $transfer;
        });
    }

    private function processItem(
        \App\Models\RawMaterial $rm,
        Warehouse $from,
        Warehouse $to,
        float $qtyBase,
        int $transferId,
        Carbon $date,
    ): float {
        $firstWh = $from->id < $to->id ? $from : $to;
        $secondWh = $from->id < $to->id ? $to : $from;
        $isFirstFrom = $firstWh->id === $from->id;

        $stockFirst = $isFirstFrom
            ? $this->lockAndValidate($rm, $firstWh, $qtyBase)
            : $this->lockOrCreate($rm, $firstWh);

        $stockSecond = $isFirstFrom
            ? $this->lockOrCreate($rm, $secondWh)
            : $this->lockAndValidate($rm, $secondWh, $qtyBase);

        $fromStock = $isFirstFrom ? $stockFirst : $stockSecond;
        $costAtTransfer = (float) $fromStock->current_avg_cost;

        $newFromQty = (float) $fromStock->current_stock - $qtyBase;
        $newFromValue = round($newFromQty * $costAtTransfer, 2);

        StockLedger::create([
            'raw_material_id' => $rm->id,
            'warehouse_id' => $from->id,
            'transaction_date' => $date,
            'direction' => 'out',
            'source_type' => 'transfer_out',
            'source_id' => $transferId,
            'qty' => $qtyBase,
            'unit_cost' => $costAtTransfer,
            'running_qty_balance' => $newFromQty,
            'running_avg_cost' => $costAtTransfer,
            'running_asset_value' => $newFromValue,
        ]);

        $fromStock->update([
            'current_stock' => $newFromQty,
            'current_asset_value' => $newFromValue,
        ]);

        if ($newFromQty <= $fromStock->min_stock) {
            $cooldownDays = config('warehouse.low_stock_notification_cooldown_days', 3);
            $shouldNotify = is_null($fromStock->last_notified_at) || 
                            $fromStock->last_notified_at->diffInDays(now()) >= $cooldownDays;
            
            if ($shouldNotify) {
                $fromStock->update(['last_notified_at' => now()]);
                
                $users = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['super_admin', 'admin']);
                })->get();
                
                Notification::send($users, new LowStockNotification($fromStock));
            }
        }

        $toStock = $isFirstFrom ? $stockSecond : $stockFirst;
        $oldToQty = (float) ($toStock?->current_stock ?? 0);
        $oldToAvg = (float) ($toStock?->current_avg_cost ?? 0);
        $newToQty = $oldToQty + $qtyBase;
        $newToAvg = $newToQty > 0
            ? round((($oldToQty * $oldToAvg) + ($qtyBase * $costAtTransfer)) / $newToQty, 4)
            : 0;
        $newToValue = round($newToQty * $newToAvg, 2);

        StockLedger::create([
            'raw_material_id' => $rm->id,
            'warehouse_id' => $to->id,
            'transaction_date' => $date,
            'direction' => 'in',
            'source_type' => 'transfer_in',
            'source_id' => $transferId,
            'qty' => $qtyBase,
            'unit_cost' => $costAtTransfer,
            'running_qty_balance' => $newToQty,
            'running_avg_cost' => $newToAvg,
            'running_asset_value' => $newToValue,
        ]);

        $toStock->update([
            'current_stock' => $newToQty,
            'current_avg_cost' => $newToAvg,
            'current_asset_value' => $newToValue,
        ]);

        return $costAtTransfer;
    }

    private function lockAndValidate(\App\Models\RawMaterial $rm, Warehouse $wh, float $qtyBase): MaterialStock
    {
        $stock = MaterialStock::where('raw_material_id', $rm->id)
            ->where('warehouse_id', $wh->id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            throw new InvalidArgumentException("Item '{$rm->name}' tidak punya stok di '{$wh->name}'.");
        }
        if ($qtyBase > (float) $stock->current_stock) {
            throw new InvalidArgumentException(
                "Stok '{$rm->name}' di '{$wh->name}' tidak cukup: perlu {$qtyBase}, tersedia {$stock->current_stock}."
            );
        }

        return $stock;
    }

    private function lockOrCreate(\App\Models\RawMaterial $rm, Warehouse $wh): MaterialStock
    {
        $stock = MaterialStock::where('raw_material_id', $rm->id)
            ->where('warehouse_id', $wh->id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            try {
                $stock = MaterialStock::create([
                    'raw_material_id' => $rm->id,
                    'warehouse_id' => $wh->id,
                    'min_stock' => 0,
                    'current_stock' => 0,
                    'current_avg_cost' => 0,
                    'current_asset_value' => 0,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                $stock = MaterialStock::where('raw_material_id', $rm->id)
                    ->where('warehouse_id', $wh->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }
        }

        return $stock;
    }
}
