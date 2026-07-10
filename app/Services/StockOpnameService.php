<?php

namespace App\Services;

use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Models\StockLedger;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockOpnameService
{
    public function __construct(
        private StockLedgerService $ledger,
        private DocumentNumberGenerator $docNumber,
    ) {}

    public function openSession(int $warehouseId, Carbon $date, ?string $notes, ?User $createdBy = null): StockOpname
    {
        return DB::transaction(function () use ($warehouseId, $date, $notes, $createdBy) {
            $warehouse = Warehouse::lockForUpdate()->findOrFail($warehouseId);

            if ($warehouse->is_locked) {
                throw new InvalidArgumentException(
                    "Gudang '{$warehouse->name}' sedang dalam proses opname. Tidak bisa membuka sesi baru."
                );
            }

            $opnameNumber = $this->docNumber->generate('stock_opname', $date);

            $opname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'opname_date' => $date,
                'warehouse_id' => $warehouseId,
                'status' => 'counting',
                'started_at' => now(),
                'notes' => $notes,
                'created_by' => $createdBy?->id ?? auth()->id(),
            ]);

            $warehouse->update([
                'is_locked' => true,
                'locked_by_opname_id' => $opname->id,
                'locked_at' => now(),
            ]);

            return $opname;
        });
    }

    public function saveDraftDetails(StockOpname $opname, array $details): void
    {
        if (!$opname->isCounting()) {
            throw new InvalidArgumentException(
                "Tidak dapat menyimpan detail. Sesi opname sudah {$opname->status}."
            );
        }

        DB::transaction(function () use ($opname, $details) {
        foreach ($details as $item) {
            $rawMaterial = RawMaterial::findOrFail($item['raw_material_id']);
            
            $physicalQtyBase = $this->ledger->convertToBaseUnit(
                $rawMaterial,
                $item['physical_qty_unit_id'],
                $item['physical_qty']
            );

            $existingStock = MaterialStock::where('raw_material_id', $rawMaterial->id)
                ->where('warehouse_id', $opname->warehouse_id)
                ->first();

            $systemQty = $existingStock ? (float) $existingStock->current_stock : 0;

            StockOpnameDetail::updateOrCreate(
                [
                    'stock_opname_id' => $opname->id,
                    'raw_material_id' => $rawMaterial->id,
                ],
                [
                    'system_qty' => $systemQty,
                    'physical_qty_unit_id' => $item['physical_qty_unit_id'],
                    'physical_qty' => $item['physical_qty'],
                    'physical_qty_base' => $physicalQtyBase,
                    'difference_qty' => 0,
                    'avg_cost_at_opname' => 0,
                    'difference_value' => 0,
                    'notes' => $item['notes'] ?? null,
                ]
            );
        }
        });
    }

    public function finalize(StockOpname $opname): void
    {
        if (!$opname->isCounting()) {
            throw new InvalidArgumentException(
                "Tidak dapat finalisasi. Sesi opname sudah {$opname->status}."
            );
        }

        DB::transaction(function () use ($opname) {
            $opname = StockOpname::lockForUpdate()->findOrFail($opname->id);
            if (!$opname->isCounting()) {
                throw new InvalidArgumentException(
                    "Tidak dapat finalisasi. Sesi opname sudah {$opname->status}."
                );
            }

            $warehouse = Warehouse::lockForUpdate()->findOrFail($opname->warehouse_id);

            $details = $opname->details;

            foreach ($details as $detail) {
                $stock = MaterialStock::where('raw_material_id', $detail->raw_material_id)
                    ->where('warehouse_id', $opname->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock && $detail->physical_qty_base > 0) {
                    $stock = MaterialStock::create([
                        'raw_material_id' => $detail->raw_material_id,
                        'warehouse_id' => $opname->warehouse_id,
                        'min_stock' => 0,
                        'current_stock' => 0,
                        'current_avg_cost' => 0,
                        'current_asset_value' => 0,
                    ]);
                }

                $systemQtyFinal = $stock ? (float) $stock->current_stock : 0;
                $avgCost = $stock ? (float) $stock->current_avg_cost : $this->getLastPurchaseCost($detail->raw_material_id);
                $differenceQty = $detail->physical_qty_base - $systemQtyFinal;
                $differenceValue = round($differenceQty * $avgCost, 2);

                $detail->update([
                    'system_qty' => $systemQtyFinal,
                    'difference_qty' => $differenceQty,
                    'avg_cost_at_opname' => $avgCost,
                    'difference_value' => $differenceValue,
                ]);

                if (abs($differenceQty) > 0.0001) {
                    if ($differenceQty > 0) {
                        $this->ledger->recordIn(
                            $detail->rawMaterial,
                            $opname->warehouse,
                            $differenceQty,
                            $avgCost,
                            'opname_adjustment',
                            $opname->id,
                            $opname->opname_date,
                            "Stock opname adjustment: {$opname->opname_number}"
                        );
                    } else {
                        $this->ledger->recordOut(
                            $detail->rawMaterial,
                            $opname->warehouse,
                            abs($differenceQty),
                            'opname_adjustment',
                            $opname->id,
                            $opname->opname_date,
                            "Stock opname adjustment: {$opname->opname_number}"
                        );
                    }
                }
            }

            $opname->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);

            $warehouse->update([
                'is_locked' => false,
                'locked_by_opname_id' => null,
                'locked_at' => null,
            ]);
        });
    }

    public function cancel(StockOpname $opname): void
    {
        if (!$opname->isCounting()) {
            throw new InvalidArgumentException(
                "Tidak dapat membatalkan. Sesi opname sudah {$opname->status}."
            );
        }

        DB::transaction(function () use ($opname) {
            $opname = StockOpname::lockForUpdate()->findOrFail($opname->id);
            if (!$opname->isCounting()) {
                throw new InvalidArgumentException(
                    "Tidak dapat membatalkan. Sesi opname sudah {$opname->status}."
                );
            }

            $warehouse = Warehouse::lockForUpdate()->findOrFail($opname->warehouse_id);

            $opname->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $warehouse->update([
                'is_locked' => false,
                'locked_by_opname_id' => null,
                'locked_at' => null,
            ]);
        });
    }

    private function getLastPurchaseCost(int $rawMaterialId): float
    {
        $lastLedger = StockLedger::where('raw_material_id', $rawMaterialId)
            ->where('direction', 'in')
            ->where('source_type', 'stock_in')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->first();

        return $lastLedger ? (float) $lastLedger->unit_cost : 0;
    }
}
