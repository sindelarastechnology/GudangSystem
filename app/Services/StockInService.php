<?php

namespace App\Services;

use App\Models\StockInDetail;
use App\Models\StockInTransaction;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockInService
{
    public function __construct(
        private StockLedgerService $ledger,
        private DocumentNumberGenerator $docNumber,
    ) {}

    public function store(
        int $warehouseId,
        ?int $supplierId,
        string $type,
        Carbon $date,
        ?string $referenceNumber,
        ?string $attachment,
        ?string $notes,
        User $createdBy,
        array $details,
    ): StockInTransaction {
        return DB::transaction(function () use (
            $warehouseId, $supplierId, $type, $date, $referenceNumber,
            $attachment, $notes, $createdBy, $details
        ) {
            $warehouse = Warehouse::lockForUpdate()->findOrFail($warehouseId);

            if ($warehouse->is_locked) {
                throw new InvalidArgumentException(
                    "Gudang '{$warehouse->name}' sedang dalam proses opname. Transaksi ditunda."
                );
            }

            $transactionNumber = $this->docNumber->generate('stock_in', $date);

            $transaction = StockInTransaction::create([
                'transaction_number' => $transactionNumber,
                'transaction_date' => $date,
                'warehouse_id' => $warehouseId,
                'supplier_id' => $supplierId,
                'type' => $type,
                'reference_number' => $referenceNumber,
                'attachment' => $attachment,
                'notes' => $notes,
                'created_by' => $createdBy->id,
            ]);

            foreach ($details as $item) {
                $rawMaterial = \App\Models\RawMaterial::findOrFail($item['raw_material_id']);
                $qtyBase = $this->ledger->convertToBaseUnit(
                    $rawMaterial, $item['unit_id'], $item['qty']
                );
                $subtotal = round($item['qty'] * $item['unit_price'], 2);

                StockInDetail::create([
                    'stock_in_transaction_id' => $transaction->id,
                    'raw_material_id' => $rawMaterial->id,
                    'unit_id' => $item['unit_id'],
                    'qty' => $item['qty'],
                    'qty_base' => $qtyBase,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ]);

                $unitPriceBase = $qtyBase > 0 ? $subtotal / $qtyBase : 0;

                $this->ledger->recordIn(
                    $rawMaterial,
                    $warehouse,
                    $qtyBase,
                    $unitPriceBase,
                    'stock_in',
                    $transaction->id,
                    $date,
                    $item['notes'] ?? null,
                );
            }

            return $transaction;
        });
    }
}
