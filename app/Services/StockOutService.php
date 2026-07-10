<?php

namespace App\Services;

use App\Models\StockOutDetail;
use App\Models\StockOutTransaction;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockOutService
{
    public function __construct(
        private StockLedgerService $ledger,
        private DocumentNumberGenerator $docNumber,
    ) {}

    public function store(
        int $warehouseId,
        string $type,
        Carbon $date,
        ?string $destination,
        ?string $notes,
        User $createdBy,
        array $details,
    ): StockOutTransaction {
        return DB::transaction(function () use (
            $warehouseId, $type, $date, $destination, $notes, $createdBy, $details
        ) {
            $warehouse = Warehouse::lockForUpdate()->findOrFail($warehouseId);

            if ($warehouse->is_locked) {
                throw new InvalidArgumentException(
                    "Gudang '{$warehouse->name}' sedang dalam proses opname. Transaksi ditunda."
                );
            }

            $transactionNumber = $this->docNumber->generate('stock_out', $date);

            $transaction = StockOutTransaction::create([
                'transaction_number' => $transactionNumber,
                'transaction_date' => $date,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'destination' => $destination,
                'notes' => $notes,
                'created_by' => $createdBy->id,
            ]);

            foreach ($details as $item) {
                $rawMaterial = \App\Models\RawMaterial::findOrFail($item['raw_material_id']);
                $qtyBase = $this->ledger->convertToBaseUnit(
                    $rawMaterial, $item['unit_id'], $item['qty']
                );

                $costAtIssue = $this->ledger->recordOut(
                    $rawMaterial,
                    $warehouse,
                    $qtyBase,
                    'stock_out',
                    $transaction->id,
                    $date,
                    $item['notes'] ?? null,
                );

                $subtotalHpp = round($qtyBase * $costAtIssue, 2);

                StockOutDetail::create([
                    'stock_out_transaction_id' => $transaction->id,
                    'raw_material_id' => $rawMaterial->id,
                    'unit_id' => $item['unit_id'],
                    'qty' => $item['qty'],
                    'qty_base' => $qtyBase,
                    'cost_at_issue' => $costAtIssue,
                    'subtotal_hpp' => $subtotalHpp,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $transaction;
        });
    }
}
