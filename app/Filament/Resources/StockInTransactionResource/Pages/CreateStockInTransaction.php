<?php

namespace App\Filament\Resources\StockInTransactionResource\Pages;

use App\Filament\Resources\StockInTransactionResource;
use App\Services\StockInService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateStockInTransaction extends CreateRecord
{
    protected static string $resource = StockInTransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(StockInService::class);

        return $service->store(
            warehouseId: $data['warehouse_id'],
            supplierId: $data['supplier_id'] ?? null,
            type: $data['type'],
            date: \Carbon\Carbon::parse($data['transaction_date']),
            referenceNumber: $data['reference_number'] ?? null,
            attachment: $data['attachment'] ?? null,
            notes: $data['notes'] ?? null,
            createdBy: Auth::user(),
            details: $data['details'],
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
